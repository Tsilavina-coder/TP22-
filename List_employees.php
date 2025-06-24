<?php
require_once 'inc/function/db_functions.php';

if (!isset($_GET['dept_no']) || empty($_GET['dept_no'])) {
    die("Numéro de département non spécifié.");
}

$dept_no = $_GET['dept_no'];

$employees = get_employees_by_department($dept_no);
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
            if (!empty($employees)) {
                foreach ($employees as $row) {
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['emp_no']) ?></td>
                        <td><?= htmlspecialchars($row['first_name']) ?></td>
                        <td><?= htmlspecialchars($row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['hire_date']) ?></td>
                    </tr>
                    <?php
                }
            } else {
                echo "<tr><td colspan='4'>Aucun employé trouvé pour ce département.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <p><a href="index.php">Retour à la liste des départements</a></p>
</body>
</html>
