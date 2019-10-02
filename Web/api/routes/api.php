<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../qrcode/vendor/autoload.php';
require '../telegram-api/vendor/autoload.php';
use Endroid\QrCode\QrCode;
use GuzzleHttp\Exception\ClientException;
use \unreal4u\TelegramAPI\TgLog;
use \unreal4u\TelegramAPI\Telegram\Methods\SendMessage;

/** API de la aplicación web del museo. Sirve de enlace entre el código de la interfaz web (fundamentalmente content.php) y la base de datos, ayudándose para ello del consumidor (consumer.php).
*
* Es la encargada de realizar las operaciones de adición (POST), modificación (PUT), eliminación (DELETE) y consulta (GET) de cada uno de los elementos de información que componen la BD, junto a
* otras funcionalidades extra.
*/

/**
* Operación encargada de realizar el login de un usuario, asociándole un token.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la operación de logueo se ha realizado correctamente, en el caso de un administrador.
* @return 2, si la operación de logueo se ha realizado correctamente, en el caso de un asistente.
* @return -2, si no existe un usuario con el DNI introducido.
* @return -1, si ha ocurrido un error durante la verificación de la contraseña introducida.
* @return token, con el token generado (aleatoriamente) durante la realización de la operación.
*/
$app->post('/login', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $dni_usuario = $json->dni;
    $pass_usuario = $json->password;

    $bd = getConnection();
    $consulta = $bd->prepare("SELECT password FROM Personal WHERE dni = ?");
    $consulta->execute(array($dni_usuario));
    $pass_db = $consulta->fetch(PDO::FETCH_ASSOC);
    $correcto = password_verify($pass_usuario, $pass_db["password"]);

    if ($correcto){
      $consulta_admin = $bd->prepare("SELECT * FROM Administradores WHERE dni = ?");
      $consulta_admin->execute(array($dni_usuario));
      $numfilas_admin = $consulta_admin->rowCount();

      if ($numfilas_admin == 1){
        $token = bin2hex(random_bytes(16));
        $consulta = $bd->prepare("UPDATE Personal SET token_personal = ? WHERE dni = ?");
        $consulta->bindParam(1, $token);
        $consulta->bindParam(2, $dni_usuario);
        $consulta->execute();

        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta_admin->fetch(PDO::FETCH_ASSOC), "token" => $token)));  ## Logueado correctamente como administrador
        $bd = null;
      }

      else{
        $consulta_asistente = $bd->prepare("SELECT * FROM Asistentes WHERE dni = ?");
        $consulta_asistente->execute(array($dni_usuario));
        $numfilas_asistente = $consulta_asistente->rowCount();

        if ($numfilas_asistente == 1){
          $token = bin2hex(random_bytes(16));
          $consulta = $bd->prepare("UPDATE Personal SET token_personal = ? WHERE dni = ?");
          $consulta->bindParam(1, $token);
          $consulta->bindParam(2, $dni_usuario);
          $consulta->execute();

          $response->withHeader("Content-type", "application/json");
          $response->withStatus(200);
          $response->getBody()->write(json_encode(array("res" => 2, "content" => $consulta_asistente->fetch(PDO::FETCH_ASSOC))));   ## Logueado correctamente como asistente
          $bd = null;
        }

        else{
          $response->withHeader("Content-type", "application/json");
          $response->withStatus(404);
          $response->getBody()->write(json_encode(array("res" => -2))); ## No existe un único admin o un único asistente con el dni introducido
          $bd = null;
        }
      }
    }
    else{
      $response->withHeader("Content-type", "application/json");
      $response->withStatus(400);
      $response->getBody()->write(json_encode(array("res" => -1))); ## Error en la verificación de password
      $bd = null;
    }
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de realizar el logout de un usuario, eliminando el token que tenía asociado.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la operación de deslogueo se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
*/
$app->post('/logout', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $bd = getConnection();
    $token = $json->token;
    $consulta = $bd->prepare("UPDATE Personal SET token_personal = NULL WHERE token_personal = ?");
    $consulta->bindParam(1, $token);
    $result = $consulta->execute();

    if ($result){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));    ## Logout realizado correctamente
        $bd = null;
    }
    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(500);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error en la operación
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de añadir un administrador.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la adición se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si ya existía un administrador con los identificadores proporcionados.
* @return content, con los datos del administrador repetido en caso de devolver un código de respuesta 2.
*/
$app->post('/admin', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $dni_admin = $json->dni;
    $nombre_admin = $json->nombre;
    $direccion_admin = $json->direccion;
    $contrasena_admin = password_hash($json->password, PASSWORD_BCRYPT);
    $correo_admin = $json->email;
    $token_admin = NULL;
    $telefono_admin = $json->telefono;
    $foto_admin = $json->foto;

    $bd = getConnection();
    $consulta = $bd->prepare("SELECT * FROM Administradores WHERE dni = ?");
    $consulta->execute(array($dni_admin));
    $numfilas = $consulta->rowCount();

    if ($numfilas == 0){
      $insercionpersonal = $bd->prepare("INSERT INTO Personal VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
      $insercionpersonal->bindParam(1, $dni_admin);
      $insercionpersonal->bindParam(2, $nombre_admin);
      $insercionpersonal->bindParam(3, $direccion_admin);
      $insercionpersonal->bindParam(4, $contrasena_admin);
      $insercionpersonal->bindParam(5, $correo_admin);
      $insercionpersonal->bindParam(6, $token_admin);
      $insercionpersonal->bindParam(7, $telefono_admin);
      $insercionpersonal->bindParam(8, $foto_admin);
      $resultpersonal = $insercionpersonal->execute();
      $insercionadmin = $bd->prepare("INSERT INTO Administradores VALUES(?)");
      $insercionadmin->bindParam(1, $dni_admin);
      $resultadmin = $insercionadmin->execute();

      $result2 = ($resultadmin and $resultpersonal);

      if($result2){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));    ## Administrador añadido correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error a la hora de añadir administrador
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 2, "content" => $consulta->fetch(PDO::FETCH_ASSOC))));   ## Ya existe un administrador con el dni introducido
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todos los administradores.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/admin', function(Request $request, Response $response) use($app) {
  try{
      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Personal WHERE dni in (SELECT dni FROM Administradores)");
      $consulta->execute();
      $numadmins = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numadmins, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos de un administrador, dado su nombre.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/admin/{nombre}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $nombre = $request->getAttribute('nombre');
      $nombre_busqueda = '%' . $nombre . '%';
      $consulta = $bd->prepare("SELECT * FROM Personal WHERE nombre LIKE ? AND dni in (SELECT dni FROM Administradores)");
      $consulta->execute(array($nombre_busqueda));
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos de un administrador, dado su token.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/admin/token/{token}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $token = $request->getAttribute('token');
      $consulta = $bd->prepare("SELECT * FROM Personal WHERE token_personal = ? AND dni in (SELECT dni FROM Administradores)");
      $consulta->execute(array($token));
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de modificar los datos de un administrador.
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Correción de errores
*
* @return 1, si la modificación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún administrador con los identificadores proporcionados.
* @return 3, si ya existe un administrador con el dni proporcionado.
*/
$app->put('/admin', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $dni_antiguo = $json->dni_antiguo;

    $bd = getConnection();
    $consultaadmin = $bd->prepare("SELECT * FROM Personal WHERE dni in (SELECT dni FROM Administradores WHERE dni = ?)");
    $consultaadmin->execute(array($dni_antiguo));
    $numfilas = $consultaadmin->rowCount();

    $dni = $json->dni;

    $nombre = $json->nombre;

    $direccion = $json->direccion;

    $password = $json->password;
    $contrasena = password_hash($password, PASSWORD_BCRYPT);

    $email = $json->email;

    $telefono = $json->telefono;

    $foto = $json->foto;

    if ($numfilas == 1){
      $consultadni = $bd->prepare("SELECT * FROM Administradores WHERE dni = ? AND dni != ?");
      $consultadni->execute(array($dni, $dni_antiguo));
      $numfilasdni = $consultadni->rowCount();

      if ($numfilasdni == 0){
        $eliminacionadmin = $bd->prepare("DELETE FROM Administradores WHERE dni = ?");
        $eliminacionadmin->bindParam(1, $dni_antiguo);
        $resulteliminacion = $eliminacionadmin->execute();

        $modificacionpersonal = $bd->prepare("UPDATE Personal SET dni = ?, nombre = ?, direccion = ?, password = ?, email = ?, telefono = ?, foto = ? WHERE dni = ?");
        $modificacionpersonal->bindParam(1, $dni);
        $modificacionpersonal->bindParam(2, $nombre);
        $modificacionpersonal->bindParam(3, $direccion);
        $modificacionpersonal->bindParam(4, $contrasena);
        $modificacionpersonal->bindParam(5, $email);
        $modificacionpersonal->bindParam(6, $telefono);
        $modificacionpersonal->bindParam(7, $foto);
        $modificacionpersonal->bindParam(8, $dni_antiguo);
        $resultmodificacion = $modificacionpersonal->execute();

        $adicionadmin = $bd->prepare("INSERT INTO Administradores VALUES(?)");
        $adicionadmin->bindParam(1, $dni);
        $resultadicion = $adicionadmin->execute();

        if($resultmodificacion and $resulteliminacion and $resultadicion){
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(200);
            $response->getBody()->write(json_encode(array("res" => 1)));   ## Administrador modificado correctamente
            $bd = null;
        }

        else{
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(400);
            $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la modificación del administrador
            $bd = null;
        }
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 3)));   ## Ya existe un administrador con el dni proporcionado
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe administrador con los datos introducidos
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de modificar los datos de un administrador, exceptuando el campo correspondiente a la foto.
*
* @author Daniel Soto del Ojo.
*
* @return 1, si la modificación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún administrador con los identificadores proporcionados.
* @return 3, si ya existe un administrador con el dni proporcionado.
*/
$app->put('/admin/nofoto', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $dni_antiguo = $json->dni_antiguo;

    $bd = getConnection();
    $consultaadmin = $bd->prepare("SELECT * FROM Personal WHERE dni in (SELECT dni FROM Administradores WHERE dni = ?)");
    $consultaadmin->execute(array($dni_antiguo));
    $numfilas = $consultaadmin->rowCount();

    $dni = $json->dni;

    $nombre = $json->nombre;

    $direccion = $json->direccion;

    $password = $json->password;
    $contrasena = password_hash($password, PASSWORD_BCRYPT);

    $email = $json->email;

    $telefono = $json->telefono;

    if ($numfilas == 1){
      $consultadni = $bd->prepare("SELECT * FROM Administradores WHERE dni = ? AND dni != ?");
      $consultadni->execute(array($dni, $dni_antiguo));
      $numfilasdni = $consultadni->rowCount();

      if ($numfilasdni == 0){
        $eliminacionadmin = $bd->prepare("DELETE FROM Administradores WHERE dni = ?");
        $eliminacionadmin->bindParam(1, $dni_antiguo);
        $resulteliminacion = $eliminacionadmin->execute();

        $modificacionpersonal = $bd->prepare("UPDATE Personal SET dni = ?, nombre = ?, direccion = ?, password = ?, email = ?, telefono = ? WHERE dni = ?");
        $modificacionpersonal->bindParam(1, $dni);
        $modificacionpersonal->bindParam(2, $nombre);
        $modificacionpersonal->bindParam(3, $direccion);
        $modificacionpersonal->bindParam(4, $contrasena);
        $modificacionpersonal->bindParam(5, $email);
        $modificacionpersonal->bindParam(6, $telefono);
        $modificacionpersonal->bindParam(7, $dni_antiguo);
        $resultmodificacion = $modificacionpersonal->execute();

        $adicionadmin = $bd->prepare("INSERT INTO Administradores VALUES(?)");
        $adicionadmin->bindParam(1, $dni);
        $resultadicion = $adicionadmin->execute();

        if($resultmodificacion and $resulteliminacion and $resultadicion){
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(200);
            $response->getBody()->write(json_encode(array("res" => 1)));   ## Administrador modificado correctamente
            $bd = null;
        }

        else{
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(400);
            $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la modificación del administrador
            $bd = null;
        }
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 3)));   ## Ya existe un administrador con el dni introducido
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe administrador con los datos introducidos
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de eliminar un administrador.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la eliminación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún administrador con los identificadores proporcionados.
* @return 3, si ya existe un administrador con el dni proporcionado.
*/
$app->delete('/admin', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $dni_admin = $json->dni;

    $bd = getConnection();
    $consulta = $bd->prepare("SELECT * FROM Administradores WHERE dni = ?");
    $consulta->execute(array($dni_admin));
    $numfilas = $consulta->rowCount();

    if ($numfilas == 1){
      $eliminacionadmin = $bd->prepare("DELETE FROM Administradores WHERE dni = ?");
      $eliminacionadmin->bindParam(1, $dni_admin);
      $resultadmin = $eliminacionadmin->execute();

      $eliminacionpersonal = $bd->prepare("DELETE FROM Personal WHERE dni = ?");
      $eliminacionpersonal->bindParam(1, $dni_admin);
      $resultpersonal = $eliminacionpersonal->execute();

      if($resultpersonal and $resultadmin){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Administrador eliminado correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la eliminación del administrador
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe un administrador con el dni introducido
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de añadir una asistencia.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la adición se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
*/
$app->post('/asistencia', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_recurso = $json->id_recurso;
    $hora_asistencia = $json->hora_asistencia;
    $fecha_asistencia = $json->fecha_asistencia;
    $dni_asistente_asistencia = $json->dni;
    $fecha_peticion_asistencia = $json->fecha_peticion;
    $hora_peticion_asistencia = $json->hora_peticion;

    $bd = getConnection();

    $insercionasistencia = $bd->prepare("INSERT INTO Asistencia_Asiste_Pedida (`#recurso, hora_asistencia, fecha_asistencia, dni, fecha_peticion, hora_peticion) VALUES(?, ?, ?, ?, ?, ?)");
    $insercionasistencia->bindParam(1, $id_recurso);
    $insercionasistencia->bindParam(2, $hora_asistencia);
    $insercionasistencia->bindParam(3, $fecha_asistencia);
    $insercionasistencia->bindParam(4, $dni_asistente_asistencia);
    $insercionasistencia->bindParam(5, $fecha_peticion_asistencia);
    $insercionasistencia->bindParam(6, $hora_peticion_asistencia);
    $resultasistencia = $insercionasistencia->execute();

    if($resultasistencia){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Asistencia añadida correctamente
        $bd = null;
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la adición de la asistencia
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de solicitar una asistencia.
*
* @author Elías Méndez García.
*
* @return 1, si la solicitud se ha completado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
*/
$app->post('/asistencia/pedir', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $codigo = $json->codigo;
    $bd = getConnection();

    $consulta_recurso = $bd->prepare("SELECT `#recurso` AS recurso FROM Recursos_Tiene WHERE codigo_qr=?");
    $consulta_recurso->bindParam(1, $codigo);
    $consulta_recurso->execute();
    $recurso = $consulta_recurso->fetch()["recurso"];

    $consulta = $bd->prepare("INSERT INTO Asistencia_Asiste_Pedida(`#recurso`, fecha_peticion, hora_peticion) VALUES(?, NOW(), NOW())");
    $consulta->bindParam(1, $recurso);
    $result = $consulta->execute();

    if ($result) {
      $consulta_asistencia = $bd->prepare("SELECT LAST_INSERT_ID() AS id");
      $consulta_asistencia->execute();
      $id_asistencia = $consulta_asistencia->fetch()["id"];

      $consulta_bot = $bd->prepare("SELECT Token FROM Telegram_bot WHERE Nombre='FundacionCGR'");
      $consulta_bot->execute();
      $token = $consulta_bot->fetch()["Token"];

      $consulta_mensaje = $bd->prepare("SELECT r.nombre AS nombre, e.ubicacion AS ubicacion, s.n_sala AS sala FROM Salas s, Equipamiento_Contiene e, Recursos_Tiene r WHERE s.`#sala`=e.`n_sala` AND e.`#equipamiento`=r.`#equipamiento` AND r.codigo_qr=?");
      $consulta_mensaje->bindParam(1, $codigo);
      $consulta_mensaje->execute();
      $ubicacion = $consulta_mensaje->fetch();

      $tgLog = new TgLog($token);

      $consulta_asistentes = $bd->prepare("SELECT id_telegram AS id FROM Asistentes");
      $consulta_asistentes->execute();
      $ids = $consulta_asistentes->fetchAll();
      $msg = 'Asistencia #' . $id_asistencia . ': Se ha solicitado una asistencia en la sala ' . $ubicacion["sala"] . ' en ' . $ubicacion["ubicacion"] . ' cerca del recurso '. $ubicacion["nombre"];

      foreach ($ids as $id) {
        $sendMessage = new SendMessage();
        $sendMessage->text = $msg;
        $sendMessage->chat_id = $id["id"];
        try {
          $tgLog->performApiRequest($sendMessage);
        } catch (ClientException $e) {
        }
      }


        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));    ## Asistencia pedida correctamente
    }
    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(500);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error en la operación
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todas las asistencias.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/asistencia', function(Request $request, Response $response) use($app) {
  try{
      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Asistencia_Asiste_Pedida");
      $consulta->execute();
      $numasistencias = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numasistencias, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos de una asistencia, dado su identificador.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/asistencia/id/{idasis}', function(Request $request, Response $response) use($app) {
  try{
      $bd = getConnection();
      $id_asistencia = $request->getAttribute('idasis');
      $consulta = $bd->prepare("SELECT * FROM Asistencia_Asiste_Pedida WHERE `#asistencia` = ?");
      $consulta->execute(array($id_asistencia));
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener las asistencias realizadas por un asistente, dado su DNI.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/asistencia/{dniasis}', function(Request $request, Response $response) use($app) {
  try{
      $bd = getConnection();
      $dni_asistente = $request->getAttribute('dniasis');
      $consulta = $bd->prepare("SELECT * FROM Asistencia_Asiste_Pedida WHERE dni = ?");
      $consulta->execute(array($dni_asistente));
      $numasistencias = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numasistencias, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de añadir un asistente.
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Corrección de errores.
*
* @return 1, si la adición se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si ya existía un asistente con los identificadores proporcionados.
* @return content, con los datos del asistente repetido en caso de devolver un código de respuesta 2.
*/
$app->post('/asistente', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $dni_asistente = $json->dni;
    $estado_asistente = "NO_DISPONIBLE";
    $nombre_asistente = $json->nombre;
    $direccion_asistente = $json->direccion;
    $contrasena_asistente = password_hash($json->password, PASSWORD_BCRYPT);
    $correo_asistente = $json->email;
    $token_asistente = NULL;
    $telefono_asistente = $json->telefono;
    $foto_asistente = $json->foto;
    $id_telegram = $json->id_telegram;

    $bd = getConnection();
    $consulta = $bd->prepare("SELECT * FROM Asistentes WHERE dni = ?");
    $consulta->execute(array($dni_asistente));
    $numfilas = $consulta->rowCount();

    if ($numfilas == 0){
      $insercionpersonal = $bd->prepare("INSERT INTO Personal VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
      $insercionpersonal->bindParam(1, $dni_asistente);
      $insercionpersonal->bindParam(2, $nombre_asistente);
      $insercionpersonal->bindParam(3, $direccion_asistente);
      $insercionpersonal->bindParam(4, $contrasena_asistente);
      $insercionpersonal->bindParam(5, $correo_asistente);
      $insercionpersonal->bindParam(6, $token_asistente);
      $insercionpersonal->bindParam(7, $telefono_asistente);
      $insercionpersonal->bindParam(8, $foto_asistente);
      $resultpersonal = $insercionpersonal->execute();
      $insercionasistente = $bd->prepare("INSERT INTO Asistentes VALUES(?, ?, ?)");
      $insercionasistente->bindParam(1, $dni_asistente);
      $insercionasistente->bindParam(2, $estado_asistente);
      $insercionasistente->bindParam(3, $id_telegram);
      $resultasistente = $insercionasistente->execute();

      $result2 = ($resultasistente and $resultpersonal);

      if($result2){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));    ## Asistente añadido correctamente
        $bd = null;
      }
      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la adición del asistente
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 2, "content" => $consulta->fetch(PDO::FETCH_ASSOC))));   ## Ya existe un asistente con el dni introducido
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todos los asistentes.
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Corrección de errores.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/asistente', function(Request $request, Response $response) use($app) {
  try{
      $bd = getConnection();
      $consulta = $bd->prepare("SELECT p.*,a.estado_actividad,a.id_telegram FROM Personal p,Asistentes a WHERE p.dni = a.dni");
      $consulta->execute();
      $numasistentes = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numasistentes, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos de un asistente concreto, dado su nombre.
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Corrección de errores.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/asistente/{nombre}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $nombre = $request->getAttribute('nombre');
      $nombre_busqueda = '%' . $nombre . '%';
      $consulta = $bd->prepare("SELECT p.*,a.estado_actividad,a.id_telegram FROM Personal p, Asistentes a WHERE p.nombre LIKE ? AND p.dni = a.dni");
      $consulta->execute(array($nombre_busqueda));
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de modificar los datos de un asistente.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la modificación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún asistente con los identificadores proporcionados.
* @return 3, si ya existe un asistente con el dni proporcionado.
*/
$app->put('/asistente', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $dni_antiguo = $json->dni_antiguo;

    $bd = getConnection();
    $consultaasistente = $bd->prepare("SELECT * FROM Personal WHERE dni in (SELECT dni FROM Asistentes WHERE dni = ?)");
    $consultaasistente->execute(array($dni_antiguo));
    $numfilas = $consultaasistente->rowCount();

    $dni = $json->dni;

    $nombre = $json->nombre;

    $direccion = $json->direccion;

    $password = $json->password;
    $contrasena = password_hash($password, PASSWORD_BCRYPT);

    $email = $json->email;

    $telefono = $json->telefono;

    $foto = $json->foto;

    $estado = $json->estado;

    $id_telegram = $json->id_telegram;

    if ($numfilas == 1){
      $consultadni = $bd->prepare("SELECT * FROM Asistentes WHERE dni = ? AND dni != ?");
      $consultadni->execute(array($dni, $dni_antiguo));
      $numfilasdni = $consultadni->rowCount();

      if ($numfilasdni == 0){
        $eliminacionasistente = $bd->prepare("DELETE FROM Asistentes WHERE dni = ?");
        $eliminacionasistente->bindParam(1, $dni_antiguo);
        $resulteliminacion = $eliminacionasistente->execute();

        $modificacionpersonal = $bd->prepare("UPDATE Personal SET dni = ?, nombre = ?, direccion = ?, password = ?, email = ?, telefono = ?, foto = ? WHERE dni = ?");
        $modificacionpersonal->bindParam(1, $dni);
        $modificacionpersonal->bindParam(2, $nombre);
        $modificacionpersonal->bindParam(3, $direccion);
        $modificacionpersonal->bindParam(4, $contrasena);
        $modificacionpersonal->bindParam(5, $email);
        $modificacionpersonal->bindParam(6, $telefono);
        $modificacionpersonal->bindParam(7, $foto);
        $modificacionpersonal->bindParam(8, $dni_antiguo);
        $resultmodificacion = $modificacionpersonal->execute();

        $adicionasistente = $bd->prepare("INSERT INTO Asistentes VALUES(?, ?, ?)");
        $adicionasistente->bindParam(1, $dni);
        $adicionasistente->bindParam(2, $estado);
        $adicionasistente->bindParam(3, $id_telegram);
        $resultadicion = $adicionasistente->execute();

        if($resultmodificacion and $resulteliminacion and $resultadicion){
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(200);
            $response->getBody()->write(json_encode(array("res" => 1)));   ## Administrador modificado correctamente
            $bd = null;
        }

        else{
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(400);
            $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la modificación del asistente
            $bd = null;
        }
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 3)));   ## Ya existe un asistente con el dni introducido
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe asistente con los datos introducidos
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de modificar los datos de un asistente, exceptuando el campo de la foto.
*
* @author Daniel Soto del Ojo.
*
* @return 1, si la modificación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún asistente con los identificadores proporcionados.
* @return 3, si ya existe un asistente con el dni proporcionado.
*/
$app->put('/asistente/nofoto', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $dni_antiguo = $json->dni_antiguo;

    $bd = getConnection();
    $consultaasistente = $bd->prepare("SELECT * FROM Personal WHERE dni in (SELECT dni FROM Asistentes WHERE dni = ?)");
    $consultaasistente->execute(array($dni_antiguo));
    $numfilas = $consultaasistente->rowCount();

    $dni = $json->dni;

    $nombre = $json->nombre;

    $direccion = $json->direccion;

    $password = $json->password;
    $contrasena = password_hash($password, PASSWORD_BCRYPT);

    $email = $json->email;

    $telefono = $json->telefono;

    $estado = $json->estado;

    $id_telegram = $json->id_telegram;

    if ($numfilas == 1){
      $consultadni = $bd->prepare("SELECT * FROM Asistentes WHERE dni = ? AND dni != ?");
      $consultadni->execute(array($dni, $dni_antiguo));
      $numfilasdni = $consultadni->rowCount();

      if ($numfilasdni == 0){
        $eliminacionasistente = $bd->prepare("DELETE FROM Asistentes WHERE dni = ?");
        $eliminacionasistente->bindParam(1, $dni_antiguo);
        $resulteliminacion = $eliminacionasistente->execute();

        $modificacionpersonal = $bd->prepare("UPDATE Personal SET dni = ?, nombre = ?, direccion = ?, password = ?, email = ?, telefono = ? WHERE dni = ?");
        $modificacionpersonal->bindParam(1, $dni);
        $modificacionpersonal->bindParam(2, $nombre);
        $modificacionpersonal->bindParam(3, $direccion);
        $modificacionpersonal->bindParam(4, $contrasena);
        $modificacionpersonal->bindParam(5, $email);
        $modificacionpersonal->bindParam(6, $telefono);
        $modificacionpersonal->bindParam(7, $dni_antiguo);
        $resultmodificacion = $modificacionpersonal->execute();

        $adicionasistente = $bd->prepare("INSERT INTO Asistentes VALUES(?, ?, ?)");
        $adicionasistente->bindParam(1, $dni);
        $adicionasistente->bindParam(2, $estado);
        $adicionasistente->bindParam(3, $id_telegram);
        $resultadicion = $adicionasistente->execute();

        if($resultmodificacion and $resulteliminacion and $resultadicion){
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(200);
            $response->getBody()->write(json_encode(array("res" => 1)));   ## Administrador modificado correctamente
            $bd = null;
        }

        else{
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(400);
            $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la modificación del asistente
            $bd = null;
        }
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 3)));   ## Ya existe un asistente con el dni introducido
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe asistente con los datos introducidos
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de eliminar un asistente.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la eliminación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún asistente con los identificadores proporcionados.
*/
$app->delete('/asistente', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $dni_asistente = $json->dni;

    $bd = getConnection();
    $consulta = $bd->prepare("SELECT * FROM Asistentes WHERE dni = ?");
    $consulta->execute(array($dni_asistente));
    $numfilas = $consulta->rowCount();

    if ($numfilas == 1){
      $eliminacionasistente = $bd->prepare("DELETE FROM Asistentes WHERE dni = ?");
      $eliminacionasistente->bindParam(1, $dni_asistente);
      $resultasistente = $eliminacionasistente->execute();

      $eliminacionpersonal = $bd->prepare("DELETE FROM Personal WHERE dni = ?");
      $eliminacionpersonal->bindParam(1, $dni_asistente);
      $resultpersonal = $eliminacionpersonal->execute();

      if($resultpersonal and $resultasistente){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Asistente eliminado correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la eliminación del asistente
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe un asistente con el dni introducido
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de añadir una guía, indicando el tipo de guía (Personalizada o Predefinida).
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Corrección de errores.
*
* @return 1, si la adición se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
*/
$app->post('/guia/tipo/{tipoguia}', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $nombre_guia = $json->nombre_guia;
    $tipo_guia = $request->getAttribute('tipoguia');

    $bd = getConnection();

    $insercionguia = $bd->prepare("INSERT INTO Guia (nombre_guia) VALUES(?)");
    $insercionguia->bindParam(1, $nombre_guia);
    $resultguia = $insercionguia->execute();

    $consultaguia = $bd->prepare("SELECT * FROM Guia ORDER BY `#guia` DESC");
    $consultaguia->execute();
    $guiaconcreta = $consultaguia->fetch(PDO::FETCH_ASSOC);

    $result2 = null;

    if (strcmp($tipo_guia, "personalizada") == 0){
        $insercionguiapersonalizada = $bd->prepare("INSERT INTO GuiasPersonalizadas VALUES(?)");
        $insercionguiapersonalizada->bindParam(1, $guiaconcreta["#guia"]);
        $resultguiapersonalizada = $insercionguiapersonalizada->execute();
        $result2 = ($resultguia and $resultguiapersonalizada);
    }

    else if (strcmp($tipo_guia, "predefinida") == 0){
        $insercionguiapredefinida = $bd->prepare("INSERT INTO GuiasPredefinidas VALUES(?)");
        $insercionguiapredefinida->bindParam(1, $guiaconcreta["#guia"]);
        $resultguiapredefinida = $insercionguiapredefinida->execute();
        $result2 = ($resultguia and $resultguiapredefinida);
    }

    if($result2){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));    ## Guía añadida correctamente
        $bd = null;
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la adición de la guía
        $bd = null;
    }
  } catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todas las guías.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/guia', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Guia");
      $consulta->execute();
      $numguias = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numguias, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todas las guías de un tipo concreto.
*
* @author Daniel Soto del Ojo.
*
* @return 1, si la búsqueda se ha completado correctamente.
* @return -1, si la búsqueda no ha podido realizarse al introducir incorrectamente el tipo de guía.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/guia/tipo/{tipo}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $tipo = $request->getAttribute('tipo');
      $result = null;

      if (strcmp($tipo, "personalizada") == 0){
        $consulta = $bd->prepare("SELECT * FROM Guia WHERE `#guia` IN (SELECT `#guias` FROM GuiasPersonalizadas)");
        $consulta->execute();
        $numguias = $consulta->rowCount();
        $result = 1;
      }

      else if (strcmp($tipo, "predefinida") == 0){
        $consulta = $bd->prepare("SELECT * FROM Guia WHERE `#guia` IN (SELECT `#guia` FROM GuiasPredefinidas)");
        $consulta->execute();
        $numguias = $consulta->rowCount();
        $result = 1;
      }

      else{
        $result = -1;
      }

      $bd = null;

      if ($result == 1){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numguias, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));
      }
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener una guía personalizada.
*
* @author Elías Méndez García
*
* @return 1, si la búsqueda se ha completado correctamente.
* @return -1, si la búsqueda no ha podido realizarse al introducir incorrectamente el código de la guía.
*/
$app->get('/guia/personalizada/{id}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $id = $request->getAttribute('id');
      $result = null;

      $consulta = $bd->prepare("SELECT * FROM Guia WHERE `#guia` IN (SELECT `#guias` FROM GuiasPersonalizadas WHERE `#guia`=?)");
      $consulta->bindParam(1, $id);
      $consulta->execute();
      $result = $consulta->rowCount();

      if ($result == 1) {
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetch(PDO::FETCH_ASSOC))));
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));
      }
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener compartir una guía personalizada.
*
* @author Elías Méndez García
*
* @return 1, si se ha insertado correctamente.
* @return -1, si no se ha podido insertar.
*/
$app->post('/guia/personalizada/', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $body = $request->getBody();
      $json = json_decode($body);

      $consulta = $bd->prepare("INSERT INTO Guia(nombre_guia) VALUES(?)");
      $consulta->bindParam(1, $json->titulo);
      $result = $consulta->execute();

      if ($result) {
        $consulta_guia = $bd->prepare("SELECT LAST_INSERT_ID() AS id");
        $consulta_guia->execute();
        $id_guia = $consulta_guia->fetch()["id"];

        $insert_personalizada = $bd->prepare("INSERT INTO GuiasPersonalizadas VALUES(?)");
        $insert_personalizada->bindParam(1, $id_guia);
        $result_insert = $insert_personalizada->execute();

        if($result_insert) {
          $insert_compuesta = $bd->prepare("INSERT INTO Compuesta VALUES(?, ?, ?)");
          $insert_compuesta->bindParam(2, $id_guia);
          $i = 1;
          foreach ($json->recursos as $recurso) {
            $insert_compuesta->bindParam(1, $recurso);
            $insert_compuesta->bindParam(3, $i);
            $result_compuesta = $insert_compuesta->execute();
            if($result_compuesta) {
              ++$i;
            }
          }
        }
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1, "codigo" => $id_guia)));
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));
      }
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de convertir una guía personalizada a predefinida.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la modificación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ninguna guía con los identificadores proporcionados.
*/
$app->put('/guia', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_guia_modificar = $json->id_guia_modificar;

    $bd = getConnection();
    $consultaguia = $bd->prepare("SELECT * FROM GuiasPersonalizadas WHERE `#guias` = ?");
    $consultaguia->execute(array($id_guia_modificar));
    $numfilas = $consultaguia->rowCount();

    if ($numfilas == 1){
      $eliminacionguia = $bd->prepare("DELETE FROM GuiasPersonalizadas WHERE `#guias` = ?");
      $eliminacionguia->bindParam(1, $id_guia_modificar);
      $resulteliminacion = $eliminacionguia->execute();

      $adicionguia = $bd->prepare("INSERT INTO GuiasPredefinidas VALUES(?)");
      $adicionguia->bindParam(1, $id_guia_modificar);
      $resultadicion = $adicionguia->execute();

      if($resulteliminacion and $resultadicion){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Guía modificada correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la modificación de la guía
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe guía personalizada con el identificador proporcionado
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de eliminar una guía.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la eliminación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ninguna guía con los identificadores proporcionados.
*/
$app->delete('/guia/tipo/{tipo}', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_guia = $json->id_guia;
    $tipo = $request->getAttribute('tipo');

    $bd = getConnection();
    $consulta = $bd->prepare("SELECT * FROM Guia WHERE `#guia` = ?");
    $consulta->execute(array($id_guia));
    $numfilas = $consulta->rowCount();

    if ($numfilas == 1){
      $resultguia = null;

      $eliminacioncompuesta = $bd->prepare("DELETE FROM Compuesta WHERE `#guia` = ?");
      $eliminacioncompuesta->bindParam(1, $id_guia);
      $resultguia = $eliminacioncompuesta->execute();

      if (strcmp($tipo, "personalizada") == 0){
        $eliminaciontipoguia = $bd->prepare("DELETE FROM GuiasPersonalizadas WHERE `#guias` = ?");
        $eliminaciontipoguia->bindParam(1, $id_guia);
        $resultguia = $resultguia and $eliminaciontipoguia->execute();
      }

      else if (strcmp($tipo, "predefinida") == 0){
        $eliminaciontipoguia = $bd->prepare("DELETE FROM GuiasPredefinidas WHERE `#guia` = ?");
        $eliminaciontipoguia->bindParam(1, $id_guia);
        $resultguia = $resultguia and $eliminaciontipoguia->execute();
      }

      $eliminacionguia = $bd->prepare("DELETE FROM Guia WHERE `#guia` = ?");
      $eliminacionguia->bindParam(1, $id_guia);
      $resultguia = $resultguia and $eliminacionguia->execute();

      if($resultguia){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Guía eliminada correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la eliminación de la guía
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe una guía con el id introducido
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de añadir un recurso a una guía.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la adición se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si ya existía una composición Recurso - Guía con los identificadores proporcionados.
* @return 3, si ya existe un recurso en la guía proporcionada con la prioridad seleccionada
* @return content, con los datos de la composición repetida en caso de devolver un código de respuesta 2.
*/
$app->post('/recursoguia', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_recurso_compuesto = $json->id_recurso;
    $id_guia_compuesta = $json->id_guia;
    $prioridad_compuesta = $json->prioridad;

    $bd = getConnection();

    $consulta = $bd->prepare("SELECT * FROM Compuesta WHERE `#recurso` = ? AND `#guia` = ?");
    $consulta->execute(array($id_recurso_compuesto, $id_guia_compuesta));
    $numfilas = $consulta->rowCount();

    if ($numfilas == 0){
      $consultaprioridad = $bd->prepare("SELECT * FROM Compuesta WHERE `#guia` = ? AND prioridad = ?");
      $consultaprioridad->execute(array($id_guia_compuesta, $prioridad_compuesta));
      $numfilasprioridad = $consultaprioridad->rowCount();

      if ($numfilasprioridad == 0){
        $insercioncompuesta = $bd->prepare("INSERT INTO Compuesta VALUES(?, ?, ?)");
        $insercioncompuesta->bindParam(1, $id_recurso_compuesto);
        $insercioncompuesta->bindParam(2, $id_guia_compuesta);
        $insercioncompuesta->bindParam(3, $prioridad_compuesta);
        $resultcompuesta = $insercioncompuesta->execute();

        if($resultcompuesta){
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(200);
            $response->getBody()->write(json_encode(array("res" => 1)));    ## Composición Guía - Recurso añadida correctamente
            $bd = null;
        }
        else{
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(400);
            $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la adición de la composición
            $bd = null;
        }
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 3, "content" => $consulta->fetch(PDO::FETCH_ASSOC)))); ## Ya existe un recurso en la guía
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 2, "content" => $consulta->fetch(PDO::FETCH_ASSOC)))); ## Ya existe una composición con los identificadores proporcionados
        $bd = null;
    }
  } catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de eliminar un recurso de una guía.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la eliminación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ninguna composición Recurso - Guía con los identificadores proporcionados.
*/
$app->delete('/recursoguia', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_guia = $json->id_guia;
    $id_recurso = $json->id_recurso;

    $bd = getConnection();
    $consulta = $bd->prepare("SELECT * FROM Compuesta WHERE `#recurso` = ? AND `#guia` = ?");
    $consulta->execute(array($id_recurso, $id_guia));
    $numfilas = $consulta->rowCount();

    if ($numfilas == 1){
      $eliminacioncompuesta = $bd->prepare("DELETE FROM Compuesta WHERE `#recurso` = ? AND `#guia` = ?");
      $eliminacioncompuesta->bindParam(1, $id_recurso);
      $eliminacioncompuesta->bindParam(2, $id_guia);
      $resulteliminacion = $eliminacioncompuesta->execute();

      if($resulteliminacion){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Recurso eliminado correctamente de la guía
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la eliminación del recurso de la guía
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe una composición de Guía - Recurso con los identificadores proporcionados
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de modificar la prioridad de un recurso de una guía.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la modificación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún recurso con la prioridad introducida.
*/
$app->put('/recursoguia/{direccion}', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_recurso = $json->id_recurso;
    $id_guia = $json->id_guia;
    $prioridad_antigua = $json->prioridad_antigua;
    $direccion = $request->getAttribute('direccion');

    $bd = getConnection();
    $consulta = $bd->prepare("SELECT * FROM Compuesta WHERE `#recurso` = ? AND `#guia` = ? AND prioridad = ?");
    $consulta->execute(array($id_recurso, $id_guia, $prioridad_antigua));
    $numfilas = $consulta->rowCount();

    if ($numfilas == 1){
      $resultmodificacion1 = null;
      $resultmodificacion2 = null;

      if (strcmp($direccion, "arriba") == 0){
        $nuevaprioridad = (+$prioridad_antigua) - 1;
        $modificacionprioridadarriba1 = $bd->prepare("UPDATE Compuesta SET prioridad = ? WHERE `#recurso` = ? AND `#guia` = ? AND prioridad = ?");
        $modificacionprioridadarriba1->bindParam(1, $nuevaprioridad);
        $modificacionprioridadarriba1->bindParam(2, $id_recurso);
        $modificacionprioridadarriba1->bindParam(3, $id_guia);
        $modificacionprioridadarriba1->bindParam(4, $prioridad_antigua);

        $modificacionprioridadarriba2 = $bd->prepare("UPDATE Compuesta SET prioridad = ? WHERE `#recurso` != ? AND prioridad = ?");
        $modificacionprioridadarriba2->bindParam(1, $prioridad_antigua);
        $modificacionprioridadarriba2->bindParam(2, $id_recurso);
        $modificacionprioridadarriba2->bindParam(3, $nuevaprioridad);

        $resultmodificacion1 = $modificacionprioridadarriba1->execute();
        $resultmodificacion2 = $modificacionprioridadarriba2->execute();
      }

      else if (strcmp($direccion, "abajo") == 0){
        $nuevaprioridad = (+$prioridad_antigua) + 1;
        $modificacionprioridadabajo1 = $bd->prepare("UPDATE Compuesta SET prioridad = ? WHERE `#recurso` = ? AND `#guia` = ? AND prioridad = ?");
        $modificacionprioridadabajo1->bindParam(1, $nuevaprioridad);
        $modificacionprioridadabajo1->bindParam(2, $id_recurso);
        $modificacionprioridadabajo1->bindParam(3, $id_guia);
        $modificacionprioridadabajo1->bindParam(4, $prioridad_antigua);

        $modificacionprioridadabajo2 = $bd->prepare("UPDATE Compuesta SET prioridad = ? WHERE `#recurso` != ? AND prioridad = ?");
        $modificacionprioridadabajo2->bindParam(1, $prioridad_antigua);
        $modificacionprioridadabajo2->bindParam(2, $id_recurso);
        $modificacionprioridadabajo2->bindParam(3, $nuevaprioridad);

        $resultmodificacion1 = $modificacionprioridadabajo1->execute();
        $resultmodificacion2 = $modificacionprioridadabajo2->execute();
      }

      if($resultmodificacion1 and $resultmodificacion2){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Prioridad modificada correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la modificación de la prioridad
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe recurso con la prioridad introducida
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos de una guía, dado su identificador.
*
* @author Daniel Soto del Ojo.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/guia/{id}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $id_guia =  $request->getAttribute('id');

      $consulta = $bd->prepare("SELECT * FROM Guia WHERE `#guia` = ?");
      $consulta->execute(array($id_guia));
      $numresultados = $consulta->rowCount();

      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numresultados, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener la composición (es decir, la información de los recursos que contiene) de una guía, dado su identificador.
*
* @author Daniel Soto del Ojo.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/guia/composicion/{id}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $id_guia =  $request->getAttribute('id');

      $consulta = $bd->prepare("SELECT r.*,g.*,c.prioridad FROM Recursos_Tiene r, Guia g, Compuesta c WHERE c.`#recurso` = r.`#recurso` AND c.`#guia` = g.`#guia` AND c.`#guia` = ?");
      $consulta->execute(array($id_guia));
      $numresultados = $consulta->rowCount();

      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numresultados, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de añadir un idioma.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la adición se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
*/
$app->post('/idioma', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $codigo_idioma = $json->codigo_idioma;
    $nombre_idioma = $json->nombre;

    $bd = getConnection();
    $insercionidioma = $bd->prepare("INSERT INTO Idioma (codigo_idioma, nombre) VALUES(?, ?)");
    $insercionidioma->bindParam(1, $codigo_idioma);
    $insercionidioma->bindParam(2, $nombre_idioma);
    $resultidioma = $insercionidioma->execute();

    if($resultidioma){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Idioma añadido correctamente
        $bd = null;
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la adición del idioma
        $bd = null;
    }

  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todos los idiomas.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/idioma', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Idioma");
      $consulta->execute();
      $numidiomas = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numidiomas, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos de un idioma, dado su nombre.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/idioma/nombre/{nombre}', function(Request $request, Response $response) use($app){
  try{
      $nombre_idioma = $request->getAttribute('nombre');

      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Idioma WHERE nombre = ?");
      $consulta->execute(array($nombre_idioma));
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener el nombre de un idioma, dado su identificador.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/idioma/id/{id}', function(Request $request, Response $response) use($app){
  try{
      $id_idioma = $request->getAttribute('id');

      $bd = getConnection();
      $consulta = $bd->prepare("SELECT nombre FROM Idioma WHERE `#idioma` = ?");
      $consulta->execute(array($id_idioma));
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de modificar los datos de un idioma.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la modificación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún idioma con los identificadores proporcionados.
*/
$app->put('/idioma', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_idioma_modificar = $json->id_idioma_modificar;

    $bd = getConnection();

    $consultaidioma = $bd->prepare("SELECT * FROM Idioma WHERE `#idioma` = ?");
    $consultaidioma->execute(array($id_idioma_modificar));
    $numfilas = $consultaidioma->rowCount();

    $codigo = $json->codigo;

    $nombre = $json->nombre;

    if ($numfilas == 1){
      $modificacionidioma = $bd->prepare("UPDATE Idioma SET codigo_idioma = ?, nombre = ? WHERE `#idioma` = ?");
      $modificacionidioma->bindParam(1, $codigo);
      $modificacionidioma->bindParam(2, $nombre);
      $modificacionidioma->bindParam(3, $id_idioma_modificar);
      $resultmodificacion = $modificacionidioma->execute();

      if($resultmodificacion){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Idioma modificado correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la modificación del idioma
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe idioma a modificar con el identificador proporcionado
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de eliminar una guía.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la eliminación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún idioma con los identificadores proporcionados.
*/
$app->delete('/idioma', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_idioma = $json->id_idioma;

    $bd = getConnection();
    $consulta = $bd->prepare("SELECT * FROM Idioma WHERE `#idioma` = ?");
    $consulta->execute(array($id_idioma));
    $numfilas = $consulta->rowCount();

    if ($numfilas == 1){
      $eliminacionidioma = $bd->prepare("DELETE FROM Idioma WHERE `#idioma` = ?");
      $eliminacionidioma->bindParam(1, $id_idioma);
      $resulteliminacion = $eliminacionidioma->execute();

      if($resultmodificacion and $resulteliminacion){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Idioma eliminado correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la eliminación del idioma
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe un idioma con los datos introducidos
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de añadir un contenido.
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Corrección de errores.
*
* @return 1, si la adición se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
*/
$app->post('/contenido', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $codigo_idioma = $json->id_idioma;
    $id_recurso = $json->id_recurso;
    $nombre = $json->nombre;
    $formato = $json->formato;
    $info = $json->info;

    $bd = getConnection();
    $consultaidioma = $bd->prepare("SELECT * FROM Idioma WHERE codigo_idioma = ?");
    $consultaidioma->bindParam(1, $codigo_idioma);
    $consultaidioma->execute();
    $idiomaconcreto = $consultaidioma->fetch(PDO::FETCH_ASSOC);
    $id_idioma = $idiomaconcreto['#idioma'];

    $insercioncontenido = $bd->prepare("INSERT INTO Contenido_Formado (`#idioma`, `#recurso`, nombre) VALUES(?, ?, ?)");
    $insercioncontenido->bindParam(1, $id_idioma);
    $insercioncontenido->bindParam(2, $id_recurso);
    $insercioncontenido->bindParam(3, $nombre);
    $resultcontenido = $insercioncontenido->execute();

    if ($resultcontenido){
        $consultacontenido = $bd->prepare("SELECT * FROM Contenido_Formado ORDER BY `#contenido` DESC");
        $consultacontenido->execute();
        $contenidoconcreto = $consultacontenido->fetch(PDO::FETCH_ASSOC);

        if (strcmp($formato, "texto") == 0){
            $inserciontexto = $bd->prepare("INSERT INTO Texto VALUES(?, ?)");
            $inserciontexto->bindParam(1, $contenidoconcreto['#contenido']);
            $inserciontexto->bindParam(2, $info);
            $resulttexto = $inserciontexto->execute();
            $resultcontenido = ($resultcontenido and $resulttexto);
        }

        else{
            $insercionmultimedia = $bd->prepare("INSERT INTO Multimedia VALUES(?, ?, ?)");
            $insercionmultimedia->bindParam(1, $contenidoconcreto['#contenido']);
            $insercionmultimedia->bindParam(2, $formato);
            $insercionmultimedia->bindParam(3, $info);
            $resultmultimedia = $insercionmultimedia->execute();
            $resultcontenido = ($resultcontenido and $resultmultimedia);
        }

        if($resultcontenido){
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(200);
            $response->getBody()->write(json_encode(array("res" => 1)));    ## Contenido añadido correctamente
            $bd = null;
        }

        else{
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(400);
            $response->getBody()->write(json_encode(array("res" => -1)));   ## Error a la hora de añadir contenido
            $bd = null;
        }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error a la hora de añadir contenido
        $bd = null;
    }

  } catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de añadir un equipamiento.
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Corrección de errores.
*
* @return 1, si la adición se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
*/
$app->post('/equipamiento', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $ubicacion_equipamiento = $json->ubicacion;
    $nsala_equipamiento = $json->n_sala;
    $nombre_equipamiento = $json->nombre;
    $proveedor_equipamiento = $json->proveedor;
    $img_equipamiento = $json->img;
    $descripcion_img_equipamiento = $json->desc_img;

    $bd = getConnection();
    $insercionequipamiento = $bd->prepare("INSERT INTO Equipamiento_Contiene (ubicacion, n_sala, nombre, proveedor, img, desc_img) VALUES(?, ?, ?, ?, ?, ?)");
    $insercionequipamiento->bindParam(1, $ubicacion_equipamiento);
    $insercionequipamiento->bindParam(2, $nsala_equipamiento);
    $insercionequipamiento->bindParam(3, $nombre_equipamiento);
    $insercionequipamiento->bindParam(4, $proveedor_equipamiento);
    $insercionequipamiento->bindParam(5, $img_equipamiento);
    $insercionequipamiento->bindParam(6, $descripcion_img_equipamiento);
    $resultequipamiento = $insercionequipamiento->execute();

    if($resultequipamiento){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));    ## Equipamiento añadido correctamente
        $bd = null;
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error a la hora de añadir equipamiento
        $bd = null;
    }

  } catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todos los contenidos.
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Corrección de errores.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultadosmultimedia, con el número de resultados obtenidos, del subtipo multimedia.
* @return numresultadostexto, con el número de resultados obtenidos, del subtipo texto.
* @return contentmultimedia, con el resultado de la búsqueda, para el subtipo multimedia.
* @return contenttexto, con el resultado de la búsqueda, para el subtipo texto.
*/
$app->get('/contenido', function(Request $request, Response $response) use($app){
  try{
      $bd = getConnection();
      $consulta_multimedia = $bd->prepare("SELECT c.*,m.* FROM Contenido_Formado c, Multimedia m WHERE c.`#contenido` = m.`#contenido`");
      $consulta_multimedia->execute();
      $nummultimedia = $consulta_multimedia->rowCount();

      $consulta_texto = $bd->prepare("SELECT c.*,t.* FROM Contenido_Formado c, Texto t WHERE c.`#contenido` = t.`#contenido`");
      $consulta_texto->execute();
      $numtexto = $consulta_texto->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultadosmultimedia" => $nummultimedia, "numresultadostexto" => $numtexto, "contentmultimedia" => $consulta_multimedia->fetchAll(PDO::FETCH_ASSOC),
      "contenttexto" => $consulta_texto->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener el contenido de un recurso.
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Corrección de errores.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultadosmultimedia, con el número de resultados obtenidos, del subtipo multimedia.
* @return numresultadostexto, con el número de resultados obtenidos, del subtipo texto.
* @return contentmultimedia, con el resultado de la búsqueda, para el subtipo multimedia.
* @return contenttexto, con el resultado de la búsqueda, para el subtipo texto.
*/
$app->get('/contenido/{idrecurso}', function(Request $request, Response $response) use($app){
  try{
      $id_recurso = $request->getAttribute('idrecurso');

      $bd = getConnection();
      $consulta_multimedia = $bd->prepare("SELECT c.*,m.* FROM Contenido_Formado c, Multimedia m WHERE c.`#contenido` = m.`#contenido` AND `#recurso` = ?");
      $consulta_multimedia->execute(array($id_recurso));
      $nummultimedia = $consulta_multimedia->rowCount();

      $consulta_texto = $bd->prepare("SELECT c.*,t.* FROM Contenido_Formado c, Texto t WHERE c.`#contenido` = t.`#contenido` AND `#recurso` = ?");
      $consulta_texto->execute(array($id_recurso));
      $numtexto = $consulta_texto->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultadosmultimedia" => $nummultimedia, "numresultadostexto" => $numtexto, "contentmultimedia" => $consulta_multimedia->fetchAll(PDO::FETCH_ASSOC),
      "contenttexto" => $consulta_texto->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos de un contenido concreto, dado su nombre.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultadosmultimedia, con el número de resultados obtenidos, del subtipo multimedia.
* @return numresultadostexto, con el número de resultados obtenidos, del subtipo texto.
* @return contentmultimedia, con el resultado de la búsqueda, para el subtipo multimedia.
* @return contenttexto, con el resultado de la búsqueda, para el subtipo texto.
*/
$app->get('/contenido/nombre/{nombre}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $nombre = $request->getAttribute('nombre');
      $nombre_busqueda = '%' . $nombre . '%';
      $consultamultimedia = $bd->prepare("SELECT c.*,m.* FROM Contenido_Formado c, Multimedia m WHERE nombre LIKE ? AND c.`#contenido` = m.`#contenido`");
      $consultamultimedia->execute(array($nombre_busqueda));
      $nummultimedia = $consultamultimedia->rowCount();
      $consultatexto = $bd->prepare("SELECT c.*,t.* FROM Contenido_Formado c, Texto t WHERE nombre LIKE ? AND c.`#contenido` = t.`#contenido`");
      $consultatexto->execute(array($nombre_busqueda));
      $numtexto = $consultatexto->rowCount();

      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultadosmultimedia" => $nummultimedia, "numresultadostexto" => $numtexto, "contentmultimedia" => $consultamultimedia->fetchAll(PDO::FETCH_ASSOC),
      "contenttexto" => $consultatexto->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener el nombre de un contenido concreto, dado su identificador.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/contenido/id/{id}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $id = $request->getAttribute('id');
      $consulta = $bd->prepare("SELECT nombre FROM Contenido_Formado WHERE `#contenido` = ?");
      $consulta->execute(array($id));
      $numresultados = $consulta->rowCount();

      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numresultados, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener el campo de información, más conocido como url para el subtipo multimedia y texto para el subtipo texto, de un contenido concreto.
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Corrección de errores.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/contenido/{qr}/{formato}/{idioma}', function(Request $request, Response $response) use($app){
  try{
      $qr = $request->getAttribute('qr');
      $formato = $request->getAttribute('formato');
      $idioma = $request->getAttribute('idioma');

      $bd = getConnection();
      $consulta = null;

      if($formato === "texto") {
        $consulta = $bd->prepare("SELECT t.informacion_texto AS info FROM Contenido_Formado c, Texto t, Recursos_Tiene r, Idioma i WHERE c.`#recurso` = r.`#recurso` AND c.`#contenido` = t.`#contenido` AND c.`#idioma` = i.`#idioma` AND i.codigo_idioma = ? AND r.codigo_qr = ?");
      }

      else {
        $consulta = $bd->prepare("SELECT m.url AS info FROM Contenido_Formado c, Multimedia m, Recursos_Tiene r, Idioma i WHERE c.`#recurso` = r.`#recurso` AND c.`#contenido` = m.`#contenido` AND c.`#idioma` = i.`#idioma` AND i.codigo_idioma = ? AND r.codigo_qr = ? AND m.formato = ?");
        $consulta->bindParam(3, $formato);
      }

      $consulta->bindParam(1, $idioma);
      $consulta->bindParam(2, $qr);

      $result = $consulta->execute();

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetch(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todo el equipamiento.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/equipamiento', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Equipamiento_Contiene");
      $consulta->execute();
      $numequipamiento = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numequipamiento, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener el equipamiento de una sala concreta.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/equipamiento/sala/{sala}', function(Request $request, Response $response) use($app){
  try{
      $sala = $request->getAttribute('sala');

      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Equipamiento_Contiene WHERE n_sala = ?");
      $consulta->execute(array($sala));
      $numequipamiento = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numequipamiento, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos de un equipamiento, dado su identificador.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/equipamiento/idequip/{idequip}', function(Request $request, Response $response) use($app){
  try{
      $id = $request->getAttribute('idequip');

      $bd = getConnection();
      $consulta = $bd->prepare("SELECT e.*, r.`#recurso` FROM Equipamiento_Contiene e, Recursos_Tiene r WHERE i.`#equipamiento` = r.`#equipamiento` AND i.`#equipamiento` = ?");
      $consulta->execute(array($id));
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener el nombre de un equipamiento, dado su identificador.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/equipamiento/idnombre/{id}', function(Request $request, Response $response) use($app){
  try{
      $id = $request->getAttribute('id');

      $bd = getConnection();
      $consulta = $bd->prepare("SELECT nombre FROM Equipamiento_Contiene WHERE `#equipamiento` = ?");
      $consulta->execute(array($id));
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos del equipamiento asociado al recurso proporcionado como argumento.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/equipamiento/idrecurso/{idrecurso}', function(Request $request, Response $response) use($app){
  try{
      $idrecurso = $request->getAttribute('idrecurso');

      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Equipamiento_Contiene WHERE `#equipamiento` IN (SELECT `#equipamiento` FROM Recursos_Tiene WHERE `#recurso` = ?)");
      $consulta->execute(array($idrecurso));
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos de un equipamiento concreto, dado su nombre.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/equipamiento/nombre/{nombre}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $nombre = $request->getAttribute('nombre');
      $nombre_busqueda = '%' . $nombre . '%';
      $consulta = $bd->prepare("SELECT * FROM Equipamiento_Contiene WHERE nombre LIKE ?");
      $consulta->execute(array($nombre_busqueda));
      $numresultados = $consulta->rowCount();

      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numresultados, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de modificar los datos de un contenido.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la modificación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún contenido con los identificadores proporcionados.
*/
$app->put('/contenido', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_contenido_modificar = $json->id_contenido_modificar;

    $bd = getConnection();

    $consultacontenido = $bd->prepare("SELECT * FROM Contenido_Formado WHERE `#contenido` = ?");
    $consultacontenido->execute(array($id_contenido_modificar));
    $contenidoantiguo = $consultacontenido->fetch(PDO::FETCH_ASSOC);
    $numfilas = $consultacontenido->rowCount();

    $consultamultimedia = $bd->prepare("SELECT * FROM Multimedia WHERE `#contenido` = ?");
    $consultamultimedia->execute(array($id_contenido_modificar));
    $multimediaantiguo = $consultamultimedia->fetch(PDO::FETCH_ASSOC);
    $nummultimedia = $consultamultimedia->rowCount();

    $consultatexto = $bd->prepare("SELECT * FROM Texto WHERE `#contenido` = ?");
    $consultatexto->execute(array($id_contenido_modificar));
    $textoantiguo = $consultatexto->fetch(PDO::FETCH_ASSOC);
    $numtexto = $consultatexto->rowCount();

    $idioma = $json->idioma;

    $nombre = $json->nombre;

    $formato = $json->formato;

    $info = $json->info;

    if ($numfilas == 1){
      $resultmodificacion = null;

      if(strcmp($formato, "texto") == 0){
        $eliminaciontexto = $bd->prepare("DELETE FROM Texto WHERE `#contenido` = ?");
        $eliminaciontexto->bindParam(1, $id_contenido_modificar);
        $resultmodificacion = $eliminaciontexto->execute();
      }

      else{
        $eliminacionmultimedia = $bd->prepare("DELETE FROM Multimedia WHERE `#contenido` = ? AND formato = ?");
        $eliminacionmultimedia->bindParam(1, $id_contenido_modificar);
        $eliminacionmultimedia->bindParam(2, $formato);
        $resultmodificacion = $eliminacionmultimedia->execute();
      }

      $modificacioncontenido = $bd->prepare("UPDATE Contenido_Formado SET `#idioma` = ?, nombre = ? WHERE `#contenido` = ?");
      $modificacioncontenido->bindParam(1, $idioma);
      $modificacioncontenido->bindParam(2, $nombre);
      $modificacioncontenido->bindParam(3, $id_contenido_modificar);
      $resultmodificacion = $resultmodificacion and $modificacioncontenido->execute();

      if(strcmp($formato, "texto") != 0){
        $adicionmultimedia = $bd->prepare("INSERT INTO Multimedia VALUES(?, ?, ?)");
        $adicionmultimedia->bindParam(1, $id_contenido_modificar);
        $adicionmultimedia->bindParam(2, $formato);
        $adicionmultimedia->bindParam(3, $info);
        $resultmodificacion = $resultmodificacion and $adicionmultimedia->execute();
      }

      else{
        $adiciontexto = $bd->prepare("INSERT INTO Texto VALUES(?, ?)");
        $adiciontexto->bindParam(1, $id_contenido_modificar);
        $adiciontexto->bindParam(2, $info);
        $resultmodificacion = $resultmodificacion and $adiciontexto->execute();
      }

      if($resultmodificacion){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Contenido modificado correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la modificación del contenido
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe contenido a modificar con el identificador proporcionado
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de modificar los datos de un equipamiento.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la modificación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún equipamiento con los identificadores proporcionados.
*/
$app->put('/equipamiento', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_equipamiento_modificar = $json->id_equipamiento_modificar;

    $bd = getConnection();

    $consultaequipamiento = $bd->prepare("SELECT * FROM Equipamiento_Contiene WHERE `#equipamiento` = ?");
    $consultaequipamiento->execute(array($id_equipamiento_modificar));
    $equipamientoantiguo = $consultaequipamiento->fetch(PDO::FETCH_ASSOC);
    $numfilas = $consultaequipamiento->rowCount();

    $ubicacion = $json->ubicacion;

    $n_sala = $json->n_sala;

    $nombre = $json->nombre;

    $proveedor = $json->proveedor;

    $img = $json->img;

    $desc_img = $json->desc_img;

    if ($numfilas == 1){
      $modificacionequipamiento = $bd->prepare("UPDATE Equipamiento_Contiene SET ubicacion = ?, n_sala = ?, nombre = ?, proveedor = ?, img = ?, desc_img = ? WHERE `#equipamiento` = ?");
      $modificacionequipamiento->bindParam(1, $ubicacion);
      $modificacionequipamiento->bindParam(2, $n_sala);
      $modificacionequipamiento->bindParam(3, $nombre);
      $modificacionequipamiento->bindParam(4, $proveedor);
      $modificacionequipamiento->bindParam(5, $img);
      $modificacionequipamiento->bindParam(6, $desc_img);
      $modificacionequipamiento->bindParam(7, $id_equipamiento_modificar);
      $resultmodificacion = $modificacionequipamiento->execute();

      if($resultmodificacion){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Equipamiento modificado correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la modificación del equipamiento
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe equipamiento a modificar con el identificador proporcionado
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de modificar los datos de un equipamiento, exceptuando el campo de la foto.
*
* @author Daniel Soto del Ojo.
*
* @return 1, si la modificación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún equipamiento con los identificadores proporcionados.
*/
$app->put('/equipamiento/nofoto', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_equipamiento_modificar = $json->id_equipamiento_modificar;

    $bd = getConnection();

    $consultaequipamiento = $bd->prepare("SELECT * FROM Equipamiento_Contiene WHERE `#equipamiento` = ?");
    $consultaequipamiento->execute(array($id_equipamiento_modificar));
    $equipamientoantiguo = $consultaequipamiento->fetch(PDO::FETCH_ASSOC);
    $numfilas = $consultaequipamiento->rowCount();

    $ubicacion = $json->ubicacion;

    $n_sala = $json->n_sala;

    $nombre = $json->nombre;

    $proveedor = $json->proveedor;

    $desc_img = $json->desc_img;

    if ($numfilas == 1){
      $modificacionequipamiento = $bd->prepare("UPDATE Equipamiento_Contiene SET ubicacion = ?, n_sala = ?, nombre = ?, proveedor = ?, desc_img = ? WHERE `#equipamiento` = ?");
      $modificacionequipamiento->bindParam(1, $ubicacion);
      $modificacionequipamiento->bindParam(2, $n_sala);
      $modificacionequipamiento->bindParam(3, $nombre);
      $modificacionequipamiento->bindParam(4, $proveedor);
      $modificacionequipamiento->bindParam(5, $desc_img);
      $modificacionequipamiento->bindParam(6, $id_equipamiento_modificar);
      $resultmodificacion = $modificacionequipamiento->execute();

      if($resultmodificacion){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Equipamiento modificado correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la modificación del equipamiento
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe equipamiento a modificar con el identificador proporcionado
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de eliminar un contenido.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la eliminación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún contenido con los identificadores proporcionados.
*/
$app->delete('/contenido', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_contenido = $json->id_contenido;
    $formato = $json->formato;

    $bd = getConnection();

    $consultacontenido = $bd->prepare("SELECT * FROM Contenido_Formado WHERE `#contenido` = ?");
    $consultacontenido->execute(array($id_contenido));
    $numfilas = $consultacontenido->rowCount();

    $consultamultimedia = $bd->prepare("SELECT * FROM Multimedia WHERE `#contenido` = ?");
    $consultamultimedia->execute(array($id_contenido));
    $nummultimedia = $consultamultimedia->rowCount();

    if ($numfilas == 1){
        $resultformato = null;

        if (strcmp($formato, "texto") != 0){
            $eliminacionmultimedia = $bd->prepare("DELETE FROM Multimedia WHERE `#contenido` = ? AND formato = ?");
            $eliminacionmultimedia->bindParam(1, $id_contenido);
            $eliminacionmultimedia->bindParam(2, $formato);
            $resultformato = $eliminacionmultimedia->execute();
        }

        else{
            $eliminaciontexto = $bd->prepare("DELETE FROM Texto WHERE `#contenido` = ?");
            $eliminaciontexto->bindParam(1, $id_contenido);
            $resultformato = $eliminaciontexto->execute();
        }

        if (strcmp($formato, "texto") == 0 or $nummultimedia == 1){
            $eliminacioncontenido = $bd->prepare("DELETE FROM Contenido_Formado WHERE `#contenido` = ?");
            $eliminacioncontenido->bindParam(1, $id_contenido);
            $resulteliminacion = $eliminacioncontenido->execute();
        }

        if($resulteliminacion and $resultformato){
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(200);
            $response->getBody()->write(json_encode(array("res" => 1)));   ## Contenido eliminado correctamente
            $bd = null;
        }

        else{
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(400);
            $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la eliminación del contenido
            $bd = null;
        }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe contenido con el identificador proporcionado
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de eliminar un equipamiento.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la eliminación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún equipamiento con los identificadores proporcionados.
*/
$app->delete('/equipamiento', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_equipamiento = $json->id_equipamiento;

    $bd = getConnection();
    $consulta = $bd->prepare("SELECT * FROM Equipamiento_Contiene WHERE `#equipamiento` = ?");
    $consulta->execute(array($id_equipamiento));
    $numfilas = $consulta->rowCount();

    if ($numfilas > 0){
        $consultarecurso = $bd->prepare("SELECT * FROM Recursos_Tiene WHERE `#equipamiento` = ?");
        $consultarecurso->bindParam(1, $id_equipamiento);
        $consultarecurso->execute();
        $numrecursos = $consultarecurso->rowCount();

        $resulteliminacion1 = true;

        if ($numrecursos > 0){
            $resultcompuesta = null;
            $resultmultimedia = null;
            $resulttexto = null;
            $resultcontenido = null;
            $resultrecurso = null;

            $recursoconcreto = $consultarecurso->fetch(PDO::FETCH_ASSOC);

            for ($i = 0 ; $i < $numrecursos ; $i++){
                $eliminacioncompuesta = $bd->prepare("DELETE FROM Compuesta WHERE `#recurso` = ?");
                $eliminacioncompuesta->bindParam(1, $recursoconcreto["#recurso"]);
                $resultcompuesta = $eliminacioncompuesta->execute();

                $eliminacionmultimedia = $bd->prepare("DELETE FROM Multimedia WHERE `#contenido` IN (SELECT `#contenido` FROM Contenido_Formado WHERE `#recurso` = ?)");
                $eliminacionmultimedia->bindParam(1, $recursoconcreto["#recurso"]);
                $resultmultimedia = $eliminacionmultimedia->execute();

                $eliminaciontexto = $bd->prepare("DELETE FROM Texto WHERE `#contenido` IN (SELECT `#contenido` FROM Contenido_Formado WHERE `#recurso` = ?)");
                $eliminaciontexto->bindParam(1, $recursoconcreto["#recurso"]);
                $resulttexto = $eliminaciontexto->execute();

                $eliminacioncontenido = $bd->prepare("DELETE FROM Contenido_Formado WHERE `#recurso` = ?");
                $eliminacioncontenido->bindParam(1, $recursoconcreto["#recurso"]);
                $resultcontenido = $eliminacioncontenido->execute();

                $eliminacionrecurso = $bd->prepare("DELETE FROM Recursos_Tiene WHERE `#recurso` = ?");
                $eliminacionrecurso->bindParam(1, $recursoconcreto["#recurso"]);
                $resultrecurso = $eliminacionrecurso->execute();

                $resulteliminacion1 = $resulteliminacion1 and $resultcompuesta and $resultmultimedia and $resulttexto and $resultcontenido and $resultrecurso;

                $recursoconcreto = $consultarecurso->fetch(PDO::FETCH_ASSOC);
            }
        }

        $eliminacionequipamiento = $bd->prepare("DELETE FROM Equipamiento_Contiene WHERE `#equipamiento` = ?");
        $eliminacionequipamiento->bindParam(1, $id_equipamiento);
        $resulteliminacion2 = $eliminacionequipamiento->execute();

        if($resulteliminacion1 and $resulteliminacion2){
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(200);
            $response->getBody()->write(json_encode(array("res" => 1)));   ## Equipamiento eliminado correctamente
            $bd = null;
        }

        else{
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(400);
            $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la eliminación del equipamiento
            $bd = null;
        }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe equipamiento con el identificador proporcionado
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de añadir un museo.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la adición se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si ya existía un museo con los identificadores proporcionados.
* @return content, con los datos del museo repetido en caso de devolver un código de respuesta 2.
*/
$app->post('/museo', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $nombre_museo = $json->nombre_museo;
    $cif_museo = $json->cif;
    $correo_museo = $json->correo;
    $direccion_museo = $json->direccion;
    $telefono_museo = $json->telefono;

    $bd = getConnection();
    $consulta = $bd->prepare("SELECT * FROM Datos_Museo WHERE nombre_museo = ?");
    $consulta->execute(array($nombre_museo));
    $numfilas = $consulta->rowCount();

    if ($numfilas == 0){
      $insercionmuseo = $bd->prepare("INSERT INTO Datos_Museo VALUES(?, ?, ?, ?, ?)");
      $insercionmuseo->bindParam(1, $nombre_museo);
      $insercionmuseo->bindParam(2, $cif_museo);
      $insercionmuseo->bindParam(3, $correo_museo);
      $insercionmuseo->bindParam(4, $direccion_museo);
      $insercionmuseo->bindParam(5, $telefono_museo);
      $resultmuseo = $insercionmuseo->execute();

      if($resultmuseo){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Museo añadido correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la adición del museo
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 2, "content" => $consulta->fetch(PDO::FETCH_ASSOC))));   ## Ya existe un museo con el nombre introducido
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todos los museos.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/museo', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Museo");
      $consulta->execute();
      $nummuseos = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $nummuseos, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos de un museo, dado su nombre.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/museo/{nombremuseo}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $nombre_museo = $request->getAttribute('nombremuseo');
      $consulta = $bd->prepare("SELECT * FROM Museo WHERE nombre_museo = ?");
      $consulta->execute(array($nombre_museo));
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de añadir un recurso.
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Corrección de errores.
*
* @return 1, si la adición se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
*/
$app->post('/recurso', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $codigo_qr_recurso = $json->codigo_qr;
    $nombre_recurso = $json->nombre;
    $id_equipamiento_recurso = $json->id_equipamiento;
    $img_recurso = $json->img;
    $desc_img_recurso = $json->desc_img;

    $bd = getConnection();
    $consulta = $bd->prepare("SELECT * FROM Recursos_Tiene WHERE codigo_qr = ?");
    $consulta->execute(array($codigo_qr_recurso));
    $numfilas = $consulta->rowCount();

    if ($numfilas == 0){
        $insercionrecurso = $bd->prepare("INSERT INTO Recursos_Tiene (fecha_creacion, codigo_qr, nombre, ultima_fecha_modificacion, `#equipamiento`, img, desc_img) VALUES(NOW(), ?, ?, NOW(), ?, ?, ?)");
        $insercionrecurso->bindParam(1, $codigo_qr_recurso);
        $insercionrecurso->bindParam(2, $nombre_recurso);
        $insercionrecurso->bindParam(3, $id_equipamiento_recurso);
        $insercionrecurso->bindParam(4, $img_recurso);
        $insercionrecurso->bindParam(5, $desc_img_recurso);
        $resultrecurso = $insercionrecurso->execute();

        if($resultrecurso){
            $consultaconcreta = $bd->prepare("SELECT * FROM Recursos_Tiene ORDER BY `#recurso` DESC");
            $consultaconcreta->execute();
            $recursoconcreto = $consultaconcreta->fetch(PDO::FETCH_ASSOC);

            // Creación de QR
            $qrCode = new QrCode();
            $qrCode
            ->setText($recursoconcreto['#recurso'])
            ->setSize(300)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabelFontSize(16)
            ->setImageType(QrCode::IMAGE_TYPE_PNG)
            ;

            // Guardar QR
            $name = 'qr_' . $recursoconcreto['#recurso'] . '.png';
            $relative_route = '/contenido/qr/' . $name;
            $filename = realpath($_SERVER['DOCUMENT_ROOT']) . $relative_route;
            $archivo = $qrCode->save($filename);

            $insercionqr = $bd->prepare("UPDATE Recursos_Tiene SET img_qr = ? WHERE `#recurso` = ?");
            $insercionqr->bindParam(1, $relative_route);
            $insercionqr->bindParam(2, $recursoconcreto['#recurso']);
            $resultqr = $insercionqr->execute();

            if ($resultqr){
                $response->withHeader("Content-type", "application/json");
                $response->withStatus(200);
                $response->getBody()->write(json_encode(array("res" => 1)));   ## Recurso añadido correctamente
                $bd = null;
            }

            else{
                $response->withHeader("Content-type", "application/json");
                $response->withStatus(400);
                $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la adición del recurso
                $bd = null;
            }
        }

        else{
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(400);
            $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la adición del recurso
            $bd = null;
        }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 2, "content" => $consulta->fetch(PDO::FETCH_ASSOC))));   ## Ya existe un recurso con el codigo_qr introducido
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todos los recursos.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/recurso', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Recursos_Tiene");
      $consulta->execute();
      $numrecursos = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numrecursos, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todos los recursos que se encuentran en una sala dada y como parte de la composición de una guía dada.
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Corrección de errores.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/recurso/sala/{idsala}/{idguia}', function(Request $request, Response $response) {
  try{
      $id = $request->getAttribute('idsala');
      $guia = $request->getAttribute('idguia');
      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Recursos_Tiene r, Equipamiento_Contiene e, Compuesta c WHERE r.`#equipamiento` = e.`#equipamiento` AND e.n_sala = ? AND r.`#recurso` = c.`#recurso` AND c.`#guia` = ?");
      $consulta->execute(array($id, $guia));
      $numrecursos = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numrecursos, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todos los recursos asociados a un equipamiento.
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Corrección de errores.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/recurso/idequip/{idequip}', function(Request $request, Response $response) use($app){
  try{
      $idequip = $request->getAttribute('idequip');

      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Recursos_Tiene WHERE `#equipamiento` = ?");
      $consulta->execute(array($idequip));
      $numrecursos = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numrecursos, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos de un recurso concreto, dado su nombre.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/recurso/nombre/{nombre}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $nombre = $request->getAttribute('nombre');
      $nombre_busqueda = '%' . $nombre . '%';
      $consulta = $bd->prepare("SELECT * FROM Recursos_Tiene WHERE nombre LIKE ?");
      $consulta->execute(array($nombre_busqueda));
      $numresultados = $consulta->rowCount();

      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numresultados, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener el nombre de un recurso concreto, dado su identificador.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/recurso/id/{id}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $id = $request->getAttribute('id');
      $consulta = $bd->prepare("SELECT nombre FROM Recursos_Tiene WHERE `#recurso` = ?");
      $consulta->execute(array($id));
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todos los recursos que se encuentran en una sala dada.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/recurso/sala/{idsala}', function(Request $request, Response $response) {
  try{
      $id = $request->getAttribute('idsala');
      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Recursos_Tiene r, Equipamiento_Contiene e WHERE r.`#equipamiento` = e.`#equipamiento` AND e.n_sala = ?");
      $consulta->execute(array($id));
      $numrecursos = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numrecursos, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de modificar los datos de un recurso.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la modificación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún recurso con los identificadores proporcionados.
* @return 3, si ya existe un recurso con el código QR proporcionado.
*/
$app->put('/recurso', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_recurso_modificar = $json->id_recurso_modificar;

    $bd = getConnection();

    $consultarecurso = $bd->prepare("SELECT * FROM Recursos_Tiene WHERE `#recurso` = ?");
    $consultarecurso->execute(array($id_recurso_modificar));
    $recursoantiguo = $consultarecurso->fetch(PDO::FETCH_ASSOC);
    $numfilas = $consultarecurso->rowCount();

    $codigo_qr = $json->codigo_qr;

    $nombre = $json->nombre;

    $img = $json->img;

    $desc_img = $json->desc_img;

    if ($numfilas == 1){
      $consultaqr = $bd->prepare("SELECT * FROM Recursos_Tiene WHERE codigo_qr = ? AND `#recurso` != ?");
      $consultaqr->execute(array($codigo_qr, $id_recurso_modificar));
      $numfilasqr = $consultaqr->rowCount();

      if ($numfilasqr == 0){
        $modificacionrecurso = $bd->prepare("UPDATE Recursos_Tiene SET codigo_qr = ?, nombre = ?, ultima_fecha_modificacion = NOW(), img = ?, desc_img = ? WHERE `#recurso` = ?");
        $modificacionrecurso->bindParam(1, $codigo_qr);
        $modificacionrecurso->bindParam(2, $nombre);
        $modificacionrecurso->bindParam(3, $img);
        $modificacionrecurso->bindParam(4, $desc_img);
        $modificacionrecurso->bindParam(5, $id_recurso_modificar);
        $resultmodificacion = $modificacionrecurso->execute();

        if($resultmodificacion){
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(200);
            $response->getBody()->write(json_encode(array("res" => 1)));   ## Recurso modificado correctamente
            $bd = null;
        }

        else{
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(400);
            $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la modificación del recurso
            $bd = null;
        }
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 3)));   ## Ya existe un recurso con el código QR proporcionado
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe recurso a modificar con el identificador proporcionado
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de modificar los datos de un recurso, exceptuando el campo de la foto.
*
* @author Daniel Soto del Ojo.
*
* @return 1, si la modificación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún recurso con los identificadores proporcionados.
* @return 3, si ya existe un recurso con el código QR proporcionado.
*/
$app->put('/recurso/nofoto', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_recurso_modificar = $json->id_recurso_modificar;

    $bd = getConnection();

    $consultarecurso = $bd->prepare("SELECT * FROM Recursos_Tiene WHERE `#recurso` = ?");
    $consultarecurso->execute(array($id_recurso_modificar));
    $recursoantiguo = $consultarecurso->fetch(PDO::FETCH_ASSOC);
    $numfilas = $consultarecurso->rowCount();

    $codigo_qr = $json->codigo_qr;

    $nombre = $json->nombre;

    $desc_img = $json->desc_img;

    if ($numfilas == 1){
      $consultaqr = $bd->prepare("SELECT * FROM Recursos_Tiene WHERE codigo_qr = ? AND `#recurso` != ?");
      $consultaqr->execute(array($codigo_qr, $id_recurso_modificar));
      $numfilasqr = $consultaqr->rowCount();

      if ($numfilasqr == 0){
        $modificacionrecurso = $bd->prepare("UPDATE Recursos_Tiene SET codigo_qr = ?, nombre = ?, ultima_fecha_modificacion = NOW(), desc_img = ? WHERE `#recurso` = ?");
        $modificacionrecurso->bindParam(1, $codigo_qr);
        $modificacionrecurso->bindParam(2, $nombre);
        $modificacionrecurso->bindParam(3, $desc_img);
        $modificacionrecurso->bindParam(4, $id_recurso_modificar);
        $resultmodificacion = $modificacionrecurso->execute();

        if($resultmodificacion){
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(200);
            $response->getBody()->write(json_encode(array("res" => 1)));   ## Recurso modificado correctamente
            $bd = null;
        }

        else{
            $response->withHeader("Content-type", "application/json");
            $response->withStatus(400);
            $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la modificación del recurso
            $bd = null;
        }
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 3)));   ## Ya existe un recurso con el código QR proporcionado
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe recurso a modificar con el identificador proporcionado
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de eliminar un recurso.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la eliminación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ningún recurso con los identificadores proporcionados.
*/
$app->delete('/recurso', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_recurso = $json->id_recurso;

    $bd = getConnection();
    $consulta = $bd->prepare("SELECT * FROM Recursos_Tiene WHERE `#recurso` = ?");
    $consulta->execute(array($id_recurso));
    $numfilas = $consulta->rowCount();

    if ($numfilas == 1){
      $eliminacioncompuesta = $bd->prepare("DELETE FROM Compuesta WHERE `#recurso` = ?");
      $eliminacioncompuesta->bindParam(1, $id_recurso);
      $resultcompuesta = $eliminacioncompuesta->execute();

      $eliminacionmultimedia = $bd->prepare("DELETE FROM Multimedia WHERE `#contenido` IN (SELECT `#contenido` FROM Contenido_Formado WHERE `#recurso` = ?)");
      $eliminacionmultimedia->bindParam(1, $id_recurso);
      $resultmultimedia = $eliminacionmultimedia->execute();

      $eliminaciontexto = $bd->prepare("DELETE FROM Texto WHERE `#contenido` IN (SELECT `#contenido` FROM Contenido_Formado WHERE `#recurso` = ?)");
      $eliminaciontexto->bindParam(1, $id_recurso);
      $resulttexto = $eliminaciontexto->execute();

      $eliminacioncontenido = $bd->prepare("DELETE FROM Contenido_Formado WHERE `#recurso` = ?");
      $eliminacioncontenido->bindParam(1, $id_recurso);
      $resultcontenido = $eliminacioncontenido->execute();

      $eliminacionrecurso = $bd->prepare("DELETE FROM Recursos_Tiene WHERE `#recurso` = ?");
      $eliminacionrecurso->bindParam(1, $id_recurso);
      $resultrecurso = $eliminacionrecurso->execute();

      if($resultcompuesta and $resultmultimedia and $resulttexto and $resultcontenido and $resultrecurso){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Recurso eliminado correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la eliminación del recurso
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe un recurso con el identificador introducido
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de añadir una sala.
*
* @author Baltasar Ruiz Hernández.
* @author Daniel Soto del Ojo: Corrección de errores.
*
* @return 1, si la adición se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
*/
$app->post('/sala', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $n_sala = $json->n_sala;
    $descripcion_sala = $json->descripcion_sala;
    $planta_sala = $json->planta;

    $bd = getConnection();

    $insercionsala = $bd->prepare("INSERT INTO Salas (n_sala, descripcion_sala, planta) VALUES(?, ?, ?)");
    $insercionsala->bindParam(1, $n_sala);
    $insercionsala->bindParam(2, $descripcion_sala);
    $insercionsala->bindParam(3, $planta_sala);
    $resultsala = $insercionsala->execute();

    if($resultsala){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Sala añadida correctamente
        $bd = null;
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la adición de la sala
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener todas las salas.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return numresultados, con el número de resultados obtenidos.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/sala', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $consulta = $bd->prepare("SELECT * FROM Salas");
      $consulta->execute();
      $numsalas = $consulta->rowCount();
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "numresultados" => $numsalas, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos de una sala concreta, dado su identificador.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/sala/id/{idsala}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $idsala = $request->getAttribute('idsala');
      $consulta = $bd->prepare("SELECT * FROM Salas WHERE `#sala` = ?");
      $consulta->execute(array($idsala));
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de obtener los datos de una sala concreta, dado su nombre.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, en todos los casos, pues se considera que la búsqueda se completará siempre, independientemente del contenido de la consulta.
* @return content, con el resultado de la búsqueda.
*/
$app->get('/sala/nombre/{nombresala}', function(Request $request, Response $response) {
  try{
      $bd = getConnection();
      $nombresala = $request->getAttribute('nombresala');
      $nombre_busqueda = '%' . $nombresala . '%';
      $consulta = $bd->prepare("SELECT * FROM Salas WHERE n_sala LIKE ?");
      $consulta->execute(array($nombre_busqueda));
      $bd = null;

      $response->withHeader("Content-type", "application/json");
      $response->withStatus(200);
      $response->getBody()->write(json_encode(array("res" => 1, "content" => $consulta->fetchAll(PDO::FETCH_ASSOC))));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de modificar los datos de una sala.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la modificación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ninguna sala con los identificadores proporcionados.
*/
$app->put('/sala', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_sala_modificar = $json->id_sala_modificar;

    $bd = getConnection();
    $consultasala = $bd->prepare("SELECT * FROM Salas WHERE `#sala` = ?");
    $consultasala->execute(array($id_sala_modificar));
    $salaantigua = $consultasala->fetch(PDO::FETCH_ASSOC);
    $numfilas = $consultasala->rowCount();

    $n_sala = $json->n_sala;

    $descripcion_sala = $json->descripcion_sala;

    $planta = $json->planta;

    if ($numfilas == 1){
      $modificacionequipamiento = $bd->prepare("UPDATE Equipamiento_Contiene SET n_sala = ? WHERE n_sala = ?");
      $modificacionequipamiento->bindParam(1, $n_sala);
      $modificacionequipamiento->bindParam(2, $salaantigua['n_sala']);
      $resultmodificacion1 = $modificacionequipamiento->execute();

      $modificacionsala = $bd->prepare("UPDATE Salas SET n_sala = ?, descripcion_sala = ?, planta = ? WHERE `#sala` = ?");
      $modificacionsala->bindParam(1, $n_sala);
      $modificacionsala->bindParam(2, $descripcion_sala);
      $modificacionsala->bindParam(3, $planta);
      $modificacionsala->bindParam(4, $id_sala_modificar);
      $resultmodificacion2 = $modificacionsala->execute();

      if($resultmodificacion1 and $resultmodificacion2){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Sala modificada correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la modificación de la sala
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe sala con los datos introducidos
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de eliminar una sala.
*
* @author Baltasar Ruiz Hernández.
*
* @return 1, si la eliminación se ha realizado correctamente.
* @return -1, si ha ocurrido un error durante la realización de la operación.
* @return 2, si no existe ninguna sala con los identificadores proporcionados.
*/
$app->delete('/sala', function(Request $request, Response $response) use($app){
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $id_sala_traspasar = $json->id_sala_traspasar;
    $id_sala_eliminar = $json->id_sala_eliminar;

    $bd = getConnection();

    $consulta_traspasar = $bd->prepare("SELECT * FROM Salas WHERE `#sala` = ?");
    $consulta_traspasar->execute(array($id_sala_traspasar));
    $numtraspasar = $consulta_traspasar->rowCount();

    $consulta_eliminar = $bd->prepare("SELECT * FROM Salas WHERE `#sala` = ?");
    $consulta_eliminar->execute(array($id_sala_eliminar));
    $numeliminar = $consulta_eliminar->rowCount();

    if ($numtraspasar == 1 and $numeliminar == 1){
      $modificacionequipamiento = $bd->prepare("UPDATE Equipamiento_Contiene SET n_sala = ? WHERE n_sala = ?");
      $modificacionequipamiento->bindParam(1, $id_sala_traspasar);
      $modificacionequipamiento->bindParam(2, $id_sala_eliminar);
      $resultmodificacion = $modificacionequipamiento->execute();

      $eliminacionsala = $bd->prepare("DELETE FROM Salas WHERE `#sala` = ?");
      $eliminacionsala->bindParam(1, $id_sala_eliminar);
      $resulteliminacion = $eliminacionsala->execute();

      if($resultmodificacion and $resulteliminacion){
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(200);
        $response->getBody()->write(json_encode(array("res" => 1)));   ## Sala eliminada correctamente
        $bd = null;
      }

      else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(400);
        $response->getBody()->write(json_encode(array("res" => -1)));   ## Error durante la eliminación de la sala
        $bd = null;
      }
    }

    else{
        $response->withHeader("Content-type", "application/json");
        $response->withStatus(404);
        $response->getBody()->write(json_encode(array("res" => 2)));   ## No existe sala a eliminar o para traspasar con los datos introducidos
        $bd = null;
    }
  } catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

/**
* Operación encargada de enviar una sugerencia.
*
* @author Elías Méndez García.
*
* @return 1, si la operación se ha completado correctamente.
*/
$app->post('/sugerencias', function(Request $request, Response $response) use($app){
  require '../phpmailer/vendor/autoload.php';
  try{
    $body = $request->getBody();
    $json = json_decode($body);

    $nombre = $json->nombre;
    $mensaje = $json->mensaje;

    $mail = new PHPMailer();

    if (file_exists('conf/config_email.xml')) {
        $xml = simplexml_load_file('conf/config_email.xml');
    }

    else {
        exit('Error abriendo config_email.xml');
    }

    $mail->CharSet = "UTF-8";
    $mail->IsSMTP();
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->Host = $xml->server;
    //$mail->SMTPDebug  = 2;

    $mail->SMTPAuth = true;
    $mail->SMTPSecure = $xml->security;
    $mail->Host = $xml->server;
    $mail->Port = $xml->port;
    $mail->Username = $xml->user;
    $mail->Password = $xml->pass;

    $mail->SetFrom($xml->email, $xml->name);
    $mail->AddReplyTo($xml->email, $nombre);

    $mail->Subject = "Sugerencia de " . $nombre;
    $mail->MsgHTML($mensaje);

    $mail->AddAddress($xml->to);

    if(!$mail->Send()) {
       echo "Mailer Error: " . $mail->ErrorInfo;
    }

    $response->withHeader("Content-type", "application/json");
    $response->withStatus(200);
    $response->getBody()->write(json_encode(array("res" => 1)));
  }
  catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
});

?>
