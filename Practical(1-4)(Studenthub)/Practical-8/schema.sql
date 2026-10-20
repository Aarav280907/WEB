-- ============================================================================
-- StudentHub Portal - Database Schema (Practical 8)
-- MySQL / MariaDB, InnoDB, utf8mb4
-- Tables: students, events, registrations
-- (users + audit_logs are added in Practical 9, remember_tokens in Practical 10)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS studenthub
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studenthub;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS students;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------------------
-- students : one row per enrolled student (managed by the admin in Practical 11)
-- --------------------------------------------------------------------------
CREATE TABLE students (
  id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  student_id  VARCHAR(15)      NOT NULL,                 -- roll number such as 25DCE072
  fullname    VARCHAR(60)      NOT NULL,
  email       VARCHAR(100)     NOT NULL,
  mobile      CHAR(10)         NOT NULL,
  course      VARCHAR(50)      NOT NULL,                 -- department
  semester    TINYINT UNSIGNED NOT NULL,
  gender      ENUM('Male','Female','Other') NOT NULL DEFAULT 'Other',
  sgpa        DECIMAL(3,2)     NOT NULL DEFAULT 0.00,
  attendance  TINYINT UNSIGNED NOT NULL DEFAULT 0,       -- percentage 0-100
  status      ENUM('active','inactive') NOT NULL DEFAULT 'active',   -- soft delete flag
  created_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_students_student_id (student_id),
  UNIQUE KEY uq_students_email (email),
  KEY idx_students_course (course),
  CONSTRAINT chk_students_semester   CHECK (semester BETWEEN 1 AND 8),
  CONSTRAINT chk_students_sgpa       CHECK (sgpa BETWEEN 0 AND 10),
  CONSTRAINT chk_students_attendance CHECK (attendance BETWEEN 0 AND 100)
) ENGINE=InnoDB;

-- --------------------------------------------------------------------------
-- events : campus events (managed by the admin in Practical 12)
-- --------------------------------------------------------------------------
CREATE TABLE events (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title        VARCHAR(150) NOT NULL,
  category     VARCHAR(30)  NOT NULL,
  event_date   DATE         NOT NULL,
  event_time   VARCHAR(30)  NOT NULL DEFAULT '',
  venue        VARCHAR(120) NOT NULL DEFAULT '',
  organizer    VARCHAR(120) NOT NULL DEFAULT '',
  description  TEXT         NULL,
  status       ENUM('open','closed') NOT NULL DEFAULT 'open',   -- open for registration or not
  poster       VARCHAR(255) NULL,                               -- uploaded poster file name
  created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_events_date (event_date),
  KEY idx_events_category (category)
) ENGINE=InnoDB;

-- --------------------------------------------------------------------------
-- registrations : resolves the many-to-many relation  students <-> events
-- --------------------------------------------------------------------------
CREATE TABLE registrations (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_id    INT UNSIGNED NOT NULL,        -- FK -> students.id
  event_id      INT UNSIGNED NOT NULL,        -- FK -> events.id
  registered_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_registration (student_id, event_id),    -- a student can register for an event only once
  KEY idx_reg_event (event_id),
  CONSTRAINT fk_reg_student FOREIGN KEY (student_id) REFERENCES students (id)
      ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_reg_event   FOREIGN KEY (event_id)   REFERENCES events (id)
      ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
