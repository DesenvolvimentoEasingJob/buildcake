<?php

use BuildCake\SqlKit\Sql;

final class LoginService
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * Valida as credenciais do usuário
     * 
     * @param string $email
     * @param string $password
     * @return array|null Retorna o usuário se válido, null caso contrário
     */
    public function validateCredentials($email, $password)
    {
        $user = $this->getUserByEmail($email);

        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        return $user;
    }

    /**
     * Busca usuário por email
     * 
     * @param string $email
     * @return array|null
     */
    private function getUserByEmail($email)
    {
        $result = Sql::runQuery(
            "SELECT * FROM users WHERE email = :email",
            ['email' => $email]
        );

        if (count($result) > 0) {
            return $result[0];
        }

        return null;
    }


    /**
     * Realiza o login do usuário
     * 
     * @param string $email
     * @param string $password
     * @param string $ipAddress
     * @param string $userAgent
     * @return array
     * @throws Exception
     */
    public function login($email, $password, $ipAddress, $userAgent)
    {
        // Validar credenciais
        $user = $this->validateCredentials($email, $password);

        if (!$user) {
            throw new Exception('Credenciais inválidas', 401);
        }

        // Buscar informações da role e montar dados do usuário
        $userRoleData = $this->userService->getUserWithRoleData($user);
        $role = $userRoleData['role'];
        $roleSlug = $this->userService->getRoleSlug($role);

        // Calcular tempos de expiração
        $accessTokenExpiry = isset($_ENV['EXPIRE_TOKEN']) ? (int)$_ENV['EXPIRE_TOKEN'] : (24 * 3600); // Padrão 24 horas
        $refreshTokenExpiry = isset($_ENV['EXPIRE_REFRESH_TOKEN']) ? (int)$_ENV['EXPIRE_REFRESH_TOKEN'] : (7 * 24 * 3600); // Padrão 7 dias

        // Criar payload do access token
        $accessTokenPayload = [
            'sub' => $user['id'],
            'name' => $user['username'],
            'email' => $user['email'],
            'role' => $roleSlug,
            'role_id' => $user['role_id'],
            'iat' => time(),
            'exp' => time() + $accessTokenExpiry,
            'type' => 'access'
        ];

        // Criar payload do refresh token
        $refreshTokenPayload = [
            'sub' => $user['id'],
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + $refreshTokenExpiry
        ];

        // Gerar tokens
        $accessToken = JWTService::generateToken($accessTokenPayload);
        $refreshToken = JWTService::generateToken($refreshTokenPayload);

        // Criar sessão no banco
        $sessionId = Sql::runPost("sessions", [
            'user_id' => $user['id'],
            'token' => $accessToken,
            'refresh_token' => $refreshToken,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ], $user);

        if (!$sessionId) {
            throw new Exception('Erro ao registrar a sessão', 500);
        }

        // Atualizar último login
        Sql::runPut("users", [
            "id" => $user['id'],
            "last_login" => date("Y-m-d H:i:s")
        ]);

        // Usar dados já montados pelo UserService
        $userData = $userRoleData['userData'];
        $userAbilityRules = $userRoleData['userAbilityRules'];

        return [
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
            'expiresIn' => $accessTokenExpiry,
            'refreshExpiresIn' => $refreshTokenExpiry,
            'tokenType' => 'Bearer',
            'userData' => $userData,
            'userAbilityRules' => $userAbilityRules
        ];
    }

}
