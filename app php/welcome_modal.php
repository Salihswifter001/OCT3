<?php
/**
 * Octaverum AI Karşılama Modalı
 * İlk kez kullanıcılar için gösterilen tanıtım modalı
 */
?>

<div class="welcome-modal-overlay" id="welcomeModal">
    <div class="welcome-modal">
        <button class="welcome-close-button" id="welcomeCloseButton">×</button>
        
        <div class="welcome-header">
            <div class="welcome-logo">
                <div class="welcome-logo-glow"></div>
                <h1>Octaverum AI</h1>
            </div>
            <h2>Yapay Zeka Müzik Asistanınıza Hoş Geldiniz</h2>
        </div>

        <div class="welcome-progress" id="welcomeProgress">
            <!-- Adım göstergeleri JavaScript ile doldurulacak -->
        </div>
        
        <div class="welcome-content" id="welcomeContent">
            <!-- Adım içerikleri burada gösterilecek -->
        </div>
        
        <div class="welcome-footer">
            <button class="welcome-button prev-button" id="welcomePrevButton" style="display: none;">
                Geri
            </button>
            <button class="welcome-button next-button" id="welcomeNextButton">
                Devam
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('welcomeModal');
    const closeButton = document.getElementById('welcomeCloseButton');
    const prevButton = document.getElementById('welcomePrevButton');
    const nextButton = document.getElementById('welcomeNextButton');
    const progressContainer = document.getElementById('welcomeProgress');
    const contentContainer = document.getElementById('welcomeContent');
    
    let currentStep = 1;
    const totalSteps = 3;
    
    // Adım göstergelerini oluştur
    function createStepIndicators() {
        progressContainer.innerHTML = '';
        for (let i = 1; i <= totalSteps; i++) {
            const dot = document.createElement('div');
            dot.className = `progress-dot ${i <= currentStep ? 'active' : ''}`;
            dot.addEventListener('click', () => goToStep(i));
            progressContainer.appendChild(dot);
        }
    }
    
    // Adım içeriklerini yükle
    function loadStepContent(step) {
        // Adım içeriklerini tanımla
        const stepContents = [
            // Adım 1
            `<div class="welcome-step">
                <div class="step-icon">🎵</div>
                <h3>Müzik Üretim Gücü</h3>
                <p>
                    Octaverum AI, yapay zeka teknolojisini kullanarak saniyeler içinde profesyonel kalitede 
                    müzik üretmenizi sağlar. Synthwave'den Vaporwave'e, Cyberpunk'tan Ambient'e kadar 
                    geniş bir müzik yelpazesinde içerik oluşturabilirsiniz.
                </p>
                <div class="feature-list">
                    <div class="feature-item">
                        <span class="feature-icon">🤖</span>
                        <span>AI Tabanlı Müzik Üretimi</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🎛️</span>
                        <span>Tam Kontrolünüzde</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">⚡</span>
                        <span>Saniyeler İçinde Sonuç</span>
                    </div>
                </div>
            </div>`,
            
            // Adım 2
            `<div class="welcome-step">
                <div class="step-icon">🎹</div>
                <h3>Nasıl Kullanılır?</h3>
                <p>
                    Octaverum AI ile müzik üretmek son derece kolay. İşte başlamanıza yardımcı olacak 
                    birkaç temel adım:
                </p>
                <div class="tutorial-steps">
                    <div class="tutorial-step">
                        <div class="tutorial-number">1</div>
                        <div class="tutorial-text">
                            <strong>Prompt Oluşturun:</strong> 
                            "Yeni Oluştur" butonuna tıklayın ve müziğinizi tanımlayın
                        </div>
                    </div>
                    <div class="tutorial-step">
                        <div class="tutorial-number">2</div>
                        <div class="tutorial-text">
                            <strong>Türleri Seçin:</strong> 
                            İstediğiniz müzik türlerini seçerek AI'ı yönlendirin
                        </div>
                    </div>
                    <div class="tutorial-step">
                        <div class="tutorial-number">3</div>
                        <div class="tutorial-text">
                            <strong>Parametreleri Ayarlayın:</strong> 
                            Ayarlar menüsünden ses kalitesi, BPM ve müzik tonu gibi ayarları özelleştirin
                        </div>
                    </div>
                    <div class="tutorial-step">
                        <div class="tutorial-number">4</div>
                        <div class="tutorial-text">
                            <strong>Müziğinizi Üretin:</strong> 
                            "Müzik Oluştur" butonuna basın ve yapay zekanın çalışmasını izleyin
                        </div>
                    </div>
                </div>
            </div>`,
            
            // Adım 3
            `<div class="welcome-step">
                <div class="step-icon">🚀</div>
                <h3>Keşfetmeye Hazır Mısınız?</h3>
                <p>
                    Octaverum AI ile yaratıcı müzik yolculuğunuza başlamaya hazırsınız! Müzik kütüphanenizi 
                    oluşturun, çalma listeleri hazırlayın ve tamamen size özel müzik parçaları üretin.
                </p>
                <div class="final-features">
                    <div class="final-feature">
                        <div class="final-feature-icon">💾</div>
                        <div class="final-feature-text">
                            <h4>Kütüphane</h4>
                            <p>Ürettiğiniz tüm müzikleri kütüphanenizde saklayın ve düzenleyin</p>
                        </div>
                    </div>
                    <div class="final-feature">
                        <div class="final-feature-icon">🎧</div>
                        <div class="final-feature-text">
                            <h4>Çalma Listeleri</h4>
                            <p>Kişiselleştirilmiş çalma listeleri oluşturun ve paylaşın</p>
                        </div>
                    </div>
                    <div class="final-feature">
                        <div class="final-feature-icon">⚙️</div>
                        <div class="final-feature-text">
                            <h4>Özelleştirme</h4>
                            <p>Ayarlar menüsünden tüm özellikleri kişiselleştirin</p>
                        </div>
                    </div>
                </div>
            </div>`
        ];
        
        contentContainer.innerHTML = stepContents[step - 1];
    }
    
    // Belirtilen adıma git
    function goToStep(step) {
        currentStep = step;
        updateUI();
    }
    
    // Bir sonraki adıma git
    function nextStep() {
        if (currentStep < totalSteps) {
            currentStep++;
            updateUI();
        } else {
            // Son adımda ise modalı kapat
            closeModal();
        }
    }
    
    // Bir önceki adıma git
    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            updateUI();
        }
    }
    
    // UI'ı güncelle
    function updateUI() {
        // İçeriği ve göstergeleri güncelle
        loadStepContent(currentStep);
        createStepIndicators();
        
        // Düğmeleri göster/gizle
        prevButton.style.display = currentStep > 1 ? 'block' : 'none';
        nextButton.textContent = currentStep < totalSteps ? 'Devam' : 'Başlayalım';
    }
    
    // Modalı kapat
    function closeModal() {
        modal.style.opacity = '0';
        
        // Oturum değişkenini ayarlama (AJAX ile)
        fetch('api/set_welcome_shown.php', {
            method: 'POST'
        }).then(response => response.json())
          .catch(error => console.error('Karşılama modalı durumu kaydedilemedi:', error));
          
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
    
    // Event listeners
    closeButton.addEventListener('click', closeModal);
    prevButton.addEventListener('click', prevStep);
    nextButton.addEventListener('click', nextStep);
    
    // İlk yükleme
    createStepIndicators();
    loadStepContent(currentStep);
});
</script>