<?php
include("php/conexion.php");

// Consulta para obtener CUIT únicos para el select
$sql2 = "SELECT DISTINCT dni FROM persona ORDER BY dni ASC";
$resultado = mysqli_query($con, $sql2);
?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Datos de las Personas</title>
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
				$cek = mysqli_query($con, "SELECT * FROM persona WHERE idpersona='$nik'");
				if (mysqli_num_rows($cek) == 0) {
					echo '<div class="alert alert-info alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					No se encontraron datos.
					</div>';
				} else {
					$delete = mysqli_query($con, "DELETE FROM persona WHERE idpersona='$nik'");
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

			// Procesar búsqueda
			$whereClause = "";
			if (isset($_POST['buscar']) && !empty($_POST['dni']) && $_POST['dni'] !== "Seleccione el DNI") {
				$dniBuscado = mysqli_real_escape_string($con, strip_tags($_POST['dni'], ENT_QUOTES));
				$whereClause = "WHERE dni = '$dniBuscado'";
			}

			$sql = "SELECT * FROM persona $whereClause ORDER BY apellido ASC";
			$query = mysqli_query($con, $sql);

			if (!$query || mysqli_num_rows($query) == 0) {
				echo '<p>No hay datos para mostrar.</p>';
			} else {
			?>

				<form class="form-inline" method="post" action="">
					<div class="form-group">
						<select name="dni" class="form-control" id="controlBuscador2" required>
							<option value="Seleccione el DNI">Seleccione el DNI</option>
							<?php while ($ver = mysqli_fetch_assoc($resultado)) { ?>
								<option value="<?php echo htmlspecialchars($ver['dni']); ?>" <?php if (isset($cuilBuscado) && $cuilBuscado == $ver['dni']) echo 'selected'; ?>>
									<?php echo htmlspecialchars($ver['dni']); ?>
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
				<br />

				<div class="cards-container">
					<?php while ($row = mysqli_fetch_assoc($query)) { ?>
						<div class="card-personal">
							<div class="titulo-card"><?php echo htmlspecialchars($row['apellido'] . ', ' . $row['nombre']); ?></div>
							<div class="info-row"><span class="info-label">DNI:</span> <span><?php echo htmlspecialchars($row['dni']); ?></span></div>
							<div class="info-row"><span class="info-label">Direccion:</span> <span><?php echo htmlspecialchars($row['direccion']); ?></span></div>
							<div class="info-row"><span class="info-label">Teléfono:</span> <span><?php echo htmlspecialchars($row['telefono']); ?></span></div>
							<div class="info-row"><span class="info-label">Correo:</span> <span><?php echo htmlspecialchars($row['email']); ?></span></div>

							<div class="card-actions">
								<a href="editPersona.php?nik=<?php echo $row['idpersona']; ?>" title="Editar datos" class="btn btn-primary btn-sm">
									<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Editar
								</a>
								<a href="indexPersona.php?aksi=delete&nik=<?php echo $row['idpersona']; ?>" title="Eliminar" onclick="return confirm('¿Está seguro de borrar los datos de <?php echo addslashes($row['apellido'] . ' ' . $row['nombre']); ?>?')" class="btn btn-danger btn-sm">
									<span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Eliminar
								</a>
							</div>
						</div>
					<?php } ?>
				</div>

			<?php } ?>
		</div>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>
	<script src="assets/js/select2.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {
			$('#controlBuscador2').select2({
				placeholder: "Seleccione el DNI",
				allowClear: true
			});
		});
	</script>

</body>

</html>