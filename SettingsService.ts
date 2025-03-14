// src/services/SettingsService.ts
// Ayarları merkezi bir yerden yönetmek için servis
export interface AppSettings {
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

// LocalStorage anahtar ismi
const SETTINGS_STORAGE_KEY = 'octaverum_settings';

// Ses Efektleri
const CLICK_SOUND_URL = '/sounds/click.mp3';
const HOVER_SOUND_URL = '/sounds/hover.mp3';
const SUCCESS_SOUND_URL = '/sounds/success.mp3';

class SettingsService {
  private settings: AppSettings;
  private listeners: Set<() => void> = new Set();

  constructor() {
    this.settings = this.loadSettings();
    this.applySettings();
  }

  // Ayarları localStorage'dan yükleme
  private loadSettings(): AppSettings {
    try {
      const savedSettings = localStorage.getItem(SETTINGS_STORAGE_KEY);
      if (savedSettings) {
        return { ...defaultSettings, ...JSON.parse(savedSettings) };
      }
    } catch (error) {
      console.error('Ayarlar yüklenirken hata oluştu:', error);
    }
    return defaultSettings;
  }

  // Ayarları localStorage'a kaydetme
  private saveSettings(): void {
    try {
      localStorage.setItem(SETTINGS_STORAGE_KEY, JSON.stringify(this.settings));
      this.notifyListeners();
    } catch (error) {
      console.error('Ayarlar kaydedilirken hata oluştu:', error);
    }
  }

  // Ayarları uygulama genelinde uygulama
  private applySettings(): void {
    this.applyThemeSettings();
    this.applyEqSettings();
  }

  // Tema ayarlarını uygulama
  private applyThemeSettings(): void {
    const { neonColor, glowIntensity, darkMode } = this.settings.theme;

    // Root CSS değişkenlerini güncelleme
    document.documentElement.style.setProperty('--primary-color', neonColor);
    document.documentElement.style.setProperty(
      '--neon-glow',
      `0 0 ${glowIntensity * 0.1}px ${neonColor}, 0 0 ${
        glowIntensity * 0.2
      }px ${neonColor}`
    );

    // Alternatif renk hesaplamalarını uygulama
    const computedColor = this.shiftColor(neonColor, 180);
    document.documentElement.style.setProperty(
      '--secondary-color',
      computedColor
    );
    document.documentElement.style.setProperty(
      '--accent-color',
      this.shiftColor(neonColor, 90)
    );

    // Karanlık/Aydınlık mod geçişi
    if (!darkMode) {
      document.documentElement.style.setProperty(
        '--background-dark',
        '#e6e6f0'
      );
      document.documentElement.style.setProperty(
        '--background-light',
        '#d6d6e0'
      );
      document.documentElement.style.setProperty('--text-color', '#1a1a2e');
    } else {
      document.documentElement.style.setProperty(
        '--background-dark',
        '#0a0a12'
      );
      document.documentElement.style.setProperty(
        '--background-light',
        '#1a1a2e'
      );
      document.documentElement.style.setProperty('--text-color', '#e0e0e0');
    }
  }

  // EQ ayarlarını uygulama
  private applyEqSettings(): void {
    // Gerçek uygulamada, burada ses API'si ile etkileşim kurulabilir
    const { bass, treble } = this.settings.audio.equalizer;
    console.log(`EQ applied - Bass: ${bass}, Treble: ${treble}`);

    // Örnek olarak, bir AudioContext ile işlenebilir (gerçek implementasyon)
    if (typeof window !== 'undefined' && window.AudioContext) {
      // Audio context oluşturma ve EQ ayarlarını uygulama kodu
    }
  }

  // Bildirim sistemi
  private notifyListeners(): void {
    this.listeners.forEach((listener) => listener());
  }

  // Dinleyici ekleme
  public subscribe(listener: () => void): () => void {
    this.listeners.add(listener);
    return () => {
      this.listeners.delete(listener);
    };
  }

  // Tüm ayarları getir
  public getSettings(): AppSettings {
    return this.settings;
  }

  // Belirli bir ayarı getir
  public getSetting<K extends keyof AppSettings>(key: K): AppSettings[K] {
    return this.settings[key];
  }

