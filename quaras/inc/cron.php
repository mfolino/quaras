<?
require_once(dirname(__FILE__, 2).'/inc/fn.php');

// Esta función la llama solamente Mercadopago cuando está activo y hay un cambio en un pago.
if((@$_GET['topic'])and($general['mercadoPago'])){
    notificarPago__custom(json_encode($_GET,true));
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
}

// Cancelo turnos que están en estado pendiente y que pasaron la fecha de vencimiento
if($general['mercadoPago']){
    MercadoPago::cancelarImpagos();
}


db_sync::sync();




/* ------------------------- */
/*          FUNCIONES        */
/* ------------------------- */
function notificarPago__custom($info){
    GLOBAL $general, $row;

    db_insert("insert into notificaciones_mp (tipo, parametros) values ('get', '" . $info . "')");

    require_once('/home/master/lib/vendor/autoload.php');
    MercadoPago\SDK::setAccessToken($general['mercadoPago_accessToken']);

    $merchant_order = null;

    $info = json_decode($info, true);

    if ($info["topic"] == "payment") {
        $payment = MercadoPago\Payment::find_by_id($info['id']);
        // Get the payment and the corresponding merchant_order reported by the IPN.
        $merchant_order = MercadoPago\MerchantOrder::find_by_id($payment->order->id);

        db_insert("insert into notificaciones_mp (tipo, id, parametros) values ('payment', '" . $info['id'] . "', '" . json_encode($payment, true) . "')");
        db_insert("insert into notificaciones_mp (tipo, id, parametros) values ('merchant_order', '" . $info['id'] . "', '" . json_encode($merchant_order, true) . "')");
    }

    if ($info["topic"] == "merchant_order") {
        $merchant_order = MercadoPago\MerchantOrder::find_by_id($info['id']);
        db_insert("insert into notificaciones_mp (tipo, id, parametros) values ('merchant_order', '" . $info['id'] . "', '" . json_encode($merchant_order, true) . "')");
    }

    $estaok = 0;

    // If the payment's transaction amount is equal (or bigger) than the merchant_order's amount you can release your items
    //Voy a verificar si dice que se acreditó, sino no me importa.
    if (($merchant_order->paid_amount >= $merchant_order->total_amount) and ($merchant_order->order_status == 'paid')) {
        $estaok = 1;
    } else {

        if ($merchant_order->order_status == 'paid') {

            $estaok = 1;
        }
    }


    if ($estaok == 1) {
        $idTurno = $merchant_order->external_reference;

        db_query(0, "select estado from turnos where idTurno='" . $idTurno . "' limit 1");
        $estadoAnterior = $row['estado'];

        //Lo dejo pasar solo si el turno NO estaba como pendiente NI cancelado
        if ($estadoAnterior == 9) {
            if ($merchant_order->paid_amount > 0) {
                $queryPago = ", cantidadPago='" . $merchant_order->paid_amount . "'";
            } else {
                $queryPago = "";
            }
            if ($info['id']) {
                $queryId = ", idPago='" . $info['id'] . "' ";
            } else {
                $queryId = "";
            }
            db_update("UPDATE turnos SET estado=0 WHERE idTurno='" . $idTurno . "'");
            db_update("UPDATE pagos SET estadoPago='accredited', fechaPago='" . date('Y-m-d H:i:s') . "' {$queryId}, pago=1 {$queryPago} WHERE idTurno='" . $idTurno . "'");

            db_log('notificacionesMP', 'marcoTurnoPagado', $idTurno);

            Mail_custom::confirmacion($idTurno);
            db_log('notificacionesMP', 'mandoMailConfirmacion', $idTurno);
        }
    }
}