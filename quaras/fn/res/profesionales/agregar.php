<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();
AuthController::checkSuperAdmin();


if(!isset($_GET['id'])) $_GET['id'] = null;

if($_GET['id']){
    db_query(0,"select * from profesionales where idProfesional=".$_GET['id']);
	
    $tratamientos=array();
    db_query(1,"select idTratamiento from profesionales_tratamientos where idProfesional='".$row['idProfesional']."'");
    for($i1=0;$i1<$tot1;$i1++){
        $nres1=$res1->data_seek($i1);
        $row1=$res1->fetch_assoc();
        $tratamientos[$row1['idTratamiento']]=$row1["idTratamiento"];
    }
	
    $titulo='Editar';
    $icono='fa-edit';
}else{
    $titulo='Agregar';
    $icono='fa-plus';

    db_query(0,"SELECT * FROM profesionales WHERE estado = 'A' ORDER BY nombre ASC");
    if($tot>=Util::getAgendas($general['plan'])){
        header("location:listado");
        die();
    }
    unset($row);
}


?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion=ucwords($general['nombreProfesionales']);
        $subseccion='Agregar';
        require_once(incPath.'/head.php');
        ?>
        <link rel="stylesheet" type="text/css" href="<?=$cdn?>/css/plugin/bootstrap-colorpicker.min.css" />
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
                    <p>Utilice este listado para ver de un rápido vistazo las <?=($general['nombreProfesionales'])?> y administrar los mismos.</p>
                </div>
                <?/*<ul class="app-breadcrumb breadcrumb side">
                    <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                    <li class="breadcrumb-item active"><a href="#">Profesionales</a></li>
                </ul>*/?>
                <a class="btn btn-outline-warning icon-btn" href="/profesionales/listado"><i class="fa fa-arrow-left"></i>Volver atrás</a>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="tile">
            <h3 class="tile-title"><i class="fa <?=$icono?>"></i> <?=$titulo?> <?=($general['nombreProfesional'])?></h3>
            <div class="tile-body">
              <form id="formulario">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                          <label class="control-label">Nombre</label>
                          <input class="form-control required" type="text" placeholder="Nombre" name="nombre" value="<?=$row['nombre']??''?>">
                        </div>
                    </div>
                    <?
                    if($general['accesoProfesionales']){
                        ?>
                        <div class="col">
                            <div class="form-group">
                            <label class="control-label" data-toggle="tooltip" title="Recordá que el correo no debe repetirse con el de otros profesionales. El mismo puede no ser un correo real.">E-mail <i class="fas fa-info-circle"></i></label>
                            <input class="form-control" type="email" placeholder="nombre@dominio.com" name="email" value="<?=$row['email']?>">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                            <label class="control-label">Contraseña de acceso</label>
                            <input class="form-control<?=(!$_GET['id']) ? ' required' : ''?>" type="password" placeholder="XXXXXX" name="password" value="">
                            <?
                            if($_GET['id']){
                                ?>
                                <small>Dejar vacío en caso de no querer modificar</small>
                                <?
                            }
                            ?>
                            </div>
                        </div>
                        <?
                    }
                    ?>
                    <div class="col-md-2">
                        <div class="form-group">
                          <label class="control-label">Color de ref.</label>
                          <input class="form-control required" type="text" placeholder="#000000" name="color" id="color" value="#<?=($row['color']) ? $row['color'] : Util::random_color()?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                          <label class="control-label" data-toggle="tooltip" title="Si está inactivo no podrá ser utilizado en ningún lugar de la app.">Estado <i class="fas fa-info-circle"></i></label>
                          <select class="form-control" name="estado" id="estado">
                            <option value="A"<?=(isset($row['estado']) && $row['estado']=='A') ? ' selected' : ''?>>Activo</option>
                            <option value="I"<?=(isset($row['estado']) && $row['estado']=='I') ? ' selected' : ''?>>Inactivo</option>
                          </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                          <label class="control-label" data-toggle="tooltip" title="Habitual son los que se repiten todas las semanas por igual. Los horarios puntuales permiten indicar fechas y horarios puntuales para cada una de ellas.">Horario <i class="fas fa-info-circle"></i></label>
                          <select class="form-control" name="tipo" id="tipo">
                            <option value="H"<?=(isset($row['tipo']) && $row['tipo']=='H') ? ' selected' : ''?>>Habitual</option>
                            <option value="P"<?=(isset($row['tipo']) && $row['tipo']=='P') ? ' selected' : ''?>>Puntual</option>
                          </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                          <label class="control-label" data-toggle="tooltip" title="Si es sólo privado estará visible solamente para tomar turnos desde el panel administrativo.">Sólo privado <i class="fas fa-info-circle"></i></label>
                          <select class="form-control w-100" name="privado" id="privado" style="width:100%">
                            <option value="0"<?=($row['privado']==0) ? ' selected' : ''?>>No</option>
                            <option value="1"<?=($row['privado']==1) ? ' selected' : ''?>>Si</option>
                          </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label class="control-label"><?=ucwords($general['nombreObrasSociales'])?> que atiende</label>
                            <select <?=(@!$general['cupos']) ? 'multiple="true"' : ''?> class="form-control w-100" name="servicios[]" id="servicios" style="width:100%">
                            <?
                            db_query(1,"select idTratamiento, nombre, duracion from tratamientos where estado='A'");
                            for($i1=0;$i1<$tot1;$i1++){
                                $nres1=$res1->data_seek($i1);
                                $row1=$res1->fetch_assoc();
                                ?>
                                <option value="<?=$row1['idTratamiento']?>" <?= isset($tratamientos) && $tratamientos[$row1["idTratamiento"]] ? "selected":""?>><?=$row1['nombre']?> (<?=$row1['duracion']?> min)</option>
                            <? } ?>
                          </select>
                        </div>
                    </div>
                </div>
				
                <div class="columnaP<?=($row['tipo']=='P') ? '' : ' d-none' ?>  border-top border-dark pt-3 my-3">
                    <div class="row">
                        <div class="col">
                            <h4 class="m-0">Fechas puntuales</h4>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col border-top pt-3 mt-3">
                            <h6>Agregar fechas por rango</h6>
                        </div>
                    </div>
                    <div class="puntuales_contenedorRango border-bottom mb-3 pb-3">
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <input type="text" class="form-control fechasSesiones" id="rangoDesde" value="<?=date("d/m/Y")?>" />
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <input type="text" class="form-control fechasSesiones" id="rangoHasta" value="<?=date("d/m/Y", strtotime('+1 day'))?>" />
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <input type="time" class="form-control" id="rangoDesdeHora" value="" />
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <input type="time" class="form-control" id="rangoHastaHora" value="" />
                                </div>
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-primary btn-block" id="agregarRango"><i class="fa fa-check-circle"></i> Agregar</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <small>Las fechas se agregarán al final del listado actual.</small>
                            </div>
                        </div>
                    </div>

                    <div class="contenedorFijas">
                        <?
                        db_query(1,"select * from horariosprofesionales where idProfesional='".$_GET['id']."' order by fechaEspecifica ASC");
                        if($tot1>0){
                            for($i1=0;$i1<$tot1;$i1++){
                                $nres1=$res1->data_seek($i1);
                                $row1=$res1->fetch_assoc();
                                ?>
                                <div class="row <?=$row1['fechaEspecifica'] < date('Y-m-d') ? 'd-none' : ''?>">
                                    <div class="col-5">
                                        <div class="form-group">
                                          <input type="text" class="form-control fechasSesiones" name="fechaSesion[]" id="fechaSesion" value="<?=date("d/m/Y",strtotime($row1['fechaEspecifica']))?>" />
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                          <input type="time" class="form-control" name="horaDesde[]" id="horaDesde" value="<?=$row1['desdeManana']?>" />
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                          <input type="time" class="form-control" name="horaHasta[]" id="horaHasta" value="<?=$row1['hastaManana']?>" />
                                        </div>
                                    </div>
                                    <div class="col-1">
                                        <button class="btn btn-danger text-center btn-block eliminarFila" type="button"><i class="fa fa-trash p-0 fa-fw"></i></button>
                                    </div>
                                </div>
                                <?
                            }
                        }else{
                            ?>
                            <div class="row <?=$row1['fechaEspecifica'] < date('Y-m-d') ? 'd-none' : ''?>">
                                <div class="col-5">
                                    <div class="form-group">
                                      <input type="text" class="form-control fechasSesiones" name="fechaSesion[]" id="fechaSesion" value="<?=date("d/m/Y")?>" />
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                      <input type="time" class="form-control" name="horaDesde[]" id="horaDesde" value="" />
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                      <input type="time" class="form-control" name="horaHasta[]" id="horaHasta" value="" />
                                    </div>
                                </div>
                                <div class="col-1">
                                    <button class="btn btn-danger text-center btn-block eliminarFila" type="button"><i class="fa fa-trash p-0 fa-fw"></i></button>
                                </div>
                            </div>
                            <?
                        }
                        ?>
                    </div>
                    <div class="row">
                        <div class="col">
                            <button type="button" id="agregarFila" class="btn btn-info"><i class="fa fa-plus"></i>  Agregar fecha</button>
                        </div>
                    </div>
                </div>
				
                <?
                if($_GET['id']){
                    ?>
                    <input type="hidden" name="id" value="<?=$_GET['id']?>" />
                    <?
                }
                ?>
                <input type="hidden" name="action" value="save" />
              </form>
            </div>
            <div class="tile-footer">
              <button class="btn btn-primary" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-check-circle"></i>Guardar</button>&nbsp;&nbsp;&nbsp;<a class="btn btn-secondary" href="/profesionales/listado"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar</a>
            </div>
          </div>
                </div>
            </div>
        </main>
		
        <?
        require_once(incPath.'/scripts.php');
        ?>
        <script src="<?=$cdn?>/js/plugins/bootstrap-colorpicker.min.js"></script>
        <script>
		
            $("#tipo").change(function(){
                if($(this).val()=='P'){
                    $(".columnaP").removeClass('d-none');
                }else{
                    $(".columnaP").addClass('d-none');
                }
            })
			
            $("#agregarFila").click(function(){
                $(".contenedorFijas").append('<div class="row">							<div class="col-5">								<div class="form-group">								  <input type="text" class="form-control fechasSesiones" name="fechaSesion[]" id="fechaSesion" value="<?=date("d/m/Y")?>" />								</div>							</div>							<div class="col-3">								<div class="form-group">								  <input type="time" class="form-control" name="horaDesde[]" id="horaDesde" value="" />								</div>							</div>							<div class="col-3">								<div class="form-group">								  <input type="time" class="form-control" name="horaHasta[]" id="horaHasta" value="" />								</div>							</div>							<div class="col-1">								<button class="btn btn-danger text-center btn-block eliminarFila" type="button"><i class="fa fa-trash p-0 fa-fw"></i></button>							</div>						</div>');
                initDate();
            })


            $("#agregarRango").click(function(){
                if($("#rangoDesde").val() == '' || $("#rangoHasta").val() == ''){
                    $.notify({
                        title: '<strong>Error!</strong>',
                        message: 'Debe completar los campos de fecha'
                    },{
                        type: 'danger',
                        placement: {
                            from: "top",
                            align: "center"
                        }
                    });
                    return false;
                }else{
                    let fechaDesde=$("#rangoDesde").val().split("/");
                    let fechaHasta=$("#rangoHasta").val().split("/");
                    for (var d = new Date(+fechaDesde[2], fechaDesde[1] - 1, +fechaDesde[0]); d <= new Date(+fechaHasta[2], fechaHasta[1] - 1, +fechaHasta[0]); d.setDate(d.getDate() + 1)) {
                        $(".contenedorFijas").append(
                            '<div class="row">            <div class="col-5">                <div class="form-group">                    <input type="text" class="form-control fechasSesiones"                        name="fechaSesion[]" id="fechaSesion" value="'+moment(d).format('DD/MM/YYYY')+'" />                </div>            </div>            <div class="col-3">                <div class="form-group">                    <input type="time" class="form-control" name="horaDesde[]"                        id="horaDesde" value="'+$("#rangoDesdeHora").val()+'" />                </div>            </div>            <div class="col-3">                <div class="form-group">                    <input type="time" class="form-control" name="horaHasta[]"                        id="horaHasta" value="'+$("#rangoHastaHora").val()+'" />                </div>            </div>            <div class="col-1">                <button class="btn btn-danger text-center btn-block eliminarFila"                    type="button"><i class="fa fa-trash p-0 fa-fw"></i></button>            </div>        </div>'
                        );
                    }

                    initDate();
                }
            })




            $('#color').colorpicker();
            $("#servicios").select2();
            $("#codArea").select2();
			
            function initDate(){
                $('.fechasSesiones').daterangepicker({
                    "timePicker": false,
                    "timePicker24Hour": false,
                    "singleDatePicker": true,
                    "autoApply":true,
                    "locale": {
                        // "format": "DD/MM/YYYY HH:mm",
                        "format": "DD/MM/YYYY",
                        "separator": " - ",
                        "applyLabel": "Aplicar",
                        "cancelLabel": "Cancelar",
                        "fromLabel": "Desde",
                        "toLabel": "Hasta",
                        "customRangeLabel": "Personalizar",
                        "weekLabel": "S",
                        "daysOfWeek": [
                            "Do",
                            "Lu",
                            "Ma",
                            "Mi",
                            "Ju",
                            "Vi",
                            "Sa"
                        ],
                        "monthNames": [
                            "Enero",
                            "Febrero",
                            "Marzo",
                            "Abril",
                            "Mayo",
                            "Junio",
                            "Julio",
                            "Agosto",
                            "Septiembre",
                            "Octubre",
                            "Noviembre",
                            "Diciembre"
                        ],
                        "firstDay": 1
                    }
                }, function(start, end, label) {
                  // console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
                });
				
                $(".eliminarFila").click(function(){
                    $(this).parent().parent().remove();
                })
            }
			
            initDate();
		
            $("#enviarForm").click(function(){
                algunoMal=0;
                $(".required").each(function(key){
                    if($(this).val().length<4){
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
                    $.post('/profesionales/save.php',$("#formulario").serialize(),function(resultado){

                        console.log(resultado);

                        if(resultado.status=='OK'){
                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'El <?=($general['nombreProfesional'])?> ha sido guardado con éxito'
                            },{
                                // settings
                                type: "success",
                                allow_dismiss: true,
                                newest_on_top: false,
                                showProgressbar: false,
                                onClose: window.location.href='/profesionales/listado',
                                delay:6000
                            });
                        }else{
                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'Ha ocurrido un error al intentar guardar el <?=($general['nombreProfesional'])?>.'
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