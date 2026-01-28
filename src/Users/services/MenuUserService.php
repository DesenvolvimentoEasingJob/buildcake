<?php

use BuildCake\SqlKit\Sql;
use BuildCake\Utils\Utils;

class MenuUserService {
    public function __construct() {}

    function getMenuUser($filters){
        $retorno = Sql::runQuery("SELECT * FROM menuuser  WHERE is_active = true {filter}",$filters);

        if(!$retorno){
            Utils::sendResponse(200, [], "Nenhum(a) MenuUser encontrado");
        }
        
        return $retorno;
    }

    function insertMenuUser($data){
        //Sql::runPost( table name , parametros para insert na tabela);
        $retorno = Sql::runPost("menuuser",$data);
        $data['id'] = $retorno;
        return $data;
    }

    function editMenuUser($data){
        //Sql::runPost( table name , parametros para insert na tabela); diferença que em $data deve conter um parametro id pu conjunto de ids para edição
        $retorno = Sql::runPut("menuuser  ",$data);
        return $retorno;
    }

     function deletMenuUser($data){
        //Sql::runPost( table name , apenas o id ou conjunto de ids separados por virgula para remoção);  
        $retorno = Sql::runDelet("menuuser",$data);
        return $retorno;
    }
}