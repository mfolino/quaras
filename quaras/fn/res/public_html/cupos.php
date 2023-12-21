<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

$campo=array();

switch ($general['tomaTurno']) {
    case 'dni':
        $campo['label']=$general['nombreDNI'];
        $campo['helper']=$general['leyendaDni'];
        $campo['tipo']='tel';
        $campo['clase']='soloNumeros';
        $campo['placeholder']=str_repeat('X',$general['largoDNI']);
        $campo['maxlength']=$general['largoDNI'];
        $campo['minlength']=5;
        $campo['hidden']='dni';
        break;
    case 'email':
        $campo['label']=$general['nombreMail'];
        $campo['helper']='';
        $campo['tipo']='email';
        $campo['clase']='';
        $campo['placeholder']='usuario@dominio.com';
        $campo['maxlength']='255';
        $campo['minlength']='10';
        $campo['hidden']='mail';
        break;
    case 'telefono':
        $campo['label']=$general['nombreTelefono'];
        $campo['helper']=$general['leyendaTelefono'];
        $campo['tipo']='tel';
        $campo['clase']='soloNumeros';
        $campo['placeholder']=str_repeat('X',$general['telLargoMax']);
        $campo['maxlength']=$general['telLargoMax'];
        $campo['minlength']=$general['telLargoMin'];
        $campo['hidden']='telefono';
        break;
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion="Turnos";
        $subseccion='';
        require_once($_SERVER['DOCUMENT_ROOT'].'/inc/head.php');
        ?>
        <link rel="stylesheet" href="<?=$cdn?>/css/cupos.min.css?v=<?=rand()?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
        <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar-scheduler/1.10.1/scheduler.min.css' rel='stylesheet' />
    </head>
    <body>
        <section class="material-half-bg">
            <div class="cover"></div>
        </section>
        <section class="login-content">
            <div class="logo mt-5">
                <img src="img/<?=$general['isologo']?>" width="<?=$general['logoWidth']?>">
            </div>
            <div class="login-box nuevoTurno-form">
            
                <div class="login-head text-left">
                    <h3><i class="fas fa-calendar-days"></i> <?=ucwords($general['nombreTurnos'])?></h3>
                    <p class="my-0 text-muted"><?=$general['leyendaTomaTurno']?> <?=($general['nombreTurno'])?>.</p>
                    <?
                    if($general['nivelCategorias']){
                        ?>
                        <div class="form-group">
                            <label class="control-label"><?=ucfirst($general['nombreCategorias'])?></label>
                            <select class="form-control" name="categoria" id="categoria">
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
                        </div>
                        <?
                    }
                    ?>
                </div>

                <div class="row">
                    <div class="col">
                        <div id="calendar"></div>
                    </div>
                </div>

            </div>
            <?
            require_once($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
            ?>
        </section>

        <div class="modal fade" role="dialog" id="cargarInfo">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        
                    </div>
                    <div class="modal-body">
                        <form id="formulario">
                            <div class="row">
                                <div class="col">
                                    <h5>Tus datos</h5>
                                    <div class="form-group">
                                        <label class="control-label"><?=$campo['label']?></label>
                                        <div class="input-group">
                                            <?
                                            if($general['tomaTurno']=='telefono'){
                                                ?>
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">+<?=$general['prefijoTelefonico']?></span>
                                                </div>
                                                <input class="form-control required soloNumeros" type="tel" placeholder="Cód area" name="codArea" id="codArea" value="<?=$general['codAreaDefault']?>" minlength="2" maxlength="4">
                                                <?
                                            }
                                            ?>
                                            <input class="form-control required <?=$campo['clase']?>" type="<?=$campo['tipo']?>" placeholder="<?=$campo['placeholder']?>" maxlength="<?=$campo['maxlength']?>" minlength="<?=$campo['minlength']?>" id="campoValidacion" value="" data-toggle="tooltip" data-placement="bottom" title="Aguarda un instante... Estamos verificando tu <?=$campo['label']?>.">
                                        </div>
                                        <small><?=$campo['helper']?></small>
                                    </div>
                                </div>					
                            </div>
                    
                            <div class="row bienvenido d-none">
                                <div class="col-md-12">
                                    <div class="alert alert-success" role="alert">
                                        Bienvenida/o de vuelta <b><span class="nombreCliente"></span></b>!
                                        <span id="alert_bienvenida_textExtra"></span>
                                    </div>
                                </div>
                            </div>
                    
                            <div class="row nuevoPaciente d-none">
                                <?
                                if(@$general['soloRegistrados']){
                                    ?>
                                    <div class="col">
                                        <div class="alert alert-danger" role="alert">
                                            <b><i class="fas fa-times-circle"></i> Lo sentimos!</b> No se encontró ningún <?=$general['nombrePaciente']?> con ese <?=$campo['label']?>. La toma de <?=$general['nombreTurnos']?> es solo para <?=$general['nombrePacientes']?> registrados.
                                        </div>
                                    </div>
                                    <?
                                }else{
                                    ?>
                                    <div class="col">
                                        <div class="form-group">
                                            <label class="control-label">Nombre</label>
                                            <input class="form-control required" type="text" placeholder="" name="nombre" id="nombre">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label class="control-label">Apellido</label>
                                            <input class="form-control required" type="text" placeholder="" name="apellido" id="apellido">
                                        </div>
                                    </div>

                                    <?
                                    if($general['tomaTurno']<>'telefono'){
                                        ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Teléfono móvil</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">+<?=$general['prefijoTelefonico']?></span>
                                                    </div>
                                                    <input class="form-control required soloNumeros" type="tel" placeholder="Cód area" name="codArea" id="codArea" value="<?=$general['codAreaDefault']?>" minlength="2" maxlength="4">
                                                    <input class="form-control required soloNumeros" type="tel" placeholder="<?=str_repeat('X',$general['telLargoMax'])?>" name="telefono" id="telefono" value="" minlength="<?=$general['telLargoMin']?>" maxlength="<?=$general['telLargoMax']?>">
                                                </div>
                                                <small><?=$general['leyendaTelefono']?></small>
                                            </div>
                                        </div>
                                        <?
                                    }
                                }
                                ?>
                            </div>

                            <?
                            if($general['tomaTurno']<>'email'){
                                ?>
                                <div class="campoMail row d-none">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label">Completá tu e-mail para poder enviarte notificaciones de tus turnos</label>
                                            <input class="form-control required" type="email" placeholder="nombre@dominio.com" name="mail" id="mail" value="">
                                        </div>
                                    </div>
                                </div>
                                <?
                            }
                            ?>

                            <input type="hidden" id="idTratamiento" name="idTratamiento" value="" />
                            <input type="hidden" id="idProfesional" name="idProfesional" value="" />
                            <input type="hidden" id="fecha" name="fecha" value="" />
                            <input type="hidden" id="horas" name="horas" value="" />
                            <input type="hidden" id="idPaciente" name="idPaciente" value="" />
                            <input type="hidden" name="action" value="saveExternal" />
                            <input type="hidden" name="<?=$campo['hidden']?>" id="<?=$campo['hidden']?>" value="" />

                        </form>
                    </div>
                    <div class="row d-none border-top border-bottom pt-2 mb-4" id="textoPost">
                        <div class="col">

                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="col px-0">
                            <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
                        </div>
                        <div class="col text-right px-0">
                            <button class="btn btn-primary btn-block loginBtn" disabled><i class="fas fa-calendar-check fa-lg fa-fw"></i> AGENDAR</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?
        require_once($_SERVER['DOCUMENT_ROOT'].'/inc/scripts.php');
        ?>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
		
        <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar-scheduler/1.10.1/scheduler.min.js'></script>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/locale/es.js'></script>
		
        <script>
		
            $('#campoValidacion').tooltip('disable');
		
            $("#campoValidacion").on('blur',function(){
                $("#alert_bienvenida_textExtra").html('')
                if($(this).hasClass('is-valid')){
                    //Verifico si existe
                    $("#<?=$campo['hidden']?>").val($(this).val());
					
                    $('#campoValidacion').tooltip('enable');
                    $('#campoValidacion').tooltip('show');
					
                    $.post('/pacientes/save',{action:'checkPax',value:$(this).val(),codArea:$("#codArea").val()},function(response){

                        console.log(response);
						
                        $('#campoValidacion').tooltip('hide');
                        $('#campoValidacion').tooltip('disable');
                        $(".loginBtn").prop('disabled',true);
						
                        if(response.status=='OK'){
                            $(".nombreCliente").html(response.nombre);
                            <? if($general["creditos"]){ ?>
                                $("#alert_bienvenida_textExtra").html(`<br>
                                Créditos disponibles: ${response.creditosDisponibles}
                                `)
                            <? } ?>
                            $("#idPaciente").val(response.idPaciente);
                            $("#mail").val(response.mail);
                            $(".bienvenido").removeClass('d-none');
                            $(".nuevoPaciente").addClass('d-none');
                            $(".loginBtn").prop('disabled',false);
                        }else if(response.status=='vacio'){
                            Swal.fire('Lo sentimos!','El campo <?=$campo['label']?> no puede estar vacío.','error');

                            $(".nombreCliente").html('');
                            $("#idPaciente").val('');
                            $(".bienvenido").addClass('d-none');
                            $(".campoMail").removeClass('d-none');
                            $(".nuevoPaciente").addClass('d-none');
                        }else{
                            <?
                            if(@$general['soloRegistrados']){
                                ?>
                                $(".nombreCliente").html('');
                                $("#idPaciente").val('');
                                $(".bienvenido").addClass('d-none');
                                $(".campoMail").addClass('d-none');
                                $(".nuevoPaciente").removeClass('d-none');
                                $(".loginBtn").prop('disabled',true);
                                <?
                            }else{
                                ?>
                                $(".nombreCliente").html('');
                                $("#idPaciente").val('');
                                $(".bienvenido").addClass('d-none');
                                $(".nuevoPaciente").removeClass('d-none');
                                $(".campoMail").removeClass('d-none');
                                $(".loginBtn").prop('disabled',false);
                                <?
                            }
                            ?>
                        }
						
                        inicializar();
						
                    })
                }
            })

            
            function inicializar(){
                $('.required:visible').on('blur keyup change',function(e){
                    if($(this).val().length>2){
                        if($(this).prop('type')=='email'){
                            if(isEmail($(this).val())){
                                $(this).removeClass('is-invalid');
                                $(this).addClass('is-valid');
                            }else{
                                $(this).removeClass('is-valid');
                                $(this).addClass('is-invalid');
                            }
                        }else{
                            $(this).removeClass('is-invalid');
                            $(this).addClass('is-valid');
                        }
                    }else{
                        if(($(this).prop('id')=='tratamiento')||($(this).prop('id')=='profesional')||($(this).prop('id')=='codArea')){
                            if($("#tratamiento option:selected").val()==""){
                                $("#tratamiento").removeClass('is-valid');
                                $("#tratamiento").addClass('is-invalid');
                            }else{
                                $("#tratamiento").removeClass('is-invalid');
                                $("#tratamiento").addClass('is-valid');
                            }
                            if($("#profesional option:selected").val()!=''){
                                $("#profesional").removeClass('is-invalid');
                                $("#profesional").addClass('is-valid');
                            }else{
                                $("#profesional").removeClass('is-valid');
                                $("#profesional").addClass('is-invalid');
                            }
                            if($("#codArea").val().length<2){
                                $("#codArea").removeClass('is-valid');
                                $("#codArea").addClass('is-invalid');
                            }else{
                                $("#codArea").removeClass('is-invalid');
                                $("#codArea").addClass('is-valid');
                            }
                        }else{
                            $(this).addClass('is-invalid');
                            $(this).removeClass('is-valid');
                        }
                    }
                })
            }
			
            inicializar();
			
            function isEmail(email) {
                var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                return regex.test(email);
            }
		
		
            function formatDate(date) {
                var d = new Date(date),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

                    if (month.length < 2) month = '0' + month;
                    if (day.length < 2) day = '0' + day;
                    return [year, month, day].join('-');
            }
			
			
            
			
            $('.loginBtn').click(function(e){
				
                var contenidoBoton=$(".loginBtn").html();

                $(".nuevoPaciente .required:visible, .campoMail .required:visible, .required:visible").trigger('change');
				
                e.preventDefault();
                var algunoMal=0;
                var algunoBien=0;
                $.each($('.required:visible'),function(key,element){
                    if($(element).hasClass('is-invalid')){
                        algunoMal++;
                    }
                    if($(element).hasClass('is-valid')){
                        algunoBien++;
                    }
                })

                if((algunoMal<1)&&(algunoBien==$('.required:visible').length)){
					
                    $(".loginBtn").html('<i class="fa fa-spinner fa-spin fa-fw"></i> Cargando...');
                    $(".loginBtn").attr('disabled','disabled');
					
                    $.post('/turnos/save',$('#formulario').serialize(),function(response){
						
                        console.log(response);
						
                        if(response.status=='OK'){

                            var texto=response.nombre+', gracias por elegirnos. Tu turno ha sido reservado correctamente. Te esperamos el '+response.fecha+'.';

                            if(response.textoConfirmacionPublic){
                               texto=response.textoConfirmacionPublic;
                            }

                            Swal.fire({
                                title:'Reservado!',
                                text:texto,
                                type:'success',
                                onClose: () => {
                                    window.location.href=window.location.href;
                                }
                            });
                        }else{
                            if(response.status=='datosIncompletos'){
                                Swal.fire('Lo sentimos!','Verifica los campos marcados en rojo para continuar.','error');
                            }else{
                                if(response.status=='duplicado'){
                                    Swal.fire('Lo sentimos!',response.nombre+', ya tenés cargado un turno el '+response.fecha+'. No podés cargar otro durante el transcurso de otro.','error');
                                }else{
                                    if(response.status=='tomado'){
                                        Swal.fire('Lo sentimos!','El turno solicitado ha sido tomado por otro usuario. Intentalo nuevamente con otros parametros.','error');
                                    }else if(response.status == "tratamientoBloqueado"){
                                        Swal.fire('Lo sentimos!',response.message ,'warning');
                                    }else if(response.status == "sinCreditos"){
                                        Swal.fire('Lo sentimos!',response.message ,'warning');
                                    }else{
                                        Swal.fire('Lo sentimos!',response,'error');
                                    }
                                }
                            }
                        }
						
                        $(".loginBtn").html(contenidoBoton);
                        $(".loginBtn").attr('disabled',false);
						
                    })
                }else{
                    Swal.fire('Lo sentimos!','Verifica los campos marcados en rojo para continuar.','error');
                }
				
            })

















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

            $("#categoria").trigger('change');
			
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
                        right: 'agendaWeek,agendaDay'
                    },
                    firstDay:1,
                    validRange: {
                        start: '<?=date("Y-m-d", strtotime("+".$general['horasAnticipacion'].' hours'))?>',
                        end: '<?=date("Y-m-d",strtotime($general['limiteCupos']))?>'
                    },
                    locale:'es',
                    selectable:false,
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
                    if(strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'mobile') || strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')) {
                    ?>
                        defaultView: 'agendaDay',
                    <?
                    }else{
                    ?>
                        defaultView: 'agendaWeek',
                    <?
                    }
                    ?>
                    allDaySlot:false,
                    editable: false,
                    eventDurationEditable: false,
                    eventClick: function(calEvent, jsEvent, view) {
                        agregarTurno(calEvent);
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

                        if(parseInt(tomados)>parseInt(event.cupo)){
                            tomados=event.cupo;
                        }

                        let horaOriginal = element.find('.fc-time').html();

                        element.find('.fc-time').addClass('tiempo'+event.id);
                        element.find('.fc-time').html('<div class="row align-items-center text-white"><div class="col flex-grow-1">'+horaOriginal+'</div><div class="text-right ml-3 ml-md-0 mr-3 textoDuracion">'+event.duracion+'</div></div>'); 
                        element.find('.fc-title').prepend('<div class="fc-status-public row align-items-center h-100" style="background-color:'+event.colorProfesional+'"><div class="col flex-grow-1">'+event.actividad+'</div></div><div class="detallesEvento row align-items-center h-100"><div class="ml-3 mr-3"><i class="fas fa-circle '+event.colorPuntito+'" style="font-size:0.6rem"></i> '+tomados+'/'+event.cupo+'</div><div class="col flex-grow-1 text-right nombreProfe">'+event.profesional+'<i class="fas fa-user ml-1" style="font-size:0.6rem"></i></div></div>'); 
                    },
                    
                    eventAfterRender: function( event, element, view ) {
                        // console.log(event);
                        
                        if(element.find('.fc-time').width()<100){
                            element.find('.textoDuracion').addClass('d-none');
                        }else{
                            element.find('.textoDuracion').removeClass('d-none');
                        }
                        if(element.find('.fc-title').width()<175){
                            element.find('.nombreProfe').addClass('d-none');
                        }else{
                            element.find('.nombreProfe').removeClass('d-none');
                        }
                    },

                    viewRender: function( view, element ) {
                        // Drop the second param ('day') if you want to be more specific
                        if(moment().isAfter(view.intervalStart, 'day')) {
                            $('.fc-prev-button').addClass('fc-state-disabled');
                        } else {
                            $('.fc-prev-button').removeClass('fc-state-disabled');
                        }
                    }
					
                });
            },500);

            function agregarTurno(event){
                console.log(event);

                //Voy a verificar si hay lugar aún y sino le muestro una alerta y vuelvo a cargar el calendario.
                //También tengo que verificar que no haya empezado ya y la cantidad de horas de anticipación
                if(event.start.format('YYYY-MM-DD HH:mm:ss')<moment().format('YYYY-MM-DD HH:mm:ss')){
                    Swal.fire('Lo sentimos!','El turno ya ha comenzado.','error');
                    event.start.format('YYYY-MM-DD HH:mm:ss')
                    return false;
                }

                $.post('/turnos/save', {action : 'checkLugar', idProfesional : event.idProfesional, idTratamiento : event.idTratamiento, fechaInicio : event.start.format('YYYY-MM-DD HH:mm:ss')}, function(response){

                    console.log(response);

                    if(response.status=='OK'){
                        //Caso contrario abro el modal
                        //Completo los campos ocultos
                        $("#idProfesional").val(event.idProfesional);
                        $("#idTratamiento").val(event.idTratamiento);
                        $("#fecha").val(event.start.format('DD/MM/YYYY'));
                        $("#horas").val(event.start.format('HH:mm'));

                        $("#cargarInfo .modal-header").html(`
                            <div class="col-6 mx-0 px-0">
                                <h3 class="modal-title">${event.actividad}</h3>
                            </div>
                            <div class="col align-items-center text-right mx-0 px-0">
                                <h6 class="my-0">${event.profesional} <i class="fas fa-user ml-1"></i></h6>
                                <small>${event.start.format('DD/MM/YYYY HH:mm')} - ${event.end.format('HH:mm')} <i class="fas fa-calendar ml-1"></i></small>
                            </div>`).css('background-color',event.colorProfesional).css('color',event.textColor);
                        $("#cargarInfo").modal('show');
                
                        $("#cargarInfo").on('shown.bs.modal', function(){
                            inicializar();
                        });
                    }else{
                        Swal.fire('Lo sentimos!',response.text,'error');
                        $('#calendar').fullCalendar('refetchEvents');
                        return false;
                    }
                })

            }
        </script>
    </body>
</html>