<?php 


class AuthController{

    private static $tipoAdmin = 1;
    private static $tipoSuperAdmin = 0;
    
    /* Redirecciona al index del admin  */
    private static function redirectionAdmin(){
        header("location: /admin");
    }
    
    /* Login */
    public static function checkLogin(){
        if($_SESSION['usuario']['logueado'] <> 1){
            session_destroy();
            self::redirectionAdmin();
        }
    }
    
    /* Chekea si es admin */
    public static function checkAdmin(){
        if($_SESSION['usuario']['tipo'] > self::$tipoAdmin){
            session_destroy();
            self::redirectionAdmin();
        }
    }
    
    /* Checka si es super admin */
    public static function checkSuperAdmin(){
        if($_SESSION['usuario']['tipo'] > self::$tipoSuperAdmin){
            session_destroy();
            self::redirectionAdmin();
        }
    }

    public static function checkSuperAdminORProfesional(){
        if(!self::isProfesional() && $_SESSION['usuario']['tipo'] > self::$tipoSuperAdmin){
            session_destroy();
            self::redirectionAdmin();
        }
    }

    public static function isLogged(){
        return isset($_SESSION["usuario"]["idUsuario"]);
    }


    /* Is Admin */
    public static function isAdmin(){
        return $_SESSION['usuario']['tipo'] < self::$tipoAdmin;
    }

    public static function isProfesional(){
        return $_SESSION['usuario']['profesional'] == 1;
    }


    /* 
        Funciones del archivo ./login.php 
    */

    /* Login */
    public static function loginEngine($user,$pass){
        global $row, $tot, $general;
    
        db_query(0,"select nombre, puesto, idAdmin, tipo from administradores where usuario='".$user."' and pass='".sha1($pass)."' and estado='A' and (vencimiento>'".date("Y-m-d H:i:s")."' or vencimiento='0000-00-00 00:00:00') limit 1");

        if($tot>0){
            $_SESSION['usuario']['idUsuario']=$row['idAdmin'];
            $_SESSION['usuario']['logueado']=1;
            $_SESSION['usuario']['nombre']=$row['nombre'];
            $_SESSION['usuario']['foto']='';
            $_SESSION['usuario']['puesto']=$row['puesto'];
            $_SESSION['usuario']['tipo']=$row['tipo'];
            $_SESSION['usuario']['profesional']=0;
    
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
                $_SESSION['usuario']['profesional']=1;
    
                $response['status']='OK';
    
            }else{
                session_destroy();
                $response['status']='error';
            }
        }
    
        return $response;
    }
    
    /* Logout */
    public static function logoutEngine(){
        session_destroy();
        $response['status']='OK';
    
        return $response;
    }
}
