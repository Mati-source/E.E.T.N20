<?php
include("php/conexion.php");

// Filtros para el WHERE
$where = [];
if (isset($_POST['dni']) && !empty($_POST['dni']) && $_POST['dni'] !== "Seleccione el DNI") {
	$cuilBuscado = mysqli_real_escape_string($con, strip_tags($_POST['dni'], ENT_QUOTES));
	$where[] = "persona.dni = '$cuilBuscado'";
}
if (isset($_POST['nombre']) && !empty($_POST['nombre']) && $_POST['nombre'] !== "Seleccione el Nombre") {
	$nombreBuscado = mysqli_real_escape_string($con, strip_tags($_POST['nombre'], ENT_QUOTES));
	$where[] = "persona.nombre = '$nombreBuscado'";
}
if (isset($_POST['apellido']) && !empty($_POST['apellido']) && $_POST['apellido'] !== "Seleccione el Apellido") {
	$apellidoBuscado = mysqli_real_escape_string($con, strip_tags($_POST['apellido'], ENT_QUOTES));
	$where[] = "persona.apellido = '$apellidoBuscado'";
}

$whereClause = "";
if (count($where) > 0) {
	$whereClause = "WHERE " . implode(" AND ", $where);
}

$sql = "SELECT alumnos.*,
               persona.nombre AS personaNombre,
               persona.apellido AS personaApellido,
               persona.dni AS personaDni,
               persona.telefono AS personaTelefono,
               alumnos.persona_idpersona AS persona_idpersona
        FROM alumnos
        JOIN persona ON alumnos.persona_idpersona = persona.idpersona
        $whereClause
        ORDER BY alumnos.idalumno ASC";


