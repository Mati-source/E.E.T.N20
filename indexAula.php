<?php
include("php/conexion.php");

$sql2 = "SELECT materia, materia FROM materias";
$resultado = mysqli_query($con, $sql2);

$sql3 = "SELECT curso, curso FROM cursos";
$resultado2 = mysqli_query($con, $sql3);

$sql4 = "SELECT denominacion, denominacion FROM division";
$resultado3 = mysqli_query($con, $sql4);
?>

<!DOCTYPE html>
<html lang="es">

<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Datos de las Materia</title>
	<link rel="stylesheet" href="assets/css/fontawesome.css" />
	<link rel="stylesheet" href="assets/css/templatemo-grad-school.css" />
	<link rel="stylesheet" href="assets/css/owl.css" />
	<link rel="stylesheet" href="assets/css/lightbox.css" />
	<link rel="stylesheet" href="assets/css/add.css" />
	<link rel="stylesheet" type="text/css" href="assets/css/select2.min.css">
	<style>
		.content {
			margin-top: 80px;
		}

		.table,
		.table th,
		.table td {
			color: rgb(0, 0, 0);
			text-align: center;
		}

		.table td,
		.table th {
			text-align: center;
			color: rgb(0, 0, 0);
			/* Centrar el texto en las celdas */
		}

		h2 {
			letter-spacing: 0;
			position: relative;
			padding: 0 0 10px 0;
			font-weight: normal;
			line-height: normal;
			color: rgb(0, 0, 0);
			margin: 0
		}
	</style>

</head>

<body>
	<nav class="navbar navbar-default navbar-fixed-top">
		<?php include('nav.php'); ?>
	</nav>
	<div class="container">
		<div class="content">
			<center>
				<h2>Lista de las Materias</h2>
			</center>
			<hr />


			<?php
			if (isset($_GET['aksi']) == 'delete') {
				// escaping, additionally removing everything that could be (html/javascript-) code
				$nik = mysqli_real_escape_string($con, (strip_tags($_GET["nik"], ENT_QUOTES)));
				$cek = mysqli_query($con, "SELECT * FROM materias WHERE idmateria='$nik'");
				if (mysqli_num_rows($cek) == 0) {
					echo '<div class="alert alert-info alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> No se encontraron datos.</div>';
				} else {
					$delete = mysqli_query($con, "DELETE FROM materias WHERE idmateria='$nik'");
				}
			}

			$whereClause = "";
			if (isset($_POST['buscar']) && !empty($_POST['cuil']) && $_POST['cuil'] !== "Seleccione el DNI") {
				$cuilBuscado = mysqli_real_escape_string($con, strip_tags($_POST['cuil'], ENT_QUOTES));
				$whereClause = "WHERE cuil = '$cuilBuscado'";
			}

			$sql = "SELECT personal.* $whereClause, persona.nombre AS personaNombre, persona.apellido AS personaApellido, persona.cuil AS personaCuil, persona.telefono AS personaTelefono 
                        FROM personal 
                        JOIN persona ON personal.persona_idpersona = persona.idpersona
                        ORDER BY idpersonal ASC";
			$query = mysqli_query($con, $sql);
			?>


			<center>
				<form class="form-inline" method="post">

					<div class="form-group">
						<div class="col-sm-3">
							<select name="materia" class="form-control" id="controlBuscador2">
								<option value="Seleccione Materia">Seleccione Materia</option>

								<?php while ($ver = mysqli_fetch_row($resultado)) { ?>

									<option value="<?php echo $ver[0] ?>">
										<?php echo $ver[1] ?>
									</option>

								<?php  } ?>
							</select>
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-3">
							<input type="submit" name="buscar" class="btn btn-sm btn-primary" value="Buscar">
						</div>
					</div>
			</center>

			<div class="table td, table th">
				<div class="table-responsive">
					<br>
					<table class="table table-striped table-hover">

						<tr>
							<th>Profesor</th>
							<th>Materia</th>
							<th>Horas Cátedra</th>
							<th>Curso y división</th>
							<th>Acciones</th>
						</tr>

						<?php

						if (isset($_POST['buscar'])) {

							$materia    = mysqli_real_escape_string($con, (strip_tags($_POST["materia"], ENT_QUOTES))); //Escanpando caracteres
							$sql1 = "SELECT * FROM materias WHERE idmateria='$materia' ORDER BY idmateria ASC";

							$query = $con->query($sql1);

							#recorrro las filas en busqueda el valor
							while ($r = $query->fetch_array()) {

								#le asigno a la variable codigo el valor encontrado y que necesito ocupar en algun lado. 
								$idmateria = $r["idmateria"];
								break;
							}


							$sql = mysqli_query($con, "SELECT * FROM materias WHERE materia='$materia' ORDER BY idmateria ASC");
						} else {
							$sql = mysqli_query($con, "SELECT * FROM materias ORDER BY idmateria ASC");
						}
						if (mysqli_num_rows($sql) == 0) {
							echo '<tr><td colspan="8">No hay datos.</td></tr>';
						} else {
							$no = 1;
							while ($row = mysqli_fetch_assoc($sql)) {
								echo '
						<tr>
						    <td>' . $row['personaApellido'] . $row['personaNombre'] . '</td>
							<td>' . $row['materia'] . '</td>
							<td>' . $row['hs_semanales'] . '</td>
							<td>' . $row['curso'] . '</td>
							<td>' . $row['division'] . '</td>
							
							<td>
								<a href="editMateria.php?nik=' . $row['idmateria'] . '" title="Editar datos" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span>Editar</a>
								<a href="indexMateria.php?aksi=delete&nik=' . $row['idmateria'] . '&Materia=' . $row['materia'] . '" title="Eliminar" onclick="return confirm(\'Esta seguro de borrar los datos ' . $row['materia'] . '?\')" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span>Eliminar</a>
							</td>
						</tr>
						';
								$no++;
							}
						}
						?>


					</table>
				</div>
			</div>
		</div>


		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>

		<script src="select2/select2.min.js"></script>

		<script type="text/javascript">
			$(document).ready(function() {
				$('#controlBuscador2').select2();
			});
		</script>

</body>

</html>