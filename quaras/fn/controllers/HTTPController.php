<?php 

class HTTPController{
    
    /* Devuelve un JSON */
    public static function responseInJSON($response){
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}