$(document).ready(function() {
    // Llamada AJAX para obtener los hoteles
    $.ajax({
        url: '../controller/hoteles.php',
        type: 'POST',
        success: function(resp) {
            console.log(resp)
            const hoteles = JSON.parse(resp);
            const contHoteles = $('#contHoteles');

            // Genera el contenido de cada tarjeta para cada hotel
            hoteles.forEach(hotel => {
                // Procesa la descripción del hotel para extraer solo el texto
                let descripcion = '';
                try {
                    const descripcionObj = JSON.parse(hotel.descripcion_hotel);
                    descripcion = descripcionObj.ops[0].insert.trim().substring(0, 60);
                } catch (error) {
                    console.error("Error al parsear la descripción:", error);
                    descripcion = hotel.descripcion_hotel.substring(0, 60);
                }

                // Define el estado del botón de like según 'like_status'
                const likeStatus = hotel.like_status === 'activo' ? 'active' : 'none';
                const likeButtonClass = likeStatus === 'active' ? 'btn-success' : 'btn-outline-secondary';
                const likeText = likeStatus === 'active' ? 'Liked' : 'Like';

                // Crea el HTML de cada tarjeta
                const hotelHtml = `
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <img src="../upload/hoteles/portadas/${hotel.foto}" class="card-img-top" alt="${hotel.nombre}">
                            <div class="card-body">
                                <h5 class="card-title">${hotel.nombre}</h5>
                                <p class="card-text">${descripcion}</p>
                                <a href="../detalle-hotel/${hotel.id_hotel}" class="btn btn-primary">Ver hotel</a>
                                <button id="likeBtn-${hotel.id_hotel}" class="btn ${likeButtonClass} like-btn" data-id="${hotel.id_hotel}" data-status="${likeStatus}">
                                    ${likeText}
                                </button>
                                <p class="mt-2">Likes: <span id="likeCount-${hotel.id_hotel}">${hotel.total_likes}</span></p>
                            </div>
                        </div>
                    </div>
                `;

                // Agrega el HTML generado al contenedor principal
                contHoteles.append(hotelHtml);
            });

            // Evento para el botón de like
            $('.like-btn').on('click', function() {
                const button = $(this);
                const idHotel = button.data('id');
                const currentStatus = button.data('status');
                // Determina el nuevo estado de like
                const newStatus = currentStatus === 'active' ? 'none' : 'active';

                // Envío AJAX para cambiar el estado de like
                $.ajax({
                    url: '../controller/like_hotel.php',
                    type: 'POST',
                    data: {
                        id_hotel: idHotel,
                        like_status: newStatus
                    },
                    success: function(resp) {
                        console.log(resp);
                        const likeCountElement = $(`#likeCount-${idHotel}`);
                        let likeCount = parseInt(likeCountElement.text());

                        // Actualiza el conteo y estado del botón según la respuesta
                        if (newStatus === 'active') {
                            button.removeClass('btn-outline-secondary').addClass('btn-success').text('Liked');
                            likeCount++;
                        } else {
                            button.removeClass('btn-success').addClass('btn-outline-secondary').text('Like');
                            likeCount--;
                        }

                        // Actualiza el contador y el estado de like
                        likeCountElement.text(likeCount);
                        button.data('status', newStatus);
                    },
                    error: function(err) {
                        console.error('Error al cambiar el estado de like:', err);
                    }
                });
            });
        },
        error: function(err) {
            console.error('Error al cargar los hoteles:', err);
        }
    });
});