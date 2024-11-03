$(document).ready(function() {
    $('.ingresar').click(function(e) {
        e.preventDefault();
        // redireciono al controlador general
        window.location.href = "../dashboard/";
    })
});