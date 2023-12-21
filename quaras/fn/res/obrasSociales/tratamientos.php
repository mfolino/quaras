<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

AuthController::checkLogin();
AuthController::checkSuperAdmin();

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
                    <h1><i class="fas fa-list"></i> <?=ucwords($general['nombreObrasSociales'])?></h1>
                    <p>Utilice este listado para ver de un rápido vistazo los <?=($general['nombreObrasSociales'])?> y administrarlos.</p>
                </div>
                <a class="btn btn-primary icon-btn" href="/obrasSociales/agregarTratamiento"><i class="fas fa-plus"></i> Agregar <?=($general['nombreObraSocial'])?></a>
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
                                            <th>Duración</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?
                                        db_query(0,"select * from tratamientos where estado<>'B' order by nombre ASC");
                                        for($i=0;$i<$tot;$i++){
                                            $nres=$res->data_seek($i);
                                            $row=$res->fetch_assoc();
                                            ?>
                                            <tr id="fila<?=$row['idTratamiento']?>">
                                                <td>
                                                    <a onclick="editar(<?=$row['idTratamiento']?>)" href="#" data-toggle="tooltip" data-placement="bottom" title="Editar"><i class="fas fa-pen-to-square"></i></a>
                                                    <a onclick="eliminar(<?=$row['idTratamiento']?>)" href="#" data-toggle="tooltip" data-placement="bottom" title="Eliminar"><i class="fas fa-trash"></i></a>
                                                    <a onclick="comisiones(<?=$row['idTratamiento']?>)" href="#" data-toggle="tooltip" data-placement="bottom" title="Valores"><i class="fas fa-money-bill"></i></a>
                                                </td>
                                                <td><?=($row['nombre']<>'') ? $row['nombre'] : ' - '?></td>
                                                <td><?=($row['duracion']<>'') ? $row['duracion'] : ' - '?> minutos</td>
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
                window.location.href="/obrasSociales/agregarTratamiento?id="+id;
            }
            function comisiones(id){
                window.location.href="/obrasSociales/editarComision?id="+id;
            }
            function eliminar(id){
                Swal.fire({
                    title: "Está seguro/a?",
                    text: "Si elimina el <?=($general['nombreObraSocial'])?> todas las estadísticas, asociaciones, etc. se eliminarán con ella.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Si, estoy seguro/a!",
                    cancelButtonText: "No, mejor no",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }).then((result) => {

                    if (result.value) {
                        $.post('/obrasSociales/save',{action: 'deleteTratamiento', id:id}, function(response){
                            if(response.status=='OK'){
                                Swal.fire("Eliminado!", "El <?=($general['nombreObraSocial'])?> seleccionado ha sido eliminado.", "success");
                                $("#fila"+id).remove();
                            }else{
                                console.log(response);
                                Swal.fire("Error!", "Ha ocurrido un error al intentar eliminar el <?=($general['nombreObraSocial'])?>. Intente nuevamente.", "error");
                            }
                        })
                    }/*  else if (result.isDenied) {
                        Swal.fire('Changes are not saved', '', 'info')
                    } */
                })


            }
        </script>
        <?
        // include(incPath.'/analytics.php');
        ?>
    </body>
</html>