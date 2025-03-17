<?php
namespace Controllers;

/**
 * Octaverum AI - ApiController Sınıfı
 * 
 * API isteklerini işleyen Controller
 */
class ApiController extends BaseController {
    /**
     * Ayarlar yardımcısı
     * @var \Helpers\SettingsHelper
     */
    protected $settingsHelper;
    
    /**
     * API yardımcısı
     * @var \Helpers\ApiHelper
     */
    protected $apiHelper;
    
    /**
     * Controller constructor
     * 
     * @param \PDO $db Veritabanı bağlantısı
     * @param \Helpers\SettingsHelper $settingsHelper
     */
    public function __construct($db, $settingsHelper) {
        parent::__construct($db);
        $this->settingsHelper = $settingsHelper;
        $this->apiHelper = new \Helpers\ApiHelper();
    }
    
    /**
     * Tüm API isteklerini karşılar
     * 
     * @return void
     */
    public function index() {
        // AJAX isteği değilse reddet
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            $this->jsonResponse(400, null, 'Bad Request');
        }
        
        // İsteği ilgili methoda yönlendir
        $action = filter_input(INPUT_GET, 'method', FILTER_SANITIZE_STRING);
        
        switch ($action) {
            case 'generateMusic':
                $this->generateMusic();
                break;
            case 'generateLyrics':
                $this->generateLyrics();
                break;
            case 'checkStatus':
                $this->checkStatus();
                break;
            case 'saveMusic':
                $this->saveMusic();
                break;
            case 'updateSettings':
                $this->updateSettings();
                break;
            case 'toggleLike':
                $this->toggleLike();
                break;
            default:
                $this->jsonResponse(404, null, 'Method Not Found');
        }
    }
    
    /**
     * Müzik oluşturma API endpointi
     * 
     * @return void
     */
    private function generateMusic() {
        // Kullanıcı oturumu kontrolü
        if (!$this->user) {
            $this->jsonResponse(401, null, 'Unauthorized');
        }
        
        // Kullanıcının aylık oluşturma limitini kontrol et
        $userPlan = $this->user['subscription_plan'] ?? 'free';
        $planLimits = SUBSCRIPTION_PLANS[$userPlan]['track_limit'] ?? 10;
        
        if ($userPlan !== 'pro') {
            $musicModel = new \Models\Music($this->db);
            $userTrackCount = $musicModel->getMonthlyTrackCountByUser($this->user['id']);
            
            if ($userTrackCount >= $planLimits) {
                $this->jsonResponse(403, null, 'Bu ay için müzik oluşturma limitinize ulaştınız.');
            }
        }
        
        // İstek parametrelerini al
        $prompt = filter_input(INPUT_POST, 'prompt', FILTER_SANITIZE_STRING);
        $includeVocals = filter_input(INPUT_POST, 'include_vocals', FILTER_VALIDATE_BOOLEAN);
        $lyrics = filter_input(INPUT_POST, 'lyrics', FILTER_SANITIZE_STRING);
        $genres = isset($_POST['genres']) ? $_POST['genres'] : [];
        
        // Kullanıcı ayarlarından model türünü al
        $userSettings = $this->settingsHelper->getUserSettings($this->user['id']);
        $model = $userPlan === 'free' ? 'V3_5' : 'V4';
        
        // Parametreleri doğrula
        if (empty($prompt)) {
            $this->jsonResponse(400, null, 'Prompt alanı boş olamaz');
        }
        
        try {
            $params = [
                'prompt' => $prompt,
                'customMode' => true,
                'instrumental' => !$includeVocals,
                'model' => $model
            ];
            
            // Şarkı sözleri dahil edilecekse ekle
            if ($includeVocals && !empty($lyrics)) {
                $params['lyrics'] = $lyrics;
            }
            
            // API isteğini yap
            $result = $this->apiHelper->generateMusic($params);
            
            // Görevi veritabanına kaydet
            $stmt = $this->db->prepare("
                INSERT INTO music_tasks (task_id, user_id, prompt, include_vocals, lyrics, model, genres, status, created_at)
                VALUES (:task_id, :user_id, :prompt, :include_vocals, :lyrics, :model, :genres, 'pending', NOW())
            ");
            
            $genresJson = json_encode($genres);
            $lyricsValue = $includeVocals ? $lyrics : null;
            
            $stmt->bindParam(':task_id', $result['taskId']);
            $stmt->bindParam(':user_id', $this->user['id']);
            $stmt->bindParam(':prompt', $prompt);
            $stmt->bindParam(':include_vocals', $includeVocals, \PDO::PARAM_BOOL);
            $stmt->bindParam(':lyrics', $lyricsValue);
            $stmt->bindParam(':model', $model);
            $stmt->bindParam(':genres', $genresJson);
            
            $stmt->execute();
            
            $this->jsonResponse(200, $result, 'Müzik oluşturma işlemi başlatıldı');
            
        } catch (\Exception $e) {
            $this->logError('Müzik oluşturma hatası: ' . $e->getMessage());
            $this->jsonResponse(500, null, 'Müzik oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }
    
    /**
     * Şarkı sözü oluşturma API endpointi
     * 
     * @return void
     */
    private function generateLyrics() {
        // Kullanıcı oturumu kontrolü
        if (!$this->user) {
            $this->jsonResponse(401, null, 'Unauthorized');
        }
        
        // Free kullanıcıları için sözleri oluşturma özelliği kapalı
        $userPlan = $this->user['subscription_plan'] ?? 'free';
        if ($userPlan === 'free') {
            $this->jsonResponse(403, null, 'Şarkı sözü oluşturma özelliği yalnızca Premium ve Pro üyeleri için mevcuttur.');
        }
        
        // İstek parametrelerini al
        $prompt = filter_input(INPUT_POST, 'prompt', FILTER_SANITIZE_STRING);
        
        // Parametreleri doğrula
        if (empty($prompt)) {
            $this->jsonResponse(400, null, 'Prompt alanı boş olamaz');
        }
        
        try {
            // API isteğini yap
            $result = $this->apiHelper->generateLyrics(['prompt' => $prompt]);
            
            $this->jsonResponse(200, $result, 'Şarkı sözü oluşturma işlemi başlatıldı');
            
        } catch (\Exception $e) {
            $this->logError('Şarkı sözü oluşturma hatası: ' . $e->getMessage());
            $this->jsonResponse(500, null, 'Şarkı sözü oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }
    
    /**
     * İşlem durumunu kontrol eden API endpointi
     * 
     * @return void
     */
    private function checkStatus() {
        // Kullanıcı oturumu kontrolü
        if (!$this->user) {
            $this->jsonResponse(401, null, 'Unauthorized');
        }
        
        // İstek parametrelerini al
        $taskId = filter_input(INPUT_GET, 'task_id', FILTER_SANITIZE_STRING);
        
        // Parametreleri doğrula
        if (empty($taskId)) {
            $this->jsonResponse(400, null, 'Task ID alanı boş olamaz');
        }
        
        try {
            // API isteğini yap
            $result = $this->apiHelper->checkTaskStatus($taskId);
            
            // Task durumunu veritabanında güncelle
            if (isset($result['status'])) {
                $stmt = $this->db->prepare("
                    UPDATE music_tasks 
                    SET status = :status, progress = :progress, updated_at = NOW() 
                    WHERE task_id = :task_id
                ");
                
                $progress = isset($result['progress']) ? $result['progress'] : null;
                
                $stmt->bindParam(':status', $result['status']);
                $stmt->bindParam(':progress', $progress);
                $stmt->bindParam(':task_id', $taskId);
                
                $stmt->execute();
                
                // Eğer task tamamlandıysa, sonucu da kaydet
                if ($result['status'] === 'completed' && isset($result['result'])) {
                    $stmt = $this->db->prepare("
                        UPDATE music_tasks 
                        SET result = :result
                        WHERE task_id = :task_id
                    ");
                    
                    $resultJson = json_encode($result['result']);
                    
                    $stmt->bindParam(':result', $resultJson);
                    $stmt->bindParam(':task_id', $taskId);
                    
                    $stmt->execute();
                }
            }
            
            $this->jsonResponse(200, $result, 'Task durumu alındı');
            
        } catch (\Exception $e) {
            $this->logError('Task durumu alınırken hata: ' . $e->getMessage());
            $this->jsonResponse(500, null, 'Task durumu kontrol edilirken bir hata oluştu: ' . $e->getMessage());
        }
    }
    
    /**
     * Oluşturulan müziği kaydetme API endpointi
     * 
     * @return void
     */
    private function saveMusic() {
        // Kullanıcı oturumu kontrolü
        if (!$this->user) {
            $this->jsonResponse(401, null, 'Unauthorized');
        }
        
        // İstek parametrelerini al
        $taskId = filter_input(INPUT_POST, 'task_id', FILTER_SANITIZE_STRING);
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $audioUrl = filter_input(INPUT_POST, 'audio_url', FILTER_SANITIZE_URL);
        
        // Parametreleri doğrula
        if (empty($taskId) || empty($title) || empty($audioUrl)) {
            $this->jsonResponse(400, null, 'Gerekli alanlar boş olamaz');
        }
        
        try {
            // Bu task'ın kullanıcıya ait olduğunu doğrula
            $stmt = $this->db->prepare("
                SELECT * FROM music_tasks 
                WHERE task_id = :task_id AND user_id = :user_id
            ");
            
            $stmt->bindParam(':task_id', $taskId);
            $stmt->bindParam(':user_id', $this->user['id']);
            
            $stmt->execute();
            
            $task = $stmt->fetch();
            
            if (!$task) {
                $this->jsonResponse(403, null, 'Bu işleme erişim izniniz yok');
            }
            
            // Müzik verilerini hazırla
            $genres = json_decode($task['genres'] ?? '[]', true);
            $genreStr = is_array($genres) && !empty($genres) ? implode(', ', $genres) : 'Electronic';
            
            // Dosyayı sunucuya kaydetme örneği (normalde API URL'ini kaydedeceğiz)
            $localPath = null;
            if (filter_var($audioUrl, FILTER_VALIDATE_URL)) {
                $fileContent = file_get_contents($audioUrl);
                if ($fileContent !== false) {
                    // Kayıt klasörünü oluştur
                    $uploadDir = UPLOAD_PATH . 'music/' . date('Y/m/');
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    // Dosya adını oluştur
                    $filename = uniqid('track_') . '.mp3';
                    $filePath = $uploadDir . $filename;
                    
                    // Dosyayı kaydet
                    if (file_put_contents($filePath, $fileContent)) {
                        $localPath = $filePath;
                    }
                }
            }
            
            // Müziği veritabanına kaydet
            $musicModel = new \Models\Music($this->db);
            $trackId = $musicModel->createTrack([
                'user_id' => $this->user['id'],
                'title' => $title,
                'artist' => $this->user['username'] ?? 'Octaverum Kullanıcısı',
                'genre' => $genreStr,
                'duration' => '0:00', // API sonucundan alınabilir
                'file_url' => $audioUrl,
                'local_path' => $localPath,
                'cover_url' => 'https://via.placeholder.com/300/00ffff/000000', // Varsayılan kapak
                'is_private' => 0,
                'prompt' => $task['prompt'],
                'include_vocals' => $task['include_vocals'],
                'lyrics' => $task['lyrics'],
                'task_id' => $taskId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->jsonResponse(200, ['track_id' => $trackId], 'Müzik başarıyla kaydedildi');
            
        } catch (\Exception $e) {
            $this->logError('Müzik kaydetme hatası: ' . $e->getMessage());
            $this->jsonResponse(500, null, 'Müzik kaydedilirken bir hata oluştu: ' . $e->getMessage());
        }
    }
    
    /**
     * Ayarları güncelleme API endpointi
     * 
     * @return void
     */
    private function updateSettings() {
        // Kullanıcı oturumu kontrolü
        if (!$this->user) {
            $this->jsonResponse(401, null, 'Unauthorized');
        }
        
        // POST verilerini al
        $settings = $_POST['settings'] ?? null;
        
        if (!$settings || !is_array($settings)) {
            $this->jsonResponse(400, null, 'Geçersiz ayarlar');
        }
        
        try {
            // Ayarları güncelle
            $result = $this->settingsHelper->updateUserSettings($this->user['id'], $settings);
            
            $this->jsonResponse(200, ['updated' => $result], 'Ayarlar başarıyla güncellendi');
            
        } catch (\Exception $e) {
            $this->logError('Ayarları güncelleme hatası: ' . $e->getMessage());
            $this->jsonResponse(500, null, 'Ayarlar güncellenirken bir hata oluştu: ' . $e->getMessage());
        }
    }
    
    /**
     * Parçayı beğenme/beğenmeme API endpointi
     * 
     * @return void
     */
    private function toggleLike() {
        // Kullanıcı oturumu kontrolü
        if (!$this->user) {
            $this->jsonResponse(401, null, 'Unauthorized');
        }
        
        // İstek parametrelerini al
        $trackId = filter_input(INPUT_POST, 'track_id', FILTER_VALIDATE_INT);
        
        if (!$trackId) {
            $this->jsonResponse(400, null, 'Geçersiz parça ID');
        }
        
        try {
            $musicModel = new \Models\Music($this->db);
            $result = $musicModel->toggleLike($trackId, $this->user['id']);
            
            $this->jsonResponse(200, [
                'track_id' => $trackId,
                'is_liked' => $result
            ], $result ? 'Parça beğenildi' : 'Parça beğenilmekten çıkarıldı');
            
        } catch (\Exception $e) {
            $this->logError('Beğeni değiştirme hatası: ' . $e->getMessage());
            $this->jsonResponse(500, null, 'İşlem sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }
}
