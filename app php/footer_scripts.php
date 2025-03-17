<!-- JavaScript dosyaları -->
<script src="<?php echo JS_URL; ?>device-service.js"></script>
<script src="<?php echo JS_URL; ?>settings-service.js"></script>
<script src="<?php echo JS_URL; ?>music-player.js"></script>
<script src="<?php echo JS_URL; ?>prompt-generator.js"></script>
<script src="<?php echo JS_URL; ?>galaxy-background.js"></script>
<script src="<?php echo JS_URL; ?>context-menu.js"></script>

<!-- Audio Wave visualizer için script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const audioVisualizerCanvas = document.getElementById('audioVisualizer');
    if (audioVisualizerCanvas) {
        const ctx = audioVisualizerCanvas.getContext('2d');
        
        // Canvas boyutlarını ayarla
        function resizeCanvas() {
            audioVisualizerCanvas.width = audioVisualizerCanvas.offsetWidth;
            audioVisualizerCanvas.height = audioVisualizerCanvas.offsetHeight;
        }
        
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);
        
        // Visualizer animasyonu
        const isGenerating = false; // Bu değişken prompt generator'dan alınabilir
        let animationId;
        
        function animate() {
            if (!ctx) return;
            
            const barCount = 60;
            const barWidth = audioVisualizerCanvas.width / barCount;
            const maxBarHeight = audioVisualizerCanvas.height * 0.8;
            const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
            const secondaryColor = getComputedStyle(document.documentElement).getPropertyValue('--secondary-color').trim();
            
            ctx.clearRect(0, 0, audioVisualizerCanvas.width, audioVisualizerCanvas.height);
            
            // Bar yüksekliklerini oluştur
            const barHeights = [];
            for (let i = 0; i < barCount; i++) {
                const height = isGenerating 
                    ? Math.random() * maxBarHeight 
                    : Math.sin((Date.now() / 1000) * Math.random()) * maxBarHeight * 0.3 + maxBarHeight * 0.1;
                barHeights.push(height);
            }
            
            // Barları çiz
            barHeights.forEach((height, index) => {
                const x = index * barWidth;
                const gradientHeight = height * 1.2;
                
                const gradient = ctx.createLinearGradient(0, audioVisualizerCanvas.height - gradientHeight, 0, audioVisualizerCanvas.height);
                gradient.addColorStop(0, primaryColor);
                gradient.addColorStop(1, secondaryColor);
                
                ctx.fillStyle = gradient;
                ctx.fillRect(x, audioVisualizerCanvas.height - height, barWidth - 1, height);
            });
            
            animationId = requestAnimationFrame(animate);
        }
        
        animate();
        
        // Temizleme fonksiyonu
        function cleanup() {
            window.removeEventListener('resize', resizeCanvas);
            cancelAnimationFrame(animationId);
        }
    }
});
</script>