  // Belirli bir ayarı güncelle
  public updateSetting<K extends keyof AppSettings>(
    key: K,
    value: AppSettings[K]
  ): void {
    this.settings[key] = value;
    this.saveSettings();
    this.applySettings();
  }

  // Tema ayarlarını güncelle
  public updateTheme(theme: Partial<AppSettings['theme']>): void {
    this.settings.theme = { ...this.settings.theme, ...theme };
    this.saveSettings();
    this.applyThemeSettings();
  }

  // Ses ayarlarını güncelle
  public updateAudio(audio: Partial<AppSettings['audio']>): void {
    this.settings.audio = { ...this.settings.audio, ...audio };

    // Eğer equalizer değişmişse, özel işleme
    if (audio.equalizer) {
      this.settings.audio.equalizer = {
        ...this.settings.audio.equalizer,
        ...audio.equalizer,
      };
      this.applyEqSettings();
    }

    this.saveSettings();
  }

  // Varsayılan ayarlara sıfırla
  public resetSettings(): void {
    this.settings = { ...defaultSettings };
    this.saveSettings();
    this.applySettings();
  }

  // Ses efektleri
  public playClickSound(): void {
    if (this.settings.interface.clickSounds) {
      this.playSound(CLICK_SOUND_URL, 0.5);
    }
  }

  public playHoverSound(): void {
    if (this.settings.interface.clickSounds) {
      this.playSound(HOVER_SOUND_URL, 0.2);
    }
  }

  public playSuccessSound(): void {
    if (this.settings.interface.clickSounds) {
      this.playSound(SUCCESS_SOUND_URL, 0.7);
    }
  }

  private playSound(url: string, volume: number = 0.5): void {
    try {
      // Gerçek uygulamada, ses dosyaları yüklenerek kullanılabilir
      console.log(`Playing sound: ${url} at volume ${volume}`);
      // const sound = new Audio(url);
      // sound.volume = volume;
      // sound.play();
    } catch (error) {
      console.error('Ses çalarken hata:', error);
    }
  }

  // HSL renk dönüşümü için yardımcı fonksiyon (ve dışa aktarımı)
  public shiftColor(hex: string, shiftDegree: number): string {
    // Hex'i RGB'ye dönüştür
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    if (!result) return hex;

    const r = parseInt(result[1], 16);
    const g = parseInt(result[2], 16);
    const b = parseInt(result[3], 16);

    // RGB'yi HSL'ye dönüştür
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    let h = 0,
      s,
      l = (max + min) / 2;

    if (max === min) {
      h = s = 0;
    } else {
      const d = max - min;
      s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
      switch (max) {
        case r:
          h = (g - b) / d + (g < b ? 6 : 0);
          break;
        case g:
          h = (b - r) / d + 2;
          break;
        case b:
          h = (r - g) / d + 4;
          break;
      }
      h /= 6;
    }

    // Renk tekerleğinde kaydırma
    h = (h * 360 + shiftDegree) % 360;
    if (h < 0) h += 360;
    h /= 360;

    // HSL'den RGB'ye geri dönüştür
    let r1, g1, b1;

    if (s === 0) {
      r1 = g1 = b1 = l;
    } else {
      const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
      const p = 2 * l - q;

      r1 = this.hue2rgb(p, q, h + 1 / 3);
      g1 = this.hue2rgb(p, q, h);
      b1 = this.hue2rgb(p, q, h - 1 / 3);
    }

    // RGB'yi Hex'e dönüştür
    return `#${Math.round(r1 * 255)
      .toString(16)
      .padStart(2, '0')}${Math.round(g1 * 255)
      .toString(16)
      .padStart(2, '0')}${Math.round(b1 * 255)
      .toString(16)
      .padStart(2, '0')}`;
  }

