<?
require_once('./fn/fn.php');

$campo = array();

switch ($general['tomaTurno']) {
    case 'dni':
        $campo['label'] = $general['nombreDNI'];
        $campo['helper'] = $general['leyendaDni'];
        $campo['tipo'] = 'tel';
        $campo['clase'] = 'soloNumeros';
        $campo['placeholder'] = str_repeat('X', $general['largoDNI']);
        $campo['maxlength'] = $general['largoDNI'];
        $campo['minlength'] = 7;
        $campo['hidden'] = 'dni';
        break;
    case 'email':
        $campo['label'] = $general['nombreMail'];
        $campo['helper'] = '';
        $campo['tipo'] = 'email';
        $campo['clase'] = '';
        $campo['placeholder'] = 'usuario@dominio.com';
        $campo['maxlength'] = '255';
        $campo['minlength'] = '10';
        $campo['hidden'] = 'mail';
        break;
    case 'telefono':
        $campo['label'] = $general['nombreTelefono'];
        $campo['helper'] = $general['leyendaTelefono'];
        $campo['tipo'] = 'tel';
        $campo['clase'] = 'soloNumeros';
        $campo['placeholder'] = str_repeat('X', $general['telLargoMax']);
        $campo['maxlength'] = $general['telLargoMax'];
        $campo['minlength'] = $general['telLargoMin'];
        $campo['hidden'] = 'telefono';
        break;
}

$entradas =  db_getAll("SELECT * FROM boletos WHERE estado = 'A' ORDER BY nombre");
$mediosDePago = db_getAll("SELECT * FROM mediosDePago WHERE eliminado = 0 ORDER BY nombre");

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?
    $seccion = "Turnos";
    $subseccion = '';
    require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/head.php');
    ?>
</head>

