<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

AuthController::checkLogin();


$fechaImprimir=date("Y-m-d",strtotime(str_replace('/','-',$_GET['fecha'])));

if($_GET['quien']){
    $filtroProfesional=" and idProfesional='".$_GET['quien']."'";
    $filtroProfesional2=" and o.idProfesional='".$_GET['quien']."'";
}

$profesionales=ProfesionalController::getProfesionales();
$profesionales=$profesionales['profesionales'];


$filtroFechas=" and date(t.fechaInicio)='".$fechaImprimir."' ";
$leyenda='';

if($_GET['quien']){
    $diaSemana['Monday']='Lunes';
    $diaSemana['Tuesday']='Martes';
    $diaSemana['Wednesday']='Miercoles';
    $diaSemana['Thursday']='Jueves';
    $diaSemana['Friday']='Viernes';
    $diaSemana['Saturday']='Sabado';
    $diaSemana['Sunday']='Domingo';
	
    db_query(0,"select * from horariosprofesionales where ((idHoras in(select max(idHoras) from horariosprofesionales group by idProfesional, dia) and dia='".strtolower($diaSemana[date("l",strtotime($fechaImprimir))])."') or (fechaEspecifica='".date("Y-m-d", strtotime($fechaImprimir))."')) ".$filtroProfesional." order by dia");
	
    $filtroFechas.=" and ((t.fechaInicio>='".$fechaImprimir.' '.$row['desdeManana'].":00' and t.fechaInicio<='".$fechaImprimir.' '.$row['hastaManana'].":00') or (t.fechaInicio>='".$fechaImprimir.' '.$row['desdeTarde'].":00' and t.fechaInicio<='".$fechaImprimir.' '.$row['hastaTarde'].":00')) ".$filtroProfesional2."";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?
    $seccion='Turnos';
    $subseccion=date("d/m/Y",strtotime($fechaImprimir)).' - '.$leyenda;
    require_once(incPath.'/head.php');
    ?>
</head>
<body class="py-4 px-3">
<h2><?=ucwords($general['nombreTurnos'])?> <?=date("d/m/Y",strtotime($fechaImprimir))?><?=($leyenda) ? ' - '.$leyenda : ''?></h2>
<table class="table table-striped table-bordered mt-4" id="tablaPacientes">
    <thead>
        <th width="10%">
            Horario
        </th>
        <th width="25%">
            <?=ucwords($general['nombreTurno'])?>
        </th>
        <th width="40%">
            Tratamiento
        </th>
        <th width="25%">
            Profesional
        </th>
    </thead>
    <tbody>
        <?
		
        db_query(0,"select t.idTurno, t.fechaInicio, o.asistentes, p.nombre, p.apellido, tra.nombre as tratamiento, o.idProfesional from turnos t, ordenes o, pacientes p, tratamientos tra where t.estado<>3 ".$filtroFechas." and o.idOrden=t.idOrden and p.idPaciente=t.idPaciente and tra.idTratamiento=o.idTratamiento and t.eliminado <> '1' order by t.fechaInicio ASC");
        for($i=0;$i<$tot;$i++){
            $nres=$res->data_seek($i);
            $row=$res->fetch_assoc();
            ?>
            <tr id="fila<?=$row['idTurno']?>">
                <td><?=date("H:i", strtotime($row['fechaInicio']))?></td>
                <td><?=ucfirst($row['nombre'])?> <?=ucfirst($row['apellido'])?></td>
                <td><?=$row['tratamiento']?></td>
                <td><?=$profesionales[$row['idProfesional']]?></td>
            </tr>
            <?
        }
        ?>
    </tbody>
</table>
</body>