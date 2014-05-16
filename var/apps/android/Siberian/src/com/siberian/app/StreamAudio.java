package com.siberian.app;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.ProtocolException;
import java.net.URL;
import java.net.URLConnection;

import com.siberian.app.R;

import android.app.Activity;
import android.app.ProgressDialog;
import android.graphics.drawable.AnimationDrawable;
import android.media.AudioManager;
import android.media.MediaPlayer;
import android.media.MediaPlayer.OnBufferingUpdateListener;
import android.media.MediaPlayer.OnErrorListener;
import android.media.MediaPlayer.OnInfoListener;
import android.media.MediaPlayer.OnPreparedListener;
import android.os.Bundle;
import android.os.Handler;
import android.os.Message;
import android.os.StrictMode;
import android.util.Log;
import android.widget.CompoundButton;
import android.widget.CompoundButton.OnCheckedChangeListener;
import android.widget.ImageView;
import android.widget.Toast;
import android.widget.ToggleButton;

public class StreamAudio extends Activity implements OnPreparedListener, OnErrorListener {
	
	MediaPlayer mp;
    private ToggleButton btn;
    private ImageView img;
    private boolean flag = false;

    ProgressDialog progress;

    String url = "";
    
    @Override
    public void onCreate(Bundle icicle) {
        super.onCreate(icicle);
        setContentView(R.layout.streamaudio);
        
        url = (String) getIntent().getSerializableExtra("url");
        url = url.replace("?webview=1", "");
        btn = (ToggleButton) findViewById(R.id.play);
        btn.setBackgroundResource(R.drawable.play);

        mp = new MediaPlayer();

        progress=ProgressDialog.show(this, null, this.getApplicationContext().getString(R.string.load_message), false, true);

        Runnable r=new Runnable() {

            @Override
            public void run() {
                setPlayBack();
            }
        };
        Thread th=new Thread(r);
        th.start();
            mp.setOnPreparedListener(this);

            mp.setOnErrorListener(this);

            btn.setOnCheckedChangeListener(new OnCheckedChangeListener() {

                @Override
                public void onCheckedChanged(CompoundButton buttonView, boolean isChecked) {

                    if(flag) {
                        if(!isChecked) {
                            btn.setBackgroundResource(R.drawable.pause);
                            mp.start();
                        } else {
		                    btn.setBackgroundResource(R.drawable.play);
                            mp.stop();
                            mp.reset();
                            flag=false;
                        }
                    } else {
                    	
                        btn.setChecked(false);

                        progress=ProgressDialog.show(StreamAudio.this, null ,StreamAudio.this.getApplicationContext().getString(R.string.load_message),false,false);
                        Runnable r=new Runnable() {

                            @Override
                            public void run() {
                                setPlayBack();
                            }
                        };
                        Thread th=new Thread(r);
                        th.start();
                    }

                }
            });
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        mp.release();
    }

    @Override
    public void onPrepared(MediaPlayer mp) {
        flag = true;
        handler.sendEmptyMessage(0);
    }

    @Override
    public boolean onError(MediaPlayer mp, int what, int extra) {
    	progress.dismiss();
    	Toast.makeText(getApplicationContext(), "Error while loading stream",  Toast.LENGTH_LONG).show();
        mp.release();
        return false;
    }
    private void setPlayBack()
    {
        mp.setAudioStreamType(AudioManager.STREAM_MUSIC);
            try {
            	Log.i("streaming url", url);
                mp.setDataSource(url);
            } catch (IllegalArgumentException e1) {
                e1.printStackTrace();
            } catch (IllegalStateException e1) {
                e1.printStackTrace();
            } catch (IOException e1) {
                e1.printStackTrace();
            }
            mp.prepareAsync();
    }
    private Handler handler=new Handler(){
        @Override
        public void handleMessage(Message msg) {
            progress.dismiss();
            btn.setBackgroundResource(R.drawable.pause);
            mp.start();
        }
    };
}
