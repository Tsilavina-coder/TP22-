 <?php
require_once 'inc/function/db_functions.php';

if (!isset($_GET['emp_no']) || empty($_GET['emp_no'])) {
    die("Numéro d'employé non spécifié.");
}

$emp_no = $_GET['emp_no'];

$conn = get_db_connection();

$message = '';
$error = '';

// Récupérer le département actuel actif avec date de début
$sql_current_dept = "SELECT d.dept_no, d.dept_name, de.from_date
                     FROM departments d
                     INNER JOIN dept_emp de ON d.dept_no = de.dept_no
                     WHERE de.emp_no = ? AND de.to_date = '9999-01-01'
                     LIMIT 1";
$stmt_current_dept = $conn->prepare($sql_current_dept);
$stmt_current_dept->bind_param("i", $emp_no);
$stmt_current_dept->execute();
$result_current_dept = $stmt_current_dept->get_result();

$current_department = null;
if ($result_current_dept && $result_current_dept->num_rows === 1) {
    $current_department = $result_current_dept->fetch_assoc();
}
$stmt_current_dept->close();

// Récupérer la liste des départements en excluant le département actuel
$sql_departments = "SELECT dept_no, dept_name FROM departments";
if ($current_department) {
    $sql_departments .= " WHERE dept_no != ?";
}
$stmt_departments = $conn->prepare($sql_departments);
if ($current_department) {
    $stmt_departments->bind_param("s", $current_department['dept_no']);
}
$stmt_departments->execute();
$result_departments = $stmt_departments->get_result();

$departments = [];
if ($result_departments) {
    while ($row = $result_departments->fetch_assoc()) {
        $departments[] = $row;
    }
}
$stmt_departments->close();

// Récupérer le manager actuel avec date de début
$sql_current_manager = "SELECT e.emp_no, e.first_name, e.last_name, dm.from_date
                        FROM employees e
                        INNER JOIN dept_manager dm ON e.emp_no = dm.emp_no
                        WHERE dm.dept_no = ? AND dm.to_date = '9999-01-01'
                        ORDER BY dm.from_date DESC";
$current_manager = null;
$managers = [];
if ($current_department) {
    $stmt_current_manager = $conn->prepare($sql_current_manager);
    $stmt_current_manager->bind_param("s", $current_department['dept_no']);
    $stmt_current_manager->execute();
    $result_current_manager = $stmt_current_manager->get_result();
    if ($result_current_manager && $result_current_manager->num_rows > 0) {
        while ($row = $result_current_manager->fetch_assoc()) {
            $managers[] = $row;
        }
        $current_manager = $managers[0];
    }
    $stmt_current_manager->close();
}

// Traitement du formulaire de changement de département
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_department'])) {
    $new_dept_no = $_POST['dept_no'] ?? '';
    $from_date = $_POST['from_date'] ?? '';

    if (!$new_dept_no || !$from_date) {
        $error = "Veuillez remplir tous les champs.";
    } elseif ($current_department && $from_date < $current_department['from_date']) {
        $error = "La date de début du nouveau département ne peut pas être antérieure à la date de début de l'actuel (" . (new DateTime($current_department['from_date']))->format('d/m/Y') . ").";
    } else {
        // Mettre à jour la date de fin de l'ancienne affectation
        $stmt_update = $conn->prepare("UPDATE dept_emp SET to_date = ? WHERE emp_no = ? AND to_date = '9999-01-01'");
        $stmt_update->bind_param("si", $from_date, $emp_no);
        $stmt_update->execute();
        $stmt_update->close();

        // Insérer la nouvelle affectation
        $stmt_insert = $conn->prepare("INSERT INTO dept_emp (emp_no, dept_no, from_date, to_date) VALUES (?, ?, ?, '9999-01-01')");
        $stmt_insert->bind_param("iss", $emp_no, $new_dept_no, $from_date);
        if ($stmt_insert->execute()) {
            $message = "Département changé avec succès.";
            // Mettre à jour current_department pour affichage
            $stmt_insert->close();
            $stmt_new_dept = $conn->prepare("SELECT dept_no, dept_name, ? as from_date FROM departments WHERE dept_no = ?");
            $stmt_new_dept->bind_param("ss", $from_date, $new_dept_no);
            $stmt_new_dept->execute();
            $result_new_dept = $stmt_new_dept->get_result();
            if ($result_new_dept && $result_new_dept->num_rows === 1) {
                $current_department = $result_new_dept->fetch_assoc();
            }
            $stmt_new_dept->close();
        } else {
            $error = "Erreur lors de la mise à jour du département : " . $stmt_insert->error;
        }
    }
}

