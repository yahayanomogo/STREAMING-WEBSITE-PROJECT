<?php
require 'config.php';

$lang = $_GET['lang'] ?? 'fr';

$translations = [
    'fr' => [
        'register_title' => 'Inscription',
        'username' => 'Nom d\'utilisateur',
        'email' => 'Email',
        'password' => 'Mot de passe',
        'confirm_password' => 'Confirmer le mot de passe',
        'register' => 'S\'inscrire',
        'login_link' => 'Déjà un compte ? Se connecter',
        'error_username' => 'Nom d\'utilisateur déjà pris.',
        'error_email' => 'Email déjà utilisé.',
        'error_password' => 'Les mots de passe ne correspondent pas.',
        'success' => 'Inscription réussie ! Vous pouvez maintenant vous connecter.',
        'back' => 'Retour'
    ],
    'ru' => [
        'register_title' => 'Регистрация',
        'username' => 'Имя пользователя',
        'email' => 'Email',
        'password' => 'Пароль',
        'confirm_password' => 'Подтвердить пароль',
        'register' => 'Зарегистрироваться',
        'login_link' => 'Уже есть аккаунт? Войти',
        'error_username' => 'Имя пользователя уже занято.',
        'error_email' => 'Email уже используется.',
        'error_password' => 'Пароли не совпадают.',
        'success' => 'Регистрация успешна! Теперь вы можете войти.',
        'back' => 'Назад'
    ]
];

$t = $translations[$lang];

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = $t['error_password'];
    } else {
        // Check if username or email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            if ($stmt->execute([$username, ''])->fetch()) {
                $error = $t['error_username'];
            } else {
                $error = $t['error_email'];
            }
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $message = $t['success'];
            } else {
                $error = 'Erreur lors de l\'inscription.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t['register_title']?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2><?=$t['register_title']?></h2>
                <?php if ($message): ?>
                    <div class="alert alert-success"><?=$message?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?=$error?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label"><?=$t['username']?></label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label"><?=$t['email']?></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label"><?=$t['password']?></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label"><?=$t['confirm_password']?></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><?=$t['register']?></button>
                </form>
                <p class="mt-3"><a href="login_user.php?lang=<?=$lang?>"><?=$t['login_link']?></a></p>
                <a href="index.php?lang=<?=$lang?>" class="btn btn-secondary"><?=$t['back']?></a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
