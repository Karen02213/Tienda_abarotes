<?php
include '../includes/db.php';
include '../includes/helpers.php';

$sql = "SELECT c.*, p.nombre_empresa as proveedor, u.nombre_usuario as usuario
        FROM compras c 
        LEFT JOIN proveedores p ON c.proveedor_id = p.id
        LEFT JOIN usuarios u ON c.usuario_id = u.id
        ORDER BY c.fecha_compra DESC";
$result = $conn->query($sql);

render_header("Historial de Compras");
get_flash_message();
?>

<a href="nueva_compra.php" class="btn btn-primary"><i class="fas fa-shopping-basket"></i> Nueva Compra</a>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Registrado por</th>
                <th>Factura Ref.</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td><?php echo date("d/m/Y H:i", strtotime($row['fecha_compra'])); ?></td>
                <td><?php echo htmlspecialchars($row['proveedor']); ?></td>
                <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                <td><?php echo htmlspecialchars($row['numero_factura']); ?></td>
                <td style="font-weight:bold; color:red;">$<?php echo number_format($row['total'], 2); ?></td>
                <td>
                    <a href="ver_compra.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Detalles</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php 
render_footer();
$conn->close(); 
?>
