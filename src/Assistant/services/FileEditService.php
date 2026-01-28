<?php

use BuildCake\Utils\Utils;

class FileEditService {
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
     * Processa um comando de edição de arquivo usando IA
     * 
     * @param array $data Dados contendo 'filepath', 'content' e 'command'
     * @return array Resposta com o conteúdo editado
     * @throws Exception Se API key não estiver configurada ou erro na chamada
     */
    public function postFileEdit($data) {
        if (!$this->apiKey) {
            throw new Exception("OPENAI_API_KEY não configurada no .env", 500);
        }
        
        // Valida se os parâmetros foram fornecidos
        if (!isset($data['filepath']) || empty($data['filepath'])) {
            throw new Exception("Parâmetro 'filepath' é obrigatório.", 400);
        }

        if (!isset($data['content'])) {
            throw new Exception("Parâmetro 'content' é obrigatório.", 400);
        }

        if (!isset($data['command']) || empty($data['command'])) {
            throw new Exception("Parâmetro 'command' é obrigatório.", 400);
        }

        $filepath = $data['filepath'];
        $content = $data['content'];
        $command = $data['command'];
        
        // Verifica se é um arquivo de controller para usar template específico
        $isController = $this->isControllerFile($filepath);
        $templateName = $isController ? 'file_edit_controller' : 'file_edit';
        
        // Monta o prompt usando template ou prompt padrão
        $prompt = $this->buildPrompt($filepath, $content, $command, $templateName);
        
        // Chama a API do OpenAI
        $editedContent = $this->callOpenAI($prompt);
        
        // Salva o arquivo editado
        $saved = $this->saveFile($filepath, $editedContent);
        
        return [
            "message" => "Arquivo editado com sucesso.",
            "path" => $filepath,
            "content" => $editedContent,
            "template_used" => $templateName,
            "model" => $this->model
        ];
    }
    
    /**
     * Verifica se o arquivo é um controller
     * 
     * @param string $filepath Caminho do arquivo
     * @return bool True se for controller
     */
    private function isControllerFile($filepath) {
        return strpos($filepath, 'controllers') !== false && 
               (strpos($filepath, 'Controller.php') !== false || 
                preg_match('/controllers\/[^\/]+\.php$/', $filepath));
    }
    
    /**
     * Constrói o prompt baseado no template ou prompt padrão
     * 
     * @param string $filepath Caminho do arquivo
     * @param string $content Conteúdo atual do arquivo
     * @param string $command Comando de edição
     * @param string $templateName Nome do template a usar
     * @return string Prompt completo
     */
    private function buildPrompt($filepath, $content, $command, $templateName) {
        $templatePath = $this->templatesPath . $templateName . '.txt';
        
        // Se o template existe, usa ele
        if (file_exists($templatePath)) {
            $templateContent = file_get_contents($templatePath);
            
            // Substitui variáveis do template
            $templateContent = str_replace('{{filepath}}', $filepath, $templateContent);
            $templateContent = str_replace('{{content}}', $content, $templateContent);
            $templateContent = str_replace('{{command}}', $command, $templateContent);
            
            return $templateContent;
        }
        
        // Se não há template, usa prompt padrão
        return "Você é um assistente especializado em edição de código. Edite o seguinte arquivo conforme o comando solicitado.\n\n" .
               "Caminho do arquivo: {$filepath}\n\n" .
               "Comando: {$command}\n\n" .
               "Conteúdo atual do arquivo:\n```\n{$content}\n```\n\n" .
               "Retorne APENAS o código editado completo, sem explicações adicionais, sem markdown code blocks, sem comentários sobre as mudanças. Apenas o código final.";
    }
    
    /**
     * Chama a API do OpenAI
     * 
     * @param string $prompt Prompt para enviar
     * @return string Resposta do OpenAI (conteúdo editado)
     * @throws Exception Se houver erro na chamada
     */
    private function callOpenAI($prompt) {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful code editor assistant. Return only the edited code without explanations, markdown code blocks, or comments about changes.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.2,
            'max_tokens' => 8000
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        
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
        $content = preg_replace('/```php\s*/', '', $content);
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);
        
        return $content;
    }
    
    /**
     * Salva o arquivo editado
     * 
     * @param string $filepath Caminho do arquivo
     * @param string $content Conteúdo a salvar
     * @return bool True se salvou com sucesso
     * @throws Exception Se houver erro ao salvar
     */
    private function saveFile($filepath, $content) {
        // Remove barras no início se houver e garante que comece com src/
        $filepath = ltrim($filepath, '/\\');
        if (strpos($filepath, 'src/') !== 0) {
            $filepath = "src/{$filepath}";
        }
        
        // Verifica se o arquivo existe
        if (!file_exists($filepath)) {
            // Se não existe, cria o diretório se necessário
            $dirPath = dirname($filepath);
            if (!is_dir($dirPath)) {
                if (!mkdir($dirPath, 0755, true)) {
                    throw new Exception("Erro ao criar o diretório.", 500);
                }
            }
        }
        
        // Verifica se é um arquivo e não um diretório
        if (is_dir($filepath)) {
            throw new Exception("O caminho especificado é um diretório, não um arquivo.", 400);
        }
        
        // Grava o conteúdo no arquivo
        if (file_put_contents($filepath, $content) === false) {
            throw new Exception("Erro ao salvar o arquivo editado.", 500);
        }
        
        return true;
    }
}
