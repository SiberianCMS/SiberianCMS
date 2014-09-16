package com.siberiancms.app;

import android.app.Activity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.webkit.WebChromeClient;
import android.widget.Button;
import android.widget.ImageButton;

public class Browser extends Activity {
	private ImageButton bBack;
	private ImageButton bForward;
	private ImageButton bReload;
	private Button bDone;
    private WebView webView;
    private boolean mIsLoadFinish = false;
    
    protected void onCreate(Bundle savedInstanceState) {
    	super.onCreate(savedInstanceState);
		setContentView(R.layout.browser);
		
		webView = (WebView) findViewById(R.id.webView);
		webView.getSettings().setSupportZoom(true);
		webView.getSettings().setBuiltInZoomControls(true);
        webView.getSettings().setDisplayZoomControls(false);

		webView.getSettings().setUseWideViewPort(true);
		webView.getSettings().setJavaScriptEnabled(true);
		webView.getSettings().setUseWideViewPort(true);
		webView.getSettings().setJavaScriptCanOpenWindowsAutomatically(true);
		webView.setScrollBarStyle(WebView.SCROLLBARS_INSIDE_OVERLAY);
		
		bBack = (ImageButton) findViewById(R.id.bBack);
		bForward = (ImageButton) findViewById(R.id.bForward);
		bReload = (ImageButton) findViewById(R.id.bReload);
        bDone = (Button) findViewById(R.id.bDone);
		
    	String url = (String) getIntent().getSerializableExtra("url");
    	
    	// Setup for button controller
    	bReload.setOnClickListener(new ImageButton.OnClickListener() {
    	    @Override
    	    public void onClick(View v) {
    	    	webView.reload();
    	    	enableControllerButton();
    	    }
    	});
    	bBack.setOnClickListener(new ImageButton.OnClickListener() {
    	    @Override
    	    public void onClick(View v) {
    	    	webView.goBack();
    	    	enableControllerButton();
    	    }
    	});
    	bForward.setOnClickListener(new ImageButton.OnClickListener() {
            @Override
            public void onClick(View v) {
                webView.goForward();
                enableControllerButton();
            }
        });

        bDone.setOnClickListener(new ImageButton.OnClickListener() {
            @Override
            public void onClick(View v) {
                finish();
            }
        });
    	
    	webView.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, String url) {
                Log.i("url : ", url);
                if (url.contains("close/1")) {
                    finish();
                    return false;
                } else {
                    view.loadUrl(url);
                }
                return true;
            }

            @Override
            public void onPageStarted(WebView view, String url, android.graphics.Bitmap favicon) {

            }

            @Override
            public void onPageFinished(WebView view, String url) {
                mIsLoadFinish = true;
                enableControllerButton();
                if (url.contains("https://m.facebook.com/v2.0/dialog/oauth")) {
//                    Main.reloadFacebook = true;
                }
            }
        });

    	webView.setWebChromeClient(new WebChromeClient());
    	
    	webView.loadUrl(url);
    }
    
	@Override
 	public void onBackPressed() {
    	finish();
    }
	
	public void enableControllerButton() {
		
		if (mIsLoadFinish) {
			bReload.setEnabled(true);
			if (webView.canGoBack()) {
				bBack.setClickable(true);
				bBack.setEnabled(true);
                bBack.setAlpha(1f);
		    } else {
		    	bBack.setClickable(false);
		    	bBack.setEnabled(false);
                bBack.setAlpha(0.5f);
		    }
		    if (webView.canGoForward()) {
		    	bForward.setClickable(true);
		    	bForward.setEnabled(true);
                bForward.setAlpha(1f);
		    } else {
		    	bForward.setClickable(false);
		    	bForward.setEnabled(false);
                bForward.setAlpha(0.5f);
		    }
		} else {
			bBack.setClickable(false);
		    bBack.setEnabled(false);
		    bForward.setClickable(false);
		    bForward.setEnabled(false);
            bBack.setAlpha(0.5f);
            bForward.setAlpha(0.5f);
		}
    }
		
}