$(document).ready(function() {

    $('.pay').click(function(e) {
        e.preventDefault();

        $.ajax({
            url: '../controller/buypro.php',
            type: 'POST',
            success: function(resp) {
                if (resp == 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Resultado',
                        text: 'Compra exitosa, ahora eres administrador', // Muestra el mensaje de respuesta
                        showCloseButton: false, // No mostrar botón de cerrar
                        allowOutsideClick: false, // No permitir cerrar al hacer clic fuera
                        allowEscapeKey: false, // No permitir cerrar con la tecla ESC
                        confirmButtonText: 'Aceptar', // Texto del botón de aceptar
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirigir a la página admin al hacer clic en Aceptar
                            window.location.href = "../dashboard/";
                        }
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Ooops.",
                        text: 'error en la compra, intentalo mas tarde.',
                    });
                }
            }
        })
    })
})