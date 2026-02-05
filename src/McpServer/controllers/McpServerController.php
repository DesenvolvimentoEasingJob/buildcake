<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('McpServer', 'McpServer');

$mcp = new McpServerService();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::sendResponse(405, [], 'Método não permitido. Use POST com body JSON-RPC 2.0.');
}

$raw = file_get_contents('php://input');
if ($raw === false || $raw === '') {
    Utils::sendResponse(400, [], 'Body vazio. Envie um objeto JSON-RPC 2.0 (jsonrpc, method, params?, id?).');
}

$request = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    Utils::sendResponse(400, [], 'JSON inválido.');
}

if (!isset($request['method'])) {
    Utils::sendResponse(400, [], 'Campo "method" é obrigatório.');
}

$response = $mcp->handle($request);

// Notificações (ex: notifications/initialized) não retornam resultado
if ($response === null) {
    header('Content-Type: application/json');
    http_response_code(204);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
http_response_code(200);
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
