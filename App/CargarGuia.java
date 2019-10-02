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
import android.widget.EditText;
import android.widget.Toast;

import org.hopto.fundacioncgr.fundacioncgr.sqlite.SQLWrapper;

/**
 * Clase de la actividad que se lanza al cargar un recorrido.
 */
public class CargarGuia extends AppCompatActivity {

    private Intent ayuda;
    private Intent guias;
    private Button confirmar;
    private SQLWrapper wrapper;

    /**
     * Método para crear la actividad y definir sus elementos
     * @param savedInstanceState Parámetro con el estado de la aplicación (null si no hay datos)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_cargar_guia);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        wrapper = new SQLWrapper(this);

        guias = new Intent(CargarGuia.this, ListaRecorridos.class);

        confirmar = (Button) findViewById(R.id.button_cargar_guia);
        final EditText input = (EditText) findViewById(R.id.input_cargar_guia);
        final Activity a = this;

        confirmar.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String c = input.getText().toString();
                wrapper.setGuiaPersonalizada(c);
                Toast.makeText(a, R.string.guia_cargada, Toast.LENGTH_LONG).show();
                startActivity(guias);
            }
        });

        ayuda = new Intent(CargarGuia.this, Ayuda.class);
        FloatingActionButton fab = (FloatingActionButton) findViewById(R.id.fab);
        fab.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                startActivity(ayuda);
            }
        });
    }

}
