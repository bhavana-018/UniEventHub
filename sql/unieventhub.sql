-- ============================================================
-- UniEventHub - Complete Database Schema
-- Import this single file in phpMyAdmin
-- Includes: users, events, registrations, feedback,
--           notifications, messages
-- ============================================================

CREATE DATABASE IF NOT EXISTS unieventhub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE unieventhub;

-- ── 1. USERS ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(150) NOT NULL,
    email       VARCHAR(191) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('student','organizer','admin') NOT NULL DEFAULT 'student',
    department  VARCHAR(100) DEFAULT NULL,
    phone       VARCHAR(20)  DEFAULT NULL,
    profile_pic VARCHAR(255) DEFAULT NULL,
    is_active   TINYINT(1)   DEFAULT 1,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ── 2. EVENTS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS events (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id      INT NOT NULL,
    title             VARCHAR(255) NOT NULL,
    description       TEXT,
    category          ENUM('seminar','workshop','cultural','sports','technical','guest_lecture','other') DEFAULT 'other',
    venue             VARCHAR(255) DEFAULT NULL,
    event_date        DATE NOT NULL,
    start_time        TIME NOT NULL,
    end_time          TIME DEFAULT NULL,
    max_participants  INT  DEFAULT 100,
    registration_fee  DECIMAL(10,2) DEFAULT 0.00,
    poster_url        VARCHAR(255)  DEFAULT NULL,
    eligibility       TEXT,
    status            ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
    created_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── 3. REGISTRATIONS ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS registrations (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    event_id       INT NOT NULL,
    student_id     INT NOT NULL,
    payment_status ENUM('pending','paid','free') DEFAULT 'free',
    attendance     TINYINT(1) DEFAULT 0,
    registered_at  DATETIME   DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_reg (event_id, student_id),
    FOREIGN KEY (event_id)   REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id)  ON DELETE CASCADE
);

-- ── 4. FEEDBACK ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS feedback (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    event_id     INT NOT NULL,
    student_id   INT NOT NULL,
    rating       INT CHECK (rating BETWEEN 1 AND 5),
    comments     TEXT,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id)   REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id)  ON DELETE CASCADE
);

-- ── 5. NOTIFICATIONS ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT  NOT NULL,
    message    TEXT NOT NULL,
    is_read    TINYINT(1) DEFAULT 0,
    created_at DATETIME   DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notif_user   (user_id),
    INDEX idx_notif_unread (user_id, is_read)
);

-- ── 6. MESSAGES (1:1 Chat) ───────────────────────────────────
CREATE TABLE IF NOT EXISTS messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT  NOT NULL,
    receiver_id INT  NOT NULL,
    message     TEXT NOT NULL,
    is_read     TINYINT(1) DEFAULT 0,
    sent_at     DATETIME   DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_msg_sender   (sender_id),
    INDEX idx_msg_receiver (receiver_id),
    INDEX idx_msg_sent_at  (sent_at),
    INDEX idx_msg_convo    (sender_id, receiver_id)
);

-- ============================================================
-- AFTER IMPORTING:
-- Visit http://localhost/unieventhub/setup.php to create
-- default admin and organizer accounts.
-- DELETE setup.php immediately after running it!
-- ============================================================
