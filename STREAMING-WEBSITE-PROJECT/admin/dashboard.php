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
        'dashboard' => 'Panneau Admin',
        'add_movie' => 'Ajouter un Film',
        'add_series' => 'Ajouter une Série',
        'add_episode' => 'Ajouter un Épisode',
        'manage_movies' => 'Gérer les Films',
        'manage_series' => 'Gérer les Séries',
        'manage_episodes' => 'Gérer les Épisodes',
        'title' => 'Titre',
        'title_ru' => 'Titre (RU)',
        'description' => 'Description',
        'description_ru' => 'Description (RU)',
        'genre' => 'Genre',
        'year' => 'Année',
        'duration' => 'Durée (min)',
        'director' => 'Réalisateur',
        'age_rating' => 'Âge minimal',
        'poster' => 'Poster',
        'banner' => 'Bannière',
        'video' => 'Vidéo',
        'submit' => 'Ajouter',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer ?',
        'logout' => 'Déconnexion',
        'select_series' => 'Sélectionner Série',
        'season' => 'Saison',
        'episode_num' => 'Numéro d\'Épisode',
        'success' => 'Ajouté avec succès!',
        'error' => 'Erreur lors de l\'ajout.',
        'back' => 'Retour'
    ],
    'ru' => [
        'dashboard' => 'Панель администратора',
        'add_movie' => 'Добавить фильм',
        'add_series' => 'Добавить сериал',
        'add_episode' => 'Добавить эпизод',
        'manage_movies' => 'Управление фильмами',
        'manage_series' => 'Управление сериалами',
        'manage_episodes' => 'Управление эпизодами',
        'title' => 'Название',
        'title_ru' => 'Название (RU)',
        'description' => 'Описание',
        'description_ru' => 'Описание (RU)',
        'genre' => 'Жанр',
        'year' => 'Год',
        'duration' => 'Длительность (мин)',
        'director' => 'Режиссёр',
        'age_rating' => 'Минимальный возраст',
        'poster' => 'Постер',
        'banner' => 'Баннер',
        'video' => 'Видео',
        'submit' => 'Добавить',
        'edit' => 'Редактировать',
        'delete' => 'Удалить',
        'confirm_delete' => 'Вы уверены, что хотите удалить?',
        'logout' => 'Выход',
        'select_series' => 'Выбрать сериал',
        'season' => 'Сезон',
        'episode_num' => 'Номер эпизода',
        'success' => 'Успешно добавлено!',
        'error' => 'Ошибка при добавлении.',
        'back' => 'Назад'
    ]
];

