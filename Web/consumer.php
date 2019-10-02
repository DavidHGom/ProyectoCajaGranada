<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 'on');

/**
 * Clase consumer con la que se controlan los envíos de las peticiones a la API desde la interfaz web.
 * 
 * @author Manuel Lafuente Aranda: implementación de todos los métodos.
 * @author Daniel Soto del Ojo: implementación y corrección de algunos métodos.
 */

class Consumer{

    /**
    * Método con el que se genera la ruta en la que se almacenará un fichero en el servidor.
    *
    * @author Elías Méndez García: implementación del método base.
    * @author Daniel Soto del Ojo: expansión de la implementación base.
    *
    * @param file nombre del archivo.
    * @param formato formato del archivo a almacenar.
    *
    * @return cadena que indica la ruta de almacenamiento del archivo a almacenar.
    */
    public function upload_contenido($file, $formato) {
        $target_dir = "contenido/";
        switch ($formato) {
          case 'video':
            $target_dir = $target_dir . 'video/';
            break;

          case 'audio':
            $target_dir = $target_dir . 'audio/';
            break;

          case 'sub':
            $target_dir = $target_dir . 'sub/';
            break;

          case 'sign':
            $target_dir = $target_dir . 'video/';
            break;

          case 'img_rec':
            $target_dir = $target_dir . 'imgs/recursos/';
            break;

          case 'img_equip':
            $target_dir = $target_dir . 'imgs/equipamientos/';
            break;

          case 'img_admin':
            $target_dir = $target_dir . 'imgs/administradores/';
            break;

          case 'img_asis':
            $target_dir = $target_dir . 'imgs/asistentes/';
            break;

          default:
            break;
    }

        $target_file = $target_dir . basename($file["name"]);
        move_uploaded_file($file["tmp_name"], $target_file);
        return "/" . $target_file;
    }

########################             HOME              #########################

