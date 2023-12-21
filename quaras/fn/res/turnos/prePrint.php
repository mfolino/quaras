<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

AuthController::checkLogin();


$fechaImprimir=date("Y-m-d",strtotime('+1 day'));

$profesionales=ProfesionalController::getProfesionales();
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion='Turnos';
        $subseccion='Imprimir';
        require_once(incPath.'/head.php');
        ?>
    </head>
    <body class="app sidebar-mini rtl">
        <div class="tile-body">
          <form id="formulario">
            <div class="row">
                <div class="col">
                    <div class="form-group">
					
                        <label class="control-label"><?=ucwords($general['nombreProfesional'])?></label>
                      <select class="form-control required" name="profesional" id="profesional">
                        <option value="">Todos</option>
                            <?
                            foreach($profesionales['profesionales'] as $idProfesional => $profesional){
                                ?>
                                <option value="<?=$idProfesional?>"><?=$profesional?></option>
                                <?
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label class="control-label">Fecha</label>
                        <input type="text" class="form-control required" name="fecha" id="fecha" readonly placeholder="dd/mm/aaaa" value="<?=date("d/m/Y",strtotime($fechaImprimir))?>" />
                    </div>
                </div>
            </div>
			
          </form>
        </div>
        <div class="tile-footer">
          <button class="btn btn-primary" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-print"></i>Imprimir</button>
        </div>
        <?
        require_once(incPath.'/scripts.php');
        ?>
        <script>
            $("#enviarForm").click(function(e){
                e.preventDefault();
                parent.imprimirTurnos($("#profesional option:selected").val(),$("#fecha").val());
            })
			
            $("#fecha").datepicker({
                language:'es',
                format:'dd/mm/yyyy'
            });
        </script>
		
        <?
        // include(incPath.'/analytics.php');
        ?>
    </body>
</html>