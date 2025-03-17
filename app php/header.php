<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $siteDescription; ?>">
    <meta name="keywords" content="<?php echo $siteKeywords; ?>">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Google Fonts: Orbitron ve Rajdhani -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Dosyaları -->
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>index.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>App.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>Sidebar.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>MusicPlayer.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>PromptGenerator.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>WelcomeModal.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>Settings.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>Profile.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>PlaylistCreationModal.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>ContextMenu.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>MobileOptimizations.css">
    
    <!-- Cihaz algılama için JavaScript - PHP header içinde çünkü sayfa yüklenmeden önce cihaz algılanmalı -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cihaz piksel oranını ve viewport boyutunu cookie'ye kaydet
        document.cookie = "device_pixel_ratio=" + window.devicePixelRatio;
        document.cookie = "viewport_width=" + window.innerWidth;
        document.cookie = "viewport_height=" + window.innerHeight;
        
        // Azaltılmış hareket tercihini kontrol et ve cookie'ye kaydet
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        document.cookie = "prefers_reduced_motion=" + prefersReducedMotion;
        
        // Cihaz yönünü belirle ve cookie'ye kaydet
        const isLandscape = window.innerWidth > window.innerHeight;
        document.cookie = "is_landscape=" + isLandscape;
        
        // Dokunmatik cihaz kontrolü
        const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        document.cookie = "is_touch_device=" + isTouchDevice;
        
        // Performance optimizasyonlarını uygula
        if (prefersReducedMotion || window.devicePixelRatio < 2 || window.innerWidth < 480) {
            document.documentElement.classList.add('reduced-motion');
            
            // Ağır animasyonları devre dışı bırak
            const heavyElements = document.querySelectorAll('.galaxy-background, .wave-container');
            heavyElements.forEach(el => {
                el.style.display = 'none';
            });
        }
    });
    
    // Tema değişikliklerini hemen uygula
    function applyTheme(theme) {
        if (!theme) return;
        
        document.documentElement.style.setProperty('--primary-color', theme.neonColor);
        document.documentElement.style.setProperty('--neon-glow', 
            `0 0 ${theme.glowIntensity * 0.1}px ${theme.neonColor}, 0 0 ${theme.glowIntensity * 0.2}px ${theme.neonColor}`
        );
        
        // Tema modunu ayarla (aydınlık/karanlık)
        if (!theme.darkMode) {
            document.documentElement.style.setProperty('--background-dark', '#e6e6f0');
            document.documentElement.style.setProperty('--background-light', '#d6d6e0');
            document.documentElement.style.setProperty('--text-color', '#1a1a2e');
        } else {
            document.documentElement.style.setProperty('--background-dark', '#0a0a12');
            document.documentElement.style.setProperty('--background-light', '#1a1a2e');
            document.documentElement.style.setProperty('--text-color', '#e0e0e0');
        }
    }
    </script>
    
    <!-- PHP ile dinamik CSS değişkenlerini ayarla -->
    <style>
        :root {
            --primary-color: <?php echo $settings['theme']['neonColor']; ?>;
            --secondary-color: <?php echo shiftColor($settings['theme']['neonColor'], 180); ?>;
            --accent-color: <?php echo shiftColor($settings['theme']['neonColor'], 90); ?>;
            --background-dark: <?php echo $settings['theme']['darkMode'] ? '#0a0a12' : '#e6e6f0'; ?>;
            --background-light: <?php echo $settings['theme']['darkMode'] ? '#1a1a2e' : '#d6d6e0'; ?>;
            --text-color: <?php echo $settings['theme']['darkMode'] ? '#e0e0e0' : '#1a1a2e'; ?>;
            --neon-glow: 0 0 <?php echo $settings['theme']['glowIntensity'] * 0.1; ?>px <?php echo $settings['theme']['neonColor']; ?>, 
                         0 0 <?php echo $settings['theme']['glowIntensity'] * 0.2; ?>px <?php echo $settings['theme']['neonColor']; ?>;
        }
    </style>
</head>
<body>
    <?php 
    // Body başlangıcından hemen sonra sayfa yüklenirken gösterilecek yükleme göstergesi
    // Gerçek uygulamada AJAX çağrılarıyla gizlenebilir
    ?>
    <div id="pageLoader" class="page-loader">
        <div class="loader-content">
            <div class="loader-icon"></div>
            <div class="loader-text">Octaverum AI Yükleniyor...</div>
        </div>
    </div>
    
    <script>
    // Sayfa yüklendikten sonra yükleme göstergesini gizle
    window.addEventListener('load', function() {
        const loader = document.getElementById('pageLoader');
        if (loader) {
            loader.style.opacity = '0';
            setTimeout(function() {
                loader.style.display = 'none';
            }, 500);
        }
    });
    </script>