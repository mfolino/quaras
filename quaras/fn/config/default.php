<?
$general=array(
        'colorPrimario'=>'000000',
        'colorSecundario'=>'5b5b5b',
        'isologo'=>'logo.png',
        'logo'=>'logo.png',
        'nombreCliente'=>'Main Master',
        'nombrePacientes'=>'clientes',
        'nombreProfesionales'=>'profesionales',
        'nombreObrasSociales'=>'servicios',
        'nombreCategorias'=>'categorias',
        'estadoPendiente'=>'Pendiente',
        'estadoCancelado'=>'Cancelado',
        'estadoConfirmado'=>'Confirmado',
        'estadoAusente'=>'Ausente',
        'colorPrimarioHover'=>'427d9b',
        'nombrePaciente'=>'cliente',
        'nombreProfesional'=>'profesional',
        'nombreObraSocial'=>'servicio',
        'nombreCategoria'=>'categoría',
        'nombreTurno'=>'turno',
        'nombreTurnos'=>'turnos',
        'favicon'=>'favicon.png',
        'clientDomain'=>'main.turnos.app',
        'hsAntesCancelacion'=>'1',
        'horasAnticipacion'=>'72',
        'minutosAnticipacion' => '', // Esta variable pisa a horasAnticipacion
        'prefijoTelefonico'=>'54',
        'leyendaTelefono'=>'Sin 0 ni 15. Ingrese sólo números.',
        'leyendaMail'=>'Agradeceremos tu puntualidad para poder atenderte adecuadamente.',
        'leyendaMail_mensajeCancelacion' => '',
        'mailFirmaProfesional'=>'0',
        'mailTextoArriba'=>'Hola %nombre%, este es un recordatorio automático del turno solicitado ',
        'nombreDNI'=>'DNI',
        'largoDNI'=>'8',
        'codAreaDefault'=>'223',
        'mailTextoArribaConfirmacion'=>'Hola %nombre%, te confirmamos el turno solicitado',
        'mailTextoArribaConfirmacionMultiples'=>'Hola %nombre%, te confirmamos los turnos solicitados',
        'telLargoMin'=>'5',
        'telLargoMax'=>'8',
        'leyendaTomaTurno'=>'Seleccioná la actividad a la que querés anotarte.',

        //1 = inicial, 2 = avanzado, 3 = premium, 4 = corp, 5 = corp con agendas en cantidadAgendas
        
        'plan'=>'1',
        'leyendaWapp'=>'Agradeceremos tu puntualidad para poder atenderte como te lo mereces.',
        'wappManual_textoFooter' => '<br><br>Te esperamos en <b>%nombreCliente%</b>',


        //Duración en minutos del bloque de turnos
        
        'minBloqueTurnos'=>'15',

        //Si la duración del bloque se basa en el tratamiento o en la variable minBloqueTurnos
        'duracionBloque'=>'tratamiento',
        'estadoPago'=>'1',
        'timezone'=>'America/Argentina/Buenos_Aires',
        'smartlookId'=>'',
        'smartlookRegion'=>'',

        //1 = Nivel categorías sobre tratamientos, 0 = no
        
        'nivelCategorias'=>'1',

        //Valida en toma de turnos por dni, telefono o email
        
        'tomaTurno'=>'telefono',

        //Helper de campo dni
        
        'leyendaDni'=>'Sin puntos ni guiones. Sólo números.',
        'nombreMail'=>'Correo electrónico',
        'nombreTelefono'=>'Teléfono celular',

        

        //Activa recordatorios por Whatsapp por más de que no apliquen al plan. Por default 0 !
        
        'wappPlugin'=>'0',

        //Días de anticipación con los que debe aparecer los recordatorios de Whatsapp
        
        'wappDays'=>'1',

        //Oculta días del calendario. 0 es domingo.
        
        'diasOcultos'=>'0',

        //Código de Google Analytics
        
        'codigoAnalytics'=>'',

        //Permite cargar código dentro del head.
        
        'codigoHead'=>'',

        //Permite cargar HTML justo antes del include de scripts.
        
        'codigoBody'=>'',

        //Indica si mostrar precio del tratamiento en el selector público de tratamientos o no.
        
        'mostrarPrecio'=>'0',

        //Permite tomar turnos en el pasado
        
        'turnosPasado'=>'0',

        //Cantidad de agendas permitidas en caso de que el plan sea 5
        'cantidadAgendas'=>'0',
        // Pisa la cantidad de agendas sin importar el plan
        'cantidadAgendasTotal' => '',

        //Prende las notificaciones por mail
        
        'notificacionesMail'=>'1',

        // Texto previo a la firma del mail de confirmacion
        'mail_confirmacion_textoPrevioFirma' => '',

        #######
        #
        #       SMS
        #
        #######
        //Prender las notificaciones por SMS. Depende de que el cliente contrate un plan acorde.
        'notificacionesSMS'=>'0',
        'sms_texto_recordatorio' => 'Hola %nombre%, te recordamos tu turno el día: %fecha%. Requisitos y cancelaciones: %link%',
        'sms_recordatorio' => '',
        'sms_recordatorio_diasAnticipacion' => '',
        // Variable para indicar en que hora del día enviar el sms
        'sms_recordatorio_horaPersonalizada' => '',

        //Prende las notificaciones por Whatsapp automático. Depende de que el cliente contrate un plan acorde.
        
        'notificacionesWhatsapp'=>'0',

        //User de acceso api SMS. Licencia aparte.
        
        'smsApiUser'=>'CUATROLADOS',

        //Clave de acceso api SMS. Licencia aparte.
        
        'smsApiKey'=>'42015LADOS!',

        //Si es 0 o es 1 es lo mismo. Si es mayor a 1 se toma ese valor como los turnos simultaneos que pueden atender. Esto es general para todas las agendas.
        
        'turnosSimultaneos'=>'1',

        //Habilita la carga de datos de acceso para los profesionales.
        
        'accesoProfesionales'=>'0',

        //Muestro o no profesional indistinto
        
        'profesionalIndistinto'=>'0',

        //Permite o no la carga de textos asociados al tratamiento.
        'textosTratamientos'=>'0',
        'mail_recordatorio_quitarEstilos_textosTratamientos' => '',

        //Muestra el texto post al seleccionar el tratamiento en el public.
        
        'mostrarTextoTratamientoPublic'=>'0',

        //Texto que se muestra al confirmar un turno del lado público.
        
        'textoConfirmacionPublic'=>'',

        //Habilita cupos o normal?
        'cupos'=>'0',

        //Permite la toma de turnos solo para usuarios registrados. No permite usuarios nuevos.
        //Default 0
        'soloRegistrados'=>'0',

        'public_textAreaTurno' => 0,

        //es el label del textArea
        'public_title_textAreaTurno'  => 'observaciones',

        //Hay que agregar el atributo imagen en la tabla de orden para que funcione
        'ordenConImagen' => 0,

        // Agrega un mail en copia al mail de confirmacion
        'copiarMailEnElMailConfirmacion' => '',
        
        // Formato 24 horas
        'formato24horas' => 0,

        // Whatsapp manual. Variable permitidas %nombre%, %dia%, %hora%, %tratamiento%
        'whatsapp_mensajePersonalizado' => '',
        
        
        /* 
                METODO DE PAGO
        */
        // Seña del pago
        'pago_sena' => '$1000',
        // Simbolo de la  moneda
        'pago_sena_moneda' => '$',
        
        
        ###########
        #
        #       Mercado Pago
        #
        ###########
                
        // Tiene Mercadopago
        'mercadoPago' => '0',
        // Credenciales Mercadopago
        'mercadoPago_accessToken' => 'TEST-4775889105502402-092112-20b9897229de58959203a218ca06fef0-682039029',
        // Seña
        'mercadoPago_sena' => '',
        // Cobra servicios
        'mercadoPago_servicios' => '0',
        // Cobra sena por servicio
        'mercadoPago_servicios_sena' => '0',
        'mercadoPago_servicios_sena_porcentaje' => '0', // Requiere: mercadoPago_sena y mercadoPago_servicios_sena
        // Esta variable pisa a las otras
        'mercadoPago_servicios_sena__porcentaje_primerTurno' => '',
        // Tiempo límite Mercadopago en minutos
        'mercadoPago_limite' => '15',
        // Devuelvo o no el pago al cancelar el turno
        'mercadoPago_devolver' => '1',
        // Texto que se muestra en el public previo a ser enviado al pago.
        'mercadoPago_textoConfirmacion' => '%nombre%, ahora serás redireccionado a nuestra plataforma para finalizar el pago de tu turno.<br>En caso de no completarlo, tu <b>turno se cancelará</b> de forma automática en <b>%limite% minutos</b>.',
        // Se usa en el select de pago, en el public
        'mercadoPago_money_vista' => '$',
        'mercadoPago_accessToken_por_profesional' => '',
        'mercadoPago_soloPrimerTurno' => '',
        'mercadoPago_agregarNombreServicioAlDetalleDeCompra' => '',
        'mercadoPago_activar_binary_mode' => '1',
        
        'mercadoPago_plataformId' => 'dev_f540aefc5e3811ee9ba85a4c5831c295',

        'mails_quitarEnlaceLogo' => '',

        ###########
        #
        #       Paypal
        #
        ###########

        'paypal' => '0',
        // Credencial de prueba
        'paypal_cliente_id' => 'Ab6GLkHxpvSWJ-5tJgDfXsb0AZ1hdzcQNCsVj8Ut26WZuhFdacB08cXZzTU85n00HcknP3oH8E9sVRHg', 
        'paypal_money' => 'USD',
        'paypal_money_vista' => 'USD $',
        // sena = 0 | Usa el valor del tratamiento
        'paypal_sena' => '0', 
        'paypal_textoConfirmacion' => '%nombre%, a continuación podrás realizar el pago de tu turno utilizando paypal.<br>En caso de no completarlo, tu turno se cancelará.',
        
        
        
        ###########
        #
        #       Cron
        #
        ###########
        'cron' => '1',
        'diasRecordatorio' => '1',
        'horaRecordatorio' => '20:00',
        // Esta puede enviar un mail o varios. Ejemplo: 1,3,5 (días con anticipación que se envía los recordatorios)
        'cron_mailRecordatorio_diasAnticipacion' => '1', 
        //Prendo o no el botón de cancelación del mail de confirmación.
        'usuarioCancela' => '1',
        'mail_recordatorio_personalizado' => '',
        'mail_recordatorio_personalizado_body' => '',



        ############
        #
        #       Husos horarios dinámicos
        #
        ############
        'husosDinamicos' => '0',
                
                
        ############
        #
        #       Ventana mis turnos pública
        #
        ############
        'misTurnos' => '1',

                
        ############
        #
        #       Campo observaciones
        #       Si 0, inactivo, 1 publico, 2 privado, 3 ambos
        #
        ############
        'campoObservaciones' => '0',
        'campoObservaciones_titulo' => 'Observaciones',
        'campoObservaciones_required' => true,
                
                
        ############
        #
        #       Turnos repetitivos
        #       Si 0, inactivo, 1 publico, 2 privado, 3 ambos
        #
        ############
        'turnosRepetitivos' => '0',
        'usaMailsAlternativos' => '0',


        ############
        #
        #       Bloquea usuario para que no puedan sacar turnos en x tratamiento/os
        #
        ############
        'bloquearTurnosAPacientesPorTratamiento' => '0',
        

        ############
        #
        #       Fondo de pantalla y logos
        #
        ############
        'logoWidth'=>'400',
        'logoAdminWidth'=>'40',
        'logoFooter'=>'black',
        'fondoAbajo' => '0',
        'admin_fondoLogo' => '',


        ############
        #
        #       Nombre de la categoria
        #
        ############
        'nombreCategoria' => 'categoría',
        'nombreCategorias' => 'categorías',
        

        ############
        #
        #       Enviar mails segun tratamiento
        #
        ############
        'tratamiento_enviarMailConfirmacion' => '',
        'tratamiento_enviarMailRecordatorio' => '',
        

        ############
        #
        #       Sistema de créditos
        #
        ############
        'creditos' => '0',
        'limiteCupos' => 'this sunday +1 month',

        
        // Texto de la etiqueta <a> de la reunion
        'mail_confirmacion_meeting_textoDelLink' => '',
        ###########################################
        #
        #       Google Meet
        #
        'google_meet' => '',
        'google_meet_data_access' => '',
        
        ############
        #
        #       Zoom
        #
        ############
        'zoom' => '',
        'zoomPassword' => '',
        'zoom_credencialesPorProfesional' => '',
        'zoom_enviarLinkDeReunionEnElMailDeRecordatorio' => '',
        'zoom_enviarLinkDeReunionEnElMailDeRecordatorio_texto' => 'Link de la reunion: <a href="%link%" target="_blank" >Ir a la reunion</a>',
        
        ############
        #
        #       El profesional puede ver sus horarios
        #
        ############
        'profesional_abm_horarios' => '',

        'simultaneosPorServicio' => '',
        
        ###########################################
        #
        #       Archivo en el mail de confirmacion
        #
        // El archivo se guarda en la carpeta public_html/uploads/
        'mailDeConfirmacion_enviarArchivo' => '',
        'mailDeConfirmacion_enviarArchivo_path' => '',
        'mailDeConfirmacion_enviarArchivo_path_siempre' => '',
        'mailDeConfirmacion_enviarArchivo_porTurno' => '',
        'mailDeConfirmacion_enviarArchivo_primerTurnoPorServicio' => '',
        
        ###########################################
        #
        #       Facturacion
        #
        'facturacion' => '',
        
        ###########################################
        #
        #       Multiples horarios. (No esta terminado)
        #
        // Por ahora solo se usa en jules
        'profesional_multiplesHorarios' => '',
        
        ###########################################
        #
        #       Whatsapp automático API
        #
        'wappApi' => 0,
        // Ejemplo: enviar con 2, 4 y 5 dias de anticipacion => 2,4,5
        'wappApi_diasAnticipacion' => '',
        // Variables del mensaje: 
        //      %soloNombre%, %nombre% (nombre y apellido), %fecha% (d/m/Y H:i hs), %diaTurno%" (nombre del día), %fechaTurno% (d/m/Y), %horaTurno% (H:i) 
        //      %nombreTratamiento%, %link% (link de cancelacion), %nombreCliente%
        'wappApi_mensajePersonalizado' => '',
        'wappApi_horaPersonalizada' => '', // Si esta variable no tiene valor entonces se toma notifica exactamente wappApi_diasAnticipacion antes del turno
        //Manda solo el recordatorio del primer turno en caso de que tenga dos en el mismo día.
        'wappApi_oneTime' => 0,
        // Manda mensajes de confirmación
        'wappApi_confirmacion' => '',

        // Mensaje de horas anticipadas (en el public)
        'leyendaHorasAnticipadas' => 'El %nomobreTurno% que seleccionó no se encuentra disponible ya que los %nomobreTurnos% deben reservarse con una anticipación de %horasAnticipadas%hs', 
        'wappApi_telefonoCliente'=>'',

        ###########################################
        #
        #       Activar sobre turnos en el admin
        #
        'sobreTurno' => '0',

        ###########################################
        #
        #       Tabla para mensajes de cancelacion
        #
        // Crea un cuadro igual al de recordatorios de whatsapp en el panel
        'guardarTurnosCancelados' => '',
        'guardarTurnosCancelados_mensajeWhatsappManual' => 'Hola %nombreCompleto%, queremos informarte que tu turno con fecha del *%fecha%* fue *cancelado*. Esperamos que tengas un maravilloso día.',

        ###########################################
        #
        #       lagos.turnos.app
        #
        'enviar_mail_app_lagos' => '',
        'app_lagos_mail_reagendado_texto_footer' => '',

        ###########################################
        #
        #       Variable para el icono de profesionales
        #
        'icon_profesional' => 'fas fa-medkit',

        ###########################################
        #
        #       Variable para poner un texto pre unico en el mail de recordatorio
        #
        'mail_recordatorio_textoPre' => '',
        
        ###########################################
        #
        #       Variable para poner un texto pre unico en el mail de recordatorio
        #
        'mail_confirmacion_linkReunionProfesional' => '',
        
        // Quitar selector de codigo de area
        'codArea_quitarSelector' => '',
        'mail_recordatorio_textoAdicionalFooter' => '',
        
        
        ###########################################
        #
        #       COPIAR PROFESIONAL EN MAILS
        #
        'mail_confirmacion_copiarAlProfesional' => '',
        'mail_cancelacion_copiarAlProfesional' => '',
        'mail_recordatorio_copiarAlProfesional' => '',


        ###########################################
        #
        #       PHP MAILER 
        #
        'PHPMAILER_ACTIVE' => '',
        'PHPMAILER_SERVER' => '',
        'PHPMAILER_USER' => '',
        'PHPMAILER_PASSWORD' => '',
        'PHPMAILER_SECURITY' => '',
        'PHPMAILER_PORT' => '',
        'PHPMAILER_FROM_MAIL' => '',

        ###########################################
        #
        #       PANEL - Seleccionar fecha de mensajes a enviar ( Agrega un input de fecha )
        #
        'panel_selectorDeFechasParaLosRecordatorios' => '',
        
        ###########################################
        #
        #       Iconos
        #
        'iconoProducto' => '<i class="fa-solid fa-tag"></i>',
        'iconoMedioDePago' => '<i class="fa-solid fa-hand-holding-dollar"></i>',
        'iconoFacturacion' => '<i class="app-menu__icon fa-solid fa-file-lines"></i>',


        ###########################################
        #
        #       Feriados personalizados
        #
        "feriadoPersonalizado" => "",

        ###########################################
        #
        #       Activa el historial clínico
        #
        "historiaClinica" => "",


        "calendario_quitarHorasAnticipacion" => "",
        "calendarioAdmin_mixHora" => "",
        "calendarioAdmin_maxHora" => "",
        "elastic_remitente" => "noresponder@turnos.app"
);
        
?>