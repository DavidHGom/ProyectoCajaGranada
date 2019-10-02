package org.hopto.fundacioncgr.fundacioncgr;

import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.ListView;
import android.widget.RelativeLayout;
import android.widget.Spinner;

import org.hopto.fundacioncgr.fundacioncgr.Adaptador.AdaptadorSalas;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Sala;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.List;
import java.util.zip.Inflater;

/**
 * Clase para lanzar la actividad que muestra la lista de salas.
 */
public class ListaSalas extends AppCompatActivity {

    private ListView listaSalas;
    private Spinner spinPreferencias;
    private ArrayList<Sala> salas;
    private final static String[] preferencias = { "Ascendente","Descendente" };
    private View v;

    /**
     * Método para crear la actividad y definir sus elementos
     * @param savedInstanceState Parámetro con el estado de la aplicación (null si no hay datos)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_lista_salas);

        spinPreferencias = (Spinner)findViewById(R.id.spin_Salas);
        loadSpinnerPreferencias();
        salas = (ArrayList<Sala>)getIntent().getSerializableExtra("salas");

        final AdaptadorSalas adaptadorSalas = new AdaptadorSalas(this,salas);
        listaSalas = (ListView)findViewById(R.id.lvSalas);
        listaSalas.setAdapter(adaptadorSalas);

        spinPreferencias.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                switch (position){
                    case 0:
                        Collections.sort(salas, new Comparator<Sala>() {
                            @Override
                            public int compare(Sala o1, Sala o2) {
                                return o1.getNumSala().compareTo(o2.getNumSala());
                            }
                        });
                        break;
                    case 1:
                        Collections.sort(salas, new Comparator<Sala>() {
                            @Override
                            public int compare(Sala o1, Sala o2) {
                                return o2.getNumSala().compareTo(o1.getNumSala());
                            }
                        });

                        break;
                }
                adaptadorSalas.notifyDataSetChanged();
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {

            }
        });
    }

    /**
     * Método para cargar la lista de salas.
     */
    private void loadSpinnerPreferencias() {

        // Create an ArrayAdapter using the string array and a default spinner
        // layout
        ArrayAdapter adapter = new ArrayAdapter<String>(this, R.layout.support_simple_spinner_dropdown_item,preferencias);
        spinPreferencias.setAdapter(adapter);

        // This activity implements the AdapterView.OnItemSelectedListener
   //     this.spinPreferencias.setOnItemSelectedListener(this);
        //this.spinPreferencias.setOnItemSelectedListener(this);

    }
}
