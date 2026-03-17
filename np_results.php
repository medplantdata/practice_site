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

$npSQL = "SELECT * FROM natural_products WHERE id = :id";
$stmt = $pdo->prepare($npSQL);
$stmt->execute(['id' => $id]);
$np = $stmt->fetch();

if (!$np) {
    die('<p>Natural product not found.</p>');
}

$diseaseSQL = "SELECT  d.*
               FROM diseases AS d
               JOIN np_diseases AS nd ON d.id = nd.diseases_id
               WHERE nd.natural_products_id = :id";
$stmt = $pdo->prepare($diseaseSQL);
$stmt->execute(['id' => $id]);
$diseases = $stmt->fetchAll();

$targetSQL = "SELECT t.*
              FROM targets AS t
              JOIN np_targets AS nt ON t.id = nt.targets_id
              WHERE nt.natural_products_id = :id";
$stmt = $pdo->prepare($targetSQL);
$stmt->execute(['id' => $id]);
$targets = $stmt->fetchAll();

$plantSQL = "SELECT p.*
             FROM plants AS p
             JOIN np_plants AS np ON p.id = np.plants_id
             WHERE np.natural_products_id = :id";
$stmt = $pdo->prepare($plantSQL);
$stmt->execute(['id' => $id]);
$plants = $stmt->fetchAll();  

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
    <h1>WAND³ - results for <?php echo htmlspecialchars($np['name']); ?></h1>
    <h2>Natural Product Details</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($np['name']); ?></p>
    <p><strong>InChIKey:</strong> <?php echo htmlspecialchars($np['inchikey']); ?></p>
    <p><strong>SMILES:</strong> <?php echo htmlspecialchars($np['smiles']); ?></p>
    <p><strong>InChI:</strong> <?php echo htmlspecialchars($np['inchi']); ?></p>
    <p><strong>Other Names:</strong> <?php echo htmlspecialchars($np['names']); ?></p>
    <p><strong>PubMed IDs:</strong> <?php echo htmlspecialchars($np['pubmedids']); ?></p>

    <h2>Associated Diseases</h2>
    <ul>
        <?php foreach ($diseases as $disease): ?>
            <li><?php echo htmlspecialchars($disease['name']); ?></li>
        <?php endforeach; ?>
    </ul>

    <h2>Associated Targets</h2>
    <ul>
        <?php foreach ($targets as $target): ?>
            <li><?php echo htmlspecialchars($target['name']); ?></li>
        <?php endforeach; ?>
    </ul>

    <h2>Associated Plants</h2>
    <ul>
        <?php foreach ($plants as $plant): ?>
            <li><?php echo htmlspecialchars($plant['genus'] . ' ' . $plant['species']); ?></li>
        <?php endforeach; ?>
    </ul>
    </div>
</body>
</html>