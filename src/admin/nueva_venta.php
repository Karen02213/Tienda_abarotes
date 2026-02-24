<?php
include '../includes/db.php';
include '../includes/helpers.php';

// Procesar Venta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['total_venta'])) {
    $cliente_id = !empty($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 'NULL';
    $usuario_id = 1; // ID fijo por ahora (admin), en sistema real usar $_SESSION['user_id']
    $total = floatval($_POST['total_venta']);
    $metodo = $_POST['metodo_pago'];
    $productos = $_POST['productos']; // Array de IDs
    $cantidades = $_POST['cantidades']; // Array de cantidades
    $precios = $_POST['precios']; // Array de precios ocultos

    // Iniciar Transacción
    $conn->begin_transaction();

    try {
        // 1. Crear Venta
        $sql_venta = "INSERT INTO ventas (usuario_id, cliente_id, total, metodo_pago) VALUES ($usuario_id, $cliente_id, $total, '$metodo')";
        $conn->query($sql_venta);
        $venta_id = $conn->insert_id;

        // 2. Insertar Detalles y Actualizar Stock
        for ($i = 0; $i < count($productos); $i++) {
            $prod_id = intval($productos[$i]);
            $cant = intval($cantidades[$i]);
            $precio = floatval($precios[$i]);
            $subtotal = $cant * $precio;

            // Insertar detalle
            $conn->query("INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES ($venta_id, $prod_id, $cant, $precio, $subtotal)");

            // Restar stock
            $conn->query("UPDATE productos SET stock_actual = stock_actual - $cant WHERE id = $prod_id");
        }

        $conn->commit();
        redirect("ventas.php?msg=Venta registrada correctamente&type=success");

    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

// Obtener datos para formulario
$clientes = $conn->query("SELECT * FROM clientes");
$productos_db = $conn->query("SELECT * FROM productos WHERE stock_actual > 0");
$lista_productos = [];
while($p = $productos_db->fetch_assoc()) {
    $lista_productos[] = $p;
}

render_header("Nueva Venta (Punto de Venta)");
?>

<div class="row" style="display:flex; gap:20px;">
    <!-- Panel Izquierdo: Selección de Productos -->
    <div class="col" style="flex:1; background: #fff; padding: 20px; border-radius:5px;">
        <h3><i class="fas fa-search"></i> Buscar Producto</h3>
        <div class="form-group">
            <input type="text" id="buscador" placeholder="Escribe para buscar..." onkeyup="filtrarProductos()">
        </div>
        
        <div class="lista-productos-scroll" style="height: 400px; overflow-y: auto; border:1px solid #ddd;">
            <table id="tabla-productos">
                <thead><tr><th>Producto</th><th>Precio</th><th>Stock</th><th></th></tr></thead>
                <tbody>
                    <?php foreach($lista_productos as $p): ?>
                    <tr class="producto-item">
                        <td><?php echo $p['nombre']; ?></td>
                        <td>$<?php echo $p['precio_venta']; ?></td>
                        <td><?php echo $p['stock_actual']; ?></td>
                        <td>
                            <button type="button" class="btn btn-success btn-sm" 
                                onclick="agregarProducto(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars($p['nombre']); ?>', <?php echo $p['precio_venta']; ?>, <?php echo $p['stock_actual']; ?>)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Panel Derecho: Carrito / Ticket -->
    <div class="col" style="flex:1; background: #fff; padding: 20px; border-radius:5px; border-left: 4px solid #007bff;">
        <h3><i class="fas fa-shopping-cart"></i> Ticket de Venta</h3>
        <form method="POST" id="form-venta" onsubmit="return validarVenta()">
            <div class="form-group">
                <label>Cliente:</label>
                <select name="cliente_id">
                    <option value="">Público General</option>
                    <?php while($c = $clientes->fetch_assoc()): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo $c['nombre']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Método de Pago:</label>
                <select name="metodo_pago">
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>

            <table width="100%">
                <thead><tr><th>Producto</th><th>Cant.</th><th>Subtotal</th><th></th></tr></thead>
                <tbody id="carrito-body">
                    <!-- Aquí se agregan filas con JS -->
                </tbody>
            </table>
            
            <hr>
            <div style="text-align: right; font-size: 1.5em; margin: 10px 0;">
                Total: $<span id="total-display">0.00</span>
                <input type="hidden" name="total_venta" id="total_input" value="0">
            </div>

            <button type="submit" class="btn btn-success" style="width: 100%; font-size: 1.2em;">COBRAR</button>
        </form>
    </div>
</div>

<script>
    function agregarProducto(id, nombre, precio, stockMax) {
        const tbody = document.getElementById('carrito-body');
        
        // Verificar si ya está en el carrito
        const existente = document.getElementById('row-' + id);
        if (existente) {
             const inputCant = document.getElementById('cant-' + id);
             const nuevaCant = parseInt(inputCant.value) + 1;
             
             if (nuevaCant <= stockMax) {
                inputCant.value = nuevaCant;
                actualizarSubtotal(id, precio);
             } else {
                 alert('No hay más stock disponible');
             }
             return;
        }

        const row = document.createElement('tr');
        row.id = 'row-' + id;
        
        // Creamos inputs hidden para enviar array[] al PHP
        row.innerHTML = `
            <td>
                ${nombre} 
                <input type="hidden" name="productos[]" value="${id}"> 
                <input type="hidden" name="precios[]" value="${precio}">
            </td>
            <td>
                <input type="number" id="cant-${id}" name="cantidades[]" value="1" min="1" max="${stockMax}" style="width:50px" onchange="actualizarSubtotal(${id}, ${precio})">
            </td>
            <td>$<span id="sub-${id}">${precio.toFixed(2)}</span></td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(${id})"><i class="fas fa-times"></i></button>
            </td>
        `;
        tbody.appendChild(row);
        calcularTotal();
    }

    function actualizarSubtotal(id, precio) {
        const inputCant = document.getElementById('cant-' + id);
        const cant = parseInt(inputCant.value);
        const sub = cant * precio;
        
        document.getElementById('sub-' + id).innerText = sub.toFixed(2);
        calcularTotal();
    }

    function eliminarFila(id) {
        document.getElementById('row-' + id).remove();
        calcularTotal();
    }

    function calcularTotal() {
        let suma = 0;
        // Buscamos todos los spans de subtotales
        const subtotales = document.querySelectorAll('[id^="sub-"]');
        subtotales.forEach(span => {
            suma += parseFloat(span.innerText);
        });
        
        document.getElementById('total-display').innerText = suma.toFixed(2);
        document.getElementById('total_input').value = suma.toFixed(2);
    }

    function filtrarProductos() {
        const texto = document.getElementById('buscador').value.toLowerCase();
        // Buscamos todas las filas de la tabla de productos (izquierda)
        const filas = document.querySelectorAll('.producto-item');
        
        filas.forEach(fila => {
            // La primera celda (td) tiene el nombre
            const nombre = fila.cells[0].innerText.toLowerCase();
            if (nombre.includes(texto)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    }

    function validarVenta() {
        const total = parseFloat(document.getElementById('total_input').value);
        if (total <= 0) {
            alert("Agrega productos al carrito primero.");
            return false;
        }
        return true;
    }
</script>

<?php 
render_footer();
$conn->close(); 
?>
