<?php
include '../view/connection.php';

$sql = "SELECT YEAR(date) as year, sitio_name, COUNT(*) as count 
        FROM trips 
        GROUP BY sitio_name, YEAR(date)
        ORDER BY YEAR(date)";

$result = $conn->query($sql);

$data = [];
$years = [];
$sitioData = [];

while ($row = $result->fetch_assoc()) {
    $year = $row['year'];
    $sitio = $row['sitio_name'];
    $count = $row['count'];

    if (!in_array($year, $years)) $years[] = $year;
    $sitioData[$sitio][$year] = $count;
}

$datasets = [];
$colors = ['#f39c12', '#27ae60', '#8e44ad', '#3498db', '#c0392b'];
$i = 0;

foreach ($sitioData as $sitio => $yearlyCounts) {
    $dataPoints = [];
    foreach ($years as $yr) {
        $dataPoints[] = isset($yearlyCounts[$yr]) ? $yearlyCounts[$yr] : 0;
    }

    $datasets[] = [
        'label' => $sitio,
        'data' => $dataPoints,
        'borderColor' => $colors[$i % count($colors)],
        'backgroundColor' => $colors[$i % count($colors)],
        'fill' => true
    ];
    $i++;
}

echo json_encode([
    'years' => $years,
    'datasets' => $datasets
]);
?>
