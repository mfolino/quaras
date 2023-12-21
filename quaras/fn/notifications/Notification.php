<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '/home/master/lib/vendor/autoload.php';

class Notification{
    
    public static function sendEmailArchivo($nombreCliente, $mailCliente, $mensaje, $domain, $subject, $pathFileSend) {
        GLOBAL $general; 

        /* Util::printVar([$nombreCliente, $mailCliente, $mensaje, $domain, $subject, $pathFileSend]); */

        $url = 'https://api.elasticemail.com/v4/emails/transactional';

        $copia = explode(",", $general['copiarMailEnElMailConfirmacion']);

        if(!is_array($mailCliente)){
            $mailCliente = array($mailCliente);
        }

        try {
            $post['Recipients']['To']=$mailCliente;
            $post['Recipients']['BCC']=$copia;

            $post['Content']['Body']=array(
                array(
                    'ContentType'=>'HTML',
                    'Content'=>$mensaje,
                    'Charset'=>'utf-8'
                )
            );
                
            $post['Content']['Attachments'][] = [
                'BinaryContent'=>base64_encode(file_get_contents($pathFileSend["filepath"])),
                'Name'=>basename($pathFileSend["filename"])
            ];

            /* Util::printVar($post['Content']['Attachments']); */

            $post['Content']['Headers']=array(
                'MessageID' => time()
            );
            $post['Content']['EnvelopeFrom']=$nombreCliente;
            $post['Content']['From']='noresponder@turnos.app';
            $post['Content']['Subject']=$subject;
            $post['Options']['ChannelName']=$domain;
            $post['Options']['TrackOpens']=true;
            $post['Options']['TrackClicks']=true;

            $ch = curl_init();

            $headers = array(
                "X-ElasticEmail-ApiKey: 3EBEB7ED3F31BED16561B92CB090D393826AB201A9EC3895D1F0DFADCD0C949B9506B294E94879B57ECE7B21244EF835",
                "Content-Type: application/json",
            );
                
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($post),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false
            ));

            $result=curl_exec($ch);
            curl_close($ch);
            
