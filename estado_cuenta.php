<?php
// includes/header.php incluye la conexión a la BD y el inicio de sesión
include 'includes/header.php';
include 'includes/navbar.php';

// Puedes incluir aquí la lógica de permisos si es necesario
// if (!check_permission('ver_estados_de_cuenta')) {
//     header("Location: dashboard.php?error=access_denied");
//     exit();
// }
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header">
            <h2 class="mb-0">Estado de Cuenta del Estudiante</h2>
        </div>
        <div class="card-body">
            <!-- 1. Buscador de Estudiantes -->
            <div class="form-group mb-4">
                <label for="buscador_estudiante">Buscar Estudiante (por nombre o email)</label>
                <input type="text" id="buscador_estudiante" class="form-control" placeholder="Escribe para buscar...">
                <div id="resultados_busqueda" class="list-group mt-2"></div>
            </div>

            <hr>

            <!-- 2. Contenedor para mostrar los resultados -->
            <div id="contenedor_estado_cuenta" class="mt-4" style="display: none;">
                
                <!-- 2.1. Información del Estudiante y Resumen Financiero -->
                <div id="info_estudiante_header" class="mb-4">
                    <h3 id="nombre_estudiante"></h3>
                    <p id="email_estudiante" class="text-muted"></p>
                </div>
                
                <div class="row text-center mb-4">
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title">Deuda Total Pendiente</h5>
                                <p class="card-text h2" id="deuda_total">¢0.00</p>
                            </div>
                        </div>
                    </div>
                    <!-- Se pueden agregar más tarjetas de resumen aquí (ej: Total Pagado) -->
                </div>

                <!-- 2.2. Pestañas de navegación para Boletas, Pagos y Arreglos -->
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="boletas-tab" data-toggle="tab" href="#boletas" role="tab" aria-controls="boletas" aria-selected="true">Boletas</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="pagos-tab" data-toggle="tab" href="#pagos" role="tab" aria-controls="pagos" aria-selected="false">Historial de Pagos</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="arreglos-tab" data-toggle="tab" href="#arreglos" role="tab" aria-controls="arreglos" aria-selected="false">Arreglos de Pago</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <!-- Contenido de la Pestaña Boletas -->
                    <div class="tab-pane fade show active" id="boletas" role="tabpanel" aria-labelledby="boletas-tab">
                        <div class="table-responsive mt-3">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>N° Boleta</th>
                                        <th>Fecha Creación</th>
                                        <th>Total</th>
                                        <th>Monto Pagado</th>
                                        <th>Saldo Pendiente</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_boletas">
                                    <!-- Las filas de las boletas se insertarán aquí dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Contenido de la Pestaña Pagos -->
                    <div class="tab-pane fade" id="pagos" role="tabpanel" aria-labelledby="pagos-tab">
                        <div class="table-responsive mt-3">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha Pago</th>
                                        <th>Monto</th>
                                        <th>Método</th>
                                        <th>Referencia</th>
                                        <th>Boleta Asociada</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_pagos">
                                    <!-- Las filas de los pagos se insertarán aquí -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Contenido de la Pestaña Arreglos -->
                    <div class="tab-pane fade" id="arreglos" role="tabpanel" aria-labelledby="arreglos-tab">
                         <div class="table-responsive mt-3">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha Creación</th>
                                        <th>Monto Acordado</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_arreglos">
                                    <!-- Las filas de los arreglos se insertarán aquí -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal para Registrar Pago -->
