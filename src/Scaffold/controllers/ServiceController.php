<?php

use BuildCake\Utils\Utils;
use BuildCake\Framework\Scaffold\ScaffoldFactory;

Utils::IncludeService('Jwt','Authentication');
$userData = JWTService::validateAuth();

$projectRoot = dirname(__DIR__, 2);
$Service = ScaffoldFactory::serviceService($projectRoot);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = $Service->getService($_GET);
    Utils::sendResponse(
       200, 
       $response, 
       "Dados processados com sucesso.");

}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = $Service->postService($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $response = $Service->putService($_PUT);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $response = $Service->deleteService($_DELETE);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');
