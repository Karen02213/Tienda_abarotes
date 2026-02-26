<?php
session_start();
include 'includes/db.php';

// Mostrar el servidor que responde (Balanceo de carga)
$instance_id = getenv('INSTANCE_ID') ? getenv('INSTANCE_ID') : 'Servidor Local';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda de Abarrotes - <?php echo $instance_id; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .server-badge {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: #ff0000;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            z-index: 9999;
            box-shadow: 0 0 5px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>
    <div class="server-badge">Conectado a: <?php echo $instance_id; ?></div>
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
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="logout.php">Salir (<?php echo $_SESSION['username']; ?>)</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Ingresar</a></li>
                    <?php endif; ?>
                    
                    <li><a href="cart.php">Carrito <?php echo isset($_SESSION['cart']) ? '('.array_sum(array_column($_SESSION['cart'], 'quantity')).')' : '(0)'; ?></a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container showcase">
        <h1>Bienvenidos a nuestra Tienda</h1>
        <p>Los mejores productos frescos y de calidad.</p>
    </div>

    <div class="container">
        <h2>Nuestros Productos</h2>
        <div class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
            <?php
            $sql = "SELECT id, nombre, precio_venta, imagen_url, stock_actual FROM productos";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    ?>
                    <div class="product-card" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; text-align: center;">
                        <?php if($row['imagen_url']): ?>
                            <img src="<?php echo htmlspecialchars($row['imagen_url']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>" style="width: 100%; height: 150px; object-fit: cover; margin-bottom: 10px;">
                        <?php else: ?>
                            <div style="width: 100%; height: 150px; background: #eee; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                <i class="fas fa-image fa-3x" style="color: #ccc;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h3><?php echo htmlspecialchars($row['nombre']); ?></h3>
                        <p class="price">$<?php echo number_format($row['precio_venta'], 2); ?></p>
                        
                        <?php if($row['stock_actual'] > 0): ?>
                            <form action="cart.php" method="post">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['nombre']); ?>">
                                <input type="hidden" name="product_price" value="<?php echo $row['precio_venta']; ?>">
                                <button type="submit" class="btn" style="width: 100%; background: #28a745; color: white; border: none; padding: 10px; cursor: pointer;">Agregar al Carrito</button>
                            </form>
                        <?php else: ?>
                            <button disabled class="btn" style="width: 100%; background: #ccc; cursor: not-allowed;">Agotado</button>
                        <?php endif; ?>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No hay productos disponibles.</p>";
            }
            ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
