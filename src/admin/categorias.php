<?php
include '../includes/db.php';
include '../includes/helpers.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// PROCESAR FORMULARIO (CREAR/EDITAR)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);

    if ($action == 'create') {
        $sql = "INSERT INTO categorias (nombre, descripcion) VALUES ('$nombre', '$descripcion')";
        if ($conn->query($sql)) {
            redirect("categorias.php?msg=Categoría creada&type=success");
        } else {
            $error = "Error: " . $conn->error;
        }
    } elseif ($action == 'edit' && $id > 0) {
        $sql = "UPDATE categorias SET nombre='$nombre', descripcion='$descripcion' WHERE id=$id";
        if ($conn->query($sql)) {
            redirect("categorias.php?msg=Categoría actualizada&type=success");
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// PROCESAR ELIMINAR
if ($action == 'delete' && $id > 0) {
    $sql = "DELETE FROM categorias WHERE id=$id";
    if ($conn->query($sql)) {
        redirect("categorias.php?msg=Categoría eliminada&type=success");
    } else {
        redirect("categorias.php?msg=Error al eliminar: " . $conn->error . "&type=error");
    }
}

render_header("Gestión de Categorías");
get_flash_message();

// VISTA FORMULARIO
if ($action == 'create' || ($action == 'edit' && $id > 0)) {
    $row = ['nombre' => '', 'descripcion' => ''];
    if ($action == 'edit') {
        $result = $conn->query("SELECT * FROM categorias WHERE id=$id");
        if ($result->num_rows > 0) $row = $result->fetch_assoc();
    }
    ?>
    <div class="table-container" style="max-width: 600px;">
        <h3><?php echo ($action == 'create') ? 'Nueva Categoría' : 'Editar Categoría'; ?></h3>
        <form method="POST">
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($row['nombre']); ?>" required>
            </div>
            <div class="form-group">
                <label>Descripción:</label>
                <textarea name="descripcion" rows="3"><?php echo htmlspecialchars($row['descripcion']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
            <a href="categorias.php" class="btn btn-warning">Cancelar</a>
        </form>
    </div>
    <?php
} else {
    // VISTA LISTA
    $result = $conn->query("SELECT * FROM categorias");
    ?>
    <a href="categorias.php?action=create" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Categoría</a>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                    <td class="actions">
                        <a href="categorias.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                        <a href="categorias.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro?');"><i class="fas fa-trash"></i></a>
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
