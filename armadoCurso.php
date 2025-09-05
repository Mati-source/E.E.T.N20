<?php
include("php/conexion.php");

// Obtener cursos
$cursos = [];
$cursos_query = mysqli_query($con, "SELECT idcuso, curso FROM cursos ORDER BY curso ASC");
while ($row = mysqli_fetch_assoc($cursos_query)) {
	$cursos[] = $row;
}

// Obtener divisiones
$divisiones = [];
$divisiones_query = mysqli_query($con, "SELECT iddivision, denominacion FROM division ORDER BY denominacion ASC");
while ($row = mysqli_fetch_assoc($divisiones_query)) {
	$divisiones[] = $row;
}

// Obtener profesores 
$profesores = [];
$profesores_query = mysqli_query($con, "
    SELECT personal.idpersonal, personal.persona_idpersona, persona.dni, persona.nombre, persona.apellido
    FROM personal
    JOIN persona ON personal.persona_idpersona = persona.idpersona
    ORDER BY persona.apellido ASC
");
while ($row = mysqli_fetch_assoc($profesores_query)) {
	$profesores[] = $row;
}

// Procesar formulario
if (isset($_POST['add'])) {
	$success = true;
	$errorMsg = "";

	$curso_id = intval($_POST['curso']);
	$division_id = intval($_POST['division']);
	$profesor_dni = mysqli_real_escape_string($con, $_POST['profesor_dni']);
	$profesor_nombre = mysqli_real_escape_string($con, $_POST['profesor_nombre']);
	$profesor_apellido = mysqli_real_escape_string($con, $_POST['profesor_apellido']);

	// Buscar el idpersonal del profesor por DNI
	$prof_query = mysqli_query($con, "
        SELECT personal.idpersonal, personal.persona_idpersona
        FROM personal
        JOIN persona ON personal.persona_idpersona = persona.idpersona
        WHERE persona.dni = '$profesor_dni'
        LIMIT 1
    ");
	if ($prof_row = mysqli_fetch_assoc($prof_query)) {
		$idpersonal = $prof_row['idpersonal'];
		$persona_id = $prof_row['persona_idpersona'];

		// Asociar curso con división y profesor 
		$insert = mysqli_query($con, "
            INSERT INTO cursos_armados (cursos_idcuso, division_iddivision)
            VALUES ('$curso_id', '$division_id')
        ");
		if ($insert) {
			$id_curso_armado = mysqli_insert_id($con);

			// Asociar profesor con curso armado 
			$insertProf = mysqli_query($con, "
                INSERT INTO personal_has_cursos_armados (personal_idpersonal, personal_persona_idpersona, cursos_armados_cursos_idcuso, cursos_armados_division_iddivision)
                VALUES ('$idpersonal', '$persona_id', '$curso_id', '$division_id')
            ");
			if ($insertProf) {
				echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>¡Asociación guardada correctamente!</div>';
			} else {
				echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error al asociar el profesor al curso armado.</div>';
			}
		} else {
			echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error al guardar el curso armado.</div>';
		}
	} else {
		echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>No se encontró el profesor con ese DNI.</div>';
	}
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8" />
	<title>Armado de Curso</title>
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
			display: flex;
			justify-content: center;
			align-items: center;
			min-height: 500px;
			width: 100%;
		}

		.centrado {
			width: 100%;
			max-width: 400px;
			margin: 0 auto;
			padding: 20px;
			background: #fff;
			border-radius: 8px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
		}

		.form-control {
			width: 100%;
			max-width: 500px;
		}

		.btn-custom {
			width: 220px;
			height: 35px;
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
						<!-- Curso -->
						<div class="form-group">
							<div class="col-sm-12">
								<label>Curso:</label>
								<select name="curso" class="form-control select2" required>
									<option value="">Seleccione un curso</option>
									<?php foreach ($cursos as $curso) { ?>
										<option value="<?php echo $curso['idcuso']; ?>"><?php echo htmlspecialchars($curso['curso']); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<!-- División -->
						<div class="form-group">
							<div class="col-sm-12">
								<label>División:</label>
								<select name="division" class="form-control select2" required>
									<option value="">Seleccione una división</option>
									<?php foreach ($divisiones as $division) { ?>
										<option value="<?php echo $division['iddivision']; ?>"><?php echo htmlspecialchars($division['denominacion']); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<!-- DNI del profesor -->
						<div class="form-group">
							<div class="col-sm-12">
								<label>DNI del Profesor:</label>
								<select name="profesor_dni" id="profesor_dni" class="form-control select2" required>
									<option value="">Seleccione DNI</option>
									<?php foreach ($profesores as $profesor) { ?>
										<option value="<?php echo $profesor['dni']; ?>"><?php echo htmlspecialchars($profesor['dni']); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<!-- Nombre del profesor -->
						<div class="form-group">
							<div class="col-sm-12">
								<label>Nombre del Profesor:</label>
								<select name="profesor_nombre" id="profesor_nombre" class="form-control select2" required>
									<option value="">Seleccione nombre</option>
									<?php foreach ($profesores as $profesor) { ?>
										<option value="<?php echo htmlspecialchars($profesor['nombre']); ?>" data-dni="<?php echo $profesor['dni']; ?>" data-apellido="<?php echo htmlspecialchars($profesor['apellido']); ?>"><?php echo htmlspecialchars($profesor['nombre']); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<!-- Apellido del profesor -->
						<div class="form-group">
							<div class="col-sm-12">
								<label>Apellido del Profesor:</label>
								<select name="profesor_apellido" id="profesor_apellido" class="form-control select2" required>
									<option value="">Seleccione apellido</option>
									<?php foreach ($profesores as $profesor) { ?>
										<option value="<?php echo htmlspecialchars($profesor['apellido']); ?>" data-dni="<?php echo $profesor['dni']; ?>" data-nombre="<?php echo htmlspecialchars($profesor['nombre']); ?>"><?php echo htmlspecialchars($profesor['apellido']); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<!-- Botones de acción -->
						<div class='form-group'>
							<div class='col-sm-12'>
								<input type='submit' name='add' class='btn btn-sm btn-primary btn-custom' value='Guardar datos'>
							</div>
						</div>
						<div class='form-group'>
							<div class='col-sm-12'>
								<a href='cursosArmados.php' class='btn btn-sm btn-danger btn-custom'>Cancelar</a>
							</div>
						</div>
					</form>
				</div>
			</div>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
			<script src="assets/js/select2.min.js"></script>
			<script>
				// Array JS con los profesores
				var profesores = <?php echo json_encode($profesores); ?>;

				$(document).ready(function() {
					$('.select2').select2({
						width: '100%',
						allowClear: true
					});

					// Autocompletar por DNI
					$('#profesor_dni').on('change', function() {
						var dni = $(this).val();
						var prof = profesores.find(function(p) {
							return p.dni == dni;
						});
						if (prof) {
							$('#profesor_nombre').val(prof.nombre).trigger('change');
							$('#profesor_apellido').val(prof.apellido).trigger('change');
						} else {
							$('#profesor_nombre').val('').trigger('change');
							$('#profesor_apellido').val('').trigger('change');
						}
					});

					// Autocompletar por nombre
					$('#profesor_nombre').on('change', function() {
						var nombre = $(this).val();
						// Si hay más de uno, toma el primero
						var prof = profesores.find(function(p) {
							return p.nombre == nombre;
						});
						if (prof) {
							$('#profesor_dni').val(prof.dni).trigger('change');
							$('#profesor_apellido').val(prof.apellido).trigger('change');
						} else {
							$('#profesor_dni').val('').trigger('change');
							$('#profesor_apellido').val('').trigger('change');
						}
					});

					// Autocompletar por apellido
					$('#profesor_apellido').on('change', function() {
						var apellido = $(this).val();
						var prof = profesores.find(function(p) {
							return p.apellido == apellido;
						});
						if (prof) {
							$('#profesor_dni').val(prof.dni).trigger('change');
							$('#profesor_nombre').val(prof.nombre).trigger('change');
						} else {
							$('#profesor_dni').val('').trigger('change');
							$('#profesor_nombre').val('').trigger('change');
						}
					});
				});
			</script>
		</div>
	</div>
</body>

</html>