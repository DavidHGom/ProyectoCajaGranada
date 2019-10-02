package org.hopto.fundacioncgr.fundacioncgr.Adaptador;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.CheckBox;
import android.widget.CompoundButton;
import android.widget.TextView;


import org.hopto.fundacioncgr.fundacioncgr.Pojo.Elemento;
import org.hopto.fundacioncgr.fundacioncgr.R;
import org.w3c.dom.Text;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by David on 17/12/2016.
 */

/**
 * Clase para generar los items de la lista de contenidos
 */

public class AdaptadorContenidos extends ArrayAdapter<Elemento> {

    private Context c;
    private ArrayList<Elemento> contenidos;

    /**
     * Método contructor de la clase
     * @param context Contexto de la actividad en la que se muestra
     * @param contenidos lista con los contenidos
     */

    public AdaptadorContenidos(Context context, ArrayList<Elemento> contenidos) {
        super(context, R.layout.content_contenido,contenidos);
        c = context;
        this.contenidos = contenidos;
    }

    /**
     * Método para obtener el tamaño de la lista de guias
     * @return cantidad de elementos
     */
    @Override
    public int getCount(){ return contenidos.size(); }

    /**
     * Método que devuelve un elemento de la lista de elementos
     * @param pos posicion del elemento de la lista
     * @return un elemento de a lista
     */
    @Override
    public Elemento getItem(int pos){
        return contenidos.get(pos);
    }


    /**
     * Metodo para consulta la lista de elementos que se encuentran seleccionados
     * @return lista de los elementos seleccionados
     */
    public List<Elemento> getChequed(){
        List<Elemento> checked = new ArrayList<>();
        for(Elemento e : contenidos) {
            if(e.getEstado()) {
                checked.add(e);
            }
        }

        return checked;
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
        if(vista==null) {
            LayoutInflater i = (LayoutInflater) c.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            vista = i.inflate(R.layout.content_contenido, padre, false);
        }
        final Elemento con = contenidos.get(posicion);

        TextView titulo = (TextView)vista.findViewById(R.id.tvTituloContenido);
        TextView descripcion = (TextView)vista.findViewById(R.id.tvDescripcionContenido);
        CheckBox ch = (CheckBox)vista.findViewById(R.id.cbAnadir);

        ch.setOnCheckedChangeListener(new CompoundButton.OnCheckedChangeListener() {
            @Override
            public void onCheckedChanged(CompoundButton buttonView, boolean isChecked) {
                con.changeEstado();
            }
        });

        titulo.setText(con.getNombre());
        descripcion.setText(c.getResources().getString(R.string.code) + ": "+con.getCodigo());

        return vista;
    }
}
