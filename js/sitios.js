$(document).ready(function() {
    // Llamada AJAX para obtener los sitios
    $.ajax({
        url: '../controller/sitios.php',
        type: 'POST',
        success: function(resp) {
            const sitios = JSON.parse(resp);
            const contSitios = $('#contSitios');

            // Genera el contenido de cada tarjeta para cada sitio
            sitios.forEach(sitio => {
                // Procesa la descripción del sitio para extraer solo el texto
                let descripcion = '';
                try {
                    const descripcionObj = JSON.parse(sitio.descripcion_sitio);
                    descripcion = descripcionObj.ops[0].insert.trim().substring(0, 60);
                } catch (error) {
                    console.error("Error al parsear la descripción:", error);
                    descripcion = sitio.descripcion_sitio.substring(0, 60);
                }

                // Define el estado del botón de like según 'like_status'
                const likeStatus = sitio.like_status === 'activo' ? 'active' : 'none';
                const likeButtonClass = likeStatus === 'active' ? 'btn-success' : 'btn-outline-secondary';
                const likeText = likeStatus === 'active' ? 'Liked' : 'Like';

                // Crea el HTML de cada tarjeta
                const sitioHtml = `
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <img src="../upload/sitios/portadas/${sitio.foto}" class="card-img-top" alt="${sitio.nombre}">
                            <div class="card-body">
                                <h5 class="card-title">${sitio.nombre}</h5>
                                <p class="card-text">${descripcion}</p>
                                <a href="../detalle-sitio/${sitio.id_sitio}" class="btn btn-primary">Ver sitio</a>
                                <button id="likeBtn-${sitio.id_sitio}" class="btn ${likeButtonClass} like-btn" data-id="${sitio.id_sitio}" data-status="${likeStatus}">
                                    ${likeText}
                                </button>
                                <p class="mt-2">Likes: <span id="likeCount-${sitio.id_sitio}">${sitio.total_likes}</span></p>
                            </div>
                        </div>
                    </div>
                `;

                // Agrega el HTML generado al contenedor principal
                contSitios.append(sitioHtml);
            });

            // Evento para el botón de like
            $('.like-btn').on('click', function() {
                const button = $(this);
                const idSitio = button.data('id');
                const currentStatus = button.data('status');
                // Determina el nuevo estado de like
                const newStatus = currentStatus === 'active' ? 'none' : 'active';

                // Envío AJAX para cambiar el estado de like
                $.ajax({
                    url: '../controller/like_sitio.php',
                    type: 'POST',
                    data: {
                        id_sitio: idSitio,
                        like_status: newStatus
                    },
                    success: function(resp) {
                        console.log(resp);
                        const likeCountElement = $(`#likeCount-${idSitio}`);
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
            console.error('Error al cargar los sitios:', err);
        }
    });
});