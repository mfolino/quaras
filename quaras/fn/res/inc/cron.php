<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
// Este cron se ejecuta cada 5 minutos y va chequeando que tiene que hacer.


// Esta función la llama solamente Mercadopago cuando está activo y hay un cambio en un pago.
if((@$_GET['topic'])and($general['mercadoPago'])){
    MercadoPago::notificarPago(json_encode($_GET,true));
}


//Ahora si, hagamos las cosas que hace un cron :)

/* 
    #########################
    #
    #   Whatsapp automático
    #
    #########################
*/
if(@$general['wappApi']){
    if($general["wappApi_diasAnticipacion"]){
        NotificationWhatsapp::whatsappAPI_recordatorios();
    }else if(($general['wappApi'] == 1 || $general['wappApi'] == 3) && !$general["wappApi_diasAnticipacion"] ){
        NotificationWhatsapp::generarRecordatoriosAuto();
    }
}

/* 
    #########################
    #
    #   Mail de recordatorio
    #
    #########################
*/
// Veo si es hora de mandar recordatorio, si la es, lo hago
if($general['horaRecordatorio']==date("H:i")){
    if($general["mail_recordatorio_personalizado"]){
        Notification::recordatorio_personalizado();
    }else{
        Notification::recordatorio();
    }
    /* Notification::recordatorio(); */
}

// Cancelo turnos que están en estado pendiente y que pasaron la fecha de vencimiento
if($general['mercadoPago']){
    MercadoPago::cancelarImpagos();
}


/* 
    #########################
    #
    #   CREDITOS
    #
    #########################
*/
if(@$general['creditos']){
    PacienteController::updateDataCreditos();
}



/* --------------------------------------------------------- */
/*      CHEQUEAMOS SI NO SACARON TURNOS EN MAS DE 7 DIAS     */
/* --------------------------------------------------------- */

if(date("H:i") != "08:00") die();

$fechaAct = date("Y-m-d");
$fechaUlt = db_getOne("SELECT fechaAlta FROM ordenes ORDER BY fechaAlta DESC");

$fechaUlt= date("Y-m-d", strtotime($fechaUlt->fechaAlta." + 7 days"));

// Armar mensaje
if($fechaAct >= $fechaUlt){
    $mail = 'sabri@cuatrolados.com';
    
    $iconoExito=0;
    $tablaProductosMail=0;
    $tituloMail='<h1><img src="https://turnos.app/assets/img/sistema/calendar.png" /> </h1><h1>App sin uso/turnos</h1>';
    $alineacionTituloMail='center';
    $firma = $general['nombreCliente'];

    $subtituloMail.= 'Hola Sabri, esta app '.$firma.' está sin turnos desde hace una semana';
    $subtituloMail .= '<br><br><br><br><a href="https://'.$general['clientDomain'].'/admin" style="border:1px solid #'.$general['colorPrimario'].';background-color:#'.$general['colorPrimarioHover'].';padding:15px 30px;margin:20px 0; color:#ffffff;font-size:16px;font-weight:bold;text-decoration:none" type="button">Admin</a>';

    $alineacionSubtituloMail='left';
    $textoPie="";
    $productos='';

    ob_start();
    include(fn."/res/mailTemplate.php");
    $mensaje=ob_get_contents();
    ob_end_clean();
    $mensaje = str_replace("nbsp", " ", $mensaje);
    
    // Enviar Mail
    $error = Notification::sendEmail($general['nombreCliente'], $mail, $mensaje, $general['clientDomain'], "App sin uso/turnos");
}
