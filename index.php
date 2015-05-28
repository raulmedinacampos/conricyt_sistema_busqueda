<?php
require 'flight/Flight.php';

/* Registro y conexión a base de datos */
$host	= "127.0.0.1";
$user	= "root";
$pass	= "";
$db		= "conricyt_evaluacion";
$dbh	= "mysql:host=$host;port=3306;dbname=$db";

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

Flight::register('db', 'PDO', array($dbh, $user, $pass), function ($db) {
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
});

/* Ruteos */
Flight::route('POST /login/', function() {
	session_start();

	$con = Flight::db();

	$usuario = (isset($_POST['usuario'])) ? addslashes($_POST['usuario']) : "";
	$password = (isset($_POST['password'])) ? addslashes($_POST['password']) : "";

	$query = "SELECT id_usuario, login, tipo_usuario FROM usuario WHERE login = ? AND password = ? LIMIT 1";
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
			
			$datos = array('usuarios' => $usuarios);
			Flight::render('resultados', $datos);
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
	
	$query = "SELECT grado, nombre, ap_paterno, ap_materno, cargo, institucion FROM usuario WHERE tipo_usuario = 2 AND estatus = 1";
	$stm = $con->prepare($query);
	$stm->execute();
	$evaluadores = $stm->fetchAll(PDO::FETCH_OBJ);
	
	$query = "SELECT u.grado, u.nombre, u.ap_paterno, u.ap_materno FROM usuario u JOIN evaluacion e ON u.id_usuario = e.usuario WHERE u.estatus = 1";
	$stm = $con->prepare($query);
	$stm->execute();
	$ev = $stm->fetchObject();
	
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
	
	$html = utf8_decode('<h3 class="titulo">Formato de Evaluación de Propuestas Técnicas,<br />de Servicios y Económicas</h3>');
	$html .= utf8_decode('<p>La evaluación de las propuestas técnicas, de servicios y económicas estará sujeta a los rubros descritos ');
	$html .= utf8_decode('y cotizados en el Formato de Presentación de Propuestas Técnicas, de Servicio y Económicas</p>');
	$html .= '<p class="evaluador"><strong>Nombre del evaluador: </strong>'.$ev->nombre." ".$ev->ap_paterno." ".$ev->ap_materno.'</p>';
	
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
	
	foreach ( $proveedores as $proveedor ) {
		${"t_".$proveedor->id_proveedor} = 0;
	}
	
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
						$valor = array(utf8_decode('Sí'), 'Parcialmente', 'No', 'Desconocido');
						
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
						$html .= '<td>'.str_replace($orig, $valor, $resp).'</td>';
					}
					$html .= '</tr>';
				}
			}
			$html .= '<tr>';
			$html .= '<td class="izq puntos">Total de puntos:</td>';
			foreach ( $proveedores as $proveedor ) {
				${"t_".$proveedor->id_proveedor} += ${"total_".$proveedor->id_proveedor};
				$html .= '<td class="puntos">'.${"total_".$proveedor->id_proveedor}.'</td>';
			}
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="izq observaciones" colspan="'.(sizeof($proveedores)+1).'"><strong>Observaciones:</strong><br/>';
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
								$valor = array(utf8_decode('Sí'), 'Parcialmente', 'No', 'Desconocido');
							
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
								$html .= '<td>'.str_replace($orig, $valor, $resp).'</td>';
							}
							
							$html .= '</tr>';
						}
					}
					
					$html .= '<tr>';
					$html .= '<td class="izq puntos">Total de puntos:</td>';
					foreach ( $proveedores as $proveedor ) {
						${"t_".$proveedor->id_proveedor} += ${"total_".$proveedor->id_proveedor};
						$html .= '<td class="puntos">'.${"total_".$proveedor->id_proveedor}.'</td>';
					}
					$html .= '</tr>';
					$html .= '<tr>';
					$html .= '<td class="izq observaciones" colspan="'.(sizeof($proveedores)+1).'"><strong>Observaciones:</strong><br/>';
					$html .= str_replace("\n", "<br />", $observaciones[$seccion->id_seccion."_".$subseccion->id_subseccion]['respuesta']);
					$html .= '</td>';
					$html .= '</table>';
				}
			}
		}
	}
	
	$clase = "sombreado";
	$html .= '<h4>&nbsp;</h4>';
	$html .= '<table class="ponderacion" style="width:50%;">';
	$html .= '<tr>';
	$html .= utf8_decode('<td colspan="2" class="titulo-tabla">Puntaje total</td>');
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<th class="izq">Proveedor</th>';
	$html .= '<th>Puntaje</th>';
	$html .= '</tr>';
	foreach ( $proveedores as $proveedor ) {
		$clase = ($clase == "") ? "sombreado" : "";
		$html .= '<tr>';
		$html .= '<td class="izq '.$clase.'">'.str_replace("<br />", " ", $proveedor->nombre).'</td>';
		$html .= '<td class="'.$clase.'">'.${"t_".$proveedor->id_proveedor}.' puntos</td>';
		$html .= '</tr>';
	}
	$html .= '</table>';
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://evaluacion.dev/css/pdf.css');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$stylesheet = curl_exec($ch);
	curl_close($ch);
	
	$header = '<img id="logo-conricyt" src="../images/ch_logo1.png"/><img id="logo-conacyt" src="../images/ch_logo2.png"/>';
	$footer = '<footer><p class="numeracion">Página {PAGENO} de {nbpg}</p>';
	$footer .= '<p><strong>Oficina del Consorcio Nacional de Recursos de Información Científica y Tecnológica</strong><br />';
	$footer .= 'Av. Insurgentes Sur 1582, Col. Crédito Constructor, Deleg. Benito Juárez, C.P. 03940 ';
	$footer .= 'México D.F. – Tel: 5322 7700 ext  4020 a la 4026</p></footer>';
	
	$mpdf = new mPDF('utf-8', 'Letter', 0, 'Arial', 13, 13, 35, 25);
	
	$mpdf->SetHTMLHeader($header);
	$mpdf->SetHTMLFooter($footer);
	
	$mpdf->WriteHTML($stylesheet, 1);
	$mpdf->WriteHTML(utf8_encode($html));
	
	$mpdf->AddPage();
	$html = '<div class="firmas">';
	
	foreach ( $evaluadores as $evaluador ) {
		$html .= '<div class="firma-div">';
		$html .= '<div class="firma">';
		$html .= '<span class="nombre">'.trim($evaluador->grado." ".$evaluador->nombre." ".$evaluador->ap_paterno." ".$evaluador->ap_materno).'</span><br />';
		$html .= '<span>'.$evaluador->cargo.'</span><br />';
		$html .= '<span>'.$evaluador->institucion.'</span>';
		$html .= '</div>';
		$html .= '</div>';
	}
	$html .= '</div>';
	
	$html .= '<p class="fin">Firmas que avalan el <strong>ACUERDO 03/III/2015-CDT</strong> de la ';
	$html .= utf8_decode('Tercera Sesión de la Comisión de Desarrollo Tecnológico, ');
	$html .= utf8_decode('celebrada el 21 de Abril de 2015, consta de '.$mpdf->PageNo().' fojas útiles por anverso, ');
	$html .= 'incluyendo esta.<br />';
	$html .= 'Consta.----------------------------------------------------------------------';
	$html .= '-----------------------------------------------------------------------------</p>';
	
	$mpdf->WriteHTML($stylesheet, 1);
	$mpdf->WriteHTML(utf8_encode($html));
	
	$mpdf->Output('evaluación.pdf', 'D');
	exit();
});

/* Se inicializa el framework */
Flight::start();
?>