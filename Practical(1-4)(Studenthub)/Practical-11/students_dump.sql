/*M!999999\- enable the sandbox mode */ 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_event_registration_count` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_event_registration_count`()
BEGIN
    SELECT e.id, e.title, COUNT(r.id) AS total_registrations
    FROM events e
    LEFT JOIN registrations r ON r.event_id = e.id
    GROUP BY e.id, e.title
    ORDER BY total_registrations DESC, e.title;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_register_student_for_event` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_register_student_for_event`(
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` varchar(15) NOT NULL,
  `fullname` varchar(60) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` char(10) NOT NULL,
  `course` varchar(50) NOT NULL,
  `semester` tinyint(3) unsigned NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL DEFAULT 'Other',
  `sgpa` decimal(3,2) NOT NULL DEFAULT 0.00,
  `attendance` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_students_student_id` (`student_id`),
  UNIQUE KEY `uq_students_email` (`email`),
  KEY `idx_students_course` (`course`),
  CONSTRAINT `chk_students_semester` CHECK (`semester` between 1 and 8),
  CONSTRAINT `chk_students_sgpa` CHECK (`sgpa` between 0 and 10),
  CONSTRAINT `chk_students_attendance` CHECK (`attendance` between 0 and 100)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES
(1,'25DCE072','Aarav Hiteshkumar Patel','25dce072@charusat.edu.in','8469012345','Computer Engineering',3,'Male',8.20,85,'active','2026-10-19 06:06:10'),
(2,'25DCE015','Diya Sanjay Shah','25dce015@charusat.edu.in','9825123456','Computer Engineering',3,'Female',9.15,92,'active','2026-10-19 06:06:10'),
(3,'25DIT034','Rohan Mukesh Mehta','25dit034@charusat.edu.in','9724567890','Information Technology',3,'Male',8.65,88,'active','2026-10-19 06:06:10'),
(4,'25DAI008','Ananya Rajesh Joshi','25dai008@charusat.edu.in','9426011223','AI & Data Science',3,'Female',9.40,95,'active','2026-10-19 06:06:10'),
(5,'25DEC021','Kavya Nilesh Desai','25dec021@charusat.edu.in','9898033445','Electronics & Communication',3,'Female',7.95,82,'active','2026-10-19 06:06:10'),
(6,'24DCE104','Manan Bharat Dave','24dce104@charusat.edu.in','9601255667','Computer Engineering',5,'Male',8.80,89,'active','2026-10-19 06:06:10'),
(7,'24DIT056','Pooja Jignesh Trivedi','24dit056@charusat.edu.in','9909977889','Information Technology',5,'Female',8.45,84,'active','2026-10-19 06:06:10'),
(8,'24DAI019','Siddharth Chetan Vora','24dai019@charusat.edu.in','9712344556','AI & Data Science',5,'Male',9.05,90,'active','2026-10-19 06:06:10'),
(9,'25DME012','Yash Pravin Panchal','25dme012@charusat.edu.in','9408812349','Mechanical Engineering',3,'Male',7.75,81,'active','2026-10-19 06:06:10'),
(10,'25DCL029','Tanvi Paresh Bhatt','25dcl029@charusat.edu.in','9879166778','Civil Engineering',3,'Female',8.10,86,'active','2026-10-19 06:06:10'),
(11,'23DCE045','Karan Sunil Parekh','23dce045@charusat.edu.in','9824088990','Computer Engineering',7,'Male',9.30,93,'active','2026-10-19 06:06:10'),
(12,'23DIT088','Riya Jayesh Soni','23dit088@charusat.edu.in','9727711335','Information Technology',7,'Female',8.70,87,'active','2026-10-19 06:06:10'),
(13,'26DCE002','Harshil Kamlesh Raval','26dce002@charusat.edu.in','9974055443','Computer Engineering',1,'Male',8.50,94,'active','2026-10-19 06:06:10'),
(14,'26DAI041','Prachi Dhaval Solanki','26dai041@charusat.edu.in','9879522110','AI & Data Science',1,'Female',8.90,96,'active','2026-10-19 06:06:10'),
(15,'25DEC067','Darshan Vipul Rana','25dec067@charusat.edu.in','9428077665','Electronics & Communication',3,'Male',7.60,80,'active','2026-10-19 06:06:10'),
(16,'24DCE089','Meera Ashok Chauhan','24dce089@charusat.edu.in','9687099887','Computer Engineering',5,'Female',9.25,91,'active','2026-10-19 06:06:10'),
(17,'25DIT071','Neel Bipin Thaker','25dit071@charusat.edu.in','9825533221','Information Technology',3,'Male',8.35,83,'active','2026-10-19 06:06:10'),
(18,'23DAI033','Vidhi Shailesh Barot','23dai033@charusat.edu.in','9714066554','AI & Data Science',7,'Female',9.55,97,'active','2026-10-19 06:06:10'),
(19,'25DCE101','Isha Mahesh Kapadia','25dce101@charusat.edu.in','9824011101','Computer Engineering',3,'Female',8.75,90,'active','2026-10-19 06:06:10'),
(20,'25DME044','Jay Dilip Gajjar','25dme044@charusat.edu.in','9824011102','Mechanical Engineering',3,'Male',7.40,78,'active','2026-10-19 06:06:10'),
(21,'24DCL012','Nidhi Hemant Amin','24dcl012@charusat.edu.in','9824011103','Civil Engineering',5,'Female',8.05,88,'active','2026-10-19 06:06:10'),
(22,'26DIT019','Om Rakesh Pandya','26dit019@charusat.edu.in','9824011104','Information Technology',1,'Male',7.95,91,'active','2026-10-19 06:06:10');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

