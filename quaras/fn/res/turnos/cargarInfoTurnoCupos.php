<?
require_once($_SERVER["DOCUMENT_ROOT"].'/inc/fn.php');
AuthController::checkLogin();

db_query(0,"SELECT simultaneos FROM tratamientos WHERE idTratamiento = '{$_POST['idTratamiento']}' LIMIT 1");
$cupo=$row['simultaneos'];

db_query(0,
    "SELECT 
        t.idTurno, 
        p.idPaciente, 
        p.nombre, 
        p.apellido, 
        p.codArea, 
        p.telefono, 
        p.mail, 
        tra.simultaneos 
    FROM 
        pacientes p, 
        turnos t, 
        ordenes o, 
        tratamientos tra 
    WHERE 
        t.idOrden=o.idOrden AND 
        t.idPaciente=p.idPaciente AND 
        o.idOrden=t.idOrden AND 
        t.estado=0 AND 
        t.eliminado<>1 AND 
        t.fechaInicio='{$_POST['fechaInicio']}' AND 
        fechaFin='{$_POST['fechaFin']}' AND 
        tra.idTratamiento=o.idTratamiento AND 
        o.idProfesional = '".$_POST['idProfesional']."' AND 
        o.idTratamiento = '".$_POST['idTratamiento']."' 
    ORDER BY 
        p.nombre, p.apellido
");

ob_start();
?>
<p>
    Visualice en el listado a continuación los turnos tomados de la <?=$general['nombreProfesional']?> y agregue nuevos participantes.
</p>
<div class="table-responsive">
    <table class="table table-striped" id="tablaParticipantes">
        <thead>
            <tr>
                <th>
                    Nombre
                </th>
                <th>
                    Apellido
                </th>
                <th>
                    Teléfono
                </th>
                <th>
                    Mail
                </th>
                <th>
                    
                </th>
            </tr>
        </thead>
        <tbody>
            <?
            $pacientesInscriptos=array();
            for($i=0;$i<$tot;$i++){
                $nres=$res->data_seek($i);
                $row=$res->fetch_assoc();
				
                $pacientesInscriptos[$row['idPaciente']]=1;
				
                $telefono=$general['prefijoTelefonico'].$row['codArea'].$row['telefono'];
                ?>
                <tr>
                    <td>
                        <?=$row['nombre']?>
                    </td>
                    <td>
                        <?=$row['apellido']?>
                    </td>
                    <td>
                        <a href="https://wa.me/<?=$telefono?>" target="_blank" data-toggle="tooltip" data-placement="bottom" title="Enviar Whatsapp al <?=$general['nombrePaciente']?>"><i class="fab fa-whatsapp"></i></a> +<?=$telefono?>
                    </td>
                    <td>
                        <?=$row['mail']?>
                    </td>
                    <td>
                        <button class="btn btn-danger m-0" onclick="eliminarTurnoModal(<?=$row['idTurno']?>)"><i class="fa fa-trash m-0 fa-fw"></i></button>
                    </td>
                </tr>
                <?
            }
            ?>
        </tbody>
    </table>
</div>
<?
$response['body']=ob_get_contents();
ob_end_clean();

ob_start();
if($_POST['fechaInicio']>date("Y-m-d H:i:s")){
    if($tot<$cupo){
    ?>
    <form id="inscribirPaciente" class="w-100">
        <div class="row mb-2">
            <div class="col">
                <h6><b>Agregar nuevo participante</b></h6>
                <label>Ingrese el nombre del <?=$general['nombrePaciente']?> que desee agregar a la <?=$general['nombreProfesional']?></label>
                <div class="form-group">

                    <select class="selectPaciente form-control" name="paciente">
                        <option value="">Seleccione...</option>
                        <option value="NN">Nuevo <?=($general['nombrePaciente'])?></option>
                    </select>

                </div>
            </div>
        </div>
        <div class="row nuevoPaciente d-none">
            <div class="col-4">
                <div class="form-group">
                    <label class="control-label">Nombre</label>
                    <input class="form-control required" type="text" placeholder="" name="nombre" id="nombre" value="">
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    <label class="control-label">Apellido</label>
                    <input class="form-control required" type="text" placeholder="" name="apellido" id="apellido" value="">
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    <label class="control-label">Teléfono</label>
                    <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">+<?=$general['prefijoTelefonico']?></span>
                    </div>
                    <input class="form-control required soloNumeros" name="codArea" id="codArea" />
                    <input class="form-control required soloNumeros" type="tel" placeholder="Número" name="telefono" id="telefono" value="" minlength="<?=$general['telLargoMin']?>" maxlength="<?=$general['telLargoMax']?>">
                    </div>
                    <small><?=$general['leyendaTelefono']?></small>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label class="control-label">E-mail</label>
                    <input class="form-control" type="email" placeholder="nombre@dominio.com" name="mail" value="" id="mail">
                </div>
            </div>
        </div>
        <input type="hidden" name="action" value="saveExternal" />
        <input type="hidden" name="fecha" value="<?=date("d/m/Y", strtotime($_POST['fechaInicio']))?>" />
        <input type="hidden" name="horas" value="<?=date("H:i", strtotime($_POST['fechaInicio']))?>" />
        <input type="hidden" name="idProfesional" value="<?=$_POST['idProfesional']?>" />
        <input type="hidden" name="idTratamiento" value="<?=$_POST['idTratamiento']?>" />
        <input type="hidden" name="idPaciente" id="idPaciente" value="" />
        
        <div class="w-100 row mx-0">
            <div class="col-3 px-0 pr-3">
                <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
            </div>
            <div class="col-9 px-0 text-right d-flex align-items-center justify-content-end">
                <button class="btn btn-primary btn-block" id="agregarPaciente"><i class="fas fa-user-plus"></i> Agregar <?=$general['nombrePaciente']?></button>
            </div>
        </div>
    </form>
    <?
    }else{
        ?>
        <div class="w-100 row">
            <div class="col-9">
                <div><i class="fa fa-times-circle text-danger"></i> La actividad se encuentra completa.</div>
            </div>
            <div class="col-3 text-right d-flex align-items-center justify-content-end">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
            </div>
        </div>
        <?
    }
}else{
    ?>
    <div class="w-100 row">
        <div class="col-9">
            <div><i class="fas fa-clock text-success"></i> La actividad ya ha finalizado.</div>
        </div>
        <div class="col-3 text-right d-flex align-items-center justify-content-end">
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
        </div>
    </div>
    <?
}
?>
</div>
<?
$response['footer']=ob_get_contents();
ob_end_clean();

HTTPController::responseInJSON($response);
?>