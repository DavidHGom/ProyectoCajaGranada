package org.hopto.fundacioncgr.fundacioncgr;

import android.app.Activity;
import android.content.Intent;
import android.support.design.widget.FloatingActionButton;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ListView;
import android.widget.Spinner;
import android.widget.Toast;

import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;

import org.hopto.fundacioncgr.fundacioncgr.Adaptador.AdaptadorRecorridos;
import org.hopto.fundacioncgr.fundacioncgr.ConexionServidor.Conexion;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Elemento;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Recorrido;
import org.hopto.fundacioncgr.fundacioncgr.sqlite.SQLWrapper;

import java.lang.reflect.Array;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.concurrent.ExecutionException;


/**
 * Clase para lanzar la actividad que muestra la lista de recorridos.
 */
public class ListaRecorridos extends AppCompatActivity {

    private ListView listaRecorridos;
    private Spinner spinPreferencias;
    private ArrayList<Recorrido> recorridos;
    private final static String[] preferencias = {"Ascendente", "Descendente"};
    private Button anadir;
    private Intent actividad;
    private Intent ayuda;
    private Activity a;

    /**
     * Método para crear la actividad y definir sus elementos
     *
     * @param savedInstanceState Parámetro con el estado de la aplicación (null si no hay datos)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_lista_recorridos);

        anadir = (Button) findViewById(R.id.btAnadir);
        spinPreferencias = (Spinner) findViewById(R.id.spin_preferencias);
        loadSpinnerPreferencias();
        //final ArrayList<Recorrido> recorridos
        recorridos = (ArrayList<Recorrido>) getIntent().getSerializableExtra("guias");
        //comprobar base de datos local y añadir al final en caso de haber nuevas guias
        SQLWrapper sql = new SQLWrapper(this);
        recorridos.addAll(recorridos.size()-1,sql.getGuiasLocales());

        final AdaptadorRecorridos adaptadorRecorridos = new AdaptadorRecorridos(this, recorridos);
        listaRecorridos = (ListView) findViewById(R.id.lvRecorridos);
        listaRecorridos.setAdapter(adaptadorRecorridos);


        spinPreferencias.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {

                switch (position) {
                    case 0:
                        Collections.sort(recorridos, new Comparator<Recorrido>() {
                            @Override
                            public int compare(Recorrido o1, Recorrido o2) {
                                return o1.getNombreGuia().compareTo(o2.getNombreGuia());
                            }
                        });
                        adaptadorRecorridos.notifyDataSetChanged();
                        break;
                    case 1:
                        Collections.sort(recorridos, new Comparator<Recorrido>() {
                            @Override
                            public int compare(Recorrido o1, Recorrido o2) {
                                return o2.getNombreGuia().compareTo(o1.getNombreGuia());
                            }
                        });
                        adaptadorRecorridos.notifyDataSetChanged();
                        break;

                }
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {

            }
        });


        //añadir una nueva ruta a las ya existentes
        a = this;
        actividad = new Intent(ListaRecorridos.this, ListaContenidos.class);
        final ArrayList<Elemento> contenidos = new ArrayList<Elemento>();
        anadir.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {


                try {
                    Conexion connect = new Conexion((Activity) a);
                    JsonArray lista = (JsonArray) connect.execute(Conexion.CONTENIDOS).get();

                    for (JsonElement e : lista) {
                        JsonObject o = e.getAsJsonObject();
                        Elemento elemento = new Elemento(o.get("#recurso").getAsString(),
                                o.get("codigo_qr").getAsString(),
                                o.get("nombre").getAsString());

                        contenidos.add(elemento);
                    }
                } catch (InterruptedException e) {
                    e.printStackTrace();
                } catch (ExecutionException e) {
                    e.printStackTrace();
                }

                actividad.putExtra("contenidos", contenidos);

                startActivity(actividad);

            }
        });
/*
        ayuda = new Intent(ListaRecorridos.this, Ayuda.class);
        FloatingActionButton fab = (FloatingActionButton) findViewById(R.id.fab);
        fab.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                startActivity(ayuda);
            }
        });

*/
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
     * Método para cargar la lista de recorridos.
     */
    private void loadSpinnerPreferencias() {

        // Create an ArrayAdapter using the string array and a default spinner
        // layout
        ArrayAdapter adapter = new ArrayAdapter<String>(this, R.layout.support_simple_spinner_dropdown_item, preferencias);
        spinPreferencias.setAdapter(adapter);

        // This activity implements the AdapterView.OnItemSelectedListener
        //     this.spinPreferencias.setOnItemSelectedListener(this);
        //this.spinPreferencias.setOnItemSelectedListener(this);

    }

}