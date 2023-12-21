<?

use function PHPSTORM_META\map;

require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');


if($_POST["action"] == "save"){
    $fechaDesdeHasta = explode(" - ", str_replace("/","-", $_POST["fecha"]));
    $fechaDesde = date("Y-m-d", strtotime($fechaDesdeHasta[0]));
    $fechaHasta = date("Y-m-d", strtotime($fechaDesdeHasta[1]));
    $horarioInicio = $_POST["horarioInicio"].":00";
    $horarioFin = $_POST["horarioFin"].":00";

    // Valido las fechas
    if($fechaDesde < date("Y-m-d")){
        HTTPController::responseInJSON(array(
            "title" => "Fecha incorrecta!",
            "message" => "La fecha de inicio no puede ser menor a la actual",
            "type" => "warning",
            "status" => "FECHA_INICIO_INCORRECTA"
        ));
        die();
    }
    
    // Valido que no haya otro feriado personalizado para el mismo profesional
    $validateFecha = db_getOne("SELECT idFeriadoPersonalizado, descripcion FROM feriadosPersonalizados WHERE eliminado <> 1 AND idProfesional IN (0, {$_POST['idProfesional']}) AND (
        ('{$fechaDesde}' >= fechaDesde AND '{$fechaDesde}' <= fechaHasta) OR 
        ('{$fechaHasta}' >= fechaDesde AND '{$fechaHasta}' <= fechaHasta) OR 
        ('{$fechaDesde}' <= fechaDesde AND '{$fechaHasta}' >= fechaHasta)
    )");
    if($validateFecha){
        HTTPController::responseInJSON(array(
            "title" => "Vigencia no válida!",
            "message" => "No puede agregar este feriado ya que el mismo coincide con el feriado '{$validateFecha->descripcion}', revise las fechas y vuelva a intentarlo.",
            "type" => "warning",
            "status" => "ERROR"
        ));
        die();
    }

    db_update("INSERT INTO feriadosPersonalizados (descripcion, idProfesional, fechaDesde, fechaHasta, horarioInicio, horarioFin) VALUES ('{$_POST['descripcion']}', {$_POST['idProfesional']}, '{$fechaDesde}', '{$fechaHasta}', '{$horarioInicio}', '{$horarioFin}')");

    HTTPController::responseInJSON(array(
        "title" => "Feriado agregado!",
        "message" => "El feriado fue agregado correctamente.",
        "type" => "success",
        "status" => "OK"
    ));
    die();
}

if($_POST["action"] == "update"){
    $fechaDesdeHasta = explode(" - ", str_replace("/","-", $_POST["fecha"]));
    $fechaDesde = date("Y-m-d", strtotime($fechaDesdeHasta[0]));
    $fechaHasta = date("Y-m-d", strtotime($fechaDesdeHasta[1]));
    $horarioInicio = $_POST["horarioInicio"].":00";
    $horarioFin = $_POST["horarioFin"].":00";

    // Valido las fechas
    if($fechaDesde < date("Y-m-d")){
        HTTPController::responseInJSON(array(
            "title" => "Fecha incorrecta!",
            "message" => "La fecha de inicio no puede ser menor a la actual",
            "type" => "warning",
            "status" => "FECHA_INICIO_INCORRECTA"
        ));
        die();
    }

    // Valido que no haya otro feriado personalizado para el mismo profesional
    $validateFecha = db_getOne("SELECT idFeriadoPersonalizado, descripcion FROM feriadosPersonalizados WHERE eliminado <> 1 AND idProfesional IN (0, {$_POST['idProfesional']}) AND idFeriadoPersonalizado <> {$_POST['id']} AND (
        ('{$fechaDesde}' >= fechaDesde AND '{$fechaDesde}' <= fechaHasta) OR 
        ('{$fechaHasta}' >= fechaDesde AND '{$fechaHasta}' <= fechaHasta) OR 
        ('{$fechaDesde}' <= fechaDesde AND '{$fechaHasta}' >= fechaHasta)
    )");
    if($validateFecha){
        HTTPController::responseInJSON(array(
            "title" => "Vigencia no válida!",
            "message" => "No puede agregar este feriado ya que el mismo coincide con el feriado '{$validateFecha->descripcion}', revise las fechas y vuelva a intentarlo.",
            "type" => "warning",
            "status" => "ERROR"
        ));
        die();
    }
    
    db_update("UPDATE feriadosPersonalizados SET descripcion = '{$_POST['descripcion']}', idProfesional = {$_POST['idProfesional']}, fechaDesde = '{$fechaDesde}', fechaHasta = '{$fechaHasta}', horarioInicio = '{$horarioInicio}', horarioFin = '{$horarioFin}' WHERE idFeriadoPersonalizado = {$_POST['id']}");

    HTTPController::responseInJSON(array(
        "title" => "Feriado modificado!",
        "message" => "El feriado fue modificado correctamente.",
        "type" => "success",
        "status" => "OK"
    ));
    die();
}

if($_POST["action"] == "delete"){
    db_update("UPDATE feriadosPersonalizados SET eliminado = 1 WHERE idFeriadoPersonalizado = {$_POST['id']}");
    HTTPController::responseInJSON(array(
        "status" => "OK",
        "title" => "Feriado eliminado!",
        "message" => "El feriado seleccionado fue eliminado correctamente.",
        "type"=> "success"
    ));
    die();
}
