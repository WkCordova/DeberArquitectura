<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['alumno', 'profesor'])) {
    header("Location: login.php");
    exit();
}

$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Fetch submission details
if ($user_role === 'alumno') {
    $stmt = $pdo->prepare("
        SELECT s.*, t.title as task_title, t.description as task_description, t.due_date, u.name as student_name
        FROM tasks t
        LEFT JOIN submissions s ON t.id = s.task_id AND s.student_id = ?
        LEFT JOIN users u ON s.student_id = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$user_id, $task_id]);
} else { // profesor
    $stmt = $pdo->prepare("
        SELECT s.*, t.title as task_title, t.description as task_description, t.due_date, u.name as student_name
        FROM tasks t
        LEFT JOIN submissions s ON t.id = s.task_id
        LEFT JOIN users u ON s.student_id = u.id
        WHERE t.id = ? AND t.professor_id = ?
    ");
    $stmt->execute([$task_id, $user_id]);
}

$submissions = $stmt->fetchAll();

if (empty($submissions)) {
    die("Task not found or you don't have permission to view it.");
}

$task = $submissions[0]; // Task details are the same for all submissions

function safe_echo($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Tarea y Entregas</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Detalles de la Tarea</h1>
        <h2><?php echo safe_echo($task['task_title']); ?></h2>
        <p><strong>Descripción de la tarea:</strong> <?php echo safe_echo($task['task_description']); ?></p>
        <p><strong>Fecha de entrega:</strong> <?php echo safe_echo($task['due_date']); ?></p>
        
        <?php if ($user_role === 'alumno'): ?>
            <?php if ($task['status']): ?>
                <h3>Tu Entrega</h3>
                <p><strong>Estado de entrega:</strong> <?php echo safe_echo($task['status']); ?></p>
                <p><strong>Fecha de entrega:</strong> <?php echo safe_echo($task['submission_date']); ?></p>
                <div class="submission-content">
                    <p><strong>Tu respuesta:</strong></p>
                    <?php echo nl2br(safe_echo($task['explanation'])); ?>
                </div>
            <?php else: ?>
                <p>Aún no has entregado esta tarea.</p>
                <a href="submit_task.php?task_id=<?php echo $task_id; ?>" class="button">Entregar Tarea</a>
            <?php endif; ?>
        <?php else: // profesor ?>
            <h3>Entregas de los Estudiantes</h3>
            <?php if (empty($submissions) || !$submissions[0]['student_name']): ?>
                <p>Aún no hay entregas para esta tarea.</p>
            <?php else: ?>
                <?php foreach ($submissions as $submission): ?>
                    <div class="submission">
                        <h4>Estudiante: <?php echo safe_echo($submission['student_name']); ?></h4>
                        <p><strong>Estado de entrega:</strong> <?php echo safe_echo($submission['status']); ?></p>
                        <p><strong>Fecha de entrega:</strong> <?php echo safe_echo($submission['submission_date']); ?></p>
                        <div class="submission-content">
                            <p><strong>Respuesta del estudiante:</strong></p>
                            <?php echo nl2br(safe_echo($submission['explanation'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
        
        <a href="<?php echo $user_role === 'alumno' ? 'student_dashboard.php' : 'teacher_dashboard.php'; ?>" class="button">Volver al Dashboard</a>
    </div>
</body>
</html>