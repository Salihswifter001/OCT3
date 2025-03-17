<?php
/**
 * Octaverum AI Ana Sayfa
 * React uygulamasından PHP'ye dönüştürülmüştür
 */

// Oturum başlat
session_start();

// Yapılandırma ve yardımcı fonksiyonları içe aktar
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Görünüm için değişkenler
$pageTitle = "Octaverum AI - Yapay Zeka Müzik Üretici";
$activeSection = isset($_GET['section']) ? $_GET['section'] : 'home';

// Hoşgeldiniz modalının durumunu kontrol et
$showWelcomeModal = false;
if (!isset($_SESSION['welcome_modal_shown'])) {
    $showWelcomeModal = true;
    $_SESSION['welcome_modal_shown'] = true;
}

// HTML başlık bölümünü içe aktar
include_once 'includes/header.php';
?>

<div class="app-container <?php echo getDeviceClass(); ?>">
    <?php 
    // Galaxy Background bileşenini yalnızca yüksek performanslı cihazlarda göster
    if (shouldEnableHighPerformanceFeatures()): 
    ?>
    <div class="galaxy-background" id="galaxyBackground"></div>
    <?php endif; ?>
    
    <!-- Müzik çalar için alt boşluk -->
    <div style="height: 90px;"></div>

    <?php 
    // Kenar çubuğunu içe aktar
    include_once 'includes/sidebar.php'; 
    ?>

    <main class="main-content">
        <?php
        // İçerik bölümü
        echo '<h1 class="title-animation octaverum-logo" style="color: var(--primary-color); text-shadow: 0 0 10px var(--primary-color), 0 0 20px var(--primary-color), 0 0 30px var(--primary-color);">Octaverum AI</h1>';
        
        // Aktif bölüme göre içeriği getir
        switch ($activeSection) {
            case 'library':
                include 'views/music_library.php';
                break;
            case 'home':
            default:
                include 'views/prompt_generator.php';
                break;
        }
        
        // Altbilgi bileşenini içe aktar
        include_once 'includes/footer.php';
        ?>
    </main>

    <?php 
    // Modal bileşenlerini koşullu olarak içe aktar
    if (isset($_SESSION['show_settings']) && $_SESSION['show_settings']): 
        include 'views/settings_modal.php';
    endif;
    
    if (isset($_SESSION['show_profile']) && $_SESSION['show_profile']):
        include 'views/profile_modal.php';
    endif;
    
    if (isset($_SESSION['show_playlist_creation']) && $_SESSION['show_playlist_creation']):
        include 'views/playlist_creation_modal.php';
    endif;
    
    if ($showWelcomeModal):
        include 'views/welcome_modal.php';
    endif;
    
    // Müzik çalar bileşenini içe aktar
    include 'views/music_player.php';
    ?>
</div>

<?php
// Hoşgeldiniz modal komut dosyasını koşullu olarak dahil et
if ($showWelcomeModal): 
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hoşgeldiniz modalını göster
    const welcomeModal = document.getElementById('welcomeModal');
    if (welcomeModal) {
        welcomeModal.style.display = 'flex';
    }
});
</script>
<?php endif; ?>

<?php
// Context menu için JavaScript
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cihaz özelliklerine göre sağ tıklama menüsü
    if (!isTouchDevice() || !isMobileDevice()) {
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            showContextMenu(e.pageX, e.pageY);
        });
        
        document.addEventListener('click', function() {
            hideContextMenu();
        });
    }
    
    // Klavye kısayolları
    if (!isMobileDevice()) {
        setupKeyboardShortcuts();
    }
});
</script>

<?php
// HTML alt kısmını içe aktar
include_once 'includes/footer_scripts.php';
?>