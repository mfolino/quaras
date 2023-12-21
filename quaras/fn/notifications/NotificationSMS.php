<?php

/* 
    Link documentación: https://privado.smsmasivos.com.ar/ayuda/Gu%C3%ADa%20API%20SMS%20Masivos.pdf
*/

class NotificationSMS{

    public static function enviarRecordatorio($idTurno, $idTratamiento){
        GLOBAL $row, $general;
    
        $response['status'] = 'ERROR';

        db_query(0,
            "SELECT 
                t.fechaInicio, 
                p.nombre, 
                p.codArea, 
                p.telefono, 
                prof.nombre as profesional,
                trat.textoPost,
                t.fechaInicio
            FROM 
                pacientes p, 
                turnos t, 
                ordenes o, 
                profesionales prof, 
                tratamientos trat 
            WHERE 
                p.idPaciente = t.idPaciente AND 
                t.idTurno='".$idTurno."' AND 
                t.idOrden = o.idOrden AND 
                o.idProfesional = prof.idProfesional AND 
                o.idTratamiento = trat.idTratamiento 
        ");
        
        $telefono=$row['codArea'].$row['telefono'];
        if(trim($telefono)){
    
            if(date('Y-m-d',strtotime($row['fechaInicio']))==date('Y-m-d')){
                $dia='Hoy';
            }else{
                $dia=DateController::daysToDias(date('l',strtotime($row['fechaInicio'])));
            }
    
            $dia=$dia.' '.date("d/m/Y H:i",strtotime($row['fechaInicio']));

            $linkCancelacion = self::getLinkCancelacion($idTurno);
            /* $linkRequisitosTratamiento = $row['textoPost'] ? self::getLinkRequisitosTratamiento($idTratamiento) : ''; */
            /* $mensaje = self::getMensaje($row['nombre'], $dia, $linkRequisitosTratamiento); */
            
            $fechaEnvio = "";
            if($general['sms_recordatorio']){
                $fechaEnvio = date('Y-m-d H:i:s', strtotime($row['fechaInicio']));

                if($general['sms_recordatorio_horaPersonalizada']){
                    $fechaEnvio = date('Y-m-d', strtotime($fechaEnvio)). " ". $general['sms_recordatorio_horaPersonalizada'];
                }
            }

            $mensaje = self::getMensaje($row['nombre'], $dia, $linkCancelacion);
            $responseEnviarSMS = self::enviarSMS($telefono, $mensaje, $fechaEnvio);

            /* Util::printVar($responseEnviarSMS, '186.138.206.135'); */
            
            if($responseEnviarSMS == 0) $response['status'] = 'OK';
        }
    
        return $response;
    }

    public static function getLinkCancelacion($idTurno){
        GLOBAL $general;
        return 'https://'.$general['clientDomain'].'/cancel?t='.base64_encode($idTurno).'';
    }
    public static function getLinkRequisitosTratamiento($idTratamiento){
        return "https://reconquista.turnos.app/tPost?id={$idTratamiento}";
    }

    /* Máximo de 160 caracteres */
    public static function getMensaje($nombre, $fecha, $link){
        GLOBAL $general;
        $saludo = str_replace(
            ['%nombre%', '%fecha%','%link%'], 
            [$nombre, $fecha, $link],
            $general['sms_texto_recordatorio']
        );
        
        return $saludo;
    }


    public static function enviarSMS($numeroCliente, $mensaje, $fechaEnvio){
        GLOBAL $general;
        $endpointAPI = 'http://servicio.smsmasivos.com.ar/enviar_sms.asp';
        try {

            $parametrosAPI = array(
                "api" => 1,
                "usuario" => $general['smsApiUser'],
                "clave" => $general['smsApiKey'],
                "tos" => $numeroCliente,
                "respuestanumerica" => "1",
                "texto" => $mensaje
            );

            if($fechaEnvio){
                $parametrosAPI['fechadesde'] = $fechaEnvio;
            }

            /* if($_SERVER['REMOTE_ADDR'] == '186.138.206.135'){
                $parametrosAPI = array(
                    "api" => 1, 
                    "usuario" => $general['smsApiUser'], 
                    "clave" => $general['smsApiKey'], 
                    "tos" => $numeroCliente, 
                    "fechadesde" => '2022-11-10 15:13:00', 
                    "respuestanumerica" => "1",
                    "test" => "1"
                ); 
            } */

            $fields_string = http_build_query($parametrosAPI);
            $ch = curl_init();
            
            curl_setopt_array($ch, array(
                CURLOPT_URL => $endpointAPI,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $fields_string,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_SSL_VERIFYPEER => false
            ));
            
            $result=curl_exec($ch);
            curl_close($ch);
            
            print_r($result);

            /* Util::printVar($result); */
            $error = $result[0] == 0 ? 0 : $result;

        } catch (Exception $e) {
            $error=$e->getMessage();
        }
        return $error;
    }

    /* Ejecutado por el Cron Job Management de Cloudways */
    public static function confirmacion(){
        GLOBAL $row, $tot, $res;

        $idTurnosRecordatorios = [];
        db_query(0, 
            "SELECT 
                idRecordatorioSMS, 
                idTurno, 
                telefono, 
                mensaje 
            FROM 
                turnos_recordatorios_sms 
            WHERE 
                enviado <> 1
        ");

        if($tot > 0){
            for ($i=0; $i < $tot; $i++) { 
                $nres = $res->data_seek($i);
                $row = $res->fetch_assoc();
                
                self::enviarSMS($row['telefono'], $row['mensaje'], true);

                $idTurnosRecordatorios[]=$row['idRecordatorioSMS'];
            }
            
            $idsInString = implode(",", $idTurnosRecordatorios);
            db_update("UPDATE turnos_recordatorios_sms SET enviado = 1 WHERE idRecordatorioSMS IN (".$idsInString.")");
        }
    }
}

?>