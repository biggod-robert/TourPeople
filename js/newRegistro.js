$(document).ready(function() {
    // Limpiar mensajes de error al escribir en los campos
    $('#documento, #nombre_p, #apellido_p, #correo, #pass, #edad, #f_nacimiento, #telefono, #imagen').on('input', function() {
        this.setCustomValidity(""); // Limpia el mensaje de error al escribir
    });

    // Envío del formulario para ser validado
    $('#register-login').on('submit', function(e) {
        e.preventDefault();

        // Obtener valores de los campos
        const documento = $('#documento').val().trim();
        const nombre = $('#nombre_p').val().trim();
        const apellido = $('#apellido_p').val().trim();
        const correo = $('#correo').val().trim();
        const clave = $('#pass').val().trim();
        const edad = $('#edad').val().trim();
        const fNacimiento = $('#f_nacimiento').val().trim();
        const telefono = $('#telefono').val().trim();
        const imagen = $('#imagen')[0].files[0]; // Obtener el archivo de imagen

        // Expresiones regulares para validaciones
        const regexDocumento = /^\d{1,15}$/; // Solo números, hasta 15 dígitos
        const regexNombreApellido = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/; // Solo letras y espacios
        const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Formato de correo
        const regexClave = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d\W]{6,}$/; // Mínimo 6 caracteres, al menos una letra y un número
        const regexEdad = /^[1-9][0-9]?$/; // Edad entre 1 y 99
        const regexTelefono = /^\+?[0-9]{10,15}$/; // Teléfono en formato internacional (opcional + y entre 10 a 15 dígitos)

        // Validaciones
        if (!regexDocumento.test(documento)) {
            $('#documento').get(0).setCustomValidity("El documento debe ser un número entre 1 y 15 dígitos.");
            $('#documento')[0].reportValidity();
            return;
        }

        if (nombre === "") {
            $('#nombre_p').get(0).setCustomValidity("El nombre no puede estar vacío.");
            $('#nombre_p')[0].reportValidity();
            return;
        } else if (!regexNombreApellido.test(nombre)) {
            $('#nombre_p').get(0).setCustomValidity("El nombre solo debe contener letras y espacios.");
            $('#nombre_p')[0].reportValidity();
            return;
        }

        if (apellido === "") {
            $('#apellido_p').get(0).setCustomValidity("El apellido no puede estar vacío.");
            $('#apellido_p')[0].reportValidity();
            return;
        } else if (!regexNombreApellido.test(apellido)) {
            $('#apellido_p').get(0).setCustomValidity("El apellido solo debe contener letras y espacios.");
            $('#apellido_p')[0].reportValidity();
            return;
        }

        if (!regexCorreo.test(correo)) {
            $('#correo').get(0).setCustomValidity("Ingrese un correo válido.");
            $('#correo')[0].reportValidity();
            return;
        }

        if (!regexClave.test(clave)) {
            $('#pass').get(0).setCustomValidity("La contraseña debe tener al menos 6 caracteres, incluyendo al menos una letra y un número.");
            $('#pass')[0].reportValidity();
            return;
        }

        if (!regexEdad.test(edad)) {
            $('#edad').get(0).setCustomValidity("La edad debe ser un número entre 1 y 99.");
            $('#edad')[0].reportValidity();
            return;
        }

        if (!regexTelefono.test(telefono)) {
            $('#telefono').get(0).setCustomValidity("Ingrese un número de teléfono válido (10-15 dígitos).");
            $('#telefono')[0].reportValidity();
            return;
        }

        // Validación de la imagen
        if (imagen) {
            const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i; // Extensiones permitidas
            const maxSize = 2 * 1024 * 1024; // 2 MB en bytes

            if (!allowedExtensions.exec(imagen.name)) {
                $('#imagen').get(0).setCustomValidity("Solo se permiten imágenes con las extensiones .jpg, .jpeg, .png o .gif.");
                $('#imagen')[0].reportValidity();
                return;
            }

            if (imagen.size > maxSize) {
                $('#imagen').get(0).setCustomValidity("La imagen debe ser menor a 2 MB.");
                $('#imagen')[0].reportValidity();
                return;
            }
        } else {
            $('#imagen').get(0).setCustomValidity("Debe seleccionar una imagen.");
            $('#imagen')[0].reportValidity();
            return;
        }

        // Si todas las validaciones pasan, puedes enviar los datos al controlador
        const formData = new FormData(this); // Crear un objeto FormData para manejar el envío de archivos

        $.ajax({
            url: '../controller/registerUser.php', // Cambia la URL según tu controlador
            type: 'POST',
            data: formData,
            contentType: false, // Necesario para enviar archivos
            processData: false, // Necesario para enviar archivos
            beforeSend: function() {
                $("#loader").removeClass("esconder");
                $("body").addClass("hidenn");
            },
            success: function(resp) {
                var data = JSON.parse(resp);
                $("#loader").addClass("esconder");
                $("body").removeClass("hidenn");
                if (data.codigo == 1) {
                    // reseteo el form de registro 
                    $('#register-login')[0].reset();
                    //mensaje de exito
                    Swal.fire({
                        icon: "success",
                        title: "ÉXITO",
                        html: data.mensaje,
                        allowOutsideClick: false, // No permite cerrar al hacer clic fuera del modal
                        allowEscapeKey: false, // No permite cerrar con la tecla ESC
                        showCancelButton: false, // No muestra botón de cancelar
                        confirmButtonText: 'Aceptar', // Texto del botón de confirmar
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '../login/'; // Redirigir al login al aceptar
                        }
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