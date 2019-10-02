<?php

session_start();

/**
* Archivo principal index.php que controla la impresión del contenido.
*
* @author Manuel Lafuente Aranda: implementación inicial, adición de algunas inicializaciones necesarias y comprobación para control de seguridad.
* @author Daniel Soto del Ojo: adición de algunas inicializaciones necesarias.
*/
include 'header.php';
include 'sidebar.php';
include 'content.php';
include 'footer.php';
include 'consumer.php';

$consumidor_content = new Consumer();
$header = new header();
$sidebar = new sidebar();
$content = new content($consumidor_content);
$footer = new footer();

$page = isset($_GET['page']) ? $_GET['page']:'';
$user = isset($_GET['user']) ? $_GET['user']:'';
$sala = isset($_GET['sala']) ? $_GET['sala']:'all';
$guia = isset($_GET['guia']) ? $_GET['guia']:'';
$equipo = isset($_GET['id_equip']) ? $_GET['id_equip']:'all';
$recurso = isset($_GET['id_rec']) ? $_GET['id_rec']:'all';

$searchAdmin = isset($_SESSION['searchNombreAdmin']) ? $_SESSION['searchNombreAdmin']:'all';
$searchAsis = isset($_SESSION['searchNombreAsis']) ? $_SESSION['searchNombreAsis']:'all';
$searchNombreSala = isset($_SESSION['searchNombreSala']) ? $_SESSION['searchNombreSala']:'all';
$searchNombreEquipamiento = isset($_SESSION['searchNombreEquipamiento']) ? $_SESSION['searchNombreEquipamiento']:'all';
$searchNombreRecurso = isset($_SESSION['searchNombreRecurso']) ? $_SESSION['searchNombreRecurso']:'all';
$searchNombreContenido = isset($_SESSION['searchNombreContenido']) ? $_SESSION['searchNombreContenido']:'all';

$_SESSION['searchNombreAdmin'] = "";
$_SESSION['searchNombreAsis'] = "";
$_SESSION['searchNombreSala'] = "";
$_SESSION['searchNombreEquipamiento'] = "";
$_SESSION['searchNombreRecurso'] = "";
$_SESSION['searchNombreContenido'] = "";

$fallo = isset($_GET['fallo']) ? $_GET['fallo']:'';

if($page == ''){
  $header->pintar($page);
  $sidebar->pintar($page, $user);
  $content->pintar($page, $sala, $guia, $searchAdmin, $searchAsis, $searchNombreSala, $equipo, $recurso, $searchNombreEquipamiento, $searchNombreRecurso, $searchNombreContenido, $fallo);
  $footer->pintar();
}
else{
  if(isset($_SESSION["userid"])){
    $header->pintar($page);
    $sidebar->pintar($page, $user);
    $content->pintar($page, $sala, $guia, $searchAdmin, $searchAsis, $searchNombreSala, $equipo, $recurso, $searchNombreEquipamiento, $searchNombreRecurso, $searchNombreContenido, $fallo);
    $footer->pintar();
  }
}

?>
