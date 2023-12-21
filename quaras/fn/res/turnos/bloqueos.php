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
                    <p>Utilice este calendario para ver de un rápido vistazo los bloqueos de calendario y administrarlos.</p>
                </div>
                <a class="btn btn-primary icon-btn" href="/turnos/agregarBloqueo"><i class="fa fa-plus"></i>Agregar bloqueo</a>
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
                                            <th>Desde</th>
                                            <th>Hasta</th>
                                            <th><?=ucwords($general['nombreProfesional'])?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
									
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td></td>
                                            <td>Descripción</td>
                                            <td></td>
                                            <td></td>
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
                         url:"/turnos/save?action=getBloqueos",
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
                        { "data": "fechaDesde", "orderable":true },
                        { "data": "fechaHasta", "orderable":true },
                        { "data": "profesional", "orderable":true }
                    ]
                }
            );
            /*table.on( 'search.dt', function (e,data) {
                // console.log(table.search());
                localStorage.setItem('ultimaBusquedaPacientes',table.search());
            });
			
            if(localStorage.getItem('ultimaBusquedaPacientes')!=''){
                table.search(localStorage.getItem('ultimaBusquedaPacientes')).draw();
            }*/
			
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
        
        function editar(id){
                window.location.href='/turnos/agregarBloqueo?id='+id;
            }
		
            function eliminar(id){
                Swal.fire({
                    title: "Está seguro/a?",
                    text: "Esta acción no puede deshacerse.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Si, estoy seguro/a!",
                    cancelButtonText: 'Cancelar',
                    showDenyButton: false,
                }).then((result) => {
                    if (result.value) {
                        $.post('/turnos/save',{action: 'deleteBloqueo', idBloqueo:id}, function(response){
                            if(response.status=='OK'){
                                Swal.fire("Eliminado!", "El bloqueo seleccionado ha sido eliminado.", "success");
                                $("#fila"+id).remove();
                            }else{
                                console.log(response);
                                Swal.fire("Error!", "Ha ocurrido un error al intentar eliminar el bloqueo. Intente nuevamente.", "error");
                            }
                        })
                    }/* else {
                        swal("Cancelled", "Your imaginary file is safe :)", "error");
                    }*/
                });
            }
        </script>
        <?
        // include(incPath.'/analytics.php');
        ?>
    </body>
</html>