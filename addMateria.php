<?php
include("php/conexion.php");

// Obtener CUILs y cursos
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

		.btn-sm {
			font-size: 0.85rem;
		}

		.centrado {
			width: 100%;
			max-width: 550px;
			margin: 20px auto 0 auto;
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
	</style>

	<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
	<nav class="navbar navbar-default navbar-fixed-top">
		<?php include("nav.php"); ?>
	</nav>
	<div class="container">
		<div class="content">
			<div class="contenedor">
				<div class="centrado">
					<center>
						<?php
						if (isset($_POST['add'])) {
							if (isset($_POST['add'])) {
								$materia = mysqli_real_escape_string($con, (strip_tags($_POST["materia"], ENT_QUOTES))); // Escaneando caracteres 
								$cek = mysqli_query($con, "SELECT * FROM materias WHERE materia='$materia'");
								if (mysqli_num_rows($cek) == 0) {
									$insert = mysqli_query($con, "INSERT INTO materias (materia) VALUES('$materia')") or die(mysqli_error($con));
									if ($insert) {
										echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Bien hecho! Los datos han sido guardados con éxito.</div>';
									} else {
										echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error. No se pudo guardar los datos !</div>';
									}
								} else {
									echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error. código existe!</div>';
								}
							}
						}

						?>

						<form class="form-horizontal" action="" method="post">
							<div class="form-group">
								<div class="col-sm-6">
									<input type="text" name="materia" class="form-control" placeholder="Nombre de la materia" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidas">
									<div id="error" style="color:red; display:none;">Error de formato en el nombre de la materia</div>
								</div>
							</div>

							<div class='form-group'>
								<div class='col-sm-6'>
									<input type='submit' name='add' class='btn btn-sm btn-primary btn-custom' value='Guardar datos'>
								</div>
							</div>
							<div class='form-group'>
								<div class='col-sm-6'>
									<a href='index.php' class='btn btn-sm btn-danger btn-custom'>Cancelar</a>
								</div>
							</div>
						</form>
					</center>
				</div>

				<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js'></script>
				<script src='assets/js/bootstrap.min.js'></script>
				<script src='assets/js/bootstrap-datepicker.js'></script>
				<script>
					$('.date').datepicker({
						format: 'dd-mm-yyyy',
					});
					// Opcional: select2 para mejor UX en cursos
					$(document).ready(function() {
						$('#cursos').select2({
							placeholder: "Selecciona curso(s)"
						});
					});
				</script>

</body>

</html>