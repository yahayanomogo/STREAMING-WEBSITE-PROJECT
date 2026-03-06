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
        'manage_series' => 'Gérer les Séries',
        'title' => 'Titre',
        'genre' => 'Genre',
        'year' => 'Année',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer cette série ?',
        'back' => 'Retour',
        'logout' => 'Déconnexion',
        'no_series' => 'Aucune série trouvée.'
    ],
    'ru' => [
        'manage_series' => 'Управление сериалами',
        'title' => 'Название',
        'genre' => 'Жанр',
        'year' => 'Год',
        'edit' => 'Редактировать',
        'delete' => 'Удалить',
        'confirm_delete' => 'Вы уверены, что хотите удалить этот сериал?',
        'back' => 'Назад',
        'logout' => 'Выход',
        'no_series' => 'Сериалы не найдены.'
    ]
];

$t = $translations[$lang];

// Handle delete
if (isset($_POST['delete_series'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM series WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: manage_series.php?lang=' . $lang);
    exit;
}

// Get series
$series = $pdo->query("SELECT id, title, title_ru, genre, release_year, age_rating FROM series ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?=$lang?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t['manage_series']?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php?lang=<?=$lang?>"><?=$t['manage_series']?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="?lang=fr">FR</a>
                <a class="nav-link" href="?lang=ru">RU</a>
                <a class="nav-link" href="logout.php"><?=$t['logout']?></a>
                <a class="nav-link" href="dashboard.php?lang=<?=$lang?>"><?=$t['back']?></a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1><?=$t['manage_series']?></h1>
        <?php if (count($series) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><?=$t['title']?></th>
                        <th><?=$t['genre']?></th>
                        <th><?=$t['year']?></th>
                        <th>Âge</th>
                        <th><?=$t['edit']?></th>
                        <th><?=$t['delete']?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($series as $s): ?>
                        <tr>
                            <td><?=$s[$lang == 'fr' ? 'title' : 'title_ru'] ?? $s['title']?></td>
                            <td><?=$s['genre']?></td>
                            <td><?=$s['release_year']?></td>
                            <td><?=$s['age_rating'] ?? '-'?></td>
                            <td><a href="edit_series.php?id=<?=$s['id']?>&lang=<?=$lang?>" class="btn btn-warning btn-sm"><?=$t['edit']?></a></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?=$s['id']?>">
                                    <button type="submit" name="delete_series" class="btn btn-danger btn-sm" onclick="return confirm('<?=$t['confirm_delete']?>')"><?=$t['delete']?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?=$t['no_series']?></p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
