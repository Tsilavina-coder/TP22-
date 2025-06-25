<?php
require_once 'inc/function/db_functions.php';

if (!isset($_GET['emp_no']) || empty($_GET['emp_no'])) {
    die("Numéro d'employé non spécifié.");
}

$emp_no = $_GET['emp_no'];

$conn = get_db_connection();

$sql = "SELECT emp_no, first_name, last_name, birth_date, gender, hire_date FROM employees WHERE emp_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $emp_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $employee = $result->fetch_assoc();
} else {
    die("Employé non trouvé.");
}

$stmt->close();
$conn->close();
?>

<?php
$salaries = get_salary_history($emp_no);
$titles = get_title_history($emp_no);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de l'employé <?= htmlspecialchars($emp_no) ?></title>
</head>
<body>
    <h1>Fiche de l'employé <?= htmlspecialchars($emp_no) ?></h1>
    <ul>
        <li><strong>Numéro employé :</strong> <?= htmlspecialchars($employee['emp_no']) ?></li>
        <li><strong>Prénom :</strong> <?= htmlspecialchars($employee['first_name']) ?></li>
        <li><strong>Nom :</strong> <?= htmlspecialchars($employee['last_name']) ?></li>
        <li><strong>Date de naissance :</strong> <?= htmlspecialchars($employee['birth_date']) ?></li>
        <li><strong>Genre :</strong> <?= htmlspecialchars($employee['gender']) ?></li>
        <li><strong>Date d'embauche :</strong> <?= htmlspecialchars($employee['hire_date']) ?></li>
    </ul>

    <h2>Historique des salaires</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Salaire</th>
                <th>Date début</th>
                <th>Date fin</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($salaries)) : ?>
                <?php foreach ($salaries as $salary) : ?>
                    <tr>
                        <td><?= htmlspecialchars($salary['salary']) ?></td>
                        <td><?= htmlspecialchars($salary['from_date']) ?></td>
                        <td><?= htmlspecialchars($salary['to_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="3">Aucun historique de salaire trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Historique des emplois occupés</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Intitulé du poste</th>
                <th>Date début</th>
                <th>Date fin</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($titles)) : ?>
                <?php foreach ($titles as $title) : ?>
                    <tr>
                        <td><?= htmlspecialchars($title['title']) ?></td>
                        <td><?= htmlspecialchars($title['from_date']) ?></td>
                        <td><?= htmlspecialchars($title['to_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="3">Aucun historique d'emploi trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p><a href="List_employees.php?dept_no=<?php echo urlencode($_GET['dept_no'] ?? ''); ?>">Retour à la liste des employés</a></p>
    <p><a href="index.php">Retour à la liste des départements</a></p>
</body>
</html>
