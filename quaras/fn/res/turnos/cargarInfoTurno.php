<?
require_once($_SERVER["DOCUMENT_ROOT"].'/inc/fn.php');
AuthController::checkLogin();


if($general['cupos']){
    include("cargarInfoTurnoCupos.php");
    die();
}

$diaSemana['Monday']='Lunes';
$diaSemana['Tuesday']='Martes';
$diaSemana['Wednesday']='Miercoles';
$diaSemana['Thursday']='Jueves';
$diaSemana['Friday']='Viernes';
$diaSemana['Saturday']='Sabado';
$diaSemana['Sunday']='Domingo';

db_query(0, "SHOW TABLES LIKE 'pagos'");
if($tot>0){
    db_query(0,"select p.codArea, p.telefono, p.mail, p.nombre, p.apellido, o.*, t.*, tra.nombre as tratamiento, pro.nombre as profesional, pa.cantidadPago, pa.pago, pa.idPago from pacientes p, ordenes o, tratamientos tra, profesionales pro, turnos t left join pagos pa on pa.idTurno=t.idTurno where t.idTurno='".$_POST['idTurno']."' and t.idPaciente=p.idPaciente and t.idOrden=o.idOrden and pro.idProfesional=o.idProfesional and tra.idTratamiento=o.idTratamiento limit 1");
}else{
    db_query(0,"select p.codArea, p.telefono, p.mail, p.nombre, p.apellido, o.*, t.*, tra.nombre as tratamiento, pro.nombre as profesional from pacientes p, ordenes o, tratamientos tra, profesionales pro, turnos t where t.idTurno='".$_POST['idTurno']."' and t.idPaciente=p.idPaciente and t.idOrden=o.idOrden and pro.idProfesional=o.idProfesional and tra.idTratamiento=o.idTratamiento limit 1");
}

