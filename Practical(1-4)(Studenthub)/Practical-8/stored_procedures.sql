-- ============================================================================
-- StudentHub Portal - Stored procedures (Practical 8, Advanced extension)
-- Import after schema.sql and seed.sql:   mysql -u root studenthub < stored_procedures.sql
-- ============================================================================
USE studenthub;
DROP PROCEDURE IF EXISTS sp_register_student_for_event;
DROP PROCEDURE IF EXISTS sp_event_registration_count;

DELIMITER $$

-- Registers a student for an event; refuses closed events and duplicate registrations.
-- result_code: 0 = registered, 1 = event not found / closed, 2 = already registered
CREATE PROCEDURE sp_register_student_for_event(
    IN  p_student_id INT UNSIGNED,
    IN  p_event_id   INT UNSIGNED,
    OUT result_code  INT)
BEGIN
    DECLARE v_status VARCHAR(10);
    SELECT status INTO v_status FROM events WHERE id = p_event_id;

    IF v_status IS NULL OR v_status <> 'open' THEN
        SET result_code = 1;
    ELSEIF EXISTS (SELECT 1 FROM registrations WHERE student_id = p_student_id AND event_id = p_event_id) THEN
        SET result_code = 2;
    ELSE
        INSERT INTO registrations (student_id, event_id) VALUES (p_student_id, p_event_id);
        SET result_code = 0;
    END IF;
END$$

-- Number of registrations for every event (events without registrations show 0)
CREATE PROCEDURE sp_event_registration_count()
BEGIN
    SELECT e.id, e.title, COUNT(r.id) AS total_registrations
    FROM events e
    LEFT JOIN registrations r ON r.event_id = e.id
    GROUP BY e.id, e.title
    ORDER BY total_registrations DESC, e.title;
END$$

DELIMITER ;
