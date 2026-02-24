<?php
include '../includes/db.php';
include '../includes/helpers.php';

$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = $conn->real_escape_string($_POST['codigo']);
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $descripcion = $conn->real_escape_string($_POST['descripcion'] ?? '');
    $precio_compra = floatval($_POST['precio_compra']);
    $precio_venta = floatval($_POST['precio_venta']);
    $stock = intval($_POST['stock']);
    $minimo = intval($_POST['minimo']);
    
    // Manejo correcto de nulos para claves foráneas
    $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : "NULL";
    $proveedor_id = !empty($_POST['proveedor_id']) ? intval($_POST['proveedor_id']) : "NULL";

    // Manejo de la imagen (subida de archivo)
    $imagen_url = $conn->real_escape_string($_POST['imagen_url_actual'] ?? '');
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["imagen"]["name"];
        $filetype = $_FILES["imagen"]["type"];
        $filesize = $_FILES["imagen"]["size"];
    
        // Verificar extensión
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)) die("Error: Formato de archivo no válido.");
    
        // Verificar tamaño (ej. max 5MB)
        if($filesize > 5 * 1024 * 1024) die("Error: El archivo es demasiado grande.");
    
        // Mover archivo
        if(in_array($filetype, $allowed)){
            $new_filename = uniqid() . "." . $ext;
            if(move_uploaded_file($_FILES["imagen"]["tmp_name"], "../assets/uploads/" . $new_filename)){
                $imagen_url = "assets/uploads/" . $new_filename;
            }
        }
    }

    if ($action == 'create') {
        $sql = "INSERT INTO productos (codigo_barras, nombre, descripcion, precio_compra, precio_venta, stock_actual, stock_minimo, categoria_id, proveedor_id, imagen_url) 
                VALUES ('$codigo', '$nombre', '$descripcion', $precio_compra, $precio_venta, $stock, $minimo, $categoria_id, $proveedor_id, '$imagen_url')";
        
        if ($conn->query($sql)) {
            // Redireccionar para evitar reenvío de formulario
            header("Location: productos.php?msg=Producto creado");
            exit();
        } else {
            echo "Error al crear producto: " . $conn->error;
        }

    } elseif ($action == 'edit' && $id > 0) {
        $sql = "UPDATE productos SET 
                codigo_barras='$codigo', 
                nombre='$nombre', 
                descripcion='$descripcion', 
                precio_compra=$precio_compra, 
                precio_venta=$precio_venta, 
                stock_actual=$stock, 
                stock_minimo=$minimo, 
                categoria_id=$categoria_id, 
                proveedor_id=$proveedor_id, 
                imagen_url='$imagen_url' 
                WHERE id=$id";
        
        if ($conn->query($sql)) {
             // Redireccionar para evitar reenvío de formulario
             header("Location: productos.php?msg=Producto actualizado");
             exit();
        } else {
            echo "Error al actualizar producto: " . $conn->error;
        }
    }
}

if ($action == 'delete' && $id > 0) {
    if ($conn->query("DELETE FROM productos WHERE id=$id")) {
        header("Location: productos.php?msg=Eliminado");
        exit();
    }
}

render_header("Gestión de Productos");
get_flash_message();

// Obtener listas desplegables
$categorias = $conn->query("SELECT * FROM categorias");
$proveedores = $conn->query("SELECT * FROM proveedores");

