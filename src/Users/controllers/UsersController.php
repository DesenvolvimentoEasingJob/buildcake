<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt','Authentication');

$userData = JWTService::validateAuth();  // Valida o token JWT

Utils::IncludeService('Users', 'Users');

$Users = new UsersService();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $filters = $_GET ?? [];
    $response = $Users->getUsers($filters);
    Utils::sendResponse(
       200, 
       $response, 
       "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = $_POST ?? [];
    $response = $Users->insertUsers($data);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = $_POST ?? [];
    $response = $Users->editUsers($data);

    if($response == 1){
        $response = $data;
    }else{
        throw new Exception("Erro ao editar usuário", 500);
    }

    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $data = $_POST ?? [];
    $response = $Users->deletUsers($data);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');