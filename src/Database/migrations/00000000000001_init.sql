-- ============================================
-- Migration: Create Authentication Tables
-- Description: Criação inicial das tabelas do módulo de autenticação
-- Date: 2024-01-01 00:00:00
-- ============================================

-- ============================================
-- TABELA: roles
-- Descrição: Perfis/Papéis de acesso do sistema
-- ============================================

CREATE TABLE `status` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) NULL,
    `is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

insert into status (name, description) values ('Ativo', 'Status ativo');
insert into status (name, description) values ('Inativo', 'Status inativo');
insert into status (name, description) values ('Pendente', 'Status pendente');
insert into status (name, description) values ('Cancelado', 'Status cancelado');
insert into status (name, description) values ('Expirado', 'Status expirado');
insert into status (name, description) values ('Bloqueado', 'Status bloqueado');
insert into status (name, description) values ('Suspenso', 'Status suspenso');


CREATE TABLE `roles` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100) NOT NULL COMMENT 'Nome da role (ex: Administrador, Usuário)',
	`slug` VARCHAR(50) NOT NULL COMMENT 'Slug único da role (ex: admin, user)',
	`description` TEXT NULL DEFAULT NULL COMMENT 'Descrição da role',
	`is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `slug` (`slug`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;

INSERT INTO `roles` (`id`, `name`,`slug`, `description`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (1, 'admin','adm', 'Usuário com acesso total ao sistema', b'1', '2025-06-21 21:43:48', '2025-06-21 21:43:48', 0, 0);
INSERT INTO `roles` (`id`, `name`,`slug`, `description`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (2, 'moderator','mod', 'Usuário com permissões intermediárias', b'1', '2025-06-21 21:43:48', '2025-06-21 21:43:48', 0, 0);
INSERT INTO `roles` (`id`, `name`,`slug`, `description`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (3, 'user','usu', 'Usuário regular do sistema', b'1', '2025-06-21 21:43:48', '2025-06-21 21:43:48', 0, 0);
INSERT INTO `roles` (`id`, `name`,`slug`, `description`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (4, 'Colaborador','colab', 'Encarregado de cola borar para o sucesso do empreendimento', b'1', '2025-07-23 21:43:53', '2025-07-23 21:43:53', 0, 0);
INSERT INTO `roles` (`id`, `name`,`slug`, `description`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (5, 'Ponto','pt', 'Usuario para bater o registro de ponto', b'1', '2025-08-08 00:44:37', '2025-08-08 00:44:37', 0, 0);
INSERT INTO `roles` (`id`, `name`,`slug`, `description`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (6, 'Gerente','gr', 'Gerente', b'1', '2025-08-19 08:51:21', '2025-08-19 08:51:21', 0, 0);

-- ============================================
-- TABELA: users
-- Descrição: Usuários do sistema
-- ============================================

CREATE TABLE `users` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role_id` BIGINT(20) UNSIGNED NOT NULL,
    `status_id` BIGINT(20) UNSIGNED NOT NULL,
    `is_active` BIT(1) NOT NULL DEFAULT b'1',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_login` DATETIME NULL DEFAULT NULL,
    `created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    `updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    `profile_picture` VARCHAR(255) NULL DEFAULT NULL,
    `two_factor_enabled` TINYINT(1) NULL DEFAULT '0',
    `two_factor_secret` VARCHAR(255) NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `username` (`username`),
    UNIQUE INDEX `email` (`email`),
    INDEX `role_id` (`role_id`),
    INDEX `status_id` (`status_id`),
    CONSTRAINT `fk_users_role_id` FOREIGN KEY (`role_id`)
        REFERENCES `roles` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_users_status_id` FOREIGN KEY (`status_id`)
        REFERENCES `status` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

insert into users (username, email, password, role_id, status_id) values ('admin', 'admin@admin.com', 'admin', 1, 1);
insert into users (username, email, password, role_id, status_id) values ('user', 'user@user.com', 'user', 2, 1);


-- ============================================
-- TABELA: sessions
-- Descrição: Sessões ativas dos usuários (tokens JWT)
-- ============================================

CREATE TABLE `sessions` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'ID do usuário da sessão',
	`token` VARCHAR(500) NOT NULL COMMENT 'Token JWT de acesso (access token)',
	`refresh_token` VARCHAR(500) NULL DEFAULT NULL COMMENT 'Token JWT de refresh',
	`ip_address` VARCHAR(45) NULL DEFAULT NULL COMMENT 'Endereço IP do cliente',
	`user_agent` VARCHAR(500) NULL DEFAULT NULL COMMENT 'User Agent do navegador/cliente',
	`revoked_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Data e hora de revogação da sessão (logout)',
	`is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `user_id` (`user_id`) USING BTREE,
	INDEX `token` (`token`(255)) USING BTREE,
	INDEX `refresh_token` (`refresh_token`(255)) USING BTREE,
	INDEX `revoked_at` (`revoked_at`) USING BTREE,
	CONSTRAINT `fk_sessions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;


CREATE TABLE `profilefilter` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`profile` BIGINT(20) UNSIGNED NOT NULL,
	`tablename` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`queryfilter` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `is_active` (`is_active`) USING BTREE,
	INDEX `FK_profilefilter_roles` (`profile`) USING BTREE,
	CONSTRAINT `FK_profilefilter_roles` FOREIGN KEY (`profile`) REFERENCES `roles` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;


CREATE TABLE `menu` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`icon` CHAR(60) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`order` INT(11) NOT NULL,
	`type` INT(11) NOT NULL DEFAULT '0',
	`name` VARCHAR(300) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`father` INT(11) NOT NULL DEFAULT '0',
	`link` VARCHAR(2000) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
	`is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `is_active` (`is_active`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;


INSERT INTO `menu` (`id`, `icon`, `order`, `type`, `name`, `father`, `link`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (1, 'ShoppingCart', 1, 0, 'Vendas', 0, '/vendas', b'1', '2025-07-16 21:34:02', '2025-07-16 21:34:02', 1, 1);
INSERT INTO `menu` (`id`, `icon`, `order`, `type`, `name`, `father`, `link`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (2, 'Package', 2, 0, 'Produtos', 0, '/produtos', b'1', '2025-07-16 21:34:02', '2025-07-16 21:34:02', 1, 1);
INSERT INTO `menu` (`id`, `icon`, `order`, `type`, `name`, `father`, `link`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (3, 'Warehouse', 3, 0, 'Estoque', 0, '/estoque', b'1', '2025-07-16 21:34:02', '2025-07-16 21:34:02', 1, 1);
INSERT INTO `menu` (`id`, `icon`, `order`, `type`, `name`, `father`, `link`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (4, 'ChefHat', 4, 0, 'Receitas', 0, '/receitas', b'1', '2025-07-16 21:34:02', '2025-07-16 21:34:02', 1, 1);
INSERT INTO `menu` (`id`, `icon`, `order`, `type`, `name`, `father`, `link`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (5, 'FileText', 5, 0, 'Pedidos', 0, '/pedidos', b'1', '2025-07-16 21:34:02', '2025-07-16 21:34:02', 1, 1);
INSERT INTO `menu` (`id`, `icon`, `order`, `type`, `name`, `father`, `link`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (6, 'DollarSign', 6, 0, 'Caixa', 0, '/caixa', b'1', '2025-07-16 21:34:02', '2025-07-16 21:34:02', 1, 1);
INSERT INTO `menu` (`id`, `icon`, `order`, `type`, `name`, `father`, `link`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (7, 'CreditCard', 7, 0, 'Financeiro', 0, '/financeiro', b'1', '2025-07-16 21:34:02', '2025-07-16 21:34:02', 1, 1);
INSERT INTO `menu` (`id`, `icon`, `order`, `type`, `name`, `father`, `link`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (8, 'BarChart3', 8, 0, 'Relatórios', 0, '/relatorios', b'1', '2025-07-16 21:34:02', '2025-07-16 21:34:02', 1, 1);
INSERT INTO `menu` (`id`, `icon`, `order`, `type`, `name`, `father`, `link`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (9, 'Settings', 11, 0, 'Configurações', 0, '/configuracoes', b'1', '2025-07-16 21:34:02', '2025-07-24 09:29:22', 1, 1);
INSERT INTO `menu` (`id`, `icon`, `order`, `type`, `name`, `father`, `link`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (10, 'Clock', 9, 0, 'Ponto', 0, '/ponto', b'1', '2025-07-16 21:34:02', '2025-07-24 09:29:17', 1, 1);
INSERT INTO `menu` (`id`, `icon`, `order`, `type`, `name`, `father`, `link`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (11, 'BarChart3', 10, 0, 'Relatórios de Ponto', 0, '/relatorios-ponto', b'1', '2025-07-16 21:34:02', '2025-07-24 09:29:20', 1, 1);
INSERT INTO `menu` (`id`, `icon`, `order`, `type`, `name`, `father`, `link`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (12, 'Clock', 12, 0, 'Registro de Ponto', 0, '/registro-ponto-usuarios', b'1', '2025-08-08 00:12:59', '2025-08-08 00:29:00', 0, 0);

CREATE TABLE `menu_user` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`profile` BIGINT(20) UNSIGNED NOT NULL,
	`menu` BIGINT(20) UNSIGNED NOT NULL,
	`is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `is_active` (`is_active`) USING BTREE,
	INDEX `FK_menu_user_roles` (`profile`) USING BTREE,
	INDEX `FK_menu_user_menu` (`menu`) USING BTREE,
	CONSTRAINT `FK_menu_user_menu` FOREIGN KEY (`menu`) REFERENCES `menu` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_menu_user_roles` FOREIGN KEY (`profile`) REFERENCES `roles` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;


INSERT INTO `menu_user` (`id`, `profile`, `menu`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (1, 1, 1, b'0', '2025-07-23 20:56:28', '2025-07-23 21:50:44', 0, 1);
INSERT INTO `menu_user` (`id`, `profile`, `menu`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (14, 1, 1, b'1', '2025-07-23 21:50:45', '2025-07-23 21:50:45', 0, 0);
INSERT INTO `menu_user` (`id`, `profile`, `menu`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (15, 1, 2, b'1', '2025-07-23 21:50:45', '2025-07-23 21:50:45', 0, 0);
INSERT INTO `menu_user` (`id`, `profile`, `menu`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (16, 1, 5, b'1', '2025-07-23 21:50:45', '2025-07-23 21:50:45', 0, 0);
INSERT INTO `menu_user` (`id`, `profile`, `menu`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (17, 1, 8, b'1', '2025-07-23 21:50:45', '2025-07-23 21:50:45', 0, 0);
INSERT INTO `menu_user` (`id`, `profile`, `menu`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (18, 1, 3, b'1', '2025-07-23 21:50:45', '2025-07-23 21:50:45', 0, 0);
INSERT INTO `menu_user` (`id`, `profile`, `menu`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (19, 1, 4, b'1', '2025-07-23 21:50:45', '2025-07-23 21:50:45', 0, 0);
INSERT INTO `menu_user` (`id`, `profile`, `menu`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (20, 1, 11, b'1', '2025-07-23 21:50:46', '2025-07-23 21:50:46', 0, 0);
INSERT INTO `menu_user` (`id`, `profile`, `menu`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (21, 1, 7, b'1', '2025-07-23 21:50:46', '2025-07-23 21:50:46', 0, 0);
INSERT INTO `menu_user` (`id`, `profile`, `menu`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (22, 1, 10, b'1', '2025-07-23 21:50:46', '2025-07-23 21:50:46', 0, 0);
INSERT INTO `menu_user` (`id`, `profile`, `menu`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (23, 1, 6, b'1', '2025-07-23 21:50:46', '2025-07-23 21:50:46', 0, 0);
INSERT INTO `menu_user` (`id`, `profile`, `menu`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (24, 1, 9, b'1', '2025-07-23 21:50:46', '2025-07-23 21:50:46', 0, 0);
INSERT INTO `menu_user` (`id`, `profile`, `menu`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (25, 5, 12, b'1', '2025-08-08 00:45:45', '2025-08-08 00:45:45', 0, 0);


CREATE TABLE `profilefilter` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`profile` BIGINT(3) UNSIGNED NOT NULL,
	`tablename` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`queryfilter` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`is_active` BIT(1) NOT NULL DEFAULT b'1',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`updated_by` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `is_active` (`is_active`) USING BTREE,
	INDEX `FK_profilefilter_roles` (`profile`) USING BTREE,
	CONSTRAINT `FK_profilefilter_roles` FOREIGN KEY (`profile`) REFERENCES `roles` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;

INSERT INTO `profilefilter` (`id`, `profile`, `tablename`, `queryfilter`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (1, 4, 'menu', ' and menu.id in (SELECT menu_user.menu FROM menu_user INNER JOIN users ON menu_user.`profile` = users.role_id\r\nWHERE users.id = :userid)', b'1', '2025-07-23 22:21:19', '2025-07-24 07:10:11', 0, 0);
INSERT INTO `profilefilter` (`id`, `profile`, `tablename`, `queryfilter`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (4, 5, 'menu', ' and menu.id in (SELECT menu_user.menu FROM menu_user INNER JOIN users ON menu_user.`profile` = users.role_id\r\nWHERE users.id = :userid)', b'1', '2025-08-08 00:51:20', '2025-08-08 00:55:18', 0, 0);
INSERT INTO `profilefilter` (`id`, `profile`, `tablename`, `queryfilter`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (2, 4, 'users', ' and users.id = :userid', b'1', '2025-07-23 22:42:46', '2025-07-24 07:10:40', 0, 0);
INSERT INTO `profilefilter` (`id`, `profile`, `tablename`, `queryfilter`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES (3, 5, 'users', ' and users.id not in (0,1,15)', b'1', '2025-08-08 00:46:24', '2025-08-08 16:01:39', 0, 0);

