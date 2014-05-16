package com.siberian.app;

import static com.siberian.app.CommonUtilities.SERVEUR_URL;
import java.io.IOException;
import java.util.HashMap;
import java.util.Map;
import java.util.Random;

import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.location.LocationManager;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.IBinder;
import android.util.Log;

import static com.siberian.app.CommonUtilities.UPDATE_POSITION_URL;

public class GpsLocation extends Service
{
	
	private static final String TAG = "LocationService";
	private LocationManager mLocationManager = null;
	private static final int LOCATION_INTERVAL = 10*60*1000;
	private static final float LOCATION_DISTANCE = 100;
	private static final int MAX_ATTEMPTS = 5;
    private static final int BACKOFF_MILLI_SECONDS = 2000;
    private static final Random random = new Random();

	private class LocationListener implements android.location.LocationListener{
		
		String regId;
	    Location mLastLocation;
	    
	    public LocationListener(String provider)
	    {
	        Log.e(TAG, "LocationListener " + provider);
	        mLastLocation = new Location(provider);
	    }
	    @Override
	    public void onLocationChanged(Location location)
	    {
	    	regId = Main.__regId;
	        Log.e(TAG, "onLocationChanged: " + location);
	        mLastLocation.set(location);
	        if(regId != "") {
	        	new AsyncPost(this).execute();
	        }
	    }
	    
	    public class AsyncPost extends AsyncTask<String, Void, String> {
			
			public AsyncPost(LocationListener locationListener){
		    }

			@Override
			protected String doInBackground(String... urls) {
				
				long backoff = BACKOFF_MILLI_SECONDS + random.nextInt(1000);
		        for (int i = 1; i <= MAX_ATTEMPTS; i++) {
		        	Log.d(TAG, "Attempt #" + i + " to register position");
		            try {
		            	Map<String, String> params = new HashMap<String, String>();
						params.put("registration_id", regId);
				        params.put("latitude", String.valueOf(mLastLocation.getLatitude()));
				        params.put("longitude", String.valueOf(mLastLocation.getLongitude()));
				        String url = SERVEUR_URL+UPDATE_POSITION_URL;
				        Log.i("UPDATE POSITION URL", url);
				        com.siberian.app.ServerUtilities.post(url, params);
		    	        break;
		            } catch (IOException e) {
		                Log.e(TAG, "Failed to set displayed on attempt " + i, e);
		                if (i == MAX_ATTEMPTS) {
		                    break;
		                }
		                try {
		                    Log.d(TAG, "Sleeping for " + backoff + " ms before retry");
		                    Thread.sleep(backoff);
		                } catch (InterruptedException e1) {
		                    Log.d(TAG, "Thread interrupted: abort remaining retries!");
		                    Thread.currentThread().interrupt();
		                }
		                backoff *= 2;
		            }
		        }
				
				return "";
			}
			
		}
	    
	    @Override
	    public void onProviderDisabled(String provider)
	    {
	    }
	    @Override
	    public void onProviderEnabled(String provider)
	    {
	    }
	    @Override
	    public void onStatusChanged(String provider, int status, Bundle extras)
	    {
	    }
	} 
	
	LocationListener[] mLocationListeners = new LocationListener[] {
        new LocationListener(LocationManager.NETWORK_PROVIDER)
	};
	
	@Override
	public IBinder onBind(Intent arg0)
	{
	    return null;
	}
	
	@Override
	public int onStartCommand(Intent intent, int flags, int startId)
	{
	    super.onStartCommand(intent, flags, startId);       
	    return START_STICKY;
	}
	
	@Override
	public void onCreate()
	{	    
	    initializeLocationManager();
	    try {
	        mLocationManager.requestLocationUpdates(
	                LocationManager.NETWORK_PROVIDER, LOCATION_INTERVAL, LOCATION_DISTANCE,
	                mLocationListeners[0]);
	    } catch (java.lang.SecurityException ex) {
	        Log.i(TAG, "fail to request location update, ignore", ex);
	    } catch (IllegalArgumentException ex) {
	        Log.d(TAG, "network provider does not exist, " + ex.getMessage());
	    }
	}
	
	@Override
	public void onDestroy()
	{
	    super.onDestroy();
	    if (mLocationManager != null) {
	        for (int i = 0; i < mLocationListeners.length; i++) {
	            try {
	                mLocationManager.removeUpdates(mLocationListeners[i]);
	            } catch (Exception ex) {
	                Log.i(TAG, "fail to remove location listners, ignore", ex);
	            }
	        }
	    }
	}
	
	private void initializeLocationManager() {
	    if (mLocationManager == null) {
	        mLocationManager = (LocationManager) getApplicationContext().getSystemService(Context.LOCATION_SERVICE);
	    }
	}
}