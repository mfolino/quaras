<?
$cual = $_GET['id'];
$operacion = $_GET['op'];
shell_exec('hidusb-relay-cmd.exe ID=BITFT '.$operacion.' '.$cual);
die();


// declare(strict_types=1);
$ffi = FFI::cdef(
    '
    unsigned usb_relay_init();
    unsigned usb_relay_device_free_enumerate();
    unsigned usb_relay_device_enumerate();
    unsigned long usb_relay_device_open();
    unsigned long usb_relay_device_open_with_serial_number();
    unsigned long usb_relay_device_open_one_relay_channel();
    unsigned long usb_relay_device_open_all_relay_channel(); 
    unsigned long usb_relay_device_close_one_relay_channel();
    unsigned long usb_relay_device_close_all_relay_channel();
    unsigned long usb_relay_device_get_status();
    unsigned long usb_relay_device_close();
    unsigned long usb_relay_exit();
    ',
    "C:\\usb_relay_device.dll"
);

// $ffi = FFI::load('C:\\usb_relay_device.h');

/*$ffi = FFI::cdef(
    file_get_contents('usb_relay_device.h')
);*/

//Inicializo el dispositivo
var_dump($ffi->usb_relay_init());


//Devuelve dispositivos conectados
$serial = $ffi->usb_relay_device_enumerate();
echo '<pre>';
print_r($serial);
echo '</pre>';





//Tomo el nuestro

//Abro el dispositivo
var_dump($ffi->usb_relay_device_open());


//Abro todos los relays
var_dump($ffi->usb_relay_device_open_all_relay_channel());

echo '<pre>';
print_r($ffi->usb_relay_device_get_status());
echo '</pre>';


//Espero 2 segundos
// sleep(2);

//Cierro todos los relays
var_dump($ffi->usb_relay_device_close_all_relay_channel());


// Limpio la lista
// print_r($ffi->usb_relay_device_free_enumerate());


//Finalizo la conexión
$ffi->usb_relay_exit();
?>