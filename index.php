<?php
$fecha = date('Y-m-d');
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['fecha'])) {
    $fecha = ($_GET['fecha']);
}

function buscarCuentasDistintas($mes, $anno)
{
    $cuentas = array();
    require('conexion/conexion.php');

    $stmt = $conn->prepare("SELECT DISTINCT l.Str_NroCuenta, m.Dbl_Saldo, p.Str_Descripcion AS Str_NombreCuenta 
    FROM librodiario l 
    LEFT JOIN plandecuentas p ON l.Str_NroCuenta = p.Str_NroCuenta 
    LEFT JOIN mayor m ON l.Str_NroCuenta = m.Str_NroCuenta AND MONTH(l.Fecha) = m.Int_Mes AND YEAR(l.Fecha) = m.Int_Año 
    WHERE MONTH(Fecha) = ? AND YEAR(Fecha) = ?");

    $stmt->bind_param("ii", $mes, $anno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $cuenta = [$row['Str_NroCuenta'], $row['Str_NombreCuenta'], $row['Dbl_Saldo']];
            $cuentas[] = $cuenta;
        }
    }
    $conn->close();
    return $cuentas;
}

function buscarMayorDebe($nroCuenta, $mes, $anno)
{
    $listaDebe = array();
    require('conexion/conexion.php');

    $sql = "SELECT Dbl_Debe
            FROM librodiario
            WHERE Str_NroCuenta LIKE '$nroCuenta' AND MONTH(Fecha) = $mes AND YEAR(Fecha) = $anno AND Dbl_Debe > 0
            ORDER BY Str_NroCuenta, Int_NroAsiento";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $debe = $row['Dbl_Debe'];
            $listaDebe[] = $debe;
        }
    }
    $conn->close();
    return $listaDebe;
}

function buscarMayorHaber($nroCuenta, $mes, $anno)
{
    $listaHaber = array();
    require('conexion/conexion.php');

    $sql = "SELECT Dbl_Haber
            FROM librodiario
            WHERE Str_NroCuenta LIKE '$nroCuenta' AND MONTH(Fecha) = $mes AND YEAR(Fecha) = $anno AND Dbl_Haber > 0
            ORDER BY Str_NroCuenta, Int_NroAsiento";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $haber = $row['Dbl_Haber'];
            $listaHaber[] = $haber;
        }
    }
    $conn->close();
    return $listaHaber;
}

