<?php

use BuildCake\Utils\Utils;

class DocumentService {
    private $apiKey;
    private $model;
    private $documentsPath;
    private $srcPath;
    private $versionFile;
    
    public function __construct() {
        // Carrega a API key do ChatGPT do .env
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? null;
        // Carrega o modelo do .env, padrão: gpt-4o-mini
        $this->model = $_ENV['OPENAI_MODEL'] ?? 'gpt-4o-mini';
        $this->documentsPath = "src/Applications/documents/";
        $this->srcPath = "src/";
        $this->versionFile = $this->documentsPath . "api_version.json";
        
        // Cria a pasta documents se não existir
        if (!is_dir($this->documentsPath)) {
            mkdir($this->documentsPath, 0755, true);
        }
    }
    
    /**
     * Retorna a documentação das APIs
     * Pode retornar todas, uma específica ou por módulo
     * 
     * @param array $filters Filtros: 'module', 'controller', 'force_refresh'
     * @return array Documentação das APIs com versão
     */
    public function getDocument($filters = []) {
        // Se force_refresh estiver definido, força atualização de todas
        $forceRefresh = isset($filters['force_refresh']) && ($filters['force_refresh'] === 'true' || $filters['force_refresh'] === true);
        
        // Varre todas as controllers
        $controllers = $this->scanControllers();
        
        // Processa cada controller
        $documentations = [];
        $hasChanges = false;
        
        foreach ($controllers as $controller) {
            // Se há filtro de módulo, ignora outros módulos
            if (isset($filters['module']) && $controller['module'] !== $filters['module']) {
                continue;
            }
            
            // Se há filtro de controller, ignora outros controllers
            if (isset($filters['controller'])) {
                $controllerName = $this->extractControllerName($controller['file']);
                if ($controllerName !== $filters['controller']) {
                    continue;
                }
            }
            
            // Gera ou busca documentação
            try {
                $doc = $this->getOrGenerateDocumentation($controller, $forceRefresh, $hasChanges);
                if ($doc) {
                    $documentations[] = $doc;
                }
            } catch (Exception $e) {
                // Log do erro mas continua processando outras controllers
                error_log("Erro ao gerar documentação para {$controller['module']}/{$controller['file']}: " . $e->getMessage());
                continue;
            }
        }
        
        // Se houve mudanças, incrementa a versão
        if ($hasChanges) {
            $this->incrementVersion();
        }
        
        // Obtém a versão atual
        $version = $this->getCurrentVersion();
        
        // Retorna apenas o array de documentações
        // A versão, título e descrição serão adicionadas no controller
        return $documentations;
    }
    
    /**
     * Escaneia todas as controllers do sistema
     * 
     * @return array Array de controllers encontradas
     */
    private function scanControllers() {
        $controllers = [];
        $modules = scandir($this->srcPath);
        
        foreach ($modules as $module) {
            // Ignora . e ..
            if ($module[0] === '.') {
                continue;
            }
            
            $modulePath = $this->srcPath . $module;
            $controllersPath = $modulePath . '/controllers';
            
            // Verifica se é um diretório e se tem a pasta controllers
            if (is_dir($modulePath) && is_dir($controllersPath)) {
                // Escaneia os arquivos dentro de controllers
                $files = scandir($controllersPath);
                
                foreach ($files as $file) {
                    if ($file[0] === '.' || pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                        continue;
                    }
                    
                    $controllerPath = $controllersPath . '/' . $file;
                    $controllers[] = [
                        "module" => $module,
                        "file" => $file,
                        "path" => $module . '/controllers/' . $file,
                        "full_path" => $controllerPath,
                        "modified_time" => filemtime($controllerPath)
                    ];
                }
            }
        }
        
        return $controllers;
    }
    
