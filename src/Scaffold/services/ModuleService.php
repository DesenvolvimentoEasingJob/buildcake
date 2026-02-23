<?php

use BuildCake\SqlKit\Sql;
use BuildCake\Utils\Utils;

Utils::IncludeService('Api', 'Applications');
Utils::IncludeService('Service', 'Applications');
Utils::IncludeService('Table', 'Applications');

class ModuleService {
    public function __construct() {}

    function getModule($filters = []){
        $modules = [];
        $srcPath =  "src/"; // Caminho para src a partir deste arquivo
        
        // Escaneia todos os módulos dentro de src
        $moduleDirs = scandir($srcPath);
        
        foreach ($moduleDirs as $module) {
            // Ignora . e ..
            if ($module[0] === '.') continue;
            
            $modulePath = $srcPath . $module;
            
            // Verifica se é um diretório
            if (!is_dir($modulePath)) continue;
            
            $controllers = $this->getControllers($modulePath, $module);
            $services = $this->getServices($modulePath, $module);
            $otherFiles = $this->otherFiles($modulePath, $module);
            $tableName = $this->checkTableExists($module);
            
            $modules[] = [
                "name" => $module,
                "path" => $modulePath,
                "controllers" => $controllers,
                "services" => $services,
                "other_files" => $otherFiles,
                "table_name" => $tableName
            ];
        }

        // Aplica filtros seguindo o mesmo padrão do ApiService
        if($filters){
            $modules = array_filter($modules, function($module) use ($filters) {
                [$key, $value] = [array_key_first($filters), reset($filters)];
                return isset($module[$key]) && $module[$key] == $value;
            });
        }

        // Reindexa o array após o filtro e adiciona ID dinâmico por ordem
        $modules = array_values($modules);
        foreach ($modules as $index => &$module) {
            $module['id'] = $index + 1;
        }
        unset($module); // Remove a referência

        return $modules;
    }

    private function getControllers($modulePath, $module){
        $controllers = [];
        $controllersPath = $modulePath . '/controllers';
        
        if (is_dir($controllersPath)) {
            $controllerFiles = scandir($controllersPath);
            foreach ($controllerFiles as $file) {
                if ($file[0] === '.') continue;
                $controllers[] = [
                    "file" => $file,
                    "path" => $module . '/controllers/' . $file
                ];
            }
        }
        
        return $controllers;
    }

    private function getServices($modulePath, $module){
        $services = [];
        $servicesPath = $modulePath . '/services';
        
        if (is_dir($servicesPath)) {
            $serviceFiles = scandir($servicesPath);
            foreach ($serviceFiles as $file) {
                if ($file[0] === '.') continue;
                $services[] = [
                    "file" => $file,
                    "path" => $module . '/services/' . $file
                ];
            }
        }
        
        return $services;
    }

    private function otherFiles($modulePath, $module){
        $otherFiles = [];
        $excludedDirs = ['controllers', 'services'];
        
        $allItems = scandir($modulePath);
        foreach ($allItems as $item) {
            // Ignora . e ..
            if ($item[0] === '.') continue;
            
            $itemPath = $modulePath . '/' . $item;
            
            // Ignora diretórios controllers e services
            if (is_dir($itemPath) && in_array($item, $excludedDirs)) continue;
            
            // Se for arquivo, adiciona diretamente
            if (is_file($itemPath)) {
                $otherFiles[] = [
                    "file" => $item,
                    "path" => $module . '/' . $item,
                    "type" => "file"
                ];
            } 
            // Se for diretório, adiciona o diretório e escaneia os arquivos dentro
            else if (is_dir($itemPath)) {
                $otherFiles[] = [
                    "file" => $item,
                    "path" => $module . '/' . $item,
                    "type" => "directory"
                ];
                
                // Escaneia arquivos dentro do diretório
                $dirItems = scandir($itemPath);
                foreach ($dirItems as $dirItem) {
                    // Ignora . e ..
                    if ($dirItem[0] === '.') continue;
                    
                    $dirItemPath = $itemPath . '/' . $dirItem;
                    
                    // Adiciona apenas arquivos (não escaneia subdiretórios recursivamente)
                    if (is_file($dirItemPath)) {
                        $otherFiles[] = [
                            "file" => $dirItem,
                            "path" => $module . '/' . $item . '/' . $dirItem,
                            "type" => "file"
                        ];
                    }
                }
            }
        }
        
        return $otherFiles;
    }

