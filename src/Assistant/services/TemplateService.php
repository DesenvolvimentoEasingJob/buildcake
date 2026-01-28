<?php

use BuildCake\Utils\Utils;

class TemplateService {
    private $templatesPath;
    
    public function __construct() {
        $this->templatesPath = "src/Assistant/templates/";
        
        // Cria a pasta templates se não existir
        if (!is_dir($this->templatesPath)) {
            mkdir($this->templatesPath, 0755, true);
        }
    }
    
    /**
     * Lista todos os templates disponíveis
     * 
     * @param array $filters Filtros opcionais
     * @return array Lista de templates
     */
    public function getTemplate($filters = []) {
        $templates = [];
        
        // Verifica se a pasta templates existe
        if (!is_dir($this->templatesPath)) {
            return $templates;
        }
        
        // Escaneia os arquivos na pasta templates
        $files = scandir($this->templatesPath);
        
        foreach ($files as $file) {
            // Ignora . e .. e arquivos que não são .txt
            if ($file[0] === '.' || pathinfo($file, PATHINFO_EXTENSION) !== 'txt') {
                continue;
            }
            
            $filePath = $this->templatesPath . $file;
            $templateName = pathinfo($file, PATHINFO_FILENAME);
            
            $templates[] = [
                "name" => $templateName,
                "filename" => $file,
                "path" => $filePath,
                "modified_time" => filemtime($filePath),
                "size" => filesize($filePath)
            ];
        }
        
        // Aplica filtros se fornecidos
        if ($filters) {
            $templates = array_filter($templates, function($template) use ($filters) {
                [$key, $value] = [array_key_first($filters), reset($filters)];
                return isset($template[$key]) && $template[$key] == $value;
            });
            $templates = array_values($templates);
        }
        
        return $templates;
    }
    
    /**
     * Obtém o conteúdo de um template específico
     * 
     * @param string $templateName Nome do template (sem extensão)
     * @return string|null Conteúdo do template ou null se não existir
     */
    public function getTemplateContent($templateName) {
        $filePath = $this->templatesPath . $templateName . '.txt';
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        return file_get_contents($filePath);
    }
}