<!-- Galaxy Background Animasyonu -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const galaxyCanvas = document.getElementById('galaxyBackground');
    if (galaxyCanvas && shouldEnableHighPerformanceFeatures()) {
        initializeGalaxyBackground();
    }
    
    function shouldEnableHighPerformanceFeatures() {
        // Düşük performanslı cihazlar ve hareket azaltma tercih edilenler için devre dışı bırak
        return !document.documentElement.classList.contains('reduced-motion') &&
               !document.documentElement.classList.contains('low-performance');
    }
    
    function initializeGalaxyBackground() {
        const canvas = document.createElement('canvas');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        galaxyCanvas.appendChild(canvas);
        
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        
        // Cihaz piksel oranına göre canvas netliğini ayarla
        const dpr = window.devicePixelRatio || 1;
        canvas.width = window.innerWidth * dpr;
        canvas.height = window.innerHeight * dpr;
        ctx.scale(dpr, dpr);
        canvas.style.width = `${window.innerWidth}px`;
        canvas.style.height = `${window.innerHeight}px`;
        
        // Pencere boyutu değiştiğinde yeniden boyutlandır
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth * dpr;
            canvas.height = window.innerHeight * dpr;
            ctx.scale(dpr, dpr);
            canvas.style.width = `${window.innerWidth}px`;
            canvas.style.height = `${window.innerHeight}px`;
        });
        
        // Parçacık sayısı - performansa göre ayarla
        const particleCount = Math.min(100, Math.floor((canvas.width * canvas.height) / 15000));
        const particles = [];
        
        // Parçacık sınıfı
        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width / dpr;
                this.y = Math.random() * canvas.height / dpr;
                this.size = Math.random() * 2 + 0.1;
                this.speedX = Math.random() * 0.5 - 0.25;
                this.speedY = Math.random() * 0.5 - 0.25;
                
                // Cyberpunk temasına uygun renkler
                const colors = ['#00ffff', '#ff00ff', '#ff0099', '#6600ff', '#00ff99'];
                this.color = colors[Math.floor(Math.random() * colors.length)];
                
                // Parlaklık değeri (titreşim efekti için)
                this.brightness = 0.8 + Math.random() * 0.2;
            }
            
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                
                // Parçacık canvas dışına çıkarsa, diğer taraftan geri getir
                if (this.x < 0) this.x = canvas.width / dpr;
                if (this.x > canvas.width / dpr) this.x = 0;
                if (this.y < 0) this.y = canvas.height / dpr;
                if (this.y > canvas.height / dpr) this.y = 0;
                
                // Parlaklık değerini rastgele değiştir (titreşim efekti için)
                this.brightness = 0.8 + Math.random() * 0.2;
            }
            
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                
                // Parlaklık değerine göre rengi ayarla
                ctx.fillStyle = this.color;
                ctx.globalAlpha = this.brightness;
                ctx.fill();
                
                // Parçacıklar için glow efekti
                ctx.shadowBlur = 15;
                ctx.shadowColor = this.color;
            }
        }
        
        // Parçacıkları oluştur
        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }
        
        // Animasyon döngüsü
        function animate() {
            // Canvas'ı temizle, ama tamamen saydam değil, iz bırakarak kaybolma efekti için
            ctx.fillStyle = 'rgba(10, 10, 18, 0.1)';
            ctx.fillRect(0, 0, canvas.width / dpr, canvas.height / dpr);
            
            // Parçacıkları güncelle ve çiz
            particles.forEach(particle => {
                particle.update();
                particle.draw();
            });
            
            // Parçacıklar arasındaki bağlantıları çiz
            for (let a = 0; a < particles.length; a++) {
                for (let b = a; b < particles.length; b++) {
                    const dx = particles[a].x - particles[b].x;
                    const dy = particles[a].y - particles[b].y;
                    const distance = Math.sqrt(dx * dx + dy * dy);
                    
                    // Belirli bir mesafeden daha yakın parçacıklar arasında çizgi çiz
                    if (distance < 100) {
                        ctx.beginPath();
                        ctx.strokeStyle = particles[a].color;
                        ctx.globalAlpha = 0.2 * (1 - distance / 100); // Mesafeye göre opaklık
                        ctx.lineWidth = 0.5;
                        ctx.moveTo(particles[a].x, particles[a].y);
                        ctx.lineTo(particles[b].x, particles[b].y);
                        ctx.stroke();
                        ctx.globalAlpha = 1;
                    }
                }
            }
            
            // Animasyonu devam ettir
            requestAnimationFrame(animate);
        }
        
        // Animasyonu başlat
        animate();
    }
});
</script>

<!-- Context Menu JavaScript -->
<script>
function showContextMenu(x, y) {
    const menu = document.getElementById('contextMenu');
    if (!menu) return;
    
    menu.style.display = 'block';
    menu.style.left = `${x}px`;
    menu.style.top = `${y}px`;
    
    // Menünün ekranın dışına taşmasını önle
    const menuRect = menu.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    
    if (menuRect.right > viewportWidth) {
        menu.style.left = `${x - menuRect.width}px`;
    }
    
    if (menuRect.bottom > viewportHeight) {
        menu.style.top = `${y - menuRect.height}px`;
    }
    
    // Animasyon ekle
    menu.style.animation = 'contextMenuFadeIn 0.2s ease forwards';
}

