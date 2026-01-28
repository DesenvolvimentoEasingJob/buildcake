CREATE TABLE `storage_history` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    	`occurred_at` DATETIME NOT NULL,
	`qty_delta` DECIMAL(18,6) NOT NULL COMMENT 'positive for incoming, negative for outgoing',
	`reason` ENUM('PURCHASE','SALE','ADJUST','WASTE','TRANSFER','PRODUCTION') NOT NULL,
	`ref_type` VARCHAR(255) NULL COMMENT 'ex: SALE',
	`ref_id` VARCHAR(255) NULL,
	`note` TEXT NULL,
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