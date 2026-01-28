<?php

use BuildCake\SqlKit\Sql;
use BuildCake\Utils\Utils;

class StatusService {
    public function __construct() {}

    function getStatus($filters){
        $retorno = Sql::runQuery("SELECT * FROM status  WHERE is_active = true {filter}",$filters);

        if(!$retorno){
            Utils::sendResponse(200, [], "Nenhum(a) Status encontrado");
        }
        
        return $retorno;
    }

    function insertStatus($data){
        //Sql::runPost( table name , parametros para insert na tabela);
        $retorno = Sql::runPost("status",$data);
        $data['id'] = $retorno;
        return $data;
    }

    function editStatus($data){
        //Sql::runPost( table name , parametros para insert na tabela); diferença que em $data deve conter um parametro id pu conjunto de ids para edição
        $retorno = Sql::runPut("status",$data);
        return $retorno;
    }

     function deletStatus($data){
        //Sql::runPost( table name , apenas o id ou conjunto de ids separados por virgula para remoção);  
        $retorno = Sql::runDelet("status",$data);
        return $retorno;
    }
}