<?php

use BuildCake\SqlKit\Sql;
use BuildCake\Utils\Utils;

class RolesService {
    public function __construct() {}

    function getRoles($filters){
        $retorno = Sql::runQuery("SELECT * FROM roles  WHERE is_active = true {filter}",$filters);

        if(!$retorno){
            Utils::sendResponse(200, [], "Nenhum(a) Roles encontrado");
        }
        
        return $retorno;
    }

    function insertRoles($data){
        //Sql::runPost( table name , parametros para insert na tabela);
        $retorno = Sql::runPost("roles",$data);
        $data['id'] = $retorno;
        return $data;
    }

    function editRoles($data){
        //Sql::runPost( table name , parametros para insert na tabela); diferença que em $data deve conter um parametro id pu conjunto de ids para edição
        $retorno = Sql::runPut("roles",$data);
        return $retorno;
    }

     function deletRoles($data){
        //Sql::runPost( table name , apenas o id ou conjunto de ids separados por virgula para remoção);  
        $retorno = Sql::runDelet("roles",$data);
        return $retorno;
    }
}