<!DOCTYPE html>
<?php

$rubro = $nroCuenta = $descripcion = '';
if (!empty($_GET["fecha"])) {
    $busqueda = test_input($_GET["fecha"]);
} else {
    $busqueda = date('Y-m-d');
}

$isUpdate = false;

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (!empty($_GET["fecha"])) {
        $busqueda = test_input($_GET["fecha"]);
    }
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function buscarAsiento($busqueda)
{
    $cuentas = array();
    require('../../conexion/conexion.php');

    $stmt = $conn->prepare("SELECT Int_NroAsiento, Fecha, Str_NroCuenta, Dbl_Debe, Dbl_Haber, descripcion
    FROM librodiario
    WHERE Fecha = ?
    ORDER BY 
    Int_NroAsiento ASC, Dbl_Debe>0 DESC");
    $stmt->bind_param("s", $busqueda);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $cuenta = [$row['Int_NroAsiento'], $row['Str_NroCuenta'], $row['descripcion'], $row['Dbl_Debe'], $row['Dbl_Haber']];
            $cuentas[] = $cuenta;
        }
    }
    $conn->close();
    return $cuentas;
}

function obtenerNumeroAsiento($year)
{
    require('../../conexion/conexion.php');

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

    } else {
        $nroAsiento = 1; // Si no hay registros para este año, asigna el número de asiento 1

    }

    // Cierra la conexión
    $stmt->close();
    $conn->close();

    return $nroAsiento;
}

