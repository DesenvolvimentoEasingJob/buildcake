CREATE TABLE `product_ingredients` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    	`product_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'ID do produto',
	`input_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'ID do insumo',
	`quantity` INT(11) NOT NULL COMMENT 'Quantidade do insumo',
	`unit` VARCHAR(255) NOT NULL COMMENT 'Unidade de medida',
	`removable` TINYINT(1) NOT NULL COMMENT 'Indica se o insumo é removível',
	`is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `created_by` (`created_by`) USING BTREE,
	INDEX `updated_by` (`updated_by`) USING BTREE,
    	CONSTRAINT `fk_product_id_products_20260205203504_28d7b1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
	CONSTRAINT `fk_input_id_inputs_20260205203504_3e4611` FOREIGN KEY (`input_id`) REFERENCES `inputs` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
	INDEX `is_active` (`is_active`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;