    private function checkTableExists($tableName){
        try {
            $existingTables = Sql::runQuery("SELECT TABLE_NAME 
                                            FROM information_schema.TABLES 
                                            WHERE TABLE_SCHEMA = DATABASE() 
                                            AND TABLE_NAME = :table_name", 
                                            ["table_name" => $tableName]);
            $tableExists = !empty($existingTables);
            return $tableExists ? $tableName : "no_table";
        } catch (Exception $e) {
            // Em caso de erro na query, assume que a tabela não existe
            return "no_table";
        }
    }

    function postModule($data){
        
        if (!isset($data['name']) || empty($data['name'])) {
            throw new Exception("Parâmetro 'name' é obrigatório.", 400);
        }

        if (!isset($data['table_name']) || empty($data['table_name'])) {
            throw new Exception("Parâmetro 'table_name' é obrigatório.", 400);
        }

        $module = isset($data['module']) ? $data['module'] : '';
        $table_name = $data['table_name'];
        $name = $data['name'];
        $modulePath = "src/{$module}";
        
        // Valida se o módulo foi informado
        if (empty($module)) {
            throw new Exception("Parâmetro 'module' é obrigatório.", 400);
        }

        if(!is_dir($modulePath)){
            mkdir($modulePath, 0755, true);
            mkdir($modulePath . '/controllers', 0755, true);
            mkdir($modulePath . '/services', 0755, true);
        }


        if(isset($data['fields']) && !empty($data['fields'])){
            $table = new TableService();
            $table->postTable($data);
        }

        $api = new ApiService();
        $api->postApi([
            "name" => $name,
            "module" => $module,
            "table_name" => $table_name,
        ]);

        $service = new ServiceService();
        $service->postService([
            "name" => $name,
            "table_name" => $table_name,
            "module" => $module
        ]);

        return [
            "message" => "Módulo criado com sucesso.",
            "path" => $modulePath,
            "name" => $name
        ];
    }

    function putModule($data){
        $retorno = Sql::runPut("modules",$data);
        return $retorno;
    }

    function deleteModule($data){
        // Extrai o nome do módulo dos dados
        // Pode vir como 'name' ou como chave primária que identifica o módulo
        $moduleName = null;
        
        if (isset($data['name']) && !empty($data['name'])) {
            $moduleName = $data['name'];
        } else {
            // Tenta buscar o módulo no banco para obter o nome
            // Assumindo que os dados contêm um ID ou identificador
            try {
                $modules = Sql::runQuery("SELECT name FROM modules WHERE " . $this->buildWhereClause($data), $data);
                if (!empty($modules) && isset($modules[0]['name'])) {
                    $moduleName = $modules[0]['name'];
                }
            } catch (Exception $e) {
                // Se não conseguir buscar, continua sem excluir documentação
                error_log("Erro ao buscar nome do módulo: " . $e->getMessage());
            }
        }
        
        // Se encontrou o nome do módulo, exclui os arquivos de documentação relacionados
        if ($moduleName) {
            $this->deleteModuleDocumentation($moduleName);
        }
        
        $retorno = Sql::runDelet("modules",$data);
        return $retorno;
    }
    
    /**
     * Exclui todos os arquivos de documentação relacionados a um módulo
     * 
     * @param string $moduleName Nome do módulo
     */
    private function deleteModuleDocumentation($moduleName) {
        $documentsPath = "src/Scaffold/documents/";
        
        // Verifica se a pasta de documentos existe
        if (!is_dir($documentsPath)) {
            return;
        }
        
        // Busca todos os arquivos JSON que começam com o nome do módulo
        $pattern = $documentsPath . $moduleName . "_*.json";
        $files = glob($pattern);
        
        // Exclui cada arquivo encontrado
        foreach ($files as $file) {
            if (is_file($file)) {
                try {
                    unlink($file);
                    error_log("Documentação excluída: " . $file);
                } catch (Exception $e) {
                    error_log("Erro ao excluir documentação " . $file . ": " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Constrói cláusula WHERE para busca no banco
     * 
     * @param array $data Dados com condições
     * @return string Cláusula WHERE
     */
    private function buildWhereClause($data) {
        $conditions = [];
        foreach ($data as $key => $value) {
            $conditions[] = "`{$key}` = :{$key}";
        }
        return implode(' AND ', $conditions);
    }
}