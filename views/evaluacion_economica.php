<link rel="stylesheet" href="../css/reporte.css" type="text/css" />
<script type="text/javascript">
$(function() {
	$("input").prop("disabled", true);

	$("select").change(function() {
		var elemento = $(this);
		if ( elemento.children("option:selected").val() == 's' || elemento.children("option:selected").val() == 'p' ) {
			elemento.parents("tr").find("input").prop("disabled", false);
		} else {
			elemento.parents("tr").find("input").prop("disabled", true);
		}
	});
});
</script>

<h3>Evaluación económica</h3>

<select class="form-control">
	<option value="">Seleccione</option>
	<?php
	foreach ( $proveedores as $proveedor ) {
	?>
	<option value="<?php echo $proveedor->id_proveedor; ?>"><?php echo $proveedor->nombre; ?></option>
	<?php
	}
	?>
</select>

<table>
  <tr>
    <th>Concepto</th>
    <th>Calificación</th>
  </tr>
  <tr>
    <td>¿La empresa incluyó el costo de cada concepto y servicio y los costos por pago de impuestos, de conformidad con lo descrito en los incisos a, c. y d, incluidos en el Formato de Presentación de Propuestas Técnicas, de Servicio y Económicas, por cada año de servicio contratado)?</td>
  </tr>
  <tr>
    <td>¿La propuesta económica se presentó en Moneda Nacional?</td>
  </tr>
  <tr>
    <td>¿La empresa muestra los montos por pago de impuestos?</td>
  </tr>
  <tr>
    <td>¿La empresa incluyó el monto de los incrementos anuales en la cotización?</td>
  </tr>
</table>


<?php
$anios = array('2015', '2016', '2017', '2018');
?>

<table class="table table-condensed table-striped">
  <tr>
    <th>Rubro</th>
    <th>¿Cumple?</th>
    <?php
    foreach ( $anios as $anio ) {
    ?>
    <th><?php echo $anio; ?></th>
    <?php
    }
    ?>
  </tr>
  <?php
  foreach ( $secciones as $seccion ) {
  ?>
  <tr>
    <td>
    	<ol type="a" start="<?php echo $seccion->id_seccion; ?>">
    		<li><?php echo utf8_encode($seccion->nombre); ?></li>
    	</ol>
    </td>
    <td>
    	<select class="form-control">
    		<option value="">Selecciona</option>
    		<option value="s">Sí</option>
    		<option value="p">Parcialmente</option>
    		<option value="n">No</option>
    	</select>
    </td>
    <?php
    foreach ( $anios as $anio ) {
    ?>
    <td><input type="text" class="form-control" /></td>
    <?php
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
    <td>
    	<select class="form-control">
    		<option>Selecciona</option>
    		<option>Sí</option>
    		<option>Parcialmente</option>
    		<option>No</option>
    	</select>
    </td>
    <?php
    foreach ( $anios as $anio ) {
    ?>
    <td><input type="text" class="form-control" /></td>
    <?php
    }
    ?>
  </tr>
  <?php
  			}
  		}
  	}
  }
  ?>
  <tr class="total">
  	<td colspan="2">Total:</td>
  	<?php
    foreach ( $anios as $anio ) {
    ?>
  	<td id="total_<?php echo $anio; ?>"></td>
  	<?php
    }
    ?>
  </tr>
</table>

<label>Observaciones:</label>
<textarea rows="5" class="form-control"></textarea>
<div class="botones">
	<button class="btn btn-primary">Guardar evaluación</button>
</div>