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
    case 'uniprotid':
        $column   = 'uniprotid';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
    case 'pdbid':
        $column   = 'pdbid';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
}
$sql = "SELECT id, name, pdbid, uniprotid
        FROM targets
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
      <div class = home-help-box>
      <a href = "https://www.carlingblacklabel.co.za/">Help</a>
      <a href = main.html>Home</a>
    </div>
  <div class="app">
    <h1>WAND³ - results for <?php echo htmlspecialchars($q); ?></h1>

    <?php if ($q === ''): ?>
      <p>No query provided.</p>
    <?php elseif (count($rows) === 0): ?>
      <p>No results found for '<?php echo htmlspecialchars($q); ?>'</p>
    <?php else: ?>
      <ul class="results-box">
        <?php foreach ($rows as $row): ?>
          <li>
            <a href="target_results.php?id=<?php echo urlencode($row['id']); ?>"><strong><?php echo htmlspecialchars($row['name']); ?></strong></a><br>
            Protein Data Bank ID: <?php echo htmlspecialchars($row['pdbid']); ?><br>
            Uniprot ID: <?php echo htmlspecialchars($row['uniprotid']); ?><br>
          </li>
          <br>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</body>
</html>
