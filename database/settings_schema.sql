CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_group VARCHAR(50) NOT NULL,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO system_settings (setting_group, setting_key, setting_value) VALUES
('epayco', 'EPAYCO_CUST_ID', '1581627'),
('epayco', 'EPAYCO_P_KEY', '7141425823ade7a9cad3b7b62058ce26f6a6a48e'),
('epayco', 'EPAYCO_PUBLIC_KEY', '355c8fbe174f54a3ee7f413162b69e47'),
('epayco', 'EPAYCO_PRIVATE_KEY', '17e7cfad6a6266254aa63bcda26ed8f0'),
('epayco', 'EPAYCO_TESTING', '1'),
('epayco', 'EPAYCO_LANG', 'es'),
('epayco', 'EPAYCO_CURRENCY', 'COP'),
('epayco', 'EPAYCO_COUNTRY', 'CO')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
