<?php
/**
 * Octaverum AI - Otomatik Sınıf Yükleme Sistemi
 * 
 * Bu dosya, sınıfların otomatik olarak yüklenmesini sağlar.
 */

spl_autoload_register(function ($className) {
    // Namespace ayırıcıyı değiştir
    $className = str_replace('\\', '/', $className);
    
    // Klasör yollarını tanımla
    $controllers = 'controllers/';
    $models = 'models/';
    $helpers = 'helpers/';
    
    // Dosya uzantısı
    $extension = '.php';
    
    // Olası dosya yolları
    $paths = [
        $controllers . $className . $extension,
        $models . $className . $extension,
        $helpers . $className . $extension
    ];
    
    // Namespace'siz sınıf adını al
    $classNameWithoutNamespace = basename(str_replace('\\', '/', $className));
    
    // Namespaceli versiyonları da kontrol et
    $paths[] = $controllers . $classNameWithoutNamespace . $extension;
    $paths[] = $models . $classNameWithoutNamespace . $extension;
    $paths[] = $helpers . $classNameWithoutNamespace . $extension;
    
    // Tüm klasörlerde arama yap
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
    
    // Özel klasör yapısı için (namespace ile klasör hiyerarşisi)
    if (strpos($className, 'Controllers\\') === 0) {
        $className = substr($className, strlen('Controllers\\'));
        $path = $controllers . $className . $extension;
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    } 
    else if (strpos($className, 'Models\\') === 0) {
        $className = substr($className, strlen('Models\\'));
        $path = $models . $className . $extension;
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
    else if (strpos($className, 'Helpers\\') === 0) {
        $className = substr($className, strlen('Helpers\\'));
        $path = $helpers . $className . $extension;
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
    
    // Sınıf bulunamazsa hata log
    $errorMessage = "Could not load class: $className. Checked paths: " . implode(', ', $paths);
    error_log($errorMessage);
});
