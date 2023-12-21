<?php

class FeriadoController{

    public static function getFeriados(){

        global $tot, $res, $row;
    
        $columnas=array('idFeriado','nombre','fecha');
        $columnasBusqueda['descripcion']='nombre';
        $columnasBusqueda['fecha']='fecha';
    
        $filtro='';
    
        if($_GET['search']['value']<>''){
            $filtro="and (nombre like '".$_GET['search']['value']."%'";
        
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
            $ordenar='fecha ASC';
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
    
    
        db_query(0,"select idFeriado from feriados where estado='A' order by ".$ordenar);
        if($tot>0){
            $response['recordsTotal']=$tot;
    
    
            db_query(0,"select idFeriado from feriados where estado='A' ".$filtro." ".$filtroCategoria." order by ".$ordenar);
    
            // PC::datatables("select codigo from ciudades ".$filtro." order by nombre ASC, codigo ASC",'consulta');
    
    
            $response['recordsFiltered']=$tot;
    
            $response['data']=array();
    
            db_query(0,"select * from feriados where estado='A' ".$filtro." ".$filtroCategoria." order by ".$ordenar." limit ".$_GET['start'].", ".$_GET['length']);
    
    
            for($i=0;$i<$tot;$i++){
                $nres=$res->data_seek($i);
                $row=$res->fetch_assoc();
            
                $acciones='<a onclick="editar('.$row['idFeriado'].')" href="#" data-toggle="tooltip" data-placement="bottom" title="Editar"><i class="fas fa-pen-to-square"></i></a> <a onclick="eliminar('.$row['idFeriado'].')" href="#" data-toggle="tooltip" data-placement="bottom" title="Eliminar"><i class="fas fa-trash"></i></a>';
                
                $response['data'][$i]["DT_RowId"]="fila".$row['idFeriado'];
                $response['data'][$i]["acciones"]=$acciones;
                $response['data'][$i]["descripcion"]=$row['nombre'];
                $response['data'][$i]["fecha"]=date("d/m/Y",strtotime($row['fecha']));
            
            }
        
            $response['data']=array_values($response['data']);
    
        }else{
            $response['recordsTotal']=(int)0;
            $response['recordsFiltered']=(int)0;
        }
    
        return $response;
    }

    public static function isFeriado($fecha){
        GLOBAL $tot, $row;
        db_query(0,"select nombre from feriados where fecha='".$fecha."' and estado='A' limit 1");

        return [
            "flag" => $tot > 0,
            "nombre" => $row["nombre"] ?? ''
        ];
    }


}

?>