package com.siberian.app;


import com.siberian.app.R;
import static com.siberian.app.CommonUtilities.SERVEUR_URL;
import static com.siberian.app.CommonUtilities.MARK_DISPLAYED_URL;
import static com.siberian.app.CommonUtilities.TAG;
import static com.siberian.app.CommonUtilities.UPDATE_POSITION_URL;

import java.io.IOException;
import java.util.HashMap;
import java.util.Map;
import java.util.Random;

import com.google.android.gcm.GCMRegistrar;

import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.location.LocationManager;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;

public class ProximityIntentReceiver extends BroadcastReceiver {

	String message_id;
	String regId;

	private static final String TAG = "ProximityIntent";
	private static final int MAX_ATTEMPTS = 5;
    private static final int BACKOFF_MILLI_SECONDS = 2000;
    private static final Random random = new Random();

	public void onCreate(Bundle savedInstanceState) {
	}

	@Override
	public void onReceive(Context context, Intent intent) {

		Log.i(TAG, "Proximity received");
		String key = LocationManager.KEY_PROXIMITY_ENTERING;

		Boolean entering = false;
		if(key != null) {
			entering = intent.getBooleanExtra(key, false);
		} else {
			entering = intent.getBooleanExtra("entering", false);
		}

	    if(entering == true ) {
	    	Log.i(TAG, String.valueOf(entering));
	        generateNotification(context, intent.getExtras().getString("MESSAGE"));
	    	regId = Main.__regId;
	    	message_id = intent.getExtras().getString("MESSAGE_ID");
	    	new AsyncPost(this).execute();
	    }
	}

	public class AsyncPost extends AsyncTask<String, Void, String> {

		public AsyncPost(ProximityIntentReceiver proximityIntentReceiver){

	    }

		@Override
		protected String doInBackground(String... urls) {

			long backoff = BACKOFF_MILLI_SECONDS + random.nextInt(1000);

	        for (int i = 1; i <= MAX_ATTEMPTS; i++) {
	        	Log.d(TAG, "Attempt #" + i + " to set displayed");
	            try {
	            	Map<String, String> params = new HashMap<String, String>();
                        params.put("registration_id", Main.__regId);
	    	        params.put("message_id", message_id);
	    	        String url = SERVEUR_URL+MARK_DISPLAYED_URL;
	    	        Log.i("serverUrl", url);
	    	        com.siberian.app.ServerUtilities.post(url, params);
	    	        Log.e(TAG, "Set displayed");
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
	                    // Activity finished before we complete - exit.
	                    Log.d(TAG, "Thread interrupted: abort remaining retries!");
	                    Thread.currentThread().interrupt();
	                }
	                backoff *= 2;
	            }
	        }

			return message_id;

		}

	}

	/**
     * Issues a notification to inform the user that server has sent a message.
     */
    @SuppressWarnings("deprecation")
	private static void generateNotification(Context context, String message) {
        int icon = R.drawable.push_icon;
        long when = System.currentTimeMillis();
        NotificationManager notificationManager = (NotificationManager)
                context.getSystemService(Context.NOTIFICATION_SERVICE);
        Notification notification = new Notification(icon, message, when);
        String title = context.getString(R.string.app_name);
        Intent notificationIntent = new Intent(context, Main.class);
        // set intent so it does not start a new activity
        notificationIntent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP |
                Intent.FLAG_ACTIVITY_SINGLE_TOP);
        PendingIntent intent =
                PendingIntent.getActivity(context, 0, notificationIntent, 0);
        notification.setLatestEventInfo(context, title, message, intent);
        notification.flags |= Notification.FLAG_AUTO_CANCEL;

        // Play default notification sound
        notification.defaults |= Notification.DEFAULT_SOUND;

        // Vibrate if vibrate is enabled
        notification.defaults |= Notification.DEFAULT_VIBRATE;

        notificationManager.notify(0, notification);
    }


}