    /**
     * Extrai o nome do controller do nome do arquivo
     * Ex: ApiController.php -> Api
     * 
     * @param string $filename Nome do arquivo
     * @return string Nome do controller
     */
    private function extractControllerName($filename) {
        return str_replace(['Controller.php', '.php'], '', $filename);
    }
    
    /**
     * Obtém ou gera a documentação de uma controller
     * 
     * @param array $controller Informações da controller
     * @param bool $forceRefresh Força regeneração mesmo se cache existir
     * @param bool &$hasChanges Referência para indicar se houve mudanças
     * @return array|null Documentação da controller
     */
    private function getOrGenerateDocumentation($controller, $forceRefresh = false, &$hasChanges = false) {
        $controllerName = $this->extractControllerName($controller['file']);
        $cacheFile = $this->documentsPath . $controller['module'] . '_' . $controllerName . '.json';
        
        $oldDocumentationHash = null;
        
        // Verifica se existe cache e se não foi modificado
        if (!$forceRefresh && file_exists($cacheFile)) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            
            // Verifica se a controller foi modificada desde o cache
            if (isset($cacheData['controller_modified_time']) && 
                $cacheData['controller_modified_time'] >= $controller['modified_time']) {
                // Cache válido - calcula hash da documentação atual para comparação
                if (isset($cacheData['documentation'])) {
                    $oldDocumentationHash = $this->calculateDocumentationHash($cacheData['documentation']);
                }
                // Retorna cache se não houver mudanças na controller
                return $cacheData['documentation'];
            }
        } else if (file_exists($cacheFile)) {
            // Se forceRefresh, ainda precisamos comparar com a documentação antiga
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            if (isset($cacheData['documentation'])) {
                $oldDocumentationHash = $this->calculateDocumentationHash($cacheData['documentation']);
            }
        }
        
        // Gera nova documentação
        try {
            $documentation = $this->generateDocumentation($controller);
            
            // Calcula hash da nova documentação
            $newDocumentationHash = $this->calculateDocumentationHash($documentation);
            
            // Compara com a documentação anterior para detectar mudanças
            if ($oldDocumentationHash !== null && $oldDocumentationHash !== $newDocumentationHash) {
                $hasChanges = true;
            } else if ($oldDocumentationHash === null) {
                // Primeira vez gerando documentação para esta controller
                $hasChanges = true;
            }
            
            // Salva no cache
            $cacheData = [
                'module' => $controller['module'],
                'controller' => $controllerName,
                'controller_modified_time' => $controller['modified_time'],
                'generated_at' => time(),
                'documentation' => $documentation,
                'documentation_hash' => $newDocumentationHash
            ];
            
            file_put_contents($cacheFile, json_encode($cacheData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            return $documentation;
        } catch (Exception $e) {
            // Em caso de erro, retorna null ou cache antigo se existir
            if (file_exists($cacheFile)) {
                $cacheData = json_decode(file_get_contents($cacheFile), true);
                return $cacheData['documentation'] ?? null;
            }
            return null;
        }
    }
    
    /**
     * Gera documentação usando ChatGPT
     * 
     * @param array $controller Informações da controller
     * @return array Documentação gerada
     */
    private function generateDocumentation($controller) {
        if (!$this->apiKey) {
            throw new Exception("OPENAI_API_KEY não configurada no .env", 500);
        }
        
        // Lê o conteúdo da controller
        $controllerContent = file_get_contents($controller['full_path']);
        
        // Tenta encontrar e ler o service correspondente
        $controllerName = $this->extractControllerName($controller['file']);
        $servicePath = $this->srcPath . $controller['module'] . '/services/' . $controllerName . 'Service.php';
        $serviceContent = '';
        
        if (file_exists($servicePath)) {
            $serviceContent = file_get_contents($servicePath);
        }
        
        // Monta o prompt para o ChatGPT
        $prompt = $this->buildPrompt($controller, $controllerContent, $serviceContent);
        
        // Chama a API do ChatGPT
        $response = $this->callChatGPT($prompt);
        
        // Parseia a resposta JSON
        $documentation = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Tenta extrair JSON se estiver dentro de markdown ou texto
            preg_match('/\{[\s\S]*\}/', $response, $matches);
            if (!empty($matches[0])) {
                $documentation = json_decode($matches[0], true);
            }
        }
        
        if (!$documentation || json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Resposta inválida do ChatGPT. Erro JSON: " . json_last_error_msg() . " - Resposta: " . substr($response, 0, 500), 500);
        }
        
        return $documentation;
    }
    
