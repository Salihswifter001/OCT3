<?php
namespace Controllers;

/**
 * Octaverum AI - UserController Sınıfı
 * 
 * Kullanıcı oturumları, profil ve hesap işlemleri için Controller
 */
class UserController extends BaseController {
    /**
     * Oturum yardımcısı
     * @var \Helpers\SessionHelper
     */
    protected $sessionHelper;
    
    /**
     * Constructor
     * 
     * @param \PDO $db Veritabanı bağlantısı
     * @param \Helpers\SessionHelper $sessionHelper
     */
    public function __construct($db, $sessionHelper) {
        parent::__construct($db);
        $this->sessionHelper = $sessionHelper;
    }
    
    /**
     * Giriş sayfası
     * 
     * @return void
     */
    public function login() {
        // Kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
        if ($this->user) {
            $this->redirect('home');
        }
        
        $error = '';
        
        // POST verilerini işle
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
            $rememberMe = isset($_POST['remember_me']) ? true : false;
            
            // CSRF token kontrolü
            $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
            if (!$this->validateCsrfToken($csrf_token)) {
                $error = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
            }
            // Gerekli alanları kontrol et
            elseif (empty($email) || empty($password)) {
                $error = 'E-posta ve şifre alanları boş olamaz.';
            } else {
                // Kullanıcı doğrulama
                $userModel = new \Models\User($this->db);
                $user = $userModel->getUserByEmail($email);
                
                if (!$user || !password_verify($password, $user['password'])) {
                    $error = 'E-posta veya şifre hatalı.';
                } else {
                    // Kullanıcının aktif olup olmadığını kontrol et
                    if (!$user['is_active']) {
                        $error = 'Hesabınız aktif değil. Lütfen e-posta ile gönderilen aktivasyon bağlantısını kullanın.';
                    } else {
                        // Oturum açma başarılı
                        $this->sessionHelper->login($user['id'], $rememberMe);
                        
                        // Son giriş zamanını güncelle
                        $userModel->updateLastLogin($user['id']);
                        
                        // Kullanıcıyı önceki sayfaya veya ana sayfaya yönlendir
                        $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'home';
                        unset($_SESSION['redirect_after_login']);
                        
                        $this->redirect($redirect);
                    }
                }
            }
        }
        
        // Giriş formunu göster
        $this->pageTitle = 'Giriş Yap - Octaverum AI';
        $csrf_token = $this->generateCsrfToken();
        
