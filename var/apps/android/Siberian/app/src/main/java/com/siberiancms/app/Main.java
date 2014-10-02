package com.siberiancms.app;

import android.content.SharedPreferences;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.os.AsyncTask;
import android.util.Log;
import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.DialogInterface;
import android.content.Context;
import android.view.View;
import android.widget.TextView;
import android.widget.Toast;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.net.Uri;
import com.google.android.gms.common.GooglePlayServicesUtil;
import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.gcm.GoogleCloudMessaging;
import com.siberiancms.app.util.CommonUtilities;


import android.webkit.WebResourceResponse;
import android.webkit.WebChromeClient;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.webkit.GeolocationPermissions;

import java.io.IOException;
import java.lang.reflect.Method;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.Map;
import java.util.concurrent.atomic.AtomicInteger;


public class Main extends Activity {

   /**
    * Main Webview
    */
    public static WebView webView;

    public static Boolean webviewIsLoaded = false;
    public static Boolean webviewHasFailed = false;

    public static final String EXTRA_MESSAGE = "message";
    public static final String PROPERTY_REG_ID = "registration_id";
    private static final String PROPERTY_APP_VERSION = "appVersion";
    private static final int PLAY_SERVICES_RESOLUTION_REQUEST = 9000;

    public static String baseUrl = "";

    GoogleCloudMessaging gcm;
    AtomicInteger msgId = new AtomicInteger();
    Context context;

    String regid;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_main);

        baseUrl = this.getApplicationContext().getString(R.string.url);

        webView = (WebView) findViewById(R.id.webView);
        webView.getSettings().setUserAgentString(webView.getSettings().getUserAgentString() + " type/siberian.application");
        WebSettings settings = webView.getSettings();

        settings.setJavaScriptEnabled(true);
        final ProgressDialog pd = ProgressDialog.show(Main.this, "",
                this.getApplicationContext().getString(R.string.load_message), true);
        webView.setInitialScale(1);
        settings.setGeolocationEnabled(true);
        settings.setUseWideViewPort(false);
        settings.setJavaScriptCanOpenWindowsAutomatically(false);
        settings.setSupportZoom(false);
        settings.setSupportMultipleWindows(false);
        settings.setBuiltInZoomControls(false);
        settings.setDisplayZoomControls(false);

        settings.setAppCachePath(getApplicationContext().getCacheDir().getAbsolutePath());
        settings.setAllowFileAccess(true);
        settings.setAppCacheEnabled(true);
        settings.setCacheMode(WebSettings.LOAD_DEFAULT);

        checkConnection();

        webView.setScrollBarStyle(WebView.SCROLLBARS_OUTSIDE_OVERLAY);

        webView.setWebViewClient(new WebViewClient() {
            public void onPageFinished(WebView view, String url) {

                Main.webviewIsLoaded = !Main.webviewHasFailed;
                view.loadUrl("javascript:if(window.Application) window.Application.handle_geo_protocol = true;");
                pd.dismiss();
            }

            @Override
            public boolean shouldOverrideUrlLoading(WebView Webview, String url) {
                Log.i("url", url);

                WebView.HitTestResult hr = Webview.getHitTestResult();

                try {

                    if (hr == null) {
                        return false;
                    }

                    if (hr.getType() == WebView.HitTestResult.SRC_ANCHOR_TYPE) {

                        if (url.startsWith("tel:")) {
                            startActivity(new Intent(Intent.ACTION_DIAL, Uri.parse(url)));
                        } else if (url.contains("www.youtube.com")) {
                            Intent myWebLink = new Intent(Intent.ACTION_VIEW);
                            Uri uri = Uri.parse(url);
                            myWebLink.setDataAndType(uri, "text/html");
                            myWebLink.addCategory(Intent.CATEGORY_BROWSABLE);
                            startActivity(myWebLink);
                        } else if (url.startsWith("geo:")) {
                            startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(url)));
                            return true;
                        } else {
                            Intent intent = new Intent(Main.this, Browser.class);
                            intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                            intent.putExtra("url", url);
                            startActivity(intent);
                        }

                        return true;

                    } else if (url.startsWith("geo:")) {
                        startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(url)));
                        return true;
                    } else {
                        Log.e("Webview", "Not from a click");
                    }
                } catch(Exception e) {

                }

                return false;

            }

            @Override
            public WebResourceResponse shouldInterceptRequest(final WebView view, String url) {

                final Uri uri = Uri.parse(url);

                Log.e("Intercepting path:", uri.getPath());
                if (uri.getPath().startsWith("/app:")) {

                    Main.this.runOnUiThread(new Runnable() {
                        public void run() {

//                            Map<String, String> params = this._parseParams(uri.getPath());
                            ArrayList paramsTmp = new ArrayList(Arrays.asList(uri.getPath().split(":")));
                            paramsTmp.remove(0);
                            Map<String, String> params = new HashMap<String, String>();

                            for(int i = 0; i < paramsTmp.size(); i++) {
                                params.put(paramsTmp.get(i).toString(), paramsTmp.get(i+1).toString());
                                i++;
                            }

                            Log.e("Method", "Done parsing");
                            for (String methodName : params.keySet()) {
                                Log.e("Method", methodName);
                                Method methodToFind = null;

                                try {
                                    Class[] cArg = new Class[1];
                                    cArg[0] = String.class;
                                    methodToFind = Main.class.getMethod(methodName, cArg);
                                    if (methodToFind != null) {
                                        Log.e("Method", "Found");

                                        methodToFind.invoke(Main.this, params.get(methodName));

                                    } else {
                                        Log.e("Method", "Not Found");
                                    }
                                } catch (Exception e) {
                                    Log.e("Method", e.toString());
                                }

                            }
                        }
                    });
//                        Log.e("List parameters", Map);

                }

                return super.shouldInterceptRequest(view, url);
//                if (url.contains(".css")) {
//                    return getCssWebResourceResponseFromAsset();
//                } else {
//                    return super.shouldInterceptRequest(view, url);
//                }
            }

            private Map _parseParams(String path) {

                ArrayList paramsTmp = new ArrayList(Arrays.asList(path.split(":")));
                paramsTmp.remove(0);
                Map<String, String> params = new HashMap<String, String>();

                for(int i = 0; i < paramsTmp.size(); i++) {
                    params.put(paramsTmp.get(i).toString(), paramsTmp.get(i+1).toString());
                    i++;
                }

                return params;
            }

            public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
