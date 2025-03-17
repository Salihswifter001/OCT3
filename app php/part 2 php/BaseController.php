<?php
namespace Controllers;

/**
 * Octaverum AI - BaseController Sınıfı
 * 
 * Tüm Controller sınıflarının temel aldığı sınıf
 */
abstract class BaseController {
    /**
     * Veritabanı bağlantısı
     * @var \PDO
     */
    protected $db;
    
    /**
     * Kullanıcı verilerini tutar
     * @var array|null
     */
    protected $user;
    
    /**
     * View için değişkenleri tutar
     * @var array
     */
    protected $viewData = [];
    
    /**
     * View dosyasının başlığı
     * @var string
     */
    protected $pageTitle = 'Octaverum AI';
    
    /**
     * Constructor
     * 
     * @param \PDO $db Veritabanı bağlantısı
     */
    public function __construct($db) {
        $this->db = $db;
        $this->user = isset($_SESSION['user_id']) ? $this->getUserData($_SESSION['user_id']) : null;
        
        // View verilerini hazırla
        $this->viewData['user'] = $this->user;
        $this->viewData['pageTitle'] = $this->pageTitle;
        $this->viewData['currentRoute'] = isset($_GET['route']) ? $_GET['route'] : 'home';
        $this->viewData['currentAction'] = isset($_GET['action']) ? $_GET['action'] : 'index';
    }
    
    /**
     * Oturum açmış kullanıcının verilerini getirir
     * 
     * @param int $userId
     * @return array|null
     */
    protected function getUserData($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (\PDOException $e) {
            $this->logError('Kullanıcı bilgileri getirilirken hata: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * View dosyasını render eder
     * 
     * @param string $view View dosyasının adı
     * @param array $data View'a aktarılacak ek veriler
     * @return void
     */
    protected function render($view, $data = []) {
        // Verilen verileri viewData ile birleştir
        $this->viewData = array_merge($this->viewData, $data);
        
        // Değişkenleri extract ederek doğrudan view içinde kullanabilir hale getir
        extract($this->viewData);
        
        // Header'ı dahil et
        include 'views/layouts/header.php';
        
        // İstenen view dosyasını dahil et
        include "views/$view.php";
        
        // Footer'ı dahil et
        include 'views/layouts/footer.php';
    }
    
    /**
     * Sadece view'ı döndürür (header/footer olmadan)
     * 
     * @param string $view View dosyasının adı
     * @param array $data View'a aktarılacak ek veriler
     * @return void
     */
    protected function renderPartial($view, $data = []) {
        // Verilen verileri viewData ile birleştir
        $this->viewData = array_merge($this->viewData, $data);
        
        // Değişkenleri extract et
        extract($this->viewData);
        
        // İstenen view dosyasını dahil et
        include "views/$view.php";
    }
    
    /**
     * API yanıtı döndürür
     * 
     * @param int $status HTTP durum kodu
     * @param mixed $data Yanıt verisi
     * @param string $message Mesaj
     * @return void
     */
    protected function jsonResponse($status = 200, $data = null, $message = '') {
        http_response_code($status);
        header('Content-Type: application/json');
        
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * Yönlendirme yapar
     * 
     * @param string $route
     * @param string $action
     * @param mixed $id
     * @return void
     */
    protected function redirect($route, $action = 'index', $id = null) {
        $url = "index.php?route=$route&action=$action";
        
        if ($id !== null) {
            $url .= "&id=$id";
        }
        
        header("Location: $url");
        exit;
    }
    
    /**
     * Hata mesajlarını loglar
     * 
     * @param string $message
     * @return void
     */
    protected function logError($message) {
        $logFile = LOG_PATH . 'error_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        
        // Klasör yoksa oluştur
        if (!file_exists(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }
        
        // Log mesajını dosyaya yaz
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Debug modundaysa ekrana yazdır
        if (DEBUG_MODE) {
            echo "<div style='color: red; background: #ffeeee; padding: 10px; margin: 10px 0; border: 1px solid #ffaaaa;'>";
            echo "<strong>Error:</strong> " . htmlspecialchars($message);
            echo "</div>";
        }
    }
    
    /**
     * CSRF token oluşturur ve session'a kaydeder
     * 
     * @return string
     */
    protected function generateCsrfToken() {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    /**
     * CSRF token'ı doğrular
     * 
     * @param string $token
     * @return bool
     */
    protected function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
            return false;
        }
        
        // Token kullanıldıktan sonra sil (one-time use)
        unset($_SESSION['csrf_token']);
        return true;
    }
}
