<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Bancario</title>

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/css/bootstrap.min.css"
        crossorigin="anonymous">
    <link rel='stylesheet' href='/ProyectoContabilidad//css/search.css'>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
        crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    </script>

    <!--Scripts-->    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var planDeCuentasSection = document.getElementById("planDeCuentasSection");
            var altaModificacionSection = document.getElementById("altaModificacionSection");            
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
    min-height: 100vh; /* Ajuste importante */
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
    min-height: 100vh; /* Ajuste importante */
}

nav {
    background-color:  #1F97AF;
    overflow: hidden;
    width: 200px;
}

nav a {
    display: block;
    color: white;
    text-align: center;
    padding: 14px 16px;
    text-decoration: none;
}

nav a:hover {
    background-color: #ddd;
    color: black;
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
        <h1>Libro Mayor</h1>
    </header>

    <nav>
        <div class="container container-center">
            <a href="/ProyectoContabilidad/index.php">Inicio</a>
            <a href="/ProyectoContabilidad/vistas/libro_diario/libro_diario.php">Libro Diario</a>
            <a href="#">Mayor</a>
            <a href="/ProyectoContabilidad/vistas/plan_de_cuentas/plan_de_cuentas.php">Plan de Cuentas</a>
        </div>
    </nav>
    </div>
    <main>
        <div class="container container-center container-fluid vh-100 overflow-auto">


            <!--  [  FECHA Y CONSULTA  ]  -->
            <div class="row">
                <div class="col-md-5">
                    <label for="fecha" class="search-form_label"> Seleccione fecha a Consultar:
                        <input class="search-form_input" type="month" id="fecha" name="fecha" autocomplete="off"
                            value="<?php echo date('Y-m'); ?>" />
                        <span class="search-form_liveout"></span>
                    </label>
                </div>
                <div class="col-md-7">
                    <button type="button" class="btn btn-block btn-secondary" style="height: 80px;" onclick="consultarLibroMayor()">Consultar</button>
                </div>
            </div>

            <hr class="mb-3">

            <table class="table">
                <thead class="sticky-top" style="background-color:#f4f4f4">
                    <tr>
                        <th>Nombre de Cuenta</th>
                        <th>Numero Cuenta</th>
                        <th>Total Debe</th>
                        <th>Total Haber</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody id="resultadoTabla">
                    <!-- Aquí se mostrarán los resultados de la consulta -->
                </tbody>
            </table>

            <script src="consulta.js"></script>
            <script>
                consultarLibroMayor();
            </script>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 Sistema Contable</p>
    </footer>

</body>

</html>