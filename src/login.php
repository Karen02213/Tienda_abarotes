<?php
session_start();
include 'includes/db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, nombre_usuario, password_hash, rol FROM usuarios WHERE nombre_usuario = '$username'";
    $result = $conn->query($sql);

    // Verificar si se encontró el usuario
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['nombre_usuario'];
            $_SESSION['role'] = $row['rol'];
            
            // Redireccionar según el rol
            if ($row['rol'] == 'admin' || $row['rol'] == 'cajero') {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no encontrado.";
    }
}

// Incluir header después de la lógica de redirección
include 'includes/header.php';
?>

<div class="container" style="max-width: 400px; margin-top: 50px;">
    <h2>Iniciar Sesión</h2>
    <?php if($error): ?>
        <p style="color: red;background: #ffe6e6; padding: 10px; border: 1px solid red; border-radius: 4px;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form action="" method="post">
        <div style="margin-bottom: 15px;">
            <label for="username">Usuario:</label>
            <input type="text" name="username" id="username" required style="width: 100%; padding: 8px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required style="width: 100%; padding: 8px;">
        </div>
        <button type="submit" class="btn" style="width: 100%;">Ingresar</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
