import React, { useState, useRef, useEffect } from 'react';
import './MusicPlayer.css';

interface MusicPlayerProps {
  currentTrack: {
    id: number;
    title: string;
    artist: string;
    coverUrl: string;
    duration: string;
  } | null;
  onClose: () => void;
  onPrevious?: () => void;
  onNext?: () => void;
}

const MusicPlayer: React.FC<MusicPlayerProps> = ({
  currentTrack,
  onClose,
  onPrevious = () => console.log("Previous track"),
  onNext = () => console.log("Next track")
}) => {
  const [isPlaying, setIsPlaying] = useState<boolean>(false);
  const [currentTime, setCurrentTime] = useState<number>(0);
  const [duration, setDuration] = useState<number>(0);
  const [volume, setVolume] = useState<number>(80);
  const [isLooping, setIsLooping] = useState<boolean>(false);
  const [isMuted, setIsMuted] = useState<boolean>(false);
  const [showVolumeControl, setShowVolumeControl] = useState<boolean>(false);
  
  const audioRef = useRef<HTMLAudioElement>(null);
  const progressRef = useRef<HTMLDivElement>(null);

  // Convert duration string (e.g., "3:45") to seconds
  const convertDurationToSeconds = (durationStr: string): number => {
    const parts = durationStr.split(':');
    if (parts.length === 2) {
      const minutes = parseInt(parts[0], 10);
      const seconds = parseInt(parts[1], 10);
      return minutes * 60 + seconds;
    }
    return 0;
  };

  // Format seconds to mm:ss
  const formatTime = (seconds: number): string => {
    const minutes = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
  };

  // Handle track play/pause
  const handlePlayPause = () => {
    if (audioRef.current) {
      if (isPlaying) {
        audioRef.current.pause();
      } else {
        audioRef.current.play();
      }
      setIsPlaying(!isPlaying);
    }
  };

  // Handle track progress change when clicking on the progress bar
  const handleProgressClick = (e: React.MouseEvent<HTMLDivElement>) => {
    if (progressRef.current && audioRef.current) {
      const rect = progressRef.current.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const percentage = x / rect.width;
      const newTime = percentage * duration;
      audioRef.current.currentTime = newTime;
      setCurrentTime(newTime);
    }
  };

  // Handle volume change
  const handleVolumeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = parseInt(e.target.value, 10);
    setVolume(value);
    if (audioRef.current) {
      audioRef.current.volume = value / 100;
    }
  };

  // Handle mute/unmute
  const handleMuteToggle = () => {
    if (audioRef.current) {
      audioRef.current.muted = !isMuted;
      setIsMuted(!isMuted);
    }
  };

  // Handle loop toggle
  const handleLoopToggle = () => {
    if (audioRef.current) {
      audioRef.current.loop = !isLooping;
      setIsLooping(!isLooping);
    }
  };

  // Effect to update audio element when currentTrack changes
  useEffect(() => {
    if (currentTrack && audioRef.current) {
      // In a real app, you would set the audio source to a real mp3 file
      // For this demo, we're simulating with the convertDurationToSeconds function
      setDuration(convertDurationToSeconds(currentTrack.duration));
      setCurrentTime(0);
      setIsPlaying(true);
      
      // Simulate starting playback
      setTimeout(() => {
        if (audioRef.current) {
          audioRef.current.play();
        }
      }, 0);
    }
  }, [currentTrack]);

  // Effect to handle time updates
  useEffect(() => {
    const audio = audioRef.current;
    
    const updateTime = () => {
      if (audio) {
        setCurrentTime(audio.currentTime);
      }
    };
    
    if (audio) {
      audio.addEventListener('timeupdate', updateTime);
      audio.addEventListener('loadedmetadata', () => {
        setDuration(audio.duration);
      });
      
      return () => {
        audio.removeEventListener('timeupdate', updateTime);
        audio.removeEventListener('loadedmetadata', () => {});
      };
    }
  }, []);

  // If no track is selected, don't render the player
  if (!currentTrack) return null;

  return (
    <div className="music-player">
      {/* Audio element (hidden) */}
      <audio 
        ref={audioRef}
        src={`#`} // In a real app, this would be a real audio file URL
        loop={isLooping}
      />
      
      {/* Track Info */}
      <div className="track-info">
        <div className="track-cover">
          <img src={currentTrack.coverUrl} alt={currentTrack.title} />
          <div className="track-cover-glow"></div>
        </div>
        <div className="track-details">
          <div className="track-title">{currentTrack.title}</div>
          <div className="track-artist">{currentTrack.artist}</div>
        </div>
      </div>
      
      {/* Player Controls */}
      <div className="player-controls">
        <div className="control-buttons">
          <button
            className="control-button previous"
            onClick={onPrevious}
            title="Previous"
          >
            <span>⏮</span>
          </button>
          
          <button
            className="control-button play-pause"
            onClick={handlePlayPause}
            title={isPlaying ? "Pause" : "Play"}
          >
            <span>{isPlaying ? '⏸' : '▶'}</span>
          </button>
          
          <button
            className="control-button next"
            onClick={onNext}
            title="Next"
          >
            <span>⏭</span>
          </button>
        </div>
        
        <div className="progress-container">
          <span className="time current">{formatTime(currentTime)}</span>
          <div 
            className="progress-bar" 
            ref={progressRef}
            onClick={handleProgressClick}
          >
            <div 
              className="progress-fill" 
              style={{ width: `${(currentTime / duration) * 100}%` }}
            ></div>
            <div 
              className="progress-handle"
              style={{ left: `${(currentTime / duration) * 100}%` }}
            ></div>
          </div>
          <span className="time total">{formatTime(duration)}</span>
        </div>
      </div>
      
      {/* Secondary Controls */}
      <div className="secondary-controls">
        <button 
          className={`control-button loop ${isLooping ? 'active' : ''}`}
          onClick={handleLoopToggle}
          title={isLooping ? "Disable Loop" : "Loop"}
        >
          <span>🔁</span>
        </button>
        
        <div className="volume-container">
          <button 
            className="control-button volume"
            onClick={() => setShowVolumeControl(!showVolumeControl)}
            title="Volume"
          >
            <span>{isMuted ? '🔇' : volume > 50 ? '🔊' : '🔉'}</span>
          </button>
          
          {showVolumeControl && (
            <div className="volume-control">
              <input 
                type="range" 
                min="0" 
                max="100" 
                value={volume}
                onChange={handleVolumeChange}
                className="volume-slider"
              />
              <button 
                className="control-button mute"
                onClick={handleMuteToggle}
                title={isMuted ? "Unmute" : "Mute"}
              >
                <span>{isMuted ? '🔇' : '🔊'}</span>
              </button>
            </div>
          )}
        </div>
        
        <button 
          className="control-button minimize"
          onClick={onClose}
          title="Close Player"
        >
          <span>✕</span>
        </button>
      </div>
    </div>
  );
};

export default MusicPlayer;