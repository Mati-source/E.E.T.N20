<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!empty($_POST["username"]) && !empty($_POST["password"])) {
		include "conexion.php"; // Ajusta la ruta según tu estructura

		// Consulta preparada para evitar inyección SQL
		$stmt = $con->prepare("SELECT * FROM persona WHERE usuario = ? AND contrasena = ?");
		$stmt->bind_param("ss", $_POST["username"], $_POST["password"]);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			// datos necesarios para del usuario para los permisos
			$user = $result->fetch_assoc();
			$_SESSION['idpersona'] = $user['idpersona'];
			$_SESSION['usuario'] = $user['usuario'];
			$_SESSION['contrasena'] = $user['contrasena'];

			echo 'success';
		} else {
			echo 'error';
		}

		$stmt->close();
		$con->close();
	} else {
		echo 'error'; // Campos vacíos
	}
} else {
	echo 'error'; // No es POST
}
