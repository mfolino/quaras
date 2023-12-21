<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();
AuthController::checkSuperAdmin();

//Traigo info para no releerla despues
db_query(0,"select * from profesionales where idProfesional=".$_GET['id']);
$profesional=$row;
db_query(0,"select * from comisiones where fechaAlta<='".date("Y-m-d")."' and idProfesional=".$_GET['id']);
for($i=0;$i<$tot;$i++){
    $nres=$res->data_seek($i);
    $row=$res->fetch_assoc();
    $comisiones[$row['idTratamiento']]=$row['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion=ucwords($general['nombreProfesionales']);
        $subseccion='Comisiones';
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
                    <h1><i class="fa fa-scissors"></i> <?=ucwords($general['nombreProfesionales'])?> <i class="fa fa-angle-right"></i> Comisiones</h1>
                    <p>Utilice este listado para ver de un rápido vistazo a los profesionales de su equipo y administrar los mismos.</p>
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
                        <div class="tile-body">
                            <a class="btn btn-outline-primary icon-btn float-right" href="#" id="programarAumento"><i class="fas fa-clock"></i> Programar aumento</a>
                            <h3 class="tile-title"><i class="fa fa-percent"></i> <?=$profesional['nombre']?></h3>
                            <p><i class="fa fa-info-circle"></i> Complete los campos. Los mismos se guardan automáticamente al salir del campo.</p>
                            <div class="jumbotron programarAumentoDiv">
                                <h3>Programar aumento</h3>
                                <p>Seleccione la fecha y la cantidad porcentual a la que desea aumentar. Tenga en cuenta que ésta aplica a todos los servicios por igual.</p>
                                <form>
                                    <div class="row">
                                        <div class="col">
                                            <label for="fechaAumento">
                                                Fecha de aplicación:
                                            </label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <input class="form-control required" id="fechaAumento" type="text" placeholder="Seleccione fecha">
                                                    <div class="input-group-append"><span class="input-group-text"><i class="fa fa-calendar"></i></span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <label for="cantidadAumento">
                                                Cantidad:
                                            </label>
                                            <div class="form-group">
                                                <div class="input-group">											
                                                    <input class="form-control required" id="cantidadAumento" type="number" placeholder="Cantidad">
                                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <p><a class="btn btn-primary" href="#" id="aceptarAumento">Programar</a>&nbsp;<a class="btn btn-secondary" href="#" id="cancelarAumento">Cancelar</a></p>
                            </div>
                            <table class="table table-hover table-bordered" id="tablaComisiones">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Comisión</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?
                                    db_query(0,"select t.nombre as tratamiento, t.idTratamiento from tratamientos t, profesionales_tratamientos pt where t.estado='A' and pt.idTratamiento=t.idTratamiento and pt.idProfesional='".$_GET['id']."' order by t.nombre ASC");
                                    for($i=0;$i<$tot;$i++){
                                        $nres=$res->data_seek($i);
                                        $row=$res->fetch_assoc();
                                        ?>
                                        <tr id="fila<?=$row['idTratamiento']?>">
                                            <td><?=$row['tratamiento']?></td>
                                            <td>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input class="form-control campoComision" data-tratamiento="<?=$row['idTratamiento']?>" type="number" placeholder="Cantidad" value="<?=$comisiones[$row['idTratamiento']]?>">
                                                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
		
        <?
        require_once(incPath.'/scripts.php');
        ?>
        <!-- Page specific javascripts-->
        <script type="text/javascript">var table = $('#tablaComisiones').DataTable();</script>
		
        
        <script>
            function guardarCampos(){
                $(".campoComision").keyup(function(){
                    // console.log($(this).val());
                    $.post("/profesionales/save.php",{action:'guardarComision',cantidad:$(this).val(),tratamiento:$(this).data('tratamiento'),idProfesional:'<?=$_GET['id']?>'},function(resultado){
                        if(resultado!='OK'){
                            // console.log(resultado);
                            console.log("Ha ocurrido un error al guardar.");
                        }
                    })
                })
            }
		
            table.on( 'draw', function () {
                guardarCampos();
            })
			
            guardarCampos();
			
            $('#fechaAumento').datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
                todayHighlight: true
              });
			
            $("#programarAumento").click(function(){
                if($(".programarAumentoDiv").is(":visible")){
                    $(".programarAumentoDiv").slideUp();
                }else{
                    $(".programarAumentoDiv").slideDown();
                }
            })
			
            $("#aceptarAumento").click(function(e){
                var algunoMal=0;
                e.preventDefault();
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
				
                if(algunoMal==0){
                    $.post('/profesionales/save.php',{action:'programarAumento',idProfesional:'<?=$_GET['id']?>',fecha:$("#fechaAumento").val(),cantidad:$("#cantidadAumento").val()},function(resultado){
                        console.log(resultado);
                        if(resultado=="OK"){
                            Swal.fire("Aumento programado!", "Las comisiones del profesional serán modificadas a "+$("#cantidadAumento").val()+"% el día "+$("#fechaAumento").val()+".", "success");
                            $(".programarAumentoDiv").slideUp();
                            $("#fechaAumento").val('');
                            $("#cantidadAumento").val('');
                        }else{
                            Swal.fire("Error!", "Ha ocurrido un error al intentar programar el aumento. Intente nuevamente.", "error");
                        }
                    })
                }
				
            })
			
            $("#cancelarAumento").click(function(e){
                e.preventDefault();
                $("#fechaAumento").val('');
                $("#cantidadAumento").val('');
                $(".programarAumentoDiv").slideUp();
            })
        </script>
        <?
        // include(incPath.'/analytics.php');
        ?>
    </body>
</html>