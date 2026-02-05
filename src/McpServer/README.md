# McpServer – Model Context Protocol (MCP)

Módulo que expõe o BuildCake como servidor MCP, permitindo que clientes como **Cursor** ou **Claude Desktop** usem as ferramentas do assistente (editar arquivos, criar módulos, obter steps, perguntas ao assistente) e os prompts (templates).

## Endpoint

- **POST** `{base_url}/McpServer/McpServer`
- **Content-Type:** `application/json`
- **Body:** mensagem JSON-RPC 2.0 (um único objeto por requisição)

## Fluxo típico

1. Cliente envia **initialize** com `protocolVersion`, `capabilities`, `clientInfo`.
2. Servidor responde com `protocolVersion`, `capabilities` (tools, prompts), `serverInfo`.
3. Cliente pode enviar **notifications/initialized** (sem resposta).
4. Cliente usa **tools/list**, **tools/call**, **prompts/list**, **prompts/get** conforme necessário.

## Exemplo: initialize

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "initialize",
  "params": {
    "protocolVersion": "2024-11-05",
    "capabilities": {},
    "clientInfo": { "name": "Cursor", "version": "1.0.0" }
  }
}
```

## Exemplo: tools/list

```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/list"
}
```

## Exemplo: tools/call (edit_file)

```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/call",
  "params": {
    "name": "edit_file",
    "arguments": {
      "filepath": "src/MyModule/controllers/MyController.php",
      "content": "<?php\nclass MyController { }",
      "command": "Adicione um método index que retorne hello world"
    }
  }
}
```

## Tools disponíveis

| Nome             | Descrição |
|-----------------|-----------|
| `edit_file`     | Edita um arquivo conforme comando em linguagem natural (delega ao FileEditService). |
| `get_steps`     | Analisa schema e retorna steps JSON para criação de módulos (ModuleCreationService::getSteps). |
| `create_module` | Cria um módulo a partir de task e opcionalmente conversa (ModuleCreationService::createModule). |
| `assistant_ask` | Pergunta ao assistente BuildCake, com template opcional (AssistantService). |

## Prompts

- **prompts/list**: lista os templates em `src/Assistant/templates/*.txt`.
- **prompts/get**: retorna o conteúdo do template com argumento `userRequest` (e opcionalmente `moduleData`, `errorMessage` para error_correction).

## Configuração no Cursor

Se o Cursor suportar MCP via HTTP (URL do servidor), use a URL do seu backend, por exemplo:

- `https://seu-dominio.com/back/McpServer/McpServer` (POST com body JSON-RPC)

Ou, se houver um proxy/adaptador MCP que converta HTTP para stdio, configure esse adaptador apontando para essa URL.
