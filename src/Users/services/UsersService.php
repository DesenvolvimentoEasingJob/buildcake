<?php

use BuildCake\SqlKit\Sql;
use BuildCake\Utils\Utils;


class UsersService {
    public function __construct() {}

    function getUsers($filters){
        $retorno = Sql::runQuery("SELECT * FROM users  WHERE is_active = true {filter}",$filters);

        if(!$retorno){
            Utils::sendResponse(200, [], "Nenhum(a) Users encontrado");
        }
        
        return $retorno;
    }

    function insertUsers($data){
        //Sql::runPost( table name , parametros para insert na tabela);

        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $retorno = Sql::runPost("users",$data);
        $data['id'] = $retorno;
        return $data;
    }

    function editUsers($data){
        //Sql::runPost( table name , parametros para insert na tabela); diferença que em $data deve conter um parametro id pu conjunto de ids para edição
        
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $retorno = Sql::runPut("users",$data);
        return $retorno;
    }

     function deletUsers($data){
        //Sql::runPost( table name , apenas o id ou conjunto de ids separados por virgula para remoção);  
        $retorno = Sql::runDelet("users",$data);
        return $retorno;
    }
}