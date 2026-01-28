<?php

use BuildCake\SqlKit\Sql;

final class SessionService
{
    public function __construct(){}

    public function getUser($post) {
        $return = Sql::runQuery("SELECT * FROM users WHERE email = :email",$post);

        if(Count($return) > 0) return $return[0];

        return null;
    }

    public function getSession($user,$remoteaddr,$httpuseragent) {
            // Payload do token
            $payload = [
                'sub' => $user['id'],
                'name' => $user['username'],
                'role' => $user['role_id'],
                'exp' => time() + (isset($_ENV['EXPIRE_TOKEN']) ? (int)$_ENV['EXPIRE_TOKEN'] : (24 * 3600))
            ];

            // Gerar token JWT
            $token = JWTService::generateToken($payload);

            // Armazenar token na tabela `sessions`
            $sessionId = Sql::runPost("sessions", [
                'user_id' => $user['id'],
                'token' => $token,
                'ip_address' => $remoteaddr,
                'user_agent' => $httpuseragent
            ],
            $user //passa usuario forçando a criação para o usuario logado pois neste momento ele nao esta logado ainda para registrar como dele
            );

            return [
                'id' => $sessionId,
                'token' => $token,
                'payload' => $payload
            ];
    }

    public function logout($token) {
        // Atualizar a sessão para marcar como revogada
        $sql = "UPDATE sessions SET revoked_at = NOW() WHERE token = :token AND revoked_at IS NULL";
        $params = [[
                "COLUMN_NAME" => "token",
                "VALUE" => $token
            ]
        ];
        
        return Sql::Call()->UpdateParms($params,$sql);
    }
}
