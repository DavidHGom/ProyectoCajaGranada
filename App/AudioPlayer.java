package org.hopto.fundacioncgr.fundacioncgr;

import android.app.Activity;
import android.content.res.Configuration;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.graphics.drawable.BitmapDrawable;
import android.media.MediaPlayer;
import android.net.Uri;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.KeyEvent;
import android.widget.MediaController;
import android.widget.VideoView;

import org.hopto.fundacioncgr.fundacioncgr.sqlite.SQLWrapper;

public class AudioPlayer extends AppCompatActivity {

    private SQLWrapper sql;
    private String url;

    /**
     * Método para crear la actividad y definir sus elementos como que se muestre siempre los botones
     * para pausar, etc. También se establece el fondo del reproductor con un imagen.
     * @param savedInstanceState Parámetro con el estado de la aplicación (null si no hay datos)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_audio_player);

        Bitmap img = BitmapFactory.decodeResource(getApplicationContext().getResources(),
                R.drawable.sound_on);
        BitmapDrawable background = new BitmapDrawable(getApplicationContext().getResources(), img);

        url = this.getIntent().getStringExtra("info");
        sql = new SQLWrapper(this);
        final int timestamp = sql.getTimestamp(url);

        final VideoView player = (VideoView) findViewById(R.id.audioView);
        player.setVideoURI(Uri.parse(url));

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setDisplayShowHomeEnabled(true);

        // media controller siempre visible
        final MediaController controller = new MediaController(this) {
            @Override
            public void hide() {
                this.show(0);
            }

            @Override
            public void setMediaPlayer(MediaPlayerControl player) {
                super.setMediaPlayer(player);
                this.show(0);
            }

            @Override
            public boolean dispatchKeyEvent(KeyEvent event) {

                if(event.getKeyCode() == KeyEvent.KEYCODE_BACK) {
                    boolean estado = sql.setTimestamp(url, player.getCurrentPosition());
                    super.hide();
                    Activity a = (Activity)getContext();
                    a.finish();

                }
                return true;
            }
        };
        player.setMediaController(controller);
        player.setBackground(background);
        controller.requestFocus();
        player.setOnPreparedListener(new MediaPlayer.OnPreparedListener() {

            @Override
            public void onPrepared(MediaPlayer mp) {
                player.seekTo(timestamp);
                player.start();
                controller.show(0);
                player.requestFocus();
            }
        });

    }

    /**
     * Método para menejar un cambio en la configuración
     * @param newConfig Parámetro que representa todas las configuraciones actuales.
     */
    @Override
    public void onConfigurationChanged(Configuration newConfig) {
        super.onConfigurationChanged(newConfig);
    }
}
