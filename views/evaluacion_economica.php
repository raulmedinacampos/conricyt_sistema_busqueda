<link rel="stylesheet" href="../css/reporte.css" type="text/css" />
<script type="text/javascript">
$(function() {
	$("#btn2").css("display", "none");
	
	$("#empresa").change(function() {
		$("#nombre_empresa").html("de "+$(this).children("option:selected").text());
	});
	
	$("#btn1").click(function() {
		if($("#formEvEco").valid()) {
			$("#empresa").prop("disabled", true);
			$("#desgloce").html("");
			$("#preguntas").slideUp();
			$("#btn2").css("display", "inline");
			var desg = $("#cmb1").children("option:selected").val();
			
			if ( desg == 's' || desg == 'p' ) {
				$("#desgloce").load('propuesta-desglozada', function() {
					$("#formEvEco input").each(function() {
						$(this).rules('add', {
							number: true,
							messages: {
								number: ""
							}
						});
					});
				});
			}
	
			if ( desg == 'n' ) {
				var cotizacion = '<table class="table"><tr>';
				cotizacion += '<th>Concepto</th>';
				cotizacion += '<th>2015-2016</th>';
				cotizacion += '<th>2016-2017</th>';
				cotizacion += '<th>2017-2018</th>';
				cotizacion += '<th>2018-2019</th>';
				cotizacion += '</tr><tr>';
				cotizacion += '<td>Escribe la cantidad por año</td>';
				cotizacion += '<td><div class="input-group"><div class="input-group-addon">$</div>';
				cotizacion += '<input id="tt_0" name="tt_0" class="form-control" /></div></td>';
				cotizacion += '<td><div class="input-group"><div class="input-group-addon">$</div>';
				cotizacion += '<input id="tt_1" name="tt_1" class="form-control" /></div></td>';
				cotizacion += '<td><div class="input-group"><div class="input-group-addon">$</div>';
				cotizacion += '<input id="tt_2" name="tt_2" class="form-control" /></div></td>';
				cotizacion += '<td><div class="input-group"><div class="input-group-addon">$</div>';
				cotizacion += '<input id="tt_3" name="tt_3" class="form-control" /></div></td>';
				cotizacion += '</tr></table>';
				$("#desgloce").append(cotizacion);

				$("#formEvEco input").each(function() {
					$(this).rules('add', {
						number: true,
						messages: {
							number: ""
						}
					});
				});
			}

			$(".botones").children("#btn1").css("display", "none");
		}
	});

	$("#btn2").click(function() {
		if($("#formEvEco").valid()) {
			$("#empresa").prop("disabled", false);
			$.post('guardarEvEco',
				$("#formEvEco").serialize(),
				function(data) {
					window.location = "evaluacion-economica";
				}
			);
		}
	});

	$("#btnCancelar").click(function() {
		$("#empresa").prop("disabled", false);
		$("#empresa").val("");
		$("#nombre_empresa").html("");
		$("#desgloce").html("");
		$("#preguntas").fadeIn();
		$("#btn1").css("display", "inline");
		$("#btn2").css("display", "none");
	});

	$("#formEvEco").validate({
		errorElement: "span",
		ignore: [],
		rules: {
			empresa: {
				required: true
			},
			cmb1: {
				required: true
			},
			cmb2: {
				required: true
			},
			cmb3: {
				required: true
			},
			cmb4: {
				required: true
			}
		},
		messages: {
			empresa: "",
			cmb1: "",
			cmb2: "",
			cmb3: "",
			cmb4: ""
		}
	});

	$("#formEvEco").submit(function(e) {
		e.preventDefault();
	});
});
</script>

<div class="titulo">
	<h3 class="titulo col-sm-10 col-sm-offset-1">Evaluación de Propuesta Económica</h3>
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

<h3>Evaluación económica <span id="nombre_empresa"></span></h3>

<form id="formEvEco" name="formEvEco" method="post" action="">
<select id="empresa" name="empresa" class="form-control">
	<option value="">Seleccione</option>
	<?php
	foreach ( $proveedores as $proveedor ) {
	?>
	<option value="<?php echo $proveedor->id_proveedor; ?>"><?php echo utf8_encode($proveedor->abreviatura); ?></option>
	<?php
	}
	?>
</select>

<div id="preguntas" >
<table class="table">
  <tr>
    <th>Concepto</th>
    <th>Calificación</th>
  </tr>
  <tr>
    <td>¿La empresa incluyó el costo de cada concepto y servicio?</td>
    <td>
    	<select id="cmb1" name="cmb1" class="form-control">
    		<option value="">Selecciona</option>
    		<option value="s">Sí cumple</option>
    		<option value="p">Cumple parcialmente</option>
    		<option value="n">No cumple</option>
    	</select>
    </td>
  </tr>
  <tr>
    <td>¿La empresa incluyó en la propuesta los montos por pago de impuestos?</td>
    <td>
    	<select id="cmb2" name="cmb2" class="form-control">
    		<option value="">Selecciona</option>
    		<option value="s">Sí</option>
    		<option value="n">No</option>
    	</select>
    </td>
  </tr>
  <tr>
    <td>¿La empresa incluyó en su propuesta el porcentaje de incremento anual para los años de servicio?</td>
    <td>
    	<select id="cmb3" name="cmb3" class="form-control">
    		<option value="">Selecciona</option>
    		<option value="s">Sí</option>
    		<option value="n">No</option>
    	</select>
    </td>
  </tr>
  <tr>
    <td>¿La propuesta económica se presentó en Moneda Nacional?</td>
    <td>
    	<select id="cmb4" name="cmb4" class="form-control">
    		<option value="">Selecciona</option>
    		<option value="s">Sí</option>
    		<option value="n">No</option>
    	</select>
    </td>
  </tr>
</table>
</div>

<div id="desgloce"></div>

<label>Comentarios:</label>
<textarea id="comentarios" name="comentarios" rows="5" class="form-control"></textarea>
<div class="botones">
	<button id="btn1" class="btn btn-primary">Siguiente</button>
	<button id="btn2" class="btn btn-primary">Guardar evaluación</button>
	<button id="btnCancelar" class="btn btn-primary">Cancelar</button>
</div>
</form>