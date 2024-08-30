<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'profesor') {
    header("Location: login.php");
    exit();
}

$task_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch task details
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND professor_id = ?");
$stmt->execute([$task_id, $_SESSION['user_id']]);
$task = $stmt->fetch();

if (!$task) {
    die("Task not found or you don't have permission to edit it.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $due_date = $_POST['due_date'] ?? '';

    if (!empty($title) && !empty($description) && !empty($start_date) && !empty($due_date)) {
        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, start_date = ?, due_date = ? WHERE id = ?");
        if ($stmt->execute([$title, $description, $start_date, $due_date, $task_id])) {
            $message = "Tarea actualizada exitosamente.";
            // Refresh task data
            $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
            $stmt->execute([$task_id]);
            $task = $stmt->fetch();
        } else {
            $message = "Error al actualizar la tarea. Por favor, inténtelo de nuevo.";
        }
    } else {
        $message = "Todos los campos son obligatorios.";
    }
}

function safe_echo($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Tarea</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Editar Tarea</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'exitosamente') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="" method="post" class="task-form">
            <label for="title">Título de la Tarea:</label>
            <input type="text" id="title" name="title" value="<?php echo safe_echo($task['title']); ?>" required>

            <label for="description">Descripción:</label>
            <textarea id="description" name="description" required><?php echo safe_echo($task['description']); ?></textarea>

            <label for="start_date">Fecha de Inicio:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo safe_echo($task['start_date']); ?>" required>

            <label for="due_date">Fecha de Entrega:</label>
            <input type="date" id="due_date" name="due_date" value="<?php echo safe_echo($task['due_date']); ?>" required>

            <button type="submit" class="button">Actualizar Tarea</button>
        </form>

        <a href="teacher_dashboard.php" class="button">Volver al Dashboard</a>
    </div>
</body>
</html>