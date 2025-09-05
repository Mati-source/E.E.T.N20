<?php
include("php/conexion.php");

$sql2 = "SELECT * FROM division";
$resultado = mysqli_query($con, $sql2);
?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Datos de los cursos</title>
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
				$cek = mysqli_query($con, "SELECT * FROM division WHERE iddivision='$nik'");
				if (mysqli_num_rows($cek) == 0) {
					echo '<div class="alert alert-info alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					No se encontraron datos.
					</div>';
				} else {
					$delete = mysqli_query($con, "DELETE FROM division WHERE iddivision='$nik'");
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
			if (isset($_POST['buscar']) && !empty($_POST['denominacion']) && $_POST['denominacion'] !== "Seleccione la división") {
				$divisionBuscado = mysqli_real_escape_string($con, strip_tags($_POST['denominacion'], ENT_QUOTES));
				$whereClause = "WHERE denominacion = '$divisionBuscado'";
			}

			$sql = "SELECT * FROM division $whereClause";
			$query = mysqli_query($con, $sql);

			if (!$query || mysqli_num_rows($query) == 0) {
				echo '<p>No hay datos para mostrar.</p>';
			} else {
			?>
				<br>
				<form class="form-inline" method="post" action="">
					<div class="form-group">
						<select name="denominacion" class="form-control" id="controlBuscador2" required>
							<option value="Seleccione la divison">Seleccione la Division</option>
							<?php while ($ver = mysqli_fetch_assoc($resultado)) { ?>
								<option value="<?php echo htmlspecialchars($ver['denominacion']); ?>" <?php if (isset($divisionBuscado) && $divisionBuscado == $ver['division']) echo 'selected'; ?>>
									<?php echo htmlspecialchars($ver['denominacion']); ?>
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
							<div class="titulo-card"><span class="info-label"><b>Division: </b></span><?php echo htmlspecialchars($row['denominacion']); ?></div>
							<div class="card-actions">
								<a href="#?nik=<?php echo $row['iddivision']; ?>" title="Editar datos" class="btn btn-primary btn-sm">
									<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Editar
								</a>
								<a href="indexDivision.php?aksi=delete&nik=<?php echo $row['iddivision']; ?>" title="Eliminar" onclick="return confirm('¿Está seguro de borrar los datos de <?php echo addslashes($row['denominacion']); ?>?')" class="btn btn-danger btn-sm">
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
				placeholder: "Seleccione el curso",
				allowClear: true
			});
		});
	</script>

</body>

</html>