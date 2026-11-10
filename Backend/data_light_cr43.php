<?php
$conn = new mysqli("localhost", "YOUR_DB_USER", "YOUR_DB_PASSWORD", "tesis");

if (isset($_GET['AMPERAGE'])) {
    $conn->query("INSERT INTO data_light_cr43 (AMPERAGE) VALUES ('" . $_GET['AMPERAGE'] . "')");
}

$conn->close();
?>
