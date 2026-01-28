<?php
use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt', 'Authentication');
Utils::IncludeService('User', 'Authentication');
Utils::IncludeService('RefreshToken', 'Authentication');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Obter refresh token do body ou do header
    $refreshToken = null;

    // Tentar obter do body primeiro
    if (isset($_POST['refreshToken'])) {
        $refreshToken = $_POST['refreshToken'];
    } elseif (isset($_POST['refresh_token'])) {
        $refreshToken = $_POST['refresh_token'];
    } else {
        // Tentar obter do header Authorization
        $headers = getallheaders();
        if ($headers) {
            $headers = array_change_key_case($headers, CASE_LOWER);
            if (isset($headers['authorization'])) {
                $authHeader = $headers['authorization'];
                // Pode ser "Bearer <token>" ou apenas o token
                $refreshToken = str_replace('Bearer ', '', $authHeader);
            }
        }
    }

    // Validar se o refresh token foi fornecido
    if (empty($refreshToken)) {
        Utils::sendResponse(400, [], 'Refresh token é obrigatório');
    }

    try {
        $refreshTokenService = new RefreshTokenService();
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $result = $refreshTokenService->refresh(
            trim($refreshToken),
            $ipAddress,
            $userAgent
        );

        Utils::sendResponse(200, $result, 'Tokens atualizados com sucesso');

    } catch (Exception $e) {
        $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
        Utils::sendResponse($statusCode, [], $e->getMessage());
    }
}

Utils::sendResponse(405, [], 'Método não permitido');

