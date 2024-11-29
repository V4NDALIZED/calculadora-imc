<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora IMC</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<div class="container">
    <h1>Calculadora IMC</h1>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Configuración de conexión
        $servername = "localhost";
        $username = "root"; // Cambia si tienes otra configuración
        $password = ""; // Contraseña vacía por defecto en XAMPP
        $dbname = "imc";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("<p>Error de conexión: " . $conn->connect_error . "</p>");
        }

        // Recibir datos del formulario
        $nombre = empty($_POST['nombre']) ? null : $_POST['nombre'];
        $edad = $_POST['edad'];
        $altura = $_POST['altura'];
        $peso = $_POST['peso'];

        // Validar entradas
        if (!is_numeric($edad) || $edad <= 0 || !is_numeric($altura) || $altura <= 0 || !is_numeric($peso) || $peso <= 0) {
            echo "<p style='color: red;'>Por favor, ingresa datos válidos.</p>";
        } else {
            // Calcular IMC
            $imc = $peso / ($altura * $altura);

            // Determinar categoría
            $categoria = match (true) {
                $imc < 18.5 => 'Bajo peso',
                $imc >= 18.5 && $imc < 25 => 'Peso normal',
                $imc >= 25 && $imc < 30 => 'Sobrepeso',
                default => 'Obesidad',
            };

            // Insertar en la base de datos
            $sql = "INSERT INTO registros (nombre, edad, peso, altura, imc) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdddd", $nombre, $edad, $peso, $altura, $imc);

            if ($stmt->execute()) {
                echo "<h2>Resultados</h2>";
                echo "<p>Tu IMC es: " . number_format($imc, 2) . "</p>";
                echo "<p>Categoría: $categoria</p>";
                if (!empty($nombre)) {
                    echo "<p>Gracias, $nombre, por usar nuestra calculadora.</p>";
                }
            } else {
                echo "<p>Error al guardar los datos: " . $conn->error . "</p>";
            }

            $stmt->close();
        }

        $conn->close();
    }
    ?>

    <form action="" method="POST">
        <div class="input-group">
            <label for="nombre">Nombre (opcional)</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ej: Juan Pérez">
        </div>
        <div class="input-group">
            <label for="edad">Edad</label>
            <input type="number" id="edad" name="edad" placeholder="Ej: 25" required>
        </div>
        <div class="input-group">
            <label for="altura">Altura (en metros)</label>
            <input type="text" id="altura" name="altura" placeholder="Ej: 1.75" required>
        </div>
        <div class="input-group">
            <label for="peso">Peso (en kilos)</label>
            <input type="text" id="peso" name="peso" placeholder="Ej: 70" required>
        </div>
        <button type="submit">Calcular IMC</button>
        <button type="button" onclick="location.href='index.php'">Volver al Inicio</button>
    </form>
</div>

</body>
</html>
