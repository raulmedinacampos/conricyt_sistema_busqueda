<?php
require 'flight/Flight.php';

/* Registro y conexión a base de datos */
$host	= "127.0.0.1";
$user	= "root";
$pass	= "";
$db		= "conricyt_evaluacion";
$dbh	= "mysql:host=$host;port=3306;dbname=$db";

Flight::register('db', 'PDO', array($dbh, $user, $pass), function ($db) {
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
});

/* Ruteos */
Flight::route('POST /login/', function() {
	session_start();

	$con = Flight::db();

	$usuario = (isset($_POST['usuario'])) ? addslashes($_POST['usuario']) : "";
	$password = (isset($_POST['password'])) ? addslashes($_POST['password']) : "";

	$query = "SELECT id_usuario, login FROM usuario WHERE login = ? AND password = ? LIMIT 1";
	$stm = $con->prepare($query);
	$stm->execute(array($usuario, $password));
	$usr = $stm->fetchObject();

	if ( $stm->rowCount() > 0 ) {
		$_SESSION['id_usr'] = $usr->id_usuario;
		$_SESSION['usuario'] = $usr->login;
	}

	Flight::redirect('/');
});

Flight::route('/salir/', function() {
	session_start();

	$_SESSION = array();
	session_destroy();
	session_unset();

	Flight::redirect('/');
});

Flight::route('/', function() {
	session_start();
	
	Flight::render('header');
	
	if ( !$_SESSION ) {
		Flight::render('login');
	} else {
		$con = Flight::db();
		$respuestas = "";
		$observaciones = "";
		
		$query = "SELECT id_evaluacion FROM evaluacion WHERE usuario = ? AND estatus > 0";
		$stm = $con->prepare($query);
		$stm->execute(array($_SESSION['id_usr']));
		$evaluacion = $stm->fetchObject();
		
		if ( sizeof($evaluacion) > 0 ) {
			$query = "SELECT id_respuesta, respuesta, seccion, subseccion, pregunta, proveedor FROM respuesta WHERE evaluacion = ? AND estatus = 1";
			$stm = $con->prepare($query);
			$stm->execute(array($evaluacion->id_evaluacion));
			$respuestas = $stm->fetchAll(PDO::FETCH_OBJ);
			
			$respuesta_arr = array();
			$observaciones_arr = array();
			
			foreach ( $respuestas as $resp ) {
				if ( !$resp->proveedor ) {
					$observaciones_arr[$resp->seccion."_".(int)$resp->subseccion]['id_respuesta'] = $resp->id_respuesta;
					$observaciones_arr[$resp->seccion."_".(int)$resp->subseccion]['respuesta'] = $resp->respuesta;
				} else {
					$respuesta_arr[$resp->pregunta."_".(int)$resp->proveedor]['id_respuesta'] = $resp->id_respuesta;
					$respuesta_arr[$resp->pregunta."_".(int)$resp->proveedor]['respuesta'] = $resp->respuesta;
				}
			}
			
			$observaciones = $observaciones_arr;
			$respuesta = $respuesta_arr;
		}
		
		$query = "SELECT id_seccion, nombre FROM seccion WHERE estatus = 1 ORDER BY id_seccion";
		$stm = $con->prepare($query);
		$stm->execute();
		$secciones = $stm->fetchAll(PDO::FETCH_OBJ);
		
		$secciones_arr = array();
		
		foreach ($secciones as $seccion ) {
			$query = "SELECT COUNT(*) AS total FROM subseccion WHERE seccion = ? AND estatus = 1";
			$stm = $con->prepare($query);
			$stm->execute(array($seccion->id_seccion));
			$row = $stm->fetchObject();
			$sb = ( $row->total > 0 ) ? 1 : 0;
			$dato = new stdClass();
			$dato->id_seccion = $seccion->id_seccion;
			$dato->nombre = $seccion->nombre;
			$dato->sub = $sb;
			$secciones_arr[] = $dato;
		}
		
		$secciones = $secciones_arr;
		
		$query = "SELECT id_subseccion, nombre, seccion FROM subseccion WHERE estatus = 1 ORDER BY id_subseccion";
		$stm = $con->prepare($query);
		$stm->execute();
		$subsecciones = $stm->fetchAll(PDO::FETCH_OBJ);
		
		$query = "SELECT id_proveedor, nombre FROM proveedor WHERE estatus = 1 ORDER BY nombre";
		$stm = $con->prepare($query);
		$stm->execute();
		$proveedores = $stm->fetchAll(PDO::FETCH_OBJ);
		
		$query = "SELECT id_pregunta, pregunta, seccion, subseccion, titulo FROM pregunta WHERE estatus = 1 ORDER BY id_pregunta";
		$stm = $con->prepare($query);
		$stm->execute();
		$preguntas = $stm->fetchAll(PDO::FETCH_OBJ);
		
		$datos = array(
				'evaluacion'	=> $evaluacion,
				'secciones'		=> $secciones,
				'subsecciones'	=> $subsecciones,
				'proveedores'	=> $proveedores,
				'preguntas'		=> $preguntas,
				'respuesta'		=> $respuesta,
				'observaciones'	=> $observaciones
		);
		Flight::render('evaluacion', $datos);
	}
	Flight::render('footer');
});

