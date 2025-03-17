<?php
/**
 * Octaverum - AI Müzik Uygulaması
 * Yardımcı Fonksiyonlar
 */

require_once 'config.php';

/**
 * XSS saldırılarına karşı çıktı temizleme
 * @param string $data
 * @return string
 */
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * URL yönlendirme
 * @param string $path
 * @return void
 */
function redirect($path) {
    header("Location: " . BASE_URL . "/" . $path);
    exit;
}

/**
 * Form verilerini filtreleme
 * @param string $data
 * @return string
 */
function filterInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = escape($data);
    return $data;
}

/**
 * CSRF token oluşturma
 * @return string
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token doğrulama
 * @param string $token
 * @return boolean
 */
function validateCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Flash mesaj oluşturma (bir sonraki sayfada gösterilecek)
 * @param string $message
 * @param string $type (error, success, warning, info)
 * @return void
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Flash mesajı gösterme ve temizleme
 * @return string|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flashMessage = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flashMessage;
    }
    return null;
}

/**
 * Tarih biçimlendirme (insan dostu)
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'd.m.Y H:i') {
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Zaman farkını insan dostu hale getirme
 * @param string $date
 * @return string
 */
function timeAgo($date) {
    $timestamp = strtotime($date);
    $difference = time() - $timestamp;
    
    $periods = [
        31536000 => 'yıl',
        2592000 => 'ay',
        604800 => 'hafta',
        86400 => 'gün',
        3600 => 'saat',
        60 => 'dakika',
        1 => 'saniye'
    ];
    
    foreach ($periods as $seconds => $label) {
        $count = floor($difference / $seconds);
        
        if ($count > 0) {
            if ($count == 1) {
                return "1 {$label} önce";
            } else {
                return "{$count} {$label} önce";
            }
        }
    }
    
    return "az önce";
}

/**
 * Metin kısaltma
 * @param string $text
 * @param int $limit
 * @param string $end
 * @return string
 */
function truncateText($text, $limit = 100, $end = '...') {
    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }
    
    return mb_substr($text, 0, $limit, 'UTF-8') . $end;
}

/**
 * Base64 ile URL dostu string oluşturma
 * @param string $string
 * @return string
 */
function base64UrlEncode($string) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
}

/**
 * Base64 URL dostu string'i çözme
 * @param string $string
 * @return string
 */
function base64UrlDecode($string) {
    return base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
}

/**
 * Dosya boyutunu insan dostu formata çevirme
 * @param int $bytes
 * @param int $precision
 * @return string
 */
function formatFileSize($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Dosya uzantısını kontrol etme
 * @param string $filename
 * @param array $allowedExtensions
 * @return boolean
 */
function isAllowedExtension($filename, $allowedExtensions = ALLOWED_EXTENSIONS) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowedExtensions);
}

/**
 * Güvenli dosya yükleme
 * @param array $file ($_FILES['file'])
 * @param string $directory
 * @param array $allowedExtensions
 * @param int $maxSize
 * @return array [success, message, filename]
 */
