<?
define('fn','/home/master/fn');
require_once(fn.'/fn.php');

/* BOUNCE */
class Mail__aux{
    
    public static function enviarFactura($idTurno, $pathFileSend = "") {
        global $general;

        $dataTurno = db_getOne("SELECT t.*, p.nombre, p.apellido, p.mail FROM turnos t, pacientes p WHERE t.idPaciente = p.idPaciente AND t.idTurno = {$idTurno} ");

        $nombrePaciente = ucfirst($dataTurno->nombre);
        $apellidoPaciente = ucfirst($dataTurno->apellido);
        $mail = $dataTurno->mail;
        
        $iconoExito=0;
        $tablaProductosMail=0;
        $tituloMail='<h1><img src="https://turnos.app/assets/img/sistema/calendar.png" /> </h1><h1>Factura de '.$general["nombreTurno"].'!</h1>';
        $alineacionTituloMail='center';
        $firma=$general['nombreCliente'];

        $subtituloMail = "Hola {$nombrePaciente} {$apellidoPaciente}, te adjuntamos la factura del turno con fecha de hoy a las ".date("H:i", strtotime($dataTurno->fechaInicio))."hs.";
        $subtituloMail.='<br><br><br><br><h2>'.$firma.'</h2><i>Te esperamos.</i>';
        $alineacionSubtituloMail='left';
        $textoPie='<small>Este aviso se envía de forma automática y no se responden consultas por este medio.</small>';
        $productos='';

        ob_start();
        include __DIR__. "/templateMail.php";
        $mensaje=ob_get_contents();
        ob_end_clean();

        // Enviar Mail
        if(Util::is_valid_email($mail) ){
            /* $pathFileSend = array(
                "filename" => "factura.pdf",
                "filepath" => $_SERVER['DOCUMENT_ROOT']."facturacion/facturas/ars/facturaB_00004-00000001.pdf"
            ); */
            
            $response = Notification::sendEmailArchivo($general['nombreCliente'], $mail, $mensaje, $general['clientDomain'], "Factura de {$general['nombreTurno']}", $pathFileSend);
        }
    }
    
    public static function enviarConsentimiento($idTurno, $pathFileSend = "") {
        global $general;

        $dataTurno = db_getOne("SELECT t.*, p.nombre, p.apellido, p.mail FROM turnos t, pacientes p WHERE t.idPaciente = p.idPaciente AND t.idTurno = {$idTurno} ");

        $nombrePaciente = ucfirst($dataTurno->nombre);
        $apellidoPaciente = ucfirst($dataTurno->apellido);
        $mail = $dataTurno->mail;
        
        $iconoExito=0;
        $tablaProductosMail=0;
        $tituloMail='<h1><img src="https://turnos.app/assets/img/sistema/calendar.png" /> </h1><h1>Consentimiento de '.$general["nombreTurno"].'!</h1>';
        $alineacionTituloMail='center';
        $firma=$general['nombreCliente'];

        $subtituloMail = "Hola {$nombrePaciente} {$apellidoPaciente}, te adjuntamos el consentimiento del turno con fecha de hoy a las ".date("H:i", strtotime($dataTurno->fechaInicio))."hs.";
        $subtituloMail.='<br><br><br><br><h2>'.$firma.'</h2><i>Te esperamos.</i>';
        $alineacionSubtituloMail='left';
        $textoPie='<small>Este aviso se envía de forma automática y no se responden consultas por este medio.</small>';
        $productos='';

        ob_start();
        include __DIR__. "/templateMail.php";
        $mensaje=ob_get_contents();
        ob_end_clean();

        // Enviar Mail
        if(Util::is_valid_email($mail) ){
            /* $pathFileSend = array(
                "filename" => "factura.pdf",
                "filepath" => $_SERVER['DOCUMENT_ROOT']."facturacion/facturas/ars/facturaB_00004-00000001.pdf"
            ); */
            
            Notification::sendEmailArchivo($general['nombreCliente'], $mail, $mensaje, $general['clientDomain'], "Consentimiento de {$general['nombreTurno']}", $pathFileSend);
        }
    }

