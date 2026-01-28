<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('ModuleCreation','Assistant');

$ModuleCreation = new ModuleCreationService();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $raw = file_get_contents('php://input');
    $data = ($raw !== false && $raw !== '') ? (json_decode($raw, true) ?: []) : ($_POST ?: []);
    $action = $data['action'] ?? '';

    try {
        if ($action === 'analyze') {
            $response = $ModuleCreation->getSteps($data);
            Utils::sendResponse(200, $response, "Steps gerados com sucesso.");
            return;
        }
        if ($action === 'create_module') {
            $response = $ModuleCreation->createModule($data);
            Utils::sendResponse(200, $response, "Módulo criado com sucesso.");
            return;
        }
        Utils::sendResponse(400, [], "Ação '{$action}' não reconhecida. Use analyze ou create_module.");
    } catch (Exception $e) {
        Utils::sendResponse($e->getCode() ?: 500, [], $e->getMessage());
    }
}

Utils::sendResponse(405, [], 'Método não permitido.');
