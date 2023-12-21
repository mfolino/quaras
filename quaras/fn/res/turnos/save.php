<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

GLOBAL $general;

foreach($_REQUEST as $key => $value){
    $post[$key] = SecurityDatabaseController::cleanVar($value);
}

//Guardado de turno desde el admin
if($post['action']=='save'){
    Turno::save();
}

//Updatea el turno cuando se cambia de fecha
if($post['action']=='updateTurno'){
    Turno::update($post['id'], $post['fecha']);
}

//Elimina turno. Cambiar por update columna eliminado=1
if($post['action']=='deleteTurno'){
    if($post['id']<>'') Turno::delete($_POST['id']);
}

//Chequea profesionales disponibles en fecha y hora solicitados. SOLO PARA ADMIN
if($post['action']=='getSobreturno'){
    Turno::getsobreTurno();
}

//Chequea si se puede un turno en ese horario. SOLO PARA ADMIN
if($post['action']=='estaAbierto'){
    Turno::estadoAbierto();
}

//Chequea si el turno arrastrado en el calendario se puede mover. SOLO PARA ADMIN
if($post['action']=='sePuedeMover'){
    Turno::sePuedeMover();
}

//NO se. Veamos en el front si lo necesita
if($post['action']=='verificarFecha'){
    /*Comento a pedido de Jacqui 06-07-2018
	
    if($_SESSION['usuario']['limites']=='limitado'){
        db_query(0,"select fechahora from turnos where idTurno='".$post['idTurno']."' and date(fechahora)>'".date("Y-m-d")."'");
        if($tot>0){
            die("OK");
        }else{
            die("NONO");
        }
    }else{*/
        die("OK");
    // }
}

//Reasiga a otro profesional en la orden cuando se cambia desde la info del turno.
if($post['action']=='cambiarProfesional'){
    Turno::cambiarProfesionalEnLaOrdenConIdTurno($_POST['idTurno'], $_POST['idProfesional']);
}

//Cambia el estado de turno a confirmado o ausente y cancela turnos posteriores si tuvo max de X ausentes
if($post['action']=='estadoTurno'){
    Turno::estadoTurno($_POST['idTurno'], $_POST['estado'], $_POST['confirmo']);
}

//Cambia el horario del turno. MAL! SAUL, IGNORAR
if($post['action']=='cambiarHorario'){
    // db_update("update turnos set fechaInicio='".$_POST['fecha'].' '.$_POST['horas'].':'.$_POST['minutos'].':00'."' where idTurno='".$post['idTurno']."'");
	
    // db_log($_SESSION['usuario']['nombre'],'cambiarHorarioTurno',$post['idTurno']);
	
    die('Action cambiar Horario');
}

//Chequea si hay updates de turnos en el log y manda a actualizar al front
if($post['action']=='checkUpdate'){
    Turno::checkUpdate();
}

// Devuelve info del paciente
if($post['action']=='getInfoPaciente'){
    Turno::getInfoPaciente($_POST['idPaciente'], $_POST['idOrden']);
}

//Guarda multiples turnos
if($post['action']=='saveMultiples'){
    $fechas=json_decode($_POST['fechas']);
    Turno::saveMultiples($fechas, $_POST['idOrden'], $_POST['idPaciente']);
}

//Verifico si hay lugar en el turno
if($post['action']=='checkLugar'){

    if($_POST['fechaInicio'] < date("Y-m-d H:i:s", strtotime("+ {$general['horasAnticipacion']} hours"))){
        $response['status']='NO';
        $response['text']="No se pueden sacar {$general['nombreTurnos']} con menos de {$general['horasAnticipacion']}hs. de anticipación.";
    }else if(Turno::checkLugar($_POST['fechaInicio'], $_POST['idProfesional'], $_POST['idTratamiento'])){
        $response['status']='OK';
    }else{
        $response['status']='NO';
        $response['text']='Ya no queda lugar en este turno.';
    }


    HTTPController::responseInJSON($response);
}


//Guarda el turno desde publico
if($post['action']=='saveExternal'){
    Turno::saveExternal();
}


//Cancela turno desde afuera. Mergear con la otra y variables de envío de mail de cancelación
if($post['action']=='cancelarTurnoExterno'){
    Turno::cancelarTurnoExterno($_POST['idTurno']);
}

