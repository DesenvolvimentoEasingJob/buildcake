<?php

use BuildCake\SqlKit\Sql;
use BuildCake\Utils\Utils;

class ProfileFilterService {
    public function __construct() {}

    function getProfileFilter($filters){
        $retorno = Sql::runQuery("SELECT * FROM profilefilter  WHERE is_active = true {filter}",$filters);

        if(!$retorno){
            Utils::sendResponse(200, [], "Nenhum(a) ProfileFilter encontrado");
        }
        
        return $retorno;
    }

    function insertProfileFilter($data){
        //Sql::runPost( table name , parametros para insert na tabela);
        $retorno = Sql::runPost("profilefilter",$data);
        $data['id'] = $retorno;
        return $data;
    }

    function editProfileFilter($data){
        //Sql::runPost( table name , parametros para insert na tabela); diferença que em $data deve conter um parametro id pu conjunto de ids para edição
        $retorno = Sql::runPut("profilefilter",$data);
        return $retorno;
    }

     function deletProfileFilter($data){
        //Sql::runPost( table name , apenas o id ou conjunto de ids separados por virgula para remoção);  
        $retorno = Sql::runDelet("profilefilter",$data);
        return $retorno;
    }
}