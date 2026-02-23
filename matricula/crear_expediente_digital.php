<?php
require_once '../includes/header.php';
require_once '../includes/permissions.php';
include '../includes/navbar.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="gestionar_expedientes.php">Expedientes Digitales</a></li>
            <li class="breadcrumb-item active" aria-current="page">Crear Expediente Digital</li>
        </ol>
    </nav>
    
    <h1 class="mb-4"><i class="bi bi-file-earmark-person"></i> Crear Expediente Digital</h1>
    
    <!-- PASO 1: BUSCADOR DE USUARIO -->
    <div class="card mb-4" id="user-search-card">
        <div class="card-header">
            <h5>Paso 1: Buscar y Seleccionar Estudiante</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <label for="user-search" class="form-label">Buscar por nombre, apellidos, email o cédula:</label>
                    <div class="input-group">
                        <input type="text" id="user-search" class="form-control" placeholder="Escriba al menos 3 caracteres...">
                        <button id="reset-user-search" class="btn btn-outline-secondary" type="button" style="display: none;">Cambiar Usuario</button>
                    </div>
                </div>
            </div>
            <div id="user-search-results" class="list-group mt-3"></div>
        </div>
    </div>

    <!-- PASOS 2 y 3: FORMULARIO DE DATOS Y CARGA DE DOCUMENTOS -->
    <form id="expediente-form" style="display: none;" method="post" action="procesar_expediente.php" enctype="multipart/form-data">
        <input type="hidden" id="student_id" name="id_usuario" value="">
        
        <!-- PASO 2: FORMULARIO DE DATOS -->
        <div class="card mb-4 bg-light">
            <div class="card-header">
                <h5 id="data-form-header">Paso 2: Completar Información del Expediente</h5>
            </div>
            <div class="card-body">
                
                <fieldset class="mb-4">
                    <legend class="h6">Información de Carrera</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Grado a matricular:</label><br>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="grado_a_matricular" value="Bachillerato"><label class="form-check-label">Bachillerato</label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="grado_a_matricular" value="Licenciatura"><label class="form-check-label">Licenciatura</label></div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="grado_a_matricular" id="radio-maestria" value="Maestría">
                                <label class="form-check-label" for="radio-maestria">Maestría</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="grado_a_matricular" id="radio-tecnico" value="Técnico">
                                <label class="form-check-label" for="radio-tecnico">Técnico</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="especialidad_deseada" class="form-label">Especialidad deseada:</label>
                            <input type="text" name="especialidad_deseada" id="especialidad_deseada" class="form-control">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mb-4">
                    <legend class="h6">Información Personal</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Género:</label><br>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="genero" value="Masculino"><label class="form-check-label">Masculino</label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="genero" value="Femenino"><label class="form-check-label">Femenino</label></div>
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento:</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="lugar_nacimiento" class="form-label">Lugar de nacimiento:</label><input type="text" name="lugar_nacimiento" id="lugar_nacimiento" class="form-control"></div>
                        <?php require_once '../includes/paises.php'; ?>
                        <div class="col-md-6 mb-3">
                            <label for="nacionalidad" class="form-label">Nacionalidad:</label>
                            <select name="nacionalidad" id="nacionalidad" class="form-select">
                                <option value="">Seleccione un país...</option>
                                <?php foreach ($paises as $pais): ?>
                                    <option value="<?php echo htmlspecialchars($pais); ?>" <?php echo ($pais === 'Costa Rica') ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pais); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="cedula_residencia" class="form-label">No. de cédula / Residencia:</label><input type="text" name="cedula_residencia" id="cedula_residencia" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Estado Civil:</label><br><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="estado_civil" value="Soltero"><label class="form-check-label">Soltero</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="estado_civil" value="Casado"><label class="form-check-label">Casado</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="estado_civil" value="Divorciado"><label class="form-check-label">Divorciado</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="estado_civil" value="Viudo"><label class="form-check-label">Viudo</label></div></div>
                    </div>
                </fieldset>

                <fieldset class="mb-4">
                    <legend class="h6">Información Domiciliar</legend>
                    <div class="mb-3"><label for="domicilio_direccion" class="form-label">Dirección de domicilio:</label><textarea name="domicilio_direccion" id="domicilio_direccion" class="form-control" rows="2"></textarea></div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label for="domicilio_provincia" class="form-label">Provincia:</label><input type="text" name="domicilio_provincia" id="domicilio_provincia" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label for="domicilio_canton" class="form-label">Cantón:</label><input type="text" name="domicilio_canton" id="domicilio_canton" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label for="domicilio_distrito" class="form-label">Distrito:</label><input type="text" name="domicilio_distrito" id="domicilio_distrito" class="form-control"></div>
                    </div>
                </fieldset>

                <fieldset class="mb-4">
                    <legend class="h6">Información de Contacto</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="contacto_tel_habitacion" class="form-label">Teléfono de habitación:</label><input type="tel" name="contacto_tel_habitacion" id="contacto_tel_habitacion" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label for="contacto_tel_celular" class="form-label">Teléfono celular:</label><input type="tel" name="contacto_tel_celular" id="contacto_tel_celular" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="contacto_otro_emergencias" class="form-label">Otro (emergencias):</label><input type="tel" name="contacto_otro_emergencias" id="contacto_otro_emergencias" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label for="email" class="form-label">Correo electrónico:</label><input type="email" name="email" id="email" class="form-control" readonly></div>
                    </div>
                </fieldset>
                
                <fieldset class="mb-4">
                    <legend class="h6">Procedencia</legend>
                    <p class="text-muted">Educación Secundaria</p>
                    <div class="row"><div class="col-md-12 mb-3"><label for="procedencia_secundaria_institucion" class="form-label">Institución secundaria de procedencia:</label><input type="text" name="procedencia_secundaria_institucion" class="form-control"></div></div>
                    <div class="row"><div class="col-md-6 mb-3"><label for="procedencia_secundaria_ano_graduacion" class="form-label">Año de graduación:</label><input type="text" name="procedencia_secundaria_ano_graduacion" class="form-control"></div><div class="col-md-6 mb-3"><label for="procedencia_secundaria_grado_obtenido" class="form-label">Grado académico obtenido:</label><input type="text" name="procedencia_secundaria_grado_obtenido" class="form-control"></div></div>
                    <hr>
                    <p class="text-muted">Educación Universitaria (si aplica)</p>
                    <div class="row"><div class="col-md-12 mb-3"><label for="procedencia_universidad" class="form-label">Universidad de procedencia:</label><input type="text" name="procedencia_universidad" class="form-control"></div></div>
                    <div class="row"><div class="col-md-4 mb-3"><label for="procedencia_universidad_ano_graduacion" class="form-label">Año de graduación:</label><input type="text" name="procedencia_universidad_ano_graduacion" class="form-control"></div><div class="col-md-4 mb-3"><label for="procedencia_universidad_especialidad" class="form-label">Especialidad:</label><input type="text" name="procedencia_universidad_especialidad" class="form-control"></div><div class="col-md-4 mb-3"><label class="form-label">Grado obtenido:</label><br><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="procedencia_universidad_grado_obtenido" value="Bachillerato"><label class="form-check-label">Bachillerato</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="procedencia_universidad_grado_obtenido" value="Licenciatura"><label class="form-check-label">Licenciatura</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="procedencia_universidad_grado_obtenido" value="Maestría"><label class="form-check-label">Maestría</label></div></div></div>
                </fieldset>

                <fieldset class="mb-4">
                    <legend class="h6">Información Laboral</legend>
                    <div class="row"><div class="col-md-8 mb-3"><label for="laboral_institucion" class="form-label">Institución donde labora:</label><input type="text" name="laboral_institucion" class="form-control"></div><div class="col-md-4 mb-3"><label for="laboral_fecha_ingreso" class="form-label">Fecha de ingreso:</label><input type="date" name="laboral_fecha_ingreso" class="form-control"></div></div>
                    <div class="row"><div class="col-md-6 mb-3"><label for="laboral_puesto" class="form-label">Puesto que desempeña:</label><input type="text" name="laboral_puesto" class="form-control"></div><div class="col-md-6 mb-3"><label for="laboral_telefono" class="form-label">Teléfono:</label><div class="input-group"><input type="tel" name="laboral_telefono" class="form-control"><span class="input-group-text">Ext:</span><input type="text" name="laboral_extension" class="form-control"></div></div></div>
                    <div class="row"><div class="col-md-6 mb-3"><label for="laboral_fax" class="form-label">Fax:</label><input type="tel" name="laboral_fax" class="form-control"></div><div class="col-md-6 mb-3"><label for="laboral_correo_electronico" class="form-label">Correo electrónico:</label><input type="email" name="laboral_correo_electronico" class="form-control"></div></div>
                </fieldset>
            </div>
        </div>

        <!-- PASO 3: CARGA DE DOCUMENTOS -->
        <div class="card">
            <div class="card-header">
                <h5>Paso 3: Cargar Documentos</h5>
            </div>
            <div class="card-body">
                <div class="document-upload-row border p-3 mb-3 rounded"><h6>1. Formulario de Matrícula (PDF)</h6><div class="input-group"><input type="file" class="form-control" name="file_matricula"></div></div>
                <div class="document-upload-row border p-3 mb-3 rounded"><h6>2. Documento de Identidad (PDF o Imagen)</h6><div class="input-group"><input type="file" class="form-control" name="file_identidad"></div></div>
                <div class="document-upload-row border p-3 mb-3 rounded"><h6>3. Títulos y Certificaciones (Opcional, puede subir varios)</h6><div class="input-group"><input type="file" class="form-control" name="file_titulos[]" multiple></div></div>
                <div class="document-upload-row border p-3 mb-3 rounded"><h6>4. Fotografía tamaño pasaporte</h6><div class="input-group"><input type="file" class="form-control" name="file_fotografia"></div></div>
            </div>
        </div>

        <!-- BOTÓN DE FINALIZACIÓN -->
        <div class="d-grid gap-2 mt-4">
            <button type="submit" id="finalize-button" class="btn btn-primary btn-lg">Finalizar y Crear Expediente</button>
        </div>
        <div id="finalize-status" class="mt-2"></div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('user-search');
    const searchResults = document.getElementById('user-search-results');
    const resetUserBtn = document.getElementById('reset-user-search');
    const expedienteForm = document.getElementById('expediente-form');
    const studentIdInput = document.getElementById('student_id');
    const dataFormHeader = document.getElementById('data-form-header');
    const emailInput = document.getElementById('email');
    let searchTimeout;

    searchInput.addEventListener('keyup', function() {
        clearTimeout(searchTimeout);
        const query = searchInput.value;
        if (query.length < 3) {
            searchResults.innerHTML = '';
            return;
        }
        searchTimeout = setTimeout(() => {
            fetch(`buscar_usuarios.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(user => {
                            const userItem = document.createElement('a');
                            userItem.href = '#';
                            userItem.className = 'list-group-item list-group-item-action';
                            userItem.innerHTML = `<strong>${user.nombre} ${user.apellidos || ''}</strong> (${user.email})`;
                            userItem.addEventListener('click', e => {
                                e.preventDefault();
                                selectUser(user.id, user.nombre, user.apellidos, user.email);
                            });
                            searchResults.appendChild(userItem);
                        });
                    } else {
                        searchResults.innerHTML = '<div class="list-group-item">No se encontraron usuarios.</div>';
                    }
                });
        }, 300);
    });

    function selectUser(userId, userName, userApellidos, userEmail) {
        const nombreCompleto = `${userName} ${userApellidos || ''}`.trim();
        studentIdInput.value = userId;
        dataFormHeader.innerText = `Paso 2: Completar Información para ${nombreCompleto}`;
        emailInput.value = userEmail; // Autocompletar email
        expedienteForm.style.display = 'block';
        searchResults.innerHTML = '';
        searchInput.value = nombreCompleto;
        searchInput.disabled = true;
        resetUserBtn.style.display = 'inline-block';
    }

    resetUserBtn.addEventListener('click', () => window.location.reload());

    // El submit se maneja con el action y method del form, ya no se necesita JS para el submit.
});
</script>

<?php
include '../includes/footer.php';
?>
