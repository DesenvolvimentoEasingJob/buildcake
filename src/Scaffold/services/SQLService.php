<?php

use BuildCake\Utils\Utils;
use BuildCake\SqlKit\Sql;

class SQLService {
    public function __construct() {
    }

    /**
     * Executa uma query SELECT
     * 
     * @param array $data Deve conter:
     *   - 'query': Query SQL SELECT completa (com placeholders :param)
     *   - 'params': Array associativo com os parâmetros para a query (opcional)
     * @return array Resultado da query
     * @throws Exception Se os parâmetros obrigatórios não forem fornecidos ou erro na query
     */
    public function getSQL($data = []) {
        if (!isset($data['query']) || empty($data['query'])) {
            throw new Exception("Parâmetro 'query' é obrigatório.", 400);
        }

        $query = $data['query'];
        
        // Process params - pode vir como array ou como string JSON (quando vem via query string)
        $params = [];
        if (isset($data['params'])) {
            if (is_array($data['params'])) {
                $params = $data['params'];
            } else if (is_string($data['params'])) {
                // Tenta decodificar JSON
                $decoded = json_decode($data['params'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $params = $decoded;
                }
            }
        }
        
        // Valida que é uma query SELECT
        if (stripos(trim($query), 'SELECT') !== 0) {
            throw new Exception("Apenas queries SELECT são permitidas no método GET.", 400);
        }
        
        try {
            return Sql::runQuery($query, $params);
        } catch (Exception $e) {
            throw new Exception("Erro ao executar query: " . $e->getMessage(), 500);
        }
    }

    /**
     * Executa uma query INSERT
     * 
     * @param array $data Deve conter:
     *   - 'query': Query SQL INSERT completa (com placeholders :param)
     *   - 'params': Array associativo com os parâmetros para a query (opcional)
     * @return array Resposta com resultado
     * @throws Exception Se os parâmetros obrigatórios não forem fornecidos ou erro na query
     */
    public function postSQL($data) {
        if (!isset($data['query']) || empty($data['query'])) {
            throw new Exception("Parâmetro 'query' é obrigatório.", 400);
        }

        $query = $data['query'];
        $params = isset($data['params']) && is_array($data['params']) ? $data['params'] : [];
        
        // Valida que é uma query INSERT
        if (stripos(trim($query), 'INSERT') !== 0) {
            throw new Exception("Apenas queries INSERT são permitidas no método POST.", 400);
        }
        
        // Substitui os parâmetros na query se houver
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $placeholder = ':' . $key;
                // Escapa valores string
                if (is_string($value)) {
                    $value = "'" . str_replace("'", "''", $value) . "'";
                }
                $query = str_replace($placeholder, $value, $query);
            }
        }
        
        try {
            Sql::Call()->PureCommand($query);
            return [
                "message" => "Registro inserido com sucesso.",
                "query" => $query
            ];
        } catch (Exception $e) {
            throw new Exception("Erro ao executar query: " . $e->getMessage(), 500);
        }
    }

    /**
     * Executa uma query UPDATE
     * 
     * @param array $data Deve conter:
     *   - 'query': Query SQL UPDATE completa (com placeholders :param)
     *   - 'params': Array associativo com os parâmetros para a query (opcional)
     * @return array Resposta com resultado
     * @throws Exception Se os parâmetros obrigatórios não forem fornecidos ou erro na query
     */
    public function putSQL($data) {
        if (!isset($data['query']) || empty($data['query'])) {
            throw new Exception("Parâmetro 'query' é obrigatório.", 400);
        }

        $query = $data['query'];
        $params = isset($data['params']) && is_array($data['params']) ? $data['params'] : [];
        
        // Valida que é uma query UPDATE
        if (stripos(trim($query), 'UPDATE') !== 0) {
            throw new Exception("Apenas queries UPDATE são permitidas no método PUT.", 400);
        }
        
        // Substitui os parâmetros na query se houver
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $placeholder = ':' . $key;
                // Escapa valores string
                if (is_string($value)) {
                    $value = "'" . str_replace("'", "''", $value) . "'";
                }
                $query = str_replace($placeholder, $value, $query);
            }
        }
        
        try {
            Sql::Call()->PureCommand($query);
            return [
                "message" => "Registro(s) atualizado(s) com sucesso.",
                "query" => $query
            ];
        } catch (Exception $e) {
            throw new Exception("Erro ao executar query: " . $e->getMessage(), 500);
        }
    }

    /**
     * Executa uma query DELETE
     * 
     * @param array $data Deve conter:
     *   - 'query': Query SQL DELETE completa (com placeholders :param)
     *   - 'params': Array associativo com os parâmetros para a query (opcional)
     * @return array Resposta com resultado
     * @throws Exception Se os parâmetros obrigatórios não forem fornecidos ou erro na query
     */
    public function deleteSQL($data) {
        if (!isset($data['query']) || empty($data['query'])) {
            throw new Exception("Parâmetro 'query' é obrigatório.", 400);
        }

        $query = $data['query'];
        $params = isset($data['params']) && is_array($data['params']) ? $data['params'] : [];
        
        // Valida que é uma query DELETE
        if (stripos(trim($query), 'DELETE') !== 0) {
            throw new Exception("Apenas queries DELETE são permitidas no método DELETE.", 400);
        }
        
        // Substitui os parâmetros na query se houver
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $placeholder = ':' . $key;
                // Escapa valores string
                if (is_string($value)) {
                    $value = "'" . str_replace("'", "''", $value) . "'";
                }
                $query = str_replace($placeholder, $value, $query);
            }
        }
        
        try {
            Sql::Call()->PureCommand($query);
            return [
                "message" => "Registro(s) deletado(s) com sucesso.",
                "query" => $query
            ];
        } catch (Exception $e) {
            throw new Exception("Erro ao executar query: " . $e->getMessage(), 500);
        }
    }
}
