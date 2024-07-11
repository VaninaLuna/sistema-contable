<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require('../../conexion/conexion.php');

    $year = $_POST["year"];

    // Obtener el número de asiento para el año actual
    $sql = "SELECT MAX(Int_NroAsiento) as max_nro FROM librodiario WHERE YEAR(Fecha) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $year);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nroAsiento = $row["max_nro"] + 1;

        // Enviar el número de asiento como parte de la respuesta AJAX
        echo $nroAsiento ;
    } else {
        $nroAsiento = 1; // Si no hay registros para este año, asigna el número de asiento 1
        echo $nroAsiento ;
    }

    // Cierra la conexión
    $stmt->close();
    $conn->close();
} 
