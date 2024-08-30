<?php
require_once 'config.php';

// Check if user is logged in and is a professor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'profesor') {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $due_date = sanitize_input($_POST['due_date']);
    $start_date = sanitize_input($_POST['start_date']);
    $professor_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO tasks (title, description, due_date, start_date, professor_id) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $description, $due_date, $start_date, $professor_id])) {
        $message = "Tarea creada exitosamente.";
    } else {
        $message = "Error al crear la tarea.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Tarea</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Crear Nueva Tarea</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'exitosamente') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="post" action="">
            <label for="title">Título:</label>
            <input type="text" name="title" required>

            <label for="description">Descripción:</label>
            <textarea name="description" required></textarea>

            <label for="start_date">Fecha de inicio:</label>
            <input type="date" name="start_date" required>

            <label for="due_date">Fecha de entrega:</label>
            <input type="date" name="due_date" required>

            <button type="submit">Crear Tarea</button>
        </form>
        <a href="dashboard.php" class="button">Volver al Dashboard</a>
    </div>
</body>
</html>