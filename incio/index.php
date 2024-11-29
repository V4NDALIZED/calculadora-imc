<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel IMC</title>
    <link rel="stylesheet" href="../css/index.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="container">
    <h1>Estadísticas de IMC</h1>

    <!-- Descripción del IMC -->
    <div class="description">
        <h2>¿Qué es el IMC?</h2>
        <p>
            El <strong>Índice de Masa Corporal (IMC)</strong> es una fórmula matemática utilizada para evaluar la relación entre el peso y la altura de una persona. Aunque no mide directamente la grasa corporal, el IMC se emplea como una herramienta de evaluación rápida para identificar si una persona tiene un peso saludable, bajo o elevado. 
        </p>
        <p>
            La fórmula es la siguiente:
            <br>
            <code>IMC = Peso (kg) / Altura (m)²</code>
        </p>
        <p>
            El IMC se interpreta de la siguiente manera:
        </p>
        <ul>
            <li><strong>Bajo peso:</strong> IMC menor a 18.5</li>
            <li><strong>Peso normal:</strong> IMC entre 18.5 y 24.9</li>
            <li><strong>Sobrepeso:</strong> IMC entre 25 y 29.9</li>
            <li><strong>Obesidad:</strong> IMC superior a 30</li>
        </ul>
    </div>

    <div class="btn-group">
        <button onclick="window.location.href='resultados.php'">Calcular IMC</button>
    </div>

    <h2>Distribución por Edades</h2>
    <canvas id="ageChart"></canvas>

    <h2>Categorías de IMC</h2>
    <canvas id="imcChart"></canvas>
</div>

<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "imc";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("<p>Error de conexión: " . $conn->connect_error . "</p>");
}

// Obtener datos de edades
$ageData = [];
$sql = "SELECT edad, COUNT(*) as cantidad FROM registros GROUP BY edad";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ageData[] = $row;
    }
}

// Obtener datos de categorías de IMC
$imcData = [
    'Bajo peso' => 0,
    'Peso normal' => 0,
    'Sobrepeso' => 0,
    'Obesidad' => 0,
];

$sql = "
    SELECT 
        CASE 
            WHEN imc < 18.5 THEN 'Bajo peso'
            WHEN imc >= 18.5 AND imc < 25 THEN 'Peso normal'
            WHEN imc >= 25 AND imc < 30 THEN 'Sobrepeso'
            ELSE 'Obesidad'
        END as categoria,
        COUNT(*) as cantidad
    FROM registros
    GROUP BY categoria
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $imcData[$row['categoria']] = $row['cantidad'];
    }
}

$conn->close();
?>

<script>
// Datos de edades (PHP a JavaScript)
const ageLabels = <?php echo json_encode(array_column($ageData, 'edad')); ?>;
const ageCounts = <?php echo json_encode(array_column($ageData, 'cantidad')); ?>;

// Gráfico de barras: Distribución por edades
const ageCtx = document.getElementById('ageChart').getContext('2d');
new Chart(ageCtx, {
    type: 'bar',
    data: {
        labels: ageLabels,
        datasets: [{
            label: 'Cantidad por Edad',
            data: ageCounts,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Datos de IMC (PHP a JavaScript)
const imcLabels = <?php echo json_encode(array_keys($imcData)); ?>;
const imcCounts = <?php echo json_encode(array_values($imcData)); ?>;

// Gráfico de pastel: Categorías de IMC
const imcCtx = document.getElementById('imcChart').getContext('2d');
new Chart(imcCtx, {
    type: 'pie',
    data: {
        labels: imcLabels,
        datasets: [{
            label: 'Categorías de IMC',
            data: imcCounts,
            backgroundColor: [
                'rgba(255, 99, 132, 0.6)',
                'rgba(75, 192, 192, 0.6)',
                'rgba(255, 206, 86, 0.6)',
                'rgba(153, 102, 255, 0.6)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(153, 102, 255, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
    }
});
</script>

</body>
</html>
