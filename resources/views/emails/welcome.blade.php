<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a {{ env('APP_NAME') }}</title>
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
            <h1>¡Bienvenido a {{ env('APP_NAME') }}!</h1>
        </div>
        <div class="content">
            <h2>Hola, {{ $user->name }}!</h2>
            @if ($role != 'root')
            <p>Estamos emocionados de que te unas a nuestra comunidad de {{ env('APP_NAME') }}, 
                donde la experiencia de reserva de turnos es más fácil y divertida.</p> 
            @endif

            <br>
            @if ($role == 'cliente')
            <div class="content-item">
                <p>¡Gracias por registrarte! Ahora puedes reservar tus turnos 
                    con solo un clic y disfrutar de un servicio excepcional. ¡Te esperamos 
                    en {{ env('BARBERSHOP_NAME') }} para cuidar de tu estilo!</p>
            </div>

            @elseif ($role == 'peluquero')
            <div class="content-item">
                <p>¡Bienvenido al equipo de {{ env('BARBERSHOP_NAME') }}! Tu talento y dedicación son 
                    clave para ofrecer a nuestros clientes el mejor servicio.</p>
            </div>
            @elseif ($role == 'root')
            <div class="content-item">
                <p>El sistema de {{ env('APP_NAME') }} se ha lanzado y está en línea. Ahora puedes comenzar a gestionar 
                    todas las operaciones y asegurarte de que todo funcione sin problemas.</p>
            </div>

            @else
            <div class="content-item">
                <p>¡Bienvenido! Estamos encantados de que formes parte de nuestra comunidad en {{ env('APP_NAME') }}. 
                    ¡Explora y descubre todo lo que tenemos para ofrecerte!</p>
            </div>
            @endif

            @if ($role != 'cliente')
            <div class="content-item">
                <p class="left-align">Para comenzar, inicia sesión en tu cuenta con las siguientes credenciales:</p>
                <p class="left-align">Correo: {{ $user->email }}</p>
                <p class="left-align">Contraseña: {{ $password }}</p>
                <p class="left-align">Te sugerimos cambiar esta contraseña después de tu primer inicio de sesión.</p>
            </div>
            @endif
            
            <br>
            <div class="content-item">
                
                <!-- <p>Si tienes alguna pregunta, no dudes en contactarnos.</p> -->
                <p>¡Que tengas un gran día!</p>
            </div>
        </div>
        
        <div class="footer">
            <p>Gracias por elegir {{ env('APP_NAME') }}.</p>
            <p>Este es un mensaje automático, por favor no respondas.</p>
        </div>
    </div>
</body>

</html>