-- Persistent application settings (general, company, inventory alerts,
-- notifications, finance and security) used by the Settings page.
-- No existing data is modified; defaults live in AppSetting::definitions().

CREATE TABLE IF NOT EXISTS app_settings (
  setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
  setting_value TEXT NULL,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
