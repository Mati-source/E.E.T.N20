<?php
include("php/conexion.php");

// Filtros para el WHERE
$where = [];
if (isset($_POST['curso']) && !empty($_POST['curso']) && $_POST['curso'] !== "Seleccione el Curso") {
	$cursoBuscado = mysqli_real_escape_string($con, strip_tags($_POST['curso'], ENT_QUOTES));
	$where[] = "cursos.curso = '$cursoBuscado'";
}
if (isset($_POST['division']) && !empty($_POST['division']) && $_POST['division'] !== "Seleccione la División") {
	$divisionBuscado = mysqli_real_escape_string($con, strip_tags($_POST['division'], ENT_QUOTES));
	$where[] = "division.denominacion = '$divisionBuscado'";
}
if (isset($_POST['profesor']) && !empty($_POST['profesor']) && $_POST['profesor'] !== "Seleccione el Profesor") {
	$profesorBuscado = mysqli_real_escape_string($con, strip_tags($_POST['profesor'], ENT_QUOTES));
	$where[] = "persona.apellido = '$profesorBuscado'";
}

$whereClause = "";
if (count($where) > 0) {
	$whereClause = "WHERE " . implode(" AND ", $where);
}

// Consulta principal: cursos armados con curso, división y profesor
$sql = "
SELECT 
    cursos_armados.cursos_idcuso,
    cursos_armados.division_iddivision,
    cursos.curso,
    division.denominacion,
    personal.idpersonal,
    persona.nombre AS profesor_nombre,
    persona.apellido AS profesor_apellido,
    persona.dni AS profesor_dni
FROM cursos_armados
JOIN cursos ON cursos_armados.cursos_idcuso = cursos.idcuso
JOIN division ON cursos_armados.division_iddivision = division.iddivision
LEFT JOIN personal_has_cursos_armados phca 
    ON phca.cursos_armados_cursos_idcuso = cursos_armados.cursos_idcuso 
    AND phca.cursos_armados_division_iddivision = cursos_armados.division_iddivision
LEFT JOIN personal ON phca.personal_idpersonal = personal.idpersonal
LEFT JOIN persona ON personal.persona_idpersona = persona.idpersona
$whereClause
ORDER BY cursos_armados.cursos_idcuso ASC, cursos_armados.division_iddivision ASC
";

$query = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8" />
	<title>Cursos Armados</title>
	<link rel="stylesheet" href="assets/css/fontawesome.css" />
	<link rel="stylesheet" href="assets/css/templatemo-grad-school.css" />
	<link rel="stylesheet" href="assets/css/owl.css" />
	<link rel="stylesheet" href="assets/css/lightbox.css" />
	<link rel="stylesheet" type="text/css" href="assets/css/select2.min.css" />
	<style>
		.content {
			margin-top: 80px;
		}

		.cards-container {
			display: flex;
			flex-wrap: wrap;
			gap: 25px;
			justify-content: center;
		}

		.card-curso {
			background: rgb(240, 240, 240);
			border-radius: 8px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
			transition: transform 0.2s;
			width: 320px;
			min-height: 160px;
			padding: 20px;
			box-sizing: border-box;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			word-break: break-word;
		}

		.card-curso:hover {
			transform: translateY(-5px);
		}

		.titulo-card {
			border-bottom: 2px solid #333;
			padding-bottom: 8px;
			margin-bottom: 15px;
			font-size: 1.1rem;
			font-weight: bold;
			color: #333;
		}

		.info-row {
			font-size: 0.97rem;
			margin-bottom: 6px;
			display: flex;
			justify-content: space-between;
		}

		.info-label {
			font-weight: 600;
			color: #555;
		}

		.form-inline {
			display: flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 30px;
			gap: 10px;
			flex-wrap: wrap;
		}

		.form-inline .form-group {
			min-width: 220px;
		}
	</style>
</head>

