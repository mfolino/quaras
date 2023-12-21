<?
require_once($_SERVER["DOCUMENT_ROOT"].'/inc/fn.php');
AuthController::checkLogin();
AuthController::checkSuperAdmin();


$fechaDesde = isset($_GET['fechaDesde']) ? DateController::formattedToFecha($_GET['fechaDesde']) : date('Y-m-01');
$fechaHasta = isset($_GET['fechaHasta']) ? DateController::formattedToFecha($_GET['fechaHasta']) : date('Y-m-t',strtotime(date('Y-m-d')));
$datediff = strtotime($fechaHasta)-strtotime($fechaDesde);
$datediff = ($datediff / (60 * 60 * 24));


$profesional = $_GET['profesional'] ?? '';
$profesionales = ProfesionalController::getProfesionales()["profesionales"] ?? array();


// Suplencias
$todasLasSuplencias = SuplenciaController::getSuplencias($profesional);
$suplencias= $todasLasSuplencias["suplencias"];
$suplenciasOtro= $todasLasSuplencias["suplenciasOtro"];


$valorMinimoAusente = TratamientoController::cantidadMinimaDeTratamientos();
$pacientes = PacienteController::getPacientesParaReporteComision();
$tratamientos=TratamientoController::getAllTratamientos();
$ordenes= Util::getAllOrdenes();


$comisionesConFecha = Util::comisionesConFecha($idProfesional);
$comisiones=$comisionesConFecha["comisiones"];
$fechasComisiones=$comisionesConFecha["fechaComisiones"];

$valoresYFechas = TratamientoController::valoresYFechas();

$valores = $valoresYFechas["valores"] ;
$fechasValores = $valoresYFechas["fechasValores"] ;

if($profesional<>''){
	$turnosAtendidos = Turno::getTurnosAtendidosConProfesional($fechaDesde, $fechaHasta, $suplencias, $suplenciasOtro, $profesional);
}else{
	$turnosAtendidos=Util::getTurnosAtendidos($fechaDesde, $fechaHasta);
}


#region comentario
/******************************/
//Calcular ventas totales en base a los turnos
/*$totalPeriodo=0;
$totalTratamiento=array();
$totalObraSocial=array();
$totalTurno=array();
$totalAnual=0;
$totalMensual=array();
db_query(0,"select t.idPaciente, t.idOrden, t.fechahora, p.idObraSocial, p.plan, o.idTratamiento from turnos t, pacientes p, ordenes o where (t.estado=1 or t.estado=2) and t.idOrden=o.idOrden and t.idPaciente=p.idPaciente");
for($i=0;$i<$tot;$i++){
	$nres=$res->data_seek($i);
	$row=$res->fetch_assoc();
	$fechaTurno=date("Y-m-d",strtotime($row['fechahora']));
	db_query(2,"select cantidad from obrassociales_valores where idTratamiento='".$row['idTratamiento']."' and idPlan='".$row['plan']."' and idObraSocial='".$row['idObraSocial']."' and fechaAlta<='".date("Y-m-d",strtotime($row['fechahora']))."' order by fechaAlta DESC");
	if(($fechaTurno>=$fechaDesde)and($fechaTurno<=$fechaHasta)){
		$totalPeriodo+=$row2['cantidad'];
		$totalTratamiento[$row['idTratamiento']]+=$row2['cantidad'];
		$totalObraSocial[$row['idObraSocial']]+=$row2['cantidad'];
		if(date("H",strtotime($row['fechahora']))<13){
			$totalTurno['manana']+=$row2['cantidad'];
		}
		if(date("H",strtotime($row['fechahora']))>=13){
			$totalTurno['tarde']+=$row2['cantidad'];
		}
	}
	if(date("Y",strtotime($fechaTurno))==date("Y")){
		$totalAnual+=$row2['cantidad'];
	}
	$totalMensual[date("m",strtotime($fechaTurno))]+=$row2['cantidad'];
}*/
#endregion 

if($_GET['export']<>''){
	header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
	header("Content-Disposition: attachment; filename=EstiloUrbano_comisiones_".$fechaDesde."-".$fechaHasta.".xls");  //File name extension was wrong
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false);
}


