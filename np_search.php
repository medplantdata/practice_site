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
      <div class = home-help-box>
      <a href = "https://www.carlingblacklabel.co.za/">Help</a>
      <a href = main.html>Home</a>
    </div>
  <div class="app">
    <h1>WAND³ - results for <?php echo htmlspecialchars($q); ?></h1>

    <?php if ($q === ''): ?>
      <p>No query provided.</p>
    <?php elseif (count($rows) === 0): 
      switch ($search_type) {
    case 'smiles':
        $URL = "https://pubchem.ncbi.nlm.nih.gov/compound/" . urlencode($q) . "#section=Canonical-SMILES&embed=true";
        break;
    case 'inchi':
        $URL = "https://pubchem.ncbi.nlm.nih.gov/compound/" . urlencode($q) . "#section=InChI&embed=true";
        break;
    case 'inchikey':
        $URL = "https://pubchem.ncbi.nlm.nih.gov/compound/" . urlencode($q) . "#section=InChIKey&embed=true";
        break;
    case 'pubchem_cid':
        $URL = "https://pubchem.ncbi.nlm.nih.gov/compound/" . urlencode($q) . "#section=Top&embed=true";
    case 'name':
    default:
        $URL = "https://pubchem.ncbi.nlm.nih.gov/compound/" . urlencode($q) . "#section=IUPAC-Name&embed=true";
        break;
}
      ?>
      <h2>No results in our database found for '<?php echo htmlspecialchars($q); ?>' but PubChem may have an entry for it.</h2>
      <iframe class="pubchem-widget" src="<?php echo urlencode($URL); ?>#section=IUPAC-Name&embed=true" style="width: 99.5%; height: 600px; border-color: blueviolet"></iframe>
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
