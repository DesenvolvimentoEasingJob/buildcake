<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt', 'Authentication');
Utils::IncludeService('Session', 'Authentication');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Validar autenticação do usuário
    $userData = JWTService::validateAuth();
    
    if (!$userData) {
        Utils::sendResponse(401, [], 'Token inválido ou expirado');
    }

    // Obter o token do header Authorization
    $headers = getallheaders();
    $headers = array_change_key_case($headers, CASE_LOWER);
    $token = str_replace('Bearer ', '', $headers['authorization']);

    // Usar o SessionService para fazer logout
    $sessionService = new SessionService();
    $updated = $sessionService->logout($token);

    if (!$updated) {
        Utils::sendResponse(500, [], 'Erro ao revogar o token');
    }

    Utils::sendResponse(200, [], 'Logout realizado com sucesso');
}

Utils::sendResponse(405, [], 'Método não permitido');
