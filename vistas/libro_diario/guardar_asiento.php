<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require('../../conexion/conexion.php');
    
    $year = $_POST['anio'];

    // Obtener el número de asiento para el año actual
    $sql = "SELECT MAX(Int_NroAsiento) as max_nro FROM librodiario WHERE YEAR(Fecha) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $year);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nroAsiento = $row["max_nro"] + 1;
    } else {
        $nroAsiento = 1; // Si no hay registros para este año, asigna el número de asiento 1
    }

    // Preparar la consulta para insertar en la tabla librodiario
    $sql = "INSERT INTO librodiario (Int_NroAsiento, Fecha, Str_NroCuenta, Dbl_Debe, Dbl_Haber, descripcion ) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Iterar sobre los datos del formulario y ejecutar la inserción
    foreach ($_POST['cuentas'] as $key => $cuenta) {
        $descripcion = $_POST['descripciones'][$key];
        echo "alert('$_POST');";
        $debe = $_POST['debes'][$key];
        $haber = $_POST['haberes'][$key];

        // Asignar los valores a los parámetros de la consulta preparada
        $stmt->bind_param("issdds", $nroAsiento, $_POST['fecha'], $cuenta, $debe, $haber,$descripcion);

        // Ejecutar la consulta preparada
        $stmt->execute();
    }

    // Cierra la conexión
    $stmt->close();
    $conn->close();
}
?>
