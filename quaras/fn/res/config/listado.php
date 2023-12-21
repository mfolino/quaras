<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();
AuthController::checkSuperAdmin();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion='Configuración';
        $subseccion='Listado';
        require_once(incPath.'/head.php');
        ?>
    </head>
    <body class="app sidebar-mini rtl">
        <?
        require_once(incPath.'/header.php');
        require_once(incPath.'/sidebar.php');
        ?>
        <main class="app-content">
            <div class="app-title">
                <div>
                    <h1><i class="fas fa-cogs"></i> Configuración</h1>
                    <p>Utilice esta sección para configurar su app de turnos.</p>
                </div>
                <div>
                    <button class="btn btn-primary icon-btn" type="button" id="guardarTodo"><i class="fas fa-save"></i> Guardar todo</a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="tile">
                        <div class="tile-body">
                            <form id="camposConfig">
                                <div class="row">
                                    <?
                                    db_query(0,"select * from config where estado=1 and userEdit=1 order by clave asc");
                                    for($i=0;$i<$tot;$i++){
                                        $nres=$res->data_seek($i);
                                        $row=$res->fetch_assoc();
                                        if($row['userEditTipo']=='textarea'){
                                            ?>
                                            <div class="col-12 border-bottom pb-3 pt-3">
                                                <label for="<?=$row['clave']?>"><b><?=$row['comentarios']?></b></label>
                                                <textarea class="form-control" id="<?=$row['clave']?>" name="<?=$row['clave']?>" rows="3" placeholder=""><?=preg_replace('#<br\s*/?>#i', "\n", $row['valor'])?></textarea>
                                            </div>
                                            <?
                                        }
                                        if($row['userEditTipo']=='select'){
                                            ?>
                                            <div class="col border-bottom pb-3">
                                                <label for="<?=$row['clave']?>"><b><?=$row['comentarios']?></b></label>
                                                <select class="form-control" id="<?=$row['clave']?>" name="<?=$row['clave']?>">
                                                    <?
                                                    if (strpos($row['userEditValidate'], '-') !== false) {
                                                        //Es un rango
                                                        $opciones=explode('-',$row['userEditValidate']);
                                                        for($j=$opciones[0];$j<=$opciones[1];$j++){
                                                            ?>
                                                            <option value="<?=$j?>" <?if($j==$row['valor']){?>selected<?}?>><?=$j?></option>
                                                            <?
                                                        }
                                                    }else{
                                                        $opciones=explode(',',$row['userEditValidate']);
                                                        foreach($opciones as $opcion){
                                                            ?>
                                                            <option value="<?=$opcion?>"<?if($row['valor']==$opcion){?> selected<?}?>><?=$opcion?></option>
                                                            <?
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <?
                                        }
                                    }
                                    ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
		
        <?
        require_once(incPath.'/scripts.php');
        ?>
		
        <script>
            
        </script>
        
    </body>
</html>