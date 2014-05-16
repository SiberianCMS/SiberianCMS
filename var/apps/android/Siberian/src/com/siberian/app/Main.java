package com.siberian.app;

import static com.siberian.app.CommonUtilities.DISPLAY_MESSAGE_ACTION;
import static com.siberian.app.CommonUtilities.SENDER_ID;

import java.net.URL;
import java.net.URLConnection;
import java.util.Locale;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.IntentFilter;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.StrictMode;
import android.util.Log;
import android.view.Menu;
import android.webkit.JavascriptInterface;
import android.webkit.JsResult;
import android.webkit.WebChromeClient;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Toast;

import com.google.android.gcm.GCMRegistrar;
import com.siberian.app.StreamAudio;

public class Main extends Activity {

	public static WebView Webview;
	private static Context context;
	public static Boolean isAtLeast11 = true;
	public static Boolean reloadFacebook = false;
	public static Boolean reloadNotifs = false;
	public static Boolean canLoadNotifs = false;
	public static Boolean webviewIsLoaded = false;
	public static Boolean webviewHasFailed = false;
	public static String __regId = "";

    AsyncTask<Void, Void, Void> mRegisterTask;

	@SuppressLint("SetJavaScriptEnabled")
	@Override
	protected void onCreate(Bundle savedInstanceState) {

		Main.context = getApplicationContext();

		int SDK = android.os.Build.VERSION.SDK_INT;
		isAtLeast11 = SDK >= 11;

		super.onCreate(savedInstanceState);
		setContentView(R.layout.main);
		__regId = GCMRegistrar.getRegistrationId(this);

		this.preparePush();

		Webview = (WebView) findViewById(R.id.wv);
        Webview.getSettings().setJavaScriptEnabled(true);
        final ProgressDialog pd = ProgressDialog.show(Main.this, "",
        		this.getApplicationContext().getString(R.string.load_message), true);
    	Webview.setInitialScale(1);
    	Webview.getSettings().setGeolocationEnabled(true);
    	Webview.getSettings().setUseWideViewPort(false);
    	Webview.getSettings().setJavaScriptCanOpenWindowsAutomatically(true);
    	Webview.setScrollBarStyle(WebView.SCROLLBARS_OUTSIDE_OVERLAY);
    	Webview.getSettings().setSupportZoom(false);
    	Webview.getSettings().setBuiltInZoomControls(false);
    	if(isAtLeast11) {
    		Webview.getSettings().setDisplayZoomControls(false);
    	}

    	Webview.setWebViewClient(new WebViewClient() {
    		public void onPageFinished(WebView view, String url) {
    			Main.loadNotif();
    			Main.webviewIsLoaded = !Main.webviewHasFailed;
    			pd.dismiss();
		    }
    		@Override
    	    public boolean shouldOverrideUrlLoading(WebView view, String url) {

    	        Log.i("url", url);
    	        if(url.contains("vnd.youtube")) {
    	            Uri uri = Uri.parse(url);
    	            Intent intent = new Intent(Intent.ACTION_VIEW, uri);
    	            startActivity(intent);
    	            return true;
    	        } else if (url.startsWith("tel:")) {
    	            startActivity(new Intent(Intent.ACTION_DIAL, Uri.parse(url)));
    	            return true;
    	        } else if (url.startsWith("geo:")) {
    	            startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(url)));
    	            return true;
    	        } else if(url.contains("www.youtube.com")) {
					Intent myWebLink = new Intent(android.content.Intent.ACTION_VIEW);
					Uri uri = Uri.parse(url);
					myWebLink.setDataAndType(uri, "text/html");
					myWebLink.addCategory(Intent.CATEGORY_BROWSABLE);
					startActivity(myWebLink);
					return true;
    	        } else {
    				//A ouvrir dans la webview
    				if(url.toLowerCase().contains("webview".toLowerCase()) || url.toLowerCase().contains("https://m.facebook.com/dialog/oauth".toLowerCase())) {
						Intent intent = new Intent(Main.this, Browser.class);
    					intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        				intent.putExtra("url", url);
        				startActivity(intent);
    				} else if(url.toLowerCase().contains("radio".toLowerCase())) {
    					Intent intent = new Intent(Main.this, StreamAudio.class);
        				intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        				intent.putExtra("url", url);
        				startActivity(intent);
    				} else {
    					view.loadUrl(url);
    				}
    				return true;
    			}
    	    }

    		public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
    			view.setVisibility(android.webkit.WebView.GONE);
    			Toast.makeText(getApplicationContext(), R.string.no_internet_connection,  Toast.LENGTH_LONG).show();
    			Main.webviewIsLoaded = false;
    			Main.webviewHasFailed = true;
		    }
		});

    	Webview.setWebChromeClient(new WebChromeClient() {
    		@Override
    		public boolean onJsAlert(WebView view, String url, String message, JsResult result) {
	                return super.onJsAlert(view, url, message, result);
    	       }
    	});

    	Webview.addJavascriptInterface(new Object() {
    		@JavascriptInterface
    		public void setPage(String page) {

    			if(page.equals("home")) {
        			DialogInterface.OnClickListener dialogClickListener = new DialogInterface.OnClickListener() {
    		    	    @Override
    		    	    public void onClick(DialogInterface dialog, int which) {
    		    	    	Webview.loadUrl("javascript:page.goBack();");
    		    	        switch (which){
    		    	        	case DialogInterface.BUTTON_POSITIVE:
	    		    	            finish();
    		    	            break;
    		    	        }
    		    	    }
    		    	};

    		    	AlertDialog.Builder builder = new AlertDialog.Builder(Webview.getContext());
    		    	builder.setMessage(R.string.quit_message).setPositiveButton(R.string.yes, dialogClickListener)
    		    	    .setNegativeButton(R.string.no, dialogClickListener).show();
    		        return;
    			} else {
    				Webview.loadUrl("javascript:page.goBack();");
    			}
        	}

    		@JavascriptInterface
    		public void setlanguage(String page) {
    			Log.i("url", page);
        	}

    	}, "Android");

    	Webview.loadUrl(this.getApplicationContext().getString(R.string.url));

        startService(new Intent(this, GpsLocation.class));
	}

    @SuppressLint({ "JavascriptInterface" })
	@Override
    public void onBackPressed() {
    	Webview.loadUrl("javascript:page.androidGoBack();");
    }

    public void preparePush() {
    	Log.i("preparePush", "preparePush");

        GCMRegistrar.checkDevice(this);

        GCMRegistrar.checkManifest(this);
        setContentView(R.layout.main);

        registerReceiver(mHandleMessageReceiver,
                new IntentFilter(DISPLAY_MESSAGE_ACTION));
        __regId = GCMRegistrar.getRegistrationId(this);
        if (__regId.equals("")) {
            // Automatically registers application on startup.
            GCMRegistrar.register(this, SENDER_ID);
        } else {
            // Device is already registered on GCM, check server.
            if (!GCMRegistrar.isRegisteredOnServer(this)) {

                final Context context = this;
                mRegisterTask = new AsyncTask<Void, Void, Void>() {

                    @Override
                    protected Void doInBackground(Void... params) {
                        boolean registered = ServerUtilities.register(context, __regId);

                        if (!registered) {
                            GCMRegistrar.unregister(context);
                        }
                        return null;
                    }

                    @Override
                    protected void onPostExecute(Void result) {
                        mRegisterTask = null;
                    }

                };
                mRegisterTask.execute(null, null, null);
            }
        }

    }

    public static Context getMainContext() {
        return Main.context;
    }

    public static void setRegId(String newRegId) {
    	__regId = newRegId;
    }

    public static void loadNotif() {
    	final String regId = __regId;
    	Webview.loadUrl("javascript:customer.device_uid = '"+regId+"'");
    	Webview.loadUrl("javascript:notif.loadBadge()");
    }

    @Override
    protected void onDestroy() {

    	Log.i("onDestroy", "onDestroy");

        if (mRegisterTask != null) {
            mRegisterTask.cancel(true);
        }
        try {
        	unregisterReceiver(mHandleMessageReceiver);
        	GCMRegistrar.onDestroy(this);
        } catch(Exception e) {
        	Log.i("destroy", "receiver not registered");
    	}

        Main.reloadNotifs = false;
        Main.canLoadNotifs = false;

        super.onDestroy();
    }

    private final BroadcastReceiver mHandleMessageReceiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context context, Intent intent) {
        }
    };

    @Override
    protected void onResume() {
    	super.onResume();
    	Main.canLoadNotifs = true;
    	if(Main.reloadNotifs && Main.webviewIsLoaded) {
    		Main.loadNotif();
    		Main.reloadNotifs = false;
    	}

    	if(Main.reloadFacebook) {
	    	Webview.loadUrl("javascript:facebook.init();");
	    	Main.reloadFacebook = false;
    	} else {
    		Webview.loadUrl("javascript:loader.hide();");
    	}

    	if(Main.webviewIsLoaded) {
    		Webview.loadUrl("javascript:app.onFocus();");
    	}
    }

    @Override
    protected void onPause() {
    	super.onPause();
    	Main.canLoadNotifs = false;
    }

}
