<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();
AuthController::checkSuperAdmin();
AuthController::checkAdmin();

if(!$general["feriadoPersonalizado"]){
    header("Location: /admin");
    die();
}

// Chequeo si existe la tabla, si no la creo
if(!Migration::existTableInDB("feriadosPersonalizados")){
    db_query(0, "CREATE TABLE feriadosPersonalizados (idFeriadoPersonalizado INT(5) UNSIGNED NOT NULL AUTO_INCREMENT ,  descripcion VARCHAR(255) NOT NULL ,  fechaDesde DATE NOT NULL ,  fechaHasta DATE NOT NULL ,  idProfesional INT(5) UNSIGNED NOT NULL COMMENT '0=todos',  horarioInicio TIME NOT NULL ,  horarioFin TIME NOT NULL , eliminado INT(1) DEFAULT 0 ,  PRIMARY KEY  (idFeriadoPersonalizado));");
}

$profesionales = array();
foreach (db_getAll("SELECT idProfesional, nombre FROM profesionales WHERE estado = 'A'") as $profesional) {
    $profesionales[$profesional->idProfesional] = ucfirst($profesional->nombre);
}

?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion="Feriados personalizados";
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
                    <h1><i class="fas fa-bed"></i> Feriados Personalizados</h1>
                    <p>Utilice esta vista para ver sus feriados personalizados y administrarlos.</p>
                </div>
                <a class="btn btn-primary icon-btn" href="/feriadosPersonalizados/editar"><i class="fas fa-plus"></i>Agregar feriado</a>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="tile">
                        <div class="tile-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered" id="tablaPacientes">
                                    <thead>
                                        <tr>
                                            <th>Acciones</th>
                                            <th>Descripción</th>
                                            <th><?=$general["nombreProfesional"]?></th>
                                            <th>Desde</th>
                                            <th>Hasta</th>
                                            <th>Horario</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <? foreach (db_getAll("SELECT * FROM feriadosPersonalizados WHERE eliminado != 1") as $feriado) { ?>
                                            <tr id="fila<?=$feriado->idFeriadoPersonalizado?>">
                                                <!-- Acciones -->
                                                <td>
                                                    <a onclick="editar(<?=$feriado->idFeriadoPersonalizado?>)" href="#" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Editar"><i class="fas fa-pen-to-square"></i></a> 
                                                    <a onclick="eliminar(<?=$feriado->idFeriadoPersonalizado?>)" href="#" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Eliminar"><i class="fas fa-trash"></i></a>
                                                </td>

                                                <td><?=$feriado->descripcion?></td>
                                                
                                                <td><?=$feriado->idProfesional == 0 ? 'Todos' : $profesionales[$feriado->idProfesional]?></td>

                                                <td><?=date("d/m/Y", strtotime($feriado->fechaDesde))?></td>
                                                <td><?=date("d/m/Y", strtotime($feriado->fechaHasta))?></td>

                                                <td><?=date("H:i", strtotime($feriado->horarioInicio))?>hs - <?=date("H:i", strtotime($feriado->horarioFin))?>hs</td>
                                            </tr>
                                        <? } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
		
        <?
        require_once(incPath.'/scripts.php');
        ?>

        <script type="text/javascript">
		    $('#tablaPacientes').DataTable();
			
            function editar(id){
                window.location.href='/feriadosPersonalizados/editar?id='+id;
            }
		
            function eliminar(id){
                Swal.fire({
                    title: "Está seguro/a?",
                    text: "Esta acción no puede deshacerse.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Si, estoy seguro/a!",
                    cancelButtonText: "No, mejor no"
                }).then((result) => {
                    if (result.value) {
                        $.post('/feriadosPersonalizados/save.php',{action: 'delete', id}, function({status, title, message, type}){
                            Swal.fire(title, message, type).then(res => {
                                if(status == "OK"){
                                    $("#fila"+id).remove();
                                }
                            })
                        })
                    }
                })
            }
        </script>
    </body>
</html>