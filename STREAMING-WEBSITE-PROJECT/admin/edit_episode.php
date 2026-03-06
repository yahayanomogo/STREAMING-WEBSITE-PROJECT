<?php
session_start();
require '../config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$lang = $_GET['lang'] ?? 'fr';
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: manage_episodes.php?lang=' . $lang);
    exit;
}

$translations = [
    'fr' => [
        'edit_episode' => 'Modifier l\'Épisode',
        'title' => 'Titre',
        'title_ru' => 'Titre (RU)',
        'season' => 'Saison',
        'episode_num' => 'Numéro d\'Épisode',
        'video' => 'Vidéo',
        'update' => 'Mettre à jour',
        'back' => 'Retour',
        'logout' => 'Déconnexion',
        'success' => 'Épisode mis à jour avec succès!',
        'error' => 'Erreur lors de la mise à jour.'
    ],
    'ru' => [
        'edit_episode' => 'Редактировать эпизод',
        'title' => 'Название',
        'title_ru' => 'Название (RU)',
        'season' => 'Сезон',
        'episode_num' => 'Номер эпизода',
        'video' => 'Видео',
        'update' => 'Обновить',
        'back' => 'Назад',
        'logout' => 'Выход',
        'success' => 'Эпизод успешно обновлён!',
        'error' => 'Ошибка при обновлении.'
    ]
];

$t = $translations[$lang];

$message = '';

// Get episode
$stmt = $pdo->prepare("SELECT * FROM episodes WHERE id = ?");
$stmt->execute([$id]);
$episode = $stmt->fetch();

if (!$episode) {
    header('Location: manage_episodes.php?lang=' . $lang);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $title_ru = $_POST['title_ru'];
    $season = $_POST['season'];
    $episode_num = $_POST['episode_num'];

    $video_path = $episode['video_path'];
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $ext = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
        $video_path = uniqid() . '.' . $ext;
        if (!move_uploaded_file($_FILES['video']['tmp_name'], '../uploads/videos/' . $video_path)) {
            $message = 'Erreur lors de l\'upload de la vidéo.';
            $video_path = $episode['video_path'];
        }
    }

    $stmt = $pdo->prepare("UPDATE episodes SET title = ?, title_ru = ?, season_number = ?, episode_number = ?, video_path = ? WHERE id = ?");
    if ($stmt->execute([$title, $title_ru, $season, $episode_num, $video_path, $id])) {
        $message = $t['success'];
        // Refresh episode data
        $stmt = $pdo->prepare("SELECT * FROM episodes WHERE id = ?");
        $stmt->execute([$id]);
        $episode = $stmt->fetch();
    } else {
        $message = $t['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t['edit_episode']?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><?=$t['edit_episode']?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="?lang=fr">FR</a>
                <a class="nav-link" href="?lang=ru">RU</a>
                <a class="nav-link" href="logout.php"><?=$t['logout']?></a>
                <a class="nav-link" href="manage_episodes.php?lang=<?=$lang?>"><?=$t['back']?></a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($message): ?>
            <div class="alert alert-info"><?=$message?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <label><?=$t['title']?></label>
                    <input type="text" name="title" class="form-control" value="<?=$episode['title']?>" required>
                </div>
                <div class="col-md-6">
                    <label><?=$t['title_ru']?></label>
                    <input type="text" name="title_ru" class="form-control" value="<?=$episode['title_ru']?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label><?=$t['season']?></label>
                    <input type="number" name="season" class="form-control" value="<?=$episode['season_number']?>" required>
                </div>
                <div class="col-md-6">
                    <label><?=$t['episode_num']?></label>
                    <input type="number" name="episode_num" class="form-control" value="<?=$episode['episode_number']?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label><?=$t['video']?></label>
                    <input type="file" name="video" class="form-control" accept="video/*">
                    <?php if ($episode['video_path']): ?>
                        <small>Actuel: <a href="../uploads/videos/<?=$episode['video_path']?>" target="_blank">Voir</a></small>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3"><?=$t['update']?></button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
