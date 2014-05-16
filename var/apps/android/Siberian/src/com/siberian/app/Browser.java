package com.siberian.app;

import android.app.Activity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ImageButton;

public class Browser extends Activity {
	private ImageButton bBack;
	private ImageButton bForward;
	private ImageButton bReload;
    private WebView mWebview;
    private boolean mIsLoadFinish = false;
    
    protected void onCreate(Bundle savedInstanceState) {
    	super.onCreate(savedInstanceState);
		setContentView(R.layout.browser);
		
		mWebview = (WebView) findViewById(R.id.bv);
		mWebview.getSettings().setSupportZoom(true);
		mWebview.getSettings().setBuiltInZoomControls(true);
		if(Main.isAtLeast11) {
			mWebview.getSettings().setDisplayZoomControls(false);
    	}
		
		mWebview.getSettings().setUseWideViewPort(true);
		mWebview.getSettings().setJavaScriptEnabled(true);
		mWebview.getSettings().setUseWideViewPort(true);
		mWebview.getSettings().setJavaScriptCanOpenWindowsAutomatically(true);
		mWebview.setScrollBarStyle(WebView.SCROLLBARS_INSIDE_OVERLAY);
		
		bBack = ((ImageButton) findViewById(R.id.bBack));
		bForward = ((ImageButton) findViewById(R.id.bForward));
		bReload = ((ImageButton) findViewById(R.id.bReload));
		
    	String url = (String) getIntent().getSerializableExtra("url");
    	
    	// Setup for button controller
    	bReload.setOnClickListener(new ImageButton.OnClickListener() {
    	    @Override
    	    public void onClick(View v) {
    	    	mWebview.reload();
    	    	enableControllerButton();
    	    }
    	});
    	bBack.setOnClickListener(new ImageButton.OnClickListener() {
    	    @Override
    	    public void onClick(View v) {
    	    	mWebview.goBack();
    	    	enableControllerButton();
    	    }
    	});
    	bForward.setOnClickListener(new ImageButton.OnClickListener() {
    	    @Override
    	    public void onClick(View v) {
	    		mWebview.goForward();
	    		enableControllerButton();
    	    }
    	});
    	
    	mWebview.setWebViewClient(new WebViewClient() {
    		@Override
            public boolean shouldOverrideUrlLoading(WebView view, String url) {
    			Log.i("url : ", url);
    			if(url.contains("close/1")) {
    				finish();
    				return false;
    			} else {
    				view.loadUrl(url);
    			}
                return true;
            }
		    @Override
		    public void onPageStarted(WebView view, String url,
			    android.graphics.Bitmap favicon) {
		    }

		    @Override
		    public void onPageFinished(WebView view, String url) {
				mIsLoadFinish = true;
				enableControllerButton();
				if(url.contains("https://m.facebook.com/dialog/oauth")) {
					Main.reloadFacebook = true;
				}
		    }
    	});
    	
    	mWebview.loadUrl(url);
    }
    
	@Override
 	public void onBackPressed() {
    	finish();
    }
	
	public void enableControllerButton() {
		
		Boolean isAtLeast11 = Main.isAtLeast11;
		
		if (mIsLoadFinish) {
			bReload.setEnabled(true);
			if (mWebview.canGoBack()) {
				bBack.setClickable(true);
				bBack.setEnabled(true);
				if(isAtLeast11) {
					bBack.setAlpha(1f);
				}
		    } else {
		    	bBack.setClickable(false);
		    	bBack.setEnabled(false);
		    	if(isAtLeast11) {
		    		bBack.setAlpha(0.5f);
		    	}
		    }
		    if (mWebview.canGoForward()) {
		    	bForward.setClickable(true);
		    	bForward.setEnabled(true);
		    	if(isAtLeast11) {
		    		bForward.setAlpha(1f);
		    	}
		    } else {
		    	bForward.setClickable(false);
		    	bForward.setEnabled(false);
		    	if(isAtLeast11) {
		    		bForward.setAlpha(0.5f);
		    	}
		    }
		} else {
			bBack.setClickable(false);
		    bBack.setEnabled(false);
		    bForward.setClickable(false);
		    bForward.setEnabled(false);
		    if(isAtLeast11) {
		    	bBack.setAlpha(0.5f);
		    	bForward.setAlpha(0.5f);
		    }
		}
    }
		
}