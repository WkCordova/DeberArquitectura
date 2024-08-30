<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'profesor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch tasks created by the teacher
$stmt = $pdo->prepare("
    SELECT t.*, 
           COUNT(s.id) as submissions_count, 
           SUM(CASE WHEN s.status = 'entregado' THEN 1 ELSE 0 END) as completed_count
    FROM tasks t
    LEFT JOIN submissions s ON t.id = s.task_id
    WHERE t.professor_id = ?
    GROUP BY t.id
    ORDER BY t.due_date
");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

// Handle new task submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $due_date = $_POST['due_date'] ?? '';

    if (!empty($title) && !empty($description) && !empty($start_date) && !empty($due_date)) {
        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, start_date, due_date, professor_id) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $description, $start_date, $due_date, $user_id])) {
            $message = "Tarea creada exitosamente.";
            // Refresh the page to show the new task
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error al crear la tarea. Por favor, inténtelo de nuevo.";
        }
    } else {
        $error = "Todos los campos son obligatorios.";
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
    <title>Dashboard del Profesor</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Bienvenido, <?php echo safe_echo($_SESSION['user_name']); ?></h1>
        
        <h2>Tus Tareas Asignadas</h2>
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Fecha de Inicio</th>
                    <th>Fecha de Entrega</th>
                    <th>Entregas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo safe_echo($task['title']); ?></td>
                        <td><?php echo safe_echo($task['description']); ?></td>
                        <td><?php echo safe_echo($task['start_date']); ?></td>
                        <td><?php echo safe_echo($task['due_date']); ?></td>
                        <td><?php echo $task['completed_count'] . ' / ' . $task['submissions_count']; ?></td>
                        <td>
                            <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="button">Editar</a>
                            <a href="view_submission.php?task_id=<?php echo $task['id']; ?>" class="button">Ver Entregas</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Enviar una Nueva Tarea</h2>
        <?php if (isset($error)): ?>
            <div class="error message"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($message)): ?>
            <div class="success message"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="task-form">
            <label for="title">Título de la Tarea:</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Descripción:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="start_date">Fecha de Inicio:</label>
            <input type="date" id="start_date" name="start_date" required>

            <label for="due_date">Fecha de Entrega:</label>
            <input type="date" id="due_date" name="due_date" required>

            <button type="submit" class="button">Crear Tarea</button>
        </form>

        <a href="logout.php" class="button">Cerrar sesión</a>
    </div>
</body>
</html>