<body>
    <section class="material-half-bg">
        <div class="cover"></div>
    </section>
    <section class="login-content">

        <div class="logo mt-5">
            <img src="img/<?= $general['isologo'] ?>" width="<?= $general['logoWidth'] ?>" class="img-fluid">
        </div>
        <div class="login-box">
            <form class="nuevoTurno-form" id="formulario">
                <div class="login-head text-left">
                    <h3><i class="fas fa-calendar-days"></i> <?= ucwords($general['nombreTurnos']) ?></h3>
                    <p class="my-0 text-muted"><?= $general['leyendaTomaTurno'] ?> <?= ($general['nombreTurno']) ?>.</p>
                </div>

                <div class="row datosCliente">
                    <div class="col">
                        <h5><i class="fas fa-user"></i> Tus datos</h5>
                        <div class="form-group">
                            <label class="control-label"><?= $campo['label'] ?></label>
                            <div class="input-group">
                                <? if ($general['tomaTurno'] == 'telefono') { ?>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">+<?= $general['prefijoTelefonico'] ?></span>
                                    </div>
                                    <input class="form-control required soloNumeros" type="tel" placeholder="Cód area" name="codArea" id="codArea" value="<?= $general['codAreaDefault'] ?>" minlength="2" maxlength="4">
                                <? } ?>
                                <input class="form-control required <?= $campo['clase'] ?>" type="<?= $campo['tipo'] ?>" placeholder="<?= $campo['placeholder'] ?>" maxlength="<?= $campo['maxlength'] ?>" minlength="<?= $campo['minlength'] ?>" id="campoValidacion" value="" data-toggle="tooltip" data-placement="bottom" title="Aguarda un instante... Estamos verificando tu <?= $campo['label'] ?>.">
                            </div>
                            <small><?= $campo['helper'] ?></small>
                        </div>
                    </div>
                </div>

                <div class="row bienvenido d-none">
                    <div class="col-md-12">
                        <div class="alert alert-success" role="alert">
                            Bienvenida/o de vuelta <b><span class="nombreCliente"></span></b>!
                        </div>
                        <? if ((@$general['misTurnos']) and (@$general['plan'] > 2)) { ?>
                            <button type="button" class="btn btn-info btn-block align-self-end my-3" id="cancelarTurnosBtn"><i class="fas fa-user-circle"></i> Acceder a mis <?= $general['nombreTurnos'] ?></button>
                        <? } ?>
                    </div>
                </div>

                <div class="row nuevoPaciente d-none datosCliente">
                    <div class="col">
                        <div class="form-group">
                            <label class="control-label">Nombre</label>
                            <input class="form-control required" type="text" placeholder="" name="nombre" id="nombre">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="control-label">Apellido</label>
                            <input class="form-control required" type="text" placeholder="" name="apellido" id="apellido">
                        </div>
                    </div>

                    <?
                    if ($general['tomaTurno'] <> 'telefono') {
                    ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Teléfono móvil</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">+<?= $general['prefijoTelefonico'] ?></span>
                                    </div>
                                    <input class="form-control required soloNumeros" type="tel" placeholder="Cód area" name="codArea" id="codArea" value="<?= $general['codAreaDefault'] ?>" minlength="2" maxlength="4">
                                    <input class="form-control required soloNumeros" type="tel" placeholder="<?= str_repeat('X', $general['telLargoMax']) ?>" name="telefono" id="telefono" value="" minlength="<?= $general['telLargoMin'] ?>" maxlength="<?= $general['telLargoMax'] ?>">
                                </div>
                                <small><?= $general['leyendaTelefono'] ?></small>
                            </div>
                        </div>
                    <?
                    }
                    ?>
                </div>

                <?
                if ($general['tomaTurno'] <> 'email') {
                ?>
                    <div class="campoMail row d-none datosCliente">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Completá tu e-mail para poder enviarte notificaciones de tus turnos</label>
                                <input class="form-control required" type="email" placeholder="nombre@dominio.com" name="mail" id="mail" value="">
                            </div>
                        </div>
                    </div>
                <?
                }
                ?>

                <div class="row">
                    <div class="col">
                        <h5><i class="fas fa-calendar"></i> Reserva</h5>
                    </div>
                </div>

                <div class="row d-none">
                    <?
                    if ($general['nivelCategorias']) {
                    ?>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label"><?= ucwords($general['nombreCategoria']) ?></label>
                                <select class="form-control selectpickerSearch" name="categoria" id="categoria">
                                    <option value="">Seleccione...</option>

                                    <?
                                    db_query(
                                        1,
                                        "SELECT idCategoria, nombre
                                        FROM categorias
                                        WHERE estado = 'A'
                                        ORDER BY nombre"
                                    );

                                    for ($i1 = 0; $i1 < $tot1; $i1++) {
                                        $nres1 = $res1->data_seek($i1);
                                        $row1 = $res1->fetch_assoc();
                                    ?>

                                        <option value="<?= $row1['idCategoria'] ?>"><?= $row1['nombre'] ?></option>

                                    <? } ?>

                                </select>
                            </div>
                        </div>
                    <?
                    }
                    ?>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label"><?= ucwords($general['nombreObraSocial']) ?></label>
                            <select class="form-control selectpickerSearch" name="tratamiento" id="tratamiento" disabled>
                                <option value="">Seleccione categoría...</option>
                            </select>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-5 d-none">
                        <div class="form-group">
                            <label class="control-label"><?= ucwords($general['nombreProfesional']) ?></label>
                            <select class="form-control required" name="profesional" id="profesional" disabled>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                    </div>

                    <div class="col">
                        <div class="form-group">
                            <label class="control-label">Fecha</label>
                            <input class="form-control required" type="text" readonly placeholder="DD/MM/AAAA" id="fecha" name="fecha" value="" style="background-color:#fff" disabled>
                        </div>
                    </div>
                    <div class="col d-none">
                        <div class="form-group">
                            <label class="control-label">Hora</label>
                            <select id="horas" name="horas" class="form-control required" disabled>
                                <option value="">Seleccione fecha...</option>
                            </select>
                        </div>
                    </div>
                </div>


                <!-- ------------------------------- -->
                <!--           PROMOCIONES           -->
                <!-- ------------------------------- -->
                <div id="contentPromociones"></div>

                
                <!-- ------------------------------- -->
                <!--            ENTRADAS             -->
                <!-- ------------------------------- -->
                <div class="pt-2">
                    <h5><i class="fas fa-ticket-alt"></i> <?=ucfirst($general["nombreBoletos"])?> <span id="entradas__cantEntradas"></span></h5>
                </div>

                <div id="contentParticipantes">
                    <div class="row filaParticipante mt-1">
                        <div class="col">
                            <select class="form-control participante__selectParticipante">
                                <? foreach ($entradas as $entrada) { ?>
                                    <option value="<?=$entrada->idBoleto?>" data-precio="<?=$entrada->precio?>" data-nombre="<?=ucfirst($entrada->nombre)?>"><?=ucfirst($entrada->nombre)?> <?= $entrada->precio == 0 ? "(GRATIS)": "($".$entrada->precio.")"?></option>
                                <? } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mt-2">
                        <button id="primerParticipante" type="button" class="btn btn-success btn-sm" onclick="participante__agregarFila()"><i class="fas fa-plus mr-1"></i>Agregar entrada</button>
                    </div>
                </div>
                    
                <!-- --------------------------- -->
                <!--            PAGO             -->
                <!-- --------------------------- -->
                <div class="row">
                    <div class="col-12"><hr></div>
                    <div class="col-12 text-center"><h5 class="m-0"><i class="fas fa-credit-card mr-1"></i>Detalle del Pago</h5></div>
                    <div class="col-12"><hr></div>

                    <div class="col-md-6 d-flex align-items-center">
                        <p class="m-0 p-0 d-none">Entradas: <span id="spanCantidadEntradas">0</span></p>
                        <div class="m-0 p-0" id="detalleEntradas">Sin fecha seleccionada</div>
                    </div>

                    <div class="col-md-6 text-center d-flex justify-content-center align-items-center">
                        <p class="m-0 p-0 display-4" style="font-size: 3.5em;">$<span id="spanTotalParticipantes">0</span></p>
                    </div>
                </div>
                
                <!-- --------------------------- -->
                <!--        MEDIOS DE PAGO       -->
                <!-- --------------------------- -->
                <? if(count($mediosDePago) > 0): ?>
                    <hr>
                    <h5 class="mb-2"><i class="fa-solid fa-hand-holding-dollar mr-1"></i>Medio de pago</h5>
                    <div class="form-group">
                        <select class="form-control selectMedioDePago" id="tipoDeMedioDePago" onchange="participante__calcularPrecio()">
                            <option value="0" data-operacion="d" data-porcentaje="0">-- Seleccione un medio de pago --</option>
                            <? foreach ($mediosDePago as $medioDePago) { 
                                $textoPorcentajeDescuento = "";
                                if($medioDePago->porcentaje > 0){
                                    $textoPorcentajeDescuento = $medioDePago->tipoOperacion == "r" ? "(+ {$medioDePago->porcentaje}%)" : "(- {$medioDePago->porcentaje}%)";
                                }
                            ?>
                                <option 
                                    value="<?=$medioDePago->idMedioDePago?>" 
                                    data-operacion="<?=$medioDePago->tipoOperacion?>" 
                                    data-porcentaje="<?=$medioDePago->porcentaje?>"
                                >
                                    <?=ucfirst($medioDePago->nombre)?> <?=$textoPorcentajeDescuento?>
                                </option>
                            <? } ?>
                        </select>
                    </div>
                <? else: ?>
                    <input type="hidden" id="tipoDeMedioDePago" value="0" />
                <? endif; ?>

                <input type="hidden" id="idTratamiento" name="idTratamiento" value="" />
                <input type="hidden" id="idProfesional" name="idProfesional" value="" />
                <input type="hidden" id="idPaciente" name="idPaciente" value="" />
                <input type="hidden" name="action" value="saveExternal" />
                <input type="hidden" id="observaciones" name="observaciones" value="" />
                <input type="hidden" name="<?= $campo['hidden'] ?>" id="<?= $campo['hidden'] ?>" value="" />
                <input type="hidden" name="precioTotal" id="precioTotal" value="">
                <input type="hidden" name="participantes" id="participantes" value="">

                <div class="form-group btn-container mt-3">
                    <button class="btn btn-primary btn-block loginBtn"><i class="fa fa-calendar-o fa-lg fa-fw"></i>AGENDAR</button>
                </div>
            </form>
        </div>

        <? require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/footer.php'); ?>
    </section>

    <div class="modal fade" role="dialog" id="cancelarTurnos">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-circle"></i> Mis turnos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="nuevoTurno-form m-0 p-0" id="formulario">

                        <div class="d-none" id="turnosCancelar">

                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- < ? require_once "./modalTerminosYCondiciones.php"; ?> -->
    <? require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/scripts.php'); ?>

    <!-- CDN Firma digital -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>



    <!-- -------------------- -->
    <!--        MAIN JS       -->
    <!-- -------------------- -->
    <script>
        var maximosCupos = 1
        var allPromociones = []
        var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        function abrirModalTerminosYCondiciones() {
            $("#modalTerminosYCondiciones").modal("show")
        }

        function entradas__getCantidad(){
            const cantidad = $(".filaParticipante").length
            $("#spanCantidadEntradas").html(cantidad)
            return cantidad
        }

        /* ***** Funciones Agregar Participantes ***** */
        function participante__agregarFila() {

            // Valido la cantidad máxima de cupos
            if(entradas__getCantidad() >= maximosCupos){
                Swal.fire("Sin cupos!", "No hay más <?=$general['nombreBoletos']?> disponibles.", "warning")
                return
            }

            $("#primerParticipante").prop("disabled", true)

            $("#contentParticipantes").append(participantes__htmlFila())
            
            $(".participante__borrarFila").click(function() {
                $(this).closest('.filaParticipante').remove();
                participante__calcularPrecio()
                entradas__getCantidad()
            })

            $(".participante__selectParticipante").last().change(function(e) {
                participante__calcularPrecio()
            })
            
            participante__calcularPrecio()

            $("#primerParticipante").prop("disabled", false)
            entradas__getCantidad()
        }


        function participantes__htmlFila() {
            return `
                <div class="row filaParticipante mt-1">
                    <div class="col">
                        <select class="form-control participante__selectParticipante">
                            <? foreach ($entradas as $entrada) { ?>
                                <option value="<?=$entrada->idBoleto?>" data-precio="<?=$entrada->precio?>" data-nombre="<?=ucfirst($entrada->nombre)?>"><?=ucfirst($entrada->nombre)?> <?= $entrada->precio == 0 ? "(GRATIS)": "($".$entrada->precio.")"?></option>
                            <? } ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-danger participante__borrarFila" ><i class="fa fa-trash m-0"></i></button>
                    </div>
                </div>
            `
        }


        function participante__calcularPrecio(){
            
            let precioTotalParticipante = 0
            let participantes = [];
            let participantesPorBoleto = {}

            $(".filaParticipante").each(function(index, obj){
                const idBoleto = $(this).find(".participante__selectParticipante option:selected").val()
                const precio = $(this).find(".participante__selectParticipante option:selected").data("precio")
                const nombre = $(this).find(".participante__selectParticipante option:selected").data("nombre")
                
                participantes.push({idBoleto,precio})
                precioTotalParticipante += parseInt(precio)

                if(participantesPorBoleto[idBoleto]){
                    participantesPorBoleto[idBoleto] = {
                        idBoleto,
                        nombre,
                        cantidad: participantesPorBoleto[idBoleto].cantidad + 1,
                        precioUnitario: parseInt(precio), 
                        precioTotal: participantesPorBoleto[idBoleto].precioTotal + parseInt(precio)
                    }
                }else{
                    participantesPorBoleto[idBoleto] = {
                        idBoleto,
                        nombre,
                        cantidad: 1,
                        precioUnitario: parseInt(precio),
                        precioTotal: parseInt(precio)
                    }
                }
            })

            participantesPorBoleto = Object.values(participantesPorBoleto)
            // console.log(participantesPorBoleto)
            
            /* ------------------------------------------------- */
            /*      VOY A CHEQUEAR LAS PROMOCIONES VIGENTES      */
            /* ------------------------------------------------- */
            let htmlDetalleEntradas = ''
            let dataPromocionesUsadas = []
            if(allPromociones.length > 0){
                let nuevoTotalConPromociones = 0;

                for (const tipoBoleto of participantesPorBoleto) {
                    // Busco si hay promociones para esa entrada
                    const promocionesDisponibles = allPromociones.filter(p => p.idBoleto == tipoBoleto.idBoleto)
                    
                    // No hay promociones para el boleto
                    if(promocionesDisponibles.length == 0) continue

                    // Busco la promocion con mayor descuento
                    let minPrecioPromocion = 0;
                    let dataMejorPromocion = {}
                    for (const promocion of promocionesDisponibles) {
                        // Si no hay boletos suficientes para la promocion
                        if(tipoBoleto.cantidad < promocion.cantidadEntradas) continue;

                        promocion.cantidadEntradas = parseInt(promocion.cantidadEntradas)
                        promocion.entradasGratis = parseInt(promocion.entradasGratis)

                        // Busco la cantidad de promociones que voy a usar
                        const cantPromociones = parseInt(tipoBoleto.cantidad / promocion.cantidadEntradas) 
                        const precioPorPromocion = (promocion.cantidadEntradas - promocion.entradasGratis) * tipoBoleto.precioUnitario
                        const precioEntradasSueltas = (tipoBoleto.cantidad % promocion.cantidadEntradas) * tipoBoleto.precioUnitario
                        const totalPromocion = (cantPromociones * precioPorPromocion) + precioEntradasSueltas

                        if(minPrecioPromocion == 0 || totalPromocion < minPrecioPromocion){
                            const dataPromocionItem = {...promocion}
                            dataPromocionItem.usadas = cantPromociones
                            dataPromocionItem.entradasSueltas = tipoBoleto.cantidad % promocion.cantidadEntradas
                            dataPromocionItem.precioEntradasSueltas = precioEntradasSueltas
                            
                            dataMejorPromocion = {
                                tipoBoleto,
                                promocion: dataPromocionItem,
                                totalEntradas: tipoBoleto.precioTotal,
                                totalDescuento: tipoBoleto.precioTotal - totalPromocion,
                                total: totalPromocion
                            }

                            // Guardo el valor de la promo más economica
                            minPrecioPromocion = totalPromocion
                        }
                    }

                    // Guardo la info de la promocion
                    if(minPrecioPromocion != 0){
                        dataPromocionesUsadas.push(dataMejorPromocion)
                    }
                }

            }
            
            precioTotalParticipante = 0
            for (const tipoBoleto of participantesPorBoleto) {
                const promocionDelBoleto = dataPromocionesUsadas.find(p => p.tipoBoleto.idBoleto == tipoBoleto.idBoleto)

                // Tiene promocion
                if(!!promocionDelBoleto){
                    const textoDetallePromo = promocionDelBoleto.promocion.usadas == 1 ? "promo" : "promos"
                    const entradasACobrarDeLaPromocion = promocionDelBoleto.promocion.cantidadEntradas - promocionDelBoleto.promocion.entradasGratis
                    htmlDetalleEntradas += `
                        <p class="m-0 p-0 font-weight-bold">${promocionDelBoleto.promocion.nombre}:</p>
                        <p class="m-0 p-0">- ${promocionDelBoleto.promocion.usadas} ${textoDetallePromo} de ${promocionDelBoleto.promocion.cantidadEntradas} x ${entradasACobrarDeLaPromocion}</p>
                    `
                    if(promocionDelBoleto.promocion.entradasSueltas){
                        const textoDetalleEntrada = promocionDelBoleto.promocion.entradasSueltas == 1 ? "entrada simple" : "entradas simples"
                        htmlDetalleEntradas += `
                            <p class="m-0 p-0">- ${promocionDelBoleto.promocion.entradasSueltas} ${textoDetalleEntrada}</p>
                        `
                    }

                    precioTotalParticipante += promocionDelBoleto.total
                }else{
                    const textoDetalleEntrada = tipoBoleto.cantidad == 1 ? "entrada simple" : "entradas simples"
                    htmlDetalleEntradas += `
                        <p class="m-0 p-0"><b>${tipoBoleto.nombre}:</b></p>
                        <p class="m-0 p-0">- ${tipoBoleto.cantidad} ${textoDetalleEntrada}</p>
                    `
                    precioTotalParticipante += tipoBoleto.precioTotal
                }

            }

            /* ----------------------------------------------------- */
            /*          APLICO DESCUENTOS POR MEDIO DE PAGO          */
            /* ----------------------------------------------------- */
            const elementMedioDePago = document.getElementById("tipoDeMedioDePago") // Siempre va a existir esto
            if(precioTotalParticipante > 0 && elementMedioDePago.value != 0){ // Si hay un precio total y si hay medio de pago
                const tipoOperacion = $("#tipoDeMedioDePago option:selected").data("operacion")
                const porcentaje = $("#tipoDeMedioDePago option:selected").data("porcentaje")

                if(porcentaje > 0){
                    const porcentajeDescuento = tipoOperacion == "d" ? (100 - porcentaje) : (100 + porcentaje)
                    precioTotalParticipante = parseInt(precioTotalParticipante * (porcentajeDescuento / 100))
                }
            }

            $("#detalleEntradas").html(htmlDetalleEntradas)

            // Seteo el total
            $("#spanTotalParticipantes").html(precioTotalParticipante) 
            $("#precioTotal").val(precioTotalParticipante)
        }
        

        $("#observaciones").val(timezone);

        $(".selectpickerSearch").selectpicker({
            liveSearch: true,
            size: 5,
            style: '',
            styleBase: 'form-control',
        })
        $(".selectpicker").selectpicker({
            style: '',
            styleBase: 'form-control',
            showSubtext: true
        })

        // Deshabilitado
        $("#cancelarTurnosBtn").on('click', function() {
            var htmlOriginal = $(this).html();
            $(this).html('<i class="fas fa-spinner fa-spin"></i> Cargando turnos...');

            $.post('/turnos/save', {
                action: 'getTurnosUsuario',
                idPaciente: $("#idPaciente").val()
            }, function(response) {

                console.log(response);

                if (response.status == 'OK') {

                    $("#cancelarTurnos #turnosCancelar").html('');

                    $.each(response.turnos, function(fecha, value) {

                        console.log(value);

                        let html = '<div class="row border-bottom py-2" id="turno' + value.idTurno + '"><div class="col-md-4 d-flex align-items-center">' + value.fecha + ' ' + value.hora + '</div><div class="col-md-6 d-flex align-items-center">' + value.tratamiento + '</div><div class="col-md-2 d-flex align-items-center">';

                        if (value.estado == 'confirmado') {
                            html += '<button type="button" data-toggle="tooltip" title="Asististe a este turno." class="btn btn-success btn-block"><i class="fa fa-user-plus m-0 p-0"></i></button>';
                        }
                        if (value.estado == 'ausente') {
                            html += '<button type="button" data-toggle="tooltip" title="No asististe a este turno." class="btn btn-warning btn-block"><i class="fa fa-user-times m-0 p-0"></i></button>';
                        }
                        if (value.estado == 'cancelar') {
                            html += '<button type="button" onclick="cancelarTurno(' + value.idTurno + ')" class="btn btn-danger btn-block"><i class="fa fa-times-circle m-0 p-0"></i></button>';
                        }
                        if (value.estado == 'cancelarAntes') {
                            html += '<button type="button" disabled data-toggle="tooltip" title="No es posible cancelar este turno ya que faltan menos de 6 horas para el inicio del mismo." class="btn btn-danger btn-block"><i class="fa fa-times-circle m-0 p-0"></i></button>';
                        }
                        if (value.estado == 'pasado') {
                            html += '<button type="button" disabled data-toggle="tooltip" title="No es posible cancelar este turno ya que el mismo ya pasó." class="btn btn-secondary btn-block"><i class="fa fa-calendar-minus-o m-0 p-0"></i></button>';
                        }

                        html += '</div></div>';

                        $("#cancelarTurnos #turnosCancelar").append(html);
                    })

                    $("#cancelarTurnos #turnosCancelar").removeClass('d-none');

                    $("#cancelarTurnos").modal('show');

                    $('[data-toggle="tooltip"]').tooltip();
                } else {
                    Swal.fire('Lo sentimos!', 'No hemos encontrado turnos asociados a tu usuario.', 'error');
                }

                $("#cancelarTurnosBtn").html(htmlOriginal);

                inicializar();
            })
        })



        function cancelarTurno(id) {
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

                if (result.value) {
                    $.post('/turnos/save', {
                        action: 'cancelarTurnoExterno',
                        idTurno: id
                    }, function(response) {

                        console.log(response);

                        if (response.status == 'OK') {
                            $("#turno" + id).remove();
                        } else {
                            Swal.fire('Lo sentimos!', response, 'error');
                        }
                    })
                }
            })
        }
        $('#campoValidacion').tooltip('disable');

        $("#campoValidacion").on('blur', function() {

            if ($(this).hasClass('is-valid')) {
                //Verifico si existe

                $("#<?= $campo['hidden'] ?>").val($(this).val());

                $('#campoValidacion').tooltip('enable');
                $('#campoValidacion').tooltip('show');

                $.post('/pacientes/save', {
                    action: 'checkPax',
                    value: $(this).val(),
                    codArea: $("#codArea").val()
                }, function(response) {

                    $('#campoValidacion').tooltip('hide');
                    $('#campoValidacion').tooltip('disable');

                    if (response.status == 'OK') {
                        $(".nombreCliente").html(response.nombre);
                        $("#idPaciente").val(response.idPaciente);
                        $("#mail").val(response.mail);
                        $(".bienvenido").removeClass('d-none');
                        $(".nuevoPaciente").addClass('d-none');

                        /* if(response.turnos<1){
                            $("#cancelarTurnosBtn").remove();
                        } */
                        
                    } else if (response.status == 'vacio') {
                        Swal.fire('Lo sentimos!', 'El campo <?= $campo['label'] ?> no puede estar vacío.', 'error');

                        $(".nombreCliente").html('');
                        $("#idPaciente").val('');
                        $(".bienvenido").addClass('d-none');
                        $(".nuevoPaciente").removeClass('d-none');
                    } else {
                        $(".nombreCliente").html('');
                        $("#idPaciente").val('');
                        $(".bienvenido").addClass('d-none');
                        $(".nuevoPaciente").removeClass('d-none');
                    }

                    $(".campoMail").removeClass('d-none');

                    inicializar();

                })
            }
        })

        if ($("#categoria").length) {
            $("#categoria").on('change', function(e) {

                e.preventDefault();

                traerTratamientos();
            })
        } else {
            traerTratamientos();
        }


        function traerTratamientos() {
            $("#tratamiento").prop('disabled', true);
            $("#tratamiento").html('<option value="">Seleccione especialidad...</option>');
            $("#profesional").prop('disabled', true);
            $("#fecha").val('');
            $("#fecha").prop('disabled', true);
            $("#horas").html('<option value="">Seleccione fecha...</option>');
            $("#horas").prop('disabled', true);

            $.post('/obrasSociales/save', {
                action: 'getTratamientos',
                categoria: $("#categoria option:selected").val()
            }, function(response) {

                if (response.status == 'OK') {

                    $("#tratamiento").prop('disabled', false);

                    if (response.tratamientos.length > 1) {
                        $("#tratamiento").html('<option value="">Seleccione...</option>');
                    } else {
                        $("#tratamiento").html('');
                    }

                    var tratamientos = response.tratamientos;
                    var traOrd = [];

                    $.each(tratamientos, function(key, value) {
                        traOrd.push({
                            v: value,
                            k: key
                        });
                    });

                    traOrd.sort(function(a, b) {
                        if (a.v > b.v) {
                            return 1
                        }
                        if (a.v < b.v) {
                            return -1
                        }
                        return 0;
                    });


                    $.each(traOrd, function(index, value) {
                        $("#tratamiento").append('<option value="' + value.k + '">' + value.v + '</option>');
                    })

                    $(".selectpickerSearch").selectpicker('refresh');

                    $("#idTratamiento").trigger('change');

                    $("#tratamiento").trigger('change');

                    $("#tratamiento").prop('disabled', false);

                    $("#profesional").prop('disabled', true);
                    $("#fecha").val('');
                    $("#fecha").prop('disabled', true);
                    $("#horas").html('<option value="">Seleccione fecha...</option>');
                    $("#horas").prop('disabled', true);

                } else {
                    Swal.fire('Lo sentimos!', 'No hay <?= $general['nombreObrasSociales'] ?> disponibles para tomar turnos.', 'error');
                }

            })
        }

        $("#tratamiento").on('change', function(e) {

            e.preventDefault();

            $("#idTratamiento").val($("#tratamiento option:selected").val());

            if ($("#idTratamiento").val()) {

                //Voy a ver cuánto sale el tratamiento, a ver si lo tengo que cobrar o no
                $.post('/obrasSociales/save', {
                    action: 'getPrice',
                    idTratamiento: $("#idTratamiento").val()
                }, function(response) {

                    $('#pago option[data-type="tratamiento"]').remove();

                    if (response) {
                        $("#pago").append('<option data-type="tratamiento" data-subtext="$' + response + '" value="turno">Turno</option>');
                        $(".selectpicker").selectpicker('refresh');
                    }
                    $(".selectpicker").selectpicker('refresh');
                })


                $("#profesional").prop('disabled', true);
                $("#fecha").val('');
                $("#fecha").prop('disabled', true);
                $("#horas").html('<option value="">Seleccione fecha...</option>');
                $("#horas").prop('disabled', true);

                $.post('/profesionales/save', {
                    action: 'getProfesionales',
                    tratamiento: $("#idTratamiento").val()
                }, function(response) {
                    if (response.status == 'OK') {
                        $("#profesional").prop('disabled', false);

                        if (response.profesionales.length > 1) {
                            $("#profesional").html('<option value="">Seleccione...</option>');
                        } else {
                            $("#profesional").html('');
                        }

                        let opciones = '';
                        let idsProfesionales = '';

                        $.each(response.profesionales, function(index, value) {
                            opciones += '<option value="' + index + '">' + value + '</option>';
                            idsProfesionales += index + ',';
                        })

                        <?
                        if ($general['profesionalIndistinto']) {
                        ?>
                            $("#profesional").html(`<option value="${idsProfesionales}" selected>Indistinto</option>`);
                        <?
                        }
                        ?>

                        $("#profesional").append(opciones);

                        $("#profesional").trigger('change');

                        $(".selectpickerSearch").selectpicker('refresh');

                        $("#fecha").val('');
                        $("#horas").html('<option value="">Seleccione fecha...</option>');
                        $("#horas").prop('disabled', true);
                    } else {
                        Swal.fire('Lo sentimos!', 'No hay <?= $general['nombreProfesionales'] ?> disponibles para tomar este <?= $general['nombreObraSocial'] ?>.', 'error');
                    }
                })

                //Voy a traer el texto post
                $.post('/obrasSociales/save', {
                    action: 'getTextoPost',
                    tratamiento: $("#idTratamiento").val()
                }, function(response) {
                    if (response) {
                        $("#textoPost .col").html(response.textoPost);
                        $("#textoPost").removeClass('d-none');
                    } else {
                        $("#textoPost").addClass('d-none');
                    }
                })


                // Mostrar precio de la sena en base al metodo de pago
                <? if($general["mercadoPago"] && $general["mercadoPago_sena"] && !$general["mercadoPago_servicios_sena_porcentaje"]){ ?>
                if ($('#pago').hasClass('activePago')) {

                    $.post(
                        '/obrasSociales/save', {
                            action: 'getSenaTratamiento',
                            tratamiento: $('#idTratamiento').val()
                        },
                        function(response) {
                            console.log(response)
                            if (response != '') {
                                if ($('#pago .senaTratamiento')[0]) {
                                    $('#pago .senaTratamiento')[0].remove()
                                }
                                $("#pago").append('<option class="senaTratamiento" data-type="sena" data-subtext="' + response + '">Seña</option>');
                                $(".selectpicker").selectpicker('refresh');
                            }
                        }
                    )
                }
                <? } ?>

                // Mostrar precio de la sena en base al metodo de pago
                <? if($general["mercadoPago"] && $general["mercadoPago_sena"] && $general["mercadoPago_servicios_sena_porcentaje"]){ ?>
                    $.post(
                        '/obrasSociales/save', {
                            action: 'getPrecioSenaPorcentajeTratamiento',
                            tratamiento: $('#idTratamiento').val()
                        },
                        function(response) {
                            if (response) {
                                $("#inputSenaPorPorcentaje").val("$"+response)
                            }
                        }
                    )
                <? } ?>
            }
        })




        $("#profesional").on('change', function() {
            $("#idProfesional").val($("#profesional option:selected").val());

            $("#fecha").val('');
            $("#horas").html('<option value="">Seleccione fecha...</option>');
            $("#horas").prop('disabled', true);

            if ($(this).val()) {
                $("#fecha").prop('disabled', false);
            } else {
                $("#fecha").prop('disabled', true);
            }

            //Acá veo las fechas
            $.post('/turnos/save', {
                action: 'getDates',
                tratamiento: $("#idTratamiento").val(),
                profesional: $("#profesional option:selected").val(),
                mesCalendario: '<?= date('Y-m') ?>'
            }, function(response) {
                if (response.fechas) {
                    getDates(response.fechas, response.fechaInicio);

                    // Para que no quere pre seleccionado por defecto ninguno
                    $("#fecha").val("")
                    $("#fecha").removeClass("is-valid")
                }

            })
        })




        function getDates(fechas, primerDia = '') {
            let bloqueadas = Object.keys(fechas).map(function(key) {
                return moment(fechas[key]).format('DD/MM/YYYY');
            });

            $('#fecha').datepicker('setDatesDisabled', bloqueadas);

            if (primerDia) {
                $('#fecha').datepicker('setDate', moment(primerDia).format('DD/MM/YYYY'));
            }
        }



        function inicializar() {
            $('.required:visible').on('blur keyup change', function(e) {
                if ($(this).val().length > 2) {
                    if ($(this).prop('type') == 'email') {
                        if (isEmail($(this).val())) {
                            $(this).removeClass('is-invalid');
                            $(this).addClass('is-valid');
                        } else {
                            $(this).removeClass('is-valid');
                            $(this).addClass('is-invalid');
                        }
                    } else {
                        if ($(this).attr('minlength')) {
                            if ($(this).val().length >= $(this).attr('minlength')) {
                                $(this).removeClass('is-invalid');
                                $(this).addClass('is-valid');
                            } else {
                                $(this).removeClass('is-valid');
                                $(this).addClass('is-invalid');
                            }
                        } else {
                            $(this).removeClass('is-invalid');
                            $(this).addClass('is-valid');
                        }
                    }
                } else {
                    if (($(this).prop('id') == 'tratamiento') || ($(this).prop('id') == 'profesional') || ($(this).prop('id') == 'codArea')) {
                        if ($("#tratamiento option:selected").val() == "") {
                            $("#tratamiento").removeClass('is-valid');
                            $("#tratamiento").addClass('is-invalid');
                        } else {
                            $("#tratamiento").removeClass('is-invalid');
                            $("#tratamiento").addClass('is-valid');
                        }
                        if ($("#profesional option:selected").val() != '') {
                            $("#profesional").removeClass('is-invalid');
                            $("#profesional").addClass('is-valid');
                        } else {
                            $("#profesional").removeClass('is-valid');
                            $("#profesional").addClass('is-invalid');
                        }
                        if ($("#codArea").val().length < 2) {
                            $("#codArea").removeClass('is-valid');
                            $("#codArea").addClass('is-invalid');
                        } else {
                            $("#codArea").removeClass('is-invalid');
                            $("#codArea").addClass('is-valid');
                        }
                    } else {
                        $(this).addClass('is-invalid');
                        $(this).removeClass('is-valid');
                    }
                }
            })
        }

        inicializar();

        function isEmail(email) {
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return regex.test(email);
        }


        function formatDate(date) {
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            return [year, month, day].join('-');
        }


        $('#fecha').datepicker({
            format: "dd/mm/yyyy",
            autoclose: true,
            todayHighlight: true,
            startDate: '<?= date("d/m/Y", strtotime('+' . $general['horasAnticipacion'] . ' hours')) ?>',
            <?= ($general['diasOcultos']) ? "daysOfWeekDisabled: '" . $general['diasOcultos'] . "'," : "" ?>
            weekStart: 1
        }).on('changeDate', function(e) {
            let mesCalendario = moment(e.date).format('YYYY-MM');
            $.post(
                '/turnos/save', {
                    action: 'getDates',
                    tratamiento: $("#idTratamiento").val(),
                    profesional: $("#profesional option:selected").val(),
                    mesCalendario: mesCalendario
                },
                function(response) {
                    if (response.fechas) {
                        getDates(response.fechas);
                    }
                }
            )

            $("#horas").html("Seleccione fecha...");

            $.post('/turnos/save', {
                action: 'getHours',
                fecha: formatDate(e.date),
                profesional: $("#idProfesional").val(),
                tratamiento: $("#idTratamiento").val(),
                ref: 'login',
                timezone: timezone
            }, function(response) {
                
                if (response.status == 'OK') {
                    searchPromociones()

                    $(".loginBtn").prop('disabled', false);
                    $("#horas").prop('disabled', false);
                    $("#horas").html('');
                    $.each(response.posiblesHoras, function(index, value) {
                        $("#horas").append('<option value="' + value.desde + '" data-idprofesional="' + value.idProfesional + '">' + value.desde + '</option>');
                    })
                    $("#horas").addClass('is-valid');

                    $("#horas").on('change', function() {
                        if ($("#horas option:selected").data('idprofesional') != '') {
                            $("#idProfesional").val($("#horas option:selected").data('idprofesional'));
                        }
                    })

                    $("#horas").trigger('change');

                    maximosCupos = response.disponibles
                } else {
                    $(".loginBtn").prop('disabled', true);
                    $("#horas").prop('disabled', true);
                    $("#horas").html('<option value="">No hay horarios disponibles</option>');
                    maximosCupos = 0
                }

                $("#entradas__cantEntradas").html("("+maximosCupos + " " + (maximosCupos == 1 ? "disponible" : "disponibles") + ")")

                /* // Si ya no hay cupos bloqueo el botón de agregar entrada
                $("#primerParticipante").prop("disabled", entradas__getCantidad() >= maximosCupos) */
            })
        }).on('show', function(e) {
            var elem = $(e.target);
            var dropDownAddClass = 'fechasPublicas';
            var datepickerDropDown = $('.datepicker');
            datepickerDropDown.addClass(dropDownAddClass);
        });


        $('#fecha').on('changeMonth', function(e) {
            let mesCalendario = moment(e.date).format('YYYY-MM-DD');

            $.post(
                '/turnos/save', {
                    action: 'getDates',
                    tratamiento: $("#idTratamiento").val(),
                    profesional: $("#profesional option:selected").val(),
                    mesCalendario: mesCalendario
                },
                function(response) {
                    if (response.fechas) {
                        getDates(response.fechas, response.fechaInicio);
                    }
                }
            )
        });


        /* ----------------------------- */
        /*          PROMOCIONES          */
        /* ----------------------------- */
        function searchPromociones(){
            
            $.post(
                "/promociones/save",
                {
                    action: "searchPromociones", 
                    fecha: $("#fecha").val()
                },
                function({status, promociones}){
                    if(status != "OK") {
                        $("#contentPromociones").html("")
                        allPromociones = []
                        return
                    }

                    let colPromociones = "col-12"
                    if(promociones.length > 1) colPromociones = "col-md-6"
                    if(promociones.length > 2) colPromociones = "col-md-4"

                    let htmlPromociones = `
                    <div class="row">
                        <div class="col-12"><hr></div>
                            <div class="col-12 text-center mb-2"><h4><i class="fa-solid fa-gift mr-1"></i>¡Promociones del día!</h4></div>
                    `;
                        for (const promocion of promociones) {
                            const entradasACobrar = promocion.cantidadEntradas - promocion.entradasGratis
                            htmlPromociones += `
                                <div class="${colPromociones} text-center">
                                    <h4 class="mb-0">${promocion.cantidadEntradas} x ${entradasACobrar}</h4>
                                    <p class="mb-0"><i class="fa-solid fa-ticket mr-1"></i>${promocion.nombre}</p>
                                    <!-- <p class="mb-0"><i class="fa-regular fa-clock mr-1"></i>${promocion.horario.desde}hs - ${promocion.horario.hasta}hs </p> -->
                                </div> 
                            `
                        }
                    htmlPromociones += `
                        <div class="col-12"><hr></div>
                    </div> 
                    `;

                    $("#contentPromociones").html(htmlPromociones)
                    allPromociones = promociones


                    // Vuelvo a calcular el precio por si la nueva fecha no tiene promocion
                    participante__calcularPrecio()
                }
            )
        }

        $('.loginBtn').click(function(e) {
            e.preventDefault();
            /* const firmaEncodeada = firma.toDataURL("image/png")

            if(firma.isEmpty()){
                Swal.fire("Sin firma!", "La firma es obligatoria para poder continuar.", "warning")
                return
            }

            if(!$("#checkTerminosYCondiciones").prop('checked')){
                Swal.fire("Acepte los Terminos y Condiciones!", "Debe aceptar los terminos y condiciones para sacar el <?= $general['nombreTurno'] ?>", "warning")
                return
            } */

            if(entradas__getCantidad() > maximosCupos){
                Swal.fire("Lo sentimos!", "Has superado el límite ("+maximosCupos+") de las <?=$general['nombreBoletos']?> disponibles", "warning")
                return
            }

            var contenidoBoton = $(".loginBtn").html();

            $(".datosCliente .required:visible").trigger('change');

            var algunoMal = 0;
            var algunoBien = 0;
            $.each($('.required:visible'), function(key, element) {
                if ($(element).hasClass('is-invalid')) {
                    algunoMal++;
                }
                if ($(element).hasClass('is-valid')) {
                    algunoBien++;
                }
            })

            
            var precioTotalParticipante = 0
            
            let participantes = [];
            $(".filaParticipante").each(function(index, obj){
                participantes.push($(this).find(".participante__selectParticipante option:selected").val())
            })


            if ((algunoMal < 1) && (algunoBien == $('.required:visible').length)) {

                $(".loginBtn").html('<i class="fa fa-spinner fa-spin fa-fw"></i> Cargando...');
                $(".loginBtn").attr('disabled', 'disabled');

                $.post(
                    '/turnos/save',
                    {
                        action: 'saveExternal',
                        codArea: $("#codArea").val(),
                        telefono: $("#telefono").val(),
                        mail: $("#mail").val(),
                        dni: $("#campoValidacion").val(),
                        idPaciente: $("#idPaciente").val(),
                        nombre: $("#nombre").val(),
                        apellido: $("#apellido").val(),
                        fecha: $("#fecha").val(),
                        horas: $("#horas").val(),
                        profesional: $("#profesional").val(),
                        idProfesional: $("#profesional").val(),
                        tratamiento: $("#categoria").val(),
                        idTratamiento: $("#idTratamiento").val(),
                        participantes,
                        idMedioDePago: document.getElementById("tipoDeMedioDePago").value,
                        totalTurno: parseInt($("#spanTotalParticipantes").html())
                    }, function(response) {
                    
                    console.log(response);
                    
                    if (response.status == 'OK') {

                        let title = "Reservado!"
                        var texto = response.nombre + ', gracias por elegirnos. Tu turno ha sido reservado correctamente. Te esperamos el ' + response.fecha + '.';

                        for (const idGrupo of response.codigos) {
                            window.open("/impresora/index.php?idGrupo="+idGrupo)
                        }

                        Swal.fire({
                            title,
                            html: texto,
                            type: 'success',
                            onClose: () => {
                                location.reload()
                            }
                        });

                    } else {
                        if (response.status == 'datosIncompletos') {
                            Swal.fire('Lo sentimos!', 'Verifica los campos marcados en rojo para continuar.', 'error');
                        } else {
                            if (response.status == 'duplicado') {
                                Swal.fire('Lo sentimos!', response.nombre + ', ya tenés cargado un turno el ' + response.fecha + '. No podés cargar otro durante el transcurso de otro.', 'error');
                            } else {
                                if (response.status == 'tomado') {
                                    Swal.fire('Lo sentimos!', 'El turno solicitado ha sido tomado por otro usuario. Intentalo nuevamente con otros parametros.', 'error');
                                } else if(response.status == "tratamientoBloqueado"){
                                    Swal.fire('Lo sentimos!', response.message, 'error');
                                } else if (response.status == "SinEntradas"){
                                    Swal.fire(response.title,response.message,response.type)
                                } else {
                                    Swal.fire('Lo sentimos!', response, 'error');
                                }
                            }
                        }
                    }

                    $(".loginBtn").html(contenidoBoton);
                    $(".loginBtn").attr('disabled', false);

                })
            } else {
                Swal.fire('Lo sentimos!', 'Verifica los campos marcados en rojo para continuar.', 'error');
            }

        })

        /* --------------------------------- */
        /*                                   */
        /*                FIRMA              */
        /*                                   */
        /* --------------------------------- */
        var firma

        /* document.addEventListener("DOMContentLoaded", () => {
            firma__init()
        }) */

        $("#btnBorrar").click(function(e){
            e.preventDefault()
            firma__borrar()
        })

        function firma__init(){
            elementoCanvas = document.querySelector("canvas")
            firma = new SignaturePad(elementoCanvas, {
                backgroundColor: "rgb(255,255,255)",
                penColor: "rgb(0, 0, 0)"
            });
        }

        function firma__borrar(){
            firma.clear()
        }

    </script>
</body>

</html>