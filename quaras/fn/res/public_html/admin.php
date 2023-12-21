<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');

if(@$_SESSION['usuario']['logueado']==1){
    header('location:/panel');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <?
        $seccion='Acceso';
        require_once('inc/head.php');
        ?>
    </head>
    <body>
        <section class="material-half-bg">
            <div class="cover"></div>
        </section>
        <section class="login-content">
            <div class="logo">
                <img src="img/<?=$general['isologo']?>" width="<?=$general['logoWidth']?>">
            </div>
            <div class="login-box">
                <form class="login-form">
                    <h3 class="login-head"><i class="fa fa-lg fa-fw fa-user"></i>ACCEDER</h3>
                    <div class="form-group">
                        <label class="control-label">E-MAIL</label>
                        <input class="form-control campo-login" type="email" placeholder="E-mail" autofocus id="email" name="email">
                    </div>
                    <div class="form-group">
                        <label class="control-label">CONTRASEÑA</label>
                        <input class="form-control campo-login" type="password" placeholder="Password" id="password" name="password">
                    </div>

                    <div class="form-group btn-container">
                        <input class="form-control" type="hidden" id="action" name="action" value="login" />
                        <button class="btn btn-primary btn-block loginBtn"><i class="fa fa-sign-in fa-lg fa-fw"></i>INGRESAR</button>
                    </div>
                </form>

            </div>
            <?
            require_once($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php');
            ?>
        </section>
		
        <? require_once('inc/scripts.php'); ?>

        <script type="text/javascript">
            function isEmail(email) {
                var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                return regex.test(email);
            }
		
            // Login Page Flipbox control
            $('.login-content [data-toggle="flip"]').click(function() {
                $('.login-box').toggleClass('flipped');
                return false;
            });
            $('.campo-login').keyup(function(e){
				
                var elValor=$(this).val();
                var quienEs=$(this).attr('id');
				
                if(elValor.length>3){
                    if(quienEs=='email'){
                        if(isEmail(elValor)){
                            $('#'+quienEs).removeClass('is-invalid');
                            $('#'+quienEs).addClass('is-valid');
                        }else{
                            $('#'+quienEs).removeClass('is-valid');
                            $('#'+quienEs).addClass('is-invalid');
                        }
                    }
                    if(quienEs=='password'){
                        $('#'+quienEs).removeClass('is-invalid');
                        $('#'+quienEs).addClass('is-valid');
                    }
                }else{
                    $('#'+quienEs).removeClass('is-invalid');
                    $('#'+quienEs).removeClass('is-valid');
                    if(quienEs=='password'){
                        $('#'+quienEs).addClass('is-invalid');
                        $('#'+quienEs).removeClass('is-valid');
                    }
                }
            })
            $('.loginBtn').click(function(e){
                e.preventDefault();
                var algunoMal=0;
                var algunoBien=0;
                $.each($('.campo-login'),function(key,element){
                    if($(element).hasClass('is-invalid')){
                        algunoMal++;
                    }
                    if($(element).hasClass('is-valid')){
                        algunoBien++;
                    }
                })
				
                if((algunoMal<1)&&(algunoBien==$('.campo-login').length)){
                    $.post(
                        '/profesionales/save',
                        $('.login-form').serialize(),
                        function(response){

                            if(response.status=='OK'){
                                window.location.href="panel";
                            }else{
                                if(response.status=='error'){
                                    $.notify({
                                        title: "Error al ingresar: ",
                                        message: "Su usuario y contraseña no coinciden.",
                                        icon: 'fa fa-check' 
                                    },{
                                        type: "danger",
                                        delay: 2000,
                                        timer: 1000,
                                        animate: {
                                            enter: 'animated fadeInDown',
                                            exit: 'animated fadeOutUp'
                                        }
                                    });
                                }else{
                                    console.log(response);
                                }
                            }
                        }
                    )
                }
            })
        </script>
    </body>
</html>