<?php

require __DIR__ . '/autoload.php'; //Nota: si renombraste la carpeta a algo diferente de "ticket" cambia el nombre en esta línea
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

/*
Este ejemplo imprime un hola mundo en una impresora de tickets
en Windows.
La impresora debe estar instalada como genérica y debe estar
compartida
 */

/*
Conectamos con la impresora
 */

/*
Aquí, en lugar de "POS-58" (que es el nombre de mi impresora)
escribe el nombre de la tuya. Recuerda que debes compartirla
desde el panel de control
 */







 //QR
 /*
ID
Tipo:  entrada, bar, etc

{id:SG9sYVNveVF1YXJhcw==,Key:aaaa1111,Tipo:ZW50cmFkYQ==}


e2lkOlNHOXNZVk52ZVZGMVlYSmhjdz09LEtleTphYWFhMTExMSxUaXBvOlpXNTBjbUZrWVE9PX0=
*/


/**
 * TIPOS DE BOLETO:
 *      1 = entrada al parque
 */
$idGrupo = $_GET['idGrupo'];
$tipoBoleto = 1;

$json = "{{$idGrupo}}-{$tipoBoleto}}";



$nombre_impresora = "Quaras";

$connector = new WindowsPrintConnector($nombre_impresora);
$printer = new Printer($connector);
$printer->setJustification(Printer::JUSTIFY_CENTER);

$logo = EscposImage::load("logo.png");
$printer->bitImage($logo);

/*
Imprimimos un mensaje. Podemos usar
el salto de línea o llamar muchas
veces a $printer->text()
 */
// $printer->setTextSize(2, 2);
$printer->setFont(Printer::FONT_A);
// $printer->text("PARQUE DE MONTAÑA");

$printer->feed();

$printer->qrCode($json, Printer::QR_ECLEVEL_L, 16, Printer::QR_MODEL_2);
$printer->feed();






$printer->setTextSize(2, 2);

$printer->text("1 entrada general\n".date('d/m/Y'));

$printer->feed();

$printer->setTextSize(1, 1);

$printer->text("\nGracias por tu compra\n\nEsta entrada es de un solo uso en el día\nde la fecha impresa en el ticket.\n\n");

$printer->setUnderline(Printer::UNDERLINE_SINGLE);

$printer->text("Importante:\n");
$printer->setUnderline(Printer::UNDERLINE_NONE);

$printer->text("Utilizar calzado adecuado.\nProhibido el ingreso al parque aventura con\ncrocs, ojotas, botines, sandalias o alpargatas.\n");

$printer->feed();

$printer->setFont(Printer::FONT_B);
$printer->setTextSize(2, 2);
$printer->text("WWW.QUARAS.COM.AR\n");
$printer->setTextSize(1, 1);
$printer->text("+54 (354) 654-1211 - Ruta 5 km 80.5, Villa General Belgrano\n\nPowered by Turnos.app");
/*
Hacemos que el papel salga. Es como
dejar muchos saltos de línea sin escribir nada
 */
$printer->feed();
// $printer->feed(15);

/*
Cortamos el papel. Si nuestra impresora
no tiene soporte para ello, no generará
ningún error
 */
$printer->cut();

/*
Por medio de la impresora mandamos un pulso.
Esto es útil cuando la tenemos conectada
por ejemplo a un cajón
 */
// $printer->pulse();

/*
Para imprimir realmente, tenemos que "cerrar"
la conexión con la impresora. Recuerda incluir esto al final de todos los archivos
 */
$printer->close();
