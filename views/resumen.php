<link rel="stylesheet" href="../css/reporte.css" type="text/css" />

<script type="text/javascript">
$(function() {
	$("#formDictamen").validate({
		errorElement: "span",
		rules: {
			proveedor: {
				required: true
			},
			periodo: {
				required: true
			}
		},
		messages: {
			proveedor: "",
			periodo: ""
		}
	});

	$("#btnEnviar").click(function() {
		$("#formDictamen").submit();
	});
});
</script>
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

<h3>Resultados de la evaluación de las propuestas técnicas y de servicios</h3>
<table class="table table-condensed table-striped">
	<tr>
		<th>Evaluador</th>
		<?php
		foreach ( $proveedores as $proveedor ) {
			${"total_".$proveedor->id_proveedor} = 0;
		?>
		<th><?php echo $proveedor->nombre.($proveedor->estatus == 0 ? " *" : ""); ?></th>
		<?php
		}
		?>
	</tr>
	<?php
	foreach ( $evaluadores as $evaluador ) {
	?>
	<tr>
		<td><?php echo trim(utf8_encode($evaluador->grado." ".$evaluador->nombre." ".$evaluador->ap_paterno." ".$evaluador->ap_materno)); ?></td>
		<?php
		foreach ( $proveedores as $proveedor ) {
			${"total_".$proveedor->id_proveedor} += $resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor];
			
			if ( $proveedor->estatus > 0 ) {
		?>
		<td><?php echo $resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor]; ?></td>
		<?php
			} else {
				echo '<td><span class="invalido">Inválido</span></td>';
			}
		}
		?>
	</tr>
	<?php
	}
	?>
	<tr class="total">
		<td>Total de puntos:</td>
		<?php
		foreach ( $proveedores as $proveedor ) {
			if ( $proveedor->estatus > 0 ) {
		?>
		<td><?php echo ${"total_".$proveedor->id_proveedor}; ?></td>
		<?php
			} else {
				echo '<td><span class="invalido">Inválido</span></td>';
			}
		}
		?>
	</tr>
</table>

<h3>Resultados de la evaluación de las propuestas económicas</h3>
<table class="table table-condensed table-striped">
  <tr>
    <th>Concepto</th>
    <?php
	foreach ( $proveedores as $proveedor ) {
	?>
	<th><?php echo $proveedor->nombre.($proveedor->estatus == 0 ? " *" : ""); ?></th>
	<?php
	}
	?>
  </tr>
  <?php
  foreach ( $p_costos as $preg ) {
  ?>
  <tr>
    <td><?php echo $preg->pregunta;?></td>
    <?php
	foreach ( $proveedores as $proveedor ) {
		if ( $proveedor->estatus > 0 ) {
	?>
	<td><?php echo $preg->{"ev_".$proveedor->id_proveedor}; ?></td>
	<?php
		} else {
			echo '<td><span class="invalido">Inválido</span></td>';
		}
	}
	?>
  </tr>
  <?php
  }
  ?>
</table>

<h3>Cuadro resumen de costos por año y empresa</h3>
<table class="table table-condensed table-striped">
  <tr>
    <th>Empresa</th>
    <?php
	foreach ( $periodos as $periodo ) {
	?>
	<th><?php echo $periodo; ?></th>
	<?php
	}
	?>
	<th>Subtotal</th>
	<?php
	if ( 1 == 1 ) {
	?>
	<th>Comentarios</th>
	<?php
	}
	?>
  </tr>
  <?php
  foreach ( $montos as $monto ) {
  ?>
  <tr>
    <td><?php echo $monto->proveedor.($monto->estatus == 0 ? " *" : "");?></td>
    <?php
	for ( $i=0; $i<sizeof($periodos); $i++ ) {
		if ( $monto->estatus > 0 ) {
	?>
	<td><?php echo '$'.number_format($monto->{"t_".$i}, 2); ?></td>
	<?php
		} else {
			echo '<td><span class="invalido">Inválido</span></td>';
		}
	}
	?>
	<td>
	<?php
	if ( $monto->estatus > 0 ) {
		echo '$'.number_format($monto->subtotal, 2);
	} else {
		echo '<span class="invalido">Inválido</span>';
	}
	?>
	</td>
	<?php
	if ( 1==1 ) {
	?>
	<td>
	<?php
	if ( $monto->estatus > 0 ) {
		echo utf8_encode($monto->comentarios);
	} else {
		echo '<span class="invalido">Inválido</span>';
	}
	?>
	</td>
	<?php
	}
	?>
  </tr>
  <?php
  }
  ?>
</table>
<p class="nota">* No se procedió a evaluar la Propuesta Técnica, de Servicio y Económica de la empresa ITMS GROUP INC., 
por incumplir con los lineamientos del procedimiento de asignación.</p>

<form id="formDictamen" name="formDictamen" method="post" action="guardarDictamen">
	<label>La empresa seleccionada es:</label>
	<select id="proveedor" name="proveedor" class="form-control">
		<option value="">Selecciona</option>
		<?php
		foreach ( $proveedores as $proveedor ) {
			if ( $proveedor->estatus > 0) {
		?>
			<option value="<?php echo $proveedor->id_proveedor; ?>"><?php echo utf8_encode($proveedor->abreviatura); ?></option>
		<?php
			}
		}
		?>
	</select>
	<label>Por un periodo de :</label>
	<div class="input-group">
		<input type="text" id="periodo" name="periodo" class="form-control" />
		<div class="input-group-addon">años</div>
	</div>
	<label>Comentarios:</label>
	<textarea rows="5" id="comentarios" name="comentarios" class="form-control"></textarea>
	<button id="btnEnviar" class="btn btn-primary">Generar dictamen</button>
</form>