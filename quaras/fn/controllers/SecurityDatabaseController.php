<?php

class SecurityDatabaseController{
    /* 
        Protección contra SQL Injection
    */
    public static function cleanVar($param){
        //$param = str_replace(' ','',$param);
        $param = str_replace('INSERT','',$param);
        $param = str_replace('UNION','',$param);
        $param = str_replace('SELECT','',$param);
        $param = str_replace('DELETE','',$param);
        $param = str_replace('*','',$param);
        $param = str_replace('[','',$param);
        $param = str_replace(']','',$param);
        $param = str_replace('-','',$param);
        $param = str_replace('&','',$param);
        $param = str_replace('\'','',$param);
        $param = str_replace('"','',$param);
        $param = @addslashes($param);
        return $param;
    }
}