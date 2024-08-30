<?php
session_start();
include('config.php');

// Verificar que el usuario es un profesor
if ($_SESSION['role'] !== 'profesor') {
    header("Location: login.php"); // Redirigir a login si el usuario no es profesor
    exit;
}

// Consulta para obtener las tareas enviadas por los estudiantes
$sql = "
    SELECT 
        s.id AS submission_id,
        t.title AS task_title,
        u.email AS student_email,
        s.submission_date,
        s.difficulty_rating,
        s.comment
    FROM submissions s
    JOIN tasks t ON s.task_id = t.id
    JOIN users u ON s.student_id = u.id
    ORDER BY s.submission_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Cerrar la conexión a la base de datos
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Tareas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            width: 100%;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
        button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Seguimiento de Tareas</h1>
        <button onclick="location.reload();">Actualizar Datos</button>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título de la Tarea</th>
                    <th>Email del Estudiante</th>
                    <th>Fecha de Entrega</th>
                    <th>Valoración de Dificultad</th>
                    <th>Comentario</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['submission_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['task_title']); ?></td>
                        <td><?php echo htmlspecialchars($row['student_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['difficulty_rating']); ?></td>
                        <td><?php echo htmlspecialchars($row['comment']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
