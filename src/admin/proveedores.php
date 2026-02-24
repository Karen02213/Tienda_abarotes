<?php
include '../includes/db.php';
include '../includes/helpers.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $contacto = $conn->real_escape_string($_POST['contacto']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $email = $conn->real_escape_string($_POST['email']);
    $direccion = $conn->real_escape_string($_POST['direccion']);

    if ($action == 'create') {
        $sql = "INSERT INTO proveedores (nombre_empresa, contacto_nombre, telefono, email, direccion) VALUES ('$nombre', '$contacto', '$telefono', '$email', '$direccion')";
        if ($conn->query($sql)) redirect("proveedores.php?msg=Proveedor creado");
        else $error = $conn->error;
    } elseif ($action == 'edit' && $id > 0) {
        $sql = "UPDATE proveedores SET nombre_empresa='$nombre', contacto_nombre='$contacto', telefono='$telefono', email='$email', direccion='$direccion' WHERE id=$id";
        if ($conn->query($sql)) redirect("proveedores.php?msg=Proveedor actualizado");
        else $error = $conn->error;
    }
}

if ($action == 'delete' && $id > 0) {
    if ($conn->query("DELETE FROM proveedores WHERE id=$id")) redirect("proveedores.php?msg=Eliminado");
}

render_header("Gestión de Proveedores");
get_flash_message();

if ($action == 'create' || $action == 'edit') {
    $row = ['nombre_empresa'=>'', 'contacto_nombre'=>'', 'telefono'=>'', 'email'=>'', 'direccion'=>''];
    if ($action == 'edit' && $id > 0) {
        $res = $conn->query("SELECT * FROM proveedores WHERE id=$id");
        if ($res->num_rows > 0) $row = $res->fetch_assoc();
    }
    ?>
    <div class="table-container" style="max-width: 600px;">
        <h3><?php echo ($action=='create')?'Nuevo':'Editar'; ?> Proveedor</h3>
        <form method="POST">
            <div class="form-group"><label>Empresa:</label><input type="text" name="nombre" value="<?php echo htmlspecialchars($row['nombre_empresa']); ?>" required></div>
            <div class="form-group"><label>Contacto:</label><input type="text" name="contacto" value="<?php echo htmlspecialchars($row['contacto_nombre']); ?>"></div>
            <div class="form-group"><label>Teléfono:</label><input type="text" name="telefono" value="<?php echo htmlspecialchars($row['telefono']); ?>"></div>
            <div class="form-group"><label>Email:</label><input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>"></div>
            <div class="form-group"><label>Dirección:</label><textarea name="direccion"><?php echo htmlspecialchars($row['direccion']); ?></textarea></div>
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="proveedores.php" class="btn btn-warning">Cancelar</a>
        </form>
    </div>
    <?php
} else {
    $result = $conn->query("SELECT * FROM proveedores");
    ?>
    <a href="proveedores.php?action=create" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Proveedor</a>
    <div class="table-container">
        <table>
            <thead><tr><th>Empresa</th><th>Contacto</th><th>Teléfono</th><th>Email</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php while ($r = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['nombre_empresa']); ?></td>
                    <td><?php echo htmlspecialchars($r['contacto_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($r['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($r['email']); ?></td>
                    <td class="actions">
                        <a href="proveedores.php?action=edit&id=<?php echo $r['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                        <a href="proveedores.php?action=delete&id=<?php echo $r['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro?');"><i class="fas fa-trash"></i></a>
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