//Cancela un turno y manda mail de cancelación. Por ahora solo admin
if($post['action']=='cancelarTurno'){
    Turno::cancelarTurno($_POST['idTurno'], $_POST['comentarios'], $_POST['devolverPago'], $_POST['mandarMail']);
}

//Guardar la actividad en la sesión
if($post['action']=='guardarActividad'){
    $_SESSION['idCategoria']=$_POST['categoria'];
}


































/* 
    -----------------------------
    -----------------------------
    ---------- NUEVOS ----------
    -----------------------------
    -----------------------------

*/



if($post['action']=='getDates'){
    //Voy a ciclar por el mes para ver que días hay disponibles

    //Ahora a mano solo para hacer las pruebas
    $profesional=rtrim($_POST['profesional'],',');

    $response['status']='OK';
    $fechas=Turno::getMes($_POST['mesCalendario'],$profesional,$post['tratamiento']);
    $response['fechas']=$fechas['opciones'];
    $response['fechaInicio']=$fechas['fechaInicio'];

    HTTPController::responseInJSON($response);
}


if($post['action']=='getHours'){

    //Voy a buscar el tratamiento, si no seleccionó ninguno es porque es el unico que atiende. En caso de que venga uno pero no tenga profesional es porque tengo que seleccionar la duración más larga.
    db_query(0,"select duracion from tratamientos where idTratamiento='".$post['tratamiento']."' limit 1");
    $duracion=$row['duracion'];



    if($general['duracionBloque']=='tratamiento'){
        $bloqueTurno=$duracion;
    }
    if($general['duracionBloque']=='bloque'){
        $bloqueTurno=$general['minBloqueTurnos'];
    }

    $profesional=rtrim($_POST['profesional'],',');

    if($general["profesionalIndistinto"]){
        $profesionales = explode(",", $profesional);
    
        $todasLasOpciones = array();
        
        foreach ($profesionales as $profesional) {
            $opciones=Turno::getHours($_POST['fecha'], $profesional, $bloqueTurno, $_POST['timezone'], 'getHours', $post["tratamiento"]);

            $todasLasOpciones = array_merge($todasLasOpciones, $opciones);
            usort($todasLasOpciones, 'date_compare');
            $todasLasOpciones = unique_multidim_array($todasLasOpciones,'desde');
        }
        $opciones = $todasLasOpciones;

    }else{
        $opciones=Turno::getHours($_POST['fecha'], $profesional, $bloqueTurno, $_POST['timezone'], 'getHours', $post["tratamiento"]);
    }


    $response['posiblesHoras']=$opciones;

    if(@sizeof($opciones)){
        $response['status']='OK';
    }else{
        $response['status']='NO';
    }

    HTTPController::responseInJSON($response);
}
/* Funciones getHours */
function unique_multidim_array($array, $key) {
    $temp_array = array();
    $i = 0;
    $key_array = array();
   
    foreach($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
        }
        $i++;
    }
    return $temp_array;
}
function date_compare($a, $b){
    $t1 = strtotime($a['hasta']);
    $t2 = strtotime($b['desde']);
    return $t1 - $t2;
}





if($post['action']=='getRecordatorios'){
    /* HTTPController::responseInJSON(NotificationWhatsapp::getRecordatorios()); // Version vieja */
    HTTPController::responseInJSON(NotificationWhatsapp::getRecordatoriosPanel()); // Toma en cuenta la variable diasOcultos

}

if($post['action']=='getWappLink'){
    HTTPController::responseInJSON(NotificationWhatsapp::getWappLink($_POST["id"]));
}

if($post['action']=='enviarSMS'){
    // Return OK | ERROR
    $response = NotificationSMS::enviarRecordatorio($_POST["id"]);
    HTTPController::responseInJSON($response);
    /* HTTPController::responseInJSON(NotificationSMS::enviarRecordatorio($_POST["id"])); */
}

//Bloqueos
if($post['action']=='getBloqueos'){

    HTTPController::responseInJSON(getBloqueos());
}
if($post['action']=='saveBloqueo'){
    $vars=$post;

    $fechas=explode(' - ',$_POST['fecha']);

    $vars['fechaDesde']=date("Y-m-d H:i:s",strtotime(str_replace('/','-',$fechas[0])));
    $vars['fechaHasta']=date("Y-m-d H:i:s",strtotime(str_replace('/','-',$fechas[1])));

    $vars['tabla']='bloqueos';
    $vars['idLabel']='idBloqueo';

    $excluir=array(
        'fecha'
    );

    $vars['estado']='A';
	
    if(@$vars['id']){
        $vars['accion']='actualizarBloqueo';
    }else{
        $vars['accion']='agregarBloqueo';
    }
    $newid=db_edit($vars,$excluir);
    if($newid){
        $response['status']='OK';
    }else{
        $response['status']='error';
    }

    HTTPController::responseInJSON($response);
}

