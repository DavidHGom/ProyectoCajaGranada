<?php

/*
Archivo content.php que controla la impresión del contenido de cada sección
*/

/**
 * Clase content con la que se imprime el contenido de una sección
 *
 * @author Daniel Soto del Ojo: Implementación final, documentación y mantenimiento
 * @author Manuel Lafuente Aranda: Implementación inicial
 */
class content{

  var $consumer = null;
  /**
  * Constructor
  *
  * @param $consumidor instancia de la clase Consumidor que nos permitirá el acceso a las diferentes funciones de la API.
  */
  function content($consumidor){
    $this->consumer = $consumidor;
  }

  /**
  * Método encargado de analizar la sección a la cual queremos acceder y como consecuencia llamar al métood pintar de dicha 
  * sección
  *
  * @param $page indica la sección que queremos visualizar
  * @param $sala indica la sala que queremos visualizar
  * @param $guia indica la guía que queremos visualizar
  * @param $searchAdmin indica el admin que hemos buscado y queremos visualizar
  * @param $searchAsis indica el asistente que hemos buscado y queremos visualizar
  * @param $searchNombreSala indica el nombre de la sala que hemos buscado y queremos visualizar
  * @param $equipo indica el equipo que queremos visualizar
  * @param $recurso indica el recurso que queremos visualizar
  * @param $searchNombreEquipamiento indica el equipamiento que hemos buscado y queremos visualizar
  * @param $searchNombreRecurso indica el recurso que hemos buscado y queremos visualizar
  * @param $searchNombreContenido indica el contenido que hemos buscado y queremos visualizar
  * @param $fallo variable que indica si hay que imprimir un mensaje de error
  */
  function pintar($page, $sala, $guia, $searchAdmin, $searchAsis, $searchNombreSala, $equipo, $recurso, $searchNombreEquipamiento, $searchNombreRecurso, $searchNombreContenido, $fallo){
    if($page == ''){
      $home = new home();
      $home->pintar();
    }
    else
      switch ($page) {
        case 'inicio_admin':
          $inicio_admin = new inicio_admin($this->consumer);
          $inicio_admin->pintar();
          break;
        case 'gestion_admin':
          $gestion_admin = new gestion_admin($this->consumer);
          $gestion_admin->pintar($searchAdmin);
          break;
        case 'gestion_asis':
          $gestion_asis = new gestion_asis($this->consumer);
          $gestion_asis->pintar($searchAsis);
          break;
        case 'gestion_info':
          $gestion_info = new gestion_info($this->consumer);
          $gestion_info->pintar();
          break;
        case 'ver_info':
          $ver_info = new ver_info($this->consumer, $sala, $searchNombreEquipamiento);
          $ver_info->pintar();
          break;
        case 'gestion_guias_indice':
          $gestion_guias_indice = new gestion_guias_indice($this->consumer);
          $gestion_guias_indice->pintar();
          break;
        case 'gestion_guias_predeterminadas':
          $gestion_guias_predeterminadas = new gestion_guias_predeterminadas($this->consumer, $guia);
          $gestion_guias_predeterminadas->pintar();
          break;
        case 'gestion_guias_personalizadas':
          $gestion_guias_personalizadas = new gestion_guias_personalizadas($this->consumer, $guia);
          $gestion_guias_personalizadas->pintar();
          break;
        case 'gestion_contenido':
          $gestion_contenido = new gestion_contenido($this->consumer);
          $gestion_contenido->pintar();
          break;
        case 'ver_contenido':
          $ver_contenido = new ver_contenido($this->consumer, $recurso, $searchNombreContenido);
          $ver_contenido->pintar();
          break;
        case 'ver_asistencias':
          $ver_asistencias = new ver_asistencias($this->consumer);
          $ver_asistencias->pintar();
          break;
        case 'ver_datos_asistencia':
          $ver_datos_asistencia = new ver_datos_asistencia($this->consumer);
          $ver_datos_asistencia->pintar();
          break;
        case 'gestion_recursos':
          $gestion_recursos = new gestion_recursos($this->consumer);
          $gestion_recursos->pintar();
          break;
        case 'ver_recursos':
          $ver_recursos = new ver_recursos($this->consumer, $equipo, $searchNombreRecurso);
          $ver_recursos->pintar();
          break;
        case 'gestion_salas':
          $gestion_salas = new gestion_salas($this->consumer);
          $gestion_salas->pintar($searchNombreSala);
          break;
      }

      if (strcmp($fallo, "1") == 0) {
        $message = "ERROR: " . $_SESSION["aviso_fallo"];
        echo "<script type='text/javascript'>alert('$message');</script>";
      }
  }
}

/**
 * Clase encargada de gestionar la impresión de la sección de acceso a la página web por parte 
 * de un usuario que no se ha identificado.
 */
class home{

  /**
  * Constructor
  *
  */
  function home(){
  }

  /**
  * Método encargado de visualizar la clase en html
  *
  */
  function pintar(){
    echo '<div class="text-center" style="background-color: lightgrey ; padding-top: 5% ; padding-bottom: 5%">
            <h2><b style="color:white">CENTRO CULTURAL CajaGRANADA</b></h2>
            <h3 style="margin-top: 5%"><b style="color: white">Inicio de sesión</b></h3>
            <form action="consumer.php?boton=login" method="post">
              <div style="margin-top: 2%">
                  <input type="text" name="dni" class="form-control" placeholder="DNI" style="border: 2px solid ; display: block ; margin: 0 auto ; width: 50%">
                  <input type="password" name="pass" class="form-control" placeholder="CONTRASEÑA" style="border: 2px solid ; display: block ; margin: 0 auto ; margin-top: 10px ; width: 50%">
              </div>
              <button type="submit" class="btn btn-primary" style="padding: 5px 85px ; color: white ; background-color: #337ab7 ; margin-top: 5%">Iniciar sesión</button>';
    echo '  </div>
          </div>';
  }
}

/**
 * Clase encargada de gestionar la impresión de la sección de inicio de un administrador
 */
class inicio_admin{

  var $consumidor = null;
  var $array_asistencias = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  */
  function inicio_admin($consumer){
    $this->consumidor = $consumer;
    $this->datos_usuario = $this->consumidor->getAdminSearchToken($_SESSION["userid"]);
  }

  /**
  * Método encargado de visualizar la clase
  *
  */
  function pintar(){
    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; background-color: white ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">';
                $tmp = $this->datos_usuario->content[0];
                echo '<h4 style="color: #337ab7">'.$tmp->{"nombre"}.'</h4>';

                if (strcmp($tmp->foto, "FILE") == 0)
                  echo '<img src="/imgs/Icono.png" height="100">';
                else
                  echo '<img src="'.$tmp->foto.'" width="100" height="100">';

    echo '
                <div style="border-style: dotted ; border-width: 2px ; margin-top: 10px">
                <p style="display: block ; margin: 0 auto ; margin-top: 20px ; width: 50%"><b>DNI del usuario: </b>'.$tmp->{"dni"}.'</p>
                <p style="display: block ; margin: 0 auto ; margin-top: 20px ; width: 50%"><b>Dirección del usuario: </b>'.$tmp->{"direccion"}.'</p>
                <p style="display: block ; margin: 0 auto ; margin-top: 20px ; width: 50%"><b>Correo del usuario: </b>'.$tmp->{"email"}.'</p>
                <p style="display: block ; margin: 0 auto ; margin-top: 20px ; margin-bottom: 20px ; width: 50%"><b>Teléfono del usuario: </b>'.$tmp->{"telefono"}.'</p></div>
                <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 2%" data-toggle="modal" data-target="#modificarAdmin">MODIFICAR DATOS</button>
            </div>
        </div>';

        //var_dump($this->datos_usuario->content[0]);

    echo '<div class="modal fade pg-show-modal" id="modificarAdmin" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Efectúe cambios en los campos a modificar:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=updateAdminOn" method="post">
                        <div class="modal-body">
                            <p style="text-align: left">DNI (sin letra):<br><input type="text" pattern="\d{8}" name="dni" value="'.$tmp->dni.'" required></p>
                            <p style="text-align: left">Contraseña:<br><input type="password" name="password" value="'.$tmp->password.'"></p>
                            <p style="text-align: left">Nombre:<br><input type="text" pattern="\D+" name="nombre" value="'.$tmp->nombre.'" required></p>
                            <p style="text-align: left">Correo electrónico:<br><input type="email" pattern="^\w+@\w+\.\w{2,3}$" name="email" value="'.$tmp->email.'" required></p>
                            <p style="text-align: left">Dirección:<br><input type="text" name="direccion" value="'.$tmp->direccion.'" required></p>
                            <p style="text-align: left">Teléfono:<br><input type="text" pattern="\d{9}" name="telefono" value="'.$tmp->telefono.'" required></p>
                            <p style="text-align: left">Fichero:<br><input class="btn btn-default" type="file" name="file" id="file" value=""></p>
                            <input type="hidden" name="old_dni" value="'.$tmp->dni.'">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>';

  }
}

/**
 * Clase encargada de gestionar la impresión de la sección de gestión de un administrador
 */
class gestion_admin{

  var $array_admins = null;
  var $consumidor = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  */
  function gestion_admin($consumer){
    $this->consumidor = $consumer;
    $this->array_admins = $this->consumidor->getAllAdmin();
    //var_dump($this->array_admins);
    //echo $this->array_admins->content[0]->dni;
  }
  
  /**
  * Método encargado de visualizar la clase
  *
  * @param $searchAdmin variable encargada de realizar el proceso de filtrado cuando se busca un administrador
  */
  function pintar($searchAdmin){

    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; background-color: white ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
                <h4 style="color: #337ab7">GESTIÓN DE ADMINISTRADORES</h4>
                <div style="overflow-y: scroll ; height: 300px">';

                if (strcmp($searchAdmin, "all") == 0 || strcmp($searchAdmin, "") == 0) {
                  $tam = $this->array_admins->numresultados;
                  for ($i = 0; $i < $tam; $i++) {
                    $tmp = $this->array_admins->content[$i];
                    $this->pintar_admin($tmp->dni, $tmp->nombre, $tmp->direccion, $tmp->password, $tmp->email, $tmp->telefono, $tmp->foto);
                  }
                }
                else {
                  $busqueda = $this->consumidor->getAdminSearch($searchAdmin);
                  //var_dump($busqueda);
                  if (strcmp($busqueda->res, "1") == 0) {
                    for ($i = 0; $i < sizeof($busqueda->content); $i++) {
                      $tmp = $busqueda->content[$i];
                      //var_dump($tmp);
                      $this->pintar_admin($tmp->dni, $tmp->nombre, $tmp->direccion, $tmp->password, $tmp->email, $tmp->telefono, $tmp->foto);
                    }
                  }
                }

                echo '</div>
                <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px" data-toggle="modal" data-target="#addadminmodal">AÑADIR NUEVO ADMINISTRADOR</button>
                <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px" data-toggle="modal" data-target="#buscarAdmin">BUSCAR ADMINISTRADOR</button>
            </div>
        </div>
        <div class="modal fade pg-show-modal" id="buscarAdmin" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Rellene el siguiente campo*:</b></h4>
                    </div>
                    <form action="consumer.php?boton=searchAdmin" method="post">
                        <div class="modal-body">
                            <p>Nombre del administrador:<br><input type="text" name="searchNombreAdmin" value=""></p>
                            <p>(*)Si se deja en blanco, devolverá todos los resultados.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade pg-show-modal" id="addadminmodal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Rellene los siguientes campos:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=addAdmin" method="post">
                        <div class="modal-body">
                            <p>DNI del administrador (sin letra):<br><input type="text" pattern="\d{8}" name="dni" value="" required></p>
                            <p>Contraseña del administrador:<br><input type="password" name="password" value="" required></p>
                            <p>Nombre del administrador:<br><input type="text" pattern="\D+" name="nombre" value="" required></p>
                            <p>Correo del administrador:<br><input type="email" pattern="^\w+@\w+\.\w{2,3}$" name="email" value="" required></p>
                            <p>Dirección del administrador:<br><input type="text" name="direccion" value="" required></p>
                            <p>Teléfono del administrador:<br><input type="text" pattern="\d{9}" name="telefono" value="" required></p>
                            <p style="text-align: left">Fichero:<br><input class="btn btn-default" type="file" name="file" id="file" value="" required></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Añadir</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        ';
  }

