<?php
session_start();
require 'config.php';

$lang = $_GET['lang'] ?? 'fr';

$translations = [
    'fr' => [
        'title' => 'Site de Streaming Moderne',
        'trending' => 'Tendances',
        'latest' => 'Nouveautés',
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
        'trending' => 'Тренды',
        'latest' => 'Новинки',
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

// Récupérer les tendances (films et séries avec au moins une note)
$trending_movies = $pdo->query("SELECT m.*, m.genre, m.age_rating, IFNULL(AVG(r.rating), 0) as avg_rating, COUNT(r.rating) as total_ratings, 'movie' as type FROM movies m LEFT JOIN ratings r ON r.content_type = 'movie' AND r.content_id = m.id GROUP BY m.id HAVING COUNT(r.rating) > 0 ORDER BY avg_rating DESC, m.created_at DESC LIMIT 10")->fetchAll();
$trending_series = $pdo->query("SELECT s.*, s.genre, s.age_rating, IFNULL(AVG(r.rating), 0) as avg_rating, COUNT(r.rating) as total_ratings, 'series' as type FROM series s LEFT JOIN ratings r ON r.content_type = 'series' AND r.content_id = s.id GROUP BY s.id HAVING COUNT(r.rating) > 0 ORDER BY avg_rating DESC, s.created_at DESC LIMIT 10")->fetchAll();

// Combiner les tendances
$trending = array_merge($trending_movies, $trending_series);
usort($trending, function($a, $b) {
    return $b['avg_rating'] <=> $a['avg_rating'];
});
$trending = array_slice($trending, 0, 7); // Top 7 au total

// Récupérer les nouveautés (derniers films et séries ajoutés)
$latest_movies = $pdo->query("SELECT m.*, m.age_rating, 'movie' as type FROM movies m ORDER BY m.created_at DESC LIMIT 10")->fetchAll();
$latest_series = $pdo->query("SELECT s.*, s.age_rating, 'series' as type FROM series s ORDER BY s.created_at DESC LIMIT 10")->fetchAll();
$latest = array_merge($latest_movies, $latest_series);
usort($latest, function($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});
$latest = array_slice($latest, 0, 20); // Top 20 latest

// Hero items: top 3 latest movies + top 3 latest series
$hero_movies = array_slice($latest_movies, 0, 3);
$hero_series = array_slice($latest_series, 0, 3);
$hero_items = array_merge($hero_movies, $hero_series);

// Récupérer tous les films
$movies = $pdo->query("SELECT *, age_rating FROM movies ORDER BY created_at DESC")->fetchAll();

// Récupérer toutes les séries
$series = $pdo->query("SELECT *, age_rating FROM series ORDER BY created_at DESC")->fetchAll();

// Fonction pour récupérer les épisodes d'une série
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
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
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

    <!-- Hero Section -->
    <?php $featured = $hero_items[0] ?? null; ?>
    <div class="hero" id="hero-banner" style="background-image: url('<?php echo $featured && $featured['banner_image'] ? 'uploads/banners/' . $featured['banner_image'] : ($featured ? 'uploads/posters/' . $featured['poster_path'] : 'https://via.placeholder.com/1920x1080/333/000?text=Movie+Background'); ?>');">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-text">
                <h1 id="hero-title"><?php echo $featured ? ($lang == 'fr' ? $featured['title'] : $featured['title_ru'] ?? $featured['title']) : $t['title']; ?></h1>
                <p id="hero-desc"><?php echo $featured ? $featured[$lang == 'fr' ? 'description' : 'description_ru'] ?? $featured['description'] : ''; ?></p>
            </div>
            <div class="hero-buttons">
                <?php if ($featured): ?>
                    <a href="<?php echo $featured['type'] === 'movie' ? 'movie_detail.php?id=' . $featured['id'] . '&lang=' . $lang : 'series_detail.php?id=' . $featured['id'] . '&lang=' . $lang; ?>" class="btn btn-play btn-lg">▶ <?=$t['watch']?></a>
                    <a href="<?php echo $featured['type'] === 'movie' ? 'movie_detail.php?id=' . $featured['id'] . '&lang=' . $lang : 'series_detail.php?id=' . $featured['id'] . '&lang=' . $lang; ?>" class="btn btn-outline-light btn-lg ms-3"><?=$t['more_info']?></a>
                <?php else: ?>
                    <a href="#trending" class="btn btn-play btn-lg">▶ <?=$t['watch']?></a>
                    <a href="#trending" class="btn btn-outline-light btn-lg ms-3"><?=$t['more_info']?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <h2><?=$t['latest']?></h2>
        <?php if (empty($latest)): ?>
            <p><?=$t['no_movies']?></p>
        <?php else: ?>
            <div class="slider latest-slider">
                <?php $counter = 1; ?>
                <?php foreach ($latest as $item): ?>
                    <div class="slide">
                        <?php if ($item['type'] === 'movie'): ?>
                            <a href="movie_detail.php?id=<?=$item['id']?>&lang=<?=$lang?>" class="text-decoration-none">
                        <?php else: ?>
                            <a href="series_detail.php?id=<?=$item['id']?>&lang=<?=$lang?>" class="text-decoration-none">
                        <?php endif; ?>
                        <div class="card hover-card" style="position: relative;">
                            <?php if ($item['poster_path']): ?>
                                <img src="uploads/posters/<?=$item['poster_path']?>" class="card-img-top" alt="<?=$item[$lang == 'fr' ? 'title' : 'title_ru']?>" style="height: 250px; object-fit: cover;">
                                <div class="genre-badge"><?=$item['genre']?></div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?=$counter?>. <?=$item[$lang == 'fr' ? 'title' : 'title_ru'] ?? $item['title']?></h5>
                            </div>
                        </div>
                        </a>
                    </div>
                    <?php $counter++; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2 id="trending"><?=$t['trending']?></h2>
        <?php if (empty($trending)): ?>
            <p><?=$t['no_movies']?></p>
        <?php else: ?>
            <div class="slider trending-slider">
                <?php foreach ($trending as $item): ?>
                    <div class="slide">
                        <?php if ($item['type'] === 'movie'): ?>
                            <a href="movie_detail.php?id=<?=$item['id']?>&lang=<?=$lang?>" class="text-decoration-none">
                        <?php else: ?>
                            <a href="series_detail.php?id=<?=$item['id']?>&lang=<?=$lang?>" class="text-decoration-none">
                        <?php endif; ?>
                        <div class="card hover-card" style="position: relative;">
                            <?php if ($item['poster_path']): ?>
                                <img src="uploads/posters/<?=$item['poster_path']?>" class="card-img-top" alt="<?=$item[$lang == 'fr' ? 'title' : 'title_ru']?>" style="height: 250px; object-fit: cover;">
                                <div class="genre-badge"><?=$item['genre']?></div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?=$item[$lang == 'fr' ? 'title' : 'title_ru'] ?? $item['title']?></h5>
                                <p class="text-warning">⭐ <?=$item['avg_rating']?>/5 (<?=$item['total_ratings']?> votes)</p>
                            </div>
                        </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2><?=$t['movies']?></h2>
        <?php if (empty($movies)): ?>
            <p><?=$t['no_movies']?></p>
        <?php else: ?>
            <div class="slider movies-slider">
                <?php foreach ($movies as $movie): ?>
                    <div class="slide">
                        <a href="movie_detail.php?id=<?=$movie['id']?>&lang=<?=$lang?>" class="text-decoration-none">
                        <div class="card hover-card" style="position: relative;">
                            <?php if ($movie['poster_path']): ?>
                                <img src="uploads/posters/<?=$movie['poster_path']?>" class="card-img-top" alt="<?=$movie[$lang == 'fr' ? 'title' : 'title_ru']?>" style="height: 250px; object-fit: cover;">
                                <div class="genre-badge"><?=$movie['genre']?></div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?=$movie[$lang == 'fr' ? 'title' : 'title_ru'] ?? $movie['title']?></h5>
                                <p class="card-text"><?=$movie[$lang == 'fr' ? 'description' : 'description_ru'] ?? $movie['description']?></p>
                            </div>
                        </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2><?=$t['series']?></h2>
        <?php if (empty($series)): ?>
            <p><?=$t['no_series']?></p>
        <?php else: ?>
            <div class="slider series-slider">
                <?php foreach ($series as $s): ?>
                    <div class="slide">
                        <a href="series_detail.php?id=<?=$s['id']?>&lang=<?=$lang?>" class="text-decoration-none">
                        <div class="card hover-card" style="position: relative;">
                            <?php if ($s['poster_path']): ?>
                                <img src="uploads/posters/<?=$s['poster_path']?>" class="card-img-top" alt="<?=$s[$lang == 'fr' ? 'title' : 'title_ru']?>" style="height: 250px; object-fit: cover;">
                                <div class="genre-badge"><?=$s['genre']?></div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?=$s[$lang == 'fr' ? 'title' : 'title_ru'] ?? $s['title']?></h5>
                                <p class="card-text"><?=$s[$lang == 'fr' ? 'description' : 'description_ru'] ?? $s['description']?></p>
                            </div>
                        </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script>
        $(document).ready(function(){
            $('.trending-slider').slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                autoplay: false,
                arrows: true,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 2
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            });
            $('.latest-slider').slick({
                slidesToShow: 3,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 3000,
                arrows: true,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 2
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 1
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            });
            $('.movies-slider').slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                autoplay: false,
                arrows: true,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 2
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            });
            $('.series-slider').slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                autoplay: false,
                arrows: true,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 2
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            const players = Plyr.setup('.player');
        });

        // Hero banner cycling
        const heroItems = <?php echo json_encode($hero_items); ?>;
        const lang = '<?php echo $lang; ?>';
        let currentIndex = 0;
        const heroBanner = document.getElementById('hero-banner');
        const watchBtn = document.querySelector('.hero-buttons .btn-play');
        const moreInfoBtn = document.querySelector('.hero-buttons .btn-outline-light');

        function updateHero() {
            if (heroItems.length === 0) return;
            const item = heroItems[currentIndex];
            const bannerUrl = item.banner_image ? `uploads/banners/${item.banner_image}` : `uploads/posters/${item.poster_path}`;
            heroBanner.style.backgroundImage = `url('${bannerUrl}')`;
            const detailUrl = item.type === 'movie' ? `movie_detail.php?id=${item.id}&lang=${lang}` : `series_detail.php?id=${item.id}&lang=${lang}`;
            watchBtn.href = detailUrl;
            moreInfoBtn.href = detailUrl;
            // Update title and description
            const titleElement = document.getElementById('hero-title');
            const descElement = document.getElementById('hero-desc');
            titleElement.textContent = lang === 'fr' ? item.title : item.title_ru || item.title;
            descElement.textContent = lang === 'fr' ? item.description : item.description_ru || item.description;
            currentIndex = (currentIndex + 1) % heroItems.length;
        }

        if (heroItems.length > 1) {
            setInterval(updateHero, 4000);
        }
    </script>
    <script src="js/script.js"></script>
</body>
</html>
