<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/fn.php');


foreach ($_REQUEST as $key => $value) {
    $post[$key] = SecurityDatabaseController::cleanVar($value);
}

if ($post['action'] == 'save') {

    $telefono = Util::limpiarTelefono($_POST['telefono']);
    $varsPaciente = $_POST;
    $varsPaciente['tabla'] = 'pacientes';
    $varsPaciente['accion'] = 'agregarPaciente';
    $varsPaciente['telefono'] = $telefono;

    // Creo el paciente
    $excluirDePaciente = ["tratamientos", "plan", "cantidad"];
    db_edit($varsPaciente, $excluirDePaciente);
    $idPaciente = $newid ?? 1;

    // Guardo los tratamientos que tiene bloqueados
    if ($general['bloquearTurnosAPacientesPorTratamiento'] && isset($_POST['tratamientos'])) {
        $varsTratamientos['tabla'] = 'pacientes_tratamientos';
        $varsTratamientos['accion'] = 'agregarBloqueoDeTurnosAUnPaciente';
        $varsTratamientos['idPaciente'] = $idPaciente;

        // Guardo los tratamientos que le paciente no va a poder tomar
        foreach ($_POST['tratamientos'] as $tratamiento) {
            /* Util::printVar($varsTratamientos, '186.138.206.135', false); */
            $varsTratamientos['idTratamiento'] = $tratamiento;
            db_edit($varsTratamientos);
        }
    }

    if ($general['creditos']) {
        db_delete("delete from creditos_pacientes where idPaciente='" . $idPaciente . "'");

        //Busco la info del plan
        db_query(0, "select modo, diaMes, cantidad from creditos_planes where idPlan='" . $_POST['plan'] . "'");

        if ($row['modo'] == 'A') {
            if ($row['diaMes'] == 'end') {
                $row['diaMes'] = date("t", strtotime(date("Y-m-d")));
            }

            if ($row['diaMes'] < date("d")) {
                $proximoVencimiento = date("Y-m-d", strtotime(date("Y-m") . "-" . $row['diaMes'] . " +1 month"));
            } else {
                $proximoVencimiento = date("Y-m-d", strtotime(date("Y-m") . "-" . $row['diaMes']));
            }
        } else {
            $proximoVencimiento = '';
        }

        db_insert("insert into creditos_pacientes (idPaciente, idPlan, disponible, fechaAlta, proximoVencimiento, estado) values ('" . $idPaciente . "','" . $_POST['plan'] . "', '" . $row['cantidad'] . "',now(), '" . $proximoVencimiento . "', 0)");
    }

    die('OK');
}


