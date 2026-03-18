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
  <script src="https://unpkg.com/@rdkit/rdkit/dist/RDKit_minimal.js"></script>
  <script>
      window.RDKitReady = window.initRDKitModule().then(function (RDKit) {
      window.RDKit = RDKit;
    });
  </script>  
</head>
<body>
    <div class = "app">

    <h1>WAND³ - results for <?php echo htmlspecialchars($np['name']); ?></h1>

    <div class="results-box" style = "align-self: center;">
    <h2>Natural Product Details:</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($np['name']); ?></p>
    <p><strong>InChIKey:</strong> <?php echo htmlspecialchars($np['inchikey']); ?></p>
    <p><strong>SMILES:</strong> <?php echo htmlspecialchars($np['SMILES']); ?></p>
    <p><strong>InChI:</strong> <?php echo htmlspecialchars($np['inchi']); ?></p>
    <p><strong>Other Names:</strong> <?php echo htmlspecialchars($np['names']); ?></p>
    <p><strong>PubMed IDs:</strong> <?php echo htmlspecialchars($np['pubmedids']); ?></p>
    </div>
    <div class="results-row">
    <div class="results-box-np">
        <h2>Associated Diseases:</h2>
    <ul>
        <?php foreach ($diseases as $disease): ?>
            <a href="disease_results.php?id=<?php echo urlencode($disease['id']); ?>"><strong><?php echo htmlspecialchars($disease['name']); ?></strong></a><br>
        <?php endforeach; ?>
    </ul>
    

    
    <h2>Associated Targets:</h2>
    <ul>
        <?php foreach ($targets as $target): ?>
            <a href="target_results.php?id=<?php echo urlencode($target['id']); ?>"><strong><?php echo htmlspecialchars($target['name']); ?></strong></a><br>
        <?php endforeach; ?>
    </ul>
    
    <h2>Associated Plants:</h2>
    <ul>
        <?php foreach ($plants as $plant): ?>
            <a href="plant_results.php?id=<?php echo urlencode($plant['id']); ?>"><strong><?php echo htmlspecialchars($plant['genus'] . ' ' . $plant['species']); ?></strong></a><br>
        <?php endforeach; ?>
    </ul>
    </div>
    <div class = "np-chem-box">
        <h2>Chemical structure:</h2>    
        <canvas id="canvas" width="600" height="600"></canvas>
        <script>
            window.RDKitReady.then(function () {
            var smiles = "<?php echo htmlspecialchars($np['SMILES']); ?>"; 
            var mol= RDKit.get_mol(smiles);
               const opts = {bondLineWidth: 1.5,
                            padding: 0.1,                 
                            includeAtomNumbers: false,
                            fixedBondLength: 25,          
                            kekulize: true,               
                            canonical: true,
                            addChiralHs: true,
                            wedgeBonds: true,
                            clearBackground: true
                            };

            var canvas = document.getElementById("canvas");
            mol.draw_to_canvas(canvas, -1, -1); 
        });   
        </script>
    </div>
    <div class = "np-chem-box">
        <h2>Descriptors:</h2>
        <pre id="output"></pre>
        <script>
            window.RDKitReady.then(function () {
            var smiles = "<?php echo htmlspecialchars($np['SMILES']); ?>";
            var mol= RDKit.get_mol(smiles);
            
            var descriptors = JSON.parse(mol.get_descriptors());
            var descriptorsSorted = Object.keys(descriptors).sort(function(a,b) {return a.localeCompare(b, undefined, {sensitivity: 'base'});}).map(function(descriptor) {return [descriptor, descriptors[descriptor]]}) 
                      
            var lines = descriptorsSorted.map(([name, value]) => `${name}: ${value}`).join('\n');
            document.getElementById("output").textContent = lines;
            });

        </script>
    </div>
        </div>
</div>
</body>
</html>