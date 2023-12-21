<?php

use function PHPSTORM_META\map;

class PacienteController{

    public static function save($dataSave){
        $vars['tabla'] = 'pacientes';
        $vars['tabla'] = $dataSave;

        if ($dataSave['paciente'] <> 'NN') {
            $vars['accion'] = 'actualizarPacienteNN';
            $vars['idLabel']='idPaciente';
            $vars['id']=$dataSave["paciente"];
            $paciente = db_edit($vars);

            $orden = $dataSave['orden'];
        } else {
            $vars['accion'] = 'agregarPacienteNN';
            $paciente = db_edit($vars);

            $orden = '';
        }

        return ["paciente" => $paciente, "orden" => $orden];
    }

    /* Save del lado publico */
    public static function updateExternal($idPaciente, $mail){
        GLOBAL $row;
        db_query(0,"select nombre, mail from pacientes where idPaciente='".$idPaciente."' limit 1");

		$paciente=$idPaciente;
		$nombrePaciente=$row['nombre'];

		if(Util::is_valid_email($mail) && $mail !== $row['mail'])
		{
			db_update(
			"UPDATE pacientes
			SET mail = '{$mail}'
			WHERE idPaciente = '{$idPaciente}'
			LIMIT 1");
		}

        return ["paciente" => $paciente, "nombrePaciente" => $nombrePaciente, "orden" => ""];
    }
    
    public static function getPacientes($data){

        global $res, $row, $tot, $general;
        global $res11, $row11, $tot11;


        $filtro='';

        if($_GET['search']['value']<>''){
            $filtro="and (";
            foreach($data['columns'] as $column){
                if($column['data']<>'acciones'){
                    if($column['searchable']=='true'){
                        $filtro.=$column['data']." like '%".$_GET['search']['value']."%' or ";
                    }
                }
            }	
            $filtro=rtrim($filtro,' or ');
            $filtro.=") ";
        }else{
            $filtro='';
        }

        if(@sizeof($data['columns'])>0){
            $filtroCategoria='';
            foreach($data['columns'] as $detalles){
                if($detalles['search']['value']<>''){
                    $filtroCategoria.=" and ".$detalles['data']." like '%".$detalles['search']['value']."%'";
                }
            }
        }else{
            $filtroCategoria="";
        }

        if(@sizeof($_GET['order'])>0){
            $ordenar='';
            foreach($_GET['order'] as $idOrder => $detalles){
                if($data['columns'][$detalles['column']]['data']=='total'){
                    $ordenar.=$data['columns'][$detalles['column']]['data'].' * 1 '.$detalles['dir'].', ';
                }else{
                    $ordenar.=$data['columns'][$detalles['column']]['data'].' '.$detalles['dir'].', ';
                }
            }
            $ordenar=rtrim($ordenar,', ');
        }else{
            $ordenar='p.nombre, p.apellido DESC';
        }

        $response['draw']=(int)$_GET['draw'];
        $response['data']=array();


        db_query(0,"select idPaciente from pacientes where estado<>'B' group by idPaciente order by ".$ordenar);
        if($tot>0){
            $response['recordsTotal']=$tot;


            db_query(0,"select idPaciente from pacientes where estado<>'B' ".$filtro." ".$filtroCategoria." group by idPaciente  order by ".$ordenar);

            // PC::datatables("select codigo from ciudades ".$filtro." order by nombre ASC, codigo ASC",'consulta');


            $response['recordsFiltered']=$tot;

            $response['data']=array();
	
            // die("select p.idPaciente, p.fechaDeNacimiento, p.nombre, p.apellido, p.telefono, o.nombre as obraSocial, case when p.numeroCarnet <> '' then 1 else 0 end as estadoPaciente, count(ord.idOrden) as ordenesTotales, SUM(if(ord.estado != 1, 1, 0)) AS ordenesActivas from obrassociales o, pacientes p left join ordenes ord on p.idPaciente=ord.idPaciente where p.idObraSocial=o.idObraSocial ".$filtro." ".$filtroCategoria." group by p.idPaciente  order by ".$ordenar);
	
            $limit = $_GET['length'] == '-1' ? "" : " limit ".$_GET['start'].", ".$_GET['length'];

            db_query(0,"select * from pacientes where estado<>'B' ".$filtro." ".$filtroCategoria." group by idPaciente  order by ".$ordenar. $limit);


            for($i=0;$i<$tot;$i++){
                $nres=$res->data_seek($i);
                $row=$res->fetch_assoc();

                foreach($data['columns'] as $column){
                    if($column['data']=='acciones'){
                        $acciones='<a onclick="editar('.$row['idPaciente'].')" href="#" data-toggle="tooltip" data-placement="bottom" title="Editar"><i class="fa fa-pencil"></i></a>&nbsp;';
		
                        if($row['ordenesTotales']==0){
                            $acciones.='<a onclick="eliminar('.$row['idPaciente'].')" href="#" data-toggle="tooltip" data-placement="bottom" title="Eliminar"><i class="fa-solid fa-trash-can"></i></a>&nbsp;';
                        }else{
                            $acciones.='<i class="fa fa-trash-o text-secondary"></i>&nbsp;';
                        }
		
                        $acciones.='<a onclick="turnos('.$row['idPaciente'].',\''.$row['nombre'].' '.$row['apellido'].'\')" href="#" data-toggle="tooltip" data-placement="bottom" title="Ver turnos"><i class="fa fa-calendar"></i></a>';

                        // Si creditos esta prendido agrego el boton
                        if($general['creditos']){
                            db_query(11,"SELECT idPlan, disponible FROM creditos_pacientes WHERE idPaciente = '{$row['idPaciente']}' LIMIT 1");
                            $idPlan = $row11['idPlan'];
                            $disponible = $row11['disponible'] ?? '';
                            
                            if($tot11 > 0){
                                db_query(11, "SELECT idPlan, modo FROM creditos_planes WHERE idPlan = '{$idPlan}' AND estado = 'A' LIMIT 1");
    
                                $tipoPlan = $row11['modo'] == 'M' ? "Recarga Manual" : "Recarga Automática";
                                $textCredito = $disponible > 1 ? 'créditos' : 'crédito';
                                $acciones.='<a class="ml-1" href="./editarRecarga?id='.$row['idPaciente'].'" data-toggle="tooltip" data-placement="bottom" title="'.$disponible.' '.$textCredito.' ('.$tipoPlan.')"><i class="fas fa-money-bill"></i></a>';
                            }
                        }

                        $response['data'][$i]["acciones"]=$acciones;
                    }else{
                        if($column['data']=='telefono'){
                            
                            if($row['codPais']){
                                $prefijo=$row['codPais'];
                            }else{
                                $prefijo=$general['prefijoTelefonico'];
                            }

                            $telefono=$prefijo.$row['codArea'].$row['telefono'];
                            $response['data'][$i]["telefono"]='<a href="https://wa.me/'.$telefono.'" target="_blank" data-toggle="tooltip" data-placement="bottom" title="Enviar Whatsapp al '.$general['nombrePaciente'].'"><i class="fab fa-whatsapp"></i></a> +'.$telefono;
                        }else{
                            $response['data'][$i][$column['data']]=$row[$column['data']];
                        }
                    }
                }
		
                $response['data'][$i]["DT_RowId"]="fila".$row['idPaciente'];
		
            }
	
            $response['data']=array_values($response['data']);

        }else{
            $response['recordsTotal']=(int)0;
            $response['recordsFiltered']=(int)0;
        }

        return $response;
    }

    public static function getPacientesSelect(){
        GLOBAL $tot, $res, $row, $general;
        AuthController::checkLogin();

        $opciones=array();

        if(@$_GET['query']){

            $query=$_GET['query'];

            db_query(0,"select p.idPaciente, p.nombre, p.apellido, p.codArea, p.telefono, p.mail from pacientes p where (estado!='B' and estado!='I') and (p.nombre like '".$query."%' or p.apellido like '".$query."%') order by p.apellido, p.nombre ASC");
            for($i=0;$i<$tot;$i++){
                $nres=$res->data_seek($i);
                $row=$res->fetch_assoc();
                $opciones[]=array('id'=>$row['idPaciente'],'idProfesional'=>$row['idProfesional'],'codArea'=>$row['codArea'],'telefono'=>$row['telefono'],'mail'=>$row['mail'],'nombre'=>$row['nombre'],'apellido'=>$row['apellido'],'tratamiento'=>$row['idTratamiento'],'text'=>$row['apellido'].' '.$row['nombre']);
            }

        }

        if($_GET['from']=='turnos'){
            $opciones[]=array(
                'id'=>'NN',
                'idProfesional'=>'',
                'codArea'=>'',
                'telefono'=>'',
                'mail'=>'',
                'tratamiento'=>'',
                'text'=>'Nuevo '.$general['nombrePaciente']
            );
        }

        $response['results']=$opciones;
        $response['pagination']['more']=false;

        return $response;
        
    }



    

    public static function getIdPacientePorEmail($mail){
        GLOBAL $tot, $row;
        db_query(0,"select idPaciente from pacientes where mail='".$mail."' AND estado <> 'B' LIMIT 1");
        if($tot==0) return null;
        return $row["idPaciente"];
    }

    public static function getIdPacientePorTelefono($telefono){
        GLOBAL $tot1, $row1;
        db_query(1,"select * from pacientes where telefono='".$telefono."' AND estado <> 'B' LIMIT 1");
        if($tot1==0) return null;
        return $row1["idPaciente"];
    }

    public static function getIdPacientePorDni($dni){
        GLOBAL $tot, $row;
        db_query(0,"select * from pacientes where dni='".$dni."' AND estado <> 'B' LIMIT 1");
        if($tot==0) return null;
        return $row["idPaciente"];
    }


    public static function getPacientesParaReporteComision(){
        GLOBAL $res, $tot, $row;

        $pacientes=array();
        db_query(0,"select nombre, apellido, idPaciente from pacientes order by nombre, apellido ASC");
        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();
            $pacientes[$row['idPaciente']]['nombre']=$row['nombre'];
            $pacientes[$row['idPaciente']]['apellido']=$row['apellido'];
        }
        return $pacientes;
    }