if ($post['action'] == 'update') {

    /* Util::printVar($_POST, '138.121.84.107', true); */
    if (!$_POST['id']) die('');

    $telefono = Util::limpiarTelefono($_POST['telefono']);
    $varsPaciente = $_POST;
    $varsPaciente['telefono'] = $telefono;
    $varsPaciente['tabla'] = 'pacientes';
    $varsPaciente['idLabel'] = 'idPaciente';
    $varsPaciente['accion'] = 'actualizarPaciente';

    if (isset($_POST['fechaNacimiento'])) {
        $varsPaciente[] = date("Y-m-d", strtotime(str_replace('/', '-', $_POST['fechaNacimiento'])));
    }

    // Updateo el paciente
    $idPaciente = db_edit($varsPaciente, ["tratamientos", "plan", "cantidad"]);


    if ($general['bloquearTurnosAPacientesPorTratamiento']) {
        // Updateo el paciente
        db_edit($varsPaciente, ["tratamientos", "plan", "cantidad"]);

        // Elimino los tratamientos que tiene bloqueados 
        db_update("DELETE FROM pacientes_tratamientos WHERE idPaciente='" . $_POST['id'] . "'");

        // Creo los tratamientos que va a tener bloqueados
        $varsTratamientos['tabla'] = 'pacientes_tratamientos';
        $varsTratamientos['accion'] = 'agregarBloqueoDeTurnosAUnPaciente';
        $varsTratamientos['idPaciente'] = $_POST['id'];
        if (isset($_POST['tratamientos'])) {

            foreach ($_POST['tratamientos'] as $tratamiento) {
                /* Util::printVar($varsTratamientos, '186.138.206.135', false); */
                $varsTratamientos['idTratamiento'] = $tratamiento;
                db_edit($varsTratamientos);
            }
        }
    } else {
        db_edit($varsPaciente);
    }

    if ($general['creditos']) {
        // Actualizo el plan solo si lo cambio
        $creditosPlanes = db_getOne("SELECT idPlan FROM creditos_pacientes WHERE idPaciente = {$idPaciente}");
        if($creditosPlanes->idPlan != $_POST["plan"]){ 
            db_delete("delete from creditos_pacientes where idPaciente='" . $idPaciente . "'");

            //Busco la info del plan
            db_query(0, "select cantidad, modo, diaMes from creditos_planes where idPlan={$_POST['plan']}");

            if ($row['modo'] == 'A') {
                if ($row['diaMes'] == 'end') {
                    $row['diaMes'] = date("t", strtotime(date("Y-m-d")));
                }

                if ($row['diaMes'] < date("d")) {
                    $proximoVencimiento = date("Y-m-d", strtotime(date("Y-m") . "-" . $row['diaMes'] . " +1 month"));
                } else {
                    $proximoVencimiento = date("Y-m-d", strtotime(date("Y-m") . "-" . $row['diaMes']));
                }
            } else {
                $proximoVencimiento = '';
            }

            db_insert("insert into creditos_pacientes (idPaciente, idPlan, disponible, fechaAlta, proximoVencimiento, estado) values ('" . $idPaciente . "','" . $_POST['plan'] . "', '" . $row['cantidad'] . "',now(), '" . $proximoVencimiento . "', 0)");
        }
    }

    die("OK");
}


if ($post['action'] == 'delete') {
    if (!$_POST['id']) die('');

    db_update("UPDATE pacientes SET estado = 'B' WHERE idPaciente = " . $_POST['id']);
    db_update("UPDATE turnos SET eliminado = 1 WHERE idPaciente = " . $_POST['id']);

    db_log($_SESSION['usuario']['nombre'], 'eliminarPaciente', $_POST['id']);

    die("OK");
}



if ($post['action'] == 'guardarComision') {
    if ($post['obraSocial'] <> 0) {
        db_query(0, "select idComision from comisiones where idProfesional='" . $post['idProfesional'] . "' and idObraSocial='" . $post['obraSocial'] . "' and fechaAlta='" . date("Y-m-d") . "'");
        if ($tot > 0) {
            db_update("update comisiones set cantidad='" . $post['cantidad'] . "' where idProfesional='" . $post['idProfesional'] . "' and idObraSocial='" . $post['obraSocial'] . "' and idComision='" . $row['idComision'] . "'");

            db_log($_SESSION['usuario']['nombre'], 'actualizarComisionProfesional', $row['idComision']);
        } else {
            db_insert("insert into comisiones values ('', '" . $post['idProfesional'] . "', '" . $post['obraSocial'] . "', '" . $post['cantidad'] . "','" . date("Y-m-d") . "')");
            db_log($_SESSION['usuario']['nombre'], 'agregarComisionProfesional', $newid);
        }
        die("OK");
    }
}

if ($post['action'] == 'programarAumento') {
    db_query(1, "select idObraSocial from obrassociales");
    for ($i1 = 0; $i1 < $tot1; $i1++) {
        $nres1 = $res1->data_seek($i1);
        $row1 = $res1->fetch_assoc();
        db_insert("insert into comisiones values ('','" . $post['idProfesional'] . "','" . $row1['idObraSocial'] . "','" . $post['cantidad'] . "','" . date("Y-m-d", strtotime(str_replace('/', '-', $post['fecha']))) . "')");
    }

    db_log($_SESSION['usuario']['nombre'], 'programarAumento', $post['idProfesional']);

    die("OK");
}

