<?php
session_start();
require 'config.php';

$lang = $_GET['lang'] ?? 'fr';
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: movies.php?lang=' . $lang);
    exit;
}

$translations = [
    'fr' => [
        'title' => 'Détails Film',
        'movies' => 'Films',
        'series' => 'Séries',
        'watch' => 'Regarder',
        'login_required' => 'Connectez-vous pour regarder.',
        'add_fav' => 'Ajouter à ma liste',
        'remove_fav' => 'Retirer des favoris',
        'back' => 'Retour',
        'play' => 'Jouer',
        'genre' => 'Genre',
        'year' => 'Année',
        'duration' => 'Durée',
        'director' => 'Réalisateur',
        'age_rating' => 'Âge minimal',
        'comments' => 'Commentaires',
        'write_comment' => 'Écrivez votre commentaire...',
        'post_comment' => 'Poster le commentaire',
        'login_to_comment' => 'Connectez-vous pour laisser un commentaire.',
        'no_comments' => 'Aucun commentaire pour le moment.'
    ],
    'ru' => [
        'title' => 'Детали фильма',
        'movies' => 'Фильмы',
        'series' => 'Сериалы',
        'watch' => 'Смотреть',
        'login_required' => 'Войдите, чтобы смотреть.',
        'add_fav' => 'Добавить в мой список',
        'remove_fav' => 'Удалить из избранного',
        'back' => 'Назад',
        'play' => 'Играть',
        'genre' => 'Жанр',
        'year' => 'Год',
        'duration' => 'Длительность',
        'director' => 'Режиссёр',
        'age_rating' => 'Минимальный возраст',
        'comments' => 'Комментарии',
        'write_comment' => 'Напишите ваш комментарий...',
        'post_comment' => 'Опубликовать комментарий',
        'login_to_comment' => 'Войдите, чтобы оставить комментарий.',
        'no_comments' => 'Пока нет комментариев.'
    ]
];

$t = $translations[$lang];

// Get movie
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$id]);
$movie = $stmt->fetch();

if (!$movie) {
    header('Location: movies.php?lang=' . $lang);
    exit;
}

// Get ratings
$rating_stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM ratings WHERE content_type = 'movie' AND content_id = ?");
$rating_stmt->execute([$id]);
$rating_data = $rating_stmt->fetch();
$avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
$total_ratings = $rating_data['total_ratings'] ?? 0;

$user_rating = null;
if (isset($_SESSION['user'])) {
    $user_rating_stmt = $pdo->prepare("SELECT rating FROM ratings WHERE user_id = ? AND content_type = 'movie' AND content_id = ?");
    $user_rating_stmt->execute([$_SESSION['user'], $id]);
    $user_rating = $user_rating_stmt->fetchColumn();
}

// Handle favorites
if (isset($_SESSION['user']) && isset($_GET['toggle_fav'])) {
    $user_id = $_SESSION['user'];
    $check = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND content_type = 'movie' AND content_id = ?");
    $check->execute([$user_id, $id]);
    if ($check->fetch()) {
        $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND content_type = 'movie' AND content_id = ?")->execute([$user_id, $id]);
    } else {
        $pdo->prepare("INSERT INTO favorites (user_id, content_type, content_id) VALUES (?, 'movie', ?)")->execute([$user_id, $id]);
    }
    $url = parse_url($_SERVER['REQUEST_URI']);
    $query = $url['query'] ?? '';
    parse_str($query, $params);
    unset($params['toggle_fav']);
    $new_query = http_build_query($params);
    $redirect_url = $url['path'] . ($new_query ? '?' . $new_query : '');
    header('Location: ' . $redirect_url);
    exit;
}

