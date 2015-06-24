<?php
ini_set('memory_limit', '1024M');

require 'flight/Flight.php';
require_once 'conf.php';

/* Función para guardar la evaluación */
function guardar(&$con) {
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
	
	foreach ( $preguntas as $pregunta ) {
		foreach ( $proveedores as $proveedor ) {
			$resp = (isset($_POST['cmb_'.$pregunta->id_pregunta.'_'.$proveedor->id_proveedor])) ? addslashes($_POST['cmb_'.$pregunta->id_pregunta.'_'.$proveedor->id_proveedor]) : "";
			$id_resp = (isset($_POST['hdnc_'.$pregunta->id_pregunta.'_'.$proveedor->id_proveedor])) ? addslashes($_POST['hdnc_'.$pregunta->id_pregunta.'_'.$proveedor->id_proveedor]) : "";
	
			if ( !$id_resp ) {
				if ( $resp ) {
					$query = "INSERT INTO respuesta(respuesta, evaluacion, pregunta, proveedor) VALUES(?, ?, ?, ?)";
					$stm = $con->prepare($query);
					$stm->execute(array($resp, $id_evaluacion, $pregunta->id_pregunta, $proveedor->id_proveedor));
				}
			} else {
				$query = "UPDATE respuesta SET respuesta = ? WHERE id_respuesta = ?";
				$stm = $con->prepare($query);
				$stm->execute(array($resp, $id_resp,));
			}
		}
	}
	
	foreach ( $secciones as $seccion ) {
		if ( !$seccion->sub ) {
			$resp = (isset($_POST['txt_'.$seccion->id_seccion.'_0'])) ? addslashes(utf8_decode($_POST['txt_'.$seccion->id_seccion.'_0'])) : "";
			$id_resp = (isset($_POST['hdnt_'.$seccion->id_seccion.'_0'])) ? addslashes($_POST['hdnt_'.$seccion->id_seccion.'_0']) : "";
				
			if ( !$id_resp ) {
				if ( $resp ) {
					$query = "INSERT INTO respuesta(respuesta, evaluacion, seccion) VALUES(?, ?, ?)";
					$stm = $con->prepare($query);
					$stm->execute(array($resp, $id_evaluacion, $seccion->id_seccion));
				}
			} else {
				$query = "UPDATE respuesta SET respuesta = ? WHERE id_respuesta = ?";
				$stm = $con->prepare($query);
				$stm->execute(array($resp, $id_resp,));
			}
		} else {
			foreach ( $subsecciones as $subseccion ) {
				$resp = (isset($_POST['txt_'.$seccion->id_seccion.'_'.$subseccion->id_subseccion])) ? addslashes(utf8_decode($_POST['txt_'.$seccion->id_seccion.'_'.$subseccion->id_subseccion])) : "";
				$id_resp = (isset($_POST['hdnt_'.$seccion->id_seccion.'_'.$subseccion->id_subseccion])) ? addslashes($_POST['hdnt_'.$seccion->id_seccion.'_'.$subseccion->id_subseccion]) : "";
					
				if ( !$id_resp ) {
					if ( $resp ) {
						$query = "INSERT INTO respuesta(respuesta, evaluacion, seccion, subseccion) VALUES(?, ?, ?, ?)";
						$stm = $con->prepare($query);
						$stm->execute(array($resp, $id_evaluacion, $seccion->id_seccion, $subseccion->id_subseccion));
					}
				} else {
					$query = "UPDATE respuesta SET respuesta = ? WHERE id_respuesta = ?";
					$stm = $con->prepare($query);
					$stm->execute(array($resp, $id_resp,));
				}
			}
		}
	}
	
	return $id_evaluacion;
}

/* Función para formatear fecha y hora */
function formatearFecha($fecha) {
	list($f, $h) = explode(" ", $fecha);
	
	$f2 = explode("-", $f);
	$f2 = array_reverse($f2);
	$f = implode("/", $f2);
	
	return "Fecha y hora de finalización: $f $h";
}

/* Registro y conexión a base de datos */
Flight::register('db', 'PDO', array($dbh, $user, $pass), function ($db) {
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
});

/* Ruteos */
Flight::route('POST /login/', function() {
	session_start();

	$con = Flight::db();

	$usuario = (isset($_POST['usuario'])) ? addslashes($_POST['usuario']) : "";
	$password = (isset($_POST['password'])) ? addslashes($_POST['password']) : "";

	$query = "SELECT id_usuario, login, tipo_usuario FROM usuario WHERE login = ? AND password = ? AND estatus = 1 LIMIT 1";
	$stm = $con->prepare($query);
	$stm->execute(array($usuario, $password));
	$usr = $stm->fetchObject();

	if ( $stm->rowCount() > 0 ) {
		$_SESSION['id_usr'] = $usr->id_usuario;
		$_SESSION['usuario'] = $usr->login;
		$_SESSION['tipo_usuario'] = $usr->tipo_usuario;
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
		$respuesta = array();
		$observaciones = "";
		
		$query = "SELECT id_usuario, grado, nombre, ap_paterno, ap_materno FROM usuario WHERE id_usuario = ? AND estatus = 1";
		$stm = $con->prepare($query);
		$stm->execute(array($_SESSION['id_usr']));
		$usuario = $stm->fetchObject();
		
		$query = "SELECT id_evaluacion, fecha_finalizacion FROM evaluacion WHERE usuario = ? AND estatus > 0";
		$stm = $con->prepare($query);
		$stm->execute(array($_SESSION['id_usr']));
		$evaluacion = $stm->fetchObject();
		
		if ( $evaluacion && sizeof($evaluacion) > 0 ) {
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
		
		$query = "SELECT id_seccion, nombre, maximo FROM seccion WHERE estatus = 1 ORDER BY id_seccion";
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
			$dato->maximo = $seccion->maximo;
			$dato->sub = $sb;
			$secciones_arr[] = $dato;
		}
		
		$secciones = $secciones_arr;
		
		$query = "SELECT id_subseccion, nombre, seccion, maximo FROM subseccion WHERE estatus = 1 ORDER BY id_subseccion";
		$stm = $con->prepare($query);
		$stm->execute();
		$subsecciones = $stm->fetchAll(PDO::FETCH_OBJ);
		
		$query = "SELECT id_proveedor, nombre, estatus FROM proveedor WHERE estatus = 1 ORDER BY nombre";
		$stm = $con->prepare($query);
		$stm->execute();
		$proveedores = $stm->fetchAll(PDO::FETCH_OBJ);
		
		$query = "SELECT id_pregunta, pregunta, seccion, subseccion, titulo FROM pregunta WHERE estatus = 1 ORDER BY id_pregunta";
		$stm = $con->prepare($query);
		$stm->execute();
		$preguntas = $stm->fetchAll(PDO::FETCH_OBJ);
		
		$datos = array(
				'usuario'		=> $usuario,
				'evaluacion'	=> $evaluacion,
				'secciones'		=> $secciones,
				'subsecciones'	=> $subsecciones,
				'proveedores'	=> $proveedores,
				'preguntas'		=> $preguntas,
				'respuesta'		=> $respuesta,
				'observaciones'	=> $observaciones
		);
		
		/* Usuario Administrador */
		if ( $_SESSION['tipo_usuario'] == 1) {
			$query = "SELECT u.id_usuario, u.grado, u.nombre, u.ap_paterno, u.ap_materno, e.id_evaluacion 
						FROM usuario u 
						JOIN evaluacion e 
						ON u.id_usuario = e.usuario 
						WHERE e.fecha_finalizacion IS NOT NULL 
						ORDER BY u.nombre, u.ap_paterno, u.ap_materno";
			$stm = $con->prepare($query);
			$stm->execute();
			$usuarios = $stm->fetchAll(PDO::FETCH_OBJ);
			
			//$datos = array('usuarios' => $usuarios);
			Flight::render('evaluacion', $datos);
		}
		
		/* Usuario Evaluador */
		if ( $_SESSION['tipo_usuario'] == 2) {
			Flight::render('evaluacion', $datos);
		}
	}
	Flight::render('footer');
});

Flight::route('/guardarEvaluacion/', function() {
	session_start();
	
	$con = Flight::db();
	
	guardar($con);
});

Flight::route('/finalizarEvaluacion/', function() {
	session_start();
	
	$con = Flight::db();
	
	$id_evaluacion = guardar($con);
	
	$fecha = date('Y-m-d H:i:s');
	$query = "UPDATE evaluacion SET fecha_finalizacion = ? WHERE id_evaluacion = ?";
	$stm = $con->prepare($query);
	$stm->execute(array($fecha, $id_evaluacion));
});