if ($post['action'] == 'guardarHorarios') {

    if ($post['idProfesional'] <> '') {
        db_delete("delete from horariosprofesionales where idProfesional='" . $post['idProfesional'] . "'");
        foreach ($_POST['dia'] as $key => $dia) {
            db_insert("insert into horariosprofesionales values('','" . $_POST['idProfesional'] . "','" . $dia . "','" . $_POST['desdeManana'][$key] . "','" . $_POST['hastaManana'][$key] . "','" . $_POST['desdeTarde'][$key] . "','" . $_POST['hastaTarde'][$key] . "')");
        }

        db_log($_SESSION['usuario']['nombre'], 'guardarHorarios', $post['idProfesional']);

        die("OK");
    } else {
        die("Error");
    }
}


if ($post['action'] == 'saveOrden') {
    if ($post['tratamiento'] <> 0) {
        db_insert("insert into ordenes (idPaciente, diagnostico, cantidad, medico, idTratamiento, fechaAlta) values ('" . $post['idPaciente'] . "', '" . $post['diagnostico'] . "', '" . $post['cantidad'] . "', '" . $post['medico'] . "', '" . $post['tratamiento'] . "', '" . date("Y-m-d H:i:s") . "')");
        $idOrden = $newid;
        db_insert("insert into sesiones values('" . $newid . "','" . $post['cantidad'] . "')");
        db_log($_SESSION['usuario']['nombre'], 'guardarOrden', $idOrden);
        die('OK|' . $idOrden);
    } else {
        die('faltaTratamiento');
    }
}

if ($post['action'] == 'updateOrden') {
    if ($post['idOrden'] <> '') {
        db_update("update ordenes set diagnostico='" . $post['diagnostico'] . "', medico='" . $post['medico'] . "' where idOrden='" . $post['idOrden'] . "'");

        if ($post['cantidad'] <> '') {
            db_update("update ordenes set cantidad='" . $post['cantidad'] . "' where idOrden='" . $post['idOrden'] . "'");
            db_update("update sesiones set sesionesPendientes='" . $post['cantidad'] . "'");
        }

        if ($post['tratamiento'] <> '') {
            db_update("update ordenes set idTratamiento='" . $post['tratamiento'] . "' where idOrden='" . $post['idOrden'] . "'");
        }

        db_log($_SESSION['usuario']['nombre'], 'actualizarOrden', $post['idOrden']);

        die("OK");
    } else {
        die(' ');
    }
}

if ($post['action'] == 'deleteOrden') {
    db_delete("delete from ordenes where idOrden='" . $post['id'] . "' limit 1");
    db_delete("delete from sesiones where idOrden='" . $post['id'] . "' limit 1");

    db_log($_SESSION['usuario']['nombre'], 'eliminarOrden', $post['id']);

    die('OK');
}

if ($post['action'] == 'finishOrden') {
    db_update("update ordenes set estado=1 where idOrden='" . $post['id'] . "' limit 1");

    db_update("update turnos set estado=3 where idOrden='" . $post['id'] . "' and estado=0");

    db_log($_SESSION['usuario']['nombre'], 'terminarOrden', $post['id']);

    die('OK');
}

if ($post['action'] == 'reopenOrden') {
    db_update("update ordenes set estado='' where idOrden='" . $post['id'] . "' limit 1");

    db_log($_SESSION['usuario']['nombre'], 'reopenOrden', $post['id']);

    die('OK');
}

