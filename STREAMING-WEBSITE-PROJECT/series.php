<?php
session_start();
require 'config.php';

$lang = $_GET['lang'] ?? 'fr';

$translations = [
    'fr' => [
        'title' => 'Séries',
        'movies' => 'Films',
        'series' => 'Séries',
        'all_genres' => 'Tous les genres',
        'search' => 'Rechercher',
        'watch' => 'Regarder',
        'episodes' => 'Épisodes',
        'season' => 'Saison',
        'episode' => 'Épisode',
        'login_required' => 'Connectez-vous pour regarder.',
        'add_fav' => 'Ajouter aux favoris',
        'remove_fav' => 'Retirer des favoris',
        'no_series' => 'Aucune série disponible.',
        'prev' => 'Précédent',
        'next' => 'Suivant'
    ],
    'ru' => [
        'title' => 'Сериалы',
        'movies' => 'Фильмы',
        'series' => 'Сериалы',
        'all_genres' => 'Все жанры',
        'search' => 'Поиск',
        'watch' => 'Смотреть',
        'episodes' => 'Эпизоды',
        'season' => 'Сезон',
        'episode' => 'Эпизод',
        'login_required' => 'Войдите, чтобы смотреть.',
        'add_fav' => 'Добавить в избранное',
        'remove_fav' => 'Удалить из избранного',
        'no_series' => 'Нет доступных сериалов.',
        'prev' => 'Предыдущий',
        'next' => 'Следующий'
    ]
];

$t = $translations[$lang];

$page = $_GET['page'] ?? 1;
$genre_filter = $_GET['genre'] ?? '';
$search = $_GET['search'] ?? '';

$limit = 6;
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM series WHERE 1=1";
$params = [];

if ($genre_filter) {
    $query .= " AND genre = ?";
    $params[] = $genre_filter;
}

if ($search) {
    $query .= " AND (title LIKE ? OR title_ru LIKE ? OR description LIKE ? OR description_ru LIKE ?)";
    $search_param = '%' . $search . '%';
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$query .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$series = $stmt->fetchAll();

// Total count for pagination
$count_query = "SELECT COUNT(*) FROM series WHERE 1=1";
$count_params = [];

if ($genre_filter) {
    $count_query .= " AND genre = ?";
    $count_params[] = $genre_filter;
}

if ($search) {
    $count_query .= " AND (title LIKE ? OR title_ru LIKE ? OR description LIKE ? OR description_ru LIKE ?)";
    $count_params = array_merge($count_params, [$search_param, $search_param, $search_param, $search_param]);
}

$stmt_count = $pdo->prepare($count_query);
$stmt_count->execute($count_params);
$total_series = $stmt_count->fetchColumn();
$total_pages = ceil($total_series / $limit);

// Get genres
$genres = $pdo->query("SELECT DISTINCT genre FROM series ORDER BY genre")->fetchAll(PDO::FETCH_COLUMN);

// Handle add/remove favorites
if (isset($_SESSION['user']) && isset($_GET['toggle_fav'])) {
    $series_id = $_GET['toggle_fav'];
    $user_id = $_SESSION['user'];

    $check = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND content_type = 'series' AND content_id = ?");
    $check->execute([$user_id, $series_id]);
    if ($check->fetch()) {
        $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND content_type = 'series' AND content_id = ?")->execute([$user_id, $series_id]);
    } else {
        $pdo->prepare("INSERT INTO favorites (user_id, content_type, content_id) VALUES (?, 'series', ?)")->execute([$user_id, $series_id]);
    }
    $url = parse_url($_SERVER['REQUEST_URI']);
    parse_str($url['query'] ?? '', $query);
    unset($query['toggle_fav']);
    $query_string = http_build_query($query);
    $redirect_url = $url['path'] . ($query_string ? '?' . $query_string : '');
    header('Location: ' . $redirect_url);
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
    <title><?=$t['title']?></title>
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
                    <input class="form-control me-2" type="search" name="q" placeholder="<?=$t['search']?>" required>
                    <button class="btn btn-outline-light" type="submit"><?=$t['search']?></button>
                </form>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="?page=<?=$page?>&genre=<?=$genre_filter?>&search=<?=$search?>&lang=fr">FR</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=<?=$page?>&genre=<?=$genre_filter?>&search=<?=$search?>&lang=ru">RU</a>
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
        <h2><?=$t['title']?></h2>

        <form method="get" class="mb-4">
            <input type="hidden" name="lang" value="<?=$lang?>">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="<?=$t['search']?>" value="<?=$search?>">
                </div>
                <div class="col-md-3">
                    <select name="genre" class="form-control">
                        <option value=""><?=$t['all_genres']?></option>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?=$g?>" <?=$genre_filter == $g ? 'selected' : ''?>><?=$g?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><?=$t['search']?></button>
                </div>
            </div>
        </form>

        <div class="row">
            <?php if (empty($series)): ?>
                <p><?=$t['no_series']?></p>
            <?php else: ?>
                <?php foreach ($series as $s): ?>
                    <div class="col-md-4 mb-4">
                        <a href="series_detail.php?id=<?=$s['id']?>&lang=<?=$lang?>&page=<?=$page?>&genre=<?=$genre_filter?>&search=<?=$search?>" class="text-decoration-none text-dark">
                        <div class="card hover-card">
                            <?php if ($s['poster_path']): ?>
                                <img src="uploads/posters/<?=$s['poster_path']?>" class="card-img-top" alt="<?=$s[$lang == 'fr' ? 'title' : 'title_ru']?>" style="height: 250px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?=$s[$lang == 'fr' ? 'title' : 'title_ru'] ?? $s['title']?></h5>
                                <p class="card-text"><?=$s[$lang == 'fr' ? 'description' : 'description_ru'] ?? $s['description']?></p>
                                <p class="text-primary"><?=$t['watch']?></p>
                            </div>
                        </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?=($page-1)?>&lang=<?=$lang?>&genre=<?=$genre_filter?>&search=<?=$search?>"><?=$t['prev']?></a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?=$i == $page ? 'active' : ''?>">
                            <a class="page-link" href="?page=<?=$i?>&lang=<?=$lang?>&genre=<?=$genre_filter?>&search=<?=$search?>"><?=$i?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?=($page+1)?>&lang=<?=$lang?>&genre=<?=$genre_filter?>&search=<?=$search?>"><?=$t['next']?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
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
