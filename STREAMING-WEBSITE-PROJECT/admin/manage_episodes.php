<?php
session_start();
require '../config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$lang = $_GET['lang'] ?? 'fr';

$translations = [
    'fr' => [
        'manage_episodes' => 'Gérer les Épisodes',
        'series' => 'Série',
        'title' => 'Titre',
        'season' => 'Saison',
        'episode' => 'Épisode',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer cet épisode ?',
        'back' => 'Retour',
        'logout' => 'Déconnexion',
        'no_episodes' => 'Aucun épisode trouvé.'
    ],
    'ru' => [
        'manage_episodes' => 'Управление эпизодами',
        'series' => 'Сериал',
        'title' => 'Название',
        'season' => 'Сезон',
        'episode' => 'Эпизод',
        'edit' => 'Редактировать',
        'delete' => 'Удалить',
        'confirm_delete' => 'Вы уверены, что хотите удалить этот эпизод?',
        'back' => 'Назад',
        'logout' => 'Выход',
        'no_episodes' => 'Эпизоды не найдены.'
    ]
];

$t = $translations[$lang];

// Handle delete
if (isset($_POST['delete_episode'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM episodes WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: manage_episodes.php?lang=' . $lang);
    exit;
}

// Get episodes with series name
$episodes = $pdo->query("SELECT e.id, e.title, e.title_ru, e.season_number, e.episode_number, s.title as series_title, s.title_ru as series_title_ru FROM episodes e JOIN series s ON e.series_id = s.id ORDER BY e.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?=$lang?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t['manage_episodes']?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php?lang=<?=$lang?>"><?=$t['manage_episodes']?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="?lang=fr">FR</a>
                <a class="nav-link" href="?lang=ru">RU</a>
                <a class="nav-link" href="logout.php"><?=$t['logout']?></a>
                <a class="nav-link" href="dashboard.php?lang=<?=$lang?>"><?=$t['back']?></a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1><?=$t['manage_episodes']?></h1>
        <?php if (count($episodes) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><?=$t['series']?></th>
                        <th><?=$t['title']?></th>
                        <th><?=$t['season']?></th>
                        <th><?=$t['episode']?></th>
                        <th><?=$t['edit']?></th>
                        <th><?=$t['delete']?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($episodes as $ep): ?>
                        <tr>
                            <td><?=$ep[$lang == 'fr' ? 'series_title' : 'series_title_ru'] ?? $ep['series_title']?></td>
                            <td><?=$ep[$lang == 'fr' ? 'title' : 'title_ru'] ?? $ep['title']?></td>
                            <td><?=$ep['season_number']?></td>
                            <td><?=$ep['episode_number']?></td>
                            <td><a href="edit_episode.php?id=<?=$ep['id']?>&lang=<?=$lang?>" class="btn btn-warning btn-sm"><?=$t['edit']?></a></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?=$ep['id']?>">
                                    <button type="submit" name="delete_episode" class="btn btn-danger btn-sm" onclick="return confirm('<?=$t['confirm_delete']?>')"><?=$t['delete']?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?=$t['no_episodes']?></p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