$query = mysqli_query($con, $sql);
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

		.cards-container {
			display: flex;
			flex-wrap: wrap;
			gap: 25px;
			justify-content: center;
		}

		.card-personal {
			background: rgb(240, 240, 240);
			border-radius: 8px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
			transition: transform 0.2s;
			width: 300px;
			min-height: 180px;
			padding: 20px;
			box-sizing: border-box;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
		}

		.card-personal:hover {
			transform: translateY(-5px);
		}

		.titulo-card {
			border-bottom: 2px solid rgb(51, 51, 51);
			padding-bottom: 8px;
			margin-bottom: 15px;
			font-size: 1rem;
			font-weight: bold;
			color: #333;
		}

		.info-row {
			font-size: 0.95rem;
			margin-bottom: 6px;
			display: flex;
			justify-content: space-between;
		}

		.info-label {
			font-weight: 600;
			color: #555;
		}

		.btn-sm {
			font-size: 0.85rem;
		}

		.card-actions {
			margin-top: 10px;
			display: flex;
			justify-content: flex-end;
			gap: 10px;
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

			<?php
			// Eliminar registro
			if (isset($_GET['aksi']) && $_GET['aksi'] == 'delete') {
				$nik = mysqli_real_escape_string($con, strip_tags($_GET["nik"], ENT_QUOTES));
				$cek = mysqli_query($con, "SELECT * FROM alumnos WHERE idalumno='$nik'");
				if (mysqli_num_rows($cek) == 0) {
					echo '<div class="alert alert-info alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        No se encontraron datos.
        </div>';
				} else {
					// Eliminar relaciones con tutores
					mysqli_query($con, "DELETE FROM alumnos_has_tutores WHERE alumnos_idalumno='$nik'");
					$delete = mysqli_query($con, "DELETE FROM alumnos WHERE idalumno='$nik'");
					if ($delete) {
						echo '<div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            Datos eliminados correctamente.
            </div>';
					} else {
						echo '<div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            Error al eliminar los datos.
            </div>';
					}
				}
			}
			?>
			<br>
			<form class="form-inline" method="post" action="">
				<!-- Buscador mediante DNI -->
				<div class="form-group">
					<select name="dni" class="form-control" id="buscadorDni">
						<option value="Seleccione el DNI">Seleccione el DNI</option>
						<?php
						// Solo DNIs de personas que están en personal
						$dni_query = mysqli_query($con, "
                            SELECT DISTINCT persona.dni 
                            FROM persona 
                            INNER JOIN alumnos ON persona.idpersona = alumnos.persona_idpersona 
                            ORDER BY persona.dni ASC
                        ");
						while ($rowDni = mysqli_fetch_assoc($dni_query)) { ?>
							<option value="<?php echo htmlspecialchars($rowDni['dni']); ?>" <?php if (isset($cuilBuscado) && $cuilBuscado == $rowDni['dni']) echo 'selected'; ?>>
								<?php echo htmlspecialchars($rowDni['dni']); ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<!-- Buscador mediante Nombre -->
				<div class="form-group">
					<select name="nombre" class="form-control" id="buscadorNombre">
						<option value="Seleccione el Nombre">Seleccione el Nombre</option>
						<?php
						$nombres_query = mysqli_query($con, "
                            SELECT DISTINCT persona.nombre 
                            FROM persona 
                            INNER JOIN alumnos ON persona.idpersona = alumnos.persona_idpersona 
                            ORDER BY persona.nombre ASC
                        ");
						while ($rowNombre = mysqli_fetch_assoc($nombres_query)) { ?>
							<option value="<?php echo htmlspecialchars($rowNombre['nombre']); ?>" <?php if (isset($nombreBuscado) && $nombreBuscado == $rowNombre['nombre']) echo 'selected'; ?>>
								<?php echo htmlspecialchars($rowNombre['nombre']); ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<!-- Buscador mediante Apellido -->
				<div class="form-group">
					<select name="apellido" class="form-control" id="buscadorApellido">
						<option value="Seleccione el Apellido">Seleccione el Apellido</option>
						<?php
						$apellidos_query = mysqli_query($con, "
                            SELECT DISTINCT persona.apellido 
                            FROM persona 
                            INNER JOIN alumnos ON persona.idpersona = alumnos.persona_idpersona 
                            ORDER BY persona.apellido ASC
                        ");
						while ($rowApellido = mysqli_fetch_assoc($apellidos_query)) { ?>
							<option value="<?php echo htmlspecialchars($rowApellido['apellido']); ?>" <?php if (isset($apellidoBuscado) && $apellidoBuscado == $rowApellido['apellido']) echo 'selected'; ?>>
								<?php echo htmlspecialchars($rowApellido['apellido']); ?>
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
			<br>

			<div class="cards-container">
				<?php
				if ($query && mysqli_num_rows($query) > 0) {
					while ($row = mysqli_fetch_assoc($query)) { ?>
						<div class="card-personal">
							<div class="titulo-card"><?php echo htmlspecialchars($row['personaApellido'] . ', ' . $row['personaNombre']); ?></div>
							<div class="info-row"><span class="info-label"><b>DNI:</b></span> <span><?php echo htmlspecialchars($row['personaDni']); ?></span></div>
							<div class="info-row"><span class="info-label"><b>Teléfono:</b></span> <span><?php echo htmlspecialchars($row['personaTelefono']); ?></span></div>

							<div class="card-actions">
								<a href="editPersona.php?nik=<?php echo $row['persona_idpersona']; ?>" title="Editar datos" class="btn btn-primary btn-sm">
									<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Editar
								</a>
								<a href="indexAlumno.php?aksi=delete&nik=<?php echo $row['idalumno']; ?>" title="Eliminar" onclick="return confirm('¿Está seguro de borrar los datos de <?php echo addslashes($row['personaApellido'] . ' ' . $row['personaNombre']); ?>?')" class="btn btn-danger btn-sm">
									<span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Eliminar
								</a>
							</div>
						</div>
				<?php }
				} else {
					// Mostrar mensaje si hay filtros y no hay resultados
					if (
						(isset($_POST['dni']) && $_POST['dni'] !== "Seleccione el DNI" && $_POST['dni'] !== "") ||
						(isset($_POST['nombre']) && $_POST['nombre'] !== "Seleccione el Nombre" && $_POST['nombre'] !== "") ||
						(isset($_POST['apellido']) && $_POST['apellido'] !== "Seleccione el Apellido" && $_POST['apellido'] !== "")
					) {
						echo '<p>No se encontraron datos para la búsqueda seleccionada.</p>';
					} else {
						echo '<p>No hay datos para mostrar.</p>';
					}
				}
				?>
			</div>
		</div>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>
	<script src="assets/js/select2.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {
			$('#buscadorDni').select2({
				placeholder: "Seleccione el DNI",
				allowClear: true
			});
			$('#buscadorNombre').select2({
				placeholder: "Seleccione el Nombre",
				allowClear: true
			});
			$('#buscadorApellido').select2({
				placeholder: "Seleccione el Apellido",
				allowClear: true
			});
			$('#buscadorCargo').select2({
				placeholder: "Seleccione el Cargo",
				allowClear: true
			});
		});
	</script>

</body>

</html>