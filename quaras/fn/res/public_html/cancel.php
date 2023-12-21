<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');


$horaLimite=date("Y-m-d H:i:s", strtotime('+ '.$general['hsAntesCancelacion'].' hours'));

$todoOk=0;

db_query(0,"select t.*, tra.nombre as tratamiento from turnos t, ordenes o, tratamientos tra where t.idTurno='".base64_decode($_GET['t'])."' and t.estado=0 and t.eliminado<>1 and t.idOrden=o.idOrden and o.idTratamiento=tra.idTratamiento limit 1");
// db_query(0,"select * from turnos where idTurno='".base64_decode($_GET['t'])."' and fechaInicio>'".$horaLimite."' and estado=0 and eliminado<>1 limit 1");
if($tot>0){
    //Voy a buscar los turnos del paciente para ver si tiene alguno que no haya pasado la hora limite y se puede cancelar
    db_query(0, "select t.*, tra.nombre as tratamiento from turnos t, ordenes o, tratamientos tra where t.idPaciente='".$row['idPaciente']."' and t.eliminado<>1 and t.estado<>3 and t.estado<>9 and t.idOrden=o.idOrden and o.idTratamiento=tra.idTratamiento order by t.fechaInicio asc");
    if($tot>0){
        $todoOk=1;
    }
}else{
    $todoOk=0;
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion=ucwords($general['nombreCliente']);
        $subseccion='Cancelar';
        require_once('inc/head.php');
        ?>
    </head>
    <body>
        <section class="material-half-bg">
            <div class="cover"></div>
        </section>
        <section class="login-content">
            <div class="logo">
                <img src="img/<?=$general['isologo']?>" width="<?=$general['logoWidth']?>" />
            </div>
            <div class="login-box w-50" style="min-height:auto">
                <form class="nuevoTurno-form" id="formulario">
                    <div class="login-head text-center">
                        <?
                        if($todoOk){
                            ?>
                            <h3 class="mt-0 mb-3"><i class="fas fa-user-circle"></i> Mis turnos</h3>
                            <?
                            for($i=0;$i<$tot;$i++){
                                $nres=$res->data_seek($i);
                                $row=$res->fetch_assoc();
                                ?>
                                <div class="row border-bottom py-2 text-left" id="turno<?=$row['idTurno']?>">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <?=date("d/m/Y H:i",strtotime($row['fechaInicio']))?>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-center">
                                        <?=$row['tratamiento']?>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-center">
                                        <?    
                                        if($row['estado']==1){
                                            ?>
                                            <button type="button" data-toggle="tooltip" title="Asististe a este turno." class="btn btn-success btn-block"><i class="fa fa-user-plus m-0 p-0"></i></button>
                                            <?
                                        }
                                        if($row['estado']==2){
                                            ?>
                                            <button type="button" data-toggle="tooltip" title="No asististe a este turno." class="btn btn-warning btn-block"><i class="fa fa-user-times m-0 p-0"></i></button>
                                            <?
                                        }
                                        if($row['estado']==0){
                                            if($row['fechaInicio']>=$horaLimite){
                                                ?>
                                                <button type="button" onclick="cancelarTurno(<?=$row['idTurno']?>)" class="btn btn-danger btn-block"><i class="fa fa-times-circle m-0 p-0"></i></button>
                                                <?
                                            }else{
                                                ?>
                                                <button type="button" data-toggle="tooltip" title="No es posible cancelar este turno ya que faltan menos de <?=$general['hsAntesCancelacion']?> horas para el inicio del mismo." class="btn btn-dark btn-block"><i class="fas fa-clock m-0 p-0"></i></button>
                                                <?
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?
                            }
                        }else{
                        ?>
                        <h3><i class="text-danger fa fa-exclamation-circle"></i> Lo sentimos!</h3>
                        <?
                        }
                        ?>
                    </div>
					
                <?
                if(!$todoOk){
                    ?>
                        <p>No es posible cancelar el turno solicitado.</p>
                        <?
                    }
                ?>
                </form>

            </div>
            <?
            require_once($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
            ?>
        </section>

        <?
        require_once('inc/scripts.php');
        ?>
		<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <?
        if($todoOk){
        ?>
        <script>
		
            $('.cancelBtn').click(function(e){
				
                var textoOriginal=$(this).html();

                $(this).html('<i class="fas fa-spinner fa-spin"></i> Cancelando...').prop('disabled',true);
				
                e.preventDefault();
                $.post('/turnos/save',$('#formulario').serialize(),function(response){
						
                    console.log(response);
						
                    if(response.status=='OK'){
                        $(".login-head").html('<p>Tu turno ha sido cancelado.</p>');
                        $(".cancelBtn").remove();
                    }else{
                        Swal.fire('Lo sentimos!',response,'error');
                        $('.cancelBtn').html(textoOriginal).prop('disabled',false);
                    }
                })
            })

            $("[data-toggle='tooltip']").tooltip();

            function cancelarTurno(id){
                $("button").prop("disabled", true)
                Swal.fire({
                    title: 'Estás seguro/a?',
                    text: 'Esta acción no puede deshacerse.',
                    type: 'question',
                    showDenyButton: false,
                    showCancelButton: true,
                    confirmButtonText: 'Si',
                    denyButtonText: `Cancelar`,
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    $("button").prop("disabled", false)

                    if (result.value) {
                        $.post('/turnos/save',{action:'cancelarTurnoExterno', idTurno:id},function(response){
                            
                            console.log(response);
                        
                            if(response.status=='OK'){
                                $("#turno"+id).remove();
                                Swal.fire('El <?=$general['nombreTurno']?> ha sido cancelado correctamente!','','success');

                            }else{
                                Swal.fire('Lo sentimos!',response,'error');
                            }
                        })
                    }
                })
            }
        </script>
        <?
        }
        ?>
    </body>
</html>