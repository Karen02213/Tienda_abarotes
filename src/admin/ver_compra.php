<?php
include '../includes/db.php';
include '../includes/helpers.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id == 0) redirect("compras.php");

$compra = $conn->query("SELECT c.*, p.nombre_empresa as proveedor, u.nombre_usuario as usuario 
                        FROM compras c 
                        LEFT JOIN proveedores p ON c.proveedor_id = p.id
                        LEFT JOIN usuarios u ON c.usuario_id = u.id
                        WHERE c.id = $id")->fetch_assoc();

$detalles = $conn->query("SELECT d.*, p.nombre FROM detalle_compras d JOIN productos p ON d.producto_id = p.id WHERE d.compra_id = $id");

render_header("Detalle de Compra #$id");
?>

<div class="table-container" style="max-width: 800px;">
    <div style="display:flex; justify-content:space-between; margin-bottom: 20px;">
        <div>
            <p><strong>Fecha:</strong> <?php echo $compra['fecha_compra']; ?></p>
            <p><strong>Proveedor:</strong> <?php echo $compra['proveedor']; ?></p>
            <p><strong>Factura:</strong> <?php echo $compra['numero_factura']; ?></p>
        </div>
        <div style="text-align:right;">
             <h2>Total: $<?php echo number_format($compra['total'], 2); ?></h2>
             <p>Registrada por: <?php echo $compra['usuario']; ?></p>
        </div>
    </div>

    <table>
        <thead><tr><th>Producto</th><th>Cantidad</th><th>Costo Unit.</th><th>Subtotal</th></tr></thead>
        <tbody>
            <?php while($d = $detalles->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($d['nombre']); ?></td>
                <td><?php echo $d['cantidad']; ?></td>
                <td>$<?php echo number_format($d['costo_unitario'], 2); ?></td>
                <td>$<?php echo number_format($d['subtotal'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>
    <a href="compras.php" class="btn btn-warning">Volver</a>
    <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Imprimir</button>
</div>

<?php render_footer(); $conn->close(); ?>