Flight::route('/guardarEvaluacion/', function() {
	session_start();
	
	$con = Flight::db();
	$fecha = date('Y-m-d H:i:s');
	
	$query = "SELECT id_seccion, nombre FROM seccion WHERE estatus = 1 ORDER BY id_seccion";
	$stm = $con->prepare($query);
	$stm->execute();
	$secciones = $stm->fetchAll(PDO::FETCH_OBJ);
	
	$secciones_arr = array();
	
	foreach ($secciones as $seccion ) {
		$query = "SELECT COUNT(*) AS total FROM subseccion WHERE seccion = ? AND estatus = 1";
		$stm = $con->prepare($query);
		$stm->execute(array($seccion->id_seccion));
		$row = $stm->fetchObject();
		$sb = ( $row->total > 0 ) ? 1 : 0;
		$dato = new stdClass();
		$dato->id_seccion = $seccion->id_seccion;
		$dato->nombre = $seccion->nombre;
		$dato->sub = $sb;
		$secciones_arr[] = $dato;
	}
	
	$secciones = $secciones_arr;
	
	$query = "SELECT id_subseccion, nombre, seccion FROM subseccion WHERE estatus = 1 ORDER BY id_subseccion";
	$stm = $con->prepare($query);
	$stm->execute();
	$subsecciones = $stm->fetchAll(PDO::FETCH_OBJ);
	
	$query = "SELECT id_proveedor FROM proveedor WHERE estatus = 1";
	$stm = $con->prepare($query);
	$stm->execute();
	$proveedores = $stm->fetchAll(PDO::FETCH_OBJ);
	
	$query = "SELECT id_pregunta, seccion, subseccion, titulo FROM pregunta WHERE estatus = 1 ORDER BY id_pregunta";
	$stm = $con->prepare($query);
	$stm->execute();
	$preguntas = $stm->fetchAll(PDO::FETCH_OBJ);
	
	$query = "SELECT id_evaluacion FROM evaluacion WHERE usuario = ? AND estatus = 1";
	$stm = $con->prepare($query);
	$stm->execute(array($_SESSION['id_usr']));
	$evaluacion = $stm->fetchObject();
	$id_evaluacion = ( $evaluacion ) ? $evaluacion->id_evaluacion : 0;
	
	if ( !$id_evaluacion ) {
		$query = "INSERT INTO evaluacion(fecha_creacion, usuario) VALUES(?, ?)";
		$stm = $con->prepare($query);
		$stm->execute(array($fecha, $_SESSION['id_usr']));
		$id_evaluacion = $con->lastInsertId();
	} else {
		$query = "UPDATE evaluacion SET fecha_actualizacion = ? WHERE id_evaluacion = ?";
		$stm = $con->prepare($query);
		$stm->execute(array($fecha, $id_evaluacion));
	}
	
	echo $id_evaluacion;
	
	echo $query;
	
	foreach ( $secciones as $seccion ) {
		if ( !$seccion->sub ) {
			$query = "INSERT INTO respuesta(respuesta, evaluacion, seccion) VALUES(?, ?, ?)";
		}
	}
	
	foreach ( $preguntas as $pregunta ) {
		foreach ( $proveedores as $proveedor ) {
			//${}
		}
	}
	
	
	
	print_r($_POST);
});

/* Se inicializa el framework */
Flight::start();
?>