$t = $translations[$lang];

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_movie'])) {
        // Add movie
        $title = $_POST['title'];
        $title_ru = $_POST['title_ru'];
        $description = $_POST['description'];
        $description_ru = $_POST['description_ru'];
        $genre = $_POST['genre'];
        $year = $_POST['year'];
        $duration = $_POST['duration'];
        $director = $_POST['director'];
        $age_rating = $_POST['age_rating'] ?? null;

        $poster_path = '';
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
            $ext = pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION);
            $poster_path = uniqid() . '.' . $ext;
            if (!move_uploaded_file($_FILES['poster']['tmp_name'], '../uploads/posters/' . $poster_path)) {
                $message = 'Erreur lors de l\'upload du poster.';
                $poster_path = '';
            }
        } elseif (isset($_FILES['poster']) && $_FILES['poster']['error'] != 4) {
            $message = 'Erreur upload poster: ' . $_FILES['poster']['error'];
        }

        $video_path = '';
        if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
            // Check file size limit (e.g., 5GB)
            if ($_FILES['video']['size'] > 5 * 1024 * 1024 * 1024) {
                $message = 'La taille de la vidéo dépasse la limite de 5 Go.';
            } else {
                $ext = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
                $video_path = uniqid() . '.' . $ext;
                if (!move_uploaded_file($_FILES['video']['tmp_name'], '../uploads/videos/' . $video_path)) {
                    $message = 'Erreur lors de l\'upload de la vidéo.';
                    $video_path = '';
                }
            }
        } elseif (isset($_FILES['video']) && $_FILES['video']['error'] != 4) {
            $message = 'Erreur upload vidéo: ' . $_FILES['video']['error'];
        }

        $banner_path = '';
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] == 0) {
            $ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
            $banner_path = uniqid() . '.' . $ext;
            if (!move_uploaded_file($_FILES['banner']['tmp_name'], '../uploads/banners/' . $banner_path)) {
                $message = 'Erreur lors de l\'upload de la bannière.';
                $banner_path = '';
            }
        } elseif (isset($_FILES['banner']) && $_FILES['banner']['error'] != 4) {
            $message = 'Erreur upload bannière: ' . $_FILES['banner']['error'];
        }

        $stmt = $pdo->prepare("INSERT INTO movies (title, title_ru, description, description_ru, genre, release_year, duration, director, age_rating, poster_path, banner_image, video_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $title_ru, $description, $description_ru, $genre, $year, $duration, $director, $age_rating, $poster_path, $banner_path, $video_path])) {
            $message = $t['success'];
        } else {
            $message = $t['error'];
        }
    } elseif (isset($_POST['add_series'])) {
        // Add series
        $title = $_POST['title'];
        $title_ru = $_POST['title_ru'];
        $description = $_POST['description'];
        $description_ru = $_POST['description_ru'];
        $genre = $_POST['genre'];
        $year = $_POST['year'];
        $age_rating = $_POST['age_rating'] ?? null;

        $poster_path = '';
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
            $ext = pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION);
            $poster_path = uniqid() . '.' . $ext;
            if (!move_uploaded_file($_FILES['poster']['tmp_name'], '../uploads/posters/' . $poster_path)) {
                $message = 'Erreur lors de l\'upload du poster.';
                $poster_path = '';
            }
        } elseif (isset($_FILES['poster']) && $_FILES['poster']['error'] != 4) {
            $message = 'Erreur upload poster: ' . $_FILES['poster']['error'];
        }

        $banner_path = '';
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] == 0) {
            $ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
            $banner_path = uniqid() . '.' . $ext;
            if (!move_uploaded_file($_FILES['banner']['tmp_name'], '../uploads/banners/' . $banner_path)) {
                $message = 'Erreur lors de l\'upload de la bannière.';
                $banner_path = '';
            }
        } elseif (isset($_FILES['banner']) && $_FILES['banner']['error'] != 4) {
            $message = 'Erreur upload bannière: ' . $_FILES['banner']['error'];
        }

        $stmt = $pdo->prepare("INSERT INTO series (title, title_ru, description, description_ru, genre, release_year, age_rating, poster_path, banner_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $title_ru, $description, $description_ru, $genre, $year, $age_rating, $poster_path, $banner_path])) {
            $message = $t['success'];
        } else {
            $message = $t['error'];
        }
    } elseif (isset($_POST['add_episode'])) {
        // Add episode
        $series_id = $_POST['series_id'];
        $title = $_POST['title'];
        $title_ru = $_POST['title_ru'];
        $season = $_POST['season'];
        $episode_num = $_POST['episode_num'];

        $video_path = '';
        if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
            // Check file size limit (e.g., 5GB)
            if ($_FILES['video']['size'] > 5 * 1024 * 1024 * 1024) {
                $message = 'La taille de la vidéo dépasse la limite de 5 Go.';
            } else {
                $ext = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
                $video_path = uniqid() . '.' . $ext;
                if (!move_uploaded_file($_FILES['video']['tmp_name'], '../uploads/videos/' . $video_path)) {
                    $message = 'Erreur lors de l\'upload de la vidéo.';
                    $video_path = '';
                }
            }
        } elseif (isset($_FILES['video']) && $_FILES['video']['error'] != 4) {
            $message = 'Erreur upload vidéo: ' . $_FILES['video']['error'];
        }

        $stmt = $pdo->prepare("INSERT INTO episodes (series_id, title, title_ru, episode_number, season_number, video_path) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$series_id, $title, $title_ru, $episode_num, $season, $video_path])) {
            $message = $t['success'];
        } else {
            $message = $t['error'];
        }
    }
}

