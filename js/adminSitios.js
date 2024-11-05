$(document).ready(function() {
    var contador = 0; // Se permiten hasta 6 imágenes
    var opcion = 1; // Esta opción trae las imágenes
    var imagenesSeleccionadas = []; // Guardar las imágenes seleccionadas

    // Cargo los sitios ya publicados
    tabla_sitios = $("#tabla_sitios").DataTable({
        ajax: {
            url: "../controller/adminSitios.php",
            method: "POST", // usamos el metodo POST
            data: { opcion: opcion }, // enviamos opcion 4 para que haga un SELECT
            dataSrc: "",
        },
        columns: [
            { data: "id_sitio" },
            { data: "nombre" },
            {
                defaultContent: "<div class='multi-button mx-auto'><button><i class='fas fa-trash deleteEvento'></i></button><button><i class='fa-regular fa-pen-to-square editSitio'></i></button></div>",
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

    // Opción para agregar un nuevo sitio
    $('.addsitio').click(function(e) {
        e.preventDefault();
        opcion = 2; // La opción 2 refiere a agregar un nuevo sitio
        clearFormSitios();
        $('#titulo-modal').text('Nuevo Sitio');
        $('#imgPortada').attr('required', true); // Hacer la imagen de portada obligatoria
        $('#modalSitio').modal('show');
    });

    // Detectar cambio en el input de tipo file (imagen de portada)
    $("#imgPortada").on("change", function() {
        const file = this.files[0]; // Obtener el archivo seleccionado

        if (file) {
            const reader = new FileReader(); // Crear un lector de archivos

            // Cuando el lector haya cargado el archivo
            reader.onload = function(e) {
                // Remover cualquier imagen previa
                $(".previewImgPortada").empty();

                // Crear una nueva imagen y asignarle la fuente cargada
                const imgElement = $("<img>").attr("src", e.target.result);
                $(".previewImgPortada").append(imgElement);
            };

            // Leer el archivo seleccionado como DataURL
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

            // Agregar el input al formulario
            $('#formSitios').append(input);

            input.change(function() {
                var files = this.files;
                var filesLength = files.length;

                if (contador + filesLength > 6) {
                    alert('Solo se permiten hasta 6 imágenes.');
                } else {
                    for (var i = 0; i < filesLength; i++) {
                        var file = files[i];
                        imagenesSeleccionadas.push(file); // Agregar imagen al array

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

                            // Cerrar el visualizador de imagen
                            $('.c-full-view').click(function() {
                                $('.viewFullImg').addClass('esconder');
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
        var indexToDelete = $(this).attr('data-index'); // Obtener el índice de la imagen a eliminar
        imagenesSeleccionadas.splice(indexToDelete, 1); // Eliminar la imagen del array

        // Eliminar la previsualización de la imagen
        $(this).closest('.col-md-4').remove();

        // Decrementar el contador
        contador--;
        $('.cap-img').attr('imgCapturadas', contador);

        // Actualizar el input de imágenes
        actualizarInputImagenes();
    });

    // Función para recrear el input con las imágenes restantes
    function actualizarInputImagenes() {
        // Eliminar el input actual del formulario
        $('input[type="file"][name="imagen[]"]').remove();

        // Crear un nuevo input
        var input = $('<input>').attr({
            type: 'file',
            accept: 'image/*',
            name: 'imagen[]',
            multiple: true
        }).css('display', 'none');

        $('#formSitios').append(input);

        // Crear un objeto de tipo DataTransfer para almacenar los archivos
        var dataTransfer = new DataTransfer();

        // Añadir las imágenes restantes al DataTransfer
        imagenesSeleccionadas.forEach(function(file) {
            dataTransfer.items.add(file);
        });

        // Asignar los archivos al nuevo input
        input[0].files = dataTransfer.files;
    }

    // Configuración del editor de texto
    const quill = new Quill('#editorDescripcion', {
        modules: {
            syntax: true,
            toolbar: '#toolbar-container',
        },
        placeholder: 'Escribe tu sitio',
        theme: 'snow',
    });

    // Envío del formulario
    $("#formSitios").submit(function(e) {
        e.preventDefault();

        // Validación de que se haya seleccionado imágenes del sitios
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

        let content = quill.getText().trim(); //Verificar con getText()

        if (content.length === 0) {
            $('#editorDescripcion').addClass('igmErrorCap');
            Swal.fire({
                title: "Advertencia",
                text: "La descripción de la sitio es obligatoria",
                icon: "warning"
            });
            setTimeout(() => {
                $('#editorDescripcion').removeClass('igmErrorCap');
            }, 3500);
            return; // Detener el envío del formulario
        }

        const formulario = document.getElementById("formSitios");
        const FormSolicitud = new FormData(formulario);

        // Obtener el contenido en formato Delta (JSON)
        let quillContent = quill.getContents();
        // Envío la opción de nueva solicitud al controlador de solicitudes
        FormSolicitud.append("opcion", opcion);
        FormSolicitud.append("descripcion", JSON.stringify(quillContent));

        $.ajax({
            type: "POST",
            url: "../controller/adminSitios.php",
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
                tabla_sitios.ajax.reload();
                $('#modalSitio').modal('hide');
                $("#loader").addClass("esconder");
                $("body").removeClass("hidenn");

                if (data.codigo == 1) {
                    //mensaje de exito
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
    function clearFormSitios() {
        $('#formSitios')[0].reset();
        $('.previewImgPortada').empty();
        $('#cont-previu').empty();
        imagenesSeleccionadas = [];
        contador = 0;
        quill.setContents([]);

        // Al limpiar el formulario, el campo de imagen de portada vuelve a ser obligatorio
        $('#imgPortada').attr('required', true);
    }

    // Opción para editar un sitio
    $(document).on('click', '.editSitio', function(e) {
        e.preventDefault();
        // Obtener la fila a la que pertenece el botón
        var row = $(this).closest("tr");

        // Verificar si la fila es una "child row" en el modo responsivo
        if (row.hasClass("child")) {
            row = row.prev(); // Si es un child row, obtenemos la fila anterior (padre)
        }

        // Usamos DataTables para obtener los datos de la fila
        var data = tabla_sitios.row(row).data(); // Accedemos a la fila completa

        // Obtener el ID de la sitio
        var idSitio = data.id_sitio; // Aquí tienes el ID oculto
        opcion = 3; // La opción 3 refiere a traer la información de un sitio
        $('#titulo-modal').text('Editar Sitio');
        clearFormSitios(); // Limpiamos el formulario antes de cargar los nuevos datos

        // Eliminar el atributo 'required' del campo de imagen de portada cuando estamos editando
        $('#imgPortada').removeAttr('required');

        $.ajax({
            type: "POST",
            url: "../controller/adminSitios.php",
            data: { opcion: opcion, idSitio: idSitio },
            beforeSend: function() {
                $("#loader").removeClass("esconder");
                $("body").addClass("hidenn");
            },
            success: function(resp) {
                try {
                    var respuesta = JSON.parse(resp);
                    if (respuesta.codigo === 1 && respuesta.data) {
                        var sitio = respuesta.data;
                        $('#id_sitioEdit').val(sitio.id_sitio);
                        $('#nombreSitio').val(sitio.nombre);
                        $('#direccion').val(sitio.ubi_sitio);
                        $('#enlace_reservas').val(sitio.enlace_reservas_turs);
                        quill.setContents(JSON.parse(sitio.descripcion));

                        var imgPortadaUrl = '../upload/sitios/portadas/' + sitio.imgPortada;
                        $(".previewImgPortada").html('<img src="' + imgPortadaUrl + '" class="img-thumbnail">');

                        sitio.imagenes.forEach(function(imagen, index) {
                            var imgSrc = '../upload/sitios/images/' + imagen.img;
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

                        opcion = 5; // esta opcion guarda los cambios de una sitio editada en la base de datos
                        $("#loader").addClass("esconder");
                        $("body").removeClass("hidenn");
                    }
                } catch (error) {
                    console.error("Error al decodificar JSON: ", error, resp);
                }
            }
        });

        $('#modalSitio').modal('show');
    });
    
    // Borrar imagen desde la base de datos
    $('#cont-previu').on('click', '.deteleteimgDB', function() {
        var idImagen = $(this).attr('data-id'); // Obtener el ID de la imagen
        var element = $(this); // Guardamos el elemento actual para usarlo después en el success

        // Enviar petición AJAX para eliminar la imagen de la base de datos
        $.ajax({
            type: "POST",
            url: "../controller/adminSitios.php",
            data: { opcion: 4, idImagen: idImagen }, // Opción 4 para eliminar imagen
            beforeSend: function() {
                $("#loader").removeClass("esconder");
                $("body").addClass("hidenn");
            },
            success: function(resp) {
                try {
                    // Decodificar la respuesta JSON
                    var respuesta = JSON.parse(resp);

                    if (respuesta.codigo === 1) {
                        // Eliminar la previsualización de la imagen si la eliminación fue exitosa
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

    $(document).on('click', '.deleteEvento', function(e) {
        e.preventDefault(); // Prevenir el comportamiento predeterminado del botón

        // Obtener la fila a la que pertenece el botón
        var row = $(this).closest("tr");

        // Verificar si la fila es una "child row" en el modo responsivo
        if (row.hasClass("child")) {
            row = row.prev(); // Si es un child row, obtenemos la fila anterior (padre)
        }

        // Usamos DataTables para obtener los datos de la fila
        var data = tabla_sitios.row(row).data(); // Accedemos a la fila completa
        // Obtener el ID de la sitio
        var idSitioDelete = data.id_sitio; // Aquí tienes el ID oculto
        var opcion = 6; // La opción 6 se refiere a borrar una sitio

        // Confirmar la acción de eliminación usando SweetAlert
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Esta sitio se eliminará permanentemente!",
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
                    url: "../controller/adminSitios.php",
                    data: { opcion: opcion, idSitioDelete: idSitioDelete },
                    beforeSend: function() {
                        $("#loader").removeClass("esconder");
                        $("body").addClass("hidenn");
                    },
                    success: function(resp) {
                        $("#loader").addClass("esconder");
                        $("body").removeClass("hidenn");
                        // Procesar la respuesta del servidor
                        var respuesta = JSON.parse(resp);
                        if (respuesta.codigo === 1) {
                            // Si la eliminación fue exitosa, eliminar la fila de la tabla
                            tabla_sitios.row(row).remove().draw();
                            Swal.fire({
                                icon: "success",
                                title: "Éxito",
                                text: respuesta.mensaje
                            }); // Mensaje de éxito
                        } else {
                            // Si hubo un error, mostrar mensaje
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: respuesta.mensaje
                            }); // Mensaje de error
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $("#loader").addClass("esconder");
                        $("body").removeClass("hidenn");
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Error en la solicitud: " + textStatus + " - " + errorThrown
                        }); // Mensaje de error
                    }
                });
            }
        });
    });


});