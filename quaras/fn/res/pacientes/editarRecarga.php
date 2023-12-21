<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/fn.php');

AuthController::checkLogin();
AuthController::checkSuperAdmin();


db_query(0, "SELECT * from creditos_pacientes where idPaciente='{$_GET['id']}'");
$creditoPaciente = $tot > 0 ? $row : null;


$plan = null;
if ($creditoPaciente) {
    db_query(0, "SELECT * FROM creditos_planes WHERE idPlan = {$creditoPaciente['idPlan']}");
    $plan = $row;
}


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?
    $seccion = ucwords($general['nombrePaciente']);
    $subseccion = 'editarRecarga';
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
                <h1><i class="fas fa-list"></i> <?= ucwords($general['nombrePaciente']) ?> <i class="fa fa-angle-right"></i> Créditos</h1>
                <p>Utilice esta sección para recargar los créditos del <?=$general['nombrePaciente']?></p>
            </div>
            <a class="btn btn-outline-warning icon-btn" href="/pacientes/listado"><i class="fa fa-arrow-left"></i>Volver atrás</a>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-money-bill"></i> Recargar créditos</h3>
                    <div class="tile-body">
                        <form id="formulario">
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label class="control-label">Créditos del <?=$general["nombrePaciente"]?></label>
                                        <input class="form-control required" type="number" name="cantidad" value="<?= $creditoPaciente['disponible'] ?? '' ?>" placeholder="Ingrese la cantidad de créditos a recargar">
                                        <small>El <?=$general["nombrePaciente"]?> tiene el <strong>plan</strong> de <strong>Regarga <?= $plan["modo"] == "M" ? "Manual" : "Automática" ?></strong></small>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="idCreditoPlan" value="<?= $plan['idPlan'] ?>" />
                            <input type="hidden" name="idPaciente" value="<?= $_GET['id'] ?>" />
                            <input type="hidden" name="action" value="updateRecarga" />

                        </form>
                    </div>
                </div>
                <div class="tile-footer">
                    <button class="btn btn-primary" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-check-circle"></i>Guardar</button>&nbsp;&nbsp;&nbsp;<a class="btn btn-secondary" href="/pacientes/listado"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar</a>
                </div>
            </div>
        </div>
        </div>
    </main>

    <?
    require_once(incPath . '/scripts.php');
    ?>
    <!-- Page specific javascripts-->
    <script type="text/javascript" src="/js/plugins/bootstrap-notify.min.js"></script>
    <script type="text/javascript" src="/js/plugins/bootstrap-datepicker.min.js"></script>
    <script>
        $("#enviarForm").click(function() {
            algunoMal = 0;
            $(".required").each(function(key) {
                if ($(this).val().length < 1) {
                    $(this).addClass('is-invalid');
                    $(this).removeClass('is-valid');
                    algunoMal = 1;
                } else {
                    $(this).addClass('is-valid');
                    $(this).removeClass('is-invalid');
                }
            })

            //Si está todo bien submiteo
            if (algunoMal == 0) {
                $.post('/pacientes/save.php', $("#formulario").serialize(), function(response) {
                    if (response.status == 'OK') {
                        $.notify({
                            // options
                            icon: 'fa fa-check',
                            title: '',
                            message: 'La nueva recarga ha sido guardada con éxito'
                        }, {
                            // settings
                            type: "success",
                            allow_dismiss: true,
                            newest_on_top: false,
                            showProgressbar: false,
                            onClose: window.location.href = '/pacientes/editarRecarga?id=<?= $_GET['id'] ?>',
                            delay: 6000
                        });
                    } else {
                        console.log(response);
                        $.notify({
                            // options
                            icon: 'fa fa-check',
                            title: '',
                            message: 'Ha ocurrido un error al intentar guardar la recarga.'
                        }, {
                            // settings
                            type: "warning",
                            allow_dismiss: true,
                            newest_on_top: false,
                            showProgressbar: false
                        });
                    }
                })
            }

        })
    </script>

    <?
    // include(incPath.'/analytics.php');
    ?>
</body>

</html>