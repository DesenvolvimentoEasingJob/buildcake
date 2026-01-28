CREATE TABLE `Teste` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    	`email` VARCHAR(255) NULL COMMENT 'Endereço de email',
	`enum` ENUM('a','b') NULL COMMENT 'Enumeração (valores separados por vírgula)',
	`user_id` BIGINT(20) UNSIGNED NULL COMMENT 'Foreign key para users.id',
	`is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `created_by` (`created_by`) USING BTREE,
	INDEX `updated_by` (`updated_by`) USING BTREE,
    	CONSTRAINT `fk_user_id_users_20260125232618_6bde6b` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
	CONSTRAINT `fk_user_id_users_20260125232618_0d250e` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
	INDEX `is_active` (`is_active`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;