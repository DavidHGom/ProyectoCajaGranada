package org.hopto.fundacioncgr.fundacioncgr;

import android.app.Activity;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.util.Log;
import android.view.View;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.Button;
import android.widget.Toast;

import com.google.android.gms.appindexing.Action;
import com.google.android.gms.appindexing.AppIndex;
import com.google.android.gms.appindexing.Thing;
import com.google.android.gms.common.api.GoogleApiClient;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.zxing.integration.android.IntentIntegrator;
import com.google.zxing.integration.android.IntentResult;


import org.hopto.fundacioncgr.fundacioncgr.ConexionServidor.Conexion;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Recorrido;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Sala;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import org.json.JSONTokener;

import java.io.File;
import java.util.ArrayList;
import java.util.Comparator;
import java.util.concurrent.ExecutionException;

/**
 * Clase para lanzar la actividad principal que se muestra al iniciar la aplicación.
 */
public class PantallaPrincipal extends AppCompatActivity {

    private Intent idiomas;
    private Intent accesibilidad;
    private Button button_idiomas;
    private Button button_accesibilidad;
    private Button button_recorridos;
    private Button button_salas;
    private Button button_codigo;
    private Intent actividad;
    private Intent ayuda;
    private Intent codigo;
    /**
     * ATTENTION: This was auto-generated to implement the App Indexing API.
     * See https://g.co/AppIndexing/AndroidStudio for more information.
     */
    private GoogleApiClient client;


    public PantallaPrincipal() {
    }

    /**
     * Método para crear la actividad y definir sus elementos
     * @param savedInstanceState Parámetro con el estado de la aplicación (null si no hay datos)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_pantalla_principal);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);



        idiomas = new Intent(PantallaPrincipal.this, Idioma.class);
        button_idiomas = (Button) findViewById(R.id.btIdiomas);
        button_idiomas.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                startActivity(idiomas);
            }
        });

        codigo = new Intent(PantallaPrincipal.this, CodigoNumerico.class);
        button_codigo = (Button) findViewById(R.id.btTeclado);
        button_codigo.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                startActivity(codigo);
            }
        });

        accesibilidad = new Intent(PantallaPrincipal.this, Accesibilidad.class);
        button_accesibilidad = (Button) findViewById(R.id.btAccesibilidad);
        button_accesibilidad.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                startActivity(accesibilidad);
            }
        });

        ayuda = new Intent(PantallaPrincipal.this, Ayuda.class);
        FloatingActionButton fab = (FloatingActionButton) findViewById(R.id.fab);
        fab.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                startActivity(ayuda);
            }
        });


        Button b = (Button) this.findViewById(R.id.btQR);
        final Activity a = this;

        b.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                IntentIntegrator integrator = new IntentIntegrator(a);
                integrator.setBarcodeImageEnabled(true);
                integrator.setBeepEnabled(true);
                integrator.setOrientationLocked(false);
                integrator.initiateScan(IntentIntegrator.QR_CODE_TYPES);
            }
        });

        button_recorridos = (Button) findViewById(R.id.btRecorridos);
        button_recorridos.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                actividad = LanzarContenido.lanzarRecorridos(a);
                startActivity(actividad);
            }
        });


        button_salas = (Button) findViewById(R.id.btSalas);
        button_salas.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Conexion connect = new Conexion(a);
                Intent act = new Intent(PantallaPrincipal.this, ListaSalas.class);

                ArrayList<Sala> salas = new ArrayList<Sala>();
                try {
                    //JSONObject obj = null;
                    //obj = objectToJSONObject(connect2.execute(Conexion.SALAS).get());
                    JSONArray lista = new JSONArray(connect.execute(Conexion.SALAS).get().toString());
                    //Log.v("tamano lista",obj.length()+"");

                    //JSONArray lista = obj.getJSONArray("content");


                    for (int i = 0; i < lista.length(); i++) {
                        JSONObject fila = lista.getJSONObject(i);
                        Sala sala = new Sala(fila.getInt("#sala"),
                                fila.getString("n_sala"),
                                fila.getString("descripcion_sala"),
                                fila.getInt("planta"));
                                salas.add(sala);
                    }

                } catch (InterruptedException e) {
                    e.printStackTrace();
                    Log.e("Interrupcion excepcion",e.toString());
                } catch (ExecutionException e) {
                    e.printStackTrace();
                    Log.e("Excepcion ejecucion",e.toString());
                } catch (JSONException e) {
                    e.printStackTrace();
                    Log.e("Error json",e.toString());
                }

                act.putExtra("salas",salas);
                startActivity(act);

            }
        });


        // ATTENTION: This was auto-generated to implement the App Indexing API.
        // See https://g.co/AppIndexing/AndroidStudio for more information.
        client = new GoogleApiClient.Builder(this).addApi(AppIndex.API).build();
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
                LanzarContenido.mostrarContenido(this, codigo);
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
        getMenuInflater().inflate(R.menu.menu_pantalla_principal, menu);
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
        if (id == R.id.legal) {
            return true;
        } else if (id == R.id.sugerencias) {
            Intent intent = new Intent(PantallaPrincipal.this, Sugerencias.class);
            startActivity(intent);
            return true;
        }

        return super.onOptionsItemSelected(item);
    }




    //TODO
    /**
     * ATTENTION: This was auto-generated to implement the App Indexing API.
     * See https://g.co/AppIndexing/AndroidStudio for more information.
     */
    public Action getIndexApiAction() {
        Thing object = new Thing.Builder()
                .setName("PantallaPrincipal Page") // TODO: Define a title for the content shown.
                // TODO: Make sure this auto-generated URL is correct.
                .setUrl(Uri.parse("http://[ENTER-YOUR-URL-HERE]"))
                .build();
        return new Action.Builder(Action.TYPE_VIEW)
                .setObject(object)
                .setActionStatus(Action.STATUS_TYPE_COMPLETED)
                .build();
    }

    //TODO
    @Override
    public void onStart() {
        super.onStart();

        // ATTENTION: This was auto-generated to implement the App Indexing API.
        // See https://g.co/AppIndexing/AndroidStudio for more information.
        client.connect();
        AppIndex.AppIndexApi.start(client, getIndexApiAction());
    }

    //TODO
    @Override
    public void onStop() {
        super.onStop();

        // ATTENTION: This was auto-generated to implement the App Indexing API.
        // See https://g.co/AppIndexing/AndroidStudio for more information.
        AppIndex.AppIndexApi.end(client, getIndexApiAction());
        client.disconnect();
    }


    /**
     * Método para indicar a que actividad debe pasar al pulsar el botón de atrás
     */
    @Override
    public void onBackPressed() {
        Intent intent = new Intent(Intent.ACTION_MAIN);
        intent.addCategory(Intent.CATEGORY_HOME);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        startActivity(intent);
    }
}
