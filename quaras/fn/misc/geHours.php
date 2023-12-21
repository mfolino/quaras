<?

class Hour{
    
    private static function overlap($fechaInicio, $fechaFin, $fechaInicio2, $fechaFin2){
        return (($fechaInicio < $fechaFin2) and ($fechaFin > $fechaInicio2));
    }


    private static function inRange($fechaInicio, $fechaFin, $fechaInicio2, $fechaFin2){
        return (($fechaInicio < $fechaInicio2) or ($fechaFin > $fechaFin2));
    }


    private static function getBloqueos($fecha, $profesional){

        global $row, $tot, $res;

        db_query(0,"SELECT fechaDesde, fechaHasta, idProfesional FROM bloqueos WHERE date(fechaDesde) <= '$fecha' AND date(fechaHasta) >= '$fecha'  AND (idProfesional  IN (".$profesional.") or idProfesional='0')");
        $bloqueos = array();

        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row = $res->fetch_assoc();

            if(date("Y-m-d",strtotime($row['fechaDesde'])) != $fecha){
                $horaDesde='00:00';
            }else{
                $horaDesde=date("H:i",strtotime($row['fechaDesde']));
            }
            
            if(date("Y-m-d",strtotime($row['fechaHasta'])) != $fecha){
                $horaHasta='23:59';
            }else{
                $horaHasta=date("H:i",strtotime($row['fechaHasta']));
            }

            $bloqueos[] = array('desde' => $horaDesde, 'hasta' => $horaHasta, 'idProfesional' => $row['idProfesional']);
        }

        return $bloqueos;

    }


    private static function getAnticipacion($fecha){

        global $general;

        $bloqueo = array();

        if(date("Y-m-d",strtotime($fecha))==date("Y-m-d",strtotime('+'.$general['horasAnticipacion'].' hours'))){
            $bloqueo=array(
                'desde'=>date("Y-m-d",strtotime($fecha)).' 00:00:00',
                'hasta'=>date("Y-m-d H:i:s",strtotime('+'.($general['horasAnticipacion']).' hours')),
                'idProfesional'=>0
            );
        }

        return $bloqueo;
    }


    private static function getTurnos($fecha, $profesional){

        global $row, $tot, $res;

        db_query(0,
        "SELECT DATE_FORMAT(t.fechaInicio,'%H:%i') as fechaInicio, DATE_FORMAT(t.fechaFin,'%H:%i') as fechaFin, o.idProfesional
        FROM turnos t, ordenes o where t.estado<>3 and date(t.fechaInicio)='".date("Y-m-d",strtotime($fecha))."' and t.idOrden=o.idOrden and o.idProfesional IN (".$profesional.")");

        $turnos = array();

        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row = $res->fetch_assoc();
            $turnos[] = array('desde' => $row['fechaInicio'], 'hasta' => $row['fechaFin'], 'idProfesional' => $row['idProfesional']);
        }

        return $turnos;

    }


    private static function getHorariosProfesionales($fecha, $profesional){

        global $row, $tot, $res;

        $diaSemana['Monday']='Lunes';
        $diaSemana['Tuesday']='Martes';
        $diaSemana['Wednesday']='Miercoles';
        $diaSemana['Thursday']='Jueves';
        $diaSemana['Friday']='Viernes';
        $diaSemana['Saturday']='Sabado';
        $diaSemana['Sunday']='Domingo';


        $diaDeLaSemana=strtolower($diaSemana[date("l",strtotime($_POST['fecha']))]);



        db_query(0,
        "SELECT hp.desdeManana, hp.hastaManana, hp.desdeTarde, hp.hastaTarde, hp.idProfesional
        FROM horariosprofesionales hp, profesionales p 
        WHERE ((dia = '{$diaDeLaSemana}' and p.tipo='H') or (fechaEspecifica = '{$fecha}' and p.tipo='P')) AND hp.idProfesional IN({$profesional}) AND hp.idProfesional=p.idProfesional AND idHoras IN
        (
            SELECT MAX(idHoras)
            FROM horariosprofesionales 
            GROUP BY idProfesional, dia
        )
        ORDER BY desdeManana, desdeTarde");

        $horarios = array();

        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();

            if(($row['desdeManana']<>'')and($row['hastaManana']<>'')){
                $horarios[]=array(
                    'desde' => $row['desdeManana'],
                    'hasta' => $row['hastaManana'],
                    'idProfesional' => $row['idProfesional']
                );
            }
            if(($row['desdeTarde']<>'')and($row['hastaTarde']<>'')){
                $horarios[]=array(
                    'desde' => $row['desdeTarde'],
                    'hasta' => $row['hastaTarde'],
                    'idProfesional' => $row['idProfesional']
                );
            }

        }

        return $horarios;

    }


    private static function checkOverlap($horaInicio, $horaFin, $bloqueos){
    //Está en un bloqueo?
    foreach($bloqueos as $bloqueo){
        return (self::overlap($horaInicio, $horaFin, $bloqueo['desde'], $bloqueo['hasta']));
        break;
    }
    }


    private static function checkInRange($horaInicio, $horaFin, $bloqueos){
    //Está en un bloqueo?
        foreach($bloqueos as $bloqueo){
            return (self::inRange($horaInicio, $horaFin, $bloqueo['desde'], $bloqueo['hasta']));
            break;
        }
    }


    public static function getHours($fecha, $profesional, $duracion){

        $horarios = self::getHorariosProfesionales($fecha, $profesional);
        $bloqueos = self::getBloqueos($fecha, $profesional);
        $turnos = self::getTurnos($fecha, $profesional);
        $anticipacion = self::getAnticipacion($fecha);

        $horas = array();

        foreach($horarios as $horario){
            $horaInicio = $horario['desde'];
            $horaFin = $horario['hasta'];
            $idProfesional = $horario['idProfesional'];

            $begin = new DateTime($horaInicio);
            $end = new DateTime($horaFin);

            $interval = DateInterval::createFromDateString($duracion.' minutes');
            $period = new DatePeriod($begin, $interval, $end);

            foreach ($period as $dt) {

                $dejo=1;

                $estaOpcion=$dt->format("H:i");

                $dt->add(new DateInterval('PT' . ($duracion) . 'M'));

                $estaOpcionFin=$dt->format("H:i");

                if(self::checkOverlap($estaOpcion, $estaOpcionFin, $bloqueos)) $dejo=0;
                
                if(self::checkOverlap($estaOpcion, $estaOpcionFin, $turnos)) $dejo=0;
                
                if(self::checkOverlap($estaOpcion, $estaOpcionFin, $anticipacion)) $dejo=0;
                
                $miHorario[]=$horario;
                if(self::checkInRange($estaOpcion, $estaOpcionFin, $miHorario)) $dejo=0;

                if($dejo){
                    $horas[] = array('desde' => $estaOpcion, 'hasta' => $estaOpcionFin, 'idProfesional' => $idProfesional);
                }

            }
        }

        return $horas;
    }



}
