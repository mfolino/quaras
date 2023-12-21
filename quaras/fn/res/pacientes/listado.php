<?

//require_once(fn.'/res/'.basename(__DIR__).'/'.basename(__FILE__));

require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion=ucwords($general['nombrePacientes']);
        $subseccion='Fichas';
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
                    <h1><i class="fa fa-users"></i> <?=ucwords($general['nombrePacientes'])?></h1>
                    <p>Utilice este listado para ver de un rápido vistazo a los <?=($general['nombrePacientes'])?> y administrar los mismos.</p>
                </div>
                
                <div>
                    <a class="btn btn-primary icon-btn" href="/pacientes/editar"><i class="fa fa-plus"></i>Agregar <?=($general['nombrePaciente'])?></a>

                    <?php if($general['plan'] > 2){ ?>
                        <a href="/pacientes/planillaDePacientes.php" target="_blank" class="btn btn-success" ><i class="fas fa-file-excel"></i> Exportar planilla</a>
                    <? } ?>
                    
                </div>
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
                                            <th>Nombre</th>
                                            <th>Apellido</th>
                                            <th>Teléfono</th>
                                            <th>E-mail</th>
                                            <?=($general['tomaTurno']=='dni') ? '<th>'.$general['nombreDNI'].'</th>' : ''?>
                                            <?//<th>Observaciones</th>?>
                                            <?//<th>Tipo</th>?>
                                            <?//<th>Estado</th>?>
                                        </tr>
                                    </thead>
                                    <tbody>
										
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td></td>
                                            <td>Nombre</td>
                                            <td>Apellido</td>
                                            <td>Teléfono</td>
                                            <td>E-mail</td>
                                            <?=($general['tomaTurno']=='dni') ? '<td>'.$general['nombreDNI'].'</td>' : ''?>
                                            <?//<td></td>?>
                                            <?//<td>Tipo</td>?>
                                            <?//<td>Estado</td>?>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
		
        <!-- Modal -->
        <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle"><?=ucwords($general['nombreTurnos'])?> Jorge Cancela</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?/*<div class="row">
                            <div class="col">
                                <select class="form-control" id="queOrdenes">
                                    <option value="0">Activas</option>
                                    <option value="1">Todas</option>
                                </select>
                            </div>
                        </div>*/?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha y hora</th>
                                    <th>Tipo de evento</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar ventana</button>
                    </div>
                </div>
            </div>
        </div>
		
        <iframe id="contenedorImpresion" class="d-none"></iframe>
		
        <?
        require_once(incPath.'/scripts.php');
        ?>
		
        <script type="text/javascript">
            $("#queOrdenes").change(function(){
                if($(this).val()==0){
                    $(".ordenVieja").addClass('d-none');
                }else{
                    $(".ordenVieja").removeClass('d-none');
                }
            })
		
            $('#tablaPacientes tfoot td').each( function () {
				
                var title = $(this).text();
				
                if((title!='Estado')&&(title!='Forma de pago')){
                    if(title!=''){
                        $(this).html( '<input type="text" class="form-control" placeholder="Buscar '+title+'" />' );
                    }
                }else{
                    if(title=='Estado'){
                        $(this).html( '<select class="form-control"><option value="">Todos</option><option value="2">Incompletos</option><option value="1">Completos</option></select>' );
                    }
                }
            } );
		
		
            var table=$('#tablaPacientes').DataTable(
                {
                    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                    serverSide: true,
                    "ajax": {
                         url:"/pacientes/save?action=getPacientes",
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
                        { "data": "nombre", "orderable":true },
                        { "data": "apellido", "orderable":true },
                        { "data": "telefono", "orderable":true },
                        { "data": "mail", "orderable":true },
                        <?=($general['tomaTurno']=='dni') ? '{ "data": "dni", "orderable":true }' : ''?>
                    ],
                    order: [[1, 'asc'], [2, 'asc']],
                }
            );
            table.on( 'search.dt', function (e,data) {
                // console.log(table.search());
                localStorage.setItem('ultimaBusquedaPacientes',table.search());
            });
			
            if(localStorage.getItem('ultimaBusquedaPacientes')){
                table.search(localStorage.getItem('ultimaBusquedaPacientes')).draw();
            }
			
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
		
        <script>
            function editar(id){
                window.location.href="/pacientes/editar?id="+id;
            }
            function ordenes(id){
                window.location.href="/pacientes/ordenes?id="+id;
            }
            function turnos(id,paciente){
                $(".modal .modal-title").html("<?=ucfirst($general['nombreTurnos'])?> "+paciente);
                $.post('/pacientes/save.php',{action:'getTurnos',idPaciente:id},function(resultado){
                    $(".modal .modal-body .table tbody").html(resultado);
                    $(".modal").modal('show');
                })
            }
            function eliminar(id){
                Swal.fire({
                        title: "Está seguro/a?",
                        text: "Si elimina el <?=($general['nombrePaciente'])?> todas las estadísticas, asociaciones, etc. se eliminarán con él.",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Si, estoy seguro/a!",
                        cancelButtonText: 'Cancelar',
                        showDenyButton: false,
                }).then((result) => {

                    if (result.value) {
                        $.post('/pacientes/save',{action: 'delete', id:id}, function(resultado){
                            if(resultado=='OK'){
                                Swal.fire("Eliminado!", "El <?=($general['nombrePaciente'])?> seleccionado ha sido eliminado.", "success");
                                $("#fila"+id).remove();
                            }else{
                                Swal.fire("Error!", "Ha ocurrido un error al intentar eliminar el <?=($general['nombrePaciente'])?>. Intente nuevamente.", "error");
                            }
                        })
                    } /* else if (result.isDenied) {
                        Swal.fire('Changes are not saved', '', 'info')
                    } */
                })
            }
			
            function imprimirFicha(id){
                $("#contenedorImpresion").attr('src','imprimirFicha.php?id='+id);
                $("#contenedorImpresion").load(function() {
                    $("#contenedorImpresion").get(0).contentWindow.print();
                })
            }
        </script>
        <?
        // include(incPath.'/analytics.php');
        ?>
    </body>
</html>