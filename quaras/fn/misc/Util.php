<?php

class Util{

    /* Limpia el numero de teléfono */
    public static function limpiarTelefono($numero){
        $telefono=ltrim($numero,0);
        return str_replace('-','',$telefono);
    }
    
    /* Valida email y host */
    public static function is_valid_email($str) {
        $result = (false !== filter_var($str, FILTER_VALIDATE_EMAIL));
        if ($result){
            list($user, $domain) = explode('@', $str);
            $result = checkdnsrr($domain, 'MX');
        }
        return $result;
    }
    
    /* Genera 2 caracteres de color aleatorio para general luego un color hexadecimal */
    public static function random_color_part() {
        return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
    }
    
    /* Genera un color hexadecimal aleatorio */
    public static function random_color() {
        return self::random_color_part() . self::random_color_part() . self::random_color_part();
    }
    
    /* Recibido un color define si el texto tiene que ser blanco o negro para que se vea bien */
    public static function getContrastColor($hexColor)
    {
            // hexColor RGB
            $R1 = hexdec(substr($hexColor, 1, 2));
            $G1 = hexdec(substr($hexColor, 3, 2));
            $B1 = hexdec(substr($hexColor, 5, 2));

            // Black RGB
            $blackColor = "#000000";
            $R2BlackColor = hexdec(substr($blackColor, 1, 2));
            $G2BlackColor = hexdec(substr($blackColor, 3, 2));
            $B2BlackColor = hexdec(substr($blackColor, 5, 2));

             // Calc contrast ratio
             $L1 = 0.2126 * pow($R1 / 255, 2.2) +
                   0.7152 * pow($G1 / 255, 2.2) +
                   0.0722 * pow($B1 / 255, 2.2);

            $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
                  0.7152 * pow($G2BlackColor / 255, 2.2) +
                  0.0722 * pow($B2BlackColor / 255, 2.2);

            $contrastRatio = 0;
            if ($L1 > $L2) {
                $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
            } else {
                $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
            }

            // If contrast is more than 5, return black color
            if ($contrastRatio > 5) {
                return '#000000';
            } else { 
                // if not, return white color.
                return '#FFFFFF';
            }
    }
    
    /* Printea las variables de post, get, request o session */
    public static function printVar($tipo='request', $ip='', $die=true,$json=false){

        $variable = $tipo;
        if($tipo == 'post' ) $variable = $_POST;
        if($tipo == 'get' ) $variable = $_GET;
        if($tipo=='request') $variable = $_REQUEST;
        if($tipo=='session') $variable = $_SESSION;

        $muestro=1;

        if($ip!=''){
            if($_SERVER['REMOTE_ADDR']!=$ip){
                $muestro=0;
            }
        }


        if($muestro){
            if(!$json){
                echo '<pre>';
                print_r($variable);
                echo '</pre>';
            }else{
                echo json_encode($var);
            }
            
            if($die) die();
        }
    }
    
    /* Activa los errors */
    public static function errors(){
        error_reporting(E_ALL ^ E_NOTICE);
        ini_set('display_errors', 1);
    }
    
    /* Cantidad maxíma de agendas */
    public static function getAgendas($plan){
        global $general;
        
        if($plan==1) $maxAgendas=1;
        if($plan==2) $maxAgendas=2;
        if($plan==3) $maxAgendas=6;
        if($plan==4) $maxAgendas=20;
        if($plan==5) $maxAgendas = $general['cantidadAgendas'];
        
        if($general["cantidadAgendasTotal"]){
            $maxAgendas = $general["cantidadAgendasTotal"];
        }

        return $maxAgendas;
    }


    /* 
        Para reportes
    */
    public static function getAllOrdenes(){
        GLOBAL $tot, $res, $row;
        
        $ordenes=array();
        db_query(0,"select idTratamiento, idOrden from ordenes order by fechaAlta ASC");
        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();
            $ordenes[$row['idOrden']]=$row['idTratamiento'];
        }

