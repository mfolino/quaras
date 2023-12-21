<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

/* --------------------------- */
/*          VALIDAR QR         */
/* --------------------------- */
if($_REQUEST["action"] == "qr_validate"){
    $dataTurno = db_getOne("SELECT t.*, p.nombre, p.apellido, COUNT(tp.idGrupo) as totalBoletos FROM turnos t, turnos_participantes tp, pacientes p WHERE t.idGrupo = '{$_POST['idGrupo']}' AND t.idPaciente = p.idPaciente AND t.idGrupo = tp.idGrupo AND t.eliminado <> 1 AND t.estado NOT IN (3, 9) GROUP BY t.idGrupo");
    $disponible = count(db_getAll("SELECT * FROM turnos_participantes tp WHERE tp.idGrupo = '{$dataTurno->idGrupo}' AND usado = 0")) > 0;


    /* --------------------- */
    /*      VALIDACIONES     */
    /* --------------------- */

    // Valido la fecha del turno
    if(date("Y-m-d", strtotime($dataTurno->fechaInicio)) != date("Y-m-d")){
        HTTPController::responseInJSON(array(
            "status" => "ERROR",
            "message" => ucfirst($general["nombreTurno"]) . " de otra fecha!"
        ));
        die();
    }

    // Valido si está disponible
    if(!$disponible){
        HTTPController::responseInJSON(array(
            "status" => "ERROR",
            "message" => "ERROR: El QR ya fue utilizado."
        ));
        die();
    }


    /* --------------------------------- */
    /*      BUSCO LA INFO DEL TURNO      */
    /* --------------------------------- */
    
    // Actualizo un boleto
    db_update("UPDATE turnos_participantes SET usado = 1 WHERE idGrupo = '{$_POST['idGrupo']}' AND usado = 0 LIMIT 1");

    $response = array(
        "status" => "OK",
        "numero" => $_POST["totalFilasEscaneadas"] + 1,
        "cliente" => ucfirst($dataTurno->nombre)." ".ucfirst($dataTurno->apellido),
        "entradas" => count(db_getAll("SELECT * FROM turnos_participantes tp WHERE tp.idGrupo = '{$dataTurno->idGrupo}' AND usado = 1")) ."/".$dataTurno->totalBoletos,
        "total" => "$".$dataTurno->precioSena,
    );
    
    HTTPController::responseInJSON($response);
    die();
}

/* ----------------------------- */
/*          PUNTO DE VENTA       */
/* ----------------------------- */

