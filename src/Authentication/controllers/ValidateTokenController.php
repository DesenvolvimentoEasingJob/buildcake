<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt', 'Authentication');
Utils::IncludeService('User', 'Authentication');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    try {
        $userData = JWTService::validateAuth();
        
        Utils::sendResponse(200, [
            'valid' => true,
            'userData' => $userData,
            'message' => 'Token válido'
        ], 'Token validado com sucesso');
        
    } catch (Exception $e) {
        Utils::sendResponse(401, [
            'valid' => false,
            'message' => $e->getMessage()
        ], 'Token inválido');
    }
}

Utils::sendResponse(405, [], 'Método não permitido'); 