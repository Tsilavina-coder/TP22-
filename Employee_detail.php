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
    <p><a href="List_employees.php?dept_no=<?php echo urlencode($_GET['dept_no'] ?? ''); ?>">Retour à la liste des employés</a></p>
    <p><a href="index.php">Retour à la liste des départements</a></p>
</body>
</html>
