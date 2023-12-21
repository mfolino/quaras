<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/fn.php');
AuthController::checkLogin();

if ($general['cupos']) {
    include("cupos.php");
    die();
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?
    $seccion = ucwords($general['nombreTurnos']);
    $subseccion = 'Calendario';
    require_once(incPath . '/head.php');
    ?>
    <link rel="stylesheet" href="<?=$cdn?>/css/cupos.min.css?v=<?=rand()?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar-scheduler/1.10.1/scheduler.min.css' rel='stylesheet' />
</head>

<body class="app sidebar-mini rtl">
    <?
    require_once(incPath . '/header.php');
    require_once(incPath . '/sidebar.php');
    ?>
    <main class="app-content">
        <?
        if (isset($general['multiplesTurnos'])) {
            require_once(incPath . '/multiplesTurnos');
        }
        ?>

        <div class="app-title">
            <div>
                <h1><i class="fa fa-calendar"></i> <?= ucwords($general['nombreTurnos']) ?></h1>
                <p>Utilice este calendario para ver de un rápido vistazo los <?= ($general['nombreTurnos']) ?> y administrarlos.</p>
            </div>
            <div class="d-flex justify-content-end w-50">
                <select id="filtrarProfesional" class="form-control mr-3 w-50">
                    <?
                    if (!$_SESSION['usuario']['profesional']) {
                    ?>
                        <option value="">Todos</option>
                        <?
                    }

                    $profesionales = ProfesionalController::getProfesionales();

                    foreach ($profesionales['profesionales'] as $idProfesional => $profesional) {
                        if ($_SESSION['usuario']['profesional']) {

                            if ($_SESSION['usuario']['idUsuario'] == $idProfesional) {
                        ?>
                                <option value="<?= $idProfesional ?>" selected><?= $profesional ?></option>
                            <?
                            }
                        } else {
                            ?>
                            <option value="<?= $idProfesional ?>"><?= $profesional ?></option>
                    <?
                        }
                    }
                    ?>
                </select>
                <a class="btn btn-secondary icon-btn float-right w-50" href="#!" onclick="prePrint('<?=$general['nombreTurnos']?>')"><i class="fa fa-print"></i>Imprimir <?= ($general['nombreTurnos']) ?></a>
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
                        db_query(0, "select color, nombre, idProfesional from profesionales where estado='A' order by nombre");
                        for ($i = 0; $i < $tot; $i++) {
                            $nres = $res->data_seek($i);
                            $row = $res->fetch_assoc();
                            if ($_SESSION['usuario']['profesional']) {
                                if ($_SESSION['usuario']['idUsuario'] == $row['idProfesional']) {
                                    echo '<div class="col"><i class="fas fa-circle" style="color:#' . $row['color'] . '"></i> ' . $row['nombre'] . '</div>';
                                }
                            } else {
                                echo '<div class="col"><i class="fas fa-circle" style="color:#' . $row['color'] . '"></i> ' . $row['nombre'] . '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
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

                </div>
                <div class="modal-footer">

                </div>
            </div>
        </div>
    </div>

    <iframe id="contenedorImpresion" class="d-none"></iframe>

    <?
    require_once(incPath . '/scripts.php');
    ?>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar-scheduler/1.10.1/scheduler.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/locale/es.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>

    <script>
        var misTurnos = new Object();


        var recursos = {
            turnosProgramados: {
                url: '/turnos/save?action=getTurnos', // use the `url` property
                data: function() { // a function that returns an object
                    return {
                        profesionalSeleccionado: profesionalSeleccionado ?? '',
                    }
                },
                error: function(e) {
                    console.log(e);
                    console.log('there was an error while fetching events!');
                },
                success: function(e) {
                    console.log(e);
                    //Acá voy a contar cuántos tengo por día
                }
            },
            resumenMensual:{
                url: '/turnos/save?action=getResumenMensual', // use the `url` property
                data:function() { // a function that returns an object
                      return {
                        profesionalSeleccionado: profesionalSeleccionado ?? '',
                      }
                },
                error: function(e) {
                    console.log(e);
                    console.log('there was an error while fetching events!');
                },
                success: function(e) {
                    console.log(e);
                    //Acá voy a contar cuántos tengo por día
                }
            },
            profesionales: {
                url: '/profesionales/save?action=getProfesionalesDisponibles',
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

        var lastView = '';

        setTimeout(function() {

            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                    // right: 'agendaWeek,agendaDay'
                },
                firstDay: 1,
                locale: 'es',
                selectable: true,
                weekends: true,
                height: 'auto',
                slotDuration: '00:<?= sprintf('%02d', $general['minBloqueTurnos']) ?>',
                <?
                if(@$general['formato24horas']){
                    ?>
                    slotLabelFormat: 'H(:mm)',
                    <?
                }else{
                    ?>
                    slotLabelFormat: 'hh:mm a',
                    <?
                }
                if(@$general['minutosBarraCalendario']){
                    ?>
                    slotLabelInterval: '<?=$general['minutosBarraCalendario']?>',
                    <?
                }
                ?>
                slotEventOverlap: false,
                minTime: '<?= ProfesionalController::getMinHour() ?>',
                maxTime: '<?= ProfesionalController::getMaxHour() ?>',
                navLinks: true,
                hiddenDays: [<?= $general['diasOcultos'] ?>],
                nowIndicator: true,
                <?
                if (!isset($_GET['v'])) {
                ?>
                    defaultView: 'agendaWeek',
                <?
                }
                if (isset($_GET['v']) && $_GET['v'] == 'mes') {
                ?>
                    defaultView: 'month',
                <?
                }
                if (isset($_GET['v']) && $_GET['v'] == 'dia') {
                ?>
                    defaultView: 'agendaDay',
                <?
                }
                ?>
                // defaultView: 'agendaDay',
                allDaySlot: false,
                editable: true,
                eventDurationEditable: false,
                eventClick: function(calEvent, jsEvent, view) {
                    if(view.type!='month'){
                        if(!localStorage.getItem('multiple')){
                            cargarInfoTurnoSimple(calEvent);
                            
                        }
                    }else{
                        miFecha=calEvent.start.format('YYYY-MM-DD');

                        $('#calendar').fullCalendar( 'gotoDate', miFecha );
                        $('#calendar').fullCalendar( 'changeView', 'agendaDay' );
                    }
                },
                views: {
                    day: {
                        slotDuration: '00:<?= $general['minBloqueTurnos'] ?>',
                        groupByResource: true,
                        groupByDateAndResource: true,
                        titleFormat: 'dddd D/M'
                    }
                },
                resources: recursos.profesionales,
                refetchResourcesOnNavigate: true,
                eventSources: [
                    recursos.turnosProgramados
                ],
                dayClick: function(date, jsEvent, view, objeto) {
                    if(view.name=='month'){
                        miFecha=date.format('YYYY-MM-DD');

                        $('#calendar').fullCalendar( 'gotoDate', miFecha );
                        $('#calendar').fullCalendar( 'changeView', 'agendaDay' );
                    }else{
                        var miFecha = date.format('YYYY-MM-DD HH:mm:ss');

                        /* console.log(objeto); */

                        if (objeto == null) {
                            idProfesional = '';
                        } else {
                            idProfesional = objeto.id;
                        }

                        $.post('/turnos/save.php', {
                            action: 'estaAbierto',
                            fecha: miFecha,
                            idProfesional: idProfesional
                        }, function(resultado) {

                            console.log('Action: estaAbierto');
                            console.log(resultado);
                            if (resultado.status == 'OK') {
                                if (!localStorage.getItem('multiple')) {
                                    $('.modal .modal-title').html("Cargar nuevo <?= ($general['nombreTurno']) ?>");
                                    if (objeto) {
                                        $('.modal .modal-body').html('<iframe class="w-100" style="height:400px" frameborder="0" src="/turnos/agregar?from=calendario&fecha=' + miFecha + '&profesional=' + objeto.id + '"></iframe>');
                                    } else {
                                        $('.modal .modal-body').html('<iframe class="w-100" style="height:400px" frameborder="0" src="/turnos/agregar?from=calendario&fecha=' + miFecha + '"></iframe>');
                                    }
                                    $('.modal .modal-footer').html('');
                                    $('.modal').modal('show');
                                } else {

                                    var nuevaFecha = date.format("DD/MM/YYYY HH:mm");
                                    // var nuevaFecha=miFecha[0]+'-'+miFecha[1]+'-'+miFecha[2]+' '+miFecha[3]+':'+miFecha[4];

                                    if (localStorage.getItem('fechasTomadas')) {
                                        var entradasActuales = JSON.parse(localStorage.getItem('fechasTomadas'));
                                        if (entradasActuales.length < localStorage.getItem('disponibles')) {
                                            entradasActuales.push(nuevaFecha);
                                        } else {
                                            Swatl.fire("Disponibles completados", "Ya ha seleccionado todos los <?= ($general['nombreTurnos']) ?> que pueden tomarse para la orden del <?= ($general['nombrePaciente']) ?>.", "warning");
                                        }
                                    } else {
                                        var entradasActuales = [nuevaFecha];
                                    }

                                    localStorage.setItem('fechasTomadas', JSON.stringify(entradasActuales));

                                    cargarTurnosTomados();
                                }
                            }
                            if (resultado.status == 'CERRADO') {
                                //Local cerrado papa!
                            }
                            if (resultado.status == 'FERIADO') {
                                Swal.fire(resultado.titulo, resultado.nombre, "info");
                            }
                        })
                    }

                },
                eventDrop: function(event, delta, revertFunc) {
                    // alert(event.title + " was dropped on " + event.start.format());

                    if (event.source.calendar.view.name == 'month') {
                        var accionEjecutar = 'confirmarHorario';
                    } else {
                        var accionEjecutar = 'confirmar';
                    }

                    var miID = event.id;
                    var miFecha = event.start._i;
                    var miTitulo = event.title;
                    var revertir = revertFunc;

                    //VERIFICAR QUE EL TURNO NO ESTE CANCELADO, AUSENTE o ASISTIO o sea en el pasado
                    $.post('/turnos/save.php', {
                        action: 'sePuedeMover',
                        fecha: miFecha,
                        idTurno: miID
                    }, function(resultado) {
                        // console.log('sePuedeMover: '+resultado);
                        if (resultado == 'OK') {

                            $.post('/turnos/save.php', {
                                action: 'estaAbierto',
                                fecha: miFecha,
                                idTurno: miID
                            }, function(resultado) {
                                console.log('estaAbierto: ');
                                console.log(miFecha);
                                if (resultado.status == 'OK') {
                                    if (accionEjecutar == 'confirmar') {
                                        Swal.fire({
                                            title: "Está seguro/a?",
                                            text: "Desea mover el <?= ($general['nombreTurno']) ?> de " + event.title + " al " + getDate(event.start.format()) + ".",
                                            type: "warning",
                                            showCancelButton: true,
                                            confirmButtonText: "Si, estoy seguro/a!",
                                            cancelButtonText: "No, mejor no",
                                            closeOnConfirm: false,
                                            closeOnCancel: true
                                        }).then((result) => {

                                            if (result.value) {

                                                $.post('/turnos/save', {
                                                    action: 'updateTurno',
                                                    id: event.id,
                                                    fecha: event.start.format()
                                                }, function(resultado) {
                                                    console.log('UpdateTurno: ');
                                                    console.log(resultado);
                                                    if (resultado == 'OK') {
                                                        $('#calendar').fullCalendar('refetchEvents');
                                                    } else {
                                                        Swal.fire("Error!", "Ha ocurrido un error al intentar eliminar el <?= ($general['nombreTurno']) ?>. Intente nuevamente.", "error");
                                                        revertFunc();
                                                    }
                                                })
                                                /* Swal.fire('Horario actualizado', '', 'success') */

                                            } else if (result.isDenied) {
                                                revertFunc();
                                            }

                                        })


                                    }
                                    if (accionEjecutar == 'confirmarHorario') {
                                        $('.modal .modal-title').html('Modificar horario ' + miTitulo);
                                        $('.modal .modal-body').html('<p>Complete el horario para poder modificar el <?= ($general['nombreTurno']) ?>.</p><iframe src="/turnos/cambiarHorario?idTurno=' + miID + '&title=' + miTitulo + '&fecha=' + miFecha + '" frameborder="0" height="150" class="w-100"></iframe>');
                                        $('.modal .modal-footer').html('');
                                        $('.modal').modal('show');
                                    }
                                } else {
                                    revertFunc();
                                }
                            })

                        } else {
                            revertFunc();
                        }
                    })
                },

                eventRender: function( event, element, view ) {
                        
                    let tomados=event.tomados;

                    if(!event.tomados){
                        tomados=0;
                    }

                    if(tomados>event.cupo){
                        tomados=event.cupo;
                    }

                    if(view.type!='month'){

                        let horaOriginal = element.find('.fc-time').html();

                        element.find('.fc-time').addClass('tiempo'+event.id);
                        element.find('.fc-time').addClass('bg-'+event.colorPuntito);
                        element.find('.fc-time').html('<div class="row align-items-center text-white"><div class="col flex-grow-1">'+horaOriginal+'</div><div class="text-right ml-3 ml-md-0 mr-3 textoDuracion">'+event.duracionString+'</div></div>').addClass('d-none d-md-block'); 
                        element.find('.fc-title').prepend('<div class="fc-status-public align-items-center h-100" style="background-color:'+event.colorProfesional+'; color:'+event.titleColor+'"><div>'+event.nombrePax+'</div></div><div class="detallesEvento row align-items-center h-100"><div class="col flex-grow-1 text-left nombreProfe"><i class="<?=$general["icon_profesional"]?> mr-1" style="font-size:0.6rem"></i>'+event.profesional+'</div><div class="col flex-grow-1 text-left nombreProfe"><i class="fas fa-list mr-1" style="font-size:0.6rem"></i>'+event.tratamiento+'</div></div>'); 
                        // element.find('.fc-title').prepend('<div class="fc-status-public row align-items-center h-100" style="background-color:'+event.colorProfesional+'"><div class="col flex-grow-1">'+event.nombrePax+'</div><div class="mr-3"><i class="fas fa-circle text-'+event.colorPuntito+'" style="font-size:0.6rem"></i></div></div><div class="detallesEvento row align-items-center h-100"><div class="col flex-grow-1 text-left nombreProfe"><i class="fas fa-medkit mr-1" style="font-size:0.6rem"></i>'+event.profesional+'</div><div class="col flex-grow-1 text-left nombreProfe"><i class="fas fa-list mr-1" style="font-size:0.6rem"></i>'+event.tratamiento+'</div></div>'); 
                    }else{
                        element.find('.fc-title').prepend('<div class="fc-status-public align-items-center h-100" style="background-color:'+event.colorProfesional+'; color: '+event.titleColor+'"><div>'+event.tipo+'</div></div><div class="detallesEvento row align-items-center h-100" style="color:'+event.textColor+'; white-space:break-spaces"><div class="col flex-grow-1 text-left nombreProfe">'+event.motivo+'</div></div>'); 
                    }
                },

                eventAfterRender:function(eventObj, element){
						
                    //console.log(eventObj);

                    // console.log(element.find('.fc-time').width());

                    if(element.find('.fc-time').width()<100){
                        element.find('.textoDuracion').addClass('d-none');
                    }else{
                        element.find('.textoDuracion').removeClass('d-none');
                    }
                    /*if(element.find('.fc-title').width()<175){
                        element.find('.nombreProfe').addClass('d-none');
                    }else{
                        element.find('.nombreProfe').removeClass('d-none');
                    } */
                        
                    if(eventObj.rendering!='background'){
                        var laFecha=eventObj.start.format("YYYY-MM-DD");
                            
                        if(misTurnos[laFecha]){
                            misTurnos[laFecha]++;
                        }else{
                            misTurnos[laFecha]=1;
                        }
                    }
                        
                    if(eventObj.duracion<60){
                        if(eventObj.dataTitle){
                            $(".tooltipss"+eventObj.dataId).tooltip({
                                title:eventObj.dataTitle,
                                html:true
                            });
                        }
                    }

                    window.dispatchEvent(new Event('resize'));
                        
                },

                eventAfterAllRender:function(e){
                    // console.log(misTurnos);
                    $.each(misTurnos,function(fecha,cantidad){
                        // console.log(fecha);
                        if($("th[data-date="+fecha+"] span").length){
                            $("th[data-date="+fecha+"] span").html(cantidad+" <?=($general['nombreTurno'])?>");
                        }else{
                            $("th[data-date="+fecha+"]").append('<br><span style="font-size:10px">'+cantidad+" <?=($general['nombreTurnos'])?></span>");
                        }
                    })
						
                    setTimeout(function(){misTurnos=new Object();},500);
						
                },

                viewRender:function(view, element){

                    // console.log(view);

                    <?
                    if(!$_SESSION['usuario']['profesional']){
                        ?>
                        if(view.type=='agendaDay'){
                            $('#filtrarProfesional').val('').trigger('change');
                        }
                        <?
                    }
                    ?>

                    $('#calendar').fullCalendar( 'removeEventSources' );

                    if((view.type=='agendaDay')||(view.type=='agendaWeek')){
                        $('#calendar').fullCalendar( 'addEventSource', recursos.turnosProgramados );
                    }
                        
                    if(view.type=='month'){
                        $('#calendar').fullCalendar( 'addEventSource', recursos.resumenMensual );
                    }

                }
            });

            $('#filtrarProfesional').trigger('change');

        });


        /* 
            Params: idEstado
                    0 = Pendiente
                    1 = Armado
                    2 = Ausente
        */
        function cambiarEstadoPedido(idTurno, idEstado) {
            $.post(
                '/turnos/save.php', {
                    action: 'actualizarEstadoPedido',
                    idTurno,
                    idEstadoPedido: idEstado
                },
                function(resultado) {

                    console.log(resultado);
                    if (resultado.status == 'OK') {
                        
                    }
                })
        }
    </script>

</body>

</html>