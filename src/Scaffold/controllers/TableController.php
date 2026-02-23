<?php

use BuildCake\Utils\Utils;
Utils::IncludeService('Jwt','Authentication');
$userData = JWTService::validateAuth(); 

Utils::IncludeService('Table', 'Applications');

$Table = new TableService();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = $Table->getTable($_GET);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = $_POST ?: (json_decode(file_get_contents('php://input'), true) ?: []);
    $response = $Table->postTable($data);
    Utils::sendResponse(
        200, 
        $response,
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = $_POST ?: (json_decode(file_get_contents('php://input'), true) ?: []);
    $response = $Table->putTable($data);
    Utils::sendResponse(
        200, 
        $response,
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $response = $Table->deleteTable($_DELETE);
    Utils::sendResponse(
        200, 
        $response,
        "Dados processados com sucesso.");
}