if ($action == 'create' || $action == 'edit') {
    $row = [
        'codigo_barras'=>'', 'nombre'=>'', 'descripcion'=>'', 'precio_compra'=>'0.00', 'precio_venta'=>'0.00',
        'stock_actual'=>'0', 'stock_minimo'=>'5', 'categoria_id'=>'', 'proveedor_id'=>'', 'imagen_url'=>''
    ];
    if ($action == 'edit' && $id > 0) {
        $res = $conn->query("SELECT * FROM productos WHERE id=$id");
        if ($res->num_rows > 0) $row = $res->fetch_assoc();
    }
    ?>
    <div class="table-container" style="max-width: 800px;">
        <h3><?php echo ($action=='create')?'Nuevo':'Editar'; ?> Producto</h3>
        
        <!-- Importante: enctype para subir archivos -->
        <form method="POST" enctype="multipart/form-data">
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group"><label>Código Barras:</label><input type="text" name="codigo" value="<?php echo htmlspecialchars($row['codigo_barras'] ?? ''); ?>"></div>
                <div class="form-group"><label>Nombre:</label><input type="text" name="nombre" value="<?php echo htmlspecialchars($row['nombre'] ?? ''); ?>" required></div>
                
                <div class="form-group"><label>Precio Compra:</label><input type="number" step="0.01" name="precio_compra" value="<?php echo $row['precio_compra']; ?>" required></div>
                <div class="form-group"><label>Precio Venta:</label><input type="number" step="0.01" name="precio_venta" value="<?php echo $row['precio_venta']; ?>" required></div>
                
                <div class="form-group"><label>Stock Actual:</label><input type="number" name="stock" value="<?php echo $row['stock_actual']; ?>"></div>
                <div class="form-group"><label>Stock Mínimo:</label><input type="number" name="minimo" value="<?php echo $row['stock_minimo']; ?>"></div>
            
                <div class="form-group"><label>Categoría:</label>
                    <select name="categoria_id">
                        <option value="">Seleccione...</option>
                        <?php 
                        if ($categorias) {
                            $categorias->data_seek(0);
                            while($c = $categorias->fetch_assoc()) {
                                $sel = ($c['id'] == ($row['categoria_id'] ?? '')) ? 'selected' : '';
                                echo "<option value='{$c['id']}' $sel>{$c['nombre']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group"><label>Proveedor:</label>
                    <select name="proveedor_id">
                        <option value="">Seleccione...</option>
                        <?php 
                        if ($proveedores) {
                            $proveedores->data_seek(0);
                            while($p = $proveedores->fetch_assoc()) {
                                $sel = ($p['id'] == ($row['proveedor_id'] ?? '')) ? 'selected' : '';
                                echo "<option value='{$p['id']}' $sel>{$p['nombre_empresa']}</option>";
                            } 
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Imagen del Producto:</label>
                <?php if(!empty($row['imagen_url'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../<?php echo htmlspecialchars($row['imagen_url']); ?>" style="max-width: 100px; border: 1px solid #ddd; padding: 2px;">
                        <input type="hidden" name="imagen_url_actual" value="<?php echo htmlspecialchars($row['imagen_url']); ?>">
                        <small>Imagen actual (subir nueva para cambiar)</small>
                    </div>
                <?php endif; ?>
                <input type="file" name="imagen" accept="image/*">
            </div>
            
            <div class="form-group"><label>Descripción:</label><textarea name="descripcion" rows="3"><?php echo htmlspecialchars($row['descripcion'] ?? ''); ?></textarea></div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="productos.php" class="btn btn-warning">Cancelar</a>
            </div>
        </form>
    </div>
    <?php
} else {
    // VISTA LISTA
    $sql = "SELECT p.*, c.nombre as cat_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.nombre";
    $result = $conn->query($sql);
    ?>
    <a href="productos.php?action=create" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Producto</a>
    <div class="table-container">
        <table>
            <thead><tr><th>ID</th><th>Imagen</th><th>Producto</th><th>Categoría</th><th>P. Venta</th><th>Stock</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php while ($r = $result && $result->num_rows > 0 ? $result->fetch_assoc() : null): ?>
                <tr style="<?php echo ($r['stock_actual'] <= $r['stock_minimo']) ? 'background-color: #ffeeba;' : ''; ?>">
                    <td>#<?php echo $r['id']; ?></td>
                    <td>
                        <?php if(!empty($r['imagen_url'])): ?>
                            <img src="../<?php echo htmlspecialchars($r['imagen_url']); ?>" alt="img" width="50" style="object-fit: cover; height: 50px;">
                        <?php else: ?>
                            <small class="text-muted">Sin foto</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <b><?php echo htmlspecialchars($r['nombre'] ?? ''); ?></b>
                        <br><small style="color:#666;"><?php echo htmlspecialchars($r['descripcion'] ?? ''); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($r['cat_nombre'] ?? 'Sin Categoría'); ?></td>
                    <td>$<?php echo number_format($r['precio_venta'], 2); ?></td>
                    <td>
                        <?php echo $r['stock_actual']; ?>
                        <?php if($r['stock_actual'] <= $r['stock_minimo']) echo '<i class="fas fa-exclamation-triangle" style="color:red" title="Stock Bajo"></i>'; ?>
                    </td>
                    <td class="actions">
                        <a href="productos.php?action=edit&id=<?php echo $r['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                        <a href="productos.php?action=delete&id=<?php echo $r['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro?');"><i class="fas fa-trash"></i></a>
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
