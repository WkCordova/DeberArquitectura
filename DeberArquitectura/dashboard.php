<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

echo "<h2>Bienvenido, " . $_SESSION['user_name'] . "</h2>";
echo "<p>Rol: " . $_SESSION['user_role'] . "</p>";

if ($_SESSION['user_role'] == 'profesor') {
    echo "<a href='profesor_tareas.php'>Gestionar Tareas</a>";
} else {
    echo "<a href='alumno_tareas.php'>Ver Tareas</a>";
}
?>