        $this->render('user/login', [
            'error' => $error,
            'csrf_token' => $csrf_token
        ]);
    }
    
    /**
     * Kayıt sayfası
     * 
     * @return void
     */
    public function register() {
        // Kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
        if ($this->user) {
            $this->redirect('home');
        }
        
        $error = '';
        $success = '';
        
        // POST verilerini işle
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
            $passwordConfirm = filter_input(INPUT_POST, 'password_confirm', FILTER_UNSAFE_RAW);
            
            // CSRF token kontrolü
            $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
            if (!$this->validateCsrfToken($csrf_token)) {
                $error = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
            }
            // Gerekli alanları kontrol et
            elseif (empty($username) || empty($email) || empty($password)) {
                $error = 'Lütfen tüm zorunlu alanları doldurun.';
            }
            // E-posta geçerliliğini kontrol et
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Lütfen geçerli bir e-posta adresi girin.';
            }
            // Şifrelerin eşleşip eşleşmediğini kontrol et
            elseif ($password !== $passwordConfirm) {
                $error = 'Şifreler eşleşmiyor.';
            }
            // Şifre güvenliğini kontrol et
            elseif (strlen($password) < 8) {
                $error = 'Şifre en az 8 karakter uzunluğunda olmalıdır.';
            } else {
                // Kullanıcı kayıt işlemini yap
                $userModel = new \Models\User($this->db);
                
                // E-posta veya kullanıcı adının daha önce kullanılıp kullanılmadığını kontrol et
                if ($userModel->emailExists($email)) {
                    $error = 'Bu e-posta adresi zaten kullanılıyor.';
                } elseif ($userModel->usernameExists($username)) {
                    $error = 'Bu kullanıcı adı zaten kullanılıyor.';
                } else {
                    // Kullanıcıyı oluştur
                    $activationToken = bin2hex(random_bytes(16));
                    
                    $userId = $userModel->createUser([
                        'username' => $username,
                        'email' => $email,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'activation_token' => $activationToken,
                        'is_active' => 0, // E-posta onayı sonrası aktifleşecek
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    if ($userId) {
                        // Başarı mesajı göster
                        $success = 'Kayıt işleminiz başarıyla tamamlandı. E-posta adresinize gönderilen aktivasyon bağlantısı ile hesabınızı aktifleştirebilirsiniz.';
                        
                        // Aktivasyon e-postası gönder
                        $this->sendActivationEmail($email, $activationToken);
                    } else {
                        $error = 'Kayıt sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
                    }
                }
            }
        }
        
        // Kayıt formunu göster
        $this->pageTitle = 'Kayıt Ol - Octaverum AI';
        $csrf_token = $this->generateCsrfToken();
        
        $this->render('user/register', [
            'error' => $error,
            'success' => $success,
            'csrf_token' => $csrf_token
        ]);
    }
    
    /**
     * Hesap aktivasyonu
     * 
     * @return void
     */
    public function activate() {
        $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
        
        if (empty($token)) {
            $this->redirect('home');
        }
        
        $userModel = new \Models\User($this->db);
        $result = $userModel->activateAccount($token);
        
        if ($result) {
            $success = 'Hesabınız başarıyla aktifleştirildi. Şimdi giriş yapabilirsiniz.';
            $this->render('user/activation_success', [
                'success' => $success
            ]);
        } else {
            $error = 'Geçersiz veya süresi dolmuş aktivasyon bağlantısı.';
            $this->render('user/activation_error', [
                'error' => $error
            ]);
        }
    }
    
    /**
     * Şifremi unuttum sayfası
     * 
     * @return void
     */
    public function forgotPassword() {
        // Kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
        if ($this->user) {
            $this->redirect('home');
        }
        
        $error = '';
        $success = '';
        
        // POST verilerini işle
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            
            // CSRF token kontrolü
            $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
            if (!$this->validateCsrfToken($csrf_token)) {
                $error = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
            }
            // E-posta alanını kontrol et
            elseif (empty($email)) {
                $error = 'Lütfen e-posta adresinizi girin.';
            } else {
                // Kullanıcıyı e-posta ile bul
                $userModel = new \Models\User($this->db);
                $user = $userModel->getUserByEmail($email);
                
                if ($user) {
                    // Şifre sıfırlama token'ı oluştur
                    $resetToken = bin2hex(random_bytes(16));
                    $resetExpires = date('Y-m-d H:i:s', time() + 3600); // 1 saat geçerli
                    
                    // Token'ı veritabanına kaydet
                    $userModel->setResetToken($user['id'], $resetToken, $resetExpires);
                    
                    // Şifre sıfırlama e-postası gönder
                    $this->sendPasswordResetEmail($email, $resetToken);
                    
                    $success = 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi. Lütfen e-postanızı kontrol edin.';
                } else {
                    // Güvenlik için kullanıcıya aynı mesajı göster
                    $success = 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi. Lütfen e-postanızı kontrol edin.';
                }
            }
        }
        
        // Şifremi unuttum formunu göster
        $this->pageTitle = 'Şifremi Unuttum - Octaverum AI';
        $csrf_token = $this->generateCsrfToken();
        
        $this->render('user/forgot_password', [
            'error' => $error,
            'success' => $success,
            'csrf_token' => $csrf_token
        ]);
    }
    
    /**
     * Şifre sıfırlama sayfası
     * 
     * @return void
     */
    public function resetPassword() {
        // Kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
        if ($this->user) {
            $this->redirect('home');
        }
        
        $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
        
        if (empty($token)) {
            $this->redirect('home');
        }
        
        // Token'ın geçerliliğini kontrol et
        $userModel = new \Models\User($this->db);
        $user = $userModel->getUserByResetToken($token);
        
        if (!$user) {
            $error = 'Geçersiz veya süresi dolmuş şifre sıfırlama bağlantısı.';
            $this->render('user/reset_password_error', [
                'error' => $error
            ]);
            return;
        }
        
        $error = '';
        $success = '';
        
        // POST verilerini işle
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
            $passwordConfirm = filter_input(INPUT_POST, 'password_confirm', FILTER_UNSAFE_RAW);
            
            // CSRF token kontrolü
            $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
            if (!$this->validateCsrfToken($csrf_token)) {
                $error = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
            }
            // Şifreleri kontrol et
            elseif (empty($password) || empty($passwordConfirm)) {
                $error = 'Lütfen tüm alanları doldurun.';
            }
            // Şifrelerin eşleşip eşleşmediğini kontrol et
            elseif ($password !== $passwordConfirm) {
                $error = 'Şifreler eşleşmiyor.';
            }
            // Şifre güvenliğini kontrol et
            elseif (strlen($password) < 8) {
                $error = 'Şifre en az 8 karakter uzunluğunda olmalıdır.';
            } else {
                // Şifreyi güncelle
                $result = $userModel->resetPassword($user['id'], password_hash($password, PASSWORD_DEFAULT));
                
                if ($result) {
                    $success = 'Şifreniz başarıyla sıfırlandı. Şimdi giriş yapabilirsiniz.';
                    
                    // 3 saniye sonra giriş sayfasına yönlendir
                    header('Refresh: 3; URL=index.php?route=user&action=login');
                } else {
                    $error = 'Şifre sıfırlama sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
                }
            }
        }
        
        // Şifre sıfırlama formunu göster
        $this->pageTitle = 'Şifre Sıfırlama - Octaverum AI';
        $csrf_token = $this->generateCsrfToken();
        
        $this->render('user/reset_password', [
            'error' => $error,
            'success' => $success,
            'csrf_token' => $csrf_token,
            'token' => $token
        ]);
    }
    
    /**
     * Profil sayfası
     * 
     * @return void
     */
    public function profile() {
        // Kullanıcı giriş yapmamışsa giriş sayfasına yönlendir
        if (!$this->user) {
            $_SESSION['redirect_after_login'] = 'user/profile';
            $this->redirect('user', 'login');
        }
        
        $error = '';
        $success = '';
        
        // POST verilerini işle (profil güncelleme)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // İşlem türünü kontrol et
            $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
            
            // CSRF token kontrolü
            $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
            if (!$this->validateCsrfToken($csrf_token)) {
                $error = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
            } else {
                switch ($action) {
                    case 'update_profile':
                        $this->updateProfile();
                        break;
                    case 'change_password':
                        $this->changePassword();
                        break;
                    case 'update_avatar':
                        $this->updateAvatar();
                        break;
                    default:
                        $error = 'Geçersiz işlem.';
                }
            }
        }
        
        // Kullanıcının güncel bilgilerini al
        $userModel = new \Models\User($this->db);
        $user = $userModel->getUserById($this->user['id']);
        
        // Kullanıcının cihazlarını al
        $devices = $userModel->getUserDevices($this->user['id']);
        
        // Kullanıcının müzik istatistiklerini al
        $musicModel = new \Models\Music($this->db);
        $musicStats = $musicModel->getUserMusicStats($this->user['id']);
        
        // Kullanıcının abonelik bilgilerini al
        $subscription = $userModel->getUserSubscription($this->user['id']);
        
        $this->pageTitle = 'Profil - Octaverum AI';
        $csrf_token = $this->generateCsrfToken();
        
        $this->render('user/profile', [
            'user' => $user,
            'devices' => $devices,
            'musicStats' => $musicStats,
            'subscription' => $subscription,
            'error' => $error,
            'success' => $success,
            'csrf_token' => $csrf_token
        ]);
    }
    
    /**
     * Profil bilgilerini günceller
     * 
     * @return void
     */
    private function updateProfile() {
        $fullName = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        
        // Kullanıcı adı ve e-posta benzersiz olmalı
        $userModel = new \Models\User($this->db);
        
        // E-posta değiştiyse ve başka bir kullanıcı tarafından kullanılıyorsa
        if ($email !== $this->user['email'] && $userModel->emailExists($email, $this->user['id'])) {
            $_SESSION['profile_error'] = 'Bu e-posta adresi zaten kullanılıyor.';
            $this->redirect('user', 'profile');
        }
        
        // Kullanıcı adı değiştiyse ve başka bir kullanıcı tarafından kullanılıyorsa
        if ($username !== $this->user['username'] && $userModel->usernameExists($username, $this->user['id'])) {
            $_SESSION['profile_error'] = 'Bu kullanıcı adı zaten kullanılıyor.';
            $this->redirect('user', 'profile');
        }
        
        // Profili güncelle
        $result = $userModel->updateProfile($this->user['id'], [
            'full_name' => $fullName,
            'username' => $username,
            'email' => $email
        ]);
        
        if ($result) {
            $_SESSION['profile_success'] = 'Profil bilgileriniz başarıyla güncellendi.';
        } else {
            $_SESSION['profile_error'] = 'Profil güncellenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
        }
        
        $this->redirect('user', 'profile');
    }
    
    /**
     * Şifre değiştirme
     * 
     * @return void
     */
    private function changePassword() {
        $currentPassword = filter_input(INPUT_POST, 'current_password', FILTER_UNSAFE_RAW);
        $newPassword = filter_input(INPUT_POST, 'new_password', FILTER_UNSAFE_RAW);
        $confirmPassword = filter_input(INPUT_POST, 'confirm_password', FILTER_UNSAFE_RAW);
        
        // Mevcut şifreyi kontrol et
        $userModel = new \Models\User($this->db);
        $user = $userModel->getUserById($this->user['id']);
        
        if (!password_verify($currentPassword, $user['password'])) {
            $_SESSION['profile_error'] = 'Mevcut şifreniz yanlış.';
            $this->redirect('user', 'profile');
        }
        
        // Yeni şifreleri kontrol et
        if ($newPassword !== $confirmPassword) {
            $_SESSION['profile_error'] = 'Yeni şifreler eşleşmiyor.';
            $this->redirect('user', 'profile');
        }
        
        // Şifre güvenliğini kontrol et
        if (strlen($newPassword) < 8) {
            $_SESSION['profile_error'] = 'Şifre en az 8 karakter uzunluğunda olmalıdır.';
            $this->redirect('user', 'profile');
        }
        
        // Şifreyi güncelle
        $result = $userModel->updatePassword($this->user['id'], password_hash($newPassword, PASSWORD_DEFAULT));
        
        if ($result) {
            $_SESSION['profile_success'] = 'Şifreniz başarıyla güncellendi.';
        } else {
            $_SESSION['profile_error'] = 'Şifre güncellenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
        }
        
        $this->redirect('user', 'profile');
    }
    
    /**
     * Profil fotoğrafı güncelleme
     * 
     * @return void
     */
    private function updateAvatar() {
        // Dosya yükleme kontrolü
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] != UPLOAD_ERR_OK) {
            $_SESSION['profile_error'] = 'Dosya yüklenirken bir hata oluştu.';
            $this->redirect('user', 'profile');
        }
        
        // Dosya türünü kontrol et
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['avatar']['type'], $allowedTypes)) {
            $_SESSION['profile_error'] = 'Yalnızca JPEG, PNG ve GIF görüntüleri yükleyebilirsiniz.';
            $this->redirect('user', 'profile');
        }
        
        // Dosya boyutunu kontrol et (2MB limit)
        if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            $_SESSION['profile_error'] = 'Dosya boyutu 2MB\'ı geçemez.';
            $this->redirect('user', 'profile');
        }
        
        // Yükleme klasörünü oluştur
        $uploadDir = UPLOAD_PATH . 'avatars/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Benzersiz dosya adı oluştur
        $filename = $this->user['id'] . '_' . time() . '_' . basename($_FILES['avatar']['name']);
        $targetFile = $uploadDir . $filename;
        
        // Dosyayı kaydet
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
            // Veritabanını güncelle
            $userModel = new \Models\User($this->db);
            $result = $userModel->updateAvatar($this->user['id'], $targetFile);
            
            if ($result) {
                $_SESSION['profile_success'] = 'Profil fotoğrafınız başarıyla güncellendi.';
            } else {
                $_SESSION['profile_error'] = 'Profil fotoğrafı güncellenirken bir hata oluştu.';
                // Yüklenen dosyayı sil
                unlink($targetFile);
            }
        } else {
            $_SESSION['profile_error'] = 'Dosya yüklenirken bir hata oluştu.';
        }
        
        $this->redirect('user', 'profile');
    }
    
    /**
     * Çıkış yapma
     * 
     * @return void
     */
    public function logout() {
        $this->sessionHelper->logout();
        $this->redirect('home');
    }
    
    /**
     * Cihazdan çıkış yapma
     * 
     * @return void
     */
    public function logoutDevice() {
        if (!$this->user) {
            $this->redirect('user', 'login');
        }
        
        $deviceId = filter_input(INPUT_GET, 'device_id', FILTER_VALIDATE_INT);
        
        if ($deviceId) {
            $userModel = new \Models\User($this->db);
            $userModel->logoutDevice($this->user['id'], $deviceId);
        }
        
        $this->redirect('user', 'profile');
    }
    
    /**
     * Tüm cihazlardan çıkış yapma
     * 
     * @return void
     */
    public function logoutAllDevices() {
        if (!$this->user) {
            $this->redirect('user', 'login');
        }
        
        $userModel = new \Models\User($this->db);
        $userModel->logoutAllDevices($this->user['id']);
        
        // Mevcut oturumu koru
        $this->sessionHelper->refresh();
        
        $this->redirect('user', 'profile');
    }
    
    /**
     * Abonelik planı değiştirme sayfası
     * 
     * @return void
     */
    public function subscription() {
        if (!$this->user) {
            $this->redirect('user', 'login');
        }
        
        $this->pageTitle = 'Abonelik Planları - Octaverum AI';
        
        $userModel = new \Models\User($this->db);
        $subscription = $userModel->getUserSubscription($this->user['id']);
        
        $this->render('user/subscription', [
            'subscription' => $subscription,
            'plans' => SUBSCRIPTION_PLANS
        ]);
    }
    
    /**
     * Aktivasyon e-postası gönderme
     * 
     * @param string $email E-posta adresi
     * @param string $token Aktivasyon token'ı
     * @return void
     */
    private function sendActivationEmail($email, $token) {
        $to = $email;
        $subject = 'Octaverum AI - Hesap Aktivasyonu';
        
        $activationLink = BASE_URL . '/index.php?route=user&action=activate&token=' . $token;
        
        $message = "
        <html>
        <head>
          <title>Octaverum AI - Hesap Aktivasyonu</title>
        </head>
        <body>
          <h1>Octaverum AI'ye Hoş Geldiniz!</h1>
          <p>Hesabınızı aktifleştirmek için lütfen aşağıdaki bağlantıya tıklayın:</p>
          <p><a href=\"$activationLink\">Hesabımı Aktifleştir</a></p>
          <p>Veya aşağıdaki bağlantıyı tarayıcınıza kopyalayın:</p>
          <p>$activationLink</p>
          <p>Bu aktivasyon bağlantısı 24 saat boyunca geçerlidir.</p>
          <p>Saygılarımızla,<br>Octaverum AI Ekibi</p>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: Octaverum AI <no-reply@octaverumapp.com>' . "\r\n";
        
        mail($to, $subject, $message, $headers);
    }
    
    /**
     * Şifre sıfırlama e-postası gönderme
     * 
     * @param string $email E-posta adresi
     * @param string $token Sıfırlama token'ı
     * @return void
     */
    private function sendPasswordResetEmail($email, $token) {
        $to = $email;
        $subject = 'Octaverum AI - Şifre Sıfırlama';
        
        $resetLink = BASE_URL . '/index.php?route=user&action=resetPassword&token=' . $token;
        
        $message = "
        <html>
        <head>
          <title>Octaverum AI - Şifre Sıfırlama</title>
        </head>
        <body>
          <h1>Şifre Sıfırlama İsteği</h1>
          <p>Şifrenizi sıfırlamak için talebiniz alındı. Şifrenizi sıfırlamak için lütfen aşağıdaki bağlantıya tıklayın:</p>
          <p><a href=\"$resetLink\">Şifremi Sıfırla</a></p>
          <p>Veya aşağıdaki bağlantıyı tarayıcınıza kopyalayın:</p>
          <p>$resetLink</p>
          <p>Bu sıfırlama bağlantısı 1 saat boyunca geçerlidir.</p>
          <p>Eğer bu talebi siz yapmadıysanız, lütfen bu e-postayı dikkate almayın ve hesabınızın güvenliğini kontrol edin.</p>
          <p>Saygılarımızla,<br>Octaverum AI Ekibi</p>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: Octaverum AI <no-reply@octaverumapp.com>' . "\r\n";
        
        mail($to, $subject, $message, $headers);
    }
}
