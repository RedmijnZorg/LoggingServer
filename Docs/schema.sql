-- phpMyAdmin SQL Dump
-- version 5.2.2deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Gegenereerd op: 13 jan 2026 om 20:08
-- Serverversie: 11.4.7-MariaDB-0ubuntu0.25.04.1
-- PHP-versie: 8.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `logserver`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `configuration`
--

CREATE TABLE `configuration` (
  `itemid` int(11) NOT NULL,
  `item` text NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `logging`
--

CREATE TABLE `logging` (
  `logid` int(11) NOT NULL,
  `sourceid` int(11) NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  `ip` text NOT NULL,
  `useragent` text NOT NULL,
  `referrer` text NOT NULL,
  `username` text NOT NULL,
  `page` text NOT NULL,
  `event` text NOT NULL,
  `data` text NOT NULL,
  `pass` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `sources`
--

CREATE TABLE `sources` (
  `sourceid` int(11) NOT NULL,
  `name` text NOT NULL,
  `token` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `users`
--

CREATE TABLE `users` (
  `userid` int(11) NOT NULL,
  `password` text DEFAULT NULL,
  `fullname` text DEFAULT NULL,
  `email` text DEFAULT NULL,
  `sso` text DEFAULT NULL,
  `lastlogin` text DEFAULT NULL,
  `failedlogins` int(11) DEFAULT NULL,
  `locked` int(1) DEFAULT NULL,
  `archived` int(1) DEFAULT NULL,
  `cookies` text DEFAULT NULL,
  `lasttoken` text DEFAULT NULL,
  `changepassword` int(1) DEFAULT NULL,
  `resettoken` text DEFAULT NULL,
  `2fa` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `configuration`
--
ALTER TABLE `configuration`
  ADD PRIMARY KEY (`itemid`);

--
-- Indexen voor tabel `logging`
--
ALTER TABLE `logging`
  ADD PRIMARY KEY (`logid`),
  ADD KEY `sourceid` (`sourceid`) USING BTREE;

--
-- Indexen voor tabel `sources`
--
ALTER TABLE `sources`
  ADD PRIMARY KEY (`sourceid`);

--
-- Indexen voor tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userid`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `configuration`
--
ALTER TABLE `configuration`
  MODIFY `itemid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `logging`
--
ALTER TABLE `logging`
  MODIFY `logid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `sources`
--
ALTER TABLE `sources`
  MODIFY `sourceid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `users`
--
ALTER TABLE `users`
  MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
