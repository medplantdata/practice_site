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

$targetSQL = "SELECT *
              FROM targets 
              WHERE id = :id";
$stmt = $pdo->prepare($targetSQL);
$stmt->execute(['id' => $id]);
$targets = $stmt->fetch();

if (!$targets) {
    die('<p>Target not found.</p>');
}

$npSQL = "SELECT np.*
          FROM natural_products AS np
          JOIN np_targets AS nt ON np.id = nt.natural_products_id
          WHERE nt.targets_id = :id";
$stmt = $pdo->prepare($npSQL);
$stmt->execute(['id' => $id]);
$np = $stmt->fetchAll();

$npIDs = array_column($np, 'id');

$diseaseSQL = "SELECT d.*
               FROM diseases AS d
               JOIN targets_diseases AS td ON d.id = td.diseases_id
               WHERE td.targets_id = :id";
$stmt = $pdo->prepare($diseaseSQL);
$stmt->execute(['id' => $id]);
$diseases = $stmt->fetchAll();


if (!$npIDs) {
    $plants = [];
} else {
    $placeholders = implode(',', array_fill(0, count($npIDs), '?'));
    $plantSQL = "SELECT DISTINCT p.*
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
        <div class = home-help-box>
      <a href = "https://www.carlingblacklabel.co.za/">Help</a>
      <a href = main.html>Home</a>
    </div>
    <div class = "app">
    <h1>WAND³ - results for <?php echo htmlspecialchars($targets['name']); ?></h1>
    <div class = "results-box">
    <h2>Target details:</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($targets['name']); ?></p>
    <p><strong>PDB ID:</strong> <?php echo htmlspecialchars($targets['pdbid']); ?></p>
    <p><strong>UniProt ID:</strong> <?php echo htmlspecialchars($targets['uniprotid']); ?></p>
    </div>

    <div class = "results-box">
    <h2>Associated ligands:</h2>
    <ul>
        <?php foreach ($np as $ligand): ?>
            <a href="np_results.php?id=<?php echo urlencode($ligand['id']); ?>"><strong><?php echo htmlspecialchars($ligand['name']); ?></strong></a><br>
        <?php endforeach; ?>
    </ul>
        </div>

    <div class = "results-box">
    <h2>Associated Diseases:</h2>
    <ul>
        <?php foreach ($diseases as $disease): ?>
            <a href="disease_results.php?id=<?php echo urlencode($disease['id']); ?>"><strong><?php echo htmlspecialchars($disease['name']); ?></strong></a><br>
        <?php endforeach; ?>
    </ul>
    </div>

    <div class = "results-box">
    <h2>Associated Plants (associated by np):</h2>
    <ul>
        <?php foreach ($plants as $plant): ?>
            <a href="plant_results.php?id=<?php echo urlencode($plant['id']); ?>"><strong><?php echo htmlspecialchars($plant['genus'] . ' ' . $plant['species']); ?></strong></a><br>
        <?php endforeach; ?>
    </ul>
        </div>
    </div>
</body>
</html>