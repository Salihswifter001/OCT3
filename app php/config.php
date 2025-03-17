<?php
/**
 * Octaverum AI Yapılandırma Dosyası
 * Uygulama genelinde kullanılan yapılandırma ayarları
 */

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Veritabanı bağlantı bilgileri (gerekirse etkinleştirin)
define('DB_HOST', 'localhost');
define('DB_NAME', 'octaverum');
define('DB_USER', 'root');
define('DB_PASS', '');

// Uygulama yolları
define('BASE_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', BASE_PATH . 'includes/');
define('VIEWS_PATH', BASE_PATH . 'views/');
define('ASSETS_PATH', BASE_PATH . 'assets/');

// URL tanımları
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMAGES_URL', ASSETS_URL . 'images/');

// Varsayılan tema ayarları (React uygulamasındaki settings ile uyumlu)
$defaultSettings = [
    'theme' => [
        'darkMode' => true,
        'neonColor' => '#00ffff', // Cyan
        'glowIntensity' => 100,
    ],
    'instrument' => [
        'defaultInstrument' => 'synth',
    ],
    'audio' => [
        'autoplay' => false,
        'equalizer' => [
            'bass' => 50,
            'treble' => 50,
        ],
        'showWaveform' => true,
        'transitionEffect' => 'smooth',
    ],
    'aiMusic' => [
        'duration' => '60s',
        'bpmRange' => [
            'min' => 90,
            'max' => 140,
        ],
        'key' => 'Minör A',
        'format' => 'mp3',
    ],
    'shortcuts' => [
        'playPause' => 'Space',
        'forward' => 'ArrowRight',
        'backward' => 'ArrowLeft',
        'mute' => 'M',
        'fullscreen' => 'F',
    ],
    'interface' => [
        'clickSounds' => true,
        'autoExpandSidebar' => true,
    ],
    'account' => [
        'sessionTime' => 30,
    ],
    'language' => 'tr',
    'storage' => [
        'musicQuality' => 'high',
    ],
];

// Ayarları oturumdan al veya varsayılanları kullan
if (!isset($_SESSION['settings'])) {
    $_SESSION['settings'] = $defaultSettings;
}

$settings = $_SESSION['settings'];

// Tema renklerini ayarla
if (!isset($_SESSION['title_color'])) {
    $_SESSION['title_color'] = $settings['theme']['neonColor'];
}

// SEO Ayarları
$siteName = "Octaverum AI";
$siteDescription = "Yapay zeka ile müzik üretin. Synthwave, Cyberpunk ve daha fazlası.";
$siteKeywords = "AI, yapay zeka, müzik üretimi, synthwave, cyberpunk, vaporwave, müzik";

// Görünüm değişkenleri
$viewVariables = [
    'pageTitle' => 'Octaverum AI',
    'siteDescription' => $siteDescription,
    'activeSection' => 'home',
];

// Müzik türleri listesi
$musicGenres = [
    ['id' => 1, 'name' => 'Synthwave', 'isHot' => true],
    ['id' => 2, 'name' => 'Cyberpunk', 'isHot' => true],
    ['id' => 3, 'name' => 'Vaporwave', 'isHot' => true],
    ['id' => 4, 'name' => 'Techno', 'isHot' => false],
    ['id' => 5, 'name' => 'House', 'isHot' => false],
    ['id' => 6, 'name' => 'Ambient', 'isHot' => false],
    ['id' => 7, 'name' => 'Lo-fi', 'isHot' => false],
    ['id' => 8, 'name' => 'Drum & Bass', 'isHot' => true],
    ['id' => 9, 'name' => 'Trap', 'isHot' => false],
    ['id' => 10, 'name' => 'Hip Hop', 'isHot' => false],
    ['id' => 11, 'name' => 'Rock', 'isHot' => false],
    ['id' => 12, 'name' => 'Metal', 'isHot' => false],
    ['id' => 13, 'name' => 'Jazz', 'isHot' => false],
    ['id' => 14, 'name' => 'Classical', 'isHot' => false],
    ['id' => 15, 'name' => 'Blues', 'isHot' => false],
    ['id' => 16, 'name' => 'Folk', 'isHot' => false],
    ['id' => 17, 'name' => 'R&B', 'isHot' => false],
    ['id' => 18, 'name' => 'Pop', 'isHot' => false],
    ['id' => 19, 'name' => 'Funk', 'isHot' => true],
    ['id' => 20, 'name' => 'Reggae', 'isHot' => false],
];

