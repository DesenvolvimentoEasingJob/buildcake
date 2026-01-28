<?php

use BuildCake\SqlKit\Sql;
use BuildCake\Utils\Utils;

class SessionsService {
    public function __construct() {}

    function getSessions($filters){
        $retorno = Sql::runQuery("SELECT * FROM sessions  WHERE is_active = true {filter}",$filters);

        if(!$retorno){
            Utils::sendResponse(200, [], "Nenhum(a) Sessions encontrado");
        }
        
        return $retorno;
    }

    function insertSessions($data){
        //Sql::runPost( table name , parametros para insert na tabela);
        $retorno = Sql::runPost("sessions",$data);
        $data['id'] = $retorno;
        return $data;
    }

    function editSessions($data){
        //Sql::runPost( table name , parametros para insert na tabela); diferença que em $data deve conter um parametro id pu conjunto de ids para edição
        $retorno = Sql::runPut("sessions",$data);
        return $retorno;
    }

     function deletSessions($data){
        //Sql::runPost( table name , apenas o id ou conjunto de ids separados por virgula para remoção);  
        $retorno = Sql::runDelet("sessions",$data);
        return $retorno;
    }
}