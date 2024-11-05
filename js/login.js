$(document).ready(function() {
    let captchaAttempts = 0; // Contador de intentos fallidos
    let isBlocked = false; // Estado de bloqueo
    let blockDuration = 1 * 60 * 1000; // 1 minuto en milisegundos
    let blockTimer;
    let countdownTimer; // Temporizador de cuenta regresiva

    // Generar el captcha aleatorio en el canvas
    function generateCaptcha() {
        const captchaCode = Math.random().toString(36).substring(2, 8).toUpperCase();
        const canvas = document.getElementById('captchaCanvas');
        const ctx = canvas.getContext('2d');

        // Limpiar el canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Estilos para el captcha
        ctx.font = '40px Arial';
        ctx.fillStyle = '#000';

        // Calcular las coordenadas para centrar el texto
        const textWidth = ctx.measureText(captchaCode).width;
        const x = (canvas.width - textWidth) / 2;
        const y = (canvas.height + 20) / 2;

        // Dibujar el texto del captcha
        ctx.fillText(captchaCode, x, y);

        // Dibujar líneas aleatorias para mayor dificultad
        for (let i = 0; i < 5; i++) {
            ctx.beginPath();
            ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
            ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
            ctx.strokeStyle = '#' + Math.floor(Math.random() * 16777215).toString(16);
            ctx.stroke();
        }

        // Guardar el captcha en el atributo del canvas
        canvas.setAttribute('data-captcha', captchaCode);
    }

    // Llamar a la función para generar el captcha al cargar la página
    generateCaptcha();

    // Recargar captcha al hacer clic en el botón
    $('#reloadCaptcha').click(function() {
        generateCaptcha();
    });

    // Quitar el mensaje de error mientras el usuario escribe en los campos
    $('#correo, #pass, #captchaInput').on('input', function() {
        $(this).get(0).setCustomValidity("");
    });

    // Validación y envío del formulario
    $('#login_user').on('submit', function(e) {
        e.preventDefault();

        // Comprobar si está bloqueado
        if (isBlocked) {
            Swal.fire({
                icon: "error",
                title: "Bloqueado",
                text: "Has realizado 3 intentos fallidos. Intenta de nuevo más tarde.",
            });
            return;
        }

        const username = $('#correo').val().trim();
        const password = $('#pass').val().trim();
        const captchaInput = $('#captchaInput').val().trim();
        const captchaCode = $('#captchaCanvas').attr('data-captcha');

        // Validación de los campos
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (username === "") {
            $('#correo').get(0).setCustomValidity("El campo de correo no puede estar vacío.");
            $('#correo')[0].reportValidity();
            return;
        } else if (!emailRegex.test(username)) {
            $('#correo').get(0).setCustomValidity("Por favor, ingrese un correo electrónico válido.");
            $('#correo')[0].reportValidity();
            return;
        }
        if (password === "") {
            $('#pass').get(0).setCustomValidity("La contraseña no puede estar vacía.");
            $('#pass')[0].reportValidity();
            return;
        }

        if (captchaInput === "" || captchaInput !== captchaCode) {
            $('#captchaInput').get(0).setCustomValidity("El CAPTCHA ingresado es incorrecto.");
            generateCaptcha(); // Regenerar el captcha si es incorrecto
            $('#captchaInput')[0].reportValidity();

            captchaAttempts++; // Incrementar el contador de intentos

            // Bloquear después de 3 intentos
            if (captchaAttempts >= 3) {
                isBlocked = true; // Activar el estado de bloqueo
                $('#loginBtn').prop('disabled', true); // Desactivar el botón de iniciar sesión
                let countdown = blockDuration / 1000; // Obtener el tiempo en segundos
                $('#loginBtn').text(`Bloqueado. Intenta de nuevo en ${countdown} segundos`);
                Swal.fire({
                    icon: "error",
                    title: "Bloqueado",
                    text: "Has realizado 3 intentos fallidos de capchat. espera 1 minuto antes de volverlo a intentar.",
                });

                // Iniciar temporizador de cuenta regresiva
                countdownTimer = setInterval(function() {
                    countdown--;
                    $('#loginBtn').text(`Bloqueado ${countdown}s`);
                    if (countdown <= 0) {
                        clearInterval(countdownTimer);
                        isBlocked = false; // Desbloquear
                        captchaAttempts = 0; // Reiniciar el contador de intentos
                        $('#loginBtn').prop('disabled', false); // Reactivar el botón
                        $('#loginBtn').text("Iniciar sesión"); // Restablecer el texto del botón
                        generateCaptcha(); // Generar un nuevo captcha
                    }
                }, 1000);
            }

            return;
        }

        // Enviar el formulario si todo es válido
        var formLogin = new FormData(document.getElementById("login_user"));
        $.ajax({
            type: "POST",
            url: "../controller/login.php",
            data: formLogin,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $("#loader_app").removeClass("esconder");
                $("body").addClass("hidenn");
            },
            success: function(resp) {
                switch (resp) {
                    case "0101a":
                        window.location.href = "../dashboard/";
                        break;
                    case "0101u":
                        window.location.href = "../inicio-usuarios/";
                        break;
                    case "0102w":
                        $("#loader_app").addClass("esconder");
                        $("body").removeClass("hidenn");
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: "La contraseña es incorrecta",
                        });
                        break;
                    case "0103r":
                        $("#loader_app").addClass("esconder");
                        $("body").removeClass("hidenn");
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: "El usuario no existe",
                        });
                        break;
                    case "0104b":
                        $("#loader_app").addClass("esconder");
                        $("body").removeClass("hidenn");
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: "Realizaste 3 intentos fallidos, se realizó un bloqueo por 1 minuto",
                        });
                        break;
                    default:
                        break;
                }
            }
        });
    });

    //redireccion a la pagina de registro
    $('.registro').click(function(e) {
        e.preventDefault();
        window.location.href = "../registro/";

    })
});