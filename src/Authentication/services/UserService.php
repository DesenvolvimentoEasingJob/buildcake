<?php

use BuildCake\SqlKit\Sql;

final class UserService
{
    /**
     * Busca informações da role do usuário
     * 
     * @param int|null $roleId
     * @return array|null
     */
    public function getUserRole($roleId)
    {
        if (!$roleId) {
            return null;
        }

        $result = Sql::runQuery(
            "SELECT * FROM roles WHERE id = :id",
            ['id' => $roleId]
        );

        if (count($result) > 0) {
            return $result[0];
        }

        return null;
    }

    /**
     * Obtém o slug da role (com fallback para 'user')
     * 
     * @param array|null $role
     * @return string
     */
    public function getRoleSlug($role)
    {
        if ($role && isset($role['slug'])) {
            return $role['slug'];
        }

        return 'user';
    }

    /**
     * Obtém o nome da role (com fallback para 'user')
     * 
     * @param array|null $role
     * @return string
     */
    public function getRoleName($role)
    {
        if ($role && isset($role['name'])) {
            return $role['name'];
        }

        return 'user';
    }

    /**
     * Gera regras de habilidade baseadas na role
     * 
     * @param array|null $role
     * @return array
     */
    public function getUserAbilityRules($role)
    {
        // Se for admin, retorna permissão total
        if ($role && ($role['slug'] === 'admin' || $role['name'] === 'Admin')) {
            return [
                [
                    'action' => 'manage',
                    'subject' => 'all'
                ]
            ];
        }

        // Caso contrário, retorna permissões básicas
        // Você pode ajustar isso conforme sua lógica de permissões
        return [
            [
                'action' => 'read',
                'subject' => 'all'
            ]
        ];
    }

    /**
     * Monta os dados do usuário para retorno na API
     * 
     * @param array $user
     * @param array|null $role
     * @return array
     */
    public function buildUserData($user, $role = null)
    {
        $roleSlug = $this->getRoleSlug($role);

        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $roleSlug,
            'role_id' => $user['role_id'] ?? null,
            'profile_picture' => $user['profile_picture'] ?? "/images/avatars/avatar-1.png"
        ];
    }

    /**
     * Busca role e monta dados completos do usuário (role + userData + abilityRules)
     * 
     * @param array $user
     * @return array ['role' => array|null, 'userData' => array, 'userAbilityRules' => array]
     */
    public function getUserWithRoleData($user)
    {
        $role = $this->getUserRole($user['role_id'] ?? null);
        $userData = $this->buildUserData($user, $role);
        $userAbilityRules = $this->getUserAbilityRules($role);

        return [
            'role' => $role,
            'userData' => $userData,
            'userAbilityRules' => $userAbilityRules
        ];
    }
}