// Handle rating
if (isset($_POST['rating']) && isset($_SESSION['user'])) {
    $rating = (int)$_POST['rating'];
    if ($rating >= 1 && $rating <= 5) {
        $pdo->prepare("INSERT INTO ratings (user_id, content_type, content_id, rating) VALUES (?, 'movie', ?, ?) ON DUPLICATE KEY UPDATE rating = ?")->execute([$_SESSION['user'], $id, $rating, $rating]);
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Handle comment submission
if (isset($_POST['comment']) && isset($_SESSION['user'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $pdo->prepare("INSERT INTO comments (user_id, content_type, content_id, comment) VALUES (?, 'movie', ?, ?)")->execute([$_SESSION['user'], $id, $comment]);
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Get comments
$comments_stmt = $pdo->prepare("
    SELECT c.comment, c.created_at, u.username
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.content_type = 'movie' AND c.content_id = ?
    ORDER BY c.created_at DESC
");
$comments_stmt->execute([$id]);
$comments = $comments_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?=$lang?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$movie[$lang == 'fr' ? 'title' : 'title_ru'] ?? $movie['title']?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://vjs.zencdn.net/8.6.1/video-js.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <input class="form-control me-2" type="search" name="q" placeholder="<?=$t['watch']?>" required>
                    <button class="btn btn-outline-light" type="submit"><?=$t['watch']?></button>
                </form>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="?id=<?=$id?>&lang=fr">FR</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?id=<?=$id?>&lang=ru">RU</a>
                    </li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php?lang=<?=$lang?>"><?=$translations[$lang]['profile'] ?? 'Profile'?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="ma_liste.php?lang=<?=$lang?>">Ma liste</a>
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
        <a href="movies.php?lang=<?=$lang?>" class="btn btn-secondary mb-3"><?=$t['back']?></a>

        <!-- Banner Section -->
        <?php if ($movie['banner_image']): ?>
            <div class="movie-banner mb-4" style="background-image: url('uploads/banners/<?=$movie['banner_image']?>'); height: 300px; background-size: cover; background-position: center; border-radius: 10px;"></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
        <div class="poster-container position-relative">
            <?php if ($movie['poster_path']): ?>
                <img id="poster" src="uploads/posters/<?=$movie['poster_path']?>" class="img-fluid" alt="<?=$movie[$lang == 'fr' ? 'title' : 'title_ru']?>">
                <?php if ($movie['video_path'] && isset($_SESSION['user'])): ?>
                    <button id="play-btn" class="play-btn btn btn-primary position-absolute top-50 start-50 translate-middle">
                        <i class="fas fa-play"></i> <?=$t['play']?>
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if ($movie['video_path']): ?>
            <?php if (isset($_SESSION['user'])): ?>
    <div id="video-container" style="display: none; position: fixed; top: 3rem; left: 0; width: 100vw; height: calc(100vh - 3rem); background: black; z-index: 9999;">
        <button id="close-player" style="position: absolute; top: 10px; right: 10px; z-index: 10000; background: rgba(0,0,0,0.5); color: white; border: none; padding: 10px; cursor: pointer;">X</button>
        <video id="player" class="video-js vjs-fill vjs-big-play-centered" controls preload="auto" data-setup='{}' style="width: 100%; height: 100%;">
            <source src="uploads/videos/<?=$movie['video_path']?>" type="video/mp4">
        </video>
    </div>
            <?php else: ?>
                <p class="text-muted"><?=$t['login_required']?></p>
            <?php endif; ?>
        <?php endif; ?>
            </div>
            <div class="col-md-8">
                <h1><?=$movie[$lang == 'fr' ? 'title' : 'title_ru'] ?? $movie['title']?></h1>
                <p><?=$movie[$lang == 'fr' ? 'description' : 'description_ru'] ?? $movie['description']?></p>
                <div class="rating mb-3">
                    <strong>Note: <?=$avg_rating?>/5 (<?=$total_ratings?> votes)</strong><br>
                    <?php if (isset($_SESSION['user'])): ?>
                        <form method="post" style="display: inline;">
                            <?php for ($i=1; $i<=5; $i++): ?>
                                <button type="submit" name="rating" value="<?=$i?>" class="star-btn" style="background: none; border: none; font-size: 24px; color: <?=$user_rating >= $i ? 'gold' : 'gray'?>;">★</button>
                            <?php endfor; ?>
                        </form>
                    <?php endif; ?>
                </div>
                <p><strong><?=$t['genre']?>:</strong> <?=$movie['genre']?></p>
                <p><strong><?=$t['year']?>:</strong> <?=$movie['release_year']?></p>
                <p><strong><?=$t['duration']?>:</strong> <?=$movie['duration']?> min</p>
                <p><strong><?=$t['director']?>:</strong> <?=$movie['director']?></p>
                <p><strong><?=$t['age_rating']?>:</strong> <?=$movie['age_rating'] ?? '-'?></p>
                <?php if (isset($_SESSION['user'])): ?>
                    <?php
                    $user_id = $_SESSION['user'];
                    $is_fav = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND content_type = 'movie' AND content_id = ?");
                    $is_fav->execute([$user_id, $id]);
                    $fav = $is_fav->fetch();
                    ?>
                    <a href="?toggle_fav=1&id=<?=$id?>&lang=<?=$lang?>" class="btn <?=$fav ? 'btn-danger' : 'btn-success'?>">
                        <?=$fav ? $t['remove_fav'] : $t['add_fav']?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="container mt-5">
        <h3><?=$t['comments']?></h3>
        <?php if (isset($_SESSION['user'])): ?>
            <form method="post" class="mb-4">
                <div class="mb-3">
                    <textarea name="comment" class="form-control" rows="3" placeholder="<?=$t['write_comment']?>" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><?=$t['post_comment']?></button>
            </form>
        <?php else: ?>
            <p class="text-muted"><?=$t['login_to_comment']?></p>
        <?php endif; ?>

        <div class="comments-list">
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title"><?=$comment['username']?></h6>
                            <p class="card-text"><?=$comment['comment']?></p>
                            <small class="text-muted"><?=$comment['created_at']?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted"><?=$t['no_comments']?></p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://vjs.zencdn.net/8.6.1/video.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const playerContainer = document.getElementById('video-container');
            const player = videojs('player');

            const closeBtn = document.getElementById('close-player');
            closeBtn.addEventListener('click', () => {
                player.pause();
                player.currentTime(0);
                playerContainer.style.display = 'none';
            });

            const playBtn = document.getElementById('play-btn');
            const poster = document.getElementById('poster');

            if (playBtn) {
                playBtn.addEventListener('click', function () {
                    poster.style.display = 'none';
                    playBtn.style.display = 'none';
                    playerContainer.style.display = 'block';
                    player.play();
                });
            }

            const videoElement = document.getElementById('player_html5_api'); // native video element inside videojs player

            player.on('loadedmetadata', () => {
                const videoWidth = videoElement.videoWidth;
                const videoHeight = videoElement.videoHeight;
                const containerWidth = window.innerWidth;
                const containerHeight = window.innerHeight;

                const videoAspectRatio = videoWidth / videoHeight;
                const containerAspectRatio = containerWidth / containerHeight;

                let newWidth, newHeight;

                if (containerAspectRatio > videoAspectRatio) {
                    // Container is wider than video aspect ratio
                    newHeight = containerHeight;
                    newWidth = newHeight * videoAspectRatio;
                } else {
                    // Container is narrower than video aspect ratio
                    newWidth = containerWidth;
                    newHeight = newWidth / videoAspectRatio;
                }

                // Apply new size and center the video
                videoElement.style.width = newWidth + 'px';
                videoElement.style.height = newHeight + 'px';
                videoElement.style.position = 'absolute';
                videoElement.style.top = '50%';
                videoElement.style.left = '50%';
                videoElement.style.transform = 'translate(-50%, -50%)';
            });
        });
    </script>
    <script src="js/script.js"></script>
</body>
</html>
