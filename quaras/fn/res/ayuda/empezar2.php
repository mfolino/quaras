<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?
        $seccion='Ayuda';
        $subseccion='Primeros pasos';
        require_once(incPath.'/head.php');
        ?>
        <style>
        .boldboton {
          font-weight: bold;
          color: white;
          background: #2a6cb6;
          border: solid 2px;
          border-radius: 5px;
          padding: 1px 5px 1px 5px;
          text-transform: Uppercase;
        }
        .bold {
          font-weight: bold;
          color: #2a6cb6;
        }
        .boldblack {
          font-weight: bold;
          color: black;
        }

            .sticky-sidebar{
                position:sticky;
                margin-top:0px;
                position: sticky;
                top: 60px;
                height: 100%;
            }
            .sticky-sidebar ul{
                list-style-type: none;
                padding-left: 20px;
                margin: 0;
            }
            .sticky-sidebar ul li{
                padding-top: 5px;
            }
            .body-area{
              /* background-color:gray; */
              /* height:5000px; */
            }
        </style>
    </head>
    <body class="app sidebar-mini rtl">
        <?
        require_once(incPath.'/header.php');
        require_once(incPath.'/sidebar.php');
        ?>
        <main class="app-content">
            <div class="app-title">
                <div>
                    <h1><i class="fas fa-flag-checkered"></i> Primeros pasos</h1>
                    <p>Utilizá esta sección para conocer los primeros pasos para configurar tu aplicación para que los usuarios puedan tomar turnos.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="tile">
                        <div class="tile-body">
                            <div class="row">
                                <div class="col-10  body-area pr-3">
                                    <h3 id="inicio">Guia rápida Turnos.app</h3>
                                    <p>
                                      Hola %nombre te damos la bienvenida a ésta guía rápida donde aprenderás en simples y sencillos pasos cómo poner en marcha tu aplicación de turnos.

                                    </p>

                                    <h3 id="Servicios">Crear Servicios</h3>

                                    <p>
                                      Ir al botón <span class="boldboton">%Servicios</span> ->  <span class="boldboton">Agregar %Servicios</span> | Esto nos permitirá colocar el nombre del mismo, su duración,
                                    <!--  Si esta activo cupos -->
                                      la cantidad de participantes del %servicio
                                    <!--  FIN -->
                                       y su estado.
                                    </p>
                                    <!--  Si tenemos activa el pre y el post  -->
                                    <p>
                                    También nos brindará espacio para añadir un texto que se mostrará en el        email y en la página selectora de turnos arriba y abajo de la fecha del turno seleccionado.
                                    </p>
                                    <!-- FIN  -->
                                    <p>
                                      Presionar el botón <span class="boldboton">Guardar</span> una vez completado los campos requeridos, volviendo asi a la página de %servicios.
                                    <br />
                                   
                                      Desde este mismo listado de %servicios presionando en el icono del billete <i class="fas fa-money-bill"></i>  nos permitirá añadirle un valor monetario al %servicio.
                                    </p>
                                    <p>
                                      Podemos añadir cuantos %servicios deseemos.
                                    </p>

                                    <!--  si tenemos activas las categorias  -->
                                    <h3 id="Categorias">Crear Categorias</h3>
                                    <p>
                                      Cuando tengamos los servicios que ofrecemos podemos crear una o múltiples %categorias% para organizar los mismos.<br />
                                      Para ello vamos a el menú <span class="boldboton">%Categorias%</span>  ->  <span class="boldboton">Agregar %categoria</span>| Debemos colocar nombre, ponerla en estado activa, y seleccionar los %servicios que pertenecen a esta %categoría.
                                    </p>
                                    <!-- FIN -->
                                      <h3 id="Profesionales">Crear Profesionales</h3>
                                    <p>
                                      Por último debemos ir al menú <span class="boldboton">%Profesionales%</span> -> <span class="boldboton">Agregar %Profesional</span> | Cada uno de estos profesionales tendrá su agenda individual. <br /> Al momento de crear un profesional nos pedirá los siguientes datos:<br />
                                      <ul>
                                        <li>
                                          Nombre: El nombre del %profesional%.
                                        </li>
                                        <li>
                                          Email: correo electrónico del %profesional.
                                        </li>
                                        <li>
                                          Contraseña: El password de acceso para poder ver sus turnos.
                                        </li>
                                        <li>
                                          Color de ref: El color con el que se va a distinguir dicho profesional en el calendario.
                                        </li>
                                        <li>
                                          Estado: Si se encuentra activo o no.
                                        </li>
                                        <li>
                                          Horario: Acá tenemos 2 opciones<br /><span class="boldblack" data-toggle="tooltip" title="La opción de horario habitual se habilita una vez guardado el profesional">Habitual</span>, es decir que se van a liberar los turnos en los horarios seleccionados, repitiendo semanalmente los mismos
                                          <img src="img/horariohabitual.png" /> <br />
                                          <span class="boldblack">Puntual</span> en el cual indicamos que día específico y desde/hasta que hora atiende.
                                          <img src="img/horariopuntual.png" />
                                        </li>
                                        <li>
                                          Solo privado: Nos permite definir si la agenda esta visible o no en el area de cliente. Si se encuentra en “SOLO PRIVADO” se podrá tomar turnos únicamente desde el sector administrativo.
                                        </li>
                                        <li>
                                          %Servicios que atiende: Nos permite seleccionar el/los %servicios a los cual nuestro %profesional brinda atención.
                                        </li>
                                      </ul>
                                    </p>
                                    <p>
                                      *La opción de horario habitual se habilita una vez guardado el profesional.<br />
                                      *A tener en cuenta en el horario habitual, el turno mañana tiene un límite hasta las 14hs. Para horarios posteriores, o un turno de corrido, se debe utilizar el turno tarde.
                                    </p>
                                    <p>
                                      Una vez terminado de completar esto presionamos <span class="boldboton">Guardar</span>.<br />
                                      Nos llevará de regreso al listado de %profesionales en los cuales clickeando en el ícono del calendario <i class="fa-solid fa-calendar-days"></i> <span class="boldblack">(sólo si pusimos horario habitual)</span> nos permitirá determinar los horarios en los cuales el profesional atiende.
                                    </p>
                                    <!-- // si tiene activa comisiones //
                                     -->
                                    <p>
                                      Tambien tendrémos el icono de <span class="boldblack">Comisiones</span> <i class="fa fa-percent"></i> el cual nos permite establecer un porcentaje de comisión para el profesional en base al costo del %servicio a realizar, lo que nos brindará en la parte de reportes el detalle de la comisión ganada por el %profesional% .
                                    </p>
                                    <!-- FIN -->



                                </div>
                                <div class="col-2 sticky-sidebar border-left">
                                    <ul>
                                        <li>
                                            <a class="text-secondary" data-href="#inicio" href="javascript:;">Inicio</a>
                                        </li>
                                        <li>
                                            <a class="text-secondary" data-href="#Servicios" href="javascript:;">Servicios</a>
                                        </li>
                                        <li>
                                            <a class="text-secondary" data-href="#Categorias" href="javascript:;">Categorias</a>
                                        </li>
                                        <li>
                                            <a class="text-secondary" data-href="#Profesionales" href="javascript:;">Profesionales</a>
                                        </li>
                                        <li>
                                            <a class="text-secondary" data-href="#configuracion" href="javascript:;">Configuración nueva</a>
                                            <ul>
                                                <li>
                                                    <a class="text-secondary" data-href="#configuracion" href="javascript:;">Paso 1</a>
                                                </li>
                                                <li>
                                                    <a class="text-secondary" data-href="#configuracion" href="javascript:;">Paso 2</a>
                                                </li>
                                                <li>
                                                    <a class="text-secondary" data-href="#configuracion" href="javascript:;">Paso 3</a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>


        <?
        require_once(incPath.'/scripts.php');
        ?>
        <!-- Page specific javascripts-->
        <script>
            $(".sticky-sidebar a").click(function() {

                let id = $(this).data("href");

                $([document.documentElement, document.body]).animate({
                    scrollTop: ($(id).offset().top-80)
                }, 500);
            });
        </script>

    </body>
</html>
