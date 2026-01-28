<?php

use BuildCake\SqlKit\Sql;
use BuildCake\Utils\Utils;

final class RefreshTokenService
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * Busca usuário por ID
     * 
     * @param int $userId
     * @return array|null
     */
    private function getUserById($userId)
    {
        $result = Sql::runQuery(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $userId]
        );

        if (count($result) > 0) {
            return $result[0];
        }

        return null;
    }

    /**
     * Valida o refresh token e retorna os dados decodificados
     * 
     * @param string $refreshToken
     * @return array|false
     */
    private function validateRefreshToken($refreshToken)
    {
        $decoded = JWTService::validateToken($refreshToken);

        if (!$decoded) {
            return false;
        }

        // Converter objeto para array
        $decodedArray = json_decode(json_encode($decoded), true);

        // Verificar se é um refresh token
        if (!isset($decodedArray['type']) || $decodedArray['type'] !== 'refresh') {
            return false;
        }

        return $decodedArray;
    }

    /**
     * Verifica se o refresh token está válido no banco de dados
     * 
     * @param string $refreshToken
     * @return array|false
     */
    private function getSessionByRefreshToken($refreshToken)
    {
        $result = Sql::runQuery(
            "SELECT * FROM sessions WHERE refresh_token = :refresh_token AND revoked_at IS NULL",
            ['refresh_token' => $refreshToken]
        );

        if (count($result) > 0) {
            return $result[0];
        }

        return false;
    }


    /**
     * Atualiza a sessão com novos tokens
     * 
     * @param int $sessionId
     * @param string $accessToken
     * @param string $refreshToken
     * @param array $user
     * @return bool
     */
    private function updateSessionTokens($sessionId, $accessToken, $refreshToken, $user)
    {
        try {
            $result = Sql::runPut("sessions", [
                'id' => $sessionId,
                'token' => $accessToken,
                'refresh_token' => $refreshToken
            ], $user);

            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Gera novos tokens usando o refresh token
     * 
     * @param string $refreshToken
     * @param string $ipAddress
     * @param string $userAgent
     * @return array
     * @throws Exception
     */
    public function refresh($refreshToken, $ipAddress, $userAgent)
    {
        // Validar o refresh token
        $decodedToken = $this->validateRefreshToken($refreshToken);

        if (!$decodedToken) {
            throw new Exception('Refresh token inválido ou expirado', 401);
        }

        // Verificar se o refresh token está no banco e não foi revogado
        $session = $this->getSessionByRefreshToken($refreshToken);

        if (!$session) {
            throw new Exception('Refresh token não encontrado ou revogado', 401);
        }

        // Buscar o usuário
        $user = $this->getUserById($decodedToken['sub']);

        if (!$user) {
            Utils::sendResponse(200, [], 'Usuário não encontrado');
        }

        // Buscar informações da role e montar dados do usuário
        $userRoleData = $this->userService->getUserWithRoleData($user);
        $role = $userRoleData['role'];
        $roleSlug = $this->userService->getRoleSlug($role);

        // Calcular tempos de expiração
        $accessTokenExpiry = isset($_ENV['EXPIRE_TOKEN']) ? (int)$_ENV['EXPIRE_TOKEN'] : (24 * 3600); // Padrão 24 horas
        $refreshTokenExpiry = isset($_ENV['EXPIRE_REFRESH_TOKEN']) ? (int)$_ENV['EXPIRE_REFRESH_TOKEN'] : (7 * 24 * 3600); // Padrão 7 dias

        // Criar payload do novo access token
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

        // Criar payload do novo refresh token
        $refreshTokenPayload = [
            'sub' => $user['id'],
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + $refreshTokenExpiry
        ];

        // Gerar novos tokens
        $newAccessToken = JWTService::generateToken($accessTokenPayload);
        $newRefreshToken = JWTService::generateToken($refreshTokenPayload);

        // Atualizar a sessão com os novos tokens
        $updated = $this->updateSessionTokens($session['id'], $newAccessToken, $refreshToken, $user);

        if (!$updated) {
            throw new Exception('Erro ao atualizar a sessão', 500);
        }

        // Usar dados já montados pelo UserService
        $userData = $userRoleData['userData'];
        $userAbilityRules = $userRoleData['userAbilityRules'];

        return [
            'accessToken' => $newAccessToken,
            'refreshToken' => $newRefreshToken,
            'expiresIn' => $accessTokenExpiry,
            'refreshExpiresIn' => $refreshTokenExpiry,
            'tokenType' => 'Bearer',
            'userData' => $userData,
            'userAbilityRules' => $userAbilityRules
        ];
    }
}

