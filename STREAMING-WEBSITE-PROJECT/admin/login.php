<?php
session_start();
require '../config.php';

$lang = $_GET['lang'] ?? 'fr';

$translations = [
    'fr' => [
        'login_title' => 'Connexion Admin',
        'username' => 'Nom d\'utilisateur',
        'password' => 'Mot de passe',
        'login' => 'Se connecter',
        'error' => 'Nom d\'utilisateur ou mot de passe incorrect.',
        'back' => 'Retour'
    ],
    'ru' => [
        'login_title' => 'Вход для администратора',
        'username' => 'Имя пользователя',
        'password' => 'Пароль',
        'login' => 'Войти',
        'error' => 'Неверное имя пользователя или пароль.',
        'back' => 'Назад'
    ]
];

$t = $translations[$lang];

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $password == $user['password']) { // Note: In production, use password_verify with hashed passwords
        $_SESSION['admin'] = true;
        $_SESSION['username'] = $username;
        header('Location: dashboard.php?lang=' . $lang);
        exit;
    } else {
        $error = $t['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t['login_title']?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2><?=$t['login_title']?></h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?=$error?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label"><?=$t['username']?></label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label"><?=$t['password']?></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><?=$t['login']?></button>
                </form>
                <a href="../index.php?lang=<?=$lang?>" class="btn btn-secondary mt-3"><?=$t['back']?></a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
