<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt', 'Authentication');
Utils::IncludeService('Session', 'Authentication');
Utils::IncludeService('User', 'Authentication');
Utils::IncludeService('Login', 'Authentication');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Validar se os campos obrigatórios foram enviados
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        Utils::sendResponse(400, [], 'Email e senha são obrigatórios');
    }

    // Validar se os campos não estão vazios
    if (empty(trim($_POST['email'])) || empty(trim($_POST['password']))) {
        Utils::sendResponse(400, [], 'Email e senha não podem estar vazios');
    }

    try {
        $loginService = new LoginService();
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $result = $loginService->login(
            trim($_POST['email']),
            $_POST['password'],
            $ipAddress,
            $userAgent
        );

        Utils::sendResponse(200, $result, 'Login realizado com sucesso');

    } catch (Exception $e) {
        $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
        Utils::sendResponse($statusCode, [], $e->getMessage());
    }
}

Utils::sendResponse(405, [], 'Método não permitido');