Flight::route('/guardarEvEco/', function () {
	session_start();
	
	$con = Flight::db();
	
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
	
	$data['proveedor'] = (isset($_POST['empresa'])) ? $_POST['empresa'] : "";
	$data['pregunta1'] = (isset($_POST['cmb1'])) ? $_POST['cmb1'] : "";
	$data['pregunta2'] = (isset($_POST['cmb2'])) ? $_POST['cmb2'] : "";
	$data['pregunta3'] = (isset($_POST['cmb3'])) ? $_POST['cmb3'] : "";
	$data['pregunta4'] = (isset($_POST['cmb4'])) ? $_POST['cmb4'] : "";
	$data['observaciones'] = (isset($_POST['comentarios'])) ? addslashes($_POST['comentarios']) : "";
	
	$id_evaluacion = 0;
	
	if ( $data['pregunta1'] && $data['pregunta1'] == 'n' ) {
		$data['total1'] = (isset($_POST['tt_0'])) ? $_POST['tt_0'] : "";
		$data['total2'] = (isset($_POST['tt_1'])) ? $_POST['tt_1'] : "";
		$data['total3'] = (isset($_POST['tt_2'])) ? $_POST['tt_2'] : "";
		$data['total4'] = (isset($_POST['tt_3'])) ? $_POST['tt_3'] : "";
		
		$campos = "";
		$values = "";
		$params = array();
	
		foreach ( $data as $key => $val ) {
			$campos .= $key.",";
			$values .= "?,";
			array_push($params, $val);
		}
	
		$campos = trim($campos, ",");
		$values = trim($values, ",");
	
		$query = "INSERT INTO evaluacion_economica($campos) VALUES($values)";
		$stm = $con->prepare($query);
		$stm->execute($params);
		
		echo "ok";
	}
	
	if ( $data['pregunta1'] && ( $data['pregunta1'] == 's' || $data['pregunta1'] == 'p' ) ) {
		$data['total1'] = (isset($_POST['txt_total_0'])) ? $_POST['txt_total_0'] : "";
		$data['total2'] = (isset($_POST['txt_total_1'])) ? $_POST['txt_total_1'] : "";
		$data['total3'] = (isset($_POST['txt_total_2'])) ? $_POST['txt_total_2'] : "";
		$data['total4'] = (isset($_POST['txt_total_3'])) ? $_POST['txt_total_3'] : "";
		
		$campos = "";
		$values = "";
		$params = array();
	
		foreach ( $data as $key => $val ) {
			$campos .= $key.",";
			$values .= "?,";
			array_push($params, $val);
		}
	
		$campos = trim($campos, ",");
		$values = trim($values, ",");
	
		$query = "INSERT INTO evaluacion_economica($campos) VALUES($values)";
		$stm = $con->prepare($query);
		$stm->execute($params);
		$id_evaluacion = $con->lastInsertId();
		
		foreach ( $secciones as $seccion ) {
			if ( !$seccion->sub ) {
				for ( $i=0; $i<=4; $i++ ) {
					$resp = (isset($_POST['txt_'.$seccion->id_seccion.'_0_'.$i])) ? addslashes($_POST['txt_'.$seccion->id_seccion.'_0_'.$i]) : "";
					
					if ( $resp ) {
						$query = "INSERT INTO costo_concepto(cantidad, evaluacion_economica, seccion) VALUES(?, ?, ?)";
						$stm = $con->prepare($query);
						$stm->execute(array($resp, $id_evaluacion, $seccion->id_seccion));
					}
				}
			} else {
				foreach ( $subsecciones as $subseccion ) {
					for ( $i=0; $i<=4; $i++ ) {
						$resp = (isset($_POST['txt_'.$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$i])) ? addslashes($_POST['txt_'.$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$i]) : "";
							
						if ( $resp ) {
							$query = "INSERT INTO costo_concepto(cantidad, evaluacion_economica, seccion, subseccion) VALUES(?, ?, ?, ?)";
							$stm = $con->prepare($query);
							$stm->execute(array($resp, $id_evaluacion, $seccion->id_seccion, $subseccion->id_subseccion));
						}
					}
				}
			}
		}
		
		echo "ok";
	}
});

Flight::route('/evaluacion-tecnica/', function() {
	session_start();
	
	if ( !$_SESSION ) {
		Flight::redirect('/');
	}
	
	$con = Flight::db();
	
	Flight::render('header');
	Flight::render('footer');
});

Flight::route('/evaluacion-economica/', function() {
	session_start();
	
	if ( !$_SESSION ) {
		Flight::redirect('/');
	}
	
	$con = Flight::db();
	
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
	
	$query = "SELECT id_proveedor, nombre, abreviatura FROM proveedor 
			WHERE estatus = 1 AND id_proveedor NOT IN(
			SELECT proveedor FROM evaluacion_economica WHERE estatus = 1
			) ORDER BY nombre";
	$stm = $con->prepare($query);
	$stm->execute();
	$proveedores = $stm->fetchAll(PDO::FETCH_OBJ);
	
	$datos = array(
			'secciones'		=> $secciones,
			'subsecciones'	=> $subsecciones,
			'proveedores'	=> $proveedores
	);
	
	Flight::render('header');
	Flight::render('evaluacion_economica', $datos);
	Flight::render('footer');
});

