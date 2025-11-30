-- ============================================================================
-- Scout Family Management System - Database Schema
-- ============================================================================
-- Version: 1.0
-- Date: 2025-11-30
-- Database: MySQL 8.0+
-- Character Set: utf8mb4 (supports emoji, international characters)
-- Collation: utf8mb4_unicode_ci (case-insensitive, accent-sensitive)
-- ============================================================================

-- Drop existing tables (use with caution in production!)
-- Uncomment the following lines only for fresh installation
-- SET FOREIGN_KEY_CHECKS = 0;
-- DROP TABLE IF EXISTS audit_logs;
-- DROP TABLE IF EXISTS sync_logs;
-- DROP TABLE IF EXISTS user_permissions;
-- DROP TABLE IF EXISTS adult_leaders;
-- DROP TABLE IF EXISTS scouts;
-- DROP TABLE IF EXISTS persons;
-- DROP TABLE IF EXISTS families;
-- DROP TABLE IF EXISTS settings;
-- SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- Table: families
-- Description: Household units containing one or more persons
-- ============================================================================

CREATE TABLE families (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Family name (e.g., "Maulit Family", "Smith-Jones Family")',
    street_address VARCHAR(255) NULL COMMENT 'Street address',
    city VARCHAR(100) NULL COMMENT 'City',
    state VARCHAR(2) NULL COMMENT 'Two-letter state code (e.g., TX, CA)',
    zip VARCHAR(10) NULL COMMENT 'ZIP code (5 or 9 digits)',
    primary_phone VARCHAR(20) NULL COMMENT 'Primary contact phone number',
    notes TEXT NULL COMMENT 'Internal notes about the family',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp (NULL = active)',

    INDEX idx_name (name),
    INDEX idx_deleted (deleted_at),
    INDEX idx_city_state (city, state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Family household units';

-- ============================================================================
-- Table: persons
-- Description: Individual members (scouts, parents, siblings, adult leaders)
-- ============================================================================

CREATE TABLE persons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    family_id BIGINT UNSIGNED NULL COMMENT 'FK to families (NULL = orphaned person)',
    bsa_member_id VARCHAR(20) NULL COMMENT 'BSA Member ID from roster (unique identifier from Scoutbook)',
    person_type ENUM('scout', 'parent', 'sibling', 'adult_leader') NOT NULL COMMENT 'Type of person',
    prefix VARCHAR(10) NULL COMMENT 'Title (Mr., Mrs., Ms., Dr., etc.)',
    first_name VARCHAR(100) NOT NULL COMMENT 'First name',
    middle_name VARCHAR(100) NULL COMMENT 'Middle name or initial',
    last_name VARCHAR(100) NOT NULL COMMENT 'Last name',
    suffix VARCHAR(10) NULL COMMENT 'Suffix (Jr., Sr., III, etc.)',
    nickname VARCHAR(100) NULL COMMENT 'Preferred name or nickname',
    gender ENUM('M', 'F', 'Other') NULL COMMENT 'Gender',
    date_of_birth DATE NULL COMMENT 'Date of birth',
    age INT NULL COMMENT 'Current age (calculated or imported)',
    email VARCHAR(255) NULL COMMENT 'Email address',
    phone VARCHAR(20) NULL COMMENT 'Phone number',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp (NULL = active)',

    FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE SET NULL,

    INDEX idx_family (family_id),
    INDEX idx_bsa_member (bsa_member_id),
    INDEX idx_email (email),
    INDEX idx_name (last_name, first_name),
    INDEX idx_type (person_type),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Individual persons (scouts, parents, siblings, leaders)';

-- ============================================================================
-- Table: scouts
-- Description: Scout-specific attributes (extends persons)
-- ============================================================================

CREATE TABLE scouts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id BIGINT UNSIGNED NOT NULL COMMENT 'FK to persons',
    grade VARCHAR(50) NULL COMMENT 'School grade (e.g., "First Grade", "Second Grade")',
    rank VARCHAR(50) NULL COMMENT 'Scout rank (Lion, Tiger, Wolf, Bear, Webelos, Arrow of Light)',
    den VARCHAR(50) NULL COMMENT 'Den assignment (e.g., "Den 3", "Webelos 1")',
    registration_expiration_date DATE NULL COMMENT 'BSA registration expiration date',
    registration_status VARCHAR(50) NULL COMMENT 'Registration status (New, Re-Registered, Transfer, Multiple)',
    ypt_status VARCHAR(50) NULL COMMENT 'Youth Protection Training status (for youth members if applicable)',
    program VARCHAR(50) DEFAULT 'Cub Scouting' COMMENT 'BSA program (Cub Scouting, Scouts BSA, etc.)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',

    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE,

    INDEX idx_person (person_id),
    INDEX idx_expiration (registration_expiration_date),
    INDEX idx_den (den),
    INDEX idx_rank (rank),
    INDEX idx_grade (grade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Scout-specific attributes';

-- ============================================================================
-- Table: adult_leaders
-- Description: Adult leader-specific attributes (extends persons)
-- ============================================================================

CREATE TABLE adult_leaders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id BIGINT UNSIGNED NOT NULL COMMENT 'FK to persons',
    positions JSON NULL COMMENT 'Array of leadership positions (e.g., ["Den Leader", "Committee Member"])',
    ypt_status VARCHAR(50) NULL COMMENT 'Youth Protection Training status (Current, Never Taken, Expired)',
    ypt_completion_date DATE NULL COMMENT 'YPT completion date',
    ypt_expiration_date DATE NULL COMMENT 'YPT expiration date (typically 2 years from completion)',
    registration_expiration_date DATE NULL COMMENT 'BSA registration expiration date',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',

    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE,

    INDEX idx_person (person_id),
    INDEX idx_ypt_expiration (ypt_expiration_date),
    INDEX idx_registration_expiration (registration_expiration_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Adult leader-specific attributes';

-- ============================================================================
-- Table: user_permissions
-- Description: Links WordPress users to persons and defines access roles
-- ============================================================================

CREATE TABLE user_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wordpress_user_id BIGINT UNSIGNED NOT NULL COMMENT 'WordPress user ID (references wp_users.ID)',
    person_id BIGINT UNSIGNED NULL COMMENT 'FK to persons (links WP user to person record)',
    role ENUM('admin', 'editor', 'viewer') NOT NULL COMMENT 'Access role',
    granted_by BIGINT UNSIGNED NULL COMMENT 'WordPress user ID of admin who granted permission',
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When permission was granted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',

    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE SET NULL,

    INDEX idx_wp_user (wordpress_user_id),
    INDEX idx_role (role),
    UNIQUE KEY unique_wp_user (wordpress_user_id) COMMENT 'One permission record per WP user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='User permissions linking WordPress users to access roles';

-- ============================================================================
-- Table: audit_logs
-- Description: Tracks all data modifications for accountability
-- ============================================================================

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL COMMENT 'WordPress user ID who performed the action',
    entity_type VARCHAR(50) NOT NULL COMMENT 'Type of entity modified (family, person, scout, etc.)',
    entity_id BIGINT UNSIGNED NOT NULL COMMENT 'ID of the modified entity',
    action ENUM('create', 'update', 'delete') NOT NULL COMMENT 'Type of action performed',
    changes JSON NULL COMMENT 'Before/after values for updates (e.g., {"field": {"old": "value1", "new": "value2"}})',
    ip_address VARCHAR(45) NULL COMMENT 'IP address of the user (supports IPv4 and IPv6)',
    user_agent VARCHAR(255) NULL COMMENT 'Browser user agent string',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When the action occurred',

    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Audit trail for all data modifications';

-- ============================================================================
-- Table: sync_logs
-- Description: Tracks Scoutbook and Mailchimp synchronization history
-- ============================================================================

CREATE TABLE sync_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sync_type ENUM('scoutbook', 'mailchimp_import') NOT NULL COMMENT 'Type of sync operation',
    status ENUM('running', 'completed', 'failed') NOT NULL COMMENT 'Sync status',
    started_at TIMESTAMP NOT NULL COMMENT 'When sync started',
    completed_at TIMESTAMP NULL COMMENT 'When sync completed (NULL if still running or failed)',
    records_processed INT DEFAULT 0 COMMENT 'Total number of records processed',
    records_created INT DEFAULT 0 COMMENT 'Number of new records created',
    records_updated INT DEFAULT 0 COMMENT 'Number of existing records updated',
    records_skipped INT DEFAULT 0 COMMENT 'Number of records skipped (errors or duplicates)',
    errors JSON NULL COMMENT 'Array of error messages (e.g., [{"row": 10, "message": "Missing email"}])',
    triggered_by BIGINT UNSIGNED NULL COMMENT 'WordPress user ID (NULL if automated/scheduled)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',

    INDEX idx_type (sync_type),
    INDEX idx_status (status),
    INDEX idx_started (started_at),
    INDEX idx_triggered_by (triggered_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Synchronization history for Scoutbook and Mailchimp';

-- ============================================================================
-- Table: settings
-- Description: Application settings and configuration
-- ============================================================================

CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL COMMENT 'Unique setting identifier (e.g., "scoutbook.username")',
    setting_value TEXT NULL COMMENT 'Setting value (plain text or encrypted)',
    is_encrypted BOOLEAN DEFAULT FALSE COMMENT 'Whether the value is encrypted (true for passwords, API keys)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',

    UNIQUE KEY unique_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Application settings and configuration';

-- ============================================================================
-- Initial Settings Data
-- ============================================================================

INSERT INTO settings (setting_key, setting_value, is_encrypted) VALUES
('scoutbook.username', NULL, FALSE),
('scoutbook.password', NULL, TRUE),
('scoutbook.unit_number', '97', FALSE),
('scoutbook.auto_sync_enabled', 'true', FALSE),
('scoutbook.sync_time', '02:00', FALSE),
('mailchimp.api_key', NULL, TRUE),
('mailchimp.audience_id', NULL, FALSE),
('pack.name', 'Pack 97', FALSE),
('pack.number', '97', FALSE),
('system.timezone', 'America/Chicago', FALSE),
('system.date_format', 'm/d/Y', FALSE),
('notification.admin_email', 'admin@pack97.com', FALSE);

-- ============================================================================
-- Views for Convenience Queries
-- ============================================================================

-- View: All persons with family information
CREATE OR REPLACE VIEW v_persons_with_families AS
SELECT
    p.id,
    p.family_id,
    f.name AS family_name,
    p.bsa_member_id,
    p.person_type,
    CONCAT_WS(' ', p.prefix, p.first_name, p.middle_name, p.last_name, p.suffix) AS full_name,
    p.first_name,
    p.last_name,
    p.nickname,
    p.gender,
    p.date_of_birth,
    p.age,
    p.email,
    p.phone,
    p.created_at,
    p.updated_at,
    p.deleted_at
FROM persons p
LEFT JOIN families f ON p.family_id = f.id
WHERE p.deleted_at IS NULL;

-- View: All scouts with family and expiration info
CREATE OR REPLACE VIEW v_scouts_with_details AS
SELECT
    p.id AS person_id,
    p.family_id,
    f.name AS family_name,
    CONCAT_WS(' ', p.first_name, p.last_name) AS scout_name,
    p.first_name,
    p.last_name,
    p.age,
    p.email,
    s.grade,
    s.rank,
    s.den,
    s.registration_expiration_date,
    s.registration_status,
    DATEDIFF(s.registration_expiration_date, CURDATE()) AS days_until_expiration,
    CASE
        WHEN s.registration_expiration_date IS NULL THEN 'Unknown'
        WHEN s.registration_expiration_date < CURDATE() THEN 'Expired'
        WHEN DATEDIFF(s.registration_expiration_date, CURDATE()) < 30 THEN 'Expiring Soon'
        WHEN DATEDIFF(s.registration_expiration_date, CURDATE()) < 60 THEN 'Expiring in 60 Days'
        ELSE 'Active'
    END AS expiration_status,
    p.created_at,
    p.updated_at
FROM persons p
INNER JOIN scouts s ON p.id = s.person_id
LEFT JOIN families f ON p.family_id = f.id
WHERE p.deleted_at IS NULL
  AND p.person_type = 'scout';

-- View: All adult leaders with YPT status
CREATE OR REPLACE VIEW v_adult_leaders_with_details AS
SELECT
    p.id AS person_id,
    p.family_id,
    f.name AS family_name,
    CONCAT_WS(' ', p.first_name, p.last_name) AS leader_name,
    p.first_name,
    p.last_name,
    p.email,
    p.phone,
    al.positions,
    al.ypt_status,
    al.ypt_completion_date,
    al.ypt_expiration_date,
    DATEDIFF(al.ypt_expiration_date, CURDATE()) AS days_until_ypt_expiration,
    al.registration_expiration_date,
    CASE
        WHEN al.ypt_expiration_date IS NULL THEN 'Unknown'
        WHEN al.ypt_expiration_date < CURDATE() THEN 'Expired'
        WHEN DATEDIFF(al.ypt_expiration_date, CURDATE()) < 30 THEN 'Expiring Soon'
        WHEN DATEDIFF(al.ypt_expiration_date, CURDATE()) < 90 THEN 'Expiring in 90 Days'
        ELSE 'Current'
    END AS ypt_expiration_status,
    p.created_at,
    p.updated_at
FROM persons p
INNER JOIN adult_leaders al ON p.id = al.person_id
LEFT JOIN families f ON p.family_id = f.id
WHERE p.deleted_at IS NULL
  AND p.person_type = 'adult_leader';

-- View: Family summary with member counts
CREATE OR REPLACE VIEW v_family_summary AS
SELECT
    f.id,
    f.name,
    f.street_address,
    f.city,
    f.state,
    f.zip,
    f.primary_phone,
    COUNT(DISTINCT CASE WHEN p.person_type = 'scout' THEN p.id END) AS scout_count,
    COUNT(DISTINCT CASE WHEN p.person_type = 'parent' THEN p.id END) AS parent_count,
    COUNT(DISTINCT CASE WHEN p.person_type = 'sibling' THEN p.id END) AS sibling_count,
    COUNT(DISTINCT CASE WHEN p.person_type = 'adult_leader' THEN p.id END) AS leader_count,
    GROUP_CONCAT(DISTINCT CASE WHEN p.person_type = 'parent' THEN p.email END SEPARATOR ', ') AS parent_emails,
    f.created_at,
    f.updated_at
FROM families f
LEFT JOIN persons p ON f.id = p.family_id AND p.deleted_at IS NULL
WHERE f.deleted_at IS NULL
GROUP BY f.id, f.name, f.street_address, f.city, f.state, f.zip, f.primary_phone, f.created_at, f.updated_at;

-- ============================================================================
-- Stored Procedures
-- ============================================================================

-- Procedure: Get Mailchimp export data for a family
DELIMITER //

CREATE PROCEDURE sp_get_mailchimp_family_data(IN family_id_param BIGINT)
BEGIN
    -- Get parent emails for the family
    SELECT
        p.email AS parent_email,
        p.first_name AS parent_first_name,
        p.last_name AS parent_last_name,
        p.phone AS parent_phone,
        p.bsa_member_id AS parent_bsa_member_id,
        f.street_address,
        f.city,
        f.state,
        f.zip,
        f.primary_phone AS family_phone
    FROM persons p
    INNER JOIN families f ON p.family_id = f.id
    WHERE p.family_id = family_id_param
      AND p.person_type = 'parent'
      AND p.email IS NOT NULL
      AND p.deleted_at IS NULL
      AND f.deleted_at IS NULL;

    -- Get scouts for the family (up to 3, ordered by expiration date DESC)
    SELECT
        CONCAT_WS(' ', p.first_name, p.last_name) AS scout_name,
        COALESCE(s.den, s.grade, s.rank) AS scout_den,
        DATE_FORMAT(s.registration_expiration_date, '%m/%d/%Y') AS scout_expiration
    FROM persons p
    INNER JOIN scouts s ON p.id = s.person_id
    WHERE p.family_id = family_id_param
      AND p.person_type = 'scout'
      AND p.deleted_at IS NULL
    ORDER BY s.registration_expiration_date DESC
    LIMIT 3;

    -- Get siblings for the family (up to 3, ordered by age ASC - youngest first)
    SELECT
        CONCAT_WS(' ', p.first_name, p.last_name) AS sibling_name
    FROM persons p
    WHERE p.family_id = family_id_param
      AND p.person_type = 'sibling'
      AND p.deleted_at IS NULL
    ORDER BY p.age ASC, p.date_of_birth DESC
    LIMIT 3;

    -- Get adult leader positions for parents in the family
    SELECT
        p.email AS parent_email,
        al.positions AS leader_positions
    FROM persons p
    INNER JOIN adult_leaders al ON p.id = al.person_id
    WHERE p.family_id = family_id_param
      AND p.person_type = 'adult_leader'
      AND p.deleted_at IS NULL;
END//

DELIMITER ;

-- ============================================================================
-- Sample Data (for testing/development only - remove in production)
-- ============================================================================

-- Uncomment the following lines to insert sample data

/*
-- Sample families
INSERT INTO families (name, street_address, city, state, zip, primary_phone) VALUES
('Anderson Family', '123 Main St', 'Austin', 'TX', '78681', '512-555-1234'),
('Baker Family', '456 Oak Ave', 'Austin', 'TX', '78681', '512-555-5678'),
('Chen Family', '789 Elm Dr', 'Georgetown', 'TX', '78628', '512-555-9012');

-- Sample persons (parents)
INSERT INTO persons (family_id, person_type, first_name, last_name, email, phone) VALUES
(1, 'parent', 'John', 'Anderson', 'parent1@example.com', '512-555-1234'),
(1, 'parent', 'Jane', 'Anderson', 'parent2@example.com', '512-555-1235'),
(2, 'parent', 'Michael', 'Baker', 'parent3@example.com', '512-555-5678'),
(3, 'parent', 'Sarah', 'Chen', 'parent4@example.com', '512-555-9012');

-- Sample scouts
INSERT INTO persons (family_id, bsa_member_id, person_type, first_name, last_name, age, email) VALUES
(1, '12345678', 'scout', 'Alex', 'Anderson', 9, NULL),
(1, '12345679', 'scout', 'Sam', 'Anderson', 7, NULL),
(2, '23456789', 'scout', 'Charlie', 'Baker', 8, NULL),
(3, '34567890', 'scout', 'Dakota', 'Chen', 10, NULL);

-- Sample scout details
INSERT INTO scouts (person_id, grade, rank, den, registration_expiration_date, registration_status) VALUES
(5, 'Fourth Grade', 'Webelos', 'Den 4', '2025-12-31', 'Re-Registered'),
(6, 'Second Grade', 'Tiger', 'Den 2', '2026-11-30', 'New'),
(7, 'Third Grade', 'Wolf', 'Den 3', '2025-11-30', 'Re-Registered'),
(8, 'Fifth Grade', 'Webelos', 'Den 5', '2025-12-31', 'Re-Registered');

-- Sample adult leader (John Anderson is also Cubmaster)
INSERT INTO adult_leaders (person_id, positions, ypt_status, ypt_completion_date, ypt_expiration_date) VALUES
(1, '["Cubmaster", "Key 3 Delegate"]', 'Current', '2024-01-15', '2026-01-15');

-- Update person type for John to adult_leader
UPDATE persons SET person_type = 'adult_leader' WHERE id = 1;

-- Sample sibling
INSERT INTO persons (family_id, person_type, first_name, last_name, age) VALUES
(1, 'sibling', 'Emma', 'Anderson', 5);

-- Sample user permissions (WordPress user IDs are examples)
INSERT INTO user_permissions (wordpress_user_id, person_id, role, granted_by) VALUES
(1, 1, 'admin', NULL),  -- John Anderson is admin
(2, 3, 'editor', 1),    -- Michael Baker is editor
(3, 4, 'viewer', 1);    -- Sarah Chen is viewer
*/

-- ============================================================================
-- Indexes for Performance Optimization
-- ============================================================================

-- Additional composite indexes for common queries

-- Find all scouts in a specific den
CREATE INDEX idx_scouts_den_expiration ON scouts(den, registration_expiration_date);

-- Find all persons by family and type
CREATE INDEX idx_persons_family_type ON persons(family_id, person_type);

-- Find expiring scouts quickly
CREATE INDEX idx_scouts_expiration_status ON scouts(registration_expiration_date, registration_status);

-- Find adult leaders by YPT expiration
CREATE INDEX idx_adult_leaders_ypt ON adult_leaders(ypt_expiration_date, ypt_status);

-- Search persons by name (full-text search - optional)
-- Uncomment if full-text search is needed
-- ALTER TABLE persons ADD FULLTEXT INDEX idx_fulltext_name (first_name, last_name, nickname);

-- ============================================================================
-- Database Triggers
-- ============================================================================

-- Trigger: Auto-update person.age when date_of_birth changes
DELIMITER //

CREATE TRIGGER trg_persons_calculate_age
BEFORE INSERT ON persons
FOR EACH ROW
BEGIN
    IF NEW.date_of_birth IS NOT NULL AND NEW.age IS NULL THEN
        SET NEW.age = TIMESTAMPDIFF(YEAR, NEW.date_of_birth, CURDATE());
    END IF;
END//

CREATE TRIGGER trg_persons_calculate_age_update
BEFORE UPDATE ON persons
FOR EACH ROW
BEGIN
    IF NEW.date_of_birth IS NOT NULL AND NEW.date_of_birth != OLD.date_of_birth THEN
        SET NEW.age = TIMESTAMPDIFF(YEAR, NEW.date_of_birth, CURDATE());
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- Database Functions
-- ============================================================================

-- Function: Get days until expiration for a scout
DELIMITER //

CREATE FUNCTION fn_days_until_expiration(expiration_date DATE)
RETURNS INT
DETERMINISTIC
BEGIN
    IF expiration_date IS NULL THEN
        RETURN NULL;
    END IF;
    RETURN DATEDIFF(expiration_date, CURDATE());
END//

DELIMITER ;

-- Function: Format person's full name
DELIMITER //

CREATE FUNCTION fn_format_full_name(
    prefix_param VARCHAR(10),
    first_name_param VARCHAR(100),
    middle_name_param VARCHAR(100),
    last_name_param VARCHAR(100),
    suffix_param VARCHAR(10)
)
RETURNS VARCHAR(255)
DETERMINISTIC
BEGIN
    RETURN TRIM(CONCAT_WS(' ',
        prefix_param,
        first_name_param,
        middle_name_param,
        last_name_param,
        suffix_param
    ));
END//

DELIMITER ;

-- Function: Format phone number to standard format (512-555-1234)
DELIMITER //

CREATE FUNCTION fn_format_phone(phone_param VARCHAR(20))
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    DECLARE cleaned VARCHAR(20);

    -- Remove all non-digit characters
    SET cleaned = REGEXP_REPLACE(phone_param, '[^0-9]', '');

    -- Format as XXX-XXX-XXXX (assuming 10-digit US phone)
    IF LENGTH(cleaned) = 10 THEN
        RETURN CONCAT(
            SUBSTRING(cleaned, 1, 3), '-',
            SUBSTRING(cleaned, 4, 3), '-',
            SUBSTRING(cleaned, 7, 4)
        );
    ELSEIF LENGTH(cleaned) = 11 AND LEFT(cleaned, 1) = '1' THEN
        -- Handle 1-XXX-XXX-XXXX format
        RETURN CONCAT(
            SUBSTRING(cleaned, 2, 3), '-',
            SUBSTRING(cleaned, 5, 3), '-',
            SUBSTRING(cleaned, 8, 4)
        );
    ELSE
        -- Return original if not standard format
        RETURN phone_param;
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- Cleanup and Maintenance Queries
-- ============================================================================

-- Query: Find orphaned persons (no family assignment)
-- SELECT * FROM persons WHERE family_id IS NULL AND deleted_at IS NULL;

-- Query: Find duplicate persons (same first + last name, similar age)
-- SELECT
--     first_name,
--     last_name,
--     age,
--     COUNT(*) as duplicate_count
-- FROM persons
-- WHERE deleted_at IS NULL
-- GROUP BY first_name, last_name, age
-- HAVING COUNT(*) > 1;

-- Query: Find scouts expiring in next 30 days
-- SELECT * FROM v_scouts_with_details
-- WHERE days_until_expiration BETWEEN 0 AND 30
-- ORDER BY days_until_expiration ASC;

-- Query: Find adult leaders with expiring YPT (within 90 days)
-- SELECT * FROM v_adult_leaders_with_details
-- WHERE days_until_ypt_expiration BETWEEN 0 AND 90
-- ORDER BY days_until_ypt_expiration ASC;

-- Query: Archive old audit logs (keep only last 365 days)
-- DELETE FROM audit_logs WHERE created_at < DATE_SUB(CURDATE(), INTERVAL 365 DAY);

-- Query: Archive old sync logs (keep only last 365 days)
-- DELETE FROM sync_logs WHERE created_at < DATE_SUB(CURDATE(), INTERVAL 365 DAY);

-- ============================================================================
-- Database Permissions (for production deployment)
-- ============================================================================

-- Create dedicated database user for ScoutDB application
-- GRANT SELECT, INSERT, UPDATE, DELETE ON scoutdb.* TO 'scoutdb_app'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
-- FLUSH PRIVILEGES;

-- Create read-only user for WordPress database (for authentication)
-- GRANT SELECT ON wordpress.wp_users TO 'scoutdb_app'@'localhost';
-- GRANT SELECT ON wordpress.wp_usermeta TO 'scoutdb_app'@'localhost';
-- FLUSH PRIVILEGES;

-- ============================================================================
-- End of Database Schema
-- ============================================================================

-- Verify installation
SELECT 'Database schema created successfully!' AS status;
SELECT
    COUNT(*) AS table_count,
    (SELECT COUNT(*) FROM information_schema.views WHERE table_schema = DATABASE()) AS view_count,
    (SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema = DATABASE() AND routine_type = 'PROCEDURE') AS procedure_count,
    (SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema = DATABASE() AND routine_type = 'FUNCTION') AS function_count
FROM information_schema.tables
WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE';