    /**
     * Constrói o prompt para o ChatGPT
     * 
     * @param array $controller Informações da controller
     * @param string $controllerContent Conteúdo da controller
     * @param string $serviceContent Conteúdo do service
     * @return string Prompt completo
     */
    private function buildPrompt($controller, $controllerContent, $serviceContent) {
        $controllerName = $this->extractControllerName($controller['file']);
        
        $prompt = "Analise o código PHP fornecido e gere uma documentação completa em formato JSON seguindo EXATAMENTE este padrão:

{
  \"module\": \"" . $controller['module'] . "\",
  \"controller\": \"" . $controllerName . "\",
  \"endpoints\": [
    {
      \"method\": \"GET\",
      \"path\": \"/api/" . $controller['module'] . "/" . $controllerName . "\",
      \"description\": \"Descrição clara do que o endpoint faz\",
      \"parameters\": [
        {
          \"name\": \"nome_parametro\",
          \"type\": \"string\",
          \"required\": false,
          \"description\": \"Descrição do parâmetro\",
          \"location\": \"query\"
        }
      ],
      \"responses\": [
        {
          \"status\": 200,
          \"description\": \"Descrição da resposta\",
          \"example\": {}
        }
      ],
      \"authentication\": false,
      \"notes\": \"\"
    }
  ],
  \"summary\": \"Resumo geral da funcionalidade da controller\"
}

REGRAS:
1. Analise TODOS os métodos HTTP (GET, POST, PUT, DELETE) presentes na controller
2. Identifique TODOS os parâmetros usados em cada método (verifique \$_GET, \$_POST, \$filters, \$data)
3. Use o service para entender melhor a lógica de negócio e os parâmetros esperados
4. Se houver JWTService::validateAuth(), marque authentication como true
5. Seja específico e detalhado nas descrições
6. Retorne APENAS o JSON válido, sem markdown, sem texto adicional
7. O campo path deve seguir o padrão /api/{module}/{controller}

CONTROLLER CODE:
{$controllerContent}

SERVICE CODE:
{$serviceContent}

