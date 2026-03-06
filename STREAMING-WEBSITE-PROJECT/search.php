<?php
session_start();
require 'config.php';

$lang = $_GET['lang'] ?? 'fr';
$q = $_GET['q'] ?? '';

$translations = [
    'fr' => [
        'search_results' => 'Résultats de recherche pour',
        'movies' => 'Films',
        'series' => 'Séries',
        'watch' => 'Regarder',
        'episodes' => 'Épisodes',
        'season' => 'Saison',
        'episode' => 'Épisode',
        'login_required' => 'Connectez-vous pour regarder.',
        'add_fav' => 'Ajouter aux favoris',
        'remove_fav' => 'Retirer des favoris',
        'no_results' => 'Aucun résultat trouvé.',
        'back' => 'Retour'
    ],
    'ru' => [
        'search_results' => 'Результаты поиска для',
        'movies' => 'Фильмы',
        'series' => 'Сериалы',
        'watch' => 'Смотреть',
        'episodes' => 'Эпизоды',
        'season' => 'Сезон',
        'episode' => 'Эпизод',
        'login_required' => 'Войдите, чтобы смотреть.',
        'add_fav' => 'Добавить в избранное',
        'remove_fav' => 'Удалить из избранного',
        'no_results' => 'Результатов не найдено.',
        'back' => 'Назад'
    ]
];

$t = $translations[$lang];

$movies = [];
$series = [];

if ($q) {
    $search_param = '%' . $q . '%';

    // Search movies
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE title LIKE ? OR title_ru LIKE ? OR description LIKE ? OR description_ru LIKE ?");
    $stmt->execute([$search_param, $search_param, $search_param, $search_param]);
    $movies = $stmt->fetchAll();

    // Search series
    $stmt = $pdo->prepare("SELECT * FROM series WHERE title LIKE ? OR title_ru LIKE ? OR description LIKE ? OR description_ru LIKE ?");
    $stmt->execute([$search_param, $search_param, $search_param, $search_param]);
    $series = $stmt->fetchAll();
}