function buscarAsiento($mes, $anno)
{
    $cuentas = array();
    require('conexion/conexion.php');

    $stmt = $conn->prepare("SELECT l.Int_NroAsiento, l.Fecha, l.Str_NroCuenta, l.Dbl_Debe, l.Dbl_Haber, l.descripcion AS Str_NombreCuenta
    FROM librodiario l
    WHERE MONTH(Fecha) = ? AND YEAR(Fecha) = ? 
    ORDER BY 
    l.Int_NroAsiento ASC, l.Dbl_Debe>0 DESC, l.Str_NroCuenta ASC");
    $stmt->bind_param("ii", $mes, $anno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $cuenta = [$row['Int_NroAsiento'], $row['Str_NroCuenta'], $row['Str_NombreCuenta'], $row['Dbl_Debe'], $row['Dbl_Haber'], $row['Fecha']];
            $cuentas[] = $cuenta;
        }
    }
    $conn->close();
    return $cuentas;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Contable</title>
    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel='stylesheet' href='/ProyectoContabilidad//css/search.css'>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: #1C6877;
            color: white;
            text-align: center;
            padding: 1em;
        }

        nav {
            background-color: #1F97AF;
            overflow: hidden;
            width: 200px;
            /* Ajusta el ancho según tus necesidades */
            height: 100vh;
            /* Ocupa toda la altura de la pantalla */
        }

        nav a {
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }

        nav a:hover {
            background-color: #084B5C;
            color: white;
        }

        main {
            padding: 20px;
            flex-grow: 1;
            display: flex;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1em;
        }
    </style>


    <!--Scripts-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>

    <header>
        <h1>Libro Mayor</h1>
    </header>
    <div style="display: flex;">
        <nav>
            <div class="container container-center">

                <a href="vistas/libro_diario/libro_diario.php">Libro Diario</a>
                <a href="#">Mayor</a>
                <a href="vistas/plan_de_cuentas/plan_de_cuentas.php">Plan de Cuentas</a>
            </div>
        </nav>
        <main>
            <div class="container container-center overflow-auto">

                <div style=" display: flex; justify-content: center; align-items: center; margin: 0;">
                    <form method="get" style="display: flex; flex-direction: row; align-items: center; height: 80px; padding: 10px;">
                        <label for="fecha" style="margin-top: 2px; width: 220px">Seleccione fecha a Consultar:</label>
                        <input class="search-form_input form-control"  style="width: 180px" type="month" id="fecha" name="fecha" autocomplete="off" value="<?php echo explode('-', $fecha)[0] . '-' . explode('-', $fecha)[1]; ?>" />
                        <button type="submit" class="btn btn-block btn-info" style="margin-left: 30px; height: 30px; width: 200px; padding: 0">Consultar</button>
                    </form>
                </div>

                <hr>
                
                <div id="mayores" class="col-md-12 overflow-auto" style="height: 80vh; overflow-y: auto;">
                    <!--  [  LIBRO MAYOR  ]  -->
                    <h2 style="text-align: center;">Libro mayor</h2>
                    <hr>
                    <div class="row" style="text-align: center;">
                        <?php
                        $cuentasDistintas = buscarCuentasDistintas(explode('-', $fecha)[1], explode('-', $fecha)[0]);
                        foreach ($cuentasDistintas as $cuenta) {
                            if ($cuenta[0] == "DESCRIPCION") {
                                // Saltar al siguiente elemento si la condición se cumple
                                continue;
                            }
                            $listaDebe = buscarMayorDebe($cuenta[0], explode('-', $fecha)[1], explode('-', $fecha)[0]);
                            $listaHaber = buscarMayorHaber($cuenta[0], explode('-', $fecha)[1], explode('-', $fecha)[0]);
                        ?>
                            <div class="mb-4" style="width:25%" aling="center">
                                <table style="width: 90%;">
                                    <tr style="border-bottom: solid #AAA 1px">
                                        <th colspan="2" style="text-align: center;">
                                            <?php echo $cuenta[1]; ?>
                                        </th>
                                    </tr>

                                    <?php
                                    $saldo = 0;
                                    $tam = max(sizeof($listaDebe), sizeof($listaHaber));
                                    for ($i = 0; $i < $tam; $i++) {
                                        $saldo += ($i < sizeof($listaDebe) ? $listaDebe[$i] : 0) - ($i < sizeof($listaHaber) ? $listaHaber[$i] : 0);
                                    ?>
                                        <tr>
                                            <td style="border-right: solid #AAA 1px; padding: 5px">
                                                <?php echo $i < sizeof($listaDebe) ? $listaDebe[$i] : "" ?>
                                            </td>
                                            <td style="padding:5px">
                                                <?php echo $i < sizeof($listaHaber) ? $listaHaber[$i] : "" ?>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>

                                    <tr>
                                        <td style="width: 50%; <?php echo $saldo >= 0 ? "border-top: solid #AAA 1px;padding: 5px;" : "" ?> border-right: solid #AAA 1px; color: #21c2f8;">
                                            <?php echo $saldo >= 0 ? $cuenta[2] : "" ?>
                                        </td>
                                        <td style="width: 50%; <?php echo $saldo < 0 ? "border-top: solid #AAA 1px;padding: 5px;" : "" ?>color: #21c2f8">
                                            <?php echo $saldo < 0 ? $cuenta[2] : "" ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
    </div>
    </main>
    </div>

    <footer>
        <p>&copy; 2023 Sistema Contable</p>
    </footer>

</body>

</html>