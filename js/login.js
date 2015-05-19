function centrarPanel() {
	var alto = $("body > .container").height();
	$(".loginContainer").height(alto);
}

$(function() {
	centrarPanel();
});