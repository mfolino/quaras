<meta name="description" content="Sistema de turnos y estadísticas <?=$general['nombreCliente']?>.">
<title><?=$seccion?><?=($subseccion<>'') ? ' - '.$subseccion : ''?> | <?=$general['nombreCliente']?></title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/png" href="/img/<?=$general['favicon']?>">
<!-- Main CSS-->
<link rel="stylesheet" type="text/css" href="/quaras/fn/res/css/variables.css.php">
<link rel="stylesheet" type="text/css" href="<?=$cdn?>/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="<?=$cdn?>/css/main.min.css?v=<?=rand()?>">
<link rel="stylesheet" type="text/css" href="/quaras/fn/res/css/custom.css?v=<?=time()?>">

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">

<?=$general['codigoHead']?>

		
<?
if($general['smartlookId']){
    ?>
    <script type='text/javascript'> window.smartlook||(function(d) { var o=smartlook=function(){ o.api.push(arguments)},h=d.getElementsByTagName('head')[0]; var c=d.createElement('script');o.api=new Array();c.async=true;c.type='text/javascript'; c.charset='utf-8';c.src='https://web-sdk.smartlook.com/recorder.js';h.appendChild(c); })(document); smartlook('init', '<?=$general['smartlookId']?>', { region: '<?=$general['smartlookRegion']?>' }); </script>
    <?
}
if($general['codigoAnalytics']){
    echo $general['codigoAnalytics'];
}

if($general["admin_fondoLogo"]){ ?>
    <style>
        .app-header__logo{
            background-color: #<?=$general["admin_fondoLogo"]?>;
        }
    </style>
<? } ?>


