<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

AuthController::checkLogin();
AuthController::checkSuperAdmin();

db_query(0,"SELECT * from tratamientos where idTratamiento=".$_GET['id']);
$tratamiento=$row;
db_query(0,"SELECT * from tratamientos_valores where fechaAlta<='".date("Y-m-d")."' and idTratamiento='".$_GET['id']."' order by idComision ASC");
for($i=0;$i<$tot;$i++){
    $nres=$res->data_seek($i);
    $row=$res->fetch_assoc();
    $valores['cantidad']=$row['cantidad'];
    $valores['fecha']=$row['fechaAlta'];
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion=ucwords($general['nombreObrasSociales']);
        $subseccion='Listado';
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
                    <h1><i class="fas fa-list"></i> <?=ucwords($general['nombreObrasSociales'])?> <i class="fa fa-angle-right"></i> Valores</h1>
                    <p>Utilice este listado para ver de un rápido vistazo los <?=ucwords($general['nombreObrasSociales'])?> y administrarlos.</p>
                </div>
                <?/*<ul class="app-breadcrumb breadcrumb side">
                    <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                    <li class="breadcrumb-item active"><a href="#">Profesionales</a></li>
                </ul>*/?>
                <a class="btn btn-outline-warning icon-btn" href="/obrasSociales/tratamientos"><i class="fa fa-arrow-left"></i>Volver atrás</a>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="tile">
            <h3 class="tile-title"><i class="fa fa-pen-to-square"></i> Editar valores "<?=$tratamiento['nombre']?>"</h3>
            <div class="tile-body">
              <form id="formulario">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                          <label class="control-label">Valor</label>
                          <input class="form-control required" type="number" name="cantidad" value="<?=$valores['cantidad']?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="control-label">Actualización</label>
                            <input class="form-control required" type="text" placeholder="dd/mm/aaaa" name="fechaAlta" id="fechaAlta" value="<?=($valores['fecha']) ? date("d/m/Y",strtotime($valores['fecha'])) : date('d/m/Y')?>">
                        </div>
                    </div>
                </div>
                <input type="hidden" name="idTratamiento" value="<?=$_GET['id']?>" />
                <input type="hidden" name="action" value="updateComision" />
              </form>
            </div>
            <div class="tile-footer">
              <button class="btn btn-primary" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-check-circle"></i>Guardar</button>&nbsp;&nbsp;&nbsp;<a class="btn btn-secondary" href="/obrasSociales/tratamientos"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar</a>
            </div>
          </div>
                </div>
            </div>
        </main>
		
        <?
        require_once(incPath.'/scripts.php');
        ?>
        <!-- Page specific javascripts-->
        <script type="text/javascript" src="/js/plugins/bootstrap-notify.min.js"></script>
        <script type="text/javascript" src="/js/plugins/bootstrap-datepicker.min.js"></script>
        <script>
		
            $('#fechaAlta').datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
                todayHighlight: true,
                startDate: "01/05/2018"
            })
		
            $("#enviarForm").click(function(){
                algunoMal=0;
                $(".required").each(function(key){
                    if($(this).val().length<1){
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
                    $.post('/obrasSociales/save.php',$("#formulario").serialize(),function(response){
                        if(response.status=='OK'){
                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'La comisión ha sido guardada con éxito'
                            },{
                                // settings
                                type: "success",
                                allow_dismiss: true,
                                newest_on_top: false,
                                showProgressbar: false,
                                onClose: window.location.href='/obrasSociales/tratamientos?id=<?=$_GET['id']?>',
                                delay:6000
                            });
                        }else{

                            console.log(response);

                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'Ha ocurrido un error al intentar guardar la comisión.'
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