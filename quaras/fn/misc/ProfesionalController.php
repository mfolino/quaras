<?

class ProfesionalController
{

    public static function getProfesionales($idTratamiento = '', $publico = false, $json = false)
    {
        global $tot;
        global $row;
        global $res;

        $profesionales = array();

        if ($publico) {
            $filtroProfesionalesPrivados = " AND p.privado=0";
        }

        if ($idTratamiento) {
            // $filtroProfesionalesPrivados = !$_SESSION ? " AND p.privado <> 1 " : "";
            $query = "SELECT p.nombre, p.idProfesional from profesionales p, profesionales_tratamientos pt 
            where p.estado='A' and p.idProfesional=pt.idProfesional and pt.idTratamiento='{$idTratamiento}' " . $filtroProfesionalesPrivados . " 
            group by p.idProfesional order by p.nombre ASC";
            db_query(0, $query);
        } else {
            $query = "SELECT p.nombre, p.idProfesional from profesionales p where p.estado='A' group by p.idProfesional order by p.nombre ASC";
            db_query(0, $query);
        }

        if ($tot > 0) {

            for ($i = 0; $i < $tot; $i++) {
                $nres = $res->data_seek($i);
                $row = $res->fetch_assoc();
                $profesionales[$row['idProfesional']] = $row['nombre'];
            }

            $response['status'] = 'OK';
            $response['profesionales'] = $profesionales;
        } else {
            $response['status'] = 'NO';
        }
        $response["query"] = $query;

        if ($json) {
            return json_encode($response);
        } else {
            return $response;
        }
    }

    public static function getProfesional($idProfesional)
    {
        global $tot;
        global $row;
        global $res;

        db_query(0, "SELECT * from profesionales where idProfesional='{$idProfesional}' limit 1");
        return $row;
    }

    public static function getMinHour($dia = '')
    {
        GLOBAL $general, $tot, $row;
        
        if ($dia) {
            $filtroDia = ' and dia="' . $dia . '"';
        } else {
            $filtroDia = '';
        }

        db_query(0, "SELECT min(desdeManana) as minHora from horariosprofesionales where desdeManana <> '00:00' AND desdeManana<>''" . $filtroDia . " ORDER BY fechaAlta DESC limit 1");
        if ($row['minHora'] == NULL) {
            db_query(0, "SELECT min(desdeTarde) as minHora from horariosprofesionales where desdeTarde <> '00:00' AND desdeTarde<>''" . $filtroDia . " ORDER BY fechaAlta DESC limit 1");

            if ($row['minHora'] == NULL) {
                $row['minHora'] = '07:00';
            }
        }

        if($general["calendarioAdmin_mixHora"]){
            $row['minHora'] = $general["calendarioAdmin_mixHora"];
        }

        return $row['minHora'] . ':00';
    }

    public static function getMaxHour($dia = '')
    {
        GLOBAL $general, $tot, $row;
        if ($dia) {
            $filtroDia = ' and dia="' . $dia . '"';
        } else {
            $filtroDia = '';
        }

        db_query(0, "SELECT max(hastaTarde) as maxHora from horariosprofesionales where hastaTarde<>''" . $filtroDia . " ORDER BY fechaAlta DESC limit 1");
        if ($row['maxHora'] == NULL) {
            db_query(0, "SELECT max(hastaManana) as maxHora from horariosprofesionales where hastaManana<>''" . $filtroDia . " ORDER BY fechaAlta DESC limit 1");

            if ($row['maxHora'] == NULL) {
                $row['maxHora'] = '22:00';
            }
        }

        if($general["calendarioAdmin_maxHora"]){
            $row['maxHora'] = $general["calendarioAdmin_maxHora"];
        }

        return $row['maxHora'] . ':00';
    }

    public static function getProfesionalesDisponibles($start, $idProfesional = '')
    {
        global $tot1, $row1, $res1;

        

        $profesionales = array();
        $filtroCoordinador = '';

        if ($idProfesional) {
            $filtroCoordinador = ' and p.idProfesional="' . $idProfesional . '"';
        }

        $contador = 0;

        db_query(
            1,
            "SELECT 
                h.idProfesional, 
                p.nombre 
            FROM 
                horariosprofesionales h, 
                profesionales p 
            WHERE 
                h.dia <> '' AND 
                (
                    (desdeManana<>'' AND hastaManana<>'') OR 
                    (desdeTarde<>'' AND hastaTarde<>'')
                ) AND 
                ( 
                    h.fechaEspecifica='" . date('Y-m-d', strtotime(str_replace('/', '-', $start))) . "' OR 
                    ( 
                        h.fechaEspecifica='0000-00-00' AND 
                        h.dia='" . strtolower(DateController::daysToDias(date('l', strtotime(str_replace('/', '-', $start))))) . "' AND 
                        h.idHoras in( SELECT MAX(idHoras) FROM horariosprofesionales GROUP BY idProfesional, dia) 
                    )
                ) AND 
                p.idProfesional = h.idProfesional " . $filtroCoordinador . " 
            
            ORDER BY p.nombre ASC
        "
        );

        for ($i1 = 0; $i1 < $tot1; $i1++) {
            $nres1 = $res1->data_seek($i1);
            $row1 = $res1->fetch_assoc();

            $profesionales[$contador]['id'] = $row1['idProfesional'];
            $profesionales[$contador]['title'] = $row1['nombre'];

            $contador++;
        }
        return $profesionales;
    }

