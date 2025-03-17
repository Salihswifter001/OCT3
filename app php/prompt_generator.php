<?php
/**
 * Octaverum AI Prompt Oluşturucu
 * Kullanıcının müzik oluşturmak için prompt girdiği ana sayfa bileşeni
 */

// Tüm müzik türlerini al
$genres = $musicGenres;

// Sıcak (trend olan) ve diğer türleri ayır
$hotGenres = array_filter($genres, function($genre) {
    return $genre['isHot'];
});

$otherGenres = array_filter($genres, function($genre) {
    return !$genre['isHot'];
});

// Prompt ipuçları
$tipIndex = rand(0, count($promptTips) - 1);
$currentTip = $promptTips[$tipIndex];

// Form işleme
$prompt = '';
$selectedGenres = [];
$isGenerating = false;
$generationResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_music'])) {
    // Form gönderildiğinde işlenecek kod
    $prompt = isset($_POST['prompt']) ? $_POST['prompt'] : '';
    $selectedGenres = isset($_POST['genres']) ? $_POST['genres'] : [];
    $includeVocals = isset($_POST['include_vocals']) ? true : false;
    $lyrics = isset($_POST['lyrics']) ? $_POST['lyrics'] : '';
    
    // Şarkı oluşturma işlemi burada simüle edilebilir
    // Gerçek uygulamada bir API'ye istek gönderilir
    
    // Başarılı bir şarkı oluşturma simülasyonu
    if (!empty($prompt)) {
        $isGenerating = true;
        
        // Oturum değişkenine kaydetme
        $_SESSION['is_generating'] = true;
        $_SESSION['prompt'] = $prompt;
        $_SESSION['selected_genres'] = $selectedGenres;
        
        // Bir miktar gecikme sonra sonuçların görüntülenmesi için yönlendirme yapılabilir
        // JavaScript ile bunu simüle edeceğiz
    }
}
?>

