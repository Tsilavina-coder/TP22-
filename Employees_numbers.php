<?php
require_once 'inc/function/db_functions.php';

$conn = get_db_connection();

$sql = "
SELECT 
    t.title,
    SUM(CASE WHEN e.gender = 'M' THEN 1 ELSE 0 END) AS male_count,
    SUM(CASE WHEN e.gender = 'F' THEN 1 ELSE 0 END) AS female_count,
    AVG(s.salary) AS average_salary
FROM employees e
JOIN titles t ON e.emp_no = t.emp_no
JOIN salaries s ON e.emp_no = s.emp_no
GROUP BY t.title
ORDER BY t.title;
";

$result = $conn->query($sql);

$rows = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $result->free();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nombre d'employés et salaire moyen par emploi</title>
    <link rel="stylesheet" href="../TP22-/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="../TP22-/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <?php include 'inc/navbar.php'; ?>
    <div class="container mt-4">
        <h1>Nombre d'employés (hommes et femmes) et salaire moyen par emploi</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Emploi</th>
                    <th>Nombre d'hommes</th>
                    <th>Nombre de femmes</th>
                    <th>Salaire moyen</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rows)): ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['male_count']) ?></td>
                            <td><?= htmlspecialchars($row['female_count']) ?></td>
                            <td><?= number_format($row['average_salary'], 2, ',', ' ') ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">Aucun résultat trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p><a href="index.php" class="btn btn-primary">Retour à la liste des départements</a></p>
    </div>
</body>
</html>
