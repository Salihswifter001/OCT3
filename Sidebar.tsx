// src/components/Sidebar.tsx
import React from 'react';
import './Sidebar.css';

interface SidebarProps {
  activeSection: string;
  onSectionChange: (section: string) => void;
  onSettingsClick: () => void;
  onProfileClick: () => void;
}

const Sidebar: React.FC<SidebarProps> = ({
  activeSection,
  onSectionChange,
  onSettingsClick,
  onProfileClick
}) => {
  return (
    <div className="sidebar">
      <div className="sidebar-header">
        <h2>Octaverum</h2>
      </div>
      
      <div className="sidebar-menu">
        <div
          className={`menu-item ${activeSection === 'home' ? 'active' : ''}`}
          onClick={() => onSectionChange('home')}
        >
          <div className="menu-icon">🏠</div>
          <span>Ana Sayfa</span>
        </div>
        
        <div
          className={`menu-item ${activeSection === 'library' ? 'active' : ''}`}
          onClick={() => onSectionChange('library')}
        >
          <div className="menu-icon">📚</div>
          <span>Kütüphanem</span>
        </div>
        
        {/* Removed "Çalma Listelerim" from sidebar as per request */}
        
        <div className="sidebar-separator"></div>
        
        <div 
          className="menu-item"
          onClick={onProfileClick}
        >
          <div className="menu-icon">👤</div>
          <span>Profilim</span>
        </div>
        
        <div 
          className="menu-item" 
          onClick={onSettingsClick}
        >
          <div className="menu-icon">⚙️</div>
          <span>Ayarlar</span>
        </div>
      </div>
      {/* Removed the create playlist button from sidebar as requested */}
      
      <div className="sidebar-footer">
        <div className="version-info">v1.0.0</div>
      </div>
    </div>
  );
};

export default Sidebar;