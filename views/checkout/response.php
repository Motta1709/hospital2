<div class="response-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div id="loading" class="card-body p-5 text-center">
                        <div class="spinner-border text-primary mb-4" role="status"></div>
                        <h3>Validando tu transacción...</h3>
                        <p class="text-muted">Por favor espera un momento mientras confirmamos el pago con ePayco.</p>
                    </div>

                    <div id="transaction-detail" class="card-body p-5 d-none">
                        <div class="text-center mb-5">
                            <div id="status-icon" class="mb-4"></div>
                            <h2 id="status-text" class="fw-bold"></h2>
                            <p id="status-desc" class="text-muted"></p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-borderless bg-light rounded-3 p-3">
                                <tbody>
                                    <tr>
                                        <td class="text-muted">Referencia ePayco</td>
                                        <td class="text-end fw-bold" id="ref-epayco"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Referencia Factura</td>
                                        <td class="text-end fw-bold" id="ref-invoice"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Descripción</td>
                                        <td class="text-end" id="desc"></td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="text-muted h5 pt-3">Total Pagado</td>
                                        <td class="text-end h5 pt-3 fw-bold text-primary" id="amount"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-grid mt-5">
                            <a href="<?= APP_URL ?>?route=home" class="btn btn-primary py-3 rounded-pill">
                                Volver al Inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const ref_payco = urlParams.get('ref_payco');

        if (ref_payco) {
            fetch(`https://secure.epayco.co/validation/v1/reference/${ref_payco}`)
                .then(response => response.json())
                .then(res => {
                    const data = res.data;
                    
                    document.getElementById('loading').classList.add('d-none');
                    document.getElementById('transaction-detail').classList.remove('d-none');

                    // Llenar datos
                    document.getElementById('ref-epayco').textContent = data.x_ref_payco;
                    document.getElementById('ref-invoice').textContent = data.x_id_invoice;
                    document.getElementById('desc').textContent = data.x_description;
                    document.getElementById('amount').textContent = '$ ' + parseInt(data.x_amount).toLocaleString('es-CO');

                    const statusIcon = document.getElementById('status-icon');
                    const statusText = document.getElementById('status-text');
                    const statusDesc = document.getElementById('status-desc');

                    switch (data.x_cod_response) {
                        case 1: // Aceptada
                            statusIcon.innerHTML = '<i class="fas fa-check-circle fa-5x text-success"></i>';
                            statusText.textContent = "¡Pago Exitoso!";
                            statusDesc.textContent = "Tu transacción ha sido aprobada. En breve recibirás tus productos.";
                            // Limpiar carrito si el pago es exitoso (Simulado en frontend para demo)
                            break;
                        case 2: // Rechazada
                            statusIcon.innerHTML = '<i class="fas fa-times-circle fa-5x text-danger"></i>';
                            statusText.textContent = "Pago Rechazado";
                            statusDesc.textContent = "Lo sentimos, la transacción no pudo ser procesada.";
                            break;
                        case 3: // Pendiente
                            statusIcon.innerHTML = '<i class="fas fa-clock fa-5x text-warning"></i>';
                            statusText.textContent = "Pago Pendiente";
                            statusDesc.textContent = "Estamos esperando la confirmación de tu banco.";
                            break;
                        default:
                            statusIcon.innerHTML = '<i class="fas fa-exclamation-circle fa-5x text-muted"></i>';
                            statusText.textContent = data.x_response;
                            statusDesc.textContent = "Estado de transacción desconocido.";
                    }
                })
                .catch(err => {
                    console.error('Error al validar:', err);
                });
        }
    });
</script>

