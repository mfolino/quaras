<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

foreach($_REQUEST as $key => $value){
    $post[$key]=SecurityDatabaseController::cleanVar($value);
}

if($post['action']=='updateComision'){
	
    AuthController::checkLogin();

    if($post['idTratamiento']<>0){
        if($_POST['cantidad']<>''){
            db_query(0,"select idComision from tratamientos_valores where idTratamiento='".$post['idTratamiento']."' and fechaAlta='".date("Y-m-d",strtotime(str_replace('/','-',$post['fechaAlta'])))."'");

            $vars=$post;
            $vars['id']=$row['idComision'];
            $vars['tabla']='tratamientos_valores';
            $vars['idLabel']='idComision';
            $vars['fechaAlta']=date("Y-m-d",strtotime(str_replace('/','-',$post['fechaAlta'])));

            if($vars['id']){
                $vars['accion']='actualizarComisionTratamiento';
            }else{
                $vars['accion']='agregarComisionTratamiento';
            }

            $newid=db_edit($vars);

            if($newid){
                $response['status']='OK';
            }else{
                $response['status']='error';
            }

        }else{
            $response['status']='error';
        }	
		
    }else{
        $response['status']='error';
    }

    HTTPController::responseInJSON($response);
}


/*Tratamientos*/
if($post['action']=='saveTratamiento'){

    AuthController::checkLogin();

    $vars=$post;
    $vars['tabla']='tratamientos';
    $vars['idLabel']='idTratamiento';
    if(!isset($vars['estado'])){
        $vars['estado']='I';
    }
    if($vars['id']){
        $vars['accion']='actualizarTratamiento';
    }else{
        $vars['accion']='agregarTratamiento';
    }

      // Agrego campos para enviar mails o no
    if($general['tratamiento_enviarMailConfirmacion'] || $general['tratamiento_enviarMailRecordatorio']){
        $tableTratamiento = new Migration('tratamientos');
        
        if($general['tratamiento_enviarMailConfirmacion']){
            $tableTratamiento->addColumn('enviarMailConfirmacion', TypeColumn::CHAR, 1);
            $vars['enviarMailConfirmacion']=$_POST['enviarMailConfirmacion'];
        }
        if($general['tratamiento_enviarMailRecordatorio']){
            $tableTratamiento->addColumn('enviarMailRecordatorio', TypeColumn::CHAR, 1);
            $vars['enviarMailRecordatorio']=$_POST['enviarMailRecordatorio'];
        }
    }

    if(isset($_POST['textoPost'])){
        $vars['textoPost'] = $_POST['textoPost'];
    }
    if(isset($_POST['textoPre'])){
        $vars['textoPre'] = $_POST['textoPre'];
    }
    /* Util::printVar($_POST, '138.121.84.107', true); */

    $newid=db_edit($vars);
    if($newid){
        $response['status']='OK';
    }else{
        $response['status']='error';
    }
    HTTPController::responseInJSON($response);
}

if($post['action']=='deleteTratamiento'){

    AuthController::checkLogin();

    if($post['id']<>''){
        db_update("update tratamientos set estado='B' where idTratamiento='".$post['id']."'");
        db_log($_SESSION['usuario']['nombre'],'eliminarTratamiento',$post['id']);
        $response['status']="OK";
    }else{
        $response['status']='vacio';
    }
    HTTPController::responseInJSON($response);
}



/*Categorías*/
if($post['action']=='saveCategoria'){

    AuthController::checkLogin();

    $vars=$post;
    $vars['tabla']='categorias';
    $vars['idLabel']='idCategoria';


    if(!isset($vars['estado'])){
        $vars['estado']='I';
    }
    if(isset($vars['id']) && $vars['id']){
        $vars['accion']='actualizarTratamiento';
        db_update("delete from categorias_tratamientos where idCategoria='".$vars['id']."'");
    }else{
        $vars['accion']='agregarTratamiento';
    }


    $excluir=array(
        'servicios'
    );


    $idCategoria=db_edit($vars,$excluir);

    foreach($_POST['servicios'] as $idServicio){
        db_insert("insert into categorias_tratamientos (idCategoria, idTratamiento) values ('".$idCategoria."', '".$idServicio."')");
    }

    if($idCategoria){
        $response['status']='OK';
    }else{
        $response['status']='error';
    }

    HTTPController::responseInJSON($response);
}

if($post['action']=='deleteCategoria'){

    AuthController::checkLogin();

    if($post['id']<>''){
        db_update("update categorias set estado='B' where idCategoria='".$post['id']."'");
			
        db_log($_SESSION['usuario']['nombre'],'eliminarCategoria',$post['id']);
			
        die("OK");

    }else{
        die('Id vacio');
    }
}


/* ---------------- */
/*      Planes      */
/* ---------------- */
if($post['action']=='savePlan'){

    //Util::printVar($post, '138.121.84.107', true);

    AuthController::checkLogin();

    $vars=$post;
    $vars['tabla']='creditos_planes';
    $vars['idLabel']='idPlan';

    if(!isset($vars['estado'])){
        $vars['estado']='I';
    }
    if(isset($vars['id']) && $vars['id']){
        $vars['accion']='actualizarPlan';
    }else{
        $vars['accion']='agregarPlan';
    }


    $excluir=array(
        'servicios'
    );


    $idPlan=db_edit($vars,$excluir);

    foreach($_POST['servicios'] as $idServicio){
        db_insert("insert into creditos_servicios (idPlan, idTratamiento) values ('".$idPlan."', '".$idServicio."')");
    }

    if($idPlan){
        $response['status']='OK';
    }else{
        $response['status']='error';
    }

    HTTPController::responseInJSON($response);
}

if($post['action']=='deletePlan'){

    AuthController::checkLogin();

    /* Util::printVar($post, '138.121.84.107', true); */

    if($post['id']<>''){
        db_update("update creditos_planes set estado='B' where idPlan='".$post['id']."'");
			
        db_log($_SESSION['usuario']['nombre'],'eliminarPlan',$post['id']);
			
        die("OK");

    }else{
        die('Id vacio');
    }
}

if($post['action']=='getTratamientos'){
    HTTPController::responseInJSON(TratamientoController::getTratamientos($post['categoria'], $post['profesional']));
}

if($post['action']=='getTextoPost'){
    if($general['mostrarTextoTratamientoPublic']){
        HTTPController::responseInJSON(TratamientoController::getTextoPost($post['tratamiento']));
    }else{
        HTTPController::responseInJSON(array('status'=>false));
    }
}

if($post['action']=='getTratamientosDisponibles'){
    HTTPController::responseInJSON(TratamientoController::getTratamientosDisponibles($_GET['start'], $_SESSION['idCategoria']));
}

if($post['action']=='getPrice'){
    HTTPController::responseInJSON(TratamientoController::getPrice($post['idTratamiento']));
}

// Valor da la sena en base al tratamiento
if($post['action'] == 'getSenaTratamiento'){
    HTTPController::responseInJson(TratamientoController::getSena($post['tratamiento']));
}


if($_POST["action"] == "getPrecioSenaPorcentajeTratamiento"){
    $precioTratamiento = intval(TratamientoController::getPrice($_POST["tratamiento"]));
    $porcentajeSena = intval($general['mercadoPago_servicios_sena_porcentaje']) / 100;
    $precioConPorcentaje = $precioTratamiento * $porcentajeSena;
    die($precioConPorcentaje);
}