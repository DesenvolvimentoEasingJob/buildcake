<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt','Authentication');
//$userData = JWTService::validateAuth(); 

Utils::IncludeService('Document', 'Scaffold');

$Document = new DocumentService();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $filters = $_GET ?? [];
    $documentations = $Document->getDocument($filters);
    
    // Obtém a versão atual
    $version = $Document->getCurrentVersion();
    
    // Monta a resposta com a estrutura esperada pelo frontend
    $response = [
        'data' => $documentations,
        'version' => $version,
        'title' => 'BuildCake API',
        'description' => 'Documentação completa das APIs do sistema'
    ];
    
    Utils::sendResponse(
      200, 
      $response, 
      "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');
