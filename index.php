
<?php
$dbHost = getenv('DB_HOST') ?: 'mysql';
$dbName = getenv('DB_NAME') ?: 'employee_db';
$dbUser = getenv('DB_USER') ?: 'appuser';
$dbPassword = getenv('DB_PASSWORD') ?: '';

$message = '';
$employees = [];

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPassword,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            address VARCHAR(255) NOT NULL
        )
    ");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name !== '' && $address !== '') {
            $stmt = $pdo->prepare(
                "INSERT INTO employees (name, address) VALUES (?, ?)"
            );
            $stmt->execute([$name, $address]);
            $message = 'Employee saved successfully!';
        } else {
            $message = 'Please enter both name and address.';
        }
    }

    $employees = $pdo->query(
        "SELECT id, name, address FROM employees ORDER BY id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    http_response_code(503);
    $message = 'Database is not ready or connection failed. Please try again.';
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f4f6f8;
        }
        h1, h2 { color: #243b53; }
        form, .records {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        input, button {
            box-sizing: border-box;
            width: 100%;
            padding: 12px;
            margin: 8px 0;
        }
        button {
            background: #1769aa;
            color: white;
            border: 0;
            cursor: pointer;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .message { margin: 12px 0; }
    </style>
</head>
<body>
    <h1>Employee Management System</h1>
    <p>Register employees and view saved records.</p>

    <form method="POST">
        <h2>Add Employee</h2>

        <label for="name">Employee Name</label>
        <input id="name" name="name" maxlength="100"
               required placeholder="Enter employee name">

        <label for="address">Address</label>
        <input id="address" name="address" maxlength="255"
               required placeholder="Enter employee address">

        <button type="submit">Save Employee</button>

        <p class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    </form>

    <section class="records">
        <h2>Saved Employees</h2>

        <?php if (count($employees) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?= (int) $employee['id'] ?></td>
                            <td><?= htmlspecialchars($employee['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($employee['address'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No employee records available yet.</p>
        <?php endif; ?>
    </section>
</body>
</html>