Flight::route('/imprimirEvaluacion/[0-9]+', function() {
	session_start();
	
	if ( $_SESSION['tipo_usuario'] != 1 ) {
		Flight::redirect('/');
	}
	
	$url = $_SERVER['REQUEST_URI'];
	$url = explode("/", $url);
	$id_evaluacion = $url[sizeof($url)-1];
	
	require_once 'lib/mpdf60/mpdf.php';
	
	$con = Flight::db();
	$respuesta = array();
	$observaciones = "";
	
	$query = "SELECT fecha_finalizacion FROM evaluacion WHERE id_evaluacion = ?";
	$stm = $con->prepare($query);
	$stm->execute(array($id_evaluacion));
	$evaluacion = $stm->fetchObject();
	
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
	
	$query = "SELECT id_proveedor, nombre, abreviatura, estatus FROM proveedor WHERE estatus = 1 ORDER BY nombre";
	$stm = $con->prepare($query);
	$stm->execute();
	$proveedores = $stm->fetchAll(PDO::FETCH_OBJ);
	
	$query = "SELECT u.grado, u.nombre, u.ap_paterno, u.ap_materno, u.cargo, u.institucion 
			FROM usuario u JOIN evaluacion e ON u.id_usuario = e.usuario WHERE e.id_evaluacion = ? AND u.estatus = 1";
	$stm = $con->prepare($query);
	$stm->execute(array($id_evaluacion));
	$evaluador = $stm->fetchObject();
	
	$query = "SELECT id_pregunta, pregunta, seccion, subseccion, titulo FROM pregunta WHERE estatus = 1 ORDER BY id_pregunta";
	$stm = $con->prepare($query);
	$stm->execute();
	$preguntas = $stm->fetchAll(PDO::FETCH_OBJ);
	
	$query = "SELECT id_respuesta, respuesta, seccion, subseccion, pregunta, proveedor FROM respuesta WHERE evaluacion = ? AND estatus = 1";
	$stm = $con->prepare($query);
	$stm->execute(array($id_evaluacion));
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
	
	$respuesta = $respuesta_arr;
	$observaciones = $observaciones_arr;
	
	$html = utf8_decode('<h3 class="titulo">Evaluación de Propuestas Técnicas y de Servicios</h3>');
	$html .= utf8_decode('<p>La evaluación de las propuestas técnicas y de servicios estará sujeta a los rubros descritos ');
	$html .= utf8_decode('en el "Formato de Presentación de Propuestas Técnicas, de Servicio y Económicas"</p>');
	$html .= '<p class="evaluador"><strong>Nombre del evaluador: </strong>'.$evaluador->nombre." ".$evaluador->ap_paterno." ".$evaluador->ap_materno.'</p>';
	
	foreach ( $proveedores as $proveedor ) {
		${"t_".$proveedor->id_proveedor} = 0;
	}
	
	foreach ( $preguntas as $pregunta ) {
		foreach ( $proveedores as $proveedor ) {
			$resp = $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta'];
			
			switch ( $resp ) {
				case 'S':
					${"t_".$proveedor->id_proveedor} += 2;
					break;
				case 'P':
					${"t_".$proveedor->id_proveedor} += 1;
					break;
				default:
					break;
			}
		}
	}
	
	$clase = "sombreado";
	$html .= '<table class="ponderacion">';
	$html .= '<tr>';
	$html .= utf8_decode('<td colspan="3" class="titulo-tabla">Puntaje total</td>');
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<th>Empresa</th>';
	$html .= '<th>Sistema</th>';
	$html .= '<th>Puntaje</th>';
	$html .= '</tr>';
	foreach ( $proveedores as $proveedor ) {
		$clase = ($clase == "") ? "sombreado" : "";
		$html .= '<tr>';
		$html .= '<td class="'.$clase.'">'.$proveedor->abreviatura.($proveedor->estatus == 0 ? " *" : "").'</td>';
		$html .= '<td class="'.$clase.'">'.$proveedor->nombre.'</td>';
		$html .= '<td class="'.$clase.'">'.($proveedor->estatus > 0 ? ${"t_".$proveedor->id_proveedor}. " puntos" : utf8_decode("Inválido")).'</td>';
		$html .= '</tr>';
	}
	$html .= '</table>';
	$html .= utf8_decode('<p class="nota">* No se procedió a evaluar la Propuesta Técnica y de Servicio de la empresa 
			ITMS GROUP INC., por incumplir con los lineamientos del procedimiento de asignación.</p>');
	$html .= '<p>&nbsp;</p>';
	
	$html .= '<table class="ponderacion">';
	$html .= '<tr>';
	$html .= utf8_decode('<td colspan="2" class="titulo-tabla">Tabla de ponderación</td>'); 
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<th class="izq">Puntaje</th>';
	$html .= '<th>Nomenclatura</th>';
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<td class="izq">2 ptos</td>';
	$html .= utf8_decode('<td><strong>Sí.</strong> La empresa/sistema cumple con el requerimiento</td>');
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<td class="izq sombreado">1 ptos</td>';
	$html .= '<td class="sombreado"><strong>Parcialmente.</strong> La empresa/sistema cumple parcialmente con el requerimiento</td>';
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<td class="izq">0 ptos</td>';
	$html .= '<td><strong>No.</strong> La empresa/sistema no cumple con el requerimiento</td>';
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<td class="izq sombreado">0 ptos</td>';
	$html .= utf8_decode('<td class="sombreado"><strong>Desconocido.</strong> La empresa/sistema no proporciona información al respecto</td>');
	$html .= '</tr>';
	$html .= '</table>';
	
	$html .= '<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>';
	
	foreach ( $secciones as $seccion ) {
		foreach ( $proveedores as $proveedor ) {
			${"total_".$proveedor->id_proveedor} = 0;
		}
		
		$html .= '<ol class="seccion" type="a" start="'.$seccion->id_seccion.'">';
		$html .= '<li><h4>'.$seccion->nombre.'</h4></li>';
		$html .= '</ol>';
		
		if ( !$seccion->sub ) {
			$html .= '<table>';
			$html .= '<tr>';
			$html .= '<th>&nbsp;</th>';
			foreach ( $proveedores as $proveedor ) {
				$html .= '<th>'.$proveedor->nombre.'</th>';
			}
			$html .= '</tr>';
			
			foreach ( $preguntas as $pregunta ) {
				if ( $pregunta->seccion == $seccion->id_seccion ) {
					$html .= '<tr>';
					$html .= '<td class="izq">'.$pregunta->pregunta.'</td>';
					
					foreach ( $proveedores as $proveedor ) {
						$resp = $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta'];
						$orig = array('S', 'P', 'N', 'D');
						$valor = array(utf8_decode('Sí')."<br />(2)", "Parcialmente<br />(1)", "No<br />(0)", "Desconocido<br />(0)");
						
						switch ( $resp ) {
							case 'S':
								${"total_".$proveedor->id_proveedor} += 2;
								break;
							case 'P':
								${"total_".$proveedor->id_proveedor} += 1;
								break;
							default:
								break;
						}
						
						if ( $proveedor->estatus > 0 ) {
							$html .= '<td>'.str_replace($orig, $valor, $resp).'</td>';
						} else {
							$html .= utf8_decode('<td>Inválido</td>');
						}
					}
					$html .= '</tr>';
				}
			}
			$html .= '<tr>';
			$html .= '<td class="izq puntos">Total de puntos:</td>';
			foreach ( $proveedores as $proveedor ) {
				if ( $proveedor->estatus > 0 ) {
					$html .= '<td class="puntos">'.${"total_".$proveedor->id_proveedor}.'</td>';
				} else {
					$html .= utf8_decode('<td class="puntos">Inválido</td>');
				}
			}
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="izq observaciones" colspan="'.(sizeof($proveedores)+1).'"><strong>Comentarios:</strong><br/>';
			$html .= str_replace("\n", "<br />", $observaciones[$seccion->id_seccion."_0"]['respuesta']);
			$html .= '</td>';
			$html .= '</tr>';
				
			$html .= '</table>';
		} else {
			$ss = 1;
			foreach ( $subsecciones as $subseccion ) {
				foreach ( $proveedores as $proveedor ) {
					${"total_".$proveedor->id_proveedor} = 0;
				}
				
				if ($subseccion->seccion == $seccion->id_seccion ) {
					$html .= '<ol class="subseccion" type="I" start="'.$ss++.'">';
					$html .= '<li><h5>'.$subseccion->nombre.'</h5></li>';
					$html .= '</ol>';
					$html .= '<table>';
					$html .= '<tr>';
					$html .= '<th>&nbsp;</th>';
					foreach ( $proveedores as $proveedor ) {
						$html .= '<th>'.$proveedor->nombre.'</th>';
					}
					$html .= '</tr>';
					
					foreach ( $preguntas as $pregunta ) {
						if ( $pregunta->subseccion == $subseccion->id_subseccion ) {
							$html .= '<tr>';
							$html .= '<td class="izq">'.$pregunta->pregunta.'</td>';
							
							foreach ( $proveedores as $proveedor ) {
								$resp = $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta'];
								$orig = array('S', 'P', 'N', 'D');
								$valor = array(utf8_decode('Sí')."<br />(2)", "Parcialmente<br />(1)", "No<br />(0)", "Desconocido<br />(0)");
							
								switch ( $resp ) {
									case 'S':
										${"total_".$proveedor->id_proveedor} += 2;
										break;
									case 'P':
										${"total_".$proveedor->id_proveedor} += 1;
										break;
									default:
										break;
								}
								
								if ( $proveedor->estatus > 0 ) {
									$html .= '<td>'.str_replace($orig, $valor, $resp).'</td>';
								} else {
									$html .= utf8_decode('<td>Inválido</td>');
								}
							}
							
							$html .= '</tr>';
						}
					}
					
					$html .= '<tr>';
					$html .= '<td class="izq puntos">Total de puntos:</td>';
					foreach ( $proveedores as $proveedor ) {
						if ( $proveedor->estatus > 0 ) {
							$html .= '<td class="puntos">'.${"total_".$proveedor->id_proveedor}.'</td>';
						} else {
							$html .= utf8_decode('<td class="puntos">Inválido</td>');
						}
					}
					$html .= '</tr>';
					$html .= '<tr>';
					$html .= '<td class="izq observaciones" colspan="'.(sizeof($proveedores)+1).'"><strong>Comentarios:</strong><br/>';
					$html .= str_replace("\n", "<br />", $observaciones[$seccion->id_seccion."_".$subseccion->id_subseccion]['respuesta']);
					$html .= '</td>';
					$html .= '</table>';
				}
			}
		}
	}
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, CSS_PDF);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$stylesheet = curl_exec($ch);
	curl_close($ch);
	
	$header = '<img id="logo-conricyt" src="../images/ch_logo1.png"/><img id="logo-conacyt" src="../images/ch_logo2.png"/>';
	$footer = '<footer><div class="finalizacion">'.formatearFecha($evaluacion->fecha_finalizacion).'</div><div class="paginacion">Página {PAGENO} de {nbpg}</div>';
	$footer .= '<p class="direccion"><strong>Oficina del Consorcio Nacional de Recursos de Información Científica y Tecnológica</strong><br />';
	$footer .= 'Av. Insurgentes Sur 1582, Col. Crédito Constructor, Deleg. Benito Juárez, C.P. 03940 ';
	$footer .= 'México D.F. – Tel: 5322 7700 ext  4020 a la 4026</p></footer>';
	
	$mpdf = new mPDF('utf-8', 'Letter', 0, 'Arial', 13, 13, 35, 25);
	
	$mpdf->SetHTMLHeader($header);
	$mpdf->SetHTMLFooter($footer);
	
	$mpdf->WriteHTML($stylesheet, 1);
	$mpdf->WriteHTML(utf8_encode($html));
	
	$mpdf->AddPage();
	$html = '<div class="firmas">';
	$html .= '<div class="firma">';
	$html .= '<span class="nombre">'.trim($evaluador->grado." ".$evaluador->nombre." ".$evaluador->ap_paterno." ".$evaluador->ap_materno).'</span><br />';
	$html .= '<span>'.$evaluador->cargo.'</span><br />';
	$html .= '<span>'.$evaluador->institucion.'</span>';
	$html .= '</div>';
	$html .= '</div>';
	
	$html .= utf8_decode('<p class="nota-firma">Firma que avala la evaluación de todos los rubros descritos en los incisos "a", "b", "c", 
			"d" y "e" de las Propuestas Técnicas y de Servicios, de las empresas participantes. Consta de '.$mpdf->PageNo().' fojas útiles por anverso, incluyendo esta.</p>');
	
	$mpdf->WriteHTML($stylesheet, 1);
	$mpdf->WriteHTML(utf8_encode($html));
	
	$mpdf->Output('evaluación.pdf', 'D');
	exit();
});

Flight::route('/imprimirFallo/', function() {
	session_start();
	
	if ( $_SESSION['tipo_usuario'] != 1 ) {
		Flight::redirect('/');
	}
	
	$con = Flight::db();
	
	$query = "SELECT d.fecha, p.nombre, d.periodo, d.comentarios 
			FROM dictamen d JOIN proveedor p ON d.proveedor = p.id_proveedor 
			WHERE d.estatus = 1 ORDER BY id_dictamen DESC";
	$stm = $con->prepare($query);
	$stm->execute();
	$dictamen = $stm->fetchObject();
	
	$query = "SELECT id_proveedor, nombre, abreviatura, estatus FROM proveedor WHERE estatus = 1 ORDER BY nombre";
	$stm = $con->prepare($query);
	$stm->execute();
	$proveedores = $stm->fetchAll(PDO::FETCH_OBJ);
	
	$query = "SELECT u.id_usuario, u.grado, u.nombre, u.ap_paterno, u.ap_materno, u.cargo, u.institucion, e.id_evaluacion FROM usuario u
		LEFT JOIN evaluacion e ON u.id_usuario = e.usuario
		WHERE u.estatus = 1 AND u.tipo_usuario = 2 ORDER BY u.nombre, u.ap_paterno, u.ap_materno";
	$stm = $con->prepare($query);
	$stm->execute();
	$evaluadores = $stm->fetchAll(PDO::FETCH_OBJ);
	
	$resultados = array();
	
	foreach ( $evaluadores as $evaluador ) {
		foreach ( $proveedores as $proveedor ) {
			$resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor] = 0;
		}
	}
	
	foreach ( $evaluadores as $evaluador ) {
		foreach ( $proveedores as $proveedor ) {
			$query = "SELECT r.id_respuesta, r.respuesta, r.evaluacion, r.proveedor
					FROM respuesta r
					JOIN evaluacion e ON r.evaluacion = e.id_evaluacion
					WHERE e.usuario = ? AND r.proveedor = ?";
			$stm = $con->prepare($query);
			$stm->execute(array($evaluador->id_usuario, $proveedor->id_proveedor));
			$res = $stm->fetchAll(PDO::FETCH_OBJ);
	
			foreach ( $res as $val ) {
				switch ( $val->respuesta ) {
					case 'S':
						$resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor] += 2;
						break;
					case 'P':
						$resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor] += 1;
						break;
					default:
						break;
				}
			}
		}
	}
	
	$p_costos_arr = array(
			'¿La empresa incluyó el costo de cada concepto y servicio?',
			'¿La empresa incluyó en la propuesta los montos por pago de impuestos?',
			'¿La empresa incluyó en su propuesta el porcentaje de incremento anual para los años de servicio?',
			'¿La propuesta económica se presentó en Moneda Nacional?'
	);
	
	$p_costos = array();
	
	foreach ( $p_costos_arr as $key => $val ) {
		$dato = new stdClass();
		$dato->id = $key+1;
		$dato->pregunta = $val;
	
		foreach ( $proveedores as $proveedor ) {
			$query = "SELECT pregunta".$dato->id." AS resp FROM evaluacion_economica WHERE proveedor = ? AND estatus = 1";
			$stm = $con->prepare($query);
			$stm->execute(array($proveedor->id_proveedor));
			$res_p_eco = $stm->fetch();
				
			switch ( $res_p_eco['resp'] ) {
				case 's':
					$dato->{"ev_".$proveedor->id_proveedor} = "Sí";
					break;
				case 'p':
					$dato->{"ev_".$proveedor->id_proveedor} = "Parcialmente";
					break;
				case 'n':
					$dato->{"ev_".$proveedor->id_proveedor} = "No";
					break;
				default:
					$dato->{"ev_".$proveedor->id_proveedor} = "";
					break;
			}
		}
	
		$p_costos[] = $dato;
	}
	
	$periodos = array('2015-2016','2016-2017','2017-2018','2018-2019');
	
	$montos = array();
	
	foreach ( $proveedores as $proveedor ) {
		$dato = new stdClass();
		$dato->proveedor = utf8_encode($proveedor->abreviatura);
		$dato->estatus = $proveedor->estatus;
		$dato->subtotal = 0;
	
		foreach ( $periodos as $key => $periodo ) {
			$query = "SELECT total".($key+1)." AS total FROM evaluacion_economica WHERE proveedor = ? AND estatus = 1";
			$stm = $con->prepare($query);
			$stm->execute(array($proveedor->id_proveedor));
			$res_monto = $stm->fetch();
				
			$dato->{"t_".$key} = ($res_monto) ? $res_monto['total'] : 0;
			$dato->subtotal += $dato->{"t_".$key};
		}
	
		$query = "SELECT observaciones FROM evaluacion_economica WHERE proveedor = ? AND estatus = 1";
		$stm = $con->prepare($query);
		$stm->execute(array($proveedor->id_proveedor));
		$res_monto = $stm->fetchObject();
	
		$dato->comentarios = ($res_monto) ? $res_monto->observaciones : "";
		$montos[] = $dato;
	}
	
	require_once 'lib/mpdf60/mpdf.php';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, CSS_PDF);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$stylesheet = curl_exec($ch);
	curl_close($ch);
	
	$header = '<img id="logo-conricyt" src="../images/ch_logo1.png"/><img id="logo-conacyt" src="../images/ch_logo2.png"/>';
	$footer = '<footer><div class="finalizacion">'.formatearFecha($dictamen->fecha).'</div><div class="paginacion">Página {PAGENO} de {nbpg}</div>';
	$footer .= '<p class="direccion"><strong>Oficina del Consorcio Nacional de Recursos de Información Científica y Tecnológica</strong><br />';
	$footer .= 'Av. Insurgentes Sur 1582, Col. Crédito Constructor, Deleg. Benito Juárez, C.P. 03940 ';
	$footer .= 'México D.F. – Tel: 5322 7700 ext  4020 a la 4026</p></footer>';
	
	$mpdf = new mPDF('utf-8', 'Letter', 0, 'Arial', 13, 13, 35, 25);
	
	$mpdf->SetHTMLHeader($header);
	$mpdf->SetHTMLFooter($footer);
	
	$html = '';
	$html .= utf8_decode('<p class="fecha">Consorcio Nacional de Recursos de Información Científica y Tecnológica<br />
			Comisión de Desarrollo Tecnológico<br />
			México, D.F., a 16 de junio de 2015</p>');
	$html .= utf8_decode('<p class="encabezado">Procediminto de Asignación de Contrato de Prestación de Sevicio	para<br /> 
			proveer el Sistema de Descubrimiento y el Servidor EZProxy para el Portal del CONRICYT</p>');
	$html .= '<p>&nbsp;</p><h2 style="text-align:center;">FALLO FINAL</h2><p>&nbsp;</p>';
	$html .= utf8_decode('<p>Con base en los resultados obtenidos de la evaluación de las Propuestas Técnicas, 
			de Servicios y Económicas, mismos que a continuación se presentan</p>');
	$html .= utf8_decode('<h4>Resultados de la evaluación de las propuestas técnicas y de servicios</h4>');
	$html .= '<table class="resumen">';
	$html .= '<tr>';
	$html .= '<th class="izq">Evaluador</th>';
	foreach ( $proveedores as $proveedor ) {
		${"total_".$proveedor->id_proveedor} = 0;
		$html .= '<th>'.$proveedor->nombre.($proveedor->estatus == 0 ? " *" : "").'</th>';
	}
	$html .= '</tr>';
	foreach ( $evaluadores as $evaluador ) {
		$html .= '<tr>';
		$html .= '<td class="izq">'.trim($evaluador->grado." ".$evaluador->nombre." ".$evaluador->ap_paterno." ".$evaluador->ap_materno).'</td>';
		foreach ( $proveedores as $proveedor ) {
			if ( $proveedor->estatus > 0 ) {
				$html .= '<td>'.$resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor].'</td>';
			} else {
				$html .= utf8_decode('<td>Inválido</td>');
			}
			${"total_".$proveedor->id_proveedor} += $resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor];
		}
		$html .= '</tr>';
	}
	$html .= '<tr>';
	$html .= '<td class="izq puntos">Total de puntos:</td>';
	foreach ( $proveedores as $proveedor ) {
		if ( $proveedor->estatus > 0 ) {
			$html .= '<td class="puntos">'.${"total_".$proveedor->id_proveedor}.'</td>';
		} else {
			$html .= utf8_decode('<td class="puntos">Inválido</td>');
		}
	}
	$html .= '</tr>';
	$html .= '</table>';
	$mpdf->WriteHTML($stylesheet, 1);
	$mpdf->WriteHTML(utf8_encode($html));
	
	$mpdf->AddPage();
	$html = '<h2 style="text-align:center;">FALLO FINAL</h2>';
	$html .= utf8_decode('<h4>Resultados de la evaluación de las propuestas económicas</h4>');
	$html .= '<table class="resumen">';
	$html .= '<tr>';
	$html .= '<th class="izq">Concepto</th>';
	foreach ( $proveedores as $proveedor ) {
		$html .= '<th>'.$proveedor->nombre.($proveedor->estatus == 0 ? " *" : "").'</th>';
	}
	$html .= '</tr>';
	foreach ( $p_costos as $preg ) {
		$html .= '<tr>';
		$html .= '<td class="izq">'.utf8_decode($preg->pregunta).'</td>';
		foreach ( $proveedores as $proveedor ) {
			if ( $proveedor->estatus > 0 ) {
				$html .= '<td>'.utf8_decode($preg->{"ev_".$proveedor->id_proveedor}).'</td>';
			} else {
				$html .= utf8_decode('<td>Inválido</td>');
			}
		}
		$html .= '</tr>';
	}
	$html .= '</table>';
	
	$html .= utf8_decode('<h4>Cuadro resumen de costos por año y empresa</h4>');
	$html .= '<table class="resumen">';
	$html .= '<tr>';
	$html .= '<th class="izq">Empresa</th>';
	foreach ( $periodos as $periodo ) {
		$html .= '<th>'.$periodo.'</th>';
	}
	$html .= '<th>Subtotal</th>';
	if ( 1 == 1 ) {
		$html .= '<th>Comentarios</th>';
	}
	$html .= '</tr>';
	foreach ( $montos as $monto ) {
		$html .= '<tr>';
		$html .= '<td class="izq">'.utf8_decode($monto->proveedor.($monto->estatus == 0 ? " *" : "")).'</td>';
		for ( $i=0; $i<sizeof($periodos); $i++ ) {
			if ( $monto->estatus > 0 ) {
				$html .= '<td>$'.number_format($monto->{"t_".$i}, 2).'</td>';
			} else {
				$html .= utf8_decode('<td>Inválido</td>');
			}
		}
		if ( $monto->estatus > 0 ) {
			$html .= '<td>$'.number_format($monto->subtotal, 2).'</td>';
		} else {
			$html .= utf8_decode('<td>Inválido</td>');
		}
		if ( 1==1 ) {
			if ( $monto->estatus > 0 ) {
				$html .= '<td>'.$monto->comentarios.'</td>';
			} else {
				$html .= utf8_decode('<td>Inválido</td>');
			}
		}
		$html .= '</tr>';
	}
	$html .= '</table>';
	
	$html .= utf8_decode('<p class="nota">* No se procedió a evaluar la Propuesta Técnica, de Servicios y Económica de la empresa 
			ITMS GROUP INC., por incumplir con los lineamientos del procedimiento de asignación.</p>');
	
	$html .= utf8_decode('<p class="dictamen">Los miembros de la Comisión de Desarrollo Tecnológico del Consorcio Nacional de 
			Recursos de Información Científica y Tecnológica de conformidad con lo establecido en 
			el inciso 8 "Gastos e inversiones elegibles" de los Lineamientos de Operación en el 
			Fondo Institucional del CONACYT, de la Subcuenta Específica del Consorcio Nacional de 
			Recursos de Información Científica y Tecnológica, aprueban por unanimidad a la empresa 
			'.$dictamen->nombre.' como ganadora del proceso de selección del Sistema de Descubrimiento y del 
			Servidor EZProxy para el Portal del CONRICYT, así como su contratación por '.$dictamen->periodo.' años, 
			ya que la Propuesta Técnica, de Servicio y Económica presentada, garantiza las mejores 
			condiciones de transparencia, eficiencia, eficacia, calidad, pertinencia y costos de 
			inversión</p>');
	if ( $dictamen->comentarios ) {
		$html .= '<p>Comentarios: <br />'.$dictamen->comentarios.'</p>';
	}
	
	$mpdf->WriteHTML($stylesheet, 1);
	$mpdf->WriteHTML(utf8_encode($html));
	
	$mpdf->AddPage();	
	$html = '<h2 style="text-align:center;">FALLO FINAL</h2>';
	$html .= '<div>';
	
	foreach ( $evaluadores as $evaluador ) {
		$html .= '<div class="firma-div">';
		$html .= '<div class="firma-fallo">';
		$html .= '<span class="nombre">'.trim($evaluador->grado." ".$evaluador->nombre." ".$evaluador->ap_paterno." ".$evaluador->ap_materno).'</span><br />';
		$html .= '<span>'.$evaluador->cargo.'</span><br />';
		$html .= '<span>'.$evaluador->institucion.'</span>';
		$html .= '</div>';
		$html .= '</div>';
	}
	
	$html .= '</div>';
	
	$mpdf->WriteHTML($stylesheet, 1);
	$mpdf->WriteHTML(utf8_encode($html));
	
	$mpdf->Output('fallo.pdf', 'D');
	exit();
});

Flight::route('/imprimirResumen/', function() {
	session_start();

	if ( $_SESSION['tipo_usuario'] != 1 ) {
		Flight::redirect('/');
	}

	$con = Flight::db();

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
	
	$query = "SELECT id_proveedor, nombre, estatus FROM proveedor WHERE estatus = 1 ORDER BY nombre";
	$stm = $con->prepare($query);
	$stm->execute();
	$proveedores = $stm->fetchAll(PDO::FETCH_OBJ);
	
	$resultados = array();
	
	foreach ( $secciones as $seccion ) {
		if ( !$seccion->sub ) {
			foreach ( $proveedores as $proveedor ) {
				$resultados[$seccion->id_seccion.'_0_'.$proveedor->id_proveedor] = 0;
			}
		} else {
			foreach ( $subsecciones as $subseccion ) {
				foreach ( $proveedores as $proveedor ) {
					$resultados[$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor] = 0;
				}
			}
		}
	}
	
	foreach ( $secciones as $seccion ) {
		if ( !$seccion->sub ) {
			foreach ( $proveedores as $proveedor ) {
				$query = "SELECT r.id_respuesta, r.respuesta, r.evaluacion, r.proveedor, p.id_pregunta, p.seccion, p.subseccion 
						FROM respuesta r 
						JOIN pregunta p ON r.pregunta = p.id_pregunta 
						WHERE p.seccion = ? AND p.subseccion IS NULL AND r.proveedor = ?";
				$stm = $con->prepare($query);
				$stm->execute(array($seccion->id_seccion, $proveedor->id_proveedor));
				$res = $stm->fetchAll(PDO::FETCH_OBJ);
				
				foreach ( $res as $val ) {
					switch ( $val->respuesta ) {
						case 'S':
							$resultados[$seccion->id_seccion.'_0_'.$proveedor->id_proveedor] += 2;
							break;
						case 'P':
							$resultados[$seccion->id_seccion.'_0_'.$proveedor->id_proveedor] += 1;
							break;
						default:
							break;
					}
				}
			}
		} else {
			foreach ( $subsecciones as $subseccion ) {
				foreach ( $proveedores as $proveedor ) {
					$query = "SELECT r.id_respuesta, r.respuesta, r.evaluacion, r.proveedor, p.id_pregunta, p.seccion, p.subseccion
						FROM respuesta r
						JOIN pregunta p ON r.pregunta = p.id_pregunta
						WHERE p.seccion = ? AND p.subseccion = ? AND r.proveedor = ?";
					$stm = $con->prepare($query);
					$stm->execute(array($seccion->id_seccion, $subseccion->id_subseccion, $proveedor->id_proveedor));
					$res = $stm->fetchAll(PDO::FETCH_OBJ);
					
					foreach ( $res as $val ) {
						switch ( $val->respuesta ) {
							case 'S':
								$resultados[$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor] += 2;
								break;
							case 'P':
								$resultados[$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor] += 1;
								break;
							default:
								break;
						}
					}
				}
			}
		}
	}

	require_once 'lib/mpdf60/mpdf.php';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, CSS_PDF);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$stylesheet = curl_exec($ch);
	curl_close($ch);

	$header = '<img id="logo-conricyt" src="../images/ch_logo1.png"/><img id="logo-conacyt" src="../images/ch_logo2.png"/>';
	$footer = '<footer><div class="finalizacion">'.formatearFecha($dictamen->fecha).'</div><div class="paginacion">Página {PAGENO} de {nbpg}</div>';
	$footer .= '<p class="direccion"><strong>Oficina del Consorcio Nacional de Recursos de Información Científica y Tecnológica</strong><br />';
	$footer .= 'Av. Insurgentes Sur 1582, Col. Crédito Constructor, Deleg. Benito Juárez, C.P. 03940 ';
	$footer .= 'México D.F. – Tel: 5322 7700 ext  4020 a la 4026</p></footer>';

	$mpdf = new mPDF('utf-8', 'Letter', 0, 'Arial', 13, 13, 35, 25);

	$mpdf->SetHTMLHeader($header);
	$mpdf->SetHTMLFooter($footer);

	$html = '';
	$html .= utf8_decode('<h4>Resultados. Detalle de la evaluación por rubro</h4>');
	$html .= '<table class="resumen">';
	$html .= '<tr>';
	$html .= '<th class="izq">Rubro evaluado</th>';
	foreach ( $proveedores as $proveedor ) {
		${"t_".$proveedor->id_proveedor} = 0;
		$html .= '<th>'.$proveedor->nombre.($proveedor->estatus == 0 ? " *" : "").'</th>';
	}
	$html .= '</tr>';
	
	$arr_letras = array("", "a", "b", "c", "d", "e");
	$arr_romanos = array("", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII", "XIII", "XIV", "XV", "XVI", "XVII", "XVIII");
	foreach ( $secciones as $seccion ) {
		$html .= '<tr>';
		$html .= '<td class="izq" '.($seccion->sub  ? 'colspan="'.(sizeof($proveedores)+1).'"' : "").'>';
		$html .= '<span>'.$arr_letras[$seccion->id_seccion].". ".$seccion->nombre.'</span></td>';
		
		foreach ( $proveedores as $proveedor ) {
			$val = 0;
			if ( isset($resultados[$seccion->id_seccion.'_0_'.$proveedor->id_proveedor]) ) {
				$val = $resultados[$seccion->id_seccion.'_0_'.$proveedor->id_proveedor];
			}
			${"t_".$proveedor->id_proveedor} += $val;
			
			if ( !$seccion->sub ) {
				if ( $proveedor->estatus > 0 ) {
					$html .= '<td>'.$val.'</td>';
				} else {
					$html .= utf8_decode('<td><span class="invalido">Inválido</span></td>');
				}
			}
		}
		$html .= '</tr>';
	
		if ( $seccion->sub ) {
			$ss = 1;
			foreach ( $subsecciones as $subseccion ) {
				if ( $subseccion->seccion == $seccion->id_seccion ) {
					$html .= '<tr><td class="izq">';
					$html .= '<span>'.$arr_romanos[($ss++)].". ".$subseccion->nombre.'</span></td>';
					
					foreach ( $proveedores as $proveedor ) {
						$val_s = 0;
						if ( isset($resultados[$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor]) ) {
							$val_s = $resultados[$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor];
						}
						${"t_".$proveedor->id_proveedor} += $val_s;
	
						if ( $proveedor->estatus > 0 ) {
							$html .= '<td>'.$val_s.'</td>';
						} else {
							$html .= utf8_decode('<td><span class="invalido">Inválido</span></td>');
						}
	
					}
					$html .= '</tr>';
				}
			}
		}
	}
	
	$html .= '<tr>';
	$html .= '<td class="izq puntos">Total de puntos:</td>';
	foreach ( $proveedores as $proveedor ) {
		if ( $proveedor->estatus > 0 ) {
			$html .= '<td class="puntos">'.${"t_".$proveedor->id_proveedor}.'</td>';
		} else {
			$html .= utf8_decode('<td class="puntos">Inválido</td>');
		}
	}
	$html .= '</tr>';
	
	$html .= '</table>';

	$html .= utf8_decode('<p class="nota">* No se procedió a evaluar la Propuesta Técnica y de Servicio de la empresa
		ITMS GROUP INC., por incumplir con los lineamientos del procedimiento de asignación.</p>');

	$mpdf->WriteHTML($stylesheet, 1);
	$mpdf->WriteHTML(utf8_encode($html));

	$mpdf->Output('resumen.pdf', 'D');
	exit();
});

Flight::route('/imprimirDetalleEvaluador/', function() {
	session_start();

	if ( $_SESSION['tipo_usuario'] != 1 ) {
		Flight::redirect('/');
	}

	$con = Flight::db();

$query = "SELECT u.id_usuario, u.grado, u.nombre, u.ap_paterno, u.ap_materno, e.id_evaluacion 
			FROM usuario u LEFT JOIN evaluacion e ON u.id_usuario = e.usuario 
			WHERE u.tipo_usuario = 2 AND u.estatus = 1 
			ORDER BY u.nombre, u.ap_paterno, u.ap_materno";
	$stm = $con->prepare($query);
	$stm->execute();
	$evaluadores = $stm->fetchAll(PDO::FETCH_OBJ);
	
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

	$query = "SELECT id_proveedor, nombre, estatus FROM proveedor WHERE estatus = 1 ORDER BY nombre";
	$stm = $con->prepare($query);
	$stm->execute();
	$proveedores = $stm->fetchAll(PDO::FETCH_OBJ);

	$totales = array();
	
	foreach ( $evaluadores as $evaluador ) {
		foreach ( $proveedores as $proveedor ) {
			$totales[$evaluador->id_usuario][$proveedor->id_proveedor] = 0;
		}
	}
	
	foreach ( $evaluadores as $evaluador ) {
		foreach ( $proveedores as $proveedor ) {
			$query = "SELECT r.id_respuesta, r.respuesta, r.evaluacion, r.proveedor
						FROM respuesta r
						JOIN evaluacion e ON r.evaluacion = e.id_evaluacion
						WHERE e.usuario = ? AND r.proveedor = ?";
			$stm = $con->prepare($query);
			$stm->execute(array($evaluador->id_usuario, $proveedor->id_proveedor));
			$res = $stm->fetchAll(PDO::FETCH_OBJ);
				
			foreach ( $res as $val ) {
				switch ( $val->respuesta ) {
					case 'S':
						$totales[$evaluador->id_usuario][$proveedor->id_proveedor] += 2;
						break;
					case 'P':
						$totales[$evaluador->id_usuario][$proveedor->id_proveedor] += 1;
						break;
					default:
						break;
				}
			}
		}
	}
	
	$resultados = array();
	
	foreach ( $evaluadores as $evaluador ) {
		foreach ( $secciones as $seccion ) {
			if ( !$seccion->sub ) {
				foreach ( $proveedores as $proveedor ) {
					$resultados[$evaluador->id_usuario][$seccion->id_seccion.'_0_'.$proveedor->id_proveedor] = 0;
				}
			} else {
				foreach ( $subsecciones as $subseccion ) {
					foreach ( $proveedores as $proveedor ) {
						$resultados[$evaluador->id_usuario][$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor] = 0;
					}
				}
			}
		}
	}
	
	foreach ( $evaluadores as $evaluador ) {
		foreach ( $secciones as $seccion ) {
			if ( !$seccion->sub ) {
				foreach ( $proveedores as $proveedor ) {
					$query = "SELECT r.id_respuesta, r.respuesta, r.evaluacion, r.proveedor, p.id_pregunta, p.seccion, p.subseccion
						FROM respuesta r
						JOIN pregunta p ON r.pregunta = p.id_pregunta
						WHERE r.evaluacion = ? AND p.seccion = ? AND p.subseccion IS NULL AND r.proveedor = ?";
					$stm = $con->prepare($query);
					$stm->execute(array($evaluador->id_evaluacion, $seccion->id_seccion, $proveedor->id_proveedor));
					$res = $stm->fetchAll(PDO::FETCH_OBJ);
	
					foreach ( $res as $val ) {
						switch ( $val->respuesta ) {
							case 'S':
								$resultados[$evaluador->id_usuario][$seccion->id_seccion.'_0_'.$proveedor->id_proveedor] += 2;
								break;
							case 'P':
								$resultados[$evaluador->id_usuario][$seccion->id_seccion.'_0_'.$proveedor->id_proveedor] += 1;
								break;
							default:
								break;
						}
					}
				}
			} else {
				foreach ( $subsecciones as $subseccion ) {
					foreach ( $proveedores as $proveedor ) {
						$query = "SELECT r.id_respuesta, r.respuesta, r.evaluacion, r.proveedor, p.id_pregunta, p.seccion, p.subseccion
						FROM respuesta r
						JOIN pregunta p ON r.pregunta = p.id_pregunta
						WHERE r.evaluacion = ? AND p.seccion = ? AND p.subseccion = ? AND r.proveedor = ?";
						$stm = $con->prepare($query);
						$stm->execute(array($evaluador->id_evaluacion, $seccion->id_seccion, $subseccion->id_subseccion, $proveedor->id_proveedor));
						$res = $stm->fetchAll(PDO::FETCH_OBJ);
							
						foreach ( $res as $val ) {
							switch ( $val->respuesta ) {
								case 'S':
									$resultados[$evaluador->id_usuario][$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor] += 2;
									break;
								case 'P':
									$resultados[$evaluador->id_usuario][$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor] += 1;
									break;
								default:
									break;
							}
						}
					}
				}
			}
		}
	}
	
	require_once 'lib/mpdf60/mpdf.php';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, CSS_PDF);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$stylesheet = curl_exec($ch);
	curl_close($ch);

	$header = '<img id="logo-conricyt" src="../images/ch_logo1.png"/><img id="logo-conacyt" src="../images/ch_logo2.png"/>';
	$footer = '<footer><div class="finalizacion">'.formatearFecha($dictamen->fecha).'</div><div class="paginacion">Página {PAGENO} de {nbpg}</div>';
	$footer .= '<p class="direccion"><strong>Oficina del Consorcio Nacional de Recursos de Información Científica y Tecnológica</strong><br />';
	$footer .= 'Av. Insurgentes Sur 1582, Col. Crédito Constructor, Deleg. Benito Juárez, C.P. 03940 ';
	$footer .= 'México D.F. – Tel: 5322 7700 ext  4020 a la 4026</p></footer>';

	$mpdf = new mPDF('utf-8', 'Letter', 0, 'Arial', 13, 13, 35, 25);

	$mpdf->SetHTMLHeader($header);
	$mpdf->SetHTMLFooter($footer);

	$html = '';
	$html .= utf8_decode('<h4>Resultados. Detalle de la evaluación por evaluador</h4>');
	
	foreach ( $proveedores as $proveedor ) {
		${"total_".$proveedor->id_proveedor} = 0;
	}
	
	foreach ( $evaluadores as $evaluador ) {
		$html .= '<h3>'.trim($evaluador->grado." ".$evaluador->nombre." ".$evaluador->ap_paterno." ".$evaluador->ap_materno).'</h3>';
		$html .= '<table class="resumen">';
		$html .= '<tr>';
		$html .= '<th class="izq">Rubro evaluado</th>';
		foreach ( $proveedores as $proveedor ) {
			${"t_".$proveedor->id_proveedor} = 0;
			$html .= '<th>'.$proveedor->nombre.($proveedor->estatus == 0 ? " *" : "").'</th>';
		}
		$html .= '</tr>';
	
		$arr_letras = array("", "a", "b", "c", "d", "e");
		$arr_romanos = array("", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII", "XIII", "XIV", "XV", "XVI", "XVII", "XVIII");
		foreach ( $secciones as $seccion ) {
			$html .= '<tr>';
			$html .= '<td class="izq" '.($seccion->sub  ? 'colspan="'.(sizeof($proveedores)+1).'"' : "").'>';
			$html .= '<span>'.$arr_letras[$seccion->id_seccion].". ".$seccion->nombre.'</span></td>';
	
			foreach ( $proveedores as $proveedor ) {
				$val = 0;
				if ( isset($resultados[$evaluador->id_usuario][$seccion->id_seccion.'_0_'.$proveedor->id_proveedor]) ) {
					$val = $resultados[$evaluador->id_usuario][$seccion->id_seccion.'_0_'.$proveedor->id_proveedor];
				}
				${"t_".$proveedor->id_proveedor} += $val;
					
				if ( !$seccion->sub ) {
					if ( $proveedor->estatus > 0 ) {
						$html .= '<td>'.$val.'</td>';
					} else {
						$html .= utf8_decode('<td><span class="invalido">Inválido</span></td>');
					}
				}
			}
			$html .= '</tr>';
	
			if ( $seccion->sub ) {
				$ss = 1;
				foreach ( $subsecciones as $subseccion ) {
					if ( $subseccion->seccion == $seccion->id_seccion ) {
						$html .= '<tr><td class="izq">';
						$html .= '<span>'.$arr_romanos[($ss++)].". ".$subseccion->nombre.'</span></td>';
							
						foreach ( $proveedores as $proveedor ) {
							$val_s = 0;
							if ( isset($resultados[$evaluador->id_usuario][$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor]) ) {
								$val_s = $resultados[$evaluador->id_usuario][$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor];
							}
							${"t_".$proveedor->id_proveedor} += $val_s;
	
							if ( $proveedor->estatus > 0 ) {
								$html .= '<td>'.$val_s.'</td>';
							} else {
								$html .= utf8_decode('<td><span class="invalido">Inválido</span></td>');
							}
	
						}
						$html .= '</tr>';
					}
				}
			}
		}
	
		$html .= '<tr>';
		$html .= '<td class="izq puntos">Total de puntos:</td>';
		foreach ( $proveedores as $proveedor ) {
			if ( $proveedor->estatus > 0 ) {
				${"total_".$proveedor->id_proveedor} += ${"t_".$proveedor->id_proveedor};
				$html .= '<td class="puntos">'.${"t_".$proveedor->id_proveedor}.'</td>';
			} else {
				$html .= utf8_decode('<td class="puntos">Inválido</td>');
			}
		}
		$html .= '</tr>';
	
		$html .= '</table>';
	}
	
	$html .= '<p>&nbsp;</p>';
	$html .= '<table class="resumen">';
	$html .= '<tr>';
	$html .= '<td class="izq puntos">Total de puntos:</td>';
	
	foreach ( $proveedores as $proveedor ) {
		if ( $proveedor->estatus > 0 ) {
			$html .= '<td class="puntos">'.${"total_".$proveedor->id_proveedor}.'</td>';
		} else {
			$html .= utf8_decode('<td class="puntos"><span class="invalido">Inválido</span></td>');
		}
	}
	
	$html .= '</tr></table>';

	$html .= utf8_decode('<p class="nota">* No se procedió a evaluar la Propuesta Técnica y de Servicio de la empresa
	ITMS GROUP INC., por incumplir con los lineamientos del procedimiento de asignación.</p>');

	$mpdf->WriteHTML($stylesheet, 1);
	$mpdf->WriteHTML(utf8_encode($html));

	$mpdf->Output('resumen.pdf', 'D');
	exit();
});

Flight::route('/concentrado/', function() {
	session_start();
	
	$con = Flight::db();
	
	if ( !$_SESSION ) {
		Flight::redirect('/');
	}
	
	$id_evaluacion = 10;
	
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
	
	$query = "SELECT id_proveedor, nombre, estatus FROM proveedor WHERE estatus = 1 ORDER BY nombre";
	$stm = $con->prepare($query);
	$stm->execute();
	$proveedores = $stm->fetchAll(PDO::FETCH_OBJ);
	
	$resultados = array();
	
	foreach ( $secciones as $seccion ) {
		if ( !$seccion->sub ) {
			foreach ( $proveedores as $proveedor ) {
				$resultados[$seccion->id_seccion.'_0_'.$proveedor->id_proveedor] = 0;
			}
		} else {
			foreach ( $subsecciones as $subseccion ) {
				foreach ( $proveedores as $proveedor ) {
					$resultados[$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor] = 0;
				}
			}
		}
	}
	
	foreach ( $secciones as $seccion ) {
		if ( !$seccion->sub ) {
			foreach ( $proveedores as $proveedor ) {
				$query = "SELECT r.id_respuesta, r.respuesta, r.evaluacion, r.proveedor, p.id_pregunta, p.seccion, p.subseccion 
						FROM respuesta r 
						JOIN pregunta p ON r.pregunta = p.id_pregunta 
						WHERE p.seccion = ? AND p.subseccion IS NULL AND r.proveedor = ?";
				$stm = $con->prepare($query);
				$stm->execute(array($seccion->id_seccion, $proveedor->id_proveedor));
				$res = $stm->fetchAll(PDO::FETCH_OBJ);
				
				foreach ( $res as $val ) {
					switch ( $val->respuesta ) {
						case 'S':
							$resultados[$seccion->id_seccion.'_0_'.$proveedor->id_proveedor] += 2;
							break;
						case 'P':
							$resultados[$seccion->id_seccion.'_0_'.$proveedor->id_proveedor] += 1;
							break;
						default:
							break;
					}
				}
			}
		} else {
			foreach ( $subsecciones as $subseccion ) {
				foreach ( $proveedores as $proveedor ) {
					$query = "SELECT r.id_respuesta, r.respuesta, r.evaluacion, r.proveedor, p.id_pregunta, p.seccion, p.subseccion
						FROM respuesta r
						JOIN pregunta p ON r.pregunta = p.id_pregunta
						WHERE p.seccion = ? AND p.subseccion = ? AND r.proveedor = ?";
					$stm = $con->prepare($query);
					$stm->execute(array($seccion->id_seccion, $subseccion->id_subseccion, $proveedor->id_proveedor));
					$res = $stm->fetchAll(PDO::FETCH_OBJ);
					
					foreach ( $res as $val ) {
						switch ( $val->respuesta ) {
							case 'S':
								$resultados[$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor] += 2;
								break;
							case 'P':
								$resultados[$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor] += 1;
								break;
							default:
								break;
						}
					}
				}
			}
		}
	}
	
	$datos = array(
			'secciones'		=> $secciones,
			'subsecciones'	=> $subsecciones,
			'proveedores'	=> $proveedores,
			'resultados'	=> $resultados
	);
	
	Flight::render('header');
	Flight::render('concentrado', $datos);
	Flight::render('footer');
});

Flight::route('/propuesta-desglozada/', function() {
	session_start();
	
	$con = Flight::db();
	
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
	
	$datos = array(
			'secciones'		=> $secciones,
			'subsecciones'	=> $subsecciones,
			'proveedores'	=> $proveedores
	);
	
	Flight::render('propuesta_desglozada', $datos);
});

Flight::route('/detalle-evaluador/', function() {
	session_start();
	
	if ( !$_SESSION ) {
		Flight::redirect('/');
	}
	
	$con = Flight::db();
	
	$query = "SELECT u.id_usuario, u.grado, u.nombre, u.ap_paterno, u.ap_materno, e.id_evaluacion 
			FROM usuario u LEFT JOIN evaluacion e ON u.id_usuario = e.usuario 
			WHERE u.tipo_usuario = 2 AND u.estatus = 1 
			ORDER BY u.nombre, u.ap_paterno, u.ap_materno";
	$stm = $con->prepare($query);
	$stm->execute();
	$evaluadores = $stm->fetchAll(PDO::FETCH_OBJ);
	
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

	$query = "SELECT id_proveedor, nombre, estatus FROM proveedor WHERE estatus = 1 ORDER BY nombre";
	$stm = $con->prepare($query);
	$stm->execute();
	$proveedores = $stm->fetchAll(PDO::FETCH_OBJ);

	$totales = array();
	
	foreach ( $evaluadores as $evaluador ) {
		foreach ( $proveedores as $proveedor ) {
			$totales[$evaluador->id_usuario][$proveedor->id_proveedor] = 0;
		}
	}
	
	foreach ( $evaluadores as $evaluador ) {
		foreach ( $proveedores as $proveedor ) {
			$query = "SELECT r.id_respuesta, r.respuesta, r.evaluacion, r.proveedor
						FROM respuesta r
						JOIN evaluacion e ON r.evaluacion = e.id_evaluacion
						WHERE e.usuario = ? AND r.proveedor = ?";
			$stm = $con->prepare($query);
			$stm->execute(array($evaluador->id_usuario, $proveedor->id_proveedor));
			$res = $stm->fetchAll(PDO::FETCH_OBJ);
				
			foreach ( $res as $val ) {
				switch ( $val->respuesta ) {
					case 'S':
						$totales[$evaluador->id_usuario][$proveedor->id_proveedor] += 2;
						break;
					case 'P':
						$totales[$evaluador->id_usuario][$proveedor->id_proveedor] += 1;
						break;
					default:
						break;
				}
			}
		}
	}
	
	$resultados = array();
	
	foreach ( $evaluadores as $evaluador ) {
		foreach ( $secciones as $seccion ) {
			if ( !$seccion->sub ) {
				foreach ( $proveedores as $proveedor ) {
					$resultados[$evaluador->id_usuario][$seccion->id_seccion.'_0_'.$proveedor->id_proveedor] = 0;
				}
			} else {
				foreach ( $subsecciones as $subseccion ) {
					foreach ( $proveedores as $proveedor ) {
						$resultados[$evaluador->id_usuario][$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor] = 0;
					}
				}
			}
		}
	}
	
	foreach ( $evaluadores as $evaluador ) {
		foreach ( $secciones as $seccion ) {
			if ( !$seccion->sub ) {
				foreach ( $proveedores as $proveedor ) {
					$query = "SELECT r.id_respuesta, r.respuesta, r.evaluacion, r.proveedor, p.id_pregunta, p.seccion, p.subseccion
						FROM respuesta r
						JOIN pregunta p ON r.pregunta = p.id_pregunta
						WHERE r.evaluacion = ? AND p.seccion = ? AND p.subseccion IS NULL AND r.proveedor = ?";
					$stm = $con->prepare($query);
					$stm->execute(array($evaluador->id_evaluacion, $seccion->id_seccion, $proveedor->id_proveedor));
					$res = $stm->fetchAll(PDO::FETCH_OBJ);
	
					foreach ( $res as $val ) {
						switch ( $val->respuesta ) {
							case 'S':
								$resultados[$evaluador->id_usuario][$seccion->id_seccion.'_0_'.$proveedor->id_proveedor] += 2;
								break;
							case 'P':
								$resultados[$evaluador->id_usuario][$seccion->id_seccion.'_0_'.$proveedor->id_proveedor] += 1;
								break;
							default:
								break;
						}
					}
				}
			} else {
				foreach ( $subsecciones as $subseccion ) {
					foreach ( $proveedores as $proveedor ) {
						$query = "SELECT r.id_respuesta, r.respuesta, r.evaluacion, r.proveedor, p.id_pregunta, p.seccion, p.subseccion
						FROM respuesta r
						JOIN pregunta p ON r.pregunta = p.id_pregunta
						WHERE r.evaluacion = ? AND p.seccion = ? AND p.subseccion = ? AND r.proveedor = ?";
						$stm = $con->prepare($query);
						$stm->execute(array($evaluador->id_evaluacion, $seccion->id_seccion, $subseccion->id_subseccion, $proveedor->id_proveedor));
						$res = $stm->fetchAll(PDO::FETCH_OBJ);
							
						foreach ( $res as $val ) {
							switch ( $val->respuesta ) {
								case 'S':
									$resultados[$evaluador->id_usuario][$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor] += 2;
									break;
								case 'P':
									$resultados[$evaluador->id_usuario][$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor] += 1;
									break;
								default:
									break;
							}
						}
					}
				}
			}
		}
	}

	$datos = array(
			'evaluadores'	=> $evaluadores,
			'secciones'		=> $secciones,
			'subsecciones'	=> $subsecciones,
			'proveedores'	=> $proveedores,
			'resultados'	=> $resultados,
			'totales'		=> $totales
	);

	Flight::render('header');
	Flight::render('detalle', $datos);
	Flight::render('footer');
});

Flight::route('/resultado-propuesta-economica/', function() {
	session_start();
	
	if ( !$_SESSION ) {
		Flight::redirect('/');
	}
	
	$con = Flight::db();

	$query = "SELECT id_proveedor, nombre, abreviatura, estatus FROM proveedor WHERE estatus = 1 ORDER BY nombre";
	$stm = $con->prepare($query);
	$stm->execute();
	$proveedores = $stm->fetchAll(PDO::FETCH_OBJ);

	$query = "SELECT u.id_usuario, u.grado, u.nombre, u.ap_paterno, u.ap_materno, e.id_evaluacion FROM usuario u
	LEFT JOIN evaluacion e ON u.id_usuario = e.usuario
	WHERE u.estatus = 1 AND u.tipo_usuario = 2 ORDER BY u.nombre, u.ap_paterno, u.ap_materno";
	$stm = $con->prepare($query);
	$stm->execute();
	$evaluadores = $stm->fetchAll(PDO::FETCH_OBJ);

	$resultados = array();

	foreach ( $evaluadores as $evaluador ) {
		foreach ( $proveedores as $proveedor ) {
			$resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor] = 0;
		}
	}

	foreach ( $evaluadores as $evaluador ) {
		foreach ( $proveedores as $proveedor ) {
			$query = "SELECT r.id_respuesta, r.respuesta, r.evaluacion, r.proveedor
				FROM respuesta r
				JOIN evaluacion e ON r.evaluacion = e.id_evaluacion
				WHERE e.usuario = ? AND r.proveedor = ?";
			$stm = $con->prepare($query);
			$stm->execute(array($evaluador->id_usuario, $proveedor->id_proveedor));
			$res = $stm->fetchAll(PDO::FETCH_OBJ);

			foreach ( $res as $val ) {
				switch ( $val->respuesta ) {
					case 'S':
						$resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor] += 2;
						break;
					case 'P':
						$resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor] += 1;
						break;
					default:
						break;
				}
			}
		}
	}

	$p_costos_arr = array(
			'¿La empresa incluyó el costo de cada concepto y servicio?',
			'¿La empresa incluyó en la propuesta los montos por pago de impuestos?',
			'¿La empresa incluyó en su propuesta el porcentaje de incremento anual para los años de servicio?',
			'¿La propuesta económica se presentó en Moneda Nacional?'
	);

	$p_costos = array();

	foreach ( $p_costos_arr as $key => $val ) {
		$dato = new stdClass();
		$dato->id = $key+1;
		$dato->pregunta = $val;

		foreach ( $proveedores as $proveedor ) {
			$query = "SELECT pregunta".$dato->id." AS resp FROM evaluacion_economica WHERE proveedor = ? AND estatus = 1";
			$stm = $con->prepare($query);
			$stm->execute(array($proveedor->id_proveedor));
			$res_p_eco = $stm->fetch();
				
			switch ( $res_p_eco['resp'] ) {
				case 's':
					$dato->{"ev_".$proveedor->id_proveedor} = "Sí";
					break;
				case 'p':
					$dato->{"ev_".$proveedor->id_proveedor} = "Parcialmente";
					break;
				case 'n':
					$dato->{"ev_".$proveedor->id_proveedor} = "No";
					break;
				default:
					$dato->{"ev_".$proveedor->id_proveedor} = "";
					break;
			}
		}

		$p_costos[] = $dato;
	}

	$periodos = array('2015-2016','2016-2017','2017-2018','2018-2019');

	$montos = array();

	foreach ( $proveedores as $proveedor ) {
		$dato = new stdClass();
		$dato->proveedor = utf8_encode($proveedor->abreviatura);
		$dato->estatus = $proveedor->estatus;
		$dato->subtotal = 0;

		foreach ( $periodos as $key => $periodo ) {
			$query = "SELECT total".($key+1)." AS total FROM evaluacion_economica WHERE proveedor = ? AND estatus = 1";
			$stm = $con->prepare($query);
			$stm->execute(array($proveedor->id_proveedor));
			$res_monto = $stm->fetch();
				
			$dato->{"t_".$key} = ($res_monto) ? $res_monto['total'] : 0;
			$dato->subtotal += $dato->{"t_".$key};
		}

		$query = "SELECT observaciones FROM evaluacion_economica WHERE proveedor = ? AND estatus = 1";
		$stm = $con->prepare($query);
		$stm->execute(array($proveedor->id_proveedor));
		$res_monto = $stm->fetchObject();

		$dato->comentarios = ($res_monto) ? $res_monto->observaciones : "";
		$montos[] = $dato;
	}

	$datos = array(
			'evaluadores'	=> $evaluadores,
			'proveedores'	=> $proveedores,
			'resultados'	=> $resultados,
			'p_costos'		=> $p_costos,
			'periodos'		=> $periodos,
			'montos'		=> $montos
	);

	Flight::render('header');
	Flight::render('vista_p_economica', $datos);
	Flight::render('footer');
});

