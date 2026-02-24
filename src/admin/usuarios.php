<?php
include '../includes/db.php';
include '../includes/helpers.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $conn->real_escape_string($_POST['usuario']);
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $rol = $conn->real_escape_string($_POST['rol']);
    
    // Contraseña solo si se escribe una nueva
    $password_sql = "";
    if (!empty($_POST['password'])) {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_sql = ", password_hash='$hash'";
    }

    if ($action == 'create') {
        // Para create la contraseña es obligatoria si no se valida en JS, aquí asumimos que viene
        if (!empty($_POST['password'])) {
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nombre_usuario, password_hash, rol, nombre_completo) VALUES ('$usuario', '$hash', '$rol', '$nombre')";
            if ($conn->query($sql)) redirect("usuarios.php?msg=Usuario creado");
        }
    } elseif ($action == 'edit' && $id > 0) {
        $sql = "UPDATE usuarios SET nombre_usuario='$usuario', rol='$rol', nombre_completo='$nombre' $password_sql WHERE id=$id";
        if ($conn->query($sql)) redirect("usuarios.php?msg=Usuario actualizado");
    }
}

if ($action == 'delete' && $id > 0) {
    if ($conn->query("DELETE FROM usuarios WHERE id=$id")) redirect("usuarios.php?msg=Usuario eliminado");
}

render_header("Gestión de Usuarios");
get_flash_message();

if ($action == 'create' || $action == 'edit') {
    $row = ['nombre_usuario'=>'', 'nombre_completo'=>'', 'rol'=>'cajero'];
    if ($action == 'edit' && $id > 0) {
        $res = $conn->query("SELECT * FROM usuarios WHERE id=$id");
        if ($res->num_rows > 0) $row = $res->fetch_assoc();
    }
    ?>
    <div class="table-container" style="max-width: 600px;">
        <h3><?php echo ($action=='create')?'Nuevo':'Editar'; ?> Usuario</h3>
        <form method="POST">
            <div class="form-group"><label>Usuario:</label><input type="text" name="usuario" value="<?php echo htmlspecialchars($row['nombre_usuario']); ?>" required></div>
            <div class="form-group"><label>Nombre Completo:</label><input type="text" name="nombre" value="<?php echo htmlspecialchars($row['nombre_completo']); ?>"></div>
            <div class="form-group"><label>Rol:</label>
                <select name="rol">
                    <option value="admin" <?php if($row['rol']=='admin') echo 'selected'; ?>>Admin</option>
                    <option value="cajero" <?php if($row['rol']=='cajero') echo 'selected'; ?>>Cajero</option>
                </select>
            </div>
            <div class="form-group">
                <label>Contraseña: <?php if($action=='edit') echo '<small>(Dejar en blanco para no cambiar)</small>'; ?></label>
                <input type="password" name="password" <?php if($action=='create') echo 'required'; ?>>
            </div>
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="usuarios.php" class="btn btn-warning">Cancelar</a>
        </form>
    </div>
    <?php
} else {
    $result = $conn->query("SELECT * FROM usuarios");
    ?>
    <a href="usuarios.php?action=create" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Usuario</a>
    <div class="table-container">
        <table>
            <thead><tr><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php while ($r = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['nombre_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($r['nombre_completo']); ?></td>
                    <td>
                        <span class="badge <?php echo ($r['rol']=='admin')?'badge-danger':'badge-info'; ?>">
                            <?php echo strtoupper($r['rol']); ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="usuarios.php?action=edit&id=<?php echo $r['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                        <?php if($r['nombre_usuario'] != 'admin'): // Proteger admin principal ?> 
                        <a href="usuarios.php?action=delete&id=<?php echo $r['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro?');"><i class="fas fa-trash"></i></a>
                        <?php endif; ?>
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
