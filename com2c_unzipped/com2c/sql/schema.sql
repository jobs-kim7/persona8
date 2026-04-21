-- Minimal starter schema for com2c MVP

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  google_id VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  plan ENUM('free','basic','pro') NOT NULL DEFAULT 'free',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cards (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  slug VARCHAR(100) NOT NULL,
  title VARCHAR(150) NOT NULL,
  purpose ENUM('basic','networking','collab','portfolio','recruiting','soho') NOT NULL DEFAULT 'basic',
  name VARCHAR(100) NOT NULL,
  one_liner VARCHAR(255) NULL,
  role_title VARCHAR(150) NULL,
  company_name VARCHAR(150) NULL,
  email VARCHAR(255) NULL,
  phone VARCHAR(50) NULL,
  bio TEXT NULL,
  share_title VARCHAR(200) NULL,
  share_description VARCHAR(300) NULL,
  share_image_url VARCHAR(500) NULL,
  status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cards_user FOREIGN KEY (user_id) REFERENCES users(id),
  UNIQUE KEY uk_user_slug (user_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS card_links (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  card_id BIGINT UNSIGNED NOT NULL,
  link_type ENUM('website','youtube','instagram','blog','linkedin','pdf','kakao','booking','other') NOT NULL DEFAULT 'website',
  url VARCHAR(1000) NOT NULL,
  title VARCHAR(255) NULL,
  description VARCHAR(500) NULL,
  thumbnail_url VARCHAR(500) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_card_links_card FOREIGN KEY (card_id) REFERENCES cards(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS card_ai_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  card_id BIGINT UNSIGNED NOT NULL,
  ai_role ENUM('guide','matchmaker','recruiting','soho_helper') NOT NULL DEFAULT 'guide',
  greeting_message TEXT NULL,
  quick_actions TEXT NULL,
  tone ENUM('business','warm','practical') NOT NULL DEFAULT 'business',
  revisit_greeting_enabled TINYINT(1) NOT NULL DEFAULT 1,
  abnormal_access_fallback ENUM('ignore','email_only') NOT NULL DEFAULT 'email_only',
  model_name VARCHAR(100) NOT NULL DEFAULT 'gpt-4o-mini',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ai_settings_card FOREIGN KEY (card_id) REFERENCES cards(id),
  UNIQUE KEY uk_ai_settings_card (card_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sponsors (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sponsor_type ENUM('self','partner') NOT NULL DEFAULT 'self',
  owner_user_id BIGINT UNSIGNED NULL,
  name VARCHAR(150) NOT NULL,
  description VARCHAR(500) NULL,
  website_url VARCHAR(1000) NULL,
  logo_url VARCHAR(500) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS card_sponsor_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  card_id BIGINT UNSIGNED NOT NULL,
  default_sponsor_id BIGINT UNSIGNED NOT NULL,
  sponsor_mode ENUM('self','partner','mixed') NOT NULL DEFAULT 'self',
  partner_sponsor_id BIGINT UNSIGNED NULL,
  allow_partner_sponsor TINYINT(1) NOT NULL DEFAULT 0,
  min_charge_amount INT NOT NULL DEFAULT 10,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_card_sponsor_card (card_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS visitor_sessions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  card_id BIGINT UNSIGNED NOT NULL,
  session_token CHAR(36) NOT NULL UNIQUE,
  sponsor_mode ENUM('self','partner','mixed') NOT NULL DEFAULT 'self',
  locked_sponsor_id BIGINT UNSIGNED NULL,
  valid_conversation TINYINT(1) NOT NULL DEFAULT 0,
  abnormal_flag TINYINT(1) NOT NULL DEFAULT 0,
  reserved_credit_amount INT NOT NULL DEFAULT 0,
  final_credit_amount INT NOT NULL DEFAULT 0,
  started_at DATETIME NULL,
  ended_at DATETIME NULL,
  last_activity_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS conversation_messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id BIGINT UNSIGNED NOT NULL,
  sender_type ENUM('visitor','assistant','system') NOT NULL,
  message_text TEXT NOT NULL,
  token_input INT NOT NULL DEFAULT 0,
  token_output INT NOT NULL DEFAULT 0,
  cost_amount INT NOT NULL DEFAULT 0,
  is_meaningful_input TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inquiries (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id BIGINT UNSIGNED NOT NULL,
  card_id BIGINT UNSIGNED NOT NULL,
  inquiry_type ENUM('collab','consulting','recruiting','booking','general') NOT NULL DEFAULT 'general',
  visitor_name VARCHAR(100) NULL,
  visitor_email VARCHAR(255) NULL,
  raw_message TEXT NULL,
  summary_text TEXT NOT NULL,
  fit_level ENUM('high','medium','low','unknown') NOT NULL DEFAULT 'unknown',
  ai_recommendation TEXT NULL,
  status ENUM('new','reviewing','replied','hold','closed') NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS credit_ledger (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  owner_type ENUM('user','sponsor') NOT NULL,
  owner_id BIGINT UNSIGNED NOT NULL,
  related_session_id BIGINT UNSIGNED NULL,
  related_inquiry_id BIGINT UNSIGNED NULL,
  entry_type ENUM('charge','deduct','refund','hold','release') NOT NULL,
  amount INT NOT NULL,
  balance_after INT NULL,
  reason_code ENUM('manual_topup','conversation_hold','conversation_deduct','conversation_release','partner_credit','refund','admin_adjust') NOT NULL,
  memo VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inquiry_messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inquiry_id BIGINT UNSIGNED NOT NULL,
  sender_type ENUM('visitor','owner','ai','system') NOT NULL DEFAULT 'visitor',
  visibility ENUM('public','owner_only','visitor_only','system') NOT NULL DEFAULT 'public',
  message_text TEXT NULL,
  is_internal TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_inquiry_messages_inquiry FOREIGN KEY (inquiry_id) REFERENCES inquiries(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inquiry_message_attachments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inquiry_message_id BIGINT UNSIGNED NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  mime_type VARCHAR(100) NULL,
  file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_message_attachments_message FOREIGN KEY (inquiry_message_id) REFERENCES inquiry_messages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
