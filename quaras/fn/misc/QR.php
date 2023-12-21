<?php

// Clase para crear códigos QR y devolverlo en png base64
class QR{

    public static function crear(){

        global $general;

        /**
         * QR Code + Logo Generator
         *
         * http://labs.nticompassinc.com
         */
        $data = 'https://'.$general['clientDomain'];
        $size = '500x500';
        $logo = isset($_GET['logo']) ? $_GET['logo'] : FALSE;

        // header('Content-type: image/png');
        // Get QR Code image from Google Chart API
        // http://code.google.com/apis/chart/infographics/docs/qr_codes.html
        $QR = imagecreatefrompng('https://chart.googleapis.com/chart?cht=qr&chld=H|1&chs='.$size.'&chl='.urlencode($data));
        if($logo !== FALSE){
            $logo = imagecreatefromstring(file_get_contents($logo));

            $QR_width = imagesx($QR);
            $QR_height = imagesy($QR);
            
            $logo_width = imagesx($logo);
            $logo_height = imagesy($logo);
            
            // Scale logo to fit in the QR Code
            $logo_qr_width = $QR_width/3;
            $scale = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;
            
            imagecopyresampled($QR, $logo, $QR_width/3, $QR_height/3, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        }
        ob_start();
        imagepng($QR);
        $stringdata = ob_get_contents();
        ob_end_clean();

        imagedestroy($QR);

        return '<img src="data:image/png;base64,' . base64_encode($stringdata) . '">';
    }

    public static function create($dataTurno){

        global $general;

        /**
         * QR Code + Logo Generator
         *
         * http://labs.nticompassinc.com
         */

        $data = 'https://'.$general['clientDomain'].'/infoEntrada.php?id='.$dataTurno;
        $size = '500x500';
        $logo = isset($_GET['logo']) ? $_GET['logo'] : FALSE;

        // header('Content-type: image/png');
        // Get QR Code image from Google Chart API
        // http://code.google.com/apis/chart/infographics/docs/qr_codes.html
        $QR = imagecreatefrompng('https://chart.googleapis.com/chart?cht=qr&chld=H|1&chs='.$size.'&chl='.urlencode($data));
        if($logo !== FALSE){
            $logo = imagecreatefromstring(file_get_contents($logo));

            $QR_width = imagesx($QR);
            $QR_height = imagesy($QR);
            
            $logo_width = imagesx($logo);
            $logo_height = imagesy($logo);
            
            // Scale logo to fit in the QR Code
            $logo_qr_width = $QR_width/3;
            $scale = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;
            
            imagecopyresampled($QR, $logo, $QR_width/3, $QR_height/3, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        }
        ob_start();
        imagepng($QR);
        $stringdata = ob_get_contents();
        ob_end_clean();

        imagedestroy($QR);

        return '<img src="data:image/png;base64,' . base64_encode($stringdata) . '">';
    }
}
?>