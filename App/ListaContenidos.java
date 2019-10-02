package org.hopto.fundacioncgr.fundacioncgr;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.support.design.widget.FloatingActionButton;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.ListView;
import android.widget.Toast;

import org.hopto.fundacioncgr.fundacioncgr.Adaptador.AdaptadorContenidos;
import org.hopto.fundacioncgr.fundacioncgr.ConexionServidor.Conexion;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Elemento;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Recorrido;
import org.hopto.fundacioncgr.fundacioncgr.sqlite.SQLWrapper;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.concurrent.ExecutionException;

/**
 * Clase para lanzar la actividad que muestra la lista de contenidos.
 */
public class ListaContenidos extends AppCompatActivity{

    private ListView listaContenidos;
    private Intent actividad;
    private Context c;
    private EditText input_titulo;
    private Activity a;

    /**
     * Método para crear la actividad y definir sus elementos
     * @param savedInstanceState Parámetro con el estado de la aplicación (null si no hay datos)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_lista_contenidos);

        ArrayList<Elemento> contenidos = (ArrayList< Elemento>)getIntent().getSerializableExtra("contenidos");
        final AdaptadorContenidos adaptadorContenidos = new AdaptadorContenidos(this, contenidos);
        listaContenidos = (ListView)findViewById(R.id.lvContenidos);
        listaContenidos.setAdapter(adaptadorContenidos);
        c = this;
        input_titulo = (EditText)findViewById(R.id.etNombreGuia);
        actividad = new Intent(ListaContenidos.this, ListaRecorridos.class);

        a = this;
        Button guardar = (Button)findViewById(R.id.btAnadirCont);
        guardar.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {

                String titulo = input_titulo.getText().toString();
                SQLWrapper sentenciassql = new SQLWrapper(c);
                if(!titulo.isEmpty()) {
                    boolean exito = sentenciassql.setGuia(titulo, adaptadorContenidos.getChequed());
                    Toast.makeText(v.getContext(), R.string.toast_tour_saved, Toast.LENGTH_SHORT).show();

                    actividad = LanzarContenido.lanzarRecorridos(a);
                    startActivity(actividad);

                }else
                    Toast.makeText(v.getContext(), R.string.toast_tittle_tour, Toast.LENGTH_SHORT).show();
            }
        });

        Button cancelar  = (Button)findViewById(R.id.btCancelar);
        cancelar.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                actividad = LanzarContenido.lanzarRecorridos(a);
                startActivity(actividad);
            }
        });

    }


}
