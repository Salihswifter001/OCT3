-- Octaverum veritabanı yapısı
-- Bu SQL dosyası Octaverum web uygulaması için gerekli veritabanı tablolarını oluşturur

CREATE DATABASE IF NOT EXISTS `octaverum` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `octaverum`;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default_avatar.jpg',
  `subscription_type` enum('free','premium','pro') NOT NULL DEFAULT 'free',
  `subscription_expiry` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Kullanıcı ayarları tablosu
CREATE TABLE IF NOT EXISTS `user_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `dark_mode` tinyint(1) NOT NULL DEFAULT '1',
  `neon_color` varchar(7) NOT NULL DEFAULT '#00ffff',
  `glow_intensity` int(11) NOT NULL DEFAULT '100',
  `default_instrument` varchar(50) NOT NULL DEFAULT 'synth',
  `autoplay` tinyint(1) NOT NULL DEFAULT '0',
  `eq_bass` int(11) NOT NULL DEFAULT '50',
  `eq_treble` int(11) NOT NULL DEFAULT '50',
  `show_waveform` tinyint(1) NOT NULL DEFAULT '1',
  `transition_effect` enum('smooth','instant') NOT NULL DEFAULT 'smooth',
  `music_duration` enum('30s','60s','90s','120s') NOT NULL DEFAULT '60s',
  `bpm_min` int(11) NOT NULL DEFAULT '90',
  `bpm_max` int(11) NOT NULL DEFAULT '140',
  `music_key` varchar(20) NOT NULL DEFAULT 'Minör A',
  `output_format` enum('mp3','wav','flac') NOT NULL DEFAULT 'mp3',
  `click_sounds` tinyint(1) NOT NULL DEFAULT '1',
  `auto_expand_sidebar` tinyint(1) NOT NULL DEFAULT '1',
  `session_time` int(11) NOT NULL DEFAULT '30',
  `language` enum('tr','en','es') NOT NULL DEFAULT 'tr',
  `music_quality` enum('low','medium','high') NOT NULL DEFAULT 'high',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Müzik parçaları tablosu
CREATE TABLE IF NOT EXISTS `tracks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `artist` varchar(100) NOT NULL,
  `album` varchar(100) DEFAULT NULL,
  `cover_url` varchar(255) NOT NULL,
  `file_url` varchar(255) NOT NULL,
  `duration` varchar(10) NOT NULL,
  `genre` varchar(50) DEFAULT NULL,
  `is_ai_generated` tinyint(1) NOT NULL DEFAULT '1',
  `is_liked` tinyint(1) NOT NULL DEFAULT '0',
  `play_count` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tracks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Çalma listeleri tablosu
CREATE TABLE IF NOT EXISTS `playlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `cover_url` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `playlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Çalma listesi içeriği tablosu
CREATE TABLE IF NOT EXISTS `playlist_tracks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playlist_id` int(11) NOT NULL,
  `track_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `playlist_track` (`playlist_id`,`track_id`),
  KEY `track_id` (`track_id`),
  CONSTRAINT `playlist_tracks_ibfk_1` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `playlist_tracks_ibfk_2` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Müzik üretim prompt'ları tablosu
CREATE TABLE IF NOT EXISTS `music_prompts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `prompt_text` text NOT NULL,
  `includes_vocals` tinyint(1) NOT NULL DEFAULT '0',
  `lyrics` text,
  `selected_genres` varchar(255) DEFAULT NULL,
  `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `result_track_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `result_track_id` (`result_track_id`),
  CONSTRAINT `music_prompts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `music_prompts_ibfk_2` FOREIGN KEY (`result_track_id`) REFERENCES `tracks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Müzik analiz sonuçları tablosu
CREATE TABLE IF NOT EXISTS `audio_analysis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `track_id` int(11) NOT NULL,
  `bpm` int(11) NOT NULL,
  `key` varchar(10) NOT NULL,
  `scale` enum('major','minor') NOT NULL,
  `genres` varchar(255) DEFAULT NULL,
  `audio_length` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `track_id` (`track_id`),
  CONSTRAINT `audio_analysis_ibfk_1` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Kullanıcı cihazları tablosu
CREATE TABLE IF NOT EXISTS `user_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `device_name` varchar(100) NOT NULL,
  `device_type` varchar(50) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT '0',
  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `location` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Oturum token'ları
CREATE TABLE IF NOT EXISTS `tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `type` enum('access','refresh','reset_password') NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Örnek kullanıcı verileri (şifre: Password123)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `subscription_type`, `subscription_expiry`)
VALUES
('demo', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo Kullanıcı', 'premium', DATE_ADD(NOW(), INTERVAL 30 DAY));

-- Örnek ayarlar
INSERT INTO `user_settings` (`user_id`) SELECT id FROM `users` WHERE `username` = 'demo';

-- Örnek müzik parçaları
INSERT INTO `tracks` (`user_id`, `title`, `artist`, `album`, `cover_url`, `file_url`, `duration`, `genre`, `is_liked`)
VALUES
((SELECT id FROM `users` WHERE `username` = 'demo'), 'Neon Rüya', 'CyberSynth', 'Dijital Anılar', 'assets/covers/neon_ruya.jpg', 'assets/tracks/track1.mp3', '3:45', 'Synthwave', 1),
((SELECT id FROM `users` WHERE `username` = 'demo'), 'Gece Şehri', 'RetroWave', 'Neon Bulvarı', 'assets/covers/gece_sehri.jpg', 'assets/tracks/track2.mp3', '4:20', 'Cyberpunk', 0),
((SELECT id FROM `users` WHERE `username` = 'demo'), 'Dijital Yağmur', 'NeonEcho', 'Elektronik Gökyüzü', 'assets/covers/dijital_yagmur.jpg', 'assets/tracks/track3.mp3', '5:12', 'Vaporwave', 1);

-- Örnek çalma listesi
INSERT INTO `playlists` (`user_id`, `name`, `description`, `cover_url`)
VALUES
((SELECT id FROM `users` WHERE `username` = 'demo'), 'Gece Sürüşü', 'Gece sürüşleri için ideal synthwave parçaları', 'assets/covers/playlist1.jpg');

-- Çalma listesine parçalar ekleme
INSERT INTO `playlist_tracks` (`playlist_id`, `track_id`, `position`)
VALUES
(1, 1, 1),
(1, 2, 2);

-- Örnek müzik prompt
INSERT INTO `music_prompts` (`user_id`, `prompt_text`, `includes_vocals`, `lyrics`, `selected_genres`, `status`, `result_track_id`)
VALUES
((SELECT id FROM `users` WHERE `username` = 'demo'), 'Elektronik davul ritimleri ve arpejli sentezleyicilerle yağmurlu bir gece', 0, NULL, 'Synthwave,Cyberpunk', 'completed', 1);

-- Örnek müzik analizi
INSERT INTO `audio_analysis` (`track_id`, `bpm`, `key`, `scale`, `genres`, `audio_length`)
VALUES
(1, 120, 'A', 'minor', 'Synthwave,Electronic', 225);

-- Örnek kullanıcı cihazı
INSERT INTO `user_devices` (`user_id`, `device_name`, `device_type`, `is_current`, `location`)
VALUES
((SELECT id FROM `users` WHERE `username` = 'demo'), 'Chrome / Windows 10', 'desktop', 1, 'İstanbul');
