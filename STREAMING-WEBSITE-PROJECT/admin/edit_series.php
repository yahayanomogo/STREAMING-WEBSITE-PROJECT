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
    header('Location: manage_series.php?lang=' . $lang);
    exit;
}

$translations = [
    'fr' => [
        'edit_series' => 'Modifier la Série',
        'title' => 'Titre',
        'title_ru' => 'Titre (RU)',
        'description' => 'Description',
        'description_ru' => 'Description (RU)',
        'genre' => 'Genre',
        'year' => 'Année',
        'age_rating' => 'Âge minimal',
        'poster' => 'Poster',
        'banner' => 'Bannière',
        'update' => 'Mettre à jour',
        'back' => 'Retour',
        'logout' => 'Déconnexion',
        'success' => 'Série mise à jour avec succès!',
        'error' => 'Erreur lors de la mise à jour.'
    ],
    'ru' => [
        'edit_series' => 'Редактировать сериал',
        'title' => 'Название',
        'title_ru' => 'Название (RU)',
        'description' => 'Описание',
        'description_ru' => 'Описание (RU)',
        'genre' => 'Жанр',
        'year' => 'Год',
        'poster' => 'Постер',
        'banner' => 'Баннер',
        'update' => 'Обновить',
        'back' => 'Назад',
        'logout' => 'Выход',
        'success' => 'Сериал успешно обновлён!',
        'error' => 'Ошибка при обновлении.'
    ]
];

$t = $translations[$lang];

$message = '';

// Get series
$stmt = $pdo->prepare("SELECT * FROM series WHERE id = ?");
$stmt->execute([$id]);
$series = $stmt->fetch();

if (!$series) {
    header('Location: manage_series.php?lang=' . $lang);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $title_ru = $_POST['title_ru'];
    $description = $_POST['description'];
    $description_ru = $_POST['description_ru'];
    $genre = $_POST['genre'];
    $year = $_POST['year'];
    $age_rating = $_POST['age_rating'] ?? null;

    $poster_path = $series['poster_path'];
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
        $ext = pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION);
        $poster_path = uniqid() . '.' . $ext;
        if (!move_uploaded_file($_FILES['poster']['tmp_name'], '../uploads/posters/' . $poster_path)) {
            $message = 'Erreur lors de l\'upload du poster.';
            $poster_path = $series['poster_path'];
        }
    }

    $banner_path = $series['banner_image'];
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] == 0) {
        $ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
        $banner_path = uniqid() . '.' . $ext;
        if (!move_uploaded_file($_FILES['banner']['tmp_name'], '../uploads/banners/' . $banner_path)) {
            $message = 'Erreur lors de l\'upload de la bannière.';
            $banner_path = $series['banner_image'];
        }
    }

    $stmt = $pdo->prepare("UPDATE series SET title = ?, title_ru = ?, description = ?, description_ru = ?, genre = ?, release_year = ?, age_rating = ?, poster_path = ?, banner_image = ? WHERE id = ?");
    if ($stmt->execute([$title, $title_ru, $description, $description_ru, $genre, $year, $age_rating, $poster_path, $banner_path, $id])) {
        $message = $t['success'];
        // Refresh series data
        $stmt = $pdo->prepare("SELECT * FROM series WHERE id = ?");
        $stmt->execute([$id]);
        $series = $stmt->fetch();
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
    <title><?=$t['edit_series']?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><?=$t['edit_series']?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="?lang=fr">FR</a>
                <a class="nav-link" href="?lang=ru">RU</a>
                <a class="nav-link" href="logout.php"><?=$t['logout']?></a>
                <a class="nav-link" href="manage_series.php?lang=<?=$lang?>"><?=$t['back']?></a>
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
                    <input type="text" name="title" class="form-control" value="<?=$series['title']?>" required>
                </div>
                <div class="col-md-6">
                    <label><?=$t['title_ru']?></label>
                    <input type="text" name="title_ru" class="form-control" value="<?=$series['title_ru']?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label><?=$t['description']?></label>
                    <textarea name="description" class="form-control" required><?=$series['description']?></textarea>
                </div>
                <div class="col-md-6">
                    <label><?=$t['description_ru']?></label>
                    <textarea name="description_ru" class="form-control"><?=$series['description_ru']?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label><?=$t['genre']?></label>
                    <select name="genre" class="form-control" required>
                        <option value="">-- Sélectionner un genre --</option>
                        <?php foreach ($config['genres'] as $g): ?>
                            <option value="<?=$g?>" <?=$series['genre'] == $g ? 'selected' : ''?>><?=$g?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label><?=$t['year']?></label>
                    <input type="number" name="year" class="form-control" value="<?=$series['release_year']?>" required>
                </div>
                <div class="col-md-4">
                    <label><?=$t['age_rating']?></label>
                    <input type="number" name="age_rating" class="form-control" min="0" value="<?=$series['age_rating']?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label><?=$t['poster']?></label>
                    <input type="file" name="poster" class="form-control" accept="image/*">
                    <?php if ($series['poster_path']): ?>
                        <small>Actuel: <a href="../uploads/posters/<?=$series['poster_path']?>" target="_blank">Voir</a></small>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label><?=$t['banner']?></label>
                    <input type="file" name="banner" class="form-control" accept="image/*">
                    <?php if ($series['banner_image']): ?>
                        <small>Actuel: <a href="../uploads/banners/<?=$series['banner_image']?>" target="_blank">Voir</a></small>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3"><?=$t['update']?></button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
