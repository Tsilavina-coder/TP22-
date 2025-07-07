CREATE OR REPLACE VIEW join_table_dept_manag_emp AS
SELECT 
    d.dept_no,
    d.dept_name,
    CONCAT(e.first_name, ' ', e.last_name) AS manager_name,
    COUNT(de.emp_no) AS employee_count
FROM departments d
LEFT JOIN dept_manager dm ON d.dept_no = dm.dept_no
LEFT JOIN employees e ON dm.emp_no = e.emp_no
LEFT JOIN dept_emp de ON d.dept_no = de.dept_no
GROUP BY d.dept_no, d.dept_name, manager_name;
