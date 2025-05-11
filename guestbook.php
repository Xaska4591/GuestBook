<?php
session_start();

// --- Функції для роботи з БД ---
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

function getUserByEmail(string $email):array {
    $db  = start_connect();
    $e   = mysqli_real_escape_string($db, $email);
    $sql = "SELECT user_id, username, email
            FROM users
            WHERE email='$e'
            LIMIT 1";
    $res  = mysqli_query($db, $sql);
    $user = mysqli_fetch_assoc($res) ?: null;
    mysqli_close($db);
    return $user;
}

function getAllFeedbacks(): array {
    $db  = start_connect();
    $sql = "
        SELECT f.text, f.created_at, 
               COALESCE(u.username, 'guestbook') AS username, 
               u.email
        FROM feedbacks AS f
        LEFT JOIN users AS u
          ON f.user_id = u.user_id
        ORDER BY f.created_at DESC
    ";
    $res   = mysqli_query($db, $sql);
    $all   = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $all[] = $row;
    }
    mysqli_close($db);
    return $all;
}

// --- Обробка форми залишення відгуку ---
$infoMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['email'])) {
        $infoMessage = "Щоб залишити відгук, необхідно увійти в акаунт.";
    } else {
        $db      = start_connect();
        $textRaw = trim($_POST['text'] ?? '');
        $user    = getUserByEmail($_SESSION['email']);

        if (!$user) {
            $infoMessage = "Користувача не знайдено.";
        } elseif (strlen($textRaw) < 8 || strlen($textRaw) > 1000) {
            $infoMessage = "Довжина відгуку має бути від 8 до 1000 символів.";
        } else {
            $text = mysqli_real_escape_string($db, $textRaw);
            $uid = intval($user['user_id']);
            $sql = "INSERT INTO feedbacks (user_id, text, created_at) VALUES ($uid, '$text', NOW())";
            if (mysqli_query($db, $sql)) {
                mysqli_close($db);
                header('Location: guestbook.php');
                exit;
            } else {
                $infoMessage = "Помилка запису: " . mysqli_error($db);
            }
        }
        mysqli_close($db);
    }
}

// Витягуємо всі відгуки
$feedbacks = getAllFeedbacks();

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <?php require_once 'sectionHead.php'; ?>
    <title>Guestbook</title>
</head>
<body>
<div class="container">
    <?php require_once 'sectionNavbar.php'; ?>

    <div class="card card-primary my-4">
        <div class="card-header bg-primary text-light">Залишити відгук</div>
        <div class="card-body">
            <?php if ($infoMessage): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($infoMessage) ?></div>
            <?php endif; ?>
            <form method="POST" action="guestbook.php">
                <div class="mb-3">
                    <label for="text" class="form-label">Ваш відгук</label>
                    <textarea id="text" name="text" class="form-control" rows="4" required></textarea>
                </div>
                <button class="btn btn-primary">Відправити</button>
            </form>
        </div>
    </div>

    <div class="card card-secondary mb-4">
        <div class="card-header bg-body-secondary text-dark">Відгуки</div>
        <div class="card-body">
            <?php if (count($feedbacks)): ?>
                <?php foreach ($feedbacks as $f): ?>
                    <div class="card mb-2">
                        <div class="card-body">
                            <h5>
                                <?= htmlspecialchars($f['username']) ?>
                                <?php if (!empty($f['email'])): ?>
                                    <small class="text-muted">(<?= htmlspecialchars($f['email']) ?>)</small>
                                <?php endif; ?>
                            </h5>
                            <p><?= nl2br(htmlspecialchars($f['text'])) ?></p>
                            <small class="text-end d-block"><?= $f['created_at'] ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Поки що немає жодного відгуку.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
