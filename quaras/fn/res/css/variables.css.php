<?

$_SERVER["DOCUMENT_ROOT"] = $_SERVER["DOCUMENT_ROOT"]."/quaras";
$cdn = "//turnos.app/assets/app"; // No sé de donde sale esto, lo dejo por las dudas
require_once($_SERVER['DOCUMENT_ROOT'].'/fn/fn.php');
header('Content-Type: text/css');
?>
:root {
  --blue: #007bff;
  --indigo: #6610f2;
  --purple: #6f42c1;
  --pink: #e83e8c;
  --red: #dc3545;
  --orange: #fd7e14;
  --yellow: #ffc107;
  --green: #28a745;
  --teal: #20c997;
  --cyan: #17a2b8;
  --white: #FFF;
  --gray: #6c757d;
  --gray-dark: #343a40;
  --primary: #<?=$general['colorPrimario']?>;
  --primaryHover: #<?=$general['colorPrimarioHover']?>;
  --secondary: #<?=$general['colorSecundario']?>;
  --success: #28a745;
  --info: #17a2b8;
  --warning: #ffc107;
  --danger: #dc3545;
  --light: #f8f9fa;
  --dark: #343a40;
  --breakpoint-xs: 0;
  --breakpoint-sm: 576px;
  --breakpoint-md: 768px;
  --breakpoint-lg: 992px;
  --breakpoint-xl: 1200px;
  --font-family-sans-serif: "Hint", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  --font-family-monospace: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  --logoChico: url(https://<?=$general['clientDomain']?>/img/<?=$general['logo']?>);
  --logoWidth: <?=$general['logoAdminWidth']?>%;
  --favicon: url(https://<?=$general['clientDomain']?>/img/<?=$general['favicon']?>);
  <?
  if($general['fondoAbajo']){
    ?>
    --material-half-top: 50%;
    <?
  }else{
    ?>
    --material-half-top: 0%;
    <?
  }
  ?>
}