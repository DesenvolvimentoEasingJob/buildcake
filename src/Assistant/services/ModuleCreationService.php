<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Module', 'Applications');

class ModuleCreationService {
    private $apiKey;
    private $model;
    private $templatesPath;

    public function __construct() {
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? null;
        $this->model = $_ENV['OPENAI_MODEL'] ?? 'gpt-4o-mini';
        $this->templatesPath = "src/Assistant/templates/";
    }

    /**
     * Analisa o comando/conteúdo usando o template schema_steps.txt e retorna JSON com steps (atividades).
     * Front usa esse JSON para dividir em atividades.
     */
    public function getSteps($data) {
        if (!$this->apiKey) {
            throw new Exception("OPENAI_API_KEY não configurada no .env", 500);
        }
        $content = $data['content'] ?? $data['conversation'] ?? '';
        if ($content === '') {
            throw new Exception("Parâmetro 'content' ou 'conversation' é obrigatório.", 400);
        }
        $path = $this->templatesPath . 'schema_steps.txt';
        if (!file_exists($path)) {
            throw new Exception("Template schema_steps não encontrado.", 404);
        }
        $prompt = str_replace('{{userRequest}}', $content, file_get_contents($path));
        $response = $this->callOpenAI($prompt);
        $json = $this->extractJsonFromResponse($response);
        if (!isset($json['steps']) || !is_array($json['steps'])) {
            throw new Exception("Resposta da IA não contém 'steps'.", 400);
        }
        return [ 'steps' => $json['steps'] ];
    }

    /**
     * Cria o módulo: recebe task e opcionalmente a conversa completa.
     * Com conversation, a IA usa o chat inteiro como contexto para gerar o JSON (campos, etc.)
     * e a task indica qual passo/módulo extrair. Sem conversation, usa só a task.
     */
    public function createModule($data) {
        if (!isset($data['task']) || $data['task'] === '') {
            throw new Exception("Parâmetro 'task' é obrigatório.", 400);
        }
        if (!$this->apiKey) {
            throw new Exception("OPENAI_API_KEY não configurada no .env", 500);
        }
        $path = $this->templatesPath . 'module_creation.txt';
        if (!file_exists($path)) {
            throw new Exception("Template module_creation não encontrado.", 404);
        }
        $conversation = $data['conversation'] ?? $data['content'] ?? '';
        $task = $data['task'];
        $userRequest = ($conversation !== '')
            ? trim($conversation) . "\n\n[Gere o JSON do módulo para ESTE passo: " . $task . "]"
            : $task;
        $prompt = str_replace('{{userRequest}}', $userRequest, file_get_contents($path));
        $response = $this->callOpenAI($prompt);
        $moduleData = $this->extractJsonFromResponse($response);
        if (!is_array($moduleData)) {
            throw new Exception("Resposta da IA não é um objeto de módulo válido.", 400);
        }
        $payload = $this->normalizeForModuleService($moduleData);
        $moduleService = new ModuleService();
        return $moduleService->postModule($payload);
    }

    /**
     * Garante formato esperado por ModuleService::postModule (table_name vazio vira "no_table").
     */
    private function normalizeForModuleService($moduleData) {
        $name = $moduleData['name'] ?? '';
        $module = $moduleData['module'] ?? '';
        $tableName = $moduleData['table_name'] ?? null;
        if ($tableName === null || $tableName === '') {
            $tableName = 'no_table';
        }
        $payload = [
            'name' => $name,
            'module' => $module,
            'table_name' => $tableName,
        ];
        if (!empty($moduleData['fields']) && is_array($moduleData['fields'])) {
            $payload['fields'] = $moduleData['fields'];
        }
        if (!empty($moduleData['foreign_keys']) && is_array($moduleData['foreign_keys'])) {
            $payload['foreign_keys'] = $moduleData['foreign_keys'];
        }
        return $payload;
    }

    private function callOpenAI($prompt) {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $this->model,
                'messages' => [ ['role' => 'user', 'content' => $prompt] ],
                'temperature' => 0.7,
                'max_tokens' => 2000
            ])
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) {
            $err = json_decode($response, true);
            throw new Exception("Erro na API OpenAI: " . ($err['error']['message'] ?? 'Erro desconhecido'), $code);
        }
        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? '';
    }

    private function extractJsonFromResponse($text) {
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);
        $text = trim($text);
        if (preg_match('/\{.*\}/s', $text, $m)) {
            $out = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE) return $out;
        }
        if (preg_match('/\[.*\]/s', $text, $m)) {
            $out = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE) return $out;
        }
        $out = json_decode($text, true);
        if (json_last_error() === JSON_ERROR_NONE) return $out;
        throw new Exception("Não foi possível extrair JSON da resposta da IA.");
    }
}
