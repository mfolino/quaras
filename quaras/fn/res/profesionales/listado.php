<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();

$whereProfesionales = '';
if($general['profesional_abm_horarios']){
    AuthController::checkSuperAdminORProfesional();
    if(AuthController::isProfesional()){
        $whereProfesionales = ' AND idProfesional = "'.$_SESSION['usuario']['idUsuario'].'" ';
    }
}else{
    AuthController::checkSuperAdmin();
}



/* if($_SERVER['REMOTE_ADDR'] == '186.138.206.135'){
    echo "<pre>";
    print_r($isProfesional);
    echo "</pre>";
    die();
} */

$diaSemana['Monday']='Lunes';
$diaSemana['Tuesday']='Martes';
$diaSemana['Wednesday']='Miercoles';
$diaSemana['Thursday']='Jueves';
$diaSemana['Friday']='Viernes';
$diaSemana['Saturday']='Sabado';
$diaSemana['Sunday']='Domingo';

$tratamientos=array();
db_query(0,"select t.nombre as tratamiento, t.duracion, pt.idProfesional from tratamientos t, profesionales_tratamientos pt where t.idTratamiento=pt.idTratamiento and t.estado NOT IN ('I', 'B')");
for($i=0;$i<$tot;$i++){
    $nres=$res->data_seek($i);
    $row=$res->fetch_assoc();
    $tratamientos[$row['idProfesional']][]=$row['tratamiento'].' ('.$row['duracion'].' min)';
}



