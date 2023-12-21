<?
db_query(0,"select clave, valor from config where estado=1");
for($i=0;$i<$tot;$i++){
    $nres=$res->data_seek($i);
    $row=$res->fetch_assoc();
    $general[$row['clave']] = $row['valor'];
}

?>