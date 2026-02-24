<?php
include '../includes/db.php';
include '../includes/helpers.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $email = $conn->real_escape_string($_POST['email']);
    $direccion = $conn->real_escape_string($_POST['direccion']);

    if ($action == 'create') {
        $sql = "INSERT INTO clientes (nombre, telefono, email, direccion) VALUES ('$nombre', '$telefono', '$email', '$direccion')";
        if ($conn->query($sql)) redirect("clientes.php?msg=Cliente creado");
    } elseif ($action == 'edit' && $id > 0) {
        $sql = "UPDATE clientes SET nombre='$nombre', telefono='$telefono', email='$email', direccion='$direccion' WHERE id=$id";
        if ($conn->query($sql)) redirect("clientes.php?msg=Cliente actualizado");
    }
}

if ($action == 'delete' && $id > 0) {
    if ($conn->query("DELETE FROM clientes WHERE id=$id")) redirect("clientes.php?msg=Eliminado");
}

render_header("Gestión de Clientes");
get_flash_message();

if ($action == 'create' || $action == 'edit') {
    $row = ['nombre'=>'', 'telefono'=>'', 'email'=>'', 'direccion'=>''];
    if ($action == 'edit' && $id > 0) {
        $res = $conn->query("SELECT * FROM clientes WHERE id=$id");
        if ($res->num_rows > 0) $row = $res->fetch_assoc();
    }
    ?>
    <div class="table-container" style="max-width: 600px;">
        <h3><?php echo ($action=='create')?'Nuevo':'Editar'; ?> Cliente</h3>
        <form method="POST">
            <div class="form-group"><label>Nombre:</label><input type="text" name="nombre" value="<?php echo htmlspecialchars($row['nombre']); ?>" required></div>
            <div class="form-group"><label>Teléfono:</label><input type="text" name="telefono" value="<?php echo htmlspecialchars($row['telefono']); ?>"></div>
            <div class="form-group"><label>Email:</label><input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>"></div>
            <div class="form-group"><label>Dirección:</label><textarea name="direccion"><?php echo htmlspecialchars($row['direccion']); ?></textarea></div>
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="clientes.php" class="btn btn-warning">Cancelar</a>
        </form>
    </div>
    <?php
} else {
    $result = $conn->query("SELECT * FROM clientes");
    ?>
    <a href="clientes.php?action=create" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Cliente</a>
    <div class="table-container">
        <table>
            <thead><tr><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Registro</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php while ($r = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($r['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($r['email']); ?></td>
                    <td><?php echo $r['fecha_registro']; ?></td>
                    <td class="actions">
                        <a href="clientes.php?action=edit&id=<?php echo $r['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                        <a href="clientes.php?action=delete&id=<?php echo $r['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro?');"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php
}
render_footer();
$conn->close();
?>