  /**
  * Método encargado de visualizar el contenido de un administrador
  *
  * @param $dni dni del administrador
  * @param $nombre nombre del administrador
  * @param $direccion dirección donde vive
  * @param $password contraseña de su usario
  * @param $email dirección de correo electrónico
  * @param telefono número de teléfono
  * @param $foto foto que permita identificarlo
  */
  function pintar_admin($dni, $nombre, $direccion, $password, $email, $telefono, $foto) {
    echo '<div style="border-style: solid ; border-width: 1px ; border-color: grey">
                        <div style="display: flex ; border-style: solid ; border-width: 1px ; border-color: grey ; margin: 5px ; padding: 20px">
                            <div> ';

                            if (strcmp($foto, "FILE") == 0)
                              echo '<img src="/imgs/Icono.png" height="100">';
                            else
                              echo '<img src="'.$foto.'" height="100">';
    echo '
                            </div>
                            <div style="flex: 1.25 ; padding: 1em ; border-style: dotted ; border-width: 1px ; margin-left: 25px">
                              <p style="margin: 0px ; text-align: left"> <b>DNI</b>: ' .$dni. '</p>
                              <p style="margin: 0px ; text-align: left""> <b>Nombre</b>: ' .$nombre. '</p>
                              <p style="margin: 0px ; text-align: left""> <b>Direccion</b>: ' .$direccion. '</p>
                              <p style="margin: 0px ; text-align: left""> <b>Email</b>: ' .$email. '</p>
                              <p style="margin: 0px ; text-align: left""> <b>Telefono</b>: ' .$telefono. '</p>
                            </div>
                            <div style="flex: 0.75 ; padding: 1em ; margin-left: 25px">
                                <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; padding: 1% 20%" data-toggle="modal" data-target="#modificarAdmin'.$dni.'">MODIFICAR</button>
                                <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 5% ; padding: 1% 20%" data-toggle="modal" data-target="#deleteadminmodal'.$dni.'">ELIMINAR</button>
                            </div>
                        </div>
                    </div>';

      echo '<div class="modal fade pg-show-modal" id="modificarAdmin'.$dni.'" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Efectúe cambios en los campos a modificar:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=updateAdmin" method="post">
                        <div class="modal-body">
                            <p style="text-align: left">DNI del administrador (sin letra):<br><input type="text" pattern="\d{8}" name="dni" value="'.$dni.'" required></p>
                            <p style="text-align: left">Contraseña del administrador:<br><input type="password" name="password" value=""></p>
                            <p style="text-align: left">Nombre del administrador:<br><input type="text" pattern="\D+" name="nombre" value="'.$nombre.'" required></p>
                            <p style="text-align: left">Correo del administrador:<br><input type="email" pattern="^\w+@\w+\.\w{2,3}$" name="email" value="'.$email.'" required></p>
                            <p style="text-align: left">Dirección del administrador:<br><input type="text" name="direccion" value="'.$direccion.'" required></p>
                            <p style="text-align: left">Teléfono del administrador:<br><input type="text" pattern="\d{9}" name="telefono" value="'.$telefono.'" required></p>
                            <p style="text-align: left">Fichero:<br><input class="btn btn-default" type="file" name="file" id="file" value="'.$foto.'"></p>
                            <input type="hidden" name="old_dni" value="'.$dni.'">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>';

      echo '<div class="modal fade pg-show-modal" id="deleteadminmodal'.$dni.'" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  </div>
                  <form enctype="multipart/form-data" action="consumer.php?boton=deleteAdmin" method="post">
                    <div class="modal-body">

                            <div id="admin">
                              <p style="text-align: left">¿Seguro que desea eliminar al administrador '.$nombre.' con DNI '.$dni.'?
                              <input type="hidden" name="deleteAdminDni" value="'.$dni.'">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Eliminar</button>
                    </div>
                  </form>
              </div>
          </div>
      </div>';
  }
}

/**
 * Clase encargada de gestionar la impresión de la sección de gestión de un asistente
 */
class gestion_asis{

  var $array_asis = null;
  var $consumidor = null;

  /**
  * Constructor 
  *
  * @param $consumer instancia del consumidor
  */
  function gestion_asis($consumer){
    $this->consumidor = $consumer;
    $this->array_asis = $this->consumidor->getAllAsis();
    $_SESSION["consultaAsistenciasDni"] = "";
    //var_dump($this->array_asis);

  }

  /**
  * Método encargado de visualizar el contenido de la sección
  * 
  * @param $searchAsis variable encargada de realizar el proceso de filtrado cuando se busca un asistente
  */
  function pintar($searchAsis){
    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; background-color: white ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
                <h4 style="color: #337ab7">GESTIÓN DE ASISTENTES</h4>
                <div style="overflow-y: scroll ; height: 300px"> ';

                if (strcmp($searchAsis, "all") == 0 || strcmp($searchAsis, "") == 0) {
                  $tam = $this->array_asis->numresultados;
                  for ($i = 0; $i < $tam; $i++) {
                    $tmp = $this->array_asis->content[$i];
                    $this->pintar_asis($tmp->dni, $tmp->nombre, $tmp->direccion, $tmp->password, $tmp->email, $tmp->telefono, $tmp->foto, $tmp->estado_actividad, $tmp->id_telegram);
                  }
                }
                else {
                  $busqueda = $this->consumidor->getAsisSearch($searchAsis);
                  //var_dump($busqueda);
                  if (strcmp($busqueda->res, "1") == 0) {
                    for ($i = 0; $i < sizeof($busqueda->content); $i++) {
                      $tmp = $busqueda->content[$i];
                      //var_dump($tmp);
                      $this->pintar_asis($tmp->dni, $tmp->nombre, $tmp->direccion, $tmp->password, $tmp->email, $tmp->telefono, $tmp->foto, $tmp->estado_actividad, $tmp->id_telegram);
                    }
                  }
                }

    echo        '</div>
                    <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px" data-toggle="modal" data-target="#addassistantmodal">AÑADIR NUEVO ASISTENTE</button>
                    <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px" data-toggle="modal" data-target="#buscarAsis">BUSCAR ASISTENTE</button>
            </div>
        </div>
        <div class="modal fade pg-show-modal" id="addassistantmodal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Rellene los siguientes campos:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=addAsis" method="post">
                      <div class="modal-body">
                          <p>DNI del asistente (sin letra):<br><input type="text" pattern="\d{8}" name="dni" value="" required></p>
                          <p>Contraseña del asistente:<br><input type="password" name="password" value="" required></p>
                          <p>Nombre del asistente:<br><input type="text" pattern="\D+" name="nombre" value="" required></p>
                          <p>Id de telegram del asistente:<br><input type="text" name="id_telegram" value=""></p>
                          <p>Correo del asistente:<br><input type="email" pattern="^\w+@\w+\.\w{2,3}$" name="email" value="" required></p>
                          <p>Dirección del Asistente:<br><input type="text" name="direccion" value="" required></p>
                          <p>Teléfono del Asistente:<br><input type="text" pattern="\d{9}" name="telefono" value="" required></p>
                          <p style="text-align: left">Fichero:<br><input class="btn btn-default" type="file" name="file" id="file" value="" required></p>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                          <button type="summit" class="btn btn-primary">Añadir</button>
                      </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade pg-show-modal" id="buscarAsis" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Rellene el siguiente campo*:</b></h4>
                    </div>
                    <form action="consumer.php?boton=searchAsis" method="post">
                      <div class="modal-body">
                          <p>Nombre del asistente:<br><input type="text" name="searchNombreAsis" value=""></p>
                          <p>(*)Si se deja en blanco, devolverá todos los resultados.</p>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                          <button type="summit" class="btn btn-primary">Buscar</button>
                      </div>
                    </form>
                </div>
            </div>
        </div>

        ';
  }

  /**
  * Método encargado de visualizar el contenido de un asistente
  *
  * @param $dni dni del asistente
  * @param $nombre nombre del asistente
  * @param $direccion dirección donde vive
  * @param $password contraseña de su usario
  * @param $email dirección de correo electrónico
  * @param telefono número de teléfono
  * @param $foto foto que permita identificarlo
  * @param $estado indica si está libre u ocupado
  */
  function pintar_asis($dni, $nombre, $direccion, $password, $email, $telefono, $foto, $estado, $id_telegram) {
    echo '<div style="border-style: solid ; border-width: 1px ; border-color: grey">
                        <div style="display: flex ; border-style: solid ; border-width: 1px ; border-color: grey ; margin: 5px ; padding: 20px"> ';

                        if (strcmp($foto, "FILE") == 0)
                              echo '<img src="/imgs/Icono.png" height="100">';
                            else
                              echo '<img src="'.$foto.'" height="100">';

    echo '

                            <div style="flex: 1.25 ; padding: 1em ; border-style: dotted ; border-width: 1px ; margin-left: 25px">
                            <p style="margin: 0px ; text-align: left"> <b>DNI</b>: ' .$dni. '</p>
                              <p style="margin: 0px ; text-align: left""> <b>Nombre</b>: ' .$nombre. '</p>
                              <p style="margin: 0px ; text-align: left""> <b>ID telegram</b>: ' .$id_telegram. '</p>
                              <p style="margin: 0px ; text-align: left""> <b>Direccion</b>: ' .$direccion. '</p>
                              <p style="margin: 0px ; text-align: left""> <b>Email</b>: ' .$email. '</p>
                              <p style="margin: 0px ; text-align: left""> <b>Telefono</b>: ' .$telefono. '</p>
                            </div>

                            <div style="flex: 0.75 ; margin-left: 10px">
                                <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 2% ; padding: 1% 20%" data-toggle="modal" data-target="#asistenciasasismodal'.$dni.'">VER ASISTENCIAS</button>
                                <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 2% ; padding: 1% 20%" data-toggle="modal" data-target="#modificarAsis'.$dni.'">MODIFICAR</button>
                                <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 2% ; padding: 1% 20%" data-toggle="modal" data-target="#deleteasismodal'.$dni.'">ELIMINAR</button>
                            </div>
                        </div>
                    </div>';

      echo '<div class="modal fade pg-show-modal" id="modificarAsis'.$dni.'" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Efectúe cambios en los campos a modificar:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=updateAsis" method="post">
                        <div class="modal-body">
                            <p style="text-align: left">DNI del asistente (sin letra):<br><input type="text" pattern="\d{8}" name="dni" value="'.$dni.'" required></p>
                            <p style="text-align: left">Contraseña del asistente:<br><input type="password" name="password" value=""></p>
                            <p style="text-align: left">Nombre del asistente:<br><input type="text" pattern="\D+" name="nombre" value="'.$nombre.'" required></p>
                            <p style="text-align: left">Id de telegram del asistente:<br><input type="text" name="id_telegram" value="'.$id_telegram.'" required></p>
                            <p style="text-align: left">Correo del asistente:<br><input type="email" pattern="^\w+@\w+\.\w{2,3}$" name="email" value="'.$email.'" required></p>
                            <p style="text-align: left">Dirección del asistente:<br><input type="text" name="direccion" value="'.$direccion.'" required></p>
                            <p style="text-align: left">Teléfono del asistente:<br><input type="text" pattern="\d{9}" name="telefono" value="'.$telefono.'" required></p>
                            <p style="text-align: left">Fichero:<br><input class="btn btn-default" type="file" name="file" id="file" value=""></p>
                            <input type="hidden" name="old_dni" value="'.$dni.'">
                            <input type="hidden" name="estado" value='.$estado.'">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>';

        echo '<div class="modal fade pg-show-modal" id="deleteasismodal'.$dni.'" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  </div>
                  <form enctype="multipart/form-data" action="consumer.php?boton=deleteAsis" method="post">
                    <div class="modal-body">

                            <div id="recurso">
                              <p style="text-align: left">¿Seguro que desea eliminar al asistente '.$nombre.' con DNI '.$dni.'?
                              <input type="hidden" name="deleteAsisDni" value="'.$dni.'">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Eliminar</button>
                    </div>
                  </form>
              </div>
          </div>
      </div>';

      echo '<div class="modal fade pg-show-modal" id="asistenciasasismodal'.$dni.'" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <form enctype="multipart/form-data" action="consumer.php?boton=asistenciasAsis" method="post">
                  <div class="modal-body">

                          <div id="recurso">
                            <p style="text-align: left">¿Quiere ver las asistencias realizadas por el asistente '.$nombre.' con DNI '.$dni.'?
                            <input type="hidden" name="consultaAsistenciasDni" value="'.$dni.'">
                          </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                      <button type="submit" class="btn btn-primary">Aceptar</button>
                  </div>
                </form>
            </div>
        </div>
    </div>';
  }
}


