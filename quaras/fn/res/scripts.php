<?=$general['codigoBody']?>

<!-- Essential javascripts for application to work-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
<script src="<?=$cdn?>/js/main.min.js?v=<?=rand()?>"></script>
<!-- The javascript plugin to display page loading on top-->
<script src="<?=$cdn?>/js/plugins/pace.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/select2.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/bootstrap-notify.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/select2.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/jquery-ui.custom.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/chart.js"></script>		

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/i18n/defaults-es_ES.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>

<? if(AuthController::isLogged() && AuthController::isAdmin()){ ?>
    <!-- Begin of Chaport Live Chat code -->
    <script type="text/javascript">
        if(window.self === window.top){
            (function(w,d,v3){
            w.chaportConfig = {
            appId : '64c3db9cb982316819cdb4ac'
            };

            if(w.chaport)return;v3=w.chaport={};v3._q=[];v3._l={};v3.q=function(){v3._q.push(arguments)};v3.on=function(e,fn){if(!v3._l[e])v3._l[e]=[];v3._l[e].push(fn)};var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://app.chaport.com/javascripts/insert.js';var ss=d.getElementsByTagName('script')[0];ss.parentNode.insertBefore(s,ss)})(window, document);
        }
    </script>
    <!-- End of Chaport Live Chat code -->
<? } ?>