    public static function checkPax($dato,$tipo, $codArea=''){
        GLOBAL $general;
        global $tot;
        global $row, $row1;
        
        if($dato){
    
            switch($tipo){
                case 'dni':
                    $buscar=array('.','-');
                    $reemplazar=array('','');
                
                    $dni=str_replace($buscar,$reemplazar,$dato);
                    
                    db_query(0,"select * from pacientes where estado <> 'B' AND dni='".$dni."' limit 1");
                    break;
                
                case 'email':
                    db_query(0,"select * from pacientes where estado <> 'B' AND mail='".$dato."' limit 1");
                    break;
    
                case 'telefono':
    
                    db_query(0,"select * from pacientes where estado <> 'B' AND codArea='".$codArea."' and telefono='".$dato."' limit 1");
                    break;
            }
            if($tot>0){

                //Voy a ver si tiene turnos tomados o cancelables
                db_query(1, "select count(idTurno) as turnos from turnos where idPaciente='".$row['idPaciente']."' and estado<>3 and eliminado<>1");

                $response['status']='OK';
                $response['nombre']=$row['nombre'];
                $response['idPaciente']=$row['idPaciente'];
                $response['mail']=$row['mail'];
                $response['telefono']=$row['telefono'];
                $response['codArea']=$row['codArea'];
                $response['dni']=$row['dni'];
                $response['direccion']=$row['direccion'];
                $response['turnos']=$row1['turnos'];

                if($general["creditos"] && Migration::existTableInDB('creditos_pacientes')){
                    $planPaciente = db_getOne("SELECT * FROM creditos_pacientes WHERE idPaciente = {$row['idPaciente']} ");
                    $response["creditosDisponibles_query"] = "SELECT * FROM cretidos_pacientes WHERE idPaciente = {$row['idPaciente']} "; 
                    $response["creditosDisponibles"] = $planPaciente->disponible ?? 0; 
                }
            }else{
                $response['status']='NO';
            }
        }else{
            $response['status']='vacio';
        }
    
        return $response;
    }
    
