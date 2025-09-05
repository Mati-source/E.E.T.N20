<?php
include("php/conexion.php");

// Obtener cargos desde la base de datos
$cargos = [];
$cargos_query = mysqli_query($con, "SELECT idcargos, cargo FROM cargos ORDER BY cargo ASC");
while ($cargo_row = mysqli_fetch_assoc($cargos_query)) {
	$cargos[] = $cargo_row;
}

// Obtener alumnos disponibles para el desplegable de tutores (no asignados a ningún tutor)
$alumnos = [];
$alumnos_query = mysqli_query($con, "
    SELECT alumnos.idalumno, alumnos.persona_idpersona, persona.nombre, persona.apellido
    FROM alumnos
    JOIN persona ON alumnos.persona_idpersona = persona.idpersona
    WHERE alumnos.persona_idpersona NOT IN (
        SELECT alumnos_persona_idpersona FROM alumnos_has_tutores
    )
    ORDER BY persona.apellido ASC
");
while ($alumno_row = mysqli_fetch_assoc($alumnos_query)) {
	$alumnos[] = $alumno_row;
}

if (isset($_POST['add'])) {
	$success = true;
	$errorMsg = "";

	$Nombres = mysqli_real_escape_string($con, strip_tags($_POST["nombre"], ENT_QUOTES));
	$Apellidos = mysqli_real_escape_string($con, strip_tags($_POST["apellido"], ENT_QUOTES));
	$Localidad = mysqli_real_escape_string($con, strip_tags($_POST["direccion"], ENT_QUOTES));
	$Telefono = mysqli_real_escape_string($con, strip_tags($_POST["telefono"], ENT_QUOTES));
	$Nacimiento = mysqli_real_escape_string($con, strip_tags($_POST["fecha_nac"], ENT_QUOTES));
	$Dni = mysqli_real_escape_string($con, strip_tags($_POST["dni"], ENT_QUOTES));
	$Correo = mysqli_real_escape_string($con, strip_tags($_POST["email"], ENT_QUOTES));

	$cek = mysqli_query($con, "SELECT * FROM persona WHERE dni='$Dni'");
	if (mysqli_num_rows($cek) == 0) {
		$insert = mysqli_query($con, "INSERT INTO persona (nombre, apellido, direccion, telefono, fecha_nac, dni, email)
            VALUES('$Nombres','$Apellidos', '$Localidad', '$Telefono', '$Nacimiento', '$Dni', '$Correo')");
		if ($insert) {
			$idpersona = mysqli_insert_id($con);

			// Si es Personal
			if (isset($_POST['es_personal'])) {
				$FechaInicio = mysqli_real_escape_string($con, strip_tags($_POST["fecha_inicio"], ENT_QUOTES));
				$insertPersonal = mysqli_query($con, "INSERT INTO personal (fecha_inicio, persona_idpersona) VALUES('$FechaInicio', '$idpersona')");
				$idpersonal = mysqli_insert_id($con);
				if (!$insertPersonal) {
					$success = false;
					$errorMsg .= "Error al guardar datos de personal.<br>";
				}

				// Guardar los títulos
				if (!empty($_POST['titulo'])) {
					foreach ($_POST['titulo'] as $titulo) {
						$titulo = mysqli_real_escape_string($con, strip_tags($titulo, ENT_QUOTES));
						if ($titulo != "") {
							if (!mysqli_query($con, "INSERT INTO titulos (titulo) VALUES ('$titulo')")) {
								$success = false;
								$errorMsg .= "Error al guardar título.<br>";
							}
							$idtitulo = mysqli_insert_id($con);
							if (!mysqli_query($con, "INSERT INTO titulos_personal (titulos_idtitulos, personal_idpersonal, personal_persona_idpersona) VALUES ('$idtitulo', '$idpersonal', '$idpersona')")) {
								$success = false;
								$errorMsg .= "Error al asociar título.<br>";
							}
						}
					}
				}
				// Guardar cargos
				if (!empty($_POST['cargos'])) {
					foreach ($_POST['cargos'] as $idcargo) {
						$idcargo = intval($idcargo);
						if (!mysqli_query($con, "INSERT INTO personal_has_cargos (personal_idpersonal, cargos_idcargos) VALUES ('$idpersonal', '$idcargo')")) {
							$success = false;
							$errorMsg .= "Error al guardar cargos.<br>";
						}
					}
				}
			}

			// Si es Alumno
			else if (isset($_POST['es_alumno'])) {
				$Legajo = mysqli_real_escape_string($con, strip_tags($_POST["legajo"], ENT_QUOTES));
				$Matriz = mysqli_real_escape_string($con, strip_tags($_POST["libro_matriz"], ENT_QUOTES));
				$Folio = mysqli_real_escape_string($con, strip_tags($_POST["folio"], ENT_QUOTES));
				if (!mysqli_query($con, "INSERT INTO alumnos (persona_idpersona, legajo, libro_matriz, folio) VALUES ('$idpersona', '$Legajo', '$Matriz', '$Folio')")) {
					$success = false;
					$errorMsg .= "Error al guardar datos de alumno.<br>";
				}
			}

			// Si es Tutor
			else if (isset($_POST['es_tutor'])) {
				$insertTutor = mysqli_query($con, "INSERT INTO tutores (persona_idpersona) VALUES ('$idpersona')");
				if (!$insertTutor) {
					$success = false;
					$errorMsg .= "Error al guardar tutor.<br>";
				} else {
					$idtutor = mysqli_insert_id($con);
					// Procesar los alumnos seleccionados para el tutor
					if (!empty($_POST['alumnos'])) {
						foreach ($_POST['alumnos'] as $alumno_persona_id) {
							$alumno_query = mysqli_query($con, "SELECT idalumno FROM alumnos WHERE persona_idpersona = '$alumno_persona_id'");
							if ($alumno_row = mysqli_fetch_assoc($alumno_query)) {
								$idalumno = $alumno_row['idalumno'];
								if (!mysqli_query($con, "INSERT INTO alumnos_has_tutores (alumnos_idalumno, alumnos_persona_idpersona, tutores_idtutores, tutores_persona_idpersona) VALUES ('$idalumno', '$alumno_persona_id', '$idtutor', '$idpersona')")) {
									$success = false;
									$errorMsg .= "Error al asociar alumno al tutor.<br>";
								}
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
		} else {
			echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error. No se pudo guardar los datos!</div>';
		}
	} else {
		echo '<div class="alert alert-warning alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error. El DNI ya existe!</div>';
	}
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
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
			min-height: 500px;
		}

		.centrado {
			width: 100%;
			max-width: 550px;
			margin-left: 550px;
			margin-right: 300px;
			margin-top: 20px;
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
					<form class="form-horizontal" action="" method="post">
						<!-- Checkboxes para tipo de persona -->
						<div class="form-group">
							<div class="col-sm-12" style="display: flex; gap: 30px;">
								<label><input type="checkbox" name="es_personal" id="es_personal" value="1" onchange="mostrarCampos()"> Personal</label>
								<label><input type="checkbox" name="es_alumno" id="es_alumno" value="1" onchange="mostrarCampos()"> Alumno</label>
								<label><input type="checkbox" name="es_tutor" id="es_tutor" value="1" onchange="mostrarCampos()"> Tutor</label>
							</div>
						</div>

						<!-- Información General de la persona -->
						<div class="form-group">
							<label>Nombres:</label>
							<div class="col-sm-6">
								<input type="text" name="nombre" class="form-control" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras permitidas">
							</div>
						</div>
						<div class="form-group">
							<label>Apellidos:</label>
							<div class="col-sm-6">
								<input type="text" name="apellido" class="form-control" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras permitidas">
							</div>
						</div>
						<div class="form-group">
							<label>DNI:</label>
							<div class="col-sm-6">
								<input type="text" name="dni" class="form-control" required minlength="8" maxlength="8" pattern="[0-9]{8,11}" title="Solo números permitidos">
							</div>
						</div>
						<div class="form-group">
							<label>Fecha de Nacimiento:</label>
							<div class="col-sm-6">
								<input type="date" name="fecha_nac" class="form-control" required>
							</div>
						</div>
						<div class="form-group">
							<label>Dirección:</label>
							<div class="col-sm-6">
								<input type="text" name="direccion" class="form-control" required>
							</div>
						</div>
						<div class="form-group">
							<label>Numero de Teléfono:</label>
							<div class="col-sm-6">
								<input type="text" name="telefono" class="form-control" maxlength="10" pattern="[0-9]{10}" title="Solo números permitidos">
							</div>
						</div>
						<div class="form-group">
							<label>Correo Electrónico:</label>
							<div class="col-sm-6">
								<input type='email' name='email' class='form-control' />
							</div>
						</div>

						<!-- Campos adicionales para Personal -->
						<div id="campos_personal" style="display:none;">
							<div class="label">Datos de Personal:</div>
							<div class="form-group" id="titulo-group">
								<div class="col-sm-6" style="display: flex; gap: 10px;">
									<label>Titulos:</label>
									<input type="text" name="titulo[]" class="form-control">
									<button type="button" id="add-titulo" style="width:35px;height:35px;" title="Añadir otro título">+</button>
								</div>
							</div>
							<div class="form-group">
								<label>Fecha de Incio en el Sistema:</label>
								<div class="col-sm-6">
									<input type="date" name="fecha_inicio" class="form-control" placeholder="Fecha de Inicio">
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-6">
									<label>Cargos:</label><br>
									<?php foreach ($cargos as $cargo) { ?>
										<label>
											<input type="checkbox" name="cargos[]" value="<?php echo $cargo['idcargos']; ?>">
											<?php echo htmlspecialchars($cargo['cargo']); ?>
										</label><br>
									<?php } ?>
								</div>
							</div>
						</div>
						<script>
							document.getElementById('add-titulo').addEventListener('click', function() {
								const group = document.getElementById('titulo-group');
								const div = document.createElement('div');
								div.className = "col-sm-6";
								div.style.display = "flex";
								div.style.gap = "10px";
								div.style.marginTop = "10px";
								div.innerHTML = '<input type="text" name="titulo[]" class="form-control" placeholder="Titulo">';
								group.appendChild(div);
							});
						</script>

						<!-- Campos adicionales para Alumnos -->
						<div id="campos_alumno" style="display:none;">
							<div class="label">Datos del Alumno:</div>
							<div class="form-group">
								<label>Número de Legajo:</label>
								<div class="col-sm-6">
									<input type="text" name="legajo" class="form-control">
								</div>
							</div>
							<div class="form-group">
								<label>Libro Matríz:</label>
								<div class="col-sm-6">
									<input type="text" name="libro_matriz" class="form-control">
								</div>
							</div>
							<div class="form-group">
								<label>Número de Folio:</label>
								<div class="col-sm-6">
									<input type="text" name="folio" class="form-control">
								</div>
							</div>
						</div>

						<!-- Campos adicionales para Tutores -->
						<div id="campos_tutor" style="display:none;">
							<div class="label">Datos de Tutor:</div>
							<div class="form-group">
								<div class="col-sm-6">
									<label>Seleccionar alumnos asociados al tutor:</label>
									<select name="alumnos[]" class="form-control" multiple>
										<?php foreach ($alumnos as $alumno) { ?>
											<option value="<?php echo $alumno['persona_idpersona']; ?>">
												<?php echo htmlspecialchars($alumno['apellido'] . ', ' . $alumno['nombre']); ?>
											</option>
										<?php } ?>
									</select>
									<small class="form-text text-muted">Puedes seleccionar uno o varios alumnos (Ctrl+click para varios).</small>
								</div>
							</div>
						</div>

						<!-- Botones de acción -->
						<div class='form-group'>
							<div class='col-sm-6'>
								<input type='submit' name='add' class='btn btn-sm btn-primary btn-custom' value='Guardar datos'>
							</div>
						</div>
						<div class='form-group'>
							<div class='col-sm-6'>
								<a href='indexPersona.php' class='btn btn-sm btn-danger btn-custom'>Cancelar</a>
							</div>
						</div>
					</form>
				</div>
			</div>
			s
			<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js'></script>
			<script src='assets/js/bootstrap.min.js'></script>
			<script src='assets/js/bootstrap-datepicker.js'></script>
			<script>
				function mostrarCampos() {
					const personalCheckbox = document.getElementById('es_personal');
					const alumnoCheckbox = document.getElementById('es_alumno');
					const tutorCheckbox = document.getElementById('es_tutor');

					// Mostrar bloques según checkbox
					document.getElementById('campos_personal').style.display = personalCheckbox.checked ? 'block' : 'none';
					document.getElementById('campos_alumno').style.display = alumnoCheckbox.checked ? 'block' : 'none';
					document.getElementById('campos_tutor').style.display = tutorCheckbox.checked ? 'block' : 'none';

					// Validación para evitar que alumno se marque con otro tipo
					if (alumnoCheckbox.checked) {
						// Deshabilitar personal y tutor si alumno está marcado
						if (personalCheckbox.checked) personalCheckbox.checked = false;
						if (tutorCheckbox.checked) tutorCheckbox.checked = false;
						personalCheckbox.disabled = true;
						tutorCheckbox.disabled = true;
					} else {
						// Habilitar personal y tutor si alumno no está marcado
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

				// Ejecutar al cargar para sincronizar estados
				document.addEventListener('DOMContentLoaded', function() {
					mostrarCampos();
				});

				// Añade listeners para manejar cambios dinámicos
				['es_personal', 'es_alumno', 'es_tutor'].forEach(function(id) {
					document.getElementById(id).addEventListener('change', mostrarCampos);
				});
			</script>
		</div>
	</div>
</body>

</html>