package org.hopto.fundacioncgr.fundacioncgr;

import android.content.Intent;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import org.hopto.fundacioncgr.fundacioncgr.ConexionServidor.Conexion;

import java.util.concurrent.ExecutionException;

/**
 * Clase para lanzar la actividad que muestra unos campos de texto para enviar una sugerencia a los administradores del museo.
 */
public class Sugerencias extends AppCompatActivity {

    private Intent ayuda;
    private EditText nombre;
    private EditText mensaje;
    private Button enviar;

    /**
     * Método para crear la actividad y definir sus elementos
     * @param savedInstanceState Parámetro con el estado de la aplicación (null si no hay datos)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_sugerencias);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        nombre = (EditText) findViewById(R.id.edit_name);
        mensaje = (EditText) findViewById(R.id.edit_mensaje);
        enviar = (Button) findViewById(R.id.button_enviar_sugerencia);

        enviar.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                enviarSugerencia();
            }
        });

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setDisplayShowHomeEnabled(true);

        ayuda = new Intent(Sugerencias.this, Ayuda.class);
        FloatingActionButton fab = (FloatingActionButton) findViewById(R.id.fab);
        fab.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                startActivity(ayuda);
            }
        });
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
            Intent intent = new Intent(Sugerencias.this, PantallaPrincipal.class);
            startActivity(intent);
            return true;
        }else if (id == R.id.m_idiomas) {
            Intent intent = new Intent(Sugerencias.this, Idioma.class);
            startActivity(intent);
            return true;
        }else if (id == R.id.m_accesibilidad) {
            Intent intent = new Intent(Sugerencias.this, Accesibilidad.class);
            startActivity(intent);
            return true;
        }else if (id == android.R.id.home) {
            Intent intent = new Intent(Sugerencias.this, PantallaPrincipal.class);
            startActivity(intent);
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    /**
     * Método para enviar la sugerencia a través de la API.
     */
    private void enviarSugerencia(){

        Conexion conexion = new Conexion(this);
        String name = nombre.getText().toString();
        String message = mensaje.getText().toString();

        if(name.isEmpty() || message.isEmpty()){
            Toast.makeText(this, R.string.toast_rellenar_campos, Toast.LENGTH_LONG).show();
        }else{
            conexion.execute(Conexion.SUGERENCIA, name, message);
            Intent cambio = new Intent(Sugerencias.this, PantallaPrincipal.class);
            Toast.makeText(this, R.string.toast_mensaje_enviado, Toast.LENGTH_LONG).show();
            startActivity(cambio);
        }

    }

}
