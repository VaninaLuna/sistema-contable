<?php
// Actualizar el libro mayor para cada cuenta en el asiento

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['datos'])) {
    $datos = $_POST['datos'];
    $servername = "localhost:3307";
    $username = "root";
    $password = "";
    $dbname = "sis_cont";

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("La conexión a la base de datos ha fallado: " . $conn->connect_error);
    }

    foreach ($datos['cuentas'] as $key => $cuenta) {
        if($cuenta != "DESCRIPCION"){
            $debe = $datos['debes'][$key];
            $haber = $datos['haberes'][$key];
            $primerCaracter = (int)substr($cuenta, 0, 1);

            // Verificar si existe un registro para la cuenta, mes y año dados
            $sqlCheck = "SELECT COUNT(*) as count FROM mayor WHERE Str_NroCuenta = ? AND Int_Año = ? AND Int_Mes = ?";
            $stmtCheck = $conn->prepare($sqlCheck);
            $mesAnterior= ( $datos['mes']- 1 ) ;
            $stmtCheck->bind_param("sis", $cuenta, $datos['anio'], $mesAnterior);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            $rowCheck = $resultCheck->fetch_assoc();
            $stmtCheck->close();

            if ($rowCheck['count'] > 0) {
                $sql = "SELECT Dbl_Saldo, Dbl_TotalDebe, Dbl_TotalHaber FROM mayor WHERE Str_NroCuenta = ? AND Int_Año = ? AND Int_Mes = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sis", $cuenta, $datos['anio'], $mesAnterior);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $debe = $datos['debes'][$key] + $row['Dbl_TotalDebe'];
                $haber = $datos['haberes'][$key] + $row['Dbl_TotalHaber'];
            }

            // Verificar si existe un registro para la cuenta, mes y año dados
            $sqlCheck = "SELECT COUNT(*) as count FROM mayor WHERE Str_NroCuenta = ? AND Int_Año = ? AND Int_Mes = ?";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->bind_param("sis", $cuenta, $datos['anio'], $datos['mes']);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            $rowCheck = $resultCheck->fetch_assoc();
            $stmtCheck->close();

            if ($rowCheck['count'] > 0) {

                if ($primerCaracter === 1 || $primerCaracter === 5) {
                    $sqlUpdate = "UPDATE mayor SET Dbl_Saldo = ((Dbl_TotalDebe+?) - (Dbl_TotalHaber+?)) , Dbl_TotalDebe = Dbl_TotalDebe + ?, Dbl_TotalHaber = Dbl_TotalHaber + ? WHERE Str_NroCuenta = ? AND Int_Año = ? AND Int_Mes = ?";        
                    $stmtUpdate = $conn->prepare($sqlUpdate);
                    $stmtUpdate->bind_param("ddddsis", $debe, $haber, $debe, $haber, $cuenta, $datos['anio'], $datos['mes']);
                
                } else {
                    $sqlUpdate = "UPDATE mayor SET Dbl_Saldo = ((Dbl_TotalHaber+?) - (Dbl_TotalDebe+?)) , Dbl_TotalDebe = Dbl_TotalDebe + ?, Dbl_TotalHaber = Dbl_TotalHaber + ? WHERE Str_NroCuenta = ? AND Int_Año = ? AND Int_Mes = ?";        
                    $stmtUpdate = $conn->prepare($sqlUpdate);
                    $stmtUpdate->bind_param("ddddsis", $haber, $debe, $debe, $haber, $cuenta, $datos['anio'], $datos['mes']);
                }
                        
                // Actualizar el libro mayor para cada cuenta en el asiento
                
                $stmtUpdate->execute();
                $stmtUpdate->close();

            } else {

                // Crear un nuevo registro en el libro mayor
                $sqlInsert = "INSERT INTO mayor (Int_Año, Int_Mes, Str_NroCuenta, Dbl_TotalDebe, Dbl_TotalHaber, Dbl_Saldo) VALUES (?, ?, ?, ?, ?, ?)";
                $stmtInsert = $conn->prepare($sqlInsert);
                $saldoInicial =  abs($debe - $haber) ; // Puedes ajustar el saldo inicial según tus necesidades
                $stmtInsert->bind_param("issddd", $datos['anio'], $datos['mes'], $cuenta, $debe, $haber, $saldoInicial);
                $stmtInsert->execute();
                $stmtInsert->close();
            }
            
        }
    }

    $conn->close();
    echo "success";
} else {
    echo "Invalid Request";
}
?>
