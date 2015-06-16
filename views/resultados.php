<div class="titulo">
	<h3 class="titulo col-sm-10 col-sm-offset-1">Evaluación de Propuestas Técnicas, de Servicios y Económicas</h3>
	<div class="salir col-sm-1"><a href="/salir"><img src="../images/salida.png" />Salir</a></div>
</div>

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="active"><a href="/">Inicio <span class="sr-only">(current)</span></a></li>
        <li><a href="evaluacion-economica">Evaluación económica</a></li>
        <li><a href="resumen-general">Resultados de la evaluación</a></li>
        <li><a href="imprimirFallo">Imprimir dictamen</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Ver resultados <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="detalle-evaluador">Detalle por evaluador</a></li>
            <li><a href="concentrado">Resumen general</a></li>
            <li><a href="resultado-propuesta-economica">Propuesta económica</a></li>
          </ul>
        </li>
      </ul>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

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
    <td><a href="imprimirEvaluacion/<?php echo $usuario->id_evaluacion; ?>"><span class="glyphicon glyphicon-file"></span></a></td>
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