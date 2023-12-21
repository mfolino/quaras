<?

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//Definimos arrays general para evitar errores de undefined de php
$response=array();
$vars=array();
$subseccion='';
$post=array();
$cdn='//turnos.app/assets/app';

//Si no pagó lo mandamos a la página de suspensión
if($general['estadoPago']==0){
    if($_SESSION['usuario']['logueado']==1){
        session_destroy();
    }
    header('location:https://cuatrolados.com/suspended');
}

//Defino timezone para clientes de todo el mundo
date_default_timezone_set($general['timezone']);


//Defino directorio de includes
define('incPath',$_SERVER['DOCUMENT_ROOT'].'/inc');


//Defino variables para poder poner codigo en todas las instancias
// $general['codigoHead']='';
// $general['codigoBody']='';


?>