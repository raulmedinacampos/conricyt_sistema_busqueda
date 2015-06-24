<link rel="stylesheet" href="../css/reporte.css" type="text/css" />

<div class="titulo">
	<h3 class="titulo col-sm-10 col-sm-offset-1">Evaluación de Propuestas Técnicas, de Servicios y Económicas</h3>
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

<a href="imprimirResumen"><span class="btn btn-warning pull-right">Imprimir PDF</span></a>

<h3>Resultados. Detalle de la evaluación por rubro</h3>
<table class="table table-condensed table-striped">
	<tr>
		<th>Rubro evaluado</th>
		<?php
		foreach ( $proveedores as $proveedor ) {
			${"total_".$proveedor->id_proveedor} = 0;
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
				if ( isset($resultados[$seccion->id_seccion.'_0_'.$proveedor->id_proveedor]) ) {
					$val = $resultados[$seccion->id_seccion.'_0_'.$proveedor->id_proveedor];
				}
				${"t_".$proveedor->id_proveedor} += $val;
				
				if ( !$seccion->sub ) {
			?>
			<td>
			<?php
			if ( $proveedor->estatus > 0 ) {
				echo $val;
			} else {
				echo '<span class="invalido">Inválido</span>';
			}
			?>
			</td>
			<?php
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
							if ( isset($resultados[$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor]) ) {
								$val_s = $resultados[$seccion->id_seccion.'_'.$subseccion->id_subseccion.'_'.$proveedor->id_proveedor];
							}
							${"t_".$proveedor->id_proveedor} += $val_s;
						?>
						<td>
						<?php
						if ( $proveedor->estatus > 0 ) {
							echo $val_s;
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
			}
		}
	?>
	<?php
	}
	?>
	<tr class="total">
		<td>Total de puntos:</td>
		<?php
		foreach ( $proveedores as $proveedor ) {
			if ( $proveedor->estatus > 0 ) {
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
<p class="nota">* No se procedió a evaluar la Propuesta Técnica y de Servicio de la empresa ITMS GROUP INC., 
por incumplir con los lineamientos del procedimiento de asignación.</p>