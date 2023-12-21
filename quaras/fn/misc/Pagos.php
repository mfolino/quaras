<?php

// Clase para procesar pagos con MercadoPago
class MercadoPago
{

    public static function generarLinkPago($idTurno, $pago, $tratamiento, $valorTotal = null){

        global $general, $row, $tot, $res;
        global $row1, $row2, $tot2;

        $aPagar = 0;

        if ($pago == 'sena') {
            $aPagar = intval($general['mercadoPago_sena']);
        } else {
            if ($general['mercadoPago_servicios']) {
                db_query(2, "SELECT cantidad FROM tratamientos_valores WHERE idTratamiento='{$tratamiento}' AND fechaAlta<=NOW() ORDER by fechaAlta DESC limit 1");
                $aPagar = $row2['cantidad'];
            } else {
                $aPagar = intval($general['mercadoPago_sena']);
            }
        }

        // Calculo por seña del servicio
        if ($pago == 'sena' && $general['mercadoPago_servicios_sena']) {
            $precioTratamiento = intval(TratamientoController::getPrice($tratamiento));
            $porcentajeSena = intval($general['mercadoPago_servicios_sena_porcentaje']) / 100;
            $aPagar = $precioTratamiento * $porcentajeSena;
            /* Util::printVar([$tratamiento, $precioTratamiento, $porcentajeSena, $aPagar], "181.12.34.119", false); */
        }

        /* Util::printVar([$idTurno, $pago, $tratamiento], "181.99.172.180", false); */

        // Calcula le precio del servicio y si es el primer turno del paciente le hace un descuento del 50%
        if($general["mercadoPago_servicios_sena__porcentaje_primerTurno"]){
            db_query(2, "SELECT * FROM turnos WHERE idTurno = {$idTurno} LIMIT 1");
            $idPaciente = $row2["idPaciente"];

            // Total del servicio
            db_query(2, "SELECT cantidad FROM tratamientos_valores WHERE idTratamiento='{$tratamiento}' AND fechaAlta<=NOW() ORDER by fechaAlta DESC limit 1");
            $aPagar = $row2['cantidad'];

            db_query(2, "SELECT * FROM turnos WHERE idPaciente = {$idPaciente}");
            $aPagar = $aPagar * ($general["mercadoPago_servicios_sena__porcentaje_primerTurno"]/100);
        }

        /* Util::printVar([$aPagar, $idPaciente, $general["mercadoPago_servicios_sena__porcentaje_primerTurno"]], "181.99.172.180"); */

        // Si viene un valorTotal es porque hay varios turnos
        if ($valorTotal) {
            $aPagar = $valorTotal;
        }

        // Si viene un valorTotal entonces vienen varios ids de turnos
        // Todos los ids de turnos pertenecen al mismo paciente
        

        if ($valorTotal) {
            // Convierto los ids que me llegan en array
            if(is_numeric($idTurno)){
                $idsTurnosInArray = array($idTurno);
            }else{
                if(is_array($idTurno)){
                    $idsTurnosInArray = $idTurno;
                }else{
                    $idsTurnosInArray = explode(",", $idTurno);
                }
            }

            // Obtengo el primer id
            $idsTurnos = $idsTurnosInArray[0];
            $montoPorTurno = $valorTotal / count($idsTurnosInArray);

            /* Util::printVar([$idsTurnos, $idsTurnosInArray, $montoPorTurno], "190.31.195.37", true); */

            foreach ($idsTurnosInArray as $id) {
                db_insert("INSERT INTO pagos (idTurno, cantidadPago) VALUES ($id, '{$montoPorTurno}') ON DUPLICATE KEY UPDATE cantidadPago='{$montoPorTurno}'");
            }

        } else {
            $idsTurnos = $idTurno;
            db_insert("INSERT INTO pagos (idTurno, cantidadPago) VALUES ($idTurno, '{$aPagar}') ON DUPLICATE KEY UPDATE cantidadPago='{$aPagar}'");
        }

        /* Util::printVar([$idTurno, $pago, $tratamiento, $valorTotal], "190.31.195.37", true); */



        /* Obtener el id del paciente */
        db_query(0, "SELECT p.* FROM pacientes p, turnos t WHERE p.idPaciente = t.idPaciente AND t.idTurno = '" . $idsTurnos . "' LIMIT 1");
        $paciente = $row["idPaciente"];


        /* 
            Busco el token de la tabla de profesionales y piso el token por defecto
        */
        // Token mercado pago
        /* Util::printVar($general['mercadoPago_accessToken'], '186.138.206.135', true); */

        $tokenMercadoPago = $general['mercadoPago_accessToken'];
        if ($general['mercadoPago_accessToken_por_profesional']) {
            db_query(
                1,
                "SELECT 
                    p.* 
                FROM 
                    profesionales p, 
                    turnos t, 
                    ordenes o 
                WHERE 
                    t.idOrden = o.idOrden AND 
                    o.idProfesional = p.idProfesional AND 
                    t.idTurno = " . $idsTurnos . " 
                "
            );
            $tokenMercadoPago = $row1["access_token"];
        }

        /* 
            // Para hacer pruebas con cupos
            - Numero:   4509 9535 6623 3704
            - Codigo:   123
            - Fecha:    11/25
         */
        if ($_SERVER['REMOTE_ADDR'] == '186.124.140.124'):
            $tokenMercadoPago = 'TEST-6682145068429258-010609-5c98dd1df0126588a93e2984f54f90e9__LD_LA__-93379560';            
        endif;

        
        require_once('/home/master/lib/vendor/autoload.php');
        MercadoPago\SDK::setAccessToken($tokenMercadoPago);
        MercadoPago\SDK::setPlatformId($general["mercadoPago_plataformId"]);

        $preference = new MercadoPago\Preference();

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();

        $titleMP = 'Turno ' . $general['nombreCliente'];
        if($general["mercadoPago_agregarNombreServicioAlDetalleDeCompra"]){
            $dataServicioTurno = db_getOne("SELECT trat.nombre FROM turnos t, ordenes o , tratamientos trat WHERE t.idOrden = o.idOrden AND o.idTratamiento = trat.idTratamiento AND t.idTurno IN ({$idTurno})");
            $nombreServicio = $dataServicioTurno->nombre;
            $titleMP .= " - ".$nombreServicio;
        }
        $item->title = $titleMP;
        $item->quantity = 1;
        $item->unit_price = floatval($aPagar);

        db_query(0, "select p.nombre, p.apellido, p.mail, p.codArea, p.telefono, p.dni from pacientes p, turnos t where p.idPaciente='" . $paciente . "'=t.idPaciente and t.idTurno='" . $idTurno . "'");

        $payer = new MercadoPago\Payer();
        $payer->name = $row['nombre'];
        $payer->surname = $row['apellido'];
        if ($row['mail']) {
            $payer->email = $row['mail'];
        }
        if ($row['telefono']) {
            $payer->phone = array(
                "area_code" => $row['codArea'],
                "number" => $row['telefono']
            );
        }

        $payer->identification = array(
            "type" => "DNI",
            "number" => $row['dni']
        );

        $preference->payment_methods = array(
            "excluded_payment_types" => array(
                array("id" => "ticket"),
                array("id" => "atm")
            ),
        );

        // Si viene un valor total envio todos los ids de los turnos como string en el external_reference
        $preference->external_reference = $valorTotal ? str_replace(",", "a", $idTurno) : $idTurno;



        $preference->notification_url = 'https://' . $general['clientDomain'] . '/inc/cron.php?source_news=ipn';
        $preference->auto_return = 'all';

        if($general["mercadoPago_activar_binary_mode"]){
            $preference->binary_mode= true;
        }else{
            $preference->binary_mode= false;
        }


        //Configuramos el vencimiento para que no pueda entrar el pago una vez cancelado el turno.
        $preference->expires = true;
        $preference->expiration_date_from = date("c", time());
        $preference->expiration_date_to = date("c", strtotime('+' . ($general['mercadoPago_limite'] - 5) . ' minutes'));

        //Le pasamos el nombre del negocio así no se asusta cuando ve el resumen de la tarjeta
        $preference->statement_descriptor = "Turnos.app - " . $general['nombreCliente'];;


        $preference->back_urls = array(
            "success" => 'https://' . $general['clientDomain'] . '/pago.php?st=1',
            "failure" => 'https://' . $general['clientDomain'] . '/pago.php?st=0',
            "pending" => 'https://' . $general['clientDomain'] . '/pago.php?st=2'
        );

        $preference->items = array($item);
        $preference->save();

        /* if($_SERVER["REMOTE_ADDR"] == "190.231.247.203"){
            Util::printVar($preference);
        } */

        // Guardar en el log
        db_log('homeTurnos', 'generarLinkDePago_MP', $idTurno);

        return $preference->init_point;
    }

