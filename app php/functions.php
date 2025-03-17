<?php
/**
 * Octaverum AI Yardımcı Fonksiyonlar
 * Uygulama genelinde kullanılan çeşitli yardımcı fonksiyonlar
 */

/**
 * Cihaz sınıfını döndürür (mobil, düşük performanslı vb.)
 * 
 * @return string Cihaz sınıfı
 */
function getDeviceClass() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $isMobile = preg_match('/(android|iphone|ipad|ipod|blackberry|windows phone)/i', $userAgent);
    $isLowEndDevice = false;
    $isLandscape = false;
    
    // Kullanıcı ajanını kontrol ederek düşük performanslı cihazları tespit et
    // Gerçek bir uygulamada daha karmaşık kontroller yapılabilir
    if ($isMobile && (strpos($userAgent, 'Android 4') !== false || 
                      strpos($userAgent, 'iPhone OS 9') !== false ||
                      strpos($userAgent, 'iPhone OS 8') !== false)) {
        $isLowEndDevice = true;
    }
    
    // Eğer ekran boyutu JavaScript ile kontrol ediliyorsa, bu PHP tarafından tespit edilemez.
    // Bu durumda varsayılan olarak portre modu kabul edelim
    
    $className = '';
    if ($isMobile) $className .= 'mobile ';
    if ($isLowEndDevice) $className .= 'low-performance ';
    if ($isLandscape) $className .= 'landscape ';
    
    return trim($className);
}

/**
 * Yüksek performanslı özelliklerin etkinleştirilip etkinleştirilmeyeceğini kontrol eder
 * 
 * @return bool Yüksek performanslı özellikler etkinleştirilmeli mi
 */
function shouldEnableHighPerformanceFeatures() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $isMobile = preg_match('/(android|iphone|ipad|ipod|blackberry|windows phone)/i', $userAgent);
    $isLowEndDevice = false;
    $prefersReducedMotion = isset($_COOKIE['prefers_reduced_motion']) && $_COOKIE['prefers_reduced_motion'] === 'true';
    
    // Kullanıcı ajanını kontrol ederek düşük performanslı cihazları tespit et
    if ($isMobile && (strpos($userAgent, 'Android 4') !== false || 
                      strpos($userAgent, 'iPhone OS 9') !== false ||
                      strpos($userAgent, 'iPhone OS 8') !== false)) {
        $isLowEndDevice = true;
    }
    
    return !$isLowEndDevice && !$prefersReducedMotion && (!$isMobile || isset($_COOKIE['viewport_width']) && $_COOKIE['viewport_width'] >= 768);
}

/**
 * Dokunmatik cihaz olup olmadığını kontrol eder
 * 
 * @return bool Dokunmatik cihaz mı
 */
function isTouchDevice() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    return preg_match('/(android|iphone|ipad|ipod|blackberry|windows phone)/i', $userAgent);
}

/**
 * Mobil cihaz olup olmadığını kontrol eder
 * 
 * @return bool Mobil cihaz mı
 */
function isMobileDevice() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    return preg_match('/(android|iphone|ipod|blackberry|windows phone)/i', $userAgent);
}

/**
 * Belirtilen dosyayı okunabilir bir şekilde içe aktarır
 * 
 * @param string $filePath Dosya yolu
 * @return string HTML güvenli dosya içeriği
 */
function includeWithSafety($filePath) {
    if (file_exists($filePath)) {
        ob_start();
        include $filePath;
        return ob_get_clean();
    }
    return '';
}

/**
 * HTML çıktısı için XSS koruması
 * 
 * @param string $text Temizlenecek metin
 * @return string Temizlenmiş metin
 */
function sanitizeOutput($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Tarihi okunabilir formatta döndürür
 * 
 * @param string $dateString Tarih metni
 * @return string Formatlanmış tarih
 */
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('d F Y');
}

/**
 * Ayarlardan renk değiştirmesi
 * React uygulamasındaki shiftColor fonksiyonuna eşdeğer
 * 
 * @param string $hex Hex renk kodu
 * @param int $shiftDegree Kaydırma derecesi
 * @return string Kaydırılmış hex renk kodu
 */
function shiftColor($hex, $shiftDegree) {
    // Hex'i RGB'ye dönüştür
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // RGB'yi HSL'ye dönüştür
    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $h = 0;
    $s = 0;
    $l = ($max + $min) / 2;
    
    if ($max !== $min) {
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        
        switch ($max) {
            case $r:
                $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                break;
            case $g:
                $h = ($b - $r) / $d + 2;
                break;
            case $b:
                $h = ($r - $g) / $d + 4;
                break;
        }
        
        $h /= 6;
    }
    
    // Renk tekerleğinde kaydırma
    $h = fmod(($h * 360 + $shiftDegree), 360);
    if ($h < 0) $h += 360;
    $h /= 360;
    
    // HSL'den RGB'ye geri dönüştür
    if ($s === 0) {
        $r1 = $g1 = $b1 = $l;
    } else {
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        
        $r1 = hue2rgb($p, $q, $h + 1/3);
        $g1 = hue2rgb($p, $q, $h);
        $b1 = hue2rgb($p, $q, $h - 1/3);
    }
    
    // RGB'yi Hex'e dönüştür
    return sprintf('#%02x%02x%02x', 
        round($r1 * 255), 
        round($g1 * 255), 
        round($b1 * 255)
    );
}

