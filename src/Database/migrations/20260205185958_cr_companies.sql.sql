CREATE TABLE `companies` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    	`name` VARCHAR(255) NOT NULL COMMENT 'Nome da empresa',
	`contact` VARCHAR(255) NULL COMMENT 'Nome do contato',
	`phone` VARCHAR(255) NULL COMMENT 'Número de telefone',
	`email` VARCHAR(255) NULL COMMENT 'Endereço de e-mail',
	`is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `created_by` (`created_by`) USING BTREE,
	INDEX `updated_by` (`updated_by`) USING BTREE,
    
	INDEX `is_active` (`is_active`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;