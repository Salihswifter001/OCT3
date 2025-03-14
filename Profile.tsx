// src/components/Profile.tsx
import React, { useState, useEffect } from 'react';
import './Profile.css';

interface ProfileProps {
  isOpen: boolean;
  onClose: () => void;
}

// Sample user profile data structure
interface UserProfile {
  username: string;
  email: string;
  fullName: string;
  avatar: string;
  joinDate: string;
  subscription: 'free' | 'premium' | 'pro';
  subscriptionExpiry?: string;
  createdTracks: number;
  likedTracks: number;
  playlists: number;
}

const Profile: React.FC<ProfileProps> = ({ isOpen, onClose }) => {
  const [activeTab, setActiveTab] = useState<string>('profile');
  const [profile, setProfile] = useState<UserProfile>({
    username: 'musiclover',
    email: 'user@example.com',
    fullName: 'Müzik Sever',
    avatar: 'https://via.placeholder.com/150',
    joinDate: '2024-03-15',
    subscription: 'premium',
    subscriptionExpiry: '2025-03-15',
    createdTracks: 27,
    likedTracks: 42,
    playlists: 8
  });
  
  // Form State
  const [formData, setFormData] = useState({
    fullName: '',
    username: '',
    email: '',
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  });
  
  const [successMessage, setSuccessMessage] = useState<string>('');
  const [errorMessage, setErrorMessage] = useState<string>('');
  
  // Load user profile
  useEffect(() => {
    if (isOpen) {
      // In a real app, you would fetch the user profile from an API
      // For this demo, we'll use the static data
      setFormData({
        ...formData,
        fullName: profile.fullName,
        username: profile.username,
        email: profile.email,
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      });
    }
  }, [isOpen, profile]);
  
  // Handle form input changes
  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value
    });
  };
  
  // Handle profile update
  const handleProfileUpdate = (e: React.FormEvent) => {
    e.preventDefault();
    
    // In a real app, you would send this data to your API
    setProfile({
      ...profile,
      fullName: formData.fullName,
      username: formData.username,
      email: formData.email
    });
    
    setSuccessMessage('Profil bilgileri başarıyla güncellendi');
    setTimeout(() => setSuccessMessage(''), 3000);
  };
  
  // Handle password change
  const handlePasswordChange = (e: React.FormEvent) => {
    e.preventDefault();
    
    // Password validation
    if (formData.newPassword.length < 8) {
      setErrorMessage('Şifre en az 8 karakter olmalıdır');
      return;
    }
    
    if (formData.newPassword !== formData.confirmPassword) {
      setErrorMessage('Şifreler eşleşmiyor');
      return;
    }
    
    // In a real app, you would send this to your API
    setSuccessMessage('Şifreniz başarıyla değiştirildi');
    setFormData({
      ...formData,
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    });
    
    setTimeout(() => setSuccessMessage(''), 3000);
    setErrorMessage('');
  };
  
  // Calculate subscription status
  const getSubscriptionStatus = () => {
    if (profile.subscription === 'free') {
      return 'Ücretsiz Plan';
    }
    
    if (profile.subscription === 'premium') {
      return 'Premium Plan';
    }
    
    return 'Pro Plan';
  };
  
  // Format date for display
  const formatDate = (dateString: string) => {
    const options: Intl.DateTimeFormatOptions = { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    };
    return new Date(dateString).toLocaleDateString('tr-TR', options);
  };
  
  // Plan rozet stillerini dinamik olarak belirle
  const getPlanBadgeStyle = (plan: 'free' | 'premium' | 'pro') => {
    const baseStyle = {
      padding: '0.5rem 1rem',
      borderRadius: '20px',
      fontWeight: 'bold' as 'bold',
      fontSize: '0.9rem',
      letterSpacing: '1px',
      display: 'inline-block',
      textShadow: '0 0 4px rgba(0, 0, 0, 0.8)',
      color: '#FFFFFF'
    };
    
    if (plan === 'premium') {
      return {
        ...baseStyle,
        backgroundColor: 'rgba(180, 0, 180, 0.9)',
        border: '2px solid #FF00FF',
        boxShadow: '0 0 10px rgba(255, 0, 255, 0.8)'
      };
    } 
    else if (plan === 'pro') {
      return {
        ...baseStyle,
        backgroundColor: 'rgba(0, 180, 180, 0.9)',
        border: '2px solid #00FFFF',
        boxShadow: '0 0 10px rgba(0, 255, 255, 0.8)'
      };
    }
    else {
      return {
        ...baseStyle,
        backgroundColor: 'rgba(50, 50, 50, 0.9)',
        border: '2px solid #CCCCCC'
      };
    }
  };
  
  if (!isOpen) return null;
  
  return (
    <div className="profile-overlay">
      <div className="profile-panel">
        <div className="profile-header">
          <h2>Kullanıcı Profili</h2>
          <div className="profile-close-button" onClick={onClose}>×</div>
        </div>
        
        <div className="profile-content">
          <div className="profile-sidebar">
            <div className="profile-avatar-container">
              <img src={profile.avatar} alt="Avatar" className="profile-avatar" />
              <div className="avatar-overlay">
                <span className="edit-avatar-btn">Değiştir</span>
              </div>
            </div>
            
            <div className="profile-username">{profile.username}</div>
            <div className="profile-subscription" style={getPlanBadgeStyle(profile.subscription)}>
              {getSubscriptionStatus()}
            </div>
            
            <div className="profile-stats">
              <div className="stat-item">
                <div className="stat-value">{profile.createdTracks}</div>
                <div className="stat-label">Oluşturulan</div>
              </div>
              <div className="stat-item">
                <div className="stat-value">{profile.likedTracks}</div>
                <div className="stat-label">Beğenilen</div>
              </div>
              <div className="stat-item">
                <div className="stat-value">{profile.playlists}</div>
                <div className="stat-label">Çalma Listesi</div>
              </div>
            </div>
            
            <div className="profile-tabs">
              <div 
                className={`profile-tab ${activeTab === 'profile' ? 'active' : ''}`}
                onClick={() => setActiveTab('profile')}
              >
                <span className="profile-tab-icon">👤</span>
                <span>Profil Bilgileri</span>
              </div>
              <div 
                className={`profile-tab ${activeTab === 'password' ? 'active' : ''}`}
                onClick={() => setActiveTab('password')}
              >
                <span className="profile-tab-icon">🔒</span>
                <span>Şifre Değiştir</span>
              </div>
              <div 
                className={`profile-tab ${activeTab === 'subscription' ? 'active' : ''}`}
                onClick={() => setActiveTab('subscription')}
              >
                <span className="profile-tab-icon">💎</span>
                <span>Abonelik</span>
              </div>
              <div 
                className={`profile-tab ${activeTab === 'devices' ? 'active' : ''}`}
                onClick={() => setActiveTab('devices')}
              >
                <span className="profile-tab-icon">📱</span>
                <span>Cihazlar</span>
              </div>
            </div>
          </div>
          
          <div className="profile-panel-content">
            {successMessage && (
              <div className="success-message">
                {successMessage}
              </div>
            )}
            
            {errorMessage && (
              <div className="error-message">
                {errorMessage}
              </div>
            )}
            
            {/* Profil Bilgileri Sekmesi */}
            {activeTab === 'profile' && (
              <div className="profile-section">
                <h3>Profil Bilgileri</h3>
                
                <form onSubmit={handleProfileUpdate} className="profile-form">
                  <div className="form-group">
                    <label htmlFor="fullName">Ad Soyad</label>
                    <input 
                      type="text" 
                      id="fullName"
                      name="fullName"
                      value={formData.fullName}
                      onChange={handleInputChange}
                      className="profile-input"
                    />
                  </div>
                  
                  <div className="form-group">
                    <label htmlFor="username">Kullanıcı Adı</label>
                    <input 
                      type="text" 
                      id="username"
                      name="username"
                      value={formData.username}
                      onChange={handleInputChange}
                      className="profile-input"
                    />
                  </div>
                  
                  <div className="form-group">
                    <label htmlFor="email">E-posta</label>
                    <input 
                      type="email" 
                      id="email"
                      name="email"
                      value={formData.email}
                      onChange={handleInputChange}
                      className="profile-input"
                    />
                  </div>
                  
                  <div className="form-info">
                    <div className="info-item">
                      <span className="info-label">Katılma Tarihi:</span>
                      <span className="info-value">{formatDate(profile.joinDate)}</span>
                    </div>
                  </div>
                  
                  <div className="form-actions">
                    <button type="submit" className="profile-save-button">
                      Değişiklikleri Kaydet
                    </button>
                  </div>
                </form>
              </div>
            )}
            
            {/* Şifre Değiştirme Sekmesi */}
            {activeTab === 'password' && (
              <div className="profile-section">
                <h3>Şifre Değiştir</h3>
                
                <form onSubmit={handlePasswordChange} className="profile-form">
                  <div className="form-group">
                    <label htmlFor="currentPassword">Mevcut Şifre</label>
                    <input 
                      type="password" 
                      id="currentPassword"
                      name="currentPassword"
                      value={formData.currentPassword}
                      onChange={handleInputChange}
                      className="profile-input"
                      required
                    />
                  </div>
                  
                  <div className="form-group">
                    <label htmlFor="newPassword">Yeni Şifre</label>
                    <input 
                      type="password" 
                      id="newPassword"
                      name="newPassword"
                      value={formData.newPassword}
                      onChange={handleInputChange}
                      className="profile-input"
                      required
                    />
                  </div>
                  
                  <div className="form-group">
                    <label htmlFor="confirmPassword">Yeni Şifre (Tekrar)</label>
                    <input 
                      type="password" 
                      id="confirmPassword"
                      name="confirmPassword"
                      value={formData.confirmPassword}
                      onChange={handleInputChange}
                      className="profile-input"
                      required
                    />
                  </div>
                  
                  <div className="password-requirements">
                    <p>Şifreniz:</p>
                    <ul>
                      <li className={formData.newPassword.length >= 8 ? 'valid' : ''}>
                        En az 8 karakter olmalıdır
                      </li>
                      <li className={/[A-Z]/.test(formData.newPassword) ? 'valid' : ''}>
                        En az bir büyük harf içermelidir
                      </li>
                      <li className={/[0-9]/.test(formData.newPassword) ? 'valid' : ''}>
                        En az bir rakam içermelidir
                      </li>
                    </ul>
                  </div>
                  
                  <div className="form-actions">
                    <button type="submit" className="profile-save-button">
                      Şifreyi Değiştir
                    </button>
                  </div>
                </form>
              </div>
            )}
            
            {/* Abonelik Sekmesi - SORUN BURADA */}
            {activeTab === 'subscription' && (
              <div className="profile-section">
                <h3>Abonelik Bilgileri</h3>
                
                <div className="subscription-info">
                  <div className="subscription-status">
                    <div className="current-plan">
                      <h4>Mevcut Plan</h4>
                      <div 
                        style={getPlanBadgeStyle(profile.subscription)}
                      >
                        {profile.subscription.toUpperCase()}
                      </div>
                    </div>
                    
                    <div className="plan-details">
                      <div className="plan-item">
                        <span className="plan-label">Durum:</span>
                        <span className="plan-value active">Aktif</span>
                      </div>
                      
                      <div className="plan-item">
                        <span className="plan-label">Yenilenme Tarihi:</span>
                        <span className="plan-value">
                          {profile.subscriptionExpiry ? formatDate(profile.subscriptionExpiry) : 'Yok'}
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <div className="subscription-features">
                    <h4>Plan Özellikleri</h4>
                    <ul className="features-list">
                      <li>Sınırsız müzik oluşturma</li>
                      <li>Yüksek kaliteli ses çıktısı</li>
                      <li>Özel şarkı sözü desteği</li>
                      <li>Gelişmiş parametre ayarları</li>
                      <li>Öncelikli oluşturma sırası</li>
                    </ul>
                  </div>
                  
                  <div className="subscription-actions">
                    <button className="upgrade-button">
                      Pro Plana Yükselt
                    </button>
                    <button className="cancel-button">
                      Aboneliği İptal Et
                    </button>
                  </div>
                </div>
              </div>
            )}
            
            {/* Cihazlar Sekmesi */}
            {activeTab === 'devices' && (
              <div className="profile-section">
                <h3>Bağlı Cihazlar</h3>
                
                <div className="devices-list">
                  <div className="device-item current">
                    <div className="device-icon">
                      <span>💻</span>
                    </div>
                    <div className="device-info">
                      <div className="device-name">Chrome / Windows 10</div>
                      <div className="device-detail">Bu cihaz • Son erişim: Bugün, 14:35</div>
                    </div>
                  </div>
                  
                  <div className="device-item">
                    <div className="device-icon">
                      <span>📱</span>
                    </div>
                    <div className="device-info">
                      <div className="device-name">Safari / iPhone 13</div>
                      <div className="device-detail">İstanbul • Son erişim: Dün, 19:20</div>
                    </div>
                    <button className="logout-device-button">Çıkış Yap</button>
                  </div>
                  
                  <div className="device-item">
                    <div className="device-icon">
                      <span>🖥️</span>
                    </div>
                    <div className="device-info">
                      <div className="device-name">Firefox / macOS</div>
                      <div className="device-detail">Ankara • Son erişim: 25 Mart 2025</div>
                    </div>
                    <button className="logout-device-button">Çıkış Yap</button>
                  </div>
                </div>
                
                <div className="devices-actions">
                  <button className="logout-all-button">
                    Tüm Cihazlardan Çıkış Yap
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
        
        <div className="profile-footer">
          <div className="profile-actions">
            <button className="close-profile-button" onClick={onClose}>
              Kapat
            </button>
            <button className="logout-button">
              Çıkış Yap
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Profile;