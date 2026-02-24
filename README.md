# BuildCake

Framework PHP instalável via Composer para criação rápida de APIs RESTful, com geração automática de módulos, controllers, services e tabelas de banco de dados.

Disponível em **duas edições**: **completa** (com usuários, autenticação e assistant) e **enxuta** (apenas Database, Scaffold e DataApi, com JWT stub customizável).

## 🚀 Quick Start

### Instalação via Composer (recomendado)

```bash
composer create-project buildcake/app meu-projeto
cd meu-projeto
```

Quando o pacote está no **Packagist**, o Composer baixa o **dist** (zip), não faz clone — igual Laravel e CakePHP — então **não vem pasta `.git` nem histórico**. O script pós-instalação cria o `.env` a partir de `.env.example` se não existir. (Se você instalar a partir de um clone, o script **post-create-project-cmd** remove o `.git` depois; em seguida pode rodar `git init` no novo projeto.)

Ajuste o `.env` (banco, JWT, etc.) e inicie o servidor ou use Docker.

### Depois de instalar

1. **Configure o ambiente** (se o `.env` foi criado pelo script, revise as variáveis):
   ```bash
   # Opcional: copiar manualmente se preferir
   cp .env.example .env
   ```

2. **Com Docker**:
   ```bash
   docker-compose up -d
   ```

3. **Faça login** (edição completa):
   ```bash
   curl -X POST http://localhost:8000/api/Authentication/Login \
     -H "Content-Type: application/json" \
     -d '{"email":"seu@email.com","password":"sua_senha"}'
   ```

4. **Crie seu primeiro módulo**:
   ```bash
   curl -X POST http://localhost:8000/api/Scaffold/Module \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer {seu_token}" \
     -d '{
       "name":"Product",
       "module":"Products",
       "table_name":"products",
       "fields":[{"name":"name","type":"VARCHAR","length":"255","null":false}],
       "foreign_keys":[],
       "additional_indexes":[]
     }'
   ```

5. **Use a API criada**:
   ```bash
   curl http://localhost:8000/api/Products/Product -H "Authorization: Bearer {seu_token}"
   curl -X POST http://localhost:8000/api/Products/Product \
     -H "Content-Type: application/json" -H "Authorization: Bearer {seu_token}" \
     -d '{"name":"Produto Teste"}'
   ```

## 📋 Índice

