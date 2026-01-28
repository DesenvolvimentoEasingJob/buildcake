<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('File', 'Applications');

$file = new FileService();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = $file->getFile($_GET);
    Utils::sendResponse(
       200, 
       $response, 
       "Dados processados com sucesso.");

}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = $file->postFile($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $response = $file->putFile($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $response = $file->deleteFile($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');

