-- ============================================================================
-- StudentHub Portal - users and audit_logs tables (Practical 9)
-- Import after Practical-8/schema.sql:   mysql -u root studenthub < users.sql
-- Passwords are stored ONLY as password_hash() values (bcrypt) - never as plain text.
-- Demo logins:  admin@charusat.edu.in / Admin@123   |   25dce072@charusat.edu.in / Student@123
-- ============================================================================
USE studenthub;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS remember_tokens;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fullname      VARCHAR(60)  NOT NULL,
  student_id    VARCHAR(15)  NOT NULL,
  email         VARCHAR(100) NOT NULL,
  mobile        CHAR(10)     NOT NULL,
  course        VARCHAR(50)  NOT NULL,
  year          VARCHAR(30)  NOT NULL,
  gender        ENUM('Male','Female','Other') NOT NULL,
  password_hash VARCHAR(255) NOT NULL,                       -- bcrypt hash (60 chars) - 255 leaves room for future algorithms
  role          ENUM('student','admin') NOT NULL DEFAULT 'student',
  status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
  last_login    DATETIME     NULL,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),                         -- the database itself blocks duplicate e-mails
  UNIQUE KEY uq_users_student_id (student_id)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     INT UNSIGNED NULL,                             -- NULL for anonymous actions (failed login ...)
  action      VARCHAR(50)  NOT NULL,
  entity      VARCHAR(30)  NOT NULL DEFAULT '',
  entity_id   INT UNSIGNED NULL,
  details     VARCHAR(500) NOT NULL DEFAULT '',
  ip_address  VARCHAR(45)  NOT NULL DEFAULT '',
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_created (created_at),
  KEY idx_audit_user (user_id),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Seed users (passwords are bcrypt hashes of Admin@123 / Student@123)
INSERT INTO users (fullname, student_id, email, mobile, course, year, gender, password_hash, role, status) VALUES
 ('Portal Administrator',     'ADMIN001', 'admin@charusat.edu.in',    '9000000001', 'Computer Engineering',  '4th Year (Semester 7/8)', 'Other', '$2y$10$tPZCH6pxqLzMXN3pluztNO1z64EbqqFXiJFWhQDzhFyb8Px16Eq7a', 'admin',   'active'),
 ('Aarav Hiteshkumar Patel',  '25DCE072', '25dce072@charusat.edu.in', '8469012345', 'Computer Engineering',  '2nd Year (Semester 3/4)', 'Male',  '$2y$10$5Ry6LCIJ4Gljt2echcdyUOcfGQXYaNiI4jXc/O/aBC4eoxxub5AvO',   'student', 'active'),
 ('Diya Sanjay Shah',         '25DCE015', '25dce015@charusat.edu.in', '9825123456', 'Computer Engineering',  '2nd Year (Semester 3/4)', 'Female','$2y$10$5Ry6LCIJ4Gljt2echcdyUOcfGQXYaNiI4jXc/O/aBC4eoxxub5AvO',   'student', 'active'),
 ('Rohan Mukesh Mehta',       '25DIT034', '25dit034@charusat.edu.in', '9724567890', 'Information Technology','2nd Year (Semester 3/4)', 'Male',  '$2y$10$5Ry6LCIJ4Gljt2echcdyUOcfGQXYaNiI4jXc/O/aBC4eoxxub5AvO',   'student', 'inactive');
