<?
function getMedioDePago($seccion, $subseccion){
	global $general;
	/* if(!AuthController::isAdmin()) return; */

	$html= '<li>
		<a class="app-menu__item';
	if($seccion=='MediosDePago'){
		$html.= ' active';
	}
	$html.='" href="/mediosDePago/">
		'.$general["iconoMedioDePago"].'
			<span class="app-menu__label ml-2">
				Medios de pago
			</span>
		</a>
	</li>';

	return $html;
}
function getProductos($seccion, $subseccion){
	global $general;
	if(!AuthController::isAdmin()) return;

	$html= '<li>
		<a class="app-menu__item';
	if($seccion=='Productos'){
		$html.= ' active';
	}
	$html.='" href="/productos/">
		'.$general["iconoProducto"].'
			<span class="app-menu__label ml-2">Productos</span>
		</a>
	</li>';

	return $html;
}
function getFacturacion($seccion, $subseccion){
	global $general;
	/* if(!AuthController::isAdmin()) return; */
	
	$html= '<li>
		<a class="app-menu__item';
	if($seccion=='Facturacion'){
		$html.= ' active';
	}
	$html.='" href="/facturacion/">
			'.$general["iconoFacturacion"].'
			<span class="app-menu__label">Facturación</span>
		</a>
	</li>';

	return $html;
}
function getVentas($seccion, $subseccion){
	if(!AuthController::isAdmin()) return;

	$html= '<li>
		<a class="app-menu__item';
	if($seccion=='Ventas'){
		$html.= ' active';
	}
	$html.='" href="/ventas/">
		<i class="fa-solid fa-cash-register"></i>
			<span class="app-menu__label ml-2">Ventas</span>
		</a>
	</li>';

	return $html;
}
function getReportes($seccion, $subseccion){
	global $general;

	
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
		<ul class="treeview-menu">';

			$html .= '<li>
				<a class="treeview-item';
				if(($seccion=='Reportes')&&($subseccion==ucwords($general['nombreTurnos']))){
					$html.= ' active';
				}
				$html.='" href="/reportes/turnos">
					<i class="icon fa fa-circle-o"></i> '.ucwords($general['nombreTurnos']).'
				</a>
			</li>';

			if(AuthController::isAdmin()){
				$html .= '
					<a class="treeview-item';
					if(($seccion=='Reportes')&&($subseccion=="Productos")){
						$html.= ' active';
					}
					$html.='" href="/reportes/productos">
						<i class="icon fa fa-circle-o"></i> Productos
					</a>
				</li>';
			}
			
			if($general['plan'] > 2 && AuthController::isAdmin()){
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
					if(($seccion=='Reportes')&&($subseccion=='MercadoPago')){
						$html.= ' active';
					}
					$html.='" href="/reportes/mercadoPago">
						<i class="icon fa fa-circle-o"></i> Señas
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

?>


<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
	<aside class="app-sidebar">
		<div class="app-sidebar__user">
			<div>
				<p class="app-sidebar__user-name"><?=$_SESSION['usuario']['nombre']?></p>
				<?
				if($_SESSION['usuario']['puesto']){
					?>
					<p class="app-sidebar__user-designation"><?=$_SESSION['usuario']['puesto']?></p>
					<?
				}
				?>
			</div>
		</div>
		<ul class="app-menu">

			<?=Menu::getEscritorio($seccion, $subseccion)?>

			<?=Menu::getTurnos($seccion, $subseccion)?>
			
			<?=Menu::getPacientes($seccion, $subseccion)?>
			
			<?=Menu::getCategorias($seccion, $subseccion)?>
			
			<!-- < ?=Menu::getTratamientos($seccion, $subseccion)?> -->
			<!-- < ?=Menu::getProfesionales($seccion, $subseccion)?> -->

			<? if(AuthController::isAdmin()){ ?>
				<li>
					<a class="app-menu__item <?=$seccion==ucwords($general['nombreBoletos']) ? ' active' : '' ?>" href="/boletos/">
						<i class="fa-solid fa-ticket mr-2"></i><span class="app-menu__label"><?=ucfirst($general['nombreBoletos'])?></span>
					</a>
				</li>
				<li>
					<a class="app-menu__item <?=$seccion=='Promociones' ? ' active' : '' ?>" href="/promociones/">
						<i class="fa-solid fa-gift mr-2"></i><span class="app-menu__label">Promociones</span>
					</a>
				</li>
				<!-- <li>
					<a class="app-menu__item <?=$seccion=='Formularios' ? ' active' : '' ?>" href="/formularios/">
						<i class="fa-solid fa-signature mr-2"></i><span class="app-menu__label">Formularios</span>
					</a>
				</li> -->
			<? } ?>

			<?=getMedioDePago($seccion, $subseccion)?>
			
			<!-- < ?=getProductos($seccion, $subseccion)?> -->
			
			<!-- < ?=getVentas($seccion, $subseccion)?> -->
			
			<!-- < ?=getFacturacion($seccion, $subseccion)?> -->

			<?=getReportes($seccion, $subseccion)?>
			
			<li class="border-top"></li>
			
			<?=Menu::getAyuda($seccion, $subseccion)?>
			
		</ul>
	</aside>