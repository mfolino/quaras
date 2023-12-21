<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();
AuthController::isAdmin();


$fechaDesde = isset($_GET['fechaDesde']) ? DateController::formattedToFecha($_GET['fechaDesde']) : date('Y-m-01');
$fechaHasta = isset($_GET['fechaHasta']) ? DateController::formattedToFecha($_GET['fechaHasta']) : date('Y-m-t',strtotime(date('Y-m-d')));

$hayTurnos = Turno::cantidadDeTurnosPorFecha($fechaDesde, $fechaHasta) > 0 ? 1 : 0; 
$valorMinimoAusente = TratamientoController::cantidadMinimaDeTratamientos();
$tratamientos = TratamientoController::getAllTratamientos();

$ventasTotalesPorTurnos = VentaController::calcularVentasTotalesPorTurnos($fechaDesde, $fechaHasta, $valorMinimoAusente);
$totalPeriodo=$ventasTotalesPorTurnos["totalPeriodo"];
$totalTratamiento=$ventasTotalesPorTurnos["totalTratamiento"];
$totalAnual=$ventasTotalesPorTurnos["totalAnual"];
$totalMensual=$ventasTotalesPorTurnos["totalMensual"];
$totalDiario=$ventasTotalesPorTurnos["totalDiario"];



?>
<!DOCTYPE html>
<html lang="es">
	<head>
		<?
		$seccion='Reportes';
		$subseccion='Ventas';
		require_once(incPath.'/head.php');
		?>
	</head>
	<body class="app sidebar-mini rtl">
		<?
		require_once(incPath.'/header.php');
		require_once(incPath.'/sidebar.php');
		?>
		<main class="app-content">
			<div class="app-title">
				<div>
					<h1><i class="fa fa-bar-chart"></i> Ventas</h1>
					<p>Utilice este módulo para ver de un rápido vistazo la información estadística del sistema. Los datos que se muestran corresponden al período seleccionado.</p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12 col-xl-4">
					<div class="widget-small primary coloured-icon">
						<i class="icon fa fa-calendar fa-3x"></i>
						<div class="info">
							<div class="row">
								<div class="col">
									<h6>Seleccionar período</h6>
								</div>
							</div>
							<div class="row">
								<div class="col">
									<input type="text" id="fechaDesdeHasta" class="form-control" value="<?=date("d/m/Y",strtotime($fechaDesde))?> - <?=date("d/m/Y",strtotime($fechaHasta))?>">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-4 col-xl-4">
					<div class="widget-small info coloured-icon">
						<i class="icon fas fa-money-bill fa-3x"></i>
						<div class="info">
							<h4>Período</h4>
							<p>
								<b>
									<?
									echo '$'.number_format($totalPeriodo,2);
									?>
								</b>
							</p>
						</div>
					</div>
				</div>
				<div class="col-md-4 col-xl-4">
					<div class="widget-small danger coloured-icon">
						<i class="icon fas fa-money-bills fa-3x"></i>
						<div class="info">
							<h4>Total anual</h4>
							<p>
								<b>
									<?
									echo '$'.number_format($totalAnual,2);
									?>
								</b>
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-12">
							<div class="tile">
								<h3 class="tile-title">Mes</h3>
								<?
								if($hayTurnos>0){
									?>
									<canvas id="graficoDeBarras"></canvas>
									<?
								}else{
									?>
									<p>No hay turnos atendidos para el período seleccionado.</p>
									<?
								}
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="row">
						<div class="col-12">
							<div class="tile">
								<h3 class="tile-title"><?=ucwords($general['nombreObraSocial'])?></h3>
								<?
								if($hayTurnos>0){
								?>
								<canvas id="graficoDeBarrasTratamiento"></canvas>
								<?
								}else{
									?>
									<p>No hay turnos atendidos para el período seleccionado.</p>
									<?
								}
								?>
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
		<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
		<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
		
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
		
		<script type="text/javascript">
		
			var table = $('#tablaObrasSociales').DataTable({
				"paging":false
			});
			
			table.on( 'draw', function () {
				var sumaTotal=0;
				var sumaPorcentaje=0;
				$(".totalObraSocial").each(function(){
					sumaTotal+=parseFloat($(this).val());
				})
				$(".porcentajeObraSocial").each(function(){
					sumaPorcentaje+=parseFloat($(this).val());
				})
				$(".totalTabla").html(sumaTotal.toFixed(2));
				if(sumaPorcentaje>100){
					sumaPorcentaje=100;
				}
				$(".totalPorcentaje").html(sumaPorcentaje.toFixed(2));
				// $(".valorTurnos").html(cantidadTurnos);
			});
			
			<?
			if($hayTurnos>0){
			?>
		
		
			var ctx = document.getElementById('graficoDeBarras').getContext("2d");
			var chart = new Chart(ctx, {
				// The type of chart we want to create
				type: 'line',

				// The data for our dataset
				data: {
					labels: [<?
					$begin = new DateTime(date('Y-01-01'));
					$end = new DateTime(date('Y-12-31'));

					$interval = DateInterval::createFromDateString('1 month');
					$period = new DatePeriod($begin, $interval, $end);

					foreach ($period as $dt) {
						echo "'".DateController::monthDayToMes($dt->format("F"))."',";
					}
					?>],
					datasets: [
					<?
					$begin = new DateTime(date('Y-01-01'));
					$end = new DateTime(date('Y-12-31'));

					$interval = DateInterval::createFromDateString('1 month');
					$period = new DatePeriod($begin, $interval, $end);

					
						?>
						{
							label: "$",
							<?
							$color=Util::random_color();
							?>
							backgroundColor: '#17a2b8',
							borderColor: '#157686',
							data: [
								<?
								foreach ($period as $dt) {
								echo "'".$totalMensual[$dt->format("Y").$dt->format("m")]."',";
								}
								?>
							],
							fill:false,
							// lineTension: 0
						},
						<?
						//echo "'".$dt->format("d")."',";
					
					?>
				]},

				// Configuration options go here
				options: {
					responsive:true,
					legend:{display:false},
					"scales":{
						"xAxes":[{
							"ticks":{
								"beginAtZero":true
							}
						}]
					}
				}
			});
			
			//Grafico por tratamiento
			var ctx = document.getElementById('graficoDeBarrasTratamiento').getContext("2d");
			var chart = new Chart(ctx, {
				// The type of chart we want to create
				type: 'horizontalBar',

				// The data for our dataset
				data: {
					labels: [<?
					db_query(99,"select nombre,idTratamiento from tratamientos where estado='A' order by nombre");
					for($i99=0;$i99<$tot99;$i99++){
						$nres99=$res99->data_seek($i99);
						$row99=$res99->fetch_assoc();
						if($totalTratamiento[$row99['idTratamiento']]>0){
							echo "'".$row99['nombre']."',";
						}
					}
					?>],
					datasets: [{
						label: "$",
						backgroundColor: '#28a745',
						borderColor: '#28a745',
						data: [
							<?
							for($i99=0;$i99<$tot99;$i99++){
								$nres99=$res99->data_seek($i99);
								$row99=$res99->fetch_assoc();
								if($totalTratamiento[$row99['idTratamiento']]>0){
									echo "'".$totalTratamiento[$row99['idTratamiento']]."',";
								}
							}
							?>
						]
					}]
				},

				// Configuration options go here
				options: {
					responsive:true,
					legend:{display:false},
					"scales":{
						"xAxes":[{
							"ticks":{
								"beginAtZero":true
							}
						}]
					}
				}
			});
			
			<?
			}
			?>
			
			
			$("#aplicarRango").click(function(e){
				e.preventDefault();
				window.location.href='/reportes/ventas?fechaDesde='+$("#fechaDesde").val()+'&fechaHasta='+$("#fechaHasta").val();
			})
			
			Date.prototype.addDays = function(days) {
				var dat = new Date(this.valueOf());
				dat.setDate(dat.getDate() + days);
				return dat;
			}
			
			$('#fechaDesdeHasta').daterangepicker({
				"minYear": 2018,
				ranges: {
					'Hoy': [moment(), moment()],
					'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
					'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
					'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
					'Este mes': [moment().startOf('month'), moment().endOf('month')],
					'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
				},
				"locale": {
					"format": "DD/MM/YYYY",
					"separator": " - ",
					"applyLabel": "Aplicar",
					"cancelLabel": "Cancelar",
					"fromLabel": "Desde",
					"toLabel": "Hasta",
					"customRangeLabel": "Personalizado",
					"weekLabel": "S",
					"daysOfWeek": [
						"Do",
						"Lu",
						"Ma",
						"Mi",
						"Ju",
						"Vi",
						"Sa"
					],
					"monthNames": [
						"Enero",
						"Febrero",
						"Marzo",
						"Abril",
						"Mayo",
						"Junio",
						"Julio",
						"Agosto",
						"Septiembre",
						"Octubre",
						"Noviembre",
						"Diciembre"
					],
					"firstDay": 1
				},
				"startDate": "<?=date('d/m/Y',strtotime($fechaDesde))?>",
				"endDate": "<?=date('d/m/Y',strtotime($fechaHasta))?>"
			}, function(start, end, label) {
				// console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
				window.location.href='/reportes/ventas?fechaDesde='+start.format('DD/MM/YYYY')+'&fechaHasta='+end.format('DD/MM/YYYY');
			});
		</script>
		
		<?
		// include(incPath.'/analytics.php');
		?>
	</body>
</html>