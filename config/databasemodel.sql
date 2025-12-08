-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 06/12/2025 às 19:26
-- Versão do servidor: 11.4.3-MariaDB
-- Versão do PHP: 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `chestcounter`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `bank_approval_logs`
--

CREATE TABLE `bank_approval_logs` (
  `id` int(11) NOT NULL,
  `bank_transaction_id` int(11) NOT NULL,
  `admin_user_id` int(11) NOT NULL,
  `action` varchar(20) NOT NULL,
  `original_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`original_values`)),
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `bank_transactions`
--

CREATE TABLE `bank_transactions` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `fee` decimal(15,2) NOT NULL,
  `final_amount` decimal(15,2) NOT NULL,
  `description` varchar(512) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `destination_member_id` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `collected_chests`
--

CREATE TABLE `collected_chests` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '0',
  `player` varchar(50) NOT NULL DEFAULT '0',
  `source` varchar(50) NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 = auto / 1 = Manual',
  `collected_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `param` varchar(45) NOT NULL,
  `value` varchar(45) NOT NULL,
  `description` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `config`
--

INSERT INTO `config` (`id`, `param`, `value`, `description`) VALUES
(1, 'reference_day', '2025-07-30 17:00:00', 'Select a reference date for the start/end of the count. For example: If it is a weekly count, select a date that represents the day of the week that the count will start/end. If the count is for the Rise of Ancients event, select a date that represents the day of the event. Always use the format (YYYY-MM-DD hh:mm:ss) with UTC time (ex: \"2025-05-13 17:00:00\")'),
(2, 'every_how_many_days', '6', 'Every how many days: Sets how many days each counting period lasts. Suggestion: 6 to start counting every elder 7 for a weekly count'),
(3, 'minimum_chest_score', '15000', 'Minimum Chest Score'),
(4, 'minimum_epic_score', '6000', 'Minimum points for collecting MONSTER epic chests.'),
(5, 'clan_name', 'Special Task Force', 'Clan name'),
(6, 'clan_acronym', 'STF', 'clan acronym'),
(7, 'kingdom_number', 'K167', 'kingdom number kxxx'),
(8, 'score_color_start_r', '255', 'R (Red) value of the initial color for low score (0-255).'),
(9, 'score_color_start_g', '0', 'G (Green) value of the starting color for low score (0-255).'),
(10, 'score_color_start_b', '0', 'Starting color B (Blue) value for low score (0-255).'),
(11, 'score_color_end_r', '0', 'R (Red) value of the final color for high score (0-255).'),
(12, 'score_color_end_g', '255', 'G (Green) value of the final color for high score (0-255).'),
(13, 'score_color_end_b', '0', 'Final color B (Blue) value for high score (0-255).'),
(14, 'score_color_transition_start', '0.9', 'Value between 0 and 1 (e.g. 0.9 for 90%) that defines the point at which the score color starts to change from the initial color to the final color.'),
(15, 'minimum_epic_chest_score', '6000', 'Minimum epic chest score'),
(16, 'deposit_fee', '50', 'Fixed deposit fee in millions of Silver'),
(17, 'withdrawal_fee', '50', 'Fixed withdrawal fee in millions of Silver'),
(18, 'transfer_fee', '10', 'Fixed transfer fee in millions of Silver'),
(19, 'caravan_fee', '20', 'Caravan fee percentage for deposits'),
(20, 'bank_function', '1', '1 = Bank active / 0 = no Bank');

-- --------------------------------------------------------

--
-- Estrutura para tabela `errors`
--

CREATE TABLE `errors` (
  `id` int(11) NOT NULL,
  `error_value` tinytext NOT NULL,
  `collected_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `start_date` timestamp NOT NULL,
  `end_date` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `incomplete_chests`
--

CREATE TABLE `incomplete_chests` (
  `id` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `player` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `source` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `type` tinyint(4) DEFAULT 0 COMMENT '	0 = auto / 1 = Manual',
  `collected_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `player` varchar(45) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NOT NULL,
  `power` int(11) NOT NULL DEFAULT 0,
  `guards` int(11) NOT NULL DEFAULT 0,
  `specialists` int(11) NOT NULL DEFAULT 0,
  `monsters` int(11) NOT NULL DEFAULT 0,
  `engineers` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `phinxlog`
--

CREATE TABLE `phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `player_cycle_summaries`
--

CREATE TABLE `player_cycle_summaries` (
  `id` int(11) NOT NULL,
  `player_name` varchar(255) NOT NULL,
  `cycle_start_date` date NOT NULL,
  `cycle_end_date` date NOT NULL,
  `total_chests` int(11) NOT NULL DEFAULT 0,
  `total_score` int(11) NOT NULL DEFAULT 0,
  `epic_crypt_score` int(11) NOT NULL DEFAULT 0,
  `goal_achieved` tinyint(1) NOT NULL DEFAULT 0,
  `fine_due` tinyint(1) NOT NULL DEFAULT 0,
  `fine_paid` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `modified` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `player_name_mappings`
--

CREATE TABLE `player_name_mappings` (
  `id` int(11) NOT NULL,
  `ocr_text` varchar(50) NOT NULL,
  `correct_name` varchar(50) NOT NULL,
  `created` timestamp NULL DEFAULT current_timestamp(),
  `modified` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,
  `alias` varchar(20) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `alias`, `created`, `modified`) VALUES
(1, 'admin', 'Administrador', 'admin', '2024-10-06 11:02:02', '2024-10-06 11:02:02'),
(2, 'user', 'Users', 'user', '2024-10-06 11:02:29', '2024-10-06 11:02:29'),
(3, 'bankers', 'Person responsible for managing the clan\'s bank.', 'bankers', '2025-11-16 23:13:05', '2025-11-16 23:13:05');

-- --------------------------------------------------------

--
-- Estrutura para tabela `roles_users`
--

CREATE TABLE `roles_users` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `standard_chests`
--

CREATE TABLE `standard_chests` (
  `id` int(11) NOT NULL,
  `source` char(50) NOT NULL,
  `score` int(11) NOT NULL,
  `monster` int(11) NOT NULL DEFAULT 0 COMMENT '1 = Epic Monsters chest 0 = Regular chest',
  `qty_chest` int(11) DEFAULT NULL COMMENT 'If the chest type is epic monsters, inform the amount of chests earned by killing a monster'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `standard_chests`
--

INSERT INTO `standard_chests` (`id`, `source`, `score`, `monster`, `qty_chest`) VALUES
(1, 'Arena', 0, 0, NULL),
(2, 'Story', 0, 0, NULL),
(3, 'Level 5 Crypt', 0, 0, NULL),
(4, 'Level 10 Crypt', 0, 0, NULL),
(5, 'Level 15 Crypt', 0, 0, NULL),
(6, 'Level 20 Crypt', 3, 0, NULL),
(7, 'Level 25 Crypt', 19, 0, NULL),
(8, 'Level 10 rare Crypt', 0, 0, NULL),
(9, 'Level 15 rare Crypt', 0, 0, NULL),
(10, 'Level 20 rare Crypt', 10, 0, NULL),
(11, 'Level 25 rare Crypt', 30, 0, NULL),
(12, 'Level 30 rare Crypt', 90, 0, NULL),
(13, 'Level 15 epic Crypt', 0, 0, NULL),
(14, 'Level 20 epic Crypt', 35, 0, NULL),
(15, 'Level 25 epic Crypt', 55, 0, NULL),
(16, 'Level 30 epic Crypt', 80, 0, NULL),
(17, 'Level 35 epic Crypt', 120, 0, NULL),
(18, 'Level 10 Citadel', 0, 0, NULL),
(19, 'Level 15 Citadel', 0, 0, NULL),
(20, 'Level 20 Citadel', 10, 0, NULL),
(21, 'Level 25 Citadel', 30, 0, NULL),
(22, 'Level 30 Citadel', 60, 0, NULL),
(23, 'Level 20 cursed Citadel', 10, 0, NULL),
(24, 'Level 25 cursed Citadel', 30, 0, NULL),
(25, 'Wooden Chest', 0, 0, NULL),
(26, 'Bronze Chest', 0, 0, NULL),
(27, 'Silver Chest', 0, 0, NULL),
(28, 'Golden Chest', 0, 0, NULL),
(29, 'Precious Chest', 0, 0, NULL),
(30, 'Magic Chest', 0, 0, NULL),
(31, 'Mercenary Exchange', 0, 0, NULL),
(32, 'Epic Undead squad', 5, 0, NULL),
(33, 'Shadow City', 5, 0, NULL),
(34, 'Level 16 heroic Monster', 5, 0, NULL),
(35, 'Level 17 heroic Monster', 5, 0, NULL),
(36, 'Level 18 heroic Monster', 5, 0, NULL),
(37, 'Level 19 heroic Monster', 5, 0, NULL),
(38, 'Level 20 heroic Monster', 5, 0, NULL),
(39, 'Level 21 heroic Monster', 5, 0, NULL),
(40, 'Level 22 heroic Monster', 5, 0, NULL),
(41, 'Level 23 heroic Monster', 5, 0, NULL),
(42, 'Level 24 heroic Monster', 5, 0, NULL),
(43, 'Level 25 heroic Monster', 10, 0, NULL),
(44, 'Level 26 heroic Monster', 10, 0, NULL),
(45, 'Level 27 heroic Monster', 10, 0, NULL),
(46, 'Level 28 heroic Monster', 10, 0, NULL),
(47, 'Level 29 heroic Monster', 10, 0, NULL),
(48, 'Level 30 heroic Monster', 10, 0, NULL),
(49, 'Level 31 heroic Monster', 30, 0, NULL),
(50, 'Level 32 heroic Monster', 30, 0, NULL),
(51, 'Level 33 heroic Monster', 30, 0, NULL),
(52, 'Level 34 heroic Monster', 30, 0, NULL),
(53, 'Level 35 heroic Monster', 30, 0, NULL),
(54, 'Level 36 heroic Monster', 30, 0, NULL),
(55, 'Level 37 heroic Monster', 30, 0, NULL),
(56, 'Level 38 heroic Monster', 30, 0, NULL),
(57, 'Level 39 heroic Monster', 30, 0, NULL),
(58, 'Level 40 heroic Monster', 30, 0, NULL),
(59, 'Level 41 heroic Monster', 30, 0, NULL),
(60, 'Level 42 heroic Monster', 30, 0, NULL),
(61, 'Level 43 heroic Monster', 30, 0, NULL),
(62, 'Level 44 heroic Monster', 30, 0, NULL),
(63, 'Level 45 heroic Monster', 30, 0, NULL),
(64, 'Authority Rush tournament', 0, 0, NULL),
(65, 'Epic Fenrir squad', 5, 1, 25),
(66, 'Epic Inferno squad', 5, 0, NULL),
(67, 'Epic Jormungandr squad', 5, 0, NULL),
(69, 'Tartaros Crypt level 20', 20, 0, NULL),
(70, 'Tartaros Crypt level 25', 60, 0, NULL),
(71, 'Tartaros Crypt level 30', 90, 0, NULL),
(72, 'Tartaros Crypt level 35', 120, 0, NULL),
(74, 'Hermes\' Store', 10, 0, NULL),
(75, 'Arachne’s Swarm Epic squad', 35, 0, NULL),
(76, 'Shadow City', 5, 0, NULL),
(77, 'Union of Triumph personal reward', 0, 0, NULL),
(78, 'Clan wealth', 0, 0, NULL),
(79, 'Level 45 Vault of the Ancients', 0, 0, NULL),
(80, 'Rise of the Ancients event', 0, 0, NULL),
(81, 'Epic Ancient squad', 0, 0, NULL),
(82, 'Mimic Chest', 0, 0, NULL),
(83, 'Epic Chimera squad', 0, 0, NULL),
(84, 'Epic Basilisk squad', 0, 0, NULL),
(85, 'Alchemy tournament', 0, 0, NULL),
(86, 'Lvl 20-24 Raid Runic squad', 0, 0, NULL),
(87, 'Lvl 45 Raid Runic squad', 0, 0, NULL),
(88, 'Lvl 40-44 Raid Runic squad', 0, 0, NULL),
(89, 'Lvi 30-34 Raid Runic squad', 0, 0, NULL),
(90, 'Tartaros Crypt level 10', 0, 0, NULL),
(91, 'Tartaros Crypt level 15', 0, 0, NULL),
(92, 'Bank', 0, 0, NULL),
(93, 'Level 40-44 Vault of the Ancients', 0, 0, NULL),
(94, 'Level 35-39 Vault of the Ancients', 0, 0, NULL),
(95, 'Hermes’ Store', 0, 0, NULL),
(96, 'Epic Briareus squad', 0, 0, NULL),
(97, 'Level 30-34 Vault of the Ancients', 0, 0, NULL),
(98, 'Event \"Trials of Olympus\"', 0, 0, NULL),
(99, 'Jérmungandr Shop', 0, 0, NULL),
(100, 'Jormungandr Shop', 0, 0, NULL),
(101, 'Epic Chimera squad', 5, 0, NULL),
(102, 'Epic Basilisk squad', 5, 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `member_id` (`member_id`);

--
-- Índices de tabela `bank_approval_logs`
--
ALTER TABLE `bank_approval_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bank_transaction_id` (`bank_transaction_id`),
  ADD KEY `admin_user_id` (`admin_user_id`);

--
-- Índices de tabela `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `destination_member_id` (`destination_member_id`);

--
-- Índices de tabela `collected_chests`
--
ALTER TABLE `collected_chests`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `errors`
--
ALTER TABLE `errors`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `incomplete_chests`
--
ALTER TABLE `incomplete_chests`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_members_users` (`user_id`);

--
-- Índices de tabela `phinxlog`
--
ALTER TABLE `phinxlog`
  ADD PRIMARY KEY (`version`);

--
-- Índices de tabela `player_cycle_summaries`
--
ALTER TABLE `player_cycle_summaries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQUE_PLAYER_CYCLE` (`player_name`,`cycle_start_date`);

--
-- Índices de tabela `player_name_mappings`
--
ALTER TABLE `player_name_mappings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ocr_text` (`ocr_text`);

--
-- Índices de tabela `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `roles_users`
--
ALTER TABLE `roles_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_roles_users_users` (`user_id`),
  ADD KEY `fk_roles_users_roles` (`role_id`);

--
-- Índices de tabela `standard_chests`
--
ALTER TABLE `standard_chests`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `bank_approval_logs`
--
ALTER TABLE `bank_approval_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `bank_transactions`
--
ALTER TABLE `bank_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `collected_chests`
--
ALTER TABLE `collected_chests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `errors`
--
ALTER TABLE `errors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `incomplete_chests`
--
ALTER TABLE `incomplete_chests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `player_cycle_summaries`
--
ALTER TABLE `player_cycle_summaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `player_name_mappings`
--
ALTER TABLE `player_name_mappings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `roles_users`
--
ALTER TABLE `roles_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `standard_chests`
--
ALTER TABLE `standard_chests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD CONSTRAINT `bank_accounts_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `bank_approval_logs`
--
ALTER TABLE `bank_approval_logs`
  ADD CONSTRAINT `bank_approval_logs_ibfk_1` FOREIGN KEY (`bank_transaction_id`) REFERENCES `bank_transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bank_approval_logs_ibfk_2` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD CONSTRAINT `bank_transactions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bank_transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bank_transactions_ibfk_3` FOREIGN KEY (`destination_member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `fk_members_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `roles_users`
--
ALTER TABLE `roles_users`
  ADD CONSTRAINT `fk_roles_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_roles_users_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
