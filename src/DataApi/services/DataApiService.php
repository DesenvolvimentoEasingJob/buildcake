<?php

use BuildCake\SqlKit\Sql;
use BuildCake\Utils\Utils;

class DataApiService {
    public function __construct() {}
    public $table = "default";

    function getDataApi($filters){
        $retorno = Sql::runQuery("SELECT * FROM {$this->table}  WHERE is_active = true {filter}",$filters);

        if(!$retorno){
            Utils::sendResponse(200, [], "Nenhum(a) {$this->table} encontrado");
        }
        
        return $retorno;
    }

    function insertDataApi($data){
        //Sql::runPost( table name , parametros para insert na tabela);
        $retorno = Sql::runPost($this->table,$data);
        $data['id'] = $retorno;
        return $data;
    }

    function editDataApi($data){
        //Sql::runPost( table name , parametros para insert na tabela); diferença que em $data deve conter um parametro id pu conjunto de ids para edição
        $retorno = Sql::runPut($this->table,$data);
        return $retorno;
    }

     function deletDataApi($data){
        //Sql::runPost( table name , apenas o id ou conjunto de ids separados por virgula para remoção);  
        $retorno = Sql::runDelet($this->table,$data);
        return $retorno;
    }
}