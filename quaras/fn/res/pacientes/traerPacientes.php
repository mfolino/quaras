<?
require_once($_SERVER["DOCUMENT_ROOT"].'/inc/fn.php');
AuthController::checkLogin();


$opciones=array();

if(@$_GET['query']){

    $query=$_GET['query'];

    db_query(0,"select p.idPaciente, p.nombre, p.apellido, p.codArea, p.telefono, p.mail from pacientes p where (p.nombre like '".$query."%' or p.apellido like '".$query."%') order by p.apellido, p.nombre ASC");
    for($i=0;$i<$tot;$i++){
        $nres=$res->data_seek($i);
        $row=$res->fetch_assoc();
        $opciones[]=array('id'=>$row['idPaciente'],'idProfesional'=>$row['idProfesional'],'codArea'=>$row['codArea'],'telefono'=>$row['telefono'],'mail'=>$row['mail'],'tratamiento'=>$row['idTratamiento'],'text'=>$row['apellido'].' '.$row['nombre']);
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

header('Content-Type: application/json');
echo $_GET['callback'].'('.json_encode($response).')';
?>