/**
 * Clase encargada de gestionar la impresión de la sección de gestión de Equipamiento
 */
class gestion_info{

  var $consumidor = null;
  var $array_salas = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  */
  function gestion_info($consumer){
    $this->consumidor = $consumer;
    $this->array_salas = $this->consumidor->getAllSalas();
    //var_dump($this->array_salas);
  }

  /**
  * Método encargado de la visualización del contenido de la clase
  */
  function pintar(){
    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; background-color: white ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
                <h4 style="color: #337ab7">GESTIÓN DE EQUIPAMIENTO</h4>
                <button type="button" class="btn btn-default" style="background-color: #337ab7 ; color: white ; display: block ; margin: 0 auto ; margin-top: 5%" data-toggle="modal" data-target="#addInfo">AÑADIR NUEVO EQUIPAMIENTO</button> ';

                $tam = $this->array_salas->numresultados;
                for ($i = 0; $i < $tam; $i++) {
                  $tmp = $this->array_salas->content[$i];
                  $this->pintar_sala($tmp->n_sala, $tmp->{"#sala"});
                }
    echo '
                <a href="index.php?page=ver_info&user=admin" class="btn btn-default" style="background-color: #337ab7 ; color: white ; display: block ; margin: 0 auto ; margin-top: 2%; margin-bottom: 5%">VER TODOS LOS EQUIPAMIENTOS</a>
            </div>
        </div>
        <div class="modal fade pg-show-modal" id="addInfo" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Rellene los siguientes campos:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=addEquipamiento" method="post">
                      <div class="modal-body">

                              <div id="recurso">
                                <p style="text-align: left">Nombre:<br><input type="text" name="nombre" value="" required></p>';

                                echo '<p style="text-align: left">Sala:</p>
                                      <select name="nsala">';
                                $tam = $this->array_salas->numresultados;
                                for ($i=0; $i<$tam; $i++) {
                                  $tmp = $this->array_salas->content[$i];
                                  echo '<option value="'.$tmp->{"#sala"}.'">'.$tmp->n_sala.'</option>';
                                }
                                //<p style="text-align: left ; margin-top:10px">Sala:<br><input type="text" pattern="\d+" name="nsala" value="" required></p>
                                echo '</select>
                                <p style="text-align: left ; margin-top:10px">Ubicación:<br><input type="text" name="ubicacion" value="" required></p>
                                <p style="text-align: left">Proveedor:<br><input type="text" name="proveedor" value=""></p>
                                <p style="text-align: left">Descripción de imagen:<br><input type="text" name="img_desc" value="" required></p>
                                <p style="text-align: left">Imagen asociada a la información:<br><input class="btn btn-default" type="file" name="file" id="file" value="" required></p>
                              </div>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-primary">Añadir</button>
                      </div>
                    </form>
                </div>
            </div>
        </div>';
  }

  /**
  * Método encargado de la visualización de una sala
  *
  * @param $n_sala nombre de la sala
  * @param $id identificador de la sala
  */
  function pintar_sala($n_sala, $id) {
    echo '<a href="index.php?page=ver_info&user=admin&sala='.$id.'" class="btn btn-default" style="background-color: #337ab7 ; color: white ; display: block ; margin: 0 auto ; margin-top: 2%">VER EQUIPAMIENTOS DE SALA '.$id.': '.$n_sala.'</a>';
  }
}

/**
 * Clase encargada de gestionar la impresión de los equipamientos 
 */
class ver_info {
  var $consumidor = null;
  var $array_info = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  * @param $sala identificador de la sala de la cual se visualizarán los equipamientos
  * @param $searchNombreEquipamiento para cuando se realiza un proceso de filtrado por nombre de equipamiento
  */
  function ver_info($consumer, $sala, $searchNombreEquipamiento) {
    $this->consumidor = $consumer;

    if(strcmp($searchNombreEquipamiento, "all") == 0 || strcmp($searchNombreEquipamiento, "") == 0){
      if (strcmp($sala, "all") == 0) {
        $this->array_info = $this->consumidor->getAllEquipamiento();
      }
      else {
       $array_salas = $this->consumidor->getAllSalas();


        $encontrada = false;
        $i = 0;
        $tam = $array_salas->numresultados;

        while (!$encontrada && $i < $tam) {
          $tmp = $array_salas->content[$i];

          if (strcmp($tmp->{"#sala"}, $sala) == 0)
            $encontrada = true;
          else
            $i++;
        }

        if ($encontrada)
          $this->array_info = $this->consumidor->getEquipamientoSala($sala);
      }
    }
    else{
      $this->array_info = $this->consumidor->getEquipamientoNombre($searchNombreEquipamiento);
    }

    //var_dump($this->array_info);
  }

  /**
  * Método encargado de la visualización del contenido de la clase
  */
  function pintar() {
    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; background-color: white ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
          <h4 style="color: #337ab7">GESTIÓN DE EQUIPAMIENTO</h4>
          <div style="flex: 1 ; border-style: solid ; border-width: 1px ; border-color: grey ; margin-right: 35px">

              <div style="overflow-y: scroll ; height: 300px">';

                  $tam = $this->array_info->numresultados;

                  for ($i = 0; $i < $tam; $i++) {
                    $tmp = $this->array_info->content[$i];
                    $this->pintar_info($tmp->{"#equipamiento"}, $tmp->nombre, $tmp->n_sala, $tmp->ubicacion, $tmp->proveedor, $tmp->img, $tmp->desc_img);
                  }
    echo '</div></div>
    <button type="button" class="btn btn-default" style="background-color: #337ab7 ; color: white ; display: block ; margin: 0 auto ; margin-top: 5%" data-toggle="modal" data-target="#buscarEquipamiento">BUSCAR EQUIPAMIENTO</button>
    <a href="index.php?page=gestion_info&user=admin" style="text-decoration:none"><button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px" data-toggle="modal" data-target="#addassistantmodal">VOLVER A GESTIÓN DE EQUIPAMIENTO</button></a>
    <a href="index.php?page=gestion_salas&user=admin" style="text-decoration:none"><button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px" data-toggle="modal" data-target="#addassistantmodal">IR A GESTIÓN DE SALAS</button></a>
    </div></div>';

    echo '<div class="modal fade pg-show-modal" id="buscarEquipamiento" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><b>Rellene el siguiente campo*:</b></h4>
                </div>
                <form enctype="multipart/form-data" action="consumer.php?boton=searchEquipamiento" method="post">
                <div class="modal-body">
                    <p>Nombre del equipamiento:<br><input type="text" name="searchNombreEquipamiento" value="'.$_SESSION['searchNombreEquipamiento'].'"></p>
                    <p>(*)Si se deja en blanco, devolverá todos los resultados.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
                </form>
            </div>
        </div>
    </div>';
  }

  /**
  * Método encargado de pintar un Equipamiento específico
  *
  * @param $id identificador del equipamiento
  * @param $nombre nombre del equipamiento
  * @param $nsala sala a la que pertenece
  * @param $ubi ubicación del equipamiento
  * @param $proveedor nombre de la empresa proveedora del equipamiento
  * @param $img imagen asociada al equipamiento
  * @param $img_desc descripción de la imagen
  */
  function pintar_info($id, $nombre, $nsala, $ubi, $proveedor, $img, $img_desc) {
    $recursos_asoc = $this->consumidor->getRecursosEnEquipamiento($id);
    //var_dump($recursos_asoc);

    echo '<div style="border-style: solid ; border-width: 1px ; border-color: grey ; margin: 5px">
                      <div style="background-color: #337ab7">
                          <small style="color: white"> ID: '.$id.' </small>
                      </div>
                      <div style="display: flex">
                          <img src=".'.$img.'" height="100">

                          <div style="flex: 1 ; padding: 1em ; border-style: dotted ; border-width: 1px ; margin: 5px ; margin-left: 25px">
                            <p style="margin: 0px ; text-align: left""> <b>Nombre</b>: '.$nombre.'</p>
                            <p style="margin: 0px ; text-align: left""> <b>Sala</b>: '.$this->consumidor->getSalasId($nsala)->content[0]->n_sala.'</p>
                            <p style="margin: 0px ; text-align: left""> <b>Ubicación</b>: '.$ubi.'</p>
                            <p style="margin: 0px ; text-align: left""> <b>Proveedor</b>: '.$proveedor.'</p>
                            <p style="margin: 0px ; text-align: left""> <b>Descripción imagen</b>: '.$img_desc.'</p>
                            <p style="margin: 0px ; text-align: left""> <b>Recursos asociados</b>: ' ;


                            $tam = $recursos_asoc->numresultados;
                            for ($i = 0; $i < $tam; $i++) {
                              $tmp = $recursos_asoc->content[$i];
                              $nombre_rec = $this->consumidor->getRecursoId($tmp->{"#recurso"})->content[0]->nombre;
                              echo ' ['.$nombre_rec.'] ';
                            }


    echo '</p>
                          </div>

                          <div style="flex: 0.75">
                              <a href="index.php?page=ver_recursos&user=admin&id_equip='.$id.'" style="text-decoration:none"><button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 2%">GESTIONAR RECURSOS</button></a>
                              <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 5%" data-toggle="modal" data-target="#updateEquipamiento'.$id.'">MODIFICAR</button>
                              <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 5% ; margin-bottom: 2%" data-toggle="modal" data-target="#deleteEquipamiento'.$id.'">ELIMINAR</button>
                          </div>
                      </div>
                  </div>';

        echo ' <div class="modal fade pg-show-modal" id="deleteEquipamiento'.$id.'" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <form enctype="multipart/form-data" action="consumer.php?boton=deleteEquipamiento" method="post">
                  <div class="modal-body">

                          <div id="recurso">
                            <p style="text-align: left">¿Seguro que desea eliminar el equipamiento '.$nombre.'?
                            <input type="hidden" name="id" value="'.$id.'">
                            <input type="hidden" name="nsala" value="'.$nsala.'">
                          </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                      <button type="submit" class="btn btn-primary">Eliminar</button>
                  </div>
                </form>
            </div>
        </div>
      </div>';

        echo '<div class="modal fade pg-show-modal" id="updateEquipamiento'.$id.'" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      <h4 class="modal-title"><b>Efectúe cambios en los campos a modificar:</b></h4>
                  </div>
                  <form enctype="multipart/form-data" action="consumer.php?boton=updateEquipamiento" method="post">
                    <div class="modal-body">

                            <div id="recurso" style="text-align:left">
                              <p style="text-align: left">Nombre:<br><input type="text" name="nombre" value="'.$nombre.'" required></p>';
                              //<p style="text-align: left">Sala:<br><input type="text" pattern="\d+" name="nsala" value="'.$nsala.'" required></p>'

                              echo '<p style="text-align: left">Sala:<p>
                              <select name="nsala">';

                              $salas = $this->consumidor->getAllSalas();
                              $tam = $salas->numresultados;

                              for($i=0; $i<$tam; $i++) {
                                $tmp = $salas->content[$i];

                                if (strcmp($nsala, $tmp->{"#sala"}) == 0)
                                  echo '<option selected value="'.$tmp->{"#sala"}.'">'.$tmp->n_sala.'</option>';
                                else
                                  echo '<option value="'.$tmp->{"#sala"}.'">'.$tmp->n_sala.'</option>';
                              }

                              echo '</select>';
                              echo '
                              <p style="text-align: left">Ubicacion:<br><input type="text" name="ubicacion" value="'.$ubi.'" required></p>
                              <p style="text-align: left">Proveedor:<br><input type="text" name="proveedor" value="'.$proveedor.'"></p>
                              <p style="text-align: left">Descripción de la imagen:<br><input type="text" name="desc_img" value="'.$img_desc.'" required></p>
                              <p style="text-align: left">Fichero:<br><input class="btn btn-default" type="file" name="file" id="file" value="'.$img.'"></p>
                              <input type="hidden" name="id_equipamiento_modificar" value="'.$id.'">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                  </form>
              </div>
          </div>
      </div>';
  }
}

/**
 * Clase encargada de gestionar la impresión de la sección de gestión de guías
 */
class gestion_guias_indice{

