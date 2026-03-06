<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login_user.php');
    exit;
}

require 'config.php';

$lang = $_GET['lang'] ?? 'fr';

$translations = [
    'fr' => [
        'profile_title' => 'Profil',
        'username' => 'Nom d\'utilisateur',
        'email' => 'Email',
        'change_password' => 'Changer le mot de passe',
        'old_password' => 'Ancien mot de passe',
        'new_password' => 'Nouveau mot de passe',
        'confirm_new_password' => 'Confirmer le nouveau mot de passe',
        'update' => 'Mettre à jour',
        'back' => 'Retour',
        'success' => 'Profil mis à jour.',
        'error' => 'Erreur lors de la mise à jour.'
    ],
    'ru' => [
        'profile_title' => 'Профиль',
        'username' => 'Имя пользователя',
        'email' => 'Email',
        'change_password' => 'Изменить пароль',
        'old_password' => 'Старый пароль',
        'new_password' => 'Новый пароль',
        'confirm_new_password' => 'Подтвердить новый пароль',
        'update' => 'Обновить',
        'back' => 'Назад',
        'success' => 'Профиль обновлен.',
        'error' => 'Ошибка при обновлении.'
    ]
];

$t = $translations[$lang];

$user_id = $_SESSION['user'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_new_password'] ?? '';
    $old_password = $_POST['old_password'] ?? '';

    if (!password_verify($old_password, $user['password'])) {
        $error = 'Ancien mot de passe incorrect.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Les nouveaux mots de passe ne correspondent pas.';
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed, $user_id])) {
            $message = $t['success'];
        } else {
            $error = $t['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t['profile_title']?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2><?=$t['profile_title']?></h2>
                <p><?=$t['username']?>: <?=$user['username']?></p>
                <p><?=$t['email']?>: <?=$user['email']?></p>
                <?php if ($message): ?>
                    <div class="alert alert-success"><?=$message?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?=$error?></div>
                <?php endif; ?>
                <form method="post">
                    <h3><?=$t['change_password']?></h3>
                    <div class="mb-3">
                        <label for="old_password" class="form-label"><?=$t['old_password']?></label>
                        <input type="password" class="form-control" id="old_password" name="old_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label"><?=$t['new_password']?></label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_new_password" class="form-label"><?=$t['confirm_new_password']?></label>
                        <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><?=$t['update']?></button>
                </form>
                <a href="index.php?lang=<?=$lang?>" class="btn btn-secondary mt-3"><?=$t['back']?></a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
