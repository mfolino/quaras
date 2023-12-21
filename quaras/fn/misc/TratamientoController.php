<?php
class TratamientoController{

    public static function getTratamientos($idCategoria = '', $idProfesional = '', $json = false){
        global $general;
        global $row;
        global $tot;
        global $res;

        $tratamientos = array();

        if ($idCategoria) {
            db_query(0, "SELECT t.nombre, t.idTratamiento from tratamientos t, categorias_tratamientos ct, profesionales_tratamientos pt where (t.estado='A' or t.estado=1) and ct.idTratamiento=t.idTratamiento and ct.idCategoria='{$idCategoria}' and pt.idTratamiento=t.idTratamiento group by t.idTratamiento order by t.nombre ASC");
        } else if ($idProfesional) {
            db_query(0, "SELECT t.nombre, t.idTratamiento from tratamientos t, profesionales_tratamientos pt where (t.estado='A' or t.estado=1) and pt.idTratamiento=t.idTratamiento and pt.idProfesional='{$idProfesional}' group by t.idTratamiento order by t.nombre ASC");
        }else{
            db_query(0, "SELECT t.nombre, t.idTratamiento from tratamientos t, profesionales_tratamientos pt where (t.estado='A' or t.estado=1) and pt.idTratamiento=t.idTratamiento group by t.idTratamiento order by t.nombre ASC");
        }

        if ($tot > 0) {

            for ($i = 0; $i < $tot; $i++) {
                $nres = $res->data_seek($i);
                $row = $res->fetch_assoc();
                $tratamientos[$row['idTratamiento']] = $row['nombre'];
            }

            $response['status'] = 'OK';
            $response['tratamientos'] = $tratamientos;
        } else {
            $response['status'] = 'NO';
        }

        if ($json) {
            return json_encode($response);
        } else {
            return $response;
        }
    }

    public static function getDuracion($tratamiento){
        global $row;

        db_query(0, "select duracion from tratamientos where idTratamiento='" . $tratamiento . "' limit 1");

        return $row['duracion'];
    }

    public static function getPrice($tratamiento){
        GLOBAL $row99;
        $precio = 0;
        if($dataTratamiento = db_getOne("SELECT cantidad FROM tratamientos_valores WHERE idTratamiento={$tratamiento} AND fechaAlta <= NOW() ORDER BY idComision DESC LIMIT 1")){
            $precio = $dataTratamiento->cantidad;
        }
        return $precio;
    }
    
    public static function getTextoPost($tratamiento){
        global $row;

        db_query(0, "select textoPost, nombre from tratamientos where idTratamiento='" . $tratamiento . "' limit 1");

        $response['textoPost'] = $row['textoPost'] ? str_replace(["nbsp;","&"],[" ",""], $row['textoPost']) : '';
        $response['titulo'] = $row['nombre'];

        return $response;
    }

    public static function getDuracionDelTratamiento($idTratamiento, $idProfesional){
        GLOBAL $row;
        db_query(0, "select tra.duracion from tratamientos tra, profesionales_tratamientos pt where pt.idTratamiento='".$idTratamiento."' and pt.idProfesional='".$idProfesional."' and pt.idTratamiento=tra.idTratamiento limit 1");
        return $row['duracion'];
    }
    
    public static function getSimultaneosDelTratamiento($idTratamiento, $idProfesional=''){
        GLOBAL $row;
        $whereProfesional = $idProfesional ? " and pt.idProfesional='".$idProfesional."' " : '';

        db_query(0, "select tra.simultaneos from tratamientos tra, profesionales_tratamientos pt where pt.idTratamiento='".$idTratamiento."' {$whereProfesional} and pt.idTratamiento=tra.idTratamiento limit 1");
        return $row['simultaneos'];
    }

    public static function cantidadMinimaDeTratamientos($profesional = ''){
		GLOBAL $row;
        $filtroProfesional = $profesional ? " AND pt.idTratamiento = tv.idTratamiento AND pt.idProfesional = {$profesional}" : "";
        /* Util::printVar("SELECT tv.cantidad from tratamientos_valores tv, profesionales_tratamientos pt where {$filtroProfesional} AND tv.cantidad<>'' order by tv.cantidad ASC, tv.fechaAlta DESC", "181.99.172.180"); */
		db_query(0,"SELECT tv.cantidad from tratamientos_valores tv, profesionales_tratamientos pt where  tv.cantidad<>'' {$filtroProfesional} order by tv.cantidad ASC, tv.fechaAlta DESC");
		return $row['cantidad'];
	}

    // Se usa en reportes
	public static function getAllTratamientos($profesional = ''){
        GLOBAL $tot, $row, $res;
        
        $filtroProfesional = $profesional ? " AND pt.idTratamiento = t.idTratamiento AND pt.idProfesional = {$profesional}" : "";

		$tratamientos=array();
		db_query(0,"SELECT t.* from tratamientos t, profesionales_tratamientos pt WHERE pt.idTratamiento = t.idTratamiento {$filtroProfesional} order by t.nombre");
		for($i=0;$i<$tot;$i++){
			$nres=$res->data_seek($i);
			$row=$res->fetch_assoc();
			$tratamientos[$row['idTratamiento']]=$row['nombre'];
		}
		return $tratamientos;
	}

