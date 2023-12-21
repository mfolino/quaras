<?

class Migration{
    
    private $tableName;

    public function __construct($tableName){
        $this->tableName = $tableName;
    }

    public static function existTableInDB($table){
        GLOBAL $tot2;
        db_query(2,"SHOW TABLES LIKE '{$table}'");
        return $tot2 > 0;
    }
    public static function existColumn($table, $column){
        GLOBAL $tot;
        db_query(0, "SHOW COLUMNS FROM {$table} WHERE Field = '{$column}'");
        return $tot > 0;
    }

    public function addColumn($columnName, $type , $size = 0, $comment = ''){
        GLOBAL $tot, $res, $row, $nres;

        try {
            $size = $size > 0 ? "({$size})" : 0;
            $comment = $comment ? "COMMENT '{$comment}'" : '' ;

            db_query(0, "SHOW COLUMNS FROM {$this->tableName} WHERE Field = '{$columnName}'");
            // Agrego la columna si tot no devuelve nada
            if(!$tot){
                db_query(0, 
                    "ALTER TABLE {$this->tableName} 
                    ADD COLUMN {$columnName} {$type}{$size} NOT NULL {$comment}
                ");
            }
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    public function existTable(){
        GLOBAL $tot2;
        db_query(2,"SHOW TABLES LIKE '{$this->tableName}'");
        return $tot2 > 0;
    }

    public function createTable($sql){
        try {
            db_query(0, $sql);
            return true;
        } catch (Exception $th) {
            return false;
        }
    }
    // TODO: Agregar un metodo para crear tablas

}