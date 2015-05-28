<div class="titulo">
	<h3 class="titulo col-sm-10 col-sm-offset-1">Formato de Evaluación de Propuestas<br />Técnicas, de Servicios y Económicas</h3>
	<div class="salir col-sm-1"><a href="/salir"><img src="../images/salida.png" />Salir</a></div>
</div>

<div class="resultados col-sm-8">
<p>Evaluaciones finalizadas</p>
<table class="table table-striped">
<?php
  if ( $usuarios ) {
?>
  <tr>
    <th>No.</th>
  	<th>Evaluador</th>
    <th>Ver evaluación</th>
  </tr>
  <?php
  	$i = 0;
  	foreach ( $usuarios as $usuario ) {
  		$i++;
  ?>
  <tr>
  	<td><?php echo $i; ?></td>
    <td><?php echo trim(utf8_encode($usuario->grado." ".$usuario->nombre." ".$usuario->ap_paterno." ".$usuario->ap_materno)); ?></td>
    <td><a href="imprimirEvaluacion/<?php echo $usuario->id_evaluacion; ?>"><span class="glyphicon glyphicon-circle-arrow-down text-primary"></span></a></td>
  </tr>
  <?php
  	}
  } else {
  ?>
  <tr>
  	<th>No hay evaluaciones finalizadas</th>
  </tr>
  <?php
  }
  ?>
</table>
</div>