if ($post['action'] == 'getTurnos') {

    $comentarios = array();
    db_query(0, "select * from comentariosturnos");
    for ($i = 0; $i < $tot; $i++) {
        $nres = $res->data_seek($i);
        $row = $res->fetch_assoc();
        $comentarios[$row['idTurno']]['comentario'] = $row['comentarios'];
        $comentarios[$row['idTurno']]['usuario'] = $row['usuario'];
    }

    db_query(0, "select t.*, tra.nombre as tratamiento from turnos t, ordenes o, tratamientos tra where t.idPaciente='" . $post['idPaciente'] . "' and t.idOrden=o.idOrden and tra.idTratamiento=o.idTratamiento and t.eliminado<>1 order by t.fechaInicio ASC");
    for ($i = 0; $i < $tot; $i++) {
        $nres = $res->data_seek($i);
        $row = $res->fetch_assoc();
        if ($row['idOrden'] <> 0) {
            if ($row['estado'] == 0) {
                $color = 'info';
            }
            if ($row['estado'] == 1) {
                $color = 'success';
            }
            if ($row['estado'] == 2) {
                $color = 'warning';
            }
            if ($row['estado'] == 3) {
                $color = 'danger';
            }
        } else {
            $color = 'secondary';
        }
        $fechaInicioDelTurno = date("d/m/Y H:i", strtotime($row['fechaInicio']));

        if ($row['estado'] <> 3) {
            $turnosTomados[] = $row['idOrden'];
            $contadores = array_count_values($turnosTomados);
        }
        ?>
        <tr class="bg-<?= $color ?> text-white<?= ($ordenes[$row['idOrden']]['estado'] == 1) ? ' d-none ordenVieja' : '' ?>">
            <td><?= $fechaInicioDelTurno ?></td>
            <?
            if ($row['idOrden'] <> 0) {
            ?>
                <td><?= $row['tratamiento'] ?></td>
            <?
            } else {
            ?>
                <td>-</td>
            <?
            }
            ?>
        </tr>
        <?
        if (($row['estado'] == 3) and (trim($comentarios[$row['idTurno']]['comentario']) <> '')) {
        ?>
            <tr class="<?= ($ordenes[$row['idOrden']]['estado'] == 1) ? ' d-none ordenVieja' : '' ?>">
                <td class="table-danger" colspan="3">
                    <i class="fa fa-comment-o fa-fw"></i> <?= $comentarios[$row['idTurno']]['comentario'] ?> <?= (trim($comentarios[$row['idTurno']]['usuario']) <> '') ? '<i class="fa fa-user-circle-o"></i> ' . $comentarios[$row['idTurno']]['usuario'] : '' ?>
                </td>
            </tr>
        <?
        }
    }
}

if ($post['action'] == 'getPlanes') {
    db_query(0, "select * from planes where idObraSocial='" . $post['idObraSocial'] . "' order by nombre ASC");
    if ($tot > 0) {
        for ($i = 0; $i < $tot; $i++) {
            $nres = $res->data_seek($i);
            $row = $res->fetch_assoc();
        ?>
            <option value="<?= $row['idPlan'] ?>" <?= ($post['idPlan'] == $row['idPlan']) ? ' selected' : '' ?>><?= $row['nombre'] ?></option>
<?
        }
    } else {
        die("Todos");
    }
}

if ($post['action'] == 'checkPax') {

    HTTPController::responseInJSON(PacienteController::checkPax($_POST['value'], $general['tomaTurno'], $_POST['codArea']));
}

if ($post['action'] == 'checkPaxModal') {
    Turno::modalPedidoExternal($_POST['value']);
}

if ($post['action'] == 'getPacientes') {
    HTTPController::responseInJSON(PacienteController::getPacientes($_GET));
}

if ($post['action'] == 'getPacientesSelect') {
    // Util::printVar($_GET);

    HTTPController::responseInJSON(PacienteController::getPacientesSelect());
}


/* ------------------------- */
/*          CREDITOS         */
/* ------------------------- */
if ($post['action'] == 'updateRecarga') {
    $vars['tabla'] = 'creditos_pacientes';
    $vars['idLabel'] = 'idPaciente';
    $vars['accion'] = 'actualizarCreditosPlanes';
    $vars['id'] = $post['idPaciente'];
    $vars['disponible'] = $post['cantidad'];

    db_edit($vars);
    db_log($_SESSION['usuario']['nombre'],'creditosDelPacienteModificados',$vars['id']);
    HttpController::responseInJSON(["status" => "OK"]);
    die();
}

?>