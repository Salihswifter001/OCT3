<?php
/**
 * Octaverum - AI Müzik Uygulaması
 * Kimlik Doğrulama ve Kullanıcı Yönetimi
 */

require_once 'config.php';
require_once 'database_connection.php';
require_once 'utils.php';

class Auth {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Oturum başlatma
        if (session_status() == PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params(
                SESSION_LIFETIME,
                SESSION_PATH,
                $_SERVER['HTTP_HOST'],
                SESSION_SECURE,
                SESSION_HTTPONLY
            );
            session_start();
        }
    }
    
    /**
     * Kullanıcı kaydı
     * @param string $username
     * @param string $email
     * @param string $password
     * @param string $fullName (isteğe bağlı)
     * @return array
     */
    public function register($username, $email, $password, $fullName = '') {
        // Giriş verilerini temizle
        $username = filterInput($username);
        $email = filterInput($email);
        $fullName = filterInput($fullName);
        
        // Alanları kontrol et
        if (empty($username) || empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Tüm alanları doldurun'
            ];
        }
        
        // E-posta doğrulaması
        if (!isValidEmail($email)) {
            return [
                'success' => false,
                'message' => 'Geçerli bir e-posta adresi girin'
            ];
        }
        
        // Şifre uzunluğu kontrolü
        if (strlen($password) < 6) {
            return [
                'success' => false,
                'message' => 'Şifre en az 6 karakter olmalıdır'
            ];
        }
        
        // Kullanıcı adı ve e-posta benzersizliği kontrolü
        $existingUser = $this->db->select('users', ['username' => $username]);
        if (!empty($existingUser)) {
            return [
                'success' => false,
                'message' => 'Bu kullanıcı adı zaten kullanılıyor'
            ];
        }
        
        $existingEmail = $this->db->select('users', ['email' => $email]);
        if (!empty($existingEmail)) {
            return [
                'success' => false,
                'message' => 'Bu e-posta adresi zaten kayıtlı'
            ];
        }
        
        // Şifreyi hash'le
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        
        // Kullanıcı verilerini hazırla
        $userData = [
            'username' => $username,
            'email' => $email,
            'password' => $passwordHash,
            'full_name' => $fullName,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'status' => 'active',
            'role' => 'user',
            'subscription' => 'free',
            'avatar' => '',
            'last_login' => null
        ];
        
        // Kullanıcıyı veritabanına ekle
        $userId = $this->db->insert('users', $userData);
        
        if (!$userId) {
            return [
                'success' => false,
                'message' => 'Kayıt sırasında bir hata oluştu'
            ];
        }
        
        // Başarılı kayıt
        return [
            'success' => true,
            'message' => 'Kayıt başarılı! Şimdi giriş yapabilirsiniz.',
            'user_id' => $userId
        ];
    }
    
    /**
     * Kullanıcı girişi
     * @param string $username Kullanıcı adı veya e-posta
     * @param string $password Şifre
     * @param boolean $remember Beni hatırla
     * @return array
     */
    public function login($username, $password, $remember = false) {
        // Giriş verilerini temizle
        $username = filterInput($username);
        
        // Alanları kontrol et
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Kullanıcı adı/e-posta ve şifre gereklidir'
            ];
        }
        
        // Kullanıcı adı veya e-posta ile kullanıcı bulma
        $user = $this->db->query(
            "SELECT * FROM " . $this->db->getTableName('users') . " 
             WHERE username = :username OR email = :email LIMIT 1",
            ['username' => $username, 'email' => $username]
        )->fetchOne();
        
        // Kullanıcı bulunamadı
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Geçersiz kullanıcı adı/e-posta veya şifre'
            ];
        }
        
        // Hesap aktif değil
        if ($user['status'] !== 'active') {
            return [
                'success' => false,
                'message' => 'Hesabınız aktif değil, lütfen yönetici ile iletişime geçin'
            ];
        }
        
        // Şifre kontrolü
        if (!password_verify($password, $user['password'])) {
            // Başarısız giriş denemesi sayısını artır
            $this->incrementLoginAttempts($user['id']);
            
            return [
                'success' => false,
                'message' => 'Geçersiz kullanıcı adı/e-posta veya şifre'
            ];
        }
        
        // Başarısız giriş denemelerini sıfırla
        $this->resetLoginAttempts($user['id']);
        
        // Son giriş zamanını güncelle
        $this->db->update('users', [
            'last_login' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $user['id']]);
        
        // Giriş kaydı oluştur
        $deviceInfo = getUserDeviceInfo();
        $this->db->insert('login_logs', [
            'user_id' => $user['id'],
            'ip_address' => $deviceInfo['ip'],
            'user_agent' => $deviceInfo['userAgent'],
            'device_type' => $deviceInfo['deviceType'],
            'browser' => $deviceInfo['browser'],
            'os' => $deviceInfo['os'],
            'login_time' => date('Y-m-d H:i:s'),
            'status' => 'success'
        ]);
        
        // Kullanıcı session bilgilerini ayarla
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['subscription'] = $user['subscription'];
        $_SESSION['last_activity'] = time();
        
        // İsteğe bağlı "beni hatırla" için çerezlerin hazırlanması
        if ($remember) {
            $token = base64UrlEncode(random_bytes(32));
            $tokenHash = password_hash($token, PASSWORD_BCRYPT);
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            // Token'ı veritabanına kaydet
            $this->db->insert('remember_tokens', [
                'user_id' => $user['id'],
                'token' => $tokenHash,
                'expires_at' => $expires,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Çerez oluştur (30 gün)
            $cookieValue = $user['id'] . ':' . $token;
            setcookie('remember_me', $cookieValue, time() + (86400 * 30), '/', '', SESSION_SECURE, true);
        }
        
        // Güvenlik nedeniyle şifreyi session'dan kaldır
        unset($user['password']);
        
        return [
            'success' => true,
            'message' => 'Giriş başarılı!',
            'user' => $user
        ];
    }
    
    /**
     * Çıkış işlemi
     * @return void
     */
    public function logout() {
        // Oturum bilgilerini temizle
        $_SESSION = [];
        
        // Oturum çerezini sil
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // "Beni hatırla" çerezini sil
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/', '', SESSION_SECURE, true);
        }
        
        // Oturumu yok et
        session_destroy();
    }
    
    /**
     * "Beni hatırla" token'ı ile otomatik giriş
     * @return boolean
     */
    public function autoLogin() {
        if (!isset($_COOKIE['remember_me'])) {
            return false;
        }
        
        list($userId, $token) = explode(':', $_COOKIE['remember_me'], 2);
        
        // Token'ı veritabanından kontrol et
        $storedToken = $this->db->select('remember_tokens', [
            'user_id' => $userId,
            'expires_at > ' => date('Y-m-d H:i:s')
        ], '*', 'created_at DESC', 1);
        
        if (empty($storedToken)) {
            // Token bulunamadı veya süresi dolmuş, çerezi sil
            setcookie('remember_me', '', time() - 3600, '/', '', SESSION_SECURE, true);
            return false;
        }
        
        $storedToken = $storedToken[0];
        
        // Token eşleşiyor mu kontrol et
        if (!password_verify($token, $storedToken['token'])) {
            return false;
        }
        
        // Kullanıcıyı bul
        $user = $this->db->select('users', ['id' => $userId], '*');
        
        if (empty($user) || $user[0]['status'] !== 'active') {
            return false;
        }
        
        $user = $user[0];
        
        // Kullanıcı session bilgilerini ayarla
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['subscription'] = $user['subscription'];
        $_SESSION['last_activity'] = time();
        
        // Son giriş zamanını güncelle
        $this->db->update('users', [
            'last_login' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $user['id']]);
        
        return true;
    }
    
    /**
     * Kullanıcının giriş yapıp yapmadığını kontrol et
     * @return boolean
     */
    public function isLoggedIn() {
        // Oturum kontrolü
        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
            // Oturum süresi kontrolü
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
                // Oturum süresi dolmuşsa çıkış yap
                $this->logout();
                return false;
            }
            
            // Son aktivite zamanını güncelle
            $_SESSION['last_activity'] = time();
            return true;
        }
        
        // "Beni hatırla" ile otomatik giriş dene
        return $this->autoLogin();
    }
    
    /**
     * Kullanıcının yönetici olup olmadığını kontrol et
     * @return boolean
     */
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    /**
     * Şifre sıfırlama token'ı oluşturma
     * @param string $email
     * @return array
     */
    public function createPasswordResetToken($email) {
        $email = filterInput($email);
        
        // E-posta kontrolü
        if (empty($email) || !isValidEmail($email)) {
            return [
                'success' => false,
                'message' => 'Geçerli bir e-posta adresi girin'
            ];
        }
        
        // Kullanıcıyı bul
        $user = $this->db->select('users', ['email' => $email]);
        
        if (empty($user)) {
            // Güvenlik için "e-posta gönderildi" mesajı ver ancak aslında göndermemiş ol
            return [
                'success' => true,
                'message' => 'Şifre sıfırlama talimatları e-posta adresinize gönderildi'
            ];
        }
        
        $user = $user[0];
        
        // Eski tokenları sil
        $this->db->delete('password_resets', ['user_id' => $user['id']]);
        
        // Yeni token oluştur
        $token = bin2hex(random_bytes(16));
        $tokenHash = password_hash($token, PASSWORD_BCRYPT);
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Token'ı veritabanına kaydet
        $this->db->insert('password_resets', [
            'user_id' => $user['id'],
            'token' => $tokenHash,
            'expires_at' => $expires,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Token ve kullanıcı bilgilerini döndür - e-posta gönderimi için
        return [
            'success' => true,
            'message' => 'Şifre sıfırlama talimatları e-posta adresinize gönderildi',
            'user' => $user,
            'token' => $token,
            'expires' => $expires
        ];
    }
    
    /**
     * Şifre sıfırlama
     * @param string $token
     * @param string $password
     * @param string $passwordConfirm
     * @return array
     */
    public function resetPassword($token, $password, $passwordConfirm) {
        // Kontroller
        if (empty($token) || empty($password) || empty($passwordConfirm)) {
            return [
                'success' => false,
                'message' => 'Tüm alanları doldurun'
            ];
        }
        
        if ($password !== $passwordConfirm) {
            return [
                'success' => false,
                'message' => 'Şifreler eşleşmiyor'
            ];
        }
        
        if (strlen($password) < 6) {
            return [
                'success' => false,
                'message' => 'Şifre en az 6 karakter olmalıdır'
            ];
        }
        
        // Veritabanında tüm geçerli tokenları al
        $resetRequests = $this->db->select('password_resets', [
            'expires_at > ' => date('Y-m-d H:i:s')
        ]);
        
        // Token eşleşmesini kontrol et
        $validRequest = null;
        foreach ($resetRequests as $request) {
            if (password_verify($token, $request['token'])) {
                $validRequest = $request;
                break;
            }
        }
        
        if (!$validRequest) {
            return [
                'success' => false,
                'message' => 'Geçersiz veya süresi dolmuş token'
            ];
        }
        
        // Yeni şifreyi hashle
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        
        // Şifreyi güncelle
        $updated = $this->db->update('users', [
            'password' => $passwordHash,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $validRequest['user_id']]);
        
        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Şifre güncellenirken bir hata oluştu'
            ];
        }
        
        // Token'ı sil
        $this->db->delete('password_resets', ['id' => $validRequest['id']]);
        
        return [
            'success' => true,
            'message' => 'Şifreniz başarıyla güncellendi. Şimdi giriş yapabilirsiniz.'
        ];
    }
    
    /**
     * Kullanıcı profilini güncelleme
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateProfile($userId, $data) {
        // Kullanıcı kimlik doğrulaması
        if (!$this->isLoggedIn() || $_SESSION['user_id'] != $userId && !$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'Bu işlem için yetkiniz yok'
            ];
        }
        
        // Güncellenebilir alanlar
        $allowedFields = ['full_name', 'username', 'email', 'avatar'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = filterInput($data[$field]);
            }
        }
        
        // Eğer veri boşsa
        if (empty($updateData)) {
            return [
                'success' => false,
                'message' => 'Güncellenecek veri bulunamadı'
            ];
        }
        
        // Kullanıcı adı değişirse benzersizliğini kontrol et
        if (isset($updateData['username'])) {
            $existingUser = $this->db->query(
                "SELECT id FROM " . $this->db->getTableName('users') . " 
                 WHERE username = :username AND id != :id",
                ['username' => $updateData['username'], 'id' => $userId]
            )->fetchOne();
            
            if ($existingUser) {
                return [
                    'success' => false,
                    'message' => 'Bu kullanıcı adı zaten kullanılıyor'
                ];
            }
        }
        
        // E-posta değişirse benzersizliğini kontrol et
        if (isset($updateData['email'])) {
            // E-posta geçerliliği
            if (!isValidEmail($updateData['email'])) {
                return [
                    'success' => false,
                    'message' => 'Geçerli bir e-posta adresi girin'
                ];
            }
            
            $existingEmail = $this->db->query(
                "SELECT id FROM " . $this->db->getTableName('users') . " 
                 WHERE email = :email AND id != :id",
                ['email' => $updateData['email'], 'id' => $userId]
            )->fetchOne();
            
            if ($existingEmail) {
                return [
                    'success' => false,
                    'message' => 'Bu e-posta adresi zaten kullanılıyor'
                ];
            }
        }
        
        // Son güncelleme zamanını ekle
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        // Güncelleme işlemi
        $updated = $this->db->update('users', $updateData, ['id' => $userId]);
        
        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Profil güncellenirken bir hata oluştu'
            ];
        }
        
        // Oturum bilgilerini güncelle
        if (isset($updateData['username'])) {
            $_SESSION['username'] = $updateData['username'];
        }
        
        if (isset($updateData['email'])) {
            $_SESSION['email'] = $updateData['email'];
        }
        
        return [
            'success' => true,
            'message' => 'Profil başarıyla güncellendi'
        ];
    }
    
    /**
     * Şifre değiştirme
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @param string $confirmPassword
     * @return array
     */
    public function changePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
        // Kullanıcı kimlik doğrulaması
        if (!$this->isLoggedIn() || $_SESSION['user_id'] != $userId && !$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'Bu işlem için yetkiniz yok'
            ];
        }
        
        // Şifre kontrolleri
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return [
                'success' => false,
                'message' => 'Tüm alanları doldurun'
            ];
        }
        
        if ($newPassword !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'Yeni şifreler eşleşmiyor'
            ];
        }
        
        if (strlen($newPassword) < 6) {
            return [
                'success' => false,
                'message' => 'Yeni şifre en az 6 karakter olmalıdır'
            ];
        }
        
        // Kullanıcıyı bul
        $user = $this->db->select('users', ['id' => $userId]);
        
        if (empty($user)) {
            return [
                'success' => false,
                'message' => 'Kullanıcı bulunamadı'
            ];
        }
        
        $user = $user[0];
        
        // Mevcut şifreyi kontrol et
        if (!password_verify($currentPassword, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Mevcut şifre yanlış'
            ];
        }
        
        // Yeni şifreyi hashle
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        
        // Şifreyi güncelle
        $updated = $this->db->update('users', [
            'password' => $passwordHash,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $userId]);
        
        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Şifre güncellenirken bir hata oluştu'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Şifreniz başarıyla güncellendi'
        ];
    }
    
    /**
     * Kullanıcı abone durumunu güncelleme
     * @param int $userId
     * @param string $subscription
     * @param string $expiryDate
     * @return array
     */
    public function updateSubscription($userId, $subscription, $expiryDate = null) {
        // Sadece yöneticiler bu işlemi yapabilir
        if (!$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'Bu işlem için yönetici yetkileri gerekiyor'
            ];
        }
        
        // Geçerli abonelik türleri
        $validSubscriptions = ['free', 'premium', 'pro'];
        
        if (!in_array($subscription, $validSubscriptions)) {
            return [
                'success' => false,
                'message' => 'Geçersiz abonelik türü'
            ];
        }
        
        $updateData = [
            'subscription' => $subscription,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Son kullanma tarihi belirlenmişse ekle
        if ($expiryDate) {
            $updateData['subscription_expiry'] = $expiryDate;
        } elseif ($subscription !== 'free') {
            // Ücretsiz değilse, 1 ay ekle
            $updateData['subscription_expiry'] = date('Y-m-d H:i:s', strtotime('+1 month'));
        } else {
            // Ücretsizse, son kullanma tarihini kaldır
            $updateData['subscription_expiry'] = null;
        }
        
        // Güncelleme işlemi
        $updated = $this->db->update('users', $updateData, ['id' => $userId]);
        
        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Abonelik güncellenirken bir hata oluştu'
            ];
        }
        
        // Abonelik geçmişine kaydet
        $this->db->insert('subscription_history', [
            'user_id' => $userId,
            'subscription_type' => $subscription,
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => $updateData['subscription_expiry'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Oturum bilgilerini güncelle (eğer kullanıcı aktif ise)
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            $_SESSION['subscription'] = $subscription;
        }
        
        return [
            'success' => true,
            'message' => 'Abonelik başarıyla güncellendi'
        ];
    }
    
    /**
     * Hesabı etkinleştirme/devre dışı bırakma
     * @param int $userId
     * @param string $status
     * @return array
     */
    public function updateAccountStatus($userId, $status) {
        // Sadece yöneticiler bu işlemi yapabilir
        if (!$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'Bu işlem için yönetici yetkileri gerekiyor'
            ];
        }
        
        // Geçerli durum türleri
        $validStatuses = ['active', 'inactive', 'suspended', 'banned'];
        
        if (!in_array($status, $validStatuses)) {
            return [
                'success' => false,
                'message' => 'Geçersiz hesap durumu'
            ];
        }
        
        // Güncelleme işlemi
        $updated = $this->db->update('users', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $userId]);
        
        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Hesap durumu güncellenirken bir hata oluştu'
            ];
        }
        
        // Eğer hesap devre dışı bırakıldıysa, tüm oturumları sonlandır
        if ($status !== 'active' && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            $this->logout();
        }
        
        return [
            'success' => true,
            'message' => 'Hesap durumu başarıyla güncellendi'
        ];
    }
    
    /**
     * Kullanıcı bilgilerini getir
     * @param int $userId
     * @return array|false
     */
    public function getUser($userId) {
        // Kullanıcıyı bul
        $user = $this->db->select('users', ['id' => $userId]);
        
        if (empty($user)) {
            return false;
        }
        
        $user = $user[0];
        
        // Hassas bilgileri temizle
        unset($user['password']);
        
        return $user;
    }
    
    /**
     * Mevcut kullanıcının bilgilerini getir
     * @return array|false
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return $this->getUser($_SESSION['user_id']);
    }
    
    /**
     * Oturum zaman aşımı kontrolü
     * @return void
     */
    public function checkSessionTimeout() {
        if (isset($_SESSION['last_activity'])) {
            $inactiveTime = time() - $_SESSION['last_activity'];
            
            if ($inactiveTime > SESSION_LIFETIME) {
                $this->logout();
                setFlashMessage('Oturumunuz zaman aşımına uğradı. Lütfen tekrar giriş yapın.', 'warning');
                redirect('login.php');
            } else {
                $_SESSION['last_activity'] = time();
            }
        }
    }
    
    /**
     * Başarısız giriş denemelerini artır
     * @param int $userId
     * @return void
     */
    private function incrementLoginAttempts($userId) {
        // Giriş denemeleri tablosunu kontrol et
        $loginAttempt = $this->db->select('login_attempts', ['user_id' => $userId]);
        
        if (empty($loginAttempt)) {
            // Yeni kayıt oluştur
            $this->db->insert('login_attempts', [
                'user_id' => $userId,
                'attempts' => 1,
                'last_attempt' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Mevcut kayıt güncelle
            $loginAttempt = $loginAttempt[0];
            $attempts = $loginAttempt['attempts'] + 1;
            
            $this->db->update('login_attempts', [
                'attempts' => $attempts,
                'last_attempt' => date('Y-m-d H:i:s')
            ], ['id' => $loginAttempt['id']]);
            
            // Maksimum deneme sayısını aştıysa hesabı geçici olarak kilitle
            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $this->db->update('users', [
                    'status' => 'locked',
                    'updated_at' => date('Y-m-d H:i:s')
                ], ['id' => $userId]);
                
                // Kilidi kaldırmak için zamanlanmış görev oluştur
                $unlockTime = date('Y-m-d H:i:s', strtotime('+' . LOGIN_TIMEOUT . ' seconds'));
                
                $this->db->insert('scheduled_tasks', [
                    'task_type' => 'unlock_account',
                    'user_id' => $userId,
                    'scheduled_time' => $unlockTime,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        // Giriş günlüğüne başarısız girişi kaydet
        $deviceInfo = getUserDeviceInfo();
        $this->db->insert('login_logs', [
            'user_id' => $userId,
            'ip_address' => $deviceInfo['ip'],
            'user_agent' => $deviceInfo['userAgent'],
            'device_type' => $deviceInfo['deviceType'],
            'browser' => $deviceInfo['browser'],
            'os' => $deviceInfo['os'],
            'login_time' => date('Y-m-d H:i:s'),
            'status' => 'failed'
        ]);
    }
    
    /**
     * Başarısız giriş denemelerini sıfırla
     * @param int $userId
     * @return void
     */
    private function resetLoginAttempts($userId) {
        $this->db->delete('login_attempts', ['user_id' => $userId]);
    }
    
    /**
     * Hesap kilidi kaldırma
     * @param int $userId
     * @return boolean
     */
    public function unlockAccount($userId) {
        return $this->db->update('users', [
            'status' => 'active',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $userId]);
    }
}
