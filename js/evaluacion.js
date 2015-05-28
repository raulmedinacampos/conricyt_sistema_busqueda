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
					$("#notificaciones .modal-title").html("Datos guardados");
					$("#notificaciones .modal-body").html("La información de tu evaluación ha sido guardada.");
					$("#notificaciones").modal('show');
				}
		);
	})
	
	$("#btnFinalizar").click(function() {
		if ( $("#formEvaluacion").valid() ) {
			$.post(
					'finalizarEvaluacion',
					$("#formEvaluacion").serialize(),
					function(data) {
						location.reload();
					}
			);
		} else {
			$("#notificaciones .modal-title").html("Revisa el formulario");
			$("#notificaciones .modal-body").html("Falta completar algún campo del formulario. Revísalo cuidadosamente.");
			$("#notificaciones").modal('show');
		}
	});
}

$(function() {
	inicializar();
	validarFormulario();
});