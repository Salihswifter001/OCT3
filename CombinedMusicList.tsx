// src/components/CombinedMusicList.tsx
import React, { useState, useEffect } from 'react';
import './CombinedMusicList.css';

interface MusicItem {
  id: number;
  title: string;
  artist: string;
  album?: string;
  coverUrl: string;
  duration: string;
  isLiked: boolean;
  playCount?: number;
  releaseDate?: string;
  genre?: string;
}

interface CombinedMusicListProps {
  tracks: MusicItem[];
  onPlay: (id: number) => void;
  onLike?: (id: number) => void; // Optional prop
  title?: string;
}

// Define filter types
type FilterType = 'all' | 'recent' | 'liked';

const CombinedMusicList: React.FC<CombinedMusicListProps> = ({
  tracks,
  onPlay,
  onLike,
  title = "Müzik Kütüphanem"
}) => {
  // State for filtering tracks
  const [filter, setFilter] = useState<FilterType>('all');
  // State for tracking if we're on mobile
  const [isMobile, setIsMobile] = useState<boolean>(false);
  
  // Check if we're on mobile
  useEffect(() => {
    const checkMobile = () => {
      setIsMobile(window.innerWidth <= 768);
    };
    
    // Initial check
    checkMobile();
    
    // Add resize listener
    window.addEventListener('resize', checkMobile);
    
    // Clean up
    return () => window.removeEventListener('resize', checkMobile);
  }, []);
  
  // Separate recent and liked tracks
  const recentTracks = tracks.slice(0, 8); // First 8 tracks are recent
  const likedTracks = tracks.filter(track => track.isLiked);
  
  // Filter tracks based on current filter
  const filteredTracks = filter === 'all'
    ? tracks
    : filter === 'recent'
      ? recentTracks
      : likedTracks;

  // Handle like if the onLike prop exists
  const handleLike = (id: number) => {
    if (onLike) {
      onLike(id);
    }
  };

  return (
    <div className="combined-music-list">
      <div className="list-header">
        {title && <h2 className="list-title">{title}</h2>}
        
        <div className="filter-tabs">
          <button
            className={`filter-tab ${filter === 'all' ? 'active' : ''}`}
            onClick={() => setFilter('all')}
          >
            Tümü
          </button>
          <button
            className={`filter-tab ${filter === 'recent' ? 'active' : ''}`}
            onClick={() => setFilter('recent')}
          >
            Son Dinlenenler
          </button>
          <button
            className={`filter-tab ${filter === 'liked' ? 'active' : ''}`}
            onClick={() => setFilter('liked')}
          >
            Beğenilenler
          </button>
        </div>
      </div>
      
      <div className="song-list">
        {filteredTracks.map((track) => {
          const isRecent = recentTracks.some(t => t.id === track.id);
          return (
            <div key={track.id} className="song-item" onClick={() => onPlay(track.id)}>
              <div className="song-cover">
                <img
                  src={track.coverUrl}
                  alt={`${track.title} cover`}
                  className="cover-image"
                />
              </div>
              
              <div className="song-info">
                <div className="song-title">{track.title}</div>
                <div className="song-details">
                  <span className="song-artist">{track.artist}</span>
                  {!isMobile && track.album && (
                    <>
                      <span className="detail-separator">•</span>
                      <span className="song-album">{track.album}</span>
                    </>
                  )}
                </div>
              </div>
              
              {/* Display badges to show if track is recent or liked */}
              <div className="track-badges">
                {isRecent && <span className="track-badge recent-badge">Son</span>}
              </div>
              
              <div className="song-meta">
                <span className="song-duration">{track.duration}</span>
                {onLike && (
                  <button 
                    className={`like-button ${track.isLiked ? 'liked' : ''}`}
                    onClick={(e) => {
                      e.stopPropagation();
                      handleLike(track.id);
                    }}
                    aria-label={track.isLiked ? "Beğeniden kaldır" : "Beğen"}
                  >
                    {track.isLiked ? '❤️' : '🤍'}
                  </button>
                )}
              </div>
            </div>
          );
        })}
        
        {filteredTracks.length === 0 && (
          <div className="empty-list-message">
            {filter === 'recent'
              ? 'Henüz hiç şarkı dinlemediniz.'
              : 'Henüz hiç şarkı beğenmediniz.'}
          </div>
        )}
      </div>
    </div>
  );
};

export default CombinedMusicList;