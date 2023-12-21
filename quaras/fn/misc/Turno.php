<?

class Turno
{

    public static function overlap($fechaInicio, $fechaFin, $fechaInicio2, $fechaFin2)
    {
        return (($fechaInicio < $fechaFin2) and ($fechaFin > $fechaInicio2));
    }


    public static function inRange($fechaInicio, $fechaFin, $fechaInicio2, $fechaFin2)
    {
        return (($fechaInicio >= $fechaInicio2) and ($fechaFin <= $fechaFin2));
    }


    public static function getBloqueos($fecha, $profesional)
    {

        global $row, $tot, $res;

        db_query(0, "SELECT fechaDesde, fechaHasta, idProfesional FROM bloqueos WHERE date(fechaDesde) <= '$fecha' AND date(fechaHasta) >= '$fecha'  AND (idProfesional  IN (" . $profesional . ") or idProfesional='0') AND estado = 'A'");
        $bloqueos = array();

        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();

            if (date("Y-m-d", strtotime($row['fechaDesde'])) != $fecha) {
                $horaDesde = '00:00';
            } else {
                $horaDesde = date("H:i", strtotime($row['fechaDesde']));
            }

            if (date("Y-m-d", strtotime($row['fechaHasta'])) != $fecha) {
                $horaHasta = '23:59';
            } else {
                $horaHasta = date("H:i", strtotime($row['fechaHasta']));
            }

            $bloqueos[] = array('desde' => $horaDesde, 'hasta' => $horaHasta, 'idProfesional' => $row['idProfesional']);
        }

