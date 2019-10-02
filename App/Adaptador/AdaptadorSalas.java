package org.hopto.fundacioncgr.fundacioncgr.Adaptador;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.Filterable;
import android.widget.TextView;

import org.hopto.fundacioncgr.fundacioncgr.Pojo.Sala;
import org.hopto.fundacioncgr.fundacioncgr.R;

import java.util.ArrayList;

/**
 * Created by David on 05/12/2016.
 */

/**
 * Clase que genera la vista de un item de la lista de salas
 */
public class AdaptadorSalas extends ArrayAdapter<Sala> implements Filterable {

    private Context c;
    private ArrayList<Sala> salas;

    /**
     * Método contructor de la clase
     * @param context Contexto de la actividad en la que se muestra
     * @param s lista con las salas
     */
    public AdaptadorSalas(Context context, ArrayList<Sala> s){
        super(context, R.layout.content_sala,s);
        c = context;
        salas = s;
    }

    /**
     * Método para obtener el tamaño de la lista de salas
     * @return cantidad de elementos
     */
    @Override
    public int getCount(){
        return salas.size();
    }

    /**
     *   Método que devuelve un elemento de la lista de salas
    * @param pos posicion del elemento de la lista
    * @return un elemento de la lista
    */
    @Override
    public Sala getItem(int pos){
        return salas.get(pos);
    }

    /**
     * Méotodo para generar la vista del item
     * @param posicion posicion del elemento en la lista
     * @param vista vista de la actividad
     * @param padre la listview a la que pertenece
     * @return la vista generada del item
     */
    @Override
    public View getView(int posicion, View vista, ViewGroup padre){
        LayoutInflater i = (LayoutInflater)c.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        vista = i.inflate(R.layout.content_sala,padre,false);

        //Rellenar lineas
        Sala s =  salas.get(posicion);
        TextView titulo = (TextView)vista.findViewById(R.id.tvTituloRecorrido);
        TextView descripcion = (TextView)vista.findViewById(R.id.tvDescripcionRecorrido);

        titulo.setText(s.getNumSala());
        descripcion.setText(s.getDescripción());

        return vista;
    }

}
