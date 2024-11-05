$(document).ready(function() {
    var contador = 0; // Se permiten hasta 6 imágenes
    var opcion = 1; // Esta opción trae las imágenes
    var imagenesSeleccionadas = []; // Guardar las imágenes seleccionadas

    // Cargo los restaurantes ya publicados
    tabla_restaurantes = $("#tabla_restaurantes").DataTable({
        ajax: {
            url: "../controller/adminRestaurantes.php",
            method: "POST",
            data: { opcion: opcion },
            dataSrc: "",
        },
        columns: [
            { data: "id_restaurante" },
            { data: "nombre" },
            { data: "ubi_restaurante" },
            {
                defaultContent: "<div class='multi-button mx-auto'><button><i class='fas fa-trash deleteRestaurante'></i></button><button><i class='fa-regular fa-pen-to-square editRestaurante'></i></button></div>",
                className: "align-middle text-center text-sm td-respon"
            },
        ],
        "order": [
            [0, "desc"]
        ],
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.childRowImmediate,
                type: "none",
                target: "",
            },
        },
    });

    // Opción para agregar un nuevo restaurante
    $('.addRestaurante').click(function(e) {
        e.preventDefault();
        opcion = 2; // La opción 2 refiere a agregar un nuevo restaurante
        clearFormRestaurantes();
        $('#titulo-modal').text('Nuevo Restaurante');
        $('#imgPortada').attr('required', true);
        $('#modalRestaurante').modal('show');
    });

    // Detectar cambio en el input de tipo file (imagen de portada)
    $("#imgPortada").on("change", function() {
        const file = this.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                $(".previewImgPortada").empty();
                const imgElement = $("<img>").attr("src", e.target.result);
                $(".previewImgPortada").append(imgElement);
            };

            reader.readAsDataURL(file);
        }
    });

    // Cargar imágenes adicionales
    $('.cap-img').click(function() {
        if (contador < 6) {
            var input = $('<input>').attr({
                type: 'file',
                accept: 'image/*',
                name: 'imagen[]',
                id: 'imgUploas' + contador,
                multiple: true
            }).css('display', 'none');

            $('#formRestaurantes').append(input);

            input.change(function() {
                var files = this.files;
                var filesLength = files.length;

                if (contador + filesLength > 6) {
                    alert('Solo se permiten hasta 6 imágenes.');
                } else {
                    for (var i = 0; i < filesLength; i++) {
                        var file = files[i];
                        imagenesSeleccionadas.push(file);

                        var reader = new FileReader();

                        reader.onload = function(event) {
                            var imgSrc = event.target.result;
                            var img = $('<img>').attr({
                                src: imgSrc,
                                class: 'img-thumbnail imgEvide'
                            });

                            var deleteIcon = $('<i>').addClass('fas fa-trash-alt detelete-img-upload btnDelete').attr('data-index', contador);
                            var div = $('<div>').addClass('contImgUP').append(img, deleteIcon);
                            var colDiv = $('<div>').addClass('col-md-4 d-flex').append(div);

                            $('#cont-previu').append(colDiv);

                            contador++;
                            $('.cap-img').attr('imgCapturadas', contador);
                            $('.previewEvide').removeClass('igmError');
                            $('.cap-img').removeClass('igmErrorCap');

                            // Ver imagen en pantalla completa
                            $('.imgEvide').click(function() {
                                var url = $(this).attr('src');
                                $('#imgFullViewSRC').attr("src", url);
                                $('.viewFullImg').removeClass('esconder');
                            });
                        };

                        reader.readAsDataURL(file);
                    }
                }
            });

            input.click();
        } else {
            alert('No se pueden agregar más de 6 imágenes.');
        }
    });

    // Borrar imagen y su input
    $('#cont-previu').on('click', '.detelete-img-upload', function() {
        var indexToDelete = $(this).attr('data-index');
        imagenesSeleccionadas.splice(indexToDelete, 1);
        $(this).closest('.col-md-4').remove();
        contador--;
        $('.cap-img').attr('imgCapturadas', contador);
        actualizarInputImagenes();
    });

    // Función para recrear el input con las imágenes restantes
    function actualizarInputImagenes() {
        $('input[type="file"][name="imagen[]"]').remove();
        var input = $('<input>').attr({
            type: 'file',
            accept: 'image/*',
            name: 'imagen[]',
            multiple: true
        }).css('display', 'none');

        $('#formRestaurantes').append(input);
        var dataTransfer = new DataTransfer();
        imagenesSeleccionadas.forEach(function(file) {
            dataTransfer.items.add(file);
        });
        input[0].files = dataTransfer.files;
    }

    // Configuración del editor de texto
    const quill = new Quill('#editorDescripcion', {
        modules: {
            syntax: true,
            toolbar: '#toolbar-container',
        },
        placeholder: 'Escribe la descripción del restaurante',
        theme: 'snow',
    });

    // Envío del formulario
    $("#formRestaurantes").submit(function(e) {
        e.preventDefault();

        if (contador == 0) {
            $(".cap-img").addClass('igmErrorCap');
            $(".previewEvide").addClass('igmErrorCap');

            setTimeout(() => {
                $(".cap-img").removeClass('igmErrorCap');
                $(".previewEvide").removeClass('igmErrorCap');
            }, 3500);
            Swal.fire({
                title: "Advertencia",
                text: "Debes de seleccionar de 1 a 6 imágenes",
                icon: "warning"
            });
            return;
        }

        let content = quill.getText().trim();

        if (content.length === 0) {
            $('#editorDescripcion').addClass('igmErrorCap');
            Swal.fire({
                title: "Advertencia",
                text: "La descripción del restaurante es obligatoria",
                icon: "warning"
            });
            setTimeout(() => {
                $('#editorDescripcion').removeClass('igmErrorCap');
            }, 3500);
            return;
        }

        const formulario = document.getElementById("formRestaurantes");
        const FormSolicitud = new FormData(formulario);
        let quillContent = quill.getContents();
        FormSolicitud.append("opcion", opcion);
        FormSolicitud.append("descripcion", JSON.stringify(quillContent));

        $.ajax({
            type: "POST",
            url: "../controller/adminRestaurantes.php",
            data: FormSolicitud,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $("#loader").removeClass("esconder");
                $("body").addClass("hidenn");
            },
            success: function(resp) {
                var data = JSON.parse(resp);
                tabla_restaurantes.ajax.reload();
                $('#modalRestaurante').modal('hide');
                $("#loader").addClass("esconder");
                $("body").removeClass("hidenn");

                if (data.codigo == 1) {
                    Swal.fire({
                        icon: "success",
                        title: "EXITO",
                        html: data.mensaje,
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

    // Cerrar el visualizador de imagen
    $('.c-full-view').click(function() {
        $('.viewFullImg').addClass('esconder');
    });

    // Limpiar el formulario
    function clearFormRestaurantes() {
        $('#formRestaurantes')[0].reset();
        $('.previewImgPortada').empty();
        $('#cont-previu').empty();
        imagenesSeleccionadas = [];
        contador = 0;
        quill.setContents([]);
        $('#imgPortada').attr('required', true);
    }

    // Opción para editar un restaurante
    $(document).on('click', '.editRestaurante', function(e) {
        e.preventDefault();
        var row = $(this).closest("tr");

        if (row.hasClass("child")) {
            row = row.prev();
        }

        var data = tabla_restaurantes.row(row).data();
        var idRestaurante = data.id_restaurante;
        opcion = 3;
        $('#titulo-modal').text('Editar Restaurante');
        clearFormRestaurantes();

        $('#imgPortada').removeAttr('required');

        $.ajax({
            type: "POST",
            url: "../controller/adminRestaurantes.php",
            data: { opcion: opcion, idRestaurante: idRestaurante },
            beforeSend: function() {
                $("#loader").removeClass("esconder");
                $("body").addClass("hidenn");
            },
            success: function(resp) {
                try {
                    var respuesta = JSON.parse(resp);
                    if (respuesta.codigo === 1 && respuesta.data) {
                        var restaurante = respuesta.data;
                        $('#id_restauranteEdit').val(restaurante.id_restaurante);
                        $('#nombreRestaurante').val(restaurante.nombre);
                        $('#ubicacion').val(restaurante.ubi_restaurante);
                        $('#enlace_reservas').val(restaurante.enlace_reservas_rest);
                        quill.setContents(JSON.parse(restaurante.descripcion_restaurante));

                        var imgPortadaUrl = '../upload/restaurantes/portadas/' + restaurante.foto;
                        $(".previewImgPortada").html('<img src="' + imgPortadaUrl + '" class="img-thumbnail">');

                        restaurante.imagenes.forEach(function(imagen, index) {
                            var imgSrc = '../upload/restaurantes/images/' + imagen.img;
                            var imgElement = `
                            <div class="col-md-4 d-flex">
                                <div class="contImgUP">
                                    <img src="${imgSrc}" class="img-thumbnail imgEvide">
                                    <i class="fas fa-trash-alt deteleteimgDB btnDelete" data-id="${imagen.id_img}"></i>
                                </div>
                            </div>`;
                            $('#cont-previu').append(imgElement);
                            contador++;
                            imagenesSeleccionadas.push(imagen.img);
                        });

                        opcion = 5;
                        $("#loader").addClass("esconder");
                        $("body").removeClass("hidenn");
                    }
                } catch (error) {
                    console.error("Error al decodificar JSON: ", error, resp);
                }
            }
        });

        $('#modalRestaurante').modal('show');
    });

    // Borrar imagen desde la base de datos
    $('#cont-previu').on('click', '.deteleteimgDB', function() {
        var idImagen = $(this).attr('data-id');
        var element = $(this);

        $.ajax({
            type: "POST",
            url: "../controller/adminRestaurantes.php",
            data: { opcion: 4, idImagen: idImagen },
            beforeSend: function() {
                $("#loader").removeClass("esconder");
                $("body").addClass("hidenn");
            },
            success: function(resp) {
                try {
                    var respuesta = JSON.parse(resp);

                    if (respuesta.codigo === 1) {
                        element.closest('.col-md-4').remove();
                        Swal.fire({
                            icon: "success",
                            title: "EXITO",
                            text: respuesta.mensaje,
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: respuesta.mensaje,
                        });
                    }
                } catch (error) {
                    console.error("Error al decodificar JSON: ", error, resp);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Error en la respuesta del servidor."
                    });
                }
                $("#loader").addClass("esconder");
                $("body").removeClass("hidenn");
            },
            error: function(xhr, status, error) {
                console.error("Error en la petición AJAX: ", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Ocurrió un error al intentar eliminar la imagen."
                });
                $("#loader").addClass("esconder");
                $("body").removeClass("hidenn");
            }
        });
    });

    // Eliminar restaurante
    $(document).on('click', '.deleteRestaurante', function(e) {
        e.preventDefault();
        var row = $(this).closest("tr");

        if (row.hasClass("child")) {
            row = row.prev();
        }

        var data = tabla_restaurantes.row(row).data();
        var idRestauranteDelete = data.id_restaurante;
        var opcion = 6;

        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Este restaurante se eliminará permanentemente!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "../controller/adminRestaurantes.php",
                    data: { opcion: opcion, idRestauranteDelete: idRestauranteDelete },
                    beforeSend: function() {
                        $("#loader").removeClass("esconder");
                        $("body").addClass("hidenn");
                    },
                    success: function(resp) {
                        $("#loader").addClass("esconder");
                        $("body").removeClass("hidenn");
                        var respuesta = JSON.parse(resp);
                        if (respuesta.codigo === 1) {
                            tabla_restaurantes.row(row).remove().draw();
                            Swal.fire({
                                icon: "success",
                                title: "Éxito",
                                text: respuesta.mensaje
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: respuesta.mensaje
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $("#loader").addClass("esconder");
                        $("body").removeClass("hidenn");
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Error en la solicitud: " + textStatus + " - " + errorThrown
                        });// Mensaje de error
                    }
                });
            }
        });
    });

});