<?php
$servername = getenv('DB_HOST') ?: "db";
$username = getenv('DB_USER') ?: "tiendauser";
$password = getenv('DB_PASS') ?: "tiendapass";
$dbname = getenv('DB_NAME') ?: "tiendadb";

// Reportar errores de MySQLi como excepciones
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
} catch (mysqli_sql_exception $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
