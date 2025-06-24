<?php
// Récupération du numéro de département depuis le paramètre GET
if (!isset($_GET['dept_no']) || empty($_GET['dept_no'])) {
    die("Numéro de département non spécifié.");
}

$dept_no = $_GET['dept_no'];

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

// Requête pour récupérer les employés du département donné
$sql = "
SELECT e.emp_no, e.first_name, e.last_name, e.hire_date
FROM employees e
INNER JOIN dept_emp de ON e.emp_no = de.emp_no
WHERE de.dept_no = ?
ORDER BY e.emp_no
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $dept_no);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des employés du département <?php echo htmlspecialchars($dept_no); ?></title>
</head>
<body>
    <h1>Liste des employés du département <?php echo htmlspecialchars($dept_no); ?></h1>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Numéro employé</th>
                <th>Prénom</th>
                <th>Nom</th>
                <th>Date d'embauche</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['emp_no']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['hire_date']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>Aucun employé trouvé pour ce département.</td></tr>";
            }
            $stmt->close();
            $conn->close();
            ?>
        </tbody>
    </table>
    <p><a href="index.php">Retour à la liste des départements</a></p>
</body>
</html>
