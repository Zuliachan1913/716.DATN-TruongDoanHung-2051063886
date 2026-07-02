-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 10, 2026 lúc 06:23 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `attendancemsystem01`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbladmin`
--

CREATE TABLE `tbladmin` (
  `Id` int(10) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `emailAddress` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `tbladmin`
--

INSERT INTO `tbladmin` (`Id`, `firstName`, `lastName`, `emailAddress`, `password`) VALUES
(1, 'Admin', '', 'admin@mail.com', 'D00F5D5217896FB7FD601412CB890830'),
(7, 'Hai Dang', 'Tran', 'bincloverz@gmail.com', 'b4af804009cb036a4ccdc33431ef9ac9');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblattendance`
--

CREATE TABLE `tblattendance` (
  `Id` int(10) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `admissionNo` varchar(255) NOT NULL,
  `classId` varchar(10) NOT NULL,
  `classArmId` varchar(10) NOT NULL,
  `diligently` int(11) NOT NULL,
  `status` enum('present','late','absent','excused') NOT NULL,
  `dateTimeTaken` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `tblattendance`
--

INSERT INTO `tblattendance` (`Id`, `id_admin`, `admissionNo`, `classId`, `classArmId`, `diligently`, `status`, `dateTimeTaken`) VALUES
(290, 0, '20', '4', '6', 0, 'excused', '2024-07-12'),
(277, 0, 'BKC4343', '4', '6', 0, 'absent', '2024-07-07'),
(213, 0, 'BKC11111', '4', '6', 0, 'present', '2024-07-04'),
(214, 0, 'BKC4343', '4', '6', 0, 'late', '2024-07-09'),
(215, 0, 'BKC12121212', '4', '6', 0, 'present', '2024-07-09'),
(216, 0, 'BKC11111', '4', '6', 0, 'absent', '2024-07-09'),
(217, 0, 'BKC4343', '4', '6', 0, 'late', '2024-07-10'),
(218, 0, 'BKC12121212', '4', '6', 0, 'excused', '2024-07-10'),
(294, 0, 'BKC12121212', '4', '6', 0, 'late', '2024-07-04'),
(279, 0, 'BKC11111', '4', '6', 0, 'present', '2024-07-12'),
(320, 0, '3456789', '4', '6', 0, 'excused', '2024-07-16'),
(321, 0, 'BKC4343', '4', '6', 0, 'present', '2024-07-16'),
(322, 0, 'BKC12121212', '4', '6', 0, 'present', '2024-07-16'),
(323, 0, 'BKC11111', '4', '6', 0, 'present', '2024-07-16'),
(336, 0, 'BKC11111', '4', '6', 0, 'absent', '2024-07-25'),
(340, 0, '3456789', '4', '6', 0, 'present', '2024-07-28'),
(328, 0, '3456789', '4', '6', 0, 'present', '2024-07-25'),
(329, 0, 'BKC4343', '4', '6', 0, 'late', '2024-07-25'),
(330, 0, 'BKC12121212', '4', '6', 0, 'absent', '2024-07-25'),
(339, 0, '3456789', '4', '6', 0, 'present', '2024-07-28'),
(332, 0, '3456789', '4', '6', 0, 'present', '2024-07-25'),
(337, 0, 'BKC11111', '4', '6', 0, 'absent', '2024-07-25'),
(338, 0, '3456789', '4', '6', 0, 'present', '2024-07-28'),
(341, 0, 'BKC4343', '4', '6', 0, 'present', '2024-07-28'),
(342, 0, 'BKC12121212', '4', '6', 0, 'late', '2024-07-28'),
(343, 0, 'BKC11111', '4', '6', 0, 'absent', '2024-07-28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblclass`
--

CREATE TABLE `tblclass` (
  `Id` int(10) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `className` varchar(255) NOT NULL,
  `duration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `tblclass`
--

INSERT INTO `tblclass` (`Id`, `id_admin`, `className`, `duration`) VALUES
(4, 0, 'IT', 60),
(5, 0, 'ITTT', 0),
(7, 0, 'QTKD', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblclassarms`
--

CREATE TABLE `tblclassarms` (
  `Id` int(10) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `classId` varchar(10) NOT NULL,
  `classArmName` varchar(255) NOT NULL,
  `isAssigned` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `tblclassarms`
--

INSERT INTO `tblclassarms` (`Id`, `id_admin`, `classId`, `classArmName`, `isAssigned`) VALUES
(6, 0, '4', 'D03K12', '1'),
(7, 0, '5', 'D02K12', '0'),
(8, 0, '7', 'D02K12', '0'),
(9, 0, '4', 'D02K12', '0'),
(11, 0, '7', 'D05K12', '0');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblclassassignment`
--

CREATE TABLE `tblclassassignment` (
  `id` int(10) NOT NULL,
  `teacherId` int(10) NOT NULL,
  `classId` int(10) NOT NULL,
  `classArmId` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `tblclassassignment`
--

INSERT INTO `tblclassassignment` (`id`, `teacherId`, `classId`, `classArmId`) VALUES
(1, 6, 5, 9),
(3, 6, 4, 7),
(5, 6, 4, 6),
(6, 6, 4, 6),
(7, 6, 4, 11),
(8, 10, 4, 7);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblclassteacher`
--

CREATE TABLE `tblclassteacher` (
  `Id` int(10) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `emailAddress` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phoneNo` varchar(50) NOT NULL,
  `classId` varchar(10) NOT NULL,
  `classArmId` varchar(10) NOT NULL,
  `dateCreated` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `tblclassteacher`
--

INSERT INTO `tblclassteacher` (`Id`, `id_admin`, `firstName`, `lastName`, `emailAddress`, `password`, `phoneNo`, `classId`, `classArmId`, `dateCreated`) VALUES
(6, 0, 'Tuan Anh', 'Nguyen', 'teacher@mail.com', '32250170a0dca92d53ec9624f336ca24', '0100000030', '4', '6', '2024-03-07'),
(10, 0, 'Hai Dang', 'Tran', 'bincloverz@gmail.com', '32250170a0dca92d53ec9624f336ca24', '0587782928', '', '', '2024-07-24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblsessionterm`
--

CREATE TABLE `tblsessionterm` (
  `Id` int(10) NOT NULL,
  `sessionName` varchar(50) NOT NULL,
  `termId` varchar(50) NOT NULL,
  `isActive` varchar(10) NOT NULL,
  `dateCreated` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `tblsessionterm`
--

INSERT INTO `tblsessionterm` (`Id`, `sessionName`, `termId`, `isActive`, `dateCreated`) VALUES
(10, 'K12', '2', '0', '2024-07-04'),
(8, 'K12', '1', '1', '2024-07-04'),
(11, 'K12', '3', '0', '2024-07-04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblstudents`
--

CREATE TABLE `tblstudents` (
  `Id` int(10) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `otherName` varchar(255) NOT NULL,
  `admissionNumber` varchar(255) NOT NULL,
  `password` varchar(50) NOT NULL,
  `classId` varchar(10) NOT NULL,
  `classArmId` varchar(10) NOT NULL,
  `dateCreated` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `tblstudents`
--

INSERT INTO `tblstudents` (`Id`, `id_admin`, `firstName`, `lastName`, `otherName`, `admissionNumber`, `password`, `classId`, `classArmId`, `dateCreated`) VALUES
(23, 0, 'Hoang ', 'Tran', '', '3456789', '12345', '4', '6', '2024-07-10'),
(22, 0, 'Quang K', 'Le', 'none', 'BKC4343', '12345', '4', '6', '2024-07-04'),
(11, 0, 'Van C', 'Le', 'none', 'BKC121212', '12345', '4', '', '2022-10-07'),
(21, 0, 'Thanh Truc', 'Dang', 'none', 'BKC12121212', '12345', '4', '6', '2024-07-04'),
(19, 0, 'qqsqsqs', 'sqsqs', 'none', 'sqqsqsqs', '12345', '4', '', '2024-07-04'),
(20, 0, 'Van A', 'Nguyen', 'non', 'BKC11111', '12345', '4', '6', '2024-07-04'),
(18, 0, 'Van C', 'Le', 'none', 'BKC111', '12345', '4', '', '2024-07-04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblteacherclass`
--

CREATE TABLE `tblteacherclass` (
  `id` int(10) NOT NULL,
  `teacherId` int(10) NOT NULL,
  `classId` int(10) NOT NULL,
  `classArmId` int(10) NOT NULL,
  `dateCreated` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblterm`
--

CREATE TABLE `tblterm` (
  `Id` int(10) NOT NULL,
  `termName` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `tblterm`
--

INSERT INTO `tblterm` (`Id`, `termName`) VALUES
(1, '1'),
(2, '2'),
(3, '3');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `tbladmin`
--
ALTER TABLE `tbladmin`
  ADD PRIMARY KEY (`Id`);

--
-- Chỉ mục cho bảng `tblattendance`
--
ALTER TABLE `tblattendance`
  ADD PRIMARY KEY (`Id`);

--
-- Chỉ mục cho bảng `tblclass`
--
ALTER TABLE `tblclass`
  ADD PRIMARY KEY (`Id`);

--
-- Chỉ mục cho bảng `tblclassarms`
--
ALTER TABLE `tblclassarms`
  ADD PRIMARY KEY (`Id`);

--
-- Chỉ mục cho bảng `tblclassassignment`
--
ALTER TABLE `tblclassassignment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacherId` (`teacherId`),
  ADD KEY `classId` (`classId`),
  ADD KEY `classArmId` (`classArmId`);

--
-- Chỉ mục cho bảng `tblclassteacher`
--
ALTER TABLE `tblclassteacher`
  ADD PRIMARY KEY (`Id`);

--
-- Chỉ mục cho bảng `tblsessionterm`
--
ALTER TABLE `tblsessionterm`
  ADD PRIMARY KEY (`Id`);

--
-- Chỉ mục cho bảng `tblstudents`
--
ALTER TABLE `tblstudents`
  ADD PRIMARY KEY (`Id`);

--
-- Chỉ mục cho bảng `tblteacherclass`
--
ALTER TABLE `tblteacherclass`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_teacher` (`teacherId`),
  ADD KEY `FK_class` (`classId`),
  ADD KEY `FK_classArm` (`classArmId`);

--
-- Chỉ mục cho bảng `tblterm`
--
ALTER TABLE `tblterm`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `tbladmin`
--
ALTER TABLE `tbladmin`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `tblattendance`
--
ALTER TABLE `tblattendance`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=344;

--
-- AUTO_INCREMENT cho bảng `tblclass`
--
ALTER TABLE `tblclass`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `tblclassarms`
--
ALTER TABLE `tblclassarms`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `tblclassassignment`
--
ALTER TABLE `tblclassassignment`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `tblclassteacher`
--
ALTER TABLE `tblclassteacher`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `tblsessionterm`
--
ALTER TABLE `tblsessionterm`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `tblstudents`
--
ALTER TABLE `tblstudents`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT cho bảng `tblteacherclass`
--
ALTER TABLE `tblteacherclass`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `tblterm`
--
ALTER TABLE `tblterm`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `tblclassassignment`
--
ALTER TABLE `tblclassassignment`
  ADD CONSTRAINT `tblclassassignment_ibfk_1` FOREIGN KEY (`teacherId`) REFERENCES `tblclassteacher` (`Id`),
  ADD CONSTRAINT `tblclassassignment_ibfk_2` FOREIGN KEY (`classId`) REFERENCES `tblclass` (`Id`),
  ADD CONSTRAINT `tblclassassignment_ibfk_3` FOREIGN KEY (`classArmId`) REFERENCES `tblclassarms` (`Id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
