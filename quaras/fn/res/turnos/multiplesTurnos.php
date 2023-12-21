<div class="contenedorTurnos d-none" id="contenedorTurnos">
    <div class="card" style="width: 18rem;">
        <div class="card-body">
            <div id="contenedorTurnosheader">
                <h6>Múltiples <?=($general['nombreTurnos'])?></h6>
                <h5 class="card-title">Nombre <?=($general['nombrePaciente'])?></h5>
                <h6 class="card-subtitle mb-2 text-muted">Orden</h6>
            </div>
            <div class="card-text my-3"></div>
            <a href="javascript:;" class="btn btn-secondary" onclick="cancelarMultiples()">Cancelar</a>
            <a href="javascript:;" class="btn btn-primary" onclick="agendarMultiples()">Agendar</a>
        </div>
    </div>
				
</div>