package org.hopto.fundacioncgr.fundacioncgr.Pojo;

import java.io.Serializable;

/**
 * Created by David on 05/12/2016.
 */

/**
 * Clase que representa una sala del museo
 */
public class Sala implements Serializable {

    private int id;
    private String numSala;
    private String descripción;
    private int planta;

    /**
     * Método constructor de la sala
     * @param id identificador de la sala
     * @param numSala número de la sala
     * @param descripción descripción de la sala
     * @param planta planta en la que se encuentra en la sala
     */
    public Sala(int id, String numSala, String descripción, int planta) {
        this.id = id;
        this.numSala = numSala;
        this.descripción = descripción;
        this.planta = planta;
    }

    /**
     * Método que devuelve el identificador de la sala
     * @return identificador de la sala
     */
    public int getId() { return id; }

    /**
     * Método que devuelve el número de la sala
     * @return devuvelve número de la sala
     */
    public String getNumSala() {
        return numSala;
    }

    /**
     * Método que devuelve la descripción de la sala
     * @return devuelve la descripcion
     */
    public String getDescripción() {
        return descripción;
    }


}
