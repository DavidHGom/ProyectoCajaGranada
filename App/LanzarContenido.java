package org.hopto.fundacioncgr.fundacioncgr;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;

import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;

import org.hopto.fundacioncgr.fundacioncgr.ConexionServidor.Conexion;
import org.hopto.fundacioncgr.fundacioncgr.Pojo.Recorrido;

import java.io.File;
import java.util.ArrayList;
import java.util.concurrent.ExecutionException;

/**
 * Clase abstracta para tener un sólo método para lanzar las actividades de visualización de contenidos.
 */
public abstract class LanzarContenido {

    /**
     * Método para lanzar el contenido en función de su formato
     * @param c Actividad desde la que se llama al método
     * @param codigo Código de identificación del contenido
     */
    public static void mostrarContenido(Activity c, String codigo) {
        Intent cambio = null;
        String formato = ((App)c.getApplicationContext()).getPref();

        Conexion conexion = new Conexion(c);
        conexion.execute(Conexion.CONTENIDO, codigo, formato);
        String info = null;
        File str = null;
        try {
            switch (formato) {
                case "audio":
                    cambio = new Intent(c, AudioPlayer.class);
                    info = (String) conexion.get();
                    break;

                case "sign":
                    cambio = new Intent(c, FullscreenVideoPlayer.class);
                    info = (String) conexion.get();
                    break;

                case "sub":
                    String url_sub = (String)conexion.get();
                    conexion = new Conexion(c);
                    conexion.execute(Conexion.DOWNLOAD_STR, url_sub);
                    str = (File)conexion.get();

                case "video":
                    Conexion conexion2 = new Conexion(c);
                    conexion2.execute(Conexion.CONTENIDO, codigo, "video");
                    info = (String) conexion2.get();
                    cambio = new Intent(c, FullscreenVideoPlayer.class);
                    break;
                case "texto":
                    cambio = new Intent(c, Visualizacion_Texto.class);
                    info = (String) conexion.get();
                    break;
            }

            cambio.putExtra("info", info);
            c.startActivity(cambio);
        } catch (InterruptedException e) {
            e.printStackTrace();
        } catch (ExecutionException e) {
            e.printStackTrace();
        }
    }

    /**
     * Método para crear la actividad de visualización de recorridos.
     * @param a actividad para lanzar la actividad de los recorridos
     * @return intent a la pantalla de recorridos con los extras introducidos.
     */
    public static Intent lanzarRecorridos(Activity a) {
        Conexion connect = new Conexion(a);
        Intent actividad = new Intent(a, ListaRecorridos.class);
        ArrayList<Recorrido> recorridos = new ArrayList<Recorrido>();
        //hacer antes consulta en la base datos para pasar la actividad con datos
        try {
            JsonArray lista = (JsonArray)connect.execute(Conexion.RECORRIDOS).get();
            for (JsonElement e : lista) {
                JsonObject fila = e.getAsJsonObject();
                Recorrido guia = new Recorrido(fila.get("#guia").getAsString(),
                        fila.get("nombre_guia").getAsString(), false);
                recorridos.add(guia);
            }
        } catch (InterruptedException e) {
            e.printStackTrace();
        } catch (ExecutionException e) {
            e.printStackTrace();
        }
        actividad.putExtra("guias",recorridos);
        return actividad;
    }
}
