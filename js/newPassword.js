$(document).ready(function() {
    // Quitar el mensaje de error mientras el usuario escribe en los campos de contraseña
    $('#newPassword, #confirmarnewPassword').on('input', function() {
        $(this).get(0).setCustomValidity("");
    });

    // Envío de la nueva newPassword al controlador para ser validada
    $('.btn-send').click(function(e) {
        e.preventDefault();

        const newPassword = $('#newPassword').val().trim();
        const confirmarnewPassword = $('#confirmarnewPassword').val().trim();

        // Validar longitud de la newPassword
        if (newPassword.length < 6) {
            $('#newPassword').get(0).setCustomValidity("La newPassword debe tener al menos 6 caracteres.");
            $('#newPassword')[0].reportValidity();
            return;
        }

        // Validar que las newPasswords coincidan
        if (newPassword !== confirmarnewPassword) {
            $('#confirmarnewPassword').get(0).setCustomValidity("Las newPasswords no coinciden.");
            $('#confirmarnewPassword')[0].reportValidity();
            return;
        }

        // Si todas las validaciones pasan, puedes enviar la newPassword a tu controlador
        $.ajax({
            url: '../controller/newPassword.php',
            type: 'POST',
            data: { newPassword: newPassword },
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
});