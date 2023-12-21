<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

AuthController::checkLogin();
AuthController::checkSuperAdmin();

$_GET['id'] = $_GET['id'] ?? false;

if($_GET['id']){
    db_query(0,"select * from categorias where idCategoria=".$_GET['id']);
    db_query(1,"select idTratamiento from categorias_tratamientos where idCategoria='".$row['idCategoria']."'");
    for($i1=0;$i1<$tot1;$i1++){
        $nres1=$res1->data_seek($i1);
        $row1=$res1->fetch_assoc();
        $tratamientos[$row1['idTratamiento']]=1;
    }
    $titulo='Editar';
    $icono='pen-to-square';
}else{
    $titulo='Agregar';
    $icono='plus';
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion='Categorías';
        $subseccion=$titulo;
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
                    <h1><i class="fas fa-list-alt"></i> <?=ucfirst($general['nombreCategorias'])?></h1>
                    <p>Utilice este listado para ver de un rápido vistazo las <?=$general['nombreCategorias']?> y administrarlas.</p>
                </div>
                <?/*<ul class="app-breadcrumb breadcrumb side">
                    <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                    <li class="breadcrumb-item active"><a href="#">Profesionales</a></li>
                </ul>*/?>
                <a class="btn btn-outline-warning icon-btn" href="/obrasSociales/categorias"><i class="fas fa-arrow-left"></i>Volver atrás</a>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="tile">
            <h3 class="tile-title"><i class="fas fa-<?=$icono?>"></i> <?=$titulo?> <?=$general['nombreCategoria']?></h3>
            <div class="tile-body">
              <form id="formulario">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                          <label class="control-label">Nombre</label>
                          <input class="form-control required" type="text" placeholder="<?=ucfirst($general['nombreCategoria'])?>" name="nombre" value="<?=$row['nombre']??''?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                          <div class="toggle-flip">
                                <p class="mb-2">Estado</p>
                                <label>
                                    <input 
                                        type="checkbox" 
                                        name="estado" 
                                        value="A" 
                                        <?= isset($row['estado']) && (($row['estado']=='A')or($row['estado']=='')) ? 'checked' : ''?>
                                    >
                                    <span class="flip-indecator" data-toggle-on="Activa" data-toggle-off="Inactiva"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                        <label class="control-label"><?=ucwords($general['nombreObrasSociales'])?></label>
                          <select multiple="true" class="form-control w-100" name="servicios[]" id="servicios" style="width:100%">
                            <?
                            db_query(1,"select idTratamiento, nombre from tratamientos where estado='A'");
                            for($i1=0;$i1<$tot1;$i1++){
                                $nres1=$res1->data_seek($i1);
                                $row1=$res1->fetch_assoc();
                                ?>
                                <option 
                                    value="<?=$row1['idTratamiento']?>"
                                    <?=(isset($tratamientos) && $tratamientos[$row1['idTratamiento']]) ? ' selected' : ''?>><?=$row1['nombre']?>
                                </option>
                                <?
                            }
                            ?>
                          </select>
                        </div>
                    </div>
                </div>
                
                <?
                if($_GET['id']){
                    $tratamientos=[];
                ?>
                <input type="hidden" name="id" value="<?=$_GET['id']?>" />
                <?
                }
                ?>

                <input type="hidden" name="action" value="saveCategoria" />
                
              </form>
            </div>
            <div class="tile-footer">
              <button class="btn btn-primary" type="button" id="enviarForm"><i class="fa fa-fw fa-lg fa-check-circle"></i>Guardar</button>&nbsp;&nbsp;&nbsp;<a class="btn btn-secondary" href="/obrasSociales/categorias"><i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar</a>
            </div>
          </div>
                </div>
            </div>
        </main>
		
        <?
        require_once(incPath.'/scripts.php');
        ?>
        
        <script>
            $("#enviarForm").click(function(){
                
                algunoMal=0;
                $(".required").each(function(key){
                    if($(this).val().length<2){
                        $(this).addClass('is-invalid');
                        $(this).removeClass('is-valid');
                        algunoMal=1;
                    }else{
                        $(this).addClass('is-valid');
                        $(this).removeClass('is-invalid');
                    }
                })
				
                //Si está todo bien submiteo
                if(algunoMal==0){
                    $.post('/obrasSociales/save.php',$("#formulario").serialize(),function(response){
                        
                        if(response.status=='OK'){
                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'La categoría ha sido guardada con éxito'
                            },{
                                // settings
                                type: "success",
                                allow_dismiss: true,
                                newest_on_top: false,
                                showProgressbar: false,
                                onClose: window.location.href='/obrasSociales/categorias',
                                delay:6000
                            });
                        }else{

                            console.log(response);

                            $.notify({
                                // options
                                icon: 'fa fa-check',
                                title: '',
                                message: 'Ha ocurrido un error al intentar guardar la categoría. '
                            },{
                                // settings
                                type: "warning",
                                allow_dismiss: true,
                                newest_on_top: false,
                                showProgressbar: false
                            });
                        }
                    })
                }
				
            })

            $("#servicios").select2();
        </script>
		
        <?
        // include(incPath.'/analytics.php');
        ?>
    </body>
</html>