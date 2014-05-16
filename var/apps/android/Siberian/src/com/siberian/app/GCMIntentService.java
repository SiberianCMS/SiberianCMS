/*
 * Copyright 2012 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package com.siberian.app;

import static com.siberian.app.CommonUtilities.SENDER_ID;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.location.LocationManager;
import android.util.Log;

import com.google.android.gcm.GCMBaseIntentService;
import com.google.android.gcm.GCMRegistrar;

/**
 * IntentService responsible for handling GCM messages.
 */
public class GCMIntentService extends GCMBaseIntentService {

	private static final String TAG = "GCMIntentService";
    static final String PROXIMITY_INTENT_ACTION = "com.siberian.app.ProximityIntentReceiver";
    private LocationManager locationManager;

    public GCMIntentService() {
        super(SENDER_ID);
    }

    @Override
    protected void onRegistered(Context context, String registrationId) {
    	Log.i(TAG, "Device registered: regId = " + registrationId);
        Main.setRegId(registrationId);
        Main.loadNotif();
        ServerUtilities.register(context, registrationId);
    }

    @Override
    protected void onUnregistered(Context context, String registrationId) {
        Log.i(TAG, "Device unregistered");
    }

    @Override
    protected void onMessage(Context context, Intent intent) {
    	
    	if(Main.canLoadNotifs) {
    		Main.loadNotif();
    	}
    	else {
    		Main.reloadNotifs = true;
    	}
    	
    	Intent proxintent = new Intent(PROXIMITY_INTENT_ACTION);
    	proxintent.putExtra("MESSAGE_ID", intent.getExtras().getString("message_id"));
		proxintent.putExtra("MESSAGE", intent.getExtras().getString("message"));
		proxintent.putExtra("APP_ID", intent.getExtras().getString("app_id"));
    	PendingIntent proximityIntent = PendingIntent.getBroadcast(this, -1, proxintent, PendingIntent.FLAG_UPDATE_CURRENT);
        Log.i(TAG, "message_id : " + intent.getExtras().getString("message_id"));
    	if (
			intent.getExtras().getString("latitude") != null && 
			intent.getExtras().getString("longitude") != null && 
			intent.getExtras().getString("radius") != null &&
			Float.parseFloat(intent.getExtras().getString("radius")) != 0 ) 
    	{
    		String target_latitude = intent.getExtras().getString("latitude");
        	String target_longitude = intent.getExtras().getString("longitude");
        	//Conversion en metres
        	Float target_radius = Float.parseFloat(intent.getExtras().getString("radius"))*1000;
        	
            Location target_location = new Location("target");
    		target_location.setLatitude(Double.parseDouble(target_latitude));
    		target_location.setLongitude(Double.parseDouble(target_longitude));
    		Log.e(TAG, "TargetLocation: " + target_location);
    		
    		locationManager = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
    		locationManager.addProximityAlert(target_location.getLatitude(), target_location.getLongitude(), target_radius, 1000, proximityIntent);
    	} else {
    		Log.i(TAG, "Missing geo data");
    		proxintent.putExtra("entering", true);
    		sendBroadcast(proxintent);
    	}
    }

    @Override
    protected void onDeletedMessages(Context context, int total) {

    }

    @Override
    public void onError(Context context, String errorId) {
        Log.i(TAG, "Received error: " + errorId);
    }

    @Override
    protected boolean onRecoverableError(Context context, String errorId) {
        // log message
        Log.i(TAG, "Received recoverable error: " + errorId);
        return super.onRecoverableError(context, errorId);
    }

}
