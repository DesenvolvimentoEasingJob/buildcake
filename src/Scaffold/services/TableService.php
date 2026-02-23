<?php

use BuildCake\Utils\Utils;
use BuildCake\SqlKit\Sql;

class TableService {
    public function __construct() {
    }

    /**
     * Lista todas as tabelas do banco de dados ou colunas de uma tabela específica
     */
    public function getTable($filters = []) {
        if(isset($filters['table_name'])){
            $tableName = $filters['table_name'];
            $filters['table_name'] = $tableName;
            return Sql::runQuery("SELECT 
                                        c.COLUMN_NAME,
                                        c.COLUMN_TYPE,
                                        c.IS_NULLABLE,
                                        c.COLUMN_DEFAULT,
                                        c.COLUMN_KEY,
                                        c.EXTRA,
                                        c.COLLATION_NAME,
                                        c.COLUMN_COMMENT,
                                        c.CHARACTER_MAXIMUM_LENGTH,
                                        c.NUMERIC_PRECISION,
                                        c.NUMERIC_SCALE,
                                        c.DATETIME_PRECISION,
                                        k.CONSTRAINT_NAME AS FK_NAME,
                                        k.REFERENCED_TABLE_NAME,
                                        k.REFERENCED_COLUMN_NAME
                                    FROM information_schema.COLUMNS c
                                    LEFT JOIN information_schema.KEY_COLUMN_USAGE k 
                                        ON k.TABLE_SCHEMA = c.TABLE_SCHEMA
                                        AND k.TABLE_NAME = c.TABLE_NAME
                                        AND k.COLUMN_NAME = c.COLUMN_NAME
                                        AND k.REFERENCED_TABLE_NAME IS NOT NULL
                                    WHERE 
                                        c.TABLE_SCHEMA = DATABASE()
                                        AND c.TABLE_NAME = :table_name

                                    ORDER BY 
                                        c.ORDINAL_POSITION", $filters);
        }

        $tables = Sql::runQuery("SELECT 
                                TABLE_NAME,
                                ENGINE,
                                TABLE_ROWS,
                                DATA_LENGTH / 1024 AS data_kb,
                                INDEX_LENGTH / 1024 AS index_kb,
                                (DATA_LENGTH + INDEX_LENGTH) / 1024 AS total_kb,
                                AUTO_INCREMENT,
                                CREATE_TIME,
                                TABLE_COLLATION
                            FROM information_schema.TABLES
                            WHERE TABLE_SCHEMA = DATABASE()",$filters);

        return $tables;
    }

    /**
     * Cria uma nova tabela baseada no template
     * 
     * @param array $data Dados contendo 'table_name', 'fields', 'foreign_keys' e 'additional_indexes'
     * @return array Resposta com status, data e message
     * @throws Exception Se os parâmetros obrigatórios não forem fornecidos, template não encontrado ou erro ao criar tabela
     */
    public function postTable($data) {
        // Valida se o parâmetro table_name foi fornecido
        if (!isset($data['table_name']) || empty($data['table_name'])) {
            throw new Exception("Parâmetro 'table_name' é obrigatório.", 400);
        }

        // Valida se o array de fields foi fornecido
        if (!isset($data['fields']) || !is_array($data['fields']) || empty($data['fields'])) {
            throw new Exception("Parâmetro 'fields' é obrigatório e deve ser um array não vazio.", 400);
        }

        $tableName = $data['table_name'];
        $templatePath = "src/Scaffold/templates/table.template";
        
        // Verifica se o template existe
        if (!file_exists($templatePath)) {
            Utils::sendResponse(200, [], "Template não encontrado.");
        }

        // Verifica se a tabela já existe
        $existingTables = Sql::runQuery("SELECT TABLE_NAME 
                                        FROM information_schema.TABLES 
                                        WHERE TABLE_SCHEMA = DATABASE() 
                                        AND TABLE_NAME = :table_name", $data);
        
        if (!empty($existingTables)) {
            return [
                "message" => "Tabela criada com sucesso.",
                "table_name" => $tableName
            ];
        }

        // Carrega o template
        $templateContent = file_get_contents($templatePath);
        
        // Substitui o nome da tabela no template
        $templateContent = str_replace('`users`', "`{$tableName}`", $templateContent);
        
        // Gera as linhas SQL dos campos adicionais
        $aditionalRows = $this->generateFieldsSQL($data['fields']);
        
        // Gera os índices adicionais (foreign keys + additional_indexes)
        $aditionalIndex = $this->generateIndexesSQL($data);
        
        // Substitui os placeholders no template
        $templateContent = str_replace('{aditional_rows}', $aditionalRows, $templateContent);
        $templateContent = str_replace('{aditional_index}', $aditionalIndex, $templateContent);

        // Executa o comando CREATE TABLE usando PureCommand (necessário para DDL)

        try {
            Sql::Call()->PureCommand($templateContent);
        } catch (Exception $e) {
            throw new Exception("Erro ao criar a tabela: " . $e->getMessage(), 500);
        }
        
        $migrationName = date('YmdHis') . "_cr_{$tableName}.sql";
        file_put_contents("src/Database/migrations/{$migrationName}.sql", $templateContent);
        
        return [
            "message" => "Tabela criada com sucesso.",
            "table_name" => $tableName
        ];
    }

    /**
     * Gera o SQL para os campos da tabela
     * 
     * @param array $fields Array de campos com name, type, length, null, default, comment
     * @return string SQL das definições dos campos
     */
    private function generateFieldsSQL($fields) {
        $sqlFields = [];
        
        foreach ($fields as $field) {
            if (!isset($field['name']) || !isset($field['type'])) {
                continue; // Pula campos inválidos
            }
            
            $name = $field['name'];
            $type = strtoupper($field['type']);
            $length = isset($field['length']) ? $field['length'] : null;
            $null = isset($field['null']) ? $field['null'] : true;
            $default = isset($field['default']) ? $field['default'] : null;
            $comment = isset($field['comment']) ? $field['comment'] : '';
            $unsigned = isset($field['unsigned']) ? $field['unsigned'] : false;
            
            // Normaliza o valor de null (pode vir como boolean ou string do JSON)
            $isNullable = true;
            if ($null === false || $null === 'false' || $null === 0 || $null === '0') {
                $isNullable = false;
            }
            
            // Normaliza o valor de unsigned
            $isUnsigned = false;
            if ($unsigned === true || $unsigned === 'true' || $unsigned === 1 || $unsigned === '1') {
                $isUnsigned = true;
            }
            
            // Monta a definição do tipo com length se necessário
            $typeDefinition = $type;
            if ($length !== null && $length !== '') {
                // Tipos que normalmente usam length
                if (in_array($type, ['VARCHAR', 'CHAR', 'INT', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT', 'DECIMAL', 'FLOAT', 'DOUBLE','ENUM'])) {
                    $typeDefinition = "{$type}({$length})";
                }
            }
            
            // Adiciona UNSIGNED se necessário (apenas para tipos numéricos)
            if ($isUnsigned && in_array($type, ['INT', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT', 'DECIMAL', 'FLOAT', 'DOUBLE'])) {
                $typeDefinition .= " UNSIGNED";
            }
            
            // Monta a linha SQL do campo
            $fieldSQL = "\t`{$name}` {$typeDefinition}";
            
            // Adiciona NOT NULL ou NULL
            if (!$isNullable) {
                $fieldSQL .= " NOT NULL";
            } else {
                $fieldSQL .= " NULL";
            }
            
            // Adiciona DEFAULT se fornecido
            // Considera 0 (zero) como um valor válido, mas ignora string vazia e null
            if ($default !== null && $default !== '') {
                // Se for numérico (incluindo 0), CURRENT_TIMESTAMP ou booleano, não coloca aspas
                if (is_numeric($default) || $default === 'CURRENT_TIMESTAMP' || $default === true || $default === false || $default === 'true' || $default === 'false') {
                    // Converte booleanos para 0/1
                    if ($default === true || $default === 'true') {
                        $default = '1';
                    } elseif ($default === false || $default === 'false') {
                        $default = '0';
                    }
                    $fieldSQL .= " DEFAULT {$default}";
                } else {
                    // Escapa aspas simples no valor padrão
                    $defaultEscaped = str_replace("'", "''", $default);
                    $fieldSQL .= " DEFAULT '{$defaultEscaped}'";
                }
            }
            
            // Adiciona COMMENT se fornecido
            if (!empty($comment)) {
                $commentEscaped = str_replace("'", "''", $comment); // Escapa aspas simples
                $fieldSQL .= " COMMENT '{$commentEscaped}'";
            }
            
            $sqlFields[] = $fieldSQL;
        }
        
        return implode(",\n", $sqlFields);
    }

    /**
     * Gera o SQL para índices adicionais (foreign keys e índices)
     * 
     * @param array $data Dados contendo 'foreign_keys' e 'additional_indexes'
     * @return string SQL dos índices
     */
    private function generateIndexesSQL($data) {
        $indexes = [];
        
        // Processa foreign keys
        if (isset($data['foreign_keys']) && is_array($data['foreign_keys'])) {
            foreach ($data['foreign_keys'] as $fk) {
                if (!isset($fk['column']) || !isset($fk['references_table']) || !isset($fk['references_column'])) {
                    continue; // Pula foreign keys inválidas
                }
                
                $column = $fk['column'];
                $refTable = $fk['references_table'];
                $refColumn = $fk['references_column'];
                $salt = date('YmdHis') . '_' . substr(md5(microtime(true)), 0, 6);
                $fkName = isset($fk['name']) ? $fk['name'] : "fk_{$column}_{$refTable}_{$salt}";
                $onDelete = isset($fk['on_delete']) ? $fk['on_delete'] : 'RESTRICT';
                $onUpdate = isset($fk['on_update']) ? $fk['on_update'] : 'RESTRICT';
                
                $indexes[] = "\tCONSTRAINT `{$fkName}` FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}` (`{$refColumn}`) ON DELETE {$onDelete} ON UPDATE {$onUpdate},";
            }
        }
        
        // Processa índices adicionais
        if (isset($data['additional_indexes']) && is_array($data['additional_indexes'])) {
            foreach ($data['additional_indexes'] as $index) {
                if (!isset($index['columns']) || !is_array($index['columns']) || empty($index['columns'])) {
                    continue; // Pula índices inválidos
                }
                
                $indexName = isset($index['name']) ? $index['name'] : 'idx_' . implode('_', $index['columns']);
                $indexType = isset($index['type']) && strtoupper($index['type']) === 'UNIQUE' ? 'UNIQUE INDEX' : 'INDEX';
                $columns = implode('`, `', $index['columns']);
                
                $indexes[] = "\t{$indexType} `{$indexName}` (`{$columns}`) USING BTREE";
            }
        }
        
        return implode("\n", $indexes);
    }

    /**
     * Altera uma tabela existente adicionando, removendo ou modificando colunas
     * 
     * @param array $data Dados contendo 'table_name' e 'fields' com ações (add, remove, alter)
     * @return array Resposta com status, data e message
     * @throws Exception Se os parâmetros obrigatórios não forem fornecidos ou erro ao alterar tabela
     */
    public function putTable($data) {
        if (!isset($data['table_name']) || empty($data['table_name'])) {
            throw new Exception("Parâmetro 'table_name' é obrigatório.", 400);
        }

        if (!isset($data['fields']) || !is_array($data['fields']) || empty($data['fields'])) {
            throw new Exception("Parâmetro 'fields' é obrigatório e deve ser um array não vazio.", 400);
        }

        $tableName = $data['table_name'];

        // Verifica se a tabela existe
        $existingTables = Sql::runQuery("SELECT TABLE_NAME 
                                        FROM information_schema.TABLES 
                                        WHERE TABLE_SCHEMA = DATABASE() 
                                        AND TABLE_NAME = :table_name", [ "table_name" => $tableName]);
        
        if (empty($existingTables)) {
            Utils::sendResponse(200, [], "Tabela não existe.");
        }

        // Obtém os campos atuais da tabela
        $currentColumns = $this->getCurrentColumns($tableName);
        $currentColumnNames = array_column($currentColumns, 'COLUMN_NAME');

        // Processa as ações e gera os comandos ALTER TABLE
        $alterCommands = [];
        
        foreach ($data['fields'] as $field) {
            if (!isset($field['name'])) {
                continue; // Pula campos sem nome
            }

            $fieldName = $field['name'];
            $action = isset($field['action']) ? strtolower($field['action']) : '';
            $columnExists = in_array($fieldName, $currentColumnNames);

            switch ($action) {
                case 'add':
                    if ($columnExists) {
                        throw new Exception("Coluna '{$fieldName}' já existe na tabela.", 400);
                    }
                    $alterCommands[] = "ADD COLUMN " . $this->generateFieldDefinition($field);
                    break;

                case 'remove':
                case 'drop':
                    if (!$columnExists) {
                        Utils::sendResponse(200, [], "Coluna '{$fieldName}' não existe na tabela.");
                    }
                    // Não permite remover colunas padrão do sistema
                    $protectedColumns = ['id', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'];
                    if (in_array($fieldName, $protectedColumns)) {
                        throw new Exception("Não é permitido remover a coluna '{$fieldName}' (coluna protegida).", 400);
                    }
                    $alterCommands[] = "DROP COLUMN `{$fieldName}`";
                    break;

                case 'alter':
                case 'modify':
                    if (!$columnExists) {
                        Utils::sendResponse(200, [], "Coluna '{$fieldName}' não existe na tabela. Use 'add' para adicionar.");
                    }
                    $alterCommands[] = "MODIFY COLUMN " . $this->generateFieldDefinition($field);
                    break;

                default:
                    throw new Exception("Ação '{$action}' inválida. Use 'add', 'remove' ou 'alter'.", 400);
            }
        }

        // Se não houver comandos, retorna sem fazer nada
        if (empty($alterCommands)) {
            return [
                "message" => "Nenhuma alteração necessária.",
                "table_name" => $tableName
            ];
        }

        // Monta e executa os comandos ALTER TABLE
        // Executa cada comando separadamente para melhor controle de erros
        $executedCommands = [];
        foreach ($alterCommands as $command) {
            $alterQuery = "ALTER TABLE `{$tableName}` " . $command;
            
            try {
                Sql::Call()->PureCommand($alterQuery);
                $executedCommands[] = $command;
            } catch (Exception $e) {
                throw new Exception("Erro ao executar comando '{$command}': " . $e->getMessage(), 500);
            }

            $migrationName = date('YmdHis') . "_alt_{$tableName}.sql";
            file_put_contents("src/Database/migrations/{$migrationName}.sql", $alterQuery);
        }

        
        return [
            "message" => "Tabela atualizada com sucesso.",
            "table_name" => $tableName,
            "executed_commands" => $executedCommands
        ];
    }

    /**
     * Obtém as colunas atuais de uma tabela
     * 
     * @param string $tableName Nome da tabela
     * @return array Array com informações das colunas
     */
    private function getCurrentColumns($tableName) {
        $colls = Sql::runQuery("SELECT 
                                COLUMN_NAME,
                                COLUMN_TYPE,
                                IS_NULLABLE,
                                COLUMN_DEFAULT,
                                COLUMN_KEY,
                                EXTRA,
                                COLUMN_COMMENT
                            FROM information_schema.COLUMNS
                            WHERE TABLE_SCHEMA = DATABASE()
                            AND TABLE_NAME = :table_name
                            ORDER BY ORDINAL_POSITION", [ "table_name" => $tableName]);

        return $colls;
    }

    /**
     * Gera a definição SQL de um campo (para ADD ou MODIFY)
     * 
     * @param array $field Array com as propriedades do campo
     * @return string Definição SQL do campo
     */
    private function generateFieldDefinition($field) {
        if (!isset($field['name']) || !isset($field['type'])) {
            throw new Exception("Campo deve ter 'name' e 'type' definidos.", 400);
        }

        $name = $field['name'];
        $type = strtoupper($field['type']);
        $length = isset($field['length']) ? $field['length'] : null;
        $null = isset($field['null']) ? $field['null'] : true;
        $default = isset($field['default']) ? $field['default'] : null;
        $comment = isset($field['comment']) ? $field['comment'] : '';
        $unsigned = isset($field['unsigned']) ? $field['unsigned'] : false;

        // Normaliza o valor de null
        $isNullable = true;
        if ($null === false || $null === 'false' || $null === 0 || $null === '0') {
            $isNullable = false;
        }

        // Normaliza o valor de unsigned
        $isUnsigned = false;
        if ($unsigned === true || $unsigned === 'true' || $unsigned === 1 || $unsigned === '1') {
            $isUnsigned = true;
        }

        // Monta a definição do tipo com length se necessário
        $typeDefinition = $type;
        if ($length !== null && $length !== '') {
            if (in_array($type, ['VARCHAR', 'CHAR', 'INT', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT', 'DECIMAL', 'FLOAT', 'DOUBLE'])) {
                $typeDefinition = "{$type}({$length})";
            }
        }

        // Adiciona UNSIGNED se necessário (apenas para tipos numéricos)
        if ($isUnsigned && in_array($type, ['INT', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT', 'DECIMAL', 'FLOAT', 'DOUBLE'])) {
            $typeDefinition .= " UNSIGNED";
        }

        // Monta a definição SQL do campo
        $fieldSQL = "`{$name}` {$typeDefinition}";

        // Adiciona NOT NULL ou NULL
        if (!$isNullable) {
            $fieldSQL .= " NOT NULL";
        } else {
            $fieldSQL .= " NULL";
        }

        // Adiciona DEFAULT se fornecido
        if ($default !== null && $default !== '') {
            if (is_numeric($default) || $default === 'CURRENT_TIMESTAMP' || $default === true || $default === false || $default === 'true' || $default === 'false') {
                if ($default === true || $default === 'true') {
                    $default = '1';
                } elseif ($default === false || $default === 'false') {
                    $default = '0';
                }
                $fieldSQL .= " DEFAULT {$default}";
            } else {
                $defaultEscaped = str_replace("'", "''", $default);
                $fieldSQL .= " DEFAULT '{$defaultEscaped}'";
            }
        }

        // Adiciona COMMENT se fornecido
        if (!empty($comment)) {
            $commentEscaped = str_replace("'", "''", $comment);
            $fieldSQL .= " COMMENT '{$commentEscaped}'";
        }

        return $fieldSQL;
    }

    /**
     * Deleta uma tabela existente
     * 
     * @param array $data Dados contendo 'table_name'
     * @return array Resposta com status, data e message
     * @throws Exception Se o parâmetro table_name não for fornecido ou erro ao deletar tabela
     */
    public function deleteTable($data) {
        if (!isset($data['table_name']) || empty($data['table_name'])) {
            throw new Exception("Parâmetro 'table_name' é obrigatório.", 400);
        }

        $tableName = $data['table_name'];

        // Verifica se a tabela existe
        $existingTables = Sql::runQuery("SELECT TABLE_NAME 
                                        FROM information_schema.TABLES 
                                        WHERE TABLE_SCHEMA = DATABASE() 
                                        AND TABLE_NAME = ?", [$tableName]);
        
        if (empty($existingTables)) {
            Utils::sendResponse(200, [], "Tabela não existe.");
        }

        // Monta o comando DROP TABLE
        $dropQuery = "DROP TABLE `{$tableName}`";

        // Executa o comando DROP TABLE usando PureCommand
        try {
            Sql::Call()->PureCommand($dropQuery);
        } catch (Exception $e) {
            throw new Exception("Erro ao deletar a tabela: " . $e->getMessage(), 500);
        }

        return [
            "message" => "Tabela deletada com sucesso.",
            "table_name" => $tableName
        ];
    }
}