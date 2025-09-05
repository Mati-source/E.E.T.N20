<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Dashboard</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <header class="main-header clearfix" role="header">
        <a href="#menu" class="menu-link"><i class="fa fa-bars"></i></a>
        <nav id="menu" class="main-nav" role="navigation">
            <ul class="main-menu">
                <li><a href="principal.php">Noticias</a></li>
                <li class="has-submenu"><a href="nuevaPersona.php">Nueva Persona</a>
                </li>
                <li class="has-submenu"><a href="indexPersonal.php">Personal</a>
                </li>
                <li class="has-submenu"><a href="indexAlumno.php">Alumnos</a>
                </li>
                <li class="has-submenu"><a href="indexTutor.php">Tutores</a>
                </li>
                <li class="has-submenu"><a href="indexMateria.php">Materias</a>
                    <ul class="sub-menu">
                        <li><a href="addMateria.php">Agregar Materia</a></li>
                    </ul>
                <li class="has-submenu"><a href="#section6">Cursos/Divisiones</a>
                    <ul class="sub-menu">
                        <li><a href="indexCurso.php">Lista de Cursos</a></li>
                        <li><a href="addCurso.php">Agregar Curso</a></li>
                        <li><a href="indexDivision.php">Lista de Divisiones</a></li>
                        <li><a href="addDivision.php">Agregar División</a></li>
                    </ul>
                </li>
                <li><a href="index.html" onclick="closed();">Cerrar Sesión</a></li>
                </li>
            </ul>

        </nav>
    </header>

</body>

</html>