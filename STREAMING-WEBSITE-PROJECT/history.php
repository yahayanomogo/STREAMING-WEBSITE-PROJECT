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
        'title' => 'Site de Streaming Moderne',
        'history_title' => 'Historique de Visionnage',
        'no_history' => 'Aucun historique.',
        'back' => 'Retour',
        'movies' => 'Films',
        'series' => 'Séries',
        'watch' => 'Regarder',
        'more_info' => 'Plus d\'infos',
        'episodes' => 'Épisodes',
        'season' => 'Saison',
        'episode' => 'Épisode',
        'admin' => 'Admin',
        'no_movies' => 'Aucun film disponible.',
        'no_series' => 'Aucune série disponible.',
        'lang_fr' => 'FR',
        'lang_ru' => 'RU',
        'login' => 'Connexion',
        'register' => 'Inscription',
        'profile' => 'Profil',
        'favorites' => 'Favoris',
        'history' => 'Historique',
        'logout' => 'Déconnexion',
        'login_required' => 'Connectez-vous pour regarder.',
        'search' => 'Rechercher',
        'genre' => 'Genre',
        'all_genres' => 'Tous les genres'
    ],
    'ru' => [
        'title' => 'Современный стриминговый сайт',
        'history_title' => 'История просмотров',
        'no_history' => 'Нет истории.',
        'back' => 'Назад',
        'movies' => 'Фильмы',
        'series' => 'Сериалы',
        'watch' => 'Смотреть',
        'more_info' => 'Больше информации',
        'episodes' => 'Эпизоды',
        'season' => 'Сезон',
        'episode' => 'Эпизод',
        'admin' => 'Админ',
        'no_movies' => 'Нет доступных фильмов.',
        'no_series' => 'Нет доступных сериалов.',
        'lang_fr' => 'FR',
        'lang_ru' => 'RU',
        'login' => 'Вход',
        'register' => 'Регистрация',
        'profile' => 'Профиль',
        'favorites' => 'Избранное',
        'history' => 'История',
        'logout' => 'Выход',
        'login_required' => 'Войдите, чтобы смотреть.',
        'search' => 'Поиск',
        'genre' => 'Жанр',
        'all_genres' => 'Все жанры'
    ]
];

$t = $translations[$lang];

$user_id = $_SESSION['user'];

// Get unique movies and series from watch history, including series from watched episodes
$stmt = $pdo->prepare("
    SELECT DISTINCT
        CASE WHEN wh.content_type = 'episode' THEN 'series' ELSE wh.content_type END as display_type,
        CASE WHEN wh.content_type = 'episode' THEN e.series_id ELSE wh.content_id END as display_id,
        MAX(wh.watched_at) as last_watched,
        CASE WHEN wh.content_type = 'episode' THEN s.title ELSE m.title END as title,
        CASE WHEN wh.content_type = 'episode' THEN s.title_ru ELSE m.title_ru END as title_ru,
        CASE WHEN wh.content_type = 'episode' THEN s.description ELSE m.description END as description,
        CASE WHEN wh.content_type = 'episode' THEN s.description_ru ELSE m.description_ru END as description_ru,
        CASE WHEN wh.content_type = 'episode' THEN s.poster_path ELSE m.poster_path END as poster_path,
        CASE WHEN wh.content_type = 'episode' THEN s.genre ELSE m.genre END as genre
    FROM watch_history wh
    LEFT JOIN movies m ON wh.content_type = 'movie' AND wh.content_id = m.id
    LEFT JOIN episodes e ON wh.content_type = 'episode' AND wh.content_id = e.id
    LEFT JOIN series s ON (wh.content_type = 'series' AND wh.content_id = s.id) OR (wh.content_type = 'episode' AND e.series_id = s.id)
    WHERE wh.user_id = ?
    GROUP BY display_type, display_id
    ORDER BY last_watched DESC
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll();

// Separate movies and series
$movies = array_filter($history, fn($item) => $item['display_type'] === 'movie');
$series = array_filter($history, fn($item) => $item['display_type'] === 'series');
?>
<!DOCTYPE html>
<html lang="<?=$lang?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t['history_title']?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?=$t['title']?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="movies.php?lang=<?=$lang?>"><?=$t['movies']?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="series.php?lang=<?=$lang?>"><?=$t['series']?></a>
                    </li>
                </ul>
                <form class="d-flex me-3" method="get" action="search.php">
                    <input type="hidden" name="lang" value="<?=$lang?>">
                    <input class="form-control me-2" type="search" name="q" placeholder="<?=$t['search']?>" required>
                    <button class="btn btn-outline-light" type="submit"><?=$t['search']?></button>
                </form>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="?lang=fr"><?=$t['lang_fr']?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?lang=ru"><?=$t['lang_ru']?></a>
                    </li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php?lang=<?=$lang?>"><?=$t['profile']?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="ma_liste.php?lang=<?=$lang?>">Ma liste</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="history.php?lang=<?=$lang?>"><?=$t['history']?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout_user.php"><?=$t['logout']?></a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login_user.php?lang=<?=$lang?>"><?=$t['login']?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php?lang=<?=$lang?>"><?=$t['register']?></a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php"><?=$t['admin']?></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2><?=$t['history_title']?></h2>
        <?php if (empty($history)): ?>
            <p><?=$t['no_history']?></p>
        <?php else: ?>
            <?php if (!empty($movies)): ?>
                <h3><?=$t['movies']?></h3>
                <div class="row">
                    <?php foreach ($movies as $movie): ?>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <a href="movie_detail.php?id=<?=$movie['display_id']?>&lang=<?=$lang?>" class="text-decoration-none">
                                <div class="card hover-card" style="position: relative;">
                                    <?php if ($movie['poster_path']): ?>
                                        <img src="uploads/posters/<?=$movie['poster_path']?>" class="card-img-top" alt="<?=$movie[$lang == 'fr' ? 'title' : 'title_ru']?>" style="height: 250px; object-fit: cover;">
                                        <div class="genre-badge"><?=$movie['genre']?></div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?=$movie[$lang == 'fr' ? 'title' : 'title_ru'] ?? $movie['title']?></h5>
                                        <p class="card-text"><?=$movie[$lang == 'fr' ? 'description' : 'description_ru'] ?? $movie['description']?></p>
                                        <small class="text-muted">Vu le: <?=$movie['last_watched']?></small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($series)): ?>
                <h3><?=$t['series']?></h3>
                <div class="row">
                    <?php foreach ($series as $s): ?>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <a href="series_detail.php?id=<?=$s['display_id']?>&lang=<?=$lang?>" class="text-decoration-none">
                                <div class="card hover-card" style="position: relative;">
                                    <?php if ($s['poster_path']): ?>
                                        <img src="uploads/posters/<?=$s['poster_path']?>" class="card-img-top" alt="<?=$s[$lang == 'fr' ? 'title' : 'title_ru']?>" style="height: 250px; object-fit: cover;">
                                        <div class="genre-badge"><?=$s['genre']?></div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?=$s[$lang == 'fr' ? 'title' : 'title_ru'] ?? $s['title']?></h5>
                                        <p class="card-text"><?=$s[$lang == 'fr' ? 'description' : 'description_ru'] ?? $s['description']?></p>
                                        <small class="text-muted">Vu le: <?=$s['last_watched']?></small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <a href="index.php?lang=<?=$lang?>" class="btn btn-secondary mt-3"><?=$t['back']?></a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
