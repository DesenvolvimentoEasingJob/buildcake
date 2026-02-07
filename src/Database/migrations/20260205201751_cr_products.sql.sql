CREATE TABLE `products` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    	`name` VARCHAR(255) NOT NULL COMMENT 'Nome do produto',
	`description` TEXT NULL COMMENT 'Descrição do produto',
	`category_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'ID da categoria',
	`price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Preço do produto',
	`cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Custo do produto',
	`type` ENUM('composed') NOT NULL DEFAULT 'composed' COMMENT 'Tipo do produto',
	`prep_time_minutes` INT(11) NOT NULL DEFAULT 0 COMMENT 'Tempo de preparo em minutos',
	`image` VARCHAR(255) NULL COMMENT 'URL da imagem do produto',
	`is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `created_by` (`created_by`) USING BTREE,
	INDEX `updated_by` (`updated_by`) USING BTREE,
    	CONSTRAINT `fk_category_id_categories_20260205201751_184263` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
	INDEX `is_active` (`is_active`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;