function hideContextMenu() {
    const menu = document.getElementById('contextMenu');
    if (menu) {
        menu.style.display = 'none';
    }
}

function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Meta tuşu (Command veya Windows tuşu) veya Ctrl tuşuyla birlikte
        const isMetaOrCtrl = e.metaKey || e.ctrlKey;
        
        // Odaklanılan element bir input veya textarea mı?
        const isInputActive = 
            document.activeElement instanceof HTMLInputElement ||
            document.activeElement instanceof HTMLTextAreaElement;
        
        // Eğer bir input alanına odaklanılmışsa, kısayolları çalıştırma
        if (isInputActive) return;
        
        // Müzik çalar kontrolü
        if (e.key === ' ' && !isInputActive) {
            e.preventDefault();
            const playButton = document.querySelector('.play-pause');
            if (playButton) playButton.click();
        }
        
        // Diğer kısayolları ekle
        switch (e.key) {
            case 'ArrowRight':
                if (!isInputActive) {
                    e.preventDefault();
                    const nextButton = document.querySelector('.next-button');
                    if (nextButton) nextButton.click();
                }
                break;
                
            case 'ArrowLeft':
                if (!isInputActive) {
                    e.preventDefault();
                    const prevButton = document.querySelector('.prev-button');
                    if (prevButton) prevButton.click();
                }
                break;
                
            case 'M':
                if (!isInputActive) {
                    e.preventDefault();
                    const muteButton = document.querySelector('.volume-button');
                    if (muteButton) muteButton.click();
                }
                break;
                
            case 's':
                if (isMetaOrCtrl) {
                    e.preventDefault();
                    // Ayarları aç
                    document.querySelector('.settings-button')?.click();
                }
                break;
        }
    });
}
</script>

<!-- Tema renk değişim animasyonu -->
<?php if (shouldEnableHighPerformanceFeatures()): ?>
<script>
// Yüksek performanslı cihazlarda tema renk animasyonu
document.addEventListener('DOMContentLoaded', function() {
    const mainColor = '<?php echo $settings["theme"]["neonColor"]; ?>';
    
    // Alternatif renkler (ana rengin etrafında varyasyonlar)
    const shiftedHue1 = shiftColor(mainColor, 20);
    const shiftedHue2 = shiftColor(mainColor, -20);
    const lightenedColor = lightenColor(mainColor, 20);
    
    const colors = [
        mainColor,
        shiftedHue1,
        lightenedColor,
        shiftedHue2,
        mainColor
    ];
    
    let colorIndex = 0;
    const logoElement = document.querySelector('.octaverum-logo');
    
    if (logoElement) {
        setInterval(() => {
            colorIndex = (colorIndex + 1) % colors.length;
            const newColor = colors[colorIndex];
            
            logoElement.style.color = newColor;
            logoElement.style.textShadow = `0 0 10px ${newColor}, 0 0 20px ${newColor}, 0 0 30px ${newColor}`;
        }, 2000); // Her 2 saniyede bir renk değiştir
    }
    
    // Yardımcı renk kaydırma fonksiyonları
    function shiftColor(hexColor, degree) {
        // JavaScript ile HSL renk kaydırma mantığı (PHP kodundaki ile benzer)
        return hexColor; // Basitlik için şimdilik orijinal rengi döndürelim
    }
    
    function lightenColor(hexColor, percent) {
        // JavaScript ile renk aydınlatma (PHP kodundaki ile benzer)
        return hexColor; // Basitlik için şimdilik orijinal rengi döndürelim
    }
});
</script>
<?php endif; ?>

</body>
</html>