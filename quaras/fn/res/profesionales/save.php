<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

foreach($_REQUEST as $key => $value){
    $post[$key] = SecurityDatabaseController::cleanVar($value);
}

if($post['action']=='save'){

    AuthController::checkLogin();

    $vars=$post;

    if($vars['password']==''){
        unset($vars['password']);
    }else{
        $vars['password']=sha1($vars['password']);
    }

    $vars['color']=str_replace('#','',$vars['color']);

    $excluir=array(
        'fechaSesion',
        'horaDesde',
        'horaHasta',
        'servicios',
    );

    $vars['tabla']='profesionales';
    $vars['idLabel']='idProfesional';
    if(!isset($vars['estado'])){
        $vars['estado']='I';
    }
    
    if($vars['id']){
        $vars['accion']='actualizarProfesional';
    }else{
        $vars['accion']='agregarProfesional';
        if($general['mercadoPago_accessToken_por_profesional']){
            $vars['access_token'] = $general['mercadoPago_access_token'];
        }
    }

    $newid=db_edit($vars, $excluir);

    if($newid){
        $response['status']='OK';
    }else{
        $response['status']='error';
    }
	
    $idProfesional=$newid;

    db_delete("delete from profesionales_tratamientos where idProfesional='".$idProfesional."'");
	
    if(isset($_POST['servicios'])){
        foreach($_POST['servicios'] as $idServicio){
            db_insert("insert into profesionales_tratamientos (idProfesional, idTratamiento) values ('".$idProfesional."', '".$idServicio."')");
        }
    }
	
    if($post['tipo']=='P'){

        db_delete("delete from horariosprofesionales where idProfesional='".$idProfesional."'");

        if(isset($_POST['fechaSesion'])){
            foreach($_POST['fechaSesion'] as $id => $fecha){
                db_insert("insert into horariosprofesionales (idProfesional, dia, desdeManana, hastaManana, fechaAlta, fechaEspecifica) values ('".$idProfesional."', '".DateController::daysToDias(date('l',strtotime(str_replace('/','-',$fecha))))."', '".$_POST['horaDesde'][$id]."', '".$_POST['horaHasta'][$id]."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d",strtotime(str_replace('/','-',$fecha)))."')");
            }
        }
    }
	
    HTTPController::responseInJSON($response);
}

if($post['action']=='delete'){
    if($post['id']<>''){
        db_update("update profesionales set estado='B' where idProfesional='".$post['id']."'");
        db_delete("delete from horariosprofesionales where idProfesional='".$post['id']."'");
		
        db_log($_SESSION['usuario']['nombre'],'eliminarProfesional',$post['id']);
		
        $response['status']="OK";
    }else{
        $response['status']="vacio";
    }

    HTTPController::responseInJSON($response);
}

if($post['action']=='guardarComision'){
    if($post['tratamiento']<>0){
        db_query(0,"select idComision from comisiones where idProfesional='".$post['idProfesional']."' and idTratamiento='".$post['tratamiento']."' and fechaAlta='".date("Y-m-d")."'");
        if($tot>0){
            db_update("update comisiones set cantidad='".$post['cantidad']."' where idProfesional='".$post['idProfesional']."' and idTratamiento='".$post['tratamiento']."' and idComision='".$row['idComision']."'");
			
            db_log($_SESSION['usuario']['nombre'],'actualizarComisionProfesional',$row['idComision']);
			
        }else{
            db_insert("insert into comisiones values ('', '".$post['idProfesional']."', '".$post['tratamiento']."', '".$post['cantidad']."','".date("Y-m-d")."')");
			
            db_log($_SESSION['usuario']['nombre'],'agregarComisionProfesional',$newid);
        }
        die("OK");
    }
}

if($post['action']=='programarAumento'){
    db_query(1,"select idTratamiento from profesionales_tratamientos where idProfesional='".$post['idProfesional']."'");
    for($i1=0;$i1<$tot1;$i1++){
        $nres1=$res1->data_seek($i1);
        $row1=$res1->fetch_assoc();
        db_insert("insert into comisiones values ('','".$post['idProfesional']."','".$row1['idTratamiento']."','".$post['cantidad']."','".date("Y-m-d",strtotime(str_replace('/','-',$post['fecha'])))."')");
    }
	
    db_log($_SESSION['usuario']['nombre'],'programarAumentoProfesional',$post['idProfesional']);
	
    die("OK");
}

if($post['action']=='guardarHorarios'){
	
    if($post['idProfesional']<>''){
        // db_delete("delete from horariosprofesionales where idProfesional='".$post['idProfesional']."'");
        foreach($_POST['dia'] as $key => $dia){
            db_insert("insert into horariosprofesionales (idProfesional, dia, desdeManana, hastaManana, desdeTarde, hastaTarde, fechaAlta) values('".$_POST['idProfesional']."','".$dia."','".$_POST['desdeManana'][$key]."','".$_POST['hastaManana'][$key]."','".$_POST['desdeTarde'][$key]."','".$_POST['hastaTarde'][$key]."','".date("Y-m-d H:i:s")."')");
        }
		
        db_log($_SESSION['usuario']['nombre'],'guardarHorariosProfesional',$_POST['idProfesional']);
		
        die("OK");
    }else{
        die("Error");
    }
	
}

if($post['action']=='getProfesionales'){
    HTTPController::responseInJSON(ProfesionalController::getProfesionales($post['tratamiento'], true));
}

if($post['action']=='getProfesionalesDisponibles'){

    if($_SESSION['usuario']['profesional']){
        $idProfesional=$_SESSION['usuario']['idUsuario'];
    }else{
        $idProfesional='';
    }

    HTTPController::responseInJSON(ProfesionalController::getProfesionalesDisponibles($_GET['start'], $idProfesional));
}

if($post['action']=='logout'){
    HTTPController::responseInJSON(AuthController::logoutEngine());
}
if($post['action']=='login'){
    $response = AuthController::loginEngine($_POST['email'],$_POST['password']);
    HTTPController::responseInJSON($response);
}

?>