// Traitement du formulaire de devenir manager
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['become_manager'])) {
    $new_manager_from_date = $_POST['manager_from_date'] ?? '';

    if (!$new_manager_from_date) {
        $error = "Veuillez remplir la date de début du nouveau manager.";
    } elseif ($current_manager && $new_manager_from_date < $current_manager['from_date']) {
        $error = "La date de début du nouveau manager ne peut pas être antérieure à la date de début de l'actuel (" . (new DateTime($current_manager['from_date']))->format('d/m/Y') . ").";
    } else {
        // Mettre à jour la date de fin de l'ancien manager
        if ($current_manager) {
            $stmt_update_manager = $conn->prepare("UPDATE dept_manager SET to_date = ? WHERE emp_no = ? AND to_date = '9999-01-01'");
            $stmt_update_manager->bind_param("si", $new_manager_from_date, $current_manager['emp_no']);
            $stmt_update_manager->execute();
            $stmt_update_manager->close();
        }

        // Insérer le nouvel enregistrement de manager
        $stmt_insert_manager = $conn->prepare("INSERT INTO dept_manager (emp_no, dept_no, from_date, to_date) VALUES (?, ?, ?, '9999-01-01')");
        $stmt_insert_manager->bind_param("iss", $emp_no, $current_department['dept_no'], $new_manager_from_date);
        if ($stmt_insert_manager->execute()) {
            $stmt_insert_manager->close();
            // Redirection vers index.php avec surbrillance du département
            header("Location: index.php?new_manager_dept_no=" . urlencode($current_department['dept_no']));
            exit();
        } else {
            $error = "Erreur lors de la mise à jour du manager : " . $stmt_insert_manager->error;
        }
    }
}

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

// Requête pour récupérer l'emploi le plus long (en durée) de l'employé
$sql_longest_title = "
    SELECT title, from_date, to_date, DATEDIFF(COALESCE(to_date, CURDATE()), from_date) AS duration
    FROM titles
    WHERE emp_no = ?
    ORDER BY duration DESC
    LIMIT 1
";
$stmt_longest = $conn->prepare($sql_longest_title);
$stmt_longest->bind_param("i", $emp_no);
$stmt_longest->execute();
$result_longest = $stmt_longest->get_result();

$longest_title = null;
if ($result_longest && $result_longest->num_rows === 1) {
    $longest_title = $result_longest->fetch_assoc();
}