    // VENTA
    public static function venta_notificacion($idVenta, $pathFileSend) {
        global $general;

        $dataVenta = db_getOne("SELECT v.*, p.nombre, p.apellido, p.mail FROM ventas v, pacientes p WHERE v.idPaciente = p.idPaciente AND v.idVenta = {$idVenta} ");

        $nombrePaciente = ucfirst($dataVenta->nombre);
        $apellidoPaciente = ucfirst($dataVenta->apellido);
        $mail = $dataVenta->mail;
        $fechaVenta = date("d/m/Y", strtotime($dataVenta->created_at));
        $horaVenta = date("H:i", strtotime($dataVenta->created_at));

        
        $iconoExito=0;
        $tablaProductosMail=0;
        $tituloMail='<h1><img src="https://turnos.app/assets/img/sistema/calendar.png" /> </h1><h1>Factura de compra!</h1>';
        $alineacionTituloMail='center';
        $firma=$general['nombreCliente'];

        $subtituloMail = "Hola {$nombrePaciente} {$apellidoPaciente}, te adjuntamos la factura de la compra realizada el {$fechaVenta} a las {$horaVenta}hs.";
        $subtituloMail.='<br><br><br><h2>'.$firma.'</h2><i>Te esperamos.</i>';
        $alineacionSubtituloMail='left';
        $textoPie='<small>Esta notificación se envía de forma automática y no se responden consultas por este medio.</small>';
        $productos='';

        ob_start();
        include __DIR__. "/templateMail.php";
        $mensaje=ob_get_contents();
        ob_end_clean();

        // Enviar Mail
        if(Util::is_valid_email($mail) ){            
            $response = Notification::sendEmailArchivo($general['nombreCliente'], $mail, $mensaje, $general['clientDomain'], "Factura de compra", $pathFileSend);
        }
    }
}

/* ------------------------- */
/*          ARENAS           */
/* ------------------------- */
class Mail_custom{

    /**
     * Manda el mail de confirmacion 
     * @param int $idTurno 
     */
    public static function confirmacion($idTurno) {
        GLOBAL $general;

        $dataTurno = db_getOne(
            "SELECT 
                p.idPaciente, 
                p.nombre, 
                p.apellido, 
                p.mail, 
                t.fechaInicio, 
                t.idOrden, 
                t.idGrupo, 
                p.codArea, 
                p.telefono,
                p.observaciones, 
                o.idTratamiento,
                o.idProfesional,
                prof.nombre as profesional,
                trat.nombre as tratamiento
            FROM 
                turnos t, 
                pacientes p, 
                ordenes o, 
                tratamientos trat,
                profesionales prof
            WHERE 
                t.idOrden = o.idOrden AND 
                o.idTratamiento = trat.idTratamiento AND 
                o.idProfesional = prof.idProfesional AND 
                p.idPaciente = t.idPaciente AND 
                t.idTurno='{$idTurno}'
        ");
        

        $nombrePaciente = ucfirst($dataTurno->nombre);
        $apellidoPaciente = ucfirst($dataTurno->apellido);
        $mail = $dataTurno->mail;
        $iconoExito=0;
        $tablaProductosMail=0;
        $tituloMail='<h1><img src="https://turnos.app/assets/img/sistema/calendar.png" /> </h1><h1>Confirmación de '.$general["nombreTurno"].'!</h1>';
        $alineacionTituloMail='center';


        // Fecha del turno
        $textoFechaTurno = "el ".date("d/m/Y H:i",strtotime($dataTurno->fechaInicio))." hs."; 
        if(date("Y-m-d",strtotime($dataTurno->fechaInicio)) == date("Y-m-d")){
            $textoFechaTurno = "hoy";
        }

        // Firma
        $firma=$general['nombreCliente'];
        if($general['mailFirmaProfesional']){
            //Busco el nombre del profesional
            $dataProfesional = db_getOne("SELECT pro.nombre FROM profesionales pro, ordenes o WHERE o.idProfesional=pro.idProfesional AND o.idOrden = {$dataTurno->idOrden}");
            $firma=$dataProfesional->nombre;
        }


        $subtituloMail=str_replace(
            array('%nombre%'), 
            array($nombrePaciente.' '.$apellidoPaciente), 
            $general['mailTextoArribaConfirmacion']
        ).' en '.$dataTurno->profesional.' para '.$textoFechaTurno.'.:';

        
        $subtituloMail .= '<h4 style="margin-bottom: .2em;"><b>'.ucfirst($general["nombreBoletos"]).':</b></h4>';
        foreach (db_getAll("SELECT b.nombre, COUNT(tp.idBoleto) as cantidad FROM turnos_participantes tp, boletos b WHERE tp.idBoleto = b.idBoleto AND tp.idTurno = {$idTurno}") as $boleto) {
            $subtituloMail .= "- ". ucfirst($boleto->nombre) . " x " . $boleto->cantidad . " " . ($boleto->cantidad == 1 ? $general["nombreBoleto"] : $general["nombreBoletos"])."<br>";
        }
        
        $subtituloMail.='<br><br>'.QR::new($dataTurno->idGrupo).'<br>';

        if($general['usuarioCancela']){
            $subtituloMail.='<br><br><br><br><a href="https://'.$general['clientDomain'].'/cancel?t='.base64_encode($idTurno).'" style="border:1px solid #'.$general['colorPrimario'].';background-color:#'.$general['colorPrimarioHover'].';padding:15px 30px;margin:20px 0; color:#ffffff;font-size:16px;font-weight:bold;text-decoration:none" type="button">Cancelar Entradas</a>';
        }
        
        if($general['mail_confirmacion_textoPrevioFirma']){
            $subtituloMail.="<br><br><br><br>".$general['mail_confirmacion_textoPrevioFirma'];
        }

        $subtituloMail.= '<br><br><br><br><h2>'.$firma.'</h2><i>Te esperamos.</i>';
        $alineacionSubtituloMail='left';
        $textoPie='<small>Este aviso se envía de forma automática y no se responden consultas por este medio.</small>';
        $productos='';

        ob_start();
        include(fn."/res/mailTemplate.php");
        $mensaje=ob_get_contents();
        ob_end_clean();
        $mensaje = str_replace("nbsp", " ", $mensaje);

        // Enviar Mail
        if(Util::is_valid_email($mail)){
            // Valido que sea el primer turno del paciente
            $isPrimerTurnoDelPaciente =  !!db_getOne("SELECT t.idTurno FROM turnos t, pacientes p WHERE t.idPaciente = p.idPaciente AND p.idPaciente = '{$dataTurno->idPaciente}' ");
            $enviarArchivo = false;        
                
            $error = Notification::sendEmail($general['nombreCliente'], $mail, $mensaje, $general['clientDomain'], "Confirmación de {$general['nombreTurno']}", $enviarArchivo, $isPrimerTurnoDelPaciente);
        }
    }

}

class db_sync{
    public static function checkRemote($host){
        if($socket =@ fsockopen($host, 80, $errno, $errstr, 30)) {
            return true;
        fclose($socket);
        } else {
            return false;
        }
    }

