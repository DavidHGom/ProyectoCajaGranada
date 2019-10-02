package org.hopto.fundacioncgr.fundacioncgr.Adaptador;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.Filterable;

import org.hopto.fundacioncgr.fundacioncgr.ConexionServidor.Conexion;
import org.hopto.fundacioncgr.fundacioncgr.ListaRecorridos;
import org.hopto.fundacioncgr.fundacioncgr.PantallaRecorrido;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Elemento;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Recorrido;
import org.hopto.fundacioncgr.fundacioncgr.R;
import org.hopto.fundacioncgr.fundacioncgr.sqlite.SQLWrapper;

import android.widget.ArrayAdapter;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;

import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ExecutionException;

/**
 * Created by David on 05/12/2016.
 **/

/**
 * Clase para generar los item de la lista de recorridos
 */
public class AdaptadorRecorridos extends ArrayAdapter<Recorrido> implements Filterable{
    private Context c;
    private ArrayList<Recorrido> recorridos;
    private Intent actividad;
    private SQLWrapper sql;

    /**
     * Constructor para crear un item de la lista de guías
     * @param context contexto de la actividad procedente en la que se muestra
     * @param r lista con las guías
     */
    public AdaptadorRecorridos(Context context, ArrayList<Recorrido> r){
        super(context, R.layout.content_recorrido);
        c = context;
        recorridos = r;
        sql = new SQLWrapper(c);
    }

    /**
     * Método para obtener el tamaño de la lista de guias
     * @return cantidad de elementos
    */
    @Override
    public int getCount(){
        return recorridos.size();
    }

    /**
     * Método que devuelve un elemento de la lista de guias
     * @param pos posicion del elemento de la lista
     * @return una guia de a lista
     */
    @Override
    public Recorrido getItem(int pos){
        return recorridos.get(pos);
    }


    /**
     * Método para devolver el código de la guía
     * @param pos posición de la lista en la que se la guía deseada
     * @return el código de la guía
     */
    public String getIdGuia(int pos) {
        return recorridos.get(pos).getNumGuia();
    }

    /**
     * Méotodo para generar la vista del item
     * @param posicion posicion del elemento en la lista
     * @param vista vista de la actividad
     * @param padre la listview a la que pertenece
     * @return la vista generada del item
     */
    @Override
    public View getView(int posicion, View vista, ViewGroup padre) {
        if(vista == null) {
            LayoutInflater i = (LayoutInflater) c.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            vista = i.inflate(R.layout.content_recorrido, padre, false);
        }

        final Recorrido r = recorridos.get(posicion);
        TextView titulo = (TextView)vista.findViewById(R.id.tvTituloRecorrido);
        TextView codigo = (TextView)vista.findViewById(R.id.tvDescripcionRecorrido);
        Button start = (Button)vista.findViewById(R.id.btStart);
        final ImageView imagen = (ImageView)vista.findViewById(R.id.ivRecorrido);

        if(r.getLocal()) {
            imagen.setImageResource(R.mipmap.no_cloud);
        } else {
            imagen.setImageResource(R.mipmap.cloud);
        }

        actividad = new Intent(c, PantallaRecorrido.class);
        final ArrayList<Elemento> elementos = new ArrayList<Elemento>();
        start.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if(r.getLocal()) {
                    elementos.addAll(consultarBaseDatos(r.getNumGuia()));
                } else {
                    elementos.addAll(consultarServidor(r.getNumGuia()));
                }
               //lanzar actividad PantallaRecorrido
                actividad.putExtra("elementosRecorrido",elementos);
                actividad.putExtra("compartir",r.getLocal());
                actividad.putExtra("titulo",r.getNombreGuia());
                actividad.putExtra("codigo",r.getNumGuia());
                c.startActivity(actividad);
            }
        });

        titulo.setText(r.getNombreGuia());
        codigo.setText(c.getResources().getString(R.string.text_code)+ ": " + getIdGuia(posicion));

        return vista;
    }

    /**
     * Método para consultar los elementos de una guía almacenada en el servidor
     * @param code código de la guía
     * @return lista de elementos
     */
    private List<Elemento> consultarServidor(String code){
        ArrayList<Elemento> elementos = new ArrayList<>();
         try {
            Conexion connect = new Conexion((Activity)c);
            JsonArray lista = (JsonArray)connect.execute(Conexion.ELEMENTOS, code).get();

            for (JsonElement e : lista) {
                JsonObject o = e.getAsJsonObject();
                Elemento elemento = new Elemento(o.get("#recurso").getAsString(),
                        o.get("codigo_qr").getAsString(),
                        o.get("nombre").getAsString());

                elementos.add(elemento);
            }
        } catch (InterruptedException e) {
            e.printStackTrace();
        } catch (ExecutionException e) {
            e.printStackTrace();
        }

        return elementos;
    }

    /**
     * Método para consultar los elementos de una guía almacenada en la base de datos local
     * @param code código
     * @return lista de elementos
     */
    private List<Elemento> consultarBaseDatos(String code){
        return sql.getContenidosLocales(code);
    }

}
