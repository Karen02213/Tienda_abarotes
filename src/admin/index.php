<?php
include '../includes/db.php';
include '../includes/helpers.php';

render_header("Dashboard");
?>

<div class="dashboard-cards">
    <p>Bienvenido al sistema de gestión de tu Tienda de Abarrotes.</p>
    <p>Selecciona una opción del menú lateral para gestionar los recursos.</p>
</div>

<?php 
render_footer(); 
$conn->close();
?>
