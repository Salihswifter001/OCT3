// src/components/AppFooter.tsx
import React from 'react';
import './AppFooter.css';

const AppFooter: React.FC = () => {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="app-footer">
      <div className="footer-content">
        <div className="footer-logo">
          <div className="footer-logo-glow"></div>
          <span className="footer-text">Powered by OctaInc.</span>
        </div>
        <div className="footer-copyright">
          © {currentYear} Octaverum AI. Tüm hakları saklıdır.
        </div>
      </div>
    </footer>
  );
};

export default AppFooter;