/**
 * HSL'den RGB'ye dönüşüm yardımcı fonksiyonu
 * 
 * @param float $p P değeri
 * @param float $q Q değeri
 * @param float $t T değeri
 * @return float RGB bileşeni
 */
function hue2rgb($p, $q, $t) {
    if ($t < 0) $t += 1;
    if ($t > 1) $t -= 1;
    if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
    if ($t < 1/2) return $q;
    if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
    return $p;
}

/**
 * Rengi belirtilen yüzde oranında aydınlatır
 * 
 * @param string $hex Hex renk kodu
 * @param int $percent Aydınlatma yüzdesi
 * @return string Aydınlatılmış hex renk kodu
 */
function lightenColor($hex, $percent) {
    // Hex'i RGB'ye dönüştür
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // RGB'yi HSL'ye dönüştür
    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $h = 0;
    $s = 0;
    $l = ($max + $min) / 2;
    
    if ($max !== $min) {
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        
        switch ($max) {
            case $r:
                $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                break;
            case $g:
                $h = ($b - $r) / $d + 2;
                break;
            case $b:
                $h = ($r - $g) / $d + 4;
                break;
        }
        
        $h /= 6;
    }
    
    // Aydınlık değerini artır
    $l = min(1, $l + $percent / 100);
    
    // HSL'den RGB'ye geri dönüştür
    if ($s === 0) {
        $r1 = $g1 = $b1 = $l;
    } else {
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        
        $r1 = hue2rgb($p, $q, $h + 1/3);
        $g1 = hue2rgb($p, $q, $h);
        $b1 = hue2rgb($p, $q, $h - 1/3);
    }
    
    // RGB'yi Hex'e dönüştür
    return sprintf('#%02x%02x%02x', 
        round($r1 * 255), 
        round($g1 * 255), 
        round($b1 * 255)
    );
}

/**
 * Şarkı için demo şarkı sözleri oluşturur
 * 
 * @param string $promptText Prompt metni
 * @return string Oluşturulan demo şarkı sözleri
 */
function generateDemoLyrics($promptText) {
    $themes = array_filter(explode(' ', $promptText), function($word) {
        return strlen($word) > 3;
    });
    
    if (empty($themes)) {
        return 'Şarkı sözü oluşturmak için bir prompt girin';
    }
    
    // Şablon dizeler
    $verses = [
        "In the world of " . ($themes[0] ?? 'dreams') . ",\nWhere " . ($themes[1] ?? 'shadows') . " come alive.\nI find myself lost in " . ($themes[2] ?? 'time') . ",\nTrying to " . ($themes[0] ?? 'survive') . ".",
        "The " . ($themes[1] ?? 'night') . " is calling,\nEchoes of " . ($themes[0] ?? 'distant') . " sounds.\nWe're " . ($themes[2] ?? 'falling') . " through space,\nNo gravity " . ($themes[0] ?? 'bounds') . ".",
        "Lights flashing in the dark,\n" . ($themes[1] ?? 'Memories') . " fading away.\nThe " . ($themes[2] ?? 'city') . " never sleeps,\nAs we continue to " . ($themes[0] ?? 'play') . "."
    ];
    
    $chorus = "\n\nChorus:\n" . ($themes[0] ?? 'Time') . " after " . ($themes[1] ?? 'time') . ",\nWe chase the " . ($themes[2] ?? 'light') . ".\n" . ($themes[0] ?? 'Dreams') . " becoming real,\nThrough the " . ($themes[1] ?? 'night') . ".";
    
    // Kıtaları ve nakaratı birleştir
    return implode("\n\n", $verses) . $chorus;
}

/**
 * Prompt'a dayalı müzik başlığı oluşturur
 * 
 * @param string $promptText Prompt metni
 * @return string Oluşturulan müzik başlığı
 */
function generateMusicTitle($promptText) {
    $words = array_filter(explode(' ', $promptText), function($word) {
        return strlen($word) > 3;
    });
    
    if (empty($words)) {
        return 'Untitled Composition';
    }
    
    if (count($words) === 1) {
        return ucfirst($words[0]);
    }
    
    // Prompttan 2 rastgele kelime seçin
    $word1 = $words[array_rand($words)];
    $word2 = $words[array_rand($words)];
    
    // word2'nin word1'den farklı olduğundan emin ol
    while (count($words) > 1 && $word2 === $word1) {
        $word2 = $words[array_rand($words)];
    }
    
    // İlk harfleri büyült
    $title1 = ucfirst($word1);
    $title2 = ucfirst($word2);
    
    return "$title1 $title2";
}
?>