<?php
include '../includes/db.php';
include '../includes/helpers.php';

// Acción: Completar orden (aceptarla y descontar stock real, o marcar como lista)
if (isset($_GET['action']) && $_GET['action'] == 'complete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Al completar una orden web, podríamos querer validar stock nuevamente
    // Por simplicidad, asumimos que al aceptarla se confirma la transacción
    // Si ya se descontó stock al crear (en cart.php no se descontó, solo se registró), debemos descontar AHORA.
    
    // 1. Obtener detalles
    $detalles = $conn->query("SELECT * FROM detalle_ventas WHERE venta_id = $id");
    while($d = $detalles->fetch_assoc()) {
        // Descontar inventario
        $conn->query("UPDATE productos SET stock_actual = stock_actual - {$d['cantidad']} WHERE id = {$d['producto_id']}");
    }
    
    $conn->query("UPDATE ventas SET estado = 'completado' WHERE id = $id");
    redirect("ordenes_web.php?msg=Orden completada y stock descontado");
}

if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("UPDATE ventas SET estado = 'cancelado' WHERE id = $id");
    redirect("ordenes_web.php?msg=Orden cancelada&type=error");
}

$sql = "SELECT v.*, c.nombre as cliente 
        FROM ventas v 
        LEFT JOIN clientes c ON v.cliente_id = c.id
        WHERE v.origen = 'web' 
        ORDER BY field(v.estado, 'pendiente', 'completado', 'cancelado'), v.fecha_venta DESC";
$result = $conn->query($sql);

render_header("Órdenes Web (Pedidos Online)");
get_flash_message();
?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID Orden</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr style="<?php echo ($row['estado']=='pendiente') ? 'background-color: #fff3cd;' : ''; ?>">
                <td>#<?php echo $row['id']; ?></td>
                <td><?php echo date("d/m/Y H:i", strtotime($row['fecha_venta'])); ?></td>
                <td>
                    <?php if($row['estado'] == 'pendiente'): ?>
                        <span class="badge badge-warning" style="background: orange; color:white; padding: 4px 8px; border-radius: 4px;">Pendiente</span>
                    <?php elseif($row['estado'] == 'completado'): ?>
                        <span class="badge badge-success" style="background: green; color:white; padding: 4px 8px; border-radius: 4px;">Completado</span>
                     <?php else: ?>
                        <span class="badge badge-danger" style="background: gray; color:white; padding: 4px 8px; border-radius: 4px;">Cancelado</span>
                    <?php endif; ?>
                </td>
                <td style="font-weight:bold;">$<?php echo number_format($row['total'], 2); ?></td>
                <td>
                    <a href="ver_venta.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Ver Detalles</a>
                    
                    <?php if($row['estado'] == 'pendiente'): ?>
                        <a href="ordenes_web.php?action=complete&id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('¿Aceptar pedido y descontar stock?');"><i class="fas fa-check"></i> Surtir</a>
                        <a href="ordenes_web.php?action=cancel&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Rechazar pedido?');"><i class="fas fa-times"></i> Cancelar</a>
                    <?php endif; ?>
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
