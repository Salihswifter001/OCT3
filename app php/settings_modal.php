<?php
/**
 * Octaverum AI Ayarlar Modalı
 * Kullanıcı tercihlerini ayarlamak için modal
 */

// Mevcut ayarları al
$settings = $_SESSION['settings'] ?? [];
?>

<div class="settings-overlay">
    <div class="settings-panel">
        <div class="settings-header">
            <h2>Ayarlar</h2>
            <div class="settings-close-button" id="settingsCloseButton">×</div>
        </div>
        
        <div class="settings-content">
            <div class="settings-tabs" id="settingsTabs">
                <div class="settings-tab active" data-tab="tema">
                    <span class="settings-tab-icon">🎨</span>
                    <span>Tema</span>
                </div>
                <div class="settings-tab" data-tab="enstruman">
                    <span class="settings-tab-icon">🎹</span>
                    <span>Enstrüman</span>
                </div>
                <div class="settings-tab" data-tab="ses">
                    <span class="settings-tab-icon">🔊</span>
                    <span>Ses</span>
                </div>
                <div class="settings-tab" data-tab="ai">
                    <span class="settings-tab-icon">🤖</span>
                    <span>AI Müzik</span>
                </div>
                <div class="settings-tab" data-tab="kisayollar">
                    <span class="settings-tab-icon">⌨️</span>
                    <span>Kısayollar</span>
                </div>
                <div class="settings-tab" data-tab="arayuz">
                    <span class="settings-tab-icon">📱</span>
                    <span>Arayüz</span>
                </div>
                <div class="settings-tab" data-tab="hesap">
                    <span class="settings-tab-icon">👤</span>
                    <span>Hesap</span>
                </div>
                <div class="settings-tab" data-tab="dil">
                    <span class="settings-tab-icon">🌐</span>
                    <span>Dil</span>
                </div>
                <div class="settings-tab" data-tab="depolama">
                    <span class="settings-tab-icon">💾</span>
                    <span>Depolama</span>
                </div>
            </div>
            
            <div class="settings-panel-content">
                <!-- Tema Ayarları -->
                <div class="settings-section" id="tema-section">
                    <h3>Tema Ayarları</h3>
                    
                    <div class="setting-item">
                        <label class="setting-label">Tema Modu</label>
                        <div class="switch-container">
                            <label class="switch">
                                <input
                                    type="checkbox"
                                    id="darkModeToggle"
                                    <?php echo isset($settings['theme']['darkMode']) && $settings['theme']['darkMode'] ? 'checked' : ''; ?>
                                />
                                <span class="slider"></span>
                            </label>
                            <span class="switch-label" id="darkModeLabel">
                                <?php echo isset($settings['theme']['darkMode']) && $settings['theme']['darkMode'] ? 'Karanlık Mod' : 'Aydınlık Mod'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <label class="setting-label">Neon Renk Seçimi</label>
                        <div class="color-picker" id="colorPicker">
                            <div 
                                class="color-option <?php echo isset($settings['theme']['neonColor']) && $settings['theme']['neonColor'] === '#00ffff' ? 'selected' : ''; ?>"
                                style="background-color: #00ffff"
                                data-color="#00ffff"
                                title="Cyan"
                            ></div>
                            <div 
                                class="color-option <?php echo isset($settings['theme']['neonColor']) && $settings['theme']['neonColor'] === '#ff00ff' ? 'selected' : ''; ?>"
                                style="background-color: #ff00ff"
                                data-color="#ff00ff"
                                title="Mor"
                            ></div>
                            <div 
                                class="color-option <?php echo isset($settings['theme']['neonColor']) && $settings['theme']['neonColor'] === '#ff0099' ? 'selected' : ''; ?>"
                                style="background-color: #ff0099"
                                data-color="#ff0099"
                                title="Pembe"
                            ></div>
                            <div 
                                class="color-option <?php echo isset($settings['theme']['neonColor']) && $settings['theme']['neonColor'] === '#00ff99' ? 'selected' : ''; ?>"
                                style="background-color: #00ff99"
                                data-color="#00ff99"
                                title="Yeşil"
                            ></div>
                            <div 
                                class="color-option <?php echo isset($settings['theme']['neonColor']) && $settings['theme']['neonColor'] === '#6600ff' ? 'selected' : ''; ?>"
                                style="background-color: #6600ff"
                                data-color="#6600ff"
                                title="Mavi Mor"
                            ></div>
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <label class="setting-label">Parlaklık Yoğunluğu</label>
                        <div class="slider-container">
                            <input
                                type="range"
                                min="0"
                                max="100"
                                id="glowIntensitySlider"
                                value="<?php echo isset($settings['theme']['glowIntensity']) ? $settings['theme']['glowIntensity'] : 100; ?>"
                                class="range-slider"
                            />
                            <span class="slider-value" id="glowIntensityValue">
                                <?php echo isset($settings['theme']['glowIntensity']) ? $settings['theme']['glowIntensity'] : 100; ?>%
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Enstrüman Ayarları -->
                <div class="settings-section" id="enstruman-section" style="display: none;">
                    <h3>Enstrüman Ayarları</h3>
                    
                    <div class="setting-item">
                        <label class="setting-label">Varsayılan Enstrüman</label>
                        <div class="select-container">
                            <select
                                id="defaultInstrument"
                                class="settings-select"
                            >
                                <option value="synth" <?php echo isset($settings['instrument']['defaultInstrument']) && $settings['instrument']['defaultInstrument'] === 'synth' ? 'selected' : ''; ?>>Synthesizer</option>
                                <option value="piano" <?php echo isset($settings['instrument']['defaultInstrument']) && $settings['instrument']['defaultInstrument'] === 'piano' ? 'selected' : ''; ?>>Piyano</option>
                                <option value="guitar" <?php echo isset($settings['instrument']['defaultInstrument']) && $settings['instrument']['defaultInstrument'] === 'guitar' ? 'selected' : ''; ?>>Gitar</option>
                                <option value="drums" <?php echo isset($settings['instrument']['defaultInstrument']) && $settings['instrument']['defaultInstrument'] === 'drums' ? 'selected' : ''; ?>>Davul</option>
                                <option value="bass" <?php echo isset($settings['instrument']['defaultInstrument']) && $settings['instrument']['defaultInstrument'] === 'bass' ? 'selected' : ''; ?>>Bas</option>
                                <option value="strings" <?php echo isset($settings['instrument']['defaultInstrument']) && $settings['instrument']['defaultInstrument'] === 'strings' ? 'selected' : ''; ?>>Yaylılar</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Ses Ayarları -->
                <div class="settings-section" id="ses-section" style="display: none;">
                    <h3>Ses Ayarları</h3>
                    
                    <div class="setting-item">
                        <label class="setting-label">Otomatik Oynatma</label>
                        <div class="switch-container">
                            <label class="switch">
                                <input
                                    type="checkbox"
                                    id="autoplayToggle"
                                    <?php echo isset($settings['audio']['autoplay']) && $settings['audio']['autoplay'] ? 'checked' : ''; ?>
                                />
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <label class="setting-label">Bas Seviyesi</label>
                        <div class="slider-container">
                            <input
                                type="range"
                                min="0"
                                max="100"
                                id="bassSlider"
                                value="<?php echo isset($settings['audio']['equalizer']['bass']) ? $settings['audio']['equalizer']['bass'] : 50; ?>"
                                class="range-slider"
                            />
                            <span class="slider-value" id="bassValue">
                                <?php echo isset($settings['audio']['equalizer']['bass']) ? $settings['audio']['equalizer']['bass'] : 50; ?>%
                            </span>
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <label class="setting-label">Tiz Seviyesi</label>
                        <div class="slider-container">
                            <input
                                type="range"
                                min="0"
                                max="100"
                                id="trebleSlider"
                                value="<?php echo isset($settings['audio']['equalizer']['treble']) ? $settings['audio']['equalizer']['treble'] : 50; ?>"
                                class="range-slider"
                            />
                            <span class="slider-value" id="trebleValue">
                                <?php echo isset($settings['audio']['equalizer']['treble']) ? $settings['audio']['equalizer']['treble'] : 50; ?>%
                            </span>
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <label class="setting-label">Ses Dalga Gösterimi</label>
                        <div class="switch-container">
                            <label class="switch">
                                <input
                                    type="checkbox"
                                    id="waveformToggle"
                                    <?php echo isset($settings['audio']['showWaveform']) && $settings['audio']['showWaveform'] ? 'checked' : ''; ?>
                                />
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <label class="setting-label">Geçiş Efekti</label>
                        <div class="radio-container">
                            <label class="radio-label">
                                <input
                                    type="radio"
                                    name="transitionEffect"
                                    value="smooth"
                                    <?php echo isset($settings['audio']['transitionEffect']) && $settings['audio']['transitionEffect'] === 'smooth' ? 'checked' : ''; ?>
                                />
                                <span>Yumuşak</span>
                            </label>
                            <label class="radio-label">
                                <input
                                    type="radio"
                                    name="transitionEffect"
                                    value="instant"
                                    <?php echo isset($settings['audio']['transitionEffect']) && $settings['audio']['transitionEffect'] === 'instant' ? 'checked' : ''; ?>
                                />
                                <span>Anlık</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- AI Müzik Ayarları -->
                <div class="settings-section" id="ai-section" style="display: none;">
                    <h3>AI Müzik Üretim Ayarları</h3>
                    
                    <div class="setting-item">
                        <label class="setting-label">Şarkı Süresi</label>
                        <div class="select-container">
                            <select
                                id="songDuration"
                                class="settings-select"
                            >
                                <option value="30s" <?php echo isset($settings['aiMusic']['duration']) && $settings['aiMusic']['duration'] === '30s' ? 'selected' : ''; ?>>30 saniye</option>
                                <option value="60s" <?php echo isset($settings['aiMusic']['duration']) && $settings['aiMusic']['duration'] === '60s' ? 'selected' : ''; ?>>60 saniye</option>
                                <option value="90s" <?php echo isset($settings['aiMusic']['duration']) && $settings['aiMusic']['duration'] === '90s' ? 'selected' : ''; ?>>90 saniye</option>
                                <option value="120s" <?php echo isset($settings['aiMusic']['duration']) && $settings['aiMusic']['duration'] === '120s' ? 'selected' : ''; ?>>120 saniye</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <label class="setting-label">BPM Aralığı (Tempo)</label>
                        <div class="double-slider-container">
                            <span class="slider-min-value" id="bpmMinValue">
                                <?php echo isset($settings['aiMusic']['bpmRange']['min']) ? $settings['aiMusic']['bpmRange']['min'] : 90; ?>
                            </span>
                            <input 
                                type="range" 
                                min="60" 
                                max="200" 
                                id="bpmMinSlider"
                                value="<?php echo isset($settings['aiMusic']['bpmRange']['min']) ? $settings['aiMusic']['bpmRange']['min'] : 90; ?>"
                                class="range-slider"
                            />
                            <span class="slider-max-value" id="bpmMaxValue">
                                <?php echo isset($settings['aiMusic']['bpmRange']['max']) ? $settings['aiMusic']['bpmRange']['max'] : 140; ?>
                            </span>
                            <input 
                                type="range" 
                                min="60" 
                                max="200" 
                                id="bpmMaxSlider"
                                value="<?php echo isset($settings['aiMusic']['bpmRange']['max']) ? $settings['aiMusic']['bpmRange']['max'] : 140; ?>"
                                class="range-slider"
                            />
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <label class="setting-label">Müzik Anahtar Tonu</label>
                        <div class="select-container">
                            <select 
                                id="musicKey"
                                class="settings-select"
                            >
                                <option value="Minör A" <?php echo isset($settings['aiMusic']['key']) && $settings['aiMusic']['key'] === 'Minör A' ? 'selected' : ''; ?>>Minör A</option>
                                <option value="Majör C" <?php echo isset($settings['aiMusic']['key']) && $settings['aiMusic']['key'] === 'Majör C' ? 'selected' : ''; ?>>Majör C</option>
                                <option value="Minör E" <?php echo isset($settings['aiMusic']['key']) && $settings['aiMusic']['key'] === 'Minör E' ? 'selected' : ''; ?>>Minör E</option>
                                <option value="Majör G" <?php echo isset($settings['aiMusic']['key']) && $settings['aiMusic']['key'] === 'Majör G' ? 'selected' : ''; ?>>Majör G</option>
                                <option value="Minör B" <?php echo isset($settings['aiMusic']['key']) && $settings['aiMusic']['key'] === 'Minör B' ? 'selected' : ''; ?>>Minör B</option>
                                <option value="Majör D" <?php echo isset($settings['aiMusic']['key']) && $settings['aiMusic']['key'] === 'Majör D' ? 'selected' : ''; ?>>Majör D</option>
                                <option value="Minör F#" <?php echo isset($settings['aiMusic']['key']) && $settings['aiMusic']['key'] === 'Minör F#' ? 'selected' : ''; ?>>Minör F#</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <label class="setting-label">Çıktı Formatı</label>
                        <div class="radio-container">
                            <label class="radio-label">
                                <input 
                                    type="radio" 
                                    name="outputFormat" 
                                    value="mp3"
                                    <?php echo isset($settings['aiMusic']['format']) && $settings['aiMusic']['format'] === 'mp3' ? 'checked' : ''; ?>
                                />
                                <span>MP3</span>
                            </label>
                            <label class="radio-label">
                                <input 
                                    type="radio" 
                                    name="outputFormat" 
                                    value="wav"
                                    <?php echo isset($settings['aiMusic']['format']) && $settings['aiMusic']['format'] === 'wav' ? 'checked' : ''; ?>
                                />
                                <span>WAV</span>
                            </label>
                            <label class="radio-label">
                                <input 
                                    type="radio" 
                                    name="outputFormat" 
                                    value="flac"
                                    <?php echo isset($settings['aiMusic']['format']) && $settings['aiMusic']['format'] === 'flac' ? 'checked' : ''; ?>
                                />
                                <span>FLAC</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- İlgili diğer bölümler buraya eklenecek (arayüz, kısayollar vb.) -->
                <div class="settings-section" id="kisayollar-section" style="display: none;">
                    <h3>Klavye Kısayolları</h3>
                    <!-- Kısayol ayarları -->
                </div>
                
                <div class="settings-section" id="arayuz-section" style="display: none;">
                    <h3>Arayüz Ayarları</h3>
                    <!-- Arayüz ayarları -->
                </div>
                
                <div class="settings-section" id="hesap-section" style="display: none;">
                    <h3>Hesap ve Güvenlik Ayarları</h3>
                    <!-- Hesap ayarları -->
                </div>
                
                <div class="settings-section" id="dil-section" style="display: none;">
                    <h3>Dil Ayarları</h3>
                    <!-- Dil ayarları -->
                </div>
                
                <div class="settings-section" id="depolama-section" style="display: none;">
                    <h3>Veri ve Depolama Yönetimi</h3>
                    <!-- Depolama ayarları -->
                </div>
            </div>
        </div>
        
        <div class="settings-footer">
            <button 
                class="settings-button reset"
                id="resetSettingsButton"
            >
                Varsayılanlara Sıfırla
            </button>
            <button 
                id="saveSettingsButton"
                class="settings-button save"
                disabled
            >
                Değişiklikleri Kaydet
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sayfa yüklendiğinde gerekli elementleri seç
    const closeButton = document.getElementById('settingsCloseButton');
    const saveButton = document.getElementById('saveSettingsButton');
    const resetButton = document.getElementById('resetSettingsButton');
    const tabs = document.querySelectorAll('.settings-tabs .settings-tab');
    
    // Tema ayarları için elementler
    const darkModeToggle = document.getElementById('darkModeToggle');
    const darkModeLabel = document.getElementById('darkModeLabel');
    const colorOptions = document.querySelectorAll('.color-option');
    const glowIntensitySlider = document.getElementById('glowIntensitySlider');
    const glowIntensityValue = document.getElementById('glowIntensityValue');
    
    // Global değişken değişiklik yapıldı mı
    let hasChanges = false;
    
    // Ayarlar değiştiğinde save butonunu etkinleştir
    const enableSaveButton = () => {
        saveButton.disabled = false;
        saveButton.classList.add('active');
        hasChanges = true;
    };
    
    // Tema modu değiştiğinde
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function() {
            darkModeLabel.textContent = this.checked ? 'Karanlık Mod' : 'Aydınlık Mod';
            enableSaveButton();
        });
    }
    
    // Renk seçimi
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Aktif renk seçimini kaldır
            colorOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Yeni rengi seç
            this.classList.add('selected');
            
            // Değişim bildirimi
            enableSaveButton();
        });
    });
    
    // Parlaklık ayarı
    if (glowIntensitySlider) {
        glowIntensitySlider.addEventListener('input', function() {
            glowIntensityValue.textContent = this.value + '%';
        });
        
        glowIntensitySlider.addEventListener('change', enableSaveButton);
    }
    
    // Tab değişimi
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Aktif tab stilini güncelle
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // İlgili bölümü göster, diğerlerini gizle
            const tabName = this.dataset.tab;
            document.querySelectorAll('.settings-section').forEach(section => {
                section.style.display = 'none';
            });
            document.getElementById(tabName + '-section').style.display = 'block';
        });
    });
    
    // Ayarları kapatma
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            // Kayıt yapılmamış değişiklikler varsa uyar
            if (hasChanges) {
                if (confirm('Kaydedilmemiş değişiklikler var. Çıkmak istediğinizden emin misiniz?')) {
                    closeSettings();
                }
            } else {
                closeSettings();
            }
        });
    }
    
    // Ayarları kapatma fonksiyonu
    function closeSettings() {
        const settingsOverlay = document.querySelector('.settings-overlay');
        if (settingsOverlay) {
            settingsOverlay.remove();
        }
    }
    
    // Ayarları kaydetme
    if (saveButton) {
        saveButton.addEventListener('click', function() {
            // Tüm ayarları topla
            const settings = collectSettings();
            
            // AJAX ile sunucuya gönder
            fetch('api/save_settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(settings),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Başarı animasyonu göster
                    saveButton.classList.add('save-success');
                    
                    // Değişiklik durumunu sıfırla
                    hasChanges = false;
                    saveButton.disabled = true;
                    saveButton.classList.remove('active');
                    
                    // Bazı ayarlar doğrudan uygulanabilir
                    applyThemeSettings(settings.theme);
                    
                    // Animasyonu temizle
                    setTimeout(() => {
                        saveButton.classList.remove('save-success');
                    }, 1500);
                    
                    // Bazı ayarlar yenileme gerektirebilir
                    if (data.requiresReload) {
                        alert('Bazı ayarlar sayfayı yenilemenizi gerektirir.');
                    }
                } else {
                    alert('Ayarlar kaydedilirken bir hata oluştu: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Ayarlar kaydedilirken hata:', error);
                alert('Ayarlar kaydedilirken bir hata oluştu.');
            });
        });
    }
    
    // Ayarları sıfırlama
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            if (confirm('Tüm ayarları varsayılan değerlerine sıfırlamak istediğinizden emin misiniz?')) {
                fetch('api/reset_settings.php', {
                    method: 'POST',
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Başarılı sıfırlama, sayfayı yenile
                        alert('Ayarlar varsayılanlara sıfırlandı.');
                        window.location.reload();
                    } else {
                        alert('Ayarlar sıfırlanırken bir hata oluştu: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Ayarlar sıfırlanırken hata:', error);
                    alert('Ayarlar sıfırlanırken bir hata oluştu.');
                });
            }
        });
    }
    
    // Tüm ayarları toplama
    function collectSettings() {
        // Tema ayarları
        const theme = {
            darkMode: darkModeToggle ? darkModeToggle.checked : true,
            neonColor: document.querySelector('.color-option.selected').dataset.color,
            glowIntensity: parseInt(glowIntensitySlider ? glowIntensitySlider.value : 100)
        };
        
        // Enstrüman ayarları
        const instrument = {
            defaultInstrument: document.getElementById('defaultInstrument')?.value || 'synth'
        };
        
        // Ses ayarları
        const audio = {
            autoplay: document.getElementById('autoplayToggle')?.checked || false,
            equalizer: {
                bass: parseInt(document.getElementById('bassSlider')?.value || 50),
                treble: parseInt(document.getElementById('trebleSlider')?.value || 50)
            },
            showWaveform: document.getElementById('waveformToggle')?.checked || true,
            transitionEffect: document.querySelector('input[name="transitionEffect"]:checked')?.value || 'smooth'
        };
        
        // AI müzik ayarları
        const aiMusic = {
            duration: document.getElementById('songDuration')?.value || '60s',
            bpmRange: {
                min: parseInt(document.getElementById('bpmMinSlider')?.value || 90),
                max: parseInt(document.getElementById('bpmMaxSlider')?.value || 140)
            },
            key: document.getElementById('musicKey')?.value || 'Minör A',
            format: document.querySelector('input[name="outputFormat"]:checked')?.value || 'mp3'
        };
        
        // Diğer ayarlar burada eklenir
        
        return {
            theme,
            instrument,
            audio,
            aiMusic
            // Diğer ayar kategorileri
        };
    }
    
    // Tema ayarlarını anında uygula
    function applyThemeSettings(theme) {
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
});
</script>