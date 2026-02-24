<?php
session_start();
include 'includes/db.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Acción: Agregar al carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $id = $_POST['product_id'];
    
    // Verificar si ya existe para incrementar cantidad
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity']++;
    } else {
        $_SESSION['cart'][$id] = array(
            'name' => $_POST['product_name'],
            'price' => $_POST['product_price'],
            'quantity' => 1
        );
    }
    
    // Redirigir para evitar reenvío de formulario
    header("Location: cart.php");
    exit();
}

// Acción: Eliminar del carrito
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id = $_GET['id'];
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit();
}

// Acción: Completar compra (Generar Orden para el Dueño)
if (isset($_POST['checkout'])) {
    if (!empty($_SESSION['cart'])) {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $conn->begin_transaction();
        try {
            // Insertar "Orden Web"
            // Nota: Se usa un usuario genérico o NULL, estado 'pendiente', origen 'web'
            $sql = "INSERT INTO ventas (usuario_id, cliente_id, total, metodo_pago, origen, estado) VALUES (NULL, NULL, $total, 'efectivo', 'web', 'pendiente')";
            $conn->query($sql);
            $venta_id = $conn->insert_id;

            foreach ($_SESSION['cart'] as $id => $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $conn->query("INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES ($venta_id, $id, {$item['quantity']}, {$item['price']}, $subtotal)");
            }

            $conn->commit();
            // Limpiar carrito
            $_SESSION['cart'] = array();
            $success_msg = "¡Tu pedido ha sido enviado! El dueño recibirá la orden.";
        } catch (Exception $e) {
            $conn->rollback();
            $error_msg = "Hubo un error al procesar tu pedido.";
        }
    }
}

// Incluir header DESPUÉS de cualquier lógica de redirección
include 'includes/header.php';
?>

<div class="container">
    <h2>Tu Carrito de Compras</h2>

    <?php if(isset($success_msg)): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <?php if(empty($_SESSION['cart'])): ?>
        <p>Tu carrito está vacío. <a href="index.php">Ir a comprar</a>.</p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
                <tr style="background: #f4f4f4; border-bottom: 2px solid #ddd;">
                    <th style="padding: 10px; text-align: left;">Producto</th>
                    <th style="padding: 10px; text-align: center;">Cantidad</th>
                    <th style="padding: 10px; text-align: right;">Precio</th>
                    <th style="padding: 10px; text-align: right;">Subtotal</th>
                    <th style="padding: 10px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                foreach($_SESSION['cart'] as $id => $item): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;"><?php echo htmlspecialchars($item['name']); ?></td>
                    <td style="padding: 10px; text-align: center;"><?php echo $item['quantity']; ?></td>
                    <td style="padding: 10px; text-align: right;">$<?php echo number_format($item['price'], 2); ?></td>
                    <td style="padding: 10px; text-align: right;">$<?php echo number_format($subtotal, 2); ?></td>
                    <td style="padding: 10px; text-align: center;">
                        <a href="cart.php?action=remove&id=<?php echo $id; ?>" style="color: red;"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" style="font-weight: bold; text-align: right; padding: 10px;">Total:</td>
                    <td style="font-weight: bold; text-align: right; padding: 10px;">$<?php echo number_format($total, 2); ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div style="text-align: right;">
            <a href="index.php" class="btn" style="background: #ccc; color: #333;">Seguir comprando</a>
            <form method="post" style="display: inline;">
                <button type="submit" name="checkout" class="btn" style="background: #28a745;">Confirmar Pedido</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
