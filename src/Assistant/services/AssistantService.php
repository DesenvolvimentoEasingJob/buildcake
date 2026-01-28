<?php

use BuildCake\Utils\Utils;

class AssistantService {
    private $apiKey;
    private $model;
    private $templatesPath;
    
    public function __construct() {
        // Carrega a API key do OpenAI do .env
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? null;
        // Carrega o modelo do .env, padrão: gpt-4o-mini
        $this->model = $_ENV['OPENAI_MODEL'] ?? 'gpt-4o-mini';
        $this->templatesPath = "src/Assistant/templates/";
        
        // Cria a pasta templates se não existir
        if (!is_dir($this->templatesPath)) {
            mkdir($this->templatesPath, 0755, true);
        }
    }
    
    /**
     * Processa uma pergunta usando IA (OpenAI)
     * 
     * @param array $data Dados contendo 'question' e opcionalmente 'template' e outros dados
     * @return array Resposta da IA
     * @throws Exception Se API key não estiver configurada ou erro na chamada
     */
    public function postAssistant($data) {
        if (!$this->apiKey) {
            throw new Exception("OPENAI_API_KEY não configurada no .env", 500);
        }
        
        // Valida se a pergunta foi fornecida
        if (!isset($data['question']) || empty($data['question'])) {
            throw new Exception("Parâmetro 'question' é obrigatório.", 400);
        }
        
        $question = $data['question'];
        $templateName = $data['template'] ?? null;
        
        // Monta o prompt
        $prompt = $this->buildPrompt($question, $templateName, $data);
        
        // Chama a API do OpenAI
        $response = $this->callOpenAI($prompt);
        
        return [
            "response" => $response,
            "template_used" => $templateName,
            "model" => $this->model
        ];
    }
    
    /**
     * Constrói o prompt baseado na pergunta e template (se fornecido)
     * 
     * @param string $question Pergunta do usuário
     * @param string|null $templateName Nome do template a usar (sem extensão)
     * @param array $data Dados adicionais para substituição de variáveis
     * @return string Prompt completo
     */
    private function buildPrompt($question, $templateName = null, $data = []) {
        // Se um template foi especificado, carrega e substitui variáveis
        if ($templateName) {
            $templatePath = $this->templatesPath . $templateName . '.txt';
            
            if (!file_exists($templatePath)) {
                throw new Exception("Template '{$templateName}' não encontrado.", 404);
            }
            
            $templateContent = file_get_contents($templatePath);
            
            // Substitui variáveis do template
            // {{userRequest}} -> pergunta do usuário
            $templateContent = str_replace('{{userRequest}}', $question, $templateContent);
            
            // Para error_correction, substitui dados adicionais
            if (isset($data['moduleData'])) {
                $moduleDataJson = is_string($data['moduleData']) 
                    ? $data['moduleData'] 
                    : json_encode($data['moduleData'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $templateContent = str_replace('{{moduleData}}', $moduleDataJson, $templateContent);
            }
            
            if (isset($data['errorMessage'])) {
                $templateContent = str_replace('{{errorMessage}}', $data['errorMessage'], $templateContent);
            }
            
            return $templateContent;
        }
        
        // Se não há template, adiciona o fileContent após a pergunta separado por ":" (se disponível)
        if (isset($data["moduleData"]["fileContent"]) && !empty($data["moduleData"]["fileContent"])) {
            return $question . ': ' . $data["moduleData"]["fileContent"];
        }
        
        // Se não há fileContent, usa apenas a pergunta
        return $question;
    }
    
    /**
     * Chama a API do OpenAI
     * 
     * @param string $prompt Prompt para enviar
     * @return string Resposta do OpenAI
     * @throws Exception Se houver erro na chamada
     */
    private function callOpenAI($prompt) {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant specialized in the BuildCake framework. BuildCake is a PHP framework for building modular applications. You help users with module creation, database management, API documentation, and general questions about the system. Always respond in Portuguese (Brazilian) unless the user asks otherwise.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3,
            'max_tokens' => 4000
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Erro ao conectar com OpenAI: " . $error, 500);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("Erro ao chamar OpenAI. Código: " . $httpCode . " - Resposta: " . $response, 500);
        }
        
        $responseData = json_decode($response, true);
        
        if (!isset($responseData['choices'][0]['message']['content'])) {
            throw new Exception("Resposta inválida do OpenAI", 500);
        }
        
        $content = $responseData['choices'][0]['message']['content'];
        
        // Remove markdown code blocks se existirem
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);
        
        return $content;
    }
    
}
