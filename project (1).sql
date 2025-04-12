

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `club` (
  `club_id` varchar(10) NOT NULL,
  `club_name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `club` (`club_id`, `club_name`) VALUES
('1', 'Robotics Club'),
('2', 'Social Service Club'),
('3', 'Game Club');

CREATE TABLE `club_events` (
  `Event_id` varchar(5) NOT NULL,
  `Event_name` varchar(40) NOT NULL,
  `Event_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `club_events` (`Event_id`, `Event_name`, `Event_date`) VALUES
('1', 'Skii Adventure', '2025-06-12'),
('2', 'MS Word Basic Skill', '2025-07-19'),
('3', 'Typorgraphiy', '2025-08-03'),
('4', ' Basic Video Editing', '2025-09-15'),
('5', 'Higher Studies', '2025-10-27');



CREATE TABLE `club_leads` (
  `Lead_id` varchar(10) NOT NULL,
  `password` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `club_leads` (`Lead_id`, `password`) VALUES
('1', 'ABC');


CREATE TABLE `club_membership` (
  `Member_id` varchar(10) NOT NULL,
  `Member_Type` varchar(10) NOT NULL,
  `Membership_fee` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `club_registration` (
  `name` varchar(30) NOT NULL,
  `Status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `executive` (
  `Position` varchar(15) NOT NULL,
  `Name` varchar(35) NOT NULL,
  `Start_date` date NOT NULL,
  `End_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `executive` (`Position`, `Name`, `Start_date`, `End_date`) VALUES
('Chair', 'Fahim', '2025-01-01', '2025-12-31'),
('Secretary', 'Sadman', '2025-01-01', '2025-12-31'),
('Treasurer', 'Salim', '2025-01-01', '2025-12-31'),
('Vice Chair', 'Omar', '2025-01-01', '2025-12-31'),
('Webmaster', 'Asif', '2025-01-01', '2025-12-31');


CREATE TABLE `general_member` (
  `Member_id` varchar(10) NOT NULL,
  `password` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `general_member` (`Member_id`, `password`) VALUES
('member123', 'pass456');


CREATE TABLE `member` (
  `U_id` varchar(10) NOT NULL,
  `password` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `member` (`U_id`, `password`) VALUES
('123', '123');



CREATE TABLE `member_records` (
  `Record_id` varchar(10) NOT NULL,
  `Activity` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `recruitment` (
  `f_name` varchar(10) NOT NULL,
  `m_name` varchar(10) NOT NULL,
  `l_name` varchar(10) NOT NULL,
  `dob` date NOT NULL,
  `age` int(4) NOT NULL,
  `club_id` varchar(10) NOT NULL,
  `mobile` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `recruitment` (`f_name`, `m_name`, `l_name`, `dob`, `age`, `club_id`, `mobile`) VALUES
('	Karim', '	Rahim', '	Sami', '1998-05-15', 26, '1', '	123-456-7890'),
('Siam', '	Ahmed', '	Siam', '2000-09-22', 24, '2', '	234-567-8901'),
('	Md', '	Abdur', '	Rahim', '2001-07-28', 23, '	2', '	567-890-1234'),
('Sakib', '	al', '		Hasan', '1997-12-03', 27, '3', '345-678-9012'),
('	Md', '	SK', 'Rasel', '1999-03-10', 26, '1', '	456-789-0123');

ALTER TABLE `club`
  ADD PRIMARY KEY (`club_id`);


ALTER TABLE `club_events`
  ADD PRIMARY KEY (`Event_id`);


ALTER TABLE `club_leads`
  ADD PRIMARY KEY (`Lead_id`);


--
ALTER TABLE `club_membership`
  ADD PRIMARY KEY (`Member_id`);


ALTER TABLE `executive`
  ADD PRIMARY KEY (`Position`);


ALTER TABLE `member`
  ADD PRIMARY KEY (`U_id`);


ALTER TABLE `member_records`
  ADD PRIMARY KEY (`Record_id`);
COMMIT;

