<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion=ucwords($general['nombreTurnos']);
        $subseccion='Calendario';
        require_once(incPath.'/head.php');
        ?>
        <link rel="stylesheet" href="<?=$cdn?>/css/cupos.min.css?v=<?=rand()?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
        <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar-scheduler/1.10.1/scheduler.min.css' rel='stylesheet' />
    </head>
    <body class="app sidebar-mini rtl">
        <?
        require_once(incPath.'/header.php');
        require_once(incPath.'/sidebar.php');
        ?>
        <main class="app-content">
            <?
            if(isset($general['multiplesTurnos'])){
                require_once(incPath.'/multiplesTurnos');
            }
            ?>
			
            <div class="app-title">
                <div>
                    <h1><i class="fa fa-calendar"></i> <?=ucwords($general['nombreTurnos'])?></h1>
                    <p>Utilice este calendario para ver de un rápido vistazo los <?=($general['nombreTurnos'])?> y administrarlos.</p>
                </div>
                <div class="d-flex justify-content-end w-50">
                    <select class="form-control mr-2" name="categoria" id="categoria">
                        <?
                        db_query(1,
                        "SELECT idCategoria, nombre
                        FROM categorias
                        WHERE estado = 'A'
                        ORDER BY nombre");

                        for($i1=0;$i1<$tot1;$i1++)
                        {
                            $nres1=$res1->data_seek($i1);
                            $row1=$res1->fetch_assoc();
                            ?>

                            <option value="<?=$row1['idCategoria']?>"><?=$row1['nombre']?></option>

                        <? } ?>

                    </select>
                    <a class="btn btn-secondary icon-btn float-right w-50" href="#!" onclick="prePrint()"><i class="fa fa-print"></i>Imprimir <?=($general['nombreTurnos'])?></a>
                </div>
            </div>
            <div class="row">

                <div class="col-md-12">
                    <div class="tile row">
                        <div id="calendar"></div>
                        <div class="row w-100 mt-3">
                            <div class="col">
                                <b>Referencias:</b>
                            </div>
                            <?
                            db_query(0,"select color, nombre, idProfesional from profesionales where estado='A' order by nombre");
                            for($i=0;$i<$tot;$i++){
                                $nres=$res->data_seek($i);
                                $row=$res->fetch_assoc();
                                if($_SESSION['usuario']['profesional']){
                                    if($_SESSION['usuario']['idUsuario']==$row['idProfesional']){
                                        echo '<div class="col"><i class="fas fa-circle" style="color:#'.$row['color'].'"></i> '.$row['nombre'].'</div>';
                                    }
                                }else{
                                    echo '<div class="col"><i class="fas fa-circle" style="color:#'.$row['color'].'"></i> '.$row['nombre'].'</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
		
        <!-- Modal -->
        <div class="modal fade" id="exampleModalCenter" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                    </div>
                    <div class="modal-body">
                    
                    </div>
                    <div class="modal-footer">
						
                    </div>
                </div>
            </div>
        </div>
		
        <iframe id="contenedorImpresion" class="d-none"></iframe>
		
        <?
        require_once(incPath.'/scripts.php');
        ?>
		
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
		
        <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar-scheduler/1.10.1/scheduler.min.js'></script>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/locale/es.js'></script>
		
        <script>

            var categoria=$("#categoria option:selected").val();

            $("#categoria").on('change',function(){
                categoria=$("#categoria option:selected").val();

                localStorage.setItem('categoria',categoria);

                $.post('/turnos/save', {
                    action : 'guardarActividad',
                    categoria : categoria
                }, function(){
                    $('#calendar').fullCalendar('refetchResources');
                    $('#calendar').fullCalendar('refetchEvents');
                })

            })

            if(localStorage.getItem('categoria')){
                $("#categoria").val(localStorage.getItem('categoria'));
                categoria=$("#categoria option:selected").val();
            }

            var estaQuieto=1;
		
            var misTurnos=new Object();
			
			
            var recursos={
                turnosProgramados:{
                    url: '/turnos/save',
                    data: {
                        action: 'getTurnosPublic',
                        categoria: function(){
                            return categoria
                        }
                    },
                    error: function(e) {
                        console.log(e);
                        console.log('there was an error while fetching events!');
                    },
                    success: function(e) {
                        console.log(e);
                    }
                },
                tratamientos:{
                    url: '/obrasSociales/save?action=getTratamientosDisponibles',
                    /*data: {
                        action: 'getTratamientosDisponibles',
                        categoria: function(){
                            return categoria
                        }
                    },*/
                    error: function(e) {
                        console.log(e);
                        console.log('there was an error while fetching profesionales!');
                    },
                    success: function(e) {
                        console.log(e);
                        //Acá voy a contar cuántos tengo por día
                    }
                }
            }
			
            var lastView='';

            setTimeout(function(){
			
				
      
                $('#calendar').fullCalendar({
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                        // right: 'agendaWeek,agendaDay'
                    },
                    firstDay:1,
                    locale:'es',
                    selectable:true,
                    weekends:true,
                    height: 'auto',
                    slotDuration:'00:<?=$general['minBloqueTurnos']?>',
                    slotLabelFormat: 'hh:mm a',
                    slotEventOverlap:false,
                    minTime:'<?=ProfesionalController::getMinHour()?>',
                    maxTime:'<?=ProfesionalController::getMaxHour()?>',
                    navLinks:true,
                    hiddenDays: [ <?=$general['diasOcultos']?> ],
                    nowIndicator:true,
                    <?
                    if(!isset($_GET['v'])){
                    ?>
                        defaultView: 'agendaWeek',
                    <?
                    }
                    if(isset($_GET['v']) && $_GET['v']=='mes'){
                    ?>
                        defaultView: 'month',
                    <?
                    }
                    if(isset($_GET['v']) && $_GET['v']=='dia'){
                    ?>
                        defaultView: 'agendaDay',
                    <?
                    }
                    ?>
                    // defaultView: 'agendaDay',
                    allDaySlot:false,
                    editable: false,
                    eventDurationEditable: false,
                    eventClick: function(calEvent, jsEvent, view) {
                        if(!localStorage.getItem('multiple')){
                            cargarInfoTurnoCupos(calEvent);
                        }
                    },
                    views:{
                        day:{
                            slotDuration:'00:<?=$general['minBloqueTurnos']?>',
                            groupByResource: true,
                            groupByDateAndResource: true,
                            titleFormat:'dddd D/M'
                        }
                    },
                    resources:recursos.tratamientos,
                    refetchResourcesOnNavigate: true,
                    eventSources: [
                        recursos.turnosProgramados
                    ],
                    eventRender: function( event, element, view ) {
                        // console.log(event);
                        
                        let tomados=event.tomados;

                        if(!event.tomados){
                            tomados=0;
                        }

                        /* if(tomados>event.cupo){
                            tomados=event.cupo;
                        } */

                        let horaOriginal = element.find('.fc-time').html();

                        element.find('.fc-time').html('<div class="row align-items-center text-white"><div class="col flex-grow-1">'+horaOriginal+'</div><div class="text-right ml-3 ml-md-0 mr-3">'+event.duracion+'</div></div>'); 
                        element.find('.fc-title').prepend('<div class="fc-status-public row align-items-center h-100" style="background-color:'+event.colorProfesional+'"><div class="col flex-grow-1">'+event.actividad+'</div></div><div class="detallesEvento row align-items-center h-100"><div class="col flex-grow-1"><i class="fas fa-user" style="font-size:0.6rem"></i> '+event.profesional+'</div><div class="text-right ml-3 ml-md-0 mr-3">'+tomados+'/'+event.cupo+' <i class="fas fa-circle '+event.colorPuntito+'" style="font-size:0.6rem"></i></div></div>'); 
                    },

                    /* viewRender: function( view, element ) {
                        // Drop the second param ('day') if you want to be more specific
                        if(moment().isAfter(view.intervalStart, 'day')) {
                            $('.fc-prev-button').addClass('fc-state-disabled');
                        } else {
                            $('.fc-prev-button').removeClass('fc-state-disabled');
                        }
                    } */
                });

            });
					
        </script>
        <?
        // include(incPath.'/analytics.php');
        ?>
    </body>
</html>