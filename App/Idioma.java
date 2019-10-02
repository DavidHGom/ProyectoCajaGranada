package org.hopto.fundacioncgr.fundacioncgr;

import android.animation.Animator;
import android.animation.AnimatorListenerAdapter;
import android.annotation.TargetApi;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.res.Configuration;
import android.content.res.Resources;
import android.os.Build;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.util.DisplayMetrics;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.RadioButton;
import android.widget.Toast;

import java.util.Locale;

/**
 * Clase para permitir al usuario cambiar el idioma de la aplicación y en el cual se obtienen los contenidos.
 */
public class Idioma extends AppCompatActivity {

    private RadioButton es;
    private RadioButton en;
    private RadioButton fr;
    private Intent ayuda;
    private Button confirmarIdioma;
    private View mProgressView;

    /**
     * Método para crear la actividad y definir sus elementos
     * @param savedInstanceState Parámetro con el estado de la aplicación (null si no hay datos)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_idioma);
        es = (RadioButton) findViewById(R.id.radioButton_es);
        en = (RadioButton) findViewById(R.id.radioButton_en);
        fr = (RadioButton) findViewById(R.id.radioButton_fr);
        CargarPreferencias();
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setDisplayShowHomeEnabled(true);


        ayuda = new Intent(Idioma.this, Ayuda.class);
        FloatingActionButton fab = (FloatingActionButton) findViewById(R.id.fab);
        fab.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                startActivity(ayuda);
            }
        });

        mProgressView = findViewById(R.id.progress);

        confirmarIdioma = (Button) findViewById(R.id.buttonConfirmarIdioma);
        confirmarIdioma.setOnClickListener(new View.OnClickListener(){
            @Override
            public void onClick(View v){
                mProgressView.setVisibility(View.VISIBLE );
                GuardarPreferencias();

            }
        });
    }

    /**
     * Método para cargar las preferencias del archivo de configuración en el menú de selección
     */
    public void CargarPreferencias(){
        String l = ((App)(getApplicationContext())).getLang();

        boolean is_es = l.contains("es");
        boolean is_fr = l.contains("fr");
        boolean is_en = !(is_es || is_fr);

        en.setChecked(is_en);
        es.setChecked(is_es);
        fr.setChecked(is_fr);
    }


    /**
     * Método para guardar las preferencias de idioma seleccionadas en el archivo de configuracion
     */
    public void GuardarPreferencias(){

        boolean is_es = es.isChecked();
        boolean is_en = en.isChecked();
        boolean is_fr = fr.isChecked();
        String language_code = "en";
        if(is_es){
            language_code = "es";
        }else if(is_en){
            language_code = "en";
        }else if(is_fr){
            language_code = "fr";
        }

        ((App)getApplicationContext()).changeLang(language_code);
        Intent refrescar = new Intent(this, Idioma.class);
        startActivity(refrescar);
        Toast.makeText(this, getString(R.string.datos_guardados), Toast.LENGTH_LONG).show();

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
        menu.removeItem(R.id.m_idiomas);
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
            Intent intent = new Intent(Idioma.this, PantallaPrincipal.class);
            startActivity(intent);
            return true;
        }else if (id == R.id.m_idiomas) {
            Intent intent = new Intent(Idioma.this, Idioma.class);
            startActivity(intent);
            return true;
        }else if (id == R.id.m_accesibilidad) {
            Intent intent = new Intent(Idioma.this, Accesibilidad.class);
            startActivity(intent);
            return true;
        }else if (id == android.R.id.home) {
            Intent intent = new Intent(Idioma.this, PantallaPrincipal.class);
            startActivity(intent);
            return true;
        }

        return super.onOptionsItemSelected(item);
    }




}
