package com.siberiancms.angular;

import android.util.Log;
import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.DialogInterface;
import android.content.Context;
import android.widget.Toast;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;

import android.annotation.TargetApi;
import android.app.Activity;
import android.content.Intent;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.view.MotionEvent;
import android.view.View;
import android.net.Uri;

import android.webkit.WebChromeClient;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.webkit.GeolocationPermissions;



public class Main extends Activity {

   /**
    * Main Webview
    */
    public static WebView webView;

    public static Boolean webviewIsLoaded = false;
    public static Boolean webviewHasFailed = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_main);

        webView = (WebView) findViewById(R.id.webView);
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
                pd.dismiss();
            }

            @Override
            public boolean shouldOverrideUrlLoading(WebView Webview, String url) {
                Log.i("url", url);

                WebView.HitTestResult hr = Webview.getHitTestResult();
                Log.i("Clicked", "getExtra = "+ hr.getExtra() + "\t\t Type=" + hr.getType());
                Log.i("SRC_ANCHOR_TYPE", String.valueOf(WebView.HitTestResult.SRC_ANCHOR_TYPE));
                Log.i("SRC_IMAGE_ANCHOR_TYPE", String.valueOf(WebView.HitTestResult.SRC_IMAGE_ANCHOR_TYPE));
                if(hr.getType() == WebView.HitTestResult.SRC_ANCHOR_TYPE) {

                    if (url.startsWith("tel:")) {
                        startActivity(new Intent(Intent.ACTION_DIAL, Uri.parse(url)));
                    } else if (url.startsWith("geo:")) {
                        startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(url)));
                    } else if (url.contains("www.youtube.com")) {
                        Intent myWebLink = new Intent(Intent.ACTION_VIEW);
                        Uri uri = Uri.parse(url);
                        myWebLink.setDataAndType(uri, "text/html");
                        myWebLink.addCategory(Intent.CATEGORY_BROWSABLE);
                        startActivity(myWebLink);

                    } else {
                        Intent intent = new Intent(Main.this, Browser.class);
                        intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                        intent.putExtra("url", url);
                        startActivity(intent);
                    }

                    return true;

                }

                return false;

            }

            public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
                view.setVisibility(WebView.GONE);
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

        Log.i("Loading URL", this.getApplicationContext().getString(R.string.url));
        webView.loadUrl(this.getApplicationContext().getString(R.string.url));

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

}
