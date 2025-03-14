// src/components/MusicLibrary.tsx
import React, { useState } from 'react';
import './MusicLibrary.css';
import CombinedMusicList from './CombinedMusicList';

// Music item interface
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

interface MusicLibraryProps {
  compact?: boolean;
  onSetCurrentTrack?: (track: MusicItem | null) => void;
}

// Sample data - in a real app, this would come from an API
const sampleMusicData: MusicItem[] = [
  {
    id: 1,
    title: "Neon Rüya",
    artist: "CyberSynth",
    album: "Dijital Anılar",
    coverUrl: "https://via.placeholder.com/200x200/00ffff/000000",
    duration: "3:45",
    isLiked: true,
    playCount: 254,
    genre: "Synthwave"
  },
  {
    id: 2,
    title: "Gece Şehri",
    artist: "RetroWave",
    album: "Neon Bulvarı",
    coverUrl: "https://via.placeholder.com/200x200/ff00ff/000000",
    duration: "4:20",
    isLiked: false,
    playCount: 187,
    genre: "Cyberpunk"
  },
  {
    id: 3,
    title: "Dijital Yağmur",
    artist: "NeonEcho",
    album: "Elektronik Gökyüzü",
    coverUrl: "https://via.placeholder.com/200x200/00ff99/000000",
    duration: "5:12",
    isLiked: true,
    playCount: 321,
    genre: "Vaporwave"
  },
  {
    id: 4,
    title: "Krom Kalp",
    artist: "ByteDancer",
    album: "Lazer Işığı",
    coverUrl: "https://via.placeholder.com/200x200/6600ff/000000",
    duration: "3:59",
    isLiked: false,
    playCount: 145,
    genre: "Darksynth"
  },
  {
    id: 5,
    title: "Elektro Rüzgar",
    artist: "PixelNebula",
    album: "Kuantum Dalgaları",
    coverUrl: "https://via.placeholder.com/200x200/ff0099/000000",
    duration: "4:05",
    isLiked: true,
    playCount: 208,
    genre: "Chillwave"
  },
  {
    id: 6,
    title: "Kuantum Şafak",
    artist: "CyberSynth",
    album: "Dijital Anılar",
    coverUrl: "https://via.placeholder.com/200x200/00ffff/000000",
    duration: "3:35",
    isLiked: false,
    playCount: 167,
    genre: "Synthtrap"
  },
  {
    id: 7,
    title: "Megaşehir",
    artist: "GlitchCore",
    album: "Holografik",
    coverUrl: "https://via.placeholder.com/200x200/ff00ff/000000",
    duration: "4:47",
    isLiked: true,
    playCount: 283,
    genre: "Cyberpunk"
  },
  {
    id: 8,
    title: "Nöral Akış",
    artist: "DeepWave",
    album: "Sanal Okyanus",
    coverUrl: "https://via.placeholder.com/200x200/00ff99/000000",
    duration: "3:52",
    isLiked: false,
    playCount: 196,
    genre: "Electronic"
  }
];

// Main MusicLibrary Component
const MusicLibrary: React.FC<MusicLibraryProps> = ({
  compact = false,
  onSetCurrentTrack
}) => {
  const [activeView] = useState<'library' | 'playlist-detail'>('library');
  const [tracks, setTracks] = useState<MusicItem[]>(sampleMusicData);
  const [viewMode] = useState<'grid' | 'list'>(compact ? 'list' : 'list');
  
  // Handle track play
  const handlePlayTrack = (id: number) => {
    console.log(`Playing track ${id}`);
    // Find the track and set it as current track to show the music player
    const track = tracks.find(track => track.id === id);
    if (track && onSetCurrentTrack) {
      onSetCurrentTrack(track);
    }
  };
  
  // Combine recent and liked tracks, but avoid duplicates
  // First add recent tracks
  const recentTracks = tracks.slice(0, 8);
  // Then add liked tracks not already in recent tracks
  const likedTracks = tracks
    .filter(track => track.isLiked)
    .filter(likedTrack => !recentTracks.some(recentTrack => recentTrack.id === likedTrack.id));
  
  // Combine the two lists
  const combinedTracks = [...recentTracks, ...likedTracks];
  
  // If in compact mode, only show the combined list
  if (compact) {
    return (
      <div className="music-library compact">
        <CombinedMusicList
          tracks={combinedTracks}
          onPlay={handlePlayTrack}
          title=""
        />
      </div>
    );
  }
  
  return (
    <div className="music-library">
      {!compact && (
        <div className="library-header">
          <div className="view-tabs">
            <button
              className="view-tab active"
              onClick={() => {}}
            >
              <span className="tab-icon">📚</span>
              <span>Kütüphanem</span>
            </button>
            {/* Removed "Çalma Listelerim" tab as requested */}
          </div>
          
          <div className="view-controls">
            <button
              className={`view-mode-button ${viewMode === 'grid' ? 'active' : ''}`}
              onClick={() => {}}
              aria-label="Grid view"
            >
              <span className="view-icon">◫</span>
            </button>
            <button
              className={`view-mode-button ${viewMode === 'list' ? 'active' : ''}`}
              onClick={() => {}}
              aria-label="List view"
            >
              <span className="view-icon">≡</span>
            </button>
          </div>
        </div>
      )}
      
      {activeView === 'library' && (
        <div className="library-container">
          <div className="library-main">
            {/* Unified view of all tracks, with a preference for recent and liked ones */}
            <CombinedMusicList
              tracks={combinedTracks}
              onPlay={handlePlayTrack}
              title="Son Dinlenenler ve Beğenilenler"
            />
            <div className="section-divider"></div>
          </div>
        </div>
      )}
    </div>
  );
};

export default MusicLibrary;