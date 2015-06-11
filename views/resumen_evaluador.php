<link rel="stylesheet" href="../css/reporte.css" type="text/css" />

<h3>Resultados de la evaluación</h3>
<table class="table table-condensed table-striped">
	<tr>
		<th>Evaluador</th>
		<?php
		foreach ( $proveedores as $proveedor ) {
			${"total_".$proveedor->id_proveedor} = 0;
		?>
		<th><?php echo $proveedor->nombre; ?></th>
		<?php
		}
		?>
	</tr>
	<?php
	foreach ( $evaluadores as $evaluador ) {
	?>
	<tr>
		<td>
			<?php
			if ( $evaluador->id_evaluacion ) {
			?>
			<a href="detalle-evaluador/<?php echo $evaluador->id_evaluacion; ?>"><?php echo trim(utf8_encode($evaluador->grado." ".$evaluador->nombre." ".$evaluador->ap_paterno." ".$evaluador->ap_materno))?></a>
			<?php
			} else {
				echo trim(utf8_encode($evaluador->grado." ".$evaluador->nombre." ".$evaluador->ap_paterno." ".$evaluador->ap_materno));
			}
			?>
		</td>
		<?php
		foreach ( $proveedores as $proveedor ) {
			${"total_".$proveedor->id_proveedor} += $resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor];
		?>
		<td><?php echo $resultados[$evaluador->id_usuario."_".$proveedor->id_proveedor]; ?></td>
		<?php
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
		?>
		<td><?php echo ${"total_".$proveedor->id_proveedor}; ?></td>
		<?php
		}
		?>
	</tr>
</table>