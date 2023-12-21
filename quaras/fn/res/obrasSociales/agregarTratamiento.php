<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/fn.php');

AuthController::checkLogin();
AuthController::checkSuperAdmin();


$action = 'agregar';
$icon = 'plus';

if ($_GET['id']) {
    db_query(0, "select * from tratamientos where idTratamiento='" . $_GET['id'] . "' limit 1");
    $action = 'editar';
    $icon = 'pen-to-square';
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?
    $seccion = ucwords($general['nombreObrasSociales']);
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
                <h1><i class="fas fa-list"></i> <?= ucwords($general['nombreObrasSociales']) ?></h1>
                <p>Utilice este listado para ver de un rápido vistazo los <?= ucwords($general['nombreObrasSociales']) ?> y administrarlos.</p>
            </div>
            <a class="btn btn-outline-warning icon-btn" href="/obrasSociales/tratamientos"><i class="fas fa-arrow-left"></i>Volver atrás</a>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title"><i class="fas fa-<?= $icon ?>"></i> <?= ucwords($action) ?> <?= ucwords($general['nombreObraSocial']) ?></h3>
                    <div class="tile-body">
                        <form id="formulario">
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label class="control-label">Nombre</label>
                                        <input class="form-control required" type="text" placeholder="<?= ucwords($general['nombreObraSocial']) ?>" name="nombre" value="<?= $row['nombre'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label class="control-label">Duración (en minutos)</label>
                                        <input class="form-control required" type="number" name="duracion" value="<?= $row['duracion'] ?? '' ?>">
                                    </div>
                                </div>

                                <!-- Metodo de pago -->
                                <? if ($general['mercadoPago'] || $general['paypal']) { ?>
                                    <!-- Si hay Pago le pido que seleccione el metodo de pago -->
                                    <div class="col">
                                        <div class="form-group">
                                            <label class="control-label">Pago</label>
                                            <select class="form-control required" name="pago">
                                                <option value="">-- Seleccione un método de pago --</option>
                                                <? if ($general['mercadoPago']) { ?>
                                                    <option value="MP" <?= ($row['pago'] == 'MP') ? 'selected' : '' ?>>MercadoPago</option>
                                                <? } ?>
                                                <? if ($general['paypal']) { ?>
                                                    <option value="PP" <?= ($row['pago'] == 'PP') ? 'selected' : '' ?>>PayPal</option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>
                                <? } ?>

                                <?
                                if (($general['cupos']) or ($general['simultaneosPorServicio'])){
                                ?>
                                    <div class="col">
                                        <div class="form-group">
                                            <label class="control-label">Cant. participantes</label>
                                            <input class="form-control required" type="number" name="simultaneos" value="<?= $row['simultaneos'] ?? '' ?>">
                                        </div>
                                    </div>
                                <?
                                }
                                ?>
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

                            <!-- Enviar mail -->
                            <div class="row">
                                <!-- Mail confirmacion -->
                                <? if ($general['tratamiento_enviarMailConfirmacion']) { ?>
                                    <div class="col">
                                        <div class="form-group">
                                            <div class="toggle-flip">
                                                <p class="mb-2">Enviar Mail de Confirmación</p>
                                                <label>
                                                    <input type="checkbox" name="enviarMailConfirmacion" value="1" <?= isset($row['enviarMailConfirmacion']) && $row['enviarMailConfirmacion'] == '1' ? 'checked' : '' ?>><span class="flip-indecator" data-toggle-on="Si" data-toggle-off="No"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <? } ?>

                                <!-- Mail recordatorio -->
                                <? if ($general['tratamiento_enviarMailRecordatorio']) { ?>
                                    <div class="col">
                                        <div class="form-group">
                                            <div class="toggle-flip">
                                                <p class="mb-2">Enviar Mail de Recordatorio</p>
                                                <label>
                                                    <input type="checkbox" name="enviarMailRecordatorio" value="1" <?= isset($row['enviarMailRecordatorio']) && $row['enviarMailRecordatorio'] == '1' ? 'checked' : '' ?>><span class="flip-indecator" data-toggle-on="Si" data-toggle-off="No"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <? } ?>
                            </div>

                            
                            <?
                            if ($general['textosTratamientos']) {
                            ?>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="control-label" title="Este texto se mostrará al principio de los emails de confirmación y recordatorio del <?=$general['nombreTurno']?>." data-toggle="tooltip">Encabezado <i class="fa fa-info-circle"></i></label>
                                        <div class="form-group g-mb-30">
                                            <div class="g-pos-rel">
                                                <textarea id="textoPre" name="textoPre" class="form-control form-control-md g-brd-gray-light-v7 g-brd-gray-light-v3--focus g-rounded-4 g-px-14 g-py-10"><?= $row['textoPre'] ?? '' ?></textarea>
                                                <small><i class="fa fa-info-circle"></i> En caso de no completar se enviará el texto estándar. Puede usar %nombre% para indicar el nombre del <?=$general["nombrePaciente"]?>.</span></b></small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <label class="control-label" title="Este texto se mostrará al final de los emails de confirmación y recordatorio del <?=$general['nombreTurno']?>.<?= ($general['mostrarTextoTratamientoPublic']) ? ' También se mostrará en la pantalla de toma de '.$general['nombreTurnos'].' cuando el usuario seleccione este ' . $general['nombreObraSocial'] . '.' : '' ?>" data-toggle="tooltip">Información adicional <i class="fa fa-info-circle"></i></label>
                                        <div class="form-group g-mb-30">
                                            <div class="g-pos-rel">
                                                <?
                                                    $valueTextPost = $row['textoPost'] ? str_replace("nbsp;"," ", $row['textoPost']) : '';
                                                ?>
                                                <textarea id="textoPost" name="textoPost" class="form-control form-control-md g-brd-gray-light-v7 g-brd-gray-light-v3--focus g-rounded-4 g-px-14 g-py-10"><?=$valueTextPost?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?
                            }
                            ?>

                            <input type="hidden" name="action" value="saveTratamiento" />
                            <?
                            if (isset($_GET['id']) && $_GET['id']) {
                            ?>
                                <input type="hidden" name="id" value="<?= $_GET['id'] ?>" />
                            <?
                            }
                            ?>
                        </form>
                    </div>
                    <div class="tile-footer">
                        <button class="btn btn-primary" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-check-circle"></i>Guardar</button>&nbsp;&nbsp;&nbsp;<a class="btn btn-secondary" href="/obrasSociales/tratamientos"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar</a>
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
        $('#textoPre, #textoPost').summernote({
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']]
            ]
        });

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
                            message: 'El <?= ($general['nombreObraSocial']) ?> ha sido guardado con éxito'
                        }, {
                            // settings
                            type: "success",
                            allow_dismiss: true,
                            newest_on_top: false,
                            showProgressbar: false,
                            onClose: window.location.href = '/obrasSociales/tratamientos',
                            delay: 6000
                        });
                    } else {
                        $.notify({
                            // options
                            icon: 'fa fa-check',
                            title: '',
                            message: 'Ha ocurrido un error al intentar guardar el <?= ($general['nombreObraSocial']) ?>. ' + response.status
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