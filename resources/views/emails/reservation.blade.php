<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="stylesheet" href="Style.css">
   <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmación de Reserva</title>
   <style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
  }
  
  .container {
    max-width: 600px;
    margin: 20px auto;
    background-color: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    /* box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  }
  
  .header {
    text-align: center;
    padding: 20px;
    font-size: 24px;
    color: #333333;
  }
  
  .header span {
    font-weight: bold;
    color: #0d0e0d;
  }
  
  .section-title {
    background-color: #5b5dca;
    color: #ffffff;
    padding: 10px;
    font-size: 16px;
    font-weight: bold;
  }
  
  .content {
    padding: 20px;
  }
  
  .details-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .details-table th, .details-table td {
    padding: 10px;
    border-bottom: 1px solid #dddddd;
    text-align: left;
  }
  
  .details-table th {
    font-weight: bold;
    color: #666666;
  }
  
  .details-table td {
    color: #333333;
  }
  
  .button {
    display: block;
    width: fit-content;
    margin: 20px auto;
    padding: 10px 20px;
    color: #ffffff;
    background-color: #4CAF50;
    text-decoration: none;
    border-radius: 5px;
    font-size: 16px;
    text-align: center;
  }
  
  .content p {
    text-align: center;
    font-weight: bold;
  }
  .footer {
    background-color: #f4f4f4;
    color: #666666;
    text-align: center;
    padding: 10px;
    font-size: 14px;
  }
   </style>

</head>
<body>
  <div class="container">
    @if ($role == 'peluquero')
    <div class="header">
        Hola <span>{{ $barber_name }}</span>, tienes una nueva reserva, revisa su agenda.<br>
    </div>
    @else
      <div class="header">
      Hola <span>{{ $client_name }}</span>, tu reserva ha sido confirmada.<br>
      </div>
    @endif   
    
    <div class="section-title">
        DETALLES
    </div>

    <div class="content">
      <p><strong>{{ env('BARBERSHOP_NAME') }}</strong></p>
      
      <table class="details-table">
        <tr>
          <th>Fecha</th>
          <td>{{ $date }}</td>
        </tr>
        <tr>
          <th>Hora</th>
          <td>{{ $time }}</td>
        </tr>
        @if ($role == 'peluquero')
          <tr>
            <th>Cliente</th>
            <td>{{ $client_name }}</td>
          </tr>
        @else
          <tr>
            <th>Peluquero</th>
            <td>{{ $barber_name }}</td>
          </tr>
        @endif 
        
        <tr>
          <th>Servicio</th>
          <td>{{ $services_details }}</td>
        </tr>
      </table>
      <!-- <p><strong>¡Gracias por elegirnos!</p> -->
    </div>

    <div class="footer">
        <p><strong>¡Gracias por elegir {{ env('APP_NAME') }}!</strong></p>
        <p>Este es un mensaje automático, por favor no lo respondas.</p>
    </div>
  </div>
</body>
</html>  
