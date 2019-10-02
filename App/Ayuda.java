package org.hopto.fundacioncgr.fundacioncgr;

import android.app.Activity;
import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import com.google.zxing.integration.android.IntentIntegrator;
import com.google.zxing.integration.android.IntentResult;

import org.hopto.fundacioncgr.fundacioncgr.ConexionServidor.Conexion;

import java.util.concurrent.ExecutionException;

/**
 * Clase de la actividad de Ayuda para pedir una asistencia al personal del museo.
 */
public class Ayuda extends AppCompatActivity {

    Button qr;
    Button enviar;
    EditText input;

    /**
     * Método para crear la actividad y definir sus elementos
     * @param savedInstanceState Parámetro con el estado de la aplicación (null si no hay datos)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_ayuda);

        input = (EditText) findViewById(R.id.input_codigo_ayuda);

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setDisplayShowHomeEnabled(true);

        final Activity a= this;
        qr = (Button) findViewById(R.id.button_ayudaQR);
        enviar = (Button) findViewById(R.id.button_ayuda_send);
        qr.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                IntentIntegrator integrator = new IntentIntegrator(a);
                integrator.setBarcodeImageEnabled(true);
                integrator.setBeepEnabled(true);
                integrator.setOrientationLocked(false);
                integrator.initiateScan(IntentIntegrator.QR_CODE_TYPES);
            }
        });
        enviar.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                enviarPeticion();
            }
        });

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setDisplayShowHomeEnabled(true);
    }

    /**
     * Método que se llama cuando se lanza una actividad que ya se ha iniciado.
     * @param requestCode El código de solicitud originalmente suministrado a startActivityForResult() para identificar de quién proviene este resultado.
     * @param resultCode Resultado devuelto por la actividad secundaria a través de su setResult().
     * @param data Intent que puede devolver datos de resultado al que lo llama
     */
    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        IntentResult result = IntentIntegrator.parseActivityResult(requestCode, resultCode, data);
        if (result != null) {
            if (result.getContents() == null) {
                Toast.makeText(this, R.string.cancelado, Toast.LENGTH_LONG).show();
            } else {
                String codigo = result.getContents();
                input.setText(codigo);
                enviar.requestFocus();
            }
        } else {
            super.onActivityResult(requestCode, resultCode, data);
        }
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
            Intent intent = new Intent(Ayuda.this, PantallaPrincipal.class);
            startActivity(intent);
            return true;
        }else if (id == R.id.m_idiomas) {
            Intent intent = new Intent(Ayuda.this, Idioma.class);
            startActivity(intent);
            return true;
        }else if (id == R.id.m_accesibilidad) {
            Intent intent = new Intent(Ayuda.this, Accesibilidad.class);
            startActivity(intent);
            return true;
        }else if (id == android.R.id.home) {
            Intent intent = new Intent(Ayuda.this, PantallaPrincipal.class);
            startActivity(intent);
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    /**
     * Método que envía la petición de asistencia a través de la API.
     */
    private void enviarPeticion(){
        String c = input.getText().toString();
        if(!c.isEmpty()){
            Conexion conexion = new Conexion(this);
            conexion.execute(Conexion.ASISTENCIA, c);

            try {
                int res = (int)conexion.get();
                Intent cambio = new Intent(Ayuda.this, AsistenciaAceptada.class);
                startActivity(cambio);
            } catch (InterruptedException e) {
                e.printStackTrace();
            } catch (ExecutionException e) {
                e.printStackTrace();
            }
        }else{
            Toast.makeText(this, R.string.toast_introdicir_codigo, Toast.LENGTH_LONG).show();
        }
    }


}