        return $bloqueos;
    }


    public static function getAnticipacion($fecha)
    {

        global $general;

        $bloqueo = array();

        $filtroAnticipacion = '+' . $general['horasAnticipacion'] . ' hours';
        if($general["minutosAnticipacion"]){
            $filtroAnticipacion = '+' . $general['minutosAnticipacion'] . ' minutes';
        }

        if (date("Y-m-d", strtotime($fecha)) == date("Y-m-d", strtotime($filtroAnticipacion))) {
            $bloqueo[] = array(
                'desde' => '00:00',
                'hasta' => date("H:i", strtotime($filtroAnticipacion)),
                'idProfesional' => 0
            );
        } else if (date("Y-m-d", strtotime($fecha)) < date("Y-m-d", strtotime($filtroAnticipacion))) {
            $bloqueo[] = array(
                'desde' => '00:00',
                'hasta' => '23:59',
                'idProfesional' => 0
            );
        }

        return $bloqueo;
    }


    public static function getTurnos($fecha, $profesional, $idTratamiento = '')
    {
        global $general, $row, $tot, $res;

        $filtroTratamiento = "";
        if($idTratamiento && $general["simultaneosPorServicio"]){
            $filtroTratamiento = " AND o.idTratamiento = {$idTratamiento} ";
        }

        db_query(
            0,
            "SELECT DATE_FORMAT(t.fechaInicio,'%H:%i') as fechaInicio, DATE_FORMAT(t.fechaFin,'%H:%i') as fechaFin, o.idProfesional
            FROM 
                turnos t, 
                ordenes o 
            WHERE 
                t.estado<>3 AND 
                date(t.fechaInicio)='" . date("Y-m-d", strtotime($fecha)) . "' AND 
                t.idOrden=o.idOrden AND 
                o.idProfesional IN (" . $profesional . ") AND
                t.eliminado <> 1
                {$filtroTratamiento}
            "
        );

        $turnos = array();

        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();

            $turnos[] = array(
                'desde' => $row['fechaInicio'],
                'hasta' => $row['fechaFin'],
                'idProfesional' => $row['idProfesional']
            );
        }

        return $turnos;
    }


    public static function getHorariosProfesionales($fecha, $profesional)
    {
        GLOBAL $row, $tot, $res, $general;

        $diaSemana['Monday'] = 'Lunes';
        $diaSemana['Tuesday'] = 'Martes';
        $diaSemana['Wednesday'] = 'Miercoles';
        $diaSemana['Thursday'] = 'Jueves';
        $diaSemana['Friday'] = 'Viernes';
        $diaSemana['Saturday'] = 'Sabado';
        $diaSemana['Sunday'] = 'Domingo';


        $diaDeLaSemana = strtolower($diaSemana[date("l", strtotime($fecha))]);

        db_query(
            0,
            "SELECT 
                hp.desdeManana, 
                hp.hastaManana, 
                hp.desdeTarde, 
                hp.hastaTarde, 
                hp.idProfesional
            FROM 
                horariosprofesionales hp, 
                profesionales p 
            WHERE 
                (
                    (dia = '{$diaDeLaSemana}' AND p.tipo='H' AND 
                    idHoras IN
                        (
                            SELECT MAX(idHoras)
                            FROM horariosprofesionales 
                            GROUP BY idProfesional, dia
                        )) OR 
                    (fechaEspecifica = '{$fecha}' AND p.tipo='P')
                ) AND 
                hp.idProfesional IN({$profesional}) AND 
                hp.idProfesional=p.idProfesional 
            ORDER BY desdeManana, desdeTarde"
        );

        $horarios = array();

        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();

            if (($row['desdeManana'] <> '') and ($row['hastaManana'] <> '')) {
                $horarios[] = array(
                    'desde' => $row['desdeManana'],
                    'hasta' => $row['hastaManana'],
                    'idProfesional' => $row['idProfesional']
                );
            }
            if (($row['desdeTarde'] <> '') and ($row['hastaTarde'] <> '')) {
                $horarios[] = array(
                    'desde' => $row['desdeTarde'],
                    'hasta' => $row['hastaTarde'],
                    'idProfesional' => $row['idProfesional']
                );
            }
        }

        // Si tiene horarios chequeo si hay que pisarlos. Solo para profesionales con horarios habituales
        if($general['feriadoPersonalizado'] && count($horarios)){
            $dataProfesional = db_getOne("SELECT idProfesional FROM profesionales WHERE idProfesional = {$profesional}");

            if($dataProfesional->tipo == 'P') return $horarios;

            if($dataNuevoHorario = db_getOne("SELECT * FROM feriadosPersonalizados WHERE eliminado != 1 AND idProfesional IN (0, {$profesional}) AND fechaDesde <= '{$fecha}' AND fechaHasta >= '{$fecha}'")){
                $horarios = array();
                $horarios[] = array(
                    'desde' => date("H:i", strtotime($dataNuevoHorario->horarioInicio)),
                    'hasta' => date("H:i", strtotime($dataNuevoHorario->horarioFin)),
                    'idProfesional' => $dataNuevoHorario->idProfesional
                );
            }
        }

        return $horarios;
    }



    public static function getFeriados($fecha)
    {

        global $tot;

        db_query(0, "SELECT idFeriado FROM feriados WHERE date(fecha) = '$fecha' AND estado='A' limit 1");
        return ($tot > 0);
    }


    public static function checkOverlap($horaInicio, $horaFin, $bloqueos, $simultaneos = 1)
    {

        $choques = 0;

        if($simultaneos==0){
            $simultaneos = 1;
        }

        //Está en un bloqueo?
        if (@sizeof($bloqueos) > 0) {
            foreach ($bloqueos as $bloqueo) {
                
                if (self::overlap($horaInicio, $horaFin, $bloqueo['desde'], $bloqueo['hasta'])) {
                    $choques++;
                    // break;
                }
                
                if ($choques >= $simultaneos) {
                    return true;
                }
            }
        }
    }


    public static function checkInRange($horaInicio, $horaFin, $bloqueos)
    {

        //Está en un bloqueo?
        foreach ($bloqueos as $bloqueo) {
            if (self::inRange($horaInicio, $horaFin, $bloqueo['desde'], $bloqueo['hasta'])) {
                return true;
                // break;
            }
        }
    }

    public static function formateDateInterval($duracion)
    {
        $paramDateInterval = $duracion . ' minutes';
        if ($duracion >= 60) {
            $horas = floor($duracion / 60);
            $minutos = $duracion % 60;

            $paramDateInterval = $horas . ' hours + ' . $minutos . ' minutes';
        }
        return DateInterval::createFromDateString($paramDateInterval);
    }


    public static function getHours($fecha, $profesional, $duracion, $timezone='', $origen='getHours', $idTratamiento = "")
    {

        global $general, $row;

        if(@!$timezone){
            $timezone = $general['timezone'];
        }

        if (self::getFeriados($fecha)) {
            return false;
        }

        $horarios = self::getHorariosProfesionales($fecha, $profesional);
        $bloqueos = self::getBloqueos($fecha, $profesional);
        $turnos = self::getTurnos($fecha, $profesional, $idTratamiento);
        
        $anticipacion = self::getAnticipacion($fecha);


        $simultaneosPorTurno = $general['turnosSimultaneos'];
        if($general["simultaneosPorServicio"] && $idTratamiento){
            db_query(0, "SELECT * FROM tratamientos WHERE idTratamiento = {$idTratamiento} LIMIT 1");
            $simultaneosPorTurno = $row["simultaneos"];
        }

        $horas = array();

        foreach ($horarios as $horario) {
            
            $horaInicio = $horario['desde'];
            $horaFin = $horario['hasta'];
            $idProfesional = $horario['idProfesional'];

            $begin = new DateTime($fecha.' '.$horaInicio);
            $end = new DateTime($fecha.' '.$horaFin);

            if ($general['duracionBloque'] == 'tratamiento') {
                $interval = DateInterval::createFromDateString($duracion . ' minutes');
            }
            if ($general['duracionBloque'] == 'bloque') {
                $interval = DateInterval::createFromDateString($general['minBloqueTurnos'] . ' minutes');
            }
            
            $period = new DatePeriod($begin, $interval, $end);
            
            /* Util::printVar($period,'186.138.206.135',true); */

            foreach ($period as $dt) {
                /* Util::printVar($dt,'186.138.206.135',false); */

                $dejo = 1;

                $estaOpcion = $dt->format("H:i");
                $estaOpcionFull = $dt->format("Y-m-d H:i:s");

                $dt->add(new DateInterval('PT' . ($duracion) . 'M'));

                $estaOpcionFin = $dt->format("H:i");
                $estaOpcionFinFull = $dt->format("Y-m-d H:i:s");

                // Util::printVar('Horario inicio: '.$estaOpcion,'190.231.85.160', false);
                // Util::printVar('Horario fin: '.$estaOpcionFin,'190.231.85.160', false);

                if (self::checkOverlap($estaOpcion, $estaOpcionFin, $bloqueos)) $dejo = 0;

                // Util::printVar('Bloqueo: '.$dejo,'190.231.85.160', false);

                if (self::checkOverlap($estaOpcion, $estaOpcionFin, $turnos, $simultaneosPorTurno)) $dejo = 0;

                // Util::printVar('Turnos: '.$dejo,'190.231.85.160', false);

                if (self::checkOverlap($estaOpcion, $estaOpcionFin, $anticipacion)) $dejo = 0;

                // Util::printVar('Anticipacion: '.$dejo. '<br>','190.231.85.160', false);

                $miHorario[] = $horario;
                if (!self::checkInRange($estaOpcion, $estaOpcionFin, $miHorario)) $dejo = 0;

                // Util::printVar($miHorario,'186.138.206.135', false);
                // Util::printVar($origen,'186.138.206.135', false);

                //Voy a ver si lo tengo que convertir al huso horario del cliente
                if((@$general['husosDinamicos'])and($origen=='getHours')){
                    $estaOpcionFull = Util::convertirHora($estaOpcionFull, $timezone);
                    $estaOpcionFinFull = Util::convertirHora($estaOpcionFinFull, $timezone);

                    // Util::printVar(date("Y-m-d",strtotime($estaOpcionFull)),'186.138.206.135',false);
                    // Util::printVar($fecha,'186.138.206.135',false);

                    if(date("Y-m-d",strtotime($estaOpcionFull)) == $fecha){
                        $estaOpcion= date("H:i",strtotime($estaOpcionFull));
                        $estaOpcionFin= date("H:i",strtotime($estaOpcionFinFull));
                    }else{
                        $dejo=0;
                    }
                }

                if ($dejo) {
                    $horas[] = array('desde' => $estaOpcion, 'hasta' => $estaOpcionFin, 'idProfesional' => $idProfesional);
                }
            }
        }

        return $horas;
    }



    public static function getMes($mes, $profesional, $tratamiento)
    {

        global $row;

        $fechas = array();
        
        db_query(0, "select duracion from tratamientos where idTratamiento={$tratamiento} limit 1");
        $duracion = $row['duracion'];
        
        /* Util::printVar($duracion, "190.231.85.160"); */

        $fechaPrincipio = date("Y-m-d", strtotime($mes . "-01"));
        $fechaFin = date("Y-m-t", strtotime($mes));

        $fechas['fechaInicio'] = $fechaPrincipio;

        $begin = new DateTime(date("Y-m-d", strtotime($fechaPrincipio . ' -15 days')));
        $end = new DateTime(date("Y-m-d", strtotime($fechaFin . ' +15 days')));


        $end->setTime(0, 0, 1);

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        foreach ($period as $dt) {
            $estaOpcion = $dt->format("Y-m-d");

            if (!self::getHours($estaOpcion, $profesional, $duracion, '', 'getDates', $tratamiento)) {
                $fechas['opciones'][] = $estaOpcion;
            }
        }
        
        return $fechas;
    }




    /* 
        return 
            - false | No hay nada que limite el turno
            - true  | Hay una limitacion
    */
    public static function getHoursEspecifica($fechaHora, $profesional, $duracion, $simultaneos = false)
    {
        //Verifico si el turno que está por reservar está libre realmente.

        global $general;

        // Util::printVar("Fecha hora".$fechaHora,'186.138.206.135', false);

        if (!$simultaneos) {
            $simultaneos = $general['turnosSimultaneos'];
        }

        $fecha = date("Y-m-d", strtotime(str_replace('/','-',$fechaHora)));
        $horaTurno = date("H:i", strtotime($fechaHora));
        $horaFinTurno = date("H:i", strtotime($fechaHora . ' +' . $duracion . ' minutes'));


        if (self::getFeriados($fecha)) {
            return false;
        }

        // Util::printVar('Fecha: '.$fecha,'186.138.206.135', false);

        // $horarios = self::getHorariosProfesionales($fecha, $profesional);
        $bloqueos = self::getBloqueos($fecha, $profesional);
        $turnos = self::getTurnos($fecha, $profesional);
        
        if(!AuthController::isLogged() && $general['calendario_quitarHorasAnticipacion'])
        {
            $anticipacion = self::getAnticipacion($fecha);
        }

        $horas = array();

        $idProfesional = $horarios['idProfesional'];

        $dejo = 1;

        $estaOpcion = $horaTurno;
        $estaOpcionFin = $horaFinTurno;

        if (self::checkOverlap($estaOpcion, $estaOpcionFin, $bloqueos)) $dejo = 0;
        if (self::checkOverlap($estaOpcion, $estaOpcionFin, $turnos, $simultaneos)) $dejo = 0;
        if(!AuthController::isLogged() && $general['calendario_quitarHorasAnticipacion']){
            if (self::checkOverlap($estaOpcion, $estaOpcionFin, $anticipacion)) $dejo = 0;
        }
        //if (self::checkOverlap($estaOpcion, $estaOpcionFin, $anticipacion)) $dejo = 0;
        // $miHorario[] = $horario;
        // if (!self::checkInRange($estaOpcion, $estaOpcionFin, $miHorario)) $dejo = 0;

        if ($dejo) {
            $horas[] = array('desde' => $estaOpcion, 'hasta' => $estaOpcionFin, 'idProfesional' => $idProfesional);
        }

        if (@sizeof($horas) > 0) {
            //No hay nada que limite tomar el turno
            return false;
        } else {
            //Hay algo que limite tomar el turno
            return true;
        }
    }

    public static function checkSimultaneosPorServicio($fechaInicio, $idProfesional, $idTratamiento){
        $dataTratamiento = db_getOne("SELECT duracion, simultaneos FROM tratamientos WHERE idTratamiento = {$idTratamiento}");
        $fechaFin = date("Y-m-d H:i:s", strtotime($fechaInicio. " + {$dataTratamiento->duracion} minutes"));
        
        // Chequeo bloqueos 
        if(db_getOne("SELECT idBloque FROM bloqueos WHERE idProfesional IN (0, {$idProfesional}) AND (
            (fechaDesde >= '{$fechaInicio}' AND fechaDesde < '{$fechaFin}') OR (fechaHasta > '{$fechaInicio}' AND fechaHasta <= '{$fechaFin}') OR(fechaDesde <= '{$fechaInicio}' AND fechaHasta >= '{$fechaFin}')
        ) AND estado <> 'B'")) return false;

        // Chequeo feriados
        if(db_getOne("SELECT idFeriado FROM feriados WHERE fecha = '".date("Y-m-d", strtotime($fechaInicio))."' AND estado = 'A'")) return false;

        // Chequeo turnos
        $turnos = db_getAll("SELECT t.idTurno FROM turnos t, ordenes o WHERE t.idOrden = o.idOrden AND t.estado <> '3' AND t.eliminado <> '1' AND o.idTratamiento = {$idTratamiento} AND o.idProfesional = '{$idProfesional}' AND (
            (t.fechaInicio >= '{$fechaInicio}' AND t.fechaInicio < '{$fechaFin}') OR (t.fechaFin > '{$fechaInicio}' AND t.fechaFin <= '{$fechaFin}') OR(t.fechaInicio <= '{$fechaInicio}' AND t.fechaFin >= '{$fechaFin}')
        )");
        if(count($turnos) >= $dataTratamiento->simultaneos) return false;

        return true;
    }

    public static function checkLugar($fechaHora, $profesional, $tratamiento)
    {
        //Verifico si el turno que está por reservar está libre realmente.

        global $general;

        $simultaneos = TratamientoController::getSimultaneosDelTratamiento($tratamiento, $profesional);
        $duracion = TratamientoController::getDuracionDelTratamiento($tratamiento, $profesional);
        
        if (@$general['turnosSimultaneos']) {
            $simultaneos = $general['turnosSimultaneos'];
        }
        /* Util::printvar($simultaneos, '186.138.206.135', true); */


        if (self::getHoursEspecifica($fechaHora, $profesional, $duracion, $simultaneos)) {
            return false;
        } else {
            return true;
        }
    }



    public static function getTurnosCalendario($fechaInicio, $fechaFin, $profesional = '')
    {

        global $row, $tot, $res, $general;

        $eventos = array();

        if ($profesional) {
            $filtroProfesional = ' and o.idProfesional=' . $profesional;
        } else {
            $filtroProfesional = '';
        }

        db_query(0, "SELECT 
            t.*, p.nombre, p.apellido, p.observaciones, o.idTratamiento, o.idProfesional, tra.duracion, tra.nombre as tratamiento, pro.nombre as profesional, pro.idProfesional, pro.color 
        FROM 
            pacientes p, tratamientos tra, profesionales pro, turnos t 
        LEFT JOIN 
            ordenes o on t.idOrden=o.idOrden 
        WHERE (t.estado<>3 and t.estado<>9) and t.eliminado<>1 and date(t.fechaInicio)>='{$fechaInicio}' and date(t.fechaInicio)<='{$fechaFin}' and t.idPaciente=p.idPaciente and o.idTratamiento=tra.idTratamiento and o.idProfesional=pro.idProfesional and p.estado <> 'B' {$filtroProfesional} group by t.idTurno order by t.fechaInicio asc");

        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();
            $contenido = $row;
            
            if ($row['estado'] == '0') {
                $clase = 'info';
                $estado = ucwords($general['estadoPendiente']);
            }
            if ($row['estado'] == '1') {
                $clase = 'success';
                $estado = ucwords($general['estadoConfirmado']);
            }
            if ($row['estado'] == '2') {
                $clase = 'warning';
                $estado = ucwords($general['estadoAusente']);
            }

            $duracion = (strtotime($row['fechaFin']) - strtotime($row['fechaInicio'])) / 60;
            if ($duracion < 60) {
                $duracionString = $duracion . ' min.';
            } else {
                $horas = intdiv($duracion, 60);
                $duracionString = $horas . ' hora';

                if ($horas > 1) {
                    $duracionString .= 's';
                }

                if (($duracion % 60) > 0) {
                    $duracionString .= ' ' . ($duracion % 60) . ' min.';
                }
            }

            $eventos[] = array(
                'id' => $row['idTurno'],
                'nombrePax' => $row['nombre'] . ' ' . $row['apellido'],
                'title' => ' ',
                'start' => str_replace(' ', 'T', $row['fechaInicio']),
                'end' => str_replace(' ', 'T', $row['fechaFin']),
                'duracion' => $duracion,
                'duracionString' => $duracionString,
                'colorPuntito' => $clase,
                'textoPuntito' => $estado,
                'profesional' => $row['profesional'],
                'tratamiento' => $row['tratamiento'],
                'colorProfesional' => '#' . $row['color'],
                'className' => array('tooltipss' . $row['idTurno']),
                'allDay' => false,
                'color' => '#e9e9e9',
                'textColor' => Util::getContrastColor('#' . $contenido['color']),
                'dataTitle' => '<b>' . $row['nombre'] . ' ' . $row['apellido'] . '</b><br><i>' . $row['tratamiento'] . '</i><br><i class="fa fa-medkit"></i> ' . $row['profesional'],
                'dataId' => $row['idTurno'],
                'resourceId' => $row['idProfesional']
            );
        }

        return $eventos;
    }

    public static function getResourcesCalendario($fechaInicio, $fechaFin, $profesional = '')
    {

        global $row, $tot, $res, $general;

        $eventos = array();

        $begin = new DateTime($fechaInicio);
        $end = new DateTime($fechaFin);
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        $feriados = $_SESSION['feriados'];

        foreach ($period as $dt) {
            $esteDia = strtolower(DateController::daysToDias($dt->format("l")));
            $esteFecha = $dt->format("Y-m-d");

            if (@$feriados[$esteFecha]) {

                $eventos[] = array(
                    'start' => $esteFecha . 'T00:00:00',
                    'end' => $esteFecha . 'T23:59:59',
                    'rendering' => 'background',
                    'color' => '#666666'
                );
            } else {
                if (@$_SESSION['horarios'][$esteDia]) {
                    foreach ($_SESSION['horarios'][$esteDia] as $vuelta => $contenido) {

                        if ($profesional) {
                            if ($contenido['idProfesional'] != $profesional) {
                                continue;
                            }
                        }

                        db_query(0, "SELECT * from bloqueos where date(fechaDesde)<='{$esteFecha}' and date(fechaHasta)>='{$esteFecha}' and (idProfesional='" . $contenido['idProfesional'] . "' or idProfesional='0') and estado='A'");
                        if ($tot > 0) {

                            $eventos[] = array(
                                'start' => str_replace(' ', 'T', $row['fechaDesde']),
                                'end' => str_replace(' ', 'T', $row['fechaHasta']),
                                'rendering' => 'background',
                                'color' => '#666666',
                                'resourceId' => $row['idProfesional']
                            );
                        } else {

                            // Profesional con horario habitual
                            if (($contenido['fechaEspecifica'] == '0000-00-00') or ($contenido['fechaEspecifica'] == $esteFecha)) {


                                // Chequeo si hay un feriado personalizado utilizo ese horario
                                if($general["feriadoPersonalizado"]){
                                    if($dataNuevoHorario = db_getOne("SELECT * FROM feriadosPersonalizados WHERE eliminado != 1 AND idProfesional IN (0, {$contenido['idProfesional']}) AND fechaDesde <= '{$esteFecha}' AND fechaHasta >= '{$esteFecha}'")){
                                        $eventos[] = array(
                                            'start' => $esteFecha . 'T' . $dataNuevoHorario->horarioInicio,
                                            'end' => $esteFecha . 'T' . $dataNuevoHorario->horarioFin,
                                            'rendering' => 'background',
                                            'color' => '#' . $contenido['color'],
                                            'resourceId' => $contenido['idProfesional']
                                        );
                                        continue;
                                    }
                                }

                                if ($contenido['desdeManana'] <> '') {
                                    $eventos[] = array(
                                        'start' => $esteFecha . 'T' . $contenido['desdeManana'] . ':00',
                                        'end' => $esteFecha . 'T' . $contenido['hastaManana'] . ':00',
                                        'rendering' => 'background',
                                        'color' => '#' . $contenido['color'],
                                        'resourceId' => $contenido['idProfesional']
                                    );
                                }
                                if ($contenido['desdeTarde'] <> '') {
                                    $eventos[] = array(
                                        'start' => $esteFecha . 'T' . $contenido['desdeTarde'] . ':00',
                                        'end' => $esteFecha . 'T' . $contenido['hastaTarde'] . ':00',
                                        'rendering' => 'background',
                                        'color' => '#' . $contenido['color'],
                                        'resourceId' => $contenido['idProfesional']
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        return $eventos;
    }


    public static function getEventosCalendario($fechaInicio, $fechaFin, $profesional = '')
    {
        return array_merge(self::getTurnosCalendario($fechaInicio, $fechaFin, $profesional), self::getResourcesCalendario($fechaInicio, $fechaFin, $profesional));
    }






    
    
    
    
    
    
    
    
    
    



    public static function getResumenMensual($fechaInicio, $fechaFin, $profesional = '')
    {
        global $row, $tot, $res, $general;

        $eventos = array();

        if ($profesional) {
            $filtroProfesional = ' and o.idProfesional=' . $profesional;
        } else {
            $filtroProfesional = '';
        }

        $begin = new DateTime($fechaInicio);
        $end = new DateTime($fechaFin);
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        $feriados = $_SESSION['feriados'];

        foreach ($period as $dt) {
            $esteDia = strtolower(DateController::daysToDias($dt->format("l")));
            $esteFecha = $dt->format("Y-m-d");

            
            $turnosRepetidos=array();

            if (@$feriados[$esteFecha]) {

                $eventos[] = array(
                    'start' => $esteFecha,
                    'colorProfesional' => '#333333',
                    'color' => '#333333',
                    'allDay' => true,
                    'title' => ' ',
                    'titleColor' => Util::getContrastColor('#333333'),
                    'textColor' => '#e9e9e9',
                    'motivo' => $feriados[$esteFecha],
                    'tipo' => 'Feriado'
                );
            } else {
                if (@$_SESSION['horarios'][$esteDia]) {

                    foreach ($_SESSION['horarios'][$esteDia] as $vuelta => $contenido) {
                        
                        if ($profesional) {
                            if ($contenido['idProfesional'] != $profesional) {
                                continue;
                            }
                        }


                        if($contenido["fechaEspecifica"] != "0000-00-00") continue;
                        

                        if(in_array($esteFecha.$contenido["idProfesional"], $turnosRepetidos) && $_SERVER["REMOTE_ADDR"] == "190.31.195.37") continue;

                        $turnosRepetidos[] = $esteFecha.$contenido["idProfesional"];

                        db_query(0, "SELECT b.*, p.nombre as profesional, p.color from bloqueos b left join profesionales p on p.idProfesional=b.idProfesional where date(b.fechaDesde)<='{$esteFecha}' and date(b.fechaHasta)>='{$esteFecha}' and (b.idProfesional='{$contenido['idProfesional']}' or b.idProfesional='0') and b.estado='A'");
                        if ($tot > 0) {

                            if ((date("Y-m-d", strtotime($row['fechaDesde'])) == date("Y-m-d", strtotime($esteFecha))) and (date("Y-m-d", strtotime($row['fechaHasta'])) == date("Y-m-d", strtotime($esteFecha)))) {
                                $rango = date(
                                    "H:i",
                                    strtotime($row['fechaDesde'])
                                ) . ' - ' . date(
                                    "H:i",
                                    strtotime($row['fechaHasta'])
                                );
                            } else if (date("d/m/Y", strtotime($row['fechaDesde'])) == date("d/m/Y", strtotime($row['fechaHasta']))) {
                                $rango = date(
                                    "d/m/Y H:i",
                                    strtotime($row['fechaDesde'])
                                ) . ' - ' . date(
                                    "H:i",
                                    strtotime($row['fechaHasta'])
                                );
                            } else {
                                $rango = date(
                                    "d/m/Y H:i",
                                    strtotime($row['fechaDesde'])
                                ) . ' - ' . date(
                                    "d/m/Y H:i",
                                    strtotime($row['fechaHasta'])
                                );
                            }

                            if ($row['idProfesional'] == 0) {
                                $eventos[] = array(
                                    'start' => $esteFecha,
                                    'colorProfesional' => '#666666',
                                    'color' => '#666666',
                                    'allDay' => true,
                                    'title' => ' ',
                                    'titleColor' => Util::getContrastColor('#666666'),
                                    'textColor' => '#e9e9e9',
                                    'motivo' => $rango . '<br>' . $row['descripcion'],
                                    'tipo' => 'Bloqueo - Todos'
                                );
                                break;
                            } else {
                                $eventos[] = array(
                                    'start' => $esteFecha,
                                    'colorProfesional' => '#' . $row['color'],
                                    'color' => '#666666',
                                    'allDay' => true,
                                    'title' => ' ',
                                    'titleColor' => Util::getContrastColor('#' . $row['color']),
                                    'textColor' => '#e9e9e9',
                                    'motivo' => $rango,
                                    'tipo' => '<del>' . $row['profesional'] . '</del> - ' . $row['descripcion']
                                );
                            }
                        } else {

                            db_query(0, "select p.color, p.nombre, count(t.idTurno) as turnos from profesionales p left join ordenes o on o.idProfesional=p.idProfesional left join turnos t on o.idOrden=t.idOrden and t.estado<>3 and t.eliminado<>1 and date(t.fechaInicio)='{$esteFecha}' where p.idProfesional='{$contenido['idProfesional']}' and p.estado='A'");

                            if (($contenido['fechaEspecifica'] == '0000-00-00') or ($contenido['fechaEspecifica'] == $esteFecha)) {

                                $horasDisponibles = array();

                                if ($contenido['desdeManana'] <> '') {
                                    $horasDisponibles[] = $contenido['desdeManana'] . ' - ' . $contenido['hastaManana'];
                                }
                                if ($contenido['desdeTarde'] <> '') {
                                    $horasDisponibles[] = $contenido['desdeTarde'] . ' - ' . $contenido['hastaTarde'];
                                }

                                $horas = implode(' | ', $horasDisponibles);

                                $eventos[] = array(
                                    'start' => $esteFecha,
                                    'colorProfesional' => '#' . $row['color'],
                                    'color' => '#e9e9e9',
                                    'allDay' => true,
                                    'title' => ' ',
                                    'titleColor' => Util::getContrastColor('#' . $row['color']),
                                    'textColor' => '#333333',
                                    'motivo' => '<i class="far fa-clock"></i> ' . $horas . '<br><i class="far fa-calendar-check"></i> ' . $row['turnos'] . ' turnos',
                                    'tipo' => $row['nombre']
                                );
                            }
                        }
                    }
                }
            }
        }


        usort($eventos, function ($a, $b) {
            return $a['tipo'] <=> $b['tipo'];
        });

        return $eventos;
    }



    // Array con fechas => con cantidad por fecha
    public static function turnosPorFecha($fechaDesde, $fechaHasta, $estado = 1)
    {
        global $tot;
        global $row;
        global $res;

        $fechas = [];
        $fechas['suma'] = 0;

        db_query(
            0,
            "SELECT 
                COUNT(t.idTurno) as cantidad, 
                date(t.fechaInicio) as fecha 
            FROM 
                turnos t
            WHERE 
                date(t.fechaInicio) >= '" . $fechaDesde . "' AND 
                date(t.fechaInicio) <= '" . $fechaHasta . "' AND 
                t.estado = " . ($estado) . " AND 
                t.eliminado <> '1' 
            GROUP BY date(t.fechaInicio);
        "
        );


        if ($tot > 0) {
            for ($i = 0; $i < $tot; $i++) {
                $nres = $res->data_seek($i);
                $row = $res->fetch_assoc();
                $fechas['fechas'][$row['fecha']] = $row['cantidad'];
                $fechas['suma'] += $row['cantidad'];
            }
        }

        return $fechas;
    }
    public static function turnosAtendidos($fechaDesde, $fechaHasta, $estado = 1)
    {
        global $tot;
        global $row;
        global $res;

        $response = [];

        $cantidadAtendidos = array();
        $cantidadAtendidosTratamiento = array();
        
        db_query(
            0,
            "SELECT 
                o.idProfesional, 
                o.idTratamiento, 
                t.estado 
            FROM 
                turnos t, 
                ordenes o 
            WHERE 
                DATE(t.fechaInicio)>='" . $fechaDesde . "' AND 
                DATE(t.fechaInicio)<='" . $fechaHasta . "' AND 
                t.idOrden=o.idOrden AND 
                t.estado = " . $estado . " AND 
                t.eliminado <> '1'
            ORDER BY t.fechaInicio ASC
        ");

        $hayTurnos = $tot > 0 ? 1 : 0;

        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();

            if ($row['estado'] == 1) {
                @$cantidadAtendidos[$row['idProfesional']]++;
                //Hago las querys a las obras sociales
                @$cantidadAtendidosTratamiento[$row['idTratamiento']]++;
            }
        }

        $response['profesionales'] = $cantidadAtendidos;
        $response['tratamientos'] = $cantidadAtendidosTratamiento;

        return $response;
    }
    
    public static function turnosAtendidosOrigen($fechaDesde, $fechaHasta, $estado = 1)
    {
        global $tot;
        global $row;
        global $res;

        $cantidadAtendidos = array();
        
        db_query(
            0,
            "SELECT 
                IF(l.usuario='homeTurnos', 'P', 'A') AS origen
            FROM 
                turnos t, 
                log l 
            WHERE 
                DATE(t.fechaInicio)>='{$fechaDesde}' AND 
                DATE(t.fechaInicio)<='{$fechaHasta}' AND 
                t.idTurno=l.id AND 
                t.estado = {$estado} AND 
                l.accion LIKE '%turno%' AND
                t.eliminado <> '1'
            GROUP BY t.idTurno 
            ORDER BY t.fechaInicio ASC
        ");

        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();

            @$cantidadAtendidos[$row['origen']]++;
            
        }

        return $cantidadAtendidos;
    }


    public static function turnosPorFechaPorProfesional($fechaDesde, $fechaHasta, $estado = 1, $profesional = '')
    {
        global $tot;
        global $row;
        global $res;

        $fechas = [];
        $fechas['suma'] = 0;

        $filtroProfesional = $profesional ? ' AND p.idProfesional = '.$profesional.' ' : '' ;

        db_query(
            0,
            "SELECT 
                COUNT(t.idTurno) as cantidad, 
                date(t.fechaInicio) as fecha 
            FROM 
                turnos t,
                ordenes o,
                profesionales p
            WHERE 
                t.idOrden = o.idOrden AND 
                o.idProfesional = p.idProfesional AND 
                date(t.fechaInicio) >= '" . $fechaDesde . "' AND 
                date(t.fechaInicio) <= '" . $fechaHasta . "' AND 
                t.estado = " . ($estado) . " AND 
                t.eliminado <> '1' 
                {$filtroProfesional}
            GROUP BY date(t.fechaInicio);
        "
        );


        if ($tot > 0) {
            for ($i = 0; $i < $tot; $i++) {
                $nres = $res->data_seek($i);
                $row = $res->fetch_assoc();
                $fechas['fechas'][$row['fecha']] = $row['cantidad'];
                $fechas['suma'] += $row['cantidad'];
            }
        }

        return $fechas;
    }
    public static function turnosAtendidosPorProfesional($fechaDesde, $fechaHasta, $estado = 1, $profesional = '')
    {
        global $tot;
        global $row;
        global $res;

        $response = [];

        $filtroProfesional = $profesional ? ' AND p.idProfesional = '.$profesional.' ' : '' ;

        $cantidadAtendidos = array();
        $cantidadAtendidosTratamiento = array();
        
        db_query(
            0,
            "SELECT 
                o.idProfesional, 
                o.idTratamiento, 
                t.estado 
            FROM 
                turnos t, 
                ordenes o ,
                profesionales p
            WHERE 
                DATE(t.fechaInicio)>='" . $fechaDesde . "' AND 
                DATE(t.fechaInicio)<='" . $fechaHasta . "' AND 
                t.idOrden=o.idOrden AND 
                o.idProfesional = p.idProfesional AND 
                t.estado = " . $estado . " AND 
                t.eliminado <> '1'
                {$filtroProfesional}
            ORDER BY t.fechaInicio ASC
        ");

        $hayTurnos = $tot > 0 ? 1 : 0;

        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();

            if ($row['estado'] == 1) {
                @$cantidadAtendidos[$row['idProfesional']]++;
                //Hago las querys a las obras sociales
                @$cantidadAtendidosTratamiento[$row['idTratamiento']]++;
            }
        }

        $response['profesionales'] = $cantidadAtendidos;
        $response['tratamientos'] = $cantidadAtendidosTratamiento;

        return $response;
    }






    /*
        Cupos
    */

    public static function getTurnosCalendarioPublic($fechaInicio, $fechaFin, $profesional = '')
    {

        global $row, $tot, $res;

        $eventos = array();

        if ($profesional) {
            $filtroProfesional = ' and o.idProfesional=' . $profesional;
        } else {
            $filtroProfesional = '';
        }

        db_query(0, "SELECT 
            t.*, p.nombre, p.apellido, p.observaciones, o.idTratamiento, o.idProfesional, tra.duracion, tra.nombre as tratamiento, pro.nombre as profesional, pro.idProfesional 
        FROM 
            pacientes p, tratamientos tra, profesionales pro, turnos t 
        LEFT JOIN 
            ordenes o on t.idOrden=o.idOrden 
        WHERE t.estado<>3 and t.eliminado<>1 and date(t.fechaInicio)>='{$fechaInicio}' and date(t.fechaInicio)<='{$fechaFin}' and t.idPaciente=p.idPaciente and o.idTratamiento=tra.idTratamiento and o.idProfesional=pro.idProfesional {$filtroProfesional} group by t.idTurno order by t.fechaInicio asc");

        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();

            if ($row['estado'] == '0') {
                $clase = 'info';
            }
            if ($row['estado'] == '1') {
                $clase = 'success';
            }
            if ($row['estado'] == '2') {
                $clase = 'warning';
            }

            $eventos[] = array(
                'id' => $row['idTurno'],
                'title' => $row['nombre'] . ' ' . $row['apellido'],
                'start' => str_replace(' ', 'T', $row['fechaInicio']),
                'end' => str_replace(' ', 'T', $row['fechaFin']),
                'className' => array('bg-' . $clase, 'tooltipss' . $row['idTurno']),
                'allDay' => false,
                'color' => '#e9e9e9',
                'textColor' => '#000000',
                'dataTitle' => '<b>' . $row['nombre'] . ' ' . $row['apellido'] . '</b><br><i>' . $row['tratamiento'] . '</i><br><i class="fa fa-medkit"></i> ' . $row['profesional'],
                'dataId' => $row['idTurno'],
                'resourceId' => $row['idProfesional']
            );
        }

        return $eventos;
    }

    public static function getResourcesCalendarioPublic($fechaInicio, $fechaFin, $categoria = '')
    {

        global $general, $row, $tot, $res, $row1, $tot1;
        GLOBAL $row11, $tot11;

        Util::updateStaticData();

        $eventos = array();

        $begin = new DateTime($fechaInicio);
        $end = new DateTime($fechaFin);
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        $feriados = $_SESSION['feriados'];

        foreach ($period as $dt) {
            $esteDia = strtolower(DateController::daysToDias($dt->format("l")));
            $esteFecha = $dt->format("Y-m-d");

            if (@$feriados[$esteFecha]) {
                $eventos[] = array(
                    'start' => $esteFecha . 'T00:00:00',
                    'end' => $esteFecha . 'T23:59:59',
                    'rendering' => 'background',
                    'color' => '#666666'
                );
            } else {

                if($categoria){
                    $filtroCategoria=" AND ct.idCategoria IN (" . $categoria . ")";
                }

                //No es feriado, voy a buscar que eventos tengo en el día, en base a los profesionales que atienden los servicios de la categoría que pido
                db_query(0, "SELECT hp.*, p.color, t.duracion, ct.idCategoria, p.nombre as profesional, t.nombre as tratamiento, t.simultaneos as cupo, t.idTratamiento, t.duracion FROM horariosprofesionales hp, profesionales p, tratamientos t, profesionales_tratamientos pt, categorias_tratamientos ct WHERE ((hp.idHoras in(select max(idHoras) from horariosprofesionales group by idProfesional, dia) and dia='{$esteDia}') or (hp.fechaEspecifica='{$esteFecha}')) and hp.idProfesional=p.idProfesional and ((hp.desdeManana<>'' and hp.hastaManana<>'') or (hp.desdeTarde<>'' and hp.hastaTarde<>'')) ".$filtroCategoria." AND ct.idTratamiento=t.idTratamiento and t.idTratamiento=pt.idTratamiento and pt.idProfesional=p.idProfesional group by t.idTratamiento order by hp.dia");

                if (@$tot) {
                    for ($i = 0; $i < $tot; $i++) {
                        $nres = $res->data_seek($i);
                        $contenido = $res->fetch_assoc();

                        if ($categoria) {
                            if ($contenido['idCategoria'] != $categoria) {
                                continue;
                            }
                        }

                        $horarios = array();

                        if ($contenido['desdeManana'] && $contenido['hastaManana']) {
                            $horarios[] = array(
                                'desde' => $contenido['desdeManana'],
                                'hasta' => $contenido['hastaManana']
                            );
                        }

                        if ($contenido['desdeTarde'] && $contenido['hastaTarde']) {
                            $horarios[] = array(
                                'desde' => $contenido['desdeTarde'],
                                'hasta' => $contenido['hastaTarde']
                            );
                        }

                        foreach ($horarios as $horario) {
                            //Ciclo por las horas, cortandolo por la duración del tratamiento
                            $begints = new DateTime($horario['desde']);
                            $endts = new DateTime($horario['hasta']);
                            $intervalts = DateInterval::createFromDateString($contenido['duracion'] . ' minutes');
                            $periodts = new DatePeriod($begints, $intervalts, $endts);

                            foreach ($periodts as $ts) {
                                $estaHora = $ts->format("H:i:s");
                                $estaHoraFin = $ts->add(new DateInterval('PT' . $contenido['duracion'] . 'M'))->format("H:i:s");


                                if (($contenido['fechaEspecifica'] == '0000-00-00') or ($contenido['fechaEspecifica'] == $esteFecha)) {

                                    /* Chequeo que no haya bloqueos */
                                    $estaFechaCompletaInicio = date('Y-m-d H:i:s', strtotime($esteFecha. " ".$estaHora));
                                    $estaFechaCompletaFin = date('Y-m-d H:i:s', strtotime($esteFecha. " ".$estaHoraFin));
                                    db_query(11, "SELECT * from bloqueos where fechaDesde <='{$estaFechaCompletaInicio}' and fechaHasta >='{$estaFechaCompletaFin}' and (idProfesional='" . $contenido['idProfesional'] . "' or idProfesional = 0) and estado='A'");

                                    if($tot11 > 0){
                                        $eventos[] = array(
                                            'start' => str_replace(' ', 'T', $row11['fechaDesde']),
                                            'end' => str_replace(' ', 'T', $row11['fechaHasta']),
                                            'rendering' => 'background',
                                            'color' => '#666666',
                                            'resourceId' => $row11['idProfesional']
                                        );
                                        continue;
                                    }

                                    $miHorario[0] = $horario;

                                    if (self::checkInRange(date("H:i", strtotime($estaHora)), date("H:i", strtotime($estaHoraFin)), $miHorario)) {

                                        //Voy a ver cuántos turnos tengo tomados en este horario para este profesional y tratamiento para sacar la cantidad de tomados
                                        db_query(1, "SELECT COUNT(t.idTurno) as tomados  from turnos t, ordenes o WHERE o.idOrden=t.idOrden and t.estado<>3 and t.eliminado<>1 and o.idProfesional={$contenido['idProfesional']} and o.idTratamiento={$contenido['idTratamiento']} and t.fechaInicio='{$esteFecha} {$estaHora}' and t.fechaFin='{$esteFecha} {$estaHoraFin}'");

                                        $ocupacion = (($row1['tomados'] * 100) / $contenido['cupo']);

                                        $colorPuntito = 'text-success';

                                        if ($ocupacion >= 60) {
                                            $colorPuntito = 'text-warning';
                                        }
                                        if ($ocupacion >= 100) {
                                            $colorPuntito = 'text-danger';
                                        }

                                        $duracion = $contenido['duracion'];
                                        if ($duracion < 60) {
                                            $duracionString = $duracion . ' min.';
                                        } else {
                                            $horas = intdiv($duracion, 60);
                                            $duracionString = $horas . ' hora';

                                            if ($horas > 1) {
                                                $duracionString .= 's';
                                            }

                                            if (($duracion % 60) > 0) {
                                                $duracionString .= ' ' . ($duracion % 60) . ' min.';
                                            }
                                        }

                                        $eventos[] = array(
                                            'id' => $contenido['idHoras'],
                                            'idProfesional' => $contenido['idProfesional'],
                                            'idTratamiento' => $contenido['idTratamiento'],
                                            'actividad' => $contenido['tratamiento'],
                                            'profesional' => $contenido['profesional'],
                                            'cupo' => $contenido['cupo'],
                                            'tomados' => $row1['tomados'],
                                            'duracion' => $duracionString,
                                            'colorPuntito' => $colorPuntito,
                                            'ocupacion' => $ocupacion,
                                            'title' => ' ',
                                            'start' => $esteFecha . 'T' . $estaHora,
                                            'end' => $esteFecha . 'T' . $estaHoraFin,
                                            'allDay' => false,
                                            'colorProfesional' => '#' . $contenido['color'],
                                            'color' => '#e9e9e9',
                                            'textColor' => Util::getContrastColor('#' . $contenido['color']),
                                            'dataId' => $row['idhoras'],
                                            'resourceId' => $contenido['idTratamiento']
                                        );
                                    }
                                }
                            }
                        }
                        
                    }
                }
            }
        }

        return $eventos;
    }


    public static function getEventosCalendarioPublic($fechaInicio, $fechaFin, $categoria = '')
    {
        return self::getResourcesCalendarioPublic($fechaInicio, $fechaFin, $categoria);
        // return array_merge(self::getTurnosCalendarioPublic($fechaInicio, $fechaFin, $categoria), self::getResourcesCalendarioPublic($fechaInicio, $fechaFin, $categoria));
    }







    /* 
        Nuevos metodos
    */
    public static function save()
    {
        global $row, $tot, $res, $newid, $general;
        GLOBAL $row13, $tot13, $res13;
        GLOBAL $tot11;

        /* Guardo o edito el paciente */
        $telefono = Util::limpiarTelefono($_POST['telefono']);
        $varsPacientes['tabla'] = 'pacientes';
        $varsPacientes['idLabel'] = 'idPaciente';
        $varsPacientes['telefono'] = $telefono;
        $varsPacientes['mail'] = $_POST['mail'];
        $varsPacientes['codArea'] = $_POST['codArea'];
        $orden = $_POST['orden'] ?? '';

        if ($_POST['paciente'] <> 'NN') {
            $varsPacientes['id'] = $_POST['paciente'];
            $varsPacientes['accion'] = 'actualizarPacienteNN';
        } else {
            $varsPacientes['nombre'] = $_POST['nombre'];
            $varsPacientes['apellido'] = $_POST['apellido'];
            $varsPacientes['estado'] = 'A';
            $varsPacientes['dni'] = $_POST['dni'];
            $varsPacientes['accion'] = 'agregarPacienteNN';
        }

        $paciente = db_edit($varsPacientes);
        /* End paciente */

        //Acá vamos a ver si son varios turnos o uno solo
        db_query(0, "select nombre, duracion from tratamientos where idTratamiento=" . $_POST['tratamiento'] . " limit 1");
        $duracionTratamiento = $row['duracion'];
        $nombreTratamiento = $row['nombre'];

        $fechasTurnos=array();
        $turnosTomados=array();

        $fechasTurnos[]=array(
            'fecha'=>$fecha=date("Y-m-d",strtotime(str_replace('/','-',$_POST['fecha']))),
            'horas'=>$_POST['horas'],
            'minutos'=>$_POST['minutos']
        );

        /* 
            Turnos recurrentes
        */
        if($_POST['lapso']<>'no'){

            // Voy a chequear si todos los turnos solicitados están libres
            if($_POST['lapso']=='semana'){
                $lapso=1;
            }
            if($_POST['lapso']=='quincena'){
                $lapso=2;
            }
            if($_POST['lapso']=='mes'){
                $lapso=4;
            }
		
            $repeticiones=$_POST['repeticion'];
	
            for($i=0;$i<$repeticiones;$i++){
		
                $fecha=date("Y-m-d",strtotime(str_replace('/','-',$_POST['fecha']).' +'.($lapso*($i+1)).' weeks' ));
			
                //Voy a verificar si la proxima fecha es feriado, si la es, pateo todo una semana.
                db_query(0,"select idFeriado from feriados where fecha='".$fecha."' limit 1");
                if($tot>0){
                    $repeticiones++;
                }else{
                    $fechasTurnos[]=array(
                        'fecha'=>$fecha,
                        'horas'=>$_POST['horas'],
                        'minutos'=>$_POST['minutos']
                    );
                    
                }
            }
        }

        /* 
            Valido que el cliente no tenga tratamientos bloqueados 
        */
        if($general['bloquearTurnosAPacientesPorTratamiento']){
            $tratamientosBloqueadosPorPaciente=[];
            db_query(13, "SELECT * FROM pacientes_tratamientos WHERE idPaciente = '{$paciente}'");
            for ($i = 0; $i < $tot13; $i++) {
                $nres13 = $res13->data_seek($i);
                $row13 = $res13->fetch_assoc();
                $tratamientosBloqueadosPorPaciente[] = $row13['idTratamiento'];
            }

            // Busco que el tratamiento del turno no coincida con un tratamiento bloqueado
            if(is_numeric(array_search($_POST['tratamiento'], $tratamientosBloqueadosPorPaciente))){
                die("No tiene permisos para sacar turnos de: ".$nombreTratamiento);
            }
        }

        // Valido por tratamiento, sino por turno
        if($general['simultaneosPorServicio']){
            $simultaneosPorServicio=TratamientoController::getSimultaneosDelTratamiento($_POST['tratamiento']);

            // Voy a chequear cuantas veces choca con otro turno
            foreach($fechasTurnos as $turno){
                $fechaTurno = $turno['fecha'].' '.$turno['horas'].':'.$turno['minutos'].':00';
                
                // Busco si hay turnos con ese tratamiento y profesional
                db_query(11, "SELECT t.idTurno FROM turnos t, ordenes o WHERE t.estado <> '3' AND t.eliminado <> '1' AND t.idOrden = o.idOrden AND o.idTratamiento = '{$_POST["tratamiento"]}' AND o.idProfesional = '{$_POST["profesional"]}' AND t.fechaInicio = '{$fechaTurno}' ");
                $contadorDeTurnosPorServicio = $tot11;
                
                /* Util::printVar([$simultaneosPorServicio, $tot11], "190.231.247.33"); */

                if($general['sobreTurno']) continue;
                
                if($contadorDeTurnosPorServicio >= $simultaneosPorServicio){
                    die("Ya hay turnos asignados en los horarios que se intentan asignar.");
                }
                
            }
 
        }else{ // Turnos simultaneos

            $maxTurnos = $general["turnosSimultaneos"] == "" ? 0 : intval(trim($general["turnosSimultaneos"]));
            $contTurnosPorHorario = 0;

            //Voy a chequear si alguno choca con otro turno
            foreach($fechasTurnos as $turno){
                if($general['sobreTurno']) continue;

                $fechaInicioEsteTurno = $turno['fecha'].' '.$turno['horas'].':'.$turno['minutos'].":00";
                $fechaFinEsteTurno = date("Y-m-d H:i:s", strtotime($fechaInicioEsteTurno." + {$duracionTratamiento} minutes"));

                
                // Chequeo horas anticipadas
                if(!$general['calendario_quitarHorasAnticipacion'])
                {
                    if($general["horasAnticipacion"] && $fechaInicioEsteTurno < date("Y-m-d H:i:s", strtotime("+ {$general["horasAnticipacion"]} hours"))){
                        die("No puede sacar {$general['nombreTurnos']} con menos de {$general["horasAnticipacion"]}hs. de anticipacion");
                    }
                }
                
                // Chequeo bloqueos
                $queryFindBloqueo = "SELECT idBloqueo, descripcion FROM bloqueos WHERE estado <> 'B' AND idProfesional IN (0, {$_POST['profesional']}) AND (
                    (fechaDesde >= '{$fechaInicioEsteTurno}' AND fechaDesde < '{$fechaFinEsteTurno}') OR 
                    (fechaHasta > '{$fechaInicioEsteTurno}' AND fechaHasta <= '{$fechaFinEsteTurno}') OR 
                    (fechaDesde < '{$fechaInicioEsteTurno}' AND fechaHasta > '{$fechaFinEsteTurno}')
                )";
                if($dataBloqueo = db_getOne($queryFindBloqueo)) die("Hay un bloqueo: {$dataBloqueo->descripcion}");

                /* // Contador de turnos
                $contTurnosPorHorario++; */

                if(!self::checkLugar($turno['fecha'].' '.$turno['horas'].':'.$turno['minutos'],$_POST['profesional'],$_POST['tratamiento'])){
                    $contTurnosPorHorario++;
                }
            }
            
            if($contTurnosPorHorario >= $maxTurnos){
                die("Ya hay turnos asignados en los horarios que se intentan asignar.");
            }
        }


        /* Util::printVar($_POST, '186.138.206.135', true); */
        /* Util::printVar($fechasTurnos, '186.138.206.135', false);        
        Util::printVar("***", '186.138.206.135', true);    */  
        
        /* Chequeo que el horario del turno este dentro del rango horario del profesional */
        /* $fechaInicioPrimerTurno = date("Y-m-d", strtotime(str_replace('/', '-', $_POST["fecha"])));
        $horaInicioPrimerTurno = $_POST['horas'] . ":" . $_POST['minutos'];
        $horaFinPrimerTurno = date("H:i", strtotime($horaInicioPrimerTurno." + {$duracionTratamiento} minutes"));
        $dentroDelHorarioDelProfesional = false;
        foreach (Turno::getHorariosProfesionales($fechaInicioPrimerTurno, $_POST["profesional"]) as $horario) {
            if($horario["desde"] <= $horaInicioPrimerTurno && $horaFinPrimerTurno <= $horario["hasta"]){
                $dentroDelHorarioDelProfesional = true;
                break;
            }
        }
        if(!$dentroDelHorarioDelProfesional) die("El horario del {$general['nombreTurno']} no está dentro del horario del {$general['nombreProfesional']}."); */

        /* ----------------------------- */
        /*          SACO TURNOS          */
        /* ----------------------------- */
        foreach($fechasTurnos as $turno){
            if ($_POST['tratamiento'] <> 0) {
                $varsTratamiento['tabla'] = 'ordenes';
                $varsTratamiento['idPaciente'] = $paciente;
                $varsTratamiento['idProfesional'] = $_POST['profesional'];
                $varsTratamiento['cantidad'] = '1';
                $varsTratamiento['idTratamiento'] = $_POST['tratamiento'];
                $varsTratamiento['fechaAlta'] = date("Y-m-d H:i:s");
                $varsTratamiento['accion'] = 'agregarOden';

                $orden = db_edit($varsTratamiento);
            } else {
                die('faltaTratamiento');
            }

            $estadoTurno = 0;

            /* Guardo los Turnos */
            $fechaInicio = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $turno['fecha']) . " " . $turno['horas'] . ":" . $turno['minutos'] . ":00"));
            $fechaFin = date("Y-m-d H:i:s", strtotime($fechaInicio . ' +' . $duracionTratamiento . ' minutes'));

            $varsTurno['tabla'] = 'turnos';
            $varsTurno['accion'] = 'agregarTurno';
            $varsTurno['idPaciente'] = $paciente;
            $varsTurno['idOrden'] = $orden;
            $varsTurno['fechaInicio'] = $fechaInicio;
            $varsTurno['fechaFin'] = $fechaFin;
            $varsTurno['estado'] = $estadoTurno;

            $idTurno = db_edit($varsTurno);
            
            // Si tiene el campo de observaciones activado lo guardo
            if (isset($general["campoObservaciones"]) && $_POST["comentarios"]) {
                self::saveObservaciones($idTurno, $_POST["comentarios"]);
            }

            $turnosTomados[]=$idTurno;
        }

        $idTurnoOriginal=$idTurno;
        $idTurno=$turnosTomados;


       /*  Util::printVar($turnosTomados, '186.138.206.135', false);
        Util::printVar($idTurno, '186.138.206.135', false);
        Util::printVar($_POST, '186.138.206.135', true); */
        
        //ENVIAR CONFIRMACION POR MAIL
        require_once($_SERVER["DOCUMENT_ROOT"] . '/inc/mailConfirmacion.php');
        db_log('homeTurnos', 'mandoMailConfirmacion', $idTurnoOriginal);

        // ENVIO MENSAJITO DE WHATSAPP 
        if($general["wappApi_confirmacion"] || $general['wappApi']==2){
            NotificationWhatsapp::confirmacion($idTurnoOriginal);
        }

        // CREDITOS
        if($general["creditos"] && Migration::existTableInDB('creditos_pacientes')){
            db_update("UPDATE creditos_pacientes SET disponible = (disponible - 1) WHERE idPaciente = {$paciente}");
        }

        die("OK");
    }

    public static function update($id, $fecha)
    {
        global $row;

        db_query(0, "select t.duracion, tu.fechaInicio, tu.fechaFin from tratamientos t, ordenes o, turnos tu where t.idTratamiento=o.idTratamiento and o.idOrden=tu.idOrden and tu.idTurno='" . $id . "'  limit 1");
        $minutos = DateController::minutesDiff($row["fechaInicio"], $row["fechaFin"]);

        $fechaInicio = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $fecha)));
        $fechaFin = date("Y-m-d H:i:s", strtotime($fechaInicio . ' +' . $minutos . ' minutes'));

        $varsTurno['tabla'] = 'turnos';
        $varsTurno['fechaInicio'] = $fechaInicio;
        $varsTurno['fechaFin'] = $fechaFin;
        $varsTurno['id'] = $id;
        $varsTurno['idLabel'] = 'idTurno';
        $varsTurno['accion'] = 'actualizarTurno a '. date("d/m/Y H:i", strtotime(str_replace('/', '-', $fecha)));

        db_edit($varsTurno);

        die("OK");
    }

    public static function delete($id)
    {
        GLOBAL $general;
        
        $varsDelete = $_POST;
        $varsDelete['tabla'] = 'turnos';
        $varsDelete['idLabel'] = 'idTurno';
        $varsDelete['id'] = $id;
        $varsDelete['accion'] = 'eliminarTurno';
        $varsDelete['eliminado'] = 1;
        
        db_edit($varsDelete);

        //  CREDITOS
        if($general["creditos"] && Migration::existTableInDB("creditos_pacientes")){
            $dataTurno = db_getOne("SELECT * FROM turnos WHERE idTurno = {$id}");
            db_update("UPDATE creditos_pacientes SET disponible = disponible + 1 WHERE idPaciente = {$dataTurno->idPaciente} ");
        }
        die("OK");
    }

    public static function getsobreTurno()
    {
        global $tot, $res, $row, $general;

        $response['profesionales'] = '<option value="">Seleccione...</option>';

        $fechaFormateada = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $_POST['fecha']) . ' ' . $_POST['hora'] . ':' . $_POST['minuto'] . ':00'));
        $profesionalesOcupados = ProfesionalController::getProfesionalesOcupados($fechaFormateada);

        if($general['simultaneosPorServicio']){
            $profesionalesOcupados = [];
        }

        $fechaFormateadaAString = strtolower(Datecontroller::daysToDias(date('l', strtotime(str_replace('/', '-', $_POST['fecha'])))));
        $fechaFormateadaSoloDias = date('Y-m-d', strtotime(str_replace('/', '-', $_POST['fecha'])));
        $horario = $_POST['hora'] . ":" . $_POST['minuto'];

        db_query(
            0,
            "SELECT 
                h.idProfesional, 
                p.nombre 
            FROM 
                horariosprofesionales h, 
                profesionales p 
            WHERE 
                (
                    (h.desdeManana<='" . $horario . "' AND h.hastaManana>='" . $horario . "') OR 
                    (h.desdeTarde<='" . $horario . "' and h.hastaTarde>='" . $horario . "')
                ) AND 
                (
                    (h.dia = '" . $fechaFormateadaAString . "' AND h.idHoras in (select max(idHoras) from horariosprofesionales group by idProfesional, dia) AND p.tipo='H') OR 
                    (h.fechaEspecifica='" . $fechaFormateadaSoloDias . "' AND p.tipo='P')
                ) AND 
                p.idProfesional = h.idProfesional 
            ORDER BY p.nombre ASC
        "
        );

        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();

            if (@!$profesionalesOcupados[$row['idProfesional']]) {
                $response['profesionales'] .= '<option value="' . $row['idProfesional'] . '"';
                if ($_POST['idProfesional'] == $row['idProfesional']) {
                    $response['profesionales'] .= ' selected';
                }
                $response['profesionales'] .= '>' . $row['nombre'] . '</option>';
            }
        }

        HTTPController::responseInJSON($response);
        die();
    }

    // estaAbierto
    public static function estadoAbierto()
    {
        global $general;

        $fecha = $_POST['fecha'];
        /* Al mover un turno en el calendario se envia por POST la fecha en un array (de año, mes, dia, hora, minutos y segundos) */
        if (is_array($fecha)) {
            $fecha = $fecha[0] . "-" . ($fecha[1] + 1) . "-" . $fecha[2] . " " . $fecha[3] . ":" . $fecha[4];
        }

        $fechaFormateada = date("Y-m-d", strtotime($fecha));

        $isFeriado = FeriadoController::isFeriado($fechaFormateada);
        if ($isFeriado["flag"]) {
            $response['status'] = 'FERIADO';
            $response['titulo'] = 'Feriado';
            $response['nombre'] = $isFeriado['nombre'];
            HTTPController::responseInJSON($response);
            die();
        }

        $isBloqueado = isBloqueado($fecha, $_POST['idProfesional'] ?? '');
        if ($isBloqueado['flag']) {
            $response['status'] = 'FERIADO';
            $response['titulo'] = 'No disponible';
            $response['nombre'] = $isBloqueado['descripcion'];
            HTTPController::responseInJSON($response);
            die();
        }

        // Chequeo que esté dentro del horario del profesional
        if($_POST["idProfesional"]){
            $dentroDelHorario = false;
            $horaDelNuevoTurno = date("H:i", strtotime($fecha));
            foreach (self::getHorariosProfesionales($fechaFormateada, $_POST["idProfesional"]) as $horario) {
                if($horario["desde"]<=$horaDelNuevoTurno && $horario["hasta"]>$horaDelNuevoTurno){
                    $dentroDelHorario = true;
                    break;
                }
            }
            if(!$dentroDelHorario){
                HTTPController::responseInJSON(array("status" => "CERRADO"));
                die();
            }
        }

        $profesionalesDisponibles = ProfesionalController::cantidadDeProfesionalesPorHorario($fecha);

        if ($general['turnosSimultaneos'] > 1) {
            $profesionalesDisponibles = $general['turnosSimultaneos'];
        }

        $filtroTurno = '';

        $turnosTomados = self::cantidadDeTurnosPorHorario($fecha, $filtroTurno);

        //Esta comparación verifica que si va a haber más turnos que profesionales en el mismo horario no se pueda agregar.
        $hayLugar = $turnosTomados >= $profesionalesDisponibles ? 0 : 1;

        
        if (!$general['turnosPasado']) {
            if (date("Y-m-d H:i:s", strtotime($fecha)) < date("Y-m-d H:i:s")) {
                $hayLugar = 0;
            }
        }
        /* Util::printVar([$profesionalesDisponibles, $turnosTomados, $hayLugar], '186.138.206.135', true); */
        
        $response['status'] = $profesionalesDisponibles > 0 && $hayLugar == 1 ? 'OK' : 'CERRADO';

        if($general['sobreTurno'] || $general["simultaneosPorServicio"]){
            $response['status'] = "OK";
        }

        HTTPController::responseInJSON($response);
        die();
    }

    public static function cantidadDeTurnosPorHorario($fecha, $filtroTurno = '')
    {
        global $tot;
        $fechaFormateada = date("Y-m-d H:i:s", strtotime($fecha));
        db_query(
            0,
            "SELECT 
                * 
            FROM 
                turnos 
            WHERE 
                fechaInicio <= '" . $fechaFormateada . "' AND 
                fechaFin > '" . $fechaFormateada . "' AND 
                estado <> 3 AND 
                estado <> 9 AND 
                eliminado <> 1 " . $filtroTurno
        );

        return $tot;
    }

    /* 
        Revisar codigo para mejora
    */
    public static function sePuedeMover()
    {
        global $tot, $row, $general;
        $todoOk = 0;

        $fecha = $_POST['fecha'];
        foreach ($fecha as $key => $valor) {
            $fechas[$key] = sprintf("%02d", $valor);
        }

        if ($fechas[0] . '-' . sprintf("%02d", ($fechas[1] + 1)) . '-' . $fechas[2] . ' ' . $fechas[3] . ':' . $fechas[4] . ':' . $fechas[5] > date("Y-m-d H:i:s")) {
            $todoOk++;
        }

        /* Util::printVar([$todoOk, $fechas], "138.121.84.107", false); */
        
        db_query(0, "select t.idTurno, t.fechaInicio, o.idProfesional from turnos t, ordenes o where t.idTurno='" . $_POST['idTurno'] . "' and t.estado=0 and t.idOrden=o.idOrden LIMIT 1");
        if ($row["fechaInicio"] > date("Y-m-d H:i:s") || $general["turnosPasado"]) {
            $todoOk++;
        }

        /* Util::printVar([$todoOk, $tot, $row], "138.121.84.107", false); */

        //Voy a verificar también si el profesional asignado atiende en ese horario. 
        $comparacion = "((desdeTarde<='" . $fechas[3] . ":" . $fechas[4] . "' and hastaTarde>='" . $fechas[3] . ":" . $fechas[4] . "' and desdeTarde<>'' and hastaTarde<>'') or (desdeManana<='" . $fechas[3] . ":" . $fechas[4] . "' and hastaManana>='" . $fechas[3] . ":" . $fechas[4] . "' and desdeManana<>'' and hastaManana<>''))";

        $consulta = "SELECT idHoras from horariosprofesionales where dia='" . strtolower(DateController::daysToDias(date('l', strtotime($fecha[0] . '-' . ($fecha[1] + 1) . '-' . $fecha[2])))) . "' and idProfesional='" . $row['idProfesional'] . "' and (" . $comparacion . ") and idHoras in(select max(idHoras) from horariosprofesionales group by idProfesional, dia)";

        db_query(0, $consulta);
        if ($tot > 0) {
            $todoOk++;
        }

        /* Util::printVar([$todoOk, $tot, $consulta], "138.121.84.107", true); */

        if ($todoOk == 3) {
            die("OK");
        } else {
            die("CERRADO");
        }
    }

    public static function cambiarProfesionalEnLaOrdenConIdTurno($turno, $idProfesional)
    {
        global $row;
        db_query(0, "select idOrden from turnos where idTurno='" . $turno . "'");

        if ($tot > 0) {
            $vars['tabla'] = 'ordenes';
            $vars['idLabel'] = 'idOrden';
            $vars['id'] = $row['idOrden'];
            $vars['idProfesional'] = $idProfesional;
            $vars['accion'] = 'cambiarProfesionalTurno';
            db_edit($vars);
        }
        die("OK");
    }

    //Cambia el estado de turno a confirmado o ausente y cancela turnos posteriores si tuvo max de X ausentes
    public static function estadoTurno($idTurno, $estado, $confirmo)
    {
        GLOBAL $row, $row1, $row2, $tot1;

        if ($estado == 1) {
            $leyendaLog = 'asistioTurno';
        }
        if ($estado == 2) {
            $leyendaLog = 'ausenteTurno';

            //Como está marcando como ausente, voy a ir a verificar si el turno anterior también fue ausente. Si es así, cancelo todos los turnos siguientes.
            db_query(0, "select idPaciente, idOrden, fechaInicio from turnos where idTurno='" . $idTurno . "' limit 1");

            db_query(1, "select idTurno, estado from turnos where idPaciente='" . $row['idPaciente'] . "' and idOrden='" . $row['idOrden'] . "' and fechaInicio<'" . $row['fechaInicio'] . "' order by fechaInicio DESC limit 1");

            if ($tot1 > 0) {
                if ($row1['estado'] == 2) {

                    $response['turnoAnterior'] = $row1['idTurno'];
                    $response['estado'] = $row1['estado'];

                    //El turno anterior también estuvo ausente
                    if ($confirmo) {
                        db_update("update turnos set estado=3 where estado=0 and idOrden='" . $row['idOrden'] . "' and idPaciente='" . $row['idPaciente'] . "'");
                    } else {
                        $response['status'] = 'dosAusentes';

                        HTTPController::responseInJSON($response);
                        die();
                    }
                }
            }
        }
        $varsTurno['tabla'] = 'turnos';
        $varsTurno['idLabel'] = 'idTurno';
        $varsTurno['id'] = $idTurno;
        $varsTurno['estado'] = $estado;
        $varsTurno['accion'] = $leyendaLog;

        db_edit($varsTurno);


        //Todo esto que viene no nos importa.
        db_query(0, "select idOrden from turnos where idTurno='" . $idTurno . "'");

        $idOrden = $row['idOrden'];

        db_query(1, "select count(idTurno) as tomados, idOrden from turnos where idOrden='" . $idOrden . "' and (estado=1 or estado=2) group by idOrden");
        db_query(2, "select cantidad from ordenes where idOrden='" . $idOrden . "'");

        if ($row1['tomados'] == $row2['cantidad']) {

            $varsOrden['tabla'] = 'ordenes';
            $varsOrden['idLabel'] = 'idOrden';
            $varsOrden['id'] = $idOrden;
            $varsOrden['accion'] = 'finalizarOrdenAuto';
            $varsOrden['estado'] = 1;

            db_edit($varsOrden);
        }

        $response['status'] = 'OK';

        HTTPController::responseInJSON($response);
        die();
    }

    public static function checkUpdate()
    {

        AuthController::checkLogin();

        global $res, $row, $tot, $row1, $tot1, $res1;

        db_query(0, "select fechahora, accion, id, usuario from log where accion like '%turno%' and fechahora>'" . $_SESSION['turnos']['lastUpdate'] . "' order by fechahora DESC limit 1");

        if ($tot > 0) {
            $_SESSION['turnos']['lastUpdate'] = $row['fechahora'];

            $updates = array();

            for ($i = 0; $i < $tot; $i++) {
                $nres = $res->data_seek($i);
                $row = $res->fetch_assoc();

                /* Cambio de t.fechahora por t.fechaInicio */
                db_query(1, "select p.nombre, p.apellido, t.fechaInicio as fechahora, t.estado from pacientes p, turnos t where idTurno='" . $row['id'] . "' and t.idPaciente=p.idPaciente");

                $esteUpdate = array();

                switch ($row['accion']) {
                    case 'agregarTurno':
                        //Agregado por el admin
                        $esteUpdate['titulo'] = 'Nuevo turno';
                        $esteUpdate['tipo'] = 'nuevo';
                        $esteUpdate['texto'] = '<b>' . $row['usuario'] . '</b> agregó un turno para <b>' . $row1['nombre'] . ' ' . $row1['apellido'] . '</b> el día ' . date("d/m/Y", strtotime($row1['fechahora'])) . ' a las ' . date("H:i", strtotime($row1['fechahora'])) . ' hs.';
                        break;

                    case 'agregarTurnoMultiple':
                        //Agregados multiples por el admin
                        $esteUpdate['titulo'] = 'Nuevos turno múltiples';
                        $esteUpdate['tipo'] = 'nuevo';
                        $esteUpdate['texto'] = '<b>' . $row['usuario'] . '</b> agregó múltiples turnos para <b>' . $row1['nombre'] . ' ' . $row1['apellido'] . '</b>.';
                        break;

                    case 'actualizarTurno':
                        //Agregados multiples por el admin
                        $esteUpdate['titulo'] = 'Turno actualizado';
                        $esteUpdate['tipo'] = 'nuevo';
                        $esteUpdate['texto'] = '<b>' . $row['usuario'] . '</b> actualizó un turno para <b>' . $row1['nombre'] . ' ' . $row1['apellido'] . '</b>.';
                        break;

                    case 'asistioTurno':
                        //Agregados multiples por el admin
                        // $esteUpdate['titulo']='Turno asistido';
                        // $esteUpdate['tipo']='confirmado';
                        // $esteUpdate['texto']='<b>'.$row['usuario'].'</b> confirmó que <b>'.$row1['nombre'].' '.$row1['apellido'].'</b> asistió a su turno el día '.date("d/m/Y",strtotime($row1['fechahora'])).' a las '.date("H:i",strtotime($row1['fechahora'])).' hs.';
                        break;

                    case 'ausenteTurno':
                        //Agregados multiples por el admin
                        // $esteUpdate['titulo']='Turno ausente';
                        // $esteUpdate['tipo']='ausente';
                        // $esteUpdate['texto']='<b>'.$row['usuario'].'</b> confirmó que <b>'.$row1['nombre'].' '.$row1['apellido'].'</b> no asistió a su turno el día '.date("d/m/Y",strtotime($row1['fechahora'])).' a las '.date("H:i",strtotime($row1['fechahora'])).' hs.';
                        break;

                    case 'cancelarTurno':
                        //Agregados multiples por el admin
                        $esteUpdate['titulo'] = 'Turno cancelado';
                        $esteUpdate['tipo'] = 'cancel';
                        $esteUpdate['texto'] = '<b>' . $row['usuario'] . '</b> canceló un turno para <b>' . $row1['nombre'] . ' ' . $row1['apellido'] . '</b> el día ' . date("d/m/Y", strtotime($row1['fechahora'])) . ' a las ' . date("H:i", strtotime($row1['fechahora'])) . ' hs.';
                        break;

                    case 'cancelarTurnoWeb':
                        //Agregados multiples por el admin
                        $esteUpdate['titulo'] = 'Turno cancelado';
                        $esteUpdate['tipo'] = 'cancel';
                        $esteUpdate['texto'] = '<b>' . $row1['nombre'] . ' ' . $row1['apellido'] . '</b> canceló su turno del día ' . date("d/m/Y", strtotime($row1['fechahora'])) . ' a las ' . date("H:i", strtotime($row1['fechahora'])) . ' hs.';
                        break;

                    case 'cargarMultiplesTurnos':
                        //Agregados multiples por el admin
                        $esteUpdate['titulo'] = 'Turnos cargados';
                        $esteUpdate['tipo'] = 'nuevo';
                        $esteUpdate['texto'] = '<b>' . $row['usuario'] . '</b> cargó múltiples turnos para <b>' . $row1['nombre'] . ' ' . $row1['apellido'] . '</b>.';
                        break;

                    case 'esperaTurno':
                        //Agregados multiples por el admin
                        $esteUpdate['titulo'] = 'Turno en espera';
                        $esteUpdate['tipo'] = 'espera';
                        $esteUpdate['texto'] = '<b>' . $row['usuario'] . '</b> confirmó que <b>' . $row1['nombre'] . ' ' . $row1['apellido'] . '</b> está en espera de su turno del ' . date("d/m/Y", strtotime($row1['fechahora'])) . ' a las ' . date("H:i", strtotime($row1['fechahora'])) . ' hs.';
                        break;

                    case 'turnoWeb':
                        //Agregados multiples por el admin
                        $esteUpdate['titulo'] = 'Turno web';
                        $esteUpdate['tipo'] = 'nuevo';
                        $esteUpdate['texto'] = '<b>' . $row1['nombre'] . ' ' . $row1['apellido'] . '</b> solicitó un turno para el día ' . date("d/m/Y", strtotime($row1['fechahora'])) . ' a las ' . date("H:i", strtotime($row1['fechahora'])) . ' hs.';
                        break;
                }

                if (@sizeof($esteUpdate)) {
                    $updates[] = $esteUpdate;
                }
            }

            $response['updates'] = $updates;
            $response['status'] = 'update';
        } else {
            $response['status'] = 'OK';
        }

        HTTPController::responseInJSON($response);
    }
    
    public static function getInfoPaciente($idPaciente, $idOrden)
    {
        global $row;
        db_query(
            0,
            "SELECT 
                p.nombre, 
                p.apellido, 
                o.diagnostico 
            FROM 
                pacientes p, 
                ordenes o 
            WHERE 
                o.idPaciente='" . $idPaciente . "' AND 
                o.idOrden='" . $idOrden . "' AND 
                p.idPaciente=o.idPaciente
        "
        );

        $response['paciente'] = $row['apellido'] . ' ' . $row['nombre'];
        $response['orden'] = $row['diagnostico'];

        HTTPController::responseInJSON($response);
        die();
    }

    public static function saveMultiples($fechas, $idOrden, $idPaciente)
    {
        $fechas = json_decode($_POST['fechas']);

        foreach ($fechas as $index => $fecha) {
            $fechaFormateada = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $fecha)));
            db_insert("insert into turnos (idPaciente, idOrden, fechaInicio, estado) values ('" . $idPaciente . "', '" . $idOrden . "', '" . $fechaFormateada . "', 0)");
        }

        db_log($_SESSION['usuario']['nombre'], 'cargarMultiplesTurnos', $idOrden);

        $response['status'] = 'OK';

        HTTPController::responseInJSON($response);
        die();
    }





    public static function saveExternal()
    {  
        //Util::printVar($_POST, "190.31.195.57");

        global $res, $row, $tot, $newid, $general;
        GLOBAL $row13, $tot13, $res13;
        
        if (isset($general["ordenConImagen"]) && $general["ordenConImagen"]) {
            // Validar usando un variable de config en la db
            $nameFolderApp = explode(".", $general['clientDomain'])[0];
            $pathFolderApp = __DIR__ . "/../../applications/" . $nameFolderApp . "/public_html/img/ordenes";
            // Creo la carpeta si no existe
            if (isset($_FILES) && $_FILES['orden']['name']) {
                /* Util::printVar('algo---', '186.138.206.135'); */
                if (!is_dir($pathFolderApp)) {
                    mkdir($pathFolderApp);
                }
                // Validar extension
                $ext = pathinfo($_FILES['orden']['name'], PATHINFO_EXTENSION);
                if ($ext != "jpg" && $ext != "png" && $ext != "pdf") {
                    $response['status'] = 'formatoDeArchivoIncorrecto';
                    $response['message'] = 'Formatos de archivo permitidos: jpg, png, pdf';
                    HttpController::responseInJSON($response);
                    die();
                }
            }
        }

        if ($_POST['idPaciente'] <> '') {
            $resPaciente = PacienteController::updateExternal($_POST['idPaciente'], $_POST["mail"]);
            $paciente = $resPaciente['paciente'];
            $nombrePaciente = $resPaciente['nombrePaciente'];
        } else {

            if (($_POST['nombre']) and ($_POST['apellido'])) {

                $telefono = isset($_POST['telefono']) ? Util::limpiarTelefono($_POST['telefono']) : '';

                // Busco al usuario en base al campo que usa para logearse
                switch ($general['tomaTurno']) {
                    case 'email':
                        $findPaciente = PacienteController::getIdPacientePorEmail($_POST["mail"]);
                        break;
                    case 'dni':
                        $findPaciente = PacienteController::getIdPacientePorDni($_POST["dni"]);
                        break;
                    case 'telefono':
                        $findPaciente = PacienteController::getIdPacientePorTelefono($_POST["telefono"]);
                        break;
                    
                    default:
                        $findPaciente = null;
                        break;
                }
                
                if ($findPaciente) {
                    $paciente = isset($findPaciente["idPaciente"]) ? $findPaciente["idPaciente"] : $findPaciente;

                    db_update("UPDATE pacientes set nombre='" . $_POST['nombre'] . "', apellido='" . $_POST['apellido'] . "', codArea='" . $_POST['codArea'] . "', telefono='" . $telefono . "', dni='" . $_POST['dni'] . "', mail='" . $_POST['mail'] . "', observaciones='".$_POST['observaciones']."' where idPaciente='" . $paciente . "'");
                } else {
                    db_insert("insert into pacientes (nombre, apellido, codArea, telefono, mail, dni, observaciones) values ('" . $_POST['nombre'] . "', '" . $_POST['apellido'] . "', '" . $_POST['codArea'] . "', '" . $telefono . "', '" . $_POST['mail'] . "', '" . $_POST['dni'] . "', '" . $_POST['observaciones'] . "')");
                    $paciente = $newid;

                    // CREDITOS
                    if($general["creditos"] && Migration::existTableInDB("creditos_pacientes")){
                        db_insert("INSERT INTO creditos_pacientes (idPaciente, idPlan, disponible) VALUES ({$paciente}, 0, 0) ");
                    }
                }


                $nombrePaciente = $_POST['nombre'];
                $orden = '';
            } else {
                $response['status'] = 'datosIncompletos';
                HTTPController::responseInJSON($response);
                die();
            }
        }

        /* ----------------------------------------------------------------------------- */
        /*          CHEQUEO QUE EL PACIENTE NO TENGA UN TURNO EN EL MISMO HORARIO        */
        /* ----------------------------------------------------------------------------- */
        db_query(0, "select fechaInicio from turnos where idPaciente='" . $paciente . "' and (fechaInicio<='" . date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $_POST['fecha']) . ' ' . $_POST['horas'] . ':00')) . "' and estado<>3 and eliminado<>1 and fechaFin>'" . date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $_POST['fecha']) . ' ' . $_POST['horas'] . ':00')) . "') limit 1");
        if ($tot > 0) {
            $response['status'] = 'duplicado';
            $response['nombre'] = $nombrePaciente;
            $response['fecha'] = date("d/m/Y H:i", strtotime($row['fechaInicio']));
            HTTPController::responseInJSON($response);
            die();
        }

        /* ------------------------------------------------ */
        /*      BLOQUEO DE TRATAMIENTOS POR PACIENTE        */
        /* ------------------------------------------------ */
        if($general['bloquearTurnosAPacientesPorTratamiento']){
            
            $tratamientosBloqueadosPorPaciente=[];
            db_query(13, "SELECT * FROM pacientes_tratamientos WHERE idPaciente = '{$paciente}'");
            for ($i = 0; $i < $tot13; $i++) {
                $nres13 = $res13->data_seek($i);
                $row13 = $res13->fetch_assoc();
                $tratamientosBloqueadosPorPaciente[] = $row13['idTratamiento'];
            }
            /* Util::printVar($_POST, '186.138.206.135', false);
            Util::printVar("Bloqueado: ".is_numeric(array_search($_POST['idTratamiento'], $tratamientosBloqueadosPorPaciente)), '186.138.206.135', true); */

            // Busco que el tratamiento del turno no coincida con un tratamiento bloqueado
            if(is_numeric(array_search($_POST['idTratamiento'], $tratamientosBloqueadosPorPaciente))){
                db_query(13,"SELECT * FROM tratamientos WHERE idTratamiento = '".$_POST['idTratamiento']."' LIMIT 1");
                $response['status'] = 'tratamientoBloqueado';
                $response['message'] = 'No tiene permiso para sacar turnos en: '.$row13['nombre'];
                HTTPController::responseInJSON($response);
                die();
            }
        }
        /* ------------------------------------------------ */
        /*          End Bloqueo de tratamiento              */
        /* ------------------------------------------------ */

        
        /* ------------------------ */
        /*          CREDITOS        */
        /* ------------------------ */
        if($general['creditos']){
            // Consulto cuantos disponibles tiene
            if(!self::checkCreditosDisponibles($paciente)){
                $response['status'] = 'sinCreditos';
                $response['message'] = 'No posees suficientes créditos disponibles para tomar este '.$general['nombreTurno'];
                HTTPController::responseInJSON($response);
                die();
            }
        }
        /* ---------------------------- */
        /*          END CREDITOS        */
        /* ---------------------------- */



        $profesional = $_POST['idProfesional'];
        if (!$profesional) {
            $response['status'] = 'tomado';
            HTTPController::responseInJSON($response);
            die();
        }


        /* ------------------------------------------------- */
        /*      CHEQUEO SIMULTANEOS POR TURNO O SERVICIO     */
        /* ------------------------------------------------- */
        $duracion = TratamientoController::getDuracionDelTratamiento($_POST['idTratamiento'], $profesional);
        $simultaneosDelServicio = TratamientoController::getSimultaneosDelTratamiento($_POST['idTratamiento'], $profesional);
        $simultaneos = $simultaneosDelServicio;


        if ($simultaneos < 2) {
            $simultaneos = 1;
        }
        if(@$general['turnosSimultaneos'] > 1){
            $simultaneos = $general['turnosSimultaneos'];
        }
        
        // Si tiene activado los simultaneos por servicio piso los turnosSimultaneos
        if($general["simultaneosPorServicio"]){
            $simultaneos = $simultaneosDelServicio;
            if(!self::checkSimultaneosPorServicio(date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $_POST['fecha']) . ' ' . $_POST['horas'] . ':00')), $profesional, $_POST["idTratamiento"])){
                $response['status'] = 'tomado';
            HTTPController::responseInJSON($response);
            die();
            }
        }else{
            //Verifico por si acaso si no hay un turno en el mismo horario pero con otro paciente o si hay un nuevo bloqueo o lo que sea.
            if (self::getHoursEspecifica(date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $_POST['fecha']) . ' ' . $_POST['horas'] . ':00')), $profesional, $duracion, $simultaneos)) {
                $response['status'] = 'tomado';
                HTTPController::responseInJSON($response);
                die();
            }
        }

        /* Util::printVar([$simultaneos, $general['turnosSimultaneos'], $general["simultaneosPorServicio"]], "190.231.245.234"); */

        

        //El estado del turno va a estar definido por la pasarela de pagos activa o no y si mando una opción de pago.
        $estadoTurno = 0;

        if (@$general['mercadoPago'] || $general['paypal']) {
            $estadoTurno = 9;

            //voy a chequear si intenta pagar seña y tengo valor de seña, sino le doy error
            if ($_POST['pago'] == 'sena') {

                if (($general['mercadoPago'] && @$general['mercadoPago_sena'] < 1) && ($general['mercadoPago'] && !$general['mercadoPago_servicios_sena']) && ($general['paypal'] && $general['paypal_sena'] < 1)) {
                    $response['status'] = 'error';
                    $response['message'] = 'No se puede pagar seña porque no se ha definido un valor de seña.';
                    HTTPController::responseInJSON($response);
                    die();
                }
            }

            if (($_POST['pago'] == 'turno') and (@$general['mercadoPago_servicios'] < 1) && $general['mercadoPago']) {
                $response['status'] = 'error';
                $response['message'] = 'No se puede pagar el turno porque solo se permite seña.';
                HTTPController::responseInJSON($response);
                die();
            }
        }

        // Si el paciente tiene turnos y la varible de solo cobro está desactivada no le cobro
        if($estadoTurno == 9 && $general["mercadoPago_soloPrimerTurno"]){
            $turnosDelPaciente = db_getAll("SELECT * FROM turnos WHERE idPaciente = {$paciente} ");
            if(count($turnosDelPaciente) > 0 ){
                $estadoTurno = 0;
            }
        }


        $estadoTurno = AuthController::isAdmin() ? 0 : $estadoTurno;

        Util::printVar($estadoTurno, "190.31.195.57");
        /* 
            Hago los inserts
        */
        // Actualizo la orden. No utilizo el metodo db_edit porque no hace el insert en el log
        db_insert("insert into ordenes (idPaciente, idProfesional, cantidad, idTratamiento, fechaAlta) values ('" . $paciente . "', '" . $profesional . "', '1', '" . $_POST['idTratamiento'] . "', '" . date("Y-m-d H:i:s") . "')");
        $orden = $newid;

        $fechaInicio = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $_POST['fecha']) . ' ' . $_POST['horas'] . ':00'));
        $fechaFin = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $_POST['fecha']) . " " . $_POST['horas'] . ":00" . " +" . $duracion . " minutes"));
        $varsTurno['tabla'] = 'turnos';
        $varsTurno['accion'] = 'agregarTurnoWeb';
        $varsTurno['usuarioLog'] = 'homeTurnos';
        $varsTurno['idPaciente'] = $paciente;
        $varsTurno['idOrden'] = $orden;
        $varsTurno['fechaInicio'] = $fechaInicio;
        $varsTurno['fechaFin'] = $fechaFin;
        $varsTurno['estado'] = $estadoTurno;
        if (isset($general["public_textAreaTurno"]) && $general["public_textAreaTurno"]) {
            $varsTurno['observacion'] = $_POST["observaciones"];
        }
        
        $idTurno = db_edit($varsTurno);

        $response['textoConfirmacionPublic'] = '';

        if (isset($general["campoObservaciones"]) && $_POST["comentarios"]) {
            self::saveObservaciones($idTurno, $_POST["comentarios"]);
        }
        
        
        // Verifico si tiene que pagar y lo mando a la pasarela, sino mando el mail de confirmación.
        if ($estadoTurno == 9) {
            global $row11;

            // Verifico el metodo de pago en base al tratamiento
            db_query(11, "SELECT * FROM tratamientos WHERE idTratamiento = '" . $_POST['idTratamiento'] . "'");
            switch ($row11['pago']) {
                    // Mercado pago
                case 'MP':
                    $response['metodoDePago'] = 'MP';
                    $buscar=array(
                        '%nombre%',
                        '%limite%'
                    );
                    $reemplazar=array(
                        $nombrePaciente,
                        $general['mercadoPago_limite']
                    );
                    $response['textoConfirmacionPublic']=str_replace($buscar,$reemplazar,$general['mercadoPago_textoConfirmacion']);

                    $pago = $_POST['pago'];
                    
                    //Genero link de pago
                    $response['aPagar'] = MercadoPago::generarLinkPago($idTurno, $pago, $_POST['idTratamiento']);
                    break;

                    // Paypal
                case 'PP':
                    $paypal_sena = intval($general['paypal_sena']);

                    if ($paypal_sena > 0) {
                        $response['valorDePago'] = $paypal_sena;
                    } else {
                        // Busco precio del tratamiento
                        db_query(
                            12,
                            "SELECT
                                tv.cantidad
                            FROM
                                tratamientos trat,
                                tratamientos_valores tv,
                                turnos t,
                                ordenes o
                            WHERE 
                                t.idOrden = o.idOrden AND
                                o.idTratamiento = trat.idTratamiento AND
                                trat.idTratamiento = tv.idTratamiento AND
                                t.idTurno = ".$idTurno." 
                        "
                        );
                        $response['valorDePago'] = $row12['cantidad'];
                    }

                    $response['metodoDePago'] = 'PP';
                    $response['idTurno'] = $idTurno;
                    $buscar = array(
                        '%nombre%'
                    );
                    $reemplazar = array(
                        $nombrePaciente
                    );
                    $response['textoConfirmacionPublic'] = str_replace($buscar, $reemplazar, $general['paypal_textoConfirmacion']);
                    
                    db_insert("INSERT INTO pagos (idTurno, cantidadPago) VALUES ($idTurno, '{$response["valorDePago"]}')
                    ON DUPLICATE KEY UPDATE cantidadPago='{$response["valorDePago"]}'");

                    break;

                default:
                    $response['status'] = 'error';
                    $response['message'] = 'El tratamiento no tiene un metodo de pago';
                    HTTPController::responseInJSON($response);
                    die();
                    break;
            }
        } else {
            //ENVIAR CONFIRMACION POR MAIL
            require_once($_SERVER["DOCUMENT_ROOT"] . '/inc/mailConfirmacion.php');
            db_log('homeTurnos', 'mandoMailConfirmacion', $idTurno);

            // ENVIO MENSAJITO DE WHATSAPP 
            if($general["wappApi_confirmacion"] || $general['wappApi']==2){
                NotificationWhatsapp::confirmacion($idTurno);
            }

            if ($general['textoConfirmacionPublic']) {
                $buscar = array(
                    '%nombre%',
                    '%horasCancelacion%',
                    '%fechaTurno%'
                );
                $reemplazar = array(
                    $nombrePaciente,
                    $general['hsAntesCancelacion'],
                    $response['fecha']
                );
                $response['textoConfirmacionPublic'] = str_replace($buscar, $reemplazar, $general['textoConfirmacionPublic']);
            }

            // CREDITOS
            if($general["creditos"] && Migration::existTableInDB('creditos_pacientes')){
                db_update("UPDATE creditos_pacientes SET disponible = (disponible - 1) WHERE idPaciente = {$paciente}");
            }
        }

        $response['status'] = 'OK';
        $response['nombre'] = ucfirst($nombrePaciente);
        $response['fecha'] = $_POST['fecha'] . ' ' . $_POST['horas'];
        $response['fechaTurno'] = $_POST['fecha'];
        $response['horaTurno'] = $_POST['horas'];


        /* Archivo */
        if (isset($general["ordenConImagen"]) && $general["ordenConImagen"]) {
            if (isset($_FILES) && $_FILES['orden']['name']) {
                $ext = pathinfo($_FILES['orden']['name'], PATHINFO_EXTENSION);
                $nombreComprobante = 'orden_' . $orden . '.' . $ext;

                if (move_uploaded_file($_FILES['orden']['tmp_name'], $pathFolderApp . "/" . $nombreComprobante)) {
                    db_update("update ordenes set imagen='" . $nombreComprobante . "' WHERE idOrden='" . $orden . "'");
                }
            }
        }

        /* Actualizo el campo razon social del paciente - App argroup */
        $razonSocial = isset($_POST['razonSocial']) ? $_POST['razonSocial'] : '';
        if ($razonSocial) {
            db_update("UPDATE pacientes set razonSocial = '" . $razonSocial . "' where idPaciente='" . $paciente . "'");
        }

        HTTPController::responseInJSON($response);
    }






    public static function cambiarEstadoDeTurno($idTurno, $estado, $accion, $usuarioLog = '')
    {
        GLOBAL $general;
        GLOBAL $row1, $res1, $tot1;

        $varsTurno['tabla'] = 'turnos';
        $varsTurno['idLabel'] = 'idTurno';
        $varsTurno['id'] = $idTurno;
        $varsTurno['estado'] = $estado;
        $varsTurno['accion'] = $accion;
        if ($usuarioLog != '') {
            $varsTurno['usuarioLog'] = $usuarioLog;
        }

        
        /* 
            Guardo los siguientes datos en la tabla de whatsapp_turnosCancelados
                - telefono completo
                - idTurno
                - mensaje
        */
        if($general["guardarTurnosCancelados"] && $estado == '3'){
            db_query(1, 
                "SELECT 
                    t.*, 
                    p.*
                FROM 
                    turnos t,
                    pacientes p
                WHERE 
                    t.idTurno = '{$idTurno}' AND 
                    t.idPaciente = p.idPaciente 
                LIMIT 1
            ");
            $telefono=$general['prefijoTelefonico'].$row1['codArea'].$row1['telefono'];
            $nombreCompleto = ucfirst($row1['nombre']). " " . ucfirst($row1["apellido"]);
            $fechaTurno = $row1["fechaInicio"];
            $mensaje = str_replace(
                ['%nombreCompleto%', '%fecha%'],
                [$nombreCompleto,date('d/m/Y H:i', strtotime($fechaTurno))],
                $general['guardarTurnosCancelados_mensajeWhatsappManual']
            );

            self::createTableTurnosCancelados();
            db_insert("INSERT INTO turnos_cancelados (telefono, mensaje,idTurno) VALUES ('{$telefono}','$mensaje', {$idTurno})");
        }

        db_edit($varsTurno);
    }

    // Cancelación de turno desde el public
    public static function cancelarTurnoExterno($idTurno)
    {
        global $general, $row, $tot, $res;

        $dataTurno = db_getOne("SELECT * FROM turnos WHERE idTurno = {$idTurno}");

        Turno::cambiarEstadoDeTurno($idTurno, 3, 'cancelarTurnoWeb', 'homeTurnos');

        //Voy a chequear si pago el turno. Si lo pago lo tengo que devolver el dinero.
        if ((@$general['mercadoPago_devolver']) and (@$general['mercadoPago'] == 1)) {
            db_query(0, "SELECT idPago FROM pagos WHERE idTurno='{$idTurno}' AND pago='1'");
            if ($row['idPago']) {
                //Hay un pago asociado y acreditado.
                MercadoPago::devolverPago($idTurno);
            }
        }

        $response['status'] = 'OK';
        
        // Enviar mail de cancelación
        Notification::mailCancelacion($idTurno);

        // Creditos
        if($general["creditos"] && Migration::existTableInDB("creditos_pacientes")){
            db_update("UPDATE creditos_pacientes SET disponible = disponible + 1 WHERE idPaciente = {$dataTurno->idPaciente} ");
        }

        HTTPController::responseInJSON($response);
    }

    //Cancela un turno y manda mail de cancelación. Por ahora solo admin
    public static function cancelarTurno($idTurno, $comentarios, $devolver = 1, $mandar = 1)
    {

        global $general, $row, $tot, $res;

        Turno::cambiarEstadoDeTurno($idTurno, 3, 'cancelarTurno');

        $varsComentarioTurno['tabla'] = 'comentariosturnos';
        $varsComentarioTurno['accion'] = 'agregarComentarios';
        $varsComentarioTurno['idTurno'] = $idTurno;
        $varsComentarioTurno['comentarios'] = $comentarios;
        $varsComentarioTurno['usuario'] = $_SESSION['usuario']['nombre'];
        db_edit($varsComentarioTurno);


        //Voy a chequear si pago el turno. Si lo pago lo tengo que devolver el dinero.
        if ((@$general['mercadoPago_devolver']) and (@$general['mercadoPago'])) {
            db_query(0, "SELECT idPago FROM pagos WHERE idTurno='{$idTurno}' AND pago='1'");
            if ($row['idPago']) {
                if ($devolver) {
                    //Hay un pago asociado y acreditado.
                    MercadoPago::devolverPago($idTurno);
                }
            }
        }

        if ($mandar) {
            // Enviar mail de cancelación
            Notification::mailCancelacion($idTurno);
        }

        // Creditos
        if($general["creditos"] && Migration::existTableInDB("creditos_pacientes")){
            $dataTurno = db_getOne("SELECT idPaciente FROM turnos WHERE idTurno = {$idTurno}");
            db_update("UPDATE creditos_pacientes SET disponible = disponible + 1 WHERE idPaciente = {$dataTurno->idPaciente}");
        }

        die("OK");
    }

    public static function cantidadDeTurnosPorFecha($fechaDesde, $fechaHasta)
    {
        global $tot;
        db_query(1, "select idTurno from turnos where date(fechaInicio)>='" . $fechaDesde . "' and date(fechaInicio)<='" . $fechaHasta . "' and estado=1 and eliminado<>1");
        return $tot;
    }
    public static function cantidadDeTurnosPorFechaYProfesional($fechaDesde, $fechaHasta, $profesional = '')
    {
        global $tot;
        $filtroProfesional = $profesional ? ' AND o.idProfesional = '.$profesional.' ' : '';
        db_query(1, "SELECT t.idTurno from turnos t, ordenes o where date(t.fechaInicio)>='" . $fechaDesde . "' and date(t.fechaInicio)<='" . $fechaHasta . "' AND t.idOrden = o.idOrden and t.estado=1 and t.eliminado<>1 {$filtroProfesional}");
        return $tot;
    }
    

    public static function getTurnosAtendidosConProfesional($fechaDesde, $fechaHasta, $suplencias, $suplenciasOtro, $idProfesional)
    {
        global $row, $tot, $res;

        $turnosAtendidos = array();
        db_query(0, "select t.*, o.idProfesional from turnos t, ordenes o where (t.estado=1 or t.estado=2) and t.eliminado<>1 and date(t.fechaInicio)>='" . $fechaDesde . "' and date(t.fechaInicio)<='" . $fechaHasta . "' and t.idOrden=o.idOrden order by t.fechaInicio ASC");
        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();

            $agregar = 0;
            if ($suplencias[$row['idTurno']]) {
                $agregar = 1;
            } else {
                if ($suplenciasOtro[$row['idTurno']]) {
                    //
                } else {
                    if ($row['idProfesional'] == $idProfesional) {
                        $agregar = 1;
                    }
                }
            }
            if ($agregar == 1) {
                $turnosAtendidos[$row['idTurno']]['idProfesional'] = $idProfesional;
                $turnosAtendidos[$row['idTurno']]['idOrden'] = $row['idOrden'];
                $turnosAtendidos[$row['idTurno']]['idPaciente'] = $row['idPaciente'];
                $turnosAtendidos[$row['idTurno']]['fecha'] = $row['fechaInicio'];
                $turnosAtendidos[$row['idTurno']]['estado'] = $row['estado'];
            }
        }

        return $turnosAtendidos;
    }

    public static function getTurnosPaciente($idPaciente)
    {
        global $row, $tot, $res;

        $misTurnos = array();
        db_query(0, "select t.idTurno, t.fechaInicio, tra.nombre as tratamiento, t.estado from turnos t, ordenes o, tratamientos tra where (t.estado<>3 and t.estado<>9) and t.eliminado<>1 and date(t.fechaInicio)>='" . date("Y-m-01", strtotime('last month')) . "' and t.idOrden=o.idOrden and tra.idTratamiento=o.idTratamiento and t.idPaciente='".$idPaciente."' order by t.fechaInicio ASC");
        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();

            if(($row['estado']==0)){
                if($row['fechaInicio']<date("Y-m-d H:i:s")){
                    $estado='pasado';
                }else{
                    $estado='cancelar';
                }
            }
            if(($row['estado']==1)){
                $estado='confirmado';
            }
            if(($row['estado']==2)){
                $estado='ausente';
            }

            $misTurnos[$row['fechaInicio']]['idTurno'] = $row['idTurno'];
            $misTurnos[$row['fechaInicio']]['fecha'] = date("d/m/Y", strtotime($row['fechaInicio']));
            $misTurnos[$row['fechaInicio']]['hora'] = date("H:i", strtotime($row['fechaInicio']));
            $misTurnos[$row['fechaInicio']]['estado'] = $estado;
            $misTurnos[$row['fechaInicio']]['tratamiento'] = $row['tratamiento'];
            
        }

        return $misTurnos;
    }

    public static function modalPedidoExternal($cuit)
    {
        global $row, $tot, $res;

        db_query(0, "select idPaciente, nombre from pacientes where dni='" . $cuit . "'");

        $nombrePaciente = $row['nombre'];
        if ($tot > 0) {

            db_query(0, "SELECT t.idTurno, t.fechaInicio, tra.nombre, t.estado FROM turnos t, tratamientos tra, ordenes o WHERE t.idPaciente='" . $row['idPaciente'] . "' AND t.estado<>3 AND tra.idTratamiento=o.idTratamiento AND t.idOrden=o.idOrden ORDER BY t.fechaInicio ASC");

            if ($tot > 0) {

                for ($i = 0; $i < $tot; $i++) {
                    $nres = $res->data_seek($i);
                    $row = $res->fetch_assoc();

                    if ($row['estado'] == 0) {
                        if ($row['fechaInicio'] > date("Y-m-d H:i:s", strtotime("+" . $general['hsAntesCancelacion'] . " hours"))) {
                            $estado = 'cancelar';
                        } else {
                            if ($row['fechaInicio'] < date("Y-m-d H:i:s")) {
                                $estado = 'pasado';
                            } else {
                                $estado = 'cancelarAntes';
                            }
                        }
                    }
                    if ($row['estado'] == 1) {
                        $estado = 'confirmado';
                    }
                    if ($row['estado'] == 2) {
                        $estado = 'ausente';
                    }

                    $response['turnos'][] = array(
                        'estado' => $estado,
                        'fecha' => date("d/m/Y", strtotime($row['fechaInicio'])),
                        'hora' => date("H:i", strtotime($row['fechaInicio'])),
                        'nombreEstudio' => $row['nombre'],
                        'idTurno' => $row['idTurno']
                    );
                }
                $response['status'] = 'OK';
                $response['nombreCliente'] = $nombrePaciente;
            } else {
                $response['status'] = 'SIN';
            }
        } else {
            $response['status'] = 'NO';
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        die();
    }

    public static function saveObservaciones($idTurno, $observaciones)
    {
        global $row, $tot, $res, $general;

        if($general['campoObservaciones']){

            db_query(0, "SHOW TABLES LIKE 'turnos_observaciones'");
            if($tot<1){
                //No existe la tabla. La creo.
                db_query(0, "CREATE TABLE `turnos_observaciones` (
                  `idTurno` int(5) NOT NULL,
                  `observaciones` tinytext NOT NULL,
                  PRIMARY KEY (`idTurno`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
            }

            db_insert("insert into turnos_observaciones (idTurno, observaciones) values ('".$idTurno."', '".$observaciones."') on duplicate key update observaciones='".$observaciones."'");

            return true;
        }else{
            return false;
        }

        
    }
    
    public static function checkCreditosDisponibles($idPaciente)
    {
        global $row, $tot, $res, $general;

        db_query(0, "SELECT disponible, fechaAlta from creditos_pacientes where idPaciente='{$idPaciente}' ");
        return $row['disponible'] > 0;

        /* db_query(0, "select count(idTurno) as tomados from turnos where idPaciente='".$idPaciente."' and (estado=1 or estado=2 or estado=0) and eliminado<>1 and date(fechaInicio)>='".date("Y-m-d", strtotime($row['fechaAlta']))."'");
        $creditos=$creditos-$row['tomados'];


        if($creditos<1){
            return false;
        }else{
            return true;
        } */
    }

    public static function createTableTurnosCancelados(){
        $migration = new Migration("turnos_cancelados");
        if($migration->existTable()) return;
        $migration->createTable("
            CREATE TABLE turnos_cancelados ( 
                idTurnoCancelado INT(5) NOT NULL AUTO_INCREMENT ,  
                telefono VARCHAR(20) NOT NULL , 
                mensaje LONGTEXT NOT NULL , 
                enviado CHAR(1) NOT NULL DEFAULT '' , 
                idTurno INT(5) NOT NULL , 
                PRIMARY KEY  (idTurnoCancelado)
            )
        ");
        return true;
    }
}
