<?php

class DateController{

    public static function monthDayToMes($monthDay){
        $meses['January']='Enero';
        $meses['February']='Febrero';
        $meses['March']='Marzo';
        $meses['April']='Abril';
        $meses['May']='Mayo';
        $meses['June']='Junio';
        $meses['July']='Julio';
        $meses['August']='Agosto';
        $meses['September']='Septiembre';
        $meses['October']='Octubre';
        $meses['November']='Noviembre';
        $meses['December']='Diciembre';
        return $meses[$monthDay];
    }

    /* Devuelve el día de la semana en español en base al día en inglés */
    public static function daysToDias($day,$lower=true){
        $diaSemana['Monday']='Lunes';
        $diaSemana['Tuesday']='Martes';
        $diaSemana['Wednesday']='Miercoles';
        $diaSemana['Thursday']='Jueves';
        $diaSemana['Friday']='Viernes';
        $diaSemana['Saturday']='Sabado';
        $diaSemana['Sunday']='Domingo';
        
        return $lower ? strtolower($diaSemana[$day]) : $diaSemana[$day];
    }
    
    /* Devuelve el día de la semana en inglés en base al día en español */
    public static function diasToDays($day,$lower=true){
        $diaSemana['Lunes']='Monday';
        $diaSemana['Martes']='Tuesday';
        $diaSemana['Miercoles']='Wednesday';
        $diaSemana['Jueves']='Thursday';
        $diaSemana['Viernes']='Friday';
        $diaSemana['Sabado']='Saturday';
        $diaSemana['Domingo']='Sunday';

        return $lower ? strtolower($diaSemana[$day]) : $diaSemana[$day];
    }

    /* Devuelve el número de día de la semana en base al día en español */
    public static function diasToNum($day,$lower=true){
        $diasSemana['domingo']=0;
        $diasSemana['lunes']=1;
        $diasSemana['martes']=2;
        $diasSemana['miercoles']=3;
        $diasSemana['jueves']=4;
        $diasSemana['viernes']=5;
        $diasSemana['sabado']=6;

        return $lower ? strtolower($diasSemana[$day]) : $diasSemana[$day];
    }

    /* Verifica si un rango está dentro de otro */
    public static function overlap($start_date, $end_date, $date_from_user, $limits=true){
        // Convert to timestamp
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);
        $user_ts = strtotime($date_from_user);

        // Verifica si la fecha está dentro del rango
        if($limits){
            //En este caso incluye el principio y el final
            return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
        }else{
            //En este caso no incluye el principio y el final
            return (($user_ts > $start_ts) && ($user_ts < $end_ts));
        }
    }

    public static function dateDiff($fechaDesde, $fechaHasta){
        $datediff = strtotime($fechaHasta)-strtotime($fechaDesde);
        return ($datediff / (60 * 60 * 24));
    }

    public static function formattedToFecha($fecha){
        return date('Y-m-d',strtotime(str_replace('/','-',$fecha)));
    }

    public static function minutesDiff($fechaInicio, $fechaFin){
        // Convertir las fechas a objetos DateTime
        $dateTime1 = new DateTime($fechaInicio);
        $dateTime2 = new DateTime($fechaFin);

        // Calcular la diferencia en minutos entre las dos fechas
        $diferencia = $dateTime2->diff($dateTime1);
        $totalMinutos = $diferencia->days * 24 * 60 + $diferencia->h * 60 + $diferencia->i;

        return $totalMinutos;
    }
}