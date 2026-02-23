<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Permiso para ver esta página
if (!has_permission($mysqli, 'gestionar_expedientes')) {
    header("Location: ../dashboard.php?error=No tienes permiso para acceder a esta area.");
    exit;
}

$page_title = 'Generar Boleta de Pago';
require_once '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4">
    <input type="hidden" id="boleta_id_estudiante" value="">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Generar Boleta de Pago</li>
        </ol>
    </nav>
    
    <h1 class="mb-4"><i class="bi bi-receipt"></i> Generar Boleta de Pago</h1>

    <!-- PASO 1: BUSCADOR DE ESTUDIANTE -->
    <div class="card mb-4" id="user-search-card">
        <div class="card-header">
            <h5>Paso 1: Buscar y Seleccionar Estudiante</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <label for="user-search" class="form-label">Buscar por nombre, apellidos, email o cédula:</label>
                    <div class="input-group">
                        <select id="user-search" class="form-select"></select>
                        <button id="reset-user-search" class="btn btn-outline-secondary" type="button" style="display: none;">Limpiar y Buscar Otro</button>
                    </div>
                </div>
            </div>
            <div id="user-search-results" class="list-group mt-3"></div>
        </div>
    </div>

    <!-- MINI-DASHBOARD DEL ESTUDIANTE SELECCIONADO -->
    <div class="card mb-4" id="student-financial-summary-card" style="display: none;">
        <div class="card-header">
            <h5>Resumen Financiero del Estudiante Seleccionado</h5>
        </div>
        <div class="card-body">
            <h4 id="summary-student-name"></h4>
            <p><small id="summary-student-email" class="text-muted"></small></p>
            <div class="row text-center mt-3">
                <div class="col-md-6 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">Deuda Total Pendiente</h6>
                            <p class="card-text h4" id="summary-deuda-total">¢0.00</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Boletas Pendientes</h6>
                            <p class="card-text h4" id="summary-boletas-pendientes">0</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="alert alert-info mt-3" role="alert">
                Para un detalle completo, visite la sección de <a href="../estado_cuenta.php" class="alert-link">Estado de Cuenta del Estudiante</a>.
            </div>
        </div>
    </div>

    <!-- PASO 2: SELECCIONAR PERÍODO -->
    <div class="card mb-4" id="periodo-select-card" style="display: none;">
        <div class="card-header">
            <h5>Paso 2: Seleccionar Cuatrimestre y Año</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="cuatrimestre-select" class="form-label">Cuatrimestre:</label>
                    <select id="cuatrimestre-select" class="form-select"></select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="anio-select" class="form-label">Año:</label>
                    <select id="anio-select" class="form-select"></select>
                </div>
            </div>
        </div>
    </div>

    <!-- PASO 3: VISUALIZACIÓN DE LA BOLETA -->
    <div id="boleta-container" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h5 id="boleta-header">Paso 3: Revisar y Firmar Boleta de Pago</h5>
            </div>
            <div class="card-body">
                <div class="boleta-preview p-4 border rounded">
                    <!-- Encabezado de la Boleta -->
                    <div class="row mb-4">
                        <div class="col-8">
                            <img src="../imgs/logo_unela_color.png" alt="Logo UNELA" style="max-height: 80px;">
                            <h4 class="mt-2">Universidad Evangélica de las Américas</h4>
                            <p>Ced. Jurídica: 3-002-066646</p>
                        </div>
                        <div class="col-4 text-end">
                            <h2>BOLETA DE PAGO</h2>
                            <p><strong>Boleta No:</strong> <span id="boleta-numero">[Automático]</span></p>
                            <p><strong>Fecha:</strong> <?php echo date('d/m/Y'); ?></p>
                        </div>
                    </div>

                    <!-- Datos del Estudiante -->
                    <fieldset class="mb-4">
                        <legend class="h6">Datos del Estudiante</legend>
                        <p><strong>Nombre:</strong> <span id="boleta-nombre-estudiante"></span></p>
                        <p><strong>Cédula:</strong> <span id="boleta-cedula-estudiante"></span></p>
                        <p><strong>Email:</strong> <span id="boleta-email-estudiante"></span></p>
                        <p><strong>Carrera:</strong> <span id="boleta-carrera"></span></p>
                        <p><strong>Cuatrimestre:</strong> <span id="boleta-cuatrimestre"></span></p>
                    </fieldset>

                    <!-- Detalle de Cursos/Pagos -->
                    <fieldset class="mb-4">
                        <legend class="h6">Detalle de Cargos</legend>
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Materia / Concepto</th>
                                    <th>Periodo</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody id="boleta-detalle-cargos">
                                <!-- Filas de cursos se insertarán aquí dinámicamente -->
                            </tbody>
                            <tbody id="boleta-resumen-cargos">
                                <!-- Matrícula y otros cargos fijos -->
                                <tr>
                                    <td colspan="3" class="text-end">Matrícula</td>
                                    <td class="text-end"><span id="boleta-matricula">0.00</span></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Sub-Total</strong></td>
                                    <td class="text-end"><strong><span id="boleta-subtotal">0.00</span></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end">Descuento</td>
                                    <td class="text-end"><input type="number" id="boleta-descuento" class="form-control form-control-sm text-end" value="0" min="0"></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <td colspan="3" class="text-end"><strong>TOTAL GENERAL</strong></td>
                                    <td class="text-end"><strong><span id="boleta-total">0.00</span></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end">Mensualidad (4 pagos de)</td>
                                    <td class="text-end"><span id="boleta-mensualidad">0.00</span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </fieldset>

                    <!-- Sección de Firma -->
                    <fieldset>
                        <legend class="h6">Firma de Registro</legend>
                        <div class="row">
                            <div class="col-md-12">
                                <div id="signature-pad-container" class="border rounded" style="height: 200px; cursor: crosshair;">
                                    <canvas id="signature-canvas" style="width: 100%; height: 100%;"></canvas>
                                </div>
                                <div class="mt-2">
                                    <button type="button" id="clear-signature" class="btn btn-secondary btn-sm">Limpiar Firma</button>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <!-- Botones de Acción -->
                <div class="d-grid gap-2 mt-4">
                    <button type="button" id="save-boleta" class="btn btn-primary btn-lg">Guardar y Generar PDF</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE BOLETA EXISTENTE -->
    <div id="existing-boleta-card" style="display: none;">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Boleta Existente para <span id="existing-boleta-periodo"></span></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Número de Boleta:</strong> <span id="existing-boleta-numero"></span></p>
                        <p><strong>Fecha de Creación:</strong> <span id="existing-boleta-fecha-creacion"></span></p>
                        <p><strong>Monto Total:</strong> <span id="existing-boleta-total"></span></p>
                        <p><strong>Monto Pagado:</strong> <span id="existing-boleta-monto-pagado"></span></p>
                        <p><strong>Saldo Pendiente:</strong> <span id="existing-boleta-saldo-pendiente"></span></p>
                        <p><strong>Estado:</strong> <span id="existing-boleta-estado"></span></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-primary btn-lg mt-3" 
                                data-bs-toggle="modal" data-bs-target="#registrarPagoModal"
                                id="btn-abonar-existente">Abonar a la Boleta</button>
                        <button type="button" class="btn btn-secondary btn-lg mt-3" id="btn-ver-pdf-existente">Ver PDF</button>
                    </div>
                </div>
                <hr>
                <h6>Historial de Pagos</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th>Registrado Por</th>
                            </tr>
                        </thead>
                        <tbody id="existing-boleta-pagos-table">
                            <!-- Pagos se cargarán aquí -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal para Registrar Pago (Duplicado de estado_cuenta.php) -->