if($tot>0){
    ob_start();
    ?>
    <h6><?=ucwords($general['nombrePaciente'])?></h6>
    <div class="row infoPaciente">

        <div class="col">
            <i class="fa fa-user"></i> <?=$row['nombre']?> <?=$row['apellido']?>
        </div>
        <div class="col">
            <a href="tel:<?=$general['prefijoTelefonico'].$row['codArea'].$row['telefono']?>"><i class="fa fa-phone"></i></a> (<?=$general['prefijoTelefonico']?>) <?=$row['codArea']?> <?=$row['telefono']?> <a href="//wa.me/<?=$general['prefijoTelefonico'].$row['codArea'].$row['telefono']?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
        </div>
        <div class="col">
            <a href="mailto:<?=$row['mail']?>"><i class="fa fa-envelope"></i> <?=$row['mail']?></a>
        </div>
    </div>
    <?
    if(isset($row['fechaCumpleanos']) && $row['fechaCumpleanos'] != '0000-00-00' && date('m-d')==date('m-d',strtotime($row['fechaCumpleanos']))){
        ?>
        <div class="row infoTurno">
            <div class="col text-primary">
                <i class="fa fa-birthday-cake text-primary"></i> Hoy es su cumpleaños!
            </div>
        </div>
        <?
    }
    ?>
    <h6 style="border-top:1px solid #e9e9e9" class="mt-2 pt-2"><?=ucwords($general['nombreObraSocial'])?></h6>
    <div class="row infoTurno mb-2">
        <div class="col">
            <i class="fa fa-list"></i> <?=ucwords($row['tratamiento'])?>
        </div>
        <div class="col">
            <i class="fa fa-medkit"></i> <?=$row['profesional']?>
        </div>
        <?

        if(($row['pago'])and($row['cantidadPago'])){
            ?>
            <div class="col">
                <div>
                    <i class="fas fa-money-bill"></i> $<?=$row['cantidadPago']?>
                </div>
                <small>ID Pago: <?=$row['idPago']?></small>
            </div>
            <?
        }
        ?>
    </div>

    <? 
    if($general["google_meet"]){
        db_query(9, "SHOW TABLES LIKE 'turnos_meetings'");
        if($tot9>0){
            db_query(9, "SELECT tm.link, t.tipo  FROM turnos_meetings tm, tratamientos t WHERE tm.idProfesional={$row['idProfesional']} AND tm.idTratamiento={$row['idTratamiento']} AND tm.fechaInicio='{$row['fechaInicio']}' AND tm.estado='A' and tm.plataforma='meet' AND t.idTratamiento = tm.idTratamiento LIMIT 1");
            if($tot9>0 && $row9["tipo"] == 'V'){
                ?>
                <h6 style="border-top:1px solid #e9e9e9" class="mt-2 pt-2"><?= $general["mail_confirmacion_meeting_textoDelLink"] ? $general["mail_confirmacion_meeting_textoDelLink"] : "Link de la reunión" ?></h6>
                <div class="row infoTurno mb-2">
                    <div class="col text-primary">
                        <a href="<?=$row9['link']?>" target="_blank"><i class="fab fa-google"></i> Unirse</a>
                    </div>
                </div>
                <? 
            }
        }
    }
        
    db_query(2, "SHOW TABLES LIKE 'turnos_observaciones'");
    if($tot2>0){
        db_query(2,"select observaciones from turnos_observaciones where idTurno='".$row['idTurno']."' limit 1");
    }

    if($general['campoObservaciones']>1){
        ?>
        <h6 style="border-top:1px solid #e9e9e9" class="mt-2 pt-2"><?=ucwords($general['campoObservaciones_titulo'])?></h6>
        <div class="row infoTurno mb-2">
            <div class="col">
                <textarea id="comentarios" class="form-control" rows="3"><?=$row2['observaciones']?></textarea>
                <button class="btn btn-primary btn-block mt-2" onclick="guardarComentarios(<?=$row['idTurno']?>)"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
        <?
    }else{
        if($tot2>0){
            ?>
            <div class="row infoTurno mb-2">
                <div class="col">
                    <h6 style="border-top:1px solid #e9e9e9" class="mt-2 pt-2"><?=$general['campoObservaciones_titulo']?></h6>
                    <p><?=$row2['observaciones']?></p>
                </div>
            </div>
            <?
        }
    }
    
    $response['body']=ob_get_contents();
    ob_end_clean();

    ob_start();
    ?>

    <div class="d-none comentariosTurno w-100 border-bottom mb-3 pb-3">
        <h5 class="text-danger"><b>Cancelar turno</b></h5>
        <div class="form-group row">
            <div class="col-12">
                <label for="comentarios" title="Este campo es opcional. En caso de completarlo se le enviará el motivo al <?=$general['nombrePaciente']?>." data-toggle="tooltip">Ingrese el motivo de la cancelación <i class="fas fa-info-circle"></i></label>
                <input type="text" class="form-control" id="comentarios" />
            </div>
        </div>
        <div class="form-group row">
            <?
            if($row['pago']){
                ?>
                <div class="col">
                    <label for="devolverPago">Devolver pago?</label>
                    <select class="form-control" id="devolverPago">
                        <option value="0">No devolver pago</option>
                        <option value="1" selected>Devolver pago</option>
                    </select>
                </div>
                <?
            }
            ?>
            <div class="col">
                <label for="mandarMail">Enviar aviso de cancelación por mail?</label>
                <select class="form-control" id="mandarMail">
                    <option value="0">No mandar aviso</option>
                    <option value="1" selected>Mandar aviso</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <button class="btn btn-secondary btn-block" onclick="abortarPreCancelar()"><i class="fas fa-xmark"></i> Cancelar</button>
            </div>
            <div class="col">
                <button class="btn btn-danger btn-block botonCancelarFinal" onclick="cancelarTurno(<?=$_POST['idTurno']?>, '<?=$_POST['title']?>', '<?=$_POST['start']?>')"><i class="far fa-calendar-xmark"></i> Cancelar turno</button>
            </div>
        </div>
    </div>




    <button type="button" class="btn btn-secondary botonCancelar" data-dismiss="modal"><i class="fas fa-xmark"></i> Cerrar</button>

    <?

    $puedeCancelar=0;

    if($row['estado']<>3 && AuthController::isAdmin()){
        if( isset($_SESSION['usuario']['limites']) && $_SESSION['usuario']['limites']<>'limitado'){
            if($row['estado']==0){
                $puedeCancelar=1;
            }
        }else{
            if($row['fechaInicio']>=date("Y-m-d H:i:s")){
                $puedeCancelar=1;
            }
        }

        if(isset($_SESSION['usuario']['limites']) && ($row['estado']<>3)and($_SESSION['usuario']['limites']<>'limitado')){
            ?>
            <div class="dropdown">
                <button class="btn btn-info dropdown-toggle botonAccion" type="button" id="cambiarProfesional" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-user"></i> <?=ucwords($general['nombreProfesional'])?>
                </button>
                <div class="dropdown-menu" aria-labelledby="cambiarProfesional">
                    <?
                    db_query(8,"select idProfesional, nombre from profesionales order by nombre");
                    for($i8=0;$i8<$tot8;$i8++){
                        $nres8=$res8->data_seek($i8);
                        $row8=$res8->fetch_assoc();
                        ?>
                            <a class="dropdown-item<?=($row['idProfesional']==$row8['idProfesional']) ? ' active' : ''?>" href="#" onclick="changeProfesional(<?=$_POST['idTurno']?>,'<?=$_POST['title']?>','<?=$_POST['start']?>','<?=$row8['idProfesional']?>','<?=$row8['nombre']?>')"><?=$row8['nombre']?></a>
                        <?
                    }
                    ?>
                </div>
            </div>
        <? } ?>

        <? if($puedeCancelar==1){ ?>
            <button type="button" class="btn btn-warning botonAccion" onclick="eliminarTurno(<?=$_POST['idTurno']?>,'<?=$_POST['title']?>','<?=$_POST['start']?>')"><i class="fas fa-trash"></i> Eliminar <?=$general['nombreTurno']?></button>
            <button type="button" class="btn btn-danger botonConfirmar botonAccion" onclick="preCancelarTurnoNew(<?=$_POST['idTurno']?>,'<?=$_POST['title']?>','<?=$_POST['start']?>')"><i class="far fa-calendar-xmark"></i> Cancelar <?=$general['nombreTurno']?></button>
        <? } ?>

        <? if((date("Y-m-d",strtotime($row['fechaInicio']))==date("Y-m-d"))or($_SESSION['usuario']['limites']<>'limitado')){
            $ahora=new DateTime(date("Y-m-d H:i:s"));
            $fechaCompara=new DateTime(date('Y-m-05 H:i:s'));
		
            $diasDiferencia=$ahora->diff($fechaCompara)->format("%a");
		
            if($diasDiferencia>7){
                $diasDiferencia=7;
            }
		
            if($_SESSION['usuario']['limites']=='administracion'){
                if($row['fechaInicio']>date("Y-m-d H:i:s",strtotime('- '.$diasDiferencia.' days'))){
                    ?>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle botonAccion" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-plus"></i> Estado
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" href="#" onclick="estadoTurno(<?=$_POST['idTurno']?>,'<?=$_POST['title']?>','<?=$_POST['start']?>',1)"><?=ucwords($general['estadoConfirmado'])?></a>
                            <a class="dropdown-item" href="#" onclick="estadoTurno(<?=$_POST['idTurno']?>,'<?=$_POST['title']?>','<?=$_POST['start']?>',2)">Ausente</a>
                        </div>
                    </div>
                    <?
                }
            }else{
                ?>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle botonAccion" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user-plus"></i> Estado
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="#" onclick="estadoTurno(<?=$_POST['idTurno']?>,'<?=$_POST['title']?>','<?=$_POST['start']?>',1)"><?=ucwords($general['estadoConfirmado'])?></a>
                        <a class="dropdown-item" href="#" onclick="estadoTurno(<?=$_POST['idTurno']?>,'<?=$_POST['title']?>','<?=$_POST['start']?>',2)">Ausente</a>
                    </div>
                </div>
                <?
            }
        }
    }

    $response['footer']=ob_get_contents();
    ob_end_clean();

    header('Content-Type: application/json');
    echo json_encode($response);

}
?>