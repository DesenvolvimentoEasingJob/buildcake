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



