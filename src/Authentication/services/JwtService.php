<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use BuildCake\SqlKit\Sql;
use BuildCake\Utils\Utils;

class JWTService {
    private static $secretKey;

    public static function init() {
        self::$secretKey = $_ENV['JWT_SECRET']; // Obtém a chave do ambiente em tempo de execução
    }

    public static function generateToken($payload) {
        if (!self::$secretKey) {
            self::init(); // Garante que a chave foi inicializada
        }
        return JWT::encode($payload, self::$secretKey, 'HS256');
    }

    public static function validateToken($token) {
        if (!self::$secretKey) {
            self::init(); // Garante que a chave foi inicializada
        }
        try {
            return JWT::decode($token, new Key(self::$secretKey, 'HS256'));
        } catch (Exception $e) {
            return false;
        }
    }


    public static function validateAuth($requiredRole = null) {
        $headers = getallheaders();
        $headers = array_change_key_case(getallheaders(), CASE_LOWER); // Normaliza as chaves para minúsculas
        
        if (!isset($headers['authorization'])) {
            Utils::sendResponse(401, [], 'authorization header missing');
        }
    
        $token = str_replace('Bearer ', '', $headers['authorization']);
        $decoded = JWTService::validateToken($token);
    
        if (!$decoded) {
            Utils::sendResponse(401, [], 'Token inválido ou expirado');
        }
    
        // Verificar se o token foi revogado na tabela `sessions`
        $sql = "SELECT * FROM sessions WHERE token = :token AND revoked_at IS NULL";
        $params = ["token" => $token];

        $session = Sql::runQuery($sql, $params);
    
        $sql = "SELECT * FROM sessions WHERE token = :token AND revoked_at IS NULL";
        
        $session = Sql::runQuery($sql, $params);
    
        if (!$session) {
            Utils::sendResponse(401, [], 'Token revogado ou inválido');
        }
    
        // Validar papel do usuário, se necessário
        if ($requiredRole && $decoded->role != $requiredRole) {
            Utils::sendResponse(403, [], 'Acesso negado');
        }
    
        $GLOBALS['currentUser'] = $decoded;
    
        return json_decode(json_encode($decoded), true);
    }
}
