<?php
include("php/conexion.php");

// Obtener ID de persona
if (!isset($_GET["nik"])) {
    header("Location: indexPersona.php");
    exit;
}
$nik = mysqli_real_escape_string($con, strip_tags($_GET["nik"], ENT_QUOTES));

// Obtener datos de persona
$sql = mysqli_query($con, "SELECT * FROM persona WHERE idpersona='$nik'");
if (mysqli_num_rows($sql) == 0) {
    echo '<div class="alert alert-danger">No se encontró la persona.</div>';
    exit;
}
$row = mysqli_fetch_assoc($sql);

// Obtener si es personal
$isPersonal = false;
$personalData = [];
$personal_query = mysqli_query($con, "SELECT * FROM personal WHERE persona_idpersona='$nik'");
if ($personal_query && mysqli_num_rows($personal_query) > 0) {
    $isPersonal = true;
    $personalData = mysqli_fetch_assoc($personal_query);
}

// Obtener títulos asociados si es personal
$titulosPersonal = [];
if ($isPersonal) {
    $idpersonal = $personalData['idpersonal'];
    $titulos_query = mysqli_query($con, "
        SELECT t.titulo
        FROM titulos_personal tp
        JOIN titulos t ON tp.titulos_idtitulos = t.idtitulos
        WHERE tp.personal_idpersonal = '$idpersonal' AND tp.personal_persona_idpersona = '$nik'
    ");
    while ($rowTitulo = mysqli_fetch_assoc($titulos_query)) {
        $titulosPersonal[] = $rowTitulo['titulo'];
    }
}

// Obtener si es alumno
$isAlumno = false;
$alumnoData = [];
$alumno_query = mysqli_query($con, "SELECT * FROM alumnos WHERE persona_idpersona='$nik'");
if ($alumno_query && mysqli_num_rows($alumno_query) > 0) {
    $isAlumno = true;
    $alumnoData = mysqli_fetch_assoc($alumno_query);
}

// Obtener si es tutor
$isTutor = false;
$tutorData = [];
$tutor_query = mysqli_query($con, "SELECT * FROM tutores WHERE persona_idpersona='$nik'");
if ($tutor_query && mysqli_num_rows($tutor_query) > 0) {
    $isTutor = true;
    $tutorData = mysqli_fetch_assoc($tutor_query);
}

// Obtener cargos
$cargos = [];
$cargosAsignados = [];
$cargos_query = mysqli_query($con, "SELECT idcargos, cargo FROM cargos ORDER BY cargo ASC");
while ($cargo_row = mysqli_fetch_assoc($cargos_query)) {
    $cargos[] = $cargo_row;
}
if ($isPersonal) {
    $cargos_asignados_query = mysqli_query($con, "SELECT cargos_idcargos FROM personal_has_cargos WHERE personal_idpersonal = {$personalData['idpersonal']}");
    while ($rowCargo = mysqli_fetch_assoc($cargos_asignados_query)) {
        $cargosAsignados[] = $rowCargo['cargos_idcargos'];
    }
}

// Obtener alumnos ya asociados al tutor
$alumnosAsociados = [];
if ($isTutor) {
    $asociados_query = mysqli_query($con, "SELECT alumnos_persona_idpersona FROM alumnos_has_tutores WHERE tutores_idtutores = '{$tutorData['idtutores']}' AND tutores_persona_idpersona = '{$tutorData['persona_idpersona']}'");
    while ($rowAsociado = mysqli_fetch_assoc($asociados_query)) {
        $alumnosAsociados[] = $rowAsociado['alumnos_persona_idpersona'];
    }
}

// Obtener alumnos para asociar al tutor (solo los disponibles o ya asignados a este tutor)
$alumnos = [];
if ($isTutor) {
    $alumnos_query = mysqli_query($con, "
        SELECT alumnos.idalumno, alumnos.persona_idpersona, persona.nombre, persona.apellido
        FROM alumnos
        JOIN persona ON alumnos.persona_idpersona = persona.idpersona
        WHERE alumnos.persona_idpersona NOT IN (
            SELECT alumnos_persona_idpersona
            FROM alumnos_has_tutores
            WHERE tutores_persona_idpersona != '{$tutorData['persona_idpersona']}'
        )
        ORDER BY persona.apellido ASC
    ");
} else {
    $alumnos_query = mysqli_query($con, "
        SELECT alumnos.idalumno, alumnos.persona_idpersona, persona.nombre, persona.apellido
        FROM alumnos
        JOIN persona ON alumnos.persona_idpersona = persona.idpersona
        WHERE alumnos.persona_idpersona NOT IN (
            SELECT alumnos_persona_idpersona
            FROM alumnos_has_tutores
        )
        ORDER BY persona.apellido ASC
    ");
}
while ($alumno_row = mysqli_fetch_assoc($alumnos_query)) {
    $alumnos[] = $alumno_row;
}

// Procesar formulario
if (isset($_POST['save'])) {
    $success = true;
    $errorMsg = "";

    // Actualizar persona
    $Nombres   = mysqli_real_escape_string($con, strip_tags($_POST["nombre"], ENT_QUOTES));
    $Apellidos = mysqli_real_escape_string($con, strip_tags($_POST["apellido"], ENT_QUOTES));
    $Localidad = mysqli_real_escape_string($con, strip_tags($_POST["direccion"], ENT_QUOTES));
    $Telefono  = mysqli_real_escape_string($con, strip_tags($_POST["telefono"], ENT_QUOTES));
    $Nacimiento = mysqli_real_escape_string($con, strip_tags($_POST["fecha_nac"], ENT_QUOTES));
    $Dni       = mysqli_real_escape_string($con, strip_tags($_POST["dni"], ENT_QUOTES));
    $Correo    = mysqli_real_escape_string($con, strip_tags($_POST["email"], ENT_QUOTES));
    if (!mysqli_query($con, "UPDATE persona SET nombre='$Nombres', apellido='$Apellidos', direccion='$Localidad', fecha_nac='$Nacimiento', telefono='$Telefono', dni='$Dni', email='$Correo' WHERE idpersona='$nik'")) {
        $success = false;
        $errorMsg .= "Error al actualizar datos de la persona.";
    }

    // Si es personal, actualizar datos de personal y títulos
    if (isset($_POST['es_personal'])) {
        $FechaInicio = mysqli_real_escape_string($con, strip_tags($_POST["fecha_inicio"], ENT_QUOTES));
        if ($isPersonal) {
            $idpersonal = $personalData['idpersonal'];
            if (!mysqli_query($con, "UPDATE personal SET fecha_inicio='$FechaInicio' WHERE idpersonal='$idpersonal'")) {
                $success = false;
                $errorMsg .= "Error al actualizar datos de personal.";
            }
            mysqli_query($con, "DELETE FROM personal_has_cargos WHERE personal_idpersonal = $idpersonal");
            if (!empty($_POST['cargos'])) {
                foreach ($_POST['cargos'] as $idcargo) {
                    $idcargo = intval($idcargo);
                    if (!mysqli_query($con, "INSERT INTO personal_has_cargos (personal_idpersonal, cargos_idcargos) VALUES ('$idpersonal', '$idcargo')")) {
                        $success = false;
                        $errorMsg .= "Error al guardar cargos.";
                    }
                }
            }
            // Actualizar títulos: eliminar y volver a insertar
            mysqli_query($con, "DELETE FROM titulos_personal WHERE personal_idpersonal = '$idpersonal' AND personal_persona_idpersona = '$nik'");
            if (!empty($_POST['titulo'])) {
                foreach ($_POST['titulo'] as $titulo) {
                    $titulo = mysqli_real_escape_string($con, strip_tags($titulo, ENT_QUOTES));
                    if ($titulo != "") {
                        if (!mysqli_query($con, "INSERT INTO titulos (titulo) VALUES ('$titulo')")) {
                            $success = false;
                            $errorMsg .= "Error al guardar título.";
                        }
                        $idtitulo = mysqli_insert_id($con);
                        if (!mysqli_query($con, "INSERT INTO titulos_personal (titulos_idtitulos, personal_idpersonal, personal_persona_idpersona) VALUES ('$idtitulo', '$idpersonal', '$nik')")) {
                            $success = false;
                            $errorMsg .= "Error al asociar título.";
                        }
                    }
                }
            }
        } else {
            if (!mysqli_query($con, "INSERT INTO personal (persona_idpersona, fecha_inicio) VALUES ('$nik', '$FechaInicio')")) {
                $success = false;
                $errorMsg .= "Error al guardar datos de personal.";
            }
            $idpersonal = mysqli_insert_id($con);
            if (!empty($_POST['cargos'])) {
                foreach ($_POST['cargos'] as $idcargo) {
                    $idcargo = intval($idcargo);
                    if (!mysqli_query($con, "INSERT INTO personal_has_cargos (personal_idpersonal, cargos_idcargos) VALUES ('$idpersonal', '$idcargo')")) {
                        $success = false;
                        $errorMsg .= "Error al guardar cargos.";
                    }
                }
            }
            // Insertar títulos
            if (!empty($_POST['titulo'])) {
                foreach ($_POST['titulo'] as $titulo) {
                    $titulo = mysqli_real_escape_string($con, strip_tags($titulo, ENT_QUOTES));
                    if ($titulo != "") {
                        if (!mysqli_query($con, "INSERT INTO titulos (titulo) VALUES ('$titulo')")) {
                            $success = false;
                            $errorMsg .= "Error al guardar título.<br>";
                        }
                        $idtitulo = mysqli_insert_id($con);
                        if (!mysqli_query($con, "INSERT INTO titulos_personal (titulos_idtitulos, personal_idpersonal, personal_persona_idpersona) VALUES ('$idtitulo', '$idpersonal', '$nik')")) {
                            $success = false;
                            $errorMsg .= "Error al asociar título.";
                        }
                    }
                }
            }
        }
    }

    // Si es alumno, actualizar datos de alumno
    if (isset($_POST['es_alumno'])) {
        $Legajo = mysqli_real_escape_string($con, strip_tags($_POST["legajo"], ENT_QUOTES));
        $Matriz = mysqli_real_escape_string($con, strip_tags($_POST["libro_matriz"], ENT_QUOTES));
        $Folio = mysqli_real_escape_string($con, strip_tags($_POST["folio"], ENT_QUOTES));
        if ($isAlumno) {
            $idalumno = $alumnoData['idalumno'];
            if (!mysqli_query($con, "UPDATE alumnos SET legajo='$Legajo', libro_matriz='$Matriz', folio='$Folio' WHERE idalumno='$idalumno'")) {
                $success = false;
                $errorMsg .= "Error al actualizar datos de alumno.<br>";
            }
        } else {
            if (!mysqli_query($con, "INSERT INTO alumnos (persona_idpersona, legajo, libro_matriz, folio) VALUES ('$nik', '$Legajo', '$Matriz', '$Folio')")) {
                $success = false;
                $errorMsg .= "Error al guardar datos de alumno.<br>";
            }
        }
    }

    // Si es tutor, actualizar datos de tutor y asociaciones
    if (isset($_POST['es_tutor'])) {
        if ($isTutor) {
            $idtutor = $tutorData['idtutores'];
            $idpersona_tutor = $tutorData['persona_idpersona'];
        } else {
            if (!mysqli_query($con, "INSERT INTO tutores (persona_idpersona) VALUES ('$nik')")) {
                $success = false;
                $errorMsg .= "Error al guardar datos de tutor.<br>";
            }
            $idtutor = mysqli_insert_id($con);
            $idpersona_tutor = $nik;
        }
        // Eliminar asociaciones previas
        mysqli_query($con, "DELETE FROM alumnos_has_tutores WHERE tutores_idtutores = '$idtutor' AND tutores_persona_idpersona = '$idpersona_tutor'");
        // Asociar alumnos seleccionados
        if (!empty($_POST['alumnos'])) {
            foreach ($_POST['alumnos'] as $alumno_persona_id) {
                $alumno_query = mysqli_query($con, "SELECT idalumno FROM alumnos WHERE persona_idpersona = '$alumno_persona_id'");
                if ($alumno_row = mysqli_fetch_assoc($alumno_query)) {
                    $idalumno = $alumno_row['idalumno'];
                    if (!mysqli_query($con, "INSERT INTO alumnos_has_tutores (alumnos_idalumno, alumnos_persona_idpersona, tutores_idtutores, tutores_persona_idpersona) VALUES ('$idalumno', '$alumno_persona_id', '$idtutor', '$idpersona_tutor')")) {
                        $success = false;
                        $errorMsg .= "Error al asociar alumno al tutor.<br>";
                    }
                }
            }
        }
    }

    if ($success) {
        echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Bien hecho! Los datos han sido guardados con éxito.</div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error. ' . $errorMsg . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Editar Persona</title>
    <link rel="stylesheet" href="assets/css/fontawesome.css" />
    <link rel="stylesheet" href="assets/css/templatemo-grad-school.css" />
    <link rel="stylesheet" href="assets/css/owl.css" />
    <link rel="stylesheet" href="assets/css/lightbox.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/select2.min.css" />
    <style>
        .content {
            margin-top: 80px;
        }

        .contenedor {
            display: grid;
            place-items: center;
            min-height: 50px;
        }

        .centrado {
            width: 100%;
            max-width: 550px;
            margin: 20px auto;
            padding: 20px;
        }

        .form-control {
            width: 100%;
            max-width: 500px;
        }

        .btn-custom {
            width: 220px;
            height: 35px;
        }

        .alert {
            margin-top: 90px;
            z-index: 9999;
            position: relative;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-default navbar-fixed-top">
        <?php include("nav.php"); ?>
    </nav>
    <div class="container">
        <div class="content">
            <div class="contenedor">
                <div class="centrado">
                    <form class="form-horizontal" action="" method="post" autocomplete="off">
                        <!-- Checkboxes para tipo de persona -->
                        <div class="form-group">
                            <div class="col-sm-12" style="display: flex; gap: 30px;">
                                <label><input type="checkbox" name="es_personal" id="es_personal" value="1" <?php if ($isPersonal) echo 'checked'; ?> onchange="mostrarCampos()"> Personal</label>
                                <label><input type="checkbox" name="es_alumno" id="es_alumno" value="1" <?php if ($isAlumno) echo 'checked'; ?> onchange="mostrarCampos()"> Alumno</label>
                                <label><input type="checkbox" name="es_tutor" id="es_tutor" value="1" <?php if ($isTutor) echo 'checked'; ?> onchange="mostrarCampos()"> Tutor</label>
                            </div>
                        </div>
                        <!-- Información General de la persona -->
                        <div class="form-group">
                            <label>Nombres:</label>
                            <div class="col-sm-6">
                                <input type="text" name="nombre" class="form-control" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras permitidas" value="<?php echo htmlspecialchars($row['nombre']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Apellidos:</label>
                            <div class="col-sm-6">
                                <input type="text" name="apellido" class="form-control" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras permitidas" value="<?php echo htmlspecialchars($row['apellido']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>DNI:</label>
                            <div class="col-sm-6">
                                <input type="text" name="dni" class="form-control" required minlength="8" maxlength="8" pattern="\d{8}" title="Solo 8 números permitidos" value="<?php echo htmlspecialchars($row['dni']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Nacimiento:</label>
                            <div class="col-sm-6">
                                <input type="getdate()" name="fecha_nac" class="form-control" required value="<?php echo htmlspecialchars($row['fecha_nac']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Dirección:</label>
                            <div class="col-sm-6">
                                <input type="text" name="direccion" class="form-control" required value="<?php echo htmlspecialchars($row['direccion']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Número de Telefono:</label>
                            <div class="col-sm-6">
                                <input type="text" name="telefono" class="form-control" maxlength="10" pattern="\d{10}" title="Solo 10 números permitidos" value="<?php echo htmlspecialchars($row['telefono']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Correo Electrónico:</label>
                            <div class="col-sm-6">
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>" />
                            </div>
                        </div>
                        <!-- Campos adicionales para Personal -->
                        <div id="campos_personal" style="display:none;">
                            <div class="label">Títulos del Personal:</div>
                            <div class="form-group" id="titulo-group">
                                <?php
                                if (!empty($titulosPersonal)) {
                                    foreach ($titulosPersonal as $i => $titulo) { ?>
                                        <div class="col-sm-6" style="display: flex; gap: 10px; margin-bottom: 10px;">
                                            <input type="text" name="titulo[]" class="form-control" placeholder="Titulo" value="<?php echo htmlspecialchars($titulo); ?>">
                                            <?php if ($i == 0) { ?>
                                                <button type="button" id="add-titulo" style="width:35px;height:35px;" title="Añadir otro título">+</button>
                                            <?php } ?>
                                        </div>
                                    <?php }
                                } else { ?>
                                    <div class="col-sm-6" style="display: flex; gap: 10px;">
                                        <input type="text" name="titulo[]" class="form-control" placeholder="Titulo">
                                        <button type="button" id="add-titulo" style="width:35px;height:35px;" title="Añadir otro título">+</button>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-6">
                                    <input type="getdate()" name="fecha_inicio" class="form-control" placeholder="Fecha de Inicio" value="<?php echo $isPersonal ? htmlspecialchars($personalData['fecha_inicio']) : ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-6">
                                    <label>Cargos:</label><br>
                                    <?php foreach ($cargos as $cargo) { ?>
                                        <label>
                                            <input type="checkbox" name="cargos[]" value="<?php echo $cargo['idcargos']; ?>" <?php echo in_array($cargo['idcargos'], $cargosAsignados) ? 'checked' : ''; ?>>
                                            <?php echo htmlspecialchars($cargo['cargo']); ?>
                                        </label><br>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var addBtn = document.getElementById('add-titulo');
                                if (addBtn) {
                                    addBtn.addEventListener('click', function() {
                                        const group = document.getElementById('titulo-group');
                                        const div = document.createElement('div');
                                        div.className = "col-sm-6";
                                        div.style.display = "flex";
                                        div.style.gap = "10px";
                                        div.style.marginTop = "10px";
                                        div.innerHTML = '<input type="text" name="titulo[]" class="form-control" placeholder="Titulo">';
                                        group.appendChild(div);
                                    });
                                }
                            });
                        </script>
                        <!-- Campos adicionales para Alumnos -->
                        <div id="campos_alumno" style="display:none;">
                            <div class="form-group">
                                <label>Número de Legajo:</label>
                                <div class="col-sm-6">
                                    <input type="text" name="legajo" class="form-control" value="<?php echo $isAlumno ? htmlspecialchars($alumnoData['legajo']) : ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Libro Matríz:</label>
                                <div class="col-sm-6">
                                    <input type="text" name="libro_matriz" class="form-control" value="<?php echo $isAlumno ? htmlspecialchars($alumnoData['libro_matriz']) : ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Número de Folio:</label>
                                <div class="col-sm-6">
                                    <input type="text" name="folio" class="form-control" placeholder="Folio" value="<?php echo $isAlumno ? htmlspecialchars($alumnoData['folio']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        <!-- Campos adicionales para Tutores -->
                        <div id="campos_tutor" style="display:none;">
                            <div class="form-group">
                                <div class="col-sm-6">
                                    <label>Seleccionar alumnos asociados al tutor:</label>
                                    <select name="alumnos[]" class="form-control" multiple>
                                        <?php foreach ($alumnos as $alumno) { ?>
                                            <option value="<?php echo $alumno['persona_idpersona']; ?>" <?php echo in_array($alumno['persona_idpersona'], $alumnosAsociados) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($alumno['apellido'] . ', ' . $alumno['nombre']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <small class="form-text text-muted">Puedes seleccionar uno o varios alumnos (Ctrl+click para varios).</small>
                                </div>
                            </div>
                        </div>
                        <!-- Botones de acción -->
                        <div class="form-group">
                            <div class="col-sm-6">
                                <input type="submit" name="save" class="btn btn-sm btn-primary btn-custom" value="Guardar datos">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-6">
                                <a href="indexPersona.php" class="btn btn-sm btn-danger btn-custom">Cancelar</a>
                            </div>
                        </div>
                    </form>
                    <script>
                        function mostrarCampos() {
                            const personalCheckbox = document.getElementById('es_personal');
                            const alumnoCheckbox = document.getElementById('es_alumno');
                            const tutorCheckbox = document.getElementById('es_tutor');

                            document.getElementById('campos_personal').style.display = personalCheckbox.checked ? 'block' : 'none';
                            document.getElementById('campos_alumno').style.display = alumnoCheckbox.checked ? 'block' : 'none';
                            document.getElementById('campos_tutor').style.display = tutorCheckbox.checked ? 'block' : 'none';

                            // Validación para evitar que alumno se marque con otro tipo
                            if (alumnoCheckbox.checked) {
                                if (personalCheckbox.checked) personalCheckbox.checked = false;
                                if (tutorCheckbox.checked) tutorCheckbox.checked = false;
                                personalCheckbox.disabled = true;
                                tutorCheckbox.disabled = true;
                            } else {
                                personalCheckbox.disabled = false;
                                tutorCheckbox.disabled = false;
                            }

                            // Si personal o tutor está marcado, deshabilitar alumno checkbox
                            if (personalCheckbox.checked || tutorCheckbox.checked) {
                                alumnoCheckbox.checked = false;
                                alumnoCheckbox.disabled = true;
                            } else {
                                alumnoCheckbox.disabled = false;
                            }
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            mostrarCampos();
                        });

                        ['es_personal', 'es_alumno', 'es_tutor'].forEach(function(id) {
                            document.getElementById(id).addEventListener('change', mostrarCampos);
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</body>

</html>