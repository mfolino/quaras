<?
require_once $_SERVER["DOCUMENT_ROOT"].'/quaras/fn/fn.php';

db_query(0, "SELECT fechahora FROM lastupdate");
$ultimoLlamado = $row['fechahora'];

$entorno = 'remoto';

db_query(0, "SELECT fechahora FROM log WHERE fechahora>'{$ultimoLlamado}' ORDER by fechahora DESC LIMIT 1");
if($tot>0){
    $update = 1;
}else{
    $update = 0;
}

$entorno = '';

//if($update){
//$tablas



//Este lo ejecuto solo post actualizar
// db_update("UPDATE lastupdate SET fechahora=NOW()");





$tables = array('tratamientos', 'tratamientos_valores', 'bloqueos', 'horariosprofesionales', 'feriados', 'mediosdepago', 'promociones');

Export_Database($tables);

function Export_Database($tables=false, $backup_name=false ){

    global $entorno;
    global $tot;
    global $res;
    global $row;
    
    $entorno = 'remoto';

    db_query(0, "SHOW TABLES");
    for($i=0;$i<$tot;$i++){
        $nres = $res->data_seek($i);
        $row=$res->fetch_assoc();
        $target_tables[] = $row[0]; 
    }

    echo '<pre>';
    print_r($target_tables);
    echo '</pre>';
    die();

    while($row = $queryTables->fetch_row()) 
    { 
        $target_tables[] = $row[0]; 
    }   
    if($tables !== false) 
    { 
        $target_tables = array_intersect( $target_tables, $tables); 
    }
    foreach($target_tables as $table)
    {
        $result         =   $mysqli->query('SELECT * FROM '.$table);  
        $fields_amount  =   $result->field_count;  
        $rows_num=$mysqli->affected_rows;     
        $res            =   $mysqli->query('SHOW CREATE TABLE '.$table); 
        $TableMLine     =   $res->fetch_row();
        $content        = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";

        for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) 
        {
            while($row = $result->fetch_row())  
            { //when started (and every after 100 command cycle):
                if ($st_counter%100 == 0 || $st_counter == 0 )  
                {
                        $content .= "\nINSERT INTO ".$table." VALUES";
                }
                $content .= "\n(";
                for($j=0; $j<$fields_amount; $j++)  
                { 
                    $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); 
                    if (isset($row[$j]))
                    {
                        $content .= '"'.$row[$j].'"' ; 
                    }
                    else 
                    {   
                        $content .= '""';
                    }     
                    if ($j<($fields_amount-1))
                    {
                            $content.= ',';
                    }      
                }
                $content .=")";
                //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) 
                {   
                    $content .= ";";
                } 
                else 
                {
                    $content .= ",";
                } 
                $st_counter=$st_counter+1;
            }
        } $content .="\n\n\n";
    }
    //$backup_name = $backup_name ? $backup_name : $name."___(".date('H-i-s')."_".date('d-m-Y').")__rand".rand(1,11111111).".sql";
    $backup_name = $backup_name ? $backup_name : $name.".sql";
    header('Content-Type: application/octet-stream');   
    header("Content-Transfer-Encoding: Binary"); 
    header("Content-disposition: attachment; filename=\"".$backup_name."\"");  
    echo $content; exit;
}
?>