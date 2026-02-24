<?php
// helpers.php - Funciones de utilidad para el panel de administración
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteger Admin: Si no hay usuario en sesión o no es admin/cajero, redirigir al login
if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'cajero')) {
        header("Location: ../login.php");
        exit();
    }
}

function render_header($title = "Panel de Administración") {
    global $conn;
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title; ?> - Admin</title>
        <link rel="stylesheet" href="../assets/css/admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <div id="sidebar">
            <div style="text-align: center; margin-bottom: 20px;">
                <h2 style="margin-bottom: 5px; color: #17a2b8;"><i class="fas fa-store"></i> Tienda</h2>
                <small style="color: #ccc;">Hola, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></small>
            </div>
            
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                <li><a href="ordenes_web.php"><i class="fas fa-globe"></i> Pedidos Web</a></li>
                <li><a href="ventas.php"><i class="fas fa-cash-register"></i> Punto de Venta</a></li>
                <li><a href="productos.php"><i class="fas fa-box"></i> Productos</a></li>
                <li><a href="categorias.php"><i class="fas fa-tags"></i> Categorías</a></li>
                <li><a href="proveedores.php"><i class="fas fa-truck"></i> Proveedores</a></li>
                <li><a href="clientes.php"><i class="fas fa-users"></i> Clientes</a></li>
                <?php if($_SESSION['role'] == 'admin'): ?>
                <li><a href="usuarios.php"><i class="fas fa-user-shield"></i> Usuarios</a></li>
                <?php endif; ?>
                <li><a href="compras.php"><i class="fas fa-shopping-basket"></i> Compras</a></li>
                <li style="border-top: 1px solid #555; margin-top: 10px; padding-top: 10px;">
                    <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Tienda</a>
                </li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
            </ul>
        </div>
        <div id="content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                <h1><?php echo $title; ?></h1>
            </div>
            <hr>
            <br>
    <?php
}

function render_footer() {
    ?>
        </div>
    </body>
    </html>
    <?php
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function get_flash_message() {
    if (isset($_GET['msg'])) {
        $type = isset($_GET['type']) ? $_GET['type'] : 'success';
        $class = ($type == 'error') ? 'error' : 'success';
        echo "<div class='flash-message $class'>" . htmlspecialchars($_GET['msg']) . "</div>";
    }
}
?>
