<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a NOMBRE_APP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #eaeaea;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header {
            background-color: #4a90e2;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 20px;
            text-align: center;
        }

        .content-item {
            color: black;
            border-radius: 5px;
            margin: 10px 0;
        }

        .footer {
            background-color: #f4f4f4;
            text-align: center;
            padding: 10px;
            font-size: 0.9em;
            color: #555;
        }

        .left-align {
            text-align: left;
            margin-left: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>¡Bienvenido a NOMBRE_APP!</h1>
        </div>
        <div class="content">
            <h2>Hola, {{ $user->name }}!</h2>

            <p>Estamos emocionados de que te unas a nuestra comunidad de NOMBRE_APP, 
                donde la experiencia de reserva de turnos es más fácil y divertida.</p>
            <br>
            @if ($role == 'cliente')
            <div class="content-item">
                <p>¡Gracias por registrarte! Ahora puedes reservar tus turnos 
                    con solo un clic y disfrutar de un servicio excepcional. ¡Te esperamos 
                    en NOMBRE_BARBERÍA para cuidar de tu estilo!</p>
            </div>

            @elseif ($role == 'peluquero')
            <div class="content-item">
                <p>¡Bienvenido al equipo de NOMBRE_BARBERÍA! Tu talento y dedicación son 
                    clave para ofrecer a nuestros clientes el mejor servicio.</p>

                <p class="left-align">Tu correo: {{ $user->email }}</p>
                <p class="left-align">Contraseña: {{ $password }}</p>
                <p class="left-align">Te sugerimos cambiar esta contraseña después de tu primer inicio de sesión.</p>
            </div>
            @elseif ($role == 'root')
            <div class="content-item">
                <p>El sistema se ha lanzado y está en línea. Ahora puedes comenzar a gestionar 
                    todas las operaciones y asegurarte de que todo funcione sin problemas.</p>

                <p class="left-align">Tu correo: {{ $user->email }}</p>
                <p class="left-align">Contraseña: {{ $password }}</p>
                <p class="left-align">Te sugerimos cambiar esta contraseña después de tu primer inicio de sesión.</p>
            </div>

            @else
            <div class="content-item">
                <p>¡Bienvenido! Estamos encantados de que formes parte de nuestra comunidad en NOMBRE_APP. 
                    ¡Explora y descubre todo lo que tenemos para ofrecerte!</p>

                <p class="left-align">Tu correo: {{ $user->email }}</p>
                <p class="left-align">Contraseña: {{ $password }}</p>
                <p class="left-align">Te sugerimos cambiar esta contraseña después de tu primer inicio de sesión.</p>
            </div>
            @endif
            <div class="content-item">
                <br>
                <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                <p>¡Que tengas un gran día!</p>
            </div>
        </div>
        
        <div class="footer">
            <p>Gracias por elegir NOMBRE_APP.</p>
            <p>Este es un mensaje automático, por favor no respondas.</p>
        </div>
    </div>
</body>

</html>