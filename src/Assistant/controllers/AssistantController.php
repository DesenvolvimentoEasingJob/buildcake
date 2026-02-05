<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Assistant', 'Assistant');

$Assistant = new AssistantService();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Aceita tanto POST form quanto JSON
    $data = $_POST ?? json_decode(file_get_contents('php://input'), true) ?? [];
    $response = $Assistant->postAssistant($data);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');

// william o comentario