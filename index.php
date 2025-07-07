<?php
require_once 'inc/function/db_functions.php';

$conn = get_db_connection();
$sql = "SELECT dept_no, dept_name, manager_name, employee_count FROM join_table_dept_manag_emp ORDER BY dept_name";
$result = $conn->query($sql);
$departments = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
    $result->free();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../TP22-/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="../TP22-/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
    <title>Liste des départements avec managers</title>
</head>
<body>
    <?php include 'inc/navbar.php'; ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <caption>Liste des départements avec leur manager en cours</caption>
        <thead>
            <tr>
                <th>Nom du département</th>
                <th>Nom du manager</th>
                <th>Nombre d'employés</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $new_manager_dept_no = $_GET['new_manager_dept_no'] ?? null;
            if (!empty($departments)) {
                foreach ($departments as $row) {
                    $dept_no = htmlspecialchars($row['dept_no']);
                    $dept_name = htmlspecialchars($row['dept_name']);
                    $manager_name = htmlspecialchars($row['manager_name'] ?? 'Non défini');
                    $highlight = ($new_manager_dept_no && $new_manager_dept_no === $row['dept_no']) ? ' style="background-color: #d4edda;"' : '';
                    ?>
                    <tr<?= $highlight ?>>
                        <td><a href="List_employees.php?dept_no=<?= $dept_no ?>"><?= $dept_name ?></a></td>
                        <td><?= $manager_name ?></td>
                        <td><?= $row['employee_count'] ?></td>
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