<div class="prompt-generator-page">
    <div class="cyber-overlay"></div>
    
    <!-- Dalga animasyonu arka planı -->
    <div class="wave-container">
        <div class="wave wave1"></div>
        <div class="wave wave2"></div>
    </div>
    
    <div class="page-header">
        <button class="back-button glitch-hover" onclick="window.history.back()">
            <span class="back-icon">◀</span> 
            <span class="back-text">Geri</span>
        </button>
        <h1 class="page-title glitch" data-text="Yeni Müzik Oluştur">Yeni Müzik Oluştur</h1>
    </div>
    
    <div class="content-grid">
        <div class="prompt-container cyber-card">
            <form id="promptForm" method="post" action="">
                <div class="card-header">
                    <div class="card-title">AI İçin Müzik Promptu</div>
                    <div class="character-count">
                        <span class="current-count" id="charCount">0</span>/150
                    </div>
                </div>
                
                <div class="prompt-input-wrapper">
                    <textarea
                        id="promptInput"
                        name="prompt"
                        placeholder="Müzik tarzını ve duygusunu tanımlayın..."
                        maxlength="150"
                        class="prompt-input"
                        autofocus
                    ><?php echo htmlspecialchars($prompt); ?></textarea>
                    
                    <div class="prompt-tips">
                        <div class="tip-icon">💡</div>
                        <div class="tip-content">
                            <span>Örnek:</span> <span id="tipContent"><?php echo htmlspecialchars($currentTip); ?></span>
                        </div>
                    </div>
                </div>
                
                <canvas id="audioVisualizer" class="audio-visualizer"></canvas>
                
                <div class="vocals-toggle-section">
                    <div class="vocals-toggle-label">Vokal İçersin Mi?</div>
                    <label class="vocals-toggle">
                        <input 
                            type="checkbox" 
                            name="include_vocals"
                            id="includeVocals"
                        />
                        <span class="vocals-toggle-slider"></span>
                        <span class="vocals-toggle-text" id="vocalsStatus">Hayır</span>
                    </label>
                </div>
                
                <div id="lyricsPanel" class="lyrics-panel" style="display: none;">
                    <div class="lyrics-header">
                        <h3>Şarkı Sözleri</h3>
                        <button 
                            type="button"
                            class="generate-lyrics-button" 
                            id="generateLyricsBtn"
                            disabled
                        >
                            <span class="lyrics-icon">🎤</span>
                            <span>Sözleri Oluştur</span>
                        </button>
                    </div>
                    <textarea
                        name="lyrics"
                        id="lyricsInput"
                        placeholder="Şarkı sözlerinizi yazın veya AI'ın oluşturmasını bekleyin..."
                        class="lyrics-input"
                    ></textarea>
                </div>

                <div class="genre-selection-section">
                    <div class="section-header">
                        <h3 class="section-title">Müzik Türleri</h3>
                        <div class="genre-search">
                            <input 
                                type="text" 
                                placeholder="Tür ara..." 
                                id="genreSearch"
                                class="search-input"
                            />
                        </div>
                    </div>
                    
                    <?php if (!empty($hotGenres)): ?>
                    <div class="genre-category">
                        <div class="category-label">Popüler Türler <span class="fire-icon">🔥</span></div>
                        <div class="genre-grid" id="hotGenresGrid">
                            <?php foreach ($hotGenres as $genre): ?>
                            <div 
                                class="genre-chip <?php echo in_array($genre['name'], $selectedGenres) ? 'selected' : ''; ?>"
                                data-genre="<?php echo htmlspecialchars($genre['name']); ?>"
                            >
                                <span><?php echo htmlspecialchars($genre['name']); ?></span>
                                <?php if ($genre['isHot']): ?>
                                <span class="fire-icon">🔥</span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($otherGenres)): ?>
                    <div class="genre-category">
                        <div class="category-label">Diğer Türler</div>
                        <div class="genre-grid" id="otherGenresGrid">
                            <?php foreach ($otherGenres as $genre): ?>
                            <div 
                                class="genre-chip <?php echo in_array($genre['name'], $selectedGenres) ? 'selected' : ''; ?>"
                                data-genre="<?php echo htmlspecialchars($genre['name']); ?>"
                            >
                                <span><?php echo htmlspecialchars($genre['name']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Seçilen türleri saklayan gizli input -->
                <div id="selectedGenresContainer">
                    <?php foreach ($selectedGenres as $genre): ?>
                    <input type="hidden" name="genres[]" value="<?php echo htmlspecialchars($genre); ?>">
                    <?php endforeach; ?>
                </div>
                
                <button 
                    type="submit"
                    name="generate_music"
                    class="generate-button <?php echo $isGenerating ? 'generating' : ''; ?>" 
                    id="generateButton"
                    disabled
                >
                    <?php if ($isGenerating): ?>
                    <span class="loading-icon"></span>
                    <span>Müzik Üretiliyor...</span>
                    <?php else: ?>
                    <span class="icon">▶</span>
                    <span>Müzik Oluştur</span>
                    <?php endif; ?>
                </button>
            </form>
            
            <?php if ($isGenerating): ?>
            <div class="progress-bar-container">
                <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                <div class="progress-text">0%</div>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['generation_result']) && $_SESSION['generation_result']): ?>
            <div class="generation-result">
                <div class="result-header">
                    <h3><?php echo htmlspecialchars($_SESSION['generation_result']['title']); ?></h3>
                </div>
                
                <audio 
                    controls 
                    src="<?php echo htmlspecialchars($_SESSION['generation_result']['url']); ?>"
                    class="audio-player"
                ></audio>
                
                <?php if (isset($_SESSION['audio_analysis'])): ?>
                <div class="audio-analysis">
                    <h4>Müzik Analizi</h4>
                    <div class="analysis-grid">
                        <div class="analysis-item">
                            <div class="analysis-label">BPM</div>
                            <div class="analysis-value"><?php echo $_SESSION['audio_analysis']['bpm']; ?></div>
                        </div>
                        <div class="analysis-item">
                            <div class="analysis-label">Anahtar</div>
                            <div class="analysis-value"><?php echo $_SESSION['audio_analysis']['key']; ?> <?php echo $_SESSION['audio_analysis']['scale'] === 'major' ? 'Majör' : 'Minör'; ?></div>
                        </div>
                        <div class="analysis-item">
                            <div class="analysis-label">Süre</div>
                            <div class="analysis-value"><?php echo floor($_SESSION['audio_analysis']['audioLength'] / 60); ?>:<?php echo str_pad($_SESSION['audio_analysis']['audioLength'] % 60, 2, '0', STR_PAD_LEFT); ?></div>
                        </div>
                        <div class="analysis-item">
                            <div class="analysis-label">Türler</div>
                            <div class="analysis-value"><?php echo implode(', ', $_SESSION['audio_analysis']['genre']); ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="result-actions">
                    <button class="result-action-button download">
                        <span class="action-icon">💾</span>
                        <span>İndir</span>
                    </button>
                    <button class="result-action-button share">
                        <span class="action-icon">🔗</span>
                        <span>Paylaş</span>
                    </button>
                    <button class="result-action-button save">
                        <span class="action-icon">📁</span>
                        <span>Kütüphaneye Kaydet</span>
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="info-container cyber-card">
            <div class="card-header">
                <div class="card-title">Oluşturma İpuçları</div>
            </div>
            
            <div class="info-content">
                <div class="info-item">
                    <div class="info-icon">🎵</div>
                    <div class="info-text">
                        <strong>Tarz belirtin:</strong> Synthwave, Cyberpunk gibi belirli müzik türleri ekleyin.
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">🎹</div>
                    <div class="info-text">
                        <strong>Enstrüman belirtin:</strong> Sentezleyici, davullar, bas gitar gibi enstrümanları tanımlayın.
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">🌙</div>
                    <div class="info-text">
                        <strong>Atmosfer ekleyin:</strong> "Yağmurlu", "gece", "neon", "retro" gibi atmosferi belirleyen kelimeler kullanın.
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">🔊</div>
                    <div class="info-text">
                        <strong>Efekt belirtin:</strong> Reverb, delay, distorsiyon gibi ses efektlerini belirtin.
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">♻️</div>
                    <div class="info-text">
                        <strong>İstediğiniz sonucu alamazsanız:</strong> Promptunuzu değiştirin ve tekrar deneyin.
                    </div>
                </div>
            </div>
            
            <div class="ai-credits">
                <div class="ai-brain-icon"></div>
                <div class="credits-text">
                    <p>Octaverum AI tarafından desteklenmektedir</p>
                    <p class="credit-detail">Yapay zeka ile oluşturulan benzersiz müzik</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prompt ve karakter sayacı
    const promptInput = document.getElementById('promptInput');
    const charCount = document.getElementById('charCount');
    const generateButton = document.getElementById('generateButton');
    const generateLyricsBtn = document.getElementById('generateLyricsBtn');
    const includeVocals = document.getElementById('includeVocals');
    const vocalsStatus = document.getElementById('vocalsStatus');
    const lyricsPanel = document.getElementById('lyricsPanel');
    const lyricsInput = document.getElementById('lyricsInput');
    const genreSearch = document.getElementById('genreSearch');
    const hotGenresGrid = document.getElementById('hotGenresGrid');
    const otherGenresGrid = document.getElementById('otherGenresGrid');
    const promptForm = document.getElementById('promptForm');
    const selectedGenresContainer = document.getElementById('selectedGenresContainer');
    
    let selectedGenres = <?php echo json_encode($selectedGenres); ?>;
    
    // Karakter sayacını güncelle
    const updateCharCount = () => {
        if (promptInput) {
            const count = promptInput.value.length;
            if (charCount) charCount.textContent = count;
            
            // Üretme butonunu etkinleştir/devre dışı bırak
            if (generateButton) {
                generateButton.disabled = count === 0;
            }
            
            // Şarkı sözü oluşturma butonunu etkinleştir/devre dışı bırak
            if (generateLyricsBtn) {
                generateLyricsBtn.disabled = count === 0;
            }
        }
    };
    
    // İlk yüklenmede sayacı güncelle
    updateCharCount();
    
    // Prompt değiştiğinde sayacı güncelle
    if (promptInput) {
        promptInput.addEventListener('input', updateCharCount);
    }
    
    // Vokal içersin mi toggle
    if (includeVocals) {
        includeVocals.addEventListener('change', function() {
            if (this.checked) {
                vocalsStatus.textContent = 'Evet';
                lyricsPanel.style.display = 'block';
            } else {
                vocalsStatus.textContent = 'Hayır';
                lyricsPanel.style.display = 'none';
                lyricsInput.value = '';
            }
        });
    }
    
    // Şarkı sözü oluşturma
    if (generateLyricsBtn) {
        generateLyricsBtn.addEventListener('click', function() {
            if (!promptInput.value.trim()) return;
            
            // Buton durumunu güncelle
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="loading-icon"></span><span>Sözler Oluşturuluyor...</span>';
            
            // AJAX ile şarkı sözü oluşturma isteği gönder
            fetch('api/generate_lyrics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    prompt: promptInput.value
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    lyricsInput.value = data.lyrics;
                } else {
                    alert('Şarkı sözü oluşturma hatası: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Şarkı sözü oluşturma hatası:', error);
            })
            .finally(() => {
                // Buton durumunu geri getir
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    }
    
    // Tür arama
    if (genreSearch) {
        genreSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            // Tüm tür chiplerini al ve filtrele
            const allGenreChips = document.querySelectorAll('.genre-chip');
            
            allGenreChips.forEach(chip => {
                const genreName = chip.dataset.genre.toLowerCase();
                if (genreName.includes(searchTerm)) {
                    chip.style.display = '';
                } else {
                    chip.style.display = 'none';
                }
            });
        });
    }
    
    // Tür seçimi
    const setupGenreSelection = () => {
        const genreChips = document.querySelectorAll('.genre-chip');
        
        genreChips.forEach(chip => {
            chip.addEventListener('click', () => {
                const genre = chip.dataset.genre;
                
                if (selectedGenres.includes(genre)) {
                    // Zaten seçili olan türü kaldır
                    selectedGenres = selectedGenres.filter(g => g !== genre);
                    chip.classList.remove('selected');
                    
                    // Prompttan da kaldır
                    const promptText = promptInput.value;
                    const regex = new RegExp(`\\b${genre}\\b`, 'g');
                    promptInput.value = promptText.replace(regex, '').replace(/\s+/g, ' ').trim();
                } else {
                    // Yeni tür ekle
                    const newPrompt = promptInput.value ? `${promptInput.value} ${genre}` : genre;
                    if (newPrompt.length <= 150) {
                        selectedGenres.push(genre);
                        chip.classList.add('selected');
                        promptInput.value = newPrompt;
                    }
                }
                
                // Karakter sayacını güncelle
                updateCharCount();
                
                // Seçilen türleri form hidden inputlarına ekle
                updateSelectedGenresInputs();
            });
        });
    };
    
    // Seçilen türleri form inputlarına ekler
    const updateSelectedGenresInputs = () => {
        // Mevcut inputları temizle
        selectedGenresContainer.innerHTML = '';
        
        // Yeni inputları ekle
        selectedGenres.forEach(genre => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'genres[]';
            input.value = genre;
            selectedGenresContainer.appendChild(input);
        });
    };
    
    // Tür seçimini başlat
    setupGenreSelection();
    
    // Prompttaki ipuçlarını otomatik değiştir
    const tipContent = document.getElementById('tipContent');
    const promptTips = <?php echo json_encode($promptTips); ?>;
    let currentTipIndex = 0;
    
    setInterval(() => {
        currentTipIndex = (currentTipIndex + 1) % promptTips.length;
        if (tipContent) {
            tipContent.textContent = promptTips[currentTipIndex];
        }
    }, 5000);
    
    <?php if ($isGenerating): ?>
    // Müzik oluşturma ilerleme çubuğu simülasyonu
    const progressBar = document.getElementById('progressBar');
    const progressText = document.querySelector('.progress-text');
    
    let progress = 0;
    const simulateProgress = () => {
        const interval = setInterval(() => {
            progress += 5;
            
            if (progressBar) progressBar.style.width = `${progress}%`;
            if (progressText) progressText.textContent = `${progress}%`;
            
            if (progress >= 100) {
                clearInterval(interval);
                
                // Müzik oluşturma tamamlandı, sonuçları göster
                // Gerçek uygulamada bu veri API'den gelir
                const generationResult = {
                    url: 'https://samplelib.com/lib/preview/mp3/sample-6s.mp3',
                    title: generateMusicTitle(promptInput.value)
                };
                
                const audioAnalysis = {
                    bpm: Math.floor(Math.random() * 60) + 90,
                    key: ['C', 'D', 'E', 'F', 'G', 'A', 'B'][Math.floor(Math.random() * 7)],
                    scale: Math.random() > 0.5 ? 'major' : 'minor',
                    genre: selectedGenres.length > 0 ? selectedGenres : ['Electronic'],
                    audioLength: Math.floor(Math.random() * 120) + 60
                };
                
                // Sayfa yenileme yapılmadan sonuçları göster (AJAX ile)
                // Burada sadece simüle ediyoruz, gerçek uygulamada API'den gelecek
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        }, 200);
    };
    
    // İlerleme simülasyonunu başlat
    simulateProgress();
    <?php endif; ?>
    
    // Müzik başlığı oluşturma fonksiyonu
    function generateMusicTitle(promptText) {
        const words = promptText.split(' ').filter(word => word.length > 3);
        
        if (words.length === 0) {
            return 'Untitled Composition';
        }
        
        if (words.length === 1) {
            return words[0].charAt(0).toUpperCase() + words[0].slice(1);
        }
        
        // Prompttan 2 rastgele kelime seçin
        const word1 = words[Math.floor(Math.random() * words.length)];
        let word2 = words[Math.floor(Math.random() * words.length)];
        
        // word2'nin word1'den farklı olduğundan emin ol
        while (words.length > 1 && word2 === word1) {
            word2 = words[Math.floor(Math.random() * words.length)];
        }
        
        // İlk harfleri büyült
        const title1 = word1.charAt(0).toUpperCase() + word1.slice(1);
        const title2 = word2.charAt(0).toUpperCase() + word2.slice(1);
        
        return `${title1} ${title2}`;
    }
});
</script>