  var $consumidor = null;
  var $array_guias_pred = null;
  var $array_guias_pers = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  */
  function gestion_guias_indice($consumer){
    $this->consumidor = $consumer;
    $this->array_guias_pred = $this->consumidor->getAllGuiasPredefinidas();
    $this->array_guias_pers = $this->consumidor->getAllGuiasPersonalizadas();
    //var_dump($this->array_guias_pred);
    //var_dump($this->array_guias_pers);
  }

  /**
  * Método encargado de la visualización del contenido de la clase
  */
  function pintar(){
    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; background-color: white ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
                <h4 style="color: #337ab7">GESTIÓN DE GUÍAS</h4>
                <div style="display: flex">
                    <div style="flex: 1 ; border-style: solid ; border-width: 1px ; border-color: grey ; margin-right: 35px">
                        <div style="background-color: thistle">
                            <small style="color: white">GUÍAS PREDEFINIDAS</small>
                            <br>
                        </div>
                        <div style="overflow-y: scroll ; height: 200px">
                            ';

                            $tam = $this->array_guias_pred->numresultados;
                            for ($i = 0; $i < $tam; $i++) {
                              $tmp = $this->array_guias_pred->content[$i];
                              $this->pintar_guia_pred($tmp->nombre_guia, $tmp->{"#guia"});
                            }
    echo '
                        </div>
                        <div>
                            <button type="button" class="btn btn-default" style="background-color: #337ab7 ; color: white ; margin: 10px" data-toggle="modal" data-target="#addguiamodal">AÑADIR NUEVA GUÍA</button>
                        </div>
                    </div>
                    <div style="flex: 1 ; border-style: solid ; border-width: 1px ; border-color: grey ; margin-right: 35px">
                        <div style="background-color: thistle">
                            <small style="color: white">GUÍAS PERSONALIZADAS</small>
                            <br>
                        </div>
                        <div style="overflow-y: scroll ; height: 200px">
                            ';

                            $tam = $this->array_guias_pers->numresultados;
                            for ($i = 0; $i < $tam; $i++) {
                              $tmp = $this->array_guias_pers->content[$i];
                              $this->pintar_guia_pers($tmp->nombre_guia, $tmp->{"#guia"});
                            }

    echo '
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade pg-show-modal" id="addguiamodal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Rellene los siguientes campos:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=addGuia" method="post">
                    <div class="modal-body">
                        <p>Nombre de la guía:<br><input type="text" value="" name="nombreguia" required></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Añadir</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>';
  }

  /**
  * Método encargado de pintar una guía predeterminada
  *
  * @param $nombre_guia nombre de la guía
  * @param $identificador id de la guía
  */
  function pintar_guia_pred($nombre_guia, $id) {
    echo '<div style="display: flex ; border-style: solid ; border-width: 1px ; border-color: grey ; margin: 5px ; padding: 5px">
                                <p style="flex: 1 ; padding: 1em ; border-style: dotted ; border-width: 1px ; margin-left: 25px">'.$nombre_guia.'</p>
                                <a href="index.php?page=gestion_guias_predeterminadas&user=admin&guia='.$id.'" class="btn btn-default" style="flex: 0.25 ; color: white ; background-color: #337ab7 ; margin: 10px">VER</a>
          </div>';
  }

  /**
  * Método encargado de pintar una guía personalizada
  *
  * @param $nombre_guia nombre de la guía
  * @param $id identificador de la guía
  */
  function pintar_guia_pers($nombre_guia, $id) {
    echo '<div style="display: flex ; border-style: solid ; border-width: 1px ; border-color: grey ; margin: 5px ; padding: 5px">
                                <p style="flex: 1 ; padding: 1em ; border-style: dotted ; border-width: 1px ; margin-left: 25px">'.$nombre_guia.'</p>
                                <a href="index.php?page=gestion_guias_personalizadas&user=admin&guia='.$id.'" class="btn btn-default" style="flex: 0.25 ; color: white ; background-color: #337ab7 ; margin: 10px">VER</a>
          </div>';
  }
}

/**
 * Clase encargada de gestionar la impresión de la sección de gestión de guías predeterminadas
 */
class gestion_guias_predeterminadas{

  var $consumidor = null;
  var $salas = null;
  var $guia = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  * @param $id identificador de la guía
  */
  function gestion_guias_predeterminadas($consumer, $id){
    $this->consumidor = $consumer;
    $this->salas = $this->consumidor->getAllSalas();
    $this->guia = $this->consumidor->getGuiaId($id);
    //var_dump($this->salas);
    //var_dump($this->guia);
  }

  /**
  * Método encargado de la visualización del contenido de la guía
  */
  function pintar(){
    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; background-color: white ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
                <h4 style="color: #337ab7">GESTIÓN DE GUÍAS</h4>
                <div style="flex: 1 ; border-style: solid ; border-width: 1px ; border-color: grey ; margin-right: 35px">
                    <div style="background-color: thistle">
                        <small style="color: white">'.$this->guia->content[0]->nombre_guia.'</small>
                        <br>
                    </div>
                    <div style="overflow-y: scroll ; height: 300px">
                        ';

                        $tamS = $this->salas->numresultados;

                        for ($i = 0; $i < $tamS; $i++) {
                          $tmp = $this->salas->content[$i];
                          $this->pintar_sala($tmp->{"#sala"}, $tmp->n_sala, $this->guia->content[0]->{"#guia"});
                        }

                        echo '

                    </div>

                </div>

                <div style="flex: 0.75">
                  <button type="button" class="btn btn-default" style="background-color: #337ab7 ; color: white; margin-top: 10px" data-toggle="modal" data-target="#deleteguia'.$this->guia->content[0]->{"#guia"}.'">ELIMINAR GUÍA</button>
                  <a href="index.php?page=gestion_guias_indice&user=admin" style="text-decoration:none"><button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px">VOLVER A GESTIÓN DE GUÍAS</button></a></div>
            </div>
        </div>';

    echo '<div class="modal fade pg-show-modal" id="deleteguia'.$this->guia->content[0]->{"#guia"}.'" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  </div>
                  <form enctype="multipart/form-data" action="consumer.php?boton=deleteGuia" method="post">
                    <div class="modal-body">

                            <div id="recurso">
                              <p style="text-align: left">¿Seguro que desea eliminar la guia '.$this->guia->content[0]->{"#guia"}.', "'.$this->guia->content[0]->nombre_guia.'"?
                              <input type="hidden" name="id" value="'.$this->guia->content[0]->{"#guia"}.'">
                              <input type="hidden" name="tipo" value="predefinida">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Eliminar</button>
                    </div>
                  </form>
              </div>
          </div>
      </div>';

  }

  /**
  * Método encargado de pintar los contenidos asociados a una guía dentro de una sala específica
  *
  * @param $num_sala número de la sala
  * @param $nombre_sala nombre de la sala
  * @param $guia identificador de la guía
  */
  function pintar_sala($num_sala, $nombre_sala, $guia) {

    echo '<div>
                            <div class="text-left">
                                <p style="border-style: solid ; border-width: 1px ; margin: 5px ; display: inline">\'SALA '.$nombre_sala.'\'</p>
                                <button type="button" class="btn btn-default" style="background-color: #337ab7 ; color: white ; margin: 5px ; display: inline" data-toggle="modal" data-target="#addcontentguia'.$num_sala.'">AÑADIR RECURSO</button>
                            </div>
                            <div>';

                            $array_rec_sala = $this->consumidor->getRecursosGuiaEnSala($num_sala, $guia);
                            //var_dump($array_rec_sala);

                            $tam = $array_rec_sala->numresultados;
                            $mejor_prioridad = 99999;
                            $ultima_prioridad = -9999;
                            $indice_p = -1;
                            $i = 0;
                            while ($i < $tam) {

                              for ($j = 0; $j < $tam; $j++) {
                                $tmp = $array_rec_sala->content[$j];
                                if ((int)$tmp->prioridad <= $mejor_prioridad && (int)$tmp->prioridad > $ultima_prioridad) {
                                  $indice_p = $j;
                                  $mejor_prioridad = (int)$tmp->prioridad;
                                }
                              }

                              $mejor = $array_rec_sala->content[$indice_p];
                              $botonUp = true;
                              $botonDown = true;

                              if ($i == 0) {
                                $botonUp = false;
                              }
                              if ($i == $tam-1) {
                                $botonDown = false;
                              }

                              $this->pintar_recurso_sala($mejor->{"#recurso"}, $mejor->nombre, $mejor->codigo_qr, $mejor->{"#equipamiento"}, $mejor->prioridad, $botonUp, $botonDown);
                              $i++;
                              $ultima_prioridad = $mejor_prioridad;
                              $mejor_prioridad = 9999;
                            }

    echo '
                            </div>
                        </div>';

    echo '<div class="modal fade pg-show-modal" id="addcontentguia'.$num_sala.'" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Rellene los siguientes campos:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=addRecursoGuia" method="post">
                      <div class="modal-body">

                              <div id="informacion" style="text-align:left"> ';

                              $recursos = $this->consumidor->getRecursoSala($num_sala);

                              echo '<p style="text-align: left">Recurso:</p>
                              <select name="idrecurso">';

                              //<p style="text-align: left ; margin-top:10px">Id del recurso:<br><input type="text" pattern="\d+" name="idrecurso" value="" required></p>

                              $tam = $recursos->numresultados;

                              for($i=0; $i<$tam; $i++) {
                                $tmp = $recursos->content[$i];
                                echo '<option value="'.$tmp->{"#recurso"}.'">'.$tmp->nombre.'</option>';
                              }
                              echo '</select>
                                
                                <p style="text-align: left ; margin-top:10px">Prioridad:<br><input type="number" min="1" max="99999999999" name="prioridad" value="" required></p>
                                <p style="text-align: left"><br><input type="hidden" name="idguia" value="'.$this->guia->content[0]->{"#guia"}.'"></p>
                              </div>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                          <button type="submit" class="btn btn-primary">Añadir</button>
                      </div>
                    </form>
                </div>
            </div>
        </div>';
  }

  /**
  * Método encargado de pintar los recursos asociados a una guía que tiene una sala
  *
  * @param $id identificador del recurso
  * @param $nombre nombre del recurso
  * @param $qr código qr del recurso
  * @param $id_equip id del equipo al que pertenece dicho recurso
  * @param $prioridad prioridad u orden al que queremos acceder a dicho recurso cuando recorramos la guía
  * @param $botonUp booleano que permite al usuario acceder al botón "MOVER ARRIBA"
  * @param $botonDown booleano que permite al usuario acceder al botón "MOVER ABAJO"
  */
  function pintar_recurso_sala($id, $nombre, $qr, $id_equip, $prioridad, $botonUp, $botonDown) {

    echo '<div style="display: flex ; border-style: solid ; border-width: 1px ; border-color: grey ; margin: 5px ; padding: 5px">
                                    <div style="flex: 1 ; padding: 1em ; border-style: dotted ; border-width: 1px ; margin-left: 25px">
                                    <p style="margin: 0px ; text-align: left"> <b>Id</b>:'.$id.' </p>
                                      <p style="margin: 0px ; text-align: left"> <b>Nombre</b>:'.$nombre.' </p>
                                      <p style="margin: 0px ; text-align: left"> <b>Código QR numérico</b>:'.$qr.' </p>
                                      <p style="margin: 0px ; text-align: left"> <b>Equipamiento asociado</b>:'.$id_equip.' </p>
                                      <p style="margin: 0px ; text-align: left""> <b>Prioridad</b>: '.$prioridad.'</p>
                                    </div>
                                    <div style="flex: 0.75 ; margin-left: 10px"> ';

                                    if ($botonUp) {
                                      echo '<a href="consumer.php?boton=moverArribaContenido&idrecurso='.$id.'&idguia='.$this->guia->content[0]->{"#guia"}.'&prioridad='.$prioridad.'" style="text-decoration: none"> <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto">MOVER ARRIBA</button> </a>';
                                    }

                                    if ($botonDown) {
                                      echo '<a href="consumer.php?boton=moverAbajoContenido&idrecurso='.$id.'&idguia='.$this->guia->content[0]->{"#guia"}.'&prioridad='.$prioridad.'" style="text-decoration: none"> <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 2%">MOVER ABAJO</button> </a>';
                                    }

                                      echo '<button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 2%" data-toggle="modal" data-target="#deleterecursoguia'.$id.'">ELIMINAR</button> </a>
                                    </div>
                                </div>';

    echo '<div class="modal fade pg-show-modal" id="deleterecursoguia'.$id.'" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  </div>
                  <form enctype="multipart/form-data" action="consumer.php?boton=deleteRecursoGuia" method="post">
                    <div class="modal-body">

                            <div id="recurso">
                              <p style="text-align: left">¿Seguro que desea eliminar el recurso "'.$nombre.'" de la guía "'.$this->guia->content[0]->nombre_guia.'"?
                              <input type="hidden" name="idguia" value="'.$this->guia->content[0]->{"#guia"}.'">
                              <input type="hidden" name="idrecurso" value="'.$id.'">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Eliminar</button>
                    </div>
                  </form>
              </div>
          </div>
      </div>';

  }
}

