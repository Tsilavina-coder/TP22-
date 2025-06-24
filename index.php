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

// Requête pour récupérer la liste des départements
$sql = "SELECT dept_no, dept_name FROM departments";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des départements</title>
</head>
<body>
    <table border="1" cellpadding="8" cellspacing="0">
        <caption>Liste des départements</caption>
        <thead>
            <tr>
                <th>Numéro du département</th>
                <th>Nom du département</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                // Affichage des départements
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['dept_no']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['dept_name']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>Aucun département trouvé.</td></tr>";
            }
            // Fermeture de la connexion
            $conn->close();
            ?>
        </tbody>
    </table>
</body>
</html>
