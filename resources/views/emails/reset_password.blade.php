<!DOCTYPE html>
<html>
<head>
    <title>Restablecimiento de Contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            background-color: #ffffff;
            margin: 50px auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }
        h1 {
            color: #333333;
        }
        p {
            color: #555555;
            line-height: 1.6;
        }
        .code {
            font-size: 20px;
            font-weight: bold;
            color: #2E86C1;
            background-color: #f0f8ff;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #999999;
        }
    </style>
</head>
<body>
    <div class="container">
        <p>Hola, {{ $user->name }}!</p>
        <h1>Este es el código de verificación para restablecer tu contraseña</h1>
        
        <p>Hemos recibido una solicitud para restablecer tu contraseña. Utiliza el siguiente código para completar el proceso:</p>
        <p class="code">{{ $code }}</p>
        <p>Si no solicitaste un restablecimiento de contraseña, por favor ignora este correo.</p>
        <div class="footer">
            <p>Este es un correo electrónico generado automáticamente, por favor no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>
