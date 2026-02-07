CREATE TABLE `ingredients` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    	`name` VARCHAR(255) NOT NULL COMMENT 'Nome do ingrediente',
	`description` TEXT NULL COMMENT 'Descrição do ingrediente',
	`unit` VARCHAR(10) NOT NULL COMMENT 'Unidade de medida',
	`min_quantity` INT(11) NOT NULL DEFAULT 0 COMMENT 'Quantidade mínima',
	`is_addon` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Indica se é um complemento',
	`category_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'ID da categoria',
	`supplier_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'ID do fornecedor',
	`is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `created_by` (`created_by`) USING BTREE,
	INDEX `updated_by` (`updated_by`) USING BTREE,
    	CONSTRAINT `fk_category_id_categories_20260205195021_aaf5d4` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
	CONSTRAINT `fk_supplier_id_suppliers_20260205195021_c47b04` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
	INDEX `is_active` (`is_active`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;