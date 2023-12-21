<?php

class SuplenciaController{

    public static function getSuplencias($idProfesional){
        GLOBAL $tot, $res, $row;

		$suplencias=array();
		$suplenciasOtro=array();
        db_query(0,"select idTurno, idProfesional from suplencias");
        
		for($i=0;$i<$tot;$i++){
			$nres=$res->data_seek($i);
			$row=$res->fetch_assoc();
			if($row['idProfesional']==$idProfesional){
				$suplencias[$row['idTurno']]=$idProfesional;
			}else{
				$suplenciasOtro[$row['idTurno']]=$idProfesional;
			}
		}
		return [
			"suplencias" => $suplencias,
			"suplenciasOtro" => $suplenciasOtro
        ];
	}

}

?>