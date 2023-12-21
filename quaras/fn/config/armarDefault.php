<?
$miPhp='<?
$general=array(
  ';
  $config=json_decode(file_get_contents(fn.'/config/config.json'),true);

    foreach($config as $key=>$value){
        if($value['comentarios']){
        $miPhp.="\n
        //".$value['comentarios']."
        ";
        }
        $miPhp.="
        '".$value['clave']."'=>'".$value['valor']."',";
    }

  $miPhp.='
);
?>';

file_put_contents(fn.'/config/default.php',$miPhp);
?>