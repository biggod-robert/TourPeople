$(document).ready(function() {
    // Función para limpiar el mensaje de error cuando el usuario empieza a escribir
    function clearErrorOnInput(input) {
        $(input).on('input', function() {
            this.setCustomValidity(''); // Limpiar el mensaje de error
        });
    }

    // Asignar el evento de limpiar errores a los campos
    clearErrorOnInput("#host");
    clearErrorOnInput("#user");
    clearErrorOnInput("#pass");
    clearErrorOnInput("#nameDB");

    $("#installForm").submit(function(event) { // Escucha del evento submit en el form
        event.preventDefault() // prevenir el evento por defecto (envio del formulario)

        let isValid = true;

        // Validar el campo Host (solo letras, números, guiones y puntos)
        const host = $("#host")[0];
        const hostRegex = /^[a-zA-Z0-9.-]+$/;
        if (!host.value.match(hostRegex)) {
            host.setCustomValidity("El host solo puede contener letras, números, guiones y puntos.");
            host.reportValidity();
            isValid = false;
        }

        // Validar el campo Usuario (solo letras y números, mínimo 3 caracteres)
        const user = $("#user")[0];
        const userRegex = /^[a-zA-Z0-9]{3,}$/;
        if (!user.value.match(userRegex)) {
            user.setCustomValidity("El usuario debe tener al menos 3 caracteres y solo puede contener letras y números.");
            user.reportValidity();
            isValid = false;
        }


        // Validar el campo Nombre de la base de datos (solo letras, números, y guiones bajos)
        const nameDB = $("#nameDB")[0];
        const nameDBRegex = /^[a-zA-Z0-9_]+$/;
        if (!nameDB.value.match(nameDBRegex)) {
            nameDB.setCustomValidity("El nombre de la base de datos solo puede contener letras, números, y guiones bajos.");
            nameDB.reportValidity();
            isValid = false;
        }

        // Si todos los campos son validos se realiza el envio del formulario
        if (isValid) {
            var formInstaller = new FormData(document.getElementById("installForm"));
            // envio por ajax 
            $.ajax({
                type: "POST",
                url: "../controller/instaladorDB.php",
                data: formInstaller,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    //se activa loader mientras se ejecuta la solicitud
                    $("#loader").removeClass("esconder");
                    $("body").addClass("hidenn");
                },
                success: function(resp) {
                    // se desactiva el loader
                    $("#loader").addClass("esconder");
                    $("body").removeClass("hidenn");
                    // Muestra el SweetAlert con el mensaje de respuesta
                    Swal.fire({
                        icon: 'success', // Cambia el icono según la respuesta (puedes usar 'success', 'error', 'warning', etc.)
                        title: 'Resultado',
                        html: resp, // Muestra el mensaje de respuesta
                        showCloseButton: false, // No mostrar botón de cerrar
                        allowOutsideClick: false, // No permitir cerrar al hacer clic fuera
                        allowEscapeKey: false, // No permitir cerrar con la tecla ESC
                        confirmButtonText: 'Aceptar', // Texto del botón de aceptar
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirigir a la página ../inicio/ al hacer clic en Aceptar
                            window.location.href = "../inicio/";
                        }
                    });
                },


                error: function(jqXHR, textStatus, errorThrown) {
                    $("#loader").addClass("esconder");
                    $("body").removeClass("hidenn");

                    // Construir el mensaje de error
                    let errorMessage = "No se pudo conectar con el servidor. Por favor, inténtalo de nuevo más tarde.";

                    // agregar más detalles al mensaje si es necesario
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        errorMessage = jqXHR.responseJSON.message; // Mensaje de error del servidor
                    } else if (textStatus === 'timeout') {
                        errorMessage = "La solicitud ha caducado. Por favor, inténtalo de nuevo.";
                    } else if (textStatus === 'abort') {
                        errorMessage = "La solicitud fue abortada. Inténtalo nuevamente.";
                    } else if (errorThrown) {
                        errorMessage = errorThrown; // Mensaje de error lanzado
                    }

                    // Mostrar el SweetAlert con el mensaje de error
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: errorMessage,
                        showCloseButton: true, // Mostrar solo el botón de cerrar
                        showConfirmButton: false, // No mostrar el botón de aceptar
                        allowOutsideClick: false, // No permitir cerrar al hacer clic fuera
                        allowEscapeKey: false, // No permitir cerrar con la tecla ESC
                    });
                },

            });
        }
    });
});