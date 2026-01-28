<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Service', 'Applications');

$Service = new ServiceService();

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
