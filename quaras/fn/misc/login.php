<?
function loginEngine($user,$pass){
    global $row, $tot, $general;

    db_query(0,"select nombre, puesto, idAdmin, tipo from administradores where usuario='".$user."' and pass='".sha1($pass)."' and estado='A' and (vencimiento>'".date("Y-m-d H:i:s")."' or vencimiento='0000-00-00 00:00:00') limit 1");
    if($tot>0){
        $_SESSION['usuario']['idUsuario']=$row['idAdmin'];
        $_SESSION['usuario']['logueado']=1;
        $_SESSION['usuario']['nombre']=$row['nombre'];
        $_SESSION['usuario']['foto']='';
        $_SESSION['usuario']['puesto']=$row['puesto'];
        $_SESSION['usuario']['tipo']=$row['tipo'];

        $response['status']='OK';

    }else{
        db_query(0,"select nombre, idProfesional from profesionales where email='".$user."' and password='".sha1($pass)."' and estado='A' limit 1");
        if($tot>0){
            $_SESSION['usuario']['idUsuario']=$row['idProfesional'];
            $_SESSION['usuario']['logueado']=1;
            $_SESSION['usuario']['nombre']=$row['nombre'];
            $_SESSION['usuario']['foto']='';
            $_SESSION['usuario']['puesto']=$general['nombreProfesional'];
            $_SESSION['usuario']['tipo']=1;

            $response['status']='OK';

        }else{
            session_destroy();
            $response['status']='error';
        }
    }

    return $response;
}

function logoutEngine(){
    session_destroy();
    $response['status']='OK';

    return $response;
}

?>