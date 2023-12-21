<?
require_once('inc/fn.php');
?>
<!DOCTYPE html>
<html>
    <head>
        <? require_once(incPath.'/head.php'); ?>
        <? $seccion='Scanner'; ?>
        <style>
            body{
                font-size:1.5rem;
            }
            .login-box{
                max-width: none!important;
                min-height: 0!important;
            }
            @media screen and (max-width: 750px) {
                .logo img{
                    width: 250px !important;
                }
            }

            #backgroundFondo{
                background-color: var(--primary);
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 50vh;
                z-index: -9;
            }
        </style>
    </head>
    
    <body>
        <section id="backgroundFondo"></section>
        <section class="login-content">
            <div class="logo">
                <img src="img/<?=$general['isologo']?>" width="<?=$general['logoWidth']?>" />
            </div>
            <div class="login-box px-5 py-4 container">
            
                <h4 class="text-center mb-3"><i class="fa-solid fa-qrcode mr-1"></i><?=ucfirst($general["nombreTurnos"])?> del día</h4>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?=ucfirst($general["nombrePaciente"])?></th>
                            <th>Entradas</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyTurnos"></tbody>
                </table>

            </div>
            
            <? require_once(incPath.'/footer.php'); ?>
        </section>
        
        <? require_once(incPath.'/scripts.php'); ?>


        <script>
            var linkQR = ""
            window.addEventListener('keypress', function(e){

                console.log(e.key);

                if(( e.which == 9 ) || ( e.key == 'Enter')) { // Tengo la URL completa
                    e.preventDefault();
                    console.log("Aca!");
                    console.log(linkQR);

                    if(linkQR.search('ShiftQ{') != -1){
                        molinete = 1;
                        QR = linkQR.split("ShiftQ{")[1] // Busco el id del grupo
                    }else{
                        molinete = 2;
                        QR = linkQR.split("Shift{")[1] // Busco el id del grupo
                    }

                    console.log("Molinete: "+molinete);

                    getInfoTurno(QR, molinete)
            
                    linkQR = ""
            
                }else {
                    linkQR += e.key;
                }

            });

            function getInfoTurno(idGrupo, molinete){
                $.post(
                    "/turnos/save",
                    {
                        action: "qr_validate",
                        idGrupo,
                        totalFilasEscaneadas: $(".filaTurno").length
                    },
                    function(response){
                        
                        if(response.status == "OK"){

                            fetch('arduino.php?id='+molinete+'&op=ON');

                            setTimeout(function(){
                                fetch('arduino.php?id='+molinete+'&op=OFF');
                            },100);

                            $(".filaTurno").removeClass("bg-success text-white")

                            const {numero, cliente, entradas, total, idGrupo} = response

                            $("#tbodyTurnos").prepend(`
                                <tr class="filaTurno" id="filaTurno-${idGrupo}">
                                    <td>${numero}</td>
                                    <td>${cliente}</td>
                                    <td>${entradas}</td>
                                    <td>${total}</td>
                                </tr>
                            `)

                            // Marco la última entrada registrada
                            $(".filaTurno").first().addClass("bg-success text-white")

                        }else{ // Error
                            $("#tbodyTurnos").prepend(`
                                <tr class="filaError bg-danger text-white">
                                    <td>-</td>
                                    <td class="text-center" colspan="4">${response.message}</td>
                                </tr>
                            `)
                        }
                    }
                )
            }
        </script>
    </body>
</html>
