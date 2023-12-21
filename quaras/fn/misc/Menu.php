<?php

// Clase para armar el sidebar
class Menu{

    public static function getEscritorio($seccion, $subseccion){
        $html= '<li>
            <a class="app-menu__item';
            if($seccion=='Home'){
                $html.= ' active';
            }
            $html.='" href="/panel">
                <i class="app-menu__icon fa fa-desktop"></i>
                <span class="app-menu__label">
                    Escritorio
                </span>
            </a>
        </li>';

        return $html;
    }
    
    public static function getTurnos($seccion, $subseccion){

        global $general;

        $html= '<li class="treeview';
        if($seccion==ucwords($general['nombreTurnos'])){
            $html.= ' is-expanded';	
        }
        $html.='">
            <a class="app-menu__item" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-calendar"></i>
                <span class="app-menu__label">
                    '.ucwords($general['nombreTurnos']).'
                </span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li>
                    <a class="treeview-item';
                    if(($seccion==ucwords($general['nombreTurnos']))&&($subseccion=='Calendario')){
                        $html.= ' active';
                    }
                    $html.='" href="/turnos/calendario">
                        <i class="icon fa fa-circle-o"></i> Calendario
                    </a>
                </li>';

                if(AuthController::isAdmin()){
                    $html.= '<li>
                        <a class="treeview-item';
                        if(($seccion==ucwords($general['nombreTurnos']))&&($subseccion=='Bloqueos')){
                            $html.= ' active';
                        }
                        $html.='" href="/turnos/bloqueos">
                            <i class="icon fa fa-circle-o"></i> Bloqueos
                        </a>
                    </li>
                    <li>
                        <a class="treeview-item';
                        if(($seccion==ucwords($general['nombreTurnos']))&&($subseccion=='Feriados')){
                            $html.= ' active';
                        }
                        $html.='" href="/turnos/feriados">
                            <i class="icon fa fa-circle-o"></i> Feriados
                        </a>
                    </li>';
                }

                if($general["feriadoPersonalizado"]){
                    $html.= '<li>
                        <a class="treeview-item';
                        if($seccion=="Feriados personalizados" && $subseccion=='Listado'){
                            $html.= ' active';
                        }
                        $html.='" href="/feriadosPersonalizados/">
                            <i class="icon fa fa-circle-o"></i> Feriados Personalizados
                        </a>
                    </li>';
                }

            $html.='</ul>
            </li>';

        return $html;
    }

    public static function getPacientes($seccion, $subseccion){

        global $general;

        $html= '<li>
            <a class="app-menu__item';
            if($seccion==ucwords($general['nombrePacientes'])){
                $html.= ' active';
            }
            $html.='" href="/pacientes/listado">
                <i class="app-menu__icon fa fa-users"></i>
                <span class="app-menu__label">
                    '.ucwords($general['nombrePacientes']).'
                </span>
            </a>
        </li>';

        return $html;
    }
    
    public static function getCategorias($seccion, $subseccion){

        global $general;

        if(AuthController::isAdmin()){
            if($general['nivelCategorias']){

                $html= '<li>
                    <a class="app-menu__item';
                    if($seccion=='Categorías'){
                        $html.= ' active';
                    }
                    $html.='" href="/obrasSociales/categorias">
                        <i class="app-menu__icon fa fa-list-alt"></i>
                        <span class="app-menu__label">
                            '.ucfirst($general["nombreCategorias"]).'
                        </span>
                    </a>
                </li>';

                return $html;
            }
        }
    }


    public static function getTratamientos($seccion, $subseccion){

        global $general;

        if(AuthController::isAdmin()){
        
            $html= '<li>
                <a class="app-menu__item';
                if($seccion==ucwords($general['nombreObrasSociales'])){
                    $html.= ' active';
                }
                $html.='" href="/obrasSociales/tratamientos">
                    <i class="app-menu__icon fa fa-list"></i>
                    <span class="app-menu__label">
                        '.ucwords($general['nombreObrasSociales']).'
                    </span>
                </a>
            </li>';

            if($general['creditos']){
                $html.= '<li>
                    <a class="app-menu__item';
                    if($seccion=='Planes'){
                        $html.= ' active';
                    }
                    $html.='" href="/obrasSociales/planesCreditos">
                        <i class="app-menu__icon fa fa-coins"></i>
                        <span class="app-menu__label">
                            Planes
                        </span>
                    </a>
                </li>';
            }
            
            return $html;
        }
    }
    
    public static function getProfesionales($seccion, $subseccion){

        global $general;

        if($general['profesional_abm_horarios'] && AuthController::isProfesional()){
            $html= '<li>
                <a class="app-menu__item';
                if($seccion==ucwords($general['nombreProfesionales'])){
                    $html.= ' active';
                }
                $html.='" href="/profesionales/listado">
                    <i class="app-menu__icon '.$general["icon_profesional"].'"></i>
                    <span class="app-menu__label">Mis horarios</span>
                </a>
            </li>';
        }elseif (AuthController::isAdmin()) {
            $html= '<li>
                <a class="app-menu__item';
                if($seccion==ucwords($general['nombreProfesionales'])){
                    $html.= ' active';
                }
                $html.='" href="/profesionales/listado">
                    <i class="app-menu__icon '.$general["icon_profesional"].'"></i>
                    <span class="app-menu__label">
                        '.ucwords($general['nombreProfesionales']).'
                    </span>
                </a>
            </li>';
        }
        return $html;

        
        // Backup 2/11 => Antes de agregar $general['profesional_abm_horarios']
        /* if(AuthController::isAdmin()){
            $html= '<li>
                <a class="app-menu__item';
                if($seccion==ucwords($general['nombreProfesionales'])){
                    $html.= ' active';
                }
                $html.='" href="/profesionales/listado">
                    <i class="app-menu__icon fa fa-medkit"></i>
                    <span class="app-menu__label">
                        '.ucwords($general['nombreProfesionales']).'
                    </span>
                </a>
            </li>';
            return $html;
        } */
        
    }
    
