<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/fn.php');
AuthController::checkLogin();

if (!$general["feriadoPersonalizado"]) {
    header("Location: /admin");
    die();
}

$iconito = 'plus';
$titulo = 'Agregar';

if (isset($_GET['id'])) {
    $dataFeriado = db_getOne("SELECT * FROM feriadosPersonalizados WHERE idFeriadoPersonalizado = {$_GET['id']}");
    $iconito = 'pen-to-square';
    $titulo = 'Editar';
}

$profesionales = array();

$profesionales[0] = "Todos";
foreach (db_getAll("SELECT idProfesional, nombre FROM profesionales WHERE estado <> 'B'") as $profesional) {
    $profesionales[$profesional->idProfesional] = ucfirst($profesional->nombre);
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?
    $seccion = "Feriados personalizados";
    $subseccion = 'Editar';
    require_once(incPath . '/head.php');
    ?>
</head>

<body class="app sidebar-mini rtl">
    <?
    require_once(incPath . '/header.php');
    require_once(incPath . '/sidebar.php');
    ?>
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-bed"></i> Feriados personalizados</h1>
                <p>Utilice este formulario para cargar feriados.</p>
            </div>
            <a class="btn btn-outline-warning icon-btn" href="/feriadosPersonalizados"><i class="fa fa-arrow-left"></i>Volver atrás</a>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title"><i class="fa fa-<?= $iconito ?>"></i> <?= $titulo ?> feriado personalizado</h3>

                    <div class="tile-body">
                        <form id="formulario">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="control-label">Descripción</label>
                                        <input type="text" id="descripcion" name="descripcion" class="form-control" value="<?= $dataFeriado->descripcion ?>" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"><?=ucfirst($general["nombreProfesionales"])?></label>
                                        <select class="form-control" name="idProfesional">
                                            <? foreach ($profesionales as $idProfesional => $nombre) { ?>
                                                <option value="<?=$idProfesional?>" <?=isset($dataFeriado) && $dataFeriado->idProfesional == $idProfesional ? "selected" : ""?> ><?=$nombre?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Rango del feriado</label>
                                        <input class="form-control" type="text" id="fecha" name="fecha">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">Inicio</label>
                                        <input class="form-control" type="time" name="horarioInicio" value="<?=isset($dataFeriado) ? date('H:i', strtotime($dataFeriado->horarioInicio)) : date('H:i')?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">Fin</label>
                                        <input class="form-control" type="time" name="horarioFin" value="<?=isset($dataFeriado) ? date('H:i', strtotime($dataFeriado->horarioFin)) : date('H:i')?>">
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="action" value="<?= isset($_GET['id']) ? 'update' : 'save' ?>" />
                            <input type="hidden" name="id" value="<?= $_GET['id'] ?? '' ?>" />
                        </form>
                    </div>
                    <div class="tile-footer">
                        <button class="btn btn-primary" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-check-circle"></i>Agendar</button>&nbsp;&nbsp;&nbsp;<a class="btn btn-secondary" href="/feriadosPersonalizados"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?
    require_once(incPath . '/scripts.php');
    ?>

    <script>
        $('#fecha').daterangepicker({
            "autoApply": true,
            "locale": {
                "format": "DD/MM/YYYY",
                "separator": " - ",
                "applyLabel": "Aplicar",
                "cancelLabel": "Cancelar",
                "fromLabel": "Desde",
                "toLabel": "Hasta",
                "customRangeLabel": "Personalizado",
                "weekLabel": "S",
                "daysOfWeek": [
                    "Do",
                    "Lu",
                    "Ma",
                    "Mi",
                    "Ju",
                    "Vi",
                    "Sa"
                ],
                "monthNames": [
                    "Enero",
                    "Febrero",
                    "Marzo",
                    "Abril",
                    "Mayo",
                    "Junio",
                    "Julio",
                    "Agosto",
                    "Septiembre",
                    "Octubre",
                    "Noviembre",
                    "Diciembre"
                ],
                "firstDay": 1
            },
            "startDate": "<?=isset($_GET['id']) ? date('d/m/Y', strtotime($dataFeriado->fechaDesde)) : date('d/m/Y')?>",
            "endDate": "<?=isset($_GET['id']) ? date('d/m/Y', strtotime($dataFeriado->fechaHasta)) : date('d/m/Y')?>"
        });


        $("#enviarForm").click(function() {
            let originalButton = $("#enviarForm").html();
            preloaderButton('show', originalButton, 'enviarForm');

            $.post('/feriadosPersonalizados/save.php', $("#formulario").serialize(), function({status, title, message, type}) {
                preloaderButton('hide', originalButton, 'enviarForm');

                Swal.fire(title, message, type).then(res => {
                    if(status == "OK"){
                        location.href="/feriadosPersonalizados/"
                    }
                })
            })
        })
    </script>
</body>

</html>