$stmt_longest->close();
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
    <link rel="stylesheet" href="../TP22-/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="../TP22-/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <h1>Fiche de l'employé <?= htmlspecialchars($emp_no) ?></h1>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <ul>
        <li><strong>Numéro employé :</strong> <?= htmlspecialchars($employee['emp_no']) ?></li>
        <li><strong>Prénom :</strong> <?= htmlspecialchars($employee['first_name']) ?></li>
        <li><strong>Nom :</strong> <?= htmlspecialchars($employee['last_name']) ?></li>
        <li><strong>Date de naissance :</strong> <?= (new DateTime($employee['birth_date']))->format('d/m/y') ?></li>
        <li><strong>Genre :</strong> <?= htmlspecialchars($employee['gender']) ?></li>
        <li><strong>Date d'embauche :</strong> <?= (new DateTime($employee['hire_date']))->format('d/m/y') ?></li>
    </ul>

    <h3>Département actuel</h3>
    <?php if ($current_department): ?>
        <p><strong><?= htmlspecialchars($current_department['dept_name']) ?></strong> (<?= htmlspecialchars($current_department['dept_no']) ?>) depuis le <?= (new DateTime($current_department['from_date']))->format('d/m/Y') ?></p>
    <?php else: ?>
        <p>Département actuel non défini.</p>
    <?php endif; ?>

    <h3>Changer de département</h3>
    <form method="post" action="Employee_detail.php?emp_no=<?= urlencode($emp_no) ?>">
        <?php if ($current_department): ?>
            <p><strong>Département actuel :</strong> <?= htmlspecialchars($current_department['dept_name']) ?> (<?= htmlspecialchars($current_department['dept_no']) ?>) depuis le <?= (new DateTime($current_department['from_date']))->format('d/m/Y') ?></p>
        <?php endif; ?>
        <div class="mb-3">
            <label for="dept_no" class="form-label">Nouveau département</label>
            <select name="dept_no" id="dept_no" class="form-select" required>
                <option value="">-- Sélectionnez un département --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= htmlspecialchars($dept['dept_no']) ?>"><?= htmlspecialchars($dept['dept_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="from_date" class="form-label">Date de début</label>
            <input type="date" name="from_date" id="from_date" class="form-control" required>
        </div>
        <button type="submit" name="change_department" class="btn btn-primary">Valider</button>
    </form>

    <h3>Devenir Manager</h3>
    <?php if ($current_manager): ?>
        <p>Manager actuel : <strong><?= htmlspecialchars($current_manager['first_name']) ?> <?= htmlspecialchars($current_manager['last_name']) ?></strong> depuis le <?= (new DateTime($current_manager['from_date']))->format('d/m/Y') ?></p>
    <?php else: ?>
        <p>Pas de manager actuel défini.</p>
    <?php endif; ?>

    <form method="post" action="Employee_detail.php?emp_no=<?= urlencode($emp_no) ?>">
        <div class="mb-3">
            <label for="manager_from_date" class="form-label">Date de début</label>
            <input type="date" name="manager_from_date" id="manager_from_date" class="form-control" required>
        </div>
        <button type="submit" name="become_manager" class="btn btn-primary">Valider</button>
    </form>

    <h3>Historique des managers</h3>
    <?php if (!empty($managers)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Date de début</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($managers as $manager): ?>
                    <tr<?php if (isset($new_manager_from_date) && $manager['emp_no'] == $emp_no && $manager['from_date'] == $new_manager_from_date) echo ' style="background-color: #d4edda;"'; ?>>
                        <td><?= htmlspecialchars($manager['first_name']) ?> <?= htmlspecialchars($manager['last_name']) ?></td>
                        <td><?= (new DateTime($manager['from_date']))->format('d/m/Y') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun manager trouvé pour ce département.</p>
    <?php endif; ?>

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
                        <td><?= (new DateTime($salary['from_date']))->format('d/m/y') ?></td>
                        <td><?= (new DateTime($salary['to_date']))->format('d/m/y') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="3">Aucun historique de salaire trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Emploi le plus long</h2>
    <?php if ($longest_title): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Intitulé du poste</th>
                    <th>Date début</th>
                    <th>Date fin</th>
                    <th>Durée (jours)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($longest_title['title']) ?></td>
                    <td><?= (new DateTime($longest_title['from_date']))->format('d/m/y') ?></td>
                    <td><?= (new DateTime($longest_title['to_date']))->format('d/m/y') ?></td>
                    <td><?= htmlspecialchars($longest_title['duration']) ?></td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun emploi trouvé.</p>
    <?php endif; ?>

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
                        <td><?= (new DateTime($title['from_date']))->format('d/m/y') ?></td>
                        <td><?= (new DateTime($title['to_date']))->format('d/m/y') ?></td>
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
