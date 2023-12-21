<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

$fechaDeLosRecordatorios = $_GET["fechaRecordatorio"] ?? date("Y-m-d", strtotime("+ {$general['wappDays']} days"));

$turnosPorFecha = db_getAll(
    "SELECT 
        p.nombre,
        p.apellido,
        t.fechaInicio,
        t.idTurno,
        IF(tr.idRecordatorio IS NULL, 0, 1) as enviado
    FROM 
        pacientes p,
        turnos t
    LEFT JOIN 
        turnos_recordatorios tr
    ON 
        t.idTurno = tr.idTurno 
    WHERE 
        t.idPaciente = p.idPaciente AND 
        t.estado = 0 AND 
        t.eliminado <> '1' AND 
        DATE(t.fechaInicio) = '{$fechaDeLosRecordatorios}'
    ORDER BY 
        t.fechaInicio
");


AuthController::checkLogin();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion='Escritorio';
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
                    <h1><i class="fa fa-desktop"></i> Escritorio</h1>
                    <p>Utilice este módulo para ver de un rápido vistazo la información de su sistema.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="widget-small primary coloured-icon" onclick="window.location.href='/pacientes/listado'" style="cursor:pointer">
                        <i class="icon fas fa-users fa-3x"></i>
                        <div class="info">
                            <h4><?=ucwords($general['nombrePacientes'])?></h4>
                            <p>
                                <b>
                                    <?
                                    db_query(0,"select count(idPaciente) as pacientes from pacientes WHERE estado <> 'B'");
                                    echo $row['pacientes'];
                                    ?>
                                </b>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="widget-small success coloured-icon" onclick="window.location.href='/turnos/calendario?v=dia'" style="cursor:pointer">
                        <i class="icon fas fa-calendar-day fa-3x"></i>
                        <div class="info">
                            <h4><?=ucwords($general['nombreTurnos'])?> hoy</h4>
                            <p>
                                <b>
                                    <?
                                    db_query(0,"select count(idTurno) as turnosHoy from turnos where date(fechaInicio)='".date("Y-m-d")."' and (estado<>3 and estado<>9) and eliminado<>1");
                                    echo $row['turnosHoy'];
                                    ?>
                                </b>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="widget-small info coloured-icon" onclick="window.location.href='/turnos/calendario?v=mes'" style="cursor:pointer">
                        <i class="icon fas fa-calendar-days fa-3x"></i>
                        <div class="info">
                            <h4><?=ucwords($general['nombreTurnos'])?> mensual</h4>
                            <p>
                                <b>
                                    <?
                                    db_query(0,"SELECT COUNT(idTurno) AS turnosMes FROM turnos WHERE month(fechaInicio)='".date("m")."' AND year(fechaInicio)='".date("Y")."' AND (estado<>3 and estado<>9) and eliminado<>1");
                                    echo $row['turnosMes'];
                                    ?>
                                </b>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="tile">
                        <h3 class="tile-title">Clasificación de <?=($general['nombreTurnos'])?></h3>
                        <?
                        db_query(1,"select estado, count(*) as cantidad from turnos where date(fechaInicio)='".date("Y-m-d")."' and eliminado<>1 and estado<>9 group by estado order by fechaInicio ASC");
                        if($tot1>0){
                        ?>
                        <div class="embed-responsive embed-responsive-16by9">
                            <canvas class="embed-responsive-item" id="pieChartDemo"></canvas>
                        </div>
                        <?
                        }else{
                            ?>
                            <p>No hay <?=($general['nombreTurnos'])?> cargados para el día de hoy.</p>
                            <?
                        }
                        ?>
                    </div>
                    <?
                    if(
                        ($general['plan']<3)
                        and
                        ($general['wappPlugin']!=1)
                    ){
                        ?>
                        </div>
                        <div class="col-md-6">
                        <?
                    }
                    ?>
                    <div class="tile">
                        <h3 class="tile-title">Próximos <?=($general['nombreTurnos'])?></h3>
                        <?
                        db_query(0,"select * from turnos t, pacientes p where date(fechaInicio)='".date("Y-m-d")."' and fechaInicio>='".date("Y-m-d H:i:s")."' and p.idPaciente=t.idPaciente and t.estado=0 and t.eliminado <> '1' order by fechaInicio asc limit 13");
                        if($tot>0){
                        ?>
                        <table class="table table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                for($i=0;$i<$tot;$i++){
                                    $nres=$res->data_seek($i);
                                    $row=$res->fetch_assoc();
                                    ?>
                                    <tr style="cursor:pointer" onclick="cargarInfoTurno(<?=$row['idTurno']?>,'<?=$row['nombre'].' '.$row['apellido']?>','<?=str_replace(' ','T',$row['fechaInicio'])?>')">
                                        <td><?=$row['nombre']?></td>
                                        <td><?=$row['apellido']?></td>
                                        <td><?=date("H:i",strtotime(str_replace('/','-',$row['fechaInicio'])))?></td>
                                    </tr>
                                    <?
                                }
                                ?>
                            </tbody>
                        </table>
                        <?
                        }else{
                        ?>
                            <p>No hay próximos <?=($general['nombreTurnos'])?> cargados</p>
                        <?
                        }
                        ?>
                    </div>
					
                </div>
				
                <?
                if(
                    ($general['plan']>2)
                    or
                    ($general['wappPlugin']==1)
                ){
                    if($general['wappDays']){
                        if(substr($general['wappDays'],0,1)=='H'){
                            //Son horas
                            $dias=substr($general['wappDays'],1,2).' hours';
                        }else{
                            $dias=$general['wappDays'].' days';
                        }
                    }else{
                        $dias='1 days';
                    }
                    ?>
                    <div class="col-md-6">
                        <div class="tile">
                            <h3 class="tile-title">Recordatorios de <?=($general['nombreTurnos'])?> <?=$general["wappApi"] ? '<em class="text-secondary">(Automáticos)</em>' : ''?></h3>
                            <? if($general["panel_selectorDeFechasParaLosRecordatorios"]){ ?>
                                <div class="form-group">
                                  <input type="date" class="form-control" id="inputDateRecordatorios" value="<?=$fechaDeLosRecordatorios?>" min="<?=date("Y-m-d")?>">
                                </div>
                            <? } ?>
                            <p>Utilizá el botón de Whatsapp para enviar los recordatorios a los <?=($general['nombrePacientes'])?> del <?=date("d/m/Y",strtotime('+'.$dias))?>.</p>
							
                            <table class="table table-striped table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th><?=ucwords($general['nombrePaciente'])?></th>
                                        <th><?=ucwords($general['nombreTurno'])?></th>
                                    </tr>
                                </thead>
                                <? if($general["panel_selectorDeFechasParaLosRecordatorios"]){ ?>
                                    <tbody>
                                        <? foreach ($turnosPorFecha as $turno) { ?>
                                            <tr id="recordatorio<?=$turno->idTurno?>" <?=$turno->enviado ? 'data-toggle="tooltip" title="Ya enviado"' : ""?>>
                                                <td class="<?=$turno->enviado ? 'text-info' : ''?>"><a href="javascript:;" onclick="mandarWapp(<?=$turno->idTurno?>)"><i class="fab fa-whatsapp"></i></a></td>
                                                <td class="<?=$turno->enviado ? 'text-info' : ''?>"><?=ucfirst($turno->nombre)." ". ucfirst($turno->apellido)?></td>
                                                <td class="<?=$turno->enviado ? 'text-info' : ''?>"><?=date("d/m/Y H:i", strtotime($turno->fechaInicio))?></td>
                                            </tr>
                                        <? } ?>
                                    </tbody>
                                <? }else{ ?>
                                    <tbody id="recordatorios">
                                        
                                    </tbody>
                                <? } ?>
                            </table>
                            
                        </div>
                    </div>
                    <?
                }
                ?>

            </div>
			

        </main>
		
        <!-- Modal -->
        <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Jorge Cancela 20/04 9:00</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer">
                        ...
                    </div>
                </div>
            </div>
        </div>
		
        <? require_once(incPath.'/scripts.php'); ?>
		
        <!-- Page specific javascripts-->
        <script type="text/javascript" src="<?=$cdn?>/js/plugins/chart.js"></script>

        <script type="text/javascript">
            <? if($tot1>0){ ?>
            var pdata = [
                <?
                for($i1=0;$i1<$tot1;$i1++){
                    $nres1=$res1->data_seek($i1);
                    $row1=$res1->fetch_assoc();
                    if($row1['estado']==0){
                        $color='#17a2b8';
                        $colorHover='#138496';
                        $leyenda=ucwords($general['estadoPendiente']);
                        $cantidad=$row1['cantidad'];
                    }
                    if($row1['estado']==1){
                        $color='#28a745';
                        $colorHover='#218838';
                        $leyenda=ucwords($general['estadoConfirmado']);
                        $cantidad=$row1['cantidad'];
                    }
                    if($row1['estado']==2){
                        $color='#ffc107';
                        $colorHover='#d39e00';
                        $leyenda=ucwords($general['estadoAusente']);
                        $cantidad=$row1['cantidad'];
                    }
                    if($row1['estado']==3){
                        $color='#dc3545';
                        $colorHover='#c82333';
                        $leyenda=ucwords($general['estadoCancelado']);
                        $cantidad=$row1['cantidad'];
                    }
                    ?>
                    {
                        value: <?=$cantidad?>,
                        color: "<?=$color?>",
                        highlight: "<?=$colorHover?>",
                        label: "<?=$leyenda?>"
                    },
                    <?
                }
                ?>
            ]

            /*var ctxl = $("#lineChartDemo").get(0).getContext("2d");
            var lineChart = new Chart(ctxl).Line(data);*/

            var ctxp = $("#pieChartDemo").get(0).getContext("2d");
            var pieChart = new Chart(ctxp).Pie(pdata);
			
            <? } ?>
			
            function mandarWapp(id){
                $.post("turnos/save",{
                    action:'getWappLink',
                    id:id
                },function(response){
                    console.log(response);
                    if(response.status=='OK'){
                        $("#recordatorio"+id).addClass('text-info');
                        $("#recordatorio"+id).attr('title','Ya enviado');
                        $("#recordatorio"+id).attr('data-toggle','tooltip');
						
                        $('[data-toggle="tooltip"]').tooltip();
						
                        window.open(response.link);
                    }else{
                        if(response.status=='telefono'){
                            Swal.fire("Teléfono inválido!", "El telefóno cargado para el <?=($general['nombrePaciente'])?> no es un teléfono celular.", "error");
                        }else{
                            Swal.fire("Lo sentimos!", "Ha ocurrido un error al procesar tu solicitud. Intentalo nuevamente.", "error");
                        }
                    }
                })
            }


            function getRecordatorios(){
                $.post(
                    '/turnos/save',
                    {
                        action:'getRecordatorios'
                    },
                    function(response){

                        console.log(response);

                        if(response.status=='OK'){
                            $("#recordatorios").html('');

                            $.each(response.recordatorios,function(index,value){

                                var color='';
                                var tooltip='';

                                if(value.enviado){
                                    color='text-info';
                                    tooltip=' data-toggle="tooltip" title="Ya enviado"';
                                }

                                let miHtml='<tr id="recordatorio'+value.idTurno+'" '+tooltip+'><td class="'+color+'"><a href="javascript:;" onclick="mandarWapp('+value.idTurno+')"><i class="fab fa-whatsapp"></i></a></td><td class="'+color+'">'+value.nombre+' '+value.apellido+'</td><td class="'+color+'">'+value.turno+'</td></tr>';

                                $("#recordatorios").append(miHtml);
                            })

                            $('[data-toggle="tooltip"]').tooltip();

                        }else{
                            $("#recordatorios").html('<tr><td colspan="3" class="alert-success text-center">No hay recordatorios para enviar.</td></tr>');
                        }
                    }
                )
            }

            getRecordatorios();


            $("#inputDateRecordatorios").change(e => {
                location.href = "/panel?fechaRecordatorio="+$("#inputDateRecordatorios").val()
            })
        </script>
		
        <?
        // include(incPath.'/analytics.php');
        ?>
    </body>
</html>