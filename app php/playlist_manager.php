<?php
/**
 * Octaverum - Çalma Listesi Yöneticisi
 * 
 * Çalma listelerini yönetmek için sınıf
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

class PlaylistManager {
    private static $instance = null;
    private $db;
    
    /**
     * PlaylistManager singleton örneğini oluşturur
     */
    private function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Singleton örneğini döndürür
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Kullanıcının tüm çalma listelerini getirir
     * 
     * @param int $userId Kullanıcı ID'si
     * @return array Çalma listeleri
     */
    public function getUserPlaylists($userId) {
        $this->db->prepare(
            "SELECT p.*, 
                    COUNT(pt.id) as track_count,
                    MAX(t.updated_at) as last_updated
             FROM playlists p
             LEFT JOIN playlist_tracks pt ON p.id = pt.playlist_id
             LEFT JOIN tracks t ON pt.track_id = t.id
             WHERE p.user_id = :user_id
             GROUP BY p.id
             ORDER BY p.created_at DESC",
            ['user_id' => $userId]
        );
        
        return $this->db->fetchAll();
    }
    
    /**
     * Belirli bir çalma listesini getirir
     * 
     * @param int $playlistId Çalma listesi ID'si
     * @param int $userId Kullanıcı ID'si
     * @return array|bool Çalma listesi veya false
     */
    public function getPlaylist($playlistId, $userId = null) {
        $userCondition = $userId ? "AND p.user_id = :user_id" : "";
        $params = ['id' => $playlistId];
        
        if ($userId) {
            $params['user_id'] = $userId;
        }
        
        $this->db->prepare(
            "SELECT p.*, 
                    COUNT(pt.id) as track_count,
                    MAX(t.updated_at) as last_updated
             FROM playlists p
             LEFT JOIN playlist_tracks pt ON p.id = pt.playlist_id
             LEFT JOIN tracks t ON pt.track_id = t.id
             WHERE p.id = :id $userCondition
             GROUP BY p.id",
            $params
        );
        
        return $this->db->fetch();
    }
    
    /**
     * Çalma listesinin parçalarını getirir
     * 
     * @param int $playlistId Çalma listesi ID'si
     * @param int $userId Kullanıcı ID'si
     * @return array Parçalar
     */
    public function getPlaylistTracks($playlistId, $userId = null) {
        $userCondition = $userId ? "AND p.user_id = :user_id" : "";
        $params = ['playlist_id' => $playlistId];
        
        if ($userId) {
            $params['user_id'] = $userId;
        }
        
        $this->db->prepare(
            "SELECT t.*, pt.position,
                    COALESCE(a.bpm, 0) as bpm, 
                    COALESCE(a.key, '') as music_key, 
                    COALESCE(a.scale, '') as scale, 
                    COALESCE(a.genres, '') as genres_list,
                    COALESCE(a.audio_length, 0) as audio_length
             FROM playlists p
             JOIN playlist_tracks pt ON p.id = pt.playlist_id
             JOIN tracks t ON pt.track_id = t.id
             LEFT JOIN audio_analysis a ON t.id = a.track_id
             WHERE pt.playlist_id = :playlist_id $userCondition
             ORDER BY pt.position ASC",
            $params
        );
        
        return $this->db->fetchAll();
    }
    
    /**
     * Yeni çalma listesi oluşturur
     * 
     * @param int $userId Kullanıcı ID'si
     * @param string $name Çalma listesi adı
     * @param string $description Açıklama
     * @param string $coverUrl Kapak resmi URL'si
     * @param bool $isPublic Herkese açık mı?
     * @return int|bool Çalma listesi ID'si veya false
     */
    public function createPlaylist($userId, $name, $description = '', $coverUrl = null, $isPublic = false) {
        if (empty($name)) {
            return false;
        }
        
        $this->db->prepare(
            "INSERT INTO playlists (user_id, name, description, cover_url, is_public) 
             VALUES (:user_id, :name, :description, :cover_url, :is_public)",
            [
                'user_id' => $userId,
                'name' => $name,
                'description' => $description,
                'cover_url' => $coverUrl,
                'is_public' => $isPublic ? 1 : 0
            ]
        );
        
        if (!$this->db->execute()) {
            return false;
        }
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Çalma listesini günceller
     * 
     * @param int $playlistId Çalma listesi ID'si
     * @param array $data Güncellenecek veriler
     * @param int $userId Kullanıcı ID'si
     * @return bool Başarılı mı?
     */
    public function updatePlaylist($playlistId, $data, $userId) {
        // Güncellenebilir alanlar
        $allowedFields = ['name', 'description', 'cover_url', 'is_public'];
        
        $updateFields = [];
        $params = [
            'id' => $playlistId,
            'user_id' => $userId
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($updateFields)) {
            return false;
        }
        
        $updateFields[] = "updated_at = NOW()";
        
        $this->db->prepare(
            "UPDATE playlists 
             SET " . implode(', ', $updateFields) . " 
             WHERE id = :id AND user_id = :user_id",
            $params
        );
        
        return $this->db->execute();
    }
    
    /**
     * Çalma listesini siler
     * 
     * @param int $playlistId Çalma listesi ID'si
     * @param int $userId Kullanıcı ID'si
     * @return bool Başarılı mı?
     */
    public function deletePlaylist($playlistId, $userId) {
        $this->db->prepare(
            "DELETE FROM playlists 
             WHERE id = :id AND user_id = :user_id",
            [
                'id' => $playlistId,
                'user_id' => $userId
            ]
        );
        
        return $this->db->execute();
    }
    
    /**
     * Parçaları çalma listesine ekler
     * 
     * @param int $playlistId Çalma listesi ID'si
     * @param array $trackIds Parça ID'leri
     * @param int $userId Kullanıcı ID'si
     * @return bool Başarılı mı?
     */
    public function addTracksToPlaylist($playlistId, $trackIds, $userId) {
        if (empty($trackIds)) {
            return false;
        }
        
        // Çalma listesinin kullanıcıya ait olduğunu kontrol et
        $this->db->prepare(
            "SELECT id FROM playlists WHERE id = :id AND user_id = :user_id",
            [
                'id' => $playlistId,
                'user_id' => $userId
            ]
        );
        
        if (!$this->db->fetch()) {
            return false;
        }
        
        // Son pozisyonu al
        $this->db->prepare(
            "SELECT COALESCE(MAX(position), 0) as max_position 
             FROM playlist_tracks 
             WHERE playlist_id = :playlist_id",
            ['playlist_id' => $playlistId]
        );
        
        $result = $this->db->fetch();
        $position = ($result['max_position'] ?? 0) + 1;
        
        // Transaction başlat
        $this->db->beginTransaction();
        
        try {
            foreach ($trackIds as $trackId) {
                // Parçanın zaten listede olup olmadığını kontrol et
                $this->db->prepare(
                    "SELECT id FROM playlist_tracks 
                     WHERE playlist_id = :playlist_id AND track_id = :track_id",
                    [
                        'playlist_id' => $playlistId,
                        'track_id' => $trackId
                    ]
                );
                
                if (!$this->db->fetch()) {
                    $this->db->prepare(
                        "INSERT INTO playlist_tracks (playlist_id, track_id, position) 
                         VALUES (:playlist_id, :track_id, :position)",
                        [
                            'playlist_id' => $playlistId,
                            'track_id' => $trackId,
                            'position' => $position++
                        ]
                    );
                    
                    $this->db->execute();
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Çalma listesine parça ekleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Parçaları çalma listesinden kaldırır
     * 
     * @param int $playlistId Çalma listesi ID'si
     * @param array $trackIds Parça ID'leri
     * @param int $userId Kullanıcı ID'si
     * @return bool Başarılı mı?
     */
    public function removeTracksFromPlaylist($playlistId, $trackIds, $userId) {
        if (empty($trackIds)) {
            return false;
        }
        
        // Çalma listesinin kullanıcıya ait olduğunu kontrol et
        $this->db->prepare(
            "SELECT id FROM playlists WHERE id = :id AND user_id = :user_id",
            [
                'id' => $playlistId,
                'user_id' => $userId
            ]
        );
        
        if (!$this->db->fetch()) {
            return false;
        }
        
        // Parçaları ID'leri ile birleştir
        $placeholders = rtrim(str_repeat('?,', count($trackIds)), ',');
        $params = array_merge([$playlistId], $trackIds);
        
        $this->db->query("DELETE FROM playlist_tracks WHERE playlist_id = ? AND track_id IN ($placeholders)");
        $result = $this->db->execute($params);
        
        // Pozisyonları yeniden düzenle
        if ($result) {
            $this->reorderPlaylistTracks($playlistId);
        }
        
        return $result;
    }
    
    /**
     * Çalma listesi parçalarını yeniden sıralar
     * 
     * @param int $playlistId Çalma listesi ID'si
     * @return bool Başarılı mı?
     */
    public function reorderPlaylistTracks($playlistId) {
        // Mevcut parçaları sıralı şekilde al
        $this->db->prepare(
            "SELECT id 
             FROM playlist_tracks 
             WHERE playlist_id = :playlist_id 
             ORDER BY position ASC",
            ['playlist_id' => $playlistId]
        );
        
        $tracks = $this->db->fetchAll();
        if (empty($tracks)) {
            return true;
        }
        
        // Transaction başlat
        $this->db->beginTransaction();
        
        try {
            // Her parçayı yeni pozisyona göre güncelle
            $position = 1;
            
            foreach ($tracks as $track) {
                $this->db->prepare(
                    "UPDATE playlist_tracks 
                     SET position = :position 
                     WHERE id = :id",
                    [
                        'id' => $track['id'],
                        'position' => $position++
                    ]
                );
                
                $this->db->execute();
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Çalma listesi sıralama hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Parça pozisyonunu değiştirir
     * 
     * @param int $playlistId Çalma listesi ID'si
     * @param int $trackId Parça ID'si
     * @param int $newPosition Yeni pozisyon
     * @param int $userId Kullanıcı ID'si
     * @return bool Başarılı mı?
     */
    public function changeTrackPosition($playlistId, $trackId, $newPosition, $userId) {
        // Çalma listesinin kullanıcıya ait olduğunu kontrol et
        $this->db->prepare(
            "SELECT id FROM playlists WHERE id = :id AND user_id = :user_id",
            [
                'id' => $playlistId,
                'user_id' => $userId
            ]
        );
        
        if (!$this->db->fetch()) {
            return false;
        }
        
        // Parçanın mevcut pozisyonunu al
        $this->db->prepare(
            "SELECT position 
             FROM playlist_tracks 
             WHERE playlist_id = :playlist_id AND track_id = :track_id",
            [
                'playlist_id' => $playlistId,
                'track_id' => $trackId
            ]
        );
        
        $result = $this->db->fetch();
        if (!$result) {
            return false;
        }
        
        $currentPosition = $result['position'];
        
        // Pozisyon değiştirilmiyorsa işlem yapma
        if ($currentPosition == $newPosition) {
            return true;
        }
        
        // Transaction başlat
        $this->db->beginTransaction();
        
        try {
            if ($currentPosition < $newPosition) {
                // Parça aşağı taşınıyor, araya giren parçaları yukarı kaydır
                $this->db->prepare(
                    "UPDATE playlist_tracks 
                     SET position = position - 1 
                     WHERE playlist_id = :playlist_id 
                     AND position > :current_position 
                     AND position <= :new_position",
                    [
                        'playlist_id' => $playlistId,
                        'current_position' => $currentPosition,
                        'new_position' => $newPosition
                    ]
                );
                
                $this->db->execute();
            } else {
                // Parça yukarı taşınıyor, araya giren parçaları aşağı kaydır
                $this->db->prepare(
                    "UPDATE playlist_tracks 
                     SET position = position + 1 
                     WHERE playlist_id = :playlist_id 
                     AND position >= :new_position 
                     AND position < :current_position",
                    [
                        'playlist_id' => $playlistId,
                        'current_position' => $currentPosition,
                        'new_position' => $newPosition
                    ]
                );
                
                $this->db->execute();
            }
            
            // Parçanın yeni pozisyonunu ayarla
            $this->db->prepare(
                "UPDATE playlist_tracks 
                 SET position = :new_position 
                 WHERE playlist_id = :playlist_id AND track_id = :track_id",
                [
                    'playlist_id' => $playlistId,
                    'track_id' => $trackId,
                    'new_position' => $newPosition
                ]
            );
            
            $this->db->execute();
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Parça pozisyonu değiştirme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Herkese açık çalma listelerini getirir
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Çalma listeleri
     */
    public function getPublicPlaylists($limit = 20, $offset = 0) {
        $this->db->prepare(
            "SELECT p.*, u.username as creator,
                    COUNT(pt.id) as track_count,
                    MAX(t.updated_at) as last_updated
             FROM playlists p
             JOIN users u ON p.user_id = u.id
             LEFT JOIN playlist_tracks pt ON p.id = pt.playlist_id
             LEFT JOIN tracks t ON pt.track_id = t.id
             WHERE p.is_public = 1
             GROUP BY p.id
             ORDER BY p.created_at DESC
             LIMIT :limit OFFSET :offset",
            [
                'limit' => $limit,
                'offset' => $offset
            ]
        );
        
        return $this->db->fetchAll();
    }
}
