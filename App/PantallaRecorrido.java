package org.hopto.fundacioncgr.fundacioncgr;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.View;
import android.widget.Button;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;

import com.google.gson.JsonArray;
import com.google.gson.JsonObject;

import org.hopto.fundacioncgr.fundacioncgr.Adaptador.AdaptadorUnRecorrido;
import org.hopto.fundacioncgr.fundacioncgr.ConexionServidor.Conexion;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Elemento;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Recorrido;
import org.hopto.fundacioncgr.fundacioncgr.sqlite.SQLWrapper;

import java.util.ArrayList;
import java.util.concurrent.ExecutionException;

/**
 * Clase para lanzar la actividad que los recursos de un recorrido.
 */
public class PantallaRecorrido extends AppCompatActivity {

    private Intent ayuda;

    /**
     * Método para crear la actividad y definir sus elementos
     * @param savedInstanceState Parámetro con el estado de la aplicación (null si no hay datos)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_pantalla_recorrido);


        final ArrayList<Elemento> elementos = (ArrayList<Elemento>)getIntent().getSerializableExtra("elementosRecorrido");
        final String titulo = getIntent().getExtras().getString("titulo");
        final String codigo_guia = getIntent().getExtras().getString("codigo");
        boolean compartir = getIntent().getExtras().getBoolean("compartir");
        AdaptadorUnRecorrido adaptadorUnRecorrido = new AdaptadorUnRecorrido(this,elementos);
        ListView listaElementos = (ListView)findViewById(R.id.lvPuntosRecorrido);
        listaElementos.setAdapter(adaptadorUnRecorrido);
        TextView titRecorrido = (TextView)findViewById(R.id.tvTituloDelRecorrido);
        titRecorrido.setText(titulo);
        final Activity a = this;


        //compartir un recorrido (subirlo al servidor)
        Button comparto = (Button)findViewById(R.id.btCompartir);
        if(compartir) {
            comparto.setVisibility(View.VISIBLE);
            comparto.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    JsonObject obj = new JsonObject();
                    obj.addProperty("titulo", titulo);
                    JsonArray array = new JsonArray();

                    for (Elemento e : elementos) {
                        array.add(e.getId());
                    }

                    obj.add("recursos", array);
                    String json = obj.toString();

                    Conexion conexion = new Conexion(a);
                    conexion.execute(Conexion.COMPARTIR, json);

                    try {
                        String codigo = (String)conexion.get();
                        SQLWrapper sql = new SQLWrapper(a);
                        sql.setGuiaPersonalizada(codigo);
                        sql.removeGuiaPersonalizadaLocal(codigo_guia);

                        Toast.makeText(a, a.getResources().getString(R.string.toast_local_tour) + ": " + codigo, Toast.LENGTH_LONG).show();
                        Intent activity = LanzarContenido.lanzarRecorridos(a);
                        startActivity(activity);
                    } catch (InterruptedException e) {
                        e.printStackTrace();
                    } catch (ExecutionException e) {
                        e.printStackTrace();
                    }

                }
            });
        }


        ayuda = new Intent(PantallaRecorrido.this, Ayuda.class);
        FloatingActionButton fab = (FloatingActionButton) findViewById(R.id.fab);
        fab.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                startActivity(ayuda);
            }
        });


    }

}
