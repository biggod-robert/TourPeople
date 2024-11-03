$(document).ready(function() {
    // Inicializar Quill en un editor oculto (solo para generar HTML desde Delta)
    var quill = new Quill('#quill-editor', {
        theme: 'snow' // Tema para un editor completo
    });

    // Obtener el ID del hotel desde la URL
    function obtenerIdHotel() {
        var url = window.location.href;
        return url.substring(url.lastIndexOf('/') + 1);
    }

    var id_hotel = obtenerIdHotel();

    // Cargar el hotel y sus imágenes
    function cargarHotel() {
        $.ajax({
            url: '../controller/detalleHotel.php',
            type: 'POST',
            dataType: 'json',
            data: { id_hotel: id_hotel },
            success: function(response) {
                console.log(response);
                // Cargar título, ubicación y enlace de reservas
                $('#titulo').html(response.nombre);
                $('#ubicacion').html(response.ubicacion_hotel);
                $('#enlaceReservas').attr('href', response.enlace_reservas);

                // Verificar que la descripción no esté vacía
                if (response.descripcion_hotel) {

                    // Convertir la cadena Delta en un objeto
                    var deltaDescripcion = typeof response.descripcion_hotel === "string" ?
                        JSON.parse(response.descripcion_hotel) :
                        response.descripcion_hotel;

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
                            <img src="../upload/hoteles/images/${img}" class="d-block w-100" alt="Imagen de sitio">
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

    cargarHotel();
    // Inicializar y empezar el ciclo automático del carrusel
    $('#carouselImages').carousel('cycle');
});