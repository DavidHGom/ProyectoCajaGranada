<?php

/**
* Archivo header.php que controla la impresión de la cabecera de la web
* @author Manuel Lafuente Aranda
*/

/**
 * Clase header con la que se imprime el contenido de la misma
 */
class header{

  function pintar($page){
    echo '<!DOCTYPE html>
          <html lang="es">
              <head>
                  <meta charset="utf-8">
                  <meta http-equiv="X-UA-Compatible" content="IE=edge">
                  <meta name="viewport" content="width=device-width, initial-scale=1">
                  <meta name="description" content="Web de fundación CGR">
                  <meta name="author" content="Manuel Lafuente Aranda">
                  <meta name="author" content="Baltasar Ruiz Hernández">
                  <meta name="author" content="Daniel Soto del Ojo">
                  <title>Museo CGR</title>
                  <!-- Bootstrap core CSS -->
                  <link href="/bootstrap/css/bootstrap.css" rel="stylesheet">
                  <!-- Custom styles for this template -->
                  <link href="style.css" rel="stylesheet">
                  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
                  <!--[if lt IE 9]>
                    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
                    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
                  <![endif]-->
              </head>
              <body>';
    if($page != ""){
      echo '<div class="text-center" style="background-color: lightgrey ; padding-top: 1% ; padding-bottom: 2%">
        <img src="/imgs/LogoEmpresa.png" width="90" height="90" style="float: left; margin-left: 10px" />
        <h2><b style="color:white">CENTRO CULTURAL CajaGRANADA</b></h2><a href="consumer.php?boton=logout" style="color: white ; margin-right: 1%" class="pull-right">Cerrar sesión</a>
      </div>';
    }
  }
}


?>
