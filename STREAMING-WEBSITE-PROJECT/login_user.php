<?php
session_start();
require 'config.php';

$lang = $_GET['lang'] ?? 'fr';

$translations = [
    'fr' => [
        'login_title' => 'Connexion Utilisateur',
        'username' => 'Nom d\'utilisateur ou Email',
        'password' => 'Mot de passe',
        'login' => 'Se connecter',
        'register_link' => 'Pas de compte ? S\'inscrire',
        'error' => 'Nom d\'utilisateur/email ou mot de passe incorrect.',
        'back' => 'Retour'
    ],
    'ru' => [
        'login_title' => 'Вход пользователя',
        'username' => 'Имя пользователя или Email',
        'password' => 'Пароль',
        'login' => 'Войти',
        'register_link' => 'Нет аккаунта? Зарегистрироваться',
        'error' => 'Неверное имя пользователя/email или пароль.',
        'back' => 'Назад'
    ]
];

$t = $translations[$lang];

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_email = $_POST['username_email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND role = 'user'");
    $stmt->execute([$username_email, $username_email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php?lang=' . $lang);
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
    <link rel="stylesheet" href="css/style.css">
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
                        <label for="username_email" class="form-label"><?=$t['username']?></label>
                        <input type="text" class="form-control" id="username_email" name="username_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label"><?=$t['password']?></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><?=$t['login']?></button>
                </form>
                <p class="mt-3"><a href="register.php?lang=<?=$lang?>"><?=$t['register_link']?></a></p>
                <a href="index.php?lang=<?=$lang?>" class="btn btn-secondary"><?=$t['back']?></a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
