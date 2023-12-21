<?php

class NotificationWhatsapp{

    public static function recordatorioWapp($nombre, $dia, $textoPost, $convert, $nombreProfesional = ''){
        global $general;
        $textoPost = $textoPost ? '<br>'.$textoPost .'. ': "";
        $nombreProfesional = $nombreProfesional ? ' con ' . $nombreProfesional : '';
        $ultimoTexto = str_replace(
            "%nombreCliente%",
            $general["nombreCliente"],
            $general["wappManual_textoFooter"]
        );
        $mensaje=str_replace('%nombre%',$nombre,strip_tags($general['mailTextoArriba'], '<br><b><i>')).' <b>'.$dia.$nombreProfesional.'</b>.<br>'.$general['leyendaWapp'].strip_tags($textoPost, '<br><b><i>').$ultimoTexto;
        
        $buscar=array(' ', '</br>', '<br>', '<b>', '</b>', '<i>', '</i>','<p>','</p>','<ul>','</ul>','<li>','</li>');
        $reemplazar=array('%20', '%0A', '%0A', '*', '*', '_', '_','','','','','+','');

        if($convert){
            $mensaje = str_replace($buscar,$reemplazar,$mensaje);
        }

        // $mensaje=urlencode($mensaje);
        
        return $mensaje;
    }
    
    public static function getRecordatorios(){
        global $tot;
        global $row;
        global $res;
        global $general;
        GLOBAL $row2, $tot2, $res2;

        // Si el campo tiene una H en el primer caracter usa un formato de horas
        $wappDays = $general['wappDays'];
        if($wappDays[0] == 'H'){
            // Agrega horas
            $hora=date("Y-m-d H:i",strtotime('+'.substr($wappDays,1).' hours'));
            $whereFecha="date_format(t.fechaInicio, '%Y-%m-%d %H:%i') <= '{$hora}' AND t.fechaInicio > '".date('Y-m-d H:i:s')."'";
        }else{
            $diasAnticipacionWhatsapp = $general['wappDays'];
            if($general['wappApi_diasAnticipacion']){
                $diasAnticipacionWhatsapp = explode(",", $general['wappApi_diasAnticipacion']);
                /* Util::printVar($diasAnticipacionWhatsapp, '138.121.84.107',false); */
                
                $fechas = array();
                foreach ($diasAnticipacionWhatsapp as $diaAnticipacion) {

                    // Si la hora actual no es igual a la personalizada no hago nada
                    if($general['wappApi_horaPersonalizada'] && date('H') != date("H", strtotime($general['wappApi_horaPersonalizada'])) ) continue;
                    
                    $fechaAnticipacion=date("Y-m-d",strtotime('+'.$diaAnticipacion.' days'));
                    $fechaAnticipacion2=date("Y-m-d",strtotime('+ '.($diaAnticipacion-1).' days'));
                    $fechas[] = "date(t.fechaInicio) = '{$fechaAnticipacion}'";
                    
                    // Si es el ultimo dia para enviar mensajes no envio mas mensajes
                    if($diaAnticipacion == $diasAnticipacionWhatsapp[count($diasAnticipacionWhatsapp)-1]) continue;
                    
                    // Elimino los registro de la tabla turnos_recordatorios para que el día siguiente envien otra vez el mensaje (depende de la variable wappApi_diasAnticipacion)
                    foreach (db_getAll("SELECT tr.idRecordatorio FROM turnos t, turnos_recordatorios tr WHERE t.idTurno = tr.idTurno AND date(t.fechaInicio) = '{$fechaAnticipacion2}' ") as $idRecordatorio) {
                        db_delete("DELETE FROM turnos_recordatorios WHERE idRecordatorio = '{$idRecordatorio}'");    
                    }
                }
                $fechas = count($fechas) == 0 ? "false AND " : "(".implode(" OR ", $fechas) . ") AND ";
                $whereFecha = $fechas." date(t.fechaInicio) >= CURDATE() ";
            }else{
                // Agrega dias
                $fecha=date("Y-m-d",strtotime('+'.$diasAnticipacionWhatsapp.' days'));
                $whereFecha="date(t.fechaInicio) <= '{$fecha}' AND date(t.fechaInicio) >= CURDATE() ";
            }

        }
        
        $recordatorios=array();

        $queryTurnosRecordatorios = "select tr.idRecordatorio, p.telefono, p.idPaciente, t.idTurno, t.fechaInicio, p.apellido, p.nombre from pacientes p, turnos t left join turnos_recordatorios tr on t.idTurno=tr.idTurno where {$whereFecha} and p.idPaciente=t.idPaciente and t.estado=0 and p.telefono<>'' and t.eliminado <> '1' group by t.idTurno order by t.fechaInicio asc";

        db_query(0, $queryTurnosRecordatorios);

        if($tot>0){
            for($i=0;$i<$tot;$i++){
                $nres=$res->data_seek($i);
                $row=$res->fetch_assoc();

                // if(@!$agregados[$row['idPaciente']]){
                    if($row['idRecordatorio']){
                        $enviado=1;
                    }else{
                        $enviado=0;
                    }
                
                    $recordatorios[]=array('nombre'=>$row['nombre'],'apellido'=>$row['apellido'],'enviado'=>$enviado,'turno'=>date("d/m/Y H:i",strtotime($row['fechaInicio'])),'idTurno'=>$row['idTurno']);
    
                    // $agregados[$row['idPaciente']]=1;
                // }
            }
            $response['recordatorios']=$recordatorios;
            $response['status']='OK';
        }else{
            $response['status']='NO';
        }
        
        return $response;
    }

