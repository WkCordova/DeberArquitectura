<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header("Location: login.php");
    exit();
}

$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $submission = $_POST['submission'];
    $stmt = $pdo->prepare("INSERT INTO submissions (task_id, student_id, status, explanation) VALUES (?, ?, 'entregado', ?)");
    if ($stmt->execute([$task_id, $_SESSION['user_id'], $submission])) {
        header("Location: student_dashboard.php");
        exit();
    } else {
        $error = "Error al enviar la tarea. Por favor, inténtelo de nuevo.";
    }
}

// Fetch task details
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
    die("Tarea no encontrada.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entregar Tarea</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Entregar Tarea: <?php echo htmlspecialchars($task['title']); ?></h1>
        <form method="POST">
            <label for="submission">Tu respuesta:</label>
            <textarea id="submission" name="submission" required></textarea>
            <button type="submit">Enviar Tarea</button>
        </form>
        <a href="student_dashboard.php">Volver al Dashboard</a>
    </div>
</body>
</html>