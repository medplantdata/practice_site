<?php
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$host     = 'localhost';
$dbname   = 'wandDB';
$username = 'root';
$password = '12345678';
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("<p>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}

$diseaseSQL = "SELECT *
               FROM diseases 
               WHERE id = :id";
$stmt = $pdo->prepare($diseaseSQL);
$stmt->execute(['id' => $id]);
$diseases = $stmt->fetch();


if (!$diseases) {
    die('<p>Disease not found.</p>');
}

$targetSQL = "SELECT t.*
              FROM targets as t
              JOIN targets_diseases AS td ON t.id = td.targets_id
              WHERE td.diseases_id = :id";
$stmt = $pdo->prepare($targetSQL);
$stmt->execute(['id' => $id]);
$targets = $stmt->fetchAll();

$npSQL = "SELECT np.*
          FROM natural_products AS np
          JOIN np_diseases AS nd ON np.id = nd.natural_products_id
          WHERE nd.diseases_id = :id";
$stmt = $pdo->prepare($npSQL);
$stmt->execute(['id' => $id]);
$np = $stmt->fetchAll();

$npIDs = array_column($np, 'id');

if (!$npIDs) {
    $plants = [];
} else {
    $placeholders = implode(',', array_fill(0, count($npIDs), '?'));
    $plantSQL = "SELECT p.*
                 FROM plants AS p
                 JOIN np_plants AS np ON p.id = np.plants_id
                 WHERE np.natural_products_id IN ($placeholders)";
    $stmt = $pdo->prepare($plantSQL);
    $stmt->execute($npIDs);
    $plants = $stmt->fetchAll();
}  

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>WAND³</title>
  <link rel="stylesheet" href="app.css" />
</head>
<body>
    <div class = "app">
    <h1>WAND³ - results for <?php echo htmlspecialchars($diseases['name']); ?></h1>
    <div class="results-box">
    <h2>Disease details</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($diseases['name']); ?></p>
    <p><strong>ICD-11 code:</strong> <?php echo htmlspecialchars($diseases['icd11']); ?></p>
    <p><strong>Category:</strong> <?php echo htmlspecialchars($diseases['category']); ?></p>
    </div>
    <div class="results-box">
    <h2>Associated drugs</h2>
        <ul>
        <?php foreach ($np as $ligand): ?>
            <a href="np_results.php?id=<?php echo urlencode($ligand['id']); ?>"><strong><?php echo htmlspecialchars($ligand['name']); ?></strong></a><br>
        <?php endforeach; ?>
    </ul>
    </div>
    
    <div class="results-box">
    <h2>Associated targets</h2>
    <ul>
        <?php foreach ($targets as $target): ?>
            <a href="target_results.php?id=<?php echo urlencode($target['id']); ?>"><strong><?php echo htmlspecialchars($target['name']); ?></strong></a><br>
        <?php endforeach; ?>
    </ul>
        </div>
    
    <div class="results-box">
    <h2>Associated Plants (associated by np)</h2>
    <ul>
        <?php foreach ($plants as $plant): ?>
            <a href="plant_results.php?id=<?php echo urlencode($plant['id']); ?>"><strong><?php echo htmlspecialchars($plant['genus'] . ' ' . $plant['species']); ?></strong></a><br>
        <?php endforeach; ?>
    </ul>
        </div>
    </div>
</body>
</html>