    public static function valoresYFechas(){
        GLOBAL $row, $res, $tot;

        $valores=array();
        $fechasValores=array();
        
        db_query(0,"select idTratamiento, cantidad, fechaAlta from tratamientos_valores order by fechaAlta DESC");
        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();
            $valores[strtotime($row['fechaAlta'])][$row['idTratamiento']]=$row['cantidad'];
            $fechasValores[$row['idTratamiento']][]=strtotime($row['fechaAlta']);
        }
        return [
            "valores" => $valores,
            "fechasValores" => $fechasValores
        ];
    }

    public static function getTratamientosDisponibles($start, $categoria=false){
        global $general, $tot1, $row1, $res1;

        $tratamientos=array();

        $contador=0;

        $filtroCategoria='';

        if($categoria && $categoria != "undefined"){
            $filtroCategoria=' c.idCategoria="'.$categoria.'" AND ';
        }else{
            if($general["nivelCategorias"] && $categoria != "undefined"){
                db_query(1,"select idCategoria from categorias where estado='A' order by nombre ASC limit 1");
                $filtroCategoria=' c.idCategoria="'.$row1['idCategoria'].'" AND ';
            }
        }

        /* Util::printVar([$filtroCategoria, $categoria], "190.231.245.234"); */
        
        $query = "SELECT 
                tra.idTratamiento, 
                t.nombre 
            FROM 
                horariosprofesionales h, 
                profesionales p,
                tratamientos t,
                profesionales_tratamientos tra,
                categorias c,
                categorias_tratamientos ct
            WHERE 
                (
                    (desdeManana<>'' AND hastaManana<>'') OR 
                    (desdeTarde<>'' AND hastaTarde<>'')
                ) AND 
                ( 
                    h.fechaEspecifica='".date('Y-m-d',strtotime(str_replace('/','-',$start)))."' OR 
                    ( 
                        h.fechaEspecifica='0000-00-00' AND 
                        h.dia='".strtolower(DateController::daysToDias(date('l',strtotime(str_replace('/','-',$start)))))."' AND 
                        h.idHoras in( SELECT MAX(idHoras) FROM horariosprofesionales GROUP BY idProfesional, dia) 
                    )
                ) AND 
                p.idProfesional = h.idProfesional AND
                tra.idProfesional = p.idProfesional AND
                tra.idTratamiento = t.idTratamiento AND
                ".$filtroCategoria."
                ct.idCategoria = c.idCategoria AND
                ct.idTratamiento = t.idTratamiento
            
            ORDER BY p.nombre ASC
        ";
        if(!$general["nivelCategorias"]){
            $query = "SELECT 
                    tra.idTratamiento, 
                    t.nombre 
                FROM 
                    horariosprofesionales h, 
                    profesionales p,
                    tratamientos t,
                    profesionales_tratamientos tra
                WHERE 
                    (
                        (desdeManana<>'' AND hastaManana<>'') OR 
                        (desdeTarde<>'' AND hastaTarde<>'')
                    ) AND 
                    ( 
                        h.fechaEspecifica='".date('Y-m-d',strtotime(str_replace('/','-',$start)))."' OR 
                        ( 
                            h.fechaEspecifica='0000-00-00' AND 
                            h.dia='".strtolower(DateController::daysToDias(date('l',strtotime(str_replace('/','-',$start)))))."' AND 
                            h.idHoras in( SELECT MAX(idHoras) FROM horariosprofesionales GROUP BY idProfesional, dia) 
                        )
                    ) AND 
                    p.idProfesional = h.idProfesional AND
                    tra.idProfesional = p.idProfesional AND
                    tra.idTratamiento = t.idTratamiento
                
                ORDER BY p.nombre ASC
            ";

        }


        db_query(1,$query);

        for($i1=0;$i1<$tot1;$i1++){
            $nres1=$res1->data_seek($i1);
            $row1=$res1->fetch_assoc();

            $tratamientos[$contador]['id']=$row1['idTratamiento'];
            $tratamientos[$contador]['title']=$row1['nombre'];

            $contador++;
        }

        return $tratamientos;
    }

    public static function getSena($idTratamiento){
        GLOBAL $row, $res, $tot;
        Global $general;

        db_query(0,
            " SELECT 
                * 
            FROM
                tratamientos 
            WHERE 
                idTratamiento = ".$idTratamiento."
        ");

        $tratamiento=null;
        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();
            $tratamiento=[
                'id' => $row['idTratamiento'],
                'pago' => $row['pago']
            ];
        }
        // Si no encontro el tratamiento devuelvo nulo
        if(!$tratamiento) return $tratamiento;
        
        if($tratamiento['pago'] == 'PP' && $general['paypal']){
            return $general['paypal_money_vista']."".$general['paypal_sena'];
        }
        if($tratamiento['pago'] == 'MP' && $general['mercadoPago']){
            return $general['mercadoPago_money_vista']." ".$general['mercadoPago_sena'];
        }

        return '';
    }

}