//                view.setVisibility(WebView.GONE);
                Toast.makeText(getApplicationContext(), R.string.no_internet_connection, Toast.LENGTH_LONG).show();
                Main.webviewIsLoaded = false;
                Main.webviewHasFailed = true;
            }
        });

        webView.setWebChromeClient(new WebChromeClient() {
            public void onGeolocationPermissionsShowPrompt(String origin, GeolocationPermissions.Callback callback) {
                callback.invoke(origin, true, false);
            }
        });

        Log.i("Loading URL", baseUrl);
        webView.loadUrl(baseUrl);

        // Handling the GCM Registration
        if (checkPlayServices()) {
            gcm = GoogleCloudMessaging.getInstance(this);
            regid = getRegistrationId(context);

            if (regid.isEmpty()) {
                registerInBackground();
            }
        } else {
            Log.i(CommonUtilities.TAG, "No valid Google Play Services APK found.");
        }

    }

    @Override
    protected void onResume() {
        super.onResume();
        checkPlayServices();
    }

    public void setIsOnline(String isOnline) {
        if(isOnline == "0") {
            Log.e("Method", "is now offline");
            webView.getSettings().setCacheMode(WebSettings.LOAD_CACHE_ELSE_NETWORK);
        } else {
            Log.e("Method", "is now online");
            webView.getSettings().setCacheMode(WebSettings.LOAD_DEFAULT);
        }
    }

    @Override
    public void onBackPressed() {
        if(webView.canGoBack()) {
            webView.goBack();
        } else {

            DialogInterface.OnClickListener dialogClickListener = new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    if(which == DialogInterface.BUTTON_POSITIVE) {
                        finish();
                    }
                }
            };

            AlertDialog.Builder builder = new AlertDialog.Builder(webView.getContext());
            builder.setMessage(R.string.quit_message).setPositiveButton(R.string.yes, dialogClickListener)
                    .setNegativeButton(R.string.no, dialogClickListener).show();
            return;

        }
    }

    private void checkConnection() {

        boolean isConnected = false;
        ConnectivityManager check = (ConnectivityManager) this.getSystemService(Context.CONNECTIVITY_SERVICE);
        if (check != null) {

            NetworkInfo[] info = check.getAllNetworkInfo();
            if (info != null) {
                for (int i = 0; i < info.length; i++) {
                    if (info[i].getState() == NetworkInfo.State.CONNECTED) {
                        isConnected = true;
                    }
                }

                if(!isConnected) {
                    webView.getSettings().setCacheMode(WebSettings.LOAD_CACHE_ELSE_NETWORK);
                }
            }

        }

    }

    /** Push Notification **/

    /**
     * Check the device to make sure it has the Google Play Services APK. If
     * it doesn't, display a dialog that allows users to download the APK from
     * the Google Play Store or enable it in the device's system settings.
     */
    private boolean checkPlayServices() {
        int resultCode = GooglePlayServicesUtil.isGooglePlayServicesAvailable(this);
        if (resultCode != ConnectionResult.SUCCESS) {
            if (GooglePlayServicesUtil.isUserRecoverableError(resultCode)) {
                GooglePlayServicesUtil.getErrorDialog(resultCode, this,
                        PLAY_SERVICES_RESOLUTION_REQUEST).show();
            } else {
                Log.i(CommonUtilities.TAG, "This device is not supported.");
                finish();
            }
            return false;
        }
        return true;
    }

    /**
     * Stores the registration ID and the app versionCode in the application's
     * {@code SharedPreferences}.
     *
     * @param context application's context.
     * @param regId registration ID
     */
    private void storeRegistrationId(Context context, String regId) {
        final SharedPreferences prefs = getGcmPreferences(context);
        int appVersion = getAppVersion(context);
        Log.i(CommonUtilities.TAG, "Saving regId on app version " + appVersion);
        SharedPreferences.Editor editor = prefs.edit();
        editor.putString(PROPERTY_REG_ID, regId);
        editor.putInt(PROPERTY_APP_VERSION, appVersion);
        editor.commit();
    }

    /**
     * Gets the current registration ID for application on GCM service, if there is one.
     * <p>
     * If result is empty, the app needs to register.
     *
     * @return registration ID, or empty string if there is no existing
     *         registration ID.
     */
    private String getRegistrationId(Context context) {
        final SharedPreferences prefs = getGcmPreferences(context);
        String registrationId = prefs.getString(PROPERTY_REG_ID, "");
        if (registrationId.isEmpty()) {
            Log.i(CommonUtilities.TAG, "Registration not found.");
            return "";
        }
        // Check if app was updated; if so, it must clear the registration ID
        // since the existing regID is not guaranteed to work with the new
        // app version.
        int registeredVersion = prefs.getInt(PROPERTY_APP_VERSION, Integer.MIN_VALUE);
        int currentVersion = getAppVersion(context);
        if (registeredVersion != currentVersion) {
            Log.i(CommonUtilities.TAG, "App version changed.");
            return "";
        }
        return registrationId;
    }

    /**
     * Registers the application with GCM servers asynchronously.
     * <p>
     * Stores the registration ID and the app versionCode in the application's
     * shared preferences.
     */
    private void registerInBackground() {
        new AsyncTask<Void, Void, String>() {
            @Override
            protected String doInBackground(Void... params) {
                String msg = "";
                try {
                    if (gcm == null) {
                        gcm = GoogleCloudMessaging.getInstance(context);
                    }
                    regid = gcm.register(CommonUtilities.SENDER_ID);
                    msg = "Device registered, registration ID=" + regid;

                    // You should send the registration ID to your server over HTTP, so it
                    // can use GCM/HTTP or CCS to send messages to your app.
                    sendRegistrationIdToBackend();

                    // For this demo: we don't need to send it because the device will send
                    // upstream messages to a server that echo back the message using the
                    // 'from' address in the message.

                    // Persist the regID - no need to register again.
                    storeRegistrationId(context, regid);
                } catch (IOException ex) {
                    msg = "Error :" + ex.getMessage();
                    // If there is an error, don't just keep trying to register.
                    // Require the user to click a button again, or perform
                    // exponential back-off.
                }
                return msg;
            }

            @Override
            protected void onPostExecute(String msg) {
                Log.e(CommonUtilities.TAG, msg);
            }
        }.execute(null, null, null);
    }

    // Send an upstream message.
    public void onClick(final View view) {

        new AsyncTask<Void, Void, String>() {
            @Override
            protected String doInBackground(Void... params) {
                String msg = "";
                try {
                    Bundle data = new Bundle();
                    data.putString("my_message", "Hello World");
                    data.putString("my_action", "com.google.android.gcm.demo.app.ECHO_NOW");
                    String id = Integer.toString(msgId.incrementAndGet());
                    gcm.send(CommonUtilities.SENDER_ID + "@gcm.googleapis.com", id, data);
                    msg = "Sent message";
                } catch (IOException ex) {
                    msg = "Error :" + ex.getMessage();
                }
                return msg;
            }

            @Override
            protected void onPostExecute(String msg) {
                Log.e(CommonUtilities.TAG, msg);
            }
        }.execute(null, null, null);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
    }

    /**
     * @return Application's version code from the {@code PackageManager}.
     */
    private static int getAppVersion(Context context) {
        try {
            PackageInfo packageInfo = context.getPackageManager()
                    .getPackageInfo(context.getPackageName(), 0);
            return packageInfo.versionCode;
        } catch (PackageManager.NameNotFoundException e) {
            // should never happen
            throw new RuntimeException("Could not get package name: " + e);
        }
    }

    /**
     * @return Application's {@code SharedPreferences}.
     */
    private SharedPreferences getGcmPreferences(Context context) {
        // This sample app persists the registration ID in shared preferences, but
        // how you store the regID in your app is up to you.
        return getSharedPreferences(Main.class.getSimpleName(),
                Context.MODE_PRIVATE);
    }
    /**
     * Sends the registration ID to your server over HTTP, so it can use GCM/HTTP or CCS to send
     * messages to your app. Not needed for this demo since the device sends upstream messages
     * to a server that echoes back the message using the 'from' address in the message.
     */
    private void sendRegistrationIdToBackend() {
        // Your implementation here.
    }


}
