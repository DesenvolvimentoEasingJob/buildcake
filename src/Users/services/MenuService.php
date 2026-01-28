<?php

use BuildCake\SqlKit\Sql;
use BuildCake\Utils\Utils;

class MenuService {
    public function __construct() {}

    function getMenu($filters){
        $retorno = Sql::runQuery("SELECT * FROM menu  WHERE is_active = true {filter}",$filters);

        if(!$retorno){
            Utils::sendResponse(200, [], "Nenhum(a) Menu encontrado");
        }
        
        return $retorno;
    }

    function insertMenu($data){
        //Sql::runPost( table name , parametros para insert na tabela);
        $retorno = Sql::runPost("menu",$data);
        $data['id'] = $retorno;
        return $data;
    }

    function editMenu($data){
        //Sql::runPost( table name , parametros para insert na tabela); diferença que em $data deve conter um parametro id pu conjunto de ids para edição
        $retorno = Sql::runPut("menu",$data);
        return $retorno;
    }

     function deletMenu($data){
        //Sql::runPost( table name , apenas o id ou conjunto de ids separados por virgula para remoção);  
        $retorno = Sql::runDelet("menu",$data);
        return $retorno;
    }
}