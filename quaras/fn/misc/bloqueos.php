<?
/* 
    Params 
    - profesional => id | [..id..]
*/
function getBloqueosFiltro($profesional, $fecha){
    global $tot, $row, $res, $horasProhibidas;

    $profesionales = implode(',', $profesional);

    db_query(0,
        "SELECT 
            * 
        FROM 
            bloqueos 
        WHERE 
            estado='A' AND 
            date(fechaDesde)<='".$fecha."' AND 
            date(fechaHasta)>='".$fecha."' AND 
            (
                idProfesional  IN (".$profesionales.") OR 
                idProfesional='0'
            )
    ");

    for($i=0;$i<$tot;$i++){
        $nres=$res->data_seek($i);
        $row=$res->fetch_assoc();

        $horasProhibidas[$i]=array(
            'fechaInicio'=>$row['fechaDesde'],
            'fechaFin'=>$row['fechaHasta'],
            'idProfesional'=>$row['idProfesional']
        );
    }
}

/* 
    Params 
    - idProfesional => id | ''
*/
function isBloqueado($fecha, $idProfesional=''){
    GLOBAL $tot, $row;

    $filtroProfesional = $idProfesional ? " or idProfesional='".$idProfesional."'" : "";

    db_query(0,"select descripcion from bloqueos where fechaDesde<='".$fecha."' and fechaHasta>'".$fecha."' and (idProfesional=0 ".$filtroProfesional.") and estado='A'");

    return [
        "flag" => $tot > 0,
        "descripcion" => $row['descripcion'] ?? ''
    ];
}

function getBloqueos(){

    global $tot, $res, $row;
    
    $columnas=array('idBloqueo','descripcion','fechaDesde','fechaHasta');
    $columnasBusqueda['descripcion']='descripcion';
    $columnasBusqueda['fechaDesde']='fechaDesde';
    $columnasBusqueda['fechaHasta']='fechaHasta';

    $profesionales=ProfesionalController::getProfesionales();
    $profesionales=$profesionales['profesionales'];

    $filtro='';

    if($_GET['search']['value']<>''){
        $filtro="and (descripcion like '".$_GET['search']['value']."%'";
	
        if($tipoEstado[strtolower($_GET['search']['value'])]){
            $filtro.=" or t.estado like '".$tipoEstado[strtolower($_GET['search']['value'])]."%'";
        }
	
        $filtro.=") ";
    }else{
        $filtro='';
    }

    if(@sizeof($_GET['order'])>0){
        $ordenar='';
        foreach($_GET['order'] as $idOrder => $detalles){
            if($columnas[$detalles['column']]=='total'){
                $ordenar.=$columnas[$detalles['column']].' * 1 '.$detalles['dir'].', ';
            }else{
                $ordenar.=$columnas[$detalles['column']].' '.$detalles['dir'].', ';
            }
        }
        $ordenar=rtrim($ordenar,', ');
    }else{
        $ordenar='fechaDesde ASC';
    }

    if(@sizeof($_GET['columns'])>0){
        $filtroCategoria='';
        foreach($_GET['columns'] as $idOrder => $detalles){
            if($detalles['search']['value']<>''){
                $filtroCategoria.=" and ".$columnasBusqueda[$detalles['data']]." like '%".$detalles['search']['value']."%'";
            }
        }
    }else{
        $filtroCategoria="";
    }

    // PC::datatables($_GET['search'],'search');

    $response['draw']=(int)$_GET['draw'];

    $response['data']=array();


    db_query(0,"select idBloqueo from bloqueos where estado='A' order by ".$ordenar);
    if($tot>0){
        $response['recordsTotal']=$tot;


        db_query(0,"select idBloqueo from bloqueos where estado='A' ".$filtro." ".$filtroCategoria." order by ".$ordenar);

        // PC::datatables("select codigo from ciudades ".$filtro." order by nombre ASC, codigo ASC",'consulta');


        $response['recordsFiltered']=$tot;

        $response['data']=array();

        db_query(0,"select * from bloqueos where estado='A' ".$filtro." ".$filtroCategoria." order by ".$ordenar." limit ".$_GET['start'].", ".$_GET['length']);


        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();
		
            $acciones='<a onclick="editar('.$row['idBloqueo'].')" href="#" data-toggle="tooltip" data-placement="bottom" title="Editar"><i class="fas fa-pen-to-square"></i></a> <a onclick="eliminar('.$row['idBloqueo'].')" href="#" data-toggle="tooltip" data-placement="bottom" title="Eliminar"><i class="fas fa-trash"></i></a>';
			
            $response['data'][$i]["DT_RowId"]="fila".$row['idBloqueo'];
            $response['data'][$i]["acciones"]=$acciones;
            $response['data'][$i]["descripcion"]=$row['descripcion'];
            $response['data'][$i]["fechaDesde"]=date("d/m/Y H:i:s",strtotime($row['fechaDesde']));
            $response['data'][$i]["fechaHasta"]=date("d/m/Y H:i:s",strtotime($row['fechaHasta']));
            if($row['idProfesional']){
                $response['data'][$i]["profesional"]=$profesionales[$row['idProfesional']];
            }else{
                $response['data'][$i]["profesional"]='Todos';
            }
		
        }
	
        $response['data']=array_values($response['data']);

    }else{
        $response['recordsTotal']=(int)0;
        $response['recordsFiltered']=(int)0;
    }
    

    return $response;
}


?>