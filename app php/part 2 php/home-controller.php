<?php
namespace Controllers;

/**
 * Octaverum AI - HomeController Sınıfı
 * 
 * Ana sayfa ve prompt generator sayfalarını yöneten Controller
 */
class HomeController extends BaseController {
    /**
     * Ayarlar yardımcısı
     * @var \Helpers\SettingsHelper
     */
    protected $settingsHelper;
    
    /**
     * Controller constructor
     * 
     * @param \PDO $db Veritabanı bağlantısı
     * @param \Helpers\SettingsHelper $settingsHelper
     */
    public function __construct($db, $settingsHelper) {
        parent::__construct($db);
        $this->settingsHelper = $settingsHelper;
        $this->pageTitle = 'Octaverum AI - Yapay Zeka ile Müzik Üretimi';
    }
    
    /**
     * Ana sayfa
     * 
     * @return void
     */
    public function index() {
        // Kullanıcı ilk kez giriş yapıyorsa karşılama modalını göster
        $showWelcomeModal = !isset($_COOKIE['welcome_modal_shown']);
        
        if ($showWelcomeModal) {
            // 1 yıl geçerli bir çerez oluştur
            setcookie('welcome_modal_shown', '1', time() + 60 * 60 * 24 * 365, '/');
        }
        
        // Kullanıcı ayarlarını al
        $userSettings = $this->user 
            ? $this->settingsHelper->getUserSettings($this->user['id']) 
            : $this->settingsHelper->getDefaultSettings();
        
        // Son 8 müzik parçasını getir
        $musicModel = new \Models\Music($this->db);
        $recentTracks = $this->user 
            ? $musicModel->getRecentTracksByUser($this->user['id'], 8) 
            : $musicModel->getSampleTracks(8);
        
        // View'a veri gönder
        $this->render('home/index', [
            'showWelcomeModal' => $showWelcomeModal,
            'userSettings' => $userSettings,
            'recentTracks' => $recentTracks,
            'genres' => $this->getGenres()
        ]);
    }
    
    /**
     * Müzik oluşturma sayfası
     * 
     * @return void
     */
    public function createMusic() {
        // Kullanıcı giriş yapmamışsa ana sayfaya yönlendir
        if (!$this->user) {
            $this->redirect('user', 'login');
        }
        
        // Kullanıcının ayarlarını al
        $userSettings = $this->settingsHelper->getUserSettings($this->user['id']);
        
        // Kullanıcının oluşturma limitini kontrol et
        $musicModel = new \Models\Music($this->db);
        $userTrackCount = $musicModel->getMonthlyTrackCountByUser($this->user['id']);
        
        // Kullanıcının planına göre limit
        $userPlan = $this->user['subscription_plan'] ?? 'free';
        $planLimits = SUBSCRIPTION_PLANS[$userPlan]['track_limit'] ?? 10;
        
        // Pro plan için limit yok
        $limitReached = $userPlan !== 'pro' && $userTrackCount >= $planLimits;
        
        // View'a veri gönder
        $this->render('home/create_music', [
            'userSettings' => $userSettings,
            'trackCount' => $userTrackCount,
            'trackLimit' => $planLimits,
            'limitReached' => $limitReached,
            'genres' => $this->getGenres()
        ]);
    }
    
    /**
     * Müzik türlerini döndürür
     * 
     * @return array
     */
    private function getGenres() {
        return [
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
            ['id' => 20, 'name' => 'Reggae', 'isHot' => false]
        ];
    }
    
    /**
     * Hakkında sayfası
     * 
     * @return void
     */
    public function about() {
        $this->pageTitle = 'Hakkımızda - Octaverum AI';
        $this->render('home/about');
    }
    
    /**
     * İletişim sayfası
     * 
     * @return void
     */
    public function contact() {
        $this->pageTitle = 'İletişim - Octaverum AI';
        
        // Form verilerini işle
        $message = '';
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
            $content = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
            
            // Basit validasyon
            if (empty($name) || empty($email) || empty($subject) || empty($content)) {
                $error = 'Lütfen tüm alanları doldurun.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Lütfen geçerli bir e-posta adresi girin.';
            } else {
                // İletişim formu verisini veritabanına kaydet
                try {
                    $stmt = $this->db->prepare("
                        INSERT INTO contact_messages (name, email, subject, message, user_id, created_at) 
                        VALUES (:name, :email, :subject, :message, :user_id, NOW())
                    ");
                    
                    $userId = $this->user ? $this->user['id'] : null;
                    
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':subject', $subject);
                    $stmt->bindParam(':message', $content);
                    $stmt->bindParam(':user_id', $userId);
                    
                    $stmt->execute();
                    
                    $message = 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.';
                    
                    // Yöneticiye bildirim e-postası gönderilebilir
                    // mailHelper->sendNotification('yeni iletişim mesajı', $content);
                    
                } catch (\PDOException $e) {
                    $this->logError('İletişim formu kaydedilirken hata: ' . $e->getMessage());
                    $error = 'Mesajınız gönderilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
                }
            }
        }
        
        $this->render('home/contact', [
            'message' => $message,
            'error' => $error
        ]);
    }
}