<div class="modal fade" id="registrarPagoModal" tabindex="-1" aria-labelledby="registrarPagoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registrarPagoModalLabel">Registrar Pago a Boleta <span id="modalBoletaNumero"></span></h5>
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
                    <button type="submit" class="btn btn-primary">Registrar Pago</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ... (rest of the JS code) ...

    // Función para pintar todos los datos en la interfaz
    function renderizarDatos(data) {
        // ... (rest of renderizarDatos function) ...

        // Renderizar tabla de boletas
        const tablaBoletas = document.getElementById('tabla_boletas');
        tablaBoletas.innerHTML = '';
        if(data.boletas.length > 0) {
            data.boletas.forEach(boleta => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${boleta.numero_boleta}</td>
                    <td>${new Date(boleta.fecha_creacion).toLocaleDateString()}</td>
                    <td>${currencyFormatter.format(boleta.total)}</td>
                    <td>${currencyFormatter.format(boleta.monto_pagado)}</td>
                    <td class="font-weight-bold">${currencyFormatter.format(boleta.saldo_pendiente)}</td>
                    <td><span class="badge badge-info">${boleta.estado}</span></td>
                    <td>
                        <button class="btn btn-sm btn-primary registrar-pago-btn"
                                data-boleta-id="${boleta.id}"
                                data-boleta-numero="${boleta.numero_boleta}"
                                data-saldo-pendiente="${boleta.saldo_pendiente}"
                                data-id-estudiante="${data.estudiante.id}"
                                data-bs-toggle="modal" data-bs-target="#registrarPagoModal">Registrar Pago</button>
                        <button class="btn btn-sm btn-secondary">Ver</button>
                    </td>
                `;
                tablaBoletas.appendChild(tr);
            });

            // Añadir event listeners a los botones de "Registrar Pago"
            tablaBoletas.querySelectorAll('.registrar-pago-btn').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('modalBoletaId').value = this.dataset.boletaId;
                    document.getElementById('modalIdEstudiante').value = this.dataset.idEstudiante;
                    document.getElementById('modalBoletaNumero').textContent = this.dataset.boletaNumero;
                    document.getElementById('modalSaldoPendiente').textContent = currencyFormatter.format(this.dataset.saldoPendiente);
                    document.getElementById('modalMontoAbono').max = parseFloat(this.dataset.saldoPendiente); // Establecer el máximo
                    document.getElementById('modalMontoAbono').value = parseFloat(this.dataset.saldoPendiente); // Valor por defecto
                });
            });

        } else {
             tablaBoletas.innerHTML = '<tr><td colspan="7" class="text-center">No hay boletas para este estudiante.</td></tr>';
        }
        
        // Renderizar tabla de pagos
        const tablaPagos = document.getElementById('tabla_pagos');
        tablaPagos.innerHTML = '';
        if(data.pagos.length > 0) {
            data.pagos.forEach(pago => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${new Date(pago.fecha_pago).toLocaleString()}</td>
                    <td>${currencyFormatter.format(pago.monto)}</td>
                    <td>${pago.metodo_pago || 'N/A'}</td>
                    <td>${pago.referencia || 'N/A'}</td>
                    <td>${pago.numero_boleta}</td>
                `;
                tablaPagos.appendChild(tr);
            });
        } else {
             tablaPagos.innerHTML = '<tr><td colspan="5" class="text-center">No hay pagos registrados para este estudiante.</td></tr>';
        }

        // Renderizar tabla de arreglos de pago
        const tablaArreglos = document.getElementById('tabla_arreglos');
        tablaArreglos.innerHTML = '';
        if(data.arreglos.length > 0) {
            data.arreglos.forEach(arreglo => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${new Date(arreglo.fecha_creacion).toLocaleDateString()}</td>
                    <td>${currencyFormatter.format(arreglo.monto_total_acordado)}</td>
                    <td><span class="badge badge-success">${arreglo.estado}</span></td>
                    <td><button class="btn btn-sm btn-info">Ver Cuotas</button></td>
                `;
                tablaArreglos.appendChild(tr);
            });
        } else {
            tablaArreglos.innerHTML = '<tr><td colspan="4" class="text-center">No hay arreglos de pago para este estudiante.</td></tr>';
        }
    }

    // Lógica para cargar estudiante desde la URL al cargar la página
    const urlParams = new URLSearchParams(window.location.search);
    const idEstudianteURL = urlParams.get('id_estudiante');

    if (idEstudianteURL) {
        seleccionarEstudiante(idEstudianteURL);
        // Opcional: Desplazar a la sección del estudiante si es necesario
        // contenedorEstadoCuenta.scrollIntoView({ behavior: 'smooth' });
    }

    // Lógica para el envío del formulario de registro de pago
    const formRegistrarPago = document.getElementById('formRegistrarPago');
    formRegistrarPago.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevenir el envío por defecto del formulario

        const boletaId = document.getElementById('modalBoletaId').value;
        const montoAbono = document.getElementById('modalMontoAbono').value;
        const fechaPago = document.getElementById('modalFechaPago').value;
        const metodoPago = document.getElementById('modalMetodoPago').value;
        const referenciaPago = document.getElementById('modalReferenciaPago').value;
        const observacionesPago = document.getElementById('modalObservacionesPago').value;
        const idEstudianteActual = document.getElementById('modalIdEstudiante').value; // Obtener el ID del estudiante del modal

        // Validaciones básicas
        if (!montoAbono || parseFloat(montoAbono) <= 0) {
            Swal.fire('Error', 'El monto del abono debe ser mayor que cero.', 'error');
            return;
        }

        // Enviar datos vía AJAX
        $.ajax({
            url: 'boleta/registrar_pago.php', // Ruta al script de backend
            type: 'POST',
            data: {
                boleta_id: boletaId,
                monto_abono: montoAbono,
                fecha_pago: fechaPago,
                metodo_pago: metodoPago,
                referencia_pago: referenciaPago,
                observaciones_pago: observacionesPago,
                id_estudiante: idEstudianteActual // Pasar el ID del estudiante
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message
                    }).then(() => {
                        // Cerrar el modal
                        const modalElement = document.getElementById('registrarPagoModal');
                        const modal = bootstrap.Modal.getInstance(modalElement); // O usar new bootstrap.Modal(modalElement).hide();
                        if (modal) {
                            modal.hide();
                        }
                        // Recargar los datos del estudiante para reflejar el nuevo pago
                        seleccionarEstudiante(idEstudianteActual); 
                        // Abrir el recibo de PDF si está disponible
                        if (response.recibo_url) {
                            window.open(response.recibo_url, '_blank');
                        }
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error de Conexión', 'Hubo un error al intentar registrar el pago.', 'error');
            }
        });
    });
});
</script>
