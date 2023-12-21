<?
header('Access-Control-Allow-Origin: *');
$qr = file_get_contents("php://input");

$buscar = array('{', '}', 'Shift');
$reemplazar = array('', '', '');

$qr = str_replace($buscar, '', $qr);

$data = explode('-', $qr);
$tipo = $data[1];
$id = $data[0];

if($id == 'AAaQ1111'){
    http_response_code(200);
}else{
    http_response_code(403);
}
?>