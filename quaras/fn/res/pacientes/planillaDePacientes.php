<?php

    db_query(0,"SELECT * FROM pacientes WHERE estado <> 'B' ");

    $pacientes = [];
    for($i=0;$i<$tot;$i++){
        $nres=$res->data_seek($i);
        $row=$res->fetch_assoc();
        $pacientes[]=$row;
    }

    header("Content-Type: application/xls");
    header("Content-Disposition: attachment; filename=planillaDePacientes.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>Nombre</th>";
    echo "<th>Apellido</th>"; 
    echo "<th>Telefono</th>"; 
    echo"<th>Mail</th>";
    echo "</tr>";

    foreach ($pacientes as $paciente) {
        echo "<tr>";
        echo "<td>".$paciente['nombre']."</td>";
        echo "<td>".$paciente['apellido']."</td>";
        echo "<td>".$paciente['telefono']."</td>";
        echo "<td>".$paciente['mail']."</td>";
        echo "</tr>";
    }
    echo "</table>";


?>