    public static function getRecordatoriosPanel(){
        global $tot;
        global $row;
        global $res;
        global $general;
        GLOBAL $row2, $tot2, $res2;

        /* 
            date("w") // number [0,1,2,3,4,5,6] 0 = domingo
        */
        // Si el campo tiene una H en el primer caracter usa un formato de horas
        $wappDays = $general['wappDays'];

        if($wappDays[0] == 'H'){
            // Agrega horas
            $hora=date("Y-m-d H:i",strtotime('+'.substr($wappDays,1).' hours'));

            if( strpos($general["diasOcultos"], date("w", strtotime($hora))) ){
                $hora = date("Y-m-d", strtotime($hora." + 1 days"));
            }
            $whereFecha="date_format(t.fechaInicio, '%Y-%m-%d %H:%i') <= '{$hora}' AND t.fechaInicio > '".date('Y-m-d H:i:s')."'";
        }else{
            // Agrega dias
            $fecha=date("Y-m-d",strtotime('+'.$wappDays.' days'));

            if( strpos($general["diasOcultos"], date("w", strtotime($fecha))) ){
                $fecha = date("Y-m-d", strtotime($fecha." + 1 days"));
            }
            $whereFecha="date(t.fechaInicio) <= '{$fecha}' AND date(t.fechaInicio) >= CURDATE() ";
        }

        
        $queryTurnosRecordatorios = "select tr.idRecordatorio, p.telefono, p.idPaciente, t.idTurno, t.fechaInicio, p.apellido, p.nombre from pacientes p, turnos t left join turnos_recordatorios tr on t.idTurno=tr.idTurno where {$whereFecha} and p.idPaciente=t.idPaciente and t.estado=0 and p.telefono<>'' and t.eliminado <> '1' GROUP BY t.idTurno order by t.fechaInicio asc";

        // Ver recordatorios de una fecha puntual
        /* if($_SERVER["REMOTE_ADDR"] == "138.121.84.107"){
            $queryTurnosRecordatorios = "select tr.idRecordatorio, p.telefono, p.idPaciente, t.idTurno, t.fechaInicio, p.apellido, p.nombre from pacientes p, turnos t left join turnos_recordatorios tr on t.idTurno=tr.idTurno where date(t.fechaInicio) = '2023-05-15' and p.idPaciente=t.idPaciente and t.estado=0 and p.telefono<>'' and t.eliminado <> '1' GROUP BY t.idTurno order by t.fechaInicio asc";
        } */
        /* Util::printVar($queryTurnosRecordatorios, "138.121.84.107", true); */

        
        $recordatorios=array();
        db_query(0, $queryTurnosRecordatorios);
        if($tot>0){
            for($i=0;$i<$tot;$i++){
                $nres=$res->data_seek($i);
                $row=$res->fetch_assoc();

                // if(@!$agregados[$row['idPaciente']]){
                    if($row['idRecordatorio']){
                        $enviado=1;
                    }else{
                        $enviado=0;
                    }
                
                    $recordatorios[]=array('nombre'=>$row['nombre'],'apellido'=>$row['apellido'],'enviado'=>$enviado,'turno'=>date("d/m/Y H:i",strtotime($row['fechaInicio'])),'idTurno'=>$row['idTurno']);
    
                    // $agregados[$row['idPaciente']]=1;
                // }
            }
            $response['recordatorios']=$recordatorios;
            $response['status']='OK';
        }else{
            $response['status']='NO';
        }
        
        return $response;
    }
    
