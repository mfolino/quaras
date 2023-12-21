<?
class ProfesionalController {
    
    public static function getProfesionales($idTratamiento='',$json=false){
        global $tot;
        global $row;
        global $res;

        $profesionales=array();

        if($idTratamiento){
            db_query(0,"SELECT p.nombre, p.idProfesional from profesionales p, profesionales_tratamientos pt where p.estado='A' and p.idProfesional=pt.idProfesional and pt.idTratamiento='{$idTratamiento}' group by p.idProfesional order by p.nombre ASC");
        }else{
            db_query(0,"SELECT p.nombre, p.idProfesional from profesionales p where p.estado='A' group by p.idProfesional order by p.nombre ASC");
        }

        if($tot>0){

            for($i=0;$i<$tot;$i++){
                $nres=$res->data_seek($i);
                $row=$res->fetch_assoc();
                $profesionales[$row['idProfesional']]=$row['nombre'];
            }

            $response['status']='OK';
            $response['profesionales']=$profesionales;
            
        }else{
            $response['status']='NO';
        }

        if($json){
            return json_encode($response);
        }else{
            return $response;
        }
    }

    public static function getMinHour($dia=''){
        global $tot, $row;
        if($dia){
            $filtroDia=' and dia="'.$dia.'"';
        }else{
            $filtroDia='';
        }
        db_query(0,"SELECT min(desdeManana) as minHora from horariosprofesionales where desdeManana<>''".$filtroDia." ORDER BY fechaAlta DESC limit 1");
        if($tot<1){
            db_query(0,"SELECT min(desdeTarde) as minHora from horariosprofesionales where desdeTarde<>''".$filtroDia." ORDER BY fechaAlta DESC limit 1");

            if($tot<1){
                $row['minHora']='07:00';
            }
        }

        return $row['minHora'].':00';	
    }

    public static function getMaxHour($dia=''){
        global $tot, $row;
        if($dia){
            $filtroDia=' and dia="'.$dia.'"';
        }else{
            $filtroDia='';
        }
        db_query(0,"SELECT max(hastaTarde) as maxHora from horariosprofesionales where hastaTarde<>''".$filtroDia." ORDER BY fechaAlta DESC limit 1");
        if($tot<1){
            db_query(0,"SELECT max(hastaManana) as maxHora from horariosprofesionales where hastaManana<>''".$filtroDia." ORDER BY fechaAlta DESC limit 1");

            if($tot<1){
                $row['maxHora']='22:00';
            }
        }

        return $row['maxHora'].':00';
    }

    public static function getProfesionalesDisponibles($start){
        global $tot1, $row1, $res1;

        $profesionales=array();
        $filtroCoordinador='';
        $contador=0;

        db_query(1,
            "SELECT 
                h.idProfesional, p.nombre 
            FROM 
                horariosprofesionales h, 
                profesionales p 
            WHERE 
                h.dia='".strtolower(DateController::daysToDias(date('l',strtotime(str_replace('/','-',$start)))))."' AND 
                (
                    (desdeManana<>'' AND hastaManana<>'') OR (desdeTarde<>'' AND hastaTarde<>'')
                ) AND 
                ( 
                    h.fechaEspecifica='".date('Y-m-d',strtotime(str_replace('/','-',$start)))."' OR 
                    ( 
                        h.fechaEspecifica='0000-00-00' AND 
                        h.idHoras in( SELECT MAX(idHoras) FROM horariosprofesionales GROUP BY idProfesional, dia) 
                    )
                ) AND 
                p.idProfesional = h.idProfesional ".$filtroCoordinador." 
            ORDER BY p.nombre ASC
        ");

        for($i1=0;$i1<$tot1;$i1++){
            $nres1=$res1->data_seek($i1);
            $row1=$res1->fetch_assoc();

            $profesionales[$contador]['id']=$row1['idProfesional'];
            $profesionales[$contador]['title']=$row1['nombre'];

            $contador++;
        }

        return $profesionales;
    }

}
?>