<div class="modal fade" id="registrarPagoModal" tabindex="-1" aria-labelledby="registrarPagoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registrarPagoModalLabel">Registrar Abono a Boleta <span id="modalBoletaNumero"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formRegistrarPago">
                    <input type="hidden" id="modalBoletaId" name="boleta_id">
                    <input type="hidden" id="modalIdEstudiante" name="id_estudiante">
                    <div class="mb-3">
                        <label for="modalMontoAbono" class="form-label">Monto del Abono</label>
                        <input type="number" step="0.01" class="form-control" id="modalMontoAbono" name="monto_abono" required>
                        <small class="text-muted">Saldo pendiente: <span id="modalSaldoPendiente"></span></small>
                    </div>
                    <div class="mb-3">
                        <label for="modalFechaPago" class="form-label">Fecha de Pago</label>
                        <input type="date" class="form-control" id="modalFechaPago" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="modalMetodoPago" class="form-label">Método de Pago</label>
                        <input type="text" class="form-control" id="modalMetodoPago" name="metodo_pago">
                    </div>
                    <div class="mb-3">
                        <label for="modalReferenciaPago" class="form-label">Referencia (Número de Factura Emitida)</label>
                        <input type="text" class="form-control" id="modalReferenciaPago" name="referencia_pago">
                    </div>
                    <div class="mb-3">
                        <label for="modalObservacionesPago" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="modalObservacionesPago" name="observaciones_pago" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Registrar Abono</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Lógica para el Signature Pad
