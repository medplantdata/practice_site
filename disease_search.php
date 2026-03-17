<?php
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

// Build WHERE clause
switch ($search_type) {
    case 'name':
        $column   = 'name';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
    case 'icd11':
        $column   = 'icd11';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
    case 'category':
        $column   = 'category';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
}
$sql = "SELECT id, name, icd11, category
        FROM diseases
        WHERE $column $operator :q
        ORDER BY name
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
          <li>
            <a href="disease_results.php?id=<?php echo urlencode($row['id']); ?>"><strong><?php echo htmlspecialchars($row['name']); ?></strong></a><br>
            ICD-11 Code: <?php echo htmlspecialchars($row['icd11']); ?><br>
            Category: <?php echo htmlspecialchars($row['category']); ?><br>
          </li>
          <br>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</body>
</html>
