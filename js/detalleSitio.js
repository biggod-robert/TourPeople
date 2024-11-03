$(document).ready(function() {
    // Inicializar Quill en un editor oculto (solo para generar HTML desde Delta)
    var quill = new Quill('#quill-editor', {
        theme: 'snow' // Tema para un editor completo
    });

    // Obtener el ID del sitio desde la URL
    function obtenerIdSitio() {
        var url = window.location.href;
        return url.substring(url.lastIndexOf('/') + 1);
    }

    var id_sitio = obtenerIdSitio();

    // Cargar el sitio y sus imágenes
    function cargarSitio() {
        $.ajax({
            url: '../controller/detalleSitio.php',
            type: 'POST',
            dataType: 'json',
            data: { id_sitio: id_sitio },
            success: function(response) {
                console.log(response);
                // Cargar título, ubicación y enlace de reservas
                $('#titulo').html(response.nombre);
                $('#ubicacion').html(response.ubi_sitio);
                $('#enlaceReservas').attr('href', response.enlace_reservas_turs);

                // Verificar que la descripción no esté vacía
                if (response.descripcion_sitio) {

                    // Convertir la cadena Delta en un objeto
                    var deltaDescripcion = typeof response.descripcion_sitio === "string" ?
                        JSON.parse(response.descripcion_sitio) :
                        response.descripcion_sitio;

                    // Cargar el Delta en Quill
                    quill.setContents(deltaDescripcion);

                    // Obtener el HTML generado por Quill
                    var htmlDescripcion = $('#quill-editor .ql-editor').html();

                    // Colocar el HTML en el contenedor de descripción
                    $('#descripcion').html(htmlDescripcion);
                } else {
                    console.warn("El Delta de la descripción está vacío o no es válido.");
                }

                // Cargar las imágenes del carrusel
                var imagenes = response.imagenes;
                var carouselInner = $('.carousel-inner');
                carouselInner.empty(); // Asegurarse de que esté vacío al inicio
                var isActive = true;

                imagenes.forEach(function(img) {
                    var itemClass = isActive ? 'carousel-item active' : 'carousel-item';
                    carouselInner.append(`
                        <div class="${itemClass}">
                            <img src="../upload/sitios/images/${img}" class="d-block w-100" alt="Imagen de sitio">
                        </div>
                    `);
                    isActive = false; // Solo la primera imagen será activa
                });

                // Inicializar y empezar el ciclo automático del carrusel
                $('#carouselImages').carousel('cycle');
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar el sitio:', error);
            }
        });
    }

    cargarSitio();
    $('#carouselImages').carousel('cycle');
});