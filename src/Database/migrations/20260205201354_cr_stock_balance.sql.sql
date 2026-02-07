CREATE TABLE `stock_balance` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    	`input_id` INT(11) NOT NULL COMMENT 'Identificador único do input',
	`quantity` INT(11) NOT NULL COMMENT 'Quantidade do input',
	`cost_per_unit` DECIMAL(10,4) NOT NULL COMMENT 'Custo por unidade',
	`sell_price` DECIMAL(10,4) NOT NULL COMMENT 'Preço de venda',
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