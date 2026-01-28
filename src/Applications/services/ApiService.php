<?php

use BuildCake\Utils\Utils;

class ApiService {
    
    public function __construct() {
    }
    /**
     * Lista todos os arquivos dentro das pastas controllers de todos os módulos
     */
    public function getApi($filters = []) {
        $controllers = [];
        $srcPath =  "src/"; // Caminho para src a partir deste arquivo
        
        // Escaneia todos os módulos dentro de src
        $modules = scandir($srcPath);
        
        foreach ($modules as $module) {
            // Ignora . e ..
            if ($module[0] === '.') {continue;}
            
            $modulePath = $srcPath . $module;
            $controllersPath = $modulePath . '/controllers';
            
            // Verifica se é um diretório e se tem a pasta controllers
            if (is_dir($modulePath) && is_dir($controllersPath)) {
                // Escaneia os arquivos dentro de controllers
                $files = scandir($controllersPath);
                
                foreach ($files as $file) {
                    if ($file[0] === '.') continue;

                    $controllers[] = [
                        "module" => $module,
                        "file" => $file,
                        "path" => $module . '/controllers/' . $file
                    ];
                }
            }
        }

        if($filters){
            $controllers = array_filter($controllers, function($controller) use ($filters) {
                [$key, $value] = [array_key_first($filters), reset($filters)];
                return isset($controller[$key]) && $controller[$key] == $value;
            });
        }
        
        return $controllers;
    }

    /**
     * Cria um novo controller baseado no template
     * 
     * @param array $data Dados contendo 'name' => 'ModuleName'
     * @return array Resposta com status, data e message
     * @throws Exception Se o parâmetro name não for fornecido, template não encontrado ou erro ao gravar arquivo
     */
    public function postApi($data) {
        // Valida se o parâmetro name foi fornecido
        if (!isset($data['name']) || empty($data['name'])) {
            throw new Exception("Parâmetro 'name' é obrigatório.", 400);
        }

        if (!isset($data['module']) || empty($data['module'])) {
            throw new Exception("Parâmetro 'module' é obrigatório.", 400);
        }

        $moduleName = $data['module'];
        $name = $data['name'];
        $templatePath = "src/Applications/templates/controller.template";
        
        // Verifica se o template existe
        if (!file_exists($templatePath)) {
            Utils::sendResponse(200, [], "Template não encontrado.");
        }

        // Substitui os placeholders usando Utils::replaceFields
        $controllerContent = Utils::replaceFields(file_get_contents($templatePath), $data);
        
        if(isset($data['filename'])){ $name = $data['filename']; }
        // Define o caminho do arquivo de destino
        $modulePath = "src/{$moduleName}";
        $controllersPath = "{$modulePath}/controllers";
        $controllerFile = "{$controllersPath}/{$name}Controller.php";

        if(file_exists($controllerFile)){
            throw new Exception("Controller já existe.", 400);
        }
        
        // Cria o diretório controllers se não existir
        if (!is_dir($controllersPath)) {
            if (!is_dir($modulePath)) {
                mkdir($modulePath, 0755, true);
            }
            mkdir($controllersPath, 0755, true);
        }
        
        // Grava o arquivo do controller
        if (file_put_contents($controllerFile, $controllerContent) === false) {
            throw new Exception("Erro ao gravar o arquivo do controller.", 500);
        }
        
        return  [
                "message" => "Controller criado com sucesso.",
                "path" => $controllerFile,
                "module" => $moduleName
            ];
    }

    public function putApi($data) {
        
        if (!isset($data['module']) || empty($data['module'])) {
            throw new Exception("Parâmetro 'module' é obrigatório.", 400);
        }

        $moduleName = $data['module'];
        $name = $data['name'];

        $modulePath = "src/{$moduleName}";
        $controllersPath = "{$modulePath}/controllers";
        $controllerFile = "{$controllersPath}/{$name}Controller.php";

        if(!file_exists($controllerFile)){
            Utils::sendResponse(200, [], "Controller não existe.");
        }

        $controllerContent = Utils::replaceFields(file_get_contents($controllerFile), $data);

        if (file_put_contents($controllerFile, $controllerContent) === false) {
            throw new Exception("Erro ao gravar o arquivo do controller.", 500);
        }

        return [
            "message" => "Controller atualizado com sucesso.",
            "path" => $controllerFile,
            "module" => $moduleName
        ];
    }

    public function deleteApi($data) {
        
        if (!isset($data['module']) || empty($data['module'])) {
            throw new Exception("Parâmetro 'module' é obrigatório.", 400);
        }

        $moduleName = $data['module'];
        $name = $data['name'];

        $modulePath = "src/{$moduleName}";
        $controllersPath = "{$modulePath}/controllers";
        $controllerFile = "{$controllersPath}/{$name}Controller.php";

        if(!file_exists($controllerFile)){
            Utils::sendResponse(200, [], "Controller não existe.");
        }

        if(unlink($controllerFile)){
            return [
                "message" => "Controller deletado com sucesso.",
                "path" => $controllerFile,
                "module" => $moduleName
            ];
        }else{
            throw new Exception("Erro ao deletar o controller.", 500);
        }
    }
}

