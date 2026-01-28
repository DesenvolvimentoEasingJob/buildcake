CREATE TABLE `workshift_hours` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    	`workshift_id` BIGINT(20) UNSIGNED NOT NULL,
	`weekday` TINYINT(4) NOT NULL,
	`start_time` TIME NOT NULL,
	`end_time` TIME NOT NULL,
	`break_minutes` INT(11) NOT NULL,
	`is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `created_by` (`created_by`) USING BTREE,
	INDEX `updated_by` (`updated_by`) USING BTREE,
    	CONSTRAINT `fk_workshift_id_workshift_20251202021026_f5af2d` FOREIGN KEY (`workshift_id`) REFERENCES `workshift` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
	INDEX `is_active` (`is_active`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;