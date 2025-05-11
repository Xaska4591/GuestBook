<?php
session_start();

// Якщо вже авторизовані — перекидаємо в адмінку
if (!empty($_SESSION['auth'])) {
    header('Location: /admin.php');
    exit;
}

function start_connect() {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'guestbook';
    $conn = mysqli_connect($host, $user, $pass, $db);
    if (!$conn) {
        die('DB Connection Error: ' . mysqli_connect_error());
    }
    return $conn;
}

// Повідомлення для користувача
$infoMessage = '';

// Обробка форми
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input    = trim($_POST['email_name'] ?? '');
    $password = trim($_POST['password']   ?? '');

    if ($input === '' || $password === '') {
        $infoMessage = 'Будь ласка, заповніть усі поля!';
    } else {
        $db = start_connect();
        $i  = mysqli_real_escape_string($db, $input);
        $p  = mysqli_real_escape_string($db, $password);

        // Визначаємо, чи це email, чи username
        if (filter_var($i, FILTER_VALIDATE_EMAIL)) {
            $where = "email='$i'";
        } else {
            $where = "username='$i'";
        }

        $sql = "SELECT user_id, email, username, password
                FROM users
                WHERE $where
                LIMIT 1";
        $res = mysqli_query($db, $sql);

        if ($res && mysqli_num_rows($res) === 1) {
            $row = mysqli_fetch_assoc($res);
            if ($row['password'] === $p) {
                // Успішна авторизація
                $_SESSION['auth']  = true;
                $_SESSION['email'] = $row['email'];
                mysqli_close($db);
                header('Location: /admin.php');
                exit;
            } else {
                $infoMessage = 'Невірний пароль.';
            }
        } else {
            $infoMessage = 'Користувача не знайдено. ';
            $infoMessage .= '<a href="/register.php">Зареєструватися</a>';
        }
        mysqli_close($db);
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <?php require_once 'sectionHead.php'; ?>
    <title>Login</title>
</head>
<body>
<div class="container">
    <?php require_once 'sectionNavbar.php'; ?>
    <br>

    <div class="card card-primary">
        <div class="card-header bg-primary text-light">
            Login form
        </div>
        <div class="card-body">
            <form method="post" action="/login.php">
                <div class="mb-3">
                    <label class="form-label">Email or Username</label>
                    <input type="text"
                           name="email_name"
                           class="form-control"
                           value="<?= htmlspecialchars($_POST['email_name'] ?? '') ?>"
                           required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <?php if ($infoMessage): ?>
                <hr>
                <div class="alert alert-danger"><?= $infoMessage ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
