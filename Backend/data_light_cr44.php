<?php
$conn = new mysqli("localhost", "YOUR_DB_USER", "YOUR_DB_PASSWORD", "tesis");

if (isset($_GET['AMPERAGE'])) {
    $conn->query("INSERT INTO data_light_cr44 (AMPERAGE) VALUES ('" . $_GET['AMPERAGE'] . "')");
}

$conn->close();
?>
