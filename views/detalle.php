<link rel="stylesheet" href="../css/reporte.css" type="text/css" />

<div class="titulo">
	<h3 class="titulo col-sm-10 col-sm-offset-1">Evaluación de Propuestas Técnicas y de Servicios</h3>
	<div class="salir col-sm-1"><a href="/salir"><img src="../images/salida.png" />Salir</a></div>
</div>

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="active"><a href="/">Inicio <span class="sr-only">(current)</span></a></li>
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

<h3>Resultados. Detalle de la evaluación por evaluador</h3>

<table>
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
</table>

<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
<?php
foreach ( $evaluadores as $evaluador ) {
?>
<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="heading<?php echo $evaluador->id_usuario; ?>">
      <table>
      <tr>
      <td>
      <h4 class="panel-title">
        <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $evaluador->id_usuario; ?>" aria-expanded="false" aria-controls="collapse<?php echo $evaluador->id_usuario; ?>">
          <?php echo trim(utf8_encode($evaluador->grado." ".$evaluador->nombre." ".$evaluador->ap_paterno." ".$evaluador->ap_materno));?>
        </a>
      </h4>
      </td>
      <?php
	  foreach ( $proveedores as $proveedor ) {
	  	if ( $proveedor->estatus > 0 ) {
	  ?>
	  <td><?php echo $totales[$evaluador->id_usuario][$proveedor->id_proveedor]; ?></td>
	  <?php
	  	} else {
	  		echo '<td><span class="invalido">Inválido</span></td>';
	  	}
	  }
      ?>
      </tr>
      </table>
    </div>
    <div id="collapse<?php echo $evaluador->id_usuario; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?php echo $evaluador->id_usuario; ?>">
<table class="table table-condensed table-striped">
	<tr>
		<th>Rubro evaluado</th>
		<?php
		foreach ( $proveedores as $proveedor ) {
			${"t_".$proveedor->id_proveedor} = 0;
		?>
		<th><?php echo $proveedor->nombre.($proveedor->estatus == 0 ? " *" : ""); ?></th>
		<?php
		}
		?>
	</tr>
	<?php
	foreach ( $secciones as $seccion ) {
	?>
		<tr class="resaltado">
			<td <?php if ( $seccion->sub ) {echo 'colspan="'.(sizeof($proveedores)+1).'"';} ?>>
				<ol type="a" start="<?php echo $seccion->id_seccion; ?>">
					<li><?php echo utf8_encode($seccion->nombre); ?></li>
				</ol>
			</td>
			<?php
			foreach ( $proveedores as $proveedor ) {
				$val = 0;
				if ( isset($resultados[$evaluador->id_usuario][$seccion->id_seccion.'_0_'.$proveedor->id_proveedor]) ) {
					$val = $resultados[$evaluador->id_usuario][$seccion->id_seccion.'_0_'.$proveedor->id_proveedor];
				}
				${"t_".$proveedor->id_proveedor} += $val;
				
				if ( !$seccion->sub ) {
					if ( $proveedor->estatus > 0 ) {
			?>
			<td><?php echo $val; ?></td>
			<?php
					} else {
						echo '<td><span class="invalido">Inválido</span></td>';
					}
				}
			}
			?>
		</tr>
	<?php
		if ( $seccion->sub ) {
			$ss = 1;
			foreach ( $subsecciones as $subseccion ) {
				if ( $subseccion->seccion == $seccion->id_seccion ) {
	?>
					<tr>
						<td>
							<ol type="I" start="<?php echo $ss++; ?>">
								<li><?php echo utf8_encode($subseccion->nombre); ?></li>
							</ol>
						</td>
						<?php
						foreach ( $proveedores as $proveedor ) {
							$val_s = 0;
							if ( isset($resultados[$evaluador->id_usuario][$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor]) ) {
								$val_s = $resultados[$evaluador->id_usuario][$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor];
							}
							${"t_".$proveedor->id_proveedor} += $val_s;
							
							if ( $proveedor->estatus > 0 ) {
						?>
						<td><?php echo $val_s; ?></td>
						<?php
							} else {
								echo '<td><span class="invalido">Inválido</span></td>';
							}
						}
						?>
					</tr>
	<?php
				}
			}
		}
	?>
	<?php
	}
	?>
	<tr class="total-claro">
		<td>Total de puntos:</td>
		<?php
		foreach ( $proveedores as $proveedor ) {
			if ( $proveedor->estatus > 0 ) {
				${"total_".$proveedor->id_proveedor} += ${"t_".$proveedor->id_proveedor};
		?>
		<td><?php echo ${"t_".$proveedor->id_proveedor}; ?></td>
		<?php
			} else {
				echo '<td><span class="invalido">Inválido</span></td>';
			}
		}
		?>
	</tr>
</table>
</div>
</div>
<?php
}
?>
</div>
<table class="table table-condensed">
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

<p class="nota">* No se procedió a evaluar la Propuesta Técnica y de Servicio de la empresa ITMS GROUP INC., 
por incumplir con los lineamientos del procedimiento de asignación.</p>