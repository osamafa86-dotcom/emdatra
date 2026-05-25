-- emdatra CMS schema (MySQL / MariaDB)
-- Tables are prefixed with emd_ so they can live safely inside an existing database.

CREATE TABLE IF NOT EXISTS emd_admins (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(64)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS emd_settings (
    skey   VARCHAR(64) PRIMARY KEY,
    svalue MEDIUMTEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS emd_blocks (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    page       VARCHAR(64)  NOT NULL DEFAULT 'home',
    type       VARCHAR(48)  NOT NULL,
    sort_order INT          NOT NULL DEFAULT 0,
    is_visible TINYINT(1)   NOT NULL DEFAULT 1,
    content    MEDIUMTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_page_order (page, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
