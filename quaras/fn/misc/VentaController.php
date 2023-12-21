<?php

class VentaController{


    public static function calcularVentasTotalesPorTurnos($fechaDesde, $fechaHasta, $valorMinimoAusente){
		GLOBAL $tot, $res, $row, $row2;

		$totalPeriodo=0;
		$totalTratamiento=array();
		$totalAnual=0;
		$totalMensual=array();
		$totalDiario=array();

		db_query(0,"select t.idOrden, t.fechaInicio, o.idTratamiento, t.estado from turnos t, ordenes o where (t.estado=1) and t.idOrden=o.idOrden and year(t.fechaInicio)='".date("Y",strtotime($fechaDesde))."'");

		for($i=0;$i<$tot;$i++){
			$nres=$res->data_seek($i);
			$row=$res->fetch_assoc();
			$fechaTurno=date("Y-m-d",strtotime($row['fechaInicio']));
			
			db_query(2,"select cantidad from tratamientos_valores where idTratamiento='".$row['idTratamiento']."'  and fechaAlta<='".date("Y-m-d",strtotime($row['fechaInicio']))."' order by fechaAlta DESC");
			
			
			if($row['estado']==2){
				$laCantidad=$valorMinimoAusente;
			}else{
				$laCantidad=$row2['cantidad'];
			}
			
			if(($fechaTurno>=$fechaDesde)and($fechaTurno<=$fechaHasta)){
				$totalPeriodo+=$laCantidad;
				
				// echo '<br>La cantidad para OS: '.$row['idObraSocial'].' - Tratamiento: '.$row['idTratamiento'].' - Plan: '.$plan.' = '.$laCantidad;
				
				@$totalTratamiento[$row['idTratamiento']]+=$laCantidad;
				
			}
			if(date("Y",strtotime($fechaTurno))==date("Y")){
				$totalAnual+=$laCantidad;
			}
			@$totalMensual[date("Ym",strtotime($fechaTurno))]+=$laCantidad;
			@$totalDiario[date("m/d",strtotime($fechaTurno))]+=$laCantidad;
			
		}

		return [
			"totalPeriodo" => $totalPeriodo,
			"totalTratamiento" => $totalTratamiento,
			"totalAnual" => $totalAnual,
			"totalMensual" => $totalMensual,
			"totalDiario" => $totalDiario
		];
	}

	public static function calcularVentasTotalesPorTurnosPorProfesional($fechaDesde, $fechaHasta, $valorMinimoAusente, $profesional = ''){
		GLOBAL $tot, $res, $row, $row2;

		$totalPeriodo=0;
		$totalTratamiento=array();
		$totalAnual=0;
		$totalMensual=array();
		$totalDiario=array();

		$filtroProfesional = $profesional ? " AND o.idProfesional = {$profesional} " : "" ;

		db_query(0,"SELECT t.idOrden, t.fechaInicio, o.idTratamiento, t.estado from turnos t, ordenes o where (t.estado=1) and t.idOrden=o.idOrden {$filtroProfesional} and year(t.fechaInicio)='".date("Y",strtotime($fechaDesde))."'");

		for($i=0;$i<$tot;$i++){
			$nres=$res->data_seek($i);
			$row=$res->fetch_assoc();
			$fechaTurno=date("Y-m-d",strtotime($row['fechaInicio']));
			
			db_query(2,"select cantidad from tratamientos_valores where idTratamiento='".$row['idTratamiento']."'  and fechaAlta<='".date("Y-m-d",strtotime($row['fechaInicio']))."' order by fechaAlta DESC");
			
			
			if($row['estado']==2){
				$laCantidad=$valorMinimoAusente;
			}else{
				$laCantidad=$row2['cantidad'];
			}
			
			if(($fechaTurno>=$fechaDesde)and($fechaTurno<=$fechaHasta)){
				$totalPeriodo+=$laCantidad;
				
				// echo '<br>La cantidad para OS: '.$row['idObraSocial'].' - Tratamiento: '.$row['idTratamiento'].' - Plan: '.$plan.' = '.$laCantidad;
				
				@$totalTratamiento[$row['idTratamiento']]+=$laCantidad;
				
			}
			if(date("Y",strtotime($fechaTurno))==date("Y")){
				$totalAnual+=$laCantidad;
			}
			@$totalMensual[date("Ym",strtotime($fechaTurno))]+=$laCantidad;
			@$totalDiario[date("m/d",strtotime($fechaTurno))]+=$laCantidad;
			
		}

		return [
			"totalPeriodo" => $totalPeriodo,
			"totalTratamiento" => $totalTratamiento,
			"totalAnual" => $totalAnual,
			"totalMensual" => $totalMensual,
			"totalDiario" => $totalDiario
		];
	}
}
