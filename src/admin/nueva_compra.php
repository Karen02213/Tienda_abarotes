<?php
include '../includes/db.php';
include '../includes/helpers.php';

// Procesar Compra
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['total_compra'])) {
    $proveedor_id = intval($_POST['proveedor_id']);
    $usuario_id = 1; // ID fijo por ahora (admin)
    $total = floatval($_POST['total_compra']);
    $factura = $conn->real_escape_string($_POST['numero_factura']);
    $productos = $_POST['productos']; 
    $cantidades = $_POST['cantidades'];
    $costos = $_POST['costos']; // Costos unitarios de compra

    $conn->begin_transaction();

    try {
        $sql_compra = "INSERT INTO compras (proveedor_id, usuario_id, total, numero_factura) VALUES ($proveedor_id, $usuario_id, $total, '$factura')";
        $conn->query($sql_compra);
        $compra_id = $conn->insert_id;

        for ($i = 0; $i < count($productos); $i++) {
            $prod_id = intval($productos[$i]);
            $cant = intval($cantidades[$i]);
            $costo = floatval($costos[$i]);
            $subtotal = $cant * $costo;

            $conn->query("INSERT INTO detalle_compras (compra_id, producto_id, cantidad, costo_unitario, subtotal) VALUES ($compra_id, $prod_id, $cant, $costo, $subtotal)");
            
            // Sumar stock
            $conn->query("UPDATE productos SET stock_actual = stock_actual + $cant, precio_compra = $costo WHERE id = $prod_id");
        }

        $conn->commit();
        redirect("compras.php?msg=Compra registrada exitosamente&type=success");

    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

$proveedores = $conn->query("SELECT * FROM proveedores");
$productos_db = $conn->query("SELECT * FROM productos");
$lista_productos = [];
while($p = $productos_db->fetch_assoc()) {
    $lista_productos[] = $p;
}

render_header("Nueva Compra (Reabastecimiento)");
?>

<div class="row" style="display:flex; gap:20px;">
    <!-- Panel Izquierdo: Selección de Productos -->
    <div class="col" style="flex:1; background: #fff; padding: 20px; border-radius:5px;">
        <h3><i class="fas fa-search"></i> Buscar Producto</h3>
        <input type="text" id="buscador" placeholder="Buscar..." onkeyup="filtrarProductos()" style="width:100%; padding:8px; margin-bottom:10px;">
        
        <div class="lista-productos-scroll" style="height: 400px; overflow-y: auto; border:1px solid #ddd;">
            <table id="tabla-productos">
                <thead><tr><th>Producto</th><th>Costo Actual</th><th></th></tr></thead>
                <tbody>
                    <?php foreach($lista_productos as $p): ?>
                    <tr class="producto-item">
                        <td><?php echo $p['nombre']; ?></td>
                        <td>$<?php echo $p['precio_compra']; ?></td>
                        <td>
                            <button type="button" class="btn btn-success btn-sm" 
                                onclick="agregarProducto(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars($p['nombre']); ?>', <?php echo $p['precio_compra']; ?>)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Panel Derecho: Carrito -->
    <div class="col" style="flex:1; background: #fff; padding: 20px; border-radius:5px; border-left: 4px solid #28a745;">
        <h3><i class="fas fa-truck"></i> Orden de Compra</h3>
        <form method="POST" id="form-compra" onsubmit="return validarCompra()">
            <div class="form-group">
                <label>Proveedor:</label>
                <select name="proveedor_id" required>
                    <option value="">Seleccione...</option>
                    <?php while($p = $proveedores->fetch_assoc()): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo $p['nombre_empresa']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Factura / Referencia:</label>
                <input type="text" name="numero_factura" placeholder="Ej: F-12345">
            </div>

            <table width="100%">
                <thead><tr><th>Producto</th><th>Cant.</th><th>Costo Unit.</th><th>Subtotal</th><th></th></tr></thead>
                <tbody id="carrito-body"></tbody>
            </table>
            
            <hr>
            <div style="text-align: right; font-size: 1.5em; margin: 10px 0;">
                Total: $<span id="total-display">0.00</span>
                <input type="hidden" name="total_compra" id="total_input" value="0">
            </div>

            <button type="submit" class="btn btn-success" style="width: 100%; font-size: 1.2em;">REGISTRAR COMPRA</button>
        </form>
    </div>
</div>

<script>
    function agregarProducto(id, nombre, costo) {
        if (document.getElementById('row-' + id)) {
            alert("Este producto ya está en la lista.");
            return;
        }

        const tbody = document.getElementById('carrito-body');
        const row = document.createElement('tr');
        row.id = 'row-' + id;
        row.innerHTML = `
            <td>${nombre} <input type="hidden" name="productos[]" value="${id}"></td>
            <td><input type="number" id="cant-${id}" name="cantidades[]" value="1" min="1" style="width:50px" onchange="actualizarSubtotal(${id})"></td>
            <td><input type="number" step="0.01" id="costo-${id}" name="costos[]" value="${costo}" style="width:80px" onchange="actualizarSubtotal(${id})"></td>
            <td>$<span id="sub-${id}">${costo.toFixed(2)}</span></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(${id})"><i class="fas fa-times"></i></button></td>
        `;
        tbody.appendChild(row);
        calcularTotal();
    }

    function actualizarSubtotal(id) {
        const cant = parseFloat(document.getElementById('cant-' + id).value) || 0;
        const costo = parseFloat(document.getElementById('costo-' + id).value) || 0;
        const sub = cant * costo;
        document.getElementById('sub-' + id).innerText = sub.toFixed(2);
        calcularTotal();
    }

    function eliminarFila(id) {
        document.getElementById('row-' + id).remove();
        calcularTotal();
    }

    function calcularTotal() {
        let suma = 0;
        document.querySelectorAll('[id^="sub-"]').forEach(span => suma += parseFloat(span.innerText));
        document.getElementById('total-display').innerText = suma.toFixed(2);
        document.getElementById('total_input').value = suma.toFixed(2);
    }

    function filtrarProductos() {
        const texto = document.getElementById('buscador').value.toLowerCase();
        document.querySelectorAll('.producto-item').forEach(fila => {
            const nombre = fila.cells[0].innerText.toLowerCase();
            fila.style.display = nombre.includes(texto) ? '' : 'none';
        });
    }

    function validarCompra() {
        const total = parseFloat(document.getElementById('total_input').value);
        if (total <= 0) {
            alert("Agrega productos a la compra.");
            return false;
        }
        return true;
    }
</script>

<?php render_footer(); $conn->close(); ?>
