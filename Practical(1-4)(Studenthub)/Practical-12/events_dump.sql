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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

