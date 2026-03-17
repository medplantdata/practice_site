<?php
// Read query and search type from GET
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$search_type = isset($_GET['search-type']) ? $_GET['search-type'] : 'name';

// DB connection settings
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

switch ($search_type) {
    case 'common_names':
        $column   = 'common_names';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
    case 'family':
        $column   = 'family';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
    case 'genus':
        $column   = 'genus';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
    case 'species':
        $column   = 'species';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
    case 'uses':
        $column   = 'uses';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
    case 'locations':
        $column   = 'locations';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
    case 'scientific_name':
        $column   = 'Scientific_name';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
}

$sql = "SELECT id, common_names, family, genus, species, uses, locations
        FROM plants
        WHERE $column $operator :q
        ORDER BY genus, species
        LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':q', $param, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>WAND³</title>
  <link rel="stylesheet" href="app.css" />
</head>
<body>
  <div class="app">
    <h1>WAND³ - results for <?php echo htmlspecialchars($q); ?></h1>

    <?php if ($q === ''): ?>
      <p>No query provided.</p>
    <?php elseif (count($rows) === 0): ?>
      <p>No results found for '<?php echo htmlspecialchars($q); ?>'</p>
    <?php else: ?>
      <ul class="results">
        <?php foreach ($rows as $row): ?>
          <div class = "results-box">
          <li>
            <a href="plant_results.php?id=<?php echo urlencode($row['id']); ?>"><strong><?php echo htmlspecialchars($row['genus'] . ' ' . $row['species']); ?></strong></a><br>
            Common Names: <?php echo htmlspecialchars($row['common_names']); ?><br>
            Family: <?php echo htmlspecialchars($row['family']); ?><br>
            Genus: <?php echo htmlspecialchars($row['genus']); ?><br>
            Species: <?php echo htmlspecialchars($row['species']); ?><br>
            Uses: <?php echo htmlspecialchars($row['uses']); ?><br>
            Locations: <?php echo htmlspecialchars($row['locations']); ?><br>
          </li>
          <br>
        </div>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</body>
</html>
