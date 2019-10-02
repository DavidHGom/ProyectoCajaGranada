package org.hopto.fundacioncgr.fundacioncgr.Pojo;

import java.io.Serializable;

/**
 * Created by David on 05/12/2016.
 */


/**
 * Clase para represetar un recorrido guiado del museo
 */
public class Recorrido implements Serializable{

    private String numGuia;
    private String nombreGuia;
    private boolean local;//false = no almacenado en local

    /**
     * Método constructor para crear un recorrido guiado
     * @param numGuia número identificador del recorrido
     * @param nombreGuia nombre del recorrido
     * @param local indicador de si se encuentra almacenado en local o en servidor
     */
    public Recorrido(String numGuia, String nombreGuia, boolean local) {
        this.numGuia = numGuia;
        this.nombreGuia = nombreGuia;
        this.local = local;
    }

    /**
     * Método que devuelve donde se encuentra almacenado el recorrido
     * @return lugar en el que está guardado
     */
    public boolean getLocal() {
        return local;
    }

    /**
     * Método para devolver el número identificador de la guía
     * @return número identificador
     */
    public String getNumGuia() {
        return numGuia;
    }

    /**
     * Método para devolver el nombre del recorrido
     * @return nombre del recorrido
     */
    public String getNombreGuia() {
        return nombreGuia;
    }

}
