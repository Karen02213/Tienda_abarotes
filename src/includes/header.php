<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda de Abarrotes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- FontAwesome para íconos (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1><span class="highlight">Tienda</span> Abarrotes</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <?php if(isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'cajero')): ?>
                        <li><a href="admin/index.php">Panel Admin</a></li>
                        <li><a href="logout.php">Salir</a></li>
                    <?php elseif(isset($_SESSION['user_id'])): ?>
                         <li><a href="logout.php">Salir</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login Dueño</a></li>
                    <?php endif; ?>
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Carrito <span id="cart-count"></span></a></li>
                </ul>
            </nav>
        </div>
    </header>
    <div class="container">
