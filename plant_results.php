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


$plantSQL = "SELECT *
             FROM plants 
             WHERE id = :id";
$stmt = $pdo->prepare($plantSQL);
$stmt->execute(['id' => $id]);
$plants = $stmt->fetch();


if (!$plants) {
    die('<p>Target not found.</p>');
}

$npSQL = "SELECT nc.*
          FROM natural_products AS nc
          JOIN np_plants AS np ON np.natural_products_id = nc.id
          WHERE np.plants_id = :id";
$stmt = $pdo->prepare($npSQL);
$stmt->execute(['id' => $id]);
$np = $stmt->fetchAll();

$npIDs = array_column($np, 'id');

if (!$npIDs) {
    $targets = [];
    $diseases = [];
} else {
    $placeholders = implode(',', array_fill(0, count($npIDs), '?'));
    $targetSQL = "SELECT DISTINCT t.*
              FROM targets AS t
              JOIN np_targets AS nt ON t.id = nt.targets_id
              WHERE nt.natural_products_id IN ($placeholders)";
    $stmt = $pdo->prepare($targetSQL);
    $stmt->execute($npIDs);
    $targets = $stmt->fetchAll();

$diseaseSQL = "SELECT DISTINCT d.*
               FROM diseases AS d
               JOIN np_diseases AS nd ON d.id = nd.diseases_id
               WHERE nd.natural_products_id IN ($placeholders)";
$stmt = $pdo->prepare($diseaseSQL);
$stmt->execute($npIDs);
$diseases = $stmt->fetchAll();
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
    <h1>WAND³ - results for <?php echo htmlspecialchars($plants['genus'] . ' ' . $plants['species']); ?></h1>
    <div class = "results-box">
    <h2>Plant details</h2>
    <p><strong>Scientific name:</strong> <?php echo htmlspecialchars($plants['genus'] . ' ' . $plants['species']); ?></p>
    <p><strong>Common names:</strong> <?php echo htmlspecialchars($plants['common_names']); ?></p>
    <p><strong>Ethnobotanical use:</strong> <?php echo htmlspecialchars($plants['uses']); ?></p>
    <p><strong>Locations</strong> <?php echo htmlspecialchars($plants['locations']); ?></p>
    <p><strong>Family:</strong> <?php echo htmlspecialchars($plants['family']); ?></p>
    <p><strong>Genus:</strong> <?php echo htmlspecialchars($plants['genus']); ?></p>
    <p><strong>Species:</strong> <?php echo htmlspecialchars($plants['species']); ?></p>
    </div>

    <div class = "results-box">
    <h2>Natural products</h2>
    <ul>
        <?php foreach ($np as $ligand): ?>
            <a href="np_results.php?id=<?php echo urlencode($ligand['id']); ?>"><strong><?php echo htmlspecialchars($ligand['name']); ?></strong></a><br>
        <?php endforeach; ?>
    </ul>
        </div>

    <div class = "results-box">
    <h2>Associated Diseases (by natural product)</h2>
    <ul>
        <?php foreach ($diseases as $disease): ?>
            <a href="disease_results.php?id=<?php echo urlencode($disease['id']); ?>"><strong><?php echo htmlspecialchars($disease['name']); ?></strong></a><br>
        <?php endforeach; ?>
    </ul>
    </div>
    <div class = "results-box">
    <h2>Associated targets (associated by np)</h2>
    <ul>
        <?php foreach ($targets as $target): ?>
            <a href="target_results.php?id=<?php echo urlencode($target['id']); ?>"><strong><?php echo htmlspecialchars($target['name']); ?></strong></a><br>
        <?php endforeach; ?>
    </ul>
        </div>
    </div>
</body>
</html>