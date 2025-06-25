<?php
require_once 'inc/function/search_employees.php';

if (!isset($_GET['dept_no']) || empty($_GET['dept_no'])) {
    die("Numéro de département non spécifié.");
}

$dept_no = $_GET['dept_no'];
$page = isset($_GET['page']) ? max(0, (int)$_GET['page']) : 0;
$limit = 20;
$offset = $page * $limit;

$employees = search_employees($dept_no, null, null, null, $offset, $limit);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des employés du département <?= htmlspecialchars($dept_no) ?></title>
    <link rel="stylesheet" href="../TP22-/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="../TP22-/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <h1>Liste des employés du département <?= htmlspecialchars($dept_no) ?></h1>
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
            <?php if (!empty($employees)) : ?>
                <?php foreach ($employees as $row) : ?>
                    <tr>
                        <td><a href="Employee_detail.php?emp_no=<?= htmlspecialchars($row['emp_no']) ?>"><?= htmlspecialchars($row['emp_no']) ?></a></td>
                        <td><?= htmlspecialchars($row['first_name']) ?></td>
                        <td><?= htmlspecialchars($row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['hire_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="4">Aucun employé trouvé dans ce département.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if (!empty($employees)) : ?>
        <div style="text-align: center; margin-top: 20px;">
            <?php if ($page > 0): ?>
                <a href="List_employees.php?dept_no=<?= urlencode($dept_no) ?>&page=<?= $page - 1 ?>" class="btn btn-secondary">Précédent</a>
            <?php endif; ?>
            <a href="List_employees.php?dept_no=<?= urlencode($dept_no) ?>&page=<?= $page + 1 ?>" class="btn btn-secondary">Suivant</a>
        </div>
    <?php endif; ?>
    <p><a href="index.php">Retour à la liste des départements</a></p>
</body>
</html>
