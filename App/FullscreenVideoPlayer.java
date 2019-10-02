package org.hopto.fundacioncgr.fundacioncgr;

import android.app.Activity;
import android.content.pm.ActivityInfo;
import android.graphics.Color;
import android.media.MediaPlayer;
import android.media.TimedText;
import android.net.Uri;

import android.os.Bundle;
import android.os.Handler;
import android.text.Html;
import android.text.Spannable;
import android.text.SpannableStringBuilder;
import android.text.style.BackgroundColorSpan;
import android.util.Log;
import android.view.Window;
import android.view.WindowManager;
import android.webkit.MimeTypeMap;
import android.widget.MediaController;
import android.widget.TextView;
import android.widget.Toast;
import android.widget.VideoView;

import org.hopto.fundacioncgr.fundacioncgr.sqlite.SQLWrapper;

import java.io.File;
import java.io.FileDescriptor;
import java.io.IOException;
import java.util.Locale;

import static org.hopto.fundacioncgr.fundacioncgr.R.id.txtDisplay;

/**
 * Clase para mostrar un reproductor de video a pantalla completa.
 */
public class FullscreenVideoPlayer extends Activity implements MediaPlayer.OnTimedTextListener {

    private TextView txtDisplay;
    private static Handler handler = new Handler();
    private VideoView player;
    private String url;
    private SQLWrapper sql;


    /**
     * Método que se usa al crear la actividad, se encarga de poner el reproductor a pantalla completa,
     * establecer los subtitulos si la opción está activa y recuperar la reproducción en un momento anterior.
     * @param savedInstanceState Parámetro con el estado de la aplicación (null si no hay datos)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        this.setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE);
        this.requestWindowFeature(Window.FEATURE_NO_TITLE);
        this.getWindow().setFlags(
                WindowManager.LayoutParams.FLAG_FULLSCREEN,
                WindowManager.LayoutParams.FLAG_FULLSCREEN);
        setContentView(R.layout.activity_fullscreen_video_player);

        txtDisplay = (TextView) findViewById(R.id.txtDisplay);

        url = this.getIntent().getStringExtra("info");
        //url = "http://fundacioncgr.hopto.org/contenido/video/extra.mp4";

        sql = new SQLWrapper(this);
        final int timestamp = sql.getTimestamp(url);


        player = (VideoView) findViewById(R.id.videoViewFull);
        player.setVideoURI(Uri.parse(url));

        final FullscreenVideoPlayer a = this;
        final String sub = ((App)this.getApplicationContext()).getPref();

        player.setOnPreparedListener(new MediaPlayer.OnPreparedListener() {

            @Override
            public void onPrepared(MediaPlayer mp) {
                try {
                    //Uri uri = Uri.parse(url_sub);
                    //String scheme = uri.getScheme();
                    if(sub.equals("sub")) {
                        File srt = new File(a.getCacheDir(), "tmp.srt");
                        mp.addTimedTextSource(srt.getAbsolutePath(), MediaPlayer.MEDIA_MIMETYPE_TEXT_SUBRIP);

                        int textTrackIndex = findTrackIndexFor(
                                MediaPlayer.TrackInfo.MEDIA_TRACK_TYPE_TIMEDTEXT, mp.getTrackInfo());
                        mp.selectTrack(textTrackIndex);
                        mp.setOnTimedTextListener(a);
                    }
                    mp.seekTo(timestamp);

                } catch (IOException e) {
                    e.printStackTrace();
                }
            }
        });



        player.setMediaController(new MediaController(this));
        player.requestFocus();
        player.start();
    }

    /**
     * Método para obtener el indice de un tipo de pista concreta de un array de información de pistas.
     * @param mediaTrackType Tipo que queremos encontrar en el array.
     * @param trackInfo array con todas las pistas donde queremos buscar.
     * @return indice dentro del array donde se encuentra la pista concreta.
     */
    private int findTrackIndexFor(int mediaTrackType, MediaPlayer.TrackInfo[] trackInfo) {
        int index = -1;
        for (int i = 0; i < trackInfo.length; i++) {
            if (trackInfo[i].getTrackType() == mediaTrackType) {
                return i;
            }
        }
        return index;
    }

    /**
     * Método para indicar a que actividad debe pasar al pulsar el botón de atrás.
     * Además guarda el tiempo de reproducción en el que se ha quedado el contenido.
     */
    @Override
    public void onBackPressed() {

        boolean estado = sql.setTimestamp(url, player.getCurrentPosition());
        if(!estado)
            Log.e("Error insertar bd", estado+"");
        super.onBackPressed();
    }


    /**
     * Método que se ejecuta cada vez que cambian los subtitulos dentro del reproductor.
     * Se encarga de actualizar el Textview que muestra los subtitulos dentro del reproductor.
     * @param mp reproductor multimedia que ha despertado el evento.
     * @param text texto de los subtitulos en ese instante de tiempo.
     */
    @Override
    public void onTimedText(final MediaPlayer mp, final TimedText text) {
        if (text != null) {
            handler.post(new Runnable() {
                @Override
                public void run() {
                    String str = text.getText();
                    if(str != null) {
                        SpannableStringBuilder style = new SpannableStringBuilder(str);
                        style.setSpan(new BackgroundColorSpan(Color.parseColor("#bf000000")), 0, str.length(), Spannable.SPAN_EXCLUSIVE_EXCLUSIVE);
                        txtDisplay.setText(style);
                    } else {
                        txtDisplay.setText(str);
                    }
                }
            });
        }
    }
}
