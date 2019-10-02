package org.hopto.fundacioncgr.fundacioncgr;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.RadioButton;
import android.widget.Toast;

/**
 * Clase de la actividad de Accesibilidad.
 */
public class Accesibilidad extends AppCompatActivity {


    private RadioButton video;
    private RadioButton subtitulos;
    private RadioButton signos;
    private RadioButton audio;
    private RadioButton text;
    private Intent ayuda;


    private Button save;

    /**
     * Método para crear la actividad y definir sus elementos
     * @param savedInstanceState Parámetro con el estado de la aplicación (null si no hay datos)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_accesibilidad);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        text = (RadioButton) findViewById(R.id.radioButton_text) ;
        video = (RadioButton) findViewById(R.id.radioButton_vid) ;
        subtitulos = (RadioButton) findViewById(R.id.radioButton_vid_sub) ;
        signos = (RadioButton) findViewById(R.id.radioButton_sign) ;
        audio = (RadioButton) findViewById(R.id.radioButton_audio) ;

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setDisplayShowHomeEnabled(true);


        save = (Button) findViewById(R.id.buttonGuardarPreferencias) ;

        save.setOnClickListener(new View.OnClickListener(){
            @Override
            public void onClick(View v){

                GuardarPreferencias();

                Snackbar.make(v, getResources().getString(R.string.snackbar_save), Snackbar.LENGTH_LONG)
                        .setAction("Action", null).show();

            }
        });

        CargarPreferencias();

        ayuda = new Intent(Accesibilidad.this, Ayuda.class);
        FloatingActionButton fab = (FloatingActionButton) findViewById(R.id.fab);
        fab.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                startActivity(ayuda);
            }
        });
    }

    /**
     * Método para cargar las preferencias(idioma y accesibilidad) guardadas en un archivo de configuración
     */
    public void CargarPreferencias(){
        String p = ((App)(getApplicationContext())).getPref();

        boolean is_sub = p.contains("sub");
        boolean is_audio = p.contains("audio");
        boolean is_sign = p.contains("sign");
        boolean is_text = p.contains("text");

        boolean is_video = !(is_sub || is_audio || is_sign || is_text);

        subtitulos.setChecked(is_sub);
        video.setChecked(is_video);
        signos.setChecked(is_sign);
        audio.setChecked(is_audio);
        text.setChecked(is_text);
    }

    /**
     * Método para guardar las preferencias(idioma y accesibilidad) en un archivo de configuración
     */
    public void GuardarPreferencias(){
        boolean is_texto = text.isChecked();
        boolean is_sub = subtitulos.isChecked();
        boolean is_audio = audio.isChecked();
        boolean is_sign = signos.isChecked();
        boolean is_video = video.isChecked();

        String pref = "texto";
        if(is_sub){
            pref = "sub";
        }else if(is_audio){
            pref = "audio";
        }else if(is_sign){
            pref = "sign";
        }else if(is_video){
            pref = "video";
        }

        ((App)getApplicationContext()).changePref(pref);
        Intent refrescar = new Intent(this, Accesibilidad.class);
        startActivity(refrescar);
    }


    /**
     * Método para indicar a que actividad debe pasar al pulsar el botón de atrás
     */
    @Override
    public void onBackPressed() {
        Intent refrescar = new Intent(this, PantallaPrincipal.class);
        startActivity(refrescar);
    }

    /**
     * Método para crear el menú superior de la actividad
     * @param menu Parametrp con el menú correspondiente
     * @return True tras su finalización para que aparezca el menú. Si devuelviera false no se mostrará.
     */
    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_main, menu);
        menu.removeItem(R.id.m_accesibilidad);
        return true;
    }

    /**
     * Método que actúa en consecuencia a pulsar un item del menú superior
     * @param item Parámetro con el item que se ha pulsado
     * @return Devuelve false para permitir que el procesamiento normal del menú continúe, true para que se ejecute es ese instante.
     */
    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.principal) {
            Intent intent = new Intent(Accesibilidad.this, PantallaPrincipal.class);
            startActivity(intent);
            return true;
        }else if (id == R.id.m_idiomas) {
            Intent intent = new Intent(Accesibilidad.this, Idioma.class);
            startActivity(intent);
            return true;
        }else if (id == R.id.m_accesibilidad) {
            Intent intent = new Intent(Accesibilidad.this, Accesibilidad.class);
            startActivity(intent);
            return true;
        }else if (id == android.R.id.home) {
            Intent intent = new Intent(Accesibilidad.this, PantallaPrincipal.class);
            startActivity(intent);
            return true;
        }

        return super.onOptionsItemSelected(item);
    }




}
