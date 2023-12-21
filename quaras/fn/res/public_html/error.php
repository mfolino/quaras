<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion="Turnos";
        $subseccion='';
        require_once($_SERVER['DOCUMENT_ROOT'].'/inc/head.php');
        ?>
    </head>
    <body>
        <section class="material-half-bg">
            <div class="cover"></div>
        </section>
        <section class="login-content">
            <div class="logo mt-5">
                <img src="/img/<?=$general['isologo']?>" width="<?=$general['logoWidth']?>">
            </div>
            <div class="login-box text-center">
                <div class="row my-5 mx-5">
                    <div class="col">
                        <h1 style="font-size:5rem;"><i class="far fa-circle-question"></i></h1>
                        <h1>No encontrado</h1>
                        <p>La página que estás buscando no está disponible o no existe.</p>
                        <a href="/" class="btn btn-primary btn-block mt-4">Volver al inicio</a>
                    </div>
                </div>
            </div>
            <?
            require_once($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
            ?>
        </section>

        <?
        require_once($_SERVER['DOCUMENT_ROOT'].'/inc/scripts.php');
        ?>
		
    </body>
</html>