function uploadFile($file, $directory = UPLOAD_DIR, $allowedExtensions = ALLOWED_EXTENSIONS, $maxSize = MAX_FILE_SIZE) {
    $result = [
        'success' => false,
        'message' => '',
        'filename' => ''
    ];
    
    // Dosya boş mu kontrol et
    if (!isset($file) || $file['error'] == UPLOAD_ERR_NO_FILE) {
        $result['message'] = 'Dosya seçilmedi';
        return $result;
    }
    
    // Hata kontrolü
    if ($file['error'] != UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'Dosya PHP yapılandırmasındaki maksimum boyutu aşıyor',
            UPLOAD_ERR_FORM_SIZE => 'Dosya form tarafından belirlenen maksimum boyutu aşıyor',
            UPLOAD_ERR_PARTIAL => 'Dosya sadece kısmen yüklendi',
            UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör yok',
            UPLOAD_ERR_CANT_WRITE => 'Disk yazma hatası',
            UPLOAD_ERR_EXTENSION => 'PHP uzantısı dosya yüklemeyi durdurdu',
        ];
        
        $errorCode = $file['error'];
        $result['message'] = isset($errorMessages[$errorCode]) ? $errorMessages[$errorCode] : 'Bilinmeyen hata';
        return $result;
    }
    
    // Dosya boyutu kontrolü
    if ($file['size'] > $maxSize) {
        $result['message'] = 'Dosya boyutu çok büyük. Maksimum ' . formatFileSize($maxSize) . ' olmalıdır';
        return $result;
    }
    
    // Dosya uzantısı kontrolü
    $filename = $file['name'];
    if (!isAllowedExtension($filename, $allowedExtensions)) {
        $result['message'] = 'Dosya türü izin verilmiyor. İzin verilen türler: ' . implode(', ', $allowedExtensions);
        return $result;
    }
    
    // Dosya adını güvenli hale getirme
    $fileInfo = pathinfo($filename);
    $extension = strtolower($fileInfo['extension']);
    $safeName = preg_replace("/[^a-z0-9]/", "-", strtolower($fileInfo['filename']));
    $uniqueName = $safeName . '-' . uniqid() . '.' . $extension;
    
    // Dizin var mı kontrol et, yoksa oluştur
    if (!is_dir($directory)) {
        if (!mkdir($directory, 0755, true)) {
            $result['message'] = 'Yükleme dizini oluşturulamadı';
            return $result;
        }
    }
    
    // Dosyayı taşı
    $destination = rtrim($directory, '/') . '/' . $uniqueName;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $result['message'] = 'Dosya yüklenirken bir hata oluştu';
        return $result;
    }
    
    $result['success'] = true;
    $result['message'] = 'Dosya başarıyla yüklendi';
    $result['filename'] = $uniqueName;
    
    return $result;
}

/**
 * API isteği gönderme
 * @param string $url
 * @param array $data
 * @param string $method
 * @param array $headers
 * @return array
 */
function apiRequest($url, $data = [], $method = 'GET', $headers = []) {
    $result = [
        'success' => false,
        'data' => null,
        'error' => ''
    ];
    
    $ch = curl_init();
    
    // Metod ayarları
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif (!empty($data)) {
        $url .= '?' . http_build_query($data);
    }
    
    // Temel cURL ayarları
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Varsayılan header'lar
    $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    // Özel header'lar
    $requestHeaders = array_merge($defaultHeaders, $headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
    
    // İsteği çalıştır
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Hata kontrolü
    if (curl_errno($ch)) {
        $result['error'] = curl_error($ch);
    } else {
        // Cevabı decode et
        $decoded = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $result['success'] = true;
            $result['data'] = $decoded ?: $response;
        } else {
            $result['error'] = $decoded['message'] ?? "API hatası: HTTP $httpCode";
            $result['data'] = $decoded;
        }
    }
    
    curl_close($ch);
    return $result;
}

/**
 * Suno API'sine müzik oluşturma isteği gönderme
 * @param array $params
 * @return array
 */
function generateMusic($params) {
    $apiKey = SUNO_API_KEY;
    if (empty($apiKey)) {
        return [
            'success' => false,
            'error' => 'API anahtarı tanımlanmamış'
        ];
    }
    
    $url = SUNO_API_URL . '/api/v1/generate';
    $headers = [
        'Authorization: Bearer ' . $apiKey
    ];
    
    return apiRequest($url, $params, 'POST', $headers);
}

/**
 * Suno API'sine şarkı sözü oluşturma isteği gönderme
 * @param array $params
 * @return array
 */
function generateLyrics($params) {
    $apiKey = SUNO_API_KEY;
    if (empty($apiKey)) {
        return [
            'success' => false,
            'error' => 'API anahtarı tanımlanmamış'
        ];
    }
    
    $url = SUNO_API_URL . '/api/v1/lyrics';
    $headers = [
        'Authorization: Bearer ' . $apiKey
    ];
    
    return apiRequest($url, $params, 'POST', $headers);
}

