<?php

use BuildCake\Utils\Utils;
use BuildCake\Framework\Scaffold\ScaffoldFactory;

Utils::IncludeService('Jwt','Authentication');
$userData = JWTService::validateAuth();

$projectRoot = dirname(__DIR__, 2);
$Module = ScaffoldFactory::moduleService($projectRoot);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = $Module->getModule($_GET);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = $_POST ?: (json_decode(file_get_contents('php://input'), true) ?: []);
    
    $response = $Module->postModule($data);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $response = $Module->putModule($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $response = $Module->deleteModule($_DELETE);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}