    /*        CRON: actualizar creditos_pacientes y creditos_planes       */
    public static function updateDataCreditos(){
        GLOBAL $row, $res, $tot;

        $fechaActual = date('Y-m-d');
        $fechaYHoraActual = date('Y-m-d H:i:s');
        $fechaAUnMes = date('Y-m-d H:i:s', strtotime($fechaYHoraActual.'+ 1 month'));

        db_query(0, "SELECT cp.* FROM creditos_pacientes cp, creditos_planes cpla WHERE cp.proximoVencimiento = '{$fechaActual}' AND cpla.idPlan = cp.idPlan AND cpla.modo ='A'");

        if($tot==0) return;

        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();
            $creditoPaciente = $row;

            // Busco la cantidad y la piso en disponible
            db_query(0, "SELECT cantidad FROM creditos_planes WHERE idPlan = '{$creditoPaciente['idPlan']}'");
            $cantidad = $row['cantidad'];

            // Actualizo la tabla creditos_pacientes
            db_edit([
                "tabla" => "creditos_pacientes",
                "accion" => "actualizarProximoVencimiento",
                "idLabel" => "idPaciente",
                "id" => $creditoPaciente['idPaciente'],
                "fechaAlta" => $fechaYHoraActual,
                "proximoVencimiento" => $fechaAUnMes,
                "disponible" => $cantidad
            ]);
        }

    }
}
