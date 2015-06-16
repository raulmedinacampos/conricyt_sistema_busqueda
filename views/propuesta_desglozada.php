<script type="text/javascript">
$(function() {
	function calcularTotal(id) {
		var suma = 0;
		
		$(".c"+id).each(function() {
			suma += parseInt($(this).val()) || 0;
		});

		$("#txt_total_"+id).val(suma);
	}

	$(".c0").change(function() {
		calcularTotal("0");
	});

	$("#txt_total_0").focus(function() {
		calcularTotal("0");
	});

	$(".c1").change(function() {
		calcularTotal("1");
	});

	$("#txt_total_1").focus(function() {
		calcularTotal("1");
	});

	$(".c2").change(function() {
		calcularTotal("2");
	});

	$("#txt_total_2").focus(function() {
		calcularTotal("2");
	});

	$(".c3").change(function() {
		calcularTotal("3");
	});

	$("#txt_total_3").focus(function() {
		calcularTotal("3");
	});

	$("#formEvEco").validate({
		errorElement: "span"
	});
});
</script>
<?php
$anios = array('2015-2016', '2016-2017', '2017-2018', '2018-2019');
?>

<table class="table table-condensed table-striped">
  <tr>
    <th>Rubro</th>
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
    <td <?php if ( $seccion->sub ) {echo 'colspan="'.(sizeof($anios)+1).'"';} ?>>
    	<ol type="a" start="<?php echo $seccion->id_seccion; ?>">
    		<li><?php echo utf8_encode($seccion->nombre); ?></li>
    	</ol>
    </td>
    <?php
    if ( !$seccion->sub ) {
	    $i = 0;
	    foreach ( $anios as $anio ) {
	    ?>
	    <td>
	    	<div class="input-group">
		    	<div class="input-group-addon">$</div>
		    	<input type="text" id="txt_<?php echo $seccion->id_seccion."_0_".$i; ?>" name="txt_<?php echo $seccion->id_seccion."_0_".$i; ?>" class="form-control c<?php echo $i; ?>" />
	    	</div>
	    </td>
	    <?php
	    	$i++;
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
    $i = 0;
    foreach ( $anios as $anio ) {
    ?>
    <td>
    	<div class="input-group">
	    	<div class="input-group-addon">$</div>
	    	<input type="text" id="txt_<?php echo $seccion->id_seccion."_".$subseccion->id_subseccion."_".$i; ?>" name="txt_<?php echo $seccion->id_seccion."_".$subseccion->id_subseccion."_".$i; ?>" class="form-control c<?php echo $i; ?>" />
    	</div>
    </td>
    <?php
    	$i++;
    }
    ?>
  </tr>
  <?php
  			}
  		}
  	}
  }
  ?>
  <tr>
  	<td><strong>Total:</strong></td>
  	<?php
  	$i = 0;
    foreach ( $anios as $anio ) {
    ?>
  	<td id="total_<?php echo $anio; ?>">
  		<div class="input-group">
	    	<div class="input-group-addon">$</div>
	    	<input type="text" id="txt_total_<?php echo $i; ?>" name="txt_total_<?php echo $i; ?>" class="form-control" />
    	</div>
  	</td>
  	<?php
  		$i++;
    }
    ?>
  </tr>
</table>