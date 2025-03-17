<?php
/**
 * Octaverum AI - Ana Giriş Dosyası
 * 
 * Bu dosya, uygulamanın giriş noktasıdır. Tüm HTTP istekleri bu dosya 
 * üzerinden işlenir ve ilgili denetleyicilere yönlendirilir.
 */

// Oturum başlat
session_start();

// Hata raporlamasını ayarla
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Zaman dilimini ayarla
date_default_timezone_set('Europe/Istanbul');

// Gerekli dosyaları dahil et
require_once 'config/config.php';
require_once 'core/autoload.php';
require_once 'core/Router.php';

// Veritabanı bağlantısını oluştur
$db = new Database();
$conn = $db->getConnection();

// Oturum yöneticisini başlat
$session = new \Helpers\SessionHelper();

// Yardımcı sınıfları yükle
$settingsHelper = new \Helpers\SettingsHelper($conn);
$deviceHelper = new \Helpers\DeviceHelper();

// Kullanıcı oturumunu kontrol et
if (isset($_SESSION['user_id'])) {
    $userModel = new \Models\User($conn);
    $currentUser = $userModel->getUserById($_SESSION['user_id']);
} else {
    $currentUser = null;
}

// Router'ı yapılandır ve çalıştır
$router = new Router();
$router->addControllers([
    'home' => new \Controllers\HomeController($conn, $settingsHelper),
    'music' => new \Controllers\MusicController($conn, $settingsHelper),
    'user' => new \Controllers\UserController($conn, $session),
    'api' => new \Controllers\ApiController($conn, $settingsHelper),
    'settings' => new \Controllers\SettingsController($conn, $settingsHelper)
]);

// İstek parametrelerini al
$route = isset($_GET['route']) ? $_GET['route'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Kullanıcının oturum durumuna göre erişim kontrolü
$publicRoutes = ['home', 'login', 'register', 'forgot-password'];

if (!$currentUser && !in_array($route, $publicRoutes) && $route !== 'user') {
    // Kullanıcı oturum açmamışsa ve korumalı bir sayfaya erişmeye çalışıyorsa,
    // oturum açma sayfasına yönlendir
    header('Location: index.php?route=user&action=login');
    exit;
}

// Router ile ilgili denetleyiciye yönlendir
$router->dispatch($route, $action, $id);
