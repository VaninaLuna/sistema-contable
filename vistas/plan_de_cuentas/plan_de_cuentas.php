<?php

$busqueda = $rubro = $nroCuenta = $descripcion = '';
$isUpdate = false;

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    if (!empty($_GET["buscar"])) {
        $busqueda = test_input($_GET["buscar"]);
    }

    if (!empty($_GET["accion"])) {
        if (test_input($_GET["accion"]) == "eliminar") {
            // Eliminar cuenta.
            $selectedNroCuenta = test_input($_GET["selectedNroCuenta"]);
            eliminarCuenta($selectedNroCuenta);
        } else if (test_input($_GET["accion"]) == "modificar") {
            // Modificar datos.
            $nroCuenta = test_input($_GET["selectedNroCuenta"]);
            $cuenta = selectCuentaXId($nroCuenta);
            $rubro = array_search($cuenta[0], ['', 'A', 'P', 'PN', 'I', 'E']);
            $descripcion = $cuenta[2];
            $isUpdate = true;
        }
    }

} else if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!empty($_POST["btnGuardar"])) {
        // Guardar datos.
        $error = "";
        if (!test_input($_POST["rubro"])) {
            $error .= "Ocurrió un error al ingresar el rubro. ";
        } else {
            $rubro = test_input($_POST["rubro"]);
        }

        if (!test_input($_POST["nroCuenta"])) {
            $error .= "Ocurrió un error al ingresar el número de cuenta. ";
        } else {
            $nroCuenta = test_input($_POST["nroCuenta"]);
            $error .= (str_starts_with($nroCuenta, $rubro) ? "" : "El número tiene que empezar por el dígito $rubro dado su rubro.");
        }

        if (!test_input($_POST["descripcion"])) {
            $error .= "Ocurrió un error al ingresar la descripción. ";
        } else {
            $descripcion = test_input($_POST["descripcion"]);
        }

        if (empty($error)) {
            $rubro = ['', 'A', 'P', 'PN', 'I', 'E'][$rubro];
            $cuenta = [$rubro, $nroCuenta, $descripcion];
            if (test_input($_POST["btnGuardar"]) == "Ingresar cuenta") {
                if (selectCuentaXId($nroCuenta) == null) {
                    insertarCuenta($cuenta);
                } else {
                    echo "<script type='text/javascript'>alert('Ya existe una cuenta con ese número.$error');</script>";
                }
            } else {
                modificarCuenta($cuenta);
            }
        } else {
            echo "<script type='text/javascript'>alert('$error');</script>";
        }
    }
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function selectCuentas($busqueda)
{
    $cuentas = array();
    require('../../conexion/conexion.php');

    $stmt = $conn->prepare("SELECT * FROM planDeCuentas WHERE Str_NroCuenta LIKE CONCAT('%', ? , '%') OR Str_Descripcion LIKE CONCAT('%', ? , '%')");
    $stmt->bind_param("ss", $busqueda, $busqueda);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $cuenta = [$row['Str_Rubro'], $row['Str_NroCuenta'], $row['Str_Descripcion']];
            $cuentas[] = $cuenta;
        }
    }
    $conn->close();
    return $cuentas;
}

function selectCuentaXId($id)
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "sis_cont";

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("La conexión a la base de datos ha fallado: " . $conn->connect_error);
    }
    
    $stmt = $conn->prepare("SELECT * FROM planDeCuentas WHERE Str_NroCuenta LIKE ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $cuenta = [$row['Str_Rubro'], $row['Str_NroCuenta'], $row['Str_Descripcion']];
        }
    }
    $conn->close();
    return $cuenta;
}

function insertarCuenta($cuenta)
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "sis_cont";

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("La conexión a la base de datos ha fallado: " . $conn->connect_error);
    }


    $stmt = $conn->prepare("INSERT INTO planDeCuentas (Str_Rubro, Str_NroCuenta, Str_Descripcion) VALUES (?,?,?)");
    $stmt->bind_param("sss", $rubro, $nroCuenta, $descripcion);
    $rubro = $cuenta[0];
    $nroCuenta = $cuenta[1];
    $descripcion = $cuenta[2];
    $stmt->execute();

    $stmt->close();
    $conn->close();

    header('Location: ' . htmlspecialchars($_SERVER["PHP_SELF"]));
    exit;
}

