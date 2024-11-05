$(document).ready(function() {
    // Llamada AJAX para obtener los restaurantes
    $.ajax({
        url: '../controller/restaurantes.php',
        type: 'POST',
        success: function(resp) {
            console.log(resp);
            const restaurantes = JSON.parse(resp);
            const contRestaurantes = $('#contRestaurantes');

            // Genera el contenido de cada tarjeta para cada restaurante
            restaurantes.forEach(restaurante => {
                // Procesa la descripción del restaurante para extraer solo el texto
                let descripcion = '';
                try {
                    const descripcionObj = JSON.parse(restaurante.descripcion_restaurante);
                    descripcion = descripcionObj.ops[0].insert.trim().substring(0, 60);
                } catch (error) {
                    console.error("Error al parsear la descripción:", error);
                    descripcion = restaurante.descripcion_restaurante.substring(0, 60);
                }

                // Define el estado del botón de like según 'like_status'
                const likeStatus = restaurante.like_status === 'activo' ? 'active' : 'none';
                const likeButtonClass = likeStatus === 'active' ? 'btn-success' : 'btn-outline-secondary';
                const likeText = likeStatus === 'active' ? 'Liked' : 'Like';

                // Crea el HTML de cada tarjeta
                const restauranteHtml = `
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <img src="../upload/restaurantes/portadas/${restaurante.foto}" class="card-img-top" alt="${restaurante.nombre}">
                            <div class="card-body">
                                <h5 class="card-title">${restaurante.nombre}</h5>
                                <p class="card-text">${descripcion}</p>
                                <a href="../detalle-restaurante/${restaurante.id_restaurante}" class="btn btn-primary">Ver restaurante</a>
                                <button id="likeBtn-${restaurante.id_restaurante}" class="btn ${likeButtonClass} like-btn" data-id="${restaurante.id_restaurante}" data-status="${likeStatus}">
                                    ${likeText}
                                </button>
                                <p class="mt-2">Likes: <span id="likeCount-${restaurante.id_restaurante}">${restaurante.total_likes}</span></p>
                            </div>
                        </div>
                    </div>
                `;

                // Agrega el HTML generado al contenedor principal
                contRestaurantes.append(restauranteHtml);
            });

            // Evento para el botón de like
            $('.like-btn').on('click', function() {
                const button = $(this);
                const idRestaurante = button.data('id');
                const currentStatus = button.data('status');
                // Determina el nuevo estado de like
                const newStatus = currentStatus === 'active' ? 'none' : 'active';
                // Envío AJAX para cambiar el estado de like
                $.ajax({
                    url: '../controller/like_restaurante.php',
                    type: 'POST',
                    data: {
                        id_restaurante: idRestaurante,
                        like_status: newStatus
                    },
                    success: function(resp) {
                        console.log(resp);
                        const likeCountElement = $(`#likeCount-${idRestaurante}`);
                        let likeCount = parseInt(likeCountElement.text());

                        // Actualiza el conteo y estado del botón según la respuesta
                        if (newStatus === 'active') {
                            button.removeClass('btn-outline-secondary').addClass('btn-success').text('Liked');
                            likeCount++;
                        } else {
                            button.removeClass('btn-success').addClass('btn-outline-secondary').text('Like');
                            likeCount--;
                        }

                        // Actualiza el conteo de likes
                        likeCountElement.text(likeCount);
                        button.data('status', newStatus);
                    },
                    error: function() {
                        console.error('Error al cambiar el estado de like.');
                    }
                });
            });
        },
        error: function() {
            console.error('Error al obtener los restaurantes.');
        }
    });
});
