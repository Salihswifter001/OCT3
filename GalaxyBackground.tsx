// src/components/GalaxyBackground.tsx
import React, { useEffect, useRef } from 'react';
import './GalaxyBackground.css';

const GalaxyBackground: React.FC = () => {
  const canvasRef = useRef<HTMLCanvasElement>(null);

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Canvas boyutlarını pencere boyutlarına ayarla
    const resizeCanvas = () => {
      if (!canvas) return;
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    };

    // İlk başta ve pencere boyutu değiştikçe canvas'ı yeniden boyutlandır
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    // Parçacık sınıfı
    class Particle {
      x: number;
      y: number;
      size: number;
      speedX: number;
      speedY: number;
      color: string;
      brightness: number;

      constructor() {
        if (!canvas) {
          this.x = 0;
          this.y = 0;
        } else {
          this.x = Math.random() * canvas.width;
          this.y = Math.random() * canvas.height;
        }
        this.size = Math.random() * 2 + 0.1;
        this.speedX = Math.random() * 0.5 - 0.25;
        this.speedY = Math.random() * 0.5 - 0.25;
        
        // Cyberpunk temasına uygun renkler (cyan, mor, pembe)
        const colors = ['#00ffff', '#ff00ff', '#ff0099', '#6600ff', '#00ff99'];
        this.color = colors[Math.floor(Math.random() * colors.length)];
        
        // Parlaklık değeri (titreşim efekti için)
        this.brightness = 0.8 + Math.random() * 0.2;
      }

      update() {
        this.x += this.speedX;
        this.y += this.speedY;

        // Parçacık canvas dışına çıkarsa, diğer taraftan geri getir
        if (canvas) {
          if (this.x < 0) this.x = canvas.width;
          if (this.x > canvas.width) this.x = 0;
          if (this.y < 0) this.y = canvas.height;
          if (this.y > canvas.height) this.y = 0;
        }
        
        // Parlaklık değerini rastgele değiştir (titreşim efekti için)
        this.brightness = 0.8 + Math.random() * 0.2;
      }

      draw() {
        if (!ctx) return;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        
        // Parlaklık değerine göre rengi ayarla
        ctx.fillStyle = this.color;
        ctx.globalAlpha = this.brightness;
        ctx.fill();
        
        // Parçacıklar için glow efekti
        ctx.shadowBlur = 15;
        ctx.shadowColor = this.color;
      }
    }

    // Parçacık sayısı - düşük performanslı cihazlar için azaltılabilir
    const particleCount = Math.min(150, Math.floor((canvas.width * canvas.height) / 10000));
    const particles: Particle[] = [];

    // Parçacıkları oluştur
    for (let i = 0; i < particleCount; i++) {
      particles.push(new Particle());
    }

    // Parçacıklar arasındaki bağlantıları çiz
    const drawConnections = () => {
      for (let a = 0; a < particles.length; a++) {
        for (let b = a; b < particles.length; b++) {
          const dx = particles[a].x - particles[b].x;
          const dy = particles[a].y - particles[b].y;
          const distance = Math.sqrt(dx * dx + dy * dy);
          
          // Belirli bir mesafeden daha yakın parçacıklar arasında çizgi çiz
          if (distance < 100) {
            ctx.beginPath();
            ctx.strokeStyle = particles[a].color;
            ctx.globalAlpha = 0.2 * (1 - distance / 100); // Mesafeye göre opaklık
            ctx.lineWidth = 0.5;
            ctx.moveTo(particles[a].x, particles[a].y);
            ctx.lineTo(particles[b].x, particles[b].y);
            ctx.stroke();
            ctx.globalAlpha = 1;
          }
        }
      }
    };

    // Mouse etkileşimi için değişkenler
    let mouseX = 0;
    let mouseY = 0;
    let isMouseActive = false;

    // Mouse olaylarını dinle
    canvas.addEventListener('mousemove', (event) => {
      mouseX = event.x;
      mouseY = event.y;
      isMouseActive = true;
      
      // Mouse hareketsiz kalırsa, etkileşimi kapat
      setTimeout(() => {
        isMouseActive = false;
      }, 3000);
    });

    // Mouse etkileşimi fonksiyonu
    const handleMouseInteraction = () => {
      if (!isMouseActive) return;
      
      particles.forEach(particle => {
        const dx = mouseX - particle.x;
        const dy = mouseY - particle.y;
        const distance = Math.sqrt(dx * dx + dy * dy);
        
        if (distance < 120) {
          // Mouse yakınındaki parçacıkları hafifçe iterek interaktif efekt oluştur
          particle.speedX += dx * 0.0002;
          particle.speedY += dy * 0.0002;
        }
      });
    };

    // Animasyon döngüsü
    const animate = () => {
      // Canvas'ı temizle, ama tamamen saydam değil, iz bırakarak kaybolma efekti için
      ctx.fillStyle = 'rgba(10, 10, 18, 0.1)';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      
      // Mouse etkileşimini işle
      handleMouseInteraction();
      
      // Parçacıkları güncelle ve çiz
      particles.forEach(particle => {
        particle.update();
        particle.draw();
      });
      
      // Parçacıklar arasındaki bağlantıları çiz
      drawConnections();
      
      // Animasyonu devam ettir
      requestAnimationFrame(animate);
    };

    // Animasyonu başlat
    animate();

    // Temizlik fonksiyonu
    return () => {
      window.removeEventListener('resize', resizeCanvas);
    };
  }, []);

  return <canvas ref={canvasRef} className="galaxy-background"></canvas>;
};

export default GalaxyBackground;