<?php
include '../includes/db.php';
include '../includes/helpers.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id == 0) redirect("ventas.php");

// Info Venta
$venta_sql = "SELECT v.*, c.nombre as cliente, u.nombre_usuario as vendedor 
              FROM ventas v 
              LEFT JOIN clientes c ON v.cliente_id = c.id
              LEFT JOIN usuarios u ON v.usuario_id = u.id
              WHERE v.id = $id";
$venta = $conn->query($venta_sql)->fetch_assoc();

// Detalles
$detalles_sql = "SELECT d.*, p.nombre 
                 FROM detalle_ventas d 
                 JOIN productos p ON d.producto_id = p.id 
                 WHERE d.venta_id = $id";
$detalles = $conn->query($detalles_sql);

render_header("Detalle de Venta #$id");
?>

<div class="table-container" style="max-width: 800px;">
    <div style="display:flex; justify-content:space-between; margin-bottom: 20px;">
        <div>
            <p><strong>Fecha:</strong> <?php echo $venta['fecha_venta']; ?></p>
            <p><strong>Cliente:</strong> <?php echo $venta['cliente'] ?: 'Público General'; ?></p>
            <p><strong>Vendedor:</strong> <?php echo $venta['vendedor']; ?></p>
        </div>
        <div style="text-align:right;">
             <h2>Total: $<?php echo number_format($venta['total'], 2); ?></h2>
             <p>Pago: <?php echo ucfirst($venta['metodo_pago']); ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while($d = $detalles->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($d['nombre']); ?></td>
                <td><?php echo $d['cantidad']; ?></td>
                <td>$<?php echo number_format($d['precio_unitario'], 2); ?></td>
                <td>$<?php echo number_format($d['subtotal'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>
    <a href="ventas.php" class="btn btn-warning">Volver</a>
    <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Imprimir</button>
</div>

<?php 
render_footer();
$conn->close(); 
?>
