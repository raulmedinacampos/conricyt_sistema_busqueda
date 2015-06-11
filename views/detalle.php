<link rel="stylesheet" href="../css/reporte.css" type="text/css" />

<h3>Evaluación de <?php echo trim(utf8_encode($evaluador->nombre." ".$evaluador->ap_paterno." ".$evaluador->ap_materno));?></h3>
<table class="table table-condensed table-striped">
	<tr>
		<th>Rubro evaluado</th>
		<?php
		foreach ( $proveedores as $proveedor ) {
			${"total_".$proveedor->id_proveedor} = 0;
			${"t_".$proveedor->id_proveedor} = 0;
		?>
		<th><?php echo $proveedor->nombre; ?></th>
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
			<td><?php echo $val; ?></td>
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
						<td><?php echo $val_s; ?></td>
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
		?>
		<td><?php echo ${"t_".$proveedor->id_proveedor}; ?></td>
		<?php
		}
		?>
	</tr>
</table>