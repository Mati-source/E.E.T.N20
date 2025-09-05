function closed() {
    var nuevaVentana = window.open('index.html', '_blank');
    if (nuevaVentana) {
        window.close();
    } else {
        alert("No se pudo abrir la nueva ventana. Por favor, verifica las configuraciones de tu navegador.");
    }
}