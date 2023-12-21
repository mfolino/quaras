<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

AuthController::checkLogin();
AuthController::checkSuperAdmin();

/* ----------------------------------------- */
/*      CHEQUEO QUE EXISTAN LAS TABLAS       */
/* ----------------------------------------- */
if(!db_getOne("SELECT TABLE_SCHEMA, TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_NAME = 'creditos_pacientes'")){
    db_query(0, "CREATE TABLE IF NOT EXISTS creditos_pacientes (
        idPaciente INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        idPlan INT(5) UNSIGNED, 
        disponible INT(4) UNSIGNED, 
        fechaAlta DATETIME, 
        proximoVencimiento DATETIME
    )");
}

if(!db_getOne("SELECT TABLE_SCHEMA, TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_NAME = 'creditos_planes'")){
    db_query(0, "CREATE TABLE IF NOT EXISTS creditos_planes (
        idPlan INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        nombre VARCHAR(255), 
        estado CHAR(1), 
        modo VARCHAR(10), 
        diaMes VARCHAR(10), 
        cantidad int(11) UNSIGNED
    )");
}
/* ---------------------- */
/*      END CHEQUEO       */
/* ---------------------- */



?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion='Planes';
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
                    <h1><i class="fas fa-coins"></i> Planes</h1>
                    <p>Utilice este listado para ver de un rápido vistazo los planes y administrarlos.</p>
                </div>
                <a class="btn btn-primary icon-btn" href="/obrasSociales/agregarPlan"><i class="fas fa-plus"></i> Agregar plan</a>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="tile">
                        <div class="tile-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered" id="tablaTratamientos">
                                    <thead>
                                        <tr>
                                            <th>Acciones</th>
                                            <th>Nombre</th>
                                            <th>Recarga</th>
                                            <th>Cantidad</th>
                                            <th>Día de renovación</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?
                                        db_query(0,"select * from creditos_planes where estado<>'B' order by nombre ASC");
                                        for($i=0;$i<$tot;$i++){
                                            $nres=$res->data_seek($i);
                                            $row=$res->fetch_assoc();
                                            ?>
                                            <tr id="fila<?=$row['idPlan']?>">
                                                <td>
                                                    <a onclick="editar(<?=$row['idPlan']?>)" href="#" data-toggle="tooltip" data-placement="bottom" title="Editar"><i class="fas fa-pen-to-square"></i></a>
                                                    <a onclick="eliminar(<?=$row['idPlan']?>)" href="#" data-toggle="tooltip" data-placement="bottom" title="Eliminar"><i class="fas fa-trash"></i></a>
                                                </td>
                                                <td><?=($row['nombre']<>'') ? $row['nombre'] : ' - '?></td>
                                                <td><?=($row['modo']=='A') ? 'Automática' : 'Manual'?></td>
                                                <td><?=($row['cantidad']<>'') ? $row['cantidad'] : ' - '?></td>
                                                <td><?
                                                    if($row['modo']=='A'){
                                                        if($row['diaMes']=='start'){
                                                            ?>
                                                            Principio de mes
                                                            <?
                                                        }else if($row['diaMes']=='end'){
                                                            ?>
                                                            Fin de mes
                                                            <?
                                                        }else{
                                                            ?>
                                                            <?=$row['diaMes']?> de cada mes
                                                            <?
                                                        }
                                                    }else{
                                                        ?>
                                                        -
                                                        <?
                                                    }
                                                    ?>
                                                </td>
                                                <td><?=(($row['estado']=='A') or ($row['estado']==1)) ? '<i class="fas fa-circle-check text-success"></i>' : '<i class="fas fa-circle-xmark text-danger"></i>'?></td>
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
            </div>
        </main>
		
        <?
        require_once(incPath.'/scripts.php');
        ?>
        
        <script type="text/javascript">
        
            $('#tablaTratamientos').DataTable();
            
            function editar(id){
                window.location.href="/obrasSociales/agregarPlan?id="+id;
            }
            function eliminar(id){
                Swal.fire({
                    title: "Está seguro/a?",
                    text: "Si elimina el plan todos los <?=$general['nombrePacientes']?> que lo tengan asociado quedarán sin plan.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Si, estoy seguro/a!",
                    cancelButtonText: "No, mejor no",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }).then((result) => {

                    if (result.value) {
                        $.post('/obrasSociales/save',{action: 'deletePlan', id:id}, function(response){
                            if(response=='OK'){
                                Swal.fire("Eliminado!", "El plan seleccionado ha sido eliminado.", "success");
                                $("#fila"+id).remove();
                            }else{
                                console.log(response);
                                Swal.fire("Error!", "Ha ocurrido un error al intentar eliminar el plan. Intente nuevamente.", "error");
                            }
                        })
                    }
                })


            }
        </script>
        <?
        // include(incPath.'/analytics.php');
        ?>
    </body>
</html>