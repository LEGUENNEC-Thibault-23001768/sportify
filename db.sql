-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql-sportify.alwaysdata.net
-- Generation Time: Feb 14, 2025 at 09:21 AM
-- Server version: 10.11.8-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sportify_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `AVAILABILITY`
--

CREATE TABLE `AVAILABILITY` (
  `availability_id` int(11) NOT NULL,
  `coach_id` int(11) NOT NULL,
  `day_of_week` varchar(10) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `AVAILABILITY`
--

INSERT INTO `AVAILABILITY` (`availability_id`, `coach_id`, `day_of_week`, `start_time`, `end_time`) VALUES
(9, 11, 'Monday', '06:00:00', '09:00:00'),
(10, 11, 'Wednesday', '18:00:00', '21:00:00'),
(11, 11, 'Friday', '06:00:00', '09:00:00'),
(12, 12, 'Monday', '09:00:00', '12:00:00'),
(13, 12, 'Tuesday', '10:00:00', '14:00:00'),
(14, 12, 'Thursday', '09:00:00', '12:00:00'),
(15, 13, 'Tuesday', '07:00:00', '08:00:00'),
(16, 13, 'Thursday', '18:00:00', '19:00:00'),
(17, 13, 'Saturday', '08:00:00', '09:00:00'),
(18, 14, 'Monday', '16:00:00', '19:00:00'),
(19, 14, 'Wednesday', '17:00:00', '20:00:00'),
(20, 14, 'Friday', '16:00:00', '19:00:00'),
(21, 15, 'Monday', '10:00:00', '12:00:00'),
(22, 15, 'Wednesday', '14:00:00', '18:00:00'),
(23, 15, 'Friday', '12:00:00', '15:00:00'),
(24, 16, 'Tuesday', '15:00:00', '18:00:00'),
(25, 16, 'Thursday', '17:00:00', '20:00:00'),
(26, 16, 'Saturday', '14:00:00', '17:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `COACH`
--

CREATE TABLE `COACH` (
  `coach_id` int(11) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `specialty` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `experience` varchar(255) DEFAULT NULL,
  `certifications` varchar(255) NOT NULL,
  `achievements` varchar(255) NOT NULL,
  `qualities` varchar(155) NOT NULL,
  `creation_date` timestamp NULL DEFAULT current_timestamp(),
  `image` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `COACH`
--

INSERT INTO `COACH` (`coach_id`, `last_name`, `first_name`, `email`, `phone`, `specialty`, `description`, `experience`, `certifications`, `achievements`, `qualities`, `creation_date`, `image`) VALUES
(11, 'Prime', 'Mk', 'mkprime@example.com', '123456789', 'Boxe', 'Expert en boxe anglaise, Mk Prime vous apprendra à maîtriser la technique, la puissance et l’endurance. Ancien champion régional, il a un style pédagogique qui inspire confiance et discipline.', 'Plus de 7 ans d’expérience en boxe anglaise.', 'Certificat d\'entraîneur professionnel, Certification en préparation physique', 'Champion régional 2018, Médaille d\'argent aux championnats nationaux', 'Cardio, Motivation, Discipline, Stratégie, Endurance, Sens de l\'humour', '2024-12-31 15:35:14', 'https://i.postimg.cc/BnnXrG4v/mk-prime.png'),
(12, 'Peak', 'Axelle', 'axellepeak@example.com', '987654321', 'Tennis', 'Axelle est une joueuse passionnée avec plus de 6 ans d’expérience en compétition. Elle se spécialise dans le perfectionnement des services et des coups droits. Son énergie est contagieuse !', 'Plus de 6 ans d’expérience en compétition.', 'Certificat d\'entraîneur de tennis, Formation avancée en technique de service', 'Vainqueuse du tournoi national junior 2015, Finaliste du tournoi régional 2017', 'Patience, Rythme, Pédagogie, Energie, Concentration', '2024-12-31 15:35:14', 'https://i.postimg.cc/7YqqdM5r/Axelle.png'),
(13, 'Ocho', 'Sabrina', 'sabrinaocho@example.com', '1122334455', 'RPM', 'Reine du cardio et des sessions RPM, Sabrina transforme chaque entraînement en une véritable aventure énergétique. Son coaching est idéal pour ceux qui veulent se dépasser tout en s’amusant.', 'Plus de 4 ans d’expérience dans l’enseignement du RPM.', 'Certificat d\'entraîneur RPM, Formation avancée en coaching de groupe', 'Championne nationale de RPM 2020, Formateur certifié RPM', 'Créativité, Leadership, Energie, Positivité', '2024-12-31 15:35:14', 'https://i.postimg.cc/P5jBJtPt/sabrinaocho.png'),
(14, 'Forge', 'Kai', 'kaiforge@example.com', '2233445566', 'Basketball', 'Avec une carrière impressionnante dans les ligues semi-professionnelles, Kai Forge est votre coach idéal pour améliorer vos tirs, vos dribbles et votre jeu collectif.', 'Carrière dans les ligues semi-professionnelles pendant 5 ans.', 'Certificat d\'entraîneur de basketball, Formation en développement des jeunes talents', 'MVP du championnat régional 2017, Vainqueur de la coupe nationale 2018', 'Compétitif, Pédagogie, Leadership, Tactique, Esprit d\'équipe', '2024-12-31 15:35:14', 'https://i.postimg.cc/g0YFSYWM/horszone.png'),
(15, 'Deter', 'Max', 'maxdeter@example.com', '3344556677', 'Musculation', 'Max est un passionné de musculation avec une approche centrée sur la technique et la sécurité. Il crée des programmes personnalisés pour tous les niveaux et vous aidera à atteindre vos objectifs.', 'Plus de 2 ans d’expérience en musculation et coaching physique.', 'Certificat d\'entraîneur de musculation, Diplôme en sciences du sport', 'Champion de musculation 2019, Coach de l\'année 2021', 'Mentalité, Rigoureux, Patient, Motivation', '2024-12-31 15:35:14', 'https://i.postimg.cc/Z5n5fFxB/determax.png'),
(16, 'Lafitte', 'Inox', 'inoxlafitte@example.com', '5566778899', 'Football', 'Inox est un ancien joueur professionnel qui combine stratégie et technique pour vous faire progresser. Sa passion pour le football est un véritable moteur pour ses élèves.', 'Ancien joueur professionnel avec 3 ans de carrière.', 'Certificat d\'entraîneur de football, Formation en stratégie et tactique de jeu', 'Champion national en 2015, Membre de l\'équipe nationale 2012-2014', 'Endurance, Stratégie, Passion, Discipline, Leadership', '2024-12-31 15:35:14', 'https://i.postimg.cc/x1PTTpj4/Capture-d-cran-2024-12-12-164241.png');

-- --------------------------------------------------------

--
-- Table structure for table `COURT`
--

CREATE TABLE `COURT` (
  `court_id` int(11) NOT NULL,
  `court_name` varchar(100) DEFAULT NULL,
  `activity_type` varchar(100) DEFAULT NULL,
  `max_capacity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `COURT`
--

INSERT INTO `COURT` (`court_id`, `court_name`, `activity_type`, `max_capacity`) VALUES
(1, 'Football', 'FOOTBALL', 22),
(2, 'Basketball', 'BASKETBALL', 10),
(3, 'RPM', 'RPM', 8),
(4, 'Musculation', 'MUSCULATION', 12),
(5, 'Boxe', 'BOXE', 12),
(6, 'Tennis', 'TENNIS', 4);

-- --------------------------------------------------------

--
-- Table structure for table `COURT_RESERVATION`
--

CREATE TABLE `COURT_RESERVATION` (
  `reservation_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `court_id` int(11) DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `COURT_RESERVATION`
--

INSERT INTO `COURT_RESERVATION` (`reservation_id`, `member_id`, `court_id`, `reservation_date`, `start_time`, `end_time`, `event_id`, `team_id`) VALUES
(76, 11, 1, '2025-01-15', '18:00:00', '19:00:00', NULL, NULL),
(78, 39, 2, '2025-01-30', '14:00:00', '15:00:00', NULL, NULL),
(79, 39, 5, '2025-01-30', '14:00:00', '15:00:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `DAILY_ACTIVITY`
--

CREATE TABLE `DAILY_ACTIVITY` (
  `activity_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `activity_description` text NOT NULL,
  `completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `EVENTS`
--

CREATE TABLE `EVENTS` (
  `event_id` int(11) NOT NULL,
  `event_name` varchar(100) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `description` text DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `EVENTS`
--

INSERT INTO `EVENTS` (`event_id`, `event_name`, `event_date`, `start_time`, `end_time`, `description`, `max_participants`, `created_by`, `location`) VALUES
(22, 'New event', '2024-12-10', '06:52:00', '08:54:00', '12', 2, 3, 'Californie'),
(67, 'k', '2025-01-07', '10:20:00', '10:21:00', '', 5, 19, 'Tennis'),
(68, 'diddy party', '2025-01-09', '16:55:00', '16:59:00', '', 7, 3, 'Football'),
(69, 'diddy party', '2025-01-10', '14:35:00', '15:31:00', 'C\'est la fête', 5, 3, 'Tennis'),
(70, 'azea', '2025-01-17', '08:00:00', '09:00:00', 'earar', 15, 3, 'RPM');

-- --------------------------------------------------------

--
-- Table structure for table `EVENT_INVITATIONS`
--

CREATE TABLE `EVENT_INVITATIONS` (
  `invitation_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `EVENT_INVITATIONS`
--

INSERT INTO `EVENT_INVITATIONS` (`invitation_id`, `event_id`, `email`, `token`, `created_at`) VALUES
(4, 22, 'caca@yopmail.com', 'bf6499f1325a8fa33dee400400920a5186baf4f09c225fcae02caa223d80b298', '2024-12-11 13:22:53'),
(5, 22, 'qsdqf@yopmail.com', 'dabf1f273a52bcd654bf1333570af8efead301de435c9013cb309a2e6de9225c', '2024-12-11 13:34:37'),
(6, 22, 'cacacqsf@yopmail.com', 'f11ad6b935a6cf63ec0b5d3e9501f071cc472ed6b57046160eaf4f6b930088d4', '2024-12-11 14:11:43'),
(7, 22, 'qsfqsf@yopmail.com', 'd1616ad6d744349fa2552edb34e67ffe6870c706d1e72b0832939b57b283aa98', '2024-12-11 14:12:12');

-- --------------------------------------------------------

--
-- Table structure for table `EVENT_REGISTRATION`
--

CREATE TABLE `EVENT_REGISTRATION` (
  `registration_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `MEMBER`
--

CREATE TABLE `MEMBER` (
  `member_id` int(11) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `creation_date` timestamp NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `status` enum('membre','coach','admin') NOT NULL DEFAULT 'membre',
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `MEMBER`
--

INSERT INTO `MEMBER` (`member_id`, `last_name`, `first_name`, `email`, `password`, `birth_date`, `address`, `phone`, `creation_date`, `is_verified`, `verification_token`, `google_id`, `reset_token`, `reset_token_expiry`, `status`, `profile_picture`) VALUES
(4, 'DefaultLast', 'neuil', 'oni1337@protonmail.com', '$2y$10$QsB0OYRngb2th3tI8Kvnq.Hrsq7cj.vw4pLChc8b2mK29yFd6WPgK', '1988-06-12', '', '', '2024-10-09 08:39:02', 0, NULL, NULL, NULL, NULL, 'coach', NULL),
(9, 'ze', 'Oni', 'arkxnaytb@gmail.com', 'GOOGLE_USER', '2005-04-26', '', '', '2024-10-14 11:54:53', 0, '9de47402549c4bf26af3ec950a425e2cfd894099510f27f9b565fcaaac353358', NULL, NULL, NULL, 'coach', NULL),
(11, 'ROBBERS', 'marc', 'sportify@yopmail.com', '$2y$10$jWMcnaBcLvrAL5IioINLluOrhagEnKsyxCdv8YP.h4dBtdF1uoAqG', '2005-02-16', '', '444', '2024-10-17 10:27:00', 0, 'b78b79e3c0238185d0894dba2a493d0275865d88ca6b0249a9e76800f1d46cea', NULL, NULL, NULL, 'membre', 'uploads/profile_pictures/675ff74281953_Capture du 2024-01-08 12-03-35.png'),
(13, 'MoDzy', 'TroPicz', 'TroPiczMoDz@outlook.fr', 'GOOGLE_USER', NULL, NULL, NULL, '2024-10-22 06:43:00', 0, 'bd3cafdec88cb62b282e345dfa32fbeb87c33581109d0d3376504b2436b01e1e', NULL, NULL, NULL, 'admin', NULL),
(16, 'DefaultLast', 'jacques', 'phpdoc@example.com', '$2y$10$YW8PFSOYWsWucbA4KRp.PeqWnRCiugfycmYfOE08h4.yeXtviyZdm', '0000-00-00', '', '', '2024-10-25 06:48:43', 0, '736946f28f6955992aab4bb842bc0b1ef275618115ded4f3b758e11fbda06ee8', NULL, NULL, NULL, 'coach', NULL),
(18, 'DefaultLast', 'DefaultFirst', 'airthon@example.com', '$2y$10$1yY/sZRZQp0dv6d0vVoNqeE2CJqRMgW1btiTI5.O7/sJDm0hAudOC', '0000-00-00', '', '', '2024-10-25 07:01:30', 0, '545b7d78c8b9e621f77f5df963d4dc6beec6f04b06ae246f6e457059f73acc03', NULL, NULL, NULL, 'coach', NULL),
(22, 'DefaultLast', 'DefaultFirst', 'ssss@protonmail.com', '$2y$10$m2pRkeMowMGtvn9B2uxIH.DLhN5/mWQ32FKiSUiptBtCjxHCh0ZGe', NULL, NULL, NULL, '2024-12-16 10:37:12', 0, '68a0c30d9ba5f2683ab2c56e5619550b9f1b6f734c5a5f9e9ba0eb413ba9bb07', NULL, NULL, NULL, 'coach', NULL),
(29, 'BBBB', 'AAA', 'lolilol@yopmail.com', '$2y$12$on.72.vZrhhR9DBCxxWpWeO5pMEkNh7.pSgHJ34yuJiYkFqA0IAAu', '0000-00-00', '', '', '2025-01-13 08:36:16', 1, NULL, NULL, 'c7dd53cef578d2947c46a363a7c5185692284cfaffc22461f9b325bb0a4650d0', '2025-01-13 10:40:36', 'membre', NULL),
(31, 'DefaultLast', 'DefaultFirst', 'zob@yopmail.com', '$2y$12$TB14r6NhpE8YMTJOmT6bZuulTZ5hKyNfczJBCH4IeGHa7njseQsYu', NULL, NULL, NULL, '2025-01-14 13:04:39', 1, NULL, NULL, '38771efe7648da6118b174bfdf3e1836cabb3aaeef737d58ea554757c77dff32', '2025-01-14 15:20:06', 'membre', NULL),
(32, 'DefaultLast', 'DefaultFirst', 'alt.cu-1ajqbpl@yopmail.com', '$2y$12$P8A.v/YV0rhdkY/w.ZVErOjoLVIvoPdLPEuTpiuIK//oyuWtYMagW', NULL, NULL, NULL, '2025-01-14 13:58:50', 1, NULL, NULL, NULL, NULL, 'membre', NULL),
(33, 'MEHMED', 'Ersan', 'mehmedersan0@gmail.com', '$2y$12$GryulO5QXWP.W3iT78eSi.T..jTvOFLi4KQDkm/QQvCVI4i4i8kHq', NULL, NULL, NULL, '2025-01-14 14:00:33', 0, '89d18552562f09299678778902ae6fba81d487842866e766108a1cf1d867df91', NULL, NULL, NULL, 'membre', NULL),
(35, 'DefaultLast', 'DefaultFirst', 'presentation@yopmail.com', '$2y$12$vJzFWWEh2U/urbu/nNCLneLWLA.XacGVoKEKVlSGElmFg2sPpO4Qi', NULL, NULL, NULL, '2025-01-15 15:38:41', 1, NULL, NULL, NULL, NULL, 'membre', NULL),
(36, 'Maalal', 'Ines', 'inesmaalal79@gmail.com', '$2y$12$jDlXQqjLLBRGH/BBD1yFwecqIEvRqi9FDvD3vt1WLNeH0SSbffC.m', NULL, NULL, NULL, '2025-01-16 18:40:47', 0, '89efaf7aa7d70fd60ba2d7d4e6eae56a03e5d570619a17e0742cd45a743fb383', NULL, NULL, NULL, 'membre', NULL),
(37, 'Jouini', 'Rim', 'rim.jouini@gmail.com', '$2y$12$5ADgUUtdJYjNzQjX5gdwiuLaKPtwZfX3LcCOdI/AXtJ8SYLJRDWtW', NULL, NULL, NULL, '2025-01-20 10:56:11', 0, '25a31b7fe0a2c9d75739bf13cae353f3c179ecb50c665d858ae5367f51e3b817', NULL, NULL, NULL, 'membre', NULL),
(38, 'ho', 'he', 'spotichiasse@gmail.com', '$2y$12$v27Xhxlatoov.MCCKeoJo.e09Hiiy4dhWM8y.xJYffWDCtI4VBT7G', NULL, NULL, NULL, '2025-01-28 08:20:39', 0, 'ad5c2aebcca67b0096e446244ecea78cdba191ef09f95f3b2c9fec3205cc3acf', NULL, NULL, NULL, 'membre', NULL),
(39, 'DefaultLast', 'TIBOWYEA', 'jack@example.com', '$2y$12$Cfl5xTQtQbwzQaMI9LRqU.KPMWNBagiDCeUaqTB.MG2n0Unvb3fMy', '2025-02-13', '', '', '2025-01-30 07:48:41', 1, 'd5d5b6e0f84696098ad62fcc53a615e06616bb755ed9435eca92ba7b886e6792', NULL, 'noeuil', '2033-01-12 08:50:30', 'admin', '/uploads/profile_pictures/profile_67aef5c67afa40.62946537.jpeg'),
(43, 'DefaultLast', 'DefaultFirst', 'creepy.vignettes@gmail.com', '$2y$10$luua1481WprWRvwseao8B.I59Ui.YEqCtxhj03dwVjJwhTkquLoFm', NULL, NULL, NULL, '2025-02-14 08:18:02', 0, '2f225fbdf988acba1afe777a30777ac33158747bb2b4cb3299fd688283cfba24', NULL, NULL, NULL, 'membre', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `PERFORMANCE`
--

CREATE TABLE `PERFORMANCE` (
  `performance_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `activity` varchar(100) DEFAULT NULL,
  `rpm` int(11) DEFAULT NULL,
  `calories` int(11) DEFAULT NULL,
  `distance` decimal(10,2) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `play_time` time DEFAULT NULL,
  `performance_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `PERFORMANCE`
--

INSERT INTO `PERFORMANCE` (`performance_id`, `member_id`, `activity`, `rpm`, `calories`, `distance`, `score`, `play_time`, `performance_date`) VALUES
(1, 11, 'rpm', NULL, NULL, NULL, 500, '00:00:10', '2025-01-13'),
(2, 11, 'rpm', NULL, NULL, NULL, 200, '00:00:45', '2025-01-13'),
(3, 11, 'rpm', NULL, NULL, NULL, 700, '00:00:10', '2025-01-13'),
(4, 11, 'rpm', NULL, NULL, NULL, 700, '00:00:10', '2025-01-13'),
(5, 11, 'rpm', NULL, NULL, NULL, 700, '00:00:10', '2025-01-13'),
(6, 11, 'rpm', NULL, NULL, NULL, 400, '00:00:10', '2025-01-13'),
(7, 11, 'rpm', NULL, NULL, NULL, 400, '00:00:10', '2025-01-13'),
(8, 11, 'rpm', NULL, NULL, NULL, 500, '00:00:10', '2025-01-13'),
(9, 11, 'rpm', NULL, 3, NULL, 200, '00:00:10', '2025-01-13'),
(10, 11, 'rpm', NULL, 888, NULL, 777, '00:00:00', '2025-01-13'),
(11, 11, 'rpm', NULL, 3, NULL, 2, '00:00:01', '2025-01-13'),
(12, 11, 'rpm', NULL, 6, 5.00, 5, '00:00:04', '2025-01-13'),
(13, 11, 'rpm', NULL, 20, 5.00, 5, '00:00:45', '2025-01-13'),
(14, 11, 'rpm', NULL, 250, 1000.00, 1000, '00:00:20', '2025-01-14'),
(15, 11, 'rpm', NULL, 3, 700.00, 700, '00:00:20', '2025-01-15'),
(16, 11, 'rpm', NULL, 5, 350.00, 350, '00:00:30', '2025-01-15');

-- --------------------------------------------------------

--
-- Table structure for table `RESERVATION_HISTORY`
--

CREATE TABLE `RESERVATION_HISTORY` (
  `reservation_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `activity` varchar(100) DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `coach_id` int(11) NOT NULL,
  `color` varchar(7) DEFAULT '#4981d6'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `RESERVATION_HISTORY`
--

INSERT INTO `RESERVATION_HISTORY` (`reservation_id`, `member_id`, `activity`, `reservation_date`, `start_time`, `end_time`, `coach_id`, `color`) VALUES
(60, 11, 'Boxe', '2025-01-14', '10:45:00', '12:30:00', 11, '#4981d6'),
(63, 11, 'Boxe', '2025-01-16', '16:15:00', '17:00:00', 11, '#4981d6');

-- --------------------------------------------------------

--
-- Table structure for table `SUBSCRIPTION`
--

CREATE TABLE `SUBSCRIPTION` (
  `subscription_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `subscription_type` varchar(50) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `SUBSCRIPTION`
--

INSERT INTO `SUBSCRIPTION` (`subscription_id`, `member_id`, `stripe_subscription_id`, `subscription_type`, `start_date`, `end_date`, `amount`, `status`) VALUES
(9, 13, 'sub_1QCeEN01Olm6yDgOtdsYWkiT', 'Abonnement salle de sport Sportify', '2024-10-22', '2024-11-22', 25.00, 'canceled'),
(10, 13, 'sub_1QfjoQ01Olm6yDgO9RaBvkd6', 'Abonnement salle de sport Sportify', '2025-01-10', '2025-02-10', 25.00, 'active'),
(14, 11, 'sub_1Qgi4R01Olm6yDgOlMu7Izm0', 'Abonnement salle de sport Sportify', '2025-01-13', '2025-02-13', 25.00, 'active'),
(15, 29, 'sub_1Qgjg501Olm6yDgOTKyWZiI7', 'Abonnement salle de sport Sportify', '2025-01-13', '2025-02-13', 25.00, 'active'),
(16, 32, 'sub_1QhAfC01Olm6yDgOUBtxah6V', 'Abonnement salle de sport Sportify', '2025-01-14', '2025-02-14', 25.00, 'active'),
(17, 35, 'sub_1QhYh201Olm6yDgO5EQL9AyH', 'Abonnement salle de sport Sportify', '2025-01-15', '2025-02-15', 25.00, 'active'),
(18, 39, 'sub_1QmsWj01Olm6yDgOwEqqNnIT', 'Abonnement salle de sport Sportify', '2025-01-30', '2025-02-28', 25.00, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `TEAM`
--

CREATE TABLE `TEAM` (
  `team_id` int(11) NOT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `reservation_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `TEAM`
--

INSERT INTO `TEAM` (`team_id`, `team_name`, `reservation_id`) VALUES
(4, 'inesscs', NULL),
(5, 'qdqDQ', NULL),
(6, 'yrurytu', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `TEAM_PARTICIPANT`
--

CREATE TABLE `TEAM_PARTICIPANT` (
  `participant_id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TRAINING_PLAN`
--

CREATE TABLE `TRAINING_PLAN` (
  `plan_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `level` varchar(20) DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `height` float DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `goals` text DEFAULT NULL,
  `equipment` text DEFAULT NULL,
  `constraints` text DEFAULT NULL,
  `preferences` text DEFAULT NULL,
  `plan_content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `TRAINING_PLAN`
--

INSERT INTO `TRAINING_PLAN` (`plan_id`, `member_id`, `gender`, `level`, `weight`, `height`, `created_at`, `goals`, `equipment`, `constraints`, `preferences`, `plan_content`) VALUES
(17, 11, 'man', 'beginner', 66, 75, '2025-01-13 08:17:01', 'lose_weight', 'none', 'Aucun', 'no_preference', '{\"days\":[{\"day\":\"Lundi\",\"exercises\":[{\"name\":\"Marche rapide\",\"description\":\"Marchez rapidement pendant 30 minutes \\u00e0 un rythme soutenu.\",\"sets\":1,\"reps\":0,\"duration\":30,\"repos\":0,\"intensity\":\"Mod\\u00e9r\\u00e9e\"},{\"name\":\"Pompes au sol\",\"description\":\"Effectuez 10 pompes en position haute ou sur les genoux si n\\u00e9cessaire.\",\"sets\":3,\"reps\":10,\"duration\":0,\"repos\":60,\"intensity\":\"Faible\"},{\"name\":\"Squats\",\"description\":\"Effectuez 15 squats, en vous concentrant sur la forme.\",\"sets\":3,\"reps\":15,\"duration\":0,\"repos\":60,\"intensity\":\"Faible\"},{\"name\":\"Planche\",\"description\":\"Maintenez la position de planche pendant 30 secondes.\",\"sets\":3,\"reps\":0,\"duration\":30,\"repos\":60,\"intensity\":\"Faible\"}]},{\"day\":\"Mardi\",\"exercises\":[{\"name\":\"Repos\",\"description\":\"Jour de repos pour permettre une r\\u00e9cup\\u00e9ration ad\\u00e9quate.\"}]},{\"day\":\"Mercredi\",\"exercises\":[{\"name\":\"V\\u00e9lo d\'appartement\",\"description\":\"P\\u00e9dalez sur un v\\u00e9lo d\'appartement pendant 20 minutes \\u00e0 un rythme mod\\u00e9r\\u00e9.\",\"sets\":1,\"reps\":0,\"duration\":20,\"repos\":0,\"intensity\":\"Mod\\u00e9r\\u00e9e\"},{\"name\":\"Fentes\",\"description\":\"Effectuez 12 fentes par jambe, en veillant \\u00e0 garder le dos droit.\",\"sets\":3,\"reps\":12,\"duration\":0,\"repos\":60,\"intensity\":\"Faible\"},{\"name\":\"Tractions \\u00e0 la barre\",\"description\":\"Effectuez 8 tractions \\u00e0 la barre ou avec une bande de r\\u00e9sistance si n\\u00e9cessaire.\",\"sets\":3,\"reps\":8,\"duration\":0,\"repos\":60,\"intensity\":\"Faible\"},{\"name\":\"Gainage lat\\u00e9ral\",\"description\":\"Maintenez la position de gainage lat\\u00e9ral pendant 30 secondes de chaque c\\u00f4t\\u00e9.\",\"sets\":3,\"reps\":0,\"duration\":30,\"repos\":60,\"intensity\":\"Faible\"}]},{\"day\":\"Jeudi\",\"exercises\":[{\"name\":\"Repos\",\"description\":\"Jour de repos pour permettre une r\\u00e9cup\\u00e9ration ad\\u00e9quate.\"}]},{\"day\":\"Vendredi\",\"exercises\":[{\"name\":\"Course \\u00e0 pied\",\"description\":\"Courez pendant 20 minutes \\u00e0 un rythme l\\u00e9ger.\",\"sets\":1,\"reps\":0,\"duration\":20,\"repos\":0,\"intensity\":\"Faible\"},{\"name\":\"Burpees\",\"description\":\"Effectuez 10 burpees, en vous concentrant sur la forme.\",\"sets\":3,\"reps\":10,\"duration\":0,\"repos\":60,\"intensity\":\"Mod\\u00e9r\\u00e9e\"},{\"name\":\"Rowing\",\"description\":\"Effectuez 12 mouvements de rowing sur une machine \\u00e0 ramer ou avec une bande de r\\u00e9sistance.\",\"sets\":3,\"reps\":12,\"duration\":0,\"repos\":60,\"intensity\":\"Faible\"},{\"name\":\"\\u00c9tirements\",\"description\":\"\\u00c9tirez les principaux groupes musculaires pendant 10 minutes.\"}]},{\"day\":\"Samedi\",\"exercises\":[{\"name\":\"Repos\",\"description\":\"Jour de repos pour permettre une r\\u00e9cup\\u00e9ration ad\\u00e9quate.\"}]},{\"day\":\"Dimanche\",\"exercises\":[{\"name\":\"Yoga ou Pilates\",\"description\":\"Participez \\u00e0 un cours de yoga ou de Pilates pour am\\u00e9liorer la flexibilit\\u00e9 et la force de base.\"}]}]}');

-- --------------------------------------------------------

--
-- Table structure for table `TRAINING_SESSIONS`
--

CREATE TABLE `TRAINING_SESSIONS` (
  `session_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `training_plan_id` int(11) DEFAULT NULL,
  `session_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `exercises` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `AVAILABILITY`
--
ALTER TABLE `AVAILABILITY`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `coach_id` (`coach_id`);

--
-- Indexes for table `COACH`
--
ALTER TABLE `COACH`
  ADD PRIMARY KEY (`coach_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `COURT`
--
ALTER TABLE `COURT`
  ADD PRIMARY KEY (`court_id`);

--
-- Indexes for table `COURT_RESERVATION`
--
ALTER TABLE `COURT_RESERVATION`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `fk_member_court_reservation` (`member_id`),
  ADD KEY `fk_court_reservation` (`court_id`),
  ADD KEY `fk_event_id` (`event_id`),
  ADD KEY `fk_reservation_team` (`team_id`);

--
-- Indexes for table `DAILY_ACTIVITY`
--
ALTER TABLE `DAILY_ACTIVITY`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `EVENTS`
--
ALTER TABLE `EVENTS`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `EVENT_INVITATIONS`
--
ALTER TABLE `EVENT_INVITATIONS`
  ADD PRIMARY KEY (`invitation_id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `EVENT_REGISTRATION`
--
ALTER TABLE `EVENT_REGISTRATION`
  ADD PRIMARY KEY (`registration_id`),
  ADD KEY `fk_member_event_registration` (`member_id`),
  ADD KEY `fk_event_registration` (`event_id`);

--
-- Indexes for table `MEMBER`
--
ALTER TABLE `MEMBER`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`);

--
-- Indexes for table `PERFORMANCE`
--
ALTER TABLE `PERFORMANCE`
  ADD PRIMARY KEY (`performance_id`),
  ADD KEY `fk_member_performance` (`member_id`);

--
-- Indexes for table `RESERVATION_HISTORY`
--
ALTER TABLE `RESERVATION_HISTORY`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `fk_member_reservation` (`member_id`),
  ADD KEY `fk_coach_reservation` (`coach_id`);

--
-- Indexes for table `SUBSCRIPTION`
--
ALTER TABLE `SUBSCRIPTION`
  ADD PRIMARY KEY (`subscription_id`),
  ADD KEY `fk_member_subscription` (`member_id`);

--
-- Indexes for table `TEAM`
--
ALTER TABLE `TEAM`
  ADD PRIMARY KEY (`team_id`),
  ADD KEY `fk_team_reservation` (`reservation_id`);

--
-- Indexes for table `TEAM_PARTICIPANT`
--
ALTER TABLE `TEAM_PARTICIPANT`
  ADD PRIMARY KEY (`participant_id`),
  ADD KEY `fk_team_participant` (`team_id`),
  ADD KEY `fk_member_participant` (`member_id`);

--
-- Indexes for table `TRAINING_PLAN`
--
ALTER TABLE `TRAINING_PLAN`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `TRAINING_SESSIONS`
--
ALTER TABLE `TRAINING_SESSIONS`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `training_plan_id` (`training_plan_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `AVAILABILITY`
--
ALTER TABLE `AVAILABILITY`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `COACH`
--
ALTER TABLE `COACH`
  MODIFY `coach_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `COURT`
--
ALTER TABLE `COURT`
  MODIFY `court_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `COURT_RESERVATION`
--
ALTER TABLE `COURT_RESERVATION`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `DAILY_ACTIVITY`
--
ALTER TABLE `DAILY_ACTIVITY`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `EVENTS`
--
ALTER TABLE `EVENTS`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `EVENT_INVITATIONS`
--
ALTER TABLE `EVENT_INVITATIONS`
  MODIFY `invitation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `EVENT_REGISTRATION`
--
ALTER TABLE `EVENT_REGISTRATION`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `MEMBER`
--
ALTER TABLE `MEMBER`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `PERFORMANCE`
--
ALTER TABLE `PERFORMANCE`
  MODIFY `performance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `RESERVATION_HISTORY`
--
ALTER TABLE `RESERVATION_HISTORY`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `SUBSCRIPTION`
--
ALTER TABLE `SUBSCRIPTION`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `TEAM`
--
ALTER TABLE `TEAM`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `TEAM_PARTICIPANT`
--
ALTER TABLE `TEAM_PARTICIPANT`
  MODIFY `participant_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TRAINING_PLAN`
--
ALTER TABLE `TRAINING_PLAN`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `TRAINING_SESSIONS`
--
ALTER TABLE `TRAINING_SESSIONS`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `AVAILABILITY`
--
ALTER TABLE `AVAILABILITY`
  ADD CONSTRAINT `AVAILABILITY_ibfk_1` FOREIGN KEY (`coach_id`) REFERENCES `COACH` (`coach_id`) ON DELETE CASCADE;

--
-- Constraints for table `COURT_RESERVATION`
--
ALTER TABLE `COURT_RESERVATION`
  ADD CONSTRAINT `fk_court_reservation` FOREIGN KEY (`court_id`) REFERENCES `COURT` (`court_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_event_id` FOREIGN KEY (`event_id`) REFERENCES `EVENTS` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_member_court_reservation` FOREIGN KEY (`member_id`) REFERENCES `MEMBER` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reservation_team` FOREIGN KEY (`team_id`) REFERENCES `TEAM` (`team_id`) ON DELETE SET NULL;

--
-- Constraints for table `DAILY_ACTIVITY`
--
ALTER TABLE `DAILY_ACTIVITY`
  ADD CONSTRAINT `DAILY_ACTIVITY_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `TRAINING_PLAN` (`plan_id`) ON DELETE CASCADE;

--
-- Constraints for table `EVENT_INVITATIONS`
--
ALTER TABLE `EVENT_INVITATIONS`
  ADD CONSTRAINT `EVENT_INVITATIONS_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `EVENTS` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `EVENT_REGISTRATION`
--
ALTER TABLE `EVENT_REGISTRATION`
  ADD CONSTRAINT `fk_event_registration` FOREIGN KEY (`event_id`) REFERENCES `EVENTS` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_member_event_registration` FOREIGN KEY (`member_id`) REFERENCES `MEMBER` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `PERFORMANCE`
--
ALTER TABLE `PERFORMANCE`
  ADD CONSTRAINT `fk_member_performance` FOREIGN KEY (`member_id`) REFERENCES `MEMBER` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `RESERVATION_HISTORY`
--
ALTER TABLE `RESERVATION_HISTORY`
  ADD CONSTRAINT `fk_coach_reservation` FOREIGN KEY (`coach_id`) REFERENCES `COACH` (`coach_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_member_reservation` FOREIGN KEY (`member_id`) REFERENCES `MEMBER` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `SUBSCRIPTION`
--
ALTER TABLE `SUBSCRIPTION`
  ADD CONSTRAINT `fk_member_subscription` FOREIGN KEY (`member_id`) REFERENCES `MEMBER` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `TEAM`
--
ALTER TABLE `TEAM`
  ADD CONSTRAINT `fk_team_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `COURT_RESERVATION` (`reservation_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `TEAM_PARTICIPANT`
--
ALTER TABLE `TEAM_PARTICIPANT`
  ADD CONSTRAINT `fk_member_participant` FOREIGN KEY (`member_id`) REFERENCES `MEMBER` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_team_participant` FOREIGN KEY (`team_id`) REFERENCES `TEAM` (`team_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `TRAINING_PLAN`
--
ALTER TABLE `TRAINING_PLAN`
  ADD CONSTRAINT `TRAINING_PLAN_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `MEMBER` (`member_id`) ON DELETE CASCADE;

--
-- Constraints for table `TRAINING_SESSIONS`
--
ALTER TABLE `TRAINING_SESSIONS`
  ADD CONSTRAINT `TRAINING_SESSIONS_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `MEMBER` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `TRAINING_SESSIONS_ibfk_2` FOREIGN KEY (`training_plan_id`) REFERENCES `TRAINING_PLAN` (`plan_id`) ON DELETE SET NULL;
COMMIT;