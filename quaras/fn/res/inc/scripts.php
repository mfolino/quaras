<?=$general['codigoBody']?>

<!-- Essential javascripts for application to work-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
<!-- The javascript plugin to display page loading on top-->
<script src="<?=$cdn?>/js/plugins/pace.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/select2.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/bootstrap-notify.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/select2.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/jquery-ui.custom.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="<?=$cdn?>/js/plugins/chart.js"></script>		

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/i18n/defaults-es_ES.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>


<script>
    var estaQuieto=1;

    //Agrego div para mostrar notificaciones
    $("body").append('<div class="alert-container"></div>');

    var alert = $(".alert-container");

    (function () {
        "use strict";

        var treeviewMenu = $('.app-menu');

        // Toggle Sidebar
        $('[data-toggle="sidebar"]').click(function(event) {
            event.preventDefault();
            $('.app').toggleClass('sidenav-toggled');
        });

        // Activate sidebar treeview toggle
        $("[data-toggle='treeview']").click(function(event) {
            event.preventDefault();
            if(!$(this).parent().hasClass('is-expanded')) {
                treeviewMenu.find("[data-toggle='treeview']").parent().removeClass('is-expanded');
            }
            $(this).parent().toggleClass('is-expanded');
        });

        // Set initial active toggle
        $("[data-toggle='treeview.'].is-expanded").parent().toggleClass('is-expanded');

        //Activate bootstrip tooltips
        $("[data-toggle='tooltip']").tooltip({html:true});

        $("#logout").click(function(){
            $.post(
                '/profesionales/save',
                {action:'logout'},
                function(response){
                    console.log(response);
                    if(response.status=='OK'){
                        window.location.href = '/admin';
                    }else{
                        console.log(response);
                        Swal.fire("Error!", "Ha ocurrido un error al cerrar sesión. Intente nuevamente.", "error");
                    }
                }
            )
        });

        alert.hide();	

    })();





    //Funciones para preloader
    function preloaderButton(status, originalButton, button){
        if(status=='show'){
            $("#"+button).prop('disabled', true).html('<i class="fas fa-spinner fa-pulse"></i> Cargando...');
        }else{
            $("#"+button).prop('disabled', false).html(originalButton);
        }
    }




    //Funciones de calendario de turnos
    function mostrarFichaCompleta(idPaciente,idTurno,title,start){
        $.post('/pacientes/ver.php',{idPaciente:idPaciente},function(resultado){
            resultados=resultado.split('|');
            $('.modal .modal-title').html('Ficha '+resultados[0]);
            $('.modal .modal-body').html(resultados[1]);
            $('.modal .modal-footer').html('<button type="button" class="btn btn-outline-warning" onclick="cargarInfoTurno('+idTurno+',\''+title+'\',\''+start+'\')">Volver atrás</button>');
            $('.modal').modal('show');
        })
    }

    function mostrarTurnosCompletos(idTurno,title,start){
        $.post('/pacientes/verTurnos.php',{idTurno:idTurno},function(resultado){
            titulo=title.split(' - ');
            $('.modal .modal-title').html('Turnos '+titulo[0]);
            $('.modal .modal-body').html(resultado);
            $('.modal .modal-footer').html('<button type="button" class="btn btn-outline-warning" onclick="cargarInfoTurno('+idTurno+',\''+title+'\',\''+start+'\')">Volver atrás</button>');
            $('.modal').modal('show');
        })
    }

    function cargarInfoTurno(idTurno,title,start){
        if(idTurno!='NO'){
            $.post('/turnos/cargarInfoTurno.php',{idTurno:idTurno,title:title,start:start},function(resultado){
                /*console.clear();
                console.log(resultado);*/
                resultado=resultado.split('|');
                $('.modal .modal-title').html('');
                $('.modal .modal-body').html('');
                $('.modal .modal-footer').html('');
                // console.log(resultado);
                if(resultado[0]=='OK'){
                    $('.modal .modal-title').html(title+' - '+getDate(start));
                    $('.modal .modal-body').html(resultado[1]);
                    $('.modal .modal-footer').html(resultado[2]);
                    $('.modal').modal('show');
                }
                if(resultado[0]=='NEW'){
                    $('.modal .modal-title').html('Completar ficha '+title);
                    $('.modal .modal-body').html('<p>Complete la ficha y la orden del usuario para poder editar el turno.</p><iframe src="/pacientes/editar?id='+resultado[1]+'&from=turnos&idTurno='+idTurno+'&title='+title+'&start='+start+'" frameborder="0" height="500" class="w-100"></iframe>');
                    $('.modal .modal-footer').html(resultado[2]);
                    $('.modal').modal('show'); 
                }
            })
        }
    }

    function cargarInfoTurnoSimple(event){
        $.post('/turnos/cargarInfoTurno.php',{idTurno:event.id,title:event.nombrePax,start:event.start.format('DD/MM/Y hh:mm')},function(response){

            console.log(response);

            $(".modal-header").html(`
                <div class="col-6 mx-0 px-0">
                    <h3 class="modal-title">${event.nombrePax}</h3>
                </div>
                <div class="col-5 align-items-center text-right mx-0 px-0">
                    <h6 class="my-0">${event.profesional} <i class="fas fa-user ml-1"></i></h6>
                    <small>${event.start.format('DD/MM/YYYY HH:mm')} - ${event.end.format('HH:mm')} <i class="fas fa-calendar ml-1"></i></small>
                </div>`).css('background-color',event.colorProfesional).css('color',event.textColor);


            $(".modal-body").html(``);
            /*console.clear();
            console.log(resultado);*/
            
            $('.modal .modal-body').html(response.body);
            $('.modal .modal-footer').html(response.footer);
            $('.modal').modal('show');

        })
    }

    function cargarInfoTurnoCupos(event){
        if(event){

            $(".modal-header").html(`
                <div class="col-6 mx-0 px-0">
                    <h3 class="modal-title">${event.actividad}</h3>
                </div>
                <div class="col-5 align-items-center text-right mx-0 px-0">
                    <h6 class="my-0">${event.profesional} <i class="fas fa-user ml-1"></i></h6>
                    <small>${event.start.format('DD/MM/YYYY HH:mm')} - ${event.end.format('HH:mm')} <i class="fas fa-calendar ml-1"></i></small>
                </div>`).css('background-color',event.colorProfesional).css('color',event.textColor);


            $(".modal-body").html(``);

            $.post('/turnos/cargarInfoTurno.php',{idProfesional : event.idProfesional, idTratamiento : event.idTratamiento, fechaInicio : event.start.format("YYYY-MM-DD HH:mm:ss"), fechaFin : event.end.format("YYYY-MM-DD HH:mm:ss")},function(response){

                $('.modal .modal-body').html(response.body);
                $('.modal .modal-footer').html(response.footer);

                $(".modal").modal('show');

                $('.selectPaciente').css('width', '100%');
            
                $('.selectPaciente').select2({
                    ajax: {
                        url: '/pacientes/save',
                        dataType: 'json',
                        data: function (params) {       
                                
                            var query = {
                                query: params.term,
                                from:'turnos',
                                action: "getPacientesSelect"
                            }
                            return query;
                        },
                        processResults: function (data) {
                            console.log(data);
                            return {
                                results: data.results
                            };
                        }
                    }
                });

                $('.selectPaciente').on('select2:select', function (e) {
                    
                    var seleccion=e.params.data;

                    console.log(seleccion);

                    if($(this).val()!='NN'){
                        $(".nuevoPaciente").removeClass('d-block');
                    }else{
                        $(".nuevoPaciente").removeClass('d-none');
                    }

                    $("#nombre").val('');
                    $("#apellido").val('');
                    $("#codArea").val('');
                    $("#telefono").val('');
                    $("#mail").val('');

                    if($(this).val()!='NN'){
                        $("#nombre").val(seleccion.nombre);
                        $("#apellido").val(seleccion.apellido);
                        $("#codArea").val(seleccion.codArea);
                        $("#telefono").val(seleccion.telefono);
                        $("#mail").val(seleccion.mail);
                        $("#idPaciente").val(seleccion.id);
                    }
                    
                });

                $("#agregarPaciente").on('click',function(e){
                    validar();

                    var contenidoBoton=$("#agregarPaciente").html();

                    $(".nuevoPaciente .required:visible, .campoMail .required:visible, .required:visible").trigger('change');

                    e.preventDefault();
                    var algunoMal=0;
                    var algunoBien=0;
                    $.each($('.required:visible'),function(key,element){
                        if($(element).hasClass('is-invalid')){
                            algunoMal++;
                        }
                        if($(element).hasClass('is-valid')){
                            algunoBien++;
                        }
                    })

                    if((algunoMal<1)&&(algunoBien==$('.required:visible').length)){
                        
                        $("#agregarPaciente").html('<i class="fa fa-spinner fa-spin fa-fw"></i> Cargando...');
                        $("#agregarPaciente").attr('disabled','disabled');
                        
                        $.post('/turnos/save',$('#inscribirPaciente').serialize(),function(response){
                            
                            console.log(response);
                            
                            if(response.status=='OK'){

                                var texto=response.nombre+' agendado correctamente para el '+response.fecha+'.';

                                if(response.textoConfirmacionPublic){
                                texto=response.textoConfirmacionPublic;
                                }

                                Swal.fire({
                                    title:'Reservado!',
                                    text:texto,
                                    type:'success',
                                    onClose: () => {
                                        parent.$('.modal').modal('hide');
                                        parent.$('#calendar').fullCalendar('refetchEvents');
                                    }
                                });
                            }else{
                                if(response.status=='datosIncompletos'){
                                    Swal.fire('Lo sentimos!','Verifica los campos marcados en rojo para continuar.','error');
                                }else{
                                    if(response.status=='duplicado'){
                                        Swal.fire('Lo sentimos!',response.nombre+', ya tenés cargado un turno el '+response.fecha+'. No podés cargar otro durante el transcurso de otro.','error');
                                    }else{
                                        if(response.status=='tomado'){
                                            Swal.fire('Lo sentimos!','El turno solicitado ha sido tomado por otro usuario. Intentalo nuevamente con otros parametros.','error');
                                        }else if(response.status == 'tratamientoBloqueado'){
                                            Swal.fire('Lo sentimos!',response.message,'warning');
                                        }else{
                                            Swal.fire('Lo sentimos!',response,'error');
                                        }
                                    }
                                }
                            }
                            
                            $(".loginBtn").html(contenidoBoton);
                            $(".loginBtn").attr('disabled',false);
                            
                        })
                    }else{
                        Swal.fire('Lo sentimos!','Verifica los campos marcados en rojo para continuar.','error');
                    }

                })

            })

        }
    }

    function estadoTurno(id,title,start,estado){
        $.post('/turnos/save',{action: 'estadoTurno', idTurno:id, estado:estado}, function(resultado){
            console.log(resultado);
            if(resultado.status=='OK'){
                $('#calendar').fullCalendar('refetchEvents');
                $(".modal").modal('hide');
            }else{
                if(resultado.status=='dosAusentes'){
                    Swal.fire({
                        title: "Está seguro/a?",
                        text: "Este es el segundo ausente del paciente. Si lo marca como ausente todos los siguientes turnos serán cancelados automáticamente.",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Si, seguro/a!",
                        cancelButtonText: "No, no",
                        closeOnConfirm: false,
                        closeOnCancel: true
                        
                    }).then((result) => {
                        /* Read more about isConfirmed, isDenied below */
                        if (result.value) {
                            $.post('/turnos/save',{action: 'estadoTurno', idTurno:id, estado:estado, confirmo:1}, function(resultado){
                                console.log(resultado);
                                if(resultado.status=='OK'){
                                    $('#calendar').fullCalendar('refetchEvents');
                                    $(".modal").modal('hide');
                                }else{
                                    Swal.fire("Error!", "Ha ocurrido un error al cambiar el estado del turno. Intente nuevamente.", "error");
                                    // revertFunc();
                                }
                            })
                        } 
                    })
                }else{
                    Swal.fire("Error!", "Ha ocurrido un error al cambiar el estado del turno. Intente nuevamente.", "error");
                }
                // revertFunc();
            }
        })
    }

    function changeProfesional(id,title,start,idProfesional,nombreProfesional){
        Swal.fire({
            title: "Está seguro/a?",
            text: "Desea reasignar el turno "+title+" a "+nombreProfesional+"? El mismo se asignará al profesional seleccionado, más allá de que el mismo no esté disponible en el horario del turno.",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Si, estoy seguro/a!",
            cancelButtonText: "No, mejor no",
            closeOnConfirm: false,
            closeOnCancel: true
            
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.value) {
                $.post('/turnos/save',{action: 'cambiarProfesional', idTurno:id, idProfesional:idProfesional}, function(resultado){
                    // console.log(resultado);
                    if(resultado=='OK'){
                        $('#calendar').fullCalendar('refetchEvents');
                        /* Swal.close(); */
                        $(".modal").modal('hide');
                    }else{
                        Swal.fire("Error!", "Ha ocurrido un error al asignar el profesional al turno. Intente nuevamente.", "error");
                        // revertFunc();
                    }
                })
            } 
        })
    }


    function preCancelarTurno(id,title,start){
        $(".comentariosTurno").removeClass('d-none');
        $(".botonConfirmar").html("Confirmar cancelación");
        $(".botonConfirmar").attr("onclick","cancelarTurno("+id+",'"+title+"','"+start+"')");
    }

    function preCancelarTurnoNew(){
        $(".comentariosTurno").removeClass('d-none');

        $(".botonAccion").prop('disabled',true);

        $("[data-toggle='tooltip']").tooltip({
            html:true,
        });
    }

    function abortarPreCancelar(){
        $(".comentariosTurno").addClass('d-none');
        
        $(".botonAccion").prop('disabled',false);

        $("[data-toggle='tooltip']").tooltip({
            html:true,
        });
    }

    function cancelarTurno(id,title,start){

        console.log(start);

        var textoOriginal = $(".botonCancelarFinal").html();

        $(".botonCancelarFinal").html('<i class="fa fa-spinner fa-spin"></i> Cancelando turno...').prop('disabled',true);

        $.post('/turnos/save.php',{action:'verificarFecha',idTurno:id},function(resultado){
            // console.log(resultado);
            if(resultado=='OK'){
                Swal.fire({
                    title: "Está seguro/a?",
                    text: "Desea cancelar el turno de "+title+" el "+start+".",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Si, estoy seguro/a!",
                    cancelButtonText: "No, mejor no",
                    closeOnConfirm: false,
                    closeOnCancel: true
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.value) {
                        $.post('/turnos/save',{action: 'cancelarTurno', idTurno:id, comentarios:$("#comentarios").val(), devolverPago: $("#devolverPago").val(), mandarMail: $("#mandarMail").val()}, function(resultado){
                            console.log(resultado);
                            if(resultado=='OK'){
                                $('#calendar').fullCalendar('refetchEvents');
                                /* Swal.close(); */
                                $(".modal").modal('hide');
                            }else{
                                Swal.fire("Error!", "Ha ocurrido un error al intentar cancelar el turno. Intente nuevamente.", "error");

                                $(".botonCancelarFinal").html(textoOriginal).prop('disabled',false);
                                // revertFunc();
                            }
                        })
                    }else{
                        $(".botonCancelarFinal").html(textoOriginal).prop('disabled',false);
                    }
                })
            }else{
                Swal.fire("Error!", "No es posible cancelar turnos en el mismo día o en el pasado.", "error");

                $(".botonCancelarFinal").html(textoOriginal).prop('disabled',false);

            }
        })
    }


    function eliminarTurno(id, fecha){
        Swal.fire({
            title: "Está seguro/a?",
            text: "Desea eliminar el turno del paciente el "+fecha+". Esta acción NO puede deshacerse",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Si, estoy seguro/a!",
            cancelButtonText: "No, mejor no",
            closeOnConfirm: false,
            closeOnCancel: true
        }).then((result) => {

            if (result.value) {
                $.post('/turnos/save',{action: 'deleteTurno', id:id}, function(resultado){
                    console.log(resultado); 
                    if(resultado=='OK'){
                        $('#calendar').fullCalendar('refetchEvents');
                        $(".modal").modal('hide');
                    }else if(resultado=='eliminar_turnoModificado'){
                        Swal.fire("No puede eliminar este turno!", "No puede eliminar el turno seleccionado porque el estado del mismo fue modificado.", "info");
                    }else{                    
                        Swal.fire("Error!", "Ha ocurrido un error al intentar cancelar el turno. Intente nuevamente.", "error");
                    }
                })
            }
        })
    }

    function eliminarTurnoModal(id){
        Swal.fire({
            title: "Está seguro/a?",
            text: "Desea eliminar al cliente de la actividad",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Si, estoy seguro/a!",
            cancelButtonText: "No, mejor no",
            closeOnConfirm: false,
            closeOnCancel: true
        }).then((result) => {

            if (result.value) {
                $.post('/turnos/save',{action: 'deleteTurno', id:id}, function(resultado){
                    console.log(resultado);
                    if(resultado=='OK'){
                        $('#calendar').fullCalendar('refetchEvents');
                        swal.close();
                        $(".modal").modal('hide');
                    }else{
                        swal("Error!", "Ha ocurrido un error al intentar cancelar el turno. Intente nuevamente.", "error");
                        // revertFunc();
                    }
                })
            }
        });
    }



    var profesionalSeleccionado = '';

    $("#filtrarProfesional").on('change',function(){
        profesionalSeleccionado=$(this).val();
        $("#calendar").fullCalendar('refetchEvents');
    })






    //Validacion
    $('.soloLetras').bind('keyup blur',function(){
        var node = $(this);
        node.val(node.val().replace(/[^a-zA-Záéíóú ]/g,'') ); }
    );
    $('.soloNumeros').bind('keyup blur',function(){
        var node = $(this);
        node.val(node.val().replace(/[^0-9-]/g,'') ); }
    );
    function getDate(fecha) {
        var date = new Date(fecha),
        year = date.getFullYear(),
        month = (date.getMonth() + 1).toString(),
        formatedMonth = (month.length === 1) ? ("0" + month) : month,
        day = date.getDate().toString(),
        formatedDay = (day.length === 1) ? ("0" + day) : day,
        hour = date.getHours().toString(),
        formatedHour = (hour.length === 1) ? ("0" + hour) : hour,
        minute = date.getMinutes().toString(),
        formatedMinute = (minute.length === 1) ? ("0" + minute) : minute,
        second = date.getSeconds().toString(),
        formatedSecond = (second.length === 1) ? ("0" + second) : second;
        return formatedDay + "/" + formatedMonth + "/" + year + " " + formatedHour + ':' + formatedMinute;
    };










    //Notificaciones
    function crearHtmlTostada(titulo,texto,tipo,tiempo){

        let type;
        let icono;

        switch(tipo){
            case 'nuevo':
                type='info';
                icono='calendar-plus-o';
                break;
            case 'confirmado':
                type='success';
                icono='calendar-check-o';
                break;
            case 'ausente':
                type='warning';
                icono='calendar-minus-o';
                break;
            case 'cancel':
                type='danger';
                icono='calendar-times-o';
                break;
            case 'espera':
                type='enEspera';
                icono='clock-o';
                break;
            default:
                type='info';
                icono='calendar-o';
                break;
        }

        console.log(type);
        console.log(icono);

        return `
            <div class="card border-${type} mb-2" id="alert${tiempo}" style="display:none">
                <div class="card-header bg-${type} text-white">
                    <i class="fa fa-${icono}"></i> ${titulo}
                </div>
                <div class="card-body py-3">
                    ${texto}
                </div>
            </div>`;
    }

    function agregarTostada(titulo, texto, tipo){

        let tiempo=Date.now();

        alert.append(crearHtmlTostada(titulo, texto, tipo, tiempo));

        if(alert.is(":visible")){
            
        }else{
            alert.show();
        }
        
        $("#alert"+tiempo).fadeIn(200,function(){
            setTimeout(function(){
            eliminarTostada(tiempo);
            }, 6000);
        });
    }

    function eliminarTostada(tiempo){
        $("#alert"+tiempo).fadeOut(100,function(){
            $(this).remove(); 
        }); 

        if(alert.children().length==0){
            alert.hide();
        }
    }
















    //Impresion
    function prePrint(title = ''){
        $('.modal .modal-title').html(title ? "Imprimir "+title : "Imprimir turnos");
        $('.modal .modal-body').html('<iframe class="w-100" style="height:400px" frameborder="0" src="/turnos/prePrint.php"></iframe>');
        $('.modal .modal-footer').html('');
        $('.modal').modal('show');
    }
            
    function imprimirTurnos(id, fecha){
        $("#contenedorImpresion").attr('src','imprimirTurnos.php?quien='+id+'&fecha='+fecha);
        $("#contenedorImpresion").load(function() {
            $("#contenedorImpresion").get(0).contentWindow.print();
        })
    }

    function imprimirFicha(id){
        $("#contenedorImpresion").attr('src','/pacientes/imprimirFicha.php?id='+id);
        $("#contenedorImpresion").load(function() {
            $("#contenedorImpresion").get(0).contentWindow.print();
        })
    }








    //Multiples turnos
    function cargarTurnosTomados(entradasActuales){
        var entradasActuales=JSON.parse(localStorage.getItem('fechasTomadas'));
                            
        $(".contenedorTurnos .card-text").html('');
                                                
        $.each(entradasActuales,function(index,value){
            // var fechaNueva = value.split('-');
            var fechaNueva = value;
            $(".contenedorTurnos .card-text").append('<p><a href="javascript:;" onclick="eliminarTurnoMultiple('+index+')"><i class="fa fa-times"></i></a> '+fechaNueva+'</p>');
        })
    }
                        
    if(localStorage.getItem('multiple')){
        cargarTurnosTomados();
    }
                        
    function eliminarTurnoMultiple(index){
                            
        var entradasActuales=JSON.parse(localStorage.getItem('fechasTomadas'));
                            
        entradasActuales.splice(index,1);
                            
        localStorage.setItem('fechasTomadas',JSON.stringify(entradasActuales));
                            
        cargarTurnosTomados();
    }
                        
                        

    function dragElement(elmnt) {
        var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
        if (document.getElementById(elmnt.id + "header")) {
            // if present, the header is where you move the DIV from:
            document.getElementById(elmnt.id + "header").onmousedown = dragMouseDown;
        } else {
            // otherwise, move the DIV from anywhere inside the DIV: 
            elmnt.onmousedown = dragMouseDown;
        }

        function dragMouseDown(e) {
            e = e || window.event;
            e.preventDefault();
            // get the mouse cursor position at startup:
            pos3 = e.clientX;
            pos4 = e.clientY;
            document.onmouseup = closeDragElement;
            // call a function whenever the cursor moves:
            document.onmousemove = elementDrag;
        }
                    
        function elementDrag(e) {
            e = e || window.event;
            e.preventDefault();
            // calculate the new cursor position:
            pos1 = pos3 - e.clientX;
            pos2 = pos4 - e.clientY;
            pos3 = e.clientX;
            pos4 = e.clientY;
            // set the element's new position:
            elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
            elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
        }
                        
        function closeDragElement() {
            // stop moving when mouse button is released:
            document.onmouseup = null;
            document.onmousemove = null;
        }
    }

    function mostrarMultiples(){
        if(localStorage.getItem('multiple')){
            $.post('/turnos/save',{action:'getInfoPaciente',idPaciente:localStorage.getItem('idPaciente'),idOrden:localStorage.getItem('idOrden')},function(response){
                $("#contenedorTurnosheader .card-title").html(response.paciente);
                $("#contenedorTurnosheader .card-subtitle").html(response.orden);
                if(!localStorage.getItem('fechasTomadas')){
                    $("#contenedorTurnos .card-text").html('Seleccione los turnos que desee asignar en el calendario. De ser necesario puede posicionar el mouse sobre la cabecera de este módulo para moverlo.');
                }
                dragElement(document.getElementById("contenedorTurnos"));
            })
            $("#contenedorTurnos").removeClass('d-none');
        }else{
            $("#contenedorTurnos").addClass('d-none');
        }
    }
                        
    function multiplesTurnos(idPaciente,idOrden,tomados,cantidad){
        localStorage.setItem('multiple',1);
        localStorage.setItem('idPaciente',idPaciente);
        localStorage.setItem('idOrden',idOrden);
        localStorage.setItem('disponibles',(cantidad-tomados));
        $('.modal').modal('hide'); 
        mostrarMultiples();
    }
    function cancelarMultiples(){
        localStorage.removeItem('multiple');
        localStorage.removeItem('idPaciente');
        localStorage.removeItem('idOrden');
        localStorage.removeItem('disponibles');
        localStorage.removeItem('fechasTomadas');
        mostrarMultiples();
    }
                        
    mostrarMultiples();
                        
    function agendarMultiples(){
                            
        if(!localStorage.getItem('fechasTomadas')){
            Swal.fire("Turnos incompletos", "Debe seleccionar al menos un turnos para agendar.", "error");
        }else{
                                
            $.post('/turnos/save',{action:'saveMultiples',idPaciente:localStorage.getItem('idPaciente'),idOrden:localStorage.getItem('idOrden'),fechas:localStorage.getItem('fechasTomadas')},function(response){
                // console.log(response);
                if(response.status=='OK'){
                    Swal.fire("Turnos tomados", "Los turnos solicitados han sido asignados correctamente.", "success");
                    cancelarMultiples();
                    $('#calendar').fullCalendar('refetchEvents');
                }else{
                    Swal.fire("Lo sentimos", "Ha ocurrido un error al intentar agendar los turnos. Intentelo nuevamente.", "error");
                }
            }).fail(function(e){
                console.log(e);
            })
        }
    }

    function validar(){
        $('.required:visible').on('blur keyup change',function(e){
            if($(this).val().length>2){
                if($(this).prop('type')=='email'){
                    if(isEmail($(this).val())){
                        $(this).removeClass('is-invalid');
                        $(this).addClass('is-valid');
                    }else{
                        $(this).removeClass('is-valid');
                        $(this).addClass('is-invalid');
                    }
                }else{
                    $(this).removeClass('is-invalid');
                    $(this).addClass('is-valid');
                }
            }else{
                if(($(this).prop('id')=='tratamiento')||($(this).prop('id')=='profesional')||($(this).prop('id')=='codArea')){
                    if($("#tratamiento option:selected").val()==""){
                        $("#tratamiento").removeClass('is-valid');
                        $("#tratamiento").addClass('is-invalid');
                    }else{
                        $("#tratamiento").removeClass('is-invalid');
                        $("#tratamiento").addClass('is-valid');
                    }
                    if($("#profesional option:selected").val()!=''){
                        $("#profesional").removeClass('is-invalid');
                        $("#profesional").addClass('is-valid');
                    }else{
                        $("#profesional").removeClass('is-valid');
                        $("#profesional").addClass('is-invalid');
                    }
                    if($("#codArea").val().length<2){
                        $("#codArea").removeClass('is-valid');
                        $("#codArea").addClass('is-invalid');
                    }else{
                        $("#codArea").removeClass('is-invalid');
                        $("#codArea").addClass('is-valid');
                    }
                }else{
                    $(this).addClass('is-invalid');
                    $(this).removeClass('is-valid');
                }
            }
        })
    }

    function guardarComentarios(idTurno){
        $.post('/turnos/save',{action:'guardarComentarios', idTurno:idTurno, comentarios:$("#comentarios").val()},function(response){
            // console.log(response);
            if(response.status=='OK'){
                Swal.fire("Excelente!", "La información ingresada ha sido guardada correctamente.", "success");
            }else{
                Swal.fire("Lo sentimos", "Ha ocurrido un error al intentar guardar la información. Intentelo nuevamente.", "error");
            }
        }).fail(function(e){
            console.log(e);
        })
    }
</script>