    public static function update($query){

        global $general, $entorno;

        if(self::checkRemote($general['remoteDomain'])){
            //Tengo conexión remota
            $entorno='remoto';
            db_update($query);
        }else{
            db_insert("insert into sync (query) values ('{$query}')");
        }
    }
    
    public static function sync(){

        global $general, $row1, $res1, $tot1;

        $consultas = array();

        db_query(1, "SELECT idSync, query FROM sync WHERE procesado = 0 ORDER BY fecha ASC");
        for($i1=0; $i1<$tot1; $i1++){
            $nres1 = $res1 -> data_seek($i1);
            $row1 = $res1 -> fetch_assoc();
            $consultas[$row1['idSync']] = $row1['query'];
        }

        $entorno = 'remoto';
        foreach($consultas as $idConsulta => $consulta){
            db_update($consulta);
            db_delete("DELETE FROM sync WHERE idSync = {$idConsulta}");
        }
    }
}

class PromocionController{
    public static function getPromocionesByFecha($fecha){
        $fecha = date("Y-m-d", strtotime(str_replace("/","-", $fecha))); // Por las dudas
        $promociones = array();

        $numeroDeDiaDeLaSemana = date("N", strtotime($fecha));
        $fecha = $fecha;
        $horaActual = date("H:i:s");
        foreach (db_getAll("SELECT p.*, b.nombre FROM promociones p, boletos b WHERE p.idBoleto = b.idBoleto AND p.eliminado = 0 AND p.estado = 'A' AND b.estado = 'A' AND p.desde <= '{$fecha}' AND '{$fecha}' <= p.hasta AND b.precio > 0 ORDER BY b.nombre") as $promocion) {
            $horarioPromocion = db_getOne("SELECT * FROM promociones_horarios WHERE idPromocion = {$promocion->idPromocion} AND dia = $numeroDeDiaDeLaSemana ");
            if(count($horarioPromocion) > 0){
                
                $horarioPromocion->desde = date("H:i", strtotime($horarioPromocion->desde));
                $horarioPromocion->hasta = date("H:i", strtotime($horarioPromocion->hasta));
                $promocion->nombre = ucfirst($promocion->nombre);
                $promocion->horario = $horarioPromocion;
                $promociones[] = $promocion;
            }
        }

        return $promociones;
    }
}