- [Visão Geral](#visão-geral)
- [Edições do framework](#edições-do-framework)
- [Arquitetura](#arquitetura)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Autenticação](#autenticação)
- [Endpoints da API](#endpoints-da-api)
- [Criando um Módulo Completo](#criando-um-módulo-completo)
- [Integração Front-end](#integração-front-end)
- [Exemplos de Uso](#exemplos-de-uso)
- [Deploy](#deploy)

## 🎯 Visão Geral

BuildCake é uma plataforma backend que permite criar módulos completos de API através de uma única requisição. Quando você cria um módulo, o sistema automaticamente:

- ✅ Cria a estrutura de diretórios do módulo
- ✅ Gera a tabela no banco de dados com campos customizados
- ✅ Cria o Controller com endpoints REST (GET, POST, PUT, DELETE)
- ✅ Cria o Service com métodos CRUD completos
- ✅ Gera migrations SQL para versionamento
- ✅ Aplica autenticação JWT automaticamente

## 📦 Edições do framework

O BuildCake pode ser servido em duas edições:

| Edição | Módulos incluídos | Uso |
|--------|-------------------|-----|
| **Completa** | Users, Authentication, Database, Scaffold, DataApi, Assistant | Aplicações com login, usuários, sessões e ferramentas de assistente (templates, criação de módulos, edição de arquivos). |
| **Enxuta** | Database, Scaffold, DataApi + Authentication (JWT stub) | APIs sem gestão de usuários; o módulo Authentication expõe um JWT com os mesmos métodos mas que **sempre retornam positivo**, para você customizar como quiser. |

Na edição enxuta, `Utils::IncludeService('Jwt','Authentication')` continua disponível: a interface é a mesma, com implementação “sempre positiva”, para quem for implementar poder substituir ou adaptar sem quebrar Scaffold e DataApi.

## 🏗️ Arquitetura

O projeto segue uma arquitetura modular baseada em convenção:

```
src/
├── {Module}/              # Módulo da aplicação (ex.: Products)
│   ├── controllers/      # Controllers da API
│   └── services/         # Lógica de negócio
├── Scaffold/              # Geração de código (módulos, tabelas, APIs, services)
│   ├── controllers/      # Module, Table, Api, Service, SQL, Document
│   ├── services/
│   └── documents/
├── DataApi/               # API de dados genérica
├── Database/              # Migrations SQL
├── Authentication/       # Login, JWT, sessões (completa) ou JWT stub (enxuta)
│   ├── controllers/      # Login, Logout, RefreshToken, ValidateToken
│   └── services/          # Jwt, User, Session, Login, RefreshToken
├── Users/                 # (edição completa) Usuários, perfis, roles, sessões
└── Assistant/             # (edição completa) Templates, criação de módulos, edição de arquivos
```

### Sistema de Roteamento

O roteamento é baseado em convenção de nomes:

- **Padrão de URL**: `/api/{Module}/{Controller}`
- **Mapeamento**: `src/{Module}/controllers/{Controller}Controller.php`
- **Exemplo**: `/api/Products/Item` → `src/Products/controllers/ItemController.php`

## 📦 Requisitos

- PHP 8.2+
- MySQL 5.7+ ou MariaDB 10.3+
- Composer
- Docker e Docker Compose (opcional)

## 🚀 Instalação

### Opção 1: Composer create-project (recomendado)

```bash
composer create-project buildcake/app meu-projeto
cd meu-projeto
```

- Com o pacote no **Packagist**, o Composer usa **dist** (zip) por padrão — não traz `.git` nem histórico, como Laravel/CakePHP.
- O script **post-install** cria o `.env` a partir de `.env.example` se não existir.
- Se a instalação for por clone (ex.: repo privado), o script **post-create-project-cmd** remove o `.git`; depois você pode rodar `git init`.
- Ajuste o `.env` (banco, `JWT_SECRET`, etc.) e suba o servidor (PHP embutido, Docker ou Apache/Nginx).

**Nota para mantenedores:** para o projeto não vir com `.git` ao baixar (como Laravel/CakePHP), publique o pacote no [Packagist](https://packagist.org). O Packagist gera o **dist** (zip) a partir do repositório; o Composer usa esse zip por padrão e o conteúdo não inclui a pasta `.git`.

### Opção 2: Docker

```bash
# Após criar o projeto com composer create-project (ou clonar o repositório)
cd meu-projeto
# Configure o .env se necessário
docker-compose up -d
# Se tiver clonado sem composer install:
docker-compose exec web composer install
```

A API estará disponível em `http://localhost:8000`.

### Opção 3: Instalação manual (clone + composer)

```bash
git clone <repository-url>
cd backend
composer install
# O .env é criado automaticamente a partir de .env.example se não existir
# Ajuste o .env e configure o servidor web (Apache/Nginx) para a pasta public/
```

## ⚙️ Configuração

Na instalação via `composer create-project` ou `composer install`, o `.env` é criado automaticamente a partir de `.env.example` quando não existir. Caso contrário, crie manualmente na raiz do projeto:

```env
# Ambiente
APP_ENV=development
APP_VERSION=1.0.0

# Banco de Dados
DB_HOST=localhost
DB_PORT=3306
DB_NAME=buildcake_db
DB_USER=root
DB_PASS=password

# JWT
JWT_SECRET=sua_chave_secreta_super_segura_aqui
EXPIRE_TOKEN=86400          # 24 horas em segundos
EXPIRE_REFRESH_TOKEN=604800 # 7 dias em segundos

# Dropbox
DROPBOX_APP_KEY=seu_app_key_aqui
DROPBOX_APP_SECRET=seu_app_secret_aqui
DROPBOX_ACCESS_TOKEN=seu_access_token_aqui
DROPBOX_REFRESH_TOKEN=seu_refresh_token_aqui  # Opcional, mas recomendado para renovação automática

# Sentry (Opcional)
SENTRY_DSN=
```

### Configuração do Banco de Dados

Certifique-se de que o banco de dados existe e está acessível. O sistema criará as tabelas automaticamente conforme você cria módulos.

## 📁 Estrutura do Projeto

```
back/
├── public/
│   ├── index.php                # Ponto de entrada da aplicação
│   └── .htaccess
├── src/
│   ├── Scaffold/                # Geração de módulos, tabelas, APIs, services
│   │   ├── controllers/
│   │   │   ├── ApiController.php
│   │   │   ├── ModuleController.php
│   │   │   ├── ServiceController.php
│   │   │   ├── TableController.php
│   │   │   ├── SQLController.php
│   │   │   └── DocumentController.php
│   │   ├── services/
│   │   └── documents/
│   ├── DataApi/                 # API de dados
│   │   └── controllers/
│   ├── Authentication/
│   │   ├── controllers/         # Login, Logout, RefreshToken, ValidateToken
│   │   └── services/            # Jwt, User, Session, Login, RefreshToken
│   ├── Users/                   # (edição completa)
│   ├── Assistant/               # (edição completa) Templates, ModuleCreation, FileEdit
│   └── Database/
│       └── migrations/          # Migrations SQL geradas automaticamente
├── vendor/                      # Dependências Composer
├── composer.json                # Inclui post-install (cria .env a partir de .env.example)
├── docker-compose.yml
├── Dockerfile
├── .env.example
└── .env                         # Configurações (criado pelo post-install ou manualmente; não versionado)
```

## 🔐 Autenticação

O sistema utiliza JWT (JSON Web Tokens) para autenticação. A maioria dos endpoints requer autenticação via header `Authorization`.

### Login

**Endpoint**: `POST /api/Authentication/Login`

**Request**:
```json
{
  "email": "usuario@exemplo.com",
  "password": "senha123"
}
```

**Response**:
```json
{
  "status": 200,
  "message": "Login realizado com sucesso",
  "data": {
    "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "expiresIn": 86400,
    "refreshExpiresIn": 604800,
    "tokenType": "Bearer",
    "userData": {
      "id": 1,
      "username": "usuario",
      "email": "usuario@exemplo.com",
      "role": "admin"
    },
    "userAbilityRules": []
  }
}
```

### Usando o Token

Inclua o token no header de todas as requisições autenticadas:

```
Authorization: Bearer {accessToken}
```

### Refresh Token

**Endpoint**: `POST /api/Authentication/RefreshToken`

**Request**:
```json
{
  "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

### Logout

**Endpoint**: `POST /api/Authentication/Logout`

**Headers**: `Authorization: Bearer {accessToken}`

## 📡 Endpoints da API

### Endpoints de Gerenciamento

#### Listar Módulos
- **GET** `/api/Scaffold/Module`
- **Query Params**: `name={nome}` (opcional, para filtrar)

#### Criar Módulo Completo
- **POST** `/api/Scaffold/Module`
- Veja seção [Criando um Módulo Completo](#criando-um-módulo-completo)

#### Listar APIs/Controllers
- **GET** `/api/Scaffold/Api`
- **Query Params**: `module={nome}` (opcional)

#### Criar Controller
- **POST** `/api/Scaffold/Api`
- **Body**:
```json
{
  "name": "Product",
  "module": "Products"
}
```

#### Listar Tabelas
- **GET** `/api/Scaffold/Table`
- **Query Params**: `table_name={nome}` (opcional, para ver colunas)

#### Criar Tabela
- **POST** `/api/Scaffold/Table`
- Veja seção [Criando um Módulo Completo](#criando-um-módulo-completo)

#### Listar Services
- **GET** `/api/Scaffold/Service`
- **Query Params**: `module={nome}` (opcional)

## 🎨 Criando um Módulo Completo

A forma mais eficiente de criar um módulo completo é usando o endpoint `/api/Scaffold/Module`, que cria tudo de uma vez.

### Exemplo Básico Completo

Este é um exemplo completo de como criar um módulo que gera automaticamente a API, service e tabela:

```bash
curl --location 'http://localhost:8000/api/Scaffold/Module' \
--header 'Content-Type: application/json' \
--data '{
  "name":"NameTeste",
  "module": "ModuleTeste",
  "table_name": "TableTeste",
  "fields": [
    {
      "name": "Description",
      "type": "VARCHAR",
      "length": "255",
      "null": false,
      "comment": "Nome do teste"
    }
  ],
  "foreign_keys": [],
  "additional_indexes": []
}'
```

Este comando cria:
- Módulo `ModuleTeste` em `src/ModuleTeste/`
- Controller `NameTesteController.php`
- Service `NameTesteService.php`
- Tabela `TableTeste` no banco de dados
- Migration SQL automática

### Exemplo: Criar Módulo "Products"

```bash
curl --location 'http://localhost:8000/api/Scaffold/Module' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer {seu_token}' \
--data '{
  "name": "Product",
  "module": "Products",
  "table_name": "products",
  "fields": [
    {
      "name": "name",
      "type": "VARCHAR",
      "length": "255",
      "null": false,
      "comment": "Nome do produto"
    },
    {
      "name": "description",
      "type": "TEXT",
      "null": true,
      "comment": "Descrição do produto"
    },
    {
      "name": "price",
      "type": "DECIMAL",
      "length": "10,2",
      "null": false,
      "default": "0.00",
      "comment": "Preço do produto"
    },
    {
      "name": "stock",
      "type": "INT",
      "length": "11",
      "null": false,
      "default": "0",
      "comment": "Quantidade em estoque"
    }
  ],
  "foreign_keys": [
    {
      "column": "category_id",
      "references_table": "categories",
      "references_column": "id",
      "name": "fk_products_category",
      "on_delete": "RESTRICT",
      "on_update": "CASCADE"
    }
  ],
  "additional_indexes": [
    {
      "name": "idx_products_name",
      "type": "INDEX",
      "columns": ["name"]
    },
    {
      "name": "idx_products_price",
      "type": "INDEX",
      "columns": ["price"]
    }
  ]
}'
```

### Parâmetros do Request

- `name` (obrigatório): Nome da entidade/controller/service (ex: "Product")
- `module` (opcional): Nome do módulo. Se não fornecido, usa `name`
- `table_name` (obrigatório): Nome da tabela no banco de dados (ex: "products")
- `fields` (obrigatório): Array de campos da tabela
- `foreign_keys` (opcional): Array de chaves estrangeiras
- `additional_indexes` (opcional): Array de índices adicionais

**Nota**: Atualmente, o código usa `name` para criar o módulo e controller. O campo `module` é aceito mas pode não ser utilizado dependendo da versão. O `table_name` é usado para criar a tabela no banco.

### O que é criado automaticamente:

1. **Estrutura de Diretórios**:
   ```
   src/{name}/
   ├── controllers/
   │   └── {name}Controller.php
   └── services/
       └── {name}Service.php
   ```

2. **Tabela no Banco de Dados**:
   - Tabela `{table_name}` com todos os campos especificados
   - Campos padrão: `id`, `is_active`, `created_at`, `updated_at`, `created_by`, `updated_by`
   - Foreign keys e índices configurados

3. **Migration SQL**:
   - Arquivo em `src/Database/migrations/` com timestamp

4. **Controller REST**:
   - GET `/api/{name}/{name}` - Listar registros
   - POST `/api/{name}/{name}` - Criar registro
   - PUT `/api/{name}/{name}` - Atualizar registro
   - DELETE `/api/{name}/{name}` - Deletar registro

   **Exemplo**: Se `name` = "Product", os endpoints serão:
   - GET `/api/Product/Product` - Listar produtos
   - POST `/api/Product/Product` - Criar produto
   - PUT `/api/Product/Product` - Atualizar produto
   - DELETE `/api/Product/Product` - Deletar produto

5. **Service com CRUD**:
   - `get{Name}($filters)` - Buscar registros
   - `insert{Name}($data)` - Inserir registro
   - `edit{Name}($data)` - Editar registro
   - `delet{Name}($data)` - Deletar registro

   **Exemplo**: Se `name` = "Product", os métodos serão:
   - `getProduct($filters)` - Buscar produtos
   - `insertProduct($data)` - Inserir produto
   - `editProduct($data)` - Editar produto
   - `deletProduct($data)` - Deletar produto

### Tipos de Campos Suportados

- **String**: `VARCHAR`, `CHAR`, `TEXT`
- **Numérico**: `INT`, `BIGINT`, `TINYINT`, `SMALLINT`, `MEDIUMINT`
- **Decimal**: `DECIMAL`, `FLOAT`, `DOUBLE`
- **Data/Hora**: `DATE`, `DATETIME`, `TIMESTAMP`, `TIME`
- **Boolean**: `BIT`, `BOOLEAN`
- **Outros**: `JSON`, `BLOB`

### Campos Padrão da Tabela

Todas as tabelas criadas incluem automaticamente:

- `id` (BIGINT UNSIGNED, AUTO_INCREMENT, PRIMARY KEY)
- `is_active` (BIT, DEFAULT 1)
- `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- `updated_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
- `created_by` (BIGINT UNSIGNED, DEFAULT 0)
- `updated_by` (BIGINT UNSIGNED, DEFAULT 0)

## 💻 Integração Front-end

### Configuração Base

```javascript
// config/api.js
const API_BASE_URL = 'http://localhost:8000/api';

const apiClient = {
  async request(endpoint, options = {}) {
    const token = localStorage.getItem('accessToken');
    
    const headers = {
      'Content-Type': 'application/json',
      ...(token && { Authorization: `Bearer ${token}` }),
      ...options.headers,
    };

    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      ...options,
      headers,
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Erro na requisição');
    }

    return data;
  },

  get(endpoint, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    return this.request(`${endpoint}${queryString ? `?${queryString}` : ''}`, {
      method: 'GET',
    });
  },

  post(endpoint, data) {
    return this.request(endpoint, {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  put(endpoint, data) {
    return this.request(endpoint, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  delete(endpoint, data) {
    return this.request(endpoint, {
      method: 'DELETE',
      body: JSON.stringify(data),
    });
  },
};
```

### Autenticação

```javascript
// services/auth.js
export const authService = {
  async login(email, password) {
    const response = await apiClient.post('/Authentication/Login', {
      email,
      password,
    });
    
    if (response.data.accessToken) {
      localStorage.setItem('accessToken', response.data.accessToken);
      localStorage.setItem('refreshToken', response.data.refreshToken);
      localStorage.setItem('userData', JSON.stringify(response.data.userData));
    }
    
    return response.data;
  },

  async logout() {
    await apiClient.post('/Authentication/Logout');
    localStorage.removeItem('accessToken');
    localStorage.removeItem('refreshToken');
    localStorage.removeItem('userData');
  },

  async refreshToken() {
    const refreshToken = localStorage.getItem('refreshToken');
    const response = await apiClient.post('/Authentication/RefreshToken', {
      refreshToken,
    });
    
    localStorage.setItem('accessToken', response.data.accessToken);
    return response.data;
  },

  isAuthenticated() {
    return !!localStorage.getItem('accessToken');
  },

  getUserData() {
    const userData = localStorage.getItem('userData');
    return userData ? JSON.parse(userData) : null;
  },
};
```

### Usando um Módulo Criado

```javascript
// services/products.js
export const productsService = {
  async list(filters = {}) {
    const response = await apiClient.get('/Products/Products', filters);
    return response.data;
  },

  async getById(id) {
    const response = await apiClient.get('/Products/Products', { id });
    return response.data[0];
  },

  async create(productData) {
    const response = await apiClient.post('/Products/Products', productData);
    return response.data;
  },

  async update(id, productData) {
    const response = await apiClient.put('/Products/Products', {
      id,
      ...productData,
    });
    return response.data;
  },

  async delete(id) {
    const response = await apiClient.delete('/Products/Products', { id });
    return response.data;
  },
};
```

### Exemplo de Uso em Componente React

```jsx
import { useState, useEffect } from 'react';
import { productsService } from './services/products';

function ProductsList() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadProducts();
  }, []);

  const loadProducts = async () => {
    try {
      const data = await productsService.list();
      setProducts(data);
    } catch (error) {
      console.error('Erro ao carregar produtos:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleCreate = async (productData) => {
    try {
      await productsService.create(productData);
      loadProducts(); // Recarrega a lista
    } catch (error) {
      console.error('Erro ao criar produto:', error);
    }
  };

  if (loading) return <div>Carregando...</div>;

  return (
    <div>
      <h1>Produtos</h1>
      {products.map(product => (
        <div key={product.id}>
          <h3>{product.name}</h3>
          <p>R$ {product.price}</p>
        </div>
      ))}
    </div>
  );
}
```

## 📚 Exemplos de Uso

### Criar um Módulo de Blog

```bash
curl --location 'http://localhost:8000/api/Scaffold/Module' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer {token}' \
--data '{
  "name": "Post",
  "module": "Blog",
  "table_name": "posts",
  "fields": [
    {
      "name": "title",
      "type": "VARCHAR",
      "length": "255",
      "null": false,
      "comment": "Título do post"
    },
    {
      "name": "content",
      "type": "TEXT",
      "null": false,
      "comment": "Conteúdo do post"
    },
    {
      "name": "author_id",
      "type": "BIGINT",
      "length": "20",
      "null": false,
      "comment": "ID do autor"
    },
    {
      "name": "published_at",
      "type": "DATETIME",
      "null": true,
      "comment": "Data de publicação"
    }
  ],
  "foreign_keys": [
    {
      "column": "author_id",
      "references_table": "users",
      "references_column": "id",
      "on_delete": "CASCADE"
    }
  ],
  "additional_indexes": [
    {
      "name": "idx_posts_published",
      "type": "INDEX",
      "columns": ["published_at"]
    }
  ]
}'
```

### Usar o Módulo Criado

```javascript
// Listar posts
const posts = await apiClient.get('/Blog/Post');

// Criar post
const newPost = await apiClient.post('/Blog/Post', {
  title: 'Meu Primeiro Post',
  content: 'Conteúdo do post...',
  author_id: 1,
  published_at: '2024-01-01 10:00:00'
});

// Atualizar post
await apiClient.put('/Blog/Post', {
  id: 1,
  title: 'Título Atualizado'
});

// Deletar post
await apiClient.delete('/Blog/Post', { id: 1 });
```

## 🚢 Deploy

### Docker Compose

O projeto inclui `docker-compose.yml` para facilitar o deploy:

```yaml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html
    networks:
      - my-network

networks:
  my-network:
    driver: bridge
```

### Variáveis de Ambiente em Produção

Certifique-se de configurar:

- `APP_ENV=production`
- `JWT_SECRET` com uma chave forte e aleatória
- Credenciais de banco de dados seguras
- `SENTRY_DSN` para monitoramento de erros (recomendado)

### Apache/Nginx

Configure o servidor web para:

1. Apontar para o diretório raiz do projeto
2. Redirecionar todas as requisições para `index.php`
3. Habilitar mod_rewrite (Apache) ou configuração equivalente (Nginx)

**Exemplo .htaccess (Apache)**:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ index.php [QSA,L]
```

## 📝 Resposta Padrão da API

Todas as respostas seguem o formato:

```json
{
  "status": 200,
  "message": "Mensagem de sucesso ou erro",
  "data": [],
  "errors": []
}
```

### Códigos de Status HTTP

- `200` - Sucesso
- `400` - Erro de validação/Bad Request
- `401` - Não autenticado
- `403` - Acesso negado
- `404` - Recurso não encontrado
- `405` - Método não permitido
- `500` - Erro interno do servidor

## 🔧 Desenvolvimento

### Estrutura de Templates

Os templates do framework estão no pacote `buildcake/framework`. Para customização na aplicação, consulte a documentação do pacote. Estrutura típica de templates (Scaffold):

- `controller.template` - Template para controllers
- `service.template` - Template para services
- `table.template` - Template para criação de tabelas

### Adicionando Novos Tipos de Campo

Para estender tipos de campo, consulte o pacote `buildcake/framework` (Scaffold): método `generateFieldsSQL()` em `TableService`.

## 📄 Licença

MIT

## 🤝 Contribuindo

felipe@buildcake.com.br

## 📞 Suporte

felipe@buildcake.com.br

