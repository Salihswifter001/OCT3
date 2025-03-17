<?php
/**
 * Octaverum - AI Müzik Uygulaması
 * Parça Yönetim Sınıfı
 */

require_once 'config.php';
require_once 'database_connection.php';
require_once 'utils.php';
require_once 'auth.php';

class TrackManager {
    private $db;
    private $auth;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->auth = new Auth();
    }
    
    /**
     * Yeni parça oluşturma
     * @param array $trackData
     * @return array
     */
    public function createTrack($trackData) {
        // Kullanıcı giriş kontrolü
        if (!$this->auth->isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Bu işlem için giriş yapmalısınız'
            ];
        }
        
        // Gerekli alanları kontrol et
        $requiredFields = ['title', 'prompt', 'file_url'];
        foreach ($requiredFields as $field) {
            if (!isset($trackData[$field]) || empty($trackData[$field])) {
                return [
                    'success' => false,
                    'message' => 'Lütfen tüm gerekli alanları doldurun'
                ];
            }
        }
        
        // Veri temizleme
        $title = filterInput($trackData['title']);
        $prompt = filterInput($trackData['prompt']);
        $fileUrl = filterInput($trackData['file_url']);
        $duration = isset($trackData['duration']) ? filterInput($trackData['duration']) : '00:00';
        $genre = isset($trackData['genre']) ? filterInput($trackData['genre']) : '';
        $taskId = isset($trackData['task_id']) ? filterInput($trackData['task_id']) : '';
        $lyrics = isset($trackData['lyrics']) ? filterInput($trackData['lyrics']) : '';
        $coverUrl = isset($trackData['cover_url']) ? filterInput($trackData['cover_url']) : '';
        $isPublic = isset($trackData['is_public']) ? (bool)$trackData['is_public'] : false;
        
        // Kullanıcı kimliğini al
        $userId = $_SESSION['user_id'];
        
        // Parça verisi hazırla
        $track = [
            'user_id' => $userId,
            'title' => $title,
            'prompt' => $prompt,
            'file_url' => $fileUrl,
            'duration' => $duration,
            'genre' => $genre,
            'task_id' => $taskId,
            'lyrics' => $lyrics,
            'cover_url' => $coverUrl,
            'is_public' => $isPublic ? 1 : 0,
            'artist' => $_SESSION['username'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'plays' => 0,
            'likes' => 0,
            'status' => 'active'
        ];
        
        // Parçayı veritabanına ekle
        $trackId = $this->db->insert('tracks', $track);
        
        if (!$trackId) {
            return [
                'success' => false,
                'message' => 'Parça oluşturulurken bir hata oluştu'
            ];
        }
        
        // Başarılı
        return [
            'success' => true,
            'message' => 'Parça başarıyla oluşturuldu',
            'track_id' => $trackId
        ];
    }
    
    /**
     * Parça güncelleme
     * @param int $trackId
     * @param array $trackData
     * @return array
     */
    public function updateTrack($trackId, $trackData) {
        // Kullanıcı giriş kontrolü
        if (!$this->auth->isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Bu işlem için giriş yapmalısınız'
            ];
        }
        
        // Parça bilgilerini al
        $track = $this->getTrackById($trackId);
        
        if (!$track) {
            return [
                'success' => false,
                'message' => 'Parça bulunamadı'
            ];
        }
        
        // Sadece parça sahibi veya yönetici güncelleyebilir
        if ($track['user_id'] != $_SESSION['user_id'] && !$this->auth->isAdmin()) {
            return [
                'success' => false,
                'message' => 'Bu parçayı güncelleme yetkiniz yok'
            ];
        }
        
        // Güncellenebilir alanlar
        $allowedFields = ['title', 'genre', 'lyrics', 'cover_url', 'is_public'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($trackData[$field])) {
                if ($field === 'is_public') {
                    $updateData[$field] = (bool)$trackData[$field] ? 1 : 0;
                } else {
                    $updateData[$field] = filterInput($trackData[$field]);
                }
            }
        }
        
        // Güncelleme zamanını ekle
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        // Parçayı güncelle
        $updated = $this->db->update('tracks', $updateData, ['id' => $trackId]);
        
        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Parça güncellenirken bir hata oluştu'
            ];
        }
        
        // Başarılı
        return [
            'success' => true,
            'message' => 'Parça başarıyla güncellendi'
        ];
    }
    
    /**
     * Parça silme
     * @param int $trackId
     * @return array
     */
    public function deleteTrack($trackId) {
        // Kullanıcı giriş kontrolü
        if (!$this->auth->isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Bu işlem için giriş yapmalısınız'
            ];
        }
        
        // Parça bilgilerini al
        $track = $this->getTrackById($trackId);
        
        if (!$track) {
            return [
                'success' => false,
                'message' => 'Parça bulunamadı'
            ];
        }
        
        // Sadece parça sahibi veya yönetici silebilir
        if ($track['user_id'] != $_SESSION['user_id'] && !$this->auth->isAdmin()) {
            return [
                'success' => false,
                'message' => 'Bu parçayı silme yetkiniz yok'
            ];
        }
        
        // Dosya yolunu al
        $fileUrl = $track['file_url'];
        $coverUrl = $track['cover_url'];
        
        // Veritabanından parçayı sil (veya pasif yap)
        $deleted = $this->db->update('tracks', [
            'status' => 'deleted',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $trackId]);
        
        if (!$deleted) {
            return [
                'success' => false,
                'message' => 'Parça silinirken bir hata oluştu'
            ];
        }
        
        // Çalma listelerinden parçayı kaldır
        $this->db->delete('playlist_tracks', ['track_id' => $trackId]);
        
        // Beğenilen parçalardan kaldır
        $this->db->delete('liked_tracks', ['track_id' => $trackId]);
        
        // Başarılı
        return [
            'success' => true,
            'message' => 'Parça başarıyla silindi'
        ];
    }
    
    /**
     * ID ile parça bilgilerini getir
     * @param int $trackId
     * @return array|false
     */
    public function getTrackById($trackId) {
        $track = $this->db->select('tracks', ['id' => $trackId]);
        
        if (empty($track)) {
            return false;
        }
        
        return $track[0];
    }
    
    /**
     * Kullanıcının tüm parçalarını getir
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @param string $orderBy
     * @return array
     */
    public function getUserTracks($userId, $limit = 10, $offset = 0, $orderBy = 'created_at DESC') {
        return $this->db->select(
            'tracks',
            ['user_id' => $userId, 'status' => 'active'],
            '*',
            $orderBy,
            $limit,
            $offset
        );
    }
    
    /**
     * Parça dinleme sayısını artır
     * @param int $trackId
     * @return boolean
     */
    public function incrementPlayCount($trackId) {
        // Parça bilgilerini al
        $track = $this->getTrackById($trackId);
        
        if (!$track) {
            return false;
        }
        
        // Oynatma sayısını artır
        $plays = $track['plays'] + 1;
        $updated = $this->db->update('tracks', ['plays' => $plays], ['id' => $trackId]);
        
        // Oynatma geçmişine ekle
        if ($this->auth->isLoggedIn()) {
            $userId = $_SESSION['user_id'];
            
            // Kullanıcının dinleme geçmişine ekle
            $this->db->insert('play_history', [
                'user_id' => $userId,
                'track_id' => $trackId,
                'played_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        return $updated;
    }
    
    /**
     * Parçayı beğen/beğenme
     * @param int $trackId
     * @param bool $like true = beğen, false = beğenme
     * @return array
     */
    public function toggleLike($trackId, $like = true) {
        // Kullanıcı giriş kontrolü
        if (!$this->auth->isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Bu işlem için giriş yapmalısınız'
            ];
        }
        
        $userId = $_SESSION['user_id'];
        
        // Parça bilgilerini al
        $track = $this->getTrackById($trackId);
        
        if (!$track) {
            return [
                'success' => false,
                'message' => 'Parça bulunamadı'
            ];
        }
        
        // Beğenilmiş mi kontrol et
        $isLiked = $this->db->select('liked_tracks', [
            'user_id' => $userId,
            'track_id' => $trackId
        ]);
        
        $currentLikes = $track['likes'];
        
        if ($like) {
            // Beğen
            if (empty($isLiked)) {
                // Beğeniler tablosuna ekle
                $this->db->insert('liked_tracks', [
                    'user_id' => $userId,
                    'track_id' => $trackId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                // Beğeni sayısını artır
                $currentLikes++;
                $this->db->update('tracks', ['likes' => $currentLikes], ['id' => $trackId]);
                
                return [
                    'success' => true,
                    'message' => 'Parça beğenildi',
                    'likes' => $currentLikes
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Parça zaten beğenilmiş',
                'likes' => $currentLikes
            ];
        } else {
            // Beğenmekten vazgeç
            if (!empty($isLiked)) {
                // Beğeniler tablosundan kaldır
                $this->db->delete('liked_tracks', [
                    'user_id' => $userId,
                    'track_id' => $trackId
                ]);
                
                // Beğeni sayısını azalt
                $currentLikes = max(0, $currentLikes - 1);
                $this->db->update('tracks', ['likes' => $currentLikes], ['id' => $trackId]);
                
                return [
                    'success' => true,
                    'message' => 'Parça beğenisi kaldırıldı',
                    'likes' => $currentLikes
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Parça zaten beğenilmemiş',
                'likes' => $currentLikes
            ];
        }
    }
    
    /**
     * Parçanın beğenilip beğenilmediğini kontrol et
     * @param int $trackId
     * @param int $userId
     * @return boolean
     */
    public function isTrackLiked($trackId, $userId = null) {
        if (!$userId && $this->auth->isLoggedIn()) {
            $userId = $_SESSION['user_id'];
        }
        
        if (!$userId) {
            return false;
        }
        
        $isLiked = $this->db->select('liked_tracks', [
            'user_id' => $userId,
            'track_id' => $trackId
        ]);
        
        return !empty($isLiked);
    }
    
    /**
     * Tüm parçaları getir (filtreleme ile)
     * @param array $filters Filtreler: genre, search, order, public_only
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getTracks($filters = [], $limit = 10, $offset = 0) {
        $where = ['status' => 'active'];
        $params = [];
        $orderBy = 'created_at DESC';
        
        // Sadece halka açık parçaları getir
        if (isset($filters['public_only']) && $filters['public_only']) {
            $where['is_public'] = 1;
        }
        
        // Türe göre filtrele
        if (isset($filters['genre']) && !empty($filters['genre'])) {
            $where['genre'] = $filters['genre'];
        }
        
        // Arama sorgusu
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            
            // WHERE kısmını SQL sorgusunda manuel oluştur
            $whereStr = "status = :status";
            $params['status'] = 'active';
            
            if (isset($where['is_public'])) {
                $whereStr .= " AND is_public = :is_public";
                $params['is_public'] = $where['is_public'];
            }
            
            if (isset($where['genre'])) {
                $whereStr .= " AND genre = :genre";
                $params['genre'] = $where['genre'];
            }
            
            $whereStr .= " AND (title LIKE :search OR artist LIKE :search OR genre LIKE :search)";
            $params['search'] = $search;
            
            // Özel SQL sorgusu
            $query = "SELECT * FROM " . $this->db->getTableName('tracks') . " WHERE " . $whereStr;
            
            // Sıralama
            if (isset($filters['order'])) {
                switch ($filters['order']) {
                    case 'newest':
                        $orderBy = 'created_at DESC';
                        break;
                    case 'oldest':
                        $orderBy = 'created_at ASC';
                        break;
                    case 'popular':
                        $orderBy = 'plays DESC';
                        break;
                    case 'likes':
                        $orderBy = 'likes DESC';
                        break;
                    case 'title':
                        $orderBy = 'title ASC';
                        break;
                }
            }
            
            $query .= " ORDER BY " . $orderBy;
            
            // Limit ve Offset
            if ($limit > 0) {
                $query .= " LIMIT " . $limit;
                if ($offset > 0) {
                    $query .= " OFFSET " . $offset;
                }
            }
            
            return $this->db->query($query, $params)->fetchAll();
        }
        
        // Sıralama
        if (isset($filters['order'])) {
            switch ($filters['order']) {
                case 'newest':
                    $orderBy = 'created_at DESC';
                    break;
                case 'oldest':
                    $orderBy = 'created_at ASC';
                    break;
                case 'popular':
                    $orderBy = 'plays DESC';
                    break;
                case 'likes':
                    $orderBy = 'likes DESC';
                    break;
                case 'title':
                    $orderBy = 'title ASC';
                    break;
            }
        }
        
        return $this->db->select('tracks', $where, '*', $orderBy, $limit, $offset);
    }
    
    /**
     * Parça sayısını al (filtreleme ile)
     * @param array $filters
     * @return int
     */
    public function getTrackCount($filters = []) {
        $where = ['status' => 'active'];
        $params = [];
        
        // Sadece halka açık parçaları say
        if (isset($filters['public_only']) && $filters['public_only']) {
            $where['is_public'] = 1;
        }
        
        // Türe göre filtrele
        if (isset($filters['genre']) && !empty($filters['genre'])) {
            $where['genre'] = $filters['genre'];
        }
        
        // Arama sorgusu
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            
            // WHERE kısmını SQL sorgusunda manuel oluştur
            $whereStr = "status = :status";
            $params['status'] = 'active';
            
            if (isset($where['is_public'])) {
                $whereStr .= " AND is_public = :is_public";
                $params['is_public'] = $where['is_public'];
            }
            
            if (isset($where['genre'])) {
                $whereStr .= " AND genre = :genre";
                $params['genre'] = $where['genre'];
            }
            
            $whereStr .= " AND (title LIKE :search OR artist LIKE :search OR genre LIKE :search)";
            $params['search'] = $search;
            
            // Sorgu
            $query = "SELECT COUNT(*) FROM " . $this->db->getTableName('tracks') . " WHERE " . $whereStr;
            
            return $this->db->query($query, $params)->fetchColumn();
        }
        
        // Standart sayma
        $query = "SELECT COUNT(*) FROM " . $this->db->getTableName('tracks') . " WHERE ";
        $conditions = [];
        
        foreach ($where as $key => $value) {
            $conditions[] = "$key = :$key";
            $params[$key] = $value;
        }
        
        $query .= implode(' AND ', $conditions);
        
        return $this->db->query($query, $params)->fetchColumn();
    }
    
    /**
     * Kullanıcının beğendiği parçaları getir
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLikedTracks($userId, $limit = 10, $offset = 0) {
        $query = "SELECT t.* FROM " . $this->db->getTableName('tracks') . " t
                  JOIN " . $this->db->getTableName('liked_tracks') . " lt ON t.id = lt.track_id
                  WHERE lt.user_id = :user_id AND t.status = 'active'
                  ORDER BY lt.created_at DESC";
        
        if ($limit > 0) {
            $query .= " LIMIT " . $limit;
            if ($offset > 0) {
                $query .= " OFFSET " . $offset;
            }
        }
        
        return $this->db->query($query, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Kullanıcının dinleme geçmişini getir
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getPlayHistory($userId, $limit = 10, $offset = 0) {
        $query = "SELECT t.*, MAX(ph.played_at) as last_played
                  FROM " . $this->db->getTableName('tracks') . " t
                  JOIN " . $this->db->getTableName('play_history') . " ph ON t.id = ph.track_id
                  WHERE ph.user_id = :user_id AND t.status = 'active'
                  GROUP BY t.id
                  ORDER BY last_played DESC";
        
        if ($limit > 0) {
            $query .= " LIMIT " . $limit;
            if ($offset > 0) {
                $query .= " OFFSET " . $offset;
            }
        }
        
        return $this->db->query($query, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * En çok dinlenen parçaları getir
     * @param int $limit
     * @param int $offset
     * @param bool $publicOnly
     * @return array
     */
    public function getPopularTracks($limit = 10, $offset = 0, $publicOnly = true) {
        $where = ['status' => 'active'];
        
        if ($publicOnly) {
            $where['is_public'] = 1;
        }
        
        return $this->db->select('tracks', $where, '*', 'plays DESC', $limit, $offset);
    }
    
    /**
     * En çok beğenilen parçaları getir
     * @param int $limit
     * @param int $offset
     * @param bool $publicOnly
     * @return array
     */
    public function getMostLikedTracks($limit = 10, $offset = 0, $publicOnly = true) {
        $where = ['status' => 'active'];
        
        if ($publicOnly) {
            $where['is_public'] = 1;
        }
        
        return $this->db->select('tracks', $where, '*', 'likes DESC', $limit, $offset);
    }
    
    /**
     * Rastgele parçalar getir
     * @param int $limit
     * @param bool $publicOnly
     * @param string $genre (isteğe bağlı)
     * @return array
     */
    public function getRandomTracks($limit = 10, $publicOnly = true, $genre = '') {
        $where = ['status' => 'active'];
        $params = [];
        
        if ($publicOnly) {
            $where['is_public'] = 1;
        }
        
        $whereStr = '';
        foreach ($where as $key => $value) {
            if (!empty($whereStr)) {
                $whereStr .= ' AND ';
            }
            $whereStr .= "$key = :$key";
            $params[$key] = $value;
        }
        
        $query = "SELECT * FROM " . $this->db->getTableName('tracks') . " WHERE " . $whereStr;
        
        // Tür filtresi
        if (!empty($genre)) {
            $query .= " AND genre = :genre";
            $params['genre'] = $genre;
        }
        
        // Rastgele sıralama ve limit
        $query .= " ORDER BY RAND() LIMIT " . $limit;
        
        return $this->db->query($query, $params)->fetchAll();
    }
    
    /**
     * En son eklenen parçaları getir
     * @param int $limit
     * @param int $offset
     * @param bool $publicOnly
     * @return array
     */
    public function getLatestTracks($limit = 10, $offset = 0, $publicOnly = true) {
        $where = ['status' => 'active'];
        
        if ($publicOnly) {
            $where['is_public'] = 1;
        }
        
        return $this->db->select('tracks', $where, '*', 'created_at DESC', $limit, $offset);
    }
    
    /**
     * Tüm müzik türlerini getir
     * @return array
     */
    public function getAllGenres() {
        $query = "SELECT DISTINCT genre FROM " . $this->db->getTableName('tracks') . 
                 " WHERE genre != '' AND status = 'active'";
        
        $genres = $this->db->query($query)->fetchAll();
        
        // Sadece tür adını içeren dizi oluştur
        $genreList = [];
        foreach ($genres as $genre) {
            $genreList[] = $genre['genre'];
        }
        
        return $genreList;
    }
    
    /**
     * Tür başına parça sayısını getir
     * @return array
     */
    public function getTrackCountByGenre() {
        $query = "SELECT genre, COUNT(*) as count FROM " . $this->db->getTableName('tracks') . 
                 " WHERE genre != '' AND status = 'active'
                   GROUP BY genre
                   ORDER BY count DESC";
        
        return $this->db->query($query)->fetchAll();
    }
    
    /**
     * Çalma listesi oluşturma
     * @param string $name
     * @param int $userId
     * @param string $description
     * @param string $coverUrl
     * @param bool $isPublic
     * @return array
     */
    public function createPlaylist($name, $userId, $description = '', $coverUrl = '', $isPublic = false) {
        // Kullanıcı giriş kontrolü
        if (!$this->auth->isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Bu işlem için giriş yapmalısınız'
            ];
        }
        
        // Çalma listesi adı kontrolü
        if (empty($name)) {
            return [
                'success' => false,
                'message' => 'Çalma listesi adı gereklidir'
            ];
        }
        
        // Veri temizleme
        $name = filterInput($name);
        $description = filterInput($description);
        $coverUrl = filterInput($coverUrl);
        
        // Çalma listesi verilerini hazırla
        $playlist = [
            'name' => $name,
            'user_id' => $userId,
            'description' => $description,
            'cover_url' => $coverUrl,
            'is_public' => $isPublic ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Çalma listesini veritabanına ekle
        $playlistId = $this->db->insert('playlists', $playlist);
        
        if (!$playlistId) {
            return [
                'success' => false,
                'message' => 'Çalma listesi oluşturulurken bir hata oluştu'
            ];
        }
        
        // Başarılı
        return [
            'success' => true,
            'message' => 'Çalma listesi başarıyla oluşturuldu',
            'playlist_id' => $playlistId
        ];
    }
    
    /**
     * Çalma listesine parça ekleme
     * @param int $playlistId
     * @param int $trackId
     * @return array
     */
    public function addTrackToPlaylist($playlistId, $trackId) {
        // Kullanıcı giriş kontrolü
        if (!$this->auth->isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Bu işlem için giriş yapmalısınız'
            ];
        }
        
        // Çalma listesi kontrolü
        $playlist = $this->getPlaylistById($playlistId);
        
        if (!$playlist) {
            return [
                'success' => false,
                'message' => 'Çalma listesi bulunamadı'
            ];
        }
        
        // Sadece çalma listesi sahibi veya yönetici parça ekleyebilir
        if ($playlist['user_id'] != $_SESSION['user_id'] && !$this->auth->isAdmin()) {
            return [
                'success' => false,
                'message' => 'Bu çalma listesine parça ekleme yetkiniz yok'
            ];
        }
        
        // Parça kontrolü
        $track = $this->getTrackById($trackId);
        
        if (!$track) {
            return [
                'success' => false,
                'message' => 'Parça bulunamadı'
            ];
        }
        
        // Parça zaten eklenmiş mi kontrol et
        $existing = $this->db->select('playlist_tracks', [
            'playlist_id' => $playlistId,
            'track_id' => $trackId
        ]);
        
        if (!empty($existing)) {
            return [
                'success' => true,
                'message' => 'Bu parça zaten çalma listesinde bulunuyor'
            ];
        }
        
        // Sıra numarasını belirle
        $query = "SELECT MAX(sort_order) as max_order FROM " . $this->db->getTableName('playlist_tracks') . 
                 " WHERE playlist_id = :playlist_id";
        
        $maxOrder = $this->db->query($query, ['playlist_id' => $playlistId])->fetchOne();
        $sortOrder = $maxOrder && isset($maxOrder['max_order']) ? $maxOrder['max_order'] + 1 : 1;
        
        // Parçayı çalma listesine ekle
        $added = $this->db->insert('playlist_tracks', [
            'playlist_id' => $playlistId,
            'track_id' => $trackId,
            'sort_order' => $sortOrder,
            'added_at' => date('Y-m-d H:i:s')
        ]);
        
        if (!$added) {
            return [
                'success' => false,
                'message' => 'Parça çalma listesine eklenirken bir hata oluştu'
            ];
        }
        
        // Çalma listesi son güncelleme zamanını güncelle
        $this->db->update('playlists', [
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $playlistId]);
        
        // Başarılı
        return [
            'success' => true,
            'message' => 'Parça çalma listesine eklendi'
        ];
    }
    
    /**
     * Çalma listesinden parça kaldırma
     * @param int $playlistId
     * @param int $trackId
     * @return array
     */
    public function removeTrackFromPlaylist($playlistId, $trackId) {
        // Kullanıcı giriş kontrolü
        if (!$this->auth->isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Bu işlem için giriş yapmalısınız'
            ];
        }
        
        // Çalma listesi kontrolü
        $playlist = $this->getPlaylistById($playlistId);
        
        if (!$playlist) {
            return [
                'success' => false,
                'message' => 'Çalma listesi bulunamadı'
            ];
        }
        
        // Sadece çalma listesi sahibi veya yönetici parça kaldırabilir
        if ($playlist['user_id'] != $_SESSION['user_id'] && !$this->auth->isAdmin()) {
            return [
                'success' => false,
                'message' => 'Bu çalma listesinden parça kaldırma yetkiniz yok'
            ];
        }
        
        // Parçayı çalma listesinden kaldır
        $removed = $this->db->delete('playlist_tracks', [
            'playlist_id' => $playlistId,
            'track_id' => $trackId
        ]);
        
        if (!$removed) {
            return [
                'success' => false,
                'message' => 'Parça çalma listesinden kaldırılırken bir hata oluştu'
            ];
        }
        
        // Çalma listesi son güncelleme zamanını güncelle
        $this->db->update('playlists', [
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $playlistId]);
        
        // Başarılı
        return [
            'success' => true,
            'message' => 'Parça çalma listesinden kaldırıldı'
        ];
    }
    
    /**
     * Çalma listesi güncelleme
     * @param int $playlistId
     * @param array $playlistData
     * @return array
     */
    public function updatePlaylist($playlistId, $playlistData) {
        // Kullanıcı giriş kontrolü
        if (!$this->auth->isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Bu işlem için giriş yapmalısınız'
            ];
        }
        
        // Çalma listesi kontrolü
        $playlist = $this->getPlaylistById($playlistId);
        
        if (!$playlist) {
            return [
                'success' => false,
                'message' => 'Çalma listesi bulunamadı'
            ];
        }
        
        // Sadece çalma listesi sahibi veya yönetici güncelleyebilir
        if ($playlist['user_id'] != $_SESSION['user_id'] && !$this->auth->isAdmin()) {
            return [
                'success' => false,
                'message' => 'Bu çalma listesini güncelleme yetkiniz yok'
            ];
        }
        
        // Güncellenebilir alanlar
        $allowedFields = ['name', 'description', 'cover_url', 'is_public'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($playlistData[$field])) {
                if ($field === 'is_public') {
                    $updateData[$field] = (bool)$playlistData[$field] ? 1 : 0;
                } else {
                    $updateData[$field] = filterInput($playlistData[$field]);
                }
            }
        }
        
        // Çalma listesi adı boş olamaz
        if (isset($updateData['name']) && empty($updateData['name'])) {
            return [
                'success' => false,
                'message' => 'Çalma listesi adı boş olamaz'
            ];
        }
        
        // Güncelleme zamanını ekle
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        // Çalma listesini güncelle
        $updated = $this->db->update('playlists', $updateData, ['id' => $playlistId]);
        
        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Çalma listesi güncellenirken bir hata oluştu'
            ];
        }
        
        // Başarılı
        return [
            'success' => true,
            'message' => 'Çalma listesi başarıyla güncellendi'
        ];
    }
    
    /**
     * Çalma listesi silme
     * @param int $playlistId
     * @return array
     */
    public function deletePlaylist($playlistId) {
        // Kullanıcı giriş kontrolü
        if (!$this->auth->isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'Bu işlem için giriş yapmalısınız'
            ];
        }
        
        // Çalma listesi kontrolü
        $playlist = $this->getPlaylistById($playlistId);
        
        if (!$playlist) {
            return [
                'success' => false,
                'message' => 'Çalma listesi bulunamadı'
            ];
        }
        
        // Sadece çalma listesi sahibi veya yönetici silebilir
        if ($playlist['user_id'] != $_SESSION['user_id'] && !$this->auth->isAdmin()) {
            return [
                'success' => false,
                'message' => 'Bu çalma listesini silme yetkiniz yok'
            ];
        }
        
        // Çalma listesini sil
        $deleted = $this->db->delete('playlists', ['id' => $playlistId]);
        
        if (!$deleted) {
            return [
                'success' => false,
                'message' => 'Çalma listesi silinirken bir hata oluştu'
            ];
        }
        
        // Çalma listesindeki parçaları sil
        $this->db->delete('playlist_tracks', ['playlist_id' => $playlistId]);
        
        // Başarılı
        return [
            'success' => true,
            'message' => 'Çalma listesi başarıyla silindi'
        ];
    }
    
    /**
     * ID ile çalma listesi bilgilerini getir
     * @param int $playlistId
     * @return array|false
     */
    public function getPlaylistById($playlistId) {
        $playlist = $this->db->select('playlists', ['id' => $playlistId]);
        
        if (empty($playlist)) {
            return false;
        }
        
        return $playlist[0];
    }
    
    /**
     * Kullanıcının çalma listelerini getir
     * @param int $userId
     * @return array
     */
    public function getUserPlaylists($userId) {
        return $this->db->select('playlists', ['user_id' => $userId], '*', 'name ASC');
    }
    
    /**
     * Çalma listesindeki parçaları getir
     * @param int $playlistId
     * @return array
     */
    public function getPlaylistTracks($playlistId) {
        $query = "SELECT t.*, pt.added_at, pt.sort_order
                  FROM " . $this->db->getTableName('tracks') . " t
                  JOIN " . $this->db->getTableName('playlist_tracks') . " pt ON t.id = pt.track_id
                  WHERE pt.playlist_id = :playlist_id AND t.status = 'active'
                  ORDER BY pt.sort_order ASC";
        
        return $this->db->query($query, ['playlist_id' => $playlistId])->fetchAll();
    }
    
    /**
     * Popüler çalma listelerini getir
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getPopularPlaylists($limit = 10, $offset = 0) {
        // Burada gerçek bir popülerlik hesaplaması yapılabilir
        // Şimdilik basit bir sorgu kullanıyoruz
        $query = "SELECT p.*, 
                  (SELECT COUNT(*) FROM " . $this->db->getTableName('playlist_tracks') . " pt WHERE pt.playlist_id = p.id) as track_count
                  FROM " . $this->db->getTableName('playlists') . " p
                  WHERE p.is_public = 1
                  ORDER BY track_count DESC";
        
        if ($limit > 0) {
            $query .= " LIMIT " . $limit;
            if ($offset > 0) {
                $query .= " OFFSET " . $offset;
            }
        }
        
        return $this->db->query($query)->fetchAll();
    }
    
    /**
     * Son eklenen çalma listelerini getir
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLatestPlaylists($limit = 10, $offset = 0) {
        $query = "SELECT p.*, 
                  (SELECT COUNT(*) FROM " . $this->db->getTableName('playlist_tracks') . " pt WHERE pt.playlist_id = p.id) as track_count
                  FROM " . $this->db->getTableName('playlists') . " p
                  WHERE p.is_public = 1
                  ORDER BY p.created_at DESC";
        
        if ($limit > 0) {
            $query .= " LIMIT " . $limit;
            if ($offset > 0) {
                $query .= " OFFSET " . $offset;
            }
        }
        
        return $this->db->query($query)->fetchAll();
    }
}
