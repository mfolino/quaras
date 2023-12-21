<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();

$fecha=$_GET['fecha'];
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion=ucwords($general['nombreTurnos']);
        $subseccion='Agregar';
        require_once(incPath.'/head.php');
        ?>
    </head>
    <body class="app sidebar-mini rtl">
        <div class="tile-body">
            <form id="formulario">
                <div class="row">
                    <div class="col">
                        <h5><?=ucwords($general['nombrePaciente'])?></h5>
                        <div class="form-group">

                            <select class="selectPaciente form-control required" name="paciente">
                                <option value="">Seleccione...</option>
                                <option value="NN">Nuevo <?=($general['nombrePaciente'])?></option>
                            </select>

                        </div>
                    </div>					
                </div>
                <div class="row nuevoPaciente d-none">
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
                    if($general['tomaTurno']=='dni'){
                        ?>
                        <div class="col">
                            <div class="form-group">
                                <label class="control-label"><?=$general['nombreDNI']?></label>
                                <input class="form-control required soloNumeros" type="tel" placeholder="<?for($i=0;$i<$general['largoDNI'];$i++){echo 'X';}?>" name="dni" value="" maxlength="<?=$general['largoDNI']?>">
                            </div>
                        </div>
                        <?
                    }
                    ?>
                </div>
				
                <div class="row d-none datosAdicionales">
                    <div class="col">
                        <div class="form-group">
                            <label class="control-label">Teléfono</label>
                            <div class="input-group">
                            <div class="input-group-prepend">
                                <input id="inputCodArea" class="form-control required " name="codArea" type="number" value="<?=$general['codAreaDefault']?>" >
                                <!-- <select class="form-control required" name="codArea" id="codArea">
                                    <option value="<?=$general['codAreaDefault']?>"><?=$general['codAreaDefault']?></option>
                                </select> -->
                            </div>
                            <input class="form-control required soloNumeros" type="tel" placeholder="Número" name="telefono" id="telefono" value="" minlength="<?=$general['telLargoMin']?>" maxlength="<?=$general['telLargoMax']?>">
                            </div>
                            <small><?=$general['leyendaTelefono']?></small>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="control-label">E-mail</label>
                            <input class="form-control" type="email" placeholder="nombre@dominio.com" name="mail" id="mail" value="">
                        </div>
                    </div>
                </div>
					
					
					
					
				
                <div class="row">
                    <div class="col">
                        <h5>Reserva</h5>
                    </div>
                </div>
				
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label"><?=ucwords($general['nombreProfesional'])?></label>
                            <select id="profesional" name="profesional" class="form-control required">
                            <option value="">--</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label"><?=ucwords($general['nombreObraSocial'])?></label>
                            <select class="form-control required" name="tratamiento" id="tratamiento" disabled>
                                <option value="">Seleccione <?=ucwords($general['nombreProfesional'])?>...</option>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="fecha" value="<?=date("d/m/Y",strtotime($fecha))?>">
                    <input type="hidden" name="horas" value="<?=date("H",strtotime($fecha))?>">
                    <input type="hidden" name="minutos" value="<?=date("i",strtotime($fecha))?>">

                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <label class="control-label">Fecha</label>
                            <input class="form-control required" type="text" placeholder="DD/MM/AAAA" id="fecha"  value="<?=date("d/m/Y",strtotime($fecha))?>" disabled>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <label class="control-label">Hora</label>
                            <select id="horas"  class="form-control required" disabled>
                            <option value="<?=date("H",strtotime($fecha))?>"><?=date("H",strtotime($fecha))?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <label class="control-label">Minutos</label>
                            <select id="minutos"  class="form-control required" disabled>
                            <option value="<?=date("i",strtotime($fecha))?>"><?=date("i",strtotime($fecha))?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <?
                if($general['turnosRepetitivos']){
                    ?>
                    <div class="row repetir datosPaciente">
                        <div class="col">
                            <div class="form-group">
                                <label class="control-label">Repetir turno</label>
                                <select id="lapso" name="lapso" class="form-control required">
                                    <option value="no">No</option>
                                    <option value="semana">Semanalmente</option>
                                    <option value="quincena">Quincenalmente</option>
                                    <option value="mes">Mensualmente</option>
                                </select>
                            </div>
                        </div>
                        <div class="col colRepeticion d-none">
                            <div class="form-group">
                                <label class="control-label">Repetir turno</label>
                                <input type="number" min="1" max="50" step="1" name="repeticion" id="repeticion" class="form-control required" value="1" />
                            </div>
                        </div>
                    </div>
                    <?
                }
                ?>

                <? if (@$general['campoObservaciones'] == 2 || @$general['campoObservaciones'] == 3) { ?>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="control-label"><?= $general['campoObservaciones_titulo'] ?></label>
                                <textarea 
                                    class="form-control <?= ($general['campoObservaciones_required']) ? 'required' : '' ?>" 
                                    name="comentarios" 
                                    id="comentarios"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                <? } ?>

                <input type="hidden" id="idObraSocial" value="" />
                <input type="hidden" name="action" value="save" />
            </form>
			  
        </div>
        <div class="tile-footer">
            <button class="btn btn-primary mr-2" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-check-circle"></i>Agendar</button>
            <a class="btn btn-secondary" href="#" onclick="parent.parent.$('.modal').modal('hide')"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar</a>
        </div>
        <?
        require_once(incPath.'/scripts.php');
        ?>
		
        <script>

            $("#lapso").on('change',function(){
                if($("#lapso option:selected").val()!='no'){
                    $(".colRepeticion").removeClass('d-none');
                }else{
                    $(".colRepeticion").addClass('d-none');
                }
            })
            
            $('#codArea').select2({
                ajax: {
                    url: 'https://turnos.app/api/codArea',
                    dataType: 'jsonp',
                    data: function (params) {
                        var query = {
                            codArea: params.term
                        }
                        return query;
                    },
                    processResults: function (data) {
                        // console.log(data);
                        return {
                            results: data.results
                        };
                    }
                }
            });
            // $("#codArea").select2();
		
            function getSobreturnoProfesional(minuto){
                $.post('/turnos/save',{action:'getSobreturno',fecha:$("#fecha").val(),hora:$("#horas").val(),minuto:minuto,idProfesional:'<?=@$_GET['profesional']?>'},function(resultado){

                    console.log(resultado);
                    $("#profesional").html(resultado.profesionales);

                    $("#profesional").on('change',function(e){
                        e.preventDefault();
						
                        $("#tratamiento").attr('disabled','disabled');

                        if($("#profesional option:selected").val()){
						
                            $.post('/obrasSociales/save',{action:'getTratamientos',profesional:$("#profesional option:selected").val()},function(response){
								
                                console.log(response);

                                if(response.status=='OK'){

                                    $("#tratamiento").html('');

                                    $.each(response.tratamientos, function(idTratamiento, tratamiento){
                                        $("#tratamiento").append( `<option value="${idTratamiento}">${tratamiento}</option>`);
                                    })

                                    $("#tratamiento").attr('disabled',false).trigger('change');
                                }else{
                                    Swal.fire("Lo sentimos", "No hay tratamientos disponibles para este profesional", "error");
                                }
								
                            })

                        }
                    });

                    $("#profesional").trigger('change');
                })
            }
			
            getSobreturnoProfesional($("#minutos").val());
		
            function formatDate(date) {
                var d = new Date(date),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

                    if (month.length < 2) month = '0' + month;
                    if (day.length < 2) day = '0' + day;
                    return [year, month, day].join('-');
            }
			
			
            $('.selectPaciente').css('width', '100%');
		
            $('.selectPaciente').select2({
                ajax: {
                    url: '/pacientes/save',
                    dataType: 'json',
                    data: function (params) {       
                              
                        var query = {
                            query: params.term,
                            from:'turnos',
                            action: "getPacientesSelect"
                        }
                        return query;
                    },
                    processResults: function (data) {
                        console.log(data);
                        return {
                            results: data.results
                        };
                    }
                }
            });

            $('.selectPaciente').on('select2:select', function (e) {
				
                var seleccion=e.params.data;

                console.log(seleccion);
				
                if($(".selectPaciente option:selected").data('estadoobrasocial')==0){
                    Swal.fire(
                        'Lo sentimos!',
                        'La obra social del <?=($general['nombrePaciente'])?> se encuentra inactiva. No es posible asignar <?=($general['nombreTurnos'])?> para este prestador.',
                        'error'
                    )
					
                    $("#enviarForm").attr('disabled','disabled');
					
                }else{
                    $("#enviarForm").attr('disabled',false);
                }
				
				
                $("#telefono").val('');
                $("#mail").val('');
                if($(this).val()=='NN'){
                    $(".nuevoPaciente").removeClass('d-none');
                }else{
                    $(".nuevoPaciente").addClass('d-none');
                    $("#mail").val(seleccion.mail);
                    $("#telefono").val(seleccion.telefono);
                    $('#inputCodArea').val(seleccion.codArea);
                    /* $("#codArea").val(seleccion.codArea).trigger('change'); */
                    /* $("#codArea").select2() */
                }
				
                $(".datosAdicionales").removeClass('d-none');
                $(".datosOrden").removeClass('d-none');
				
                $('#fechaCumpleanos').datepicker({
                    format: "dd/mm/yyyy",
                    weekStart: 1,
                    startView: 3,
                    todayBtn: true,
                    clearBtn: true,
                    language: "es",
                    autoclose: true,
                    todayHighlight: true
                })
				
            });
			
            $('#fecha').datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
                todayHighlight: true,
                startDate: '<?=date("d/m/Y")?>',
                // daysOfWeekDisabled: '0,6',
                <?
                db_query(0,"select fecha from feriados");
                $bloqueos='';
                for($i=0;$i<$tot;$i++){
                    $nres=$res->data_seek($i);
                    $row=$res->fetch_assoc();
                    $bloqueos.='"'.date("d/m/Y",strtotime($row['fecha'])).'",';
                }
                if($tot>0){
                    echo 'datesDisabled: ['.$bloqueos.'],';
                }
                ?>
                weekStart: 1
            }).on('changeDate', function(e){
                $("#alertaSobreturno").addClass('d-none');
                $.post('/turnos/save',{action:'getHours',fecha:formatDate(e.date)},function(resultado){
                    // console.log(resultado);
                    if(resultado!='NO'){
                        $("#horas").prop('readonly',false);
                        $("#horas").html(resultado);
                        $("#minutos").prop('readonly',true);
                        $("#minutos").html('<option value="">--</option>');
                    }else{
                        $("#horas").prop('readonly',true);
                        $("#horas").html('<option value="">No hay horarios disponibles</option>');
                        $("#minutos").prop('readonly',true);
                        $("#minutos").html('<option value="">--</option>');
                    }
                })
            });
			
            $("#horas").change(function(){
                $("#alertaSobreturno").addClass('d-none');
                $.post('/turnos/save',{action:'getMinutes',fecha:$("#fecha").val(),hora:$(this).val()},function(resultado){
                    // console.log(resultado);
                    if(resultado!='NO'){
                        $("#minutos").prop('readonly',false);
                        $("#minutos").html(resultado);
                    }else{
                        $("#minutos").prop('readonly',true);
                        $("#minutos").html('<option value="">No hay <?=($general['nombreTurnos'])?> disponibles</option>');
                    }
                })
            })
			
            $("#minutos").change(function(){
                getSobreturnoProfesional($(this).val());
            })
		
            $("#enviarForm").click(function(){
                algunoMal=0;
                $(".required:visible").each(function(key){
                    if($(this).val() != null && $(this).val().length < 1){
                        $(this).addClass('is-invalid');
                        $(this).removeClass('is-valid');
                        algunoMal=1;
                    }else{
                        $(this).addClass('is-valid');
                        $(this).removeClass('is-invalid');
                    }
                })
				
                //Si está todo bien submiteo
                if(algunoMal==0){
                    $.post('/turnos/save.php',$("#formulario").serialize(),function(resultado){
                        console.log(resultado);
                        if(resultado=='OK'){
                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'El <?=($general['nombreTurno'])?> ha sido guardado con éxito'
                            },{
                                // settings
                                type: "success",
                                allow_dismiss: true,
                                newest_on_top: false,
                                showProgressbar: false,
                                onClose: <?=($_GET['from']=='calendario') ? 'function(){parent.parent.$("#calendar").fullCalendar( "refetchEvents");parent.parent.$(".modal").modal("hide")}' : 'window.location.href=\'/turnos/calendario\''?>,
                                delay:<?=($_GET['from']=='calendario') ? '1500' : '6000'?>
                            });
                        }else{
                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'Ha ocurrido un error al intentar guardar el <?=($general['nombreTurno'])?>. '+resultado
                            },{
                                // settings
                                type: "warning",
                                allow_dismiss: true,
                                newest_on_top: false,
                                showProgressbar: false
                            });
                        }
                    })
                }
				
            })
        </script>
		
        <?
        // include(incPath.'/analytics.php');
        ?>
    </body>
</html>