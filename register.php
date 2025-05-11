<?php
// register.php
session_start();

// Якщо вже авторизовані — перекидаємо в адмінку
if (!empty($_SESSION['auth'])) {
    header('Location: /admin.php');
    exit;
}

// --- Функція підключення до БД ---
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
    $email    = trim($_POST['email']    ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // 1) Перевіряємо заповненість
    if ($email === '' || $username === '' || $password === '') {
        $infoMessage = 'Будь ласка, заповніть всі поля!';
    }
    // 2) Перевіряємо довжину username/password
    elseif (strlen($username) < 8 || strlen($username) > 45
        || strlen($password) < 8 || strlen($password) > 45
    ) {
        $infoMessage = 'Логін та пароль повинні містити від 8 до 45 символів.';
    }
    // 3) Перевіряємо формат email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $infoMessage = 'Невірний формат email.';
    }
    else {
        $db = start_connect();
        // Екранізуємо вхідні дані
        $e = mysqli_real_escape_string($db, $email);
        $u = mysqli_real_escape_string($db, $username);
        $p = mysqli_real_escape_string($db, $password);

        // 4) Перевіряємо, чи вже є такий email або username
        $sql  = "SELECT 1 
                 FROM users 
                 WHERE email='$e' OR username='$u' 
                 LIMIT 1";
        $res  = mysqli_query($db, $sql);
        if (!$res) {
            $infoMessage = 'Помилка запиту: ' . mysqli_error($db);
        }
        elseif (mysqli_num_rows($res) > 0) {
            $infoMessage = 'Такий користувач уже існує! ';
            $infoMessage .= '<a href="/login.php">Перейти на сторінку входу</a>';
        }
        else {
            // 5) Вставляємо нового користувача
            $insert = "
                INSERT INTO users (username, email, password, created_at)
                VALUES ('$u', '$e', '$p', NOW())
            ";
            if (mysqli_query($db, $insert)) {
                mysqli_close($db);
                header('Location: /login.php');
                exit;
            } else {
                $infoMessage = 'Помилка запису: ' . mysqli_error($db);
            }
        }
        mysqli_close($db);
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <?php require_once 'sectionHead.php'; ?>
    <title>Реєстрація</title>
</head>
<body>
<div class="container">
    <?php require_once 'sectionNavbar.php'; ?>
    <br>

    <div class="card card-primary">
        <div class="card-header bg-success text-light">
            Register form
        </div>
        <div class="card-body">
            <form method="post" action="/register.php">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text"
                           name="username"
                           class="form-control"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           required>
                </div>
                <button type="submit" class="btn btn-primary">Зареєструватися</button>
            </form>

            <?php if ($infoMessage): ?>
                <hr>
                <div class="alert alert-danger">
                    <?= $infoMessage ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