if($post['action']=='deleteBloqueo'){
    if($_POST['idBloqueo']){
	
        db_delete("update bloqueos set estado='B' where idBloqueo='".$post['idBloqueo']."'");
        db_log($_SESSION['usuario']['nombre'],'eliminarBloqueo',$post['idBloqueo']);
        $response['status']="OK";

    }else{
        $response['status']='vacio';
    }
    HTTPController::responseInJSON($response);
}






//Feriados
if($post['action']=='getFeriados'){
    HTTPController::responseInJSON(getFeriados());
}
if($post['action']=='saveFeriado'){
    $vars=$post;

    $vars['tabla']='feriados';
    $vars['idLabel']='idFeriado';
    $vars['fecha']=date("Y-m-d",strtotime(str_replace('/','-',$vars['fecha'])));
	
    if(@$vars['id']){
        $vars['accion']='actualizarFeriado';
    }else{
        $vars['accion']='agregarFeriado';
    }
    $newid=db_edit($vars);

    if($newid){
        $response['status']='OK';
    }else{
        $response['status']='error';
    }

    HttpController::responseInJSON($response);
}

if($post['action']=='deleteFeriado'){
    if($_POST['idFeriado']){
	
        db_delete("update feriados set estado='B' where idFeriado='".$_POST['idFeriado']."'");
        db_log($_SESSION['usuario']['nombre'],'eliminarFeriado',$_POST['idFeriado']);
        $response['status']="OK";

    }else{
        $response['status']='vacio';
    }
    HTTPController::responseInJSON($response);
}

if($post['action']=='getTurnos'){
    if(isset($_SESSION['idProfesional']) && $_SESSION['idProfesional']){
        $profesional=$_SESSION['idProfesional'];
    }else if(isset($_REQUEST['profesionalSeleccionado']) && $_REQUEST['profesionalSeleccionado']){
        $profesional=$_REQUEST['profesionalSeleccionado'];
    }else{
        $profesional='';
    }

    $eventosFinal=Turno::getEventosCalendario(date('Y-m-d',strtotime($post['start'])),date('Y-m-d',strtotime($post['end'])),$profesional);

    HTTPController::responseInJSON($eventosFinal);
    die();
}

if($post['action']=='getResumenMensual'){

    if(@$_SESSION['idProfesional']){
        $profesional=$_SESSION['idProfesional'];
    }else if(@$_REQUEST['profesionalSeleccionado']){
        $profesional=$_REQUEST['profesionalSeleccionado'];
    }else{
        $profesional='';
    }

    $eventosFinal=Turno::getResumenMensual(date('Y-m-d',strtotime($_GET['start'])),date('Y-m-d',strtotime($_GET['end'])),$profesional);

    HTTPController::responseInJSON($eventosFinal);
}

if($post['action']=='getTurnosPublic'){

    $_SESSION['idCategoria']=$_REQUEST['categoria'];

    if(@$_REQUEST['categoria'] && $_SESSION["idCategoria"] != "undefined"){
        $categoria=$_REQUEST['categoria'];
    }else{
        $categoria='';
    }

    $eventosFinal=Turno::getEventosCalendarioPublic(date('Y-m-d',strtotime($_GET['start'])),date('Y-m-d',strtotime($_GET['end'])),$categoria);

    /* Util::printVar($eventosFinal, "186.138.206.135", false); */
    
    HTTPController::responseInJSON($eventosFinal);
}

if($post['action']=='guardarComentarios'){

    if (isset($general["campoObservaciones"])){
        if(Turno::saveObservaciones($_POST["idTurno"], $_POST["comentarios"])){
            $response['status']="OK";
        }else{
            $response['status']="error";
        }
    }

    HTTPController::responseInJSON($response);
}

if($post['action']=='getTurnosUsuario'){

    $misTurnos=Turno::getTurnosPaciente($_POST["idPaciente"]);

    if(@sizeof($misTurnos)>0){
        $response['status']="OK";
        $response['turnos']=$misTurnos;
    }else{
        $response['status']="error";
    }

    HTTPController::responseInJSON($response);
}



?>