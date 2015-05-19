function inicializar() {
	$('.panel-group').on('hide.bs.collapse', posicionarFlecha);
	$('.panel-group').on('show.bs.collapse', posicionarFlecha);
}

function posicionarFlecha(e) {
    $(e.target)
        .prev('.panel-heading')
        .find("img")
        .toggleClass('colapsado expandido');
}

function validarFormulario() {
	$("#formEvaluacion").validate({
		errorElement: "span",
		ignore: []
	});
	
	$("#formEvaluacion textarea").each(function() {
		$(this).rules('add', {
			required: true,
			messages: {
				required: ""
			}
		});
	});
	
	$("#formEvaluacion select").each(function() {
		$(this).rules('add', {
			required: true,
			messages: {
				required: ""
			}
		});
	});
	
	$("#btnGuardar").click(function() {
		$.post(
				'guardarEvaluacion',
				$("#formEvaluacion").serialize(),
				function(data) {
					alert(data);
				}
		);
	})
	
	$("#btnFinalizar").click(function() {
		if ( $("#formEvaluacion").valid() ) {
			
		} else {
			$("#notificaciones").modal('show');
		}
	});
}

$(function() {
	inicializar();
	validarFormulario();
});