/**
 * Clase encargada de gestionar la impresión de la sección de gestión de guías personalizadas
 */
class gestion_guias_personalizadas{

  var $consumidor = null;
  var $salas = null;
  var $guia = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  * @param $id identificador de la guía a visualizar
  */
  function gestion_guias_personalizadas($consumer, $id){
    $this->consumidor = $consumer;
    $this->salas = $this->consumidor->getAllSalas();
    $this->guia = $this->consumidor->getGuiaId($id);
    //var_dump($this->salas);
    //var_dump($this->guia);
  }

  /**
  * Método encargado de la visualización del contenido de la sección
  */
  function pintar(){
    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; background-color: white ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
                <h4 style="color: #337ab7">GESTIÓN DE GUÍAS</h4>
                <div style="flex: 1 ; border-style: solid ; border-width: 1px ; border-color: grey ; margin-right: 35px">
                    <div style="background-color: thistle">
                        <small style="color: white">'.$this->guia->content[0]->nombre_guia.'</small>
                        <br>
                    </div>
                    <div style="overflow-y: scroll ; height: 300px">
                        ';

                        $tamS = $this->salas->numresultados;

                        for ($i = 0; $i < $tamS; $i++) {
                          $tmp = $this->salas->content[$i];
                          $this->pintar_sala($tmp->n_sala);
                        }

    echo '

                    </div>
                    <div>

                    </div>
                </div>
                <div style="flex: 0.5">
                  <button type="button" class="btn btn-default" style="background-color: #337ab7 ; color: white ; margin: 10px" data-toggle="modal" data-target="#convertirPred'.$this->guia->content[0]->{"#guia"}.'">CONVERTIR EN PREDETERMINADA</button>                  <a href="index.php?page=gestion_guias_indice&user=admin" style="text-decoration:none"><button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px">VOLVER A GESTIÓN DE GUÍAS</button></a>
                  <button type="button" class="btn btn-default" style="background-color: #337ab7 ; color: white; margin-top: 10px" data-toggle="modal" data-target="#deleteguia'.$this->guia->content[0]->{"#guia"}.'">ELIMINAR GUÍA</button>
                </div>
            </div>
        </div>';

    echo '<div class="modal fade pg-show-modal" id="deleteguia'.$this->guia->content[0]->{"#guia"}.'" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  </div>
                  <form enctype="multipart/form-data" action="consumer.php?boton=deleteGuia" method="post">
                    <div class="modal-body">

                            <div id="recurso">
                              <p style="text-align: left">¿Seguro que desea eliminar la guia '.$this->guia->content[0]->{"#guia"}.', "'.$this->guia->content[0]->nombre_guia.'"?
                              <input type="hidden" name="id" value="'.$this->guia->content[0]->{"#guia"}.'">
                              <input type="hidden" name="tipo" value="personalizada">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Eliminar</button>
                    </div>
                  </form>
              </div>
          </div>
      </div>';

      echo '<div class="modal fade pg-show-modal" id="convertirPred'.$this->guia->content[0]->{"#guia"}.'" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  </div>
                  <form enctype="multipart/form-data" action="consumer.php?boton=convertirPersonalizada" method="post">
                    <div class="modal-body">

                            <div id="recurso">
                              <p style="text-align: left">¿Seguro que desea convertir la guía '.$this->guia->content[0]->{"#guia"}.', "'.$this->guia->content[0]->nombre_guia.'" en predefinida?
                              <input type="hidden" name="idguia" value="'.$this->guia->content[0]->{"#guia"}.'">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Convertir</button>
                    </div>
                  </form>
              </div>
          </div>
      </div>';
  }

  /**
  * Método encargado de pintar los recursos asociados a una guía de una sala
  *
  * @param $num_sala identificador de la sala a visualizar
  */
  function pintar_sala($num_sala) {
    echo '<div>
                            <div class="text-left">
                                <p style="border-style: solid ; border-width: 1px ; margin: 5px ; display: inline">\'SALA '.$num_sala.'\'</p>
                            </div>
                            <div>';

                            $array_rec_sala = $this->consumidor->getRecursosGuiaEnSala($num_sala, $this->guia->content[0]->{"#guia"});
                            //var_dump($array_rec_sala);
                            $tam = $array_rec_sala->numresultados;
                            for ($i = 0; $i < $tam; $i++) {
                              $tmp = $array_rec_sala->content[$i];
                              $this->pintar_recurso_sala($tmp->{"#recurso"}, $tmp->nombre, $tmp->codigo_qr, $tmp->{"#equipamiento"}, $tmp->prioridad);
                            }

                            echo '

                            </div>
                        </div>';
  }

  /**
  * Método encargado de visualizar los recursos que tiene asociados una sala
  *
  * @param $id identificador del recurso
  * @param $nombre nombre del recurso
  * @param $qr código qr del recurso
  * @param $id_equip identificador del equipo al que pertenece el recurso
  * @param $prioridad variable que indica el orden en el que se pintará el recurso con respecto a los demás
  */
  function pintar_recurso_sala($id, $nombre, $qr, $id_equip, $prioridad) {

    echo '<div style="display: flex ; border-style: solid ; border-width: 1px ; border-color: grey ; margin: 5px ; padding: 5px">
                                    <div style="flex: 1 ; padding: 1em ; border-style: dotted ; border-width: 1px ; margin-left: 25px">
                                    <p style="margin: 0px ; text-align: left"> <b>Id</b>:'.$id.' </p>
                                      <p style="margin: 0px ; text-align: left"> <b>Nombre</b>:'.$nombre.' </p>
                                      <p style="margin: 0px ; text-align: left"> <b>Código QR numérico</b>:'.$qr.' </p>
                                      <p style="margin: 0px ; text-align: left"> <b>Equipamiento asociado</b>:'.$id_equip.' </p>
                                      <p style="margin: 0px ; text-align: left""> <b>Prioridad</b>: '.$prioridad.'</p>
                                    </div>
                                    <div style="flex: 0.75 ; margin-left: 10px"> ';

                                      echo '<a href="consumer.php?boton=deleteInfoConcretaGuia&id_info='.$id.'&id_guia='.$this->guia->content[0]->{"#guia"}.'" style="text-decoration: none"> <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 2%">ELIMINAR</button> </a>
                                    </div>
                                </div>';



  }

}

/**
 * Clase encargada de gestionar la impresión de la sección de asistencias de un asistente
 */
class ver_asistencias{

  var $consumidor = null;
  var $array_asistencias = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  */
  function ver_asistencias($consumer){
    $this->consumidor = $consumer;
    $this->array_asistencias = $this->consumidor->getAsistenciasAsistente($_SESSION["consultaAsistenciasDni"]);
  }

  /**
  * Método encargado de la visualización del contenido de la clase
  */
  function pintar(){
    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
                <h4 style="color: #337ab7 ; margin-top: 5%">HISTORIAL DE ASISTENCIAS</h4>
                <div style="border-style: dotted ; border-width: 2px ; background-color: white ; overflow-y: scroll ; height: 300px ; margin-top: 1% ; margin-bottom: 1%">';
                for ($i = 0 ; $i < $this->array_asistencias->numresultados ; $i++){
                      $tmp = $this->array_asistencias->content[$i];
                echo '<div style="display: flex; border-style: solid ; border-width: 1px ; margin-top: 10px ; margin-bottom: 10px ; margin-left: 10px ; margin-right: 10px">';
                echo '<div style="flex: 0.5 ; margin-left: 25px">
                              <p style="text-align: left ; margin-top: 15px ; margin-left: 10px ; width: 50%"><b>Identificador de asistencia: </b>'.$tmp->{"#asistencia"}.'</p>
                            </div>
                        <div style="flex: 0.5 ; margin-left: 10px">
                          <a href="index.php?page=ver_datos_asistencia&user=admin&asistencia='.$tmp->{"#asistencia"}.'" style="text-decoration:none"><button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 1%; margin-bottom: 1%">VER DETALLES</button></a>
                      </div></div>';
                    }
                echo '</div>
                <a href="index.php?page=gestion_asis&user=admin" style="text-decoration:none"><button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px" data-toggle="modal" data-target="#addassistantmodal">VOLVER A GESTIÓN DE ASISTENTES</button></a>
        </div></div>';
  }
}

/**
 * Clase encargada de gestionar la impresión de los detalles de una asistencia
 */
class ver_datos_asistencia{

  var $consumidor = null;
  var $asistencia = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  */
  function ver_datos_asistencia($consumer){
    $this->consumidor = $consumer;
    $this->asistencia = $this->consumidor->getAsistenciaId($_GET["asistencia"]);
    //var_dump($this->asistencia);
  }

  /**
  * Método encargado de la visualización del contenido de la clase
  */
  function pintar(){
    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
                <h4 style="color: #337ab7 ; margin-top: 5%">DATOS DE ASISTENCIA '.$this->asistencia->content[0]->{"#asistencia"}.'</h4>';
                echo '<div style="border-style: solid ; border-width: 1px ; margin-top: 10px ; margin-bottom: 10px ; margin-left: 10px ; margin-right: 10px">';
                      echo '<p style="text-align: left ; margin-top: 10px ; margin-left: 10px ; width: 50%"><b>Identificador de asistencia: </b>'.$this->asistencia->content[0]->{"#asistencia"}.'</p>
                      <p style="text-align: left ; margin-top: 10px ; margin-left: 10px ; margin-bottom: 10px ; width: 50%"><b>Identificador de recurso: </b>'.$this->asistencia->content[0]->{"#recurso"}.'</p>
                      <p style="text-align: left ; margin-top: 10px ; margin-left: 10px ; margin-bottom: 10px ; width: 50%"><b>Hora de la asistencia: </b>'.$this->asistencia->content[0]->{"hora_asistencia"}.'</p>
                      <p style="text-align: left ; margin-top: 10px ; margin-left: 10px ; margin-bottom: 10px ; width: 50%"><b>Fecha de la asistencia: </b>'.$this->asistencia->content[0]->{"fecha_asistencia"}.'</p>
                      <p style="text-align: left ; margin-top: 10px ; margin-left: 10px ; margin-bottom: 10px ; width: 50%"><b>DNI: </b>'.$this->asistencia->content[0]->{"dni"}.'</p>
                      <p style="text-align: left ; margin-top: 10px ; margin-left: 10px ; margin-bottom: 10px ; width: 50%"><b>Hora de la petición: </b>'.$this->asistencia->content[0]->{"hora_peticion"}.'</p>
                      <p style="text-align: left ; margin-top: 10px ; margin-left: 10px ; margin-bottom: 10px ; width: 50%"><b>Fecha de la petición: </b>'.$this->asistencia->content[0]->{"fecha_peticion"}.'</p></div>';
                echo '<a href="index.php?page=ver_asistencias&user=admin" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 1% ; margin-bottom: 1%">VOLVER A HISTORIAL DE ASISTENCIAS</a>
                <a href="index.php?page=gestion_asis&user=admin" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 1% ; margin-bottom: 1%">VOLVER A GESTIÓN DE ASISTENTES</a></div>
        </div>';
  }
}

/**
 * Clase encargada de gestionar la impresión de la sección de gestión de contenidos
 */
class gestion_contenido{

  var $consumidor = null;
  var $array_recursos = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  */
  function gestion_contenido($consumer){
    $this->consumidor = $consumer;
    $this->array_recursos = $this->consumidor->getAllRecursos();
    //var_dump($this->array_recursos);
  }

