package org.hopto.fundacioncgr.fundacioncgr.Pojo;

import java.io.Serializable;

/**
 * Created by David on 05/01/2017.
 */

/**
 * Clase que representa un elemento del museo
 */
public class Elemento implements Serializable{
    private String id;
    private String codigo;
    private String nombre;
    private boolean estado; //para saber si está seleccionado o no a la hora de crear una guía

    /**
     * Método contructor para generar generar un elemento
     * @param id identificador en la base datos
     * @param codigo código identificados del elemento
     * @param nombre nombre del elemento
     */
    public Elemento(String id, String codigo, String nombre) {
        this.id = id;
        this.codigo = codigo;
        this.nombre = nombre;
        estado = false;
    }

    /**
     * Método para devolver el identificador
     * @return devuelve el identificador
     */
    public String getId() {
        return id;
    }

    /**
     * Método para devolver el código
     * @return devuelve el código del elemento
     */
    public String getCodigo() {
        return codigo;
    }

    /**.
     * Método para devolver el nombre
     * @return devuelve el nombre del elemento
     */
    public String getNombre(){
        return nombre;
    }

    /**
     * Método que devuelve el estado de selección en el que se encuentra el elemento
     * @return devuelve el estado del elemento
     */
    public boolean getEstado(){ return estado;}

    /**
     * Metodo para cambiar el estado de selección del elemento
     */
    public void changeEstado(){
        estado = !estado;
    }
}
