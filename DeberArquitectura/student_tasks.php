<?php
session_start();
include('config.php');

if ($_SESSION['role'] != 'alumno') {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

// Crear tarea
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    $sql = "INSERT INTO tasks (title, description, due_date, professor_id) VALUES (?, ?, ?, NULL)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $title, $description, $due_date);
    $stmt->execute();
}

// Actualizar tarea
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $task_id = $_POST['task_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    $sql = "UPDATE tasks SET title = ?, description = ?, due_date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $description, $due_date, $task_id);
    $stmt->execute();
}

// Eliminar tarea
if (isset($_GET['delete'])) {
    $task_id = $_GET['delete'];
    $sql = "DELETE FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
}

// Mostrar tareas
$sql = "SELECT * FROM tasks";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tareas</title>
</head>
<body>
    <h1>Tus Tareas</h1>

    <!-- Crear nueva tarea -->
    <form method="POST">
        <input type="text" name="title" placeholder="Título" required>
        <textarea name="description" placeholder="Descripción" required></textarea>
        <input type="date" name="due_date" required>
        <button type="submit" name="create">Crear Tarea</button>
    </form>

    <h2>Lista de Tareas</h2>
    <table>
        <tr>
            <th>Título</th>
            <th>Descripción</th>
            <th>Fecha de Entrega</th>
            <th>Acciones</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['title']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td><?php echo $row['due_date']; ?></td>
            <td>
                <!-- Actualizar tarea -->
                <form method="POST">
                    <input type="hidden" name="task_id" value="<?php echo $row['id']; ?>">
                    <input type="text" name="title" value="<?php echo $row['title']; ?>" required>
                    <textarea name="description" required><?php echo $row['description']; ?></textarea>
                    <input type="date" name="due_date" value="<?php echo $row['due_date']; ?>" required>
                    <button type="submit" name="update">Actualizar</button>
                </form>
                <!-- Eliminar tarea -->
                <a href="?delete=<?php echo $row['id']; ?>">Eliminar</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
