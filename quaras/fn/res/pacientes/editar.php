<?
require_once($_SERVER["DOCUMENT_ROOT"].'/inc/fn.php');
AuthController::checkLogin();


if(isset($_GET['id'])){
    db_query(0,"select * from pacientes where idPaciente='".$_GET['id']."'");

    if($general['bloquearTurnosAPacientesPorTratamiento']){
        $tratamientos=[];
        db_query(1,"select idTratamiento from pacientes_tratamientos where idPaciente='".$_GET['id']."'");
        for($i1=0;$i1<$tot1;$i1++){
            $nres1=$res1->data_seek($i1);
            $row1=$res1->fetch_assoc();
            $tratamientos[$row1['idTratamiento']]=1;
        }
        /* Util::printVar($tratamientos, '186.138.206.135', true); */
    }
}


if($general["plan"] && Migration::existTableInDB('creditos_planes')){
    $planes = db_getAll("SELECT * FROM creditos_planes WHERE estado = 'A' ");

    if(isset($_GET['id'])){
        $planPaciente = db_getOne("SELECT * FROM creditos_pacientes WHERE idPaciente = {$_GET['id']}");
    }
}

?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion=ucwords($general['nombrePacientes']);

        $subseccion= isset($_GET['id']) ? 'Editar' : 'Agregar';

        require_once(incPath.'/head.php');
        ?>
    </head>
    <body class="app sidebar-mini rtl">
        <?
        if($_GET['from']<>'turnos'){
            require_once(incPath.'/header.php');
            require_once(incPath.'/sidebar.php');
		
        ?>
        <main class="app-content">
            <div class="app-title">
                <div>
                    <h1><i class="fa fa-users"></i> <?=ucwords($general['nombrePacientes'])?></h1>
                    <p>Utilice este listado para ver de un rápido vistazo a los <?=($general['nombrePacientes'])?> y administrar los mismos.</p>
                </div>
                
                <?php if(isset($_GET['id'])){ ?>
                    <a class="btn btn-outline-warning icon-btn" href="<?=($_GET['from']=='calendario') ? '/turnos/calendario' : '/pacientes/listado'?>"><i class="fa fa-arrow-left"></i>Volver atrás</a>
                <?php }else{ ?>
                    <a class="btn btn-outline-warning icon-btn" href="/pacientes/listado"><i class="fa fa-arrow-left"></i>Volver atrás</a>
                <?php } ?>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="tile">
            <h3 class="tile-title"><i class="fa fa-plus"></i> <?= isset($_GET["id"]) ? 'Editar' : 'Agregar' ?> <?=($general['nombrePaciente'])?></h3>
            <div class="tile-body">
            <?
        }
        ?>
              <form id="formulario">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                          <label class="control-label">Nombre</label>
                          <input class="form-control required" type="text" placeholder="" name="nombre" value="<?=$row['nombre'] ?? ''?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                          <label class="control-label">Apellido</label>
                          <input class="form-control required" type="text" placeholder="" name="apellido" value="<?=$row['apellido'] ?? ''?>">
                        </div>
                    </div>
                    <?
                    if($general['tomaTurno']=='dni'){
                        ?>
                        <div class="col">
                            <div class="form-group">
                            <? $placeholderDNI = ''; for($i=0;$i<$general['largoDNI'];$i++){$placeholderDNI .= 'X';} ?>
                            <label class="control-label"><?=$general['nombreDNI']?></label>
                            <input class="form-control required soloNumeros" type="tel" placeholder="<?=$placeholderDNI?>" name="dni" value="<?=$row['dni'] ?? ''?>" maxlength="<?=$general['largoDNI']?>">
                            </div>
                        </div>
                        <?
                    }
                    ?>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                          <label class="control-label">Teléfono</label>
                          <div class="input-group">
                            <div class="input-group-prepend">
                                <? $codigoDeArea = $row['codArea'] ?? $general['codAreaDefault'] ?>
                            
                                <? if($general["codArea_quitarSelector"]){ ?>
                                    <input class="form-control required soloNumeros" type="tel" name="codArea" id="codArea" value="<?=$row['codArea'] ?? $general['codAreaDefault'] ?>">
                                <? }else{ ?>
                                    <select class="form-control required" name="codArea" id="codArea">
                                        <option value="<?=$codigoDeArea?>"><?=$codigoDeArea?></option>
                                    </select>
                                <? } ?>

                            </div>
                            <input class="form-control required soloNumeros" type="tel" placeholder="Número" name="telefono" value="<?=$row['telefono']??''?>" minlength="<?=$general['telLargoMin']?>" maxlength="<?=$general['telLargoMax']?>">
                          </div>
                          <small><?=$general['leyendaTelefono']?></small>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                          <label class="control-label">E-mail</label>
                          <input class="form-control" type="email" placeholder="nombre@dominio.com" name="mail" value="<?=$row['mail']??''?>">
                        </div>
                    </div>
                </div>

                <!-- BLoqueo de pacientes por tratamiento -->
                <? if($general['bloquearTurnosAPacientesPorTratamiento']){ ?>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                            <label class="control-label"><?=ucwords($general['nombreObrasSociales'])?> bloqueadas</label>

                            <select multiple="true" class="form-control w-100" name="tratamientos[]" id="tratamientos" style="width:100%">
                                <?
                                db_query(1,"select idTratamiento, nombre from tratamientos where estado='A'");
                                for($i1=0;$i1<$tot1;$i1++){
                                    $nres1=$res1->data_seek($i1);
                                    $row1=$res1->fetch_assoc();
                                    ?>
                                    <option 
                                    value="<?=$row1['idTratamiento']?>"
                                    <?=(isset($tratamientos) && $tratamientos[$row1['idTratamiento']]) ? ' selected' : ''?>><?=$row1['nombre']?>
                                    </option>
                                    <?
                                }
                                ?>
                            </select>

                            </div>
                        </div>
                    </div>
                <? } ?>
                

                <!-- ----------------------- -->
                <!--    PLAN DE CREDITOS     -->
                <!-- ----------------------- -->
                <? if($general['creditos']){ ?>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="control-label">Plan de créditos</label>
                                <select class="form-control w-100 required" name="plan" id="plan" style="width:100%">
                                    <option value="">-- Seleccione un plan --</option>
                                    <? foreach ($planes as $plan) { ?>
                                        <option 
                                            value="<?=$plan->idPlan?>" 
                                            <?=(isset($planPaciente) && $planPaciente->idPlan == $plan->idPlan) ? 'selected' : ''?> 
                                            data-cantidad="<?=$plan->cantidad?>"
                                        >
                                            <?=ucfirst($plan->nombre)?>
                                        </option>
                                    <? } ?>
                                </select>
                            </div>
                        </div>
                       <!--  <div class="col">
                            <div class="form-group">
                                <label class="control-label" data-toggle="tooltip" title="Cantidad de créditos que se acreditarán al guardar">Cant. créditos <i class="fas fa-info-circle"></i></label>
                                <input type="number" class="form-control" name="cantidad" id="cantidad" />
                            </div>
                        </div> -->
                    </div>
                <? } ?>
                


                <? if(isset($_GET['id'])){ ?>
                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" name="id" value="<?=$_GET['id']?>" />
                    <input type="hidden" id="idPlan" value="<?=$row['plan']?>" />
                <? }else{ ?>
                    <input type="hidden" name="action" value="save" />
                <? } ?>
              </form>
              <? if($_GET['from']<>'turnos'){ ?>
            </div>
            <div class="tile-footer">
            <? } ?>

                <? if(isset($_GET['id'])){ ?>
                    <button class="btn btn-primary" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-check-circle"></i>Guardar</button>&nbsp;&nbsp;&nbsp;<a class="btn btn-secondary" href="<?=($_GET['from']=='turnos') ? '#" onclick="parent.parent.$(\'.modal\').modal(\'hide\')' : '/pacientes/listado'?>"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar</a>
                <? }else{ ?>
                    <button class="btn btn-primary" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-check-circle"></i>Guardar</button>&nbsp;&nbsp;&nbsp;<a class="btn btn-secondary" href="/pacientes/listado"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar</a>
                <? } ?>
              
              
              <?
                if($_GET['from']<>'turnos'){
                    ?>
            </div>
          </div>
                </div>
            </div>
        </main>
        <?
                }
                ?>
		
        <?
        require_once(incPath.'/scripts.php');
        ?>
        <script>
            $("#tratamientos").select2();

            $("#plan").on('change', function(){
                $("#cantidad").val($(this).find(':selected').data('cantidad'));
            })

            $("#plan").trigger('change');

            <? if(!$general["codArea_quitarSelector"]){ ?>
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
            <? } ?>
		
            <? if(isset($_GET['id'])){ ?>
                $.post("/pacientes/save.php",{action:'getPlanes',idObraSocial:'<?=$row['idObraSocial']?>',idPlan:'<?=$row['plan']?>'},function(resultado){
                    if(resultado=='Todos'){
                        $(".detallePlanes").addClass('d-none');
                    }else{
                        $("#plan").html(resultado);
                        $(".detallePlanes").removeClass('d-none');
                    }
                })
            <? } ?>
		
            jQuery.fn.forceNumeric = function () {

                 return this.each(function () {
                     $(this).keydown(function (e) {
                         var key = e.which || e.keyCode;

                         if (!e.shiftKey && !e.altKey && !e.ctrlKey &&
                         // numbers   
                             key >= 48 && key <= 57 ||
                         // Numeric keypad
                             key >= 96 && key <= 105 ||
                         // comma, period and minus, . on keypad
                            key == 190 || key == 188 || key == 109 || key == 110 ||
                         // Backspace and Tab and Enter
                            key == 8 || key == 9 || key == 13 ||
                         // Home and End
                            key == 35 || key == 36 ||
                         // left and right arrows
                            key == 37 || key == 39 ||
                         // Del and Ins
                            key == 46 || key == 45)
                             return true;

                         return false;
                     });
                 });
             }
			 
             <? if(!isset($_GET['id'])){ ?>
                $("#dni").keyup(function(e){
                    $("#dni").removeClass('is-invalid');
                    $("#dniUsado").addClass('d-none');
                    $("#enviarForm").attr('disabled',false);
                    $.post('/pacientes/save',{action:'checkDni',dni:$(this).val()},function(resultado){
                        if(resultado=='repetido'){
                            $("#dni").addClass('is-invalid');
                            $("#dniUsado").removeClass('d-none');
                            $("#enviarForm").attr('disabled',true);
                        }
                    })
                })
             <? } ?>

             $(".numberinput").forceNumeric();
			
            $('.selectObraSocial').select2();
            $('.selectObraSocial').on('select2:select', function (e) {
                if($(this).val()==1){
                    $(".codSeguridad").removeClass('d-none');
                }else{
                    $(".codSeguridad").addClass('d-none');
                }
                if($(this).val()==62){
                    $("#numeroCarnet").removeClass('required');
                    $("#numeroCarnet").removeClass('is-invalid');
                }else{
                    $("#numeroCarnet").addClass('required');
                }
                $.post("/pacientes/save.php",{action:'getPlanes',idObraSocial:$(this).val(),idPlan:$("#idPlan").val()},function(resultado){
                    if(resultado=='Todos'){
                        $(".detallePlanes").addClass('d-none');
                    }else{
                        $("#plan").html(resultado);
                        $(".detallePlanes").removeClass('d-none');
                    }
                })
            });
			
            $('#fechaNacimiento').datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
                todayHighlight: true,
                startView:'decade'
            });
		
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
                    
                    let originalButton	= $("#enviarForm").html();
                    preloaderButton('show', originalButton, 'enviarForm');

                    $.post('/pacientes/save.php',$("#formulario").serialize(),function(resultado){
                        console.log(resultado);
                        
                        preloaderButton('hide', originalButton, 'enviarForm');

                        if(resultado=='OK'){
                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'El <?=($general['nombrePaciente'])?> ha sido guardado con éxito'
                            },{
                                // settings
                                type: "success",
                                allow_dismiss: true,
                                newest_on_top: false,
                                showProgressbar: false,

                                <? if(isset($_GET['id'])){ ?>
                                    onClose: window.location.href='<?=($_GET['from']=='turnos') ? '/pacientes/agregarOrden?from=turnos&id='.$_GET['id'].'&title='.$_GET['title'].'&start='.$_GET['start'].'&idTurno='.$_GET['idTurno'] : '/pacientes/listado'?>',
                                <? }else{ ?>
                                    onClose: window.location.href='/pacientes/listado',
                                <? } ?>
                                delay:6000
                            });
                        }else{
                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'Ha ocurrido un error al intentar guardar el <?=($general['nombrePaciente'])?>. '+resultado
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