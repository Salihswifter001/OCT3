// src/components/PlaylistCreationModal.tsx
import React, { useState } from 'react';
import './PlaylistCreationModal.css';

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

interface PlaylistCreationModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSave: (name: string, tracks: number[], coverUrl: string) => void;
  availableTracks: MusicItem[];
}

const PlaylistCreationModal: React.FC<PlaylistCreationModalProps> = ({
  isOpen,
  onClose,
  onSave,
  availableTracks
}) => {
  const [playlistName, setPlaylistName] = useState<string>('');
  const [selectedTracks, setSelectedTracks] = useState<number[]>([]);
  const [coverImage, setCoverImage] = useState<string>('');
  const [coverPreview, setCoverPreview] = useState<string>('');
  const [activeTab, setActiveTab] = useState<'info' | 'tracks'>('info');
  const [searchQuery, setSearchQuery] = useState<string>('');

  if (!isOpen) return null;

  const handleImageUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (event) => {
        if (event.target?.result) {
          setCoverPreview(event.target.result as string);
          setCoverImage(event.target.result as string);
        }
      };
      reader.readAsDataURL(file);
    }
  };

  const handleTrackToggle = (trackId: number) => {
    setSelectedTracks(prev => 
      prev.includes(trackId)
        ? prev.filter(id => id !== trackId)
        : [...prev, trackId]
    );
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (playlistName.trim()) {
      onSave(
        playlistName, 
        selectedTracks, 
        coverImage || 'https://via.placeholder.com/200x200/00ffff/000000'
      );
      // Reset form
      setPlaylistName('');
      setSelectedTracks([]);
      setCoverImage('');
      setCoverPreview('');
      onClose();
    }
  };

  const filteredTracks = availableTracks.filter(track => 
    track.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
    track.artist.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div className="modal-overlay">
      <div className="playlist-creation-modal">
        <div className="modal-header">
          <h2>Yeni Çalma Listesi Oluştur</h2>
          <button className="close-button" onClick={onClose}>×</button>
        </div>

        <div className="tab-navigation">
          <button 
            className={`tab-button ${activeTab === 'info' ? 'active' : ''}`}
            onClick={() => setActiveTab('info')}
          >
            Temel Bilgiler
          </button>
          <button 
            className={`tab-button ${activeTab === 'tracks' ? 'active' : ''}`}
            onClick={() => setActiveTab('tracks')}
          >
            Parçalar <span className="count-badge">{selectedTracks.length}</span>
          </button>
        </div>

        <form onSubmit={handleSubmit}>
          {activeTab === 'info' && (
            <div className="modal-content info-tab">
              <div className="form-group">
                <label htmlFor="playlist-name">Çalma Listesi Adı</label>
                <input
                  id="playlist-name"
                  type="text"
                  value={playlistName}
                  onChange={(e) => setPlaylistName(e.target.value)}
                  placeholder="Çalma listesi adını girin"
                  required
                />
              </div>

              <div className="cover-upload-section">
                <div className="cover-preview">
                  {coverPreview ? (
                    <img src={coverPreview} alt="Kapak önizleme" />
                  ) : (
                    <div className="placeholder-cover">
                      <span>Kapak Resmi</span>
                    </div>
                  )}
                </div>
                <div className="upload-controls">
                  <label htmlFor="cover-upload" className="upload-button">
                    Kapak Resmi Yükle
                  </label>
                  <input
                    id="cover-upload"
                    type="file"
                    accept="image/*"
                    onChange={handleImageUpload}
                    className="file-input"
                  />
                  <p className="upload-hint">PNG veya JPG, önerilen: 300x300px</p>
                </div>
              </div>
            </div>
          )}

          {activeTab === 'tracks' && (
            <div className="modal-content tracks-tab">
              <div className="search-container">
                <input
                  type="text"
                  placeholder="Parça veya sanatçı ara..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="search-input"
                />
              </div>

              <div className="tracks-list">
                {filteredTracks.length === 0 ? (
                  <p className="no-tracks-message">Şarkı bulunamadı.</p>
                ) : (
                  filteredTracks.map(track => (
                    <div 
                      key={track.id} 
                      className={`track-item ${selectedTracks.includes(track.id) ? 'selected' : ''}`}
                      onClick={() => handleTrackToggle(track.id)}
                    >
                      <div className="track-checkbox">
                        <input
                          type="checkbox"
                          checked={selectedTracks.includes(track.id)}
                          onChange={() => {}} // Handled by div click
                          onClick={(e) => e.stopPropagation()}
                        />
                      </div>
                      <div className="track-cover">
                        <img src={track.coverUrl} alt={track.title} />
                      </div>
                      <div className="track-details">
                        <div className="track-title">{track.title}</div>
                        <div className="track-artist">{track.artist}</div>
                      </div>
                      <div className="track-duration">{track.duration}</div>
                    </div>
                  ))
                )}
              </div>
            </div>
          )}

          <div className="modal-footer">
            <button type="button" className="cancel-button" onClick={onClose}>
              İptal
            </button>
            <button 
              type="submit" 
              className="save-button"
              disabled={playlistName.trim() === '' || selectedTracks.length === 0}
            >
              Çalma Listesi Oluştur
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default PlaylistCreationModal;