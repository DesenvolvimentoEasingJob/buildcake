<?php

use BuildCake\Utils\Utils;

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
    $response = $Table->postTable($_POST);
    Utils::sendResponse(
        200, 
        $response,
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $response = $Table->putTable($_POST);
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