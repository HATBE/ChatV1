-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 16. Mrz 2021 um 17:50
-- Server-Version: 10.4.11-MariaDB
-- PHP-Version: 7.4.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `chatsystem`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `conversation`
--

CREATE TABLE `conversation` (
  `idConversation` int(11) NOT NULL,
  `titleConversation` varchar(255) NOT NULL,
  `keyConversation` text NOT NULL,
  `owner_fsConversation` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `conversation`
--

INSERT INTO `conversation` (`idConversation`, `titleConversation`, `keyConversation`, `owner_fsConversation`) VALUES
(9, 'test123', 'clytem145y2qn10payz53o5uukfcpvdn46ptjmfcl3a6jlag3rvb6jzq5gd0dxu9lon0gszva0x', 2);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `message`
--

CREATE TABLE `message` (
  `idMessage` int(11) NOT NULL,
  `messageMessage` text NOT NULL,
  `user_fsMessage` int(11) NOT NULL,
  `conversation_fsMessage` int(11) NOT NULL,
  `dateSentMessage` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `message`
--

INSERT INTO `message` (`idMessage`, `messageMessage`, `user_fsMessage`, `conversation_fsMessage`, `dateSentMessage`) VALUES
(26, 'bmJGRi94UExEeUtmSmVkeldtc1hjdz09Ojo/+ze/I47mC2A1W6ZxtUl8', 1, 9, '2021-03-16 16:48:25'),
(27, 'N21FWUQ3QlFnZVQ5N2lPVk1icENVdVJhT3NocEkxeXpHeXhLWUY5YnFUOD06Omxghq99cv0KAEkQ0lbzHbs=', 2, 9, '2021-03-16 16:48:35'),
(28, 'WlUwWklHZ2VERU82N2dBVEhNZnh6dz09OjqQD5W3Ho5Z1GKQW+a0QN49', 2, 9, '2021-03-16 16:48:48'),
(29, 'NFJ4ZGlHZzVDTHZnSkJhS0VjTjRFUT09OjpFKabzawpSzAhsrvRyp5GK', 1, 9, '2021-03-16 16:48:57'),
(30, 'ZGVleHRjb09MQk9rRnBybmREcHMwdz09OjqS2Dp2bJtm4mkdeYNN3KeB', 1, 9, '2021-03-16 16:49:07'),
(31, 'a1VHUy9xZG93VDFFeVlCTEVLbVhHdz09OjqBqQzL7DB9KU5tykkiwrTo', 2, 9, '2021-03-16 16:49:27');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `idUsers` int(11) NOT NULL,
  `nameUsers` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`idUsers`, `nameUsers`) VALUES
(1, 'user1'),
(2, 'user2'),
(3, 'user3');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `usertoconversation`
--

CREATE TABLE `usertoconversation` (
  `idUserToConversation` int(11) NOT NULL,
  `user_fsUserToConversation` int(11) NOT NULL,
  `conversation_fsUserToConversation` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `usertoconversation`
--

INSERT INTO `usertoconversation` (`idUserToConversation`, `user_fsUserToConversation`, `conversation_fsUserToConversation`) VALUES
(24, 2, 9),
(29, 1, 9);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `conversation`
--
ALTER TABLE `conversation`
  ADD PRIMARY KEY (`idConversation`),
  ADD KEY `conversation_to_users` (`owner_fsConversation`);

--
-- Indizes für die Tabelle `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`idMessage`),
  ADD KEY `message_to_user_fs` (`user_fsMessage`),
  ADD KEY `message_to_conversation_fs` (`conversation_fsMessage`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`idUsers`);

--
-- Indizes für die Tabelle `usertoconversation`
--
ALTER TABLE `usertoconversation`
  ADD PRIMARY KEY (`idUserToConversation`),
  ADD KEY `usettoconversation_to_user` (`user_fsUserToConversation`),
  ADD KEY `usettoconversation_to_conversation` (`conversation_fsUserToConversation`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `conversation`
--
ALTER TABLE `conversation`
  MODIFY `idConversation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT für Tabelle `message`
--
ALTER TABLE `message`
  MODIFY `idMessage` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `idUsers` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `usertoconversation`
--
ALTER TABLE `usertoconversation`
  MODIFY `idUserToConversation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `conversation`
--
ALTER TABLE `conversation`
  ADD CONSTRAINT `conversation_to_users` FOREIGN KEY (`owner_fsConversation`) REFERENCES `users` (`idUsers`);

--
-- Constraints der Tabelle `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_to_conversation_fs` FOREIGN KEY (`conversation_fsMessage`) REFERENCES `conversation` (`idConversation`),
  ADD CONSTRAINT `message_to_user_fs` FOREIGN KEY (`user_fsMessage`) REFERENCES `users` (`idUsers`);

--
-- Constraints der Tabelle `usertoconversation`
--
ALTER TABLE `usertoconversation`
  ADD CONSTRAINT `usettoconversation_to_conversation` FOREIGN KEY (`conversation_fsUserToConversation`) REFERENCES `conversation` (`idConversation`),
  ADD CONSTRAINT `usettoconversation_to_user` FOREIGN KEY (`user_fsUserToConversation`) REFERENCES `users` (`idUsers`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
