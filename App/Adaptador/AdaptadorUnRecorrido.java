package org.hopto.fundacioncgr.fundacioncgr.Adaptador;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.Filterable;
import android.widget.ImageButton;
import android.widget.TextView;

import org.hopto.fundacioncgr.fundacioncgr.LanzarContenido;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Elemento;
import org.hopto.fundacioncgr.fundacioncgr.R;

import java.util.ArrayList;

/**
 * Created by David on 05/01/2017.
 */


/**
 * Clase que genera la vista de una lista de elementos
 */
public class AdaptadorUnRecorrido extends ArrayAdapter<Elemento> implements Filterable {
    private Context c;
    private ArrayList<Elemento>elementos;

    /**
     * Método contructor de la clase
     * @param context Contexto de la actividad en la que se muestra
     * @param elementos lista con los contenidos
     */
    public AdaptadorUnRecorrido(Context context, ArrayList<Elemento> elementos){
        super(context, R.layout.content_elemento,elementos);
        this.c = context;
        this.elementos = elementos;

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
        if (vista == null) {
            LayoutInflater i = (LayoutInflater) c.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            vista = i.inflate(R.layout.content_elemento, padre, false);
        }

        final Elemento e = elementos.get(posicion);
        TextView nombre = (TextView)vista.findViewById(R.id.tvNombreElemento);
        nombre.setText(e.getNombre());


        //coger código del elemento y lanzar la actividad de lanzar contenido
        ImageButton btPlay = (ImageButton)vista.findViewById(R.id.ibPlay);
        btPlay.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                LanzarContenido.mostrarContenido((Activity)c, e.getCodigo());
            }
        });

        return vista;
    }
}