    /**
    * Método con el que se llama a la función de login la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param dni identificador del administrador que va a loguearse en el sistema.
    * @param pass contraseña que introduce el usuario para introducirse en el sistema.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function postLogin($dni, $pass){
        $data = array("dni" => $dni, "password" => $pass);

        $ch = curl_init("http://localhost/api/login");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de logout de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param token cadena necesaria para identificar al usuario que quiere cerrar sesión. Además sirve para control de seguridad de no retorno con el navegador (flecha "Atrás") una vez se cierra sesión.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function postLogout($token){
        $data = array("token" => $token);

        $ch = curl_init("http://localhost/api/logout");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

########################            INICIO             #########################

    /**
    * Método con el que se llama a la función de búsqueda de un administrador mediante un token de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param token cadena con la que se busca al administrador en la BD.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAdminSearchToken($token){
        $ch = curl_init("http://localhost/api/admin/token/$token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de modificación de datos de un administrador de la API.
    *
    * @author Manuel Lafuente Aranda: implementación del método.
    * @author Daniel Soto del Ojo: corrección de implementación base.
    *
    * @param dni_antiguo DNI antiguo del administrador.
    * @param dni_nuevo DNI nuevo del administrador.
    * @param nombre valor a indicar en el campo del nombre del administrador.
    * @param direccion valor a indicar en el campo de la dirección del administrador.
    * @param contrasena valor a indicar en el campo de la contraseña del administrador.
    * @param correo valor a indicar en el campo del correo del administrador.
    * @param telefono valor a indicar en el campo del teléfono del administrador.
    * @param foto nombre de la foto del administrador.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function putAdmin($dni_antiguo, $dni_nuevo, $nombre, $direccion, $contrasena, $correo, $telefono, $foto){
        if (strcmp($foto["name"],"") == 0) {
            $data = array("dni_antiguo" => $dni_antiguo, "dni" => $dni_nuevo, "nombre" => $nombre, "direccion" => $direccion, "password" => $contrasena, "email" => $correo, "telefono" => $telefono);
            $ch = curl_init("http://localhost/api/admin/nofoto");
        }
        else {
            $info = $this->upload_contenido($foto, "img_admin");
            $data = array("dni_antiguo" => $dni_antiguo, "dni" => $dni_nuevo, "nombre" => $nombre, "direccion" => $direccion, "password" => $contrasena, "email" => $correo, "telefono" => $telefono, "foto" => $info);
            $ch = curl_init("http://localhost/api/admin");
        }

        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

########################         GEST. ADMIN.          #########################

    /**
    * Método con el que se llama a la función de adición de un administrador de la API.
    *
    * @author Manuel Lafuente Aranda: implementación del método.
    * @author Daniel Soto del Ojo: corrección de implementación base.
    *
    * @param dni DNI del administrador.
    * @param nombre nombre del administrador.
    * @param direccion dirección del administrador.
    * @param contrasena contraseña del administrador.
    * @param correo correo del administrador.
    * @param telefono teléfono del administrador.
    * @param foto nombre de la foto del administrador.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function postAddAdmin($dni, $nombre, $direccion, $contrasena, $correo, $telefono, $foto){
        $info = $this->upload_contenido($foto, "img_admin");
        $data = array("dni" => $dni, "nombre" => $nombre, "direccion" => $direccion, "password" => $contrasena, "email" => $correo, "telefono" => $telefono, "foto" => $info);

        $ch = curl_init("http://localhost/api/admin");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de todos los administradores de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAllAdmin(){
        $ch = curl_init("http://localhost/api/admin");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de eliminación de un administrador de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param dni DNI con el que se identifica al administrador.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function deleteAdmin($dni){
        $data = array("dni" => $dni);

        $ch = curl_init("http://localhost/api/admin");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de administradores mediante el nombre de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param nombre nombre o parte del mismo por el que hay que buscar al administrador/es.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAdminSearch($nombre){
        $ch = curl_init("http://localhost/api/admin/$nombre");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

########################         GEST. ASIS.           #########################

    /**
    * Método con el que se llama a la función de adición de un asistente de la API.
    *
    * @author Manuel Lafuente Aranda: implementación del método.
    * @author Daniel Soto del Ojo: corrección de implementación base.
    *
    * @param dni DNI del asistente.
    * @param nombre nombre del asistente.
    * @param direccion dirección del asistente.
    * @param contrasena contraseña del asistente.
    * @param correo correo del asistente.
    * @param telefono teléfono del asistente.
    * @param foto nombre de la foto del asistente.
    * @param id_telegram identificador de Telegram para poder mandar mensajes al asistente.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function postAddAsis($dni, $nombre, $direccion, $contrasena, $correo, $telefono, $foto, $id_telegram){
        $info = $this->upload_contenido($foto, "img_asis");
        $data = array("dni" => $dni, "nombre" => $nombre, "direccion" => $direccion, "password" => $contrasena, "email" => $correo, "telefono" => $telefono, "foto" => $info, "id_telegram" => $id_telegram);

        $ch = curl_init("http://localhost/api/asistente");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de todos los asistentes de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAllAsis(){
        $ch = curl_init("http://localhost/api/asistente");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de modificación de datos de un administrador de la API.
    *
    * @author Manuel Lafuente Aranda: implementación del método.
    * @author Daniel Soto del Ojo: corrección de implementación base.
    *
    * @param dni_antiguo DNI antiguo del asistente.
    * @param dni_nuevo DNI nuevo del asistente.
    * @param nombre valor a indicar en el campo del nombre del asistente.
    * @param direccion valor a indicar en el campo de la dirección del asistente.
    * @param contrasena valor a indicar en el campo de la contraseña del asistente.
    * @param correo valor a indicar en el campo del correo del asistente.
    * @param telefono valor a indicar en el campo del teléfono del asistente.
    * @param foto nombre de la foto del asistente.
    * @param estado valor a indicar en el campo del estado de disponibilidad del asistente.
    * @param id_telegram valor a indicar en el campo del identificador de Telegram para mandar mensajes al asistente.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function putAsis($dni_antiguo, $dni_nuevo, $nombre, $direccion, $contrasena, $correo, $telefono, $foto, $estado, $id_telegram){
        if (strcmp($foto["name"], "") == 0) {
            $data = array("dni_antiguo" => $dni_antiguo, "dni" => $dni_nuevo, "nombre" => $nombre, "direccion" => $direccion, "password" => $contrasena, "email" => $correo, "telefono" => $telefono, "estado" => $estado, "id_telegram" => $id_telegram);
            $ch = curl_init("http://localhost/api/asistente/nofoto");
        }
        else {
            $info = $this->upload_contenido($foto, "img_asis");
            $data = array("dni_antiguo" => $dni_antiguo, "dni" => $dni_nuevo, "nombre" => $nombre, "direccion" => $direccion, "password" => $contrasena, "email" => $correo, "telefono" => $telefono, "foto" => $info, "estado" => $estado, "id_telegram" => $id_telegram);
            $ch = curl_init("http://localhost/api/asistente");
        }

        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de las asistencias de un asistente de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param dni DNI del asistente del que se consultan las asistencias.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAsistenciasAsistente($dni){
        $ch = curl_init("http://localhost/api/asistencia/$dni");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de datos de una asistencia mediante su identificador de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id identificador de la asistencia de la que obtener los datos.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAsistenciaId($id){
        $ch = curl_init("http://localhost/api/asistencia/id/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de eliminación de un asistente de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param dni DNI con el que se identifica al asistente.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function deleteAsis($dni){
        $data = array("dni" => $dni);

        $ch = curl_init("http://localhost/api/asistente");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de asistentes mediante el nombre de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param nombre nombre o parte del mismo por el que hay que buscar al asistente/s.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAsisSearch($nombre){
    $ch = curl_init("http://localhost/api/asistente/$nombre");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_HEADER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($response);
    return $json;
    }

########################         GEST. EQUIP.          #########################

    /**
    * Método con el que se llama a la función de adición de un equipamiento de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @param ubicacion ubicación del equipamiento en el museo.
    * @param n_sala número de la sala en la que se encuentra el equipamiento en el museo.
    * @param nombre nombre del equipamiento.
    * @param proveedor proveedor del equipamiento.
    * @param img nombre del archivo de la imagen del equipamiento.
    * @param desc_img descripción de la imagen del equipamiento.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function postAddEquipamiento($ubicacion, $n_sala, $nombre, $proveedor, $img, $desc_img){
        $info = $this->upload_contenido($img, "img_equip");

        $data = array("ubicacion" => $ubicacion, "n_sala" => $n_sala, "nombre" => $nombre, "proveedor" => $proveedor, "img" => $info, "desc_img" => $desc_img);

        $ch = curl_init("http://localhost/api/equipamiento");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de modificación de datos de un equipamiento de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @param id_equipamiento_modificar identificador del equipamiento sobre el que hay que realizar cambios.
    * @param ubicacion valor a indicar en el campo de la ubicación del equipamiento.
    * @param n_sala valor a indicar en el campo del número de la sala en la que se encuentra el equipamiento.
    * @param nombre valor a indicar en el campo del nombre del equipamiento.
    * @param proveedor valor a indicar en el campo del proveedor del equipamiento.
    * @param img nombre de la imagen del equipamiento.
    * @param desc_img valor a indicar en el campo de la descripción de la imagen del equipamiento.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function putEquipamiento($id_equipamiento_modificar, $ubicacion, $n_sala, $nombre, $proveedor, $img, $desc_img){
        if (strcmp($img["name"], "") == 0) {
            $data = array("id_equipamiento_modificar" => $id_equipamiento_modificar, "ubicacion" => $ubicacion, "n_sala" => $n_sala, "nombre" => $nombre, "proveedor" => $proveedor, "desc_img" => $desc_img);

        $ch = curl_init("http://localhost/api/equipamiento/nofoto");
        }
        else {
            $info = $this->upload_contenido($img, "img_equip");
            $data = array("id_equipamiento_modificar" => $id_equipamiento_modificar, "ubicacion" => $ubicacion, "n_sala" => $n_sala, "nombre" => $nombre, "proveedor" => $proveedor, "img" => $info, "desc_img" => $desc_img);

            $ch = curl_init("http://localhost/api/equipamiento");
        }

        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de eliminación de un equipamiento de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id_equipamiento identificador del equipamiento a eliminar.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function deleteEquipamiento($id_equipamiento){
        $data = array("id_equipamiento" => $id_equipamiento);

        $ch = curl_init("http://localhost/api/equipamiento");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de todos los equipamientos de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAllEquipamiento(){
        $ch = curl_init("http://localhost/api/equipamiento");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de equipamientos por identificador de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @param idequip identificador del que se obtiene el equipamiento.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getEquipamientoId($idequip){
        $ch = curl_init("http://localhost/api/equipamiento/idequip/$idequip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de equipamientos por la sala en la que se encuentran de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @param sala sala de la que se obtienen los equipamientos.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getEquipamientoSala($sala){
        $ch = curl_init("http://localhost/api/equipamiento/sala/$sala");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de equipamientos por un recurso que contienen de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @param idrecurso identificador del recurso que deben contener los equipamientos que se obtienen.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getEquipamientoRecurso($idrecurso){
        $ch = curl_init("http://localhost/api/equipamiento/idrecurso/$idrecurso");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de equipamientos por el nombre de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param nombre nombre o parte del mismo del que se obtienen los equipamientos.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getEquipamientoNombre($nombre){
        $ch = curl_init("http://localhost/api/equipamiento/nombre/$nombre");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de todos los recursos pertenecientes a un equipamiento de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @param idequip identificador del equipamiento del que se quieren obtener todos los recursos.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getRecursosEnEquipamiento($idequip){
        $ch = curl_init("http://localhost/api/recurso/idequip/$idequip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención del nombre de un equipamiento por el identificador del equipamiento de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param nombre nombre del que se obtienen los equipamientos.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getNombreEquipamiento($id){
        $ch = curl_init("http://localhost/api/equipamiento/idnombre/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

########################         GEST. RECUR.          #########################

    /**
    * Método con el que se llama a la función de adición de un recurso de la API.
    *
    * @author Manuel Lafuente Aranda: implementación del método.
    * @author Daniel Soto del Ojo: corrección de implementación base.
    *
    * @param codigo_qr código qr con el que se podrá acceder a la información del recurso.
    * @param nombre nombre del recurso.
    * @param id_equipamiento identificador del equipamiento al que pertenece.
    * @param img nombre de la imagen del recurso.
    * @param desc_img descripción de la imagen del recurso.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function postAddRecurso($codigo_qr, $nombre, $id_equipamiento, $img, $desc_img){
        $info = $this->upload_contenido($img, "img_rec");

        $data = array("codigo_qr" => $codigo_qr, "nombre" => $nombre, "id_equipamiento" => $id_equipamiento, "img" => $info, "desc_img" => $desc_img);

        $ch = curl_init("http://localhost/api/recurso");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de todos los recursos de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAllRecursos(){
        $ch = curl_init("http://localhost/api/recurso");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención del contenido perteneciente a un recurso de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @param idrecurso identificador del recurso del que hay que obtener todos los contenidos.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAllContenidoRecurso($idrecurso){
        $ch = curl_init("http://localhost/api/contenido/$idrecurso");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de modificación de datos de un recurso de la API.
    *
    * @author Manuel Lafuente Aranda: implementación del método.
    * @author Daniel Soto del Ojo: corrección de implementación base.
    *
    * @param id_recurso_modificar identificador del recurso sobre el que hay que realizar cambios.
    * @param codigo_qr valor a indicar en el campo del código qr del recurso.
    * @param nombre valor a indicar en el campo del nombre del recurso.
    * @param img nombre de la imagen del recurso.
    * @param desc_img valor a indicar en el campo de la descripción de la imagen del recurso.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function putRecurso($id_recurso_modificar, $codigo_qr, $nombre, $img, $desc_img){
        if (strcmp($img["name"], "") == 0) {
            $data = array("id_recurso_modificar" => $id_recurso_modificar, "codigo_qr" => $codigo_qr, "nombre" => $nombre, "desc_img" => $desc_img);

            $ch = curl_init("http://localhost/api/recurso/nofoto");
        }
        else {
            $info = $this->upload_contenido($img, "img_rec");
            $data = array("id_recurso_modificar" => $id_recurso_modificar, "codigo_qr" => $codigo_qr, "nombre" => $nombre, "img" => $info, "desc_img" => $desc_img);

            $ch = curl_init("http://localhost/api/recurso");
        }

        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de eliminación de un recurso de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id_recurso identificador del recurso a eliminar.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function deleteRecurso($id_recurso){
        $data = array("id_recurso" => $id_recurso);

        $ch = curl_init("http://localhost/api/recurso");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de recursos por el nombre de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param nombre nombre o parte del mismo del que se obtienen los recursos.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getRecursoNombre($nombre){
        $ch = curl_init("http://localhost/api/recurso/nombre/$nombre");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de recursos por el identificador de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id identificador del que se obtienen los recursos.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getRecursoId($id){
        $ch = curl_init("http://localhost/api/recurso/id/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de recursos por la sala de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param sala sala de la que se obtienen los recursos.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getRecursoSala($sala){
        $ch = curl_init("http://localhost/api/recurso/sala/$sala");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

########################         GEST. CONT.           #########################

    /**
    * Método con el que se llama a la función de adición de contenido de texto de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @param id_idioma identificador del idioma del contenido.
    * @param id_recurso identificador del recurso al que pertenece el contenido.
    * @param nombre nombre del contenido.
    * @param formato formato en el que se encuentra el contenido.
    * @param info información del contenido.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function postAddContenidoTexto($id_idioma, $id_recurso, $nombre, $formato, $info){
        
        $data = array("id_idioma" => $id_idioma, "id_recurso" => $id_recurso, "nombre" => $nombre, "formato" => $formato, "info" => $info);

        $ch = curl_init("http://localhost/api/contenido");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_encode($data);
    }

    /**
    * Método con el que se llama a la función de adición de contenido de multimedia de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @param id_idioma identificador del idioma del contenido.
    * @param id_recurso identificador del recurso al que pertenece el contenido.
    * @param nombre nombre del contenido.
    * @param formato formato en el que se encuentra el contenido.
    * @param info información del contenido.
    * @param file fichero asociado.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function postAddContenidoMultimedia($id_idioma, $id_recurso, $nombre, $formato, $info, $file){
        $info = $this->upload_contenido($file, $formato);
        $data = array("id_idioma" => $id_idioma, "id_recurso" => $id_recurso, "nombre" => $nombre, "formato" => $formato, "info" => $info);

        $ch = curl_init("http://localhost/api/contenido");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_encode($data);
    }

    /**
    * Método con el que se llama a la función de obtención de todos los contenidos de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAllContenido(){
        $ch = curl_init("http://localhost/api/contenido");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de modificación de datos de un contenido de la API.
    *
    * @author Manuel Lafuente Aranda: implementación del método.
    * @author Daniel Soto del Ojo: corrección de implementación base.
    *
    * @param id_contenido_modificar identificador del contenido sobre el que hay que realizar cambios.
    * @param idioma valor a indicar en el campo del idioma del contenido.
    * @param nombre valor a indicar en el campo del nombre del contenido.
    * @param info valor a indicar en el campo de la información del contenido.
    * @param formato valor a indicar en el campo del formato del contenido.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function putContenido($id_contenido_modificar, $idioma, $nombre, $info, $formato){
        $data = array("id_contenido_modificar" => $id_contenido_modificar, "idioma" => $idioma, "nombre" => $nombre, "info" => $info, "formato" => $formato);

        $ch = curl_init("http://localhost/api/contenido");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de eliminación de un contenido de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id_contenido identificador del contenido que hay que eliminar.
    * @param formato formato del contenido a eliminar.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function deleteContenido($id_contenido, $formato){
        $data = array("id_contenido" => $id_contenido, "formato" => $formato);

        $ch = curl_init("http://localhost/api/contenido");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de contenidos por el nombre de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param nombre nombre o parte del mismo del que se obtienen los contenidos.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getContenidoNombre($nombre){
        $ch = curl_init("http://localhost/api/contenido/nombre/$nombre");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de idiomas disponibles de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getIdiomas(){
        $ch = curl_init("http://localhost/api/idioma");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de idiomas por nombre de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param nombre nombre o parte del mismo del que se obtienen los idiomas.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getIdiomaNombre($nombre){
        $ch = curl_init("http://localhost/api/idioma/nombre/$nombre");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de idiomas por id de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id identificador del que se obtienen los idiomas.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getIdiomaId($id){
        $ch = curl_init("http://localhost/api/idioma/id/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de contenidos por id de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id identificador del que se obtienen los contenidos.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getContenidoId($id){
        $ch = curl_init("http://localhost/api/contenido/id/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

########################         GEST. GUIAS           #########################

    /**
    * Método con el que se llama a la función de obtención de todas las guías de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAllGuias(){
        $ch = curl_init("http://localhost/api/guia");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de las guías predefinidas de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAllGuiasPredefinidas(){
        $ch = curl_init("http://localhost/api/guia/tipo/predefinida");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de las guías personalizadas de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAllGuiasPersonalizadas(){
        $ch = curl_init("http://localhost/api/guia/tipo/personalizada");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de una guía por id de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id identificador del que se obtiene la guía.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getGuiaId($id){
        $ch = curl_init("http://localhost/api/guia/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención del contenido de una guía de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id identifidcador de la guía de la que se obtendrán los contenidos.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getGuiaIdComposicion($id){
        $ch = curl_init("http://localhost/api/guia/composicion/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de adición de una guía de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id identificador de la guía.
    * @param nombre nombre de la guía.
    * @param tipo tipo de la guía.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function postAddGuia($nombre, $tipo){
        $data = array("nombre_guia" => $nombre);

        $ch = curl_init("http://localhost/api/guia/tipo/$tipo");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
    * Método con el que se llama a la función de eliminación de una guía de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id identificador de la guía a eliminar.
    * @param tipo tipo de la guía a eliminar.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function deleteGuia($id, $tipo){
        $data = array("id_guia" => $id);

        $ch = curl_init("http://localhost/api/guia/tipo/$tipo");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de aumento de prioridad de un recurso de una guía de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id_recurso identificador del recurso al que se le aumentará la prioridad.
    * @param id_guia identificador de la guía a la que pertenece el recurso.
    * @param prioridad_antigua prioridad que tiene el recurso antes de ser modificado.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function putArribaRecurso($id_recurso, $id_guia, $prioridad_antigua){
        $data = array("id_recurso" => $id_recurso, "id_guia" => $id_guia, "prioridad_antigua" => $prioridad_antigua);

        $ch = curl_init("http://localhost/api/recursoguia/arriba");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de disminución de prioridad de un recurso de una guía de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id_recurso identificador del recurso al que se le disminuirá la prioridad.
    * @param id_guia identificador de la guía a la que pertenece el recurso.
    * @param prioridad_antigua prioridad que tiene el recurso antes de ser modificado.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function putAbajoRecurso($id_recurso, $id_guia, $prioridad_antigua){
        $data = array("id_recurso" => $id_recurso, "id_guia" => $id_guia, "prioridad_antigua" => $prioridad_antigua);

        $ch = curl_init("http://localhost/api/recursoguia/abajo");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de adición de un recurso a una guía de la API.
    *
    * @author Manuel Lafuente Aranda: implementación del método.
    * @author Daniel Soto del Ojo: corrección de implementación base.
    *
    * @param id_recurso identificador del recurso a añadir.
    * @param id_guia identificador de la guía en la cual añadir el recurso.
    * @param prioridad prioridad con la que se introduciría el recurso.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function postRecursoGuia($id_recurso, $id_guia, $prioridad){
        $data = array("id_recurso" => $id_recurso, "id_guia" => $id_guia, "prioridad" => $prioridad);

        $ch = curl_init("http://localhost/api/recursoguia");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de eliminación de un recurso de una guía de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id_guia identificador de la guía de la cual eliminar el recurso.
    * @param id_recurso identificador del recurso a eliminar de la guía.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function deleteRecursoGuia($id_guia, $id_recurso){
        $ch = curl_init("http://localhost/api/recursoguia");
        $data = array("id_guia" => $id_guia, "id_recurso" => $id_recurso);
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de conversión de una guía personalizada a una predefinida de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id_guia_modificar identificador de la guía personalizada a convertir.
    */
    public function conversionPersonalizadaPredeterminada($id_guia_modificar){
        $data = array("id_guia_modificar" => $id_guia_modificar);

        $ch = curl_init("http://localhost/api/guia");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de los recursos de una sala que hay en una guía de la API.
    *
    * @author Daniel Soto del Ojo.
    *
    * @param idsala identificador de la sala de la cual obtener los recursos pertenecientes a la guía.
    * @param idguia identificador de la guía de la cual obtener los recursos pertenecientes a la sala.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getRecursosGuiaEnSala($idsala, $idguia){
        $ch = curl_init("http://localhost/api/recurso/sala/$idsala/$idguia");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

########################         GEST. SALAS           #########################

    /**
    * Método con el que se llama a la función de adición de una sala de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param n_sala número de la sala.
    * @param descripcion descripción de la sala (del contenido de la misma mayoritariamente).
    * @param planta planta del museo en la que se encuentra la sala.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function postAddSala($n_sala, $descripcion, $planta){
        $data = array("n_sala" => $n_sala, "descripcion_sala" => $descripcion, "planta" => $planta);

        $ch = curl_init("http://localhost/api/sala");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de todas las salas de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getAllSalas(){
        $ch = curl_init("http://localhost/api/sala");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de eliminación de una sala de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id_sala_traspasar identificador de la sala a la que se trasladarán temporal o definitivamente los equipamientos de la sala a eliminar.
    * @param id_sala_eliminar identificador de la sala a eliminar.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function deleteSala($id_sala_traspasar, $id_sala_eliminar){
        $data = array("id_sala_traspasar" => $id_sala_traspasar, "id_sala_eliminar" => $id_sala_eliminar);

        $ch = curl_init("http://localhost/api/sala");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de modificación de datos de una sala de la API.
    *
    * @author Manuel Lafuente Aranda: implementación del método.
    * @author Daniel Soto del Ojo: corrección de implementación base.
    *
    * @param id_sala_modificar identificador de la sala sobre la que hay que realizar cambios.
    * @param n_sala valor a indicar en el campo del número de la sala.
    * @param descripcion_sala valor a indicar en el campo de la descripción de la sala.
    * @param planta valor a indicar en el campo de la planta en la que se encuentra la sala.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function putSala($id_sala_modificar, $n_sala, $descripcion_sala, $planta){
        $data = array("id_sala_modificar" => $id_sala_modificar, "n_sala" => $n_sala, "descripcion_sala" => $descripcion_sala, "planta" => $planta);

        $ch = curl_init("http://localhost/api/sala");
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de las salas por id de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param id identificador del que se obtendrá la sala.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getSalasId($id){
        $ch = curl_init("http://localhost/api/sala/id/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
    }

    /**
    * Método con el que se llama a la función de obtención de salas por nombre de la API.
    *
    * @author Manuel Lafuente Aranda.
    *
    * @param nombre nombre o parte del mismo del que se obtendrán las salas.
    *
    * @return cadena, en formato JSON, con datos sobre el resultado de la ejecución de la operación.
    */
    public function getSalasNombre($nombre){
        $ch = curl_init("http://localhost/api/sala/nombre/$nombre");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);
        return $json;
        }
}

/**
* De aquí en adelante se detecta el botón pulsado y según el valor de éste se realizará una llamada a una función u otra de
* las previamente definidas.
*
* @author Manuel Lafuente Aranda: implementación de la mayoría de los casos.
* @author Daniel Soto del Ojo: implementación y correción de algunos casos.
*
*/
$botonPulsado = isset($_GET['boton']) ? $_GET['boton']:'';
$consumer = new Consumer();

if($botonPulsado != '')
  switch ($botonPulsado) {

########################             HOME              #########################

    case 'login':
        $result = $consumer->postLogin($_POST["dni"], $_POST["pass"]);
        $_SESSION["userid"] = $result->token;

        switch ($result->res) {
            case 1:
            case 2:
                header('Location: index.php?page=inicio_admin&user=admin');
                break;
            case -1:
                $_SESSION["aviso_fallo"] = "Se ha producido un error, el usuario o la contraseña no son correctos.";
                header('Location: index.php?fallo=1');
                break;
        }
            
    break;

    case 'logout':
        $result = $consumer->postLogout($_SESSION["userid"]);
        unset($_SESSION["userid"]);

        switch ($result->res) {
            case 1:
                header('Location: index.php');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=inicio_admin&user=admin&fallo=1');
                break;
        }
        
    break;

########################            INICIO             #########################

    case 'updateAdminOn':
      $result = $consumer->putAdmin($_POST["old_dni"], $_POST["dni"], $_POST["nombre"], $_POST["direccion"], $_POST["password"], $_POST["email"], $_POST["telefono"], $_FILES["file"]);

      switch ($result->res) {
            case 1:
                header('Location: index.php?page=inicio_admin&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=inicio_admin&user=admin&fallo=1');
                break;

            case 3:
                $_SESSION["aviso_fallo"] = "Ya existe un administrador con ese DNI.";
                header('Location: index.php?page=inicio_admin&user=admin&fallo=1');
                break;
        }
        
    break;

########################         GEST. ADMIN.          #########################

    case 'addAdmin':
        $result = $consumer->postAddAdmin($_POST["dni"], $_POST["nombre"], $_POST["direccion"], $_POST["password"], $_POST["email"], $_POST["telefono"], $_FILES["file"]);
        
        switch ($result->res) {
            case 1:
                header('Location: index.php?page=gestion_admin&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=gestion_admin&user=admin&fallo=1');
                break;

            case 2:
                $_SESSION["aviso_fallo"] = "Ya existe un administrador con ese DNI.";
                header('Location: index.php?page=gestion_admin&user=admin&fallo=1');
                break;
        }

    break;

    case 'deleteAdmin':
      $result = $consumer->deleteAdmin($_POST["deleteAdminDni"]);
      
      switch ($result->res) {
            case 1:
                header('Location: index.php?page=gestion_admin&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=gestion_admin&user=admin&fallo=1');
                break;
        }
        
    break;

    case 'updateAdmin':
      $result = $consumer->putAdmin($_POST["old_dni"], $_POST["dni"], $_POST["nombre"], $_POST["direccion"], $_POST["password"], $_POST["email"], $_POST["telefono"], $_FILES["file"]);

      switch ($result->res) {
            case 1:
                header('Location: index.php?page=gestion_admin&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=gestion_admin&user=admin&fallo=1');
                break;

            case 3:
                $_SESSION["aviso_fallo"] = "Ya existe un administrador con ese DNI.";
                header('Location: index.php?page=gestion_admin&user=admin&fallo=1');
                break;
        }
        
    break;

    case 'searchAdmin':
      $_SESSION["searchNombreAdmin"] = isset($_POST['searchNombreAdmin']) ? $_POST['searchNombreAdmin']:null;
      
      header('Location: index.php?page=gestion_admin&user=admin');
    break;

########################         GEST. ASIS.           #########################

    case 'addAsis':
        $result = $consumer->postAddAsis($_POST["dni"], $_POST["nombre"], $_POST["direccion"], $_POST["password"], $_POST["email"], $_POST["telefono"], $_FILES["file"], $_POST["id_telegram"]);
        echo "result -> "; var_dump($result);
        echo "result->res ->"; var_dump($result->res);
        switch ($result->res) {
            case 1:
                header('Location: index.php?page=gestion_asis&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=gestion_asis&user=admin&fallo=1');
                break;

            case 2:
                $_SESSION["aviso_fallo"] = "Ya existe un asistente con ese DNI.";
                header('Location: index.php?page=gestion_asis&user=admin&fallo=1');
                break;
        }        
        
    break;

    case 'updateAsis':
      $result = $consumer->putAsis($_POST["old_dni"], $_POST["dni"], $_POST["nombre"], $_POST["direccion"], $_POST["password"], $_POST["email"], $_POST["telefono"], $_FILES["file"], $_POST["estado"], $_POST["id_telegram"]);

      switch ($result->res) {
            case 1:
                header('Location: index.php?page=gestion_asis&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=gestion_asis&user=admin&fallo=1');
                break;

            case 3:
                $_SESSION["aviso_fallo"] = "Ya existe un asistente con ese DNI.";
                header('Location: index.php?page=gestion_asis&user=admin&fallo=1');
                break;
        }
        
    break;

    case 'deleteAsis':
      $result = $consumer->deleteAsis($_POST["deleteAsisDni"]);

      switch ($result->res) {
            case 1:
                header('Location: index.php?page=gestion_asis&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=gestion_asis&user=admin&fallo=1');
                break;
        }
        
    break;

    case 'searchAsis':
      $_SESSION["searchNombreAsis"] = isset($_POST['searchNombreAsis']) ? $_POST['searchNombreAsis']:null;
      
      header('Location: index.php?page=gestion_asis&user=admin');
    break;

    case 'asistenciasAsis':
      $_SESSION["consultaAsistenciasDni"] = isset($_POST['consultaAsistenciasDni']) ? $_POST['consultaAsistenciasDni']:null;
      
      header('Location: index.php?page=ver_asistencias&user=admin');
    break;

########################         GEST. EQUIP.          #########################

    case 'addEquipamiento':
      $result = $consumer->postAddEquipamiento($_POST["ubicacion"], $_POST["nsala"], $_POST["nombre"], $_POST["proveedor"], $_FILES["file"], $_POST["img_desc"]);
      
      if ($result->res == 1)
        header('Location: index.php?page=gestion_info&user=admin');
    break;

    case 'updateEquipamiento':
      $result = $consumer->putEquipamiento($_POST["id_equipamiento_modificar"], $_POST["ubicacion"], $_POST["nsala"], $_POST["nombre"], $_POST["proveedor"], $_FILES["file"], $_POST["desc_img"]);

      switch ($result->res) {
            case 1:
                header('Location: index.php?page=ver_info&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=ver_info&user=admin&fallo=1');
                break;
        }
        
    break;

    case 'deleteEquipamiento':
      $result = $consumer->deleteEquipamiento($_POST["id"]);
      
      if ($result->res == 1)
        header('Location: index.php?page=ver_info&user=admin&sala='.$_POST["nsala"]);
    break;

    case 'searchEquipamiento':
      $_SESSION["searchNombreEquipamiento"] = isset($_POST['searchNombreEquipamiento']) ? $_POST['searchNombreEquipamiento']:null;
      
      header('Location: index.php?page=ver_info&user=admin');
    break;

########################         GEST. RECUR.          #########################

    case 'addRecurso':
      $result = $consumer->postAddRecurso($_POST["codigoqr"], $_POST["nombre"], $_POST["idequipo"], $_FILES["file"], $_POST["desc_img"]);

      switch ($result->res) {
            case 1:
                header('Location: index.php?page=gestion_recursos&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=gestion_recursos&user=admin&fallo=1');
                break;

            case 2:
                $_SESSION["aviso_fallo"] = "Ya existe un recurso con ese código QR.";
                header('Location: index.php?page=gestion_recursos&user=admin&fallo=1');
                break;
        }
        
    break;

    case 'updateRecurso':
        $result = $consumer->putRecurso($_POST["id_recurso_modificar"], $_POST["codigoqr"], $_POST["nombre"], $_FILES["file"], $_POST["desc_img"]);

        switch ($result->res) {
            case 1:
                header('Location: index.php?page=ver_recursos&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=ver_recursos&user=admin&fallo=1');
                break;

            case 3:
                $_SESSION["aviso_fallo"] = "Código QR ya existente.";
                header('Location: index.php?page=ver_recursos&user=admin&fallo=1');
                break;
        }
          
    break;

    case 'deleteRecurso':
      $result = $consumer->deleteRecurso($_POST["id"]);
      
      if ($result->res == 1)
        header('Location: index.php?page=ver_recursos&user=admin');
    break;

    case 'searchRecurso':
      $_SESSION["searchNombreRecurso"] = isset($_POST['searchNombreRecurso']) ? $_POST['searchNombreRecurso']:null;
      
      header('Location: index.php?page=ver_recursos&user=admin');
    break;

########################         GEST. CONT.           #########################

    case 'addContenido':
        $recurso_asoc = isset($_POST["recurso_asoc"]) ? $_POST["recurso_asoc"]:-1;

        if(isset($_POST["formato"]) && $_POST["formato"] === "texto") {
            $result = $consumer->postAddContenidoTexto($_POST["idioma"], $_POST["id_recurso"], $_POST["nombre"], $_POST["formato"], $_POST["info"]);
        }
        else {
            $result = $consumer->postAddContenidoMultimedia($_POST["idioma"], $_POST["id_recurso"], $_POST["nombre"], $_POST["formato"], $_POST["info"], $_FILES["file"]);
        }
        header('Location: index.php?page=gestion_contenido&user=admin');
    break;

    case 'updateContenidoTexto':
      $result = $consumer->putContenido($_POST["id_antiguo"], $_POST["idioma"], $_POST["nombre"], $_POST["info"], $_POST["formato"]);

      switch ($result->res) {
            case 1:
                header('Location: index.php?page=ver_contenido&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=ver_contenido&user=admin&fallo=1');
                break;
        }
        
    break;

    case 'updateContenidoMultimedia':
      $result = $consumer->putContenido($_POST["id_antiguo"], $_POST["idioma"], $_POST["nombre"], $_POST["url"], $_POST["formato"]);

      switch ($result->res) {
            case 1:
                header('Location: index.php?page=ver_contenido&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=ver_contenido&user=admin&fallo=1');
                break;
        }
        
    break;

    case 'searchContenidoIdIdioma':
      header('Location: index.php?page=ver_contenido&user=admin&searchContenidoId='.$_POST["id_informacion"].'&searchContenidoIdioma='.$_POST["idioma"]);
    break;

    case 'searchContenido':
      $_SESSION["searchNombreContenido"] = isset($_POST['searchNombreContenido']) ? $_POST['searchNombreContenido']:null;
      
      header('Location: index.php?page=ver_contenido&user=admin');
    break;

    case 'deleteContenido':
      $result = $consumer->deleteContenido($_POST["idcontenido"], $_POST["formatocontenido"]);
      
      header('Location: index.php?page=ver_contenido&user=admin');
    break;

########################         GEST. GUIAS           #########################

    case 'addGuia':
        $result = $consumer->postAddGuia($_POST["nombreguia"], "predefinida");
        
        header('Location: index.php?page=gestion_guias_indice&user=admin');
    break;

    case 'deleteGuia':
        $result = $consumer->deleteGuia($_POST["id"], $_POST["tipo"]);

        if($result->res == 1)
            header('Location: index.php?page=gestion_guias_indice&user=admin');
    break;

    case 'moverArribaContenido':
      $result = $consumer->putArribaRecurso($_GET["idrecurso"], $_GET["idguia"], $_GET["prioridad"]);

      switch ($result->res) {
            case 1:
                header('Location: index.php?page=gestion_guias_predeterminadas&user=admin&guia='.$_GET["idguia"]);
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=gestion_guias_predeterminadas&user=admin&guia='.$_GET["idguia"].'&fallo=1');
                break;
        }
          
    break;

    case 'moverAbajoContenido':
      $result = $consumer->putAbajoRecurso($_GET["idrecurso"], $_GET["idguia"], $_GET["prioridad"]);

      switch ($result->res) {
            case 1:
                header('Location: index.php?page=gestion_guias_predeterminadas&user=admin&guia='.$_GET["idguia"]);
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=gestion_guias_predeterminadas&user=admin&guia='.$_GET["idguia"].'&fallo=1');
                break;
        }
          
    break;

    case 'addRecursoGuia':
      $result = $consumer->postRecursoGuia($_POST["idrecurso"], $_POST["idguia"], $_POST["prioridad"]);

      switch ($result->res) {
            case 1:
                header('Location: index.php?page=gestion_guias_predeterminadas&user=admin&guia='.$_POST["idguia"]);
                break;

            case 2:
                $_SESSION["aviso_fallo"] = "Asociación entre la guía y el recurso ya existente.";
                header('Location: index.php?page=gestion_guias_predeterminadas&user=admin&guia='.$_POST["idguia"].'&fallo=1');
                break;

            case 3:
                $_SESSION["aviso_fallo"] = "Prioridad existente.";
                header('Location: index.php?page=gestion_guias_predeterminadas&user=admin&guia='.$_POST["idguia"].'&fallo=1');
                break;
        }
      
    break;

    case 'deleteRecursoGuia':
      $result = $consumer->deleteRecursoGuia($_POST["idguia"], $_POST["idrecurso"]);

      if($result->res == 1)
        header('Location: index.php?page=gestion_guias_predeterminadas&user=admin&guia='.$_POST["idguia"]);
    break;

    case 'convertirPersonalizada':
      $result = $consumer->conversionPersonalizadaPredeterminada($_POST["idguia"]);

      switch ($result->res) {
            case 1:
                header('Location: index.php?page=gestion_guias_indice&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=gestion_guias_indice&user=admin&fallo=1');
                break;
        }
        
    break;

########################         GEST. SALAS           #########################

    case 'addSala':
        $result = $consumer->postAddSala($_POST["n_sala"], $_POST["descripcionsala"], $_POST["planta"]);
        
        switch ($result->res) {
            case 1:
                header('Location: index.php?page=gestion_salas&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=gestion_salas&user=admin&fallo=1');
                break;
        }
            
    break;

    case 'deleteSala':
      $result = $consumer->deleteSala($_POST["id_sala_traspasar"], $_POST["id_sala_eliminar"]);

      if($result->res == 1)
        header('Location: index.php?page=gestion_salas&user=admin');
    break;

    case 'updateSala':
      $result = $consumer->putSala($_POST["id_sala_modificar"], $_POST["n_sala"], $_POST["descripcion_sala"], $_POST["planta"]);

      switch ($result->res) {
            case 1:
                header('Location: index.php?page=gestion_salas&user=admin');
                break;

            case -1:
                $_SESSION["aviso_fallo"] = "Error durante la realización de la operación.";
                header('Location: index.php?page=gestion_salas&user=admin&fallo=1');
                break;
        }
          
    break;

    case 'searchSala':
      $_SESSION["searchNombreSala"] = isset($_POST['searchNombreSala']) ? $_POST['searchNombreSala']:null;
      
      header('Location: index.php?page=gestion_salas&user=admin');
    break;

    default:
        header('Location: index.php');
    break;
  }

?>