if($_POST["action"] == "saveExternal"){  
    
    if ($_POST['idPaciente'] <> '') {
        $resPaciente = PacienteController::updateExternal($_POST['idPaciente'], $_POST["mail"]);
        $paciente = $resPaciente['paciente'];
        $nombrePaciente = $resPaciente['nombrePaciente'];
    } else {
        if (($_POST['nombre']) and ($_POST['apellido'])) {

            $telefono = isset($_POST['telefono']) ? Util::limpiarTelefono($_POST['telefono']) : '';

            // Busco al usuario en base al campo que usa para logearse
            switch ($general['tomaTurno']) {
                case 'email':
                    $findPaciente = PacienteController::getIdPacientePorEmail($_POST["mail"]);
                    break;
                case 'dni':
                    $findPaciente = PacienteController::getIdPacientePorDni($_POST["dni"]);
                    break;
                case 'telefono':
                    $findPaciente = PacienteController::getIdPacientePorTelefono($_POST["telefono"]);
                    break;
                
                default:
                    $findPaciente = null;
                    break;
            }
            
            if ($findPaciente) {
                $paciente = isset($findPaciente["idPaciente"]) ? $findPaciente["idPaciente"] : $findPaciente;

                db_update("UPDATE pacientes set nombre='" . $_POST['nombre'] . "', apellido='" . $_POST['apellido'] . "', codArea='" . $_POST['codArea'] . "', telefono='" . $telefono . "', dni='" . $_POST['dni'] . "', mail='" . $_POST['mail'] . "', observaciones='".$_POST['observaciones']."' where idPaciente='" . $paciente . "'");
            } else {
                db_insert("insert into pacientes (nombre, apellido, codArea, telefono, mail, dni, observaciones) values ('" . $_POST['nombre'] . "', '" . $_POST['apellido'] . "', '" . $_POST['codArea'] . "', '" . $telefono . "', '" . $_POST['mail'] . "', '" . $_POST['dni'] . "', '" . $_POST['observaciones'] . "')");
                $paciente = $newid;
            }


            $nombrePaciente = $_POST['nombre'];
            $orden = '';
        } else {
            $response['status'] = 'datosIncompletos';
            HTTPController::responseInJSON($response);
            die();
        }
    }

    $profesional = $_POST['idProfesional'];
    $dataParticipantes = $_POST["participantes"];

    $duracion = TratamientoController::getDuracionDelTratamiento($_POST['idTratamiento'], $profesional);
    $simultaneos = TratamientoController::getSimultaneosDelTratamiento($_POST['idTratamiento'], $profesional);

    $fechaInicio = date("Y-m-d", strtotime(str_replace("/","-",$_POST["fecha"])))." ".$_POST["horas"].":00";
    $fechaFin = date("Y-m-d H:i:s", strtotime($fechaInicio." + {$duracion} minutes"));

    // Buscar participantes del turno
    $tomados = 0;
    foreach (db_getAll("SELECT t.* FROM turnos t, ordenes o WHERE t.idOrden = o.idOrden AND t.fechaInicio = '{$fechaInicio}' AND t.fechaFin = '{$fechaFin}' AND t.estado <> '3' AND t.eliminado <> '1' AND o.idTratamiento = {$_POST['idTratamiento']}") as $turno) {
        $tomados += count(db_getAll("SELECT * FROM turnos_participantes WHERE idTurno = {$turno->idTurno}"));
    }
    
    // Valido participantes
    if(!$dataParticipantes || count($dataParticipantes) == 0){
        die("Las {$general['nombreBoletos']} son obligatorias, agregué como mínimo una.");
    }

    // Validar cupos
    if(($simultaneos - $tomados) < count($dataParticipantes)){
        die("Lo sentimos, otra persona reservo recientemente y no hay suficientes {$general['nombreBoletos']} disponibles.");
    }


    /* ------------------------------- */
    /*          SACO LOS TURNOS        */
    /* ------------------------------- */
    $sqlInsertParticipantes = array();
    $idsGrupos = array();
    foreach ($dataParticipantes as $participante) {
        $idGrupo = uniqid();
        $idsGrupos[] = $idGrupo;

        // Actualizo la orden. No utilizo el metodo db_edit porque no hace el insert en el log
        db_insert("insert into ordenes (idPaciente, idProfesional, cantidad, idTratamiento, fechaAlta) values ('" . $paciente . "', '" . $profesional . "', '1', '" . $_POST['idTratamiento'] . "', '" . date("Y-m-d H:i:s") . "')");
        $orden = $newid;

        $fechaInicio = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $_POST['fecha']) . ' ' . $_POST['horas'] . ':00'));
        $fechaFin = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $_POST['fecha']) . " " . $_POST['horas'] . ":00" . " +" . $duracion . " minutes"));
        $varsTurno['tabla'] = 'turnos';
        $varsTurno['accion'] = 'agregarTurnoWeb';
        $varsTurno['usuarioLog'] = 'homeTurnos';
        $varsTurno['idPaciente'] = $paciente;
        $varsTurno['idOrden'] = $orden;
        $varsTurno['fechaInicio'] = $fechaInicio;
        $varsTurno['fechaFin'] = $fechaFin;
        $varsTurno['estado'] = 0;
        $varsTurno['medioDePago'] = 0; // Mercado pago
        $varsTurno['precioSena'] = $_POST["totalTurno"]; // Precio del turno
        $varsTurno['idGrupo'] = $idGrupo;
        
        $varsTurno['senaPorParticipante'] = ceil($_POST["totalTurno"] / count($dataParticipantes));
        $varsTurno['porcentajeMedioDePago'] = "";
        
        if (isset($general["public_textAreaTurno"]) && $general["public_textAreaTurno"]) {
            $varsTurno['observacion'] = $_POST["observaciones"];
        }
        $varsTurno['idAdmin'] = 0;
        
        $idTurno = db_edit($varsTurno);

        // Guardo el boleto
        $sqlInsertParticipantes[] = "('{$idGrupo}', '-', '-','-', '-', {$participante})";
        $sqlInsertParticipantes = implode(", ", $sqlInsertParticipantes);
        db_insert("INSERT INTO turnos_participantes (idGrupo, nombre, apellido, dni, isMayor, idBoleto) VALUES {$sqlInsertParticipantes}");
    }
    

    
    $response['status'] = 'OK';
    $response['codigos'] = $idsGrupos;
    $response['nombre'] = ucfirst($nombrePaciente);
    $response['fecha'] = $_POST['fecha'] . ' ' . $_POST['horas'];
    $response['fechaTurno'] = $_POST['fecha'];
    $response['horaTurno'] = $_POST['horas'];
    HTTPController::responseInJSON($response);
    die();
}


if($_POST["action"] == "searchPromociones"){
    $promociones = PromocionController::getPromocionesByFecha($_POST["fecha"]);
    
    HTTPController::responseInJSON(array(
        "status" => count($promociones) > 0 ? "OK" :"NO",
        "total" => count($promociones),
        "promociones" => $promociones
    ));
    die();
}