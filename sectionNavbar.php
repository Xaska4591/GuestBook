<nav class="navbar navbar-expand-lg bg-body-secondary">
    <div class="container-fluid">

        <!-- Логотип і посилання на головну (guestbook) -->
        <a class="navbar-brand" href="/guestbook.php">
            <span style="color: Dodgerblue;">
                <i class="fa-brands fa-php fa-2xl"></i>
            </span>
        </a>

        <!-- Кнопка для згорнутого меню на мобілці -->
        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Меню -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Посилання на гостьову книгу завжди -->
                <li class="nav-item">
                    <a href="/guestbook.php" class="nav-link">GuestBook</a>
                </li>

                <!-- Якщо користувач авторизований — показуємо Admin -->
                <?php if (!empty($_SESSION['auth'])): ?>
                    <li class="nav-item">
                        <a href="/admin.php" class="nav-link">Admin</a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav mb-2 mb-lg-0">
                <?php if (!empty($_SESSION['auth'])): ?>
                    <!-- Коли авторизовані — показуємо Logout -->
                    <li class="nav-item">
                        <a href="/logout.php" class="nav-link">Logout</a>
                    </li>
                <?php else: ?>
                    <!-- Інакше — Register та Login -->
                    <li class="nav-item">
                        <a href="/register.php" class="nav-link">Register</a>
                    </li>
                    <li class="nav-item">
                        <a href="/login.php" class="nav-link">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

    </div>
</nav>