    public static function notificarPago($info)
    {

        global $general, $row, $tot, $res;

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

        // Util::printVar($estaok, '186.138.206.135');

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

                require_once($_SERVER["DOCUMENT_ROOT"] . '/inc/mailConfirmacion.php');
                db_log('notificacionesMP', 'mandoMailConfirmacion', $idTurno);
            }
        }
    }

    public static function devolverPago($idTurno)
    {

        global $general, $row, $tot, $res;

        db_query(
            0,
            "SELECT idPago, cantidadPago
        FROM pagos
        WHERE idTurno = '{$idTurno}'
        LIMIT 1"
        );

        if ($tot > 0 && $row['idPago']) {
            require_once('/home/master/lib/vendor/autoload.php');

            MercadoPago\SDK::setAccessToken($general['mercadoPago_accessToken']);

            $refund = new MercadoPago\Refund();
            $refund->payment_id = $row['idPago'];
            $refund->save();

            $payment = MercadoPago\Payment::find_by_id($row['idPago']);

            db_update(
                "UPDATE pagos SET estadoPago = '{$payment->status_detail}'
                WHERE idTurno = '{$idTurno}'"
            );

            db_log('homeTurnos', 'devolverPago', $idTurno);

            return true;
        } else {

            return false;
        }
    }

    public static function cancelarImpagos()
    {
        global $general, $row9, $tot9, $res9;

        $fechaLimite = date('Y-m-d H:i:s', strtotime('-' . $general['mercadoPago_limite'] . ' minutes'));

        db_query(9, "SELECT t.idTurno FROM turnos t, pagos p WHERE p.fechaAlta<'{$fechaLimite}' AND t.estado=9 AND t.idTurno=p.idTurno AND p.pago=0 limit 1");
        for ($i9 = 0; $i9 < $tot9; $i9++) {
            $nres9 = $res9->data_seek($i9);
            $row9 = $res9->fetch_assoc();
            db_update("UPDATE turnos SET estado=3 WHERE idTurno='" . $row9['idTurno'] . "' limit 1");
            db_log('cron', 'cancelarTurnoImpago', $row9['idTurno']);
        }
        
        
        db_query(9, "SELECT t.idTurno FROM turnos t, ordenes o WHERE t.idOrden = o.idOrden AND t.estado = 9 AND o.fechaAlta<'{$fechaLimite}'");
        for ($i9 = 0; $i9 < $tot9; $i9++) {
            $nres9 = $res9->data_seek($i9);
            $row9 = $res9->fetch_assoc();
            db_update("UPDATE turnos SET estado=3 WHERE idTurno='" . $row9['idTurno'] . "' limit 1");
            db_log('cron', 'cancelarTurnoImpago', $row9['idTurno']);
        }
    }


    /* ----------------------------------------- */
    /* ----------------------------------------- */
    /*                                           */
    /*      FUNCIONES PERSONALIZADAS DE APP      */
    /*                                           */
    /* ----------------------------------------- */
    /* ----------------------------------------- */

    /* ------------------------- */
    /*          FLOTARIO         */
    /* ------------------------- */
    /* Link pago turno */
    public static function flotario_generarLinkPago($idGrupo, $total, $idPaciente){

        global $general, $row;

        $aPagar = $total;

        $turnos = db_getAll("SELECT * FROM turnos WHERE idGrupo = '{$idGrupo}'");
        $precioPorTurno = $total / count($turnos);
        foreach ($turnos as $turno) {
            db_insert("INSERT INTO pagos (idTurno, cantidadPago) VALUES ($turno->idTurno, '{$precioPorTurno}') ON DUPLICATE KEY UPDATE cantidadPago='{$precioPorTurno}'");
        }

        /* Obtener el id del paciente */
        db_query(0, "SELECT p.* FROM pacientes p WHERE p.idPaciente = {$idPaciente} LIMIT 1");
        $paciente = $row["idPaciente"];


        // Token mercado pago
        $tokenMercadoPago = $general['mercadoPago_accessToken'];

        // Para hacer pruebas con cupos
        if (in_array($_SERVER['REMOTE_ADDR'], ['138.121.84.107', '181.99.172.180', '190.31.193.129'])) {
            $tokenMercadoPago = 'TEST-6682145068429258-010609-5c98dd1df0126588a93e2984f54f90e9__LD_LA__-93379560';
        }
        require_once('/home/master/lib/vendor/autoload.php');
        MercadoPago\SDK::setAccessToken($tokenMercadoPago);
        MercadoPago\SDK::setPlatformId($general["mercadoPago_plataformId"]);

        $preference = new MercadoPago\Preference();

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();

        $item->title = 'Turno ' . $general['nombreCliente'];
        $item->quantity = 1;
        $item->unit_price = floatval($aPagar);

        $payer = new MercadoPago\Payer();
        $payer->name = $row['nombre'];
        $payer->surname = $row['apellido'];
        if ($row['mail']) {
            $payer->email = $row['mail'];
        }
        if ($row['telefono']) {
            $payer->phone = array(
                "area_code" => $row['codArea'],
                "number" => $row['telefono']
            );
        }

        $payer->identification = array(
            "type" => "DNI",
            "number" => $row['dni']
        );

        $preference->payment_methods = array(
            "excluded_payment_types" => array(
                array("id" => "ticket"),
                array("id" => "atm")
            ),
        );

        // Si viene un valor total envio todos los ids de los turnos como string en el external_reference
        /* $preference->external_reference = $valorTotal ? str_replace(",", "a", $idTurno) : $idTurno; */
        $preference->external_reference = $idGrupo;

        $preference->notification_url = 'https://' . $general['clientDomain'] . '/inc/cron.php?source_news=ipn';
        $preference->auto_return = 'all';
        $preference->binary_mode=true;

        //Configuramos el vencimiento para que no pueda entrar el pago una vez cancelado el turno.
        $preference->expires = true;
        $preference->expiration_date_from = date("c", time());
        $preference->expiration_date_to = date("c", strtotime('+' . ($general['mercadoPago_limite'] - 5) . ' minutes'));

        //Le pasamos el nombre del negocio así no se asusta cuando ve el resumen de la tarjeta
        $preference->statement_descriptor = "Turnos.app - " . $general['nombreCliente'];

        $preference->back_urls = array(
            "success" => 'https://' . $general['clientDomain'] . '/pago.php?st=1',
            "failure" => 'https://' . $general['clientDomain'] . '/pago.php?st=0',
            "pending" => 'https://' . $general['clientDomain'] . '/pago.php?st=2'
        );

        $preference->items = array($item);
        $preference->save();

        return $preference->init_point;
    }
    /* Link de giftcard */
    public static function flotario_generarLinkPago_giftcards($idGrupoGiftcard)
    {

        global $general, $row;

        $giftcards  = db_getAll("SELECT gc.*, gcp.* FROM giftcard gc, giftcard_packs gcp WHERE gcp.idGiftcardPack = gc.idGiftcardPack AND gc.idGrupo = '{$idGrupoGiftcard}'");
        $precioTotal = 0;
        foreach ($giftcards as $giftcard) {
            db_insert("INSERT INTO giftcard_pagos (idGiftcard, cantidadPago) VALUES ({$giftcard->idGiftcard}, '{$giftcard->precio}') ");
            $precioTotal += $giftcard->precio;
        }


        /* Obtener el id del paciente */
        db_query(0, "SELECT p.* FROM pacientes p WHERE p.idPaciente = {$giftcards[0]->idPaciente} LIMIT 1");
        $paciente = $row["idPaciente"];
        $nombre = $row["nombre"];
        $apellido = $row["apellido"];
        $mail = $row["mail"];
        $codArea = $row["codArea"];
        $telefono = $row["telefono"];
        $dni = $row["dni"];

        /* Token mercado pago */
        $tokenMercadoPago = $general['mercadoPago_accessToken'];

        // Para hacer pruebas con cupos
        if (in_array($_SERVER['REMOTE_ADDR'], ['138.121.84.107', '190.31.193.129'])) {
            $tokenMercadoPago = 'TEST-6682145068429258-010609-5c98dd1df0126588a93e2984f54f90e9__LD_LA__-93379560';
        }


        require_once('/home/master/lib/vendor/autoload.php');
        MercadoPago\SDK::setAccessToken($tokenMercadoPago);
        MercadoPago\SDK::setPlatformId($general["mercadoPago_plataformId"]);
        
        $preference = new MercadoPago\Preference();

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();

        $item->title = 'Turno ' . $general['nombreCliente'];
        $item->quantity = 1;
        $item->unit_price = floatval($precioTotal);

        $payer = new MercadoPago\Payer();
        $payer->name = $nombre;
        $payer->surname = $apellido;
        if ($mail) {
            $payer->email = $mail;
        }
        if ($telefono) {
            $payer->phone = array(
                "area_code" => $codArea,
                "number" => $telefono
            );
        }

        $payer->identification = array(
            "type" => "DNI",
            "number" => $dni
        );

        $preference->payment_methods = array(
            "excluded_payment_types" => array(
                array("id" => "ticket"),
                array("id" => "atm")
            ),
        );

        // Si viene un valor total envio todos los ids de los turnos como string en el external_reference
        $preference->external_reference = $idGrupoGiftcard;

        $preference->notification_url = "https://{$general['clientDomain']}/inc/cron.php?source_news=ipn&giftcard_grupo=1";
        $preference->auto_return = 'all';
        // $preference->binary_mode=true;

        //Configuramos el vencimiento para que no pueda entrar el pago una vez cancelado el turno.
        $preference->expires = true;
        $preference->expiration_date_from = date("c", time());
        $preference->expiration_date_to = date("c", strtotime('+' . ($general['mercadoPago_limite'] - 5) . ' minutes'));

        //Le pasamos el nombre del negocio así no se asusta cuando ve el resumen de la tarjeta
        $preference->statement_descriptor = "Turnos.app - " . $general['nombreCliente'];

        $preference->back_urls = array(
            "success" => 'https://' . $general['clientDomain'] . '/pagoGiftcardPack.php?st=1',
            "failure" => 'https://' . $general['clientDomain'] . '/pagoGiftcardPack.php?st=0',
            "pending" => 'https://' . $general['clientDomain'] . '/pagoGiftcardPack.php?st=2'
        );

        $preference->items = array($item);
        $preference->save();

        return $preference->init_point;
    }
    /* Notificaciones de mercado pago */
    public static function flotario_notificarPago($info)
    {

        global $general, $row, $tot, $res;

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

        // Si no esta OKEY. mato la funcion
        if ($estaok != 1) return;

        $idGrupo = $merchant_order->external_reference;

        // Notificaciones para giftcards
        if (isset($info["giftcard_grupo"])) {
            
            // Chequeo que estado de las giftcard sea 9
            $giftcards = db_getAll("SELECT gc.*, gcp.* FROM giftcard gc, giftcard_packs gcp WHERE gc.idGiftcardPack = gcp.idGiftcardPack AND gc.idGrupo = '{$idGrupo}' ");
            if ($giftcards[0]->estado != '9') return;
            
            // Actualizo la tabla que envia las notificaciones por mail al destinatario de la giftcard
            db_update("UPDATE notificaciones_giftcards SET estado = 0 WHERE idGrupo = '{$idGrupo}' ");

            foreach ($giftcards as $giftcard) {
                $queryPago = $merchant_order->paid_amount > 0 ? ", cantidadPago='{$giftcard->precio}'" : "";
                $queryId = $info['id'] ? ", idPago='{$info['id']}' " : "";

                db_update("UPDATE giftcard SET estado = 0 WHERE idGiftcard='{$giftcard->idGiftcard}'");
                db_update("UPDATE giftcard_pagos SET estadoPago='accredited', fechaPago='DATE(NOW())' {$queryId}, pago=1 {$queryPago} WHERE idGiftcard = '{$giftcard->idGiftcard}'");

                db_log('notificacionesMP', 'marcoGiftcarPagada', $giftcard->idGiftcard);
            }
            /* Mail_custom::giftcard_notificacion_creacion($idGrupo); */
            /* db_log('cron', 'giftcard_mandoMailConfirmacionPago', $idGrupo); */

            return;
        }


        $dataTurnos = db_getAll("SELECT * FROM turnos WHERE idGrupo = '{$idGrupo}'");
        if($dataTurnos[0]->estado != 9 ) return; // Si el pago no esta pendiente no hago nada.

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
        db_update("UPDATE turnos SET estado=1 WHERE idGrupo='" . $idGrupo . "'");
        foreach (db_getAll("SELECT * FROM turnos WHERE idGrupo = '{$idGrupo}'") as $turno) {
            db_update("UPDATE pagos SET estadoPago='accredited', fechaPago='" . date('Y-m-d H:i:s') . "' {$queryId}, pago=1 {$queryPago} WHERE idTurno='" . $turno->idTurno . "'");
            db_log('notificacionesMP', 'marcoTurnoPagado', $turno->idTurno);
        }


        db_log('homeTurnos', 'mandoMailConfirmacion_Grupo', $idGrupo);
    }


    /* --------------------------- */
    /*          HAPPY PETS         */
    /* --------------------------- */
    public static function generarLinkPago_happypets($idGrupo, $precioTotal, $tipoTurno = "peluqueria")
    {
        global $general, $row;

        $tokenMercadoPago = $general['mercadoPago_accessToken'];

        // Crendenciales de prueba
        if (in_array($_SERVER['REMOTE_ADDR'], ['138.121.84.107', '190.31.193.129']) ) {
            $tokenMercadoPago = 'TEST-6682145068429258-010609-5c98dd1df0126588a93e2984f54f90e9__LD_LA__-93379560';
        }


        foreach (db_getAll("SELECT trat.valorSena, t.* FROM turnos t, tratamientos trat, ordenes o WHERE t.idOrden = o.idOrden AND o.idTratamiento = trat.idTratamiento AND t.grupoTurnoHabitacion = {$idGrupo} ") as $turno) {

            // Si es del tipo hotel aplico el descuento por noche
            if ($tipoTurno == "hotel" && $turno->mascotasHabitacion) {
                $precioSenaPorNoche = $turno->valorSena;

                $desde = date("Y-m-d", strtotime($turno->fechaInicio));
                $hasta = date("Y-m-d", strtotime($turno->fechaHasta));
                $diferencia = (new DateTime($desde))->diff(new DateTime($hasta));
                $dias = $diferencia->days;
                $promociones = db_getAll("SELECT * FROM promociones WHERE desde <= {$dias} AND hasta >= {$dias} LIMIT 1");
                if (count($promociones) > 0) {
                    $promocion = $promociones[0];
                    $precioSenaPorNoche = round($precioSenaPorNoche * ((100 - $promocion) / 100), 2);
                }
                db_insert("INSERT INTO pagos (idTurno, cantidadPago) VALUES ({$turno->idTurno}, '{$turno->valorSena}') ON DUPLICATE KEY UPDATE cantidadPago='{$precioSenaPorNoche}'");
            } else {
                // Turno de peluqueria. No paga por traslados
                if ($turno->idTratamiento != 3) {
                    db_insert("INSERT INTO pagos (idTurno, cantidadPago) VALUES ({$turno->idTurno}, '{$turno->valorSena}') ON DUPLICATE KEY UPDATE cantidadPago='{$turno->valorSena}'");
                }
            }
        }

        require_once('/home/master/lib/vendor/autoload.php');

        MercadoPago\SDK::setAccessToken($tokenMercadoPago);
        MercadoPago\SDK::setPlatformId($general["mercadoPago_plataformId"]);

        $preference = new MercadoPago\Preference();

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();

        $item->title = 'Turno ' . $general['nombreCliente'];
        $item->quantity = 1;
        $item->unit_price = floatval($precioTotal);

        db_query(0, "select p.nombre, p.apellido, p.mail, p.codArea, p.telefono, p.dni from pacientes p, turnos t where p.idPaciente=t.idPaciente and t.grupoTurnoHabitacion='" . $idGrupo . "' LIMIT 1");

        $payer = new MercadoPago\Payer();
        $payer->name = $row['nombre'];
        $payer->surname = $row['apellido'];
        if ($row['mail']) {
            $payer->email = $row['mail'];
        }
        if ($row['telefono']) {
            $payer->phone = array(
                "area_code" => $row['codArea'],
                "number" => $row['telefono']
            );
        }

        $payer->identification = array(
            "type" => "DNI",
            "number" => $row['dni']
        );

        $preference->payment_methods = array(
            "excluded_payment_types" => array(
                array("id" => "ticket"),
                array("id" => "atm")
            ),
        );

        // Si viene un valor total envio todos los ids de los turnos como string en el external_reference
        $preference->external_reference = $idGrupo;



        $preference->notification_url = 'https://' . $general['clientDomain'] . '/inc/cron.php?source_news=ipn';
        $preference->auto_return = 'all';
        // $preference->binary_mode=true;


        //Configuramos el vencimiento para que no pueda entrar el pago una vez cancelado el turno.
        $preference->expires = true;
        $preference->expiration_date_from = date("c", time());
        $preference->expiration_date_to = date("c", strtotime('+' . ($general['mercadoPago_limite'] - 5) . ' minutes'));


        //Le pasamos el nombre del negocio así no se asusta cuando ve el resumen de la tarjeta
        $preference->statement_descriptor = "Turnos.app - " . $general['nombreCliente'];


        $preference->back_urls = array(
            "success" => 'https://' . $general['clientDomain'] . '/pago.php?st=1&tipoTurno=' . $tipoTurno,
            "failure" => 'https://' . $general['clientDomain'] . '/pago.php?st=0&tipoTurno=' . $tipoTurno,
            "pending" => 'https://' . $general['clientDomain'] . '/pago.php?st=2&tipoTurno=' . $tipoTurno
        );

        $preference->items = array($item);
        $preference->save();

        return $preference->init_point;
    }
    
    /* --------------------------- */
    /*          BOUNCE PARK        */
    /* --------------------------- */
    public static function bouncepark__generarLinkPago($idTurno, $total){

        global $general;

        $dataTurno = db_getOne("SELECT t.*, p.nombre, p.apellido, p.mail, p.codArea, p.telefono, p.dni FROM turnos t, pacientes p WHERE t.idTurno = {$idTurno} AND t.idPaciente = p.idPaciente");

        // Guardo el turno y el monto
        db_insert("INSERT INTO pagos (idTurno, cantidadPago) VALUES ({$idTurno}, '{$total}') ON DUPLICATE KEY UPDATE cantidadPago='{$total}'");

        // Token mercado pago
        $tokenMercadoPago = $general['mercadoPago_accessToken'];        

        require_once('/home/master/lib/vendor/autoload.php');

        MercadoPago\SDK::setAccessToken($tokenMercadoPago);
        MercadoPago\SDK::setPlatformId($general["mercadoPago_plataformId"]);

        $preference = new MercadoPago\Preference();

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();

        $item->title = 'Turno ' . $general['nombreCliente'];
        $item->quantity = 1;
        $item->unit_price = floatval($total);


        $payer = new MercadoPago\Payer();
        $payer->name = $dataTurno->nombre;
        $payer->surname = $dataTurno->apellido;
        if ($dataTurno->mail) {
            $payer->email = $dataTurno->mail;
        }
        if ($dataTurno->telefono) {
            $payer->phone = array(
                "area_code" => $dataTurno->codArea,
                "number" => $dataTurno->telefono
            );
        }

        $payer->identification = array(
            "type" => "DNI",
            "number" => $dataTurno->dni
        );

        $preference->payment_methods = array(
            "excluded_payment_types" => array(
                array("id" => "ticket"),
                array("id" => "atm")
            ),
        );

        // Si viene un valor total envio todos los ids de los turnos como string en el external_reference
        $preference->external_reference = $idTurno;


        $preference->notification_url = 'https://' . $general['clientDomain'] . '/inc/cron.php?source_news=ipn';
        $preference->auto_return = 'all';
        // $preference->binary_mode=true;


        //Configuramos el vencimiento para que no pueda entrar el pago una vez cancelado el turno.
        $preference->expires = true;
        $preference->expiration_date_from = date("c", time());
        $preference->expiration_date_to = date("c", strtotime('+' . ($general['mercadoPago_limite'] - 5) . ' minutes'));


        //Le pasamos el nombre del negocio así no se asusta cuando ve el resumen de la tarjeta
        $preference->statement_descriptor = "Turnos.app - " . $general['nombreCliente'];


        $preference->back_urls = array(
            "success" => 'https://' . $general['clientDomain'] . '/pago.php?st=1',
            "failure" => 'https://' . $general['clientDomain'] . '/pago.php?st=0',
            "pending" => 'https://' . $general['clientDomain'] . '/pago.php?st=2'
        );

        $preference->items = array($item);
        $preference->save();

        // Guardar en el log
        db_log('homeTurnos', 'generarLinkDePago_MP', $idTurno);

        return $preference->init_point;
    }

    /* ------------------------------ */
    /*          CRUDE ESCUELA         */
    /* ------------------------------ */
    public static function crudeescuela__generarLinkPago($idPacientePlan, $montoTotal){
        GLOBAL $general, $newid;
        
        db_insert("INSERT INTO pagosPlan (idPaciente, cantidadPago) VALUES ({$idPacientePlan}, '{$montoTotal}') ");
        $idPago = $newid;

        $dataPaciente = db_getOne("SELECT * FROM pacientes WHERE idPaciente = {$idPacientePlan}");

        // Token mercado pago
        $tokenMercadoPago = $general['mercadoPago_accessToken'];

        // Para hacer pruebas con cupos
        if (in_array($_SERVER['REMOTE_ADDR'], ['138.121.84.107'])) {
            $tokenMercadoPago = 'TEST-6682145068429258-010609-5c98dd1df0126588a93e2984f54f90e9__LD_LA__-93379560';
        }

        require_once('/home/master/lib/vendor/autoload.php');
        MercadoPago\SDK::setAccessToken($tokenMercadoPago);
        MercadoPago\SDK::setPlatformId($general["mercadoPago_plataformId"]);

        $preference = new MercadoPago\Preference();

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();

        $item->title = 'Turno ' . $general['nombreCliente'];
        $item->quantity = 1;
        $item->unit_price = floatval($montoTotal);

        $payer = new MercadoPago\Payer();
        $payer->name = $dataPaciente->nombre;
        $payer->surname = $dataPaciente->apellido;
        if ($dataPaciente->mail) {
            $payer->email = $dataPaciente->mail;
        }
        if ($dataPaciente->telefono) {
            $payer->phone = array(
                "area_code" => $dataPaciente->codArea,
                "number" => $dataPaciente->telefono
            );
        }

        $payer->identification = array(
            "type" => "DNI",
            "number" => $dataPaciente->dni
        );

        $preference->payment_methods = array(
            "excluded_payment_types" => array(
                array("id" => "ticket"),
                array("id" => "atm")
            ),
        );

        // Si viene un valor total envio todos los ids de los turnos como string en el external_reference
        $preference->external_reference = $idPago;

        $preference->notification_url = 'https://' . $general['clientDomain'] . '/inc/cron.php?source_news=ipn';
        $preference->auto_return = 'all';
        // $preference->binary_mode=true;

        //Configuramos el vencimiento para que no pueda entrar el pago una vez cancelado el turno.
        $preference->expires = true;
        $preference->expiration_date_from = date("c", time());
        $preference->expiration_date_to = date("c", strtotime('+' . ($general['mercadoPago_limite'] - 5) . ' minutes'));

        //Le pasamos el nombre del negocio así no se asusta cuando ve el resumen de la tarjeta
        $preference->statement_descriptor = "Turnos.app - " . $general['nombreCliente'];

        $preference->back_urls = array(
            "success" => 'https://' . $general['clientDomain'] . '/pago.php?st=1',
            "failure" => 'https://' . $general['clientDomain'] . '/pago.php?st=0',
            "pending" => 'https://' . $general['clientDomain'] . '/pago.php?st=2'
        );

        $preference->items = array($item);
        $preference->save();

        return $preference->init_point;
    }
    public static function crudeescuela__notificarPago($info){
        GLOBAL $general;

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

        // Util::printVar($estaok, '186.138.206.135');

        if ($estaok == 1) {

            $idPagoPlan = $merchant_order->external_reference;
            $dataPago = db_getOne("SELECT * FROM pagosPlan WHERE idPagoPlan = {$idPagoPlan}");
            $dataPlan = db_getOne("SELECT * FROM creditos_pacientes WHERE idPaciente = {$dataPago->idPaciente}");

            //Lo dejo pasar solo si el turno NO estaba como pendiente NI cancelado
            if ($dataPlan->estado == 9) {
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
                db_update("UPDATE creditos_pacientes SET estado = 0 WHERE idPaciente = {$dataPago->idPaciente}");
                db_update("UPDATE pagosPlan SET estadoPago='accredited', fechaPago='" . date('Y-m-d H:i:s') . "' {$queryId}, pago=1 {$queryPago} WHERE idPagoPlan = {$idPagoPlan}");

                db_log('notificacionesMP', 'pagos_creditosPaciente_pagado', $idPagoPLan);

                // Mando mail de confirmación
                db_log('notificacionesMP', 'mandoMailConfirmacion', $idPagoPlan);
            }
        }
    }
    public static function crudeescuela__cancelarImpagos(){
        GLOBAL $general;

        $fechaLimite = date('Y-m-d H:i:s', strtotime('-' . $general['mercadoPago_limite'] . ' minutes'));

        foreach (db_getAll("SELECT cp.* FROM creditos_pacientes cp, pagosPlan p WHERE p.idPaciente = cp.idPaciente AND p.fechaAlta < '{$fechaLimite}' AND cp.estado=9 AND p.pago=0") as $pago) {
            db_delete("DELETE FROM creditos_pacientes WHERE idPaciente = {$pago->idPaciente}");
            db_log('cron', 'cancelarPlanImpago', $pago->idPagoPlan);
        }
    }

    /* ------------------------ */
    /*          PLAYROOM        */
    /* ------------------------ */
    public static function playroom__generarLinkPago($idTurno, $total){

        global $general;

        $dataTurno = db_getOne("SELECT t.*, p.nombre, p.apellido, p.mail, p.codArea, p.telefono, p.dni FROM turnos t, pacientes p WHERE t.idTurno = {$idTurno} AND t.idPaciente = p.idPaciente");

        // Guardo el turno y el monto
        db_insert("INSERT INTO pagos (idTurno, cantidadPago) VALUES ({$idTurno}, '{$total}') ON DUPLICATE KEY UPDATE cantidadPago='{$total}'");

        // Token mercado pago
        $tokenMercadoPago = $general['mercadoPago_accessToken'];
        
        /* if($_SERVER["REMOTE_ADDR"] == "190.231.247.203"){
            $tokenMercadoPago = "TEST-6682145068429258-010609-5c98dd1df0126588a93e2984f54f90e9__LD_LA__-93379560";
        } */
        
        require_once('/home/master/lib/vendor/autoload.php');
        MercadoPago\SDK::setAccessToken($tokenMercadoPago);
        MercadoPago\SDK::setPlatformId($general["mercadoPago_plataformId"]);

        $preference = new MercadoPago\Preference();

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();

        $item->title = 'Turno ' . $general['nombreCliente'];
        $item->quantity = 1;
        $item->unit_price = floatval($total);


        $payer = new MercadoPago\Payer();
        $payer->name = $dataTurno->nombre;
        $payer->surname = $dataTurno->apellido;
        if ($dataTurno->mail) {
            $payer->email = $dataTurno->mail;
        }
        if ($dataTurno->telefono) {
            $payer->phone = array(
                "area_code" => $dataTurno->codArea,
                "number" => $dataTurno->telefono
            );
        }

        $payer->identification = array(
            "type" => "DNI",
            "number" => $dataTurno->dni
        );

        $preference->payment_methods = array(
            "excluded_payment_types" => array(
                array("id" => "ticket"),
                array("id" => "atm")
            ),
        );

        // Si viene un valor total envio todos los ids de los turnos como string en el external_reference
        $preference->external_reference = $idTurno;


        $preference->notification_url = 'https://' . $general['clientDomain'] . '/inc/cron.php?source_news=ipn';
        $preference->auto_return = 'all';
        // $preference->binary_mode=true;


        //Configuramos el vencimiento para que no pueda entrar el pago una vez cancelado el turno.
        $preference->expires = true;
        $preference->expiration_date_from = date("c", time());
        $preference->expiration_date_to = date("c", strtotime('+' . ($general['mercadoPago_limite'] - 5) . ' minutes'));


        //Le pasamos el nombre del negocio así no se asusta cuando ve el resumen de la tarjeta
        $preference->statement_descriptor = "Turnos.app - " . $general['nombreCliente'];


        $preference->back_urls = array(
            "success" => 'https://' . $general['clientDomain'] . '/pago.php?st=1',
            "failure" => 'https://' . $general['clientDomain'] . '/pago.php?st=0',
            "pending" => 'https://' . $general['clientDomain'] . '/pago.php?st=2'
        );

        $preference->items = array($item);
        $preference->save();

        // Guardar en el log
        db_log('homeTurnos', 'generarLinkDePago_MP', $idTurno);

        return $preference->init_point;
    }

    /* ------------------------ */
    /*          COMER RICO        */
    /* ------------------------ */
    public static function comerrico__generarLinkPago($idTurno, $valorTotal){

        global $general;

        $dataPaciente = db_getOne("SELECT p.* FROM pacientes p, turnos t WHERE t.idPaciente = p.idPaciente AND t.idTurno = {$idTurno}");
        db_insert("INSERT INTO pagos (idTurno, cantidadPago) VALUES ($idTurno, '{$valorTotal}') ON DUPLICATE KEY UPDATE cantidadPago='{$valorTotal}'");

        
        require_once('/home/master/lib/vendor/autoload.php');
        MercadoPago\SDK::setAccessToken($general['mercadoPago_accessToken']);
        MercadoPago\SDK::setPlatformId($general["mercadoPago_plataformId"]);

        $preference = new MercadoPago\Preference();

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();
        $item->title = ucwords($general['nombreTurnos']).' ' . $general['nombreCliente'];
        $item->quantity = 1;
        $item->unit_price = floatval($valorTotal);

        $payer = new MercadoPago\Payer();
        $payer->name = $dataPaciente->nombre;
        $payer->surname = $dataPaciente->apellido;
        if ($dataPaciente->mail) {
            $payer->email = $dataPaciente->mail;
        }
        if ($dataPaciente->telefono) {
            $payer->phone = array(
                "area_code" => $dataPaciente->codArea,
                "number" => $dataPaciente->telefono
            );
        }

        $payer->identification = array(
            "type" => "DNI",
            "number" => $dataPaciente->dni
        );

        $preference->payment_methods = array(
            "excluded_payment_types" => array(
                array("id" => "ticket"),
                array("id" => "atm")
            ),
        );

        // Si viene un valor total envio todos los ids de los turnos como string en el external_reference
        $preference->external_reference = $idTurno;
        $preference->notification_url = 'https://' . $general['clientDomain'] . '/inc/cron.php?source_news=ipn';
        $preference->auto_return = 'all';
        // $preference->binary_mode=true;


        //Configuramos el vencimiento para que no pueda entrar el pago una vez cancelado el turno.
        $preference->expires = true;
        $preference->expiration_date_from = date("c", time());
        $preference->expiration_date_to = date("c", strtotime('+' . ($general['mercadoPago_limite'] - 5) . ' minutes'));

        //Le pasamos el nombre del negocio así no se asusta cuando ve el resumen de la tarjeta
        $preference->statement_descriptor = "Turnos.app - " . $general['nombreCliente'];;


        $preference->back_urls = array(
            "success" => 'https://' . $general['clientDomain'] . '/pago.php?st=1',
            "failure" => 'https://' . $general['clientDomain'] . '/pago.php?st=0',
            "pending" => 'https://' . $general['clientDomain'] . '/pago.php?st=2'
        );

        $preference->items = array($item);
        $preference->save();

        // Guardar en el log
        db_log('homeTurnos', 'generarLinkDePago_MP', $idTurno);

        return $preference->init_point;
    }

    /* ----------------- */
    /*      SELAILU      */
    /* ----------------- */
    public static function selailu__generarLinkPago($idCuentaCorrienteLog, $pago){

        GLOBAL $general;

        // Guardo el registro del pago y el monto
        db_insert("INSERT INTO pagos (idTurno, cantidadPago) VALUES ($idCuentaCorrienteLog, '{$pago}') ON DUPLICATE KEY UPDATE cantidadPago='{$pago}'");

        $dataPaciente = db_getOne("SELECT p.* FROM pacientes p, cuentacorriente_log ccl WHERE p.idPaciente = ccl.idPaciente AND ccl.idCuentaCorrienteLog = {$idCuentaCorrienteLog}");

        require_once('/home/master/lib/vendor/autoload.php');
        MercadoPago\SDK::setAccessToken($general['mercadoPago_accessToken']);
        MercadoPago\SDK::setPlatformId($general["mercadoPago_plataformId"]);

        $preference = new MercadoPago\Preference();

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();
        $item->title = 'Turno ' . $general['nombreCliente'];;
        $item->quantity = 1;
        $item->unit_price = floatval($pago);

        $payer = new MercadoPago\Payer();
        $payer->name = $dataPaciente->nombre;
        $payer->surname = $dataPaciente->apellido;
        if ($dataPaciente->mail) {
            $payer->email = $dataPaciente->mail;
        }
        if ($dataPaciente->telefono) {
            $payer->phone = array(
                "area_code" => $dataPaciente->codArea,
                "number" => $dataPaciente->telefono
            );
        }

        $payer->identification = array(
            "type" => "DNI",
            "number" => $dataPaciente->dni
        );

        $preference->payment_methods = array(
            "excluded_payment_types" => array(
                array("id" => "ticket"),
                array("id" => "atm")
            ),
        );

        // Si viene un valor total envio todos los ids de los turnos como string en el external_reference
        $preference->external_reference = $idCuentaCorrienteLog;
        $preference->notification_url = 'https://' . $general['clientDomain'] . '/inc/cron.php?source_news=ipn';
        $preference->auto_return = 'all';
        $preference->binary_mode= true;

        //Configuramos el vencimiento para que no pueda entrar el pago una vez cancelado el turno.
        $preference->expires = true;
        $preference->expiration_date_from = date("c", time());
        $preference->expiration_date_to = date("c", strtotime('+' . ($general['mercadoPago_limite'] - 5) . ' minutes'));

        //Le pasamos el nombre del negocio así no se asusta cuando ve el resumen de la tarjeta
        $preference->statement_descriptor = "Turnos.app - " . $general['nombreCliente'];;


        $preference->back_urls = array(
            "success" => 'https://' . $general['clientDomain'] . '/pago.php?st=1',
            "failure" => 'https://' . $general['clientDomain'] . '/pago.php?st=0',
            "pending" => 'https://' . $general['clientDomain'] . '/pago.php?st=2'
        );

        $preference->items = array($item);
        $preference->save();

        // Guardar en el log
        db_log('homeTurnos', 'generarLinkDePago_MP', $idCuentaCorrienteLog);

        return $preference->init_point;
    }

    /**
     * 
     *      DANZAS LORELEY
     * 
     * 
     * @param list $idTurnos
     * @param int $total
     * @return string $link
     */
    public static function danzasLoreley__generarLinkPago($idTurnos, $total){

        GLOBAL $general;

        /* 
        // Para hacer pruebas con cupos
        - Numero:   4509 9535 6623 3704
        - Codigo:   123
        - Fecha:    11/25
        */
        $tokenMercadoPago = $general['mercadoPago_accessToken'];
        if (in_array($_SERVER['REMOTE_ADDR'], ['138.121.84.107', '190.31.193.129', "190.231.245.234", "181.94.40.6"])) {
            $tokenMercadoPago = 'TEST-6682145068429258-010609-5c98dd1df0126588a93e2984f54f90e9__LD_LA__-93379560';
        }
            
            
        require_once('/home/master/lib/vendor/autoload.php');
        MercadoPago\SDK::setAccessToken($tokenMercadoPago);
        MercadoPago\SDK::setPlatformId($general["mercadoPago_plataformId"]);

        $preference = new MercadoPago\Preference();

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();

        $item->title = 'Turno ' . $general['nombreCliente'];
        $item->quantity = 1;
        $item->unit_price = floatval($total);


        //      DATA PACIENTE
        $dataPaciente = db_getOne("SELECT p.* FROM pacientes p, turnos t WHERE p.idPaciente = t.idPaciente AND t.idTurno = {$idTurnos[0]} ");

        $payer = new MercadoPago\Payer();
        $payer->name = $dataPaciente->nombre;
        $payer->surname = $dataPaciente->apellido;
        if ($dataPaciente->mail) {
            $payer->email = $dataPaciente->mail;
        }
        if ($dataPaciente->telefono) {
            $payer->phone = array(
                "area_code" => $dataPaciente->codArea,
                "number" => $dataPaciente->telefono
            );
        }

        $payer->identification = array(
            "type" => "DNI",
            "number" => $dataPaciente->dni
        );

        $preference->payment_methods = array(
            "excluded_payment_types" => array(
                array("id" => "ticket"),
                array("id" => "atm")
            ),
        );

        // Si viene un valor total envio todos los ids de los turnos como string en el external_reference
        $preference->external_reference = implode("a", $idTurnos);



        $preference->notification_url = 'https://' . $general['clientDomain'] . '/inc/cron.php?source_news=ipn';
        $preference->auto_return = 'all';

        if($general["mercadoPago_activar_binary_mode"]){
            $preference->binary_mode= true;
        }else{
            $preference->binary_mode= false;
        }


        //Configuramos el vencimiento para que no pueda entrar el pago una vez cancelado el turno.
        $preference->expires = true;
        $preference->expiration_date_from = date("c", time());
        $preference->expiration_date_to = date("c", strtotime('+' . ($general['mercadoPago_limite'] - 5) . ' minutes'));

        //Le pasamos el nombre del negocio así no se asusta cuando ve el resumen de la tarjeta
        $preference->statement_descriptor = "Turnos.app - " . $general['nombreCliente'];


        $preference->back_urls = array(
            "success" => 'https://' . $general['clientDomain'] . '/pago.php?st=1',
            "failure" => 'https://' . $general['clientDomain'] . '/pago.php?st=0',
            "pending" => 'https://' . $general['clientDomain'] . '/pago.php?st=2'
        );

        $preference->items = array($item);
        $preference->save();

        // Guardar en el log
        db_log('homeTurnos', 'mercadoPago_generarLink', $idTurnos[0]);

        return $preference->init_point;
    }

    public static function arenas__generarLinkPago($idTurno, $pago, $tratamiento, $valorTotal = null, $idGrupo){

        GLOBAL $general, $row, $tot, $res;
        GLOBAL $row1, $row2, $tot2;

        /* Util::printVar([$valorTotal, str_replace(",", "a", $idTurno), $idTurno], "190.231.87.219"); */

        $aPagar = 0;

        if ($pago == 'sena') {
            $aPagar = intval($general['mercadoPago_sena']);
        } else {
            if ($general['mercadoPago_servicios']) {
                db_query(2, "SELECT cantidad FROM tratamientos_valores WHERE idTratamiento='{$tratamiento}' AND fechaAlta<=NOW() ORDER by fechaAlta DESC limit 1");
                $aPagar = $row2['cantidad'];
            } else {
                $aPagar = intval($general['mercadoPago_sena']);
            }
        }

        // Calculo por seña del servicio
        if ($pago == 'sena' && $general['mercadoPago_servicios_sena']) {
            $precioTratamiento = intval(TratamientoController::getPrice($tratamiento));
            $porcentajeSena = intval($general['mercadoPago_servicios_sena_porcentaje']) / 100;
            $aPagar = $precioTratamiento * $porcentajeSena;
            /* Util::printVar([$tratamiento, $precioTratamiento, $porcentajeSena, $aPagar], "181.12.34.119", false); */
        }

        /* Util::printVar([$idTurno, $pago, $tratamiento], "181.99.172.180", false); */

        // Calcula le precio del servicio y si es el primer turno del paciente le hace un descuento del 50%
        if($general["mercadoPago_servicios_sena__porcentaje_primerTurno"]){
            db_query(2, "SELECT * FROM turnos WHERE idTurno = {$idTurno} LIMIT 1");
            $idPaciente = $row2["idPaciente"];

            // Total del servicio
            db_query(2, "SELECT cantidad FROM tratamientos_valores WHERE idTratamiento='{$tratamiento}' AND fechaAlta<=NOW() ORDER by fechaAlta DESC limit 1");
            $aPagar = $row2['cantidad'];

            db_query(2, "SELECT * FROM turnos WHERE idPaciente = {$idPaciente}");
            $aPagar = $aPagar * ($general["mercadoPago_servicios_sena__porcentaje_primerTurno"]/100);
        }

        /* Util::printVar([$aPagar, $idPaciente, $general["mercadoPago_servicios_sena__porcentaje_primerTurno"]], "181.99.172.180"); */

        // Si viene un valorTotal es porque hay varios turnos
        if ($valorTotal) {
            $aPagar = $valorTotal;
        }

        // Si viene un valorTotal entonces vienen varios ids de turnos
        // Todos los ids de turnos pertenecen al mismo paciente
        if ($valorTotal) {
            // Convierto los ids que me llegan en array
            $idsTurnosInArray = $idTurno;

            // Obtengo el primer id
            $idsTurnos = $idsTurnosInArray[0];

            $idTurno = $idsTurnosInArray[0];

            $dataBoletos  = array();
            foreach (db_getAll("SELECT * FROM boletos") as $boleto) {
                $dataBoletos[$boleto->idBoleto] = $boleto;
            }

            foreach ($idsTurnosInArray as $id) {
                $dataTurno = db_getOne("SELECT * FROM turnos WHERE idTurno = ".$id);
                $montoPorTurno = $dataBoletos[$dataTurno->idBoleto]->precio;
                
                db_insert("INSERT INTO pagos (idTurno, cantidadPago,idGrupo) VALUES ($id, '{$montoPorTurno}','{$idGrupo}') ON DUPLICATE KEY UPDATE cantidadPago='{$montoPorTurno}'");
            }

        } else {
            $idsTurnos = $idTurno;
            db_insert("INSERT INTO pagos (idTurno, cantidadPago,idGrupo) VALUES ($idTurno, '{$aPagar}','{$idGrupo}') ON DUPLICATE KEY UPDATE cantidadPago='{$aPagar}'");
        }

        /* Util::printVar([$aPagar, $pago, $general['mercadoPago_servicios_sena'], $valorTotal], "181.12.34.119", false); */


        /* Obtener el id del paciente */
        db_query(0, "SELECT p.* FROM pacientes p, turnos t WHERE p.idPaciente = t.idPaciente AND t.idTurno = '" . $idsTurnos . "' LIMIT 1");
        $paciente = $row["idPaciente"];


        
        // Token mercado pago
        $tokenMercadoPago = $general['mercadoPago_accessToken'];
        if ($general['mercadoPago_accessToken_por_profesional']) {
            db_query(
                1,
                "SELECT 
                    p.* 
                FROM 
                    profesionales p, 
                    turnos t, 
                    ordenes o 
                WHERE 
                    t.idOrden = o.idOrden AND 
                    o.idProfesional = p.idProfesional AND 
                    t.idTurno = " . $idsTurnos . " 
                "
            );
            $tokenMercadoPago = $row1["access_token"];
        }

        /* 
            // Para hacer pruebas con cupos
            - Numero:   4509 9535 6623 3704
            - Codigo:   123
            - Fecha:    11/25
         */
        /* if (in_array($_SERVER['REMOTE_ADDR'], ["181.12.33.186", "181.93.159.206"])) {
            $tokenMercadoPago = 'TEST-6682145068429258-010609-5c98dd1df0126588a93e2984f54f90e9__LD_LA__-93379560';
        } */

        
        require_once('/home/master/lib/vendor/autoload.php');
        MercadoPago\SDK::setAccessToken($tokenMercadoPago);
        MercadoPago\SDK::setPlatformId($general["mercadoPago_plataformId"]);

        $preference = new MercadoPago\Preference();

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();

        $titleMP = 'Turno ' . $general['nombreCliente'];
        if($general["mercadoPago_agregarNombreServicioAlDetalleDeCompra"]){
            $dataServicioTurno = db_getOne("SELECT trat.nombre FROM turnos t, ordenes o , tratamientos trat WHERE t.idOrden = o.idOrden AND o.idTratamiento = trat.idTratamiento AND t.idTurno IN ({$idTurno})");
            $nombreServicio = $dataServicioTurno->nombre;
            $titleMP .= " - ".$nombreServicio;
        }
        $item->title = $titleMP;
        $item->quantity = 1;
        $item->unit_price = floatval($aPagar);

        db_query(0, "select p.nombre, p.apellido, p.mail, p.codArea, p.telefono, p.dni from pacientes p, turnos t where p.idPaciente='" . $paciente . "'=t.idPaciente and t.idTurno='" . $idTurno . "'");

        $payer = new MercadoPago\Payer();
        $payer->name = $row['nombre'];
        $payer->surname = $row['apellido'];
        if ($row['mail']) {
            $payer->email = $row['mail'];
        }
        if ($row['telefono']) {
            $payer->phone = array(
                "area_code" => $row['codArea'],
                "number" => $row['telefono']
            );
        }

        $payer->identification = array(
            "type" => "DNI",
            "number" => $row['dni']
        );

        $preference->payment_methods = array(
            "excluded_payment_types" => array(
                array("id" => "ticket"),
                array("id" => "atm")
            ),
        );

        // Si viene un valor total envio todos los ids de los turnos como string en el external_reference
        $preference->external_reference = $valorTotal ? str_replace(",", "a", $idTurno) : $idTurno;


        $preference->notification_url = 'https://' . $general['clientDomain'] . '/inc/cron.php?source_news=ipn';
        $preference->auto_return = 'all';

        if($general["mercadoPago_activar_binary_mode"]){
            $preference->binary_mode= true;
        }else{
            $preference->binary_mode= false;
        }


        //Configuramos el vencimiento para que no pueda entrar el pago una vez cancelado el turno.
        $preference->expires = true;
        $preference->expiration_date_from = date("c", time());
        $preference->expiration_date_to = date("c", strtotime('+' . ($general['mercadoPago_limite'] - 5) . ' minutes'));

        //Le pasamos el nombre del negocio así no se asusta cuando ve el resumen de la tarjeta
        $preference->statement_descriptor = "Turnos.app - " . $general['nombreCliente'];


        $preference->back_urls = array(
            "success" => 'https://' . $general['clientDomain'] . '/pago.php?st=1',
            "failure" => 'https://' . $general['clientDomain'] . '/pago.php?st=0',
            "pending" => 'https://' . $general['clientDomain'] . '/pago.php?st=2'
        );

        $preference->items = array($item);
        $preference->save();

        /* if($_SERVER["REMOTE_ADDR"] == "190.231.247.203"){
            Util::printVar($preference);
        } */

        // Guardar en el log
        db_log('homeTurnos', 'generarLinkDePago_MP_externalReferenc', $valorTotal ? str_replace(",", "a", $idTurno) : $idTurno);
        db_log('homeTurnos', 'generarLinkDePago_MP', $idTurno);

        return $preference->init_point;
    }

    public static function areanas_generarLinkPagoReserva($aPagar, $idTurno){
        // Token mercado pago
        GLOBAL $general, $row;

        $tokenMercadoPago = $general['mercadoPago_accessToken'];

        require_once('/home/master/lib/vendor/autoload.php');
        MercadoPago\SDK::setAccessToken($tokenMercadoPago);
        MercadoPago\SDK::setPlatformId($general["mercadoPago_plataformId"]);

        $preference = new MercadoPago\Preference();

        // Crea un ítem en la preferencia
        $item = new MercadoPago\Item();
        $item->title = 'Turno ' . $general['nombreCliente'];
        $item->quantity = 1;
        $item->unit_price = floatval($aPagar);

        db_query(0, "select p.nombre, p.apellido, p.mail, p.codArea, p.telefono, p.dni from pacientes p, turnos t where p.idPaciente=t.idPaciente and t.idTurno='" . $idTurno . "'");

        $payer = new MercadoPago\Payer();
        $payer->name = $row['nombre'];
        $payer->surname = $row['apellido'];
        if ($row['mail']) {
            $payer->email = $row['mail'];
        }
        if ($row['telefono']) {
            $payer->phone = array(
                "area_code" => $row['codArea'],
                "number" => $row['telefono']
            );
        }

        $payer->identification = array(
            "type" => "DNI",
            "number" => $row['dni']
        );

        $preference->payment_methods = array(
            "excluded_payment_types" => array(
                array("id" => "ticket"),
                array("id" => "atm")
            ),
        );

        // Si viene un valor total envio todos los ids de los turnos como string en el external_reference
        $preference->external_reference = $idTurno;


        $preference->notification_url = 'https://' . $general['clientDomain'] . '/inc/cron.php?source_news=ipn';
        $preference->auto_return = 'all';

        if($general["mercadoPago_activar_binary_mode"]){
            $preference->binary_mode= true;
        }else{
            $preference->binary_mode= false;
        }


        //Configuramos el vencimiento para que no pueda entrar el pago una vez cancelado el turno.
        $preference->expires = true;
        $preference->expiration_date_from = date("c", time());
        $preference->expiration_date_to = date("c", strtotime('+ 48 hours'));

        //Le pasamos el nombre del negocio así no se asusta cuando ve el resumen de la tarjeta
        $preference->statement_descriptor = "Turnos.app - " . $general['nombreCliente'];


        $preference->back_urls = array(
            "success" => 'https://' . $general['clientDomain'] . '/pago.php?st=1',
            "failure" => 'https://' . $general['clientDomain'] . '/pagoAux.php?st=0',
            "pending" => 'https://' . $general['clientDomain'] . '/pago.php?st=2'
        );

        $preference->items = array($item);
        $preference->save();

        // Guardar en el log
        db_log('homeTurnos', 'generarLinkDePagoAux_MP_externalReferenc', $idTurno);
        db_log('homeTurnos', 'generarLinkDePagoAux_MP', $idTurno);

        return $preference->init_point;
    }
}
