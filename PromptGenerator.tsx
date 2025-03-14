// src/components/PromptGenerator.tsx
import React, { useState, useEffect, useRef } from 'react';
import './PromptGenerator.css';
import { MusicGenre } from '../types';

interface PromptGeneratorProps {
  onBack?: () => void;
}

const PromptGenerator: React.FC<PromptGeneratorProps> = ({ onBack }) => {
  const [prompt, setPrompt] = useState<string>('');
  const [searchTerm, setSearchTerm] = useState<string>('');
  const [isGenerating, setIsGenerating] = useState<boolean>(false);
  const [selectedGenres, setSelectedGenres] = useState<string[]>([]);
  const promptInputRef = useRef<HTMLTextAreaElement>(null);
  const audioVisualizerRef = useRef<HTMLCanvasElement>(null);
  
  // Müzik türleri verileri - gerçek uygulamada API'den gelecek
  const [genres] = useState<MusicGenre[]>([
    { id: 1, name: 'Synthwave', isHot: true },
    { id: 2, name: 'Cyberpunk', isHot: true },
    { id: 3, name: 'Vaporwave', isHot: true },
    { id: 4, name: 'Techno', isHot: false },
    { id: 5, name: 'House', isHot: false },
    { id: 6, name: 'Ambient', isHot: false },
    { id: 7, name: 'Lo-fi', isHot: false },
    { id: 8, name: 'Drum & Bass', isHot: true },
    { id: 9, name: 'Trap', isHot: false },
    { id: 10, name: 'Hip Hop', isHot: false },
    { id: 11, name: 'Rock', isHot: false },
    { id: 12, name: 'Metal', isHot: false },
    { id: 13, name: 'Jazz', isHot: false },
    { id: 14, name: 'Classical', isHot: false },
    { id: 15, name: 'Blues', isHot: false },
    { id: 16, name: 'Folk', isHot: false },
    { id: 17, name: 'R&B', isHot: false },
    { id: 18, name: 'Pop', isHot: false },
    { id: 19, name: 'Funk', isHot: true },
    { id: 20, name: 'Reggae', isHot: false },
  ]);

  // Prompt input işleme
  const handlePromptChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    if (e.target.value.length <= 150) {
      setPrompt(e.target.value);
    }
  };

  // Tür seçim işleme
  const handleGenreSelect = (genre: string) => {
    if (selectedGenres.includes(genre)) {
      // Zaten seçili olan türü kaldır
      setSelectedGenres(prev => prev.filter(g => g !== genre));
      
      // Prompttan da kaldır
      setPrompt(prev => {
        const regex = new RegExp(`\\b${genre}\\b`, 'g');
        return prev.replace(regex, '').replace(/\s+/g, ' ').trim();
      });
    } else {
      // Yeni tür ekle, karakter limitini kontrol et
      const newPrompt = prompt ? `${prompt} ${genre}` : genre;
      if (newPrompt.length <= 150) {
        setSelectedGenres(prev => [...prev, genre]);
        setPrompt(newPrompt);
      }
    }
  };

  // Müzik oluşturma simülasyonu
  const handleGenerateMusic = () => {
    if (!prompt.trim()) return;
    
    setIsGenerating(true);
    
    // Üretim simülasyonu - gerçek uygulamada API çağrısı yapılacak
    setTimeout(() => {
      setIsGenerating(false);
      // Başarı mesajı veya yönlendirme yapılabilir
      alert('Müzik oluşturuldu!');
      if (onBack) onBack();
    }, 3000);
  };

  // Filtrelenmiş müzik türleri
  const filteredGenres = genres.filter(genre => 
    genre.name.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // Müzik türlerini kategorilere ayırma
  const hotGenres = filteredGenres.filter(genre => genre.isHot);
  const otherGenres = filteredGenres.filter(genre => !genre.isHot);

  // Ses görselleştirici animasyonu
  useEffect(() => {
    const canvas = audioVisualizerRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const resizeCanvas = () => {
      canvas.width = canvas.offsetWidth;
      canvas.height = canvas.offsetHeight;
    };

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    let animationId: number;
    const barCount = 60;
    const barWidth = canvas.width / barCount;
    const maxBarHeight = canvas.height * 0.8;
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
    const secondaryColor = getComputedStyle(document.documentElement).getPropertyValue('--secondary-color').trim();

    const animate = () => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      
      const barHeights = Array.from({ length: barCount }, () => {
        const height = isGenerating 
          ? Math.random() * maxBarHeight 
          : Math.sin((Date.now() / 1000) * Math.random()) * maxBarHeight * 0.3 + maxBarHeight * 0.1;
        return height;
      });

      barHeights.forEach((height, index) => {
        const x = index * barWidth;
        const gradientHeight = height * 1.2;
        
        const gradient = ctx.createLinearGradient(0, canvas.height - gradientHeight, 0, canvas.height);
        gradient.addColorStop(0, primaryColor);
        gradient.addColorStop(1, secondaryColor);
        
        ctx.fillStyle = gradient;
        ctx.fillRect(x, canvas.height - height, barWidth - 1, height);
      });

      animationId = requestAnimationFrame(animate);
    };

    animate();

    return () => {
      window.removeEventListener('resize', resizeCanvas);
      cancelAnimationFrame(animationId);
    };
  }, [isGenerating]);

  // Odak efekti
  useEffect(() => {
    if (promptInputRef.current) {
      promptInputRef.current.focus();
    }
  }, []);

  // İpuçları listesi
  const promptTips = [
    'Elektronik davul ritimleri ve arpejli sentezleyicilerle yağmurlu bir gece',
    'Uzay temalı reverb efektleri ile minimal ambient',
    'Retro 80\'ler tarzı, bas gitar ve parlak sentezleyiciler',
    'Distorsiyonlu gitarlar ve dijital gürültülerle siber punk',
    'Derinlik hissi veren pad\'lerle düşük tempolu lo-fi'
  ];
  
  const [currentTipIndex, setCurrentTipIndex] = useState(0);
  
  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentTipIndex(prev => (prev + 1) % promptTips.length);
    }, 5000);
    
    return () => clearInterval(interval);
  }, [promptTips.length]);

  return (
    <div className="prompt-generator-page">
      <div className="cyber-overlay"></div>
      
      {/* Dalga animasyonu arka planı */}
      <div className="wave-container">
        <div className="wave wave1"></div>
        <div className="wave wave2"></div>
      </div>
      
      <div className="page-header">
        {onBack && (
          <button className="back-button glitch-hover" onClick={onBack}>
            <span className="back-icon">◀</span> 
            <span className="back-text">Geri</span>
          </button>
        )}
        <h1 className="page-title glitch" data-text="Yeni Müzik Oluştur">Yeni Müzik Oluştur</h1>
      </div>
      
      <div className="content-grid">
        <div className="prompt-container cyber-card">
          <div className="card-header">
            <div className="card-title">AI İçin Müzik Promptu</div>
            <div className="character-count">
              <span className="current-count">{prompt.length}</span>/150
            </div>
          </div>
          
          <div className="prompt-input-wrapper">
            <textarea
              ref={promptInputRef}
              value={prompt}
              onChange={handlePromptChange}
              placeholder="Müzik tarzını ve duygusunu tanımlayın..."
              maxLength={150}
              className="prompt-input"
            />
            
            <div className="prompt-tips">
              <div className="tip-icon">💡</div>
              <div className="tip-content">
                <span>Örnek:</span> {promptTips[currentTipIndex]}
              </div>
            </div>
          </div>
          
          <canvas ref={audioVisualizerRef} className="audio-visualizer"></canvas>

          <div className="genre-selection-section">
            <div className="section-header">
              <h3 className="section-title">Müzik Türleri</h3>
              <div className="genre-search">
                <input 
                  type="text" 
                  placeholder="Tür ara..." 
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="search-input"
                />
              </div>
            </div>
            
            {hotGenres.length > 0 && (
              <div className="genre-category">
                <div className="category-label">Popüler Türler <span className="fire-icon">🔥</span></div>
                <div className="genre-grid">
                  {hotGenres.map(genre => (
                    <div 
                      key={genre.id} 
                      className={`genre-chip ${selectedGenres.includes(genre.name) ? 'selected' : ''}`}
                      onClick={() => handleGenreSelect(genre.name)}
                    >
                      <span>{genre.name}</span>
                      {genre.isHot && <span className="fire-icon">🔥</span>}
                    </div>
                  ))}
                </div>
              </div>
            )}
            
            {otherGenres.length > 0 && (
              <div className="genre-category">
                <div className="category-label">Diğer Türler</div>
                <div className="genre-grid">
                  {otherGenres.map(genre => (
                    <div 
                      key={genre.id} 
                      className={`genre-chip ${selectedGenres.includes(genre.name) ? 'selected' : ''}`}
                      onClick={() => handleGenreSelect(genre.name)}
                    >
                      <span>{genre.name}</span>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>
          
          <button 
            className={`generate-button ${isGenerating ? 'generating' : ''}`} 
            onClick={handleGenerateMusic}
            disabled={isGenerating || !prompt.trim()}
          >
            {isGenerating ? (
              <>
                <span className="loading-icon"></span>
                <span>Müzik Üretiliyor...</span>
              </>
            ) : (
              <>
                <span className="icon">▶</span>
                <span>Müzik Oluştur</span>
              </>
            )}
          </button>
        </div>
        
        <div className="info-container cyber-card">
          <div className="card-header">
            <div className="card-title">Oluşturma İpuçları</div>
          </div>
          
          <div className="info-content">
            <div className="info-item">
              <div className="info-icon">🎵</div>
              <div className="info-text">
                <strong>Tarz belirtin:</strong> Synthwave, Cyberpunk gibi belirli müzik türleri ekleyin.
              </div>
            </div>
            
            <div className="info-item">
              <div className="info-icon">🎹</div>
              <div className="info-text">
                <strong>Enstrüman belirtin:</strong> Sentezleyici, davullar, bas gitar gibi enstrümanları tanımlayın.
              </div>
            </div>
            
            <div className="info-item">
              <div className="info-icon">🌙</div>
              <div className="info-text">
                <strong>Atmosfer ekleyin:</strong> "Yağmurlu", "gece", "neon", "retro" gibi atmosferi belirleyen kelimeler kullanın.
              </div>
            </div>
            
            <div className="info-item">
              <div className="info-icon">🔊</div>
              <div className="info-text">
                <strong>Efekt belirtin:</strong> Reverb, delay, distorsiyon gibi ses efektlerini belirtin.
              </div>
            </div>
            
            <div className="info-item">
              <div className="info-icon">♻️</div>
              <div className="info-text">
                <strong>İstediğiniz sonucu alamazsanız:</strong> Promptunuzu değiştirin ve tekrar deneyin.
              </div>
            </div>
          </div>
          
          <div className="ai-credits">
            <div className="ai-brain-icon"></div>
            <div className="credits-text">
              <p>Octaverum AI tarafından desteklenmektedir</p>
              <p className="credit-detail">Yapay zeka ile oluşturulan benzersiz müzik</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PromptGenerator;