  /**
  * Método encargado de la visualización del contenido de la clase
  */
  function pintar(){
    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; background-color: white ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
                <h4 style="color: #337ab7">GESTIÓN DE CONTENIDOS</h4>
                <button type="button" class="btn btn-default" style="background-color: #337ab7 ; color: white ; display: block ; margin: 0 auto ; margin-top: 5%" data-toggle="modal" data-target="#addContenido">AÑADIR NUEVO CONTENIDO</button> ';

                $tam = $this->array_recursos->numresultados;
                for ($i = 0; $i < $tam; $i++) {
                  $tmp = $this->array_recursos->content[$i];
                  $this->pintar_recurso($tmp->nombre, $tmp->{"#recurso"});
                }
    echo '
                <a href="index.php?page=ver_contenido&user=admin" class="btn btn-default" style="background-color: #337ab7 ; color: white ; display: block ; margin: 0 auto ; margin-top: 2%; margin-bottom: 5%">VER TODOS LOS CONTENIDOS</a>
            </div>
        </div>';

        echo '<div class="modal fade pg-show-modal" id="addContenido" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Rellene los siguientes campos:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=addContenido" method="post">
                    <div class="modal-body">
                        <p>Seleccione el formato del contenido:</p>
                            <label class="control-label" style="margin-right: 2%" data-pg-collapsed="">
                                <input type="radio" name="formato" value="video" checked> Vídeo
                            </label>
                            <label class="control-label" style="margin-right: 2%" data-pg-collapsed="">
                                <input type="radio" name="formato" value="texto" > Texto
                            </label>
                            <label class="control-label" style="margin-right: 2%" data-pg-collapsed="">
                                <input type="radio" name="formato" value="sub" > Subtítulos
                            </label>
                            <label class="control-label" style="margin-right: 2%" data-pg-collapsed="">
                                <input type="radio" name="formato" value="sign" > Lenguaje de signos
                            </label>
                            <label class="control-label" style="margin-right: 2%" data-pg-collapsed="">
                                <input type="radio" name="formato" value="audio" > Audio descripción
                            </label>

                            <div id="subir_contenido">
                              <p style="text-align:left">Nombre del contenido:<br><input type="text" name="nombre" value="" required></p> ';

                              $idiomas = $this->consumidor->getIdiomas();
        echo '
                              <p style="text-align:left">Idioma del contenido:</p>
                              <select name="idioma">';

                              $tam_idiomas = $idiomas->numresultados;

                              for ($i=0; $i<$tam_idiomas; $i++) {
                                $tmp = $idiomas->content[$i];
                                echo '<option value="'.$tmp->codigo_idioma.'">'.$tmp->nombre.'</option>';
                              }
                                
                              echo '</select>';
                              //<p style="text-align:left">Idioma del contenido:<br><input type="text" pattern="[a-zA-Z]*" maxlength="2" name="idioma" value="" required></p>
        
                              //<p style="text-align:left ; margin-top:10px">Id del recurso asociado:<br><input type="text" pattern="\d+" name="id_recurso" value="" required></p>

                              echo '<p style="text-align:left ; margin-top:10px">Recurso al que pertenece:</p>
                              <select name="id_recurso"';

                              $tam_recursos = $this->array_recursos->numresultados;

                              for ($i=0; $i<$tam_recursos; $i++) {
                                $tmp = $this->array_recursos->content[$i];
                                echo '<option value="'.$tmp->{"#recurso"}.'">'.$tmp->nombre.'</option>';
                              }
                              echo '</select>';
        echo '
                              <p style="text-align:left ; margin-top:10px">Información del texto (si se marcó Texto):<br><input type="text" name="info" value="" size="50"></p>
                              <p style="text-align:left">URL (si lo tiene):<br><input type="text" name="url" value=""></p>
                              <p style="text-align:left">Fichero:<br><input class="btn btn-default" type="file" name="file" id="file" value=""></p>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Añadir</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>';
  }

  /**
  * Método encargado de pintar el nombre de un recurso para acceder a sus contenidos
  *
  * @param $nombre nombre del recurso
  * @param $id identificador del recurso
  */
  function pintar_recurso($nombre, $id) {
    echo '<a href="index.php?page=ver_contenido&user=admin&id_rec='.$id.'" class="btn btn-default" style="background-color: #337ab7 ; color: white ; display: block ; margin: 0 auto ; margin-top: 2%">VER LOS CONTENIDOS DEL RECURSO '.$id.': '.$nombre.'</a>';
  }
}

/**
 * Clase encargada de gestionar la impresión de los contenidos de un recurso
 */
class ver_contenido{

  var $consumidor = null;
  var $array_contenido = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  * @param $recurso identificador del recurso cuyos contenidos queremos visualizar
  * @param $searchNombreContenido variable usada para realizar filtrados de búsqueda de contenidos por su nombre
  */
  function ver_contenido($consumer, $recurso, $searchNombreContenido)
  {

    $this->consumidor = $consumer;
    if(strcmp($searchNombreContenido, "all") == 0 || strcmp($searchNombreContenido, "") == 0){
      if (strcmp($recurso, "all") == 0) {
        $this->array_contenido = $this->consumidor->getAllContenido();
      }
      else {
        $array_rec = $this->consumidor->getAllRecursos();

        $encontrada = false;
        $i = 0;
        $tam = $array_rec->numresultados;

        while (!$encontrada && $i < $tam) {
          $tmp = $array_rec->content[$i];

          if (strcmp($tmp->{"#recurso"}, $recurso) == 0)
            $encontrada = true;
          else
            $i++;
        }

        if ($encontrada)
          $this->array_contenido = $this->consumidor->getAllContenidoRecurso($recurso);
      }
    }
    else{
      $this->array_contenido = $this->consumidor->getContenidoNombre($searchNombreContenido);
      //var_dump($this->array_contenido);
    }

    //var_dump($this->array_contenido);

  }

  /**
  * Método encargado de la visualización del contenido de la clase
  */
  function pintar(){

    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; background-color: white ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
          <h4 style="color: #337ab7">GESTIÓN DE CONTENIDO</h4>
          <div style="flex: 1 ; border-style: solid ; border-width: 1px ; border-color: grey ; margin-right: 35px">

              <div style="overflow-y: scroll ; height: 300px">
                  ';

                  $tam_text = $this->array_contenido->numresultadostexto;
                  $tam_multi = $this->array_contenido->numresultadosmultimedia;

                  for ($i = 0; $i < $tam_text; $i++) {
                      $tmp = $this->array_contenido->contenttexto[$i];
                      $this->pintar_contenido_texto($tmp->{"#contenido"}, $tmp->{"#idioma"}, $tmp->{"#recurso"}, $tmp->nombre, $tmp->informacion_texto);
                    }

                    for ($i = 0; $i < $tam_multi; $i++) {
                      $tmp = $this->array_contenido->contentmultimedia[$i];
                      $this->pintar_contenido_multimedia($tmp->{"#contenido"}, $tmp->{"#idioma"}, $tmp->{"#recurso"}, $tmp->nombre, $tmp->formato, $tmp->url);
                    }

                  echo '</div>
          </div>
          <button type="button" class="btn btn-default" style="background-color: #337ab7 ; color: white ; display: block ; margin: 0 auto ; margin-top: 5%" data-toggle="modal" data-target="#buscarContenido">BUSCAR CONTENIDO</button>
          <a href="index.php?page=gestion_contenido&user=admin" style="text-decoration:none"><button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px">VOLVER A GESTIÓN DE CONTENIDO</button></a>
          <a href="index.php?page=gestion_recursos&user=admin" style="text-decoration:none"><button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px">IR A GESTIÓN DE RECURSOS</button></a></div>

          <div class="modal fade pg-show-modal" id="buscarContenido" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 class="modal-title"><b>Rellene los siguientes campos:</b></h4>
                      </div>
                      <form enctype="multipart/form-data" action="consumer.php?boton=searchContenido" method="post">
                      <div class="modal-body">
                          <p>Nombre del contenido:<br><input type="text" name="searchNombreContenido" value="'.$_SESSION['searchNombreContenido'].'"></p>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                          <button type="submit" class="btn btn-primary">Buscar</button>
                      </div>
                      </form>
                  </div>
              </div>
          </div>

        <div class="modal fade pg-show-modal" id="addContenido" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Rellene los siguientes campos:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=addContenido" method="post">
                    <div class="modal-body">
                        <p>Seleccione el formato del contenido:</p>
                            <label class="control-label" style="margin-right: 2%" data-pg-collapsed="">
                                <input type="radio" name="formato" value="video" checked> Vídeo
                            </label>
                            <label class="control-label" style="margin-right: 2%" data-pg-collapsed="">
                                <input type="radio" name="formato" value="texto" > Texto
                            </label>
                            <label class="control-label" style="margin-right: 2%" data-pg-collapsed="">
                                <input type="radio" name="formato" value="sub" > Subtítulos
                            </label>
                            <label class="control-label" style="margin-right: 2%" data-pg-collapsed="">
                                <input type="radio" name="formato" value="sign" > Lenguaje de signos
                            </label>
                            <label class="control-label" style="margin-right: 2%" data-pg-collapsed="">
                                <input type="radio" name="formato" value="audio" > Audio descripción
                            </label>

                            <div id="subir_contenido">
                              <p style="text-align:left">Nombre del contenido:<br><input type="text" name="nombre" value=""></p>
                              <p style="text-align:left">Id del contenido:<br><input type="text" name="id_informacion" value=""></p>
                              <p style="text-align:left">Idioma del contenido:<br><input type="text" name="idioma" value=""></p>
                              <p style="text-align:left">Id recurso asociado:<br><input type="text" name="id_recurso" value=""></p>
                              <p style="text-align:left">Información del texto (si se marcó Texto):<br><input type="text" name="info" value="" size="50"></p>
                              <p style="text-align:left">URL (si lo tiene):<br><input type="text" name="url" value=""></p>
                              <p style="text-align:left">Fichero:<br><input class="btn btn-default" type="file" name="file" id="file" value=""></p>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

      </div>
    </div>';
  }

  /**
  * Método encargado de pintar el contenido de tipo multimedia
  *
  * @param $id identificador del contenido
  * @param $idioma idioma del contenido
  * @param $recurso identificador del recurso al que pertenece
  * @param $nombre nombre del contenido
  * @param $formato formato del contenido
  * @param $info descripción del contenido
  */
  function pintar_contenido_multimedia($id, $idioma, $recurso, $nombre, $formato, $info) {
    $nombre_idioma = $this->consumidor->getIdiomaId($idioma);
    $nombre_recurso = $this->consumidor->getRecursoId($recurso);

    echo '<div style="border-style: solid ; border-width: 1px ; border-color: grey ; margin: 5px">
                      <div style="background-color: #337ab7">
                          <small style="color: white">'.$id.'</small>
                      </div>
                      <div style="display: flex">

                          <div style="flex: 1 ; padding: 1em ; border-style: dotted ; border-width: 1px ; margin: 5px ; margin-left: 25px">
                            <p style="margin: 0px ; text-align: left"> <b>Nombre</b>: ' .$nombre. '</p>
                            <p style="margin: 0px ; text-align: left""> <b>Idioma</b>: ' .$nombre_idioma->content[0]->nombre. '</p>
                            <p style="margin: 0px ; text-align: left""> <b>Recurso al que pertenece</b>: ' .$nombre_recurso->content[0]->nombre. '</p>
                            <p style="margin: 0px ; text-align: left""> <b>Formato</b>: ' .$formato. '</p>
                            <p style="margin: 0px ; text-align: left""> <b>Url</b>: ' .$info. '</p>
                          </div>

                          <div style="flex: 0.75">
                              <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 5%"  data-toggle="modal" data-target="#modificarContentMultimedia'.$id.'">MODIFICAR</button>
                              <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 5% ; margin-bottom: 2%" data-toggle="modal" data-target="#deleteContentMultimedia'.$id.'">ELIMINAR</button>
                          </div>
                      </div>
                  </div>';

                  echo '<div class="modal fade pg-show-modal" id="modificarContentMultimedia'.$id.'" tabindex="-1" role="dialog" aria-hidden="true" style="text-align:left">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Efectúe cambios en los campos a modificar:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=updateContenidoMultimedia" method="post">
                    <div class="modal-body">
                        <div id="subir_contenido">
                              <p>Nombre del contenido:<br><input type="text" name="nombre" value="'.$nombre.'"></p>';

                              $idiomas = $this->consumidor->getIdiomas();
        echo '
                              <p style="text-align:left">Idioma del contenido:</p>
                              <select name="idioma">';

                              //<p>Idioma del contenido (2 caracteres):<br><input type="text" pattern="[a-zA-Z]*" maxlength="2" name="idioma" value="'.$idioma.'"></p>
                              
                              $tam_idiomas = $idiomas->numresultados;

                              for ($i=0; $i<$tam_idiomas; $i++) {
                                $tmp = $idiomas->content[$i];
                                if (strcmp($idioma, $tmp->{"#idioma"}) == 0) 
                                  echo '<option selected value="'.$tmp->{"#idioma"}.'">'.$tmp->nombre.'</option>';
                                else
                                  echo '<option value="'.$tmp->{"#idioma"}.'">'.$tmp->nombre.'</option>';
                              }
                                
                              echo '</select>';
                              echo '
                              
                              <p style="margin-top:10px">URL:<br><input type="text" name="url" value="'.$info.'"></p>
                              <input type="hidden" name="id_antiguo" value="'.$id.'">
                              <input type="hidden" name="recurso" value="'.$recurso.'">
                              <input type="hidden" name="formato" value="'.$formato.'">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade pg-show-modal" id="deleteContentMultimedia'.$id.'" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  </div>
                  <form enctype="multipart/form-data" action="consumer.php?boton=deleteContenido" method="post">
                    <div class="modal-body">

                            <div id="contenido">
                              <p style="text-align: left">¿Seguro que desea eliminar el contenido '.$nombre.'?
                              <input type="hidden" name="idcontenido" value="'.$id.'">
                              <input type="hidden" name="formatocontenido" value="'.$formato.'">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Eliminar</button>
                    </div>
                  </form>
              </div>
          </div>
      </div>';

  }

