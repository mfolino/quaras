<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();

if($general['profesional_abm_horarios']){
    AuthController::checkSuperAdminORProfesional();
}else{
    AuthController::checkSuperAdmin();
}

/* 
    ************************************************
    ************************************************
    COPIAR EL ARCHIVO DE JULES CUANDO ESTE TERMINADO
    ************************************************
    ************************************************
*/


foreach($_POST as $key => $value){
    $post[$key]=SecurityDatabaseController::cleanVar($value);
}

$dias[]='lunes';
$dias[]='martes';
$dias[]='miercoles';
$dias[]='jueves';
$dias[]='viernes';
$dias[]='sabado';
$dias[]='domingo';

db_query(0,"select * from horariosprofesionales where idProfesional='".$post['idProfesional']."' and idHoras in(select max(idHoras) from horariosprofesionales group by idProfesional, dia)");

$horarios=array();

for($i=0;$i<$tot;$i++){
    $nres=$res->data_seek($i);
    $row=$res->fetch_assoc();
    if(($row['desdeManana']<>'')and($row['desdeManana']<>'--:--')and($row['desdeManana']<>'00:00')){
        $horarios[$row['dia']]['M']['desde']=$row['desdeManana'];
        $horarios[$row['dia']]['M']['hasta']=$row['hastaManana'];
    }
    if(($row['desdeTarde']<>'')and($row['desdeTarde']<>'--:--')and($row['desdeTarde']<>'00:00')){
        $horarios[$row['dia']]['T']['desde']=$row['desdeTarde'];
        $horarios[$row['dia']]['T']['hasta']=$row['hastaTarde'];
    }
}

ob_start();
?>
<?//<h4>Editar días y horarios</h4>?>
<p>Acceda a la pestaña del día para el que desee administrar los horarios.</p>
<form id="horariosDias">
    <div class="bs-component">
        <ul class="nav nav-tabs">
            <?
            foreach($dias as $key => $dia){
                ?>
                <li class="nav-item"><a class="nav-link<?=($key==0) ? ' active' : ''?>" data-toggle="tab" href="#<?=$dia?>Tab"><?=ucwords($dia)?></a></li>
                <?
            }
            ?>
            <?//<li class="nav-item"><a class="nav-link disabled" href="#">Sábado</a></li>?>
            <?//<li class="nav-item"><a class="nav-link disabled" href="#">Domingo</a></li>?>
            <?/*<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Dropdown</a>
                <div class="dropdown-menu"><a class="dropdown-item" href="#">Action</a><a class="dropdown-item" href="#">Another action</a><a class="dropdown-item" href="#">Something else here</a>
                    <div class="dropdown-divider"></div><a class="dropdown-item" href="#">Separated link</a>
                </div>
            </li>*/?>
        </ul>
        <div class="tab-content" id="horariosSemana">
            <?
            foreach($dias as $key => $dia){
                $prenderManana=0;
                $prenderTarde=0;
				
                if(($horarios[$dia]['M']['desde']<>'')and($horarios[$dia]['M']['hasta']<>'')){
                    $prenderManana=1;
                }
                if(($horarios[$dia]['T']['desde']<>'')and($horarios[$dia]['T']['hasta']<>'')){
                    $prenderTarde=1;
                }
                ?>
                <div class="tab-pane oneDay fade<?=($key==0) ? ' active show' : ''?>" id="<?=$dia?>Tab" data-dia="<?=$dia?>">
                    <div class="mt-3 mb-3">
                        <div class="row">
                            <div class="col">
                                <p>Seleccione los turnos que desea para la <?=$general['nombreProfesional']?>. Una vez seleccionado el turno ingrese el rango horario del mismo.</p>
                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-primary<?=($prenderManana==1) ? ' active' : ''?>">
                                        <input type="checkbox" autocomplete="off" id="<?=$dia?>AM" value="1"<?=($prenderTarde==1) ? ' checked="true"' : ''?> class="prenderTurno" name="manana[]" data-turno="Manana" data-dia="<?=$dia?>"> Mañana
                                    </label>
                                    <label class="btn btn-primary<?=($prenderTarde==1) ? ' active' : ''?>">
                                        <input type="checkbox" autocomplete="off" id="<?=$dia?>PM" value="1"<?=($prenderTarde==1) ? ' checked="true"' : ''?> class="prenderTurno" name="tarde[]" data-turno="Tarde" data-dia="<?=$dia?>"> Tarde
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col<?=($prenderManana==1) ? '' : ' d-none'?>" id="<?=$dia?>Manana">
                                <div class="row">
                                    <div class="col">
                                        <h5>Mañana</h5>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Desde</span>
                                            </div>
                                            <input type="time" id="<?=$dia?>DesdeAM" name="desdeManana[]" class="form-control" min="07:30" max="14:00" value="<?=$horarios[$dia]['M']['desde']?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text">hs.</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Hasta</span>
                                            </div>
                                            <input type="time" id="<?=$dia?>HastaAM" name="hastaManana[]" class="form-control" min="08:00" max="14:01" value="<?=$horarios[$dia]['M']['hasta']?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text">hs.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col<?=($prenderTarde==1) ? '' : ' d-none'?>" id="<?=$dia?>Tarde">
                                <div class="row">
                                    <div class="col">
                                        <h5>Tarde</h5>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Desde</span>
                                            </div>
                                            <input type="time" id="<?=$dia?>DesdePM" name="desdeTarde[]" class="form-control" min="13:00" max="21:00" value="<?=$horarios[$dia]['T']['desde']?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text">hs.</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Hasta</span>
                                            </div>
                                            <input type="time" id="<?=$dia?>HastaPM" name="hastaTarde[]" class="form-control" min="13:30" max="21:01" value="<?=$horarios[$dia]['T']['hasta']?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text">hs.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="dia[]" value="<?=$dia?>">
                <?
            }
            ?>
        </div>
    </div>
    <input type="hidden" name="idProfesional" value="<?=$post['idProfesional']?>">
    <input type="hidden" name="action" value="guardarHorarios">
    <button type="button" class="btn btn-primary" onclick="guardarHorarios()">Guardar todos los horarios</button>
    <button type="button" class="btn btn-secondary" onclick="$('.administrarHorarios').remove();">Cancelar</button>
</form>
<?
$devolucion=ob_get_contents();
ob_end_clean();
echo preg_replace( "/\r|\n/", "", $devolucion );
?>