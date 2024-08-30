<?php
header('Content-Type: application/json');
include('config.php');

// Obtener el ID del estudiante desde el parámetro GET
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

if ($student_id <= 0) {
    echo json_encode(['error' => 'ID de estudiante inválido']);
    exit;
}

// Consulta para obtener los datos del seguimiento
$sql = "
    SELECT 
        t.title AS task_title,
        s.submission_date,
        s.difficulty_rating,
        s.comment,
        u.email AS student_email
    FROM submissions s
    JOIN tasks t ON s.task_id = t.id
    JOIN users u ON s.student_id = u.id
    WHERE s.student_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Cerrar la conexión a la base de datos
$conn->close();

// Enviar respuesta en formato JSON
echo json_encode($data);
?>