    public static function getReportes($seccion, $subseccion){

        global $general;

        if(AuthController::isAdmin()){
        
            $html= '<li class="treeview';
                if($seccion=='Reportes'){
                    $html.= ' is-expanded';	
                }
                $html.='">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-bar-chart"></i>
                    <span class="app-menu__label">
                        Reportes
                    </span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a class="treeview-item';
                        if(($seccion=='Reportes')&&($subseccion==ucwords($general['nombreTurnos']))){
                            $html.= ' active';
                        }
                        $html.='" href="/reportes/turnos">
                            <i class="icon fa fa-circle-o"></i> '.ucwords($general['nombreTurnos']).'
                        </a>
                    </li>';
                    
                    if($general['plan']>2){
                        $html.='<li>
                            <a class="treeview-item';
                            if(($seccion=='Reportes')&&($subseccion=='Ventas')){
                                $html.= ' active';
                            }
                            $html.='" href="/reportes/ventas">
                                <i class="icon fa fa-circle-o"></i> Ventas
                            </a>
                        </li>
                        <li>
                            <a class="treeview-item';
                            if($subseccion=='Comisiones'){
                                $html.= ' active';
                            }
                            $html.='" href="/reportes/comisiones">
                                <i class="icon fa fa-circle-o"></i> Comisiones
                            </a>
                        </li>';
                    }
                $html.='</ul>
            </li>';

            return $html;
        }
    }
    
    
    public static function getAyuda($seccion, $subseccion){

        global $general;

        if(AuthController::isAdmin()){

            $html='';

            if(@$general['panelConfig']){
        
                $html.= '<li>
                    <a class="app-menu__item';
                    if($seccion=='Configuración'){
                        $html.= ' active';
                    }
                    $html.='" href="/config/listado">
                        <i class="app-menu__icon fas fa-cogs"></i>
                        <span class="app-menu__label">
                            Configuración
                        </span>
                    </a>
                </li>';

            }
        
            $html.= '<li class="treeview';
                if($seccion=='Ayuda'){
                    $html.= ' is-expanded';	
                }
                $html.='">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fas fa-life-ring"></i>
                    <span class="app-menu__label">
                        Ayuda
                    </span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">';

                $html.='<li>
                <a class="treeview-item';
                if(($seccion=='Ayuda')&&($subseccion=='Primeros pasos')){
                    $html.= ' active';
                }
                $html.='" href="/ayuda/empezar">
                    <i class="icon fa fa-circle-o"></i> Primeros pasos
                </a>
            </li>';


                    /* $html.='<li>
                        <a class="treeview-item';
                        if(($seccion=='Ayuda')&&($subseccion=='Manual de usuario')){
                            $html.= ' active';
                        }
                        $html.='" href="/ayuda/manual">
                            <i class="icon fa fa-circle-o"></i> Manual de usuario
                        </a>
                    </li>'; */


                    /* $html.='<li>
                        <a class="treeview-item';
                        if(($seccion=='Ayuda')&&($subseccion=='Preguntas frecuentes')){
                            $html.= ' active';
                        }
                        $html.='" href="/ayuda/faq">
                            <i class="icon fa fa-circle-o"></i> Preguntas frecuentes
                        </a>
                    </li>'; */


                    $html.='<li>
                        <a class="treeview-item';
                        if(($seccion=='Ayuda')&&($subseccion=='Soporte')){
                            $html.= ' active';
                        }
                        $html.='" href="mailto:soporte@cuatrolados.com?subject=Ayuda desde '.$general['nombreCliente'].'">
                            <i class="icon fa fa-circle-o"></i> Contactar a soporte
                        </a>
                    </li>';


                    $html.='<li>
                        <a class="treeview-item';
                        if(($seccion=='Ayuda')&&($subseccion=='Comentarios')){
                            $html.= ' active';
                        }
                        $html.='" href="mailto:soporte@cuatrolados.com?subject=Feedback desde '.$general['nombreCliente'].'">
                            <i class="icon fa fa-circle-o"></i> Envíanos tus comentarios
                        </a>
                    </li>';


                    $html.='</ul>
                    </li>';
                
                
                    $html.= '<li class="treeview';
                    if($seccion=='Recursos'){
                        $html.= ' is-expanded';	
                    }
                    $html.='">
                    <a class="app-menu__item" href="#" data-toggle="treeview">
                        <i class="app-menu__icon fas fa-tools"></i>
                        <span class="app-menu__label">
                            Recursos
                        </span>
                        <i class="treeview-indicator fa fa-angle-right"></i>
                    </a>
                    <ul class="treeview-menu">';
    
                        $html.='<li>
                            <a class="treeview-item';
                            if(($seccion=='Recursos')&&($subseccion=='QR')){
                                $html.= ' active';
                            }
                            $html.='" href="/ayuda/qr" target="_blank" title="Imprimí este QR y ponelo en un lugar visible de tu negocio para que la gente pueda escanearlo con su celular." data-toggle="tooltip">
                                <i class="icon fa fa-circle-o"></i> Código QR
                            </a>
                        </li>';
    
    
                    $html.='</ul>
                </li>';

            return $html;
        }
    }
    
}