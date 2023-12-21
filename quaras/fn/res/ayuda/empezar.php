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
          /* background: #2a6cb6; */
          border: solid 2px;
          border-radius: 5px;
          padding: 4px 6px;
          /*text-transform: Uppercase;*/
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
              font-size:1rem;
            }

            h2{
                font-size:2.5rem;
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
                                <div class="col  body-area pr-3">
                                    <div class="row">
                                      <div class="col">
                                        <h2 id="inicio" class="text-dark mb-3">Guía rápida</h2>
                                        <p>
                                            Hola <b><?=$_SESSION['usuario']['nombre']?></b> te damos la bienvenida a ésta guía rápida donde aprenderás en simples y sencillos pasos cómo poner en marcha tu aplicación de turnos.
                                        </p>
                                      </div>
                                    </div>

                                    <div class="row">
                                        <div class="col">
                                            <h2 id="Servicios" class="text-dark mt-4"><?=ucwords($general['nombreObrasSociales'])?></h2>

                                            <h4 class="text-muted mb-3">Cómo crear <?=($general['nombreObrasSociales'])?>?</h4>

                                            <p>
                                            Ir al botón <span class="boldboton btn-primary"><i class="fas fa-list"></i> <?=ucwords($general['nombreObrasSociales'])?></span> en el menú y a continuación apretar el botón <span class="boldboton btn-primary"><i class="fas fa-plus"></i> Agregar <?=ucwords($general['nombreObraSocial'])?></span>
                                            </p>
                                            <p>
                                                Esto nos permitirá asignarle un nombre, su duración en minutos,
                                                <!--  Si esta activo cupos -->
                                                la cantidad de participantes o turnos simultáneos que permite
                                                <!--  FIN -->
                                                y su <span class="font-weight-bold" data-toggle="tooltip" title="En caso de estar inactivo no podrá ser usado en ningún lugar de la app.">estado <i class="fas fa-info-circle"></i></span>.
                                            </p>
                                            </p>
                                            <!--  Si tenemos activa el pre y el post  -->
                                            <p>
                                                Desde aquí también podremos añadir un encabezado y un mensaje de información adicional que se mostrará en el emails automáticos de <b>confirmación</b> y de <b>recordatorio</b> del turno:
                                            </p>
                                            <img src="//turnos.app/assets/app/img/textoPrevioPosteriorMail.png" class="img-fluid mb-4" />

                                            <?
                                            if($general['mostrarTextoTratamientoPublic'])
                                            {
                                                ?>
                                                <p>
                                                    Y en el sector público de la app una vez seleccionada la opción de <?=($general['nombreObraSocial'])?>:
                                                </p>
                                                <img src="//turnos.app/assets/app/img/textoPosteriorPublico.png" class="img-fluid mb-5" />
                                                <?
                                            }
                                            ?>
                                            <!-- FIN  -->
                                            <p>
                                            Una vez completados los campos, presionar el botón <span class="boldboton btn-primary">Guardar</span>. La app nos llevará nuevamente al listado de <?=$general['nombreObrasSociales']?>.
                                            </p>
                                            <p>
                                            Desde este mismo listado de podremos añadirle un valor monetario a cada <?=$general['nombreObraSocial']?> presionando en el icono del billete <i class="fas fa-money-bill"></i>.
                                            </p>
                                            <p>
                                            Podemos añadir la cantidad de <?=$general['nombreObrasSociales']?> que necesitemos.
                                            </p>
                                        </div>
                                    </div>

                                    <?
                                    if($general['nivelCategorias'])
                                    {
                                        ?>
                                        <!--  si tenemos activas las categorias  -->
                                        <div class="row">
                                            <div class="col">
                                                <h2 id="Categorias" class="text-dark mt-4"><?=ucwords($general['nombreCategorias'])?></h2>

                                                <h4 class="text-muted mb-3">Cómo crear <?=($general['nombreCategorias'])?>?</h4>

                                                <p>
                                                    Una vez guardada la información de <?=($general['nombreObrasSociales'])?> que ofrecemos podemos crear un/a o múltiples <?=$general['nombreCategorias']?> para organizar nuestra app.
                                                </p>
                                                <p>
                                                    Para ello vamos al menú  y seleccionamos la opción <span class="boldboton btn-primary"><i class="fas fa-list-alt"></i> <?=ucwords($general['nombreCategorias'])?></span>. Una vez en la sección clickeamos <span class="boldboton btn-primary"><i class="fas fa-plus"></i> Agregar <?=ucwords($general['nombreCategoria'])?></span>.
                                                </p>

                                                <p>La app nos despliega un formulario donde debemos colocar <b title="El mismo se mostrará tanto en el sector público como el privado." data-toggle="tooltip">nombre <i class="fas fa-info-circle"></i></b>, poner el estado en <b>Activo/a</b> y seleccionar las/os <?=($general['nombreObrasSociales'])?> que pertenecen a esta/e <?=($general['nombreCategoria'])?>.
                                                </p>
                                            </div>
                                        </div>
                                        <?
                                    }
                                    ?>

                                    <div class="row">
                                      <div class="col">


                                    <!-- FIN -->
                                    <h2 id="Profesionales" class="text-dark mt-4"><?=ucwords($general['nombreProfesionales'])?></h2>

                                    <h4 class="text-muted mb-3">Cómo crear <?=($general['nombreProfesionales'])?>?</h4>
                                    <p>
                                      Por último, para configurar nuestros horarios de atención, debemos ir a la sección <span class="boldboton btn-primary"><i class="fas fa-medkit"></i> <?=ucwords($general['nombreProfesionales'])?></span> del menú. A continuación hacemos click en <span class="boldboton btn-primary"><i class="fas fa-plus"></i> Agregar <?=ucwords($general['nombreProfesional'])?></span>. Cada uno de los/as <?=$general['nombreProfesionales']?> que agregues aquí tendrá su agenda individual. </p>

                                    <p>Al momento de crear un/a <?=$general['nombreProfesional']?> debemos completar los siguientes datos:<br />
                                      <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                          <b>Nombre:</b>
                                          <br>
                                          El nombre del/la <?=$general['nombreProfesional']?>.
                                          <br>
                                          <small class="text-muted">Este se mostrará públicamente.</small>
                                        </li>
                                        <?
                                        if($general['accesoProfesionales'])
                                        {
                                            ?>
                                            <li class="list-group-item">
                                                <b>E-mail:</b>
                                                <br>Correo electrónico con el que el/la <?=$general['nombreProfesional']?> accederá a la app para ver su agenda.<br>
                                                <small class="text-muted">Recordá usar un e-mail único por <?=$general['nombreProfesional']?>.</small>
                                            </li>
                                            <li class="list-group-item">
                                            <b>Contraseña:</b><br>
                                            Contraseña con el que el/la <?=$general['nombreProfesional']?> accederá a la app para ver su agenda.
                                            </li>
                                            <?
                                        }
                                        ?>
                                        <li class="list-group-item">
                                          <b>Color de referencia:</b><br>
                                          El color con el que se va a distinguir dicho profesional en el calendario.<br>
                                          <small class="text-muted">Podés dejar el que la app aplica por defecto o seleccionar el que vos quieras.</small>
                                        </li>
                                        <li class="list-group-item">
                                          <b>Estado:</b><br>
                                          Si se encuentra activo o no.
                                          <br>
                                          <small class="text-muted">En caso de que esté inactivo no podrá usarse en ningún lugar de la app.</small>
                                        </li>
                                        <li class="list-group-item">
                                          <b>Horario:</b>
                                          Puede ser <b data-toggle="tooltip" title="La opción de horario habitual se habilita una vez guardado el profesional"><i>Habitual</i></b> o <b data-toggle="tooltip" title="La opción de horario puntual se edita desde esta misma sección."><i>Puntual</i></b>.<br>
                                          <ul>
                                            <li class="my-2">
                                                En <b>Habitual</b> seleccionarás los horarios disponibles de atención para el/la <?=$general['nombreProfesional']?>.<br>
                                                <small class="text-muted">Estos horarios se aplicarán a todos los días de la semana y se repetirán por igual semana a semana. El turno mañana tiene un límite hasta las 14hs. Para horarios posteriores, o un turno de corrido, se debe utilizar el turno tarde.</small>
                                                <div class="mt-3">
                                                    <img src="//turnos.app/assets/app/img/horarioHabitual.png" class="img-fluid w-75" />
                                                </div>
                                            </li>
                                            <li class="my-4">
                                                En <b>Puntual</b> seleccionarás las fechas y horarios específicos donde estará disponible el/la <?=$general['nombreProfesional']?>.<br>
                                                <small class="text-muted">Estos horarios se aplicarán a todos los días de la semana y se repetirán por igual semana a semana.</small>
                                                <div class="mt-3">
                                                    <img src="//turnos.app/assets/app/img/horarioPuntual.png" class="img-fluid w-75" />
                                                </div>
                                            </li>
                                          </ul>
                                        </li>
                                        <li class="list-group-item">
                                          <b>Sólo privado:</b><br>
                                          Nos permite definir si la agenda esta visible o no en el área del cliente.<br>
                                          <small class="text-muted">Si se encuentra en “SÓLO PRIVADO” se podrán tomar turnos únicamente desde el sector administrativo.</small>
                                        </li>
                                        <li class="list-group-item">
                                          <b><?=ucwords($general['nombreObrasSociales'])?> que atiende</b>
                                        </li>
                                      </ul>
                                    </p>
                                    <p class="mt-5">
                                      Una vez completado presionamos <span class="boldboton btn-primary"><i class="fas fa-save"></i> Guardar</span>.  
                                    </p>
                                    <p>
                                        La app nos llevará de regreso al listado de <?=$general['nombreProfesionales']?> donde, si seleccionamos <b><i>Horario habitual</i></b>, nos mostrará un icono de un calendario <i class="fa-solid fa-calendar-days"></i>. Clickeando sobre el mismo nos permitirá definir los horarios que el/la <?=$general['nombreProfesional']?> atiende.
                                    </p>

                                    <?
                                    if($general['plan']>2)
                                    {
                                        ?>
                                        <!-- // si tiene activa comisiones //
                                        -->
                                        <p>
                                          También encontraremos un icono de <b>Comisiones</b> <i class="fa fa-percent"></i> que nos permite establecer un porcentaje de comisión para el profesional en base al costo del/la <?=$general['nombreObraSocial']?> a realizar, lo que nos brindará en la parte de reportes el detalle de la comisión ganada por el <?=$general['nombreObraSocial']?>.
                                        </p>
                                        <!-- FIN -->
                                        <?
                                    }
                                    ?>


                                  </div>
                                </div>
                              </div>
                                <div class="col-2 sticky-sidebar border-left d-none d-md-block">
                                    <img src="//turnos.app/assets/img/logo_web2_new.png" class="px-3 pb-2" />
                                    <ul>
                                        <li>
                                            <a class="text-secondary" data-href="#inicio" href="javascript:;">Inicio</a>
                                        </li>
                                        <li>
                                            <a class="text-secondary" data-href="#Servicios" href="javascript:;"><?=ucwords($general['nombreObrasSociales'])?></a>
                                        </li>
                                        <li>
                                            <a class="text-secondary" data-href="#Categorias" href="javascript:;"><?=ucwords($general['nombreCategorias'])?></a>
                                        </li>
                                        <li>
                                            <a class="text-secondary" data-href="#Profesionales" href="javascript:;"><?=ucwords($general['nombreProfesionales'])?></a>
                                        </li>
                                        <?/*<li>
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
                                        </li>*/?>
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