        return $ordenes;
    }
    public static function comisionesConFecha($idProfesional=''){
        GLOBAL $tot, $row, $res;

        if($idProfesional<>''){					 
            $comisiones=array();
            $fechasComisiones=array();
            db_query(0,"select idTratamiento, cantidad, fechaAlta from comisiones where idProfesional='".$idProfesional."' order by fechaAlta DESC");
            for($i=0;$i<$tot;$i++){
                $nres=$res->data_seek($i);
                $row=$res->fetch_assoc();
                $comisiones[strtotime($row['fechaAlta'])][$row['idTratamiento']]=$row['cantidad'];
                $fechasComisiones[$row['idTratamiento']][]=strtotime($row['fechaAlta']);	
            }
        }else{
            $comisiones=array();
            $fechasComisiones=array();
            db_query(0,"select idTratamiento, cantidad, fechaAlta, idProfesional from comisiones order by fechaAlta DESC");
            for($i=0;$i<$tot;$i++){
                $nres=$res->data_seek($i);
                $row=$res->fetch_assoc();
                $comisiones[strtotime($row['fechaAlta'])][$row['idProfesional']][$row['idTratamiento']]=$row['cantidad'];
                $fechasComisiones[$row['idProfesional']][$row['idTratamiento']][]=strtotime($row['fechaAlta']);
            }
        }

        return [
            "comisiones" => $comisiones,
            "fechaComisiones" => $fechasComisiones
        ];
    }

    public static function getTurnosAtendidos($fechaDesde, $fechaHasta){
        GLOBAL $row, $res, $tot, $row1, $tot1;

        $turnosAtendidos=array();
        db_query(0,"select t.*, o.idProfesional from turnos t, ordenes o where (t.estado=1 or t.estado=2) and date(t.fechaInicio)>='".$fechaDesde."' and date(t.fechaInicio)<='".$fechaHasta."' and t.idOrden=o.idOrden order by t.fechaInicio ASC");
        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();
            
            $idProfesional=$row['idProfesional'];
            
            db_query(1,"select idProfesional from suplencias where idTurno='".$row['idTurno']."'");
            if($tot1>0){
                $idProfesional=$row1['idProfesional'];
            }
        
            $turnosAtendidos[$row['idTurno']]['idProfesional']=$idProfesional;
            
            $turnosAtendidos[$row['idTurno']]['idOrden']=$row['idOrden'];
            $turnosAtendidos[$row['idTurno']]['idPaciente']=$row['idPaciente'];
            $turnosAtendidos[$row['idTurno']]['fecha']=$row['fechaInicio'];
            $turnosAtendidos[$row['idTurno']]['estado']=$row['estado'];
        }

        return $turnosAtendidos;
    }

    public static function updateStaticData(){
        GLOBAL $row, $res, $tot;
        
        $feriados=array();

        //Voy a ir a leer los feriados para poner el día en gris
        db_query(0,"select fecha, nombre from feriados where estado='A'");

        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();
            $feriados[$row['fecha']]=$row['nombre'];
        }

        $_SESSION['feriados']=$feriados;

        /*Acá pinto de color los horarios de cada kinesiologo*/

        if($_SESSION['usuario']['profesional']){
            $filtroProfesional=" and p.idProfesional=".$_SESSION['usuario']['idUsuario'];
        }else{
            $filtroProfesional="";
        }

        $_SESSION['horarios']=array();

        db_query(0,"select hp.*, p.color from horariosprofesionales hp, profesionales p where hp.dia <> '' AND (hp.idHoras in(select max(idHoras) from horariosprofesionales group by idProfesional, dia) or hp.fechaEspecifica<>'0000-00-00') and hp.idProfesional=p.idProfesional and ((hp.desdeManana<>'' and hp.hastaManana<>'') or (hp.desdeTarde<>'' and hp.hastaTarde<>''))".$filtroProfesional."  order by hp.dia");

        /* if($_SERVER["REMOTE_ADDR"] == "138.121.84.107"){
            db_query(0, "select hp.*, p.color from horariosprofesionales hp, profesionales p where ( ( p.tipo = 'H' AND (hp.idHoras in(select max(idHoras) from horariosprofesionales group by idProfesional, dia) OR hp.fechaEspecifica<>'0000-00-00') AND hp.dia <> '' ) OR ( p.tipo = 'P' AND (hp.idHoras in(select max(idHoras) from horariosprofesionales group by idProfesional)) ) ) AND hp.idProfesional=p.idProfesional and ( (hp.desdeManana<>'' and hp.hastaManana<>'') or (hp.desdeTarde<>'' and hp.hastaTarde<>'') )  {$filtroProfesional} order by hp.dia; ");
        } */

        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();

            if($row['dia']){
                $dia=$row['dia'];
            }else{
                $dia=date("l",strtotime($row['fechaEspecifica']));
                $dia=DateController::daysToDias($dia);
            }

            $_SESSION['horarios'][$dia][$i]['desdeManana']=$row['desdeManana'];
            $_SESSION['horarios'][$dia][$i]['hastaManana']=$row['hastaManana'];
            $_SESSION['horarios'][$dia][$i]['desdeTarde']=$row['desdeTarde'];
            $_SESSION['horarios'][$dia][$i]['hastaTarde']=$row['hastaTarde'];
            $_SESSION['horarios'][$dia][$i]['color']=$row['color'];
            $_SESSION['horarios'][$dia][$i]['idProfesional']=$row['idProfesional'];
            $_SESSION['horarios'][$dia][$i]['fechaEspecifica']=$row['fechaEspecifica'];
        }

        session_write_close();
    }

    /*public static function convertirHora($hora, $timezone, $modo=0){

        global $general;

        if($modo==0){
            $date = new DateTime($hora, new DateTimeZone($general['timezone']));
            $date->setTimezone(new DateTimeZone($timezone));
        }else{
            $date = new DateTime($hora, new DateTimeZone($timezone));
            $date->setTimezone(new DateTimeZone($general['timezone']));
        }
        return $date->format('H:i');
    }*/
    
    public static function convertirHora($fecha, $timezone, $modo=0){

        global $general;

        if($modo==0){
            $date = new DateTime($fecha, new DateTimeZone($general['timezone']));
            $date->setTimezone(new DateTimeZone($timezone));
        }else{
            $date = new DateTime($fecha, new DateTimeZone($timezone));
            $date->setTimezone(new DateTimeZone($general['timezone']));
        }
        return $date->format('Y-m-d H:i:s');
    }

    public static function caracterAleatorio($desde, $hasta, $tipo = "numero"){
        return $tipo == "numero" ? rand($desde, $hasta) : chr(rand(ord($desde), ord($hasta)));
    }
}