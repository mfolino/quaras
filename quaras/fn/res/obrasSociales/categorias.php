<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

AuthController::checkLogin();
AuthController::checkSuperAdmin();

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion='Categorías';
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
                    <h1><i class="fas fa-list-alt"></i> <?=ucfirst($general['nombreCategorias'])?></h1>
                    <p>Utilice este listado para ver de un rápido vistazo las <?=$general['nombreCategorias']?> y administrarlas.</p>
                </div>
                <a class="btn btn-primary icon-btn" href="/obrasSociales/agregarCategoria"><i class="fas fa-plus"></i> Agregar <?=$general['nombreCategoria']?></a>
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
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?
                                        db_query(0,"select * from categorias where estado<>'B' order by nombre ASC");
                                        for($i=0;$i<$tot;$i++){
                                            $nres=$res->data_seek($i);
                                            $row=$res->fetch_assoc();
                                            ?>
                                            <tr id="fila<?=$row['idCategoria']?>">
                                                <td>
                                                    <a onclick="editar(<?=$row['idCategoria']?>)" href="#" data-toggle="tooltip" data-placement="bottom" title="Editar"><i class="fas fa-pen-to-square"></i></a>
                                                    <a onclick="eliminar(<?=$row['idCategoria']?>)" href="#" data-toggle="tooltip" data-placement="bottom" title="Eliminar"><i class="fa-solid fa-trash-can"></i></a>
                                                </td>
                                                <td><?=($row['nombre']<>'') ? $row['nombre'] : ' - '?></td>
                                                <td><?=($row['estado']=='A') ? '<i class="fas fa-circle-check text-success"></i>' : '<i class="fas fa-circle-xmark text-danger"></i>'?></td>
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
                window.location.href="/obrasSociales/agregarCategoria?id="+id;
            }
            function eliminar(id){
                Swal.fire({
                    title: "Está seguro/a?",
                    text: "Si elimina la categoría todas las asociaciones, etc. se eliminarán con ella.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Si, estoy seguro/a!",
                    cancelButtonText: "No, mejor no",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.value) {
                        $.post('/obrasSociales/save',{action: 'deleteCategoria', id:id}, function(resultado){
							
                            console.log(resultado);
							
                            if(resultado=='OK'){
                                Swal.fire("Eliminada!", "La categoría seleccionado ha sido eliminada.", "success");
                                $("#fila"+id).remove();
                            }else{
                                Swal.fire("Error!", "Ha ocurrido un error al intentar eliminar la categoría. Intente nuevamente.", "error");
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