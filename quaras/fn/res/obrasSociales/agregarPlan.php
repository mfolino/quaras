<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/fn.php');

AuthController::checkLogin();
AuthController::checkSuperAdmin();

/* Util::errors(); */

$action = 'agregar';
$icon = 'plus';

if ($_GET['id']) {
    db_query(0, "select * from creditos_planes where idPlan='" . $_GET['id'] . "' limit 1");
    /* db_query(1,"select idTratamiento from creditos_servicios where idPlan='".$row['idPlan']."'");
    for($i1=0;$i1<$tot1;$i1++){
        $nres1=$res1->data_seek($i1);
        $row1=$res1->fetch_assoc();
        $tratamientos[$row1['idTratamiento']]=1;
    } */
    $action = 'editar';
    $icon = 'pen-to-square';
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?
    $seccion = 'Planes';
    $subseccion = ucwords($action);
    require_once(incPath . '/head.php');
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.css">
</head>

<body class="app sidebar-mini rtl">
    <?
    require_once(incPath . '/header.php');
    require_once(incPath . '/sidebar.php');
    ?>
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fas fa-coins"></i> Planes</h1>
                <p>Utilice este listado para ver de un rápido vistazo los planes y administrarlos.</p>
            </div>
            <a class="btn btn-outline-warning icon-btn" href="/obrasSociales/planesCreditos"><i class="fas fa-arrow-left"></i>Volver atrás</a>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-<?= $icon ?>"></i> <?= ucwords($action) ?> plan</h3>
                    <div class="tile-body">
                        <form id="formulario">
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label class="control-label">Nombre</label>
                                        <input class="form-control required" type="text" placeholder="Plan" name="nombre" value="<?= $row['nombre'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label class="control-label">Recarga</label>
                                        <select class="form-control required" name="modo" id="modo">
                                            <option value="M" <?=($row['modo']=='M') ? ' selected' : ''?>>Manual</option>
                                            <option value="A"<?=($row['modo']=='A') ? ' selected' : ''?>>Automática</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col col-diaMes <?=!isset($_GET['id']) ? 'd-none' : ''?>">
                                    <div class="form-group">
                                        <label class="control-label">Día de renovación</label>
                                        <select class="form-control" name="diaMes" id="diaMes">
                                            <option value="1" <?=($row['diaMes']=='1') ? ' selected' : ''?>>Principio de mes</option>
                                            <option value="5"<?=($row['diaMes']=='5') ? ' selected' : ''?>>5 de cada mes</option>
                                            <option value="10"<?=($row['diaMes']=='10') ? ' selected' : ''?>>10 de cada mes</option>
                                            <option value="15"<?=($row['diaMes']=='15') ? ' selected' : ''?>>15 de cada mes</option>
                                            <option value="20"<?=($row['diaMes']=='20') ? ' selected' : ''?>>20 de cada mes</option>
                                            <option value="25"<?=($row['diaMes']=='25') ? ' selected' : ''?>>25 de cada mes</option>
                                            <option value="end"<?=($row['diaMes']=='end') ? ' selected' : ''?>>Fin de mes</option>
                                        </select>
                                    </div>
                                </div>

                            
                                <div class="col">
                                    <div class="form-group">
                                        <label class="control-label">Cant. créditos</label>
                                        <input class="form-control required" type="number" name="cantidad" value="<?= $row['cantidad'] ?? '' ?>">
                                    </div>
                                </div>
                                

                                <!-- Estado -->
                                <div class="col">
                                    <div class="form-group">
                                        <div class="toggle-flip">
                                            <p class="mb-2">Estado</p>
                                            <label>
                                                <input type="checkbox" name="estado" value="A" <?= isset($row['estado']) && (($row['estado'] == '') or ($row['estado'] == 'A') or ($row['estado'] == 1)) ? 'checked' : '' ?>><span class="flip-indecator" data-toggle-on="Activa" data-toggle-off="Inactiva"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>


                            <input type="hidden" name="action" value="savePlan" />
                            <? if (isset($_GET['id']) && $_GET['id']) { ?>
                                <input type="hidden" name="id" value="<?= $_GET['id'] ?>" />
                            <? } ?>
                        </form>
                    </div>
                    <div class="tile-footer">
                        <button class="btn btn-primary" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-check-circle"></i>Guardar</button>&nbsp;&nbsp;&nbsp;<a class="btn btn-secondary" href="/obrasSociales/planesCreditos"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?
    require_once(incPath . '/scripts.php');
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>

    <script>

        $("#modo").on('change', function(){
            if($(this).val()=='A'){
                $(".col-diaMes").removeClass('d-none');
            }else{
                $(".col-diaMes").addClass('d-none');
            }
        })

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
                $.post('/obrasSociales/save.php', $("#formulario").serialize(), function(response) {
                    console.log(response);
                    if (response.status == 'OK') {
                        $.notify({
                            // options
                            icon: 'fa fa-check',
                            title: '',
                            message: 'El plan ha sido guardado con éxito'
                        }, {
                            // settings
                            type: "success",
                            allow_dismiss: true,
                            newest_on_top: false,
                            showProgressbar: false,
                            onClose: window.location.href = '/obrasSociales/planesCreditos',
                            delay: 6000
                        });
                    } else {
                        $.notify({
                            // options
                            icon: 'fa fa-check',
                            title: '',
                            message: 'Ha ocurrido un error al intentar guardar el plan. ' + response.status
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

        $("#servicios").select2();
    </script>

    <?
    // include(incPath.'/analytics.php');
    ?>
</body>

</html>