<body>
	<nav class="navbar navbar-default navbar-fixed-top">
		<?php include('nav.php'); ?>
	</nav>
	<div class="container">
		<div class="content">
			<h2 style="text-align:center;">Cursos Armados</h2>
			<form class="form-inline" method="post" action="">
				<!-- Buscador Curso -->
				<div class="form-group">
					<select name="curso" class="form-control" id="buscadorCurso">
						<option value="Seleccione el Curso">Seleccione el Curso</option>
						<?php
						$cursos_query = mysqli_query($con, "SELECT DISTINCT curso FROM cursos ORDER BY curso ASC");
						while ($rowCurso = mysqli_fetch_assoc($cursos_query)) { ?>
							<option value="<?php echo htmlspecialchars($rowCurso['curso']); ?>" <?php if (isset($cursoBuscado) && $cursoBuscado == $rowCurso['curso']) echo 'selected'; ?>>
								<?php echo htmlspecialchars($rowCurso['curso']); ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<!-- Buscador División -->
				<div class="form-group">
					<select name="division" class="form-control" id="buscadorDivision">
						<option value="Seleccione la División">Seleccione la División</option>
						<?php
						$divisiones_query = mysqli_query($con, "SELECT DISTINCT denominacion FROM division ORDER BY denominacion ASC");
						while ($rowDivision = mysqli_fetch_assoc($divisiones_query)) { ?>
							<option value="<?php echo htmlspecialchars($rowDivision['denominacion']); ?>" <?php if (isset($divisionBuscado) && $divisionBuscado == $rowDivision['denominacion']) echo 'selected'; ?>>
								<?php echo htmlspecialchars($rowDivision['denominacion']); ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<!-- Buscador Profesor (Apellido) -->
				<div class="form-group">
					<select name="profesor" class="form-control" id="buscadorProfesor">
						<option value="Seleccione el Profesor">Seleccione el Profesor</option>
						<?php
						$profesores_query = mysqli_query($con, "
                            SELECT DISTINCT persona.apellido 
                            FROM personal 
                            JOIN persona ON personal.persona_idpersona = persona.idpersona 
                            ORDER BY persona.apellido ASC
                        ");
						while ($rowProf = mysqli_fetch_assoc($profesores_query)) { ?>
							<option value="<?php echo htmlspecialchars($rowProf['apellido']); ?>" <?php if (isset($profesorBuscado) && $profesorBuscado == $rowProf['apellido']) echo 'selected'; ?>>
								<?php echo htmlspecialchars($rowProf['apellido']); ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="form-group">
					<input type="submit" name="buscar" class="btn btn-sm btn-primary" value="Buscar">
					<a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-sm btn-secondary" style="margin-left: 10px;">Limpiar</a>
				</div>
			</form>
			<hr />
			<div class="cards-container">
				<?php
				if ($query && mysqli_num_rows($query) > 0) {
					while ($row = mysqli_fetch_assoc($query)) { ?>
						<div class="card-curso">
							<div class="titulo-card"><?php echo htmlspecialchars($row['curso']); ?> - División <?php echo htmlspecialchars($row['denominacion']); ?></div>
							<div class="info-row"><span class="info-label"><b>Profesor:</b></span> <span><?php echo htmlspecialchars($row['profesor_apellido'] . ', ' . $row['profesor_nombre']); ?></span></div>
							<div class="info-row"><span class="info-label"><b>DNI:</b></span> <span><?php echo htmlspecialchars($row['profesor_dni']); ?></span></div>
						</div>
				<?php }
				} else {
					if (
						(isset($_POST['curso']) && $_POST['curso'] !== "Seleccione el Curso" && $_POST['curso'] !== "") ||
						(isset($_POST['division']) && $_POST['division'] !== "Seleccione la División" && $_POST['division'] !== "") ||
						(isset($_POST['profesor']) && $_POST['profesor'] !== "Seleccione el Profesor" && $_POST['profesor'] !== "")
					) {
						echo '<p>No se encontraron datos para la búsqueda seleccionada.</p>';
					} else {
						echo '<p>No hay cursos armados para mostrar.</p>';
					}
				}
				?>
			</div>
		</div>
	</div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="assets/js/select2.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#buscadorCurso').select2({
				placeholder: "Seleccione el Curso",
				allowClear: true
			});
			$('#buscadorDivision').select2({
				placeholder: "Seleccione la División",
				allowClear: true
			});
			$('#buscadorProfesor').select2({
				placeholder: "Seleccione el Profesor",
				allowClear: true
			});
		});
	</script>
</body>

</html>