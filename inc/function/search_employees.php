<?php
require_once 'db_functions.php'; // Ajout de l'inclusion du fichier contenant get_db_connection

// Fonction de recherche d'employés selon plusieurs critères : département, nom, âge min et max
function search_employees($dept_no = null, $employee_name = null, $age_min = null, $age_max = null, $offset = 0, $limit = 20) {
    $conn = get_db_connection();

    $params = [];
    $types = '';
    $conditions = [];

    // Filtre département
    if (!empty($dept_no)) {
        $conditions[] = 'de.dept_no = ?';
        $params[] = $dept_no;
        $types .= 's';
    }

    // Filtre nom employé (prénom ou nom de famille)
    if (!empty($employee_name)) {
        $conditions[] = '(e.first_name LIKE ? OR e.last_name LIKE ?)';
        $name_param = '%' . $employee_name . '%';
        $params[] = $name_param;
        $params[] = $name_param;
        $types .= 'ss';
    }

    // Filtre âge minimum
    if (!empty($age_min)) {
        $conditions[] = 'TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) >= ?';
        $params[] = $age_min;
        $types .= 'i';
    }

    // Filtre âge maximum
    if (!empty($age_max)) {
        $conditions[] = 'TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) <= ?';
        $params[] = $age_max;
        $types .= 'i';
    }

    $sql = "
    SELECT DISTINCT e.emp_no, e.first_name, e.last_name, e.birth_date, e.gender, e.hire_date
    FROM employees e
    LEFT JOIN dept_emp de ON e.emp_no = de.emp_no
    ";

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY e.emp_no LIMIT ?, ?";

    $params[] = (int)$offset;
    $params[] = (int)$limit;
    $types .= 'ii';

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Erreur de préparation de la requête : ' . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $employees = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        $result->free();
    }

    $stmt->close();
    $conn->close();

    return $employees;
}
?>
