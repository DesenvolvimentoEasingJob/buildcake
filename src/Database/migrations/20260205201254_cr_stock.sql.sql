CREATE TABLE `stock` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    	`name` VARCHAR(255) NOT NULL COMMENT 'Nome do produto',
	`description` TEXT NULL COMMENT 'Descrição do produto',
	`category` VARCHAR(255) NOT NULL COMMENT 'Categoria do produto',
	`unit` VARCHAR(10) NOT NULL COMMENT 'Unidade de medida',
	`quantity` INT(11) NOT NULL DEFAULT 0 COMMENT 'Quantidade disponível',
	`min_quantity` INT(11) NOT NULL DEFAULT 0 COMMENT 'Quantidade mínima',
	`cost_per_unit` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Custo por unidade',
	`sell_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Preço de venda',
	`is_addon` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Indica se é um complemento',
	`supplier_name` VARCHAR(255) NOT NULL COMMENT 'Nome do fornecedor',
	`supplier_id` BIGINT(20) UNSIGNED NULL COMMENT 'Foreign key para suppliers.id',
	`is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `created_by` (`created_by`) USING BTREE,
	INDEX `updated_by` (`updated_by`) USING BTREE,
    	CONSTRAINT `fk_supplier_id_suppliers_20260205201254_5fc988` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
	INDEX `is_active` (`is_active`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;