Retorne apenas o JSON:";
        
        return $prompt;
    }
    
    /**
     * Chama a API do ChatGPT
     * 
     * @param string $prompt Prompt para enviar
     * @return string Resposta do ChatGPT
     */
    private function callChatGPT($prompt) {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Você é um especialista em documentação de APIs REST. Sempre retorne apenas JSON válido, sem markdown, sem texto adicional.'
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
            throw new Exception("Erro ao conectar com ChatGPT: " . $error, 500);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("Erro ao chamar ChatGPT. Código: " . $httpCode . " - Resposta: " . $response, 500);
        }
        
        $responseData = json_decode($response, true);
        
        if (!isset($responseData['choices'][0]['message']['content'])) {
            throw new Exception("Resposta inválida do ChatGPT", 500);
        }
        
        $content = $responseData['choices'][0]['message']['content'];
        
        // Remove markdown code blocks se existirem
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);
        
        return $content;
    }
    
    /**
     * Calcula hash da documentação para comparação
     * 
     * @param array $documentation Documentação a ser hasheada
     * @return string Hash MD5 da documentação
     */
    private function calculateDocumentationHash($documentation) {
        // Normaliza a documentação removendo campos que não afetam a versão
        $normalized = [
            'module' => $documentation['module'] ?? '',
            'controller' => $documentation['controller'] ?? '',
            'summary' => $documentation['summary'] ?? '',
            'endpoints' => []
        ];
        
        // Processa endpoints de forma normalizada
        if (isset($documentation['endpoints']) && is_array($documentation['endpoints'])) {
            foreach ($documentation['endpoints'] as $endpoint) {
                $normalizedEndpoint = [
                    'method' => $endpoint['method'] ?? '',
                    'path' => $endpoint['path'] ?? '',
                    'description' => $endpoint['description'] ?? '',
                    'authentication' => $endpoint['authentication'] ?? false,
                    'parameters' => [],
                    'responses' => []
                ];
                
                // Normaliza parâmetros
                if (isset($endpoint['parameters']) && is_array($endpoint['parameters'])) {
                    foreach ($endpoint['parameters'] as $param) {
                        $normalizedEndpoint['parameters'][] = [
                            'name' => $param['name'] ?? '',
                            'type' => $param['type'] ?? '',
                            'required' => $param['required'] ?? false,
                            'location' => $param['location'] ?? '',
                            'description' => $param['description'] ?? ''
                        ];
                    }
                }
                
                // Normaliza respostas
                if (isset($endpoint['responses']) && is_array($endpoint['responses'])) {
                    foreach ($endpoint['responses'] as $response) {
                        $normalizedEndpoint['responses'][] = [
                            'status' => $response['status'] ?? 0,
                            'description' => $response['description'] ?? ''
                        ];
                    }
                }
                
                $normalized['endpoints'][] = $normalizedEndpoint;
            }
        }
        
        // Ordena endpoints para garantir consistência
        usort($normalized['endpoints'], function($a, $b) {
            $methodOrder = ['GET' => 1, 'POST' => 2, 'PUT' => 3, 'PATCH' => 4, 'DELETE' => 5];
            $methodA = $methodOrder[$a['method']] ?? 99;
            $methodB = $methodOrder[$b['method']] ?? 99;
            
            if ($methodA !== $methodB) {
                return $methodA <=> $methodB;
            }
            
            return strcmp($a['path'], $b['path']);
        });
        
        // Gera hash
        return md5(json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
    
    /**
     * Obtém a versão atual da API
     * 
     * @return string Versão no formato X.Y.Z
     */
    public function getCurrentVersion() {
        if (file_exists($this->versionFile)) {
            $versionData = json_decode(file_get_contents($this->versionFile), true);
            if (isset($versionData['version'])) {
                return $versionData['version'];
            }
        }
        
        // Versão inicial
        return '1.0.0';
    }
    
    /**
     * Incrementa a versão da API
     * Segue semântica de versionamento: MAJOR.MINOR.PATCH
     * - PATCH: mudanças pequenas (incrementa automaticamente)
     * 
     * @return string Nova versão
     */
    private function incrementVersion() {
        $currentVersion = $this->getCurrentVersion();
        $parts = explode('.', $currentVersion);
        
        // Garante que temos 3 partes
        while (count($parts) < 3) {
            $parts[] = '0';
        }
        
        // Incrementa PATCH (último número)
        $parts[2] = (int)$parts[2] + 1;
        
        // Se PATCH ultrapassar 9, incrementa MINOR e zera PATCH
        if ($parts[2] >= 10) {
            $parts[2] = 0;
            $parts[1] = (int)$parts[1] + 1;
        }
        
        // Se MINOR ultrapassar 9, incrementa MAJOR e zera MINOR
        if ($parts[1] >= 10) {
            $parts[1] = 0;
            $parts[0] = (int)$parts[0] + 1;
        }
        
        $newVersion = implode('.', $parts);
        
        // Salva a nova versão
        $versionData = [
            'version' => $newVersion,
            'updated_at' => time(),
            'updated_at_formatted' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($this->versionFile, json_encode($versionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $newVersion;
    }
}
