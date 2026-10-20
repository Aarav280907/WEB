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

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `studenthub` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `studenthub`;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity` varchar(30) NOT NULL DEFAULT '',
  `entity_id` int(10) unsigned DEFAULT NULL,
  `details` varchar(500) NOT NULL DEFAULT '',
  `ip_address` varchar(45) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_created` (`created_at`),
  KEY `idx_audit_user` (`user_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `category` varchar(30) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` varchar(30) NOT NULL DEFAULT '',
  `venue` varchar(120) NOT NULL DEFAULT '',
  `organizer` varchar(120) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `poster` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_events_date` (`event_date`),
  KEY `idx_events_category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES
(1,'CHARUSAT National Tech Fest & Hackathon 2026','Tech Fest','2026-10-25','09:00 AM - 06:00 PM','Central Auditorium & Computer Center','Department of Computer Engineering','Annual national-level 24-hour hackathon with tracks in Web Development, AI/ML, Cloud Computing, and Cybersecurity. Exciting cash prizes and internship offers.','open','849267c34b2312b0ca3433c6.jpg','2026-10-19 06:06:10','2026-10-19 06:06:10'),
(2,'Mid-Semester Examination Schedule - Semester 3 & 4','Examination','2026-10-28','10:30 AM - 01:30 PM','FTE Examination Halls (Block A & B)','Office of Controller of Examinations','Official mid-term examination timetable for all B.Tech engineering branches. Students must carry valid university identity cards and exam admit slips.','closed',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10'),
(3,'Hands-on Workshop on Cloud Computing & Kubernetes','Workshop','2026-11-02','02:00 PM - 05:00 PM','Advanced Computing Lab (Lab 402)','Google Developer Student Club (GDSC)','Interactive technical workshop covering Docker containerization, Kubernetes cluster orchestration, and Google Cloud deployment workflows.','closed',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10'),
(4,'AI & Generative Deep Learning Bootcamp','Tech Fest','2026-11-05','10:00 AM - 04:30 PM','Seminar Hall 1, FTE Building','AI & Data Science Student Chapter','Deep dive into Large Language Models, Prompt Engineering, Retrieval-Augmented Generation (RAG), and building multimodal AI applications.','open','fcaab2971793d7a1ee42bfb0.jpg','2026-10-19 06:06:10','2026-10-19 06:06:10'),
(5,'Annual Inter-College Sports Meet - Spardha 2026','Sports','2026-11-10','08:00 AM - 05:00 PM','University Sports Complex & Grounds','CHARUSAT Sports Council','Annual university sports tournament featuring Cricket, Football, Volleyball, Basketball, Athletics, and indoor games. Trophies for winning college departments.','closed',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10'),
(6,'Campus Placement Drive & Internship Fair 2026','Career','2026-11-15','09:30 AM - 05:30 PM','Training & Placement Cell (T&P Cell)','University Industry Interaction Cell','Recruitment drive by leading IT companies and MNCs for software engineering, cloud architecture, QA engineering, and product analyst roles.','open','f388e4787b4444de2cbf8636.jpg','2026-10-19 06:06:10','2026-10-19 06:06:10'),
(7,'Annual Cultural Festival - Vrund 2026','Cultural','2026-11-20','05:00 PM - 10:00 PM','University Open Air Theatre','Student Cultural Committee','Grand cultural evening celebrating music, drama, traditional dance performances, fashion walk, and live musical concert by renowned artists.','closed',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10'),
(8,'Cybersecurity & Ethical Hacking Hands-on Training','Workshop','2026-11-24','11:00 AM - 03:00 PM','Network Security Lab (Lab 205)','Department of Information Technology','Practical session on web application vulnerabilities (OWASP Top 10), penetration testing techniques, packet sniffing, and defensive security measures.','closed',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10'),
(9,'Web Development Frameworks Lab Practical Evaluation','Academic','2026-11-28','09:00 AM - 01:00 PM','Web Technology Lab (Lab 301)','Faculty of Technology & Engineering','Continuous evaluation and code walkthrough for semester laboratory practicals, covering semantic HTML5, CSS Grid/Flexbox, DOM, and Fetch API.','closed',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10'),
(10,'Alumni Mentorship & Higher Education Webinar','Career','2026-12-02','04:00 PM - 06:00 PM','Virtual Meet (Google Meet / YouTube Live)','CHARUSAT Alumni Association','Interactive panel discussion with alumni working at top global tech firms, sharing guidance on GRE/TOEFL preparation, MS admissions, and tech careers.','closed',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10'),
(11,'Cross-Platform Mobile App Development with Flutter','Workshop','2026-12-06','01:30 PM - 04:30 PM','Mobile Computing Lab (Lab 108)','ACM Student Chapter','Step-by-step masterclass on building responsive Android and iOS apps using Flutter and Dart, including REST API integration and state management.','open',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10'),
(12,'Final Year Capstone Project Innovation Fair','Academic','2026-12-10','10:00 AM - 04:00 PM','FTE Exhibition Gallery','Project Review Committee','Showcase of innovative multidisciplinary engineering prototypes and software solutions evaluated by industry experts and academic judges.','closed',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10'),
(13,'University Table Tennis & Badminton Championship','Sports','2026-12-14','02:00 PM - 07:00 PM','Indoor Sports Complex','Physical Education Department','Singles and doubles knockout tournament for undergraduate and postgraduate students. Certificates and medals for winners and runners-up.','closed',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10'),
(14,'End-Semester Examination Hall Ticket & Seating Plan','Examination','2026-12-18','09:00 AM - 12:00 PM','StudentHub Portal / Examination Cell','Office of the Registrar','Eligible students with minimum 80% attendance can generate and download digital examination hall tickets for university end-semester exams.','closed',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10'),
(15,'Open Source Contribution & Git/GitHub Masterclass','Workshop','2026-12-22','10:30 AM - 01:30 PM','Software Engineering Lab (Lab 202)','Open Source Club CHARUSAT','Hands-on guide to version control, branching strategies, creating pull requests, writing clean documentation, and participating in global open source events.','open','9f034d34ab25b8d7cfef8045.jpg','2026-10-19 06:06:10','2026-10-19 06:06:10'),
(16,'IoT & Embedded Systems Robotics Showcase','Tech Fest','2026-12-26','11:00 AM - 05:00 PM','Robotics & Automation Laboratory','Robotics & Electronics Club','Line-follower robots, autonomous drones, smart agriculture IoT devices, and home automation systems demonstration by student engineering teams.','closed',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10'),
(17,'Annual Blood Donation & Community Health Camp','Social','2026-12-29','09:00 AM - 03:00 PM','Charusat Hospital & Health Center','National Service Scheme (NSS)','Voluntary blood donation camp organized in collaboration with Red Cross Society. Free general health checkup and donor certificate provided.','closed',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10'),
(18,'Student Startup & Innovation Pitch Competition','Career','2027-01-05','10:00 AM - 03:30 PM','University Innovation & Incubation Center (EDC)','Student Startup and Innovation Policy (SSIP)','Present early-stage startup ideas to angel investors and industry leaders. Seed funding grants and incubation mentorship available for winning teams.','open',NULL,'2026-10-19 06:06:10','2026-10-19 06:06:10');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `registrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int(10) unsigned NOT NULL,
  `event_id` int(10) unsigned NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_registration` (`student_id`,`event_id`),
  KEY `idx_reg_event` (`event_id`),
  CONSTRAINT `fk_reg_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reg_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `registrations` WRITE;
/*!40000 ALTER TABLE `registrations` DISABLE KEYS */;
INSERT INTO `registrations` VALUES
(1,1,8,'2026-06-21 19:29:00'),
(2,1,11,'2026-09-23 16:16:00'),
(3,1,18,'2026-10-13 18:19:00'),
(4,4,8,'2026-10-26 18:32:00'),
(5,4,15,'2026-09-26 14:27:00'),
(6,4,18,'2026-08-10 12:00:00'),
(7,5,1,'2026-05-05 18:56:00'),
(8,7,3,'2026-10-15 20:33:00'),
(9,8,1,'2026-09-12 11:16:00'),
(10,8,16,'2026-09-15 16:49:00'),
(11,9,3,'2026-09-26 14:35:00'),
(12,9,18,'2026-05-27 12:20:00'),
(13,10,15,'2026-09-26 09:28:00'),
(14,11,4,'2026-10-09 15:59:00'),
(15,12,3,'2026-07-14 19:30:00'),
(16,12,8,'2026-06-21 12:57:00'),
(17,13,16,'2026-09-12 09:12:00'),
(18,14,4,'2026-09-05 15:58:00'),
(19,14,16,'2026-05-03 08:28:00'),
(20,15,6,'2026-10-18 09:39:00'),
(21,16,4,'2026-08-08 09:39:00'),
(22,16,6,'2026-05-25 16:17:00'),
(23,16,8,'2026-09-05 08:20:00'),
(24,16,11,'2026-10-19 20:59:00'),
(25,17,4,'2026-09-23 19:08:00'),
(26,17,16,'2026-10-08 15:37:00'),
(27,18,16,'2026-07-20 19:32:00'),
(28,19,8,'2026-08-03 14:42:00'),
(29,20,3,'2026-07-05 20:50:00'),
(30,20,4,'2026-10-10 16:26:00'),
(31,20,8,'2026-09-26 10:33:00'),
(32,21,4,'2026-08-13 08:06:00'),
(33,21,15,'2026-09-03 08:53:00'),
(34,22,18,'2026-09-21 13:59:00');
/*!40000 ALTER TABLE `registrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `remember_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `remember_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `selector` char(24) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_selector` (`selector`),
  KEY `idx_remember_user` (`user_id`),
  CONSTRAINT `fk_remember_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `remember_tokens` WRITE;
/*!40000 ALTER TABLE `remember_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `remember_tokens` ENABLE KEYS */;
UNLOCK TABLES;
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
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fullname` varchar(60) NOT NULL,
  `student_id` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` char(10) NOT NULL,
  `course` varchar(50) NOT NULL,
  `year` varchar(30) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_student_id` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Portal Administrator','ADMIN001','admin@charusat.edu.in','9000000001','Computer Engineering','4th Year (Semester 7/8)','Other','$2y$10$tPZCH6pxqLzMXN3pluztNO1z64EbqqFXiJFWhQDzhFyb8Px16Eq7a','admin','active',NULL,'2026-10-19 06:06:10'),
(2,'Aarav Hiteshkumar Patel','25DCE072','25dce072@charusat.edu.in','8469012345','Computer Engineering','2nd Year (Semester 3/4)','Male','$2y$10$5Ry6LCIJ4Gljt2echcdyUOcfGQXYaNiI4jXc/O/aBC4eoxxub5AvO','student','active',NULL,'2026-10-19 06:06:10'),
(3,'Diya Sanjay Shah','25DCE015','25dce015@charusat.edu.in','9825123456','Computer Engineering','2nd Year (Semester 3/4)','Female','$2y$10$5Ry6LCIJ4Gljt2echcdyUOcfGQXYaNiI4jXc/O/aBC4eoxxub5AvO','student','active',NULL,'2026-10-19 06:06:10'),
(4,'Rohan Mukesh Mehta','25DIT034','25dit034@charusat.edu.in','9724567890','Information Technology','2nd Year (Semester 3/4)','Male','$2y$10$5Ry6LCIJ4Gljt2echcdyUOcfGQXYaNiI4jXc/O/aBC4eoxxub5AvO','student','inactive',NULL,'2026-10-19 06:06:10');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

