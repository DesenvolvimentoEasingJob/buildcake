<?php

use BuildCake\Utils\Utils;
Utils::IncludeService('Jwt','Authentication');
$userData = JWTService::validateAuth(); 

Utils::IncludeService('Template', 'Assistant');

$Template = new TemplateService();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $filters = $_GET ?? [];
    $response = $Template->getTemplate($filters);
    Utils::sendResponse(
       200, 
       $response, 
       "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');
