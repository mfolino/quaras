<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

if((@!$general['mercadoPago']) || (@$_GET['st']=='') || (@$_GET['external_reference']=='') || !$general['paypal'] ){
    http_response_code(403);
    die();
}



/* 
    Si el external_reference no es un numero (id), 
    entonces son varios ids encodeados en base 64.


    $_GET[st] == 0 | No procesado = error
    $_GET[st] == 1 | Confirmado 
    $_GET[st] == 2 | Pendiente
    $_GET[st] == 3 | paypal | Confirmado
    $_GET[st] == 4 | paypal | Pago cancelado
*/
/* Util::printVar($_GET); */


$idTurno = $_GET['external_reference'];

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion=ucwords($general['nombreCliente']);
        $subseccion='Pagar';
        require_once('inc/head.php');
        ?>
    </head>

    <body>
        <section class="material-half-bg">
            <div class="cover"></div>
        </section>
        <section class="login-content">
            <div class="logo">
                <img src="img/<?=$general['isologo']?>" width="<?=$general['logoWidth']?>" />
            </div>
            <div class="login-box" style="min-height:auto">
                <form class="nuevoTurno-form" id="formulario">
                    <div class="login-head text-center">
                        <?
                        switch($_GET['st'])
                        {
                            // Mercado pago
                            case 1:

                                ?>
                                <h1><i class="fa fa-check-circle fa-2x text-success"></i></h1>
                                <h3>Turno confirmado</h3>

                                <?
                                $paragraph = 'En breve te llegara un correo con toda la información. Muchas gracias.';
                            
                                db_query(0,
                                "SELECT estado
                                FROM turnos
                                WHERE idTurno = '{$idTurno}'
                                LIMIT 1");

                                $estadoAnterior = $row['estado'];

                                //Si el turno en cuestion no estaba ya cancelado, lo damos por pago y pasamos a estado pendiente
                                if($estadoAnterior != '3')
                                {
                                    db_update(
                                        "UPDATE turnos
                                        SET estado = '0'
                                        WHERE idTurno = '{$idTurno}'"
                                    );
                                    
                                    db_update(
                                        "UPDATE pagos
                                        SET estadoPago = 'accredited', fechaPago = NOW(), idPago = '{$_GET['payment_id']}', pago = '1'
                                        WHERE idTurno = '{$idTurno}'"
                                    );
                                
                                    db_log('pago', 'marcoTurnoPagado', $idTurno);

                                    if($estadoAnterior != 1)
                                    {
                                        require_once($_SERVER["DOCUMENT_ROOT"].'/inc/mailConfirmacion.php');
                                        db_log('pago', 'mandoMailConfirmacion', $idTurno);
                                    }
                                }

                                break;
                        
                            case 0:
                                ?>
                                <h1><i class="fa fa-times-circle fa-2x text-danger"></i></h1>
                                <h3>Lo sentimos!</h3>

                                <?
                                $paragraph = 'Tu pago no ha podido ser procesado. El turno solicitado ha sido cancelado. Volvé a intentarlo nuevamente tomando un nuevo turno.';
                            
                                db_update(
                                    "UPDATE turnos
                                    SET estado = '3'
                                    WHERE idTurno = '{$idTurno}'"
                                );

                                db_update(
                                    "UPDATE pagos
                                    SET estadoPago = 'failure', fechaPago = NOW(), idPago = '{$_GET['payment_id']}'
                                    WHERE idTurno = '{$idTurno}'");
                            
                                db_log('pago', 'canceloPagoRechazado', $idTurno);

                                break;

                            case 2:
                                ?>
                                <h1><i class="fa fa-clock-o fa-2x text-warning"></i></h1>
                                <h3>Pago pendiente</h3>

                                <?
                                db_update(
                                    "UPDATE turnos
                                    SET estado = '9'
                                    WHERE idTurno = '{$idTurno}'"
                                );

                                db_update(
                                    "UPDATE pagos
                                    SET estadoPago = 'pending', fechaPago = NOW(), idPago = '{$_GET['payment_id']}'
                                    WHERE idTurno = '{$idTurno}'"
                                );
                            
                                db_log('pago', 'marcoPagoPendiente', $idTurno);
                            
                                $paragraph = 'Tu pago aún no ha sido confirmado.<br>Apenas se confirme el mismo recibirás un correo con toda la información.<br>Te pedimos que seas paciente y no intentes tomar otro turno.';
                                break;
                                

                            // Paypal
                            case 3:
                                ?>
                                <h1><i class="fa fa-check-circle fa-2x text-success"></i></h1>
                                <h3>Turno confirmado</h3>

                                <?

                                $paragraph = 'En breve te llegara un correo con toda la información. Muchas gracias.';
                                db_query(0,
                                "SELECT estado
                                FROM turnos
                                WHERE idTurno = '{$idTurno}'
                                LIMIT 1");

                                $estadoAnterior = $row['estado'];

                                //Si el turno en cuestion no estaba ya cancelado, lo damos por pago y pasamos a estado pendiente
                                if($estadoAnterior != '3')
                                {
                                    db_update(
                                        "UPDATE turnos
                                        SET estado = '0'
                                        WHERE idTurno = '{$idTurno}'"
                                    );

                                    db_insert(
                                        "UPDATE 
                                            pagos 
                                        SET
                                            idTurno= {$idTurno},
                                            estadoPago = 'accredited', 
                                            idPago = '".$_GET['idOrdenPaypal']."',
                                            fechaPago = NOW(),
                                            pago = '1'
                                        WHERE 
                                            idTurno = '{$idTurno}'
                                    ");

                                    db_log('pago', 'marcoTurnoPagado', $idTurno);

                                    if($estadoAnterior != 1)
                                    {
                                        require_once($_SERVER["DOCUMENT_ROOT"].'/inc/mailConfirmacion.php');
                                        db_log('pago', 'mandoMailConfirmacion', $idTurno);
                                    }
                                }

                                break;
                            case 4:
                                ?>
                                <h1><i class="fa fa-clock-o fa-2x text-warning"></i></h1>
                                <h3>Pago Incompleto</h3>

                                <?

                                // Cancelo el turno
                                db_update(
                                    "UPDATE turnos
                                    SET estado = '3'
                                    WHERE idTurno = '{$idTurno}'"
                                );

                                db_update(
                                    "UPDATE pagos
                                    SET estadoPago = 'failure', fechaPago = NOW() 
                                    WHERE idTurno = '{$idTurno}'"
                                );

                                db_log('pago', 'canceloPagoRechazado', $idTurno);

                                $paragraph = 'El pago no fue efectuado. El turno solicitado ha sido cancelado. Vuelve a intentarlo nuevamente.';
                                break;
                        }
                        ?>
                    </div>

                    <p class="mt-3 mb-0 font-weight-normal text-center"><?=$paragraph?></p>
                    <a class="btn btn-primary btn-block mt-5" href="/"><i class="fa fa-home"></i> Volver al inicio</a>
				
                </form>

            </div>
            <?
            require_once($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
            ?>
        </section>

        <?
        require_once('inc/scripts.php');
        ?>
		
    </body>
</html>