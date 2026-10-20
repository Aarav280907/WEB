-- ============================================================================
-- StudentHub Portal - remember-me tokens (Practical 10, Advanced extension)
-- Only a SHA-256 HASH of the random token is stored, so a stolen database
-- backup cannot be used to log in.  Not a JWT: just a random value.
-- ============================================================================
USE studenthub;
DROP TABLE IF EXISTS remember_tokens;
CREATE TABLE remember_tokens (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     INT UNSIGNED NOT NULL,
  selector    CHAR(24)     NOT NULL,          -- public part, used to find the row
  token_hash  CHAR(64)     NOT NULL,          -- sha256 of the secret part stored in the cookie
  expires_at  DATETIME     NOT NULL,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_selector (selector),
  KEY idx_remember_user (user_id),
  CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;
