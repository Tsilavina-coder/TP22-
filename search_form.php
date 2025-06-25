<?php
require_once 'inc/function/db_functions.php';
require_once 'inc/function/search_employees.php';

$departments = [];
$conn = get_db_connection();
$sql = "SELECT dept_no, dept_name FROM departments ORDER BY dept_name";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
    $result->free();
}
$conn->close();

$search_results = [];
$dept_no = null;
$employee_name = null;
$age_min = null;
$age_max = null;
$page = 0;
$limit = 20;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dept_no = $_POST['dept_no'] ?? null;
    $employee_name = $_POST['employee_name'] ?? null;
    $age_min = $_POST['age_min'] ?? null;
    $age_max = $_POST['age_max'] ?? null;
    $page = isset($_POST['page']) ? max(0, (int)$_POST['page']) : 0;

    $offset = $page * $limit;
    $search_results = search_employees($dept_no, $employee_name, $age_min, $age_max, $offset, $limit);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche d'employés</title>
    <link rel="stylesheet" href="../TP22-/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Recherche d'employés</h1>
        <form method="post" action="search_form.php" class="mb-4">
            <div class="mb-3">
                <label for="dept_no" class="form-label">Département</label>
                <select name="dept_no" id="dept_no" class="form-select">
                    <option value="">-- Tous les départements --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= htmlspecialchars($dept['dept_no']) ?>" <?= ($dept_no === $dept['dept_no']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept['dept_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="employee_name" class="form-label">Nom de l'employé</label>
                <input type="text" name="employee_name" id="employee_name" class="form-control" value="<?= htmlspecialchars($employee_name ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="age_min" class="form-label">Âge minimum</label>
                <input type="number" name="age_min" id="age_min" class="form-control" min="0" value="<?= htmlspecialchars($age_min ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="age_max" class="form-label">Âge maximum</label>
                <input type="number" name="age_max" id="age_max" class="form-control" min="0" value="<?= htmlspecialchars($age_max ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <h2>Résultats de la recherche</h2>
            <?php if (!empty($search_results)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Numéro employé</th>
                            <th>Prénom</th>
                            <th>Nom</th>
                            <th>Date de naissance</th>
                            <th>Genre</th>
                            <th>Date d'embauche</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $employee): ?>
                            <tr>
                                <td><a href="Employee_detail.php?emp_no=<?= htmlspecialchars($employee['emp_no']) ?>"><?= htmlspecialchars($employee['emp_no']) ?></a></td>
                                <td><?= htmlspecialchars($employee['first_name']) ?></td>
                                <td><?= htmlspecialchars($employee['last_name']) ?></td>
                                <td><?= htmlspecialchars($employee['birth_date']) ?></td>
                                <td><?= htmlspecialchars($employee['gender']) ?></td>
                                <td><?= htmlspecialchars($employee['hire_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
        </table>
            <?php else: ?>
                <p>Aucun employé ne correspond aux critères de recherche.</p>
            <?php endif; ?>
            <?php if (!empty($search_results)): ?>
                <form method="post" action="search_form.php" style="text-align: center; margin-top: 20px;">
                    <input type="hidden" name="dept_no" value="<?= htmlspecialchars($dept_no ?? '') ?>">
                    <input type="hidden" name="employee_name" value="<?= htmlspecialchars($employee_name ?? '') ?>">
                    <input type="hidden" name="age_min" value="<?= htmlspecialchars($age_min ?? '') ?>">
                    <input type="hidden" name="age_max" value="<?= htmlspecialchars($age_max ?? '') ?>">
                    <input type="hidden" name="page" value="<?= $page + 1 ?>">
                    <button type="submit" class="btn btn-secondary">Suivant</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <script src="../TP22-/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
