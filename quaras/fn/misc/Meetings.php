<?php

use Zoom\Client;
use Zoom\Meeting;

// Clase para administrar reuniones con Zoom
class Zoom{

    public static function generarMeeting($profesional, $tratamiento, $fechaInicio){

        require_once '/home/master/lib/Zoom/Index.php';

        global $general, $row, $tot, $res;

        /* Util::printVar('---', '138.121.84.107', false);
        Util::printVar(class_exists('Client') ? 'Existe la clase' : 'No existe', '138.121.84.107',false);
        Util::printVar('--', '138.121.84.107', true); */

        $passwordZoom = $general['zoomPassword'];
        if($general['zoom_credencialesPorProfesional']){
            db_query(0, "SELECT zoom_password, zoom_api_key, zoom_secret_key, zoom_email FROM profesionales WHERE idProfesional = '{$profesional}' ");
            $passwordZoom = $row['zoom_password'];
            $api_key = $row['zoom_api_key'];
            $secret_key = $row['zoom_secret_key'];
            $zoom_email = $row['zoom_email'];

            $meeting = new Meeting(
                array(
                    'apiKey' => $api_key,
                    'apiSecret' => $secret_key,
                    'email' => $zoom_email,
                )
            );

        }else{
            $meeting = new Meeting();
        }

        
        //$meeting = $general['zoom_credencialesPorProfesional'] ? $meetingCustom : new Meeting();
        
        /* Util::printVar($meeting, '138.121.84.107',false );
        Util::printVar($profesional, '138.121.84.107',false );
        Util::printVar($tratamiento, '138.121.84.107',false );
        Util::printVar($fechaInicio, '138.121.84.107'); */


        //create a data meeting
        $data = [
            'topic' => $general['nombreCliente'],
            'agenda' => ucwords($general['nombreTurno']).' con '.ProfesionalController::getProfesional($profesional)['nombre'],
            'type' => 2,
            'start_time' => str_replace(' ', 'T', $fechaInicio),
            'duration' => TratamientoController::getDuracion($tratamiento),
            'settings' => [
                'host_video' => true,
                'participant_video' => true,
                'join_before_host' => false,
                'mute_upon_entry' => true,
                'watermark' => false,
                'use_pmi' => false,
                'approval_type' => 2,
                'audio' => 'voip',
                'auto_recording' => 'cloud',
                'alternative_hosts' => '',
                'registrants_confirmation_email' => true,
                'registrants_email_notification' => true,
                'private_meeting' => true
            ],
            'timezone' => $general['timezone'],
        ];

        if(trim($passwordZoom) != ''){
            // $data['password'] = $passwordZoom;
        }
        
        $meeting = $meeting->create($data);

					
        /*if($_SERVER['REMOTE_ADDR'] == '186.138.206.135'){
            echo ('--- general Meting ---' . '<br>');
            var_dump($data);
            echo "<br>";
            var_dump($meeting);
            die();
        }*/

        return $meeting;
        
    }
    
}


// Clase para administrar reuniones con Zoom
class GoogleMeet{

    public static function getClient()
    {
        global $general;

        $domainInArray = explode(".",$general["clientDomain"]);
        $nombreDeCarpetaFTP = $domainInArray[0];

        $client = new Google_Client();

        $client->setApplicationName('Turnos.app');
        $client->setScopes("https://www.googleapis.com/auth/calendar");

        $client->setAuthConfig("/home/master/applications/{$nombreDeCarpetaFTP}/private_html/client_secrets.json");
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');


        $accessToken = json_decode($general['google_meet_data_access'], true);
        $client->setAccessToken($accessToken);

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            }/* else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }*/