            $error=0;
        } catch (Exception $e) {
            $error=$e->getMessage();
        }
        
        return $error;
    }

    public static function sendEmail($nombreCliente, $mailCliente, $mensaje, $domain, $subject, $enviarArchivo = false, $isPrimerTurnoDelPaciente = false) {
        GLOBAL $general; 
        // Array con el nombre del archivo y el path [ "filename" => "", "filepath" => "" ]
        GLOBAL $pathFileSend;
        
        /* Util::printVar([$nombreCliente, $mailCliente, $mensaje, $domain, $subject, $enviarArchivo, $isPrimerTurnoDelPaciente], "138.121.84.107"); */
        
        
        if($general["PHPMAILER_ACTIVE"]){
            $responsePHPMAILER = self::PHPMAILER_sendMail($nombreCliente, $mailCliente, $mensaje, $domain, $subject, $enviarArchivo = false, $isPrimerTurnoDelPaciente = false);
            /* Util::printVar($responsePHPMAILER); */
            return $responsePHPMAILER;
        }

        /* $url = 'https://api.elasticemail.com/v2/email/send'; */
        $url = 'https://api.elasticemail.com/v4/emails/transactional';

        /* $myBcc = $general['copiarMailEnElMailConfirmacion']; */

        $copia = explode(",", $general['copiarMailEnElMailConfirmacion']);
        $copiaOculta = '';

        if(!is_array($mailCliente)){
            $mailCliente = array($mailCliente);
        }
        try {

            $post['Recipients']['To']=$mailCliente;
            // $post['Recipients']['CC']=$copia;
            $post['Recipients']['BCC']=$copia;

            $post['Content']['Body']=array(
                array(
                    'ContentType'=>'HTML',
                    'Content'=>$mensaje,
                    'Charset'=>'utf-8'
                )
            );
                
            if($enviarArchivo){
                
                $archivos = array();

                if($general['mailDeConfirmacion_enviarArchivo_porTurno'] && isset($pathFileSend)){
                    $pathFile = $pathFileSend["filepath"];
                    $pathFileName = $pathFileSend["filename"];
                    $archivos[] = [
                        'BinaryContent'=>base64_encode(file_get_contents($pathFile)),
                        'Name'=>basename($pathFileName)
                    ];
                }
                
                if($general['mailDeConfirmacion_enviarArchivo_path'] && $isPrimerTurnoDelPaciente){
                    $pathFile = $_SERVER["DOCUMENT_ROOT"]."uploads/".$general['mailDeConfirmacion_enviarArchivo_path'];
                    $pathFileName = $general['mailDeConfirmacion_enviarArchivo_path']; 
                    $archivos[] = [
                        'BinaryContent'=>base64_encode(file_get_contents($pathFile)),
                        'Name'=>basename($pathFileName)
                    ];
                }

                $post['Content']['Attachments']=$archivos;                
            }

            $post['Content']['Headers']=array(
                'MessageID' => time()
            );
            $post['Content']['EnvelopeFrom']=$nombreCliente;
            $post['Content']['From']=$general['elastic_remitente'];
            

            /* if($mailConfig['replyto']){
                $post['Content']['ReplyTo']=$mailConfig['replyto'];
            } */

            $post['Content']['Subject']=$subject;
                
                
            $post['Options']['ChannelName']=$domain;
            $post['Options']['TrackOpens']=true;
            $post['Options']['TrackClicks']=true;


            $ch = curl_init();

            $headers = array(
                "X-ElasticEmail-ApiKey: 3EBEB7ED3F31BED16561B92CB090D393826AB201A9EC3895D1F0DFADCD0C949B9506B294E94879B57ECE7B21244EF835",
                "Content-Type: application/json",
            );
                
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($post),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false
            ));

            /* 
                $post = array(
                    'from' => 'noresponder@turnos.app',
                    'fromName' => $nombreCliente,
                    'apikey' => '3EBEB7ED3F31BED16561B92CB090D393826AB201A9EC3895D1F0DFADCD0C949B9506B294E94879B57ECE7B21244EF835',
                    'subject' => $subject,
                    'msgTo' => $mailCliente,
                    'msgBcc' => $myBcc,
                    'bodyHtml' => $mensaje,
                    'charset' => 'UTF-8',
                    'trackClicks' => false,
                    'trackOpens' => 'true',
                    'channel' => $domain,
                    // 'bodyText' => 'Text Body',
                    'isTransactional' => true
                );
                
                $ch = curl_init();
                
                curl_setopt_array($ch, array(
                    CURLOPT_URL => $url,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $post,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_SSL_VERIFYPEER => false
                )); 
            */
            
            $result=curl_exec($ch);
            curl_close($ch);

            /* if ($_SERVER['REMOTE_ADDR'] == '181.94.40.6'):
                Util::printVar($result);
            endif; */
            
            $error=0;
        } catch (Exception $e) {
            $error=$e->getMessage();
        }
        
        return $error;
    }
    
    public static function sendSMS($numeroCliente, $texto) {
        $url = 'http://servicio.smsmasivos.com.ar/enviar_sms.asp';
        $myBcc='';
        
        try {
            $parametrosAPI = array(
                "api" => 1,
                "usuario" => $general['smsApiUser'],
                "clave" => $general['smsApiKey'],
                "tos" => $numeroCliente,
                "texto" => $texto
            );
            
            $ch = curl_init();
            
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $parametrosAPI,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_SSL_VERIFYPEER => false
            ));
            
            $result=curl_exec($ch);
            curl_close($ch);

            $error = 0;
        } catch (Exception $e) {
            $error=$e->getMessage();
        }
        
        return $error;
    }
    
    public static function sendWhatsapp($nombreCliente, $mailCliente, $mensaje, $domain, $subject) {
        $url = 'https://api.elasticemail.com/v2/email/send';
        $myBcc='';
        
        try {
            $post = array(
                'from' => 'noresponder@turnos.app',
                'fromName' => $nombreCliente,
                'apikey' => '3EBEB7ED3F31BED16561B92CB090D393826AB201A9EC3895D1F0DFADCD0C949B9506B294E94879B57ECE7B21244EF835',
                'subject' => $subject,
                'msgTo' => $mailCliente,
                'msgBcc' => $myBcc,
                'bodyHtml' => $mensaje,
                'charset' => 'UTF-8',
                'trackClicks' => false,
                'trackOpens' => 'true',
                'channel' => $domain,
                // 'bodyText' => 'Text Body',
                'isTransactional' => true
            );
            
            $ch = curl_init();
            
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $post,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_SSL_VERIFYPEER => false
            ));
            
            $result=curl_exec($ch);
            curl_close($ch);
            
            $error=0;
        } catch (Exception $e) {
            $error=$e->getMessage();
        }
        
        return $error;
    }
    

    public static function recordatorioSMS($idTurno){
        GLOBAL $res, $tot, $row, $general;

        db_query(0, "SELECT o.idTratamiento FROM turnos t, ordenes o WHERE t.idOrden = o.idOrden AND t.idTurno = {$idTurno}");
        NotificationSMS::enviarRecordatorio($idTurno, $row['idTratamiento']);
    }


    public static function confirmacion($idTurno, $estado=0, $usuarioCancela=false) {
        global $res, $tot, $row, $general, $row2, $row1, $linkReunion;
        global $row3, $tot3;
        global $row5;
        GLOBAL $tot11,$row11,$nres11, $res11;
        GLOBAL $row8;
        // $pathFileSend = [ "filename" => "example.pdf", "filepath" => ".../files/example.pdf" ]
        GLOBAL $pathFileSend;
        $pathFileSend = $pathFileSend;

        /* Util::printVar([$pathFileSend, "confirmacion()"], "138.121.84.107", false); */
        /* Util::printVar([$linkReunion, $idTurno], "181.99.172.180", true); */

        //$idTurno puede ser un vector de turnos. Por default, para tomar los datos del pax y todo vamos a tomar siempre el primero.
        $turnos=$idTurno;
        
        if (!is_array($turnos)) {
            $turnos=array();
            $turnos[]=$idTurno;
        }
        $idTurno=$turnos[0];

        if(!$usuarioCancela){
            $usuarioCancela=$general['usuarioCancela'];
        }

        /* Busco la informacion del turno */
        db_query(0,
            "SELECT 
                p.idPaciente, 
                p.nombre, 
                p.apellido, 
                p.mail, 
                t.fechaInicio, 
                t.idOrden, 
                p.codArea, 
                p.telefono,
                p.observaciones, 
                o.idTratamiento,
                o.idProfesional 
            FROM 
                turnos t, 
                pacientes p, 
                ordenes o, 
                tratamientos trat
            WHERE 
                t.idOrden = o.idOrden AND 
                o.idTratamiento = trat.idTratamiento AND 
                p.idPaciente = t.idPaciente AND 
                t.estado='{$estado}' AND 
                t.idTurno='{$idTurno}' 
            LIMIT 1
        ");

        $nombrePaciente = ucfirst($row["nombre"]);
        $apellidoPaciente = ucfirst($row["apellido"]);
        $mail = $row['mail'];
        $idProfesional = $row['idProfesional'];
        $idTratamiento = $row['idTratamiento'];
        $idPaciente = $row['idPaciente']; // Para buscar los mails alternativos
        $iconoExito=0;
        $tablaProductosMail=0;
        $tituloMail='<h1><img src="https://turnos.app/assets/img/sistema/calendar.png" /> </h1><h1>Confirmación de '.$general["nombreTurno"].'!</h1>';
        $alineacionTituloMail='center';

        // Zona horaria actual
        $zonaHorariaActual = date_default_timezone_get();

        $fechas=array();

        $textoLink = $general["mail_confirmacion_meeting_textoDelLink"] ? $general["mail_confirmacion_meeting_textoDelLink"] : "Acceder a la reunión";

        foreach($turnos as $turno){

            db_query(1, "SELECT fechaInicio FROM turnos WHERE idTurno='{$turno}' LIMIT 1");

            if(isset($general['husosDinamicos']) && $general['husosDinamicos']){
                $date = new DateTime($row1['fechaInicio'], new DateTimeZone($zonaHorariaActual)); 
                $date->setTimeZone(new DateTimeZone($row['observaciones']));

                if(date("Y-m-d",strtotime($row1['fechaInicio']))==date("Y-m-d")){

                    $string = "Hoy ". $date->format('H:i');

                }else{
                    $string = $date->format('d/m/Y H:i');
                }

                if(@$linkReunion[$turno]){
                    $string.=': <a href="'.$linkReunion[$turno].'">'.$textoLink.'</a>';
                }

                $fechas[] = $string;

            }else{
                if(date("Y-m-d",strtotime($row1['fechaInicio']))==date("Y-m-d")){
                    $string = "Hoy ".date("H:i",strtotime($row1['fechaInicio']))." hs.";
                }else{
                    $string = date("d/m/Y H:i",strtotime($row1['fechaInicio']))." hs."; 
                }

                if($general["google_meet"] || $general["zoom"]){
                    $tableTurnosMeeting = db_getOne("SELECT * FROM turnos_meetings WHERE idProfesional = {$row['idProfesional']} AND idTratamiento = {$row['idTratamiento']} AND fechaInicio = '{$row["fechaInicio"]}'");
                    if($tableTurnosMeeting){
                        $string.=': <a href="'.$tableTurnosMeeting->link.'">'.$textoLink.'</a>';
                    }
                }else{
                    if($general["mail_confirmacion_linkReunionProfesional"]){
                        $dataProfesional = db_getOne("SELECT * FROM profesionales WHERE idProfesional = {$row['idProfesional']} ");
                        $string.=': <a href="'.$dataProfesional->linkZoom.'">'.$textoLink.'</a>';
                    }else{
                        if(@$linkReunion[$turno]){
                            $string.=': <a href="'.$linkReunion[$turno].'">'.$textoLink.'</a>';
                        }
                    }
                }

                $fechas[] = $string;
            }
        }
        
        // Seteo la zona horaria a la anterior
        date_default_timezone_set($zonaHorariaActual);

        if($general['mailFirmaProfesional']){
            //Busco el nombre del profesional
            db_query(1,"select pro.nombre from profesionales pro, ordenes o where o.idProfesional=pro.idProfesional and o.idOrden='".$row['idOrden']."' limit 1");
            $firma=$row1['nombre'];
        } else {
            //Sino tomo el de la empresa
            $firma=$general['nombreCliente'];
        }

        // Verifico si tiene un texto personalizado
        db_query(2,
            "SELECT t.nombre as tratamiento, t.textoPre, t.textoPost
            FROM ordenes o, tratamientos t
            WHERE t.idTratamiento = o.idTratamiento AND o.idOrden = '{$row['idOrden']}'
            LIMIT 1 
        ");

        $textoPre = '';
        if($row2['textoPre']){
            $textoPre = str_replace("%nombre%", $nombrePaciente." ". $apellidoPaciente , $row2['textoPre']);
        }
        $textoPost = $row2['textoPost'] ? ucfirst($row2['textoPost']).'<br><br>' : '';

        if(@sizeof($fechas)==1){
            $subtituloMail=str_replace(
                array('%nombre%'), 
                array($nombrePaciente.' '.$apellidoPaciente), 
                $general['mailTextoArribaConfirmacion']
            ).' para '.$row2['tratamiento'].':<br><br>'.$textoPre.'<b><span style="color:#000;font-size:16px">';   
        }else{
            $subtituloMail=str_replace(
                array('%nombre%'), 
                array($nombrePaciente.' '.$apellidoPaciente), 
                $general['mailTextoArribaConfirmacionMultiples']
            ).' para '.$row2['tratamiento'].' en las siguientes fechas:<br><br>'.$textoPre.'<b><span style="color:#000;font-size:16px">';   
        }

        /* Util::printVar([$nombrePaciente, $apellidoPaciente, $subtituloMail, $general['mailTextoArribaConfirmacionMultiples']], "138.121.84.107", false); */

        if(@sizeof($fechas)==1){
            $subtituloMail.=$fechas[0];
            $nombreTurno='el';
            $nombreTurnoCancel= $general["nombreTurno"];
        }else{
            foreach($fechas as $fecha){
                $subtituloMail.='<li>'.$fecha.'</li>';
                $nombreTurno='algún';
                $nombreTurnoCancel=$general["nombreTurnos"];
            }
        }

        $subtituloMail.='</span></b><br><br>'.$textoPost."<br>".$general['leyendaMail'];

        if($usuarioCancela){
            if($general["leyendaMail_mensajeCancelacion"]){
                $subtituloMail.='<br><br>'.$general["leyendaMail_mensajeCancelacion"];
            }else{
                $subtituloMail.='<br><br>En caso de que desees cancelar '.$nombreTurno.' turno, podés hacerlo hasta '.$general['hsAntesCancelacion'].'hs. antes del mismo.';
            }
            $subtituloMail.='<br><br><br><br><a href="https://'.$general['clientDomain'].'/cancel?t='.base64_encode($idTurno).'" style="border:1px solid #'.$general['colorPrimario'].';background-color:#'.$general['colorPrimarioHover'].';padding:15px 30px;margin:20px 0; color:#ffffff;font-size:16px;font-weight:bold;text-decoration:none" type="button">Cancelar '.$nombreTurnoCancel.'</a>';
        }
        
        if($general['mail_confirmacion_textoPrevioFirma']){
            $subtituloMail.="<br><br><br><br>".$general['mail_confirmacion_textoPrevioFirma'];
        }

        $subtituloMail.= '<br><br><br><br><h2>'.$firma.'</h2><i>Te esperamos.</i>';
        $alineacionSubtituloMail='left';
        $textoPie='<small>Este aviso se envía de forma automática y no se responden consultas por este medio.</small>';
        $productos='';

        /* Util::printVar($subtituloMail, "186.138.206.135", true); */

        ob_start();
        include(fn."/res/mailTemplate.php");
        $mensaje=ob_get_contents();
        ob_end_clean();
        $mensaje = str_replace("nbsp", " ", $mensaje);

        /* Util::printVar($mensaje, "138.121.84.107"); */
        
        // Valido que el tratamiento permita enviar mails de confirmacion
        $puedeEnviarMail = true;
        if($general['tratamiento_enviarMailConfirmacion'] && Migration::existColumn('tratamientos','enviarMailConfirmacion')){
            db_query(11, "SELECT idTratamiento, enviarMailConfirmacion FROM tratamientos WHERE idTratamiento = '{$idTratamiento}' LIMIT 1");
            if($row11['enviarMailConfirmacion'] != '1'){
                $puedeEnviarMail = false;
            }
        }

        /* Util::printVar([$puedeEnviarMail, $general['tratamiento_enviarMailConfirmacion'], $mail, $row11], '190.31.193.129'); */
        
        // Enviar Mail
        if(Util::is_valid_email($mail) && $puedeEnviarMail){

            // Envio recordatorio a varios mails
            if($general['usaMailsAlternativos']){
                $mails = [$row['mail']];

                db_query(8,"SELECT * FROM pacientes WHERE idPaciente = {$idPaciente} LIMIT 1");

                // Mail alternativo 1
                if($row8['mailAlternativo1'])
                    $mails[] = $row8['mailAlternativo1'];

                // Mail alternativo 2
                if($row8['mailAlternativo2'])
                    $mails[] = $row8['mailAlternativo2'];

                $mail = implode(",",$mails);
            }

            if($general["mail_confirmacion_copiarAlProfesional"]){
                $dataProfesional = db_getOne("SELECT * FROM profesionales WHERE idProfesional = {$idProfesional} ");
                $mail = array($mail, $dataProfesional->email);
            }


            // Valido que sea el primer turno del paciente
            db_query(3, "SELECT t.idTurno FROM turnos t, pacientes p WHERE t.idPaciente = p.idPaciente AND p.idPaciente = '{$idPaciente}' ");
            $isPrimerTurnoDelPaciente = $tot3 == 1;

            $enviarArchivo = false;        
                
            // Verifico si tiene que enviar un archivo
            if($general['mailDeConfirmacion_enviarArchivo']){
                // valido que el servicio autorize el envío
                if($general['mailDeConfirmacion_enviarArchivo_primerTurnoPorServicio'] && Migration::existColumn('tratamientos', 'enviarArchivoEnPrimerTurno')){
                    db_query(3, "SELECT * FROM tratamientos WHERE idTratamiento = {$idTratamiento} AND estado = 'A' ");
                    $enviarArchivoEnPrimerTurno = $row3['enviarArchivoEnPrimerTurno'];

                    // Valido que sea el primer turno del paciente y que el servicio permita enviar el archivo
                    if($enviarArchivoEnPrimerTurno && $isPrimerTurnoDelPaciente){
                        $enviarArchivo = true;
                    }
                }

                if($general['mailDeConfirmacion_enviarArchivo_porTurno'] && isset($pathFileSend) && $pathFileSend){
                    $enviarArchivo = true;
                }
            }

            if($general["mailDeConfirmacion_enviarArchivo_path"]){
                $enviarArchivo = true;
            }
            if($general["mailDeConfirmacion_enviarArchivo_path_siempre"]){
                $isPrimerTurnoDelPaciente = true;
            }


            /* Util::printVar($mail, "190.231.245.234"); */
            
            $error = self::sendEmail($general['nombreCliente'], $mail, $mensaje, $general['clientDomain'], "Confirmación de {$general['nombreTurno']}", $enviarArchivo, $isPrimerTurnoDelPaciente);
            
        }


        /* 
            Se puede programar la fecha y hora en la que se envia el mensaje desde la API
        */
        if($general['sms_recordatorio'] && $general['sms_recordatorio_horaPersonalizada']){
            self::recordatorioSMS($idTurno);
        }
        
    }

    
    /* App - Lagos */
    public static function confirmacion_appLagos($idTurno, $estado=0, $usuarioCancela=false){
        global $res, $tot, $row, $general, $row2, $row1, $linkReunion;
        global $row3, $tot3;
        global $row5;
        GLOBAL $tot11,$row11,$nres11, $res11;
        GLOBAL $row8;
        // Data = [ "filename" => "", "filepath" => "" ]
        GLOBAL $pathFileSend;

        $pathFileSend = $pathFileSend;

        //$idTurno puede ser un vector de turnos. Por default, para tomar los datos del pax y todo vamos a tomar siempre el primero.
        $turnos=$idTurno;
        
        if (!is_array($turnos)) {
            $turnos=array();
            $turnos[]=$idTurno;
        }
        $idTurno=$turnos[0];

        if(!$usuarioCancela){
            $usuarioCancela=$general['usuarioCancela'];
        }

        /* Busco la informacion del turno */
        db_query(0,
            "SELECT 
                p.idPaciente, 
                p.nombre, 
                p.apellido, 
                p.mail, 
                t.fechaInicio, 
                t.idOrden, 
                t.vehiculos, 
                p.codArea, 
                p.telefono,
                p.observaciones, 
                o.idProfesional 
            FROM 
                turnos t, 
                pacientes p, 
                ordenes o
            WHERE 
                t.idOrden = o.idOrden AND 
                p.idPaciente = t.idPaciente AND 
                t.estado='{$estado}' AND 
                t.idTurno='{$idTurno}' 
            LIMIT 1
        ");

        $mail = $row['mail'];
        $nombreCompletoPaciente = ucfirst($row["nombre"])." ".ucfirst($row["apellido"]);
        $idProfesional = $row['idProfesional'];
        $idTratamiento = $row['idTratamiento'];
        $idPaciente = $row['idPaciente']; // Para buscar los mails alternativos
        $iconoExito=0;
        $tablaProductosMail=0;
        $tituloMail='<h1><img src="https://turnos.app/assets/img/sistema/calendar.png" /> </h1><h1>Confirmación de turno!</h1>';
        $alineacionTituloMail='center';
        // Zona horaria actual
        $zonaHorariaActual = date_default_timezone_get();

        $fechas=array();
    

        foreach($turnos as $turno){

            db_query(1, "SELECT fechaInicio FROM turnos WHERE idTurno='{$turno}' LIMIT 1");

            if(date("Y-m-d",strtotime($row1['fechaInicio']))==date("Y-m-d")){
                $string = "Hoy ".date("H:i",strtotime($row1['fechaInicio']));
            }else{
                $string = date("d/m/Y H:i",strtotime($row1['fechaInicio'])); 
            }

            if(@$linkReunion[$turno]){
                $string.=': <a href="'.$linkReunion[$turno].'">Acceder al Meet</a>';
            }

            $fechas[] = $string;
        }
        
        // Seteo la zona horaria a la anterior
        date_default_timezone_set($zonaHorariaActual);

        if($general['mailFirmaProfesional']){
            //Busco el nombre del profesional
            db_query(1,"select pro.nombre from profesionales pro, ordenes o where o.idProfesional=pro.idProfesional and o.idOrden='".$row['idOrden']."' limit 1");
            $firma=$row1['nombre'];
        } else {
            //Sino tomo el de la empresa
            $firma=$general['nombreCliente'];
        }

        $subtituloMail = str_replace("%nombre%",$nombreCompletoPaciente, $general["mailTextoArribaConfirmacion"]). " para el día: <b> ";

        if(@sizeof($fechas)==1){
            $subtituloMail.=$fechas[0];
            $nombreTurno='el';
            $nombreTurnoCancel='turno';
        }else{
            foreach($fechas as $fecha){
                $subtituloMail.='<li>'.$fecha.'</li>';
                $nombreTurno='algún';
                $nombreTurnoCancel='turnos';
            }
        }
        $subtituloMail .= "</b>";
        
        // Busco la imagen del profesional
        db_query(5, "SELECT imageName, nombre, dni FROM profesionales WHERE idProfesional = '{$idProfesional}' LIMIT 1");
        $imageProfesional = "<p>Te atenderás con el profesiona {$row5["nombre"]} con DNI {$row5['dni']}</p><img src='https://{$general['clientDomain']}/uploads/profesionales/{$row5['imageName']}' style='width: 150px; margin-bottom: 10px'/>";
        $subtituloMail.='<br><br>'.$imageProfesional."<br><br>".$general['leyendaMail'];

        $subtituloMail.='<br><br>En caso de que desees reagendar '.$nombreTurno.' turno, podés hacerlo hasta '.$general['hsAntesCancelacion'].'hs. antes del mismo.';
        
        $subtituloMail.='<br><br><br><br><a href="https://'.$general['clientDomain'].'/cancel?t='.base64_encode($idTurno).'" style="border:1px solid #'.$general['colorPrimario'].';background-color:#'.$general['colorPrimarioHover'].';padding:15px 30px;margin:20px 0; color:#ffffff;font-size:16px;font-weight:bold;text-decoration:none" type="button">Reagendar '.$nombreTurnoCancel.'</a><br><br><br><br><h2>'.$firma.'</h2><i>Te esperamos.</i>';
        $alineacionSubtituloMail='left';
        $textoPie='<small>Este aviso se envía de forma automática y no se responden consultas por este medio.</small>';
        $productos='';

        /* Util::printVar($subtituloMail, "186.138.206.135", true); */

        ob_start();
        include(fn."/res/mailTemplate.php");
        $mensaje=ob_get_contents();
        ob_end_clean();

        // Enviar Mail
        if(Util::is_valid_email($mail)){

            // Envio recordatorio a varios mails
            if($general['usaMailsAlternativos']){
                $mails = [$row['mail']];

                db_query(8,"SELECT * FROM pacientes WHERE idPaciente = {$idPaciente} LIMIT 1");

                // Mail alternativo 1
                if($row8['mailAlternativo1'])
                    $mails[] = $row8['mailAlternativo1'];

                // Mail alternativo 2
                if($row8['mailAlternativo2'])
                    $mails[] = $row8['mailAlternativo2'];

                $mail = implode(",",$mails);
            }

            if($general["mail_confirmacion_copiarAlProfesional"]){
                $dataProfesional = db_getOne("SELECT * FROM profesionales WHERE idProfesional = {$idProfesional} ");
                if(Util::is_valid_email($dataProfesional->email)){
                    $mail = array($mail, $dataProfesional->email);
                }
            }

            // Valido que sea el primer turno del paciente
            db_query(3, "SELECT t.idTurno FROM turnos t, pacientes p WHERE t.idPaciente = p.idPaciente AND p.idPaciente = '{$idPaciente}' ");
            $isPrimerTurnoDelPaciente = false;
            $enviarArchivo = false;


            $error = self::sendEmail($general['nombreCliente'], $mail, $mensaje, $general['clientDomain'], "Confirmación de turno", $enviarArchivo, $isPrimerTurnoDelPaciente);
        }

    }

    public static function turnoReagendado($idTurno, $estado=0, $usuarioCancela=false, $motivo , $fechaAnterior){
        
        global $res, $tot, $row, $general, $row2, $row1, $linkReunion;
        global $row3, $tot3;
        global $row5;
        GLOBAL $tot11,$row11,$nres11, $res11;
        GLOBAL $row8;
        // Data = [ "filename" => "", "filepath" => "" ]
        GLOBAL $pathFileSend;

        $pathFileSend = $pathFileSend;

        //$idTurno puede ser un vector de turnos. Por default, para tomar los datos del pax y todo vamos a tomar siempre el primero.
        $turnos=$idTurno;
        
        if (!is_array($turnos)) {
            $turnos=array();
            $turnos[]=$idTurno;
        }
        $idTurno=$turnos[0];

        if(!$usuarioCancela){
            $usuarioCancela=$general['usuarioCancela'];
        }

        /* Busco la informacion del turno */
        db_query(0,
            "SELECT 
                p.idPaciente, 
                p.nombre, 
                p.apellido, 
                p.mail, 
                t.fechaInicio, 
                t.idOrden, 
                t.vehiculos, 
                p.codArea, 
                p.telefono,
                p.observaciones, 
                o.idProfesional 
            FROM 
                turnos t, 
                pacientes p, 
                ordenes o
            WHERE 
                t.idOrden = o.idOrden AND 
                p.idPaciente = t.idPaciente AND 
                t.estado='{$estado}' AND 
                t.idTurno='{$idTurno}' 
            LIMIT 1
        ");

        $mail = $row['mail'];
        $nombreCompletoPaciente = ucfirst($row["nombre"])." ".ucfirst($row["apellido"]);
        $idProfesional = $row['idProfesional'];
        $idTratamiento = $row['idTratamiento'];
        $idPaciente = $row['idPaciente']; // Para buscar los mails alternativos
        $iconoExito=0;
        $tablaProductosMail=0;
        $tituloMail='<h1><img src="https://turnos.app/assets/img/sistema/calendar.png" /> </h1><h1>Confirmación de turno!</h1>';
        $alineacionTituloMail='center';
        // Zona horaria actual
        $zonaHorariaActual = date_default_timezone_get();

        $fechas=array();
    

        foreach($turnos as $turno){

            db_query(1, "SELECT fechaInicio FROM turnos WHERE idTurno='{$turno}' LIMIT 1");

            if(date("Y-m-d",strtotime($row1['fechaInicio']))==date("Y-m-d")){
                $string = "Hoy ".date("H:i",strtotime($row1['fechaInicio']));
            }else{
                $string = date("d/m/Y H:i",strtotime($row1['fechaInicio'])); 
            }

            if(@$linkReunion[$turno]){
                $string.=': <a href="'.$linkReunion[$turno].'">Acceder al Meet</a>';
            }

            $fechas[] = $string;
        }
        
        // Seteo la zona horaria a la anterior
        date_default_timezone_set($zonaHorariaActual);

        if($general['mailFirmaProfesional']){
            //Busco el nombre del profesional
            db_query(1,"select pro.nombre from profesionales pro, ordenes o where o.idProfesional=pro.idProfesional and o.idOrden='".$row['idOrden']."' limit 1");
            $firma=$row1['nombre'];
        } else {
            //Sino tomo el de la empresa
            $firma=$general['nombreCliente'];
        }

        $subtituloMail = "Hola {$nombreCompletoPaciente}, le informamos que su {$general['nombreTurno']} de la fecha <b>{$fechaAnterior}</b>, fue reagendada para el día: <b> ";

        if(@sizeof($fechas)==1){
            $subtituloMail.=$fechas[0];
            $nombreTurno='el';
            $nombreTurnoCancel='turno';
        }else{
            foreach($fechas as $fecha){
                $subtituloMail.='<li>'.$fecha.'</li>';
                $nombreTurno='algún';
                $nombreTurnoCancel='turnos';
            }
        }
        $subtituloMail .= "</b> <br><br>Motivo: {$motivo}";
        
        // Busco la imagen del profesional
        db_query(5, "SELECT imageName, nombre, dni FROM profesionales WHERE idProfesional = '{$idProfesional}' LIMIT 1");
        $imageProfesional = "<p>Te atenderás con el profesiona {$row5["nombre"]} con DNI {$row5['dni']}</p><img src='https://{$general['clientDomain']}/uploads/profesionales/{$row5['imageName']}' style='width: 150px; margin-bottom: 10px'/>";
        $subtituloMail.='<br><br>'.$imageProfesional."<br><br>".$general['leyendaMail'];


        $textoFooter = 'En caso de que desees reagendar '.$nombreTurno.' turno, podés hacerlo hasta '.$general['hsAntesCancelacion'].'hs. antes del mismo.';
        if($general['app_lagos_mail_reagendado_texto_footer']){
            $textoFooter = $general['app_lagos_mail_reagendado_texto_footer'];
        }
        $subtituloMail.='<br><br>'.$textoFooter;
        
        $subtituloMail.='<br><br><br><br><a href="https://'.$general['clientDomain'].'/cancel?t='.base64_encode($idTurno).'" style="border:1px solid #'.$general['colorPrimario'].';background-color:#'.$general['colorPrimarioHover'].';padding:15px 30px;margin:20px 0; color:#ffffff;font-size:16px;font-weight:bold;text-decoration:none" type="button">Reagendar '.$nombreTurnoCancel.'</a><br><br><br><br><h2>'.$firma.'</h2><i>Te esperamos.</i>';
        $alineacionSubtituloMail='left';
        $textoPie='<small>Este aviso se envía de forma automática y no se responden consultas por este medio.</small>';
        $productos='';

        ob_start();
        include(fn."/res/mailTemplate.php");
        $mensaje=ob_get_contents();
        ob_end_clean();
        
        //Util::printVar($mensaje);

        // Enviar Mail
        if(Util::is_valid_email($mail)){

            // Envio recordatorio a varios mails
            if($general['usaMailsAlternativos']){
                $mails = [$row['mail']];

                db_query(8,"SELECT * FROM pacientes WHERE idPaciente = {$idPaciente} LIMIT 1");

                // Mail alternativo 1
                if($row8['mailAlternativo1'])
                    $mails[] = $row8['mailAlternativo1'];

                // Mail alternativo 2
                if($row8['mailAlternativo2'])
                    $mails[] = $row8['mailAlternativo2'];

                $mail = implode(",",$mails);
            }


            // Valido que sea el primer turno del paciente
            db_query(3, "SELECT t.idTurno FROM turnos t, pacientes p WHERE t.idPaciente = p.idPaciente AND p.idPaciente = '{$idPaciente}' ");
            $isPrimerTurnoDelPaciente = false;
            $enviarArchivo = false;


            $error = self::sendEmail($general['nombreCliente'], $mail, $mensaje, $general['clientDomain'], "Confirmación de turno", $enviarArchivo, $isPrimerTurnoDelPaciente);
        }

    }
    /* End App Lagos */
    
    
    public static function recordatorio($fechaBusqueda='', $estado=0, $usuarioCancela=false) {

        //$fechaBusqueda=YYYY-mm-dd
        GLOBAL $res, $tot, $row, $general, $row1, $row2, $row3;
        GLOBAL $row11, $tot11;

        if(!$usuarioCancela){
            $usuarioCancela=$general['usuarioCancela'];
        }

        $fechaBusquedaPuntual = $fechaBusqueda;

        if(!$fechaBusqueda) $fechaBusqueda=date("Y-m-d",strtotime("+1 day"));
        $fechaBusqueda = " date(t.fechaInicio)='{$fechaBusqueda}' AND ";

        if($general['cron_mailRecordatorio_diasAnticipacion']){
            $fechasAnticipacion = array();
            $diasAnticipacion = explode(",",$general['cron_mailRecordatorio_diasAnticipacion']);
            foreach ($diasAnticipacion as $diaAnticipacion) {
                $fechasAnticipacion[] = '"'. date('Y-m-d', strtotime("+ {$diaAnticipacion} days")). '"';
            }
            $fechasAnticipacion = implode(",", $fechasAnticipacion);
            $fechaBusqueda = "DATE(t.fechaInicio) IN ({$fechasAnticipacion}) AND ";
        }

        $consulta="SELECT 
            p.nombre, 
            p.apellido, 
            p.mail, 
            p.codArea, 
            p.telefono, 
            p.observaciones, 
            t.fechaInicio, 
            t.idOrden, 
            t.idTurno, 
            t.fechaInicio,
            o.idProfesional,
            o.idTratamiento
        FROM 
            turnos t, 
            ordenes o, 
            pacientes p 
        WHERE 
            t.idOrden = o.idOrden AND 
            p.idPaciente = t.idPaciente AND 
            t.estado={$estado} AND 
            {$fechaBusqueda} 
            t.eliminado<>1
        ";

        db_query(0, $consulta);
        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();  

            /* Util::printVar($row, "190.31.193.129", false); */
            // Chequeo si el servicio puede mandar el mail de recordatorio
            if($general['tratamiento_enviarMailRecordatorio'] && Migration::existColumn('tratamientos','enviarMailRecordatorio')){
                // Si la query no tiene resultados es porque no puede enviar el mail
                if(!db_getOne("SELECT enviarMailRecordatorio FROM tratamientos WHERE idTratamiento = {$row['idTratamiento']} AND enviarMailRecordatorio = '1'")) continue;
            }

            $iconoExito=0;
            $tablaProductosMail=0;
            $tituloMail='<h1><img src="https://turnos.app/assets/img/sistema/calendar.png" /> </h1><h1>Recordatorio de '.$general["nombreTurno"].'!</h1>';
            $alineacionTituloMail='center';
            $idProfesional = $row["idProfesional"];
            $idTratamiento = $row["idTratamiento"];
            $mail = $row['mail'];

            // Zona horaria actual
            $zonaHorariaActual = date_default_timezone_get();

            if(isset($general['husosDinamicos']) && $general['husosDinamicos']){
                $date = new DateTime($row['fechaInicio'], new DateTimeZone($zonaHorariaActual)); 
                $date->setTimeZone(new DateTimeZone($row['observaciones']));
                $fecha = date("Y-m-d",strtotime($row['fechaInicio']))==date("Y-m-d") ? "Hoy ". $date->format('H:i') : $date->format('d/m/Y H:i');
            }else{
                if(date("Y-m-d",strtotime($row['fechaInicio']))==date("Y-m-d")) $fecha="Hoy ".date("H:i",strtotime($row['fechaInicio']));
                else $fecha=date("d/m/Y H:i",strtotime($row['fechaInicio'])); 
            }

            // Seteo la zona horaria a la anterior
            date_default_timezone_set($zonaHorariaActual);

            if($general['mailFirmaProfesional']){
                //Busco el nombre del profesional
                db_query(1,
                    "SELECT 
                        pro.nombre, 
                        t.textoPre, 
                        t.textoPost,
                        t.idTratamiento 
                    FROM 
                        profesionales pro, 
                        ordenes o, 
                        tratamiento t
                    WHERE 
                        o.idTratamiento = t.idTratamiento AND 
                        o.idProfesional = pro.idProfesional AND 
                        o.idOrden='".$row['idOrden']."' 
                    LIMIT 1
                ");

                $firma=$row1['nombre'];
            } else {
                //Sino tomo el de la empresa
                $firma=$general['nombreCliente'];
            }

            // Verifico si tiene un texto personalizado
            db_query(2,
                "SELECT nombre as tratamiento, t.textoPre, t.textoPost, o.idTratamiento
                FROM ordenes o, tratamientos t
                WHERE t.idTratamiento = o.idTratamiento AND o.idOrden = '{$row['idOrden']}'
                LIMIT 1 
            ");

            if($general["mail_recordatorio_quitarEstilos_textosTratamientos"]){
                $textoPre = $general['mailTextoArriba'];
                if($row2['textoPre']){
                    $textoPre = preg_replace("/<p[^>]*?>/", "", $row2['textoPre']);
                    $textoPre = strip_tags($textoPre);
                }
                
                $textoPost = $general['leyendaMail'];
                if($row2['textoPost']){
                    $textoPost = preg_replace("/<p[^>]*?>/", "", $row2['textoPost']);
                    $textoPost = strip_tags($textoPost, "<br></br>");
                }
            }else{
                $textoPre = $row2['textoPre'] ? ucfirst($row2['textoPre']) : $general['mailTextoArriba'];
                $textoPost = $row2['textoPost'] ? ucfirst($row2['textoPost']) : $general['leyendaMail'];
            }

            $subtituloMail=str_replace('%nombre%', $row['nombre'].' '.$row['apellido'], $textoPre).' para '.$row2['tratamiento'].':<b><span style="color:#000;">'.$fecha.'</span></b><br><br>';
            
            $subtituloMail .= $textoPost;
            
            if($general['zoom_enviarLinkDeReunionEnElMailDeRecordatorio']){
                $idTurno = 1;
                $fechaInicio = $row['fechaInicio'];
                $idProfesional = $row['idProfesional'];
                //Voy a ver si ya existe un link para este profesional y este tratamiento a esta hora, si no lo creo
                db_query(3, "select link from turnos_meetings where idProfesional='{$idProfesional}' and idTratamiento='{$idTratamiento}' and fechaInicio='{$fechaInicio}' limit 1");
                $subtituloMail .= '<br><br>'. str_replace("%link%", $row3['link'], $general['zoom_enviarLinkDeReunionEnElMailDeRecordatorio_texto']);
            }

            if($usuarioCancela){
                $subtituloMail.='<br><br>En caso de que desees cancelar el turno, podés hacerlo hasta '.$general['hsAntesCancelacion'].'hs. antes del mismo.<br><br><br><br><a href="https://'.$general['clientDomain'].'/cancel?t='.base64_encode($row['idTurno']).'" style="border:1px solid #'.$general['colorPrimario'].';background-color:#'.$general['colorPrimarioHover'].';padding:15px 30px;margin:20px 0; color:#ffffff;font-size:16px;font-weight:bold;text-decoration:none" type="button">Cancelar '.$general["nombreTurno"].'</a>';
            }
            
            $subtituloMail.=$general["mail_recordatorio_textoAdicionalFooter"];

            $subtituloMail.='<br><br><br><br><i>Muchas gracias.</i><h2>'.$firma.'</h2>';
            $alineacionSubtituloMail='left';
            $textoPie='<small>Este aviso se envía de forma automática y no se responden consultas por este medio.</small>';
            $productos='';

            ob_start();
            include(fn."/res/mailTemplate.php");
            $mensaje=ob_get_contents();
            ob_end_clean();

            /* Util::printVar($mensaje, "138.121.84.107"); */

            // Valido si el tratamiento puede enviar mails
            $puedeEnviarMail = true;
            if($general['tratamiento_enviarMailRecordatorio'] && Migration::existColumn('tratamientos','enviarMailRecordatorio')){
                db_query(11, "SELECT enviarMailRecordatorio FROM tratamientos WHERE idTratamiento = '{$idTratamiento}' LIMIT 1");
                if($row11['enviarMailRecordatorio'] != '1'){
                    $puedeEnviarMail = false;
                }                
            }

            // Recordatorio Email
            if(Util::is_valid_email($mail) && $puedeEnviarMail){

                if($general["mail_recordatorio_copiarAlProfesional"]){
                    $dataProfesional = db_getOne("SELECT * FROM profesionales WHERE idProfesional = {$idProfesional} ");
                    if(Util::is_valid_email($dataProfesional->email)){
                        $mail = array($mail, $dataProfesional->email);
                    }
                }

                $error = self::sendEmail($general['nombreCliente'], $mail, $mensaje, $general['clientDomain'], "Recordatorio de {$general['nombreTurno']}");
		
            }

        }
        
    }
    
    
    public static function turnosDelDia($fechaBusqueda='', $estado=0) {

        //$fechaBusqueda=YYYY-mm-dd

        global $res, $tot, $row, $general;

        if(!$fechaBusqueda) $fechaBusqueda=date("Y-m-d");

        db_query(0,"select p.nombre, p.idProfesional, p.email from profesionales p, horariosprofesionales hp where p.idProfesional=hp.idProfesional and hp.dia='".strtolower($diaSemana[date('l',strtotime($fechaBusqueda))])."' and date(hp.fechaAlta)<='".date("Y-m-d H:i:s")."' and ((hp.desdeManana<>'' and hp.hastaManana<>'') or (hp.desdeTarde<>'' and hp.hastaTarde<>'')) group by hp.idProfesional order by p.nombre ASC, hp.fechaAlta DESC");

        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();

            $iconoExito=0;
            $tablaProductosMail=0;
            $tituloMail='<h1><img src="https://turnos.app/assets/img/sistema/calendar.png" /> </h1><h1>Turnos del día</h1>';
            $alineacionTituloMail='center';

            $subtituloMail='Buen día <b>'.$row['nombre'].'</b>, te recordamos los turnos del día:<br><br><ul>';

            //Busco los turnos del profesional
            db_query(1,"select p.nombre, p.apellido, t.fechaInicio, tra.nombre as tratamiento from turnos t, tratamientos tra, ordenes o, pacientes p where p.idPaciente=t.idPaciente and o.idOrden=t.idOrden and t.estado=0 and date(t.fechaInicio)='".$fechaBusqueda."' and o.idProfesional='".$row['idProfesional']."' and o.idTratamiento=tra.idTratamiento");
            for($i1=0;$i1<$tot1;$i1++){
                $nres1=$res1->data_seek($i1);
                $row1=$res1->fetch_assoc();
    
                $subtituloMail.='<li><b>'.date("H:i",strtotime($row1['fechaInicio'])).'</b> '.$row1['nombre'].' '.$row1['apellido'].' - '.$row1['tratamiento'].'</li>';
    
            }

            $subtituloMail.='</ul><br><br><i>Saludos!</i><h2>'.$general['nombreCliente'].'</h2>';

            $alineacionSubtituloMail='left';

            $textoPie='<small>Este aviso se envía de forma automática y no se responden consultas por este medio.</small>';

            $productos='';

            if($tot1>0){

                ob_start();
                include(fn."/res/mailTemplate.php");
                $mensaje=ob_get_contents();
                ob_end_clean();
		
                $error = self::sendEmail($general['nombreCliente'], $row['email'], $mensaje, $general['clientDomain'], "Turnos del día");
		
                /*if($error) echo "<br>".$error;
                else echo "<br>Envio OK";*/
            }
        }
    }
    
    public static function mailCancelacion($idTurno){
        GLOBAL $row, $tot, $res, $general;
        GLOBAL $row1, $tot1, $res1;
        db_query(0,
            "SELECT 
                p.nombre, 
                p.apellido, 
                p.mail, 
                p.codArea, 
                p.telefono, 
                t.fechaInicio, 
                t.idOrden, 
                t.idTurno,
                o.idProfesional
            FROM 
                turnos t, 
                ordenes o,
                pacientes p 
            WHERE 
                t.idOrden = o.idOrden AND
                p.idPaciente = t.idPaciente AND 
                t.estado = 3 AND 
                t.idTurno='".$idTurno."'
        ");

        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();

            if(Util::is_valid_email($row['mail'])){
                $iconoExito=0;
                $tablaProductosMail=0;
                $tituloMail='<h1><img src="https://turnos.app/assets/img/sistema/cancel.png" /> </h1><h1>Turno cancelado!</h1>';
                $alineacionTituloMail='center';
                $idProfesional = $row["idProfesional"];

                if(date("Y-m-d",strtotime($row['fechaInicio']))==date("Y-m-d")) $fecha="Hoy ".date("H:i",strtotime($row['fechaInicio']));
                else $fecha=date("d/m/Y H:i",strtotime($row['fechaInicio'])); 

                if($general['mailFirmaProfesional']){
                    //Busco el nombre del profesional
                    db_query(1,"select pro.nombre from profesionales pro, ordenes o where o.idProfesional=pro.idProfesional and o.idOrden='".$row['idOrden']."' limit 1");
                    $firma=$row1['nombre'];
                } else {
                    //Sino tomo el de la empresa
                    $firma=$general['nombreCliente'];
                }

                $subtituloMail='Hola '.$row['nombre'].' '.$row['apellido'].', queremos informarte que tu turno ha sido <b>CANCELADO</b><br><br>El turno cancelado es:<br><br><b><span style="color:#000;font-size:16px">'.$fecha.'</span></b>';
                
                if(trim($_POST['comentarios'])){
                    $subtituloMail.='<br><br>El motivo de la cancelación es:<br><br><span style="color:#000;font-size:16px">'.$_POST['comentarios'].'</span>';
                }

                $subtituloMail.='<br><br><br><br><i>Muchas gracias.</i><h2>'.$firma.'</h2>';
                $alineacionSubtituloMail='left';
                $textoPie='<small>Este aviso se envía de forma automática y no se responden consultas por este medio.</small>';
                $productos='';

                ob_start();
                include(fn."/res/mailTemplate.php");
                $mensaje=ob_get_contents();
                ob_end_clean();
                
                if($general['mailCliente']){
                    $error = self::sendEmail($general['nombreCliente'], $general['mailCliente'], $mensaje, $general['clientDomain'], "Cancelación de turno");
                }else{
                    $mail = $row["mail"];

                    // Copio al profesional
                    if($general["mail_cancelacion_copiarAlProfesional"]){
                        $dataProfesional = db_getOne("SELECT * FROM profesionales WHERE idProfesional = {$idProfesional} ");
                        if(Util::is_valid_email($dataProfesional->email)){
                            $mail = array($mail, $dataProfesional->email);
                        }
                    }

                    $error = self::sendEmail($general['nombreCliente'], $mail, $mensaje, $general['clientDomain'], "Cancelación de turno");
                }

                if($error) echo "<br>".$error;
            }

        }
    }

    public static function mailCancelacionAlAdmin($idTurno){
        GLOBAL $row, $tot, $res, $general;
        GLOBAL $row1, $tot1, $res1;

        db_query(0,
            "SELECT 
                p.nombre, 
                p.apellido, 
                p.mail, 
                p.codArea, 
                p.telefono, 
                t.fechaInicio, 
                t.idOrden, 
                t.idTurno 
            FROM 
                turnos t, 
                pacientes p 
            WHERE 
                p.idPaciente = t.idPaciente AND 
                t.idTurno='".$idTurno."'
        ");

        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();
            if(Util::is_valid_email($row['mail'])){
                $iconoExito=0;
                $tablaProductosMail=0;
                $tituloMail='<h1><img src="https://turnos.app/assets/img/sistema/cancel.png" /> '."</h1><h1>Cancelación de {$general['nombreTurno']} del {$general['nombrePaciente']}!</h1>";
                $alineacionTituloMail='center';

                if(date("Y-m-d",strtotime($row['fechaInicio']))==date("Y-m-d")) $fecha="de hoy a las ".date("H:i",strtotime($row['fechaInicio']));
                else $fecha=date("d/m/Y H:i",strtotime($row['fechaInicio'])); 

                $subtituloMail='Queremos informarte que el '. $general["nombrePaciente"]." ".ucfirst($row['nombre']).' '.ucfirst($row['apellido']).' ha <b>CANCELADO</b> el '.$general["nombreTurno"].' para el día <b><span style="color:#000;font-size:16px">'.$fecha.'</span></b>';

                $subtituloMail.='<br><br><br><br><i>Muchas gracias.</i>';
                $alineacionSubtituloMail='left';
                $textoPie='<small>Este aviso se envía de forma automática y no se responden consultas por este medio.</small>';
                $productos='';

                ob_start();
                include(fn."/res/mailTemplate.php");
                $mensaje=ob_get_contents();
                ob_end_clean();
                
                $error = self::sendEmail($general['nombreCliente'], $general['mailAdmin'], $mensaje, $general['clientDomain'], "Cancelación de {$general['nombreTurno']} del {$general['nombrePaciente']}");
            }

        }
    }

    public static function recordatorio_personalizado() {
        GLOBAL $general;
        
        $fechaBusqueda=date("Y-m-d",strtotime("+ {$general['diasRecordatorio']} day"));
        $fechaBusqueda = " date(t.fechaInicio)='{$fechaBusqueda}' AND ";

        $query="SELECT 
                p.nombre, 
                p.apellido, 
                p.mail, 
                p.codArea, 
                p.telefono, 
                t.fechaInicio, 
                t.idOrden, 
                t.idTurno, 
                t.fechaInicio,
                o.idProfesional,
                o.idTratamiento,
                prof.nombre as profesional,
                trat.nombre as tratamiento
            FROM 
                turnos t, 
                ordenes o, 
                pacientes p,
                profesionales prof,
                tratamientos trat
            WHERE 
                t.idOrden = o.idOrden AND 
                p.idPaciente = t.idPaciente AND 
                o.idProfesional = prof.idProfesional AND 
                o.idTratamiento = trat.idTratamiento AND 
                t.estado = '0' AND 
                {$fechaBusqueda} 
                t.eliminado<>1 
        ";
        
        $turnos = db_getAll($query);
        /* Util::printVar($turnos, "138.121.84.107"); */
        foreach ($turnos as $turno) {

            // Chequeo si el servicio puede mandar el mail de recordatorio
            if($general['tratamiento_enviarMailRecordatorio'] && Migration::existColumn('tratamientos','enviarMailRecordatorio')){
                // Si la query no tiene resultados es porque no puede enviar el mail
                if(!db_getOne("SELECT enviarMailRecordatorio FROM tratamientos WHERE idTratamiento = {$turno->idTratamiento} AND enviarMailRecordatorio = '1'")) continue;
            }

            $iconoExito=0;
            $tablaProductosMail=0;
            $tituloMail='<h1><img src="https://turnos.app/assets/img/sistema/calendar.png" /> </h1><h1>Recordatorio de '.$general["nombreTurno"].'!</h1>';
            $alineacionTituloMail='center';

            /* Body message */ 
            $fecha = date("d/m/Y", strtotime($turno->fechaInicio));
            $hora = date("H:i", strtotime($turno->fechaInicio));
            $idTurnoEncode = base64_encode($turno->idTurno);
            $buttonCancelacion = "<a href='https://{$general['clientDomain']}/cancel?t={$idTurnoEncode}' style='border:1px solid #{$general['colorPrimario']};background-color:#{$general['colorPrimarioHover']};padding:15px 30px;margin:20px 0; color:#ffffff;font-size:16px;font-weight:bold;text-decoration:none' type='button'>Cancelar turno</a>";

            $replace = array(
                ucfirst($turno->nombre)." ".ucfirst($turno->apellido),
                $turno->tratamiento,
                DateController::daysToDias(date("l", strtotime($turno->fechaInicio))),
                date("d/m/Y", strtotime($turno->fechaInicio)),
                date("H:i", strtotime($turno->fechaInicio)),
                $buttonCancelacion,
                $general["hsAntesCancelacion"],
                $general["nombreTurno"],
                $general["nombreCliente"],

            );
            $search = array(
                "%nombrePaciente%",
                "%servicio%",
                "%nombreDia%",
                "%fecha%",
                "%hora%",
                "%buttonCancelacion%",
                "%hsAntesCancelacion%",
                "%nombreTurno%",
                "%nombreCliente%",

            );
            $subtituloMail = str_replace($search, $replace, $general["mail_recordatorio_personalizado_body"]);

            $alineacionSubtituloMail='left';
            $textoPie='<small>Este aviso se envía de forma automática y no se responden consultas por este medio.</small>';
            $productos='';

            ob_start();
            include(fn."/res/mailTemplate.php");
            $mensaje=ob_get_contents();
            ob_end_clean();

            /* Util::printVar($mensaje, "138.121.84.107"); */

            // Recordatorio Email
            if(Util::is_valid_email($turno->mail)){
                $error = self::sendEmail($general['nombreCliente'], $turno->mail, $mensaje, $general['clientDomain'], "Recordatorio de turno");
            }
        }
    }

    public static function PHPMAILER_sendMail($nombreCliente, $mailCliente, $mensaje, $domain, $subject, $enviarArchivo = false, $isPrimerTurnoDelPaciente = false){
        
        $SMTP_SERVER = $general["PHPMAILER_SERVER"];
        $SMTP_PORT = $general["PHPMAILER_PORT"];
        $SMTP_SECURITY = $general["PHPMAILER_SECURITY"];
        $SMTP_USER = $general["PHPMAILER_USER"];
        $SMTP_PASSWORD = $general["PHPMAILER_PASSWORD"];
        $SMTP_FROM_MAIL = $general["PHPMAILER_FROM_MAIL"];

        // Testing
        /* $SMTP_SERVER = "mail.cuatrolados.com";
        $SMTP_PORT = 587;
        $SMTP_SECURITY = "tls";
        $SMTP_USER = "envios@cuatrolados.com";
        $SMTP_PASSWORD = "envios2019externos";
        $SMTP_FROM_MAIL = "contacto@ibpgroup.net"; */

        /* Util::printVar([$nombreCliente, $mailCliente, $mensaje, $domain, $subject, $SMTP_USER]); */
        
        try {
            $mail = new PHPMailer();
        
            //Server settings
            $mail->CharSet = "UTF-8";                                   //Set charset
            $mail->SMTPDebug = 0;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $SMTP_SERVER;                           //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $SMTP_USER;                             //SMTP username
            $mail->Password   = $SMTP_PASSWORD;                         //SMTP password
            $mail->SMTPSecure = $SMTP_SECURITY;                         //Enable implicit TLS encryption
            $mail->Port       = $SMTP_PORT;                             //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            //Recipients
            $mail->setFrom($SMTP_FROM_MAIL);
            $mail->addAddress($mailCliente, $nombreCliente);            //Add a recipient
        
            //Attachments
            /* $mail->addAttachment('/var/tmp/file.tar.gz');            //Add attachments
            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');          //Optional name */
        
            //Content
            $mail->isHTML(true);                                        //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $mensaje;
            
            $mail->send();
            return true;
        
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

}