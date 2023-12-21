<?
//Inicio sesion si la misma no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//Chequea si el usuario está logueado, sino lo tira al login y elimina la sesion
function checkLogin(){
    if($_SESSION['usuario']['logueado']<>1){
        echo "No es admin";
        Util::printVar($_SESSION);
        
        session_destroy();
        header('location:/admin');
    }
}

//Verifica si el usuario logueado que intenta acceder al contenido es admin, si no lo es lo tira al login y elimina la sesion
function checkAdmin(){
    if($_SESSION['usuario']['tipo']>1){
        session_destroy();
        header('location:/admin');
    }
}

//Verifica si el usuario logueado que intenta acceder al contenido es super admin, si no lo es lo tira al login y elimina la sesion
function checkSuperAdmin(){
    if($_SESSION['usuario']['tipo']>0){
        session_destroy();
        header('location:/admin');
    }
}

//Verifica si es admin y en base a eso permite ver algo o no
function isAdmin(){
    if($_SESSION['usuario']['tipo']<1){
        return true;
    }else{
        return false;
    }
}

//Elimina los 0 del principio del telefono y los guiones
function limpiarTelefono($numero){
    $telefono=ltrim($numero,0);
    $telefono=str_replace('-','',$telefono);
    return $telefono;
}

//Verifica si el mail y el host son válidos
function is_valid_email($str) {
    $result = (false !== filter_var($str, FILTER_VALIDATE_EMAIL));
    if ($result){
        list($user, $domain) = explode('@', $str);
        $result = checkdnsrr($domain, 'MX');
    }
	
    return $result;
}

//Devuelve la cantidad de agendas máximas que puede tener un usuario según el plan que tenga a excepción de que venga definido por base de datos en plan a medida
function getAgendas($plan){
    
    global $general;

    if($plan==1){
        $maxAgendas=1;
    }
    if($plan==2){
        $maxAgendas=2;
    }
    if($plan==3){
        $maxAgendas=6;
    }
    if($plan==4){
        $maxAgendas=20;
    }
    if($plan==0){
        //Es un plan a medida, tomamos el valor de la base de datos
        $maxAgendas=$general['cantidadAgendas'];
    }

    return $maxAgendas;
}

//Limpia los valores enviados a las funciones de base de datos para evitar inyección de código
function cleanVar($param){
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

//Genera 2 caracteres de color aleatorio para general luego un color hexadecimal
function random_color_part() {
    return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
}

//Genera un color hexadecimal aleatorio
function random_color() {
    return random_color_part() . random_color_part() . random_color_part();
}

//Devuelve el día de la semana en español en base al día en inglés
function daysToDias($day,$lower=true){
    $diaSemana['Monday']='Lunes';
    $diaSemana['Tuesday']='Martes';
    $diaSemana['Wednesday']='Miercoles';
    $diaSemana['Thursday']='Jueves';
    $diaSemana['Friday']='Viernes';
    $diaSemana['Saturday']='Sabado';
    $diaSemana['Sunday']='Domingo';

    if($lower){
        return strtolower($diaSemana[$day]);
    }else{
        return $diaSemana[$day];
    }
}

//Devuelve el día de la semana en inglés en base al día en español
function diasToDays($day,$lower=true){
    $diaSemana['Lunes']='Monday';
    $diaSemana['Martes']='Tuesday';
    $diaSemana['Miercoles']='Wednesday';
    $diaSemana['Jueves']='Thursday';
    $diaSemana['Viernes']='Friday';
    $diaSemana['Sabado']='Saturday';
    $diaSemana['Domingo']='Sunday';

    if($lower){
        return strtolower($diaSemana[$day]);
    }else{
        return $diaSemana[$day];
    }
}

//Devuelve el número de día de la semana en base al día en español
function diasToNum($day,$lower=true){
    $diasSemana['domingo']=0;
    $diasSemana['lunes']=1;
    $diasSemana['martes']=2;
    $diasSemana['miercoles']=3;
    $diasSemana['jueves']=4;
    $diasSemana['viernes']=5;
    $diasSemana['sabado']=6;

    if($lower){
        return strtolower($diasSemana[$day]);
    }else{
        return $diasSemana[$day];
    }
}

//Printea las variables de post, get, request o session
function printVar($tipo='request',$die=true,$json=false){
    if($tipo=='post'){
        $var=$_POST;
    }else if($tipo=='get'){
        $var=$_GET;
    }else if($tipo=='request'){
        $var=$_REQUEST;
    }else if($tipo=='session'){
        $var=$_SESSION;
    }else{
        $var=$tipo;
    }

    if(!$json){
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }else{
        echo json_encode($var);
    }
    
    if($die){
        die();
    }
}

//Verifica si un rango está dentro de otro
function overlap($start_date, $end_date, $date_from_user, $limits=true){
    // Convert to timestamp
    $start_ts = strtotime($start_date);
    $end_ts = strtotime($end_date);
    $user_ts = strtotime($date_from_user);

    // Verifica si la fecha está dentro del rango
    if($limits){
        //En este caso incluye el principio y el final
        return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
    }else{
        //En este caso no incluye el principio y el final
        return (($user_ts > $start_ts) && ($user_ts < $end_ts));
    }
}

function ret($response){
    header('Content-Type: application/json');
    echo json_encode($response);
}

function errors(){
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
