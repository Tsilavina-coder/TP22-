<?php
// Fonctions pour la gestion de la base de données employees

function get_db_connection() {
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'employees';

    $conn = new mysqli($host, $user, $password, $dbname);
    if ($conn->connect_error) {
        die("Échec de la connexion à la base de données : " . $conn->connect_error);
    }
    return $conn;
}

function get_departments_with_managers() {
    $conn = get_db_connection();

    $sql = "
    SELECT d.dept_no, d.dept_name, CONCAT(e.first_name, ' ', e.last_name) AS manager_name
    FROM departments d
    LEFT JOIN dept_manager dm ON d.dept_no = dm.dept_no
    LEFT JOIN employees e ON dm.emp_no = e.emp_no
    WHERE dm.to_date = (
        SELECT MAX(to_date)
        FROM dept_manager
        WHERE dept_no = d.dept_no
    )
    ORDER BY d.dept_no
    ";

    $result = $conn->query($sql);
    $departments = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
        $result->free();
    }
    $conn->close();
    return $departments;
}

function get_employees_by_department($dept_no) {
    $conn = get_db_connection();

    $sql = "
    SELECT e.emp_no, e.first_name, e.last_name, e.hire_date
    FROM employees e
    INNER JOIN dept_emp de ON e.emp_no = de.emp_no
    WHERE de.dept_no = ?
    ORDER BY e.emp_no
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $dept_no);
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
