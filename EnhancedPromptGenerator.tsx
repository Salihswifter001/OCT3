// src/components/EnhancedPromptGenerator.tsx - Optimized version
import React, { useState, useEffect, useRef } from 'react';
import './EnhancedPromptGenerator.css';
import { MusicGenre } from '../types';
// Kullanılmayan settingsService import'unu kaldırdık

interface EnhancedPromptGeneratorProps {
  onBack?: () => void;
}

interface AudioAnalysisResult {
  bpm: number;
  key: string;
  scale: 'major' | 'minor';
  genre: string[];
  audioLength: number;
}

const EnhancedPromptGenerator: React.FC<EnhancedPromptGeneratorProps> = () => {
  // onBack prop'u kullanılmıyordu, constructor parametrelerinden kaldırdık
  
  // Core prompt states
  const [prompt, setPrompt] = useState<string>('');
  const [searchTerm, setSearchTerm] = useState<string>('');
  const [isGenerating, setIsGenerating] = useState<boolean>(false);
  const [selectedGenres, setSelectedGenres] = useState<string[]>([]);
  
  // New states
  const [includeVocals, setIncludeVocals] = useState<boolean>(false);
  const [lyrics, setLyrics] = useState<string>('');
  const [showLyricsPanel, setShowLyricsPanel] = useState<boolean>(false);
  const [generationResult, setGenerationResult] = useState<{ url: string, title: string } | null>(null);
  const [audioAnalysis, setAudioAnalysis] = useState<AudioAnalysisResult | null>(null);
  const [generationProgress, setGenerationProgress] = useState<number>(0);
  const [generationStatus, setGenerationStatus] = useState<string>('');
  
  // UI refs
  const promptInputRef = useRef<HTMLTextAreaElement>(null);
  const audioPlayerRef = useRef<HTMLAudioElement>(null);
  
  // Music genres data
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

  // Process prompt input
  const handlePromptChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    if (e.target.value.length <= 150) {
      setPrompt(e.target.value);
    }
  };

  // Handle genre selection
  const handleGenreSelect = (genre: string) => {
    if (selectedGenres.includes(genre)) {
      // Remove already selected genre
      setSelectedGenres(prev => prev.filter(g => g !== genre));
      
      // Remove from prompt
      setPrompt(prev => {
        const regex = new RegExp(`\\b${genre}\\b`, 'g');
        return prev.replace(regex, '').replace(/\s+/g, ' ').trim();
      });
    } else {
      // Add new genre, check character limit
      const newPrompt = prompt ? `${prompt} ${genre}` : genre;
      if (newPrompt.length <= 150) {
        setSelectedGenres(prev => [...prev, genre]);
        setPrompt(newPrompt);
      }
    }
  };

  // Toggle vocals option
  const handleVocalToggle = () => {
    setIncludeVocals(!includeVocals);
    if (!includeVocals) {
      setShowLyricsPanel(true);
    } else {
      setShowLyricsPanel(false);
      setLyrics('');
    }
  };
  
  // Lyrics change handler
  const handleLyricsChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    setLyrics(e.target.value);
  };
  
  // Generate lyrics button handler
  const handleGenerateLyrics = async () => {
    if (!prompt.trim()) {
      alert('Lütfen önce bir prompt girin');
      return;
    }
    
    try {
      setIsGenerating(true);
      setGenerationStatus('Şarkı sözleri oluşturuluyor...');
      
      // Simulate API call - in a real app, this would call the actual API
      setTimeout(() => {
        // For demo purposes, generate some sample lyrics based on the prompt
        const generatedLyrics = generateDemoLyrics(prompt);
        setLyrics(generatedLyrics);
        setIsGenerating(false);
        setGenerationStatus('Şarkı sözleri hazır');
      }, 1500);
      
    } catch (error) {
      console.error('Şarkı sözü oluşturma hatası:', error);
      setIsGenerating(false);
      setGenerationStatus('Şarkı sözü oluşturma başarısız oldu');
    }
  };

  // Generate music function
  const handleGenerateMusic = async () => {
    if (!prompt.trim()) {
      alert('Lütfen bir prompt girin');
      return;
    }
    
    setIsGenerating(true);
    setGenerationStatus('Müzik oluşturuluyor...');
    setGenerationProgress(0);
    setGenerationResult(null);
    setAudioAnalysis(null);
    
    try {
      // In a real app, we would call the actual API here
      // For demo purposes, let's simulate the generation process
      simulateMusicGeneration();
    } catch (error) {
      console.error('Müzik oluşturma hatası:', error);
      setIsGenerating(false);
      setGenerationStatus('Müzik oluşturma başarısız oldu');
    }
  };
  
  // Simulate music generation process
  const simulateMusicGeneration = () => {
    const totalSteps = 10;
    let currentStep = 0;
    
    const interval = setInterval(() => {
      currentStep++;
      const progress = Math.round((currentStep / totalSteps) * 100);
      setGenerationProgress(progress);
      
      if (currentStep === 3) {
        setGenerationStatus('Müzikal parametreler hazırlanıyor...');
      } else if (currentStep === 5) {
        setGenerationStatus('Melodi oluşturuluyor...');
      } else if (currentStep === 7) {
        setGenerationStatus('Enstrümanlar ekleniyor...');
      } else if (currentStep === 9) {
        setGenerationStatus('Ses dosyası hazırlanıyor...');
      }
      
      if (currentStep >= totalSteps) {
        clearInterval(interval);
        
        // Simulation completed
        setIsGenerating(false);
        setGenerationStatus('Müzik başarıyla oluşturuldu!');
        
        // Set sample result
        setGenerationResult({
          url: 'https://samplelib.com/lib/preview/mp3/sample-6s.mp3', // Sample audio URL
          title: generateMusicTitle(prompt)
        });
        
        // Generate sample analysis
        setAudioAnalysis({
          bpm: Math.floor(Math.random() * 60) + 90, // Random BPM between 90-150
          key: ['C', 'D', 'E', 'F', 'G', 'A', 'B'][Math.floor(Math.random() * 7)],
          scale: Math.random() > 0.5 ? 'major' : 'minor',
          genre: selectedGenres.length > 0 ? selectedGenres : ['Electronic'],
          audioLength: Math.floor(Math.random() * 120) + 60 // Random length between 60-180 seconds
        });
      }
    }, 500);
  };
  
  // Helper function to generate a music title based on the prompt
  const generateMusicTitle = (promptText: string): string => {
    const words = promptText.split(' ').filter(word => word.length > 3);
    
    if (words.length === 0) {
      return 'Untitled Composition';
    }
    
    if (words.length === 1) {
      return words[0].charAt(0).toUpperCase() + words[0].slice(1);
    }
    
    // Pick 2 random words from the prompt
    const word1 = words[Math.floor(Math.random() * words.length)];
    let word2 = words[Math.floor(Math.random() * words.length)];
    
    // Make sure word2 is different from word1
    while (words.length > 1 && word2 === word1) {
      word2 = words[Math.floor(Math.random() * words.length)];
    }
    
    // Capitalize first letters
    const title1 = word1.charAt(0).toUpperCase() + word1.slice(1);
    const title2 = word2.charAt(0).toUpperCase() + word2.slice(1);
    
    return `${title1} ${title2}`;
  };
  
  // Helper function to generate demo lyrics based on the prompt
  const generateDemoLyrics = (promptText: string): string => {
    const themes = promptText.split(' ').filter(word => word.length > 3);
    
    if (themes.length === 0) {
      return 'Enter a prompt to generate lyrics';
    }
    
    // Template lines
    const verses = [
      `In the world of ${themes[0] || 'dreams'},\nWhere ${themes[1] || 'shadows'} come alive.\nI find myself lost in ${themes[2] || 'time'},\nTrying to ${themes[0] || 'survive'}.`,
      `The ${themes[1] || 'night'} is calling,\nEchoes of ${themes[0] || 'distant'} sounds.\nWe're ${themes[2] || 'falling'} through space,\nNo gravity ${themes[0] || 'bounds'}.`,
      `${themes[0] || 'Lights'} flashing in the dark,\n${themes[1] || 'Memories'} fading away.\nThe ${themes[2] || 'city'} never sleeps,\nAs we continue to ${themes[0] || 'play'}.`
    ];
    
    const chorus = `\n\nChorus:\n${themes[0] || 'Time'} after ${themes[1] || 'time'},\nWe chase the ${themes[2] || 'light'}.\n${themes[0] || 'Dreams'} becoming real,\nThrough the ${themes[1] || 'night'}.`;
    
    // Combine verses and chorus
    return verses.join('\n\n') + chorus;
  };

  // Filtered music genres
  const filteredGenres = genres.filter(genre => 
    genre.name.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // Categorize music genres
  const hotGenres = filteredGenres.filter(genre => genre.isHot);
  const otherGenres = filteredGenres.filter(genre => !genre.isHot);

  // Focus effect
  useEffect(() => {
    if (promptInputRef.current) {
      promptInputRef.current.focus();
    }
  }, []);

  // Tips list for the prompt placeholder
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
    <div className="prompt-generator">
      <div className="prompt-container">
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
        
        <div className="vocals-toggle-section">
          <div className="vocals-toggle-label">Vokal İçersin Mi?</div>
          <label className="vocals-toggle">
            <input 
              type="checkbox" 
              checked={includeVocals} 
              onChange={handleVocalToggle}
            />
            <span className="vocals-toggle-slider"></span>
            <span className="vocals-toggle-text">{includeVocals ? 'Evet' : 'Hayır'}</span>
          </label>
        </div>
        
        {showLyricsPanel && (
          <div className="lyrics-panel">
            <div className="lyrics-header">
              <h3>Şarkı Sözleri</h3>
              <button 
                className="generate-lyrics-button"
                onClick={handleGenerateLyrics}
                disabled={isGenerating || !prompt.trim()}
              >
                <span className="lyrics-icon">🎤</span>
                <span>Sözleri Oluştur</span>
              </button>
            </div>
            <textarea
              value={lyrics}
              onChange={handleLyricsChange}
              placeholder="Şarkı sözlerinizi yazın veya AI'ın oluşturmasını bekleyin..."
              className="lyrics-input"
            />
          </div>
        )}

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
              <span>{generationStatus}</span>
            </>
          ) : (
            <>
              <span className="icon">▶</span>
              <span>Müzik Oluştur</span>
            </>
          )}
        </button>
        
        {isGenerating && (
          <div className="progress-bar-container">
            <div 
              className="progress-bar" 
              style={{ width: `${generationProgress}%` }}
            ></div>
            <div className="progress-text">{Math.round(generationProgress)}%</div>
          </div>
        )}
        
        {generationResult && (
          <div className="generation-result">
            <div className="result-header">
              <h3>{generationResult.title}</h3>
            </div>
            
            <audio 
              ref={audioPlayerRef}
              controls 
              src={generationResult.url}
              className="audio-player"
            />
            
            {audioAnalysis && (
              <div className="audio-analysis">
                <h4>Müzik Analizi</h4>
                <div className="analysis-grid">
                  <div className="analysis-item">
                    <div className="analysis-label">BPM</div>
                    <div className="analysis-value">{audioAnalysis.bpm}</div>
                  </div>
                  <div className="analysis-item">
                    <div className="analysis-label">Anahtar</div>
                    <div className="analysis-value">{audioAnalysis.key} {audioAnalysis.scale === 'major' ? 'Majör' : 'Minör'}</div>
                  </div>
                  <div className="analysis-item">
                    <div className="analysis-label">Süre</div>
                    <div className="analysis-value">{Math.floor(audioAnalysis.audioLength / 60)}:{(audioAnalysis.audioLength % 60).toString().padStart(2, '0')}</div>
                  </div>
                  <div className="analysis-item">
                    <div className="analysis-label">Türler</div>
                    <div className="analysis-value">{audioAnalysis.genre.join(', ')}</div>
                  </div>
                </div>
              </div>
            )}
            
            <div className="result-actions">
              <button className="result-action-button download">
                <span className="action-icon">💾</span>
                <span>İndir</span>
              </button>
              <button className="result-action-button share">
                <span className="action-icon">🔗</span>
                <span>Paylaş</span>
              </button>
              <button className="result-action-button save">
                <span className="action-icon">📁</span>
                <span>Kütüphaneye Kaydet</span>
              </button>
            </div>
          </div>
        )}
      </div>
      
      <div className="info-container">
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
      </div>
    </div>
  );
};

export default EnhancedPromptGenerator;