// Örnek şarkı verileri (gerçek uygulamada veritabanından gelir)
$sampleTracks = [
    [
        'id' => 1,
        'title' => "Neon Rüya",
        'artist' => "CyberSynth",
        'album' => "Dijital Anılar",
        'coverUrl' => "https://via.placeholder.com/200x200/00ffff/000000",
        'duration' => "3:45",
        'isLiked' => true,
        'playCount' => 254,
        'genre' => "Synthwave"
    ],
    [
        'id' => 2,
        'title' => "Gece Şehri",
        'artist' => "RetroWave",
        'album' => "Neon Bulvarı",
        'coverUrl' => "https://via.placeholder.com/200x200/ff00ff/000000",
        'duration' => "4:20",
        'isLiked' => false,
        'playCount' => 187,
        'genre' => "Cyberpunk"
    ],
    [
        'id' => 3,
        'title' => "Dijital Yağmur",
        'artist' => "NeonEcho",
        'album' => "Elektronik Gökyüzü",
        'coverUrl' => "https://via.placeholder.com/200x200/00ff99/000000",
        'duration' => "5:12",
        'isLiked' => true,
        'playCount' => 321,
        'genre' => "Vaporwave"
    ],
    [
        'id' => 4,
        'title' => "Krom Kalp",
        'artist' => "ByteDancer",
        'album' => "Lazer Işığı",
        'coverUrl' => "https://via.placeholder.com/200x200/6600ff/000000",
        'duration' => "3:59",
        'isLiked' => false,
        'playCount' => 145,
        'genre' => "Darksynth"
    ],
    [
        'id' => 5,
        'title' => "Elektro Rüzgar",
        'artist' => "PixelNebula",
        'album' => "Kuantum Dalgaları",
        'coverUrl' => "https://via.placeholder.com/200x200/ff0099/000000",
        'duration' => "4:05",
        'isLiked' => true,
        'playCount' => 208,
        'genre' => "Chillwave"
    ],
    [
        'id' => 6,
        'title' => "Kuantum Şafak",
        'artist' => "CyberSynth",
        'album' => "Dijital Anılar",
        'coverUrl' => "https://via.placeholder.com/200x200/00ffff/000000",
        'duration' => "3:35",
        'isLiked' => false,
        'playCount' => 167,
        'genre' => "Synthtrap"
    ],
    [
        'id' => 7,
        'title' => "Megaşehir",
        'artist' => "GlitchCore",
        'album' => "Holografik",
        'coverUrl' => "https://via.placeholder.com/200x200/ff00ff/000000",
        'duration' => "4:47",
        'isLiked' => true,
        'playCount' => 283,
        'genre' => "Cyberpunk"
    ],
    [
        'id' => 8,
        'title' => "Nöral Akış",
        'artist' => "DeepWave",
        'album' => "Sanal Okyanus",
        'coverUrl' => "https://via.placeholder.com/200x200/00ff99/000000",
        'duration' => "3:52",
        'isLiked' => false,
        'playCount' => 196,
        'genre' => "Electronic"
    ]
];

// Prompt ipuçları
$promptTips = [
    'Elektronik davul ritimleri ve arpejli sentezleyicilerle yağmurlu bir gece',
    'Uzay temalı reverb efektleri ile minimal ambient',
    'Retro 80\'ler tarzı, bas gitar ve parlak sentezleyiciler',
    'Distorsiyonlu gitarlar ve dijital gürültülerle siber punk',
    'Derinlik hissi veren pad\'lerle düşük tempolu lo-fi'
];
?>