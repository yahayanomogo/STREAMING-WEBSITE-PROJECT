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
        'favorites_title' => 'Mes Favoris',
        'no_favorites' => 'Aucun favori.',
        'remove' => 'Retirer',
        'back' => 'Retour'
    ],
    'ru' => [
        'favorites_title' => 'Мои избранные',
        'no_favorites' => 'Нет избранных.',
        'remove' => 'Удалить',
        'back' => 'Назад'
    ]
];

$t = $translations[$lang];

$user_id = $_SESSION['user'];

if (isset($_GET['remove'])) {
    $fav_id = $_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE id = ? AND user_id = ?");
    $stmt->execute([$fav_id, $user_id]);
    header('Location: favorites.php?lang=' . $lang);
    exit;
}

$stmt = $pdo->prepare("
    SELECT f.id, f.content_type, f.content_id, m.title, m.title_ru, m.poster_path, s.title as s_title, s.title_ru as s_title_ru, s.poster_path as s_poster
    FROM favorites f
    LEFT JOIN movies m ON f.content_type = 'movie' AND f.content_id = m.id
    LEFT JOIN series s ON f.content_type = 'series' AND f.content_id = s.id
    WHERE f.user_id = ?
");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?=$lang?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t['favorites_title']?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-4">
        <h2><?=$t['favorites_title']?></h2>
        <div class="row">
            <?php if (empty($favorites)): ?>
                <p><?=$t['no_favorites']?></p>
            <?php else: ?>
                <?php foreach ($favorites as $fav): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <?php
                            $title = $fav['content_type'] == 'movie' ? ($lang == 'fr' ? $fav['title'] : $fav['title_ru']) : ($lang == 'fr' ? $fav['s_title'] : $fav['s_title_ru']);
                            $poster = $fav['content_type'] == 'movie' ? $fav['poster_path'] : $fav['s_poster'];
                            ?>
                            <?php if ($poster): ?>
                                <img src="uploads/posters/<?=$poster?>" class="card-img-top" alt="<?=$title?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?=$title?></h5>
                                <a href="?remove=<?=$fav['id']?>&lang=<?=$lang?>" class="btn btn-danger"><?=$t['remove']?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <a href="index.php?lang=<?=$lang?>" class="btn btn-secondary"><?=$t['back']?></a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
