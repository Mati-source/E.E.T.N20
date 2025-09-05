<?php
include("php/conexion.php");
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
			/* Altura mínima para evitar cambios al mostrar mensajes */
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
					<?php
					// escaping, additionally removing everything that could be (html/javascript-) code
					$nik = mysqli_real_escape_string($con, strip_tags($_GET["nik"], ENT_QUOTES));
					$sql = mysqli_query($con, "SELECT * FROM materias WHERE idmateria='$nik'");
					if (mysqli_num_rows($sql) == 0) {
						header("Location: indexMateria.php");
					} else {
						$row = mysqli_fetch_assoc($sql);
					}
					if (isset($_POST['save'])) {
						$materia = mysqli_real_escape_string($con, (strip_tags($_POST["materia"], ENT_QUOTES))); //Escanpando caracteres 
						$hs_semanales = mysqli_real_escape_string($con, (strip_tags($_POST["hs_semanales"], ENT_QUOTES))); //Escanpando caracteres 

						$update = mysqli_query($con, "UPDATE materias SET materia='$materia', hs_semanales='$hs_semanales' WHERE idmateria='$nik'") or die(mysqli_error($con));
						if ($update) {
							header("Location: editMateria.php?nik=" . $nik . "&pesan=sukses");
						} else {
							echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error, no se pudo guardar los datos.</div>';
						}
					}

					if (isset($_GET['pesan']) == 'sukses') {
						echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Los datos han sido guardados con éxito.</div>';
					}
					?>
					<form class="form-horizontal" action="" method="post">
						<div class="form-group">
							<div class="col-sm-6">
								<input type="text" name="materia" value="<?php echo $row['materia']; ?>" class="form-control" placeholder="materia" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidas">
								<div id="error" style="color:red; display:none;">Error de formato en el nombre de la materia</div>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-6">
								<input type="text" name="hs_semanales" value="<?php echo $row['hs_semanales']; ?>" class="form-control" placeholder="hs_semanales" min="1" max="15" required title="Solo números del 1 al 15 permitidas">
								<div id="error" style="color:red; display:none;">Error de formato en la horas de la materia</div>
							</div>
						</div>
						<div class='form-group'>
							<div class='col-sm-6'>
								<input type='submit' name='save' class='btn btn-sm btn-primary btn-custom' value='Guardar datos'>
							</div>
						</div>
						<div class='form-group'>
							<div class='col-sm-6'>
								<a href='indexMateria.php' class='btn btn-sm btn-danger btn-custom'>Cancelar</a>
							</div>
						</div>
					</form>
				</div>
			</div>

			<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
			<script src="assets/js/bootstrap.min.js"></script>
			<script src="assets/js/bootstrap-datepicker.js"></script>
			<script>
				$('.date').datepicker({
					format: 'dd-mm-yyyy',
				});
				document.querySelector('input[name="materia"]').addEventListener('input', function() {
					var input = this.value;
					var error = document.getElementById('error');
					var regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/; // Expresión regular para letras y espacios
					if (!regex.test(input)) {
						this.value = input.slice(0, -1); // Elimina el último caracter si no es una letra.
						alert('Solo letras y espacios permitidas');
					}
				});
			</script>
</body>

</html>