?>
<!DOCTYPE html>
<html lang="es">
	<head>
		<?
		$seccion='Reportes';
		$subseccion='Comisiones';
		require_once(incPath.'/head.php');
		?>
	</head>
	<body class="app sidebar-mini rtl">
		<? if($_GET['export']==''){ 
			require_once(incPath.'/header.php');
			require_once(incPath.'/sidebar.php');
		?>
		<main class="app-content">
			<div class="app-title">
				<div>
					<h1><i class="fa fa-bar-chart"></i> Comisiones</h1>
					<p>Utilice este módulo para ver de un rápido vistazo la información estadística del sistema. Los datos que se muestran corresponden al período seleccionado.</p>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12 col-xl-9">
					<div class="widget-small primary coloured-icon">
						<i class="icon fa fa-calendar fa-3x"></i>
						<div class="info">
							<div class="row">
								<div class="col">
									<h6>Seleccionar período y <?=($general['nombreProfesional'])?></h6>
								</div>
							</div>
							<div class="row">							
								<div class="col-md-3">
									<input type="text" id="fechaDesde" class="form-control" value="<?=date("d/m/Y",strtotime($fechaDesde))?>">
								</div>
								<div class="col-md-3">
									<input type="text" id="fechaHasta" class="form-control" value="<?=date("d/m/Y",strtotime($fechaHasta))?>">
								</div>
								<div class="col-md-4">
									<select id="profesional" class="form-control">
										<option value=""<?=($profesional=='') ? ' selected' : ''?>>Todos</option>
										
										<? foreach($profesionales as $id => $nombre){ ?>
											<option value="<?=$id?>"<?=($id==$profesional) ? ' selected' : '' ?>><?=$nombre?></option>
										<? } ?>

									</select>
								</div>
								<div class="col-md-2">
									<button type="button" id="aplicarRango" class="btn btn-primary">Aplicar</button>
									<?
									if($_GET){
										?>
										<button type="button" id="aplicarRango" class="btn btn-success" onclick="window.open('<?=$_SERVER['REQUEST_URI']?>&export=1');"><i class="fa fa-file-excel-o"></i></button>
										<?
									}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 d-none d-xl-block">
					<div class="widget-small info coloured-icon">
						<i class="icon fa fa-percent fa-3x"></i>
						<div class="info">
							<h4>Comisión total</h4>
							<p>
								<b>
									<?
									//echo '$'.number_format($totalPeriodo,2);
									?>
									<span class="valorTotalResultante"></span>
								</b>
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- DataTable -->
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-12">
							<div class="tile">
								<h3 class="tile-title">Turnos atendidos y ausentes</h3>
								<div class="tile-body">
									<div class="table-responsive">
									<? } ?>
										<table class="table table-stripped" id="tablaComisiones">
											<thead>
												<th>Fecha</th>
												<th><?=ucwords($general['nombrePaciente'])?></th>
												<th>Estado</th>
												<? if($profesional==''){ ?>
												<th><?=ucwords($general['nombreProfesional'])?></th>
												<? } ?>
												<th><?=ucwords($general['nombreObraSocial'])?></th>
												<th>Comisión</th>
												<th>Venta</th>
											</thead>
											<tbody>

												<? 

												/* Util::printVar($comisionesConFecha, '186.138.206.135', false); 
												Util::printVar($turnosAtendidos, '186.138.206.135', false);  */

												/* Util::printVar('Fechas comisiones:', '186.138.206.135', false); 
												Util::printVar($fechasComisiones, '186.138.206.135', false); 
												
												Util::printVar('Ordenes:', '186.138.206.135', false); 
												Util::printVar($ordenes, '186.138.206.135', false);  */
												?>

												<?
												$aPagar=0;
												$ventaTotal=0;
												foreach($turnosAtendidos as $idTurno => $contenido){
													
													if($contenido['estado']==1){
														$color='-success';
														$leyenda='Asistió';
													}
													if($contenido['estado']==2){
														$color='-warning';
														$leyenda='Ausente';
													}
													?>
													<tr>
														<td><?=date("d/m/Y H:i",strtotime($contenido['fecha']))?></td>
														<td><?=$pacientes[$contenido['idPaciente']]['nombre']?> <?=$pacientes[$contenido['idPaciente']]['apellido']?></td>
														<td class="table<?=$color?>"><?=$leyenda?></td>
														<? if($profesional==''){ ?>
															<td><?=$profesionales[$contenido['idProfesional']]?></td>
														<? } ?>
														<td><?=$tratamientos[$ordenes[$contenido['idOrden']]]?></td>

														<!-- Comision -->
														<td>
															<?
															$pagaObraSocial=0;
															$porcentajeProfesional=0;
															
															$fechaEnTiempo=strtotime(date("Y-m-d",strtotime($contenido['fecha'])));
															$vectorFechas=$fechasValores[$ordenes[$contenido['idOrden']]];
															/* 
																Util::printVar($fechaEnTiempo, '186.138.206.135', false);
																Util::printVar($vectorFechas, '186.138.206.135', false);
															*/
															if(sizeof($vectorFechas)>0){
																	
																$fechaBuscarValor=array_filter($vectorFechas,function($valor){
																	global $fechaEnTiempo;
																	return $valor<=$fechaEnTiempo;
																});
																
																$fechaBuscarValor=array_unique($fechaBuscarValor);
																
																rsort($fechaBuscarValor);
																
																if(sizeof($fechaBuscarValor)>0){
																	
																	// PagaObraSocial  = venta
																	$pagaObraSocial=$valores[$fechaBuscarValor[0]][$ordenes[$contenido['idOrden']]];
																	$auxPagoObraSocial =  $pagaObraSocial;

																	/* Util::printVar('Paga obra social', '186.138.206.135', false);
																	Util::printVar($pagaObraSocial, '186.138.206.135', false); */
																	
																	if(trim($pagaObraSocial)==''){
																		foreach($fechaBuscarValor as $key => $fecha){
																			$pagaObraSocial=$valores[$fecha][$ordenes[$contenido['idOrden']]];
																			if(trim($pagaObraSocial)<>''){
																				break;
																			}
																		}
																	}

																	if($contenido['estado']==2){
																		$pagaObraSocial=$valorMinimoAusente;
																		$pagaObraSocial=0;
																	}
																	
																	//Voy a buscar cuanto le corresponde al kinesiologo
																	$vectorFechas=$fechasComisiones[$contenido['idProfesional']][$ordenes[$contenido['idOrden']]];
																	/* if($profesional<>''){
																		$vectorFechas=$fechasComisiones[$ordenes[$contenido['idOrden']]];
																	}else{
																		$vectorFechas=$fechasComisiones[$contenido['idProfesional']][$ordenes[$contenido['idOrden']]];
																	} */
																	
																	if(isset($vectorFechas) && sizeof($vectorFechas)>0){	

																		/* Util::printVar('Fecha en tiempo', '186.138.206.135', false);
																		Util::printVar($fechaEnTiempo, '186.138.206.135', false);

																		Util::printVar('Vector de fechas', '186.138.206.135', false);
																		Util::printVar($vectorFechas, '186.138.206.135', false); */
																		
																		$fechaBuscarValor=array_filter($vectorFechas,function($valor){
																			global $fechaEnTiempo;
																			return $valor <= $fechaEnTiempo;
																		});

																		/* Util::printVar('Fecha Buscar Valor', '186.138.206.135', false);
																		Util::printVar($fechaBuscarValor, '186.138.206.135', false); */
																		
																		rsort($fechaBuscarValor);
																		
																		if(sizeof($fechaBuscarValor)>0){
																			
																			/*echo '<br>Fecha: '.$fechaBuscarValor[0];
																			echo '<br>Obra social: '.$pacientes[$contenido['idPaciente']]['idObraSocial'];
																			echo '<br>Tratamiento: '.$ordenes[$contenido['idOrden']];
																			echo '<br>Plan: '.$pacientes[$contenido['idPaciente']]['idPlan'];*/
																			
																			$comisionKine=$comisiones[$fechaBuscarValor[0]][$contenido['idProfesional']][$ordenes[$contenido['idOrden']]];

																			$estaSesion = $contenido['estado']==1 ? ($pagaObraSocial*$comisionKine)/100 : 0;

																			if($auxPagoObraSocial){
																				if($estaSesion != 0){
																					if(!$_GET['export']){
																						// echo $pagaObraSocial.'x'.$comisionKine.'% = ';
																					}
																					
																					/* Util::printVar('Comision', '186.138.206.135', false); */
																					echo "$".$estaSesion;
																					echo '<input type="hidden" class="totalSesion" value="<?=$estaSesion?>" />';
																				}else{
																					if($comisionKine){
																						echo '-';
																					}else{
																						echo 'No hay comisión cargada en el '.($general['nombreProfesional']);
																					}
																				}
																			}else{
																				echo 'No hay precio cargado para el '.($general['nombreObraSocial']);
																			}
																			

																		}
																	}else{
																		echo 'No hay comisión cargada en el '.($general['nombreProfesional']);
																	}
																	
																}
															}else{
																echo 'No hay precio cargado para el '.($general['nombreObraSocial']);
															}
															?>
														</td>


														<td>
															$<?=$pagaObraSocial ?? 0?>
															<input type="hidden" class="totalVenta" value="<?=$pagaObraSocial?>" />
															<?
															$aPagar+=$estaSesion;
															$ventaTotal+=$pagaObraSocial;
															?>
														</td>
													</tr>

													<? 
													/* Util::printVar("idTurno: ".$idTurno, '186.138.206.135', false); 
													Util::printVar($contenido, '186.138.206.135', false); 
													
													Util::printVar("Fechas comisiones: ", '186.138.206.135', false); 
													Util::printVar($fechasComisiones, '186.138.206.135', true);  */
													?>



													<?
												}
												?>
											</tbody>
											<tfooter>
												<tr>
													<td class="font-weight-bold"><?/*Total turnos: <span class="valorTurnos"><?=sizeof($turnosAtendidos)?></span>*/?></td>
													<td></td>

													<? if($profesional==''){ ?>
														<td></td>
													<? } ?>
													<td></td>

													<td class="font-weight-bold">
														TOTAL:
													</td>
													<td class="font-weight-bold">
														$<span class="valorTabla"><?=number_format($aPagar,2)?></span>
													</td>
													<td class="font-weight-bold">
														$<span class="valorTablaTotal"><?=number_format($ventaTotal,2)?></span>
													</td>
												</tr>
											</tfooter>
										</table>
										<?
										if($_GET['export']==''){
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>
		
		<!-- Modal -->
		<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLongTitle">Jorge Cancela 20/04 9:00</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						...
					</div>
					<div class="modal-footer">
						...
					</div>
				</div>
			</div>
		</div>
		
		<?
		require_once(incPath.'/scripts.php');
		?>
		<!-- Page specific javascripts-->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
		<script type="text/javascript">
			
			var table = $('#tablaComisiones').DataTable({
				"paging":false
			});
			
			table.on( 'draw', function () {
				var sumaTotal=0;
				var cantidadTurnos=0;
				$(".totalSesion").each(function(){
					sumaTotal+=parseFloat($(this).val());
					cantidadTurnos++;
				})
				$(".valorTabla").html(sumaTotal.toFixed(2));
				// $(".valorTurnos").html(cantidadTurnos);
				
				var sumaTotal=0;
				var cantidadTurnos=0;
				$(".totalVenta").each(function(){
					sumaTotal+=parseFloat($(this).val());
					cantidadTurnos++;
				})
				$(".valorTablaTotal").html(sumaTotal.toFixed(2));
				// $(".valorTurnos").html(cantidadTurnos);
			});
			
			<?
			if($_GET['debug']){
				?>
				table.search('<?=$_GET['debug']?>').draw();
				<?
			}
			?>
		
			$("#aplicarRango").click(function(e){
				e.preventDefault();
				const profesional = $("#profesional").val();
				const filtroProfesional = profesional ? `&profesional=${profesional}` : ' ';
				window.location.href='/reportes/comisiones?fechaDesde='+$("#fechaDesde").val()+'&fechaHasta='+$("#fechaHasta").val()+filtroProfesional;
			})
			
			Date.prototype.addDays = function(days) {
				var dat = new Date(this.valueOf());
				dat.setDate(dat.getDate() + days);
				return dat;
			}
			
			$('#fechaDesde').datepicker({
				format: "dd/mm/yyyy",
				autoclose: true,
				todayHighlight: true,
				startDate: "01/05/2018"
			}).on('hide',function(e){
				// e.date
				$("#fechaHasta").datepicker('setStartDate',e.date);
				$("#fechaHasta").datepicker('setEndDate',e.date.addDays(90));
			});
			
			var fechaInicio=new Date('<?=$fechaDesde?>');
			
			$('#fechaHasta').datepicker({
				format: "dd/mm/yyyy",
				autoclose: true,
				todayHighlight: true,
				startDate:fechaInicio,
				endDate:fechaInicio.addDays(90)
			});
			
			$(".valorTotalResultante").html('$<?=number_format($aPagar,2)?>');
		</script>
		
		<?
		// include(incPath.'/analytics.php');
		?>
		<?
}
?>
	</body>
</html>