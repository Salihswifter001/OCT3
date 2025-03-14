// src/components/Settings.tsx
import React, { useState, useEffect } from 'react';
import './Settings.css';
import settingsService from '../services/SettingsService';

interface SettingsProps {
  isOpen: boolean;
  onClose: () => void;
}

// Ayarlar için tip tanımlamaları
interface AppSettings {
  theme: {
    darkMode: boolean;
    neonColor: string;
    glowIntensity: number;
  };
  instrument: {
    defaultInstrument: string;
  };
  audio: {
    autoplay: boolean;
    equalizer: {
      bass: number;
      treble: number;
    };
    showWaveform: boolean;
    transitionEffect: 'smooth' | 'instant';
  };
  aiMusic: {
    duration: '30s' | '60s' | '90s' | '120s';
    bpmRange: {
      min: number;
      max: number;
    };
    key: string;
    format: 'mp3' | 'wav' | 'flac';
  };
  shortcuts: {
    playPause: string;
    forward: string;
    backward: string;
    mute: string;
    fullscreen: string;
  };
  interface: {
    clickSounds: boolean;
    autoExpandSidebar: boolean;
  };
  account: {
    sessionTime: number; // dakika cinsinden
  };
  language: 'tr' | 'en' | 'es';
  storage: {
    musicQuality: 'low' | 'medium' | 'high';
  };
}

// Varsayılan ayarlar
const defaultSettings: AppSettings = {
  theme: {
    darkMode: true,
    neonColor: '#00ffff', // Cyan
    glowIntensity: 100,
  },
  instrument: {
    defaultInstrument: 'synth',
  },
  audio: {
    autoplay: false,
    equalizer: {
      bass: 50,
      treble: 50,
    },
    showWaveform: true,
    transitionEffect: 'smooth',
  },
  aiMusic: {
    duration: '60s',
    bpmRange: {
      min: 90,
      max: 140,
    },
    key: 'Minör A',
    format: 'mp3',
  },
  shortcuts: {
    playPause: 'Space',
    forward: 'ArrowRight',
    backward: 'ArrowLeft',
    mute: 'M',
    fullscreen: 'F',
  },
  interface: {
    clickSounds: true,
    autoExpandSidebar: true,
  },
  account: {
    sessionTime: 30,
  },
  language: 'tr',
  storage: {
    musicQuality: 'high',
  },
};

