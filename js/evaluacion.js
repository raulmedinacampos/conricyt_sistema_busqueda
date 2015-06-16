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
	
	$("#formEvaluacion select").each(function() {
		$(this).rules('add', {
			required: true,
			messages: {
				required: ""
			}
		});
	});
	
	$("#btnGuardar").click(function() {
		$("#notificaciones .modal-title").html("Guardando datos");
		$("#notificaciones .modal-body").html('<img style="display:block; margin:auto;" src="../images/loading.gif" />');
		$("#notificaciones .modal-footer button").css("display", "none");
		$("#notificaciones").modal('show');
		$.post(
				'guardarEvaluacion',
				$("#formEvaluacion").serialize(),
				function(data) {
					$("#notificaciones .modal-title").html("Datos guardados");
					$("#notificaciones .modal-body").html("La información de tu evaluación ha sido guardada.");
					$("#notificaciones .modal-footer button").css("display", "inline");
					setTimeout(location.reload(), 2000);
				}
		);
	})
	
	$("#btnFinalizar").click(function() {
		if ( $("#formEvaluacion").valid() ) {
			$("#notificaciones .modal-title").html("Finalizando evaluación");
			$("#notificaciones .modal-body").html('<img style="display:block; margin:auto;" src="../images/loading.gif" />');
			$("#notificaciones .modal-footer button").css("display", "none");
			$("#notificaciones").modal('show');
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