function modificarCuenta($cuenta)
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "sis_cont";

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("La conexión a la base de datos ha fallado: " . $conn->connect_error);
    }


    $stmt = $conn->prepare("UPDATE planDeCuentas SET Str_Rubro=?, Str_Descripcion=? WHERE Str_NroCuenta=?");
    $stmt->bind_param("sss", $rubro, $descripcion, $nroCuenta);
    $rubro = $cuenta[0];
    $nroCuenta = $cuenta[1];
    $descripcion = $cuenta[2];
    $stmt->execute();

    $stmt->close();
    $conn->close();

    header('Location: ' . htmlspecialchars($_SERVER["PHP_SELF"]));
    exit;
}

function eliminarCuenta($id)
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "sis_cont";

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("La conexión a la base de datos ha fallado: " . $conn->connect_error);
    }


    $stmt = $conn->prepare("DELETE FROM planDeCuentas WHERE Str_NroCuenta LIKE ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();

    $stmt->close();
    $conn->close();

    header('Location: ' . htmlspecialchars($_SERVER["PHP_SELF"]));
    exit;
}
?>

<html lang="es">

<head>
    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/css/bootstrap.min.css"
        crossorigin="anonymous">
    <link rel='stylesheet' href='/ProyectoContabilidad//css/search.css'>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
        crossorigin="anonymous">
    </script>

    <!--Scripts-->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var planDeCuentasSection = document.getElementById("planDeCuentasSection");
            var altaModificacionSection = document.getElementById("altaModificacionSection");

            var btnAlta = document.getElementById("btnAlta");

            btnAlta.addEventListener("click", function () {
                var descripcion = document.getElementById('descripcion');
                var buscar = document.getElementsByName('buscar')[0];
                descripcion.value = buscar.value;
                // Ocultar la sección "Plan de cuentas"
                planDeCuentasSection.style.display = "none";
                // Mostrar la sección "Alta, baja y modificación"
                altaModificacionSection.style.display = "block";
            });

            var selectRubro = document.getElementById('rubro-select');
            selectRubro.addEventListener('click', function (_e) {
                var nroCuenta = document.getElementById('nroCuenta');
                nroCuenta.setAttribute('pattern', _e.target.value + '.+');
                nroCuenta.setAttribute('title', "El número tiene que empezar por " + _e.target.value + " dado su rubro.");
            }, false);
        });
    </script>
        <style>
         body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            background-color: #1C6877;
            color: white;
            text-align: center;
            padding: 1em;
        }

        .container-main {
            display: flex;
            flex-grow: 1;
        }

        nav {
            background-color: #1F97AF;
            overflow: hidden;
            width: 200px; /* Ajusta el ancho según tus necesidades */
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
            flex-grow: 1;
            padding: 20px;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1em;
            width: 100%;
        }

        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
        }

        .ui-menu-item {
            padding: 4px;
        }
    </style>
    
</head>

