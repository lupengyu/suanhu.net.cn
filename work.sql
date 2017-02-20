-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 2017-02-20 12:09:09
-- 服务器版本： 10.1.13-MariaDB
-- PHP Version: 5.6.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `work`
--

-- --------------------------------------------------------

--
-- 表的结构 `activity`
--

CREATE TABLE `activity` (
  `id` int(8) NOT NULL,
  `user_id` int(8) NOT NULL,
  `user_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(28) COLLATE utf8_unicode_ci NOT NULL,
  `summary` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `body` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `number` int(8) NOT NULL,
  `photo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `school` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `grade` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sign` int(10) DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(1) NOT NULL DEFAULT '1',
  `see` int(8) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `classes`
--

CREATE TABLE `classes` (
  `id` int(8) NOT NULL,
  `school` int(8) NOT NULL,
  `grade` int(8) NOT NULL,
  `code` int(20) NOT NULL,
  `success` int(8) NOT NULL DEFAULT '0',
  `sign` int(8) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `classes`
--

INSERT INTO `classes` (`id`, `school`, `grade`, `code`, `success`, `sign`) VALUES
(1, 14, 2015, 14011501, 0, 0),
(2, 14, 2015, 14011502, 0, 0),
(3, 14, 2015, 14011503, 0, 0),
(4, 14, 2015, 14011504, 0, 0),
(5, 14, 2015, 14011505, 0, 0),
(6, 14, 2015, 14011506, 0, 0),
(7, 14, 2015, 14011507, 0, 0),
(8, 14, 2015, 14011508, 0, 0),
(9, 14, 2015, 14011509, 0, 0),
(10, 14, 2015, 14011510, 0, 0),
(11, 14, 2015, 14021501, 0, 0),
(12, 14, 2015, 14021502, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `diary`
--

CREATE TABLE `diary` (
  `id` int(8) NOT NULL,
  `summary` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `grade`
--

CREATE TABLE `grade` (
  `id` int(8) NOT NULL,
  `school` int(11) NOT NULL,
  `grade` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `grade`
--

INSERT INTO `grade` (`id`, `school`, `grade`) VALUES
(3, 14, 2015);

-- --------------------------------------------------------

--
-- 表的结构 `navigate`
--

CREATE TABLE `navigate` (
  `id` int(8) NOT NULL,
  `activity_id` int(8) NOT NULL,
  `school_id` int(8) NOT NULL DEFAULT '-1',
  `school` int(8) DEFAULT NULL,
  `grade` int(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `sign`
--

CREATE TABLE `sign` (
  `id` int(8) NOT NULL,
  `user_id` int(8) NOT NULL,
  `activity_id` int(8) NOT NULL,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `code` int(20) NOT NULL,
  `school` int(8) NOT NULL,
  `grade` int(8) NOT NULL,
  `class` int(20) NOT NULL,
  `title` varchar(28) COLLATE utf8_unicode_ci NOT NULL,
  `body` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `id` int(8) NOT NULL,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `school` int(8) NOT NULL,
  `grade` int(8) NOT NULL,
  `class` int(20) NOT NULL,
  `email` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `code` int(20) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `success` int(10) NOT NULL DEFAULT '0',
  `sign` int(10) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `name`, `school`, `grade`, `class`, `email`, `code`, `status`, `success`, `sign`) VALUES
(2, '卢鹏宇', 14, 2015, 14011503, '729387121@qq.com', 2015303135, 4, 1, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity`
--
ALTER TABLE `activity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `diary`
--
ALTER TABLE `diary`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grade`
--
ALTER TABLE `grade`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `navigate`
--
ALTER TABLE `navigate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sign`
--
ALTER TABLE `sign`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `activity`
--
ALTER TABLE `activity`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
--
-- 使用表AUTO_INCREMENT `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- 使用表AUTO_INCREMENT `diary`
--
ALTER TABLE `diary`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;
--
-- 使用表AUTO_INCREMENT `grade`
--
ALTER TABLE `grade`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- 使用表AUTO_INCREMENT `navigate`
--
ALTER TABLE `navigate`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
--
-- 使用表AUTO_INCREMENT `sign`
--
ALTER TABLE `sign`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
