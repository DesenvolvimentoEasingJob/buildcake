CREATE TABLE `orders` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    	`customer_name` VARCHAR(255) NULL,
	`type` ENUM('balcao','comanda','outro') NULL,
	`commanda` INT NULL,
	`status` ENUM('completed','preparing','pending','delivere') NULL,
	`subtotal` DECIMAL(10,2) NULL,
	`discount` DECIMAL(10,2) NULL,
	`total` DECIMAL(10,2) NULL,
	`payment_method` ENUM('pix','debit','money','credit') NULL,
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