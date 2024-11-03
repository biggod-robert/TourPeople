$(document).ready(function() {
    // Quitar el mensaje de error mientras el usuario escribe en los campos
    $('#correo', '#code').on('input', function() {
        $(this).get(0).setCustomValidity("");
    });

    // Envío del correo al controlador para ser validado y enviar un código de validación a ese correo
    $('.btn-sendCode').click(function(e) {
        e.preventDefault();
        const correo = $('#correo').val().trim();

        // Expresión regular para validar el formato del correo electrónico
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (correo === "") {
            $('#correo').get(0).setCustomValidity("El campo de correo no puede estar vacío.");
            $('#correo')[0].reportValidity();
            return;
        } else if (!emailRegex.test(correo)) {
            $('#correo').get(0).setCustomValidity("Por favor, ingrese un correo electrónico válido.");
            $('#correo')[0].reportValidity();
            return;
        }

        // Envío AJAX al controlador para validación y envío del código al correo 
        $.ajax({
            url: '../controller/resetPass.php',
            type: 'POST',
            data: { correo: correo },
            beforeSend: function() {
                $("#loader").removeClass("esconder");
                $("body").addClass("hidenn");
            },
            success: function(resp) {
                var data = JSON.parse(resp);
                $("#loader").addClass("esconder");
                $("body").removeClass("hidenn");
                if (data.codigo == 1) {
                    Swal.fire({
                        icon: "success",
                        title: "ÉXITO",
                        html: data.mensaje,
                    });
                    // Deshabilitar el botón y comenzar la cuenta regresiva
                    $('.btn-sendCode').prop('disabled', true);
                    startCountdown(60, $('.btn-sendCode'));
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Ooops.",
                        html: data.mensaje,
                    });
                }
            }
        });
    });

    // Función para iniciar la cuenta regresiva
    function startCountdown(duration, button) {
        let timer = duration,
            seconds;
        const countdownInterval = setInterval(function() {
            seconds = parseInt(timer % 60, 10);

            // Añadir ceros delante si es necesario
            seconds = seconds < 10 ? "0" + seconds : seconds;

            // Actualizar el texto del botón
            button.text('Reenviar código en ' + seconds + 's');

            // Si el tiempo se ha agotado
            if (--timer < 0) {
                clearInterval(countdownInterval);
                button.prop('disabled', false); // Habilitar el botón
                button.text('Enviar código'); // Resetear el texto del botón
            }
        }, 1000);
    }

    // Validación del correo y el código de verificación digitado por el usuario
    $('.btn-send').click(function(e) {
        e.preventDefault();
        const correo = $('#correo').val().trim();
        const code = $('#code').val().trim(); // Asegúrate de que el ID coincida con el del input del código

        // Expresión regular para validar el formato del correo electrónico
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        // Expresión regular para validar que el código contenga solo letras (mayúsculas y minúsculas) y números
        const codeRegex = /^[a-zA-Z0-9]+$/;

        if (correo === "") {
            $('#correo').get(0).setCustomValidity("El campo de correo no puede estar vacío.");
            $('#correo')[0].reportValidity();
            return;
        } else if (!emailRegex.test(correo)) {
            $('#correo').get(0).setCustomValidity("Por favor, ingrese un correo electrónico válido.");
            $('#correo')[0].reportValidity();
            return;
        }

        if (code === "") {
            $('#code').get(0).setCustomValidity("El campo de código no puede estar vacío.");
            $('#code')[0].reportValidity();
            return;
        } else if (!codeRegex.test(code)) {
            $('#code').get(0).setCustomValidity("El código solo puede contener letras (mayúsculas y minúsculas) y números.");
            $('#code')[0].reportValidity();
            return;
        }

        // Envío AJAX al controlador para validación del codigo digitado por el usuario
        var formReset = new FormData(document.getElementById("formReset"));
        $.ajax({
            url: '../controller/verificarCodigo.php',
            type: 'POST',
            data: formReset,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $("#loader").removeClass("esconder");
                $("body").addClass("hidenn");
            },
            success: function(resp) {
                console.log(resp);
                var data = JSON.parse(resp);
                if (data.codigo == 1) {
                    window.location.href = "../nueva-clave/";
                } else {
                    $("#loader").addClass("esconder");
                    $("body").removeClass("hidenn");
                    Swal.fire({
                        icon: "error",
                        title: "Ooops.",
                        html: data.mensaje,
                    });
                }
            }
        });

    });
});