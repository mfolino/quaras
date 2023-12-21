<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();

$fecha=explode(',',$_GET['fecha']);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion='Turnos';
        $subseccion='Agregar';
        require_once(incPath.'/head.php');
        ?>
    </head>
    <body class="app sidebar-mini rtl">
            <div class="tile-body">
              <form id="formulario">
                <div class="row">
                    <div class="col-md-2 col-sm-2">
                        <div class="form-group">
                          <label class="control-label">Hora</label>
                          <select id="horas" name="horas" class="form-control required" readonly>
                            <?=($_GET['from']=='calendario') ? '<option value="'.sprintf("%02d", $fecha[3]).'">'.sprintf("%02d", $fecha[3]).'</option>' : '<option value="">Seleccione fecha...</option>'?>
                          </select>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-2">
                        <div class="form-group">
                          <label class="control-label">Minutos</label>
                          <select id="minutos" name="minutos" class="form-control required" readonly>
                            <?=($_GET['from']=='calendario') ? '<option value="'.sprintf("%02d", $fecha[4]).'">'.sprintf("%02d", $fecha[4]).'</option>' : '<option value="">--</option>'?>
                          </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <div class="form-group">
                          <label class="control-label">Profesional</label>
                          <select id="profesional" name="profesional" class="form-control required" readonly>
                            <option value="">--</option>
                          </select>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="fecha" id="fecha" value="<?=$fecha[0].'-'.sprintf("%02d", ($fecha[1]+1)).'-'.$fecha[2]?>" />
                <input type="hidden" name="idTurno" id="idTurno" value="<?=$_GET['idTurno']?>" />
                <input type="hidden" name="action" value="cambiarHorario" />
              </form>
            </div>
            <div class="tile-footer">
              <button class="btn btn-primary" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-check-circle"></i>Agendar</button>&nbsp;&nbsp;&nbsp;<a class="btn btn-secondary" href="#" onclick="parent.parent.$('#calendar').fullCalendar('refetchEvents');parent.parent.$('.modal').modal('hide')"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar</a>
              <div class="alert alert-danger float-right d-none" id="alertaSobreturno">
                    <strong>Sobreturno!</strong>
                </div>
            </div>
        <?
        require_once(incPath.'/scripts.php');
        ?>
        <!-- Page specific javascripts-->
        <script type="text/javascript" src="/js/plugins/bootstrap-notify.min.js"></script>
        <script>
            function getSobreturnoProfesional(minuto){
                $.post('/turnos/save',{action:'getSobreturno',fecha:$("#fecha").val(),hora:$("#horas").val(),minuto:minuto},function(resultado){
                    resultado=resultado.split('|');
                    if(resultado[0]!='NO'){
                        $("#alertaSobreturno").removeClass('d-none');
                    }else{
                        $("#alertaSobreturno").addClass('d-none');
                    }
                    $("#profesional").html('<option value="'+resultado[1]+'">'+resultado[2]+'</option>');
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
			
            $("#alertaSobreturno").addClass('d-none');
            $.post('/turnos/save',{action:'getHours',fecha:$("#fecha").val()},function(resultado){
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
			
            $("#horas").change(function(){
                $("#alertaSobreturno").addClass('d-none');
                $.post('/turnos/save',{action:'getMinutes',fecha:$("#fecha").val(),hora:$(this).val()},function(resultado){
                    // console.log(resultado);
                    if(resultado!='NO'){
                        $("#minutos").prop('readonly',false);
                        $("#minutos").html(resultado);
                    }else{
                        $("#minutos").prop('readonly',true);
                        $("#minutos").html('<option value="">No hay turnos disponibles</option>');
                    }
                })
            })
			
            $("#minutos").change(function(){
                getSobreturnoProfesional($(this).val());
            })
		
            $("#enviarForm").click(function(){
                algunoMal=0;
                $(".required").each(function(key){
                    if($(this).is(":visible")){
                        if($(this).val().length<1){
                            $(this).addClass('is-invalid');
                            $(this).removeClass('is-valid');
                            algunoMal=1;
                        }else{
                            $(this).addClass('is-valid');
                            $(this).removeClass('is-invalid');
                        }
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
                                message: 'El turno ha sido actualizado con éxito'
                            },{
                                // settings
                                type: "success",
                                allow_dismiss: true,
                                newest_on_top: false,
                                showProgressbar: false,
                                onClose: function(){parent.parent.$("#calendar").fullCalendar( "refetchEvents");parent.parent.$(".modal").modal("hide")},
                                delay:1500
                            });
                        }else{
                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'Ha ocurrido un error al intentar guardar el turno. '+resultado
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