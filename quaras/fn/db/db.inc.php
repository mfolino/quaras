<?
require_once($_SERVER['DOCUMENT_ROOT'].'/quaras/fn/db.settings.php');

$connect='';

function connect_db(){
    $server=$GLOBALS['server'];
    $user=$GLOBALS['user'];
    $pass=$GLOBALS['pass'];
    $db=$GLOBALS['db'];

    global $connect;
    global $entorno;

    if($entorno == 'remoto'){
        $server=$GLOBALS['serverRemoto'];
        $user=$GLOBALS['userRemoto'];
        $pass=$GLOBALS['passRemoto'];
        $db=$GLOBALS['dbRemoto'];
    }
	
    $connect=new mysqli($server, $user, $pass,$db);
    if($connect->connect_errno) {handle_error("Fallo conectar a MySQL (".$connect->connect_errno.") ".$connect->connect_error);}
	
    $connect->set_charset('utf8');
}

/*Funcion para hacer consultas*/
function db_query($i,$param){
    if($i==0){$i='';}
    global $connect;
    global ${'tot'.$i};
    global ${'res'.$i};
    global ${'row'.$i};

    if(!$connect){
        connect_db();
    }
	
    ${'res'.$i}=$connect->query($param);
	
    if(!${'res'.$i}) {handle_error($connect->error, $param);}
	
    ${'tot'.$i}=${'res'.$i}->num_rows;
	
    if (${'tot'.$i}>0){
        ${'nres'.$i}=${'res'.$i}->data_seek(0);
        ${'row'.$i}=${'res'.$i}->fetch_assoc();
    }
}

/*Funcion para hacer updates*/
function db_update($param){
    global $tot;
    global $connect;

    if(!$connect){
        connect_db();
    }

    $res=$connect->query($param);
    if(!$res) {handle_error($connect->error, $param);}else{$tot=1;}
}

/*Funcion para insertar un nuevo registro*/
function db_insert($param){
    global $tot;
    global $newid;
    global $connect;

    if(!$connect){
        connect_db();
    }

    $res=$connect->query($param);
    $newid=mysqli_insert_id($connect);
    if(!$res) {handle_error($connect->error, $param);}
}

/*Funcion para insertar un registro en el log*/
function db_log($usuario,$accion,$id){
    global $connect;

    if(!$connect){
        connect_db();
    }

    $res=$connect->query("insert into log (usuario,accion,fechahora,id) values ('".$usuario."','".$accion."','".date('Y-m-d H:i:s')."','".$id."')");
    if(!$res) {handle_error($connect->error, $param);}
}

/*Funcion para borrar dato/s*/
function db_delete($param){
    global $tot;
    global $connect;

    if(!$connect){
        connect_db();
    }

    $res=$connect->query($param);
    if(!$res) {handle_error($connect->error);}
}

function handle_error($perror, $param=''){
    /* die("<b>Database Error:</b> ".$perror); */
    die("<b>Database Error ".__FILE__." line ".__LINE__.":</b> ".$perror." - ".$param);
}


function db_edit($vars,$excluir=array()){
    global $newid, $connect;

    $excluir[]='action';
    $excluir[]='id';
    $excluir[]='tabla';
    $excluir[]='idLabel';
    $excluir[]='accion';
    $excluir[]='usuarioLog';
    $excluir[]='path';

    $camposInsert=array();

    $camposInsert['columnas']='';
    $camposInsert['variables']='';
    $camposUpdate='';
    
    foreach($vars as $nombre => $contenido){
        if(!in_array($nombre,$excluir)){
            /* if($_SERVER["REMOTE_ADDR"] == "181.99.172.180"){
                $contenido = $connect->real_escape_string($contenido);
            } */
            $camposInsert['columnas'].=$nombre.',';
            $camposInsert['variables'].="'".$contenido."',";
            $camposUpdate.=$nombre."='".$contenido."',";
        }
    }

    $camposInsert['columnas']=rtrim($camposInsert['columnas'],',');
    $camposInsert['variables']=rtrim($camposInsert['variables'],',');
	
    $camposUpdate=rtrim($camposUpdate,',');

    if((@$camposUpdate<>'')or(@$camposInsert['columnas']<>'')){
	
        if(@trim($vars['id'])){
            //Estoy editando
            db_update("update ".$vars['tabla']." set ".$camposUpdate." where ".$vars['idLabel']."=".$vars['id']);
            $id=$vars['id'];
			
        }else{
            //Estoy insertando
            db_insert("insert into ".$vars['tabla']." (".$camposInsert['columnas'].") values (".$camposInsert['variables'].")");
            $id=$newid;
        }
	
    }

    $usuarioLog = isset($vars['usuarioLog']) && $vars['usuarioLog'] != '' ? $vars['usuarioLog'] : $_SESSION['usuario']['nombre'];
    if($vars['accion']){
        db_log($usuarioLog, $vars['accion'], $id);
    }

    return $id;
}

/* Nueva funcion para consultas */
function db_getAll($sql, $firstRow = false){
    GLOBAL $connect;
    $resultsQuery = array();

    if(!$connect){
        connect_db();
    }

    $result = $connect->query($sql);

    if(!$result) return $resultsQuery;

    while ($obj = $result -> fetch_object()) {
        $resultsQuery[] = $obj;
    }
    $result -> free_result();

    if($firstRow){
        return $resultsQuery[0];
    }

    return $resultsQuery;
}


function db_getOne($sql){
    GLOBAL $connect;
    $resultsQuery = array();

    if(!$connect){
        connect_db();
    }

    $result = $connect->query($sql);

    if(!$result) return $resultsQuery;

    while ($obj = $result -> fetch_object()) {
        $resultsQuery[] = $obj;
    }
    $result -> free_result();

    return $resultsQuery[0];
}
?>