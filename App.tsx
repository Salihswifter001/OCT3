// src/App.tsx
import { useState, useEffect } from 'react';
import './App.css';
import './MobileOptimizations.css';
import Sidebar from './components/Sidebar';
import GalaxyBackground from './components/GalaxyBackground';
import Settings from './components/Settings';
import EnhancedPromptGenerator from './components/EnhancedPromptGenerator';
import MusicLibrary from './components/MusicLibrary';
import Profile from './components/Profile';
import PlaylistCreationModal from './components/PlaylistCreationModal';
import WelcomeModal from './components/WelcomeModal';
import AppFooter from './components/AppFooter';
import ContextMenu from './components/ContextMenu';
import MusicPlayer from './components/MusicPlayer';
import settingsService from './services/SettingsService';
import deviceService, { DeviceInfo } from './services/DeviceService';

// Music track interface
interface MusicTrack {
  id: number;
  title: string;
  artist: string;
  coverUrl: string;
  duration: string;
  album?: string;
  isLiked?: boolean;
  playCount?: number;
  releaseDate?: string;
  genre?: string;
}

function App() {
  // App settings
  const [settings, setSettings] = useState(settingsService.getSettings());
  const [titleColor, setTitleColor] = useState<string>(
    settings.theme.neonColor
  );

  // Navigation state
  const [activeSection, setActiveSection] = useState<string>('home');
  const [showSettings, setShowSettings] = useState<boolean>(false);
  const [showProfile, setShowProfile] = useState<boolean>(false);
  const [showPlaylistCreation, setShowPlaylistCreation] = useState<boolean>(false);

  // Welcome modal state
  const [showWelcomeModal, setShowWelcomeModal] = useState<boolean>(false);

  // Music player state
  const [currentTrack, setCurrentTrack] = useState<MusicTrack | null>(null);

  // Device capability state
  const [deviceInfo, setDeviceInfo] = useState<DeviceInfo>(
    deviceService.getDeviceInfo()
  );
  const [deviceClass, setDeviceClass] = useState<string>('');

  // Context menu state
  const [contextMenu, setContextMenu] = useState<{
    x: number;
    y: number;
    items: Array<{
      icon: string;
      label: string;
      action: () => void;
      divider?: boolean;
      disabled?: boolean;
      danger?: boolean;
    }>;
  } | null>(null);

  // Apply device optimizations on mount
  useEffect(() => {
    deviceService.applyPerformanceOptimizations();

    // Subscribe to device changes
    const unsubscribe = deviceService.subscribe((info) => {
      setDeviceInfo(info);
      setDeviceClass(deviceService.getDeviceClass());
      deviceService.applyPerformanceOptimizations();
    });

    setDeviceClass(deviceService.getDeviceClass());

    return () => {
      unsubscribe();
    };
  }, []);

  // First login check
  useEffect(() => {
    // Check if welcome message has been shown from localStorage
    const welcomeShown = localStorage.getItem('welcomeModalShown');
    if (!welcomeShown) {
      setShowWelcomeModal(true);
    }
  }, []);

  // Listen for settings changes
  useEffect(() => {
    // Start listening for settings changes
    const unsubscribe = settingsService.subscribe(() => {
      setSettings(settingsService.getSettings());
    });

    return () => {
      unsubscribe(); // Cleanup function
    };
  }, []);

  // Color changing effect - only enable on high-performance devices
  useEffect(() => {
    // Skip animation on low-end devices
    if (!deviceService.shouldEnableHighPerformanceFeatures()) {
      setTitleColor(settings.theme.neonColor);
      return;
    }

    // Use theme color from settings
    const mainColor = settings.theme.neonColor;

    // Alternative colors (variations around the main color)
    const shiftedHue1 = settingsService.shiftColor(mainColor, 20);
    const shiftedHue2 = settingsService.shiftColor(mainColor, -20);
    const lightenedColor = settingsService.lightenColor(mainColor, 20);

    const colors = [
      mainColor,
      shiftedHue1,
      lightenedColor,
      shiftedHue2,
      mainColor,
    ];

    let colorIndex = 0;

    const interval = setInterval(() => {
      colorIndex = (colorIndex + 1) % colors.length;
      setTitleColor(colors[colorIndex]);
    }, 1000); // Change color every second

    return () => clearInterval(interval);
  }, [settings.theme.neonColor]);

  // Close context menu
  useEffect(() => {
    const handleDocumentClick = () => {
      setContextMenu(null);
    };

    document.addEventListener('click', handleDocumentClick);

    return () => {
      document.removeEventListener('click', handleDocumentClick);
    };
  }, []);

  // Custom context menu for right click - disable on touch devices
  useEffect(() => {
    // Skip on pure touch devices
    if (deviceInfo.isTouch && !deviceInfo.isMobile) {
      return;
    }

    const handleContextMenu = (e: MouseEvent) => {
      e.preventDefault();

      // Default menu items
      const menuItems = [
        {
          icon: '🏠',
          label: 'Ana Sayfa',
          action: () => setActiveSection('home'),
        },
        {
          icon: '📚',
          label: 'Kütüphanem',
          action: () => setActiveSection('library'),
        },
        {
          icon: '⚙️',
          label: 'Ayarlar',
          action: () => setShowSettings(true),
          divider: true,
        },
        {
          icon: '👤',
          label: 'Profilim',
          action: () => setShowProfile(true),
        },
        {
          icon: '🧹',
          label: 'Önbelleği Temizle',
          action: () => {
            // Cache cleaning process
            settingsService.clearCache();
            alert('Önbellek temizlendi!');
          },
        },
        {
          icon: '🔄',
          label: 'Sayfayı Yenile',
          action: () => window.location.reload(),
          divider: true,
        },
        {
          icon: 'ℹ️',
          label: 'Hakkında',
          action: () => alert('Octaverum AI v1.0.0\nCopyright © 2025 OctaInc.'),
        },
      ];

      setContextMenu({
        x: e.pageX,
        y: e.pageY,
        items: menuItems,
      });
    };

    // Enable right-click listening for the entire page
    document.addEventListener('contextmenu', handleContextMenu);

    return () => {
      document.removeEventListener('contextmenu', handleContextMenu);
    };
  }, [deviceInfo.isTouch, deviceInfo.isMobile]);

  // Music player handlers
  const handleSetCurrentTrack = (track: MusicTrack | null) => {
    setCurrentTrack(track);
  };

  const handlePreviousTrack = () => {
    // This is a placeholder - in a real app, you would manage track list and navigation at the App level
    // or through a context/state management system
    if (currentTrack) {
      // For now, we'll broadcast an event for MusicLibrary to handle
      const event = new CustomEvent('previous-track', { detail: { trackId: currentTrack.id } });
      document.dispatchEvent(event);
    }
  };

  const handleNextTrack = () => {
    // This is a placeholder - in a real app, you would manage track list and navigation at the App level
    if (currentTrack) {
      // For now, we'll broadcast an event for MusicLibrary to handle
      const event = new CustomEvent('next-track', { detail: { trackId: currentTrack.id } });
      document.dispatchEvent(event);
    }
  };

  // Sidebar handlers
  const handleSectionChange = (section: string) => {
    settingsService.playClickSound();
    setActiveSection(section);
  };

  const handleSettingsClick = () => {
    settingsService.playClickSound();
    setShowSettings(true);
  };

  const handleProfileClick = () => {
    settingsService.playClickSound();
    setShowProfile(true);
  };

  // Keyboard shortcuts
  useEffect(() => {
    // Set up keyboard shortcuts - only on desktop
    if (!deviceInfo.isMobile) {
      const cleanupKeyboardShortcuts = settingsService.setupKeyboardShortcuts();

      return () => {
        // Cleanup function
        cleanupKeyboardShortcuts();
      };
    }
  }, [deviceInfo.isMobile]);

  // Close welcome modal
  const handleCloseWelcomeModal = () => {
    setShowWelcomeModal(false);
  };

  // Helper function to render content based on active section
  const renderContent = () => {
    switch (activeSection) {
      case 'library':
        return (
          <>
            <h1
              className="title-animation octaverum-logo"
              style={{
                color: titleColor,
                textShadow: `0 0 10px ${titleColor}, 0 0 20px ${titleColor}, 0 0 30px ${titleColor}`,
              }}
            >
              Octaverum AI
            </h1>

            <div className="content-section">
              <MusicLibrary
                onSetCurrentTrack={handleSetCurrentTrack}
              />
            </div>
          </>
        );

      case 'home':
      default:
        return (
          <>
            <h1
              className="title-animation octaverum-logo"
              style={{
                color: titleColor,
                textShadow: deviceInfo.isLowEndDevice
                  ? 'none'
                  : `0 0 10px ${titleColor}, 0 0 20px ${titleColor}, 0 0 30px ${titleColor}`,
              }}
            >
              Octaverum AI
            </h1>

            <div className="content-section prompt-content-section">
              <EnhancedPromptGenerator />
            </div>
          </>
        );
    }
  };

  return (
    <div className={`app-container ${deviceClass}`}>
      {/* Only render GalaxyBackground on higher-end devices */}
      {deviceService.shouldEnableHighPerformanceFeatures() && (
        <GalaxyBackground />
      )}
      
      {/* Add a spacer at the bottom for music player */}
      <div style={{ height: '90px' }}></div>

      <Sidebar
        activeSection={activeSection}
        onSectionChange={handleSectionChange}
        onSettingsClick={handleSettingsClick}
        onProfileClick={handleProfileClick}
      />

      <main className="main-content">
        {renderContent()}
        <AppFooter />
      </main>

      {showSettings && (
        <Settings
          isOpen={showSettings}
          onClose={() => setShowSettings(false)}
        />
      )}

      {showProfile && (
        <Profile isOpen={showProfile} onClose={() => setShowProfile(false)} />
      )}
      
      {/* Playlist Creation Modal */}
      {showPlaylistCreation && (
        <PlaylistCreationModal
          isOpen={showPlaylistCreation}
          onClose={() => setShowPlaylistCreation(false)}
          onSave={(name, trackIds, coverUrl) => {
            // Modal'ı kapat
            setShowPlaylistCreation(false);
            
            // Navigate to playlists view to see the new playlist
            setActiveSection('playlists');
          }}
          availableTracks={[]} // We'll populate this in a real app
        />
      )}
      
      {showWelcomeModal && <WelcomeModal onClose={handleCloseWelcomeModal} />}

      {contextMenu && (
        <ContextMenu
          items={contextMenu.items}
          position={{ x: contextMenu.x, y: contextMenu.y }}
          onClose={() => setContextMenu(null)}
        />
      )}
      
      {/* Global Music Player - fixed at bottom of page */}
      <MusicPlayer
        currentTrack={currentTrack}
        onClose={() => setCurrentTrack(null)}
        onPrevious={handlePreviousTrack}
        onNext={handleNextTrack}
      />
    </div>
  );
}

export default App;