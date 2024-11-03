<!doctype html>
<html lang="es">

<head>
    <title>Restablecer contraseña</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <!-- cdn de sweet alert -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.14.4/sweetalert2.css" integrity="sha512-6qScZESleBziOBqJwOPurSy6lhLqHGjHNALOOFX0mzRVPiE5SZQvepRzeSO1OB475fcZywuMjxtkrFaO93aq9g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- hoja de estilos para el loader -->
    <link rel="stylesheet" href="../assets/css/loader.css">
    <!-- hoja de extilos para el instalador de la base de datos -->
    <link rel="stylesheet" href="../css/general_forms.css">
</head>

<body class="hidenn">
    <header>
        <!-- place navbar here -->
    </header>
    <main>
        <div class="conLoader" id="loader">
            <span class="loader"></span>
        </div>
        <div class="general-form">
            <form id="formReset">
                <div class="form-icon">
                    <img src="../assets/images/logo.png" alt="">
                </div>
                <div class="form-group">
                    <input type="email" class="form-control item" id="correo" name="correo" placeholder="Correo">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control item" id="code" name="code" placeholder="Ingrese su codigo">
                </div>


                <div class="form-group">
                    <button type="button" class="btn btn-block btn-sendCode">Enviar codigo</button>
                    <button type="button" class="btn btn-block btn-send">Restablecer contraseña</button>
                </div>
            </form>
            <div class="social-media">
                <h5>Recupera tu contraseña</h5>
                <p>Envia un codigo a tu correo electronico registrado, luego digita el codigo en el campo "Codigo", y restablece tu contraseña</p>
            </div>
        </div>
    </main>
    <footer>
        <!-- place footer here -->
    </footer>

    <!-- Bootstrap JavaScript Ldibracries -->
    <script src="../assets/js/bootstrap.min.js"></script>
    <!-- jquery librari f-->
    <script src="../assets/js/jquery-3.3.1.min.js"></script>
    <!-- cdn js de sweet alert -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.14.4/sweetalert2.min.js" integrity="sha512-a/ljmGyCvVDl+QZXCxw/6hKcG4V7Syo7qmb9lUFTwrP12lCCItvQKeTMBMjtpa+3RE6UZ7gk+/IZzj4H04y4ng==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- scrips del main.js -->
    <script src="../js/main.js"></script>
    <!-- scrips del restableceedor de contraseñas -->
    <script src="../js/resetPass.js"></script>
</body>

</html>