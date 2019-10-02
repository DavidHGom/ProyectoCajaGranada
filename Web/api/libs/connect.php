<?php

#if(!defined("VAIRABLECONTROLADORA"))
#  die("Acceso denegado");

function getConnection(){
  try {
    if (file_exists('conf/config_db.xml')) {
        $xml = simplexml_load_file('conf/config_db.xml');
    } else {
        exit('Error abriendo config_db.xml');
    }
    $bd_username = $xml->user;
    $bd_password = $xml->pass;
    $bd = $xml->db;
    $connection = new PDO("mysql:host=localhost;dbname=" . $bd, $bd_username, $bd_password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
  }

  return $connection;
}

?>
