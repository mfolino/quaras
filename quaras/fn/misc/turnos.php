<?
function getHorarios($fecha,$profesional,$tratamiento,$suma=false){
    global $row, $tot, $res, $general;

    //Formateo la fecha
    $fecha=date("Y-m-d",strtotime($fecha));

    //Traigo la duración del tratamiento seleccionado
    $duracion=getDuracion($tratamiento);

    //Me fijo si voy a mostrar los horarios en base a la duración del turno o a un bloque prefijado
    if($general['duracionBloque']=='tratamiento'){
        $bloqueTurno=$duracion;
    }
    if($general['duracionBloque']=='bloque'){
        $bloqueTurno=$general['minBloqueTurnos'];
    }

    //Tomo los profesionales en base a la selección ya que pueden ser múltiples si es indistinto
    $profesional = explode(';', $profesional);
    $cantidadProfesionales = count($profesional);


    $horasProhibidas=array();

    
    //Me va a devolver todos los turnos ocupados para el día y el profesional seleccionado
    getTurnosTomados($profesional, $fecha);


    //Voy a verificar si corresponde aplicar las horas de anticipación
    getHorasAnticipacion($fecha);

    //Voy a verificar si hay bloqueos para la fecha seleccionada
    getBloqueosFiltro($profesional, $fecha);



    printVar($horasProhibidas);

    //Voy a buscar los horarios que atienden los profesionales seleccionados en la fecha en cuestión
    getHorariosProfesionales();


    //Acá voy a ciclar por todos los horarios que voy a ofrecer y en base a eso voy a ver si están ocupados o no. 
    
    //Si están ocupados también voy a verificar cuántos simultáneos para el tratamiento seleccionado puedo atender


    if($suma){
        //Solo devuelvo si hay turnos o no
    }else{
        //Devuelvo las opciones disponibles
    }
}





function getTurnosTomados($profesional, $fecha){
    global $tot, $row, $res, $horasProhibidas;

    $profesionales = implode(',', $profesional);
    
    db_query(0,
    "SELECT DATE_FORMAT(t.fechaInicio,'%H:%i') as fechaInicio, DATE_FORMAT(t.fechaFin,'%H:%i') as fechaFin, o.idProfesional
    FROM turnos t, ordenes o where t.estado<>3 and date(t.fechaInicio)='".$fecha."' and t.idOrden=o.idOrden and o.idProfesional IN (".$profesionales.")");

    for($i=0;$i<$tot;$i++){
        $nres=$res->data_seek($i);
        $row=$res->fetch_assoc();
        $horasProhibidas[]=$row;
    }

}

function getHorasAnticipacion($fecha){
    global $row, $general, $horasProhibidas;

    //Si la fecha más las horas de anticipación es hoy, entonces no se puede reservar en esas horas.
    if($fecha==date("Y-m-d", strtotime('+'.$general['horasAnticipacion'].' hours'))){
        $horasProhibidas[]=array(
            'fechaInicio'=>date("Y-m-d",strtotime($fecha)).' 00:00:00',
            'fechaFin'=>date("Y-m-d H:i:s",strtotime('+'.($general['horasAnticipacion']).' hours')),
            //Va 0 porque aplica para todos los profesionales
            'idProfesional'=>0
        );
    }
}

function getHorariosProfesionales(){

}
?>