    /* 
        Params: 
            - fecha: date('Y-m-d H:i:s')
            - profesionales: idProfesional | [...idProfesional...]
        Return []
    */
    public static function getProfesionalesOcupados($fecha, $profesional = '')
    {
        global $tot, $res, $general;

        $profesionalesOcupados = array();

        $sim = $general['turnosSimultaneos'];

        if ($sim < 2) {
            $sim = 1;
        }


        $filtroProfesional = $profesional ? " AND o.idProfesional IN ({$profesional}) " : '';
        
        db_query(
            0,
            "SELECT 
                o.idProfesional 
            FROM 
                turnos t, 
                ordenes o, 
                bloqueos b 
            WHERE 
                o.idProfesional = b.idProfesional AND 
                b.fechaDesde <= '{$fecha}' AND 
                b.fechaHasta > '{$fecha}' AND 
                b.estado = 'A' AND 
                t.fechaInicio <= '" . $fecha . "' AND 
                t.fechaFin > '" . $fecha . "' AND 
                t.idOrden = o.idOrden AND 
                t.estado <> 3 and t.eliminado<>1 " . $filtroProfesional . "
        ");


        $simultaneos = array();

        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();

            $simultaneos[$row['idProfesional']]++;

            if ($simultaneos[$row['idProfesional']] >= $sim) {
                if($general['sobreTurno']) continue;
                $profesionalesOcupados[$row['idProfesional']] = 1;
            }
        }

        return $profesionalesOcupados;
    }


    /* 
        return: [..idTratamientos...]
    */
    public static function getTratamientos($idProfesional)
    {
        global $row, $res, $tot;

        $tratamientos = '';
        db_query(0, "SELECT GROUP_CONCAT(pt.idTratamiento) as tratamientos FROM profesionales p, profesionales_tratamientos pt WHERE p.idProfesional = pt.idProfesional AND p.idProfesional = '{$idProfesional}' AND p.estado = 'A' ");
        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();
            $tratamientos = $row['tratamientos'];
        }

        return explode(",", $tratamientos);
    }

    /* 
        return int
        Descripcion: Verifica si hay profesionales trabajando en el horario del turno solicitado
    */
    public static function cantidadDeProfesionalesPorHorario($fecha)
    {
        GLOBAL $general;

        $fechaADia = date("Y-m-d", strtotime($fecha));
        $fechaAHora = date("H:i", strtotime($fecha));
        $fechaADiaSemana = strtolower(DateController::daysToDias(date('l', strtotime($fecha))));

        $total = 0;

        $profesionales = db_getAll("SELECT 
                hp.*,
                p.tipo
            FROM 
                horariosprofesionales hp, 
                profesionales p 
            WHERE 
                (
                    (
                        hp.dia='" . $fechaADiaSemana . "' AND p.tipo='H'  AND 
                        idHoras in( select max(idHoras) from horariosprofesionales group by idProfesional, dia )
                    ) OR 
                    (hp.fechaEspecifica='" . $fechaADia . "' AND p.tipo='P')
                ) AND (
                    (
                        hp.desdeTarde<='" . $fechaAHora . "' AND 
                        hp.hastaTarde>='" . $fechaAHora . "' AND 
                        hp.desdeTarde<>'' AND 
                        hp.hastaTarde<>''
                    ) OR 
                    (
                        hp.desdeManana<='" . $fechaAHora . "' AND 
                        hp.hastaManana>='" . $fechaAHora . "' AND 
                        hp.desdeManana<>'' AND 
                        hp.hastaManana<>''
                    )
                )
                GROUP BY idHoras
        ");

        foreach ($profesionales as $profesional) {

            if($profesional->tipo == 'P'){
                $total++;
                continue;
            }

            // Si tiene horario habitual chequeo los feriados personalizados
            if($general["feriadoPersonalizado"] && $profesional->tipo == 'H'){
                if($feriadoPersonalizado = db_getOne("SELECT * FROM feriadosPersonalizados WHERE idProfesional IN (0, $profesional->idProfesional) AND fechaDesde <= '{$fechaADia}' AND fechaHasta >= '{$fechaADia}' AND eliminado != 1 ")){
                    if($feriadoPersonalizado->horarioInicio > $fechaAHora.":00" || $feriadoPersonalizado->horarioFin < $fechaAHora.":00" ) continue; 
                }
                $total++;
                continue;
            }

            if($profesional->tipo == 'H'){
                $total++;
                continue;
            }
        }

        /* db_query(
            0,
            "SELECT 
                idHoras 
            FROM 
                horariosprofesionales hp, 
                profesionales p 
            WHERE 
                (
                    (
                        hp.dia='" . $fechaADiaSemana . "' AND p.tipo='H'  AND 
                        idHoras in( select max(idHoras) from horariosprofesionales group by idProfesional, dia )
                    ) OR 
                    (hp.fechaEspecifica='" . $fechaADia . "' AND p.tipo='P')
                ) AND (
                    (
                        hp.desdeTarde<='" . $fechaAHora . "' AND 
                        hp.hastaTarde>='" . $fechaAHora . "' AND 
                        hp.desdeTarde<>'' AND 
                        hp.hastaTarde<>''
                    ) OR 
                    (
                        hp.desdeManana<='" . $fechaAHora . "' AND 
                        hp.hastaManana>='" . $fechaAHora . "' AND 
                        hp.desdeManana<>'' AND 
                        hp.hastaManana<>''
                    )
                )
                GROUP BY idHoras
        "); */

        return $total;
    }

    public static function getProfesionalesPorIdTratamiento($idTratamiento){
        $ids = array();
        foreach (db_getAll("SELECT p.idProfesional FROM profesionales p, profesionales_tratamientos pt WHERE p.idProfesional = pt.idProfesional AND pt.idTratamiento = {$idTratamiento} AND p.estado = 'A' ") as $profesional) {
            $ids[] = $profesional->idProfesional;
        }
        return $ids;
    }
}
