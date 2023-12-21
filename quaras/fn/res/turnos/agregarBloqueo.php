<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();

/* $profesionales = getProfesionales()["profesionales"]; */

$_GET['id'] = $_GET['id'] ?? '';

if($_GET['id']<>''){
    db_query(0,"select * from bloqueos where idBloqueo='".$_GET['id']."'");
    $iconito='edit';
    $titulo='Editar';
    $fechaDesde=date("d/m/Y H:i",strtotime($row['fechaDesde']));
    $fechaHasta=date("d/m/Y H:i",strtotime($row['fechaHasta']));
}else{
    $iconito='plus';
    $titulo='Agregar';
    $fechaDesde=date("d/m/Y H:00");
    $fechaHasta=date("d/m/Y H:00");
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion=ucwords($general['nombreTurnos']);
        $subseccion='Bloqueos';
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
                    <h1><i class="fas fa-lock"></i> Bloqueos</h1>
                    <p>Utilice este formulario para cargar bloqueos.</p>
                </div>
                <?/*<ul class="app-breadcrumb breadcrumb side">
                    <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                    <li class="breadcrumb-item active"><a href="#">Profesionales</a></li>
                </ul>*/?>
                <a class="btn btn-outline-warning icon-btn" href="/turnos/bloqueos"><i class="fa fa-arrow-left"></i>Volver atrás</a>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="tile">
                        <h3 class="tile-title"><i class="fa fa-<?=$iconito?>"></i> <?=$titulo?> bloqueo</h3>
			
                        <div class="tile-body">
                            <form id="formulario">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" data-toggle="tooltip" title="Este texto de mostrará en la alerta en caso de que se intente agregar un <?=($general['nombreTurno'])?> en este rango.">Descripción <i class="fas fa-info-circle"></i></label>
                                            <input type="text" id="descripcion" name="descripcion" class="form-control" value="<?=$row['descripcion']??''?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label">Rango</label>
                                            <input class="form-control required" type="text" placeholder="DD/MM/AAAA hh:mm" id="fecha" name="fecha" value="<?=($_GET['id']) ? date("d/m/Y H:00",strtotime($row['fechaDesde'])).' - '.date("d/m/Y H:00",strtotime($row['fechaHasta'])) : ''?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" data-toggle="tooltip" title="Indica si aplica a todos los <?=ucwords($general['nombreProfesional'])?> o a alguno específico."><?=ucwords($general['nombreProfesional'])?> <i class="fas fa-info-circle"></i></label>
                                            <select class="form-control required" id="idProfesional" name="idProfesional">
                                                <option value="0"<?=isset($row['idProfesional']) && $row['idProfesional']==0 ? ' selected' : ''?>>Todos</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <?
                                if($_GET['id']){
                                    ?>
                                    <input type="hidden" id="id" name="id" value="<?=$_GET['id']?>" />
                                    <?
                                }
                                ?>
                                <input type="hidden" name="action" value="saveBloqueo" />
                            </form>
                        </div>
                        <div class="tile-footer">
                            <button class="btn btn-primary" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-check-circle"></i>Agendar</button>&nbsp;&nbsp;&nbsp;<a class="btn btn-secondary" href="/turnos/bloqueos"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
			
        <?
        require_once(incPath.'/scripts.php');
        ?>
		
        <script>
		
            $('#fecha').daterangepicker({
                "timePicker": true,
                "timePicker24Hour": true,
                "autoApply": true,
                "locale": {
                    "format": "DD/MM/YYYY HH:mm",
                    "separator": " - ",
                    "applyLabel": "Aplicar",
                    "cancelLabel": "Cancelar",
                    "fromLabel": "Desde",
                    "toLabel": "Hasta",
                    "customRangeLabel": "Personalizado",
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
                },
                "startDate": "<?=$fechaDesde?>",
                "endDate": "<?=$fechaHasta?>"
            }, function(start, end, label) {
              // console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
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
                    let button = "enviarForm";
                    preloaderButton('show', originalButton, button);

                    $.post('/turnos/save.php',$("#formulario").serialize(),function(response){

                        console.log(response);

                        preloaderButton('hide', originalButton, button);

                        if(response.status=='OK'){
                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'El bloqueo ha sido guardado con éxito'
                            },{
                                // settings
                                type: "success",
                                allow_dismiss: true,
                                newest_on_top: false,
                                showProgressbar: false,
                                onClose: window.location.href='/turnos/bloqueos',
                                delay:6000
                            });
                        }else{
                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'Ha ocurrido un error al intentar guardar el bloqueo.'
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

            $.post(
                '/profesionales/save',
                {
                    action:'getProfesionales'
                },
                function(response){

                    console.log(response);

                    if(response.status=='OK'){
                        $.each(response.profesionales,function(id,profesional){
                            let html='<option value="'+id+'"';
							
                            if(id=='<?=$row['idProfesional']?>'){
                                html+=' selected';
                            }

                            html+='>'+profesional+'</option>';
                            $("#idProfesional").append(html);
                        })
                    }else{

                        $.notify({
                            // options
                            icon: 'fas fa-xmark',
                            title: '',
                            message: 'Ha ocurrido un error al cargar los profesionales disponibles. Intente nuevamente más tarde.'
                        },{
                            // settings
                            type: "warning",
                            allow_dismiss: true,
                            newest_on_top: false,
                            showProgressbar: false,
                            onClose: window.location.href='/turnos/bloqueos',
                            delay:6000
                        });
                    }
                }
            )
        </script>
		
        <?
        // include(incPath.'/analytics.php');
        ?>
    </body>
</html>