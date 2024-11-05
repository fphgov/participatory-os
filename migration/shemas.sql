CREATE TABLE `log_audit`
(
    `id`           int(11) unsigned NOT NULL AUTO_INCREMENT,
    `timestamp`    datetime NOT NULL,
    `priority`     int(11) NOT NULL,
    `priorityName` varchar(45) DEFAULT '',
    `message`      longtext NOT NULL,
    `extra`        longtext,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `log_error`
(
    `id`           int(11) unsigned NOT NULL AUTO_INCREMENT,
    `timestamp`    datetime NOT NULL,
    `priority`     int(11) NOT NULL,
    `priorityName` varchar(45) DEFAULT '',
    `message`      longtext NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user_login_attempt`
(
    `id`        int(11) unsigned NOT NULL AUTO_INCREMENT,
    `user_id`   int(11) unsigned NOT NULL,
    `timestamp` datetime NOT NULL,
    `is_failed` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    INDEX       `idx_user_id` (`user_id`),
    INDEX       `idx_timestamp` (`timestamp`),
    INDEX       `idx_is_failed` (`is_failed`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `blacklisted_tokens`
(
    `id`         int(11) unsigned NOT NULL AUTO_INCREMENT,
    `token`      longtext NOT NULL,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`id`),
    INDEX        `idx_token` (`token`),
    INDEX        `idx_created_at` (`created_at`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
