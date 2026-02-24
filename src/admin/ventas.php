<?php
include '../includes/db.php';
include '../includes/helpers.php';

$sql = "SELECT v.*, c.nombre as cliente, u.nombre_usuario as vendedor 
        FROM ventas v 
        LEFT JOIN clientes c ON v.cliente_id = c.id
        LEFT JOIN usuarios u ON v.usuario_id = u.id
        ORDER BY v.fecha_venta DESC";
$result = $conn->query($sql);

render_header("Historial de Ventas");
get_flash_message();
?>

<a href="nueva_venta.php" class="btn btn-primary"><i class="fas fa-cash-register"></i> Nueva Venta</a>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Método</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td><?php echo date("d/m/Y H:i", strtotime($row['fecha_venta'])); ?></td>
                <td><?php echo $row['cliente'] ? htmlspecialchars($row['cliente']) : 'Público General'; ?></td>
                <td><?php echo htmlspecialchars($row['vendedor'] ?? 'Sistema/Web'); ?></td>
                <td><?php echo ucfirst($row['metodo_pago']); ?></td>
                <td style="font-weight:bold; color:green;">$<?php echo number_format($row['total'], 2); ?></td>
                <td>
                    <a href="ver_venta.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Detalles</a>
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