<body>

    <header>
        <h1>Plan de cuentas</h1>
    </header>

    <div class="container-main">
    <nav>
        <div class="container container-center">
            
            <a href="/ProyectoContabilidad/vistas/libro_diario/libro_diario.php">Libro Diario</a> 
            <a href="/ProyectoContabilidad/index.php">Mayor</a>           
            <a href="#">Plan de Cuentas</a>
        </div>
    </nav>

    <main>
        <div class="container container-center container-fluid vh-100 overflow-auto">
            <section id="planDeCuentasSection" <?php if ($isUpdate)
                echo 'style="display:none;"' ?>>
                    <!--<h1 class="pt-4">Plan de cuentas</h1>-->
                    <div class="row">
                        <div class="col-md-6">
                            <!--  [  ALTA  ]  -->
                            <button class="btn btn-block btn-info" style="margin-top: 15px; height: 50px;"
                                name="btnAlta" id="btnAlta" tabindex="2">Agregar
                                cuenta</button>
                        </div>
                        <!--  [  FORMULARIO DE BÚSQUEDA  ]  -->
                        <div class="col-md-6 search-form">
                            <form action="plan_de_cuentas.php" method="GET" accept-charset="utf-8">
                                <label class="search-form_label">
                                    <input class="search-form_input" type="text" name="buscar" autocomplete="off"
                                        placeholder="Ingrese Texto" value="<?php echo $busqueda ?>" />
                                <span class="search-form_liveout"></span>
                            </label>

                            <button class="search-form_submit" style="margin-right: 5px" type="submit"></button>
                        </form>
                    </div>
                </div>

                <hr class="mb-4">

                <!--  [  TABLA / PLAN DE CUENTAS  ]  -->
                <table class="table table-responsive table-hover table-selectable" width="80%" align="center"
                    name="cuentas" id="cuentas" tabindex="1">
                    
                    <thead class="sticky-top" style="background-color:#f4f4f4">
                        <tr>
                            <th width="10%">Rubro</th>
                            <th width="10%">Número de Cuenta</th>
                            <th width="60%">Descripción</th>
                            <th width="10%"></th>
                            <th width="10%"></th>
                        </tr>
                    </thead>

                        <?php
                        $cuentas = selectCuentas($busqueda);
                        foreach ($cuentas as $cuenta) {
                            ?>
                            <tr class="clickable-row">
                                <td>
                                    <?php echo $cuenta[0] ?>
                                </td>
                                <td>
                                    <?php echo $cuenta[1] ?>
                                </td>
                                <td>
                                    <?php echo $cuenta[2] ?>
                                </td>
                                <td>
                                    <?php echo "<a href='plan_de_cuentas.php?accion=modificar&selectedNroCuenta=" . $cuenta[1] . "'>Modificar</a>" ?>
                                </td>
                                <td>
                                    <?php echo "<a href='plan_de_cuentas.php?accion=eliminar&selectedNroCuenta=" . $cuenta[1] . "'>Eliminar</a>" ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>

                </table>

            </section>

            <section id="altaModificacionSection" <?php if (!$isUpdate)
                echo 'style="display:none;"' ?>>

                    <h1 class="pt-4">Alta y modificación</h1>

                    <!--  [  FORMULARIO  ]  -->
                    <form id="formCuenta" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                    enctype="multipart/form-data">

                    <?php
                    // Si es modificación, se agrega un campo oculto para que se marque como tal a la hora de realizar el envío.
                    if ($isUpdate) {
                        ?><input type="hidden" name="btnModificar" value="Modificar">
                        <?php
                    }
                    ?>

                    <div class="row">
                        <!-- RUBRO -->
                        <div class="col-md-4">
                            <label for="rubro">Rubro</label>
                            <select class="form-control input-sm" id="rubro-select" required disabled="true"
                                tabindex="1">
                                <option disabled <?php if ($rubro == '')
                                    echo 'selected' ?> value> -- Seleccione un rubro
                                        --
                                    </option>
                                    <option value="1" <?php if ($rubro == '1')
                                    echo 'selected' ?>>Activo</option>
                                    <option value="2" <?php if ($rubro == '2')
                                    echo 'selected' ?>>Pasivo</option>
                                    <option value="3" <?php if ($rubro == '3')
                                    echo 'selected' ?>>Patrimonio Neto</option>
                                    <option value="4" <?php if ($rubro == '4')
                                    echo 'selected' ?>>Ingreso</option>
                                    <option value="5" <?php if ($rubro == '5')
                                    echo 'selected' ?>>Egreso</option>
                                </select>
                                <input type="hidden" name="rubro" value="<?php echo $rubro ?>" />
                        </div>

                        <!-- N° CUENTA -->
                        <div class="col-md-4">
                            <label for="nroCuenta">Número de Cuenta</label>
                            <input type="text" name="nroCuenta" id="nroCuenta" value="<?php echo $nroCuenta ?>"
                                class="form-control input-sm" autocomplete="off" required maxlength="15"
                                pattern="<?php echo array_search($rubro, ['', 'A', 'P', 'PN', 'I', 'E']) ?>.+"
                                title="El número tiene que empezar por <?php echo array_search($rubro, ['', 'A', 'P', 'PN', 'I', 'E']) ?> dado su rubro."
                                <?php echo ($isUpdate ? 'readOnly' : '') ?> tabindex="2">
                        </div>

                        <!-- DESCRIPCIÓN -->
                        <div class="col-md-4">
                            <label for="descripcion">Descripción</label>
                            <input type="text" name="descripcion" id="descripcion" value="<?php echo $descripcion ?>"
                                class="form-control input-sm" autocomplete="off" placeholder required maxlength="50"
                                tabindex="3">
                        </div>
                    </div>

                    <hr>

                    <!-- BOTÓN DE ENVIO -->
                    <input class="btn btn-primary btn-xs btn-block" type="submit" name="btnGuardar" id="btnGuardar"
                        value="<?php echo ($isUpdate ? 'Modificar' : 'Ingresar') ?> cuenta" tabindex="4">

                </form>

                <!-- Habilitar la selección de un rubro (si no es modificación) -->
                <?php if (!$isUpdate) { ?>
                    <script>
                        $('#formCuenta input[name=rubro]')
                            .attr("disabled", true);

                        $('#rubro-select')
                            .attr('disabled', false)
                            .attr('name', 'rubro');
                    </script>
                <?php } ?>
            </section>
        </div>
    </main>
    </div>

    <footer>
        <p>&copy; 2023 Sistema Contable</p>
    </footer>

</body>

</html>