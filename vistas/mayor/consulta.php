<?php
// Establecer la conexión a la base de datos (asegúrate de tener esto configurado correctamente)
require('../../conexion/conexion.php');

// Obtener la fecha de la solicitud
$fecha = $_GET['fecha'];
// Divide la cadena en partes usando el delimitador "-"
$partesFecha = explode('-', $fecha);

// $partesFecha contendrá un array con dos elementos: [0] => "2023", [1] => "11"

// Extrae el año y el mes en variables
$anio = $partesFecha[0];
$mes = $partesFecha[1];


// Realizar la consulta en la base de datos
$sql = "SELECT m.Str_NroCuenta, m.Dbl_TotalDebe, m.Dbl_TotalHaber, m.Dbl_Saldo, p.Str_Descripcion AS Str_NombreCuenta
FROM mayor m
LEFT JOIN plandecuentas p ON m.Str_NroCuenta = p.Str_NroCuenta
WHERE m.Int_Año = ? AND m.Int_Mes = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $anio ,$mes);
$stmt->execute();
$result = $stmt->get_result();


// Convertir los resultados a un array asociativo
$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Devolver los resultados como JSON
header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>
