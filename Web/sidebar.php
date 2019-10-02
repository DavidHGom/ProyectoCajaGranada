<?php

/**
* Archivo sidebar.php que controla la impresión de la barra de navegación lateral
* @author Daniel Soto del Ojo
*/

/**
 * Clase sidebar con la que se imprime el contenido del mismo
 */
class sidebar{

  function pintar($page, $user) {
    if ($user == "admin") {
      $this->pintarAdmin($page);
    }
  }

  function pintarGestion($page, $gestion, $texto) {
    echo '<a href="index.php?page='.$gestion.'&user=admin" class="btn btn-default" style="display: block ; color: white ; background-color: ';
    if ($page == $gestion || (strpos($page, "guias") && strpos($gestion, "guias"))
          || (strpos($page, "info") && strpos($gestion, "info"))
          || (strpos($page, "asis") && strpos($gestion, "asis"))
          || (strpos($page, "recursos") && strpos($gestion, "recursos"))
          || (strpos($page, "contenido") && strpos($gestion, "contenido"))) {
      echo 'plum ;';
    }
    else
      echo '#337ab7 ;';
    echo ' width: 100% ; margin-top: 5%">'.$texto.'</a>';
  }

  function pintarAdmin($page){
    echo '<div style="display: flex">
          <div style="flex: 0.4 ; padding: 1em ; background-color: lightblue; padding-bottom: 75px" class="pull-left">';

    // inicio
    echo '<a href="index.php?page=inicio_admin&user=admin" class="btn btn-default" style="display: block ; color: white ; background-color: ';
    if ($page == "inicio_admin")
      echo 'plum ;';
    else
      echo '#337ab7 ;';
    echo ' width: 100%">INICIO</a>';
    // gestiones
    $this->pintarGestion($page, "gestion_admin", "GESTIÓN DE ADMINISTRADORES");
    $this->pintarGestion($page, "gestion_asis", "GESTIÓN DE ASISTENTES");
    $this->pintarGestion($page, "gestion_info", "GESTIÓN DE EQUIPAMIENTO");
    $this->pintarGestion($page, "gestion_recursos", "GESTIÓN DE RECURSOS");
    $this->pintarGestion($page, "gestion_contenido", "GESTIÓN DE CONTENIDO");
    $this->pintarGestion($page, "gestion_guias_indice", "GESTIÓN DE GUIAS");
    $this->pintarGestion($page, "gestion_salas", "GESTIÓN DE SALAS");
    echo '</div>';
  }
}

?>
