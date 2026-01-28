<?php

use BuildCake\Utils\Utils;

class ServiceService {
    
    public function __construct() {
    }
    /**
     * Lista todos os arquivos dentro das pastas services de todos os módulos
     */
    public function getService($filters = []) {
        $services = [];
        $srcPath =  "src/"; // Caminho para src a partir deste arquivo
        
        // Escaneia todos os módulos dentro de src
        $modules = scandir($srcPath);
        
        foreach ($modules as $module) {
            // Ignora . e ..
            if ($module[0] === '.') {continue;}
            
            $modulePath = $srcPath . $module;
            $servicesPath = $modulePath . '/services';
            
            // Verifica se é um diretório e se tem a pasta services
            if (is_dir($modulePath) && is_dir($servicesPath)) {
                // Escaneia os arquivos dentro de services
                $files = scandir($servicesPath);
                
                foreach ($files as $file) {
                    if ($file[0] === '.') continue;

                    $services[] = [
                        "module" => $module,
                        "file" => $file,
                        "path" => $module . '/services/' . $file
                    ];
                }
            }
        }

        if($filters){
            $services = array_filter($services, function($Service) use ($filters) {
                [$key, $value] = [array_key_first($filters), reset($filters)];
                return isset($Service[$key]) && $Service[$key] == $value;
            });
        }
        
        return $services;
    }

    /**
     * Cria um novo Service baseado no template
     * 
     * @param array $data Dados contendo 'name' => 'ModuleName'
     * @return array Resposta com status, data e message
     * @throws Exception Se o parâmetro name não for fornecido, template não encontrado ou erro ao gravar arquivo
     */
    public function postService($data) {
        // Valida se o parâmetro name foi fornecido
        if (!isset($data['name']) || empty($data['name'])) {
            throw new Exception("Parâmetro 'name' é obrigatório.", 400);
        }

        if (!isset($data['table_name']) || empty($data['table_name'])) {
            throw new Exception("Parâmetro 'table_name' é obrigatório.", 400);
        }

        $moduleName = $data['module'];
        $name = $data['name'];
        $templatePath = "src/Applications/templates/Service.template";
        
        // Verifica se o template existe
        if (!file_exists($templatePath)) {
            Utils::sendResponse(200, [], "Template não encontrado.");
        }

        // Substitui os placeholders usando Utils::replaceFields
        $ServiceContent = Utils::replaceFields(file_get_contents($templatePath), $data);
        
        if(isset($data['filename'])){ $name = $data['filename']; }
        // Define o caminho do arquivo de destino
        $modulePath = "src/{$moduleName}";
        $servicesPath = "{$modulePath}/services";
        $ServiceFile = "{$servicesPath}/{$name}Service.php";

        if(file_exists($ServiceFile)){
            throw new Exception("Service já existe.", 400);
        }
        
        // Cria o diretório services se não existir
        if (!is_dir($servicesPath)) {
            if (!is_dir($modulePath)) {
                mkdir($modulePath, 0755, true);
            }
            mkdir($servicesPath, 0755, true);
        }
        
        // Grava o arquivo do Service
        if (file_put_contents($ServiceFile, $ServiceContent) === false) {
            throw new Exception("Erro ao gravar o arquivo do Service.", 500);
        }
        
        return  [
                "message" => "Service criado com sucesso.",
                "path" => $ServiceFile,
                "module" => $moduleName
            ];
    }

    public function putService($data) {
        
        if (!isset($data['module']) || empty($data['module'])) {
            throw new Exception("Parâmetro 'module' é obrigatório.", 400);
        }

        $moduleName = $data['module'];
        $name = $data['name'];

        $modulePath = "src/{$moduleName}";
        $servicesPath = "{$modulePath}/services";
        $ServiceFile = "{$servicesPath}/{$name}Service.php";

        if(!file_exists($ServiceFile)){
            Utils::sendResponse(200, [], "Service não existe.");
        }

        $ServiceContent = Utils::replaceFields(file_get_contents($ServiceFile), $data);

        if (file_put_contents($ServiceFile, $ServiceContent) === false) {
            throw new Exception("Erro ao gravar o arquivo do Service.", 500);
        }

        return [
            "message" => "Service atualizado com sucesso.",
            "path" => $ServiceFile,
            "module" => $moduleName
        ];
    }

    public function deleteService($data) {
        
        if (!isset($data['module']) || empty($data['module'])) {
            throw new Exception("Parâmetro 'module' é obrigatório.", 400);
        }

        $moduleName = $data['module'];
        $name = $data['name'];

        $modulePath = "src/{$moduleName}";
        $servicesPath = "{$modulePath}/services";
        $ServiceFile = "{$servicesPath}/{$name}Service.php";

        if(!file_exists($ServiceFile)){
            Utils::sendResponse(200, [], "Service não existe.");
        }

        if(unlink($ServiceFile)){
            return [
                "message" => "Service deletado com sucesso.",
                "path" => $ServiceFile,
                "module" => $moduleName
            ];
        }else{
            throw new Exception("Erro ao deletar o Service.", 500);
        }
    }
}

