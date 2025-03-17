<?php
/**
 * Octaverum AI Müzik Kütüphanesi
 * Kullanıcının oluşturduğu, beğendiği ve dinlediği müzikleri görüntülediği sayfa
 */

// Şarkı verilerini al (gerçek uygulamada veritabanından gelecek)
$tracks = $sampleTracks;

// Son çalınanlar ve beğenilenler listelerini oluştur
$recentTracks = array_slice($tracks, 0, 8);

$likedTracks = array_filter($tracks, function($track) {
    return $track['isLiked'];
});

// Beğenilen şarkılar arasında son çalınanlar arasında olmayan şarkıları seç
$uniqueLikedTracks = array_filter($likedTracks, function($likedTrack) use ($recentTracks) {
    foreach ($recentTracks as $recentTrack) {
        if ($recentTrack['id'] === $likedTrack['id']) {
            return false;
        }
    }
    return true;
});

// Liste ve grid görünümleri arasında geçiş için görünüm modu
$viewMode = isset($_GET['view']) ? $_GET['view'] : 'list';

// Filtre
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Filtreye göre şarkıları seç
switch ($filter) {
    case 'recent':
        $filteredTracks = $recentTracks;
        break;
    case 'liked':
        $filteredTracks = $likedTracks;
        break;
    case 'all':
    default:
        $filteredTracks = array_merge($recentTracks, $uniqueLikedTracks);
        break;
}
?>