document.addEventListener('DOMContentLoaded', (event) => {
    const canvas = document.getElementById('signature-canvas');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)' // necessary for saving image as JPEG; can be removed for PNG.
    });

    // Ajustar el tamaño del canvas al contenedor
    function resizeCanvas() {
        const ratio =  Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear(); // otherwise isEmpty() might return incorrect value
    }

    window.addEventListener("resize", resizeCanvas);
    resizeCanvas();

    // Limpiar la firma
    const clearButton = document.getElementById('clear-signature');
    clearButton.addEventListener('click', function () {
        signaturePad.clear();
    });

    // Guardar la boleta
    const saveButton = document.getElementById('save-boleta');
    saveButton.addEventListener('click', function () {
        // Temporalmente deshabilitado para pruebas
        // if (signaturePad.isEmpty()) {
        //     return alert("Por favor, provea una firma.");
        // }

        const id_estudiante = $('#boleta_id_estudiante').val();
        const total = $('#boleta-total').text();
        const signatureData = signaturePad.isEmpty() ? '' : signaturePad.toDataURL('image/png');
        const periodo = $('#periodo-select').val();
        const descuento = $('#boleta-descuento').val();

        // Desactivar botón para evitar doble envío
        saveButton.disabled = true;
        saveButton.innerText = 'Procesando...';

        $.ajax({
            url: 'guardar_boleta.php',
            type: 'POST',
            data: {
                id_estudiante: id_estudiante,
                total: total,
                signature: signatureData,
                periodo: periodo,
                descuento: descuento
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message
                    }).then(() => {
                        // Abrir el PDF en una nueva pestaña
                        window.open('../' + response.pdf_url, '_blank');
                        // Redirigir a estado_cuenta.php con el ID del estudiante
                        window.location.href = '../estado_cuenta.php?id_estudiante=' + id_estudiante;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al guardar la boleta: ' + response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'Error de conexión al intentar guardar la boleta.'
                });
            },
            complete: function() {
                // Reactivar el botón
                saveButton.disabled = false;
                saveButton.innerText = 'Guardar y Generar PDF';
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    const currencyFormatter = new Intl.NumberFormat('es-CR', {
        style: 'currency',
        currency: 'CRC'
    });

    // Inicializar Select2 para el campo de búsqueda de estudiantes
    $('#user-search').select2({
        theme: 'bootstrap-5',
        placeholder: 'Busque un estudiante por nombre, email o cédula',
        ajax: {
            url: '../get_estudiantes.php', // Ruta actualizada si es necesario
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) { return { results: data.results }; },
            cache: true
        },
        minimumInputLength: 3
    }).on('select2:select', function (e) {
        var data = e.params.data;
        if (data.id) {
            selectUser(data.id);
        }
    });

    let costoCursos = 0;
    let costoMatricula = 0;

    function selectUser(userId) {
        $('#boleta_id_estudiante').val(userId);
        $('#user-search-card').hide();
        $('#student-financial-summary-card').show();
        $('#periodo-select-card').show(); // Mostrar siempre la selección de período

        // Mostrar el botón de limpiar búsqueda
        $('#reset-user-search').show();
        
        // Cargar el resumen financiero del estudiante
        fetch(`../get_estado_cuenta.php?id_estudiante=${userId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    $('#summary-student-name').text(`${data.estudiante.nombre} ${data.estudiante.apellidos || ''}`);
                    $('#summary-student-email').text(data.estudiante.email);
                    $('#summary-deuda-total').text(currencyFormatter.format(data.resumen.deuda_total));
                    
                    // Contar boletas pendientes
                    const boletasPendientesCount = data.boletas.filter(b => b.estado === 'pendiente' || b.estado === 'pago_parcial' || b.estado === 'en_arreglo').length;
                    $('#summary-boletas-pendientes').text(boletasPendientesCount);

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al cargar resumen financiero: ' + result.message
                    });
                }
            })
            .catch(error => {
                console.error('Error en la petición fetch de resumen financiero:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'Hubo un error al cargar el resumen financiero del estudiante.'
                });
            });

        // Cargar los períodos del estudiante
        $.ajax({
            url: 'get_periodos_estudiante.php',
            type: 'GET',
            data: { id_estudiante: userId },
            dataType: 'json',
            success: function(response) {
                if(response.success && response.periodos.length > 0) {
                    const periodoSelect = $('#periodo-select');
                    periodoSelect.empty().append('<option value="">Seleccione un cuatrimestre...</option>');
                    response.periodos.forEach(periodo => {
                        periodoSelect.append($('<option>', { value: periodo, text: periodo }));
                    });
                } else {
                    const periodoSelect = $('#periodo-select');
                    periodoSelect.empty().append('<option value="">No hay cuatrimestres disponibles</option>');
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin Períodos',
                        text: 'Este estudiante no tiene cursos matriculados en ningún período.'
                    });
                    // resetBoleta(); // No resetear, solo informar y permitir seguir
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'Error al cargar los períodos del estudiante.'
                });
                // resetBoleta();
            }
        });
    }

    $('#periodo-select').on('change', function() {
        const selectedPeriodo = $(this).val();
        const idEstudiante = $('#boleta_id_estudiante').val();

        if (!selectedPeriodo) {
            $('#boleta-container').hide();
            return;
        }

        // 1. Verificar si ya existe una boleta para este estudiante y período
        $.ajax({
            url: 'check_existing_boleta.php',
            type: 'GET',
            data: { id_estudiante: idEstudiante, periodo: selectedPeriodo },
            dataType: 'json',
            success: function(checkResponse) {
                if (checkResponse.exists) {
                    // Si existe una boleta, mostrarla
                    $('#boleta-container').hide();
                    $('#existing-boleta-card').show();
                    displayExistingBoleta(checkResponse.boleta_id);
                } else {
                    // Si no existe boleta, proceder a cargar los datos para una nueva
                    $('#existing-boleta-card').hide(); // Ocultar la sección de boleta existente
                    loadBoletaData(idEstudiante, selectedPeriodo);
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Verificación',
                    text: 'Error al verificar boletas existentes.'
                });
                $('#boleta-container').hide();
            }
        });
    });

    function loadBoletaData(idEstudiante, selectedPeriodo) {
        $.ajax({
            url: 'get_boleta_data.php',
            type: 'GET',
            data: { id_estudiante: idEstudiante, periodo: selectedPeriodo },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    const userData = response.data.usuario;
                    const cursosData = response.data.cursos;
                    
                    $('#boleta-header').text(`Paso 3: Revisar Boleta para ${userData.nombre} ${userData.apellidos}`);
                    $('#boleta-nombre-estudiante').text(`${userData.nombre} ${userData.apellidos}`);
                    $('#boleta-cedula-estudiante').text(userData.cedula);
                    $('#boleta-email-estudiante').text(userData.email);

                    costoCursos = 0;
                    costoMatricula = 0;
                    $('#boleta-detalle-cargos').empty();
                    
                    if (cursosData.length > 0) {
                        const programas = [...new Set(cursosData.map(c => c.nombre_programa))].join(', ');
                        costoMatricula = parseFloat(cursosData[0].costo_matricula) || 0;
                        $('#boleta-carrera').text(programas);
                        $('#boleta-cuatrimestre').text(selectedPeriodo);

                        cursosData.forEach(curso => {
                            const precio = parseFloat(curso.precio_final) || 0;
                            const row = `<tr>
                                <td>${curso.codigo}</td>
                                <td>${curso.materia}</td>
                                <td>${curso.periodo}</td>
                                <td class="text-end">${currencyFormatter.format(precio)}</td>
                            </tr>`;
                            $('#boleta-detalle-cargos').append(row);
                            costoCursos += precio;
                        });
                    } else {
                        $('#boleta-carrera').text('N/A');
                        $('#boleta-cuatrimestre').text(selectedPeriodo);
                        $('#boleta-detalle-cargos').append(`<tr><td colspan="4" class="text-center">No se encontraron cursos para este período.</td></tr>`);
                    }

                    $('#boleta-matricula').text(currencyFormatter.format(costoMatricula));
                    recalculateTotal();
                    $('#boleta-container').show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error: ' + response.message
                    });
                    resetBoleta();
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'Error de conexión al cargar los datos de la boleta.'
                });
                resetBoleta();
            }
        });
    }

    function recalculateTotal() {
        const descuento = parseFloat($('#boleta-descuento').val()) || 0;
        const subtotal = costoCursos + costoMatricula;
        const totalGeneral = subtotal - descuento;
        const mensualidad = costoCursos / 4; 

        $('#boleta-subtotal').text(currencyFormatter.format(subtotal));
        $('#boleta-total').text(currencyFormatter.format(totalGeneral));
        $('#boleta-mensualidad').text(currencyFormatter.format(mensualidad));
    }

    $('#boleta-descuento').on('input', recalculateTotal);

    // Add an event listener to the "Abonar a la Boleta" button to pre-fill the modal
    $('#btn-abonar-existente').on('click', function() {
        const saldoPendienteText = $('#existing-boleta-saldo-pendiente').text();
        // Assuming saldoPendienteText is formatted like "¢100,000.00"
        // Remove currency symbol, thousands separator, and replace comma with dot for decimal
        const saldoPendiente = parseFloat(saldoPendienteText.replace(/[^0-9,-]+/g, "").replace('.', '').replace(',', '.'));

        $('#modalMontoAbono').val(saldoPendiente); // Pre-fill with max possible
        $('#modalSaldoPendiente').text(saldoPendienteText); // Display formatted text
    });

    // Function to display existing boleta
    function displayExistingBoleta(boletaId) {
        // Hide new boleta form
        $('#boleta-container').hide();
        // Show existing boleta card
        $('#existing-boleta-card').show();

        // Fetch boleta details
        $.ajax({
            url: 'get_boleta_details.php',
            type: 'GET',
            data: { boleta_id: boletaId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const boleta = response.boleta;
                    const pagos = response.pagos;

                    // Populate boleta details
                    $('#existing-boleta-periodo').text(boleta.periodo || 'N/A');
                    $('#existing-boleta-numero').text(boleta.numero_boleta || 'N/A');
                    $('#existing-boleta-fecha-creacion').text(new Date(boleta.fecha_creacion).toLocaleDateString());
                    $('#existing-boleta-total').text(currencyFormatter.format(parseFloat(boleta.total)));
                    $('#existing-boleta-monto-pagado').text(currencyFormatter.format(parseFloat(boleta.monto_pagado)));
                    $('#existing-boleta-saldo-pendiente').text(currencyFormatter.format(parseFloat(boleta.saldo_pendiente)));
                    $('#existing-boleta-estado').text(boleta.estado);

                    // Populate payments table
                    const pagosTableBody = $('#existing-boleta-pagos-table');
                    pagosTableBody.empty();
                    if (pagos.length > 0) {
                        pagos.forEach(pago => {
                            const registradoPor = pago.registrado_por_nombre ? `${pago.registrado_por_nombre} ${pago.registrado_por_apellidos || ''}` : 'N/A';
                            const row = `<tr>
                                <td>${new Date(pago.fecha_pago).toLocaleDateString()}</td>
                                <td>${currencyFormatter.format(parseFloat(pago.monto))}</td>
                                <td>${pago.metodo_pago || 'N/A'}</td>
                                <td>${pago.referencia || 'N/A'}</td>
                                <td>${registradoPor}</td>
                            </tr>`;
                            pagosTableBody.append(row);
                        });
                    } else {
                        pagosTableBody.append('<tr><td colspan="5" class="text-center">No hay pagos registrados para esta boleta.</td></tr>');
                    }

                    // Set data for payment modal
                    $('#modalBoletaId').val(boleta.id);
                    $('#modalIdEstudiante').val(boleta.id_estudiante);
                    $('#modalBoletaNumero').text(boleta.numero_boleta);
                    $('#modalMontoAbono').val(''); // Clear previous amount
                    $('#modalSaldoPendiente').text(currencyFormatter.format(parseFloat(boleta.saldo_pendiente)));
                    $('#btn-ver-pdf-existente').attr('onclick', `window.open('../${boleta.ruta_pdf}', '_blank');`);


                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al cargar detalles de la boleta: ' + response.message
                    });
                    $('#existing-boleta-card').hide(); // Hide if error
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'Error de conexión al intentar cargar los detalles de la boleta.'
                });
                $('#existing-boleta-card').hide(); // Hide if error
            }
        });
    }

    function resetBoleta() {
        $('#user-search-card').show();
        $('#student-financial-summary-card').hide(); // Ocultar el resumen
        $('#periodo-select-card').hide();
        $('#boleta-container').hide();
        $('#existing-boleta-card').hide(); // Ocultar la sección de boleta existente
        $('#user-search').val(null).trigger('change'); // Limpiar Select2
        $('#periodo-select').empty();
        $('#boleta-descuento').val(0);
        costoCursos = 0;
        costoMatricula = 0;
        $('#reset-user-search').hide(); // Ocultar botón de limpiar
    }

    $('#reset-user-search').click(function() {
        resetBoleta();
        $('#user-search').select2('open'); // Reabrir el Select2 para buscar
    });
});
</script>

<?php include '../includes/footer.php'; ?>