            db_update("update config set valor='".json_encode($client->getAccessToken())."' where clave='google_meet_data_access'");

        }

        return $client;
    }

    public static function generarMeeting($profesional, $tratamiento, $fechaInicio){
        require_once('/home/master/lib/vendor/autoload.php');
        global $general, $row, $tot, $res;
        GLOBAL $row9, $tot9, $res9;

        // Buscar nombre del cliente
        db_query(9, "SELECT 
                p.nombre,
                p.apellido 
            FROM 
                turnos t,
                ordenes o,
                pacientes p
            WHERE
                t.idOrden = o.idOrden AND 
                t.idPaciente = p.idPaciente AND 
                o.idProfesional = '{$profesional}' AND 
                o.idTratamiento = '{$tratamiento}' AND 
                t.fechaInicio = '{$fechaInicio}' AND 
                t.estado <> '3' AND 
                t.eliminado <> '1'
            LIMIT 1 
        ");
        
        $nombrePaciente = ucfirst($row9["nombre"])." ".ucfirst($row9["apellido"]);

        $client = self::getClient();
        $service = new Google_Service_Calendar($client);

        $duracion=TratamientoController::getDuracion($tratamiento);

        /* 'summary' => ucwords($general['nombreTurno']).' con '.ProfesionalController::getProfesional($profesional)['nombre'], */
        $event = new Google_Service_Calendar_Event(array(
            'summary' => ucwords($general['nombreTurno']).' con '.$nombrePaciente,
            'start' => array(
              'dateTime' => str_replace(' ', 'T', $fechaInicio),
              'timeZone' => 'America/Argentina/Buenos_Aires',
            ),
            'end' => array(
              'dateTime' => str_replace(' ', 'T', date("Y-m-d H:i:s",strtotime($fechaInicio." +".$duracion." minutes"))),
              'timeZone' => 'America/Argentina/Buenos_Aires',
            ),
            'reminders' => array(
              'useDefault' => true
            ),
            'conferenceData' => array(
              'createRequest' => array(
                'requestId' => "Turnosapp-".rand(0,9999),
                'conferenceSolutionKey' => array(
                  'type' => 'hangoutsMeet'
                )
              )
            )
          ));

          $options=array(
            'conferenceDataVersion' => 1,
            'sendUpdates' => 'all'
          );
  

          $calendarId = 'primary';
          $event = $service->events->insert($calendarId, $event, $options);

        return $event;
    }

    public static function createEventInCalendar($idTurno){
        require_once('/home/master/lib/vendor/autoload.php');
        global $general, $row, $tot, $res;
        GLOBAL $row9, $tot9, $res9;

        // Buscar nombre del cliente
        db_query(9, 
          "SELECT 
            t.fechaInicio,
            t.fechaFin,
            p.nombre,
            p.apellido,
            trat.nombre as tratamiento
          FROM 
            turnos t,
            ordenes o,
            pacientes p,
            tratamientos trat
          WHERE
            t.idOrden = o.idOrden AND 
            t.idPaciente = p.idPaciente AND 
            t.idTurno = '{$idTurno}' AND
            t.estado <> '3' AND 
            t.eliminado <> '1'
          LIMIT 1 
        ");
        
        $nombrePaciente = ucfirst($row9["nombre"])." ".ucfirst($row9["apellido"]);
        $tratamiento = $row9["tratamiento"];
        $fechaInicio = $row9["fechaInicio"];
        $fechaFin = $row9["fechaInicio"];

        $client = self::getClient();
        $service = new Google_Service_Calendar($client);

        $duracion=TratamientoController::getDuracion($tratamiento);

        $event = new Google_Service_Calendar_Event(array(
            'summary' => ucwords($general['nombreTurno']).' con '.$nombrePaciente . ' - '. ucfirst($tratamiento),
            'start' => array(
              'dateTime' => str_replace(' ', 'T', $fechaInicio),
              'timeZone' => 'America/Argentina/Buenos_Aires',
            ),
            'end' => array(
              'dateTime' => str_replace(' ', 'T', $fechaFin),
              'timeZone' => 'America/Argentina/Buenos_Aires',
            ),
            'reminders' => array(
              'useDefault' => true
            )
          ));

          $calendarId = 'primary';
          
          $event = $service->events->insert($calendarId, $event);

        return $event;
    }

    /**
     * Crear link para agregar un evento en google calendar
     * 
     * @param string $asunto Titulo de la reunion
     * @param string|void $detalle Descripcion
     * @param string $dateTimeStart Y-m-d H:i:s Inicio de la reunion
     * @param string $dateTimeEnd Y-m-d H:i:s Fin de la reunion
     * @param array|void $mailInvitados Invitados
     */
    public static function getLinkEvento($asunto, $detalle="", $dateTimeStart, $dateTimeEnd, $mailInvitados = array()){
        $linkEvento = "http://www.google.com/calendar/event?action=TEMPLATE&location=";
        $linkEvento .= "&dates=".date("Ymd\THis", strtotime($dateTimeStart))."/".date("Ymd\THis", strtotime($dateTimeEnd))."";

        $asunto = urlencode($asunto);
        $linkEvento .= "&text={$asunto}";

        if($detalle){
            $detalle = urlencode($detalle);
            $linkEvento .= "&details={$detalle}";
        }

        // Invitados
        if(!is_array($mailInvitados)){
            $mailInvitados = array($mailInvitados);
        }
        if(count($mailInvitados) > 0){
            $linkEvento .= "&add=". implode(",",$mailInvitados);
        }

        return $linkEvento;
    }
}

class GoogleCalendar{

  /**
   * Crear link 
   * @param string $asunto Titulo de la reunion
   * @param string|void $detalle Descripcion
   * @param string $dateTimeStart Y-m-d H:i:s Inicio de la reunion
   * @param string $dateTimeEnd Y-m-d H:i:s Fin de la reunion
   * @param array|void $mailInvitados Invitados
   */
  public static function getLinkEvento($asunto, $detalle="", $dateTimeStart, $dateTimeEnd, $mailInvitados = array()){
      $linkEvento = "http://www.google.com/calendar/event?action=TEMPLATE&location=";
      $linkEvento .= "&dates=".date("Ymd\THis", strtotime($dateTimeStart))."/".date("Ymd\THis", strtotime($dateTimeEnd))."";

      $asunto = urlencode($asunto);
      $linkEvento .= "&text={$asunto}";

      if($detalle){
          $detalle = urlencode($detalle);
          $linkEvento .= "&details={$detalle}";
      }

      // Invitados
      if(!is_array($mailInvitados)){
          $mailInvitados = array($mailInvitados);
      }
      if(count($mailInvitados) > 0){
          $linkEvento .= "&add=". implode(",",$mailInvitados);
      }

      return $linkEvento;
  }
  
}


class Reunion{

    public static function crear($profesional, $tratamiento, $fechaInicio){
        global $general, $row, $tot, $res;

        //Voy a ver si ya existe un link para este profesional y este tratamiento a esta hora, si no lo creo
        db_query(0, "select link from turnos_meetings where idProfesional='{$profesional}' and idTratamiento='{$tratamiento}' and fechaInicio='{$fechaInicio}' limit 1");
        
        if($tot>0){
            //Existe, devuelvo el link
            return $row['link'];
        }else{

            if($general['google_meet']){
                $meeting = GoogleMeet::generarMeeting($profesional, $tratamiento, $fechaInicio);
                $plataforma='meet';
            }else{
                $meeting = Zoom::generarMeeting($profesional, $tratamiento, $fechaInicio);
                $plataforma='zoom';
            }

            if($meeting['id']){
                $link = $plataforma == 'meet' ? $meeting['hangoutLink'] : $meeting['join_url'];
                db_insert("INSERT INTO turnos_meetings (idProfesional, idTratamiento, fechaInicio, plataforma, link, estado) VALUES ('{$profesional}', '{$tratamiento}', '{$fechaInicio}', '{$plataforma}', '{$link}', 'A')");

                return $general["google_meet"] ? $meeting->hangoutLink : $meeting['join_url'] ;
            }else{
                return false;
            }
        }
    }

}