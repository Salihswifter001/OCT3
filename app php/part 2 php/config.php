<?php
/**
 * Octaverum AI - Yapılandırma Dosyası
 * 
 * Bu dosya, uygulama genelinde kullanılan yapılandırma parametrelerini içerir.
 */

// Uygulama URL bilgileri
define('BASE_URL', 'http://localhost/octaverum-ai');
define('SITE_NAME', 'Octaverum AI');

// Veritabanı bağlantı ayarları
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'octaverum_ai');

// Varsayılan tema ayarları
define('DEFAULT_THEME', [
    'dark_mode' => true,
    'primary_color' => '#00ffff',
    'secondary_color' => '#ff00ff',
    'accent_color' => '#ff0099',
    'background_dark' => '#0a0a12',
    'background_light' => '#1a1a2e',
    'text_color' => '#e0e0e0',
    'glow_intensity' => 100
]);

// API ayarları
define('SUNO_API_URL', 'https://apibox.erweima.ai');
define('SUNO_API_VERSION', 'v1');

// Dosya yükleme ayarları
define('UPLOAD_PATH', 'public/uploads/');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'mp3', 'wav']);

// Oturum ayarları
define('SESSION_LIFETIME', 30 * 60); // 30 dakika
define('SESSION_NAME', 'octaverum_session');

// Güvenlik ayarları
define('HASH_COST', 10); // Şifre hash güvenlik seviyesi
define('AUTH_SALT', 'octaverum_secure_salt_1234'); // Güvenlik tuzu

// Hata raporlama
define('LOG_PATH', 'logs/');
define('DEBUG_MODE', true);

// Cache ayarları
define('CACHE_ENABLED', true);
define('CACHE_PATH', 'cache/');
define('CACHE_LIFETIME', 3600); // 1 saat

// Abonelik planları
define('SUBSCRIPTION_PLANS', [
    'free' => [
        'name' => 'Ücretsiz',
        'price' => 0,
        'track_limit' => 10,
        'features' => [
            'Temel AI müzik oluşturma',
            'Standart kalite ses',
            'Sınırlı enstrüman seçenekleri',
            '10 müzik oluşturma hakkı/ay'
        ]
    ],
    'premium' => [
        'name' => 'Premium',
        'price' => 99.90,
        'track_limit' => 50,
        'features' => [
            'Gelişmiş AI müzik oluşturma',
            'Yüksek kaliteli ses',
            'Tüm enstrüman seçenekleri',
            '50 müzik oluşturma hakkı/ay',
            'Şarkı sözü oluşturma',
            'Müzik indirme'
        ]
    ],
    'pro' => [
        'name' => 'Profesyonel',
        'price' => 199.90,
        'track_limit' => null, // Sınırsız
        'features' => [
            'Premium özelliklerin tümü',
            'Stüdyo kalitesinde ses',
            'Vokal ayırma ve düzenleme',
            'Sınırsız müzik oluşturma',
            'API erişimi',
            'Ticari kullanım lisansı'
        ]
    ]
]);

// Varsayılan dil
define('DEFAULT_LANGUAGE', 'tr');
define('SUPPORTED_LANGUAGES', ['tr', 'en', 'es']);

// Uygulama sürümü
define('APP_VERSION', '1.0.0');

/**
 * Veritabanı bağlantı sınıfı
 */
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $connection;
    
    /**
     * Veritabanı bağlantısını oluşturur
     */
    public function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->user,
                $this->pass
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Veritabanı bağlantı hatası: " . $e->getMessage());
            } else {
                die("Veritabanı bağlantısı kurulamadı. Lütfen daha sonra tekrar deneyin.");
            }
        }
    }
    
    /**
     * Veritabanı bağlantısını döndürür
     */
    public function getConnection() {
        return $this->connection;
    }
}
