<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt','Authentication');

$userData = JWTService::validateAuth();  // Valida o token JWT

Utils::IncludeService('DropBox', 'DropBox');

$DropBox = new DropBoxService();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = $DropBox->getDropBox($_GET);
    Utils::sendResponse(
       200, 
       $response, 
       "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Se for para trocar código de autorização por tokens
    if (isset($_POST['code']) || isset($_POST['authorization_code'])) {
        $code = $_POST['code'] ?? $_POST['authorization_code'];
        $redirectUri = $_POST['redirect_uri'] ?? null;
        
        $response = $DropBox->exchangeAuthorizationCode($code, $redirectUri);
        Utils::sendResponse(
            200, 
            $response, 
            "Tokens obtidos com sucesso. Salve o refresh_token no .env como DROPBOX_REFRESH_TOKEN.");
    } else {
        // Comportamento original: inserir dados
        $response = $DropBox->insertDropBox($_POST);
        Utils::sendResponse(
            200, 
            $response, 
            "Dados processados com sucesso.");
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $response = $DropBox->editDropBox($_POST);
    Utils::sendResponse(
        200,  
        $response, 
        "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $response = $DropBox->deletDropBox($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');