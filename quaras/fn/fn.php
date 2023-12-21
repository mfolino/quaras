<?

$fn = $_SERVER["DOCUMENT_ROOT"].'/quaras/';


//BASE DE DATOS
require_once ($fn.'fn/db/db.inc.php');

//Traigo las variables de configuracion
require_once ($fn.'fn/config/default.php');

require_once ($fn.'fn/config/general.php');

//Funciones varias
require_once ($fn.'fn/misc/init.php');

require_once ($fn.'fn/misc/pax.php');
require_once ($fn.'fn/misc/turnos.php');
require_once ($fn.'fn/misc/bloqueos.php');
require_once ($fn.'fn/misc/feriados.php');


require_once ($fn.'fn/misc/Util.php');
require_once ($fn.'fn/controllers/DateController.php');
require_once ($fn.'fn/controllers/AuthController.php');
require_once ($fn.'fn/controllers/HTTPController.php');
require_once ($fn.'fn/controllers/SecurityDatabaseController.php');

require_once ($fn.'fn/misc/PacienteController.php');
require_once ($fn.'fn/misc/ProfesionalController.php');
require_once ($fn.'fn/misc/FeriadoController.php');
require_once ($fn.'fn/misc/TratamientoController.php');
require_once ($fn.'fn/misc/VentaController.php');
require_once ($fn.'fn/misc/SuplenciaController.php');


// require_once ($fn.'fn/notifications/NotificationWhatsapp.php');
// require_once ($fn.'fn/notifications/NotificationSMS.php');
// require_once ($fn.'fn/notifications/Notification.php');


require_once ($fn.'fn/misc/Menu.php');
require_once ($fn.'fn/misc/Turno.php');
require_once ($fn.'fn/misc/Pagos.php');


require_once ($fn.'fn/misc/Meetings.php');

// Migrations
require_once ($fn.'fn/misc/migrations/Enum_types_column.php');
require_once ($fn.'fn/misc/migrations/Migration.php');




require_once ($fn.'fn/misc/QR.php');
?>