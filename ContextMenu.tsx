// src/components/ContextMenu.tsx
import React, { useState, useEffect, useRef } from 'react';
import './ContextMenu.css';

interface ContextMenuItem {
  icon: string;
  label: string;
  action: () => void;
  divider?: boolean;
  disabled?: boolean;
  danger?: boolean;
}

interface ContextMenuProps {
  items: ContextMenuItem[];
  position: { x: number; y: number } | null;
  onClose: () => void;
}

const ContextMenu: React.FC<ContextMenuProps> = ({ items, position, onClose }) => {
  const menuRef = useRef<HTMLDivElement>(null);
  const [menuStyle, setMenuStyle] = useState<React.CSSProperties>({
    top: '0px',
    left: '0px',
    opacity: 0,
    transform: 'scale(0.95)',
    pointerEvents: 'none' // Burada hata vardı
  });

  useEffect(() => {
    if (!position) {
      setMenuStyle(prev => ({
        ...prev,
        opacity: 0,
        transform: 'scale(0.95)',
        pointerEvents: 'none'
      }));
      return;
    }

    const calculatePosition = () => {
      const menuWidth = 220;
      const menuHeight = items.length * 40 + (items.filter(item => item.divider).length * 10);
      
      const windowWidth = window.innerWidth;
      const windowHeight = window.innerHeight;
      
      let left = position.x;
      let top = position.y;
      
      // Sağa taşma kontrolü
      if (left + menuWidth > windowWidth) {
        left = windowWidth - menuWidth - 10;
      }
      
      // Alta taşma kontrolü
      if (top + menuHeight > windowHeight) {
        top = windowHeight - menuHeight - 10;
      }
      
      return { top, left };
    };
    
    const { top, left } = calculatePosition();
    
    setMenuStyle({
      top: `${top}px`,
      left: `${left}px`,
      opacity: 1,
      transform: 'scale(1)',
      pointerEvents: 'auto' // 'auto' React.CSSProperties için geçerli bir değer
    });
    
    // Dışarı tıklandığında menüyü kapatma
    const handleClickOutside = (e: MouseEvent) => {
      if (menuRef.current && !menuRef.current.contains(e.target as Node)) {
        onClose();
      }
    };
    
    document.addEventListener('mousedown', handleClickOutside);
    
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [position, items, onClose]);
  
  if (!position) return null;
  
  return (
    <div 
      className="context-menu"
      ref={menuRef}
      style={menuStyle}
    >
      {items.map((item, index) => (
        <React.Fragment key={index}>
          <div 
            className={`context-menu-item ${item.disabled ? 'disabled' : ''} ${item.danger ? 'danger' : ''}`}
            onClick={() => {
              if (!item.disabled) {
                item.action();
                onClose();
              }
            }}
          >
            <span className="menu-item-icon">{item.icon}</span>
            <span className="menu-item-label">{item.label}</span>
          </div>
          {item.divider && <div className="context-menu-divider"></div>}
        </React.Fragment>
      ))}
    </div>
  );
};

export default ContextMenu;