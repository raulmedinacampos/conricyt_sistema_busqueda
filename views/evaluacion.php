<?php
if ( $evaluacion->fecha_finalizacion ) {
?>
<script type="text/javascript">
$(function() {
	$("textarea, select").prop("disabled", "disabled");
});
</script>
<?php
}
?>

<div class="titulo">
	<h3 class="titulo col-sm-10 col-sm-offset-1">Evaluación de Propuestas Técnicas, de Servicios y Económicas</h3>
	<div class="salir col-sm-1"><a href="/salir"><img src="../images/salida.png" />Salir</a></div>
</div>

<p style="clear: both;">La evaluación de las propuestas técnicas, de servicios y económicas
	estará sujeta a los rubros descritos y cotizados en el Formato de
	Presentación de Propuestas Técnicas, de Servicio y Económicas</p>
<div class="ponderacion col-xs-12 col-sm-6">
	<p>Tabla de ponderación</p>
	<table class="table table-striped">
		<tr>
			<th>Puntaje</th>
			<th>Nomenclatura</th>
		</tr>
		<tr>
			<td><strong>2 ptos</strong></td>
			<td><strong>Sí</strong>. La empresa/sistema cumple con el
				requerimiento</td>
		</tr>
		<tr>
			<td><strong>1 ptos</strong></td>
			<td><strong>Parcialmente</strong>. La empresa/sistema cumple
				parcialmente con el requerimiento</td>
		</tr>
		<tr>
			<td><strong>0 ptos</strong></td>
			<td><strong>No</strong>. La empresa/sistema no cumple con el
				requerimiento</td>
		</tr>
		<tr>
			<td><strong>0 ptos</strong></td>
			<td><strong>Desconocido</strong>. La empresa/sistema no proporciona
				información al respecto</td>
		</tr>
	</table>
</div>

