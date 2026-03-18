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

// Build WHERE clause
switch ($search_type) {
    case 'smiles':
        $column   = 'smiles';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
    case 'inchi':
        $column   = 'inchi';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
    case 'inchikey':
        $column   = 'inchikey';
        $operator = '=';
        $param    = $q;
        break;
    case 'pubchem_cid':
        // if you want to search PubChem CID, make sure this column name is correct
        $column   = 'pubchem_cid';
        $operator = '=';
        $param    = $q;
        break;
    case 'name':
    default:
        $column   = 'name';
        $operator = 'LIKE';
        $param    = '%' . $q . '%';
        break;
}

$sql = "SELECT id, name, inchikey, smiles, inchi, names, pubmedids
        FROM natural_products
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
          <div class = "results-box">
          <li>
            
            <a href="np_results.php?id=<?php echo urlencode($row['id']); ?>"><strong><?php echo htmlspecialchars($row['name']); ?></strong></a><br>
            InChIKey: <?php echo htmlspecialchars($row['inchikey']); ?><br>
            SMILES: <?php echo htmlspecialchars($row['smiles']); ?><br>
            InChI: <?php echo htmlspecialchars($row['inchi']); ?><br>
            Names: <?php echo htmlspecialchars($row['names']); ?><br>
            PubMed IDs: <?php echo htmlspecialchars($row['pubmedids']); ?><br>
        
          </li>
          <br>
          </div>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</body>
</html>