function obtenerCuentas()
{
    require('../../conexion/conexion.php');

    $result = $conn->query("SELECT
    plandecuentas.Str_NroCuenta,
    plandecuentas.Str_Descripcion,
    IFNULL(mayor.Dbl_Saldo, 0) AS SaldoActual
FROM
    plandecuentas
LEFT JOIN
    mayor ON plandecuentas.Str_NroCuenta = mayor.Str_NroCuenta
        AND mayor.Int_Año = YEAR(CURDATE()) -- Año actual
        AND mayor.Int_Mes = MONTH(CURDATE()) -- Mes actual
    ORDER BY
    plandecuentas.Str_NroCuenta");
    $cuentas = array();

    while ($row = $result->fetch_assoc()) {
        $cuentas[] = (
            (strlen($row['Str_NroCuenta']) > 4 && $row['Str_NroCuenta'] != 'DESCRIPCION') ?
            array(
                'value' => $row['Str_NroCuenta'],
                'saldo' => $row['SaldoActual'],
                'descripcion' => $row['Str_Descripcion'],
                'label' => $row['Str_NroCuenta'] . ' - ' . $row['Str_Descripcion'] . ' - $' . $row['SaldoActual']
            ) :
            array(
                'value' => $row['Str_NroCuenta'],
                'saldo' => '',
                'descripcion' => $row['Str_Descripcion'],
                'label' => $row['Str_NroCuenta'] . ' - ' . $row['Str_Descripcion']
            )
        );
    }


    return json_encode($cuentas);
}

?>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel='stylesheet' href='/ProyectoContabilidad//css/search.css'>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <link rel="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">
    </script>
    <!--Scripts-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // var planDeCuentasSection = document.getElementById("planDeCuentasSection");
            // var altaModificacionSection = document.getElementById("altaModificacionSection");

            $("#tabla_asientos").on("input", "input[name='debe[]'], input[name='haber[]']", function() {
                validarMontos(this);
            });

            agregarFila();
            agregarFila();

            // btnAlta.addEventListener("click", function() {
            //     var fecha = document.getElementsByName('fecha');
            //     // Ocultar la sección "Plan de cuentas"
            //     planDeCuentasSection.style.display = "none";
            //     // Mostrar la sección "Alta, baja y modificación"
            //     altaModificacionSection.style.display = "block";
            // });

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
            width: 200px;
            /* Ajusta el ancho según tus necesidades */
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

        .underline-link {
            border-bottom: 2px solid white; /* Grosor y color de la línea debajo del enlace */
            padding-bottom: 2px; /* Espaciado opcional entre el texto y la línea */
        }

        main {
            flex-grow: 1;
            padding: 20px;
        }

        footer {
            background-color: #1C6877;
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

    <title>Sistema Contable</title>

</head>

<body>

    <header>
        <h1>Libro Diario</h1>
    </header>

    <div class="container-main">

        <nav>
            <div class="container container-center">
                <a href="#">Libro Diario</a>
                <a href="/ProyectoContabilidad/index.php">Mayor</a>
                <a href="/ProyectoContabilidad/vistas/plan_de_cuentas/plan_de_cuentas.php">Plan de Cuentas</a>
            </div>
        </nav>

        <main>
            <div class="container container-center container-fluid vh-100 overflow-auto">

                <div>
                    <h2 class="pt-4" id="titulo_asiento">Agregar Asiento - Número de Asiento:
                        <?php echo obtenerNumeroAsiento(date('Y')); ?>
                    </h2>

                    <!--  [  FORMULARIO  ]  -->
                    <hr>
                    <div style=" display: flex; justify-content: left; align-items: center; margin: 0;">
                        <form id="formulario_fecha" style="display: flex; flex-direction: row; align-items: center; height: 80px; padding: 10px;">
                            <label style="margin-top: 2px; width: 220px" for="fecha_asiento">Fecha de Asiento:</label>
                            <input class="search-form_input" style="width: 180px" type="date" id="fecha_asiento" name="fecha_asiento" placeholder="dd/mm/yyyy"  value="<?php echo date('Y-m-d'); ?> required" />
                        </form>
                    </div>
                    <hr>

                    <table class="table table-responsive table-hover" style="width:100%; border-color: #AAA;" align="center" name="cuentas" id="tabla_asientos" tabindex="1">
                        <thead class="sticky-top" style="background-color:#f4f4f4">
                            <tr sticky-top>
                                <th width="22%">Cuenta</th>
                                <th width="22%">Descripción</th>
                                <th width="22%">Debe</th>
                                <th width="22%">Haber</th>
                                <th width="2%">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-md-6">
                            <button class="btn btn-block btn-info" style="margin-top: 15px; height: 50px;" onclick="agregarFila()">Agregar Fila</button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-block btn-info" style="margin-top: 15px; height: 50px;" onclick="guardarAsiento()">Guardar Asiento</button>
                        </div>
                    </div>
                </div>
                <br>
                <div>
                <hr class="mb-3">
                    <div class="row">
                        <!-- <div class="col-md-9">
                            <button class="btn btn-block btn-info" style="margin-top: 15px; height: 50px;" name="btnAlta" id="btnAlta" tabindex="2">Agregar Asiento</button>

                        </div> -->

                        <!--  [  FORMULARIO DE BÚSQUEDA  ]  -->
                        <div class="search-form" style=" display: flex; justify-content: center; align-items: center; margin: 0;">
                            <form action="" method="GET" accept-charset="utf-8" style="display: flex; flex-direction: row; align-items: center; height: 80px; padding: 10px;">
                                <label style="margin-top: 2px; width: 220px">Seleccione fecha a Consultar:</label>
                                <input class="search-form_input" style="width: 180px" type="date" name="fecha" autocomplete="off" placeholder="Ingrese Texto" value="<?php echo $busqueda; ?>" />

                                <button class="btn btn-block btn-info" type="submit" style="margin-left: 30px; height: 30px; width: 200px; padding: 0">Consultar</button>
                            </form>
                        </div>
                    </div>
                    <hr class="mb-3">
                    <!--  [  LIBRO DIARIO  ]  -->
                    <table class="table table-responsive table-hover -selectabletable" width="80%" align="center" name="cuentas" id="cuentas" tabindex="1">
                        <thead class="sticky-top" style="background-color:#f4f4f4">
                            <tr>
                            <tr>
                                <th width="10%">N° de Cuenta</th>
                                <th width="60%">Descripción</th>
                                <th width="15%" style='text-align: center'>Debe</th>
                                <th width="15%" style='text-align: center'>Haber</th>
                            </tr>
                        </thead>
                        <?php
                        $cuentas = buscarAsiento($busqueda);
                        $numero = 0;
                        $totalDebe = 0;
                        $totalHaber = 0;
                        $asientoDebe = 0;
                        $asientoHaber = 0;
                        foreach ($cuentas as $cuenta) {
                            if ($numero != $cuenta[0]) {
                                if ($asientoDebe > 0) {
                                    echo "<tr>
                                            <td colspan='2'></td>
                                            <td style='text-align: center; border-left: solid 1px #bbb'>" . number_format($asientoDebe, 2, '.', '') . "</td>
                                            <td style='text-align: center; border-left: solid 1px #bbb'>" . number_format($asientoHaber, 2, '.', '') . "</td>
                                        </tr>";
                                    $asientoDebe = 0;
                                    $asientoHaber = 0;
                                }
                                echo "<tr style='text-align: center; background-color: #ddd;'>
                                <td></td>
                                <td>Asiento N° " . $cuenta[0] . "</td>
                                <td colspan='2'></td>
                            </tr>";
                                $numero = $cuenta[0];
                            }
                            $asientoDebe += $cuenta[3];
                            $asientoHaber += $cuenta[4];
                            $totalDebe += $cuenta[3];
                            $totalHaber += $cuenta[4];
                            echo "<tr class=''>
                                    <td>" . ($cuenta[1] != "DESCRIPCION" ? $cuenta[1] : "") . "</td> 
                                    <td" . ($cuenta[4] > 0 ? " style='padding-left: 6%;'> a " : "> ") . $cuenta[2] . "</td>
                                    <td style='text-align: center'>" . ($cuenta[1] != "DESCRIPCION" ? $cuenta[3] : "") . "</td>
                                    <td style='text-align: center'>" . ($cuenta[1] != "DESCRIPCION" ? $cuenta[4] : "") . "</td>
                                </tr>";
                        }
                        if ($asientoDebe > 0) {
                            echo "<tr>
                                <td colspan='2'></td>
                                <td style='text-align: center; border-left: solid 1px #bbb'>" . number_format($asientoDebe, 2, '.', '') . "</td>
                                <td style='text-align: center; border-left: solid 1px #bbb'>" . number_format($asientoHaber, 2, '.', '') . "</td>
                            </tr>";
                        }
                        ?>

                    </table>
                </div>

                <!-- <section id="planDeCuentasSection" <?php if ($isUpdate)
                                                            echo 'style="display:none;"' ?>>
                    
                </section>

                <section id="altaModificacionSection" <?php if (!$isUpdate)
                                                            echo 'style="display:none;"' ?>>
                </section> -->
            </div>
        </main>
    </div>

    <footer>
        <p>&copy; 2023 Sistema Contable</p>
    </footer>

    <script>
        var cuentas = <?php echo obtenerCuentas(); ?>;
        console.log(cuentas);

        $("#fecha_asiento").on("change", function() {
            // Llamar a la función para actualizar el número de asiento
            actualizarNumeroAsiento();
        });

        var idFila = 1; // Inicializa una variable para llevar un seguimiento de los ID únicos de las filas

        function agregarFila() {
            var nuevaFila =
                "<tr data-id='" +
                idFila +
                "'>" +
                "<td width='18%'><input type='text' name='cuenta[]' class='form-control cuenta-autocomplete' value=''></td>" +
                "<td width='18%'><input type='text' name='descripcion[]' class='form-control descripcion-autocomplete' value=''></td>" +
                "<td width='18%'><input type='number' name='debe[]' class='form-control' step='0.01' value='0.00' min='0' saldo=''></td>" +
                "<td width='18%'><input type='number' name='haber[]' class='form-control' step='0.01' value='0.00' min='0'saldo=''></td>" +
                "<td width='18%'><a href='javascript:void(0);' onclick='eliminarFila(this)'>Eliminar</a></td>" +
                "</tr>";

            $("#tabla_asientos").append(nuevaFila);

            $(".cuenta-autocomplete").autocomplete({
                source: cuentas,
                minLength: 1
            });

            // Aplica el autocompletado y la validación a la nueva fila mediante la delegación de eventos
            $("#tabla_asientos")
                .on("change", ".cuenta-autocomplete", function() {
                    cuentas = <?php echo obtenerCuentas(); ?>;
                    $(this).autocomplete({
                        source: function(request, response) {
                            var term = request.term.toLowerCase();
                            var cuentasFiltradas = $.grep(cuentas, function(item) {
                                return item.value.toLowerCase().startsWith(term);
                            });
                            response(cuentasFiltradas);
                        },
                        minLength: 1,
                        select: function(event, ui) {

                            // Al seleccionar una cuenta, establecer la descripción correspondiente
                            var descripcionCorrespondiente = ui.item.descripcion;
                            $(this).closest("tr").find(".descripcion-autocomplete").val(descripcionCorrespondiente);

                        }
                    });
                })
                .on("change", ".cuenta-autocomplete", function() {
                    validarCuenta($(this));
                });

            idFila++;
        }

        function eliminarFila(enlace) {
            $(enlace).closest("tr").remove();
        }

        function validarCuenta(input) {
            var cuenta = input.val();
            var cuentaExistente = false;

            cuentas.forEach(function(item) {
                if (item.value === cuenta && cuenta.length > 4) {
                    var fila = input.closest("tr");
                    if (cuenta != "DESCRIPCION") {
                        fila.find("input[name='descripcion[]']").val(item.descripcion);
                        fila.find("input[name='descripcion[]']").attr('readonly', true);
                        fila.find("input[name='debe[]']").attr('readonly', false);
                        fila.find("input[name='haber[]']").attr('readonly', false);
                        fila.find("input[name='debe[]']").attr("saldo", item.saldo);
                        fila.find("input[name='haber[]']").attr("saldo", item.saldo);
                        console.log(fila.find("input[name='debe[]']").val() + "|" + item.saldo);
                        if ((item.value[0] == '2' || item.value[0] == '3' || item.value[0] == '4') && parseFloat(fila.find("input[name='debe[]']").val()) > item.saldo) {
                            fila.find("input[name='debe[]']").val(0.00);
                        }
                        if ((item.value[0] == '1' || item.value[0] == '5') && parseFloat(fila.find("input[name='haber[]']").val()) > item.saldo) {
                            fila.find("input[name='haber[]']").val(0.00);
                        }
                    } else {
                        fila.find("input[name='descripcion[]']").attr('placeholder', item.descripcion);
                        fila.find("input[name='debe[]']").attr('readonly', true);
                        fila.find("input[name='haber[]']").attr('readonly', true);
                    }
                    cuentaExistente = true;
                }

            });

            if (!cuentaExistente) {
                input.val("");
                input.closest("tr").find(".descripcion-autocomplete").val("");
                input.closest("tr").find("input[name='descripcion[]']").attr('readonly', false);
                return cuenta;
            }

            return null;
        }

        function validarMontos(input) {
            var esDebe = $(input).attr("name") === "debe[]";
            var cuentaCodigo = $(input).closest("tr").find(".cuenta-autocomplete").val();
            saldoCuenta = $(input).attr("saldo");

            console.log(saldoCuenta);
            var montoIngresado = parseFloat($(input).val()) || 0;

            // Verificar si el código de cuenta comienza con 1 o 7
            var permiteSuperarSaldo = cuentaCodigo.startsWith("1") || cuentaCodigo.startsWith("5");

            if ((esDebe && !permiteSuperarSaldo && montoIngresado > saldoCuenta) ||
                (!esDebe && permiteSuperarSaldo && montoIngresado > saldoCuenta)) {
                alert("El monto ingresado supera el saldo de la cuenta.");
                $(input).val(saldoCuenta); // Restablecer el valor al saldo de la cuenta
            }
        }


        function actualizarNumeroAsiento() {
            // Obtener el año del input de fecha
            console.log("Iniciando la función actualizarNumeroAsiento");

            // Obtener el año del input de fecha
            var year = new Date($("#fecha_asiento").val()).getFullYear();
            console.log("Año obtenido:", year);
            var fechaCompleta = $("#fecha_asiento").val();

            // Desglosa la fecha en variables
            var partesFecha = fechaCompleta.split('-');
            var año = partesFecha[0];
            var mes = partesFecha[1];
            var día = partesFecha[2];

            // Imprime las variables
            console.log("Año: " + año);
            console.log("Mes: " + mes);
            console.log("Día: " + día);

            // Obtener el número de asiento mediante AJAX
            $.ajax({
                type: "POST",
                url: "obtener_numero_asiento.php", // Reemplaza con la URL correcta
                data: {
                    year: year
                },
                success: function(response) {
                    // Actualizar el número de asiento en el HTML
                    nroAsiento = parseInt(response);
                    $("#titulo_asiento").text("Agregar Asiento - Número de Asiento: " + nroAsiento);
                },
                error: function(error) {
                    console.error(error);
                    alert("Hubo un error al obtener el número de asiento.");
                }

            });
        }


        function guardarAsiento() {

            // Validar que la suma de los valores en "Debe" sea igual a la suma en "Haber"
            var totalDebe = 0;
            var totalHaber = 0;

            $("input[name='debe[]']").each(function() {
                totalDebe += parseFloat($(this).val()) || 0;
            });

            $("input[name='haber[]']").each(function() {
                totalHaber += parseFloat($(this).val()) || 0;
            });

            if ((totalDebe + totalHaber) <= 0) {
                alert("El saldo de la cuenta no puede ser negativo o igual a 0");
                return;

            } else if ((totalDebe - totalHaber) !== 0) {
                alert("el total del 'Debe' debe ser igual al total del 'Haber'.");
                return;
            }



            // Validar que todas las cuentas ingresadas existen en el plan de cuentas
            var cuentasIngresadas = $("input[name='cuenta[]']");
            var cuentasNoValidas = [];

            cuentasIngresadas.each(function() {
                var cuentaNoValida = validarCuenta($(this));
                if (cuentaNoValida !== null) {
                    cuentasNoValidas.push(cuentaNoValida);
                }
            });

            if (cuentasNoValidas.length > 0) {
                alert("Las siguientes cuentas no son válidas:\n" + cuentasNoValidas.join("\n"));
                return;
            }

            // Obtener el número de asiento actual

            console.log("Iniciando la función actualizarNumeroAsiento");

            // Obtener el año del input de fecha
            var year = new Date($("#fecha_asiento").val()).getFullYear();
            console.log("Año obtenido:", year);
            var fechaAsiento = $("#fecha_asiento").val();

            // Desglosa la fecha en variables
            var partesFecha = fechaAsiento.split('-');
            var anio = partesFecha[0];
            var mes = partesFecha[1];

            // Obtener el número de asiento mediante AJAX
            $.ajax({
                type: "POST",
                url: "obtener_numero_asiento.php", // Reemplaza con la URL correcta
                data: {
                    year: year
                },
                success: function(response) {
                    // Actualizar el número de asiento en el HTML
                    var nroAsiento = parseInt(response);


                    // Obtener los datos de la tabla
                    var datos = {
                        'anio': anio,
                        'mes': mes,
                        'fecha': fechaAsiento,
                        'nroAsiento': nroAsiento,
                        'cuentas': [],
                        'descripciones': [],
                        'debes': [],
                        'haberes': []
                    };

                    $("#tabla_asientos tbody tr").each(function(index) {
                        datos.cuentas.push($(this).find("input[name='cuenta[]']").val());
                        datos.descripciones.push($(this).find("input[name='descripcion[]']").val());
                        datos.debes.push($(this).find("input[name='debe[]']").val());
                        datos.haberes.push($(this).find("input[name='haber[]']").val());
                    });

                    console.log(datos);

                    $.ajax({
                        type: "POST",
                        url: "actualizar_mayor.php",
                        data: {
                            datos: datos
                        },
                        success: function(response) {
                            if (response === "success") {
                                // Libros mayores actualizados correctamente, ahora guardar el asiento
                                guardarAsientoEnBD(datos);
                            } else {
                                console.error(response);
                                //alert("Hubo un error al actualizar los libros mayores.");
                            }
                        },
                        error: function(error) {
                            console.error(error);
                            alert("Hubo un error al actualizar los libros mayores.");
                        }
                    });
                },
                error: function(error) {
                    console.error(error);
                    //alert("Hubo un error al obtener el número de asiento.");
                }
            });

        }

        function guardarAsientoEnBD(datos) {
            // Continuar con el código para guardar el asiento en la base de datos
            console.log(datos);
            $.ajax({
                type: "POST",
                url: "guardar_asiento.php",
                data: datos,
                success: function(response) {
                    // Actualizar la página después de guardar el asiento
                    location.reload();
                },
                error: function(error) {
                    console.error(error);
                    alert("Hubo un error al guardar el asiento.");
                }
            });
        }
    </script>


</body>

</html>