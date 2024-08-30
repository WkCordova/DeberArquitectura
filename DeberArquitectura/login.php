<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session at the very beginning
session_start();

require_once 'config.php';

$message = "";

// Debug: Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email inválido.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, role, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Debug: Print session data before redirect
                error_log("Session data before redirect: " . print_r($_SESSION, true));

                // Redirect based on user role
                if ($user['role'] == 'alumno') {
                    header("Location: student_dashboard.php");
                } elseif ($user['role'] == 'profesor') {
                    header("Location: teacher_dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit(); // Make sure to exit after redirect
            } else {
                $message = "Email o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            // Log database errors
            error_log("Database error: " . $e->getMessage());
            $message = "Error del sistema. Por favor, inténtelo más tarde.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Inicio de Sesión</h1>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'incorrectos') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="email">Correo electrónico:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Iniciar sesión</button>
        </form>
        <div class="register-link">
            ¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a>
        </div>
    </div>
</body>
</html>