  /**
  * Método encargado de pintar el contenido de tipo texto
  *
  * @param $id identificador del contenido
  * @param $idioma idioma del contenido
  * @param $recurso identificador del recurso al que pertenece
  * @param $nombre nombre del contenido
  * @param $info descripción del contenido
  */
  function pintar_contenido_texto($id, $idioma, $recurso, $nombre, $info) {
    $nombre_idioma = $this->consumidor->getIdiomaId($idioma);
    $nombre_recurso = $this->consumidor->getRecursoId($recurso);

    echo '<div style="border-style: solid ; border-width: 1px ; border-color: grey ; margin: 5px">
                      <div style="background-color: #337ab7">
                          <small style="color: white">'.$id.'</small>
                      </div>
                      <div style="display: flex">

                          <div style="flex: 1 ; padding: 1em ; border-style: dotted ; border-width: 1px ; margin: 5px ; margin-left: 25px">
                            <p style="margin: 0px ; text-align: left"> <b>Nombre</b>: ' .$nombre. '</p>
                            <p style="margin: 0px ; text-align: left""> <b>Idioma</b>: ' .$nombre_idioma->content[0]->nombre. '</p>
                            <p style="margin: 0px ; text-align: left""> <b>Recurso al que pertenece</b>: ' .$nombre_recurso->content[0]->nombre. '</p>
                            <p style="margin: 0px ; text-align: left""> <b>Formato</b>: texto</p>
                            <p style="margin: 0px ; text-align: left""> <b>Información de texto</b>: ' .$info. '</p>

                          </div>

                          <div style="flex: 0.75">
                              <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 5%"  data-toggle="modal" data-target="#modificarContentTexto'.$id.'" ">MODIFICAR</button>
                              <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 5% ; margin-bottom: 2%" data-toggle="modal" data-target="#deleteContentTexto'.$id.'">ELIMINAR</button>
                          </div>
                      </div>
                  </div>';

                  echo '<div class="modal fade pg-show-modal" id="modificarContentTexto'.$id.'" tabindex="-1" role="dialog" aria-hidden="true" style="text-align:left">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Efectúe cambios en los campos a modificar:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=updateContenidoTexto" method="post">
                    <div class="modal-body">

                            <div id="subir_contenido">
                              <p>Nombre del contenido:<br><input type="text" name="nombre" value="'.$nombre.'"></p>
                              <p>Idioma del contenido:</p>';

                              echo '<select name=idioma>"';
                              $idiomas = $this->consumidor->getIdiomas();
                              $tam_idiomas = $idiomas->numresultados;

                              for ($i=0; $i<$tam_idiomas; $i++) {
                                $tmp = $idiomas->content[$i];
                                if (strcmp($idioma, $tmp->{"#idioma"}) == 0)
                                  echo '<option selected value="'.$tmp->{"#idioma"}.'">'.$tmp->nombre.'</option>';
                                else
                                  echo '<option value="'.$tmp->{"#idioma"}.'">'.$tmp->nombre.'</option>';
                              }
                                
                              echo '</select>';
                              echo '
                              <p style="margin-top:10px">Información del texto:<br><input type="text" name="info" value="'.$info.'" size="50"></p>
                              <input type="hidden" name="id_antiguo" value="'.$id.'">
                              <input type="hidden" name="recurso" value="'.$recurso.'">
                              <input type="hidden" name="formato" value="texto">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade pg-show-modal" id="deleteContentTexto'.$id.'" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  </div>
                  <form enctype="multipart/form-data" action="consumer.php?boton=deleteContenido" method="post">
                    <div class="modal-body">

                            <div id="contenido">
                              <p style="text-align: left">¿Seguro que desea eliminar el contenido '.$nombre.'?
                              <input type="hidden" name="idcontenido" value="'.$id.'">
                              <input type="hidden" name="formatocontenido" value="texto">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Eliminar</button>
                    </div>
                  </form>
              </div>
          </div>
      </div>';

  }
}


/**
 * Clase encargada de gestionar la impresión de la sección de gestión de salas
 */
class gestion_salas{

  var $consumidor = null;
  var $array_salas = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  */
  function gestion_salas($consumer){
    $this->consumidor = $consumer;
    $this->array_salas = $this->consumidor->getAllSalas();
    //var_dump($this->array_salas);
  }

  /**
  * Método encargado de la visualización del contenido de la clase
  *
  * @param $searchNombreSala variable usada para realizar filtrados de búsqueda de salas por su nombre
  */
  function pintar($searchNombreSala){
    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; background-color: white ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
                <h4 style="color: #337ab7">GESTIÓN DE SALAS</h4>
                <div style="flex: 1 ; border-style: solid ; border-width: 1px ; border-color: grey ; margin-right: 35px">
                    <div style="background-color: thistle">
                        <small style="color: white">SALAS</small>
                        <br>
                    </div>
                    <div style="overflow-y: scroll ; height: 300px">
                        ';
                        
                        if((strcmp($searchNombreSala, "all") == 0 || strcmp($searchNombreSala, "") == 0)){
                          $tam = $this->array_salas->numresultados;
                          for ($i = 0; $i < $tam; $i++) {
                            $tmp = $this->array_salas->content[$i];
                            $this->pintar_sala($tmp->{"#sala"}, $tmp->n_sala, $tmp->descripcion_sala, $tmp->planta);
                          }
                        }
                        else{
                          $busqueda = $this->consumidor->getSalasNombre($searchNombreSala);
                          for ($i = 0; $i < sizeof($busqueda->content); $i++) {
                            $tmp = $busqueda->content[$i];
                            $this->pintar_sala($tmp->{"#sala"}, $tmp->n_sala, $tmp->descripcion_sala, $tmp->planta);
                          }
                        }

    echo '
                    </div>
                </div>
                <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; margin: 5px; display: block; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px" data-toggle="modal" data-target="#addsalamodal">AÑADIR NUEVA SALA</button>
                <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; margin: 5px; display: block; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px" data-toggle="modal" data-target="#buscarSala">BUSCAR SALA</button>
            </div>
        </div>
        <div class="modal fade pg-show-modal" id="addsalamodal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Rellene los siguientes campos:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=addSala" method="post">
                    <div class="modal-body">
                        <p>Nombre:<br><input type="text" name="n_sala" value="" required></p>
                        <p>Descripción de la sala:<br><input type="text" value="" name="descripcionsala" size="50" required></p>
                        <p>Planta de la sala:<br><input type="number" min="0" max="99999999999" name="planta" value="" required></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Añadir</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade pg-show-modal" id="buscarSala" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Rellene los siguientes campos:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=searchSala" method="post">
                    <div class="modal-body">
                        <p>Nombre de la sala:<br><input type="text" name="searchNombreSala" value=""></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>';
  }

  /**
  * Método encargado de pintar los detalles de una sala
  *
  * @param $id identificador de la sala
  * @param $nombre nombre de la sala
  * @param $desc descripción de la sala
  * @param $planta planta o nivel en la que se encuentra la sala
  */
  function pintar_sala($id, $nombre, $desc, $planta) {
    echo '<div>
            <div class="text-left"></div>
            <div>
                <div style="border-style: solid ; border-width: 1px ; border-color: grey ; margin: 5px">
                    <div style="background-color: #337ab7">
                      <small style="color: white">SALA '.$id.'</small>
                    </div>

                    <div style="display: flex">

                      <div style="flex: 1 ; padding: 1em ; border-style: dotted ; border-width: 1px ; margin: 5px">
                        <p style="margin: 2px ; text-align: left"><b>Nombre: </b>'.$nombre.'</p>
                        <p style="margin: 2px ; text-align: left"><b>Descripción: </b>'.$desc.'</p>
                        <p style="margin: 2px ; text-align: left"><b>Planta: </b>'.$planta.'</p>
                      </div>

                      <div style="flex: 0.75">
                          <a href="index.php?page=ver_info&user=admin&sala='.$id.'" style="text-decoration:none"><button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 2%">GESTIONAR EQUIPAMIENTOS</button></a>
                          <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 2%" ; data-toggle="modal" data-target="#modificarSala'.$id.'">MODIFICAR</button>
                          <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 2% ; margin-bottom: 2%" data-toggle="modal" data-target="#deletesala'.$id.'">ELIMINAR SALA</button> </a>
                      </div>

                    </div>
                  </div>
                </div>
            </div>';

    echo '<div class="modal fade pg-show-modal" id="modificarSala'.$id.'" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Efectúe cambios en los campos a modificar:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=updateSala" method="post">
                    <div class="modal-body">
                        <p style="margin: 0px ; text-align: left">Nombre:<br><input type="text" name="n_sala" value="'.$nombre.'"></p>
                        <p style="margin: 0px ; text-align: left">Descripción de la sala:<br><input type="text" value="'.$desc.'" name="descripcion_sala" size="50"></p>
                        <p style="margin: 0px ; text-align: left">Planta de la sala:<br><input type="number" min="0" max="99999999999" name="planta" value="'.$planta.'"></p>
                        <input type="hidden" name="id_sala_modificar" value="'.$id.'">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade pg-show-modal" id="deletesala'.$id.'" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  </div>
                  <form enctype="multipart/form-data" action="consumer.php?boton=deleteSala" method="post">
                    <div class="modal-body">

                            <div id="recurso">
                              <p style="text-align: left">¿Seguro que desea eliminar la sala '.$id.', "'.$nombre.'"? El equipamiento que contiene se trasladará a la sala que indique en el siguiente campo:
                              <p style="margin: 0px ; text-align: left">Sala destino del equipamiento: ';

                              //<br><input type="number" min="1" max="99999999999" name="id_sala_traspasar" value=""></p>
                              echo '<select name="id_sala_traspasar">';
                              $tam = $this->array_salas->numresultados;

                              for($i=0; $i<$tam; $i++) {
                                $tmp = $this->array_salas->content[$i];
                                if (strcmp($tmp->{"#sala"}, $id) != 0) {
                                  echo '<option value="'.$tmp->{"#sala"}.'">'.$tmp->n_sala.'</option>';
                                }
                              }
                              echo '</select>';
                              echo '
                              </p><input type="hidden" name="id_sala_eliminar" value="'.$id.'">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Eliminar</button>
                    </div>
                  </form>
              </div>
          </div>
      </div>';


  }

}

/**
 * Clase encargada de gestionar la impresión de la sección de gestión de recursos
 */
class gestion_recursos{

  var $consumidor = null;
  var $array_equipo = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  */
  function gestion_recursos($consumer){
    $this->consumidor = $consumer;
    $this->array_equipo = $this->consumidor->getAllEquipamiento();
    //var_dump($this->array_equipo);
  }