    public static function getCancelacionManual(){
        GLOBAL $tot1, $row1, $res1, $nres1;
        $recordatorios=array();
        
        db_query(1,"SELECT tc.*, t.fechaInicio, p.nombre, p.apellido FROM turnos_cancelados tc, turnos t, pacientes p WHERE t.idTurno = tc.idTurno AND t.idPaciente = p.idPaciente AND enviado <> '1' ");
        if($tot1>0){
            for($i=0;$i<$tot1;$i++){
                $nres1=$res1->data_seek($i);
                $row1=$res1->fetch_assoc();

                $recordatorios[]=array(
                    'nombre'=>$row1['nombre'],
                    'apellido'=>$row1['apellido'],
                    'enviado'=>$row1['enviado'],
                    'turno'=>date("d/m/Y H:i",strtotime($row1['fechaInicio'])),
                    'idTurno'=>$row1['idTurno']
                );
            }
            $response['recordatorios']=$recordatorios;
            $response['status']='OK';
        }else{
            $response['status']='NO';
        }
        
        return $response;
    }

    public static function getWappLink($id){
        global $general, $row;
    
        db_query(0,
            "SELECT 
                t.fechaInicio, 
                p.nombre, 
                p.codArea, 
                p.telefono,
                trat.textoPost,
                trat.nombre as tratamiento,
                prof.nombre as profesional
            FROM 
                pacientes p, 
                turnos t, 
                ordenes o,
                tratamientos trat,
                profesionales prof
            WHERE 
                t.idOrden = o.idOrden AND 
                o.idTratamiento = trat.idTratamiento AND
                p.idPaciente = t.idPaciente AND 
                o.idProfesional = prof.idProfesional AND 
                t.idTurno='".$id."' 
            LIMIT 1
        ");
    
        $telefono=$general['prefijoTelefonico'].$row['codArea'].$row['telefono'];
        $textoPost = $general['textosTratamientos'] ? $row['textoPost'] : '';

        if(trim($telefono)){
    
            if(date('Y-m-d',strtotime($row['fechaInicio']))==date('Y-m-d')){
                $dia='Hoy';
            }else{
                $dia = DateController::daysToDias(date('l',strtotime($row['fechaInicio'])));
            }
    
            $dia=$dia.' '.date("d/m/Y H:i",strtotime($row['fechaInicio']));
    
            $mensaje=self::recordatorioWapp($row['nombre'], $dia, $textoPost, true);

            // Mensaje personalizado
            if($general["whatsapp_mensajePersonalizado"]){
                $mensaje = str_replace(
                    ["%nombre%", "%fecha%", "%hora%", "%tratamiento%","%profesional%"],
                    [ucfirst($row["nombre"]), date("d/m/Y", strtotime($row["fechaInicio"])), date("H:i", strtotime($row["fechaInicio"])), ucfirst($row["tratamiento"]), ucfirst($row["profesional"]) ],
                    $general["whatsapp_mensajePersonalizado"]
                );
                $buscar=array(' ', '</br>', '<br>', '<b>', '</b>', '<i>', '</i>','<p>','</p>','<ul>','</ul>','<li>','</li>');
                $reemplazar=array('%20', '%0A', '%0A', '*', '*', '_', '_','','','','','+','');
                $mensaje = str_replace($buscar, $reemplazar, $mensaje);
            }

        
            $response['link']='https://wa.me/'.$telefono.'?text='.$mensaje;
        
            db_insert("insert into turnos_recordatorios (idTurno) values ('".$id."')");
        
            $response['status']='OK';
        }else{
            $response['status']='telefono';
        }
        
        return $response;
    
    }

    public static function getWappLinkCancelacion($id){
        GLOBAL $row1;
    
        db_query(1,
            "SELECT 
                *
            FROM 
                turnos_cancelados
            WHERE 
                idTurno = {$id} AND 
                enviado <> '1'
            LIMIT 1
        ");
        $telefono = $row1["telefono"];
        $mensaje = $row1["mensaje"];
        $idTurnoCancelado = $row1["idTurnoCancelado"];
        if(trim($telefono)){
    
            $response['link']='https://wa.me/+'.$telefono.'?text='.$mensaje;
            $response['status']='OK';
            $varsTurnoCancelados['tabla'] = 'turnos_cancelados';
            $varsTurnoCancelados['idLabel'] = 'idTurnoCancelado';
            $varsTurnoCancelados['id'] = $idTurnoCancelado;
            $varsTurnoCancelados['enviado'] = '1';
            $varsTurnoCancelados['accion'] = 'Mensaje de cancelacion de whatsapp enviado';
            db_edit($varsTurnoCancelados);
        }else{
            $response['status']='telefono';
        }
        
        return $response;
    
    }
    
    public static function getMensajeCancelacionManual(){
        return "getMensajeCancelacionManual";
    }

    public static function confirmacion($idTurno){
        GLOBAL $general;

        $dataTurno = db_getOne(
            "SELECT 
                t.*,
                prof.nombre as profesional,
                trat.nombre as tratamiento,
                trat.textoPre,
                trat.textoPost, 
                p.*

            FROM 
                turnos t,
                ordenes o,
                profesionales prof,
                tratamientos trat,
                pacientes p
            WHERE   
                t.idOrden = o.idOrden AND 
                t.idPaciente = p.idPaciente AND 
                t.idTurno = {$idTurno} AND 
                o.idProfesional = prof.idProfesional AND 
                o.idTratamiento = trat.idTratamiento 
            "
        );
        $nombreCompleto = ucfirst($dataTurno->nombre)." ". ucfirst($dataTurno->apellido);
        $telefono = ($dataTurno->codPais ?? $general['prefijoTelefonico']) . '9' . $dataTurno->codArea . $dataTurno->telefono;
        
        $fechaInicio = date("d/m/Y", strtotime($dataTurno->fechaInicio));
        $horaInicio = date("H:i", strtotime($dataTurno->fechaInicio));

        $textoPre = "";
        $textoPost = "";

        /* Util::printVar([$dataTurno->textoPre, $dataTurno->textoPost], "190.31.193.129"); */
        
        if($dataTurno->textoPre){
            $textoPre = $dataTurno->textoPre;
            $textoPre = str_replace(["<li>","</li>", "<u>", "</u>"], ["+ ","<br>","<i>","</i>"], $textoPre);
            $textoPre = preg_replace("/<p[^>]*?>/", "", $textoPre);
            $textoPre = str_replace("</p>", "<br>", $textoPre);
            $textoPre = "<br><br>".strip_tags($textoPre, "<br><i></i>");
        }

        if($dataTurno->textoPost){
            $textoPost = "<br>".$dataTurno->textoPost;
            $textoPost = str_replace(["<li>","</li>", "<u>", "</u>"], ["+ ","<br>","<i>","</i>"], $textoPost);
            $textoPost = preg_replace("/<p[^>]*?>/", "", $textoPost);
            $textoPost = str_replace("</p>", "<br>", $textoPost);
            $textoPost = strip_tags($textoPost, "<br><i></i>")."<br>";
        }

        /* $message = str_replace("%nombre%", $nombreCompleto, $general["mailTextoArribaConfirmacion"]). " para {$dataTurno->tratamiento} con el {$general['nombreProfesional']} {$dataTurno->profesional}. {$textoPre}<br><br>Fecha: {$fechaInicio} a las {$horaInicio} hs.<br><br>{$textoPost}"; */
        $message = str_replace("%nombre%", $nombreCompleto, $general["mailTextoArribaConfirmacion"]). " para {$dataTurno->tratamiento}. {$textoPre}<br><br>Fecha: {$fechaInicio} a las {$horaInicio} hs.<br><br>{$textoPost}";
        $message.=$general["leyendaMail"]."<br><br>";
        $message.=$general["nombreCliente"]."<br><br><br>Este mensaje se envía automáticamente, no responder.";

        /* Util::printVar($message, "190.231.245.234", true); */

        // Envio el mensaje
        if(self::setMensajeAuto($telefono, $message)){
            // Guardo en el log
            db_insert("INSERT INTO log (usuario, accion, id, fechahora) VALUES ('app','whatsappAPI_confirmacion', {$idTurno}, '".date("Y-m-d H:i:s")."' )");
        }
    }


    /* 
        ################## 
        #
        #   WHATSAPP API   
        #
        ################## 
    */
    public static function generarRecordatoriosAuto(){

        global $general, $row, $tot;

        $recordatorios = self::getRecordatorios();

        // Si no hay recordatorios no hago nada
        if($recordatorios['status'] == 'NO') return;
        
        /* Util::printVar($recordatorios,"190.31.193.129", false); */

        //Armo un vector de comparacion. Si quiere que se envíe solo el primero chequeo el vector y si ya está el paciente lo salteo.
        $agregados=array();
        
        foreach($recordatorios['recordatorios'] as $recordatorio){
            
            db_query(0, "select idRecordatorio from turnos_recordatorios where idTurno = '".$recordatorio['idTurno']."'");
            if($tot<1){
                db_query(0,
                    "SELECT 
                        t.idTurno,
                        t.fechaInicio, 
                        p.idPaciente, 
                        p.nombre, 
                        p.apellido, 
                        p.codArea, 
                        p.telefono,
                        trat.textoPost 
                    FROM 
                        pacientes p, 
                        turnos t, 
                        ordenes o,
                        tratamientos trat
                    WHERE 
                        t.idOrden = o.idOrden AND 
                        o.idTratamiento = trat.idTratamiento AND
                        p.idPaciente = t.idPaciente AND 
                        t.idTurno='".$recordatorio['idTurno']."' 
                    LIMIT 1
                ");
                
                $telefono=$general['prefijoTelefonico'].'9'.$row['codArea'].$row['telefono'];

                $textoPost = $general['textosTratamientos'] ? $row['textoPost'] : '';


                if(trim($telefono)){

                    if(date('Y-m-d',strtotime($row['fechaInicio']))==date('Y-m-d')){
                        $dia='Hoy';
                    }else{
                        $dia = DateController::daysToDias(date('l',strtotime($row['fechaInicio'])));
                    }
            
                    $dia=$dia.' '.date("d/m/Y H:i",strtotime($row['fechaInicio']));

                    if($general['wappApi_mensajePersonalizado']){
                        $serach = [
                            "%nombre%",
                            "%fecha%",
                            "%link%",
                            "%nombreCliente%"
                        ];
                        $replace = [
                            ucfirst($row["nombre"]). " " . ucfirst($row["apellido"]),
                            $dia,
                            'https://'.$general['clientDomain'].'/cancel?t='.base64_encode($recordatorio['idTurno']),
                            $general["nombreCliente"]
                        ];
                        $mensaje = str_replace($serach, $replace, $general['wappApi_mensajePersonalizado']);
                    }else{
                        $mensaje=self::recordatorioWapp($row['nombre'], $dia, $textoPost, false);
                    }

                    /* Util::printVar($mensaje,"190.31.193.129"); */

                    if((!$general['wappApi_oneTime'])or(($general['wappApi_oneTime'] and !in_array($row['idPaciente'], $agregados)))){

                        if(self::setMensajeAuto($telefono, $mensaje)){
                            db_insert("insert into turnos_recordatorios (idTurno) values ('".$recordatorio['idTurno']."')");
                            // Guardo en el log
                            db_insert("INSERT INTO log (usuario, accion, id, fechahora) VALUES ('app','whatsappAPI_recordatorio_enviado', {$recordatorio['idTurno']}, '".date("Y-m-d H:i:s")."' )");
                        }
                        
                    }else{
                        db_insert("insert into turnos_recordatorios (idTurno) values ('".$recordatorio['idTurno']."')");
                        db_insert("INSERT INTO log (usuario, accion, id, fechahora) VALUES ('app','whatsappAPI_recordatorio_noEnviado', {$recordatorio['idTurno']}, '".date("Y-m-d H:i:s")."' )");
                    }

                    $agregados[$row['idPaciente']] = $row['idPaciente'];

                }

            }

        }

    }
    
    public static function setMensajeAuto($destinatario, $mensaje){
        global $general;

        db_query(0, "SET NAMES 'utf8mb4'");
        $data=array(
            'idCliente' => $GLOBALS['user'],
            'cliente' => $general['nombreCliente'],
            'destinatario' => $destinatario,
            'mensaje' => $mensaje,
            'telefonoCliente' => $general["wappApi_telefonoCliente"]
        );
        
        // ENDPOINT API
        $endpointAPI = 'http://whatsapp1.cuatrolados.com/wsp-services/new.php';
        // Para MB Beauty studio
        /*if($general["nombreCliente"] == "MB Beauty Studio - de Macarena Bolatti"){
            $endpointAPI = 'https://cuatrolados.com/whatsapp-api/wsp-services/new.php';
        }*/

        /* Util::printVar($endpointAPI, "138.121.84.107"); */

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $endpointAPI,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_HEADER => true,
          CURLOPT_POSTFIELDS =>json_encode($data),
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer a875fb45cef092fc3b99fecb2fcb595fe88f56df'
          ),
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        
        if($httpcode==200){
            return true;
        }else{	
            return false;
        }
    
    }


    /* -------------------------------------------- */
    /*      FUNCIONES PERSONALIZADAS PARA APPS      */
    /* -------------------------------------------- */
    public static function whatsappAPI_recordatorios(){
        GLOBAL $general, $tot;

        if($general['wappApi_horaPersonalizada']){
            // Ejemplo: 08 => 08:00
            if(strlen($general['wappApi_horaPersonalizada']) == 2){
                $general['wappApi_horaPersonalizada'] = $general['wappApi_horaPersonalizada'].":00";
            }

            // Envio los mensajitos con un rango de 1 hora
            $horaDeEnvio = date("H:i", strtotime($general['wappApi_horaPersonalizada']));
            $rango_desde = date("H:i", strtotime($horaDeEnvio)); // Ejemplo: de 08:00 a las 09:00 se va a enviar
            $rango_hasta = date("H:i", strtotime($horaDeEnvio." + 1 hours"));
            $horaActual = date("H:i");
            
            // Si no esta en el horario para enviar los recordatorios mato la funcion
            if(!($rango_desde <= $horaActual && $horaActual <= $rango_hasta) ) return;
        }else{
            // No envio los mensajes antes de las 7am
            if(date("H") < "07") return;
        }
        
        db_query(0, "SHOW TABLES LIKE 'whatsappAPI_recordatorios'");
        if($tot<1){
            //No existe la tabla. La creo.
            db_query(0, "CREATE TABLE `whatsappAPI_recordatorios` (
                `idWhatsappAPIRecordatorio` int(5) NOT NULL AUTO_INCREMENT,
                `idTurno` int(5) UNSIGNED NOT NULL,
                `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (idWhatsappAPIRecordatorio)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
              ");
        }

        $fechasAnticipacion = array();
        foreach (explode(",", trim($general['wappApi_diasAnticipacion'])) as $diaAnticipacion) {
            // Si tiene horas personalizadas solo me importan los días, si no, me importa la fecha y hora del turno
            if($general['wappApi_horaPersonalizada']){
                $fechasAnticipacion[] = "'".date("Y-m-d", strtotime(" + {$diaAnticipacion} days"))."'";
            }else{
                $fechasAnticipacion[] = "'".date("Y-m-d H", strtotime(" + {$diaAnticipacion} days"))."'";
            }
        }

        $fechasAnticipacion = implode(",", $fechasAnticipacion);
        if($general['wappApi_horaPersonalizada']){
            $filtroFechasAnticipacion = "AND DATE(t.fechaInicio) IN ({$fechasAnticipacion}) ";
        }else{
            $filtroFechasAnticipacion = "AND DATE_FORMAT(t.fechaInicio, '%Y-%m-%d %H') IN ({$fechasAnticipacion}) ";
        }

        $turnos = db_getAll(
            "SELECT 
                t.*, 
                p.nombre, 
                p.apellido, 
                p.codArea, 
                p.telefono, 
                trat.nombre as tratamiento
            FROM 
                turnos t, 
                ordenes o,
                tratamientos trat,
                pacientes p 
            WHERE 
                t.idOrden = o.idOrden AND 
                o.idTratamiento = trat.idTratamiento AND 
                t.idPaciente = p.idPaciente AND 
                t.estado = '0' AND 
                t.eliminado <> '1' 
                {$filtroFechasAnticipacion}
        ");
        

        foreach ($turnos as $turno) {
            
            // Si ya enviamos el mensaje paso al siguiente turno
            if(db_getOne("SELECT idTurno FROM whatsappAPI_recordatorios WHERE idTurno = {$turno->idTurno}")) continue;

            $telefono=$general['prefijoTelefonico'].'9'.$turno->codArea.$turno->telefono;
            if($general["wappApi_mensajePersonalizado"]){
                $mensaje = str_replace(array(
                        "%soloNombre%",
                        "%nombre%",
                        "%fecha%",
                        "%diaTurno%",
                        "%fechaTurno%",
                        "%horaTurno%",
                        "%nombreTratamiento%",
                        "%link%", 
                        "%nombreCliente%"
                    ), array(
                        ucfirst($turno->nombre),
                        ucfirst($turno->nombre). " " . ucfirst($turno->apellido),
                        date('d/m/Y H:i',strtotime($turno->fechaInicio))."hs",
                        DateController::daysToDias(date('l',strtotime($turno->fechaInicio))),
                        date('d/m/Y',strtotime($turno->fechaInicio)),
                        date('H:i',strtotime($turno->fechaInicio)),
                        $turno->tratamiento,
                        'https://'.$general['clientDomain'].'/cancel?t='.base64_encode($turno->idTurno),
                        $general["nombreCliente"]
                    ),
                    $general['wappApi_mensajePersonalizado']
                );
            }else{
                $mensaje = self::recordatorioWapp(ucfirst($turno->nombre), date("d/m/Y H:i", strtotime($turno->fechaInicio))." hs.", $textoPost = "", false);
            }

            $messageLog  = "whatsappAPI_recordatorio_noEnviado";
            if(self::setMensajeAuto($telefono, $mensaje)){
                $messageLog = "whatsappAPI_recordatorio_enviado";
                db_insert("INSERT INTO whatsappAPI_recordatorios (idTurno) VALUES ({$turno->idTurno}) ");
            }
            db_insert("INSERT INTO log (usuario, accion, id, fechahora) VALUES ('app','".$messageLog."', {$turno->idTurno}, NOW() )");
        }


    }

    public static function futurebarber__confirmacionAlAdmin($idTurno){
        GLOBAL $general;

        $dataTurno = db_getOne(
            "SELECT 
                t.*,
                prof.nombre as profesional,
                trat.nombre as tratamiento,
                p.*
            FROM 
                turnos t,
                ordenes o,
                profesionales prof,
                tratamientos trat,
                pacientes p
            WHERE   
                t.idOrden = o.idOrden AND 
                t.idPaciente = p.idPaciente AND 
                t.idTurno = {$idTurno} AND 
                o.idProfesional = prof.idProfesional AND 
                o.idTratamiento = trat.idTratamiento 
            "
        );
        $nombreCompleto = ucfirst($dataTurno->nombre)." ". ucfirst($dataTurno->apellido);
        $telefono = "5492964587497";
        
        $fechaInicio = date("d/m/Y", strtotime($dataTurno->fechaInicio));
        $horaInicio = date("H:i", strtotime($dataTurno->fechaInicio));
        $message = "El {$general['nombrePaciente']} {$nombreCompleto} agendó un {$general['nombreTurno']} para {$dataTurno->tratamiento} con el {$general['nombreProfesional']} {$dataTurno->profesional}. <br><br>Fecha: {$fechaInicio} a las {$horaInicio} hs.<br><br>";
        $message.=$general["nombreCliente"]."<br><br><br>Este mensaje se envía automáticamente, no responder.";

        // Envio el mensaje
        if(self::setMensajeAuto($telefono, $message)){
            // Guardo en el log
            db_insert("INSERT INTO log (usuario, accion, id, fechahora) VALUES ('app','whatsappAPI_confirmacion', {$idTurno}, '".date("Y-m-d H:i:s")."' )");
        }
    }
    
}