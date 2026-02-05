<?php

use BuildCake\Utils\Utils;

/**
 * MCP Server (Model Context Protocol) - expõe Tools e Prompts do BuildCake.
 * Delega para os services do Assistant (FileEdit, ModuleCreation, Template, Assistant).
 */
class McpServerService
{
    private const PROTOCOL_VERSION = '2024-11-05';
    private const SERVER_NAME = 'BuildCake MCP';
    private const SERVER_VERSION = '1.0.0';

    private $fileEditService;
    private $moduleCreationService;
    private $templateService;
    private $assistantService;

    public function __construct()
    {
        Utils::IncludeService('FileEdit', 'Assistant');
        Utils::IncludeService('ModuleCreation', 'Assistant');
        Utils::IncludeService('Template', 'Assistant');
        Utils::IncludeService('Assistant', 'Assistant');
        $this->fileEditService = new FileEditService();
        $this->moduleCreationService = new ModuleCreationService();
        $this->templateService = new TemplateService();
        $this->assistantService = new AssistantService();
    }

    /**
     * Processa uma mensagem JSON-RPC 2.0 e retorna a resposta (ou null para notificações).
     *
     * @param array $request { jsonrpc, method, params?, id? }
     * @return array|null Resposta JSON-RPC ou null para notificação
     */
    public function handle(array $request): ?array
    {
        $id = $request['id'] ?? null;
        $method = $request['method'] ?? '';
        $params = $request['params'] ?? [];

        // Notificações não têm id e não retornam resultado
        if ($id === null && isset($request['method'])) {
            if ($method === 'notifications/initialized') {
                return null;
            }
        }

        try {
            $result = match ($method) {
                'initialize' => $this->handleInitialize($params),
                'tools/list' => $this->handleToolsList($params),
                'tools/call' => $this->handleToolsCall($params),
                'prompts/list' => $this->handlePromptsList($params),
                'prompts/get' => $this->handlePromptsGet($params),
                'ping' => [],
                default => throw new \InvalidArgumentException("Method not found: {$method}", -32601)
            };

            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => $result
            ];
        } catch (\Throwable $e) {
            $code = $e->getCode();
            if (!is_int($code) || $code > -32000) {
                $code = -32603; // Internal error
            }
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => $code,
                    'message' => $e->getMessage()
                ]
            ];
        }
    }

    private function handleInitialize(array $params): array
    {
        $clientProtocol = $params['protocolVersion'] ?? '';
        $version = ($clientProtocol === self::PROTOCOL_VERSION) ? self::PROTOCOL_VERSION : self::PROTOCOL_VERSION;

        return [
            'protocolVersion' => $version,
            'capabilities' => [
                'tools' => ['listChanged' => true],
                'prompts' => ['listChanged' => true]
            ],
            'serverInfo' => [
                'name' => self::SERVER_NAME,
                'version' => self::SERVER_VERSION
            ]
        ];
    }

    private function handleToolsList(array $params): array
    {
        $tools = [
            [
                'name' => 'edit_file',
                'description' => 'Edita um arquivo do projeto conforme um comando em linguagem natural. Use para alterar controllers, services ou qualquer arquivo PHP/texto.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'filepath' => ['type' => 'string', 'description' => 'Caminho do arquivo (ex: src/Module/controllers/MyController.php)'],
                        'content' => ['type' => 'string', 'description' => 'Conteúdo atual do arquivo'],
                        'command' => ['type' => 'string', 'description' => 'Comando de edição em linguagem natural']
                    ],
                    'required' => ['filepath', 'content', 'command']
                ]
            ],
            [
                'name' => 'get_steps',
                'description' => 'Analisa uma especificação de schema (tabelas/campos) e retorna steps em JSON para criação de módulos em ordem de dependência.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'content' => ['type' => 'string', 'description' => 'Descrição do schema ou conversa com a especificação']
                    ],
                    'required' => ['content']
                ]
            ],
            [
                'name' => 'create_module',
                'description' => 'Cria um módulo no BuildCake a partir de uma task e opcionalmente do contexto da conversa. Retorna o resultado da criação.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'task' => ['type' => 'string', 'description' => 'Descrição do passo/módulo a criar'],
                        'conversation' => ['type' => 'string', 'description' => 'Contexto da conversa (opcional)']
                    ],
                    'required' => ['task']
                ]
            ],
            [
                'name' => 'assistant_ask',
                'description' => 'Envia uma pergunta ao assistente BuildCake (especializado em módulos, API, banco). Pode usar um template (ex: error_correction) e dados adicionais.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'question' => ['type' => 'string', 'description' => 'Pergunta ou solicitação'],
                        'template' => ['type' => 'string', 'description' => 'Nome do template (opcional, ex: error_correction)'],
                        'moduleData' => ['type' => 'object', 'description' => 'Dados do módulo (opcional)'],
                        'errorMessage' => ['type' => 'string', 'description' => 'Mensagem de erro (opcional, para error_correction)']
                    ],
                    'required' => ['question']
                ]
            ]
        ];

        return ['tools' => $tools];
    }

    private function handleToolsCall(array $params): array
    {
        $name = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];

        try {
            $text = match ($name) {
                'edit_file' => $this->callEditFile($arguments),
                'get_steps' => $this->callGetSteps($arguments),
                'create_module' => $this->callCreateModule($arguments),
                'assistant_ask' => $this->callAssistantAsk($arguments),
                default => throw new \InvalidArgumentException("Unknown tool: {$name}", -32602)
            };
            return [
                'content' => [['type' => 'text', 'text' => $text]],
                'isError' => false
            ];
        } catch (\Throwable $e) {
            return [
                'content' => [['type' => 'text', 'text' => $e->getMessage()]],
                'isError' => true
            ];
        }
    }

    private function callEditFile(array $args): string
    {
        $data = [
            'filepath' => $args['filepath'] ?? '',
            'content' => $args['content'] ?? '',
            'command' => $args['command'] ?? ''
        ];
        $result = $this->fileEditService->postFileEdit($data);
        return json_encode([
            'message' => $result['message'] ?? 'Arquivo editado.',
            'path' => $result['path'] ?? null,
            'content' => $result['content'] ?? null
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function callGetSteps(array $args): string
    {
        $data = ['content' => $args['content'] ?? '', 'conversation' => $args['content'] ?? ''];
        $result = $this->moduleCreationService->getSteps($data);
        return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function callCreateModule(array $args): string
    {
        $data = [
            'task' => $args['task'] ?? '',
            'conversation' => $args['conversation'] ?? ''
        ];
        $result = $this->moduleCreationService->createModule($data);
        return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function callAssistantAsk(array $args): string
    {
        $data = [
            'question' => $args['question'] ?? '',
            'template' => $args['template'] ?? null,
            'moduleData' => $args['moduleData'] ?? null,
            'errorMessage' => $args['errorMessage'] ?? null
        ];
        $result = $this->assistantService->postAssistant($data);
        return $result['response'] ?? json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function handlePromptsList(array $params): array
    {
        $templates = $this->templateService->getTemplate([]);
        $prompts = [];
        foreach ($templates as $t) {
            $prompts[] = [
                'name' => $t['name'],
                'description' => 'Template BuildCake: ' . $t['name'],
                'arguments' => [
                    ['name' => 'userRequest', 'description' => 'Texto ou pedido do usuário', 'required' => true]
                ]
            ];
        }
        return ['prompts' => $prompts];
    }

    private function handlePromptsGet(array $params): array
    {
        $name = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];
        $userRequest = $arguments['userRequest'] ?? '';

        $content = $this->templateService->getTemplateContent($name);
        if ($content === null) {
            throw new \InvalidArgumentException("Prompt not found: {$name}", -32602);
        }
        $content = str_replace('{{userRequest}}', $userRequest, $content);
        if (isset($arguments['moduleData'])) {
            $moduleDataJson = is_string($arguments['moduleData'])
                ? $arguments['moduleData']
                : json_encode($arguments['moduleData'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $content = str_replace('{{moduleData}}', $moduleDataJson, $content);
        }
        if (isset($arguments['errorMessage'])) {
            $content = str_replace('{{errorMessage}}', $arguments['errorMessage'], $content);
        }

        return [
            'description' => 'Template BuildCake: ' . $name,
            'messages' => [
                ['role' => 'user', 'content' => ['type' => 'text', 'text' => $content]]
            ]
        ];
    }
}
