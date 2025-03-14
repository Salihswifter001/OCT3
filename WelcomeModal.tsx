// src/components/WelcomeModal.tsx
import React, { useState } from 'react';
import './WelcomeModal.css';

interface WelcomeModalProps {
  onClose: () => void;
}

const WelcomeModal: React.FC<WelcomeModalProps> = ({ onClose }) => {
  const [currentStep, setCurrentStep] = useState<number>(1);
  const totalSteps = 3;

  const nextStep = () => {
    if (currentStep < totalSteps) {
      setCurrentStep(prev => prev + 1);
    } else {
      // Son adımda ise modalı kapat
      handleClose();
    }
  };

  const prevStep = () => {
    if (currentStep > 1) {
      setCurrentStep(prev => prev - 1);
    }
  };

  const handleClose = () => {
    // Kullanıcı modalı kapattığında, localStorage'a kaydedelim
    localStorage.setItem('welcomeModalShown', 'true');
    onClose();
  };

  return (
    <div className="welcome-modal-overlay">
      <div className="welcome-modal">
        <button className="welcome-close-button" onClick={handleClose}>×</button>
        
        <div className="welcome-header">
          <div className="welcome-logo">
            <div className="welcome-logo-glow"></div>
            <h1>Octaverum AI</h1>
          </div>
          <h2>Yapay Zeka Müzik Asistanınıza Hoş Geldiniz</h2>
        </div>

        <div className="welcome-progress">
          {Array.from({ length: totalSteps }).map((_, index) => (
            <div 
              key={index} 
              className={`progress-dot ${currentStep >= index + 1 ? 'active' : ''}`}
              onClick={() => setCurrentStep(index + 1)}
            ></div>
          ))}
        </div>
        
        <div className="welcome-content">
          {currentStep === 1 && (
            <div className="welcome-step">
              <div className="step-icon">🎵</div>
              <h3>Müzik Üretim Gücü</h3>
              <p>
                Octaverum AI, yapay zeka teknolojisini kullanarak saniyeler içinde profesyonel kalitede 
                müzik üretmenizi sağlar. Synthwave'den Vaporwave'e, Cyberpunk'tan Ambient'e kadar 
                geniş bir müzik yelpazesinde içerik oluşturabilirsiniz.
              </p>
              <div className="feature-list">
                <div className="feature-item">
                  <span className="feature-icon">🤖</span>
                  <span>AI Tabanlı Müzik Üretimi</span>
                </div>
                <div className="feature-item">
                  <span className="feature-icon">🎛️</span>
                  <span>Tam Kontrolünüzde</span>
                </div>
                <div className="feature-item">
                  <span className="feature-icon">⚡</span>
                  <span>Saniyeler İçinde Sonuç</span>
                </div>
              </div>
            </div>
          )}
          
          {currentStep === 2 && (
            <div className="welcome-step">
              <div className="step-icon">🎹</div>
              <h3>Nasıl Kullanılır?</h3>
              <p>
                Octaverum AI ile müzik üretmek son derece kolay. İşte başlamanıza yardımcı olacak 
                birkaç temel adım:
              </p>
              <div className="tutorial-steps">
                <div className="tutorial-step">
                  <div className="tutorial-number">1</div>
                  <div className="tutorial-text">
                    <strong>Prompt Oluşturun:</strong> 
                    "Yeni Oluştur" butonuna tıklayın ve müziğinizi tanımlayın
                  </div>
                </div>
                <div className="tutorial-step">
                  <div className="tutorial-number">2</div>
                  <div className="tutorial-text">
                    <strong>Türleri Seçin:</strong> 
                    İstediğiniz müzik türlerini seçerek AI'ı yönlendirin
                  </div>
                </div>
                <div className="tutorial-step">
                  <div className="tutorial-number">3</div>
                  <div className="tutorial-text">
                    <strong>Parametreleri Ayarlayın:</strong> 
                    Ayarlar menüsünden ses kalitesi, BPM ve müzik tonu gibi ayarları özelleştirin
                  </div>
                </div>
                <div className="tutorial-step">
                  <div className="tutorial-number">4</div>
                  <div className="tutorial-text">
                    <strong>Müziğinizi Üretin:</strong> 
                    "Müzik Oluştur" butonuna basın ve yapay zekanın çalışmasını izleyin
                  </div>
                </div>
              </div>
            </div>
          )}
          
          {currentStep === 3 && (
            <div className="welcome-step">
              <div className="step-icon">🚀</div>
              <h3>Keşfetmeye Hazır Mısınız?</h3>
              <p>
                Octaverum AI ile yaratıcı müzik yolculuğunuza başlamaya hazırsınız! Müzik kütüphanenizi 
                oluşturun, çalma listeleri hazırlayın ve tamamen size özel müzik parçaları üretin.
              </p>
              <div className="final-features">
                <div className="final-feature">
                  <div className="final-feature-icon">💾</div>
                  <div className="final-feature-text">
                    <h4>Kütüphane</h4>
                    <p>Ürettiğiniz tüm müzikleri kütüphanenizde saklayın ve düzenleyin</p>
                  </div>
                </div>
                <div className="final-feature">
                  <div className="final-feature-icon">🎧</div>
                  <div className="final-feature-text">
                    <h4>Çalma Listeleri</h4>
                    <p>Kişiselleştirilmiş çalma listeleri oluşturun ve paylaşın</p>
                  </div>
                </div>
                <div className="final-feature">
                  <div className="final-feature-icon">⚙️</div>
                  <div className="final-feature-text">
                    <h4>Özelleştirme</h4>
                    <p>Ayarlar menüsünden tüm özellikleri kişiselleştirin</p>
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
        
        <div className="welcome-footer">
          {currentStep > 1 && (
            <button className="welcome-button prev-button" onClick={prevStep}>
              Geri
            </button>
          )}
          <button className="welcome-button next-button" onClick={nextStep}>
            {currentStep < totalSteps ? 'Devam' : 'Başlayalım'}
          </button>
        </div>
      </div>
    </div>
  );
};

export default WelcomeModal;