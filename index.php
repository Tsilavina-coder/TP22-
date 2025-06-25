<?php
require_once 'inc/function/db_functions.php';

$departments = get_departments_with_managers();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../TP22-/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <script src="../TP22-/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
    <title>Liste des départements avec managers</title>
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
            if (!empty($departments)) {
                foreach ($departments as $row) {
                    $dept_no = htmlspecialchars($row['dept_no']);
                    $dept_name = htmlspecialchars($row['dept_name']);
                    $manager_name = htmlspecialchars($row['manager_name'] ?? 'Non défini');
                    ?>
                    <tr>
                        <td><a href="List_employees.php?dept_no=<?= $dept_no ?>"><?= $dept_no ?></a></td>
                        <td><a href="List_employees.php?dept_no=<?= $dept_no ?>"><?= $dept_name ?></a></td>
                        <td><?= $manager_name ?></td>
                    </tr>
                    <?php
                }
            } else {
                echo "<tr><td colspan='3'>Aucun département trouvé.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <div style="text-align: center; margin-top: 20px;">
        <a href="search_form.php" class="btn btn-primary">Recherche avancée d'employés</a>
    </div>
</body>
</html>