?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion=ucwords($general['nombreProfesionales']);
        $subseccion='Listado';
        require_once(incPath.'/head.php');
        ?>
    </head>
    <body class="app sidebar-mini rtl">
        <?
        require_once(incPath.'/header.php');
        require_once(incPath.'/sidebar.php');
        ?>
        <main class="app-content">
            <div class="app-title">
                <div>
                    <h1><i class="fa fa-medkit"></i> <?=ucwords($general['nombreProfesionales'])?></h1>
                    <p>Utilice este listado para ver de un rápido vistazo las <?=($general['nombreProfesionales'])?> y administrar las mismas.</p>
                </div>
                <?/*<ul class="app-breadcrumb breadcrumb side">
                    <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                    <li class="breadcrumb-item active"><a href="#">Profesionales</a></li>
                </ul>*/?>
                <? db_query(0,"SELECT * FROM profesionales WHERE estado = 'A' ORDER BY nombre ASC"); ?>
				
                <? if(!$isProfesional){ ?>
                    <div>
                        <?/*<a class="btn btn-primary icon-btn" href="#" id="abrirHorarios"><i class="far fa-clock"></i> Ver horarios</a>*/?>
                        <? 
                        if($tot < Util::getAgendas($general['plan'])){ ?>
                            <a class="btn btn-primary icon-btn" href="/profesionales/agregar"><i class="fa fa-plus"></i>Agregar <?=($general['nombreProfesional'])?></a>
                        <? } ?>

                        <? /* if($tot<$maxAgendas){ */ ?>
                            <!-- <a class="btn btn-primary icon-btn" href="/profesionales/agregar">
                                <i class="fa fa-plus"></i>
                                Agregar 
                                < ? = ($general['nombreProfesional']) ?>
                            </a> -->
                        <? /* } */ ?>
                    </div>
                <? } ?>

            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="tile">
                        <div class="tile-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered" id="tablaProfesionales">
                                    <thead>
                                        <tr>
                                            <th>Acciones</th>
                                            <th>Nombre</th>
                                            <th>Tipo</th>
                                            <?/*<th>Teléfono</th>*/?>
                                            <?=($general['accesoProfesionales']) ? '<th>E-mail</th>' : ''?>
                                            <?/*<th>Especialidad</th>*/?>
                                            <th>Privado</th>
                                            <th><?=ucwords($general['nombreObrasSociales'])?></th>
                                            <th>Referencia</th>
                                            <?/*<th>Ganancia mensual</th>*/?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?
                                        $profesionales=array();
                                        db_query(0,"SELECT * FROM profesionales WHERE estado <> 'B' {$whereProfesionales} ORDER BY nombre ASC");
                                        for($i=0;$i<$tot;$i++){
                                            $nres=$res->data_seek($i);
                                            $row=$res->fetch_assoc();
                                            $telefono=$general['prefijoTelefonico'].$row['codArea'].$row['telefono'];
                                            ?>
                                            <tr id="fila<?=$row['idProfesional']?>">
                                                <td>
                                                    <a onclick="editar(<?=$row['idProfesional']?>)" href="#" data-toggle="tooltip" data-placement="bottom" title="Editar"><i class="fa fa-pencil"></i></a>
                                                    <a onclick="eliminar(<?=$row['idProfesional']?>)" href="#" data-toggle="tooltip" data-placement="bottom" title="Eliminar"><i class="fa-solid fa-trash-can"></i></a>
                                                    <?
                                                    if($row['tipo']=='H'){
                                                    ?>
                                                        <a onclick="javascript:;" class="editarHorarios" href="#" data-idProfesional="<?=$row['idProfesional']?>" data-toggle="tooltip" data-placement="bottom" title="Días y horarios"><i class="fa-solid fa-calendar-days"></i></a>
                                                    <?
                                                    }
                                                    ?>
                                                    <a onclick="comisiones(<?=$row['idProfesional']?>)" href="#" data-toggle="tooltip" data-placement="bottom" title="Comisiones"><i class="fa fa-percent"></i></a>
                                                </td>
                                                <td><?=$row['nombre']?></td>
                                                <td><?=($row['tipo']=='P') ? 'Puntual' : 'Habitual'?></td>
                                                <?=($general['accesoProfesionales']) ? '<td>'.$row['email'].'</td>' : ''?>
                                                <td><?=($row['privado']==1) ? 'Sólo Privado' : 'No'?></td>
                                                <td>
                                                    <?
                                                    $servicios='<div><ul>';
                                                    if(isset($tratamientos[$row['idProfesional']])){
                                                        foreach($tratamientos[$row['idProfesional']] as $tratamiento){
                                                            $servicios.='<li>'.$tratamiento.'</li>';
                                                            $serviciosCupos=$tratamiento;
                                                        }
                                                    }else{
                                                        $servicios.='<li>No tiene '.$general['nombreObrasSociales'].' asociados</li>';
                                                    }
                                                    $servicios.='</ul></div>';
                                                    
                                                    if($general['cupos']){
                                                        ?>
                                                        <?=$serviciosCupos?>
                                                        <?
                                                    }else{
                                                        ?>
                                                        <a href="javascript:;" class="abrirServicios" data-toggle="tooltip" data-servicios="<?=$servicios?>" title="Ver servicios"><?=(isset($tratamientos[$row['idProfesional']])) ? count($tratamientos[$row['idProfesional']]): ' - '?></a>
                                                        <?
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="cuadraditoProfesional" id="patricia" style="background-color:#<?=$row['color']?>"></div>
                                                </td>
                                                <?/*<td>No disponible</td>*/?>
                                            </tr>
                                            <?
                                            $profesionales[$row['idProfesional']]['nombre']=$row['nombre'];
                                            $profesionales[$row['idProfesional']]['color']=$row['color'];
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
		
        <div class="modal fade" tabindex="-1" role="dialog" id="verHorario">
            <div class="modal-dialog  modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="far fa-clock"></i> Horarios <?=($general['nombreProfesionales'])?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?
                        foreach($diaSemana as $ingles => $dia){
                            // if(($dia<>'Sabado')and($dia<>'Domingo')){
                            // if(($dia<>'Domingo')){
                            ?>
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th width="20%">
                                            <h4><?=$dia?></h4>
                                        </th>
                                        <th width="40%" colspan="2">
                                            <h6>Mañana</h6>
                                        </th>
                                        <th width="40%" colspan="2">
                                            <h6>Tarde</h6>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?
                                db_query(1, "select * from horariosprofesionales where dia='".strtolower($dia)."' and idHoras in(select max(idHoras) from horariosprofesionales group by idProfesional, dia) order by desdeTarde ASC, hastaTarde ASC, desdeManana ASC, hastaManana ASC");
                                if($tot1>0){
                                    for($i1=0;$i1<$tot1;$i1++){
                                        $nres1=$res1->data_seek($i1);
                                        $row1=$res1->fetch_assoc();
                                        ?>
                                        <tr>
                                            <?
                                            $leyenda='';
                                            /*if(($row1['desdeManana']<>'')and($row1['hastaManana']<>'')){
                                                $leyenda='Mañana';
                                            }
                                            if(($row1['desdeTarde']<>'')and($row1['hastaTarde']<>'')){
                                                if($leyenda==""){
                                                    $leyenda='Tarde';
                                                }else{
                                                    $leyenda.='/Tarde';
                                                }
                                            }*/
                                            $color=$profesionales[$row1['idProfesional']]['color'];
                                            if((($row1['desdeManana']<>'')and($row1['hastaManana']<>''))or(($row1['desdeTarde']<>'')and($row1['hastaTarde']<>''))){
                                                /*
                                            ?>
                                            <div class="col">
                                                <p><?=$leyenda?></p>
                                            </div>*/
                                            ?>
                                            <td>
                                                <p style="color:#<?=$color?>"><?=$profesionales[$row1['idProfesional']]['nombre']?></p>
                                            </td>
                                            <td>
                                                <p<?=($row1['desdeManana']<>'') ? ' style="color:#'.$color.'"' : ''?>><?=$row1['desdeManana']?></p>
                                            </td>
                                            <td>
                                                <p<?=($row1['hastaManana']<>'') ? ' style="color:#'.$color.'"' : ''?>><?=$row1['hastaManana']?></p>
                                            </td>
                                            <td>
                                                <p<?=($row1['desdeTarde']<>'') ? ' style="color:#'.$color.'"' : ''?>><?=$row1['desdeTarde']?></p>
                                            </td>
                                            <td>
                                                <p<?=($row1['hastaTarde']<>'') ? ' style="color:#'.$color.'"' : ''?>><?=$row1['hastaTarde']?></p>
                                            </td>
                                            <?
                                            }
                                            ?>
                                        </tr>
                                        <?
                                    }
                                }else{
                                    ?>
                                    <tr>
                                        <td colspan="5">
                                            <p>No hay horarios disponibles para este día.</p>
                                        </td>
                                    </tr>
                                    <?
                                }
                                ?>
                                    </tbody>
                                </table>
                                <?
                            // }
                        }
                        ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>







        <div class="modal fade" id="verServicios" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Ver <?=$general['nombreObrasSociales']?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
				
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
		
        <?
        require_once(incPath.'/scripts.php');
        ?>
		
        <script>

			
            $(".abrirServicios").click(function(e){
                e.preventDefault();
                $("#verServicios .modal-body").html($(this).data('servicios'));
                $("#verServicios").modal('show');
            })
			
            $("#abrirHorarios").click(function(e){
                e.preventDefault();
                $("#verHorario").modal('show');
            })
		
            $('#tablaProfesionales').DataTable({
                "drawCallback": function() {

                    $(".administrarHorarios").remove();

                    $('.editarHorarios').click(function(){
                        if($(".administrarHorarios").is(":visible")){
                            $(".administrarHorarios").remove();
                        }
						
                        var miFila=$(this);
                        $.post(
                            '/profesionales/horarios.php',
                            { idProfesional:miFila.data('idprofesional') },
                            function(resultado){
								
                                miFila.closest('tr').after('<tr class="administrarHorarios"><td colspan="8">'+resultado+'</td></tr>');
                                $(".prenderTurno").on('change',function(){
                                    if($(this).prop("checked") == true){
                                        // console.log("#"+$(this).data('dia')+$(this).data('turno'));
                                        $("#"+$(this).data('dia')+$(this).data('turno')).removeClass('d-none');
                                    }else{
                                        $("#"+$(this).data('dia')+$(this).data('turno')).addClass('d-none');
                                        $("#"+$(this).data('dia')+$(this).data('turno')+" input").val('');
                                    }
                                })
                            }
                        )
                    })
                }
            })
		
            function editar(id){
                window.location.href="/profesionales/agregar?id="+id;
            }
            function horarios(id){
                window.location.href="/profesionales/horarios?id="+id;
            }
            function comisiones(id){
                window.location.href="/profesionales/comisiones?id="+id;
            }
            function eliminar(id){
                Swal.fire({
                    title: "Está seguro/a?",
                    text: "Si elimina la <?=($general['nombreProfesional'])?> todas las estadísticas, <?=($general['nombreTurnos'])?> asignados, etc. se eliminarán con él.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Si, estoy seguro/a!",
                    cancelButtonText: "No, mejor no",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }).then((result) => {

                    if (result.value) {
                        $.post('/profesionales/save',{action: 'delete', id:id}, function(resultado){
                            if(resultado.status=='OK'){
                                Swal.fire("Eliminado!", "La <?=($general['nombreProfesional'])?> seleccionado ha sido eliminada.", "success");
                                $("#fila"+id).remove();
                            }else{
                                Swal.fire("Error!", "Ha ocurrido un error al intentar eliminar la <?=($general['nombreProfesional'])?>. Intente nuevamente.", "error");
                            }
                        })
                    }/*  else if (result.isDenied) {
                        Swal.fire('Changes are not saved', '', 'info')
                    } */
                })
            }
			
            function guardarHorarios(){
                $.post('/profesionales/save.php',$("#horariosDias").serialize(),function(resultado){
                    // console.log(resultado);
                    if(resultado=="OK"){
                        $(".administrarHorarios").remove();
                    }else{
                        console.error("APP: Ha ocurrido un error al guardar los horarios. Contactar con el proveedor.");
                    }
                })
            }
        </script>
        <?
        // include(incPath.'/analytics.php');
        ?>
    </body>
</html>