Flight::route('/resumen-general/', function() {
	session_start();
	
	if ( !$_SESSION ) {
		Flight::redirect('/');
	}
	
	$con = Flight::db();

	$query = "SELECT id_proveedor, nombre, abreviatura, estatus FROM proveedor WHERE estatus = 1 ORDER BY nombre";
	$stm = $con->prepare($query);
	$stm->execute();
	$proveedores = $stm->fetchAll(PDO::FETCH_OBJ);

	$query = "SELECT u.id_usuario, u.grado, u.nombre, u.ap_paterno, u.ap_materno, e.id_evaluacion FROM usuario u
		LEFT JOIN evaluacion e ON u.id_usuario = e.usuario
		WHERE u.estatus = 1 AND u.tipo_usuario = 2 ORDER BY u.nombre, u.ap_paterno, u.ap_materno";
	$stm = $con->prepare($query);
	$stm->execute();
	$evaluadores = $stm->fetchAll(PDO::FETCH_OBJ);

	$resultados = array();

	foreach ( $evaluadores as $evaluador ) {
		foreach ( $proveedores as $proveedor ) {
			$resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor] = 0;
		}
	}

	foreach ( $evaluadores as $evaluador ) {
		foreach ( $proveedores as $proveedor ) {
			$query = "SELECT r.id_respuesta, r.respuesta, r.evaluacion, r.proveedor
					FROM respuesta r
					JOIN evaluacion e ON r.evaluacion = e.id_evaluacion
					WHERE e.usuario = ? AND r.proveedor = ?";
			$stm = $con->prepare($query);
			$stm->execute(array($evaluador->id_usuario, $proveedor->id_proveedor));
			$res = $stm->fetchAll(PDO::FETCH_OBJ);
				
			foreach ( $res as $val ) {
				switch ( $val->respuesta ) {
					case 'S':
						$resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor] += 2;
						break;
					case 'P':
						$resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor] += 1;
						break;
					default:
						break;
				}
			}
		}
	}
	
	$p_costos_arr = array(
			'¿La empresa incluyó el costo de cada concepto y servicio?',
			'¿La empresa incluyó en la propuesta los montos por pago de impuestos?',
			'¿La empresa incluyó en su propuesta el porcentaje de incremento anual para los años de servicio?',
			'¿La propuesta económica se presentó en Moneda Nacional?'
	);
	
	$p_costos = array();
	
	foreach ( $p_costos_arr as $key => $val ) {
		$dato = new stdClass();
		$dato->id = $key+1;
		$dato->pregunta = $val;
		
		foreach ( $proveedores as $proveedor ) {
			$query = "SELECT pregunta".$dato->id." AS resp FROM evaluacion_economica WHERE proveedor = ? AND estatus = 1";
			$stm = $con->prepare($query);
			$stm->execute(array($proveedor->id_proveedor));
			$res_p_eco = $stm->fetch();
			
			switch ( $res_p_eco['resp'] ) {
				case 's':
					$dato->{"ev_".$proveedor->id_proveedor} = "Sí";
					break;
				case 'p':
					$dato->{"ev_".$proveedor->id_proveedor} = "Parcialmente";
					break;
				case 'n':
					$dato->{"ev_".$proveedor->id_proveedor} = "No";
					break;
				default:
					$dato->{"ev_".$proveedor->id_proveedor} = "";
					break;
			}
		}
		
		$p_costos[] = $dato;
	}
	
	$periodos = array('2015-2016','2016-2017','2017-2018','2018-2019');
	
	$montos = array();
	
	foreach ( $proveedores as $proveedor ) {
		$dato = new stdClass();
		$dato->proveedor = utf8_encode($proveedor->abreviatura);
		$dato->estatus = $proveedor->estatus;
		$dato->subtotal = 0;
		
		foreach ( $periodos as $key => $periodo ) {
			$query = "SELECT total".($key+1)." AS total FROM evaluacion_economica WHERE proveedor = ? AND estatus = 1";
			$stm = $con->prepare($query);
			$stm->execute(array($proveedor->id_proveedor));
			$res_monto = $stm->fetch();
			
			$dato->{"t_".$key} = ($res_monto) ? $res_monto['total'] : 0;
			$dato->subtotal += $dato->{"t_".$key};
		}
		
		$query = "SELECT observaciones FROM evaluacion_economica WHERE proveedor = ? AND estatus = 1";
		$stm = $con->prepare($query);
		$stm->execute(array($proveedor->id_proveedor));
		$res_monto = $stm->fetchObject();
		
		$dato->comentarios = ($res_monto) ? $res_monto->observaciones : ""; 
		$montos[] = $dato;
	}
	
	$datos = array(
			'evaluadores'	=> $evaluadores,
			'proveedores'	=> $proveedores,
			'resultados'	=> $resultados,
			'p_costos'		=> $p_costos,
			'periodos'		=> $periodos,
			'montos'		=> $montos
	);

	Flight::render('header');
	Flight::render('resumen', $datos);
	Flight::render('footer');
});

Flight::route('/guardarDictamen/', function() {
	session_start();
	
	if ( !$_SESSION ) {
		Flight::redirect('/');
	}
	
	$fecha = date('Y-m-d H:i:s');
	$proveedor = (isset($_POST['proveedor'])) ? $_POST['proveedor'] : "";
	$periodo = (isset($_POST['periodo'])) ? addslashes($_POST['periodo']) : "";
	$comentarios = (isset($_POST['comentarios'])) ? $_POST['comentarios'] : "";
	
	$con = Flight::db();
	
	$query = "INSERT INTO dictamen(fecha, proveedor, periodo, comentarios) VALUES(?, ?, ?, ?)";
	$stm = $con->prepare($query);
	if ( $stm->execute(array($fecha, $proveedor, $periodo, $comentarios)) ) {
		Flight::redirect('/imprimirFallo');
	}
});

/* Se inicializa el framework */
Flight::start();
?>