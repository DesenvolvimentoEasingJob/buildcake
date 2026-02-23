<?php

use BuildCake\Utils\Utils;

class FileService {
    
    public function __construct() {
    }
    
    /**
     * Lista todos os arquivos dentro de src
     * Se receber filtro, retorna o conteúdo do arquivo
     */
    public function getFile($filters = []) {
        $files = [];
        $srcPath = "src/"; // Caminho para src a partir deste arquivo
        
        // Função recursiva para escanear diretórios
        $scanDirectory = function($dir, $basePath = '') use (&$scanDirectory, &$files) {
            $items = scandir($dir);
            
            foreach ($items as $item) {
                if ($item[0] === '.') continue;
                
                $itemPath = $dir . '/' . $item;
                $relativePath = $basePath ? $basePath . '/' . $item : $item;
                
                if (is_dir($itemPath)) {
                    // Recursivamente escaneia subdiretórios
                    $scanDirectory($itemPath, $relativePath);
                } else {
                    // Adiciona arquivo à lista
                    $files[] = [
                        "file" => $item,
                        "path" => $relativePath,
                        "fullPath" => $itemPath
                    ];
                }
            }
        };
        
        $scanDirectory($srcPath);
        
        // Se houver filtros, aplica filtro ou retorna conteúdo
        if ($filters) {
            // Se o filtro for 'path' ou 'filepath', retorna o conteúdo do arquivo
            if (isset($filters['path']) || isset($filters['filepath'])) {
                $filePath = isset($filters['path']) ? $filters['path'] : $filters['filepath'];
                $fullPath = $srcPath . $filePath;
                
                if (!file_exists($fullPath)) {
                    Utils::sendResponse(200, [], "Arquivo não encontrado.");
                }
                
                if (is_dir($fullPath)) {
                    throw new Exception("O caminho especificado é um diretório, não um arquivo.", 400);
                }
                
                return [
                    "path" => $filePath,
                    "content" => file_get_contents($fullPath)
                ];
            }
            
            // Caso contrário, aplica filtros normais como no getApi
            $files = array_filter($files, function($file) use ($filters) {
                [$key, $value] = [array_key_first($filters), reset($filters)];
                return isset($file[$key]) && $file[$key] == $value;
            });
        }
        
        return $files;
    }

    /**
     * Cria um novo arquivo
     * 
     * @param array $data Dados contendo 'directory', 'filename' e 'content'
     * @return array Resposta com status, data e message
     * @throws Exception Se os parâmetros não forem fornecidos ou erro ao criar arquivo
     */
    public function postFile($data) {
        // Valida se os parâmetros foram fornecidos
        if (!isset($data['directory']) || empty($data['directory'])) {
            throw new Exception("Parâmetro 'directory' é obrigatório.", 400);
        }

        if (!isset($data['filename']) || empty($data['filename'])) {
            throw new Exception("Parâmetro 'filename' é obrigatório.", 400);
        }

        if (!isset($data['content'])) {
            throw new Exception("Parâmetro 'content' é obrigatório.", 400);
        }

        $directory = $data['directory'];
        $filename = $data['filename'];
        $content = $data['content'];
        
        // Remove barras no início se houver
        $directory = ltrim($directory, '/\\');
        
        // Define o caminho completo do arquivo
        $filePath = "src/{$directory}/{$filename}";
        
        // Verifica se o arquivo já existe
        if (file_exists($filePath)) {
            throw new Exception("Arquivo já existe.", 400);
        }
        
        // Cria o diretório se não existir
        $dirPath = dirname($filePath);
        if (!is_dir($dirPath)) {
            if (!mkdir($dirPath, 0755, true)) {
                throw new Exception("Erro ao criar o diretório.", 500);
            }
        }
        
        // Grava o arquivo
        if (file_put_contents($filePath, $content) === false) {
            throw new Exception("Erro ao criar o arquivo.", 500);
        }
        
        return [
            "message" => "Arquivo criado com sucesso.",
            "path" => $filePath,
            "directory" => $directory,
            "filename" => $filename
        ];
    }

    /**
     * Edita um arquivo existente
     * 
     * @param array $data Dados contendo 'filepath' e 'content'
     * @return array Resposta com status, data e message
     * @throws Exception Se os parâmetros não forem fornecidos ou erro ao editar arquivo
     */
    public function putFile($data) {
        // Valida se os parâmetros foram fornecidos
        if (!isset($data['filepath']) || empty($data['filepath'])) {
            throw new Exception("Parâmetro 'filepath' é obrigatório.", 400);
        }

        if (!isset($data['content'])) {
            throw new Exception("Parâmetro 'content' é obrigatório.", 400);
        }

        $filePath = $data['filepath'];
        $content = $data['content'];
        
        // Remove barras no início se houver e garante que comece com src/
        $filePath = ltrim($filePath, '/\\');
        if (strpos($filePath, 'src/') !== 0) {
            $filePath = "src/{$filePath}";
        }
        
        // Verifica se o arquivo existe
        if (!file_exists($filePath)) {
            Utils::sendResponse(200, [], "Arquivo não encontrado.");
        }
        
        // Verifica se é um arquivo e não um diretório
        if (is_dir($filePath)) {
            throw new Exception("O caminho especificado é um diretório, não um arquivo.", 400);
        }
        
        // Grava o conteúdo no arquivo
        if (file_put_contents($filePath, $content) === false) {
            throw new Exception("Erro ao editar o arquivo.", 500);
        }
        
        return [
            "message" => "Arquivo editado com sucesso.",
            "path" => $filePath
        ];
    }

    /**
     * Deleta um arquivo
     * 
     * @param array $data Dados contendo 'filepath'
     * @return array Resposta com status, data e message
     * @throws Exception Se o parâmetro não for fornecido ou erro ao deletar arquivo
     */
    public function deleteFile($data) {
        // Valida se o parâmetro foi fornecido
        if (!isset($data['filepath']) || empty($data['filepath'])) {
            throw new Exception("Parâmetro 'filepath' é obrigatório.", 400);
        }

        $filePath = $data['filepath'];
        
        // Remove barras no início se houver e garante que comece com src/
        $filePath = ltrim($filePath, '/\\');
        if (strpos($filePath, 'src/') !== 0) {
            $filePath = "src/{$filePath}";
        }
        
        // Verifica se o arquivo existe
        if (!file_exists($filePath)) {
            Utils::sendResponse(200, [], "Arquivo não encontrado.");
        }
        
        // Verifica se é um arquivo e não um diretório
        if (is_dir($filePath)) {
            throw new Exception("O caminho especificado é um diretório, não um arquivo.", 400);
        }
        
        // Deleta o arquivo
        if (unlink($filePath)) {
            return [
                "message" => "Arquivo deletado com sucesso.",
                "path" => $filePath
            ];
        } else {
            throw new Exception("Erro ao deletar o arquivo.", 500);
        }
    }
}

