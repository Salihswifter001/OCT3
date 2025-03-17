<?php
/**
 * Octaverum AI - Router Sınıfı
 * 
 * Bu sınıf, gelen HTTP isteklerini uygun denetleyicilere yönlendirir.
 */

class Router {
    /**
     * Kullanılabilir controller'ları tutar
     * @var array
     */
    private $controllers = [];
    
    /**
     * Hata sayfalarının işlendiği controller
     * @var mixed
     */
    private $errorController;
    
    /**
     * Constructor - Hata controller'ını hazırlar
     */
    public function __construct() {
        $this->errorController = new \Controllers\ErrorController();
    }
    
    /**
     * Controller'ları ekler
     * 
     * @param array $controllers Controller sınıflarının bir dizisi
     * @return void
     */
    public function addControllers(array $controllers) {
        $this->controllers = array_merge($this->controllers, $controllers);
    }
    
    /**
     * İsteği uygun controller ve action'a yönlendirir
     * 
     * @param string $route Çağrılan rota (controller adı)
     * @param string $action Çağrılan eylem (method adı)
     * @param mixed $id Opsiyonel ID parametresi
     * @return void
     */
    public function dispatch($route, $action, $id = null) {
        // Route adını standartlaştır
        $route = $this->formatRouteName($route);
        
        // Controller mevcut mu kontrol et
        if (!isset($this->controllers[$route])) {
            $this->handleError(404, "Sayfa bulunamadı: '$route'");
            return;
        }
        
        $controller = $this->controllers[$route];
        
        // Action'ın controller'da mevcut olduğunu kontrol et
        if (!method_exists($controller, $action)) {
            $this->handleError(404, "Eylem bulunamadı: '$action'");
            return;
        }
        
        // Controller üzerinde action'ı çağır (parametrelerle birlikte)
        try {
            if ($id !== null) {
                $controller->$action($id);
            } else {
                $controller->$action();
            }
        } catch (\Exception $e) {
            $this->handleError(500, $e->getMessage());
        }
    }
    
    /**
     * Rota ismini standartlaştırır
     * 
     * @param string $route
     * @return string
     */
    private function formatRouteName($route) {
        // Tire ve alt çizgileri yönet
        $route = str_replace(['-', '_'], ' ', $route);
        // CamelCase'e dönüştür
        $route = lcfirst(str_replace(' ', '', ucwords($route)));
        
        return $route;
    }
    
    /**
     * Hata handling
     * 
     * @param int $code HTTP durum kodu
     * @param string $message Hata mesajı
     * @return void
     */
    private function handleError($code, $message) {
        http_response_code($code);
        
        switch ($code) {
            case 404:
                $this->errorController->notFound($message);
                break;
            case 403:
                $this->errorController->forbidden($message);
                break;
            default:
                $this->errorController->serverError($message);
        }
    }
    
    /**
     * Ajax isteği olup olmadığını kontrol eder
     * 
     * @return bool
     */
    public static function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * POST isteği olup olmadığını kontrol eder
     * 
     * @return bool
     */
    public static function isPostRequest() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Yönlendirme yapar
     * 
     * @param string $route
     * @param string $action
     * @param mixed $id
     * @return void
     */
    public static function redirect($route, $action = 'index', $id = null) {
        $url = "index.php?route=$route&action=$action";
        
        if ($id !== null) {
            $url .= "&id=$id";
        }
        
        header("Location: $url");
        exit;
    }
}