// Handle add/remove favorites
if (isset($_SESSION['user']) && isset($_GET['toggle_fav']) && isset($_GET['type'])) {
    $content_id = $_GET['toggle_fav'];
    $content_type = $_GET['type'];
    $user_id = $_SESSION['user'];

    $check = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND content_type = ? AND content_id = ?");
    $check->execute([$user_id, $content_type, $content_id]);
    if ($check->fetch()) {
        $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND content_type = ? AND content_id = ?")->execute([$user_id, $content_type, $content_id]);
    } else {
        $pdo->prepare("INSERT INTO favorites (user_id, content_type, content_id) VALUES (?, ?, ?)")->execute([$user_id, $content_type, $content_id]);
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Function to get episodes
function getEpisodes($pdo, $series_id) {
    $stmt = $pdo->prepare("SELECT * FROM episodes WHERE series_id = ? ORDER BY season_number, episode_number");
    $stmt->execute([$series_id]);
    return $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="<?=$lang?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t['search_results']?> "<?=$q?>"</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?=$translations[$lang]['title'] ?? 'Site'?></a>
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
                    <input class="form-control me-2" type="search" name="q" placeholder="<?=$t['search'] ?? 'Search'?>" value="<?=$q?>" required>
                    <button class="btn btn-outline-light" type="submit"><?=$t['search'] ?? 'Search'?></button>
                </form>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="?q=<?=$q?>&lang=fr">FR</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?q=<?=$q?>&lang=ru">RU</a>
                    </li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php?lang=<?=$lang?>"><?=$translations[$lang]['profile'] ?? 'Profile'?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="favorites.php?lang=<?=$lang?>"><?=$translations[$lang]['favorites'] ?? 'Favorites'?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="history.php?lang=<?=$lang?>"><?=$translations[$lang]['history'] ?? 'History'?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout_user.php"><?=$translations[$lang]['logout'] ?? 'Logout'?></a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login_user.php?lang=<?=$lang?>"><?=$translations[$lang]['login'] ?? 'Login'?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php?lang=<?=$lang?>"><?=$translations[$lang]['register'] ?? 'Register'?></a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php"><?=$translations[$lang]['admin'] ?? 'Admin'?></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2><?=$t['search_results']?> "<?=$q?>"</h2>

        <?php if (!empty($movies)): ?>
            <h3><?=$t['movies']?></h3>
            <div class="row">
                <?php foreach ($movies as $movie): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <?php if ($movie['poster_path']): ?>
                                <img src="uploads/posters/<?=$movie['poster_path']?>" class="card-img-top" alt="<?=$movie[$lang == 'fr' ? 'title' : 'title_ru']?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?=$movie[$lang == 'fr' ? 'title' : 'title_ru'] ?? $movie['title']?></h5>
                                <p class="card-text"><?=$movie[$lang == 'fr' ? 'description' : 'description_ru'] ?? $movie['description']?></p>
                                <p><strong><?=$t['watch']?></strong></p>
                                <?php if ($movie['video_path']): ?>
                                    <?php if (isset($_SESSION['user'])): ?>
                                        <video class="player w-100">
                                            <source src="uploads/videos/<?=$movie['video_path']?>" type="video/mp4">
                                        </video>
                                    <?php else: ?>
                                        <p class="text-muted"><?=$t['login_required']?></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['user'])): ?>
                                    <?php
                                    $user_id = $_SESSION['user'];
                                    $is_fav = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND content_type = 'movie' AND content_id = ?");
                                    $is_fav->execute([$user_id, $movie['id']]);
                                    $fav = $is_fav->fetch();
                                    ?>
                                    <a href="?q=<?=$q?>&lang=<?=$lang?>&toggle_fav=<?=$movie['id']?>&type=movie" class="btn btn-sm <?=$fav ? 'btn-danger' : 'btn-success'?>">
                                        <?=$fav ? $t['remove_fav'] : $t['add_fav']?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($series)): ?>
            <h3><?=$t['series']?></h3>
            <div class="row">
                <?php foreach ($series as $s): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <?php if ($s['poster_path']): ?>
                                <img src="uploads/posters/<?=$s['poster_path']?>" class="card-img-top" alt="<?=$s[$lang == 'fr' ? 'title' : 'title_ru']?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?=$s[$lang == 'fr' ? 'title' : 'title_ru'] ?? $s['title']?></h5>
                                <p class="card-text"><?=$s[$lang == 'fr' ? 'description' : 'description_ru'] ?? $s['description']?></p>
                                <h6><?=$t['episodes']?></h6>
                                <ul>
                                    <?php $episodes = getEpisodes($pdo, $s['id']); ?>
                                    <?php foreach ($episodes as $ep): ?>
                                        <li><?=$t['season']?> <?=$ep['season_number']?> - <?=$t['episode']?> <?=$ep['episode_number']?>: <?=$ep[$lang == 'fr' ? 'title' : 'title_ru'] ?? $ep['title']?>
                                            <?php if ($ep['video_path']): ?>
                                                <?php if (isset($_SESSION['user'])): ?>
                                                    <video class="player w-100 mt-2">
                                                        <source src="uploads/videos/<?=$ep['video_path']?>" type="video/mp4">
                                                    </video>
                                                <?php else: ?>
                                                    <p class="text-muted"><?=$t['login_required']?></p>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if (isset($_SESSION['user'])): ?>
                                    <?php
                                    $user_id = $_SESSION['user'];
                                    $is_fav = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND content_type = 'series' AND content_id = ?");
                                    $is_fav->execute([$user_id, $s['id']]);
                                    $fav = $is_fav->fetch();
                                    ?>
                                    <a href="?q=<?=$q?>&lang=<?=$lang?>&toggle_fav=<?=$s['id']?>&type=series" class="btn btn-sm <?=$fav ? 'btn-danger' : 'btn-success'?>">
                                        <?=$fav ? $t['remove_fav'] : $t['add_fav']?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($movies) && empty($series)): ?>
            <p><?=$t['no_results']?></p>
        <?php endif; ?>

        <a href="index.php?lang=<?=$lang?>" class="btn btn-secondary"><?=$t['back']?></a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const players = Plyr.setup('.player');
        });
    </script>
    <script src="js/script.js"></script>
</body>
</html>