<form action="guardarEvaluacion" id="formEvaluacion" name="formEvaluacion" method="post">
	<ul class="nav nav-tabs">
	<?php
	foreach ( $secciones as $seccion ) {
	?>
  		<li class="<?php echo ( ($seccion->id_seccion == 1) ? 'active' : '' ); ?>">
			<a data-toggle="tab" href="#s<?php echo $seccion->id_seccion; ?>">Sección <?php echo $seccion->id_seccion; ?></a>
		</li>
	<?php
	}
	?>
	</ul>
	<!-- Fin de tabs -->

	<!-- Contenido de cada tab -->
	<div class="tab-content">
	<?php
	foreach ( $secciones as $seccion ) {
	?>
  		<div id="s<?php echo $seccion->id_seccion; ?>"
			class="tab-pane fade <?php echo ( ($seccion->id_seccion == 1) ? 'in active' : '' ); ?>">
			<h4 class="panel-title"><?php echo utf8_encode($seccion->nombre); ?></h4>
			
      	<?php
		if ($seccion->sub == 1) {
		?>
           <div class="panel-group"
				id="accordion<?php echo $seccion->id_seccion; ?>" role="tablist"
				aria-multiselectable="true">
     	<?php
		foreach ( $subsecciones as $subseccion ) {
			if ($subseccion->seccion == $seccion->id_seccion) {
		?>
  				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="heading<?php echo $subseccion->id_subseccion; ?>">
						<h4 class="panel-title">
							<a data-toggle="collapse"
								data-parent="#accordion<?php echo $seccion->id_seccion; ?>"
								href="#collapse<?php echo $subseccion->id_subseccion; ?>"
								<?php echo ( ($subseccion->id_subseccion != 1 && $subseccion->id_subseccion != 19) ? 'data-toggle="collapse"' : '' ); ?>
								aria-expanded="<?php echo ( ($subseccion->id_subseccion == 1 || $subseccion->id_subseccion == 19) ? 'true' : 'false' ); ?>"
								aria-controls="collapse<?php echo $subseccion->id_subseccion; ?>">
					          <?php echo utf8_encode($subseccion->nombre); ?>
					        	<img src="../images/flecha.png" class="<?php echo ( ($subseccion->id_subseccion == 1 || $subseccion->id_subseccion == 19) ? 'expandido' : 'colapsado' ); ?>" />
					        </a>
						</h4>
					</div>
					<div id="collapse<?php echo $subseccion->id_subseccion; ?>"
						class="panel-collapse collapse <?php echo ( ($subseccion->id_subseccion == 1 || $subseccion->id_subseccion == 19) ? 'in' : '' ); ?>"
						role="tabpanel"
						aria-labelledby="heading<?php echo $subseccion->id_subseccion; ?>">
						<table class="table table-striped table-condensed">
							<tr>
								<th>&nbsp;</th>
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
					foreach ( $preguntas as $pregunta ) {
						if ($pregunta->subseccion == $subseccion->id_subseccion) {
					?>
          					<tr>
								<td <?php if ( $pregunta->titulo ) {echo 'class="pregunta-titulo"';} ?>>
									<?php echo utf8_encode($pregunta->pregunta); ?>
								</td>
            			<?php
						if (! $pregunta->titulo) {
							foreach ( $proveedores as $proveedor ) {
								$resp = (isset($respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor])) ? $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta'] : '';
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
						?>
            					<td>
            						<select
									id="cmb_<?php echo $pregunta->id_pregunta."_".$proveedor->id_proveedor; ?>"
									name="cmb_<?php echo $pregunta->id_pregunta."_".$proveedor->id_proveedor; ?>"
									class="form-control">
										<option value="">Selecciona</option>
										<option value="S"
											<?php if(isset($respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta']) && $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta'] == "S") {echo 'selected="selected"';}?>>Sí</option>
										<option value="P"
											<?php if(isset($respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta']) && $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta'] == "P") {echo 'selected="selected"';}?>>Parcialmente</option>
										<option value="N"
											<?php if(isset($respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta']) && $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta'] == "N") {echo 'selected="selected"';}?>>No</option>
										<option value="D"
											<?php if(isset($respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta']) && $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta'] == "D") {echo 'selected="selected"';}?>>Desconocido</option>
									</select>
									<input type="hidden" 
										id="hdnc_<?php echo $pregunta->id_pregunta."_".$proveedor->id_proveedor; ?>" 
										name="hdnc_<?php echo $pregunta->id_pregunta."_".$proveedor->id_proveedor; ?>" 
										value="<?php echo ( (isset($respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor])) ? $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['id_respuesta'] : ""); ?>" />
								</td>
            			<?php
							}
						} else {
						?>
            					<td colspan="<?php echo sizeof($proveedores); ?>">&nbsp;</td>
            			<?php
						}
						?>
          					</tr>
          		<?php
					}
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

						<div class="panel-body">
							<label>Observaciones:</label>
							<textarea
								id="txt_<?php echo $seccion->id_seccion."_".$subseccion->id_subseccion; ?>"
								name="txt_<?php echo $seccion->id_seccion."_".$subseccion->id_subseccion; ?>"
								rows="3" class="form-control"><?php echo ( (isset($observaciones[$seccion->id_seccion."_".$subseccion->id_subseccion])) ? utf8_encode($observaciones[$seccion->id_seccion."_".$subseccion->id_subseccion]['respuesta']) : ""); ?></textarea>
							<input type="hidden" 
								id="hdnt_<?php echo $seccion->id_seccion."_".$subseccion->id_subseccion; ?>"
								name="hdnt_<?php echo $seccion->id_seccion."_".$subseccion->id_subseccion; ?>"
								value="<?php echo ( (isset($observaciones[$seccion->id_seccion."_".$subseccion->id_subseccion])) ? $observaciones[$seccion->id_seccion."_".$subseccion->id_subseccion]['id_respuesta'] : ""); ?>" />
						</div>
					</div>
				</div>
      	<?php
			}
		}
		?>
      </div>
    <?php
	} else {
	?>      
		<table class="table table-striped table-condensed">
			<tr>
				<th>&nbsp;</th>
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
		foreach ( $preguntas as $pregunta ) {
			if ($pregunta->seccion == $seccion->id_seccion) {
		?>
          	<tr>
				<td><?php echo utf8_encode($pregunta->pregunta); ?></td>
            	<?php
				foreach ( $proveedores as $proveedor ) {
					$resp = (isset($respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor])) ? $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta'] : '';
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
				?>
            	<td>
            		<select
						id="cmb_<?php echo $pregunta->id_pregunta."_".$proveedor->id_proveedor; ?>"
						name="cmb_<?php echo $pregunta->id_pregunta."_".$proveedor->id_proveedor; ?>"
						class="form-control">
							<option value="">Selecciona</option>
							<option value="S"
								<?php if(isset($respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta']) && $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta'] == "S") {echo 'selected="selected"';}?>>Sí</option>
							<option value="P"
								<?php if(isset($respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta']) && $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta'] == "P") {echo 'selected="selected"';}?>>Parcialmente</option>
							<option value="N"
								<?php if(isset($respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta']) && $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta'] == "N") {echo 'selected="selected"';}?>>No</option>
							<option value="D"
								<?php if(isset($respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta']) && $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['respuesta'] == "D") {echo 'selected="selected"';}?>>Desconocido</option>
					</select>
					<input type="hidden" 
						id="hdnc_<?php echo $pregunta->id_pregunta."_".$proveedor->id_proveedor; ?>" 
						name="hdnc_<?php echo $pregunta->id_pregunta."_".$proveedor->id_proveedor; ?>" 
						value="<?php echo ( (isset($respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor])) ? $respuesta[$pregunta->id_pregunta."_".$proveedor->id_proveedor]['id_respuesta'] : ""); ?>" />
				</td>
            	<?php
				}
				?>
          	</tr>
	    <?php
			}
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

		<label>Observaciones:</label>
		<textarea id="txt_<?php echo $seccion->id_seccion."_0"; ?>"
			name="txt_<?php echo $seccion->id_seccion."_0"; ?>" rows="3"
			class="form-control"><?php echo ( (isset($observaciones[$seccion->id_seccion."_0"])) ? utf8_encode($observaciones[$seccion->id_seccion."_0"]['respuesta']) : ""); ?></textarea>
		<input type="hidden" 
			id="hdnt_<?php echo $seccion->id_seccion."_0"; ?>" 
			name="hdnt_<?php echo $seccion->id_seccion."_0"; ?>" 
			value="<?php echo ( (isset($observaciones[$seccion->id_seccion."_0"])) ? $observaciones[$seccion->id_seccion."_0"]['id_respuesta'] : ""); ?>" />
    <?php
	}
	?>
  	</div>
<?php
}
?>
	</div>
	<!-- Fin de contenido de tabs -->

	<div class="botones">
	<?php
	if ( !$evaluacion->fecha_finalizacion ) {
	?>
		<input type="button" id="btnGuardar" value="Guardar cambios" class="btn btn-primary" /> 
		<input type="button" id="btnFinalizar" value="Finalizar evaluación" class="btn btn-primary" />
	<?php
	}
	?>
	</div>
</form>

<!-- Ventana modal para notificaciones -->
<div class="modal fade" id="notificaciones" tabindex="-1" role="dialog"
	aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="myModalLabel">Revisa el formulario</h4>
			</div>
			<div class="modal-body">Falta completar algún campo del formulario.
				Revísalo cuidadosamente.</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>
			</div>
		</div>
	</div>
</div>