// Get series for episode form
$series_list = $pdo->query("SELECT id, title FROM series")->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?=$lang?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t['dashboard']?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><?=$t['dashboard']?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="?lang=fr">FR</a>
                <a class="nav-link" href="?lang=ru">RU</a>
                <a class="nav-link" href="logout.php"><?=$t['logout']?></a>
                <a class="nav-link" href="../index.php?lang=<?=$lang?>"><?=$t['back']?></a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($message): ?>
            <div class="alert alert-info"><?=$message?></div>
        <?php endif; ?>

        <ul class="nav nav-tabs" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="movie-tab" data-bs-toggle="tab" data-bs-target="#movie" type="button" role="tab"><?=$t['add_movie']?></button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="series-tab" data-bs-toggle="tab" data-bs-target="#series" type="button" role="tab"><?=$t['add_series']?></button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="episode-tab" data-bs-toggle="tab" data-bs-target="#episode" type="button" role="tab"><?=$t['add_episode']?></button>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_movies.php?lang=<?=$lang?>"><?=$t['manage_movies']?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_series.php?lang=<?=$lang?>"><?=$t['manage_series']?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_episodes.php?lang=<?=$lang?>"><?=$t['manage_episodes']?></a>
            </li>
        </ul>

        <div class="tab-content" id="adminTabsContent">
            <div class="tab-pane fade show active" id="movie" role="tabpanel">
                <form method="post" enctype="multipart/form-data" class="mt-3">
                    <input type="hidden" name="add_movie" value="1">
                    <div class="row">
                        <div class="col-md-6">
                            <label><?=$t['title']?></label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label><?=$t['title_ru']?></label>
                            <input type="text" name="title_ru" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label><?=$t['description']?></label>
                            <textarea name="description" class="form-control" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label><?=$t['description_ru']?></label>
                            <textarea name="description_ru" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label><?=$t['genre']?></label>
                            <select name="genre" class="form-control" required>
                                <option value="">-- Sélectionner un genre --</option>
                                <?php foreach ($config['genres'] as $g): ?>
                                    <option value="<?=$g?>"><?=$g?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label><?=$t['year']?></label>
                            <input type="number" name="year" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label><?=$t['duration']?></label>
                            <input type="number" name="duration" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label><?=$t['director']?></label>
                            <input type="text" name="director" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label><?=$t['age_rating']?></label>
                            <input type="number" name="age_rating" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label><?=$t['poster']?></label>
                            <input type="file" name="poster" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label><?=$t['banner']?></label>
                            <input type="file" name="banner" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label><?=$t['video']?></label>
                            <input type="file" name="video" class="form-control" accept="video/*">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3"><?=$t['submit']?></button>
                </form>
            </div>

            <div class="tab-pane fade" id="series" role="tabpanel">
                <form method="post" enctype="multipart/form-data" class="mt-3">
                    <input type="hidden" name="add_series" value="1">
                    <div class="row">
                        <div class="col-md-6">
                            <label><?=$t['title']?></label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label><?=$t['title_ru']?></label>
                            <input type="text" name="title_ru" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label><?=$t['description']?></label>
                            <textarea name="description" class="form-control" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label><?=$t['description_ru']?></label>
                            <textarea name="description_ru" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label><?=$t['genre']?></label>
                            <select name="genre" class="form-control" required>
                                <option value="">-- Sélectionner un genre --</option>
                                <?php foreach ($config['genres'] as $g): ?>
                                    <option value="<?=$g?>"><?=$g?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label><?=$t['year']?></label>
                            <input type="number" name="year" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label><?=$t['age_rating']?></label>
                            <input type="number" name="age_rating" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label><?=$t['poster']?></label>
                            <input type="file" name="poster" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label><?=$t['banner']?></label>
                            <input type="file" name="banner" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3"><?=$t['submit']?></button>
                </form>
            </div>

            <div class="tab-pane fade" id="episode" role="tabpanel">
                <form method="post" enctype="multipart/form-data" class="mt-3">
                    <input type="hidden" name="add_episode" value="1">
                    <div class="row">
                        <div class="col-md-6">
                            <label><?=$t['select_series']?></label>
                            <select name="series_id" class="form-control" required>
                                <option value="">-- <?=$t['select_series']?> --</option>
                                <?php foreach ($series_list as $s): ?>
                                    <option value="<?=$s['id']?>"><?=$s['title']?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label><?=$t['title']?></label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label><?=$t['title_ru']?></label>
                            <input type="text" name="title_ru" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label><?=$t['season']?></label>
                            <input type="number" name="season" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label><?=$t['episode_num']?></label>
                            <input type="number" name="episode_num" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label><?=$t['video']?></label>
                            <input type="file" name="video" class="form-control" accept="video/*">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3"><?=$t['submit']?></button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
