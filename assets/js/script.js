$(document).ready(function(){
    $('.log-btn').click(function(event){
        event.preventDefault(); // Prevenir el envío normal del formulario

        // Obtener los datos del formulario
        var username = $('input[name="username"]').val();
        var password = $('input[name="password"]').val();

        // Enviar los datos usando AJAX
        $.ajax({
            type: 'POST',
            url: 'php/login.php',
            data: {username: username, password: password},
            success: function(response) {
                if (response === 'success') {
                    // Redirigir a la página correspondiente si el inicio de sesión es exitoso
                    window.location.href = 'principal.php';
                } else {
                    // Si las credenciales son incorrectas, mostrar error visualmente
                    $('.log-status').addClass('wrong-entry');
                    $('.alert').fadeIn(500);
                    setTimeout(function() {
                        $('.alert').fadeOut(1500);
                    }, 3000);
                }
            },
            error: function() {
                alert('Error en la conexión con el servidor.');
            }
        });
    });

    $('.form-control').keypress(function(){
        $('.log-status').removeClass('wrong-entry'); // Remover clase de error al escribir en los campos
    });
});