<?php

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$search_type = isset($_GET['search-type']) ? $_GET['search-type'] : 'name';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
  <title>WAND³ np search</title>
  <link rel="stylesheet" href="app.css" />
</head>
<body>
    <div class="app">
        <h1 class="main_heading">WAND<sup>3</sup></h1>
        <h2>Search natural products</h2>    

        <form method="get" action="np_search.php" class="search-container">
            <input
                id="search-input"
                type="text"
                placeholder="Search for natural products..."
                class="search-input"
            />
            <div class = "radio-buttons">
                <label>
                    <input type = 'radio' name = 'search-type' value = 'name' id = 'exact-search' unchecked>
                    Name
                </label>
                <label>
                    <input type = 'radio' name = 'search-type' value = 'smiles' id = 'exact-search' unchecked>
                    SMILES
                </label>
                <label>
                    <input type = 'radio' name = 'search-type' value = 'InChI' id = 'exact-search' unchecked>
                    InChI
                </label>
                <label>
                    <input type = 'radio' name = 'search-type' value = 'InChIKey' id = 'exact-search' unchecked>
                    InChIKey
                </label>
                <label>
                    <input type = 'radio' name = 'search-type' value = 'PubChem CID' id = 'exact-search' unchecked>
                    PubChem CID
                </label>
            </div>
            <div class = "button-one">
            <button class="search-buttonINDIVIDUAL" id="btn-natural-products">Search Natural Products</button>
            </div> 
        </form>    
    </div>
    <?php
// If no query yet, just show the form and stop
if ($q === '') {
    echo "</body></html>";
    exit;
}

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
    echo "<p>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</body></html>";
    exit;
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


$sql = "SELECT id, name, inchikey, pubmed_ids
        FROM natural_products
        WHERE $column $operator :q
        ORDER BY name
        LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':q', $param, PDO::PARAM_STR);
$stmt->execute();

$rows = $stmt->fetchAll();

if (count($rows) > 0) {
    echo "<h3>Search results:</h3>";
    echo "<ul>";
    foreach ($rows as $row) {
        echo "<li><strong>" . htmlspecialchars($row['name']) . "</strong><br>";
        echo "InChIKey: " . htmlspecialchars($row['inchikey']) . "<br>";
        echo "PubMed IDs: " . htmlspecialchars($row['pubmed_ids']) . "</li><br>";
    }
    echo "</ul>";
} else {
    echo "<p>No results found for '" . htmlspecialchars($q) . "'</p>";
}
?>
</body>
</html>