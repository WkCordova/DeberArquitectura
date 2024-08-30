<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header("Location: login.php");
    exit();
}

// Define the safe_echo function
function safe_echo($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Fetch tasks for the student
$stmt = $pdo->prepare("
    SELECT t.*, IFNULL(s.status, 'no entregado') as submission_status 
    FROM tasks t
    LEFT JOIN submissions s ON t.id = s.task_id AND s.student_id = ?
    ORDER BY t.due_date
");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard del Estudiante</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Bienvenido, <?php echo safe_echo($_SESSION['user_name']); ?></h1>
        <h2>Tus Tareas</h2>
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Fecha de Inicio</th>
                    <th>Fecha de Entrega</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td data-label="Título"><?php echo safe_echo($task['title']); ?></td>
                        <td data-label="Descripción"><?php echo safe_echo($task['description']); ?></td>
                        <td data-label="Fecha de Inicio"><?php echo safe_echo($task['start_date']); ?></td>
                        <td data-label="Fecha de Entrega"><?php echo safe_echo($task['due_date']); ?></td>
                        <td data-label="Estado"><?php echo safe_echo($task['submission_status']); ?></td>
                        <td data-label="Acción">
                            <?php if ($task['submission_status'] == 'no entregado'): ?>
                                <a href="submit_task.php?task_id=<?php echo $task['id']; ?>" class="button">Entregar</a>
                            <?php else: ?>
                                <a href="view_submission.php?task_id=<?php echo $task['id']; ?>" class="button">Ver Entrega</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="logout.php" class="button mt-20">Cerrar sesión</a>
    </div>
</body>
</html>