<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();

if($_GET['id']<>''){
    db_query(0,"select * from feriados where idFeriado='".$_GET['id']."'");
    $iconito='pen-to-square';
    $titulo='Editar';
    $fecha=date("d/m/Y",strtotime($row['fecha']));
}else{
    $iconito='plus';
    $titulo='Agregar';
    $fecha=date("d/m/Y");
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion=ucwords($general['nombreTurnos']);
        $subseccion='Feriados';
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
                    <h1><i class="fa fa-bed"></i> Feriados</h1>
                    <p>Utilice este formulario para cargar feriados.</p>
                </div>
                <?/*<ul class="app-breadcrumb breadcrumb side">
                    <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                    <li class="breadcrumb-item active"><a href="#">Profesionales</a></li>
                </ul>*/?>
                <a class="btn btn-outline-warning icon-btn" href="/turnos/feriados"><i class="fa fa-arrow-left"></i>Volver atrás</a>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="tile">
                        <h3 class="tile-title"><i class="fa fa-<?=$iconito?>"></i> <?=$titulo?> feriado</h3>
			
                        <div class="tile-body">
                            <form id="formulario">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Descripción</label>
                                            <input type="text" id="nombre" name="nombre" class="form-control" value="<?=$row['nombre']?>" />
                                            <small class="text-muted">Este texto de mostrará en la alerta en caso de que se intente agregar un <?=($general['nombreTurno'])?> en esta fecha.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Fecha</label>
                                            <input class="form-control required" type="text" placeholder="DD/MM/AAAA" id="fecha" name="fecha" value="<?=($_GET['id']) ? date("d/m/Y",strtotime($row['fecha'])) : ''?>">
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
                                <input type="hidden" name="action" value="saveFeriado" />
                            </form>
                        </div>
                        <div class="tile-footer">
                            <button class="btn btn-primary" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-check-circle"></i>Agendar</button>&nbsp;&nbsp;&nbsp;<a class="btn btn-secondary" href="/turnos/feriados"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar</a>
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
                "singleDatePicker": true,
                "autoApply": true,
                "locale": {
                    "format": "DD/MM/YYYY",
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
                "startDate": "<?=$fecha?>",
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
                    preloaderButton('show', originalButton, 'enviarForm');

                    $.post('/turnos/save.php',$("#formulario").serialize(),function(response){

                        preloaderButton('hide', originalButton, 'enviarForm');

                        if(response.status=='OK'){
                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'El feriado ha sido guardado con éxito'
                            },{
                                // settings
                                type: "success",
                                allow_dismiss: true,
                                newest_on_top: false,
                                showProgressbar: false,
                                onClose: window.location.href='/turnos/feriados',
                                delay:6000
                            });
                        }else{
                            console.log(response);
                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'Ha ocurrido un error al intentar guardar el feriado.'
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