const Settings: React.FC<SettingsProps> = ({ isOpen, onClose }) => {
  const [activeTab, setActiveTab] = useState<string>('tema');
  const [settings, setSettings] = useState<AppSettings>(defaultSettings);
  const [hasChanges, setHasChanges] = useState<boolean>(false);

  // Ayarları service'den yükle
  useEffect(() => {
    const currentSettings = settingsService.getSettings();
    setSettings(currentSettings);
  }, []);

  // Ayarları kaydet
  const saveSettings = () => {
    try {
      // Tema ayarlarını uygula
      settingsService.updateTheme(settings.theme);
      
      // Audio ayarlarını uygula
      settingsService.updateAudio(settings.audio);
      
      // Diğer ayarları kategori bazında uygula
      settingsService.updateSetting('instrument', settings.instrument);
      settingsService.updateSetting('aiMusic', settings.aiMusic);
      settingsService.updateSetting('shortcuts', settings.shortcuts);
      settingsService.updateSetting('interface', settings.interface);
      settingsService.updateSetting('account', settings.account);
      settingsService.updateSetting('language', settings.language);
      settingsService.updateSetting('storage', settings.storage);
      
      setHasChanges(false);
      
      // Başarı sesi çal
      settingsService.playSuccessSound();
      
      // Kaydedildi efekti
      const saveButton = document.getElementById('save-settings-button');
      if (saveButton) {
        saveButton.classList.add('save-success');
        setTimeout(() => {
          saveButton.classList.remove('save-success');
        }, 1500);
      }
    } catch (error) {
      console.error('Ayarlar kaydedilirken hata oluştu:', error);
    }
  };

  // Ayarları sıfırla
  const resetSettings = () => {
    settingsService.resetSettings();
    setSettings(settingsService.getSettings());
    setHasChanges(true);
  };

  // Ayarlar değiştiğinde hasChanges'i güncelle
  useEffect(() => {
    setHasChanges(true);
  }, [settings]);

  // Tema rengini değiştir
  const handleColorChange = (color: string) => {
    setSettings(prev => ({
      ...prev,
      theme: {
        ...prev.theme,
        neonColor: color,
      }
    }));
    
    // Anlık olarak tema rengini uygula
    settingsService.updateTheme({ neonColor: color });
    settingsService.playClickSound();
  };

  if (!isOpen) return null;

  return (
    <div className="settings-overlay">
      <div className="settings-panel">
        <div className="settings-header">
          <h2>Ayarlar</h2>
          <div className="settings-close-button" onClick={onClose}>×</div>
        </div>
        
        <div className="settings-content">
          <div className="settings-tabs">
            <div 
              className={`settings-tab ${activeTab === 'tema' ? 'active' : ''}`}
              onClick={() => setActiveTab('tema')}
            >
              <span className="settings-tab-icon">🎨</span>
              <span>Tema</span>
            </div>
            <div 
              className={`settings-tab ${activeTab === 'enstruman' ? 'active' : ''}`}
              onClick={() => setActiveTab('enstruman')}
            >
              <span className="settings-tab-icon">🎹</span>
              <span>Enstrüman</span>
            </div>
            <div 
              className={`settings-tab ${activeTab === 'ses' ? 'active' : ''}`}
              onClick={() => setActiveTab('ses')}
            >
              <span className="settings-tab-icon">🔊</span>
              <span>Ses</span>
            </div>
            <div 
              className={`settings-tab ${activeTab === 'ai' ? 'active' : ''}`}
              onClick={() => setActiveTab('ai')}
            >
              <span className="settings-tab-icon">🤖</span>
              <span>AI Müzik</span>
            </div>
            <div 
              className={`settings-tab ${activeTab === 'kisayollar' ? 'active' : ''}`}
              onClick={() => setActiveTab('kisayollar')}
            >
              <span className="settings-tab-icon">⌨️</span>
              <span>Kısayollar</span>
            </div>
            <div 
              className={`settings-tab ${activeTab === 'arayuz' ? 'active' : ''}`}
              onClick={() => setActiveTab('arayuz')}
            >
              <span className="settings-tab-icon">📱</span>
              <span>Arayüz</span>
            </div>
            <div 
              className={`settings-tab ${activeTab === 'hesap' ? 'active' : ''}`}
              onClick={() => setActiveTab('hesap')}
            >
              <span className="settings-tab-icon">👤</span>
              <span>Hesap</span>
            </div>
            <div 
              className={`settings-tab ${activeTab === 'dil' ? 'active' : ''}`}
              onClick={() => setActiveTab('dil')}
            >
              <span className="settings-tab-icon">🌐</span>
              <span>Dil</span>
            </div>
            <div 
              className={`settings-tab ${activeTab === 'depolama' ? 'active' : ''}`}
              onClick={() => setActiveTab('depolama')}
            >
              <span className="settings-tab-icon">💾</span>
              <span>Depolama</span>
            </div>
          </div>
          
          <div className="settings-panel-content">
            {/* Tema Ayarları */}
            {activeTab === 'tema' && (
              <div className="settings-section">
                <h3>Tema Ayarları</h3>
                
                <div className="setting-item">
                  <label className="setting-label">Tema Modu</label>
                  <div className="switch-container">
                    <label className="switch">
                      <input
                        type="checkbox"
                        checked={settings.theme.darkMode}
                        onChange={(e) => {
                          const newDarkMode = e.target.checked;
                          setSettings(prev => ({
                            ...prev,
                            theme: {
                              ...prev.theme,
                              darkMode: newDarkMode,
                            }
                          }));
                          // Anlık olarak tema modunu uygula
                          settingsService.updateTheme({ darkMode: newDarkMode });
                          settingsService.playClickSound();
                        }}
                      />
                      <span className="slider"></span>
                    </label>
                    <span className="switch-label">{settings.theme.darkMode ? 'Karanlık Mod' : 'Aydınlık Mod'}</span>
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">Neon Renk Seçimi</label>
                  <div className="color-picker">
                    <div 
                      className={`color-option ${settings.theme.neonColor === '#00ffff' ? 'selected' : ''}`}
                      style={{ backgroundColor: '#00ffff' }}
                      onClick={() => handleColorChange('#00ffff')}
                      title="Cyan"
                    ></div>
                    <div 
                      className={`color-option ${settings.theme.neonColor === '#ff00ff' ? 'selected' : ''}`}
                      style={{ backgroundColor: '#ff00ff' }}
                      onClick={() => handleColorChange('#ff00ff')}
                      title="Mor"
                    ></div>
                    <div 
                      className={`color-option ${settings.theme.neonColor === '#ff0099' ? 'selected' : ''}`}
                      style={{ backgroundColor: '#ff0099' }}
                      onClick={() => handleColorChange('#ff0099')}
                      title="Pembe"
                    ></div>
                    <div 
                      className={`color-option ${settings.theme.neonColor === '#00ff99' ? 'selected' : ''}`}
                      style={{ backgroundColor: '#00ff99' }}
                      onClick={() => handleColorChange('#00ff99')}
                      title="Yeşil"
                    ></div>
                    <div 
                      className={`color-option ${settings.theme.neonColor === '#6600ff' ? 'selected' : ''}`}
                      style={{ backgroundColor: '#6600ff' }}
                      onClick={() => handleColorChange('#6600ff')}
                      title="Mavi Mor"
                    ></div>
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">Parlaklık Yoğunluğu</label>
                  <div className="slider-container">
                    <input
                      type="range"
                      min="0"
                      max="100"
                      value={settings.theme.glowIntensity}
                      onChange={(e) => {
                        const newIntensity = parseInt(e.target.value);
                        setSettings(prev => ({
                          ...prev,
                          theme: {
                            ...prev.theme,
                            glowIntensity: newIntensity,
                          }
                        }));
                        // Anlık olarak parlaklık değerini uygula
                        settingsService.updateTheme({ glowIntensity: newIntensity });
                      }}
                      onMouseUp={() => settingsService.playClickSound()}
                      className="range-slider"
                    />
                    <span className="slider-value">{settings.theme.glowIntensity}%</span>
                  </div>
                </div>
              </div>
            )}
            
            {/* Enstrüman Ayarları */}
            {activeTab === 'enstruman' && (
              <div className="settings-section">
                <h3>Enstrüman Ayarları</h3>
                
                <div className="setting-item">
                  <label className="setting-label">Varsayılan Enstrüman</label>
                  <div className="select-container">
                    <select
                      value={settings.instrument.defaultInstrument}
                      onChange={(e) => {
                        const newInstrument = e.target.value;
                        setSettings(prev => ({
                          ...prev,
                          instrument: {
                            ...prev.instrument,
                            defaultInstrument: newInstrument,
                          }
                        }));
                        // Varsayılan enstrümanı ayarla
                        settingsService.setDefaultInstrument(newInstrument);
                      }}
                      className="settings-select"
                    >
                      <option value="synth">Synthesizer</option>
                      <option value="piano">Piyano</option>
                      <option value="guitar">Gitar</option>
                      <option value="drums">Davul</option>
                      <option value="bass">Bas</option>
                      <option value="strings">Yaylılar</option>
                    </select>
                  </div>
                </div>
              </div>
            )}
            
            {/* Ses Ayarları */}
            {activeTab === 'ses' && (
              <div className="settings-section">
                <h3>Ses Ayarları</h3>
                
                <div className="setting-item">
                  <label className="setting-label">Otomatik Oynatma</label>
                  <div className="switch-container">
                    <label className="switch">
                      <input
                        type="checkbox"
                        checked={settings.audio.autoplay}
                        onChange={(e) => {
                          const autoplay = e.target.checked;
                          setSettings(prev => ({
                            ...prev,
                            audio: {
                              ...prev.audio,
                              autoplay: autoplay,
                            }
                          }));
                          // Otomatik oynatmayı ayarla
                          settingsService.toggleAutoplay(autoplay);
                        }}
                      />
                      <span className="slider"></span>
                    </label>
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">Bas Seviyesi</label>
                  <div className="slider-container">
                    <input
                      type="range"
                      min="0"
                      max="100"
                      value={settings.audio.equalizer.bass}
                      onChange={(e) => {
                        const newBass = parseInt(e.target.value);
                        setSettings(prev => ({
                          ...prev,
                          audio: {
                            ...prev.audio,
                            equalizer: {
                              ...prev.audio.equalizer,
                              bass: newBass,
                            }
                          }
                        }));
                        // Bas ve tiz değerlerini uygula - özel bir method çağırma, sürükleme sırasında sürekli uygulamama
                      }}
                      onMouseUp={() => {
                        // Mouse bırakıldığında eşitleyici ayarlarını güncelle
                        settingsService.updateEqualizer(
                          settings.audio.equalizer.bass,
                          settings.audio.equalizer.treble
                        );
                      }}
                      className="range-slider"
                    />
                    <span className="slider-value">{settings.audio.equalizer.bass}%</span>
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">Tiz Seviyesi</label>
                  <div className="slider-container">
                    <input
                      type="range"
                      min="0"
                      max="100"
                      value={settings.audio.equalizer.treble}
                      onChange={(e) => {
                        const newTreble = parseInt(e.target.value);
                        setSettings(prev => ({
                          ...prev,
                          audio: {
                            ...prev.audio,
                            equalizer: {
                              ...prev.audio.equalizer,
                              treble: newTreble,
                            }
                          }
                        }));
                        // Tiz değerlerini uygula - sürükleme sırasında sürekli uygulamama
                      }}
                      onMouseUp={() => {
                        // Mouse bırakıldığında eşitleyici ayarlarını güncelle
                        settingsService.updateEqualizer(
                          settings.audio.equalizer.bass,
                          settings.audio.equalizer.treble
                        );
                      }}
                      className="range-slider"
                    />
                    <span className="slider-value">{settings.audio.equalizer.treble}%</span>
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">Ses Dalga Gösterimi</label>
                  <div className="switch-container">
                    <label className="switch">
                      <input
                        type="checkbox"
                        checked={settings.audio.showWaveform}
                        onChange={(e) => {
                          const showWaveform = e.target.checked;
                          setSettings(prev => ({
                            ...prev,
                            audio: {
                              ...prev.audio,
                              showWaveform: showWaveform,
                            }
                          }));
                          // Dalga formunu gösterme ayarını uygula
                          settingsService.toggleWaveform(showWaveform);
                        }}
                      />
                      <span className="slider"></span>
                    </label>
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">Geçiş Efekti</label>
                  <div className="radio-container">
                    <label className="radio-label">
                      <input
                        type="radio"
                        name="transitionEffect"
                        value="smooth"
                        checked={settings.audio.transitionEffect === 'smooth'}
                        onChange={() => {
                          setSettings(prev => ({
                            ...prev,
                            audio: {
                              ...prev.audio,
                              transitionEffect: 'smooth',
                            }
                          }));
                          // Geçiş efektini ayarla
                          settingsService.setTransitionEffect('smooth');
                        }}
                      />
                      <span>Yumuşak</span>
                    </label>
                    <label className="radio-label">
                      <input
                        type="radio"
                        name="transitionEffect"
                        value="instant"
                        checked={settings.audio.transitionEffect === 'instant'}
                        onChange={() => {
                          setSettings(prev => ({
                            ...prev,
                            audio: {
                              ...prev.audio,
                              transitionEffect: 'instant',
                            }
                          }));
                          // Geçiş efektini ayarla
                          settingsService.setTransitionEffect('instant');
                        }}
                      />
                      <span>Anlık</span>
                    </label>
                  </div>
                </div>
              </div>
            )}
            
            {/* AI Müzik Ayarları */}
            {activeTab === 'ai' && (
              <div className="settings-section">
                <h3>AI Müzik Üretim Ayarları</h3>
                
                <div className="setting-item">
                  <label className="setting-label">Şarkı Süresi</label>
                  <div className="select-container">
                    <select
                      value={settings.aiMusic.duration}
                      onChange={(e) => {
                        const duration = e.target.value as '30s' | '60s' | '90s' | '120s';
                        setSettings(prev => ({
                          ...prev,
                          aiMusic: {
                            ...prev.aiMusic,
                            duration: duration,
                          }
                        }));
                        // Şarkı süresini ayarla
                        settingsService.updateSongDuration(duration);
                      }}
                      className="settings-select"
                    >
                      <option value="30s">30 saniye</option>
                      <option value="60s">60 saniye</option>
                      <option value="90s">90 saniye</option>
                      <option value="120s">120 saniye</option>
                    </select>
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">BPM Aralığı (Tempo)</label>
                  <div className="double-slider-container">
                    <span className="slider-min-value">{settings.aiMusic.bpmRange.min}</span>
                    <input 
                      type="range" 
                      min="60" 
                      max="200" 
                      value={settings.aiMusic.bpmRange.min}
                      onChange={(e) => setSettings(prev => ({
                        ...prev,
                        aiMusic: {
                          ...prev.aiMusic,
                          bpmRange: {
                            ...prev.aiMusic.bpmRange,
                            min: parseInt(e.target.value),
                          }
                        }
                      }))}
                      className="range-slider"
                    />
                    <span className="slider-max-value">{settings.aiMusic.bpmRange.max}</span>
                    <input 
                      type="range" 
                      min="60" 
                      max="200" 
                      value={settings.aiMusic.bpmRange.max}
                      onChange={(e) => setSettings(prev => ({
                        ...prev,
                        aiMusic: {
                          ...prev.aiMusic,
                          bpmRange: {
                            ...prev.aiMusic.bpmRange,
                            max: parseInt(e.target.value),
                          }
                        }
                      }))}
                      className="range-slider"
                    />
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">Müzik Anahtar Tonu</label>
                  <div className="select-container">
                    <select 
                      value={settings.aiMusic.key}
                      onChange={(e) => setSettings(prev => ({
                        ...prev,
                        aiMusic: {
                          ...prev.aiMusic,
                          key: e.target.value,
                        }
                      }))}
                      className="settings-select"
                    >
                      <option value="Minör A">Minör A</option>
                      <option value="Majör C">Majör C</option>
                      <option value="Minör E">Minör E</option>
                      <option value="Majör G">Majör G</option>
                      <option value="Minör B">Minör B</option>
                      <option value="Majör D">Majör D</option>
                      <option value="Minör F#">Minör F#</option>
                    </select>
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">Çıktı Formatı</label>
                  <div className="radio-container">
                    <label className="radio-label">
                      <input 
                        type="radio" 
                        name="format" 
                        value="mp3"
                        checked={settings.aiMusic.format === 'mp3'}
                        onChange={() => setSettings(prev => ({
                          ...prev,
                          aiMusic: {
                            ...prev.aiMusic,
                            format: 'mp3',
                          }
                        }))}
                      />
                      <span>MP3</span>
                    </label>
                    <label className="radio-label">
                      <input 
                        type="radio" 
                        name="format" 
                        value="wav"
                        checked={settings.aiMusic.format === 'wav'}
                        onChange={() => setSettings(prev => ({
                          ...prev,
                          aiMusic: {
                            ...prev.aiMusic,
                            format: 'wav',
                          }
                        }))}
                      />
                      <span>WAV</span>
                    </label>
                    <label className="radio-label">
                      <input 
                        type="radio" 
                        name="format" 
                        value="flac"
                        checked={settings.aiMusic.format === 'flac'}
                        onChange={() => setSettings(prev => ({
                          ...prev,
                          aiMusic: {
                            ...prev.aiMusic,
                            format: 'flac',
                          }
                        }))}
                      />
                      <span>FLAC</span>
                    </label>
                  </div>
                </div>
              </div>
            )}
            
            {/* Kısayollar Ayarları */}
            {activeTab === 'kisayollar' && (
              <div className="settings-section">
                <h3>Klavye Kısayolları</h3>
                
                <div className="setting-item">
                  <label className="setting-label">Oynat/Duraklat</label>
                  <div className="shortcut-key">
                    {settings.shortcuts.playPause}
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">İleri Sarma</label>
                  <div className="shortcut-key">
                    {settings.shortcuts.forward}
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">Geri Sarma</label>
                  <div className="shortcut-key">
                    {settings.shortcuts.backward}
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">Sessize Alma</label>
                  <div className="shortcut-key">
                    {settings.shortcuts.mute}
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">Tam Ekran</label>
                  <div className="shortcut-key">
                    {settings.shortcuts.fullscreen}
                  </div>
                </div>
                
                <div className="shortcut-help">
                  <p>Kısayolları değiştirmek için yakında destek eklenecek. Şu anda varsayılan kısayolları kullanabilirsiniz.</p>
                </div>
              </div>
            )}
            
            {/* Arayüz Ayarları */}
            {activeTab === 'arayuz' && (
              <div className="settings-section">
                <h3>Arayüz Ayarları</h3>
                
                <div className="setting-item">
                  <label className="setting-label">Tıklama Ses Efektleri</label>
                  <div className="switch-container">
                    <label className="switch">
                      <input
                        type="checkbox"
                        checked={settings.interface.clickSounds}
                        onChange={(e) => {
                          const clickSounds = e.target.checked;
                          setSettings(prev => ({
                            ...prev,
                            interface: {
                              ...prev.interface,
                              clickSounds: clickSounds,
                            }
                          }));
                          // Tıklama seslerini ayarla
                          settingsService.toggleClickSounds(clickSounds);
                        }}
                      />
                      <span className="slider"></span>
                    </label>
                  </div>
                </div>
                
                <div className="setting-item">
                  <label className="setting-label">Sidebar Otomatik Açılsın</label>
                  <div className="switch-container">
                    <label className="switch">
                      <input
                        type="checkbox"
                        checked={settings.interface.autoExpandSidebar}
                        onChange={(e) => {
                          const autoExpand = e.target.checked;
                          setSettings(prev => ({
                            ...prev,
                            interface: {
                              ...prev.interface,
                              autoExpandSidebar: autoExpand,
                            }
                          }));
                          // Sidebar otomatik genişlemesini ayarla
                          settingsService.toggleAutoExpandSidebar(autoExpand);
                        }}
                      />
                      <span className="slider"></span>
                    </label>
                  </div>
                </div>
              </div>
            )}
            
            {/* Hesap Ayarları */}
            {activeTab === 'hesap' && (
              <div className="settings-section">
                <h3>Hesap ve Güvenlik Ayarları</h3>
                
                <div className="setting-item">
                  <label className="setting-label">Oturum Süresi (dakika)</label>
                  <div className="select-container">
                    <select
                      value={settings.account.sessionTime}
                      onChange={(e) => {
                        const sessionTime = parseInt(e.target.value);
                        setSettings(prev => ({
                          ...prev,
                          account: {
                            ...prev.account,
                            sessionTime: sessionTime,
                          }
                        }));
                        // Oturum süresini ayarla
                        settingsService.setSessionTime(sessionTime);
                      }}
                      className="settings-select"
                    >
                      <option value="15">15 dakika</option>
                      <option value="30">30 dakika</option>
                      <option value="60">60 dakika</option>
                      <option value="120">120 dakika</option>
                      <option value="0">Oturumu açık tut</option>
                    </select>
                  </div>
                </div>
                
                <div className="account-actions">
                  <button className="account-button password-change">
                    Şifre Değiştir
                  </button>
                  <button className="account-button danger">
                    Hesabı Sil
                  </button>
                </div>
              </div>
            )}
            
            {/* Dil Ayarları */}
            {activeTab === 'dil' && (
              <div className="settings-section">
                <h3>Dil Ayarları</h3>
                
                <div className="setting-item">
                  <label className="setting-label">Uygulama Dili</label>
                  <div className="select-container">
                    <select
                      value={settings.language}
                      onChange={(e) => {
                        const language = e.target.value as 'tr' | 'en' | 'es';
                        setSettings(prev => ({
                          ...prev,
                          language: language,
                        }));
                        // Dili değiştir
                        settingsService.changeLanguage(language);
                      }}
                      className="settings-select"
                    >
                      <option value="tr">Türkçe</option>
                      <option value="en">English</option>
                      <option value="es">Español</option>
                    </select>
                  </div>
                </div>
              </div>
            )}
            
            {/* Depolama Ayarları */}
            {activeTab === 'depolama' && (
              <div className="settings-section">
                <h3>Veri ve Depolama Yönetimi</h3>
                
                <div className="setting-item">
                  <label className="setting-label">Müzik Kalitesi</label>
                  <div className="radio-container">
                    <label className="radio-label">
                      <input
                        type="radio"
                        name="musicQuality"
                        value="low"
                        checked={settings.storage.musicQuality === 'low'}
                        onChange={() => {
                          setSettings(prev => ({
                            ...prev,
                            storage: {
                              ...prev.storage,
                              musicQuality: 'low',
                            }
                          }));
                          // Müzik kalitesini ayarla
                          settingsService.setMusicQuality('low');
                        }}
                      />
                      <span>Düşük</span>
                    </label>
                    <label className="radio-label">
                      <input
                        type="radio"
                        name="musicQuality"
                        value="medium"
                        checked={settings.storage.musicQuality === 'medium'}
                        onChange={() => {
                          setSettings(prev => ({
                            ...prev,
                            storage: {
                              ...prev.storage,
                              musicQuality: 'medium',
                            }
                          }));
                          // Müzik kalitesini ayarla
                          settingsService.setMusicQuality('medium');
                        }}
                      />
                      <span>Orta</span>
                    </label>
                    <label className="radio-label">
                      <input
                        type="radio"
                        name="musicQuality"
                        value="high"
                        checked={settings.storage.musicQuality === 'high'}
                        onChange={() => {
                          setSettings(prev => ({
                            ...prev,
                            storage: {
                              ...prev.storage,
                              musicQuality: 'high',
                            }
                          }));
                          // Müzik kalitesini ayarla
                          settingsService.setMusicQuality('high');
                        }}
                      />
                      <span>Yüksek</span>
                    </label>
                  </div>
                </div>
                
                <div className="storage-actions">
                  <button className="storage-button clear-cache">
                    Önbelleği Temizle
                  </button>
                  <button className="storage-button clear-history">
                    Geçmişi Temizle
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
        
        <div className="settings-footer">
          <button 
            className="settings-button reset"
            onClick={resetSettings}
          >
            Varsayılanlara Sıfırla
          </button>
          <button 
            id="save-settings-button"
            className={`settings-button save ${hasChanges ? 'active' : ''}`}
            onClick={saveSettings}
            disabled={!hasChanges}
          >
            Değişiklikleri Kaydet
          </button>
        </div>
      </div>
    </div>
  );
};

export default Settings;