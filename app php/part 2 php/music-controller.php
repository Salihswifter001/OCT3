<?php
namespace Controllers;

/**
 * Octaverum AI - MusicController Sınıfı
 * 
 * Müzik kütüphanesi, parçalar ve çalma listeleri için Controller
 */
class MusicController extends BaseController {
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
        $this->pageTitle = 'Müzik Kütüphanesi - Octaverum AI';
    }
    
    /**
     * Müzik kütüphanesi (library) sayfası
     * 
     * @return void
     */
    public function index() {
        // Kullanıcı giriş yapmamışsa ana sayfaya yönlendir
        if (!$this->user) {
            $this->redirect('user', 'login');
        }
        
        $musicModel = new \Models\Music($this->db);
        
        // Kullanıcının son dinlediği parçaları al
        $recentTracks = $musicModel->getRecentTracksByUser($this->user['id'], 8);
        
        // Kullanıcının beğendiği parçaları al
        $likedTracks = $musicModel->getLikedTracksByUser($this->user['id']);
        
        // Kullanıcının oluşturduğu parçaları al
        $createdTracks = $musicModel->getTracksByUser($this->user['id']);
        
        // Kullanıcının çalma listelerini al
        $playlistModel = new \Models\Playlist($this->db);
        $playlists = $playlistModel->getPlaylistsByUser($this->user['id']);
        
        $this->render('music/library', [
            'recentTracks' => $recentTracks,
            'likedTracks' => $likedTracks,
            'createdTracks' => $createdTracks,
            'playlists' => $playlists
        ]);
    }
    
    /**
     * Son dinlenen parçalar sayfası
     * 
     * @return void
     */
    public function recentTracks() {
        if (!$this->user) {
            $this->redirect('user', 'login');
        }
        
        $musicModel = new \Models\Music($this->db);
        $tracks = $musicModel->getRecentTracksByUser($this->user['id'], 50);
        
        $this->pageTitle = 'Son Dinlenenler - Octaverum AI';
        
        $this->render('music/recent_tracks', [
            'tracks' => $tracks
        ]);
    }
    
    /**
     * Beğenilen parçalar sayfası
     * 
     * @return void
     */
    public function likedTracks() {
        if (!$this->user) {
            $this->redirect('user', 'login');
        }
        
        $musicModel = new \Models\Music($this->db);
        $tracks = $musicModel->getLikedTracksByUser($this->user['id']);
        
        $this->pageTitle = 'Beğenilen Şarkılar - Octaverum AI';
        
        $this->render('music/liked_tracks', [
            'tracks' => $tracks
        ]);
    }
    
    /**
     * Oluşturulan parçalar sayfası
     * 
     * @return void
     */
    public function createdTracks() {
        if (!$this->user) {
            $this->redirect('user', 'login');
        }
        
        $musicModel = new \Models\Music($this->db);
        $tracks = $musicModel->getTracksByUser($this->user['id']);
        
        $this->pageTitle = 'Oluşturduğum Şarkılar - Octaverum AI';
        
        $this->render('music/created_tracks', [
            'tracks' => $tracks
        ]);
    }
    
    /**
     * Çalma listeleri sayfası
     * 
     * @return void
     */
    public function playlists() {
        if (!$this->user) {
            $this->redirect('user', 'login');
        }
        
        $playlistModel = new \Models\Playlist($this->db);
        $playlists = $playlistModel->getPlaylistsByUser($this->user['id']);
        
        $this->pageTitle = 'Çalma Listelerim - Octaverum AI';
        
        $this->render('music/playlists', [
            'playlists' => $playlists
        ]);
    }
    
    /**
     * Çalma listesi detay sayfası
     * 
     * @param int $id Çalma listesi ID'si
     * @return void
     */
    public function playlist($id) {
        if (!$this->user) {
            $this->redirect('user', 'login');
        }
        
        $playlistModel = new \Models\Playlist($this->db);
        $playlist = $playlistModel->getPlaylistById($id);
        
        // Çalma listesi bulunamazsa veya kullanıcıya ait değilse
        if (!$playlist || $playlist['user_id'] != $this->user['id']) {
            $this->redirect('music', 'playlists');
        }
        
        // Çalma listesindeki parçaları al
        $tracks = $playlistModel->getPlaylistTracks($id);
        
        $this->pageTitle = $playlist['name'] . ' - Octaverum AI';
        
        $this->render('music/playlist_detail', [
            'playlist' => $playlist,
            'tracks' => $tracks
        ]);
    }
    
    /**
     * Yeni çalma listesi oluşturma sayfası
     * 
     * @return void
     */
    public function createPlaylist() {
        if (!$this->user) {
            $this->redirect('user', 'login');
        }
        
        $message = '';
        $error = '';
        
        // POST verisini işle
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            $trackIds = isset($_POST['track_ids']) ? $_POST['track_ids'] : [];
            
            if (empty($name)) {
                $error = 'Lütfen çalma listesi için bir isim girin.';
            } else {
                $playlistModel = new \Models\Playlist($this->db);
                
                try {
                    // Önce çalma listesini oluştur
                    $playlistId = $playlistModel->createPlaylist($this->user['id'], $name, $description);
                    
                    // Parçaları çalma listesine ekle
                    if ($playlistId && !empty($trackIds)) {
                        foreach ($trackIds as $trackId) {
                            $playlistModel->addTrackToPlaylist($playlistId, $trackId);
                        }
                    }
                    
                    // Başarılı mesajı
                    $message = 'Çalma listesi başarıyla oluşturuldu.';
                    
                    // Yeni oluşturulan çalma listesine yönlendir
                    $this->redirect('music', 'playlist', $playlistId);
                } catch (\Exception $e) {
                    $this->logError('Çalma listesi oluşturulurken hata: ' . $e->getMessage());
                    $error = 'Çalma listesi oluşturulurken bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
                }
            }
        }
        
        // Müzik kütüphanesindeki tüm parçaları al
        $musicModel = new \Models\Music($this->db);
        $allTracks = $musicModel->getAllAccessibleTracks($this->user['id']);
        
        $this->pageTitle = 'Yeni Çalma Listesi - Octaverum AI';
        
        $this->render('music/create_playlist', [
            'message' => $message,
            'error' => $error,
            'tracks' => $allTracks
        ]);
    }
    
    /**
     * Parça detay sayfası
     * 
     * @param int $id Parça ID'si
     * @return void
     */
    public function track($id) {
        $musicModel = new \Models\Music($this->db);
        $track = $musicModel->getTrackById($id);
        
        // Parça bulunamazsa veya erişilemezse
        if (!$track || ($track['is_private'] && (!$this->user || $track['user_id'] != $this->user['id']))) {
            $this->redirect('music', 'index');
        }
        
        // Parçayı dinlendi olarak işaretle
        if ($this->user) {
            $musicModel->recordPlayback($id, $this->user['id']);
        }
        
        // Benzer parçaları getir
        $similarTracks = $musicModel->getSimilarTracks($id, 6);
        
        $this->pageTitle = $track['title'] . ' - ' . $track['artist'] . ' | Octaverum AI';
        
        $this->render('music/track_detail', [
            'track' => $track,
            'similarTracks' => $similarTracks
        ]);
    }
    
    /**
     * Parçayı beğenme/beğenmeme AJAX endpointi
     * 
     * @return void
     */
    public function toggleLike() {
        if (!$this->user) {
            $this->jsonResponse(401, null, 'Oturum açmanız gerekiyor');
        }
        
        // AJAX isteği değilse reddet
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            $this->redirect('music', 'index');
        }
        
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
            $this->jsonResponse(500, null, 'İşlem sırasında bir hata oluştu');
        }
    }
    
    /**
     * Müzik türleri sayfası
     * 
     * @return void
     */
    public function genres() {
        $musicModel = new \Models\Music($this->db);
        $genres = $musicModel->getAllGenres();
        
        $this->pageTitle = 'Müzik Türleri - Octaverum AI';
        
        $this->render('music/genres', [
            'genres' => $genres
        ]);
    }
    
    /**
     * Belirli bir türdeki parçaları listeleyen sayfa
     * 
     * @param string $genre Tür adı
     * @return void
     */
    public function genre($genre) {
        $musicModel = new \Models\Music($this->db);
        $tracks = $musicModel->getTracksByGenre($genre);
        
        $this->pageTitle = ucfirst($genre) . ' - Octaverum AI';
        
        $this->render('music/genre_tracks', [
            'genre' => $genre,
            'tracks' => $tracks
        ]);
    }
}