  /**
  * Método encargado de la visualización del contenido de la clase
  */
  function pintar(){
    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; background-color: white ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
                <h4 style="color: #337ab7">GESTIÓN DE RECURSOS</h4>
                <button type="button" class="btn btn-default" style="background-color: #337ab7 ; color: white ; display: block ; margin: 0 auto ; margin-top: 5%" data-toggle="modal" data-target="#addcontentmodal2">AÑADIR NUEVO RECURSO</button> ';

                $tam = $this->array_equipo->numresultados;
                for ($i = 0; $i < $tam; $i++) {
                  $tmp = $this->array_equipo->content[$i];
                  $this->pintar_equipo($tmp->nombre, $tmp->{"#equipamiento"});
                }
    echo '
                <a href="index.php?page=ver_recursos&user=admin" class="btn btn-default" style="background-color: #337ab7 ; color: white ; display: block ; margin: 0 auto ; margin-top: 2%; margin-bottom: 5%">VER TODOS LOS RECURSOS</a>
            </div>
        </div>
        <div class="modal fade pg-show-modal" id="addcontentmodal2" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><b>Rellene los siguientes campos:</b></h4>
                    </div>
                    <form enctype="multipart/form-data" action="consumer.php?boton=addRecurso" method="post">
                      <div class="modal-body">

                              <div id="recurso">
                                <p style="text-align: left">Nombre:<br><input type="text" name="nombre" value="" required></p> ';

                                echo '<p style="text-align: left">Id del equipamiento al que pertenece:</p>
                                <select name="idequipo">';
                                $tam = $this->array_equipo->numresultados;
                                for ($i=0; $i<$tam; $i++) {
                                  $tmp = $this->array_equipo->content[$i];
                                  echo '<option value="'.$tmp->{"#equipamiento"}.'">'.$tmp->nombre.'</option>';
                                }
                                echo '</select>';
                                //<p style="text-align: left">Id del equipamiento al que pertenece:<br><input type="text" pattern="\d+" name="idequipo" value="" required></p>
                                echo '
                                <p style="text-align: left ; margin-top:10px">Código QR numérico:<br><input type="text" pattern="\d+" name="codigoqr" value="" required></p>
                                <p style="text-align: left">Descripción imagen:<br><input type="text" name="desc_img" value="" required></p>
                                <p style="text-align: left">Fichero:<br><input class="btn btn-default" type="file" name="file" id="file" value="" required></p>
                              </div>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-primary">Añadir</button>
                      </div>
                    </form>
                </div>
            </div>
        </div>';
  }

  /**
  * Método encargado de pintar el nombre de los distintos equipamientos para poder acceder a sus recursos
  *
  * @param $nombre nombre del equipo
  * @param $id identificador del equipo
  */
  function pintar_equipo($nombre, $id) {
    echo '<a href="index.php?page=ver_recursos&user=admin&id_equip='.$id.'" class="btn btn-default" style="background-color: #337ab7 ; color: white ; display: block ; margin: 0 auto ; margin-top: 2%">VER LOS RECURSOS DEL EQUIPAMIENTO '.$id.': '.$nombre.'</a>';
  }
}

/**
 * Clase encargada de gestionar la impresión de recursos
 */
class ver_recursos {
  var $consumidor = null;
  var $array_recursos = null;

  /**
  * Constructor
  *
  * @param $consumer instancia del consumidor
  * @param $equipo identificador del equipo al que pertenece
  * @param $searchNombreRecurso variable usada para realizar filtrado de búsqueda de recursos por su nombre
  */
  function ver_recursos($consumer, $equipo, $searchNombreRecurso) {
    $this->consumidor = $consumer;

    if(strcmp($searchNombreRecurso, "all") == 0 || strcmp($searchNombreRecurso, "") == 0){
      if (strcmp($equipo, "all") == 0) {
        $this->array_recursos = $this->consumidor->getAllRecursos();
      }
      else {
        $array_equip = $this->consumidor->getAllEquipamiento();

        $encontrada = false;
        $i = 0;
        $tam = $array_equip->numresultados;

        while (!$encontrada && $i < $tam) {
          $tmp = $array_equip->content[$i];

          if (strcmp($tmp->{"#equipamiento"}, $equipo) == 0)
            $encontrada = true;
          else
            $i++;
        }

        if ($encontrada)
          $this->array_recursos = $this->consumidor->getRecursosEnEquipamiento($equipo);
      }
    }
    else{
      $this->array_recursos = $this->consumidor->getRecursoNombre($searchNombreRecurso);
    }
    //var_dump($this->array_recursos);
  }

  /**
  * Método encargado de la visualización del contenido de la clase
  */
  function pintar() {
    echo '<div class="text-center" style="flex: 1 ; padding: 1em ; background-color: white ; border-style: solid ; border-width: 10px ; border-color: ghostwhite">
          <h4 style="color: #337ab7">GESTIÓN DE RECURSOS</h4>
          <div style="flex: 1 ; border-style: solid ; border-width: 1px ; border-color: grey ; margin-right: 35px">

              <div style="overflow-y: scroll ; height: 300px">
                  ';


                  $tam = $this->array_recursos->numresultados;

                  for ($i = 0; $i < $tam; $i++) {
                    $tmp = $this->array_recursos->content[$i];
                    $this->pintar_recurso($tmp->{"#recurso"}, $tmp->nombre, $tmp->codigo_qr, $tmp->img_qr,$tmp->fecha_creacion, $tmp->ultima_fecha_modificacion, $tmp->{"#equipamiento"}, $tmp->desc_img, $tmp->img);
                  }

    echo '</div>
          </div>
          <button type="button" class="btn btn-default" style="background-color: #337ab7 ; color: white ; display: block ; margin: 0 auto ; margin-top: 5%" data-toggle="modal" data-target="#buscarRecurso">BUSCAR RECURSO</button>
          <a href="index.php?page=gestion_recursos&user=admin" style="text-decoration:none"><button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px" data-toggle="modal" data-target="#addassistantmodal">VOLVER A GESTIÓN DE RECURSOS</button></a>
          <a href="index.php?page=gestion_info&user=admin" style="text-decoration:none"><button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 10px ; margin-bottom: 10px" data-toggle="modal" data-target="#addassistantmodal">IR A GESTIÓN DE EQUIPAMIENTOS</button></a></div>

          <div class="modal fade pg-show-modal" id="buscarRecurso" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 class="modal-title"><b>Rellene los siguientes campos:</b></h4>
                      </div>
                      <form enctype="multipart/form-data" action="consumer.php?boton=searchRecurso" method="post">
                      <div class="modal-body">
                          <p>Nombre del recurso:<br><input type="text" name="searchNombreRecurso" value="'.$_SESSION["searchNombreRecurso"].'"></p>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                          <button type="submit" class="btn btn-primary">Buscar</button>
                      </div>
                      </form>
                  </div>
              </div>
          </div>

      </div>
    </div>';
  }

  /**
  * Método encargado de pintar los detalles de un recurso
  *
  * @param $id identificador del recurso
  * @param $nombre nombre del recurso
  * @param $codigo_qr código numérico del qr del recurso
  * @param $imagen_qr imagen qr del recurso
  * @param $fecha_creacion fecha en la cual fue creado el recurso
  * @param $fecha_modificacion fecha en la cual el recurso fue modificado por última vez
  * @param $info equipamiento al que pertenece
  * @param $desc_img descripción de la imagen asociada al recurso
  * @param $img imagen asociada al recurso
  */
  function pintar_recurso($id, $nombre, $codigo_qr, $imagen_qr, $fecha_creacion, $fecha_modificacion, $info, $desc_img, $img) {
    $contenidos_asoc = $this->consumidor->getAllContenidoRecurso($id);
    $nombre_equipamiento = $this->consumidor->getNombreEquipamiento($info);
    //var_dump($contenidos_asoc);

    echo '<div style="border-style: solid ; border-width: 1px ; border-color: grey ; margin: 5px">
                      <div style="background-color: #337ab7">
                          <small style="color: white">ID: '.$id.'</small>
                      </div>
                      <div style="display: flex">
                          <img src='.$imagen_qr.' style="" height="125" width="125">

                          <div style="flex: 1 ; padding: 1em ; border-style: dotted ; border-width: 1px ; margin: 5px ; margin-left: 25px">
                            <p style="margin: 0px ; text-align: left""> <b>Nombre</b>: '.$nombre.'</p>
                            <p style="margin: 0px ; text-align: left""> <b>Código QR numérico</b>: '.$codigo_qr.'</p>
                            <p style="margin: 0px ; text-align: left""> <b>Fecha creación</b>: '.$fecha_creacion.'</p>
                            <p style="margin: 0px ; text-align: left""> <b>Última fecha modificación</b>: '.$fecha_modificacion.'</p>
                            <p style="margin: 0px ; text-align: left""> <b>Pertenece a</b>: '.$nombre_equipamiento->content[0]->nombre.'</p>
                            <p style="margin: 0px ; text-align: left""> <b>Contenidos asociados</b>: ' ;


                            $tamM = $contenidos_asoc->numresultadosmultimedia;
                            for ($i = 0; $i < $tamM; $i++) {
                              $tmp = $contenidos_asoc->contentmultimedia[$i];
                              $nombre_contenido = $this->consumidor->getContenidoId($tmp->{"#contenido"})->content[0]->nombre;
                              $nombre_idioma = $this->consumidor->getIdiomaId($tmp->{"#idioma"})->content[0]->nombre;
                              echo '<p></p> ['.$nombre_contenido.':'.$nombre_idioma.'] ';
                            }

                            $tamT = $contenidos_asoc->numresultadostexto;
                            for ($i = 0; $i < $tamT; $i++) {
                              $tmp = $contenidos_asoc->contenttexto[$i];
                              $nombre_contenido = $this->consumidor->getContenidoId($tmp->{"#contenido"})->content[0]->nombre;
                              $nombre_idioma = $this->consumidor->getIdiomaId($tmp->{"#idioma"})->content[0]->nombre;
                              echo '<p></p> ['.$nombre_contenido.':'.$nombre_idioma.'] ';
                            }
    echo                    '<p style="margin: 0px ; text-align: left ; margin-top:10px"> <b>Descripción de imagen: </b> '.$desc_img.'</p>
                            <p style="margin: 2px"> <b>Imagen:</b> </p>
                            <img src='.$img.' style="" height="150">';
    echo '</p>
                          </div>

                          <div style="flex: 0.75">
                              <a href="index.php?page=ver_contenido&user=admin&id_rec='.$id.'" style="text-decoration: none"><button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 5%">GESTIONAR CONTENIDOS</button></a>
                              <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 5%" data-toggle="modal" data-target="#updateRecurso'.$id.'">MODIFICAR</button>
                              <button type="button" class="btn btn-default" style="color: white ; background-color: #337ab7 ; display: block ; margin: 0 auto ; margin-top: 5% ; margin-bottom: 2%" data-toggle="modal" data-target="#deleteRecurso'.$id.'">ELIMINAR</button>
                          </div>
                      </div>
                  </div>';

        echo '<div class="modal fade pg-show-modal" id="updateRecurso'.$id.'" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      <h4 class="modal-title"><b>Rellene los siguientes campos:</b></h4>
                  </div>
                  <form enctype="multipart/form-data" action="consumer.php?boton=updateRecurso" method="post">
                    <div class="modal-body">

                            <div id="recurso">
                              <p style="text-align: left">Nombre:<br><input type="text" name="nombre" value="'.$nombre.'"></p>
                              <p style="text-align: left">Código QR numérico:<br><input type="text" pattern="\d+" name="codigoqr" value="'.$codigo_qr.'"></p>
                              <p style="text-align: left">Descripción de imagen:<br><input type="text" name="desc_img" value="'.$desc_img.'"></p>
                              <p style="text-align: left">Imagen:<br><input class="btn btn-default" type="file" name="file" id="file" value="" ></p>
                              <input type="hidden" name="id_recurso_modificar" value="'.$id.'">
                              <input type="hidden" name="idequipo" value="'.$info.'">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                  </form>
              </div>
          </div>
      </div>';

      echo '<div class="modal fade pg-show-modal" id="deleteRecurso'.$id.'" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <form enctype="multipart/form-data" action="consumer.php?boton=deleteRecurso" method="post">
                  <div class="modal-body">

                          <div id="recurso">
                            <p style="text-align: left">¿Seguro que desea eliminar el recurso '.$nombre.'?
                            <input type="hidden" name="id" value="'.$id.'">
                            <input type="hidden" name="idequipo" value="'.$info.'">
                          </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                      <button type="submit" class="btn btn-primary">Eliminar</button>
                  </div>
                </form>
            </div>
        </div>
    </div>';
  }
}

?>
