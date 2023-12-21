<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();
?>
<!DOCTYPE html>
<html>
    <head>
        <?
        $seccion='Ayuda';
        $subseccion='QR';
        require_once(incPath.'/head.php');
        ?>
    </head>
    <body>
        <section class="material-half-bg">
            <div class="cover"></div>
        </section>
        <section class="login-content">
            <div class="logo">
                <img src="/img/<?=$general['isologo']?>" width="<?=$general['logoWidth']?>" />
            </div>
            <div class="login-box px-5 py-5 text-center">
                 <h1 class="login-head pb-5" style="font-size:3.25rem"><i class="far fa-lg fa-fw fa-calendar-plus"></i><b>Reservá tu turno</b></h1>
                <div class="form-group">
                    <?=QR::crear()?>
                    <h1 style="font-size:3.5rem"><b><?=$general['clientDomain']?></b></h1>
                </div>
            </div>
            <?
            require_once($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
            ?>
        </section>
		
        <? 
        require_once(incPath.'/scripts.php');
        ?>

        <script>
            window.print();
        </script>

    </body>
</html>