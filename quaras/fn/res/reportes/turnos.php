<?
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fn.php');
AuthController::checkLogin();
AuthController::isAdmin();

if(@$_GET['fechaDesde']==''){
	$fechaDesde=date('Y-m-01');
}else{
	$fechaDesde=date('Y-m-d',strtotime(str_replace('/','-',$_GET['fechaDesde'])));
}
if(@$_GET['fechaHasta']==''){
	$fechaHasta=date('Y-m-t',strtotime(date('Y-m-d')));
}else{
	$fechaHasta=date('Y-m-d',strtotime(str_replace('/','-',$_GET['fechaHasta'])));
}

$atendidos=Turno::turnosPorFecha($fechaDesde, $fechaHasta, 1);
$ausentes=Turno::turnosPorFecha($fechaDesde, $fechaHasta, 2);
$cancelados=Turno::turnosPorFecha($fechaDesde, $fechaHasta, 3);
$atendidosFiltrado=Turno::turnosAtendidos($fechaDesde, $fechaHasta);

$cantidadAtendidos=$atendidosFiltrado['profesionales'];
$cantidadAtendidosTratamiento=$atendidosFiltrado['tratamientos'];

$profesionales=ProfesionalController::getProfesionales();
/******************************/
?>
<!DOCTYPE html>
<html lang="es">
	<head>
		<?
		$seccion='Reportes';
		$subseccion=ucwords($general['nombreTurnos']);
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
					<h1><i class="fa fa-bar-chart"></i> <?=ucwords($general['nombreTurnos'])?></h1>
					<p>Utilice este módulo para ver de un rápido vistazo la información estadística del sistema. Los datos que se muestran corresponden al período seleccionado.</p>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-6 col-xl-12">
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
							<?/*<div class="row">							
								<div class="col-md-4">
									<input type="text" id="fechaDesde" class="form-control" value="<?=date("d/m/Y",strtotime($fechaDesde))?>">
								</div>
								<div class="col-md-4">
									<input type="text" id="fechaHasta" class="form-control" value="<?=date("d/m/Y",strtotime($fechaHasta))?>">
								</div>
								<div class="col-md-4">
									<button type="button" id="aplicarRango" class="btn btn-primary">Aplicar</button>
								</div>
							</div>*/?>
						</div>
					</div>
				</div>
						<div class="col-lg-6 col-xl-4">
							<div class="widget-small success coloured-icon">
								<i class="icon fa fa-check fa-3x"></i>
								<div class="success">
									<h4>Realizados</h4>
									<p>
										<b>
											<?
											echo $atendidos['suma'];
											?>
										</b>
									</p>
								</div>
							</div>
						</div>
						<div class="col-lg-6 col-xl-4">
							<div class="widget-small warning coloured-icon">
								<i class="icon fa fa-user-times fa-3x"></i>
								<div class="info">
									<h4>Ausentes</h4>
									<p>
										<b>
											<?
											echo $ausentes['suma'];
											?>
										</b>
									</p>
								</div>
							</div>
						</div>
						<div class="col-lg-6 col-xl-4">
							<div class="widget-small danger coloured-icon">
								<i class="icon fa fa-ban fa-3x"></i>
								<div class="info">
									<h4>Cancelados</h4>
									<p>
										<b>
											<?
											echo $cancelados['suma'];
											?>
										</b>
									</p>
								</div>
							</div>
						</div>
						<?/*<div class="col-xl-3">
							<div class="widget-small primary coloured-icon">
								<i class="icon fa fa-check fa-3x"></i>
								<div class="info">
									<h4>O. terminadas</h4>
									<p>
										<b>
											<?
											db_query(92,"select t.idOrden from turnos t, ordenes o where date(t.fechaInicio)>='".$fechaDesde."' and date(t.fechaInicio)<='".$fechaHasta."' and t.idOrden=o.idOrden and o.estado=1 group by t.idOrden");
											echo $tot92;
											?>
										</b>
									</p>
								</div>
							</div>
						</div>*/?>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3">
					<div class="row">
						<div class="col-md-12">
							<div class="tile">
								<h3 class="tile-title">Realizados por <?=($general['nombreProfesional'])?></h3>
								<?
								if($atendidos['suma']>0){
								?>
								<canvas id="graficoPorProfesional"></canvas>
								<?
								}else{
									?>
									<p>No hay <?=($general['nombreTurnos'])?> realizados para el período seleccionado.</p>
									<?
								}
								?>
							</div>
						</div>
						<div class="col-md-12">
							<div class="tile">
								<h3 class="tile-title">Realizados por <?=($general['nombreObraSocial'])?></h3>
								<?
								if($atendidos['suma']>0){
								?>
								<canvas id="graficoDeBarrasOS"></canvas>
								<?
								}else{
									?>
									<p>No hay <?=($general['nombreTurnos'])?> realizados para el período seleccionado.</p>
									<?
								}
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-9">
					<div class="row">
						<div class="col-12">
							<div class="tile">
								<h3 class="tile-title">Realizados por día</h3>
								<?
								if($atendidos['suma']>0){
								?>
								<canvas id="graficoDeBarras"></canvas>
								<?
								}else{
									?>
									<p>No hay <?=($general['nombreTurnos'])?> realizados para el período seleccionado.</p>
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
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
		
		<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
		<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
		<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
		
		<script type="text/javascript">
			<?
			if($atendidos['suma']>0){
			?>
			var ctx = document.getElementById('graficoDeBarras').getContext("2d");
			var chart = new Chart(ctx, {
				// The type of chart we want to create
				type: 'bar',

				// The data for our dataset
				data: {
					labels: [<?
					$begin = new DateTime($fechaDesde);
					$end = new DateTime($fechaHasta);

					$interval = DateInterval::createFromDateString('1 day');
					$period = new DatePeriod($begin, $interval, $end);

					foreach ($period as $dt) {
						echo "'".$dt->format("d/m/Y")."',";
					}
					?>],
					datasets: [{
						label: "Realizados",
						backgroundColor: '#28a745',
						borderColor: '#28a745',
						data: [
							<?
							foreach ($period as $dt) {
								if(@$atendidos['fechas'][$dt->format("Y-m-d")]>0){
									echo $atendidos['fechas'][$dt->format("Y-m-d")].',';
								}else{
									echo '0,';
								}
							}
							?>
						],
					},{
						label: "Ausentes",
						backgroundColor: '#ffc107',
						borderColor: '#ffc107',
						data: [
							<?
							foreach ($period as $dt) {
								if(@$ausentes['fechas'][$dt->format("Y-m-d")]>0){
									echo $ausentes['fechas'][$dt->format("Y-m-d")].',';
								}else{
									echo '0,';
								}
							}
							?>
						],
					},{
						label: "Cancelados",
						backgroundColor: '#dc3545',
						borderColor: '#dc3545',
						data: [
							<?
							foreach ($period as $dt) {
								if(@$cancelados['fechas'][$dt->format("Y-m-d")]>0){
									echo $cancelados['fechas'][$dt->format("Y-m-d")].',';
								}else{
									echo '0,';
								}
							}
							?>
						],
					}]
				},

				// Configuration options go here
				options: {
					tooltips: {
						mode: 'index',
						intersect: false
					},
					responsive: true,
					scales: {
						xAxes: [{
							stacked: true,
						}],
						yAxes: [{
							stacked: true
						}]
					}}
			});
			
			//Grafico por profesional
			var ctx = document.getElementById('graficoPorProfesional').getContext("2d");
			var chart = new Chart(ctx, {
				// The type of chart we want to create
				type: 'pie',

				// The data for our dataset
				data: {
					labels: [<?
					db_query(99,"select nombre,color,idProfesional from profesionales order by nombre");
					for($i99=0;$i99<$tot99;$i99++){
						$nres99=$res99->data_seek($i99);
						$row99=$res99->fetch_assoc();
						if(@$cantidadAtendidos[$row99['idProfesional']]){
							echo "'".$row99['nombre']."',";
						}
					}
					?>],
					datasets: [{
						data: [
							<?
							for($i99=0;$i99<$tot99;$i99++){
								$nres99=$res99->data_seek($i99);
								$row99=$res99->fetch_assoc();
								if(@$cantidadAtendidos[$row99['idProfesional']]){
									echo @$cantidadAtendidos[$row99['idProfesional']].",";
								}
							}
							?>
						],
						backgroundColor: [
							<?
							for($i99=0;$i99<$tot99;$i99++){
								$nres99=$res99->data_seek($i99);
								$row99=$res99->fetch_assoc();
								if(@$cantidadAtendidos[$row99['idProfesional']]){
									echo "'#".$row99['color']."',";
								}
							}
							?>
						],
					}]
				},

				// Configuration options go here
				options: {
					responsive:true,
					/*legend:{display:false},
					"scales":{
						"xAxes":[{
							"ticks":{
								"beginAtZero":true
							}
						}]
					}*/
				}
			});
			
			
			
			
			//Grafico de barras por obra social
			var ctx = document.getElementById('graficoDeBarrasOS').getContext("2d");
			var chart = new Chart(ctx, {
				// The type of chart we want to create
				type: 'bar',

				// The data for our dataset
				data: {
					labels: [<?
					db_query(99,"select t.nombre, t.idTratamiento from tratamientos t where t.estado='A' order by t.nombre");
					// die("Tot: ".$tot10);
					for($i99=0;$i99<$tot99;$i99++){
						$nres99=$res99->data_seek($i99);
						$row99=$res99->fetch_assoc();
						if(@$cantidadAtendidosTratamiento[$row99['idTratamiento']]>0){
							echo "'".$row99['nombre']."',";
						}
					}
					?>],
					datasets: [{
						data: [
							<?
							for($i99=0;$i99<$tot99;$i99++){
								$nres99=$res99->data_seek($i99);
								$row99=$res99->fetch_assoc();
								if(@$cantidadAtendidosTratamiento[$row99['idTratamiento']]>0){
									echo $cantidadAtendidosTratamiento[$row99['idTratamiento']].",";
								}
							}
							?>
						],
						backgroundColor: [
							<?
							for($i99=0;$i99<$tot99;$i99++){
								echo "'#".Util::random_color()."',";
							}
							?>
						],
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
				window.location.href='/reportes/turnos?fechaDesde='+$("#fechaDesde").val()+'&fechaHasta='+$("#fechaHasta").val();
			})
			
			Date.prototype.addDays = function(days) {
				var dat = new Date(this.valueOf());
				dat.setDate(dat.getDate() + days);
				return dat;
			}
			
			/*$('#fechaDesde').datepicker({
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
			});*/
			
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
				window.location.href='/reportes/turnos?fechaDesde='+start.format('DD/MM/YYYY')+'&fechaHasta='+end.format('DD/MM/YYYY');
			});
		</script>
		
		<?
		// include(incPath.'/analytics.php');
		?>
	</body>
</html>