/**
 * Suno API'sine görev durumu sorgulama isteği gönderme
 * @param string $taskId
 * @return array
 */
function checkTaskStatus($taskId) {
    $apiKey = SUNO_API_KEY;
    if (empty($apiKey)) {
        return [
            'success' => false,
            'error' => 'API anahtarı tanımlanmamış'
        ];
    }
    
    $url = SUNO_API_URL . '/api/v1/status';
    $headers = [
        'Authorization: Bearer ' . $apiKey
    ];
    
    return apiRequest($url, ['taskId' => $taskId], 'POST', $headers);
}

/**
 * JSON döndürme
 * @param array $data
 * @param int $statusCode
 * @return void
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Şu anki sayfanın URL'ini alma
 * @return string
 */
function getCurrentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $requestUri = $_SERVER['REQUEST_URI'];
    return $protocol . $host . $requestUri;
}

/**
 * SEO dostu URL oluşturma
 * @param string $text
 * @return string
 */
function slugify($text) {
    // Türkçe karakterleri dönüştür
    $turkishChars = ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'];
    $latinChars = ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c'];
    $text = str_replace($turkishChars, $latinChars, $text);
    
    // Küçük harfe çevir
    $text = strtolower($text);
    
    // Alfanumerik olmayan karakterleri tire ile değiştir
    $text = preg_replace('/[^a-z0-9]/', '-', $text);
    
    // Birden fazla tireyi tek tire yap
    $text = preg_replace('/-+/', '-', $text);
    
    // Baştaki ve sondaki tireleri temizle
    $text = trim($text, '-');
    
    return $text;
}

/**
 * Rastgele bir şifre oluşturma
 * @param int $length
 * @return string
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * İki tarih arasındaki farkı hesaplama
 * @param string $date1
 * @param string $date2
 * @param string $differenceFormat
 * @return string
 */
function dateDifference($date1, $date2, $differenceFormat = '%a') {
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);
    $interval = $datetime1->diff($datetime2);
    return $interval->format($differenceFormat);
}

/**
 * Veri geçerliliğini kontrol etme
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * JSON verisini alma (örn. POST isteği)
 * @return array
 */
function getJsonInput() {
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?: [];
}

/**
 * Kullanıcı cihaz bilgilerini alma
 * @return array
 */
function getUserDeviceInfo() {
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    
    $deviceType = 'Unknown';
    $browser = 'Unknown';
    $os = 'Unknown';
    
    // Mobil cihaz tespiti
    if (preg_match('/(android|webos|iphone|ipad|ipod|blackberry|windows phone)/i', $ua)) {
        $deviceType = 'Mobile';
    } elseif (preg_match('/(tablet|ipad)/i', $ua)) {
        $deviceType = 'Tablet';
    } else {
        $deviceType = 'Desktop';
    }
    
    // İşletim Sistemi tespiti
    if (preg_match('/windows/i', $ua)) {
        $os = 'Windows';
    } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
        $os = 'MacOS';
    } elseif (preg_match('/linux/i', $ua)) {
        $os = 'Linux';
    } elseif (preg_match('/android/i', $ua)) {
        $os = 'Android';
    } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
        $os = 'iOS';
    }
    
    // Tarayıcı tespiti
    if (preg_match('/MSIE/i', $ua) || preg_match('/Trident/i', $ua)) {
        $browser = 'Internet Explorer';
    } elseif (preg_match('/Firefox/i', $ua)) {
        $browser = 'Firefox';
    } elseif (preg_match('/Chrome/i', $ua)) {
        $browser = 'Chrome';
    } elseif (preg_match('/Safari/i', $ua)) {
        $browser = 'Safari';
    } elseif (preg_match('/Opera|OPR/i', $ua)) {
        $browser = 'Opera';
    } elseif (preg_match('/Edge/i', $ua)) {
        $browser = 'Edge';
    }
    
    return [
        'userAgent' => $ua,
        'deviceType' => $deviceType,
        'browser' => $browser,
        'os' => $os,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ];
}
