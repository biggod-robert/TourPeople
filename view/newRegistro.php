<!doctype html>
<html lang="es">

<head>
    <title>Instalador DB</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.14.4/sweetalert2.css" integrity="sha512-6qScZESleBziOBqJwOPurSy6lhLqHGjHNALOOFX0mzRVPiE5SZQvepRzeSO1OB475fcZywuMjxtkrFaO93aq9g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../assets/css/loader.css">
    <link rel="stylesheet" href="../css/general_forms.css">
</head>

<body class="hidenn">
    <header>
        <!-- place navbar here -->
    </header>
    <main>
        <?php
        if (file_exists('model/connectionDB.php')) {
            header('Location: ../inicio/');
            exit();
        }
        ?>
        <div class="conLoader" id="loader">
            <span class="loader"></span>
        </div>
        <div class="general-form">
            <form id="register-login">
                <div class="form-icon">
                    <img src="../assets/images/logo.png" alt="">
                </div>

                <div class="form-group">
                    <label for="documento">Documento ID:</label>
                    <input type="text" class="form-control item" id="documento" name="documento" placeholder="Documento de identidad" required>
                </div>

                <div class="form-group">
                    <label for="nombre_p">Nombre:</label>
                    <input type="text" class="form-control item" id="nombre_p" name="nombre_p" placeholder="Nombre" required>
                </div>

                <div class="form-group">
                    <label for="apellido_p">Apellido:</label>
                    <input type="text" class="form-control item" id="apellido_p" name="apellido_p" placeholder="Apellido" required>
                </div>

                <div class="form-group">
                    <label for="correo">Correo:</label>
                    <input type="email" class="form-control item" id="correo" name="correo" placeholder="Correo" required>
                </div>

                <div class="form-group">
                    <label for="pass">Contraseña:</label>
                    <input type="password" class="form-control item" id="pass" name="clave" placeholder="Contraseña" required>
                </div>

                <div class="form-group">
                    <label for="edad">Edad:</label>
                    <input type="number" class="form-control item" id="edad" name="edad" placeholder="Edad" required>
                </div>

                <div class="form-group">
                    <label for="f_nacimiento">Fecha de Nacimiento:</label>
                    <input type="date" class="form-control item" id="f_nacimiento" name="f_nacimiento" required>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" class="form-control item" id="telefono" name="telefono" placeholder="Teléfono" required>
                </div>

                <div class="form-group">
                    <label for="imagen">Imagen:</label>
                    <input type="file" class="form-control item" id="imagen" name="imagen" accept="image/*">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-block btn-send" id="loginBtn">Registrarme</button>
                </div>
            </form>
            <div class="social-media">
                <h5><a href="../reset-password/">Olvide mi contraseña</a></h5>
            </div>
        </div>
    </main>
    <footer>
        <!-- place footer here -->
    </footer>

    <script src="../assets/js/jquery-3.3.1.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.14.4/sweetalert2.min.js" integrity="sha512-a/ljmGyCvVDl+QZXCxw/6hKcG4V7Syo7qmb9lUFTwrP12lCCItvQKeTMBMjtpa+3RE6UZ7gk+/IZzj4H04y4ng==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="../js/main.js"></script>
    <script src="../js/newRegistro.js"></script>
</body>

</html>