  public lightenColor(hex: string, percent: number): string {
    // Hex'i RGB'ye dönüştür
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    if (!result) return hex;

    const r = parseInt(result[1], 16);
    const g = parseInt(result[2], 16);
    const b = parseInt(result[3], 16);

    // RGB'yi HSL'ye dönüştür
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    let h = 0,
      s,
      l = (max + min) / 2;

    if (max === min) {
      h = s = 0;
    } else {
      const d = max - min;
      s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
      switch (max) {
        case r:
          h = (g - b) / d + (g < b ? 6 : 0);
          break;
        case g:
          h = (b - r) / d + 2;
          break;
        case b:
          h = (r - g) / d + 4;
          break;
      }
      h /= 6;
    }

    // Aydınlık değerini artır
    l = Math.min(1, l + percent / 100);

    // HSL'den RGB'ye geri dönüştür
    let r1, g1, b1;

    if (s === 0) {
      r1 = g1 = b1 = l;
    } else {
      const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
      const p = 2 * l - q;

      r1 = this.hue2rgb(p, q, h + 1 / 3);
      g1 = this.hue2rgb(p, q, h);
      b1 = this.hue2rgb(p, q, h - 1 / 3);
    }

    // RGB'yi Hex'e dönüştür
    return `#${Math.round(r1 * 255)
      .toString(16)
      .padStart(2, '0')}${Math.round(g1 * 255)
      .toString(16)
      .padStart(2, '0')}${Math.round(b1 * 255)
      .toString(16)
      .padStart(2, '0')}`;
  }

  private hue2rgb(p: number, q: number, t: number): number {
    if (t < 0) t += 1;
    if (t > 1) t -= 1;
    if (t < 1 / 6) return p + (q - p) * 6 * t;
    if (t < 1 / 2) return q;
    if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
    return p;
  }

  // Uygulama için kısayol dinleyicileri
  public setupKeyboardShortcuts(): () => void {
    const handleKeyDown = (e: KeyboardEvent) => {
      // Meta tuşu (Command veya Windows tuşu) veya Ctrl tuşuyla birlikte
      const isMetaOrCtrl = e.metaKey || e.ctrlKey;

      // Shift tuşu
      const isShift = e.shiftKey;

      // Alt tuşu (Option)
      const isAlt = e.altKey;

      // Odaklanılan element bir input veya textarea mı?
      const isInputActive =
        document.activeElement instanceof HTMLInputElement ||
        document.activeElement instanceof HTMLTextAreaElement;

      // Eğer bir input alanına odaklanılmışsa, kısayolları çalıştırma
      if (isInputActive) return;

      // Kısayolları kontrol et
      const { playPause, forward, backward, mute, fullscreen } =
        this.settings.shortcuts;

      // Her bir kısayol için kontrolleri yap
      switch (e.key) {
        case playPause:
          // Oynat/Duraklat
          console.log('Oynat/Duraklat kısayolu çalıştı');
          e.preventDefault();
          // Oynatma fonksiyonu burada çağrılacak
          break;

        case forward:
          // İleri Sar
          console.log('İleri Sar kısayolu çalıştı');
          e.preventDefault();
          // İleri sarma fonksiyonu burada çağrılacak
          break;

        case backward:
          // Geri Sar
          console.log('Geri Sar kısayolu çalıştı');
          e.preventDefault();
          // Geri sarma fonksiyonu burada çağrılacak
          break;

        case mute:
          // Sessize Al
          console.log('Sessize Al kısayolu çalıştı');
          e.preventDefault();
          // Sessize alma fonksiyonu burada çağrılacak
          break;

        case fullscreen:
          // Tam Ekran
          console.log('Tam Ekran kısayolu çalıştı');
          e.preventDefault();
          // Tam ekran fonksiyonu burada çağrılacak
          break;

        case 's':
          // Ctrl+S ile kaydetme
          if (isMetaOrCtrl) {
            console.log('Kaydet kısayolu çalıştı');
            e.preventDefault();
            // Kaydetme fonksiyonu burada çağrılacak
          }
          break;

        case 'n':
          // Ctrl+N ile yeni oluşturma
          if (isMetaOrCtrl) {
            console.log('Yeni oluştur kısayolu çalıştı');
            e.preventDefault();
            // Yeni oluşturma fonksiyonu burada çağrılacak
          }
          break;

        case ',':
          // Ctrl+, ile ayarlar
          if (isMetaOrCtrl) {
            console.log('Ayarlar kısayolu çalıştı');
            e.preventDefault();
            // Ayarlar açma fonksiyonu burada çağrılacak
          }
          break;
      }
    };

    // Event listener'ı ekle
    window.addEventListener('keydown', handleKeyDown);

    // Temizleme fonksiyonu döndür
    return () => {
      window.removeEventListener('keydown', handleKeyDown);
    };
  }

  // İşlevsel ayarlar eklentileri
  // Şarkı süresini ayarla
  public updateSongDuration(duration: '30s' | '60s' | '90s' | '120s'): void {
    this.settings.aiMusic.duration = duration;
    this.saveSettings();

    // Bir bildirim göster
    console.log(`Şarkı süresi ${duration} olarak ayarlandı`);
    this.playClickSound();
  }

  // BPM aralığını ayarla
  public updateBpmRange(min: number, max: number): void {
    if (min > max) {
      const temp = min;
      min = max;
      max = temp;
    }

    this.settings.aiMusic.bpmRange = { min, max };
    this.saveSettings();

    // Bir bildirim göster
    console.log(`BPM aralığı ${min}-${max} olarak ayarlandı`);
    this.playClickSound();
  }

  // Müzik anahtarını ayarla
  public updateMusicKey(key: string): void {
    this.settings.aiMusic.key = key;
    this.saveSettings();

    // Bir bildirim göster
    console.log(`Müzik anahtarı ${key} olarak ayarlandı`);
    this.playClickSound();
  }

  // Müzik formatını ayarla
  public updateMusicFormat(format: 'mp3' | 'wav' | 'flac'): void {
    this.settings.aiMusic.format = format;
    this.saveSettings();

    // Bir bildirim göster
    console.log(`Müzik formatı ${format} olarak ayarlandı`);
    this.playClickSound();
  }

  // EQ ayarlarını güncelle
  public updateEqualizer(bass: number, treble: number): void {
    this.settings.audio.equalizer = { bass, treble };
    this.saveSettings();
    this.applyEqSettings();

    // Bir bildirim göster
    console.log(`EQ ayarları güncellendi: Bas=${bass}, Tiz=${treble}`);
    this.playSuccessSound();
  }

  // Dalga formu görünürlüğünü ayarla
  public toggleWaveform(show: boolean): void {
    this.settings.audio.showWaveform = show;
    this.saveSettings();

    // Bir bildirim göster
    console.log(`Dalga formu ${show ? 'gösteriliyor' : 'gizleniyor'}`);
    this.playClickSound();
  }

  // Otomatik oynatmayı ayarla
  public toggleAutoplay(autoplay: boolean): void {
    this.settings.audio.autoplay = autoplay;
    this.saveSettings();

    // Bir bildirim göster
    console.log(`Otomatik oynatma ${autoplay ? 'açıldı' : 'kapatıldı'}`);
    this.playClickSound();
  }

  // Geçiş efektini ayarla
  public setTransitionEffect(effect: 'smooth' | 'instant'): void {
    this.settings.audio.transitionEffect = effect;
    this.saveSettings();

    // Bir bildirim göster
    console.log(`Geçiş efekti ${effect} olarak ayarlandı`);
    this.playClickSound();
  }

  // Klavye kısayolunu ayarla
  public setShortcut(
    action: keyof AppSettings['shortcuts'],
    key: string
  ): void {
    this.settings.shortcuts[action] = key;
    this.saveSettings();

    // Bir bildirim göster
    console.log(`'${action}' kısayolu '${key}' olarak ayarlandı`);
    this.playClickSound();
  }

  // Dili değiştir
  public changeLanguage(language: 'tr' | 'en' | 'es'): void {
    this.settings.language = language;
    this.saveSettings();

    // Bir bildirim göster
    console.log(`Dil ${language} olarak değiştirildi`);
    this.playSuccessSound();

    // Dil değişimi için sayfa yenileme gerekebilir
    // Gerçek uygulamada bunu i18n kütüphanesi ile yapabiliriz
    if (typeof window !== 'undefined' && window.localStorage) {
      window.localStorage.setItem('language', language);
      // Dili değiştirdikten sonra sayfayı yenilemeyi kullanıcıya sor
      if (
        confirm(
          'Dil değişikliğinin uygulanması için sayfa yenilenmelidir. Devam etmek istiyor musunuz?'
        )
      ) {
        window.location.reload();
      }
    }
  }

  // Müzik kalitesini ayarla
  public setMusicQuality(quality: 'low' | 'medium' | 'high'): void {
    this.settings.storage.musicQuality = quality;
    this.saveSettings();

    // Bir bildirim göster
    console.log(`Müzik kalitesi ${quality} olarak ayarlandı`);
    this.playClickSound();
  }

