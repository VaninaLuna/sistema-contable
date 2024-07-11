

function consultarLibroMayor() {
    var fecha = document.getElementById('fecha').value;

    // Realizar una solicitud AJAX (puedes usar Fetch o jQuery.ajax)
    // Enviar la fecha al servidor para la consulta y obtener los resultados

    // Supongamos que tienes un archivo PHP llamado "consulta.php" para manejar la lógica del servidor
    var url = 'consulta.php?fecha=' + fecha;

    // Ejemplo usando Fetch API
    fetch(url)
        .then(response => response.json())
        .then(data => mostrarResultados(data))
        .catch(error => console.error('Error:', error));
}



function mostrarResultados(data) {
    console.log(data);
    var tabla = document.getElementById('resultadoTabla');
    tabla.innerHTML = ''; // Limpiar la tabla antes de mostrar nuevos resultados
    
    data.forEach(function (fila) {
        var nuevaFila = tabla.insertRow();
        
        nuevaFila.insertCell(0).textContent = fila.Str_NombreCuenta;
        nuevaFila.insertCell(1).textContent = fila.Str_NroCuenta;
        nuevaFila.insertCell(2).textContent = fila.Dbl_TotalDebe;
        nuevaFila.insertCell(3).textContent = fila.Dbl_TotalHaber;
        nuevaFila.insertCell(4).textContent = fila.Dbl_Saldo;
    });
}


