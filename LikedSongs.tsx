// src/components/LikedSongs.tsx
import React, { useState } from 'react';
import './MusicPages.css';

interface MusicItem {
  id: number;
  title: string;
  artist: string;
  coverUrl: string;
  duration: string;
  isLiked: boolean;
  genre?: string;
}

interface LikedSongsProps {
  onBack: () => void;
}

const LikedSongs: React.FC<LikedSongsProps> = ({ onBack }) => {
  // Sample data - in a real app, this would be filtered for liked songs only
  const [tracks, setTracks] = useState<MusicItem[]>([
    {
      id: 1,
      title: "Neon Rüya",
      artist: "CyberSynth",
      coverUrl: "https://via.placeholder.com/200x200/00ffff/000000",
      duration: "3:45",
      isLiked: true,
      genre: "Synthwave"
    },
    {
      id: 3,
      title: "Dijital Yağmur",
      artist: "NeonEcho",
      coverUrl: "https://via.placeholder.com/200x200/00ff99/000000",
      duration: "5:12",
      isLiked: true,
      genre: "Vaporwave"
    },
    {
      id: 5,
      title: "Elektro Rüzgar",
      artist: "PixelNebula",
      coverUrl: "https://via.placeholder.com/200x200/ff0099/000000",
      duration: "4:05",
      isLiked: true,
      genre: "Chillwave"
    },
    {
      id: 7,
      title: "Megaşehir",
      artist: "GlitchCore",
      coverUrl: "https://via.placeholder.com/200x200/ff00ff/000000",
      duration: "4:47",
      isLiked: true,
      genre: "Cyberpunk"
    }
  ]);

  // Play and like track handlers
  const handlePlayTrack = (id: number) => {
    console.log(`Şarkı oynatılıyor: ID ${id}`);
    // Here you would implement real playback logic
  };

  const handleLikeTrack = (id: number) => {
    setTracks(tracks.map(track => 
      track.id === id ? { ...track, isLiked: !track.isLiked } : track
    ));
  };

  return (
    <div className="music-page">
      <div className="page-header">
        <button className="back-button" onClick={onBack}>
          <span className="back-icon">◀</span> Geri
        </button>
        <h1 className="page-title">Beğenilen Şarkılar</h1>
      </div>
      
      <div className="tracks-grid">
        {tracks.map(track => (
          <div className="music-card" key={track.id}>
            <div className="music-card-cover">
              <img src={track.coverUrl} alt={`${track.title} - ${track.artist}`} />
              <div className="card-overlay">
                <button 
                  className="play-button"
                  onClick={() => handlePlayTrack(track.id)}
                  aria-label="Oynat"
                >
                  <span className="play-icon">▶</span>
                </button>
              </div>
            </div>
            
            <div className="music-card-info">
              <h3 className="music-title">{track.title}</h3>
              <p className="music-artist">{track.artist}</p>
              <div className="music-meta">
                <span className="music-duration">{track.duration}</span>
                <button 
                  className={`like-button ${track.isLiked ? 'liked' : ''}`}
                  onClick={() => handleLikeTrack(track.id)}
                  aria-label={track.isLiked ? "Beğeniden kaldır" : "Beğen"}
                >
                  {track.isLiked ? '❤️' : '🤍'}
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default LikedSongs;