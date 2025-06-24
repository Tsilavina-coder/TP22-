<?php
// Connexion à la base de données MySQL
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'employees';

// Création de la connexion
$conn = new mysqli($host, $user, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Échec de la connexion à la base de données : " . $conn->connect_error);
}

// Requête pour récupérer la liste des départements avec le nom du manager en cours
$sql = "
SELECT d.dept_no, d.dept_name, CONCAT(e.first_name, ' ', e.last_name) AS manager_name
FROM departments d
LEFT JOIN dept_manager dm ON d.dept_no = dm.dept_no
LEFT JOIN employees e ON dm.emp_no = e.emp_no
WHERE dm.to_date = (
    SELECT MAX(to_date)
    FROM dept_manager
    WHERE dept_no = d.dept_no
)
ORDER BY d.dept_no
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../TP22-/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <script src="../TP22-/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
    <title>Document</title>
</head>
<body>
    <table border="1" cellpadding="8" cellspacing="0">
        <caption>Liste des départements avec leur manager en cours</caption>
        <thead>
            <tr>
                <th>Numéro du département</th>
                <th>Nom du département</th>
                <th>Nom du manager</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                // Affichage des départements avec managers
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    $dept_no = htmlspecialchars($row['dept_no']);
                    $dept_name = htmlspecialchars($row['dept_name']);
                    $manager_name = htmlspecialchars($row['manager_name'] ?? 'Non défini');
                    echo "<td><a href='List_employees.php?dept_no={$dept_no}'>{$dept_no}</a></td>";
                    echo "<td><a href='List_employees.php?dept_no={$dept_no}'>{$dept_name}</a></td>";
                    echo "<td>{$manager_name}</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>Aucun département trouvé.</td></tr>";
            }
            // Fermeture de la connexion
            $conn->close();
            ?>
        </tbody>
    </table>
</body>
</html>