<div class="music-library">
    <div class="library-header">
        <div class="view-tabs">
            <button
                class="view-tab active"
                onclick="switchTab('library')"
            >
                <span class="tab-icon">📚</span>
                <span>Kütüphanem</span>
            </button>
        </div>
        
        <div class="view-controls">
            <button
                class="view-mode-button <?php echo $viewMode === 'grid' ? 'active' : ''; ?>"
                onclick="changeViewMode('grid')"
                aria-label="Grid view"
            >
                <span class="view-icon">◫</span>
            </button>
            <button
                class="view-mode-button <?php echo $viewMode === 'list' ? 'active' : ''; ?>"
                onclick="changeViewMode('list')"
                aria-label="List view"
            >
                <span class="view-icon">≡</span>
            </button>
        </div>
    </div>
    
    <div class="library-container">
        <div class="library-main">
            <div class="combined-music-list">
                <div class="list-header">
                    <h2 class="list-title">Son Dinlenenler ve Beğenilenler</h2>
                    
                    <div class="filter-tabs">
                        <button
                            class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>"
                            onclick="setFilter('all')"
                        >
                            Tümü
                        </button>
                        <button
                            class="filter-tab <?php echo $filter === 'recent' ? 'active' : ''; ?>"
                            onclick="setFilter('recent')"
                        >
                            Son Dinlenenler
                        </button>
                        <button
                            class="filter-tab <?php echo $filter === 'liked' ? 'active' : ''; ?>"
                            onclick="setFilter('liked')"
                        >
                            Beğenilenler
                        </button>
                    </div>
                </div>
                
                <div class="song-list">
                    <?php if (empty($filteredTracks)): ?>
                    <div class="empty-list-message">
                        <?php if ($filter === 'recent'): ?>
                            Henüz hiç şarkı dinlemediniz.
                        <?php elseif ($filter === 'liked'): ?>
                            Henüz hiç şarkı beğenmediniz.
                        <?php else: ?>
                            Henüz kütüphanenizde şarkı bulunmuyor.
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <?php foreach ($filteredTracks as $track): ?>
                    <?php $isRecent = in_array($track['id'], array_column($recentTracks, 'id')); ?>
                    <div class="song-item" data-track-id="<?php echo $track['id']; ?>" onclick="playTrack(<?php echo $track['id']; ?>)">
                        <div class="song-cover">
                            <img
                                src="<?php echo htmlspecialchars($track['coverUrl']); ?>"
                                alt="<?php echo htmlspecialchars($track['title']); ?> kapak"
                                class="cover-image"
                            />
                        </div>
                        
                        <div class="song-info">
                            <div class="song-title"><?php echo htmlspecialchars($track['title']); ?></div>
                            <div class="song-details">
                                <span class="song-artist"><?php echo htmlspecialchars($track['artist']); ?></span>
                                <?php if (isset($track['album']) && !isMobileDevice()): ?>
                                <span class="detail-separator">•</span>
                                <span class="song-album"><?php echo htmlspecialchars($track['album']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Display badges to show if track is recent or liked -->
                        <div class="track-badges">
                            <?php if ($isRecent): ?>
                            <span class="track-badge recent-badge">Son</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="song-meta">
                            <span class="song-duration"><?php echo htmlspecialchars($track['duration']); ?></span>
                            <button 
                                class="like-button <?php echo $track['isLiked'] ? 'liked' : ''; ?>"
                                onclick="toggleLike(event, <?php echo $track['id']; ?>)"
                                aria-label="<?php echo $track['isLiked'] ? 'Beğeniden kaldır' : 'Beğen'; ?>"
                            >
                                <?php echo $track['isLiked'] ? '❤️' : '🤍'; ?>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="section-divider"></div>
        </div>
    </div>
</div>

<script>
// Görünüm modunu değiştir
function changeViewMode(mode) {
    window.location.href = `index.php?section=library&view=${mode}`;
}

// Tab değiştir 
function switchTab(tab) {
    window.location.href = `index.php?section=library&tab=${tab}`;
}

// Filtre uygula
function setFilter(filter) {
    window.location.href = `index.php?section=library&filter=${filter}`;
}

// Şarkı çal
function playTrack(trackId) {
    console.log(`Şarkı çalınıyor: ID ${trackId}`);
    
    // API isteği (burada AJAX kullanılabilir)
    fetch(`api/get_track.php?id=${trackId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Müzik çaları görünür yap ve şarkıyı ayarla
                const musicPlayer = document.getElementById('musicPlayer');
                if (musicPlayer) {
                    musicPlayer.style.display = 'flex';
                    
                    // Şarkı bilgilerini ayarla
                    document.getElementById('playerTrackTitle').textContent = data.track.title;
                    document.getElementById('playerTrackArtist').textContent = data.track.artist;
                    document.getElementById('playerTrackCover').src = data.track.coverUrl;
                    
                    // Şarkıyı oynat
                    const audioElement = document.getElementById('playerAudio');
                    if (audioElement) {
                        audioElement.src = data.track.audioUrl;
                        audioElement.play();
                    }
                }
            } else {
                console.error('Şarkı yüklenirken hata:', data.message);
            }
        })
        .catch(error => {
            console.error('Şarkı isteği hatası:', error);
        });
    
    // Gerçek bir API isteği olmadığından, sayfada bir şarkı çalar görüntüleyelim
    // Bu kısım gerçek uygulamada API yanıtı içinde olacak
    const track = <?php echo json_encode($tracks[0]); ?>;
    
    // Şarkı çalarını görünür yap ve bilgileri ayarla
    const musicPlayer = document.querySelector('.music-player');
    if (musicPlayer) {
        musicPlayer.style.display = 'flex';
        
        // Şarkı bilgilerini ayarla
        const trackTitle = musicPlayer.querySelector('.track-title');
        const trackArtist = musicPlayer.querySelector('.track-artist');
        const trackCover = musicPlayer.querySelector('.track-cover img');
        
        if (trackTitle) trackTitle.textContent = track.title;
        if (trackArtist) trackArtist.textContent = track.artist;
        if (trackCover) trackCover.src = track.coverUrl;
    }
}

// Beğeni durumunu değiştir
function toggleLike(event, trackId) {
    event.stopPropagation(); // Şarkının çalınmasını engelle
    
    // Burada AJAX ile beğeni durumunu değiştirme isteği gönderilebilir
    console.log(`Beğeni durumu değiştiriliyor: ID ${trackId}`);
    
    // Buton görünümünü değiştir
    const button = event.currentTarget;
    const isLiked = button.classList.contains('liked');
    
    if (isLiked) {
        button.classList.remove('liked');
        button.innerHTML = '🤍';
        button.setAttribute('aria-label', 'Beğen');
    } else {
        button.classList.add('liked');
        button.innerHTML = '❤️';
        button.setAttribute('aria-label', 'Beğeniden kaldır');
        
        // Beğeni animasyonu
        button.animate([
            { transform: 'scale(1)' },
            { transform: 'scale(1.3)' },
            { transform: 'scale(1)' }
        ], {
            duration: 300,
            easing: 'ease'
        });
    }
    
    // API isteği (gerçek uygulamada AJAX kullanılır)
    fetch('api/toggle_like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            trackId: trackId, 
            liked: !isLiked 
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Hata durumunda UI'ı eski haline getir
            if (isLiked) {
                button.classList.add('liked');
                button.innerHTML = '❤️';
                button.setAttribute('aria-label', 'Beğeniden kaldır');
            } else {
                button.classList.remove('liked');
                button.innerHTML = '🤍';
                button.setAttribute('aria-label', 'Beğen');
            }
            console.error('Beğeni durumu değiştirilemedi:', data.message);
        }
    })
    .catch(error => {
        console.error('Beğeni isteği hatası:', error);
        // Hata durumunda UI'ı eski haline getir
        if (isLiked) {
            button.classList.add('liked');
            button.innerHTML = '❤️';
        } else {
            button.classList.remove('liked');
            button.innerHTML = '🤍';
        }
    });
}
</script>