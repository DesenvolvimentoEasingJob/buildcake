<?php

use BuildCake\Utils\Utils;
Utils::IncludeService('Jwt','Authentication');
$userData = JWTService::validateAuth(); 

Utils::IncludeService('FileEdit', 'Assistant');

$fileEdit = new FileEditService();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lê o JSON do body da requisição
    $json = file_get_contents('php://input');
    $data = json_decode($json, true) ?? [];
    $response = $fileEdit->postFileEdit($data);
    Utils::sendResponse(
        200, 
        $response, 
        "Arquivo editado com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');
