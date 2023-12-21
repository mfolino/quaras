<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();
AuthController::checkSuperAdmin();
AuthController::checkAdmin();

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
                    <h1><i class="fas fa-bed"></i> Feriados</h1>
                    <p>Utilice este calendario para ver de un rápido vistazo los feriados y administrarlos.</p>
                </div>
                <a class="btn btn-primary icon-btn" href="/turnos/agregarFeriado"><i class="fas fa-plus"></i>Agregar feriado</a>
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
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td></td>
                                            <td>Descripción</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
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
        <!-- Page specific javascripts-->
        <!-- <script type="text/javascript" src="/js/plugins/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="/js/plugins/dataTables.bootstrap.min.js"></script> -->

        <script type="text/javascript">
		
            $('#tablaPacientes tfoot td').each( function () {
				
                var title = $(this).text();
				
                if((title!='Estado')&&(title!='Forma de pago')){
                    if(title!=''){
                        $(this).html( '<input type="text" class="form-control" placeholder="Buscar '+title+'" />' );
                    }
                }else{
                    if(title=='Estado'){
                        $(this).html( '<select class="form-control"><option value="">Todos</option><option value="0">Pendiente</option><option value="1">Asistió</option><option value="2">Ausente</option><option value="3">Cancelado</option></select>' );
                    }
                }
            } );
		
		
            var table=$('#tablaPacientes').DataTable(
                {
                    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                    serverSide: true,
                    "ajax": {
                         url:"/turnos/save?action=getFeriados",
                         "data":function(outData){
                             // what is being sent to the server
                             // console.log(outData);
                             return outData;
                         },
                         dataFilter:function(inData){
                             // what is being sent back from the server (if no error)
                             // console.log(inData);
                             return inData;
                         },
                         error:function(err, status){
                             // what error is seen(it could be either server side or client side.
                             console.log(err);
                         },
                     },
                    // ajax: 'reservas.php',
                    "columns": [
                        { "data": "acciones","orderable":false },
                        { "data": "descripcion", "orderable":true },
                        { "data": "fecha", "orderable":true }
                    ]
                }
            );
			
            table.columns().every( function () {
                var that = this;
		 
                $( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that
                            .search( this.value )
                            .draw();
                    }
                } );
                $( 'select', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that
                            .search( this.value )
                            .draw();
                    }
                } );
            } );
		
            table.on( 'draw.dt', function () {
                $('[data-toggle="tooltip"]').tooltip()
            });
        </script>
		
        <!-- <script type="text/javascript" src="/js/plugins/sweetalert.min.js"></script> -->
		
        <script>
            function editar(id){
                window.location.href='/turnos/agregarFeriado?id='+id;
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
                        $.post('/turnos/save',{action: 'deleteFeriado', idFeriado:id}, function(response){
                            // console.log(resultado);
                            if(response.status=='OK'){
                                Swal.fire("Eliminado!", "El feriado seleccionado ha sido eliminado.", "success");
                                $("#fila"+id).remove();
                            }else{
                                console.log(response);
                                Swal.fire("Error!", "Ha ocurrido un error al intentar eliminar el feriado. Intente nuevamente.", "error");
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