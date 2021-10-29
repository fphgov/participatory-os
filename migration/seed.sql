INSERT INTO `users` (`id`, `username`, `firstname`, `lastname`, `email`, `password`, `role`, `active`, `created_at`, `updated_at`, `hash`) VALUES (1, 'anonymus', 'N/A', 'N/A', 'anonymus@budapest.hu', '-', 'anonymus', 1, '2021-09-20 14:53:28', '2021-09-20 14:53:30', NULL);

INSERT INTO `workflow_states` (`id`, `name`, `description`) VALUES (100, 'RECEIVED', 'Beküldött');
INSERT INTO `workflow_states` (`id`, `name`, `description`) VALUES (110, 'PUBLISHED', 'Közzétéve');
INSERT INTO `workflow_states` (`id`, `name`, `description`) VALUES (120, 'FORMALLY_APPROPRIATE', 'Formailag nem megfelelő');
INSERT INTO `workflow_states` (`id`, `name`, `description`) VALUES (130, 'VOTING_LIST', 'Szavazólistán');
INSERT INTO `workflow_states` (`id`, `name`, `description`) VALUES (140, 'UNDER_CONSTRUCTION', 'Megvalósítás alatt');
INSERT INTO `workflow_states` (`id`, `name`, `description`) VALUES (200, 'READY', 'Megvalósult');
INSERT INTO `workflow_states` (`id`, `name`, `description`) VALUES (510, 'STATUS_REJECTED', 'Elutasított');
INSERT INTO `workflow_states` (`id`, `name`, `description`) VALUES (520, 'FORMALLY_NOT_APPROPRIATE', 'Formailag megfelelő');
INSERT INTO `workflow_states` (`id`, `name`, `description`) VALUES (530, 'COUNCIL_REJECTED', 'Tanács elutasította');
INSERT INTO `workflow_states` (`id`, `name`, `description`) VALUES (540, 'NOT_VOTED', 'Szavazáson nem nyert');

INSERT INTO `phases` (`id`, `campaign_id`, `code`, `title`, `description`, `start`, `end`) VALUES (1, 2, 'PRE_IDEATION', 'Ötlet beküldés előtt', 'Ötlet beküldés előtt', '2021-09-14 12:41:01', '2021-09-30 23:59:59');
INSERT INTO `phases` (`id`, `campaign_id`, `code`, `title`, `description`, `start`, `end`) VALUES (2, 2, 'IDEATION', 'Ötlet beküldés', 'Ötlet beküldés', '2021-10-01 00:00:00', '2021-12-31 23:59:59');
INSERT INTO `phases` (`id`, `campaign_id`, `code`, `title`, `description`, `start`, `end`) VALUES (3, 2, 'POST_IDEATION', 'Ötlet beküldés után', 'Ötlet beküldés után', '2030-01-09 23:59:59', '2030-01-09 23:59:59');
INSERT INTO `phases` (`id`, `campaign_id`, `code`, `title`, `description`, `start`, `end`) VALUES (4, 2, 'CO_CONSTRUCTION', 'Feldolgozás', 'Feldolgozás', '2030-01-09 23:59:59', '2030-01-09 23:59:59');
INSERT INTO `phases` (`id`, `campaign_id`, `code`, `title`, `description`, `start`, `end`) VALUES (5, 2, 'PRE_VOTE', 'Szavazás előtt', 'Szavazás előtt', '2030-01-09 23:59:59', '2030-01-09 23:59:59');
INSERT INTO `phases` (`id`, `campaign_id`, `code`, `title`, `description`, `start`, `end`) VALUES (6, 2, 'VOTE', 'Szavazás', 'Szavazás', '2030-01-09 23:59:59', '2030-01-09 23:59:59');
INSERT INTO `phases` (`id`, `campaign_id`, `code`, `title`, `description`, `start`, `end`) VALUES (7, 2, 'POST_VOTE', 'Szavazás után', 'Szavazás után', '2030-01-09 23:59:59', '2030-01-09 23:59:59');
INSERT INTO `phases` (`id`, `campaign_id`, `code`, `title`, `description`, `start`, `end`) VALUES (8, 2, 'PRE_RESULT', 'Eredmény előtt', 'Eredmény előtt', '2030-01-09 23:59:59', '2030-01-09 23:59:59');
INSERT INTO `phases` (`id`, `campaign_id`, `code`, `title`, `description`, `start`, `end`) VALUES (9, 2, 'RESULT', 'Eredmény', 'Eredmény', '2030-01-09 23:59:59', '2030-01-09 23:59:59');