  // Oturum süresini ayarla
  public setSessionTime(minutes: number): void {
    this.settings.account.sessionTime = minutes;
    this.saveSettings();

    // Bir bildirim göster
    console.log(
      `Oturum süresi ${
        minutes === 0 ? 'sınırsız' : minutes + ' dakika'
      } olarak ayarlandı`
    );
    this.playClickSound();

    // Gerçek uygulamada oturum zamanlayıcısını güncelle
    if (typeof window !== 'undefined') {
      // Oturum zamanlayıcısı kodu burada olacak
    }
  }

  // Varsayılan enstrümanı ayarla
  public setDefaultInstrument(instrument: string): void {
    this.settings.instrument.defaultInstrument = instrument;
    this.saveSettings();

    // Bir bildirim göster
    console.log(`Varsayılan enstrüman ${instrument} olarak ayarlandı`);
    this.playClickSound();
  }

  // Tıklama seslerini aç/kapat
  public toggleClickSounds(enabled: boolean): void {
    this.settings.interface.clickSounds = enabled;
    this.saveSettings();

    // Bir bildirim göster (ses olmadan)
    console.log(`Tıklama sesleri ${enabled ? 'açıldı' : 'kapatıldı'}`);

    // Sesler açıksa, bir ses çal
    if (enabled) {
      this.playSuccessSound();
    }
  }

  // Sidebar'ın otomatik genişlemesini aç/kapat
  public toggleAutoExpandSidebar(enabled: boolean): void {
    this.settings.interface.autoExpandSidebar = enabled;
    this.saveSettings();

    // Bir bildirim göster
    console.log(
      `Sidebar otomatik genişleme ${enabled ? 'açıldı' : 'kapatıldı'}`
    );
    this.playClickSound();
  }

  // Önbelleği temizle
  public clearCache(): void {
    if (typeof window !== 'undefined' && window.caches) {
      try {
        // Service worker önbelleğini temizle
        window.caches.keys().then((cacheNames) => {
          cacheNames.forEach((cacheName) => {
            window.caches.delete(cacheName);
          });
        });

        // İndexedDB'yi temizle
        if (window.indexedDB) {
          window.indexedDB.databases().then((databases) => {
            databases.forEach((database) => {
              if (database.name) {
                window.indexedDB.deleteDatabase(database.name);
              }
            });
          });
        }

        console.log('Önbellek başarıyla temizlendi');
        this.playSuccessSound();
      } catch (error) {
        console.error('Önbellek temizlenirken hata oluştu:', error);
      }
    } else {
      // Tarayıcı desteklemiyorsa, sadece localStorage'ı temizle
      // Kritik ayarlar hariç
      if (typeof window !== 'undefined' && window.localStorage) {
        const savedSettings = window.localStorage.getItem(SETTINGS_STORAGE_KEY);
        const welcomeModalShown =
          window.localStorage.getItem('welcomeModalShown');
        const apiKey = window.localStorage.getItem('suno_api_key');

        window.localStorage.clear();

        // Kritik ayarları geri yükle
        if (savedSettings) {
          window.localStorage.setItem(SETTINGS_STORAGE_KEY, savedSettings);
        }
        if (welcomeModalShown) {
          window.localStorage.setItem('welcomeModalShown', welcomeModalShown);
        }
        if (apiKey) {
          window.localStorage.setItem('suno_api_key', apiKey);
        }

        console.log('Veri deposu başarıyla temizlendi');
        this.playSuccessSound();
      }
    }
  }

  // Geçmişi temizle
  public clearHistory(): void {
    // Uygulama içi müzik geçmişini temizle
    // Gerçek uygulamada, bu bir veri deposunda saklanacaktır
    console.log('Geçmiş başarıyla temizlendi');
    this.playSuccessSound();

    // Bir API çağrısı veya localStorage işlemi burada yapılabilir
  }
}

export const settingsService = new SettingsService();
export default settingsService;

// Yardımcı fonksiyonları dışa aktar
export function shiftColor(hex: string, shiftDegree: number): string {
  return settingsService.shiftColor(hex, shiftDegree);
}

export function lightenColor(hex: string, percent: number): string {
  return settingsService.lightenColor(hex, percent);
}
