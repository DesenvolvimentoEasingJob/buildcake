CREATE TABLE `testes` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    	`nome` VARCHAR(255) NOT NULL COMMENT 'Field to store the test name',
	`descricao` TEXT NULL COMMENT 'Field for a detailed description of the test',
	`data_criacao` DATETIME NOT NULL COMMENT 'Field to record the date and time the test was created',
	`status` ENUM('ativo','inativo') NOT NULL,
	`resultado` FLOAT NULL COMMENT 'Field to store the result of the test, if applicable',
	`usuario_id` INT NULL COMMENT 'Field to reference the user who created the test (foreign key)',
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