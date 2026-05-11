<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Pagar con Nequi — PharmaCRM</title>
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/customer.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}
body{margin:0;font-family:'Inter',sans-serif}

/* --- Page --- */
.nequi-page{min-height:100vh;background:linear-gradient(160deg,#0f0c29 0%,#1a1145 30%,#302b63 60%,#24243e 100%);padding:40px 20px;position:relative;overflow:hidden}
.nequi-page::before{content:'';position:absolute;top:-150px;right:-150px;width:400px;height:400px;background:radial-gradient(circle,rgba(218,55,145,.15),transparent 70%);border-radius:50%;pointer-events:none}
.nequi-page::after{content:'';position:absolute;bottom:-100px;left:-100px;width:350px;height:350px;background:radial-gradient(circle,rgba(118,75,162,.2),transparent 70%);border-radius:50%;pointer-events:none}

.nequi-container{max-width:1000px;margin:0 auto;position:relative;z-index:1}

/* --- Header --- */
.nequi-header{text-align:center;margin-bottom:36px}
.nequi-header h1{font-size:30px;font-weight:800;color:#fff;margin-bottom:8px;display:flex;align-items:center;justify-content:center;gap:12px}
.nequi-logo{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#da3791,#e04068);display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff;box-shadow:0 6px 20px rgba(218,55,145,.4)}
.nequi-header p{color:rgba(255,255,255,.55);font-size:14px}

/* --- Grid --- */
.nequi-grid{display:grid;grid-template-columns:1fr 420px;gap:28px;align-items:start}
@media(max-width:820px){.nequi-grid{grid-template-columns:1fr}}

/* --- Card Base --- */
.glass-card{background:rgba(255,255,255,.06);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:28px;box-shadow:0 8px 32px rgba(0,0,0,.25)}

/* --- Items List --- */
.items-card h2{font-size:17px;color:rgba(255,255,255,.85);margin-bottom:18px;display:flex;align-items:center;gap:8px}
.items-card h2 i{color:#da3791}
.item-row{display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid rgba(255,255,255,.06)}
.item-row:last-child{border-bottom:none}
.item-icon{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,rgba(218,55,145,.2),rgba(118,75,162,.25));display:flex;align-items:center;justify-content:center;color:#da3791;font-size:16px;flex-shrink:0}
.item-info{flex:1}
.item-info h4{font-size:13px;color:rgba(255,255,255,.9);margin:0 0 2px}
.item-info small{color:rgba(255,255,255,.4);font-size:11px}
.item-price{text-align:right;font-weight:700;color:#fff;font-size:14px}
.item-qty{color:rgba(255,255,255,.4);font-size:11px;text-align:right}

/* --- Payment Panel --- */
.payment-card{position:sticky;top:20px}
.payment-card h2{font-size:17px;color:rgba(255,255,255,.85);margin-bottom:20px;display:flex;align-items:center;gap:8px}
.payment-card h2 i{color:#e04068}

/* Nequi Badge */
.nequi-brand{display:flex;align-items:center;gap:12px;padding:16px;background:linear-gradient(135deg,rgba(218,55,145,.12),rgba(224,64,104,.08));border:1px solid rgba(218,55,145,.2);border-radius:14px;margin-bottom:20px}
.nequi-brand-icon{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#da3791,#e04068);display:flex;align-items:center;justify-content:center;font-size:24px;color:#fff;font-weight:800;box-shadow:0 4px 16px rgba(218,55,145,.35)}
.nequi-brand-text{flex:1}
.nequi-brand-text h3{font-size:15px;color:#fff;margin:0 0 2px}
.nequi-brand-text small{color:rgba(255,255,255,.5);font-size:11px}

/* Summary */
.summary-row{display:flex;justify-content:space-between;padding:9px 0;font-size:13px;color:rgba(255,255,255,.55)}
.summary-row span:last-child{color:rgba(255,255,255,.85);font-weight:600}
.summary-row.total{border-top:1px solid rgba(255,255,255,.1);padding-top:14px;margin-top:6px;font-size:20px}
.summary-row.total span:first-child{color:rgba(255,255,255,.7);font-weight:700}
.summary-row.total span:last-child{color:#fff;font-weight:800;background:linear-gradient(135deg,#da3791,#e04068);-webkit-background-clip:text;-webkit-text-fill-color:transparent}

/* Phone Input */
.phone-group{margin-top:22px}
.phone-group label{display:block;font-size:12px;color:rgba(255,255,255,.55);margin-bottom:8px;font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.phone-input-wrap{display:flex;align-items:center;gap:0;border-radius:14px;overflow:hidden;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);transition:border-color .3s}
.phone-input-wrap:focus-within{border-color:rgba(218,55,145,.5);box-shadow:0 0 0 3px rgba(218,55,145,.1)}
.phone-prefix{padding:14px 16px;background:rgba(255,255,255,.06);color:rgba(255,255,255,.5);font-size:14px;font-weight:600;border-right:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:6px;flex-shrink:0}
.phone-prefix img{width:20px;height:14px;border-radius:2px}
.phone-input{flex:1;padding:14px 16px;background:transparent;border:none;color:#fff;font-size:16px;font-weight:600;outline:none;letter-spacing:1px}
.phone-input::placeholder{color:rgba(255,255,255,.25);font-weight:400;letter-spacing:0}

/* Button */
.btn-nequi{width:100%;padding:16px;border:none;border-radius:14px;background:linear-gradient(135deg,#da3791,#e04068);color:#fff;font-size:16px;font-weight:700;cursor:pointer;margin-top:18px;display:flex;align-items:center;justify-content:center;gap:10px;transition:all .3s ease;box-shadow:0 6px 24px rgba(218,55,145,.35);position:relative;overflow:hidden}
.btn-nequi:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(218,55,145,.5)}
.btn-nequi:active{transform:translateY(0)}
.btn-nequi:disabled{opacity:.6;cursor:not-allowed;transform:none!important}
.btn-nequi::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.1),transparent);transition:left .6s}
.btn-nequi:hover::before{left:100%}

/* Secure Badge */
.secure-badge{display:flex;align-items:center;gap:8px;padding:14px;background:rgba(56,161,105,.08);border:1px solid rgba(56,161,105,.15);border-radius:12px;margin-top:16px;font-size:11px;color:rgba(56,161,105,.9)}
.secure-badge i{font-size:14px}

/* Back Link */
.btn-back{display:inline-flex;align-items:center;gap:6px;color:rgba(255,255,255,.4);text-decoration:none;font-size:12px;margin-top:16px;transition:color .2s}
.btn-back:hover{color:rgba(255,255,255,.7)}

/* --- Processing State --- */
.processing-overlay{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,12,41,.95);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(10px)}
.processing-overlay.active{display:flex}
.processing-box{text-align:center;max-width:440px;padding:40px}

/* Nequi Animation */
.nequi-pulse{width:100px;height:100px;border-radius:28px;background:linear-gradient(135deg,#da3791,#e04068);display:flex;align-items:center;justify-content:center;margin:0 auto 28px;position:relative;animation:nequiPulse 2s infinite;font-size:42px;color:#fff;font-weight:800;box-shadow:0 8px 32px rgba(218,55,145,.4)}
@keyframes nequiPulse{0%{box-shadow:0 0 0 0 rgba(218,55,145,.5)}50%{box-shadow:0 0 0 20px rgba(218,55,145,0)}100%{box-shadow:0 0 0 0 rgba(218,55,145,0)}}

.processing-box h2{color:#fff;font-size:22px;margin-bottom:10px;font-weight:700}
.processing-box p{color:rgba(255,255,255,.5);font-size:14px;margin-bottom:28px;line-height:1.6}

/* Steps */
.nequi-steps{display:flex;flex-direction:column;gap:12px;text-align:left;margin-bottom:28px}
.nequi-step{display:flex;align-items:center;gap:14px;padding:14px 18px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);border-radius:14px;transition:all .5s ease}
.nequi-step.active{background:rgba(218,55,145,.1);border-color:rgba(218,55,145,.25)}
.nequi-step.done{background:rgba(56,161,105,.1);border-color:rgba(56,161,105,.25)}
.step-num{width:32px;height:32px;border-radius:10px;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.4);font-size:13px;font-weight:700;flex-shrink:0;transition:all .5s}
.nequi-step.active .step-num{background:linear-gradient(135deg,#da3791,#e04068);color:#fff}
.nequi-step.done .step-num{background:linear-gradient(135deg,#38a169,#48bb78);color:#fff}
.step-text{color:rgba(255,255,255,.5);font-size:13px;transition:color .5s}
.nequi-step.active .step-text{color:rgba(255,255,255,.9)}
.nequi-step.done .step-text{color:rgba(56,161,105,.9)}

/* Timer */
.timer-text{color:rgba(255,255,255,.35);font-size:12px;margin-top:12px}

/* Error */
.error-msg{background:rgba(229,62,62,.1);border:1px solid rgba(229,62,62,.2);border-radius:12px;padding:12px 16px;color:#fc8181;font-size:13px;margin-top:14px;display:none}
.error-msg.show{display:block}

/* --- Result States --- */
.result-icon{width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:36px}
.result-icon.success{background:rgba(56,161,105,.15);color:#48bb78}
.result-icon.failed{background:rgba(229,62,62,.15);color:#fc8181}
</style>
</head>
<body>
<div class="nequi-page">
<div class="nequi-container">
    <div class="nequi-header">
        <h1>
            <span class="nequi-logo"><i class="fas fa-mobile-alt"></i></span>
            Pagar con Nequi
        </h1>
        <p>Paga de forma rapida y segura desde tu billetera digital Nequi</p>
    </div>

    <div class="nequi-grid">
        <!-- Productos -->
        <div class="glass-card items-card">
            <h2><i class="fas fa-shopping-bag"></i> Tu Pedido (<?= count($data['items']) ?> productos)</h2>
            <?php foreach ($data['items'] as $item): ?>
            <div class="item-row">
                <div class="item-icon"><i class="fas fa-pills"></i></div>
                <div class="item-info">
                    <h4><?= htmlspecialchars($item['name']) ?></h4>
                    <small><?= htmlspecialchars($item['generic_name'] ?? '') ?> — <?= htmlspecialchars($item['presentation'] ?? '') ?></small>
                </div>
                <div>
                    <div class="item-price"><?= formatCurrency($item['subtotal']) ?></div>
                    <div class="item-qty">x<?= $item['qty'] ?> und</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Panel de Pago -->
        <div class="glass-card payment-card">
            <h2><i class="fas fa-wallet"></i> Resumen de Pago</h2>

            <div class="nequi-brand">
                <div class="nequi-brand-icon">N</div>
                <div class="nequi-brand-text">
                    <h3>Nequi</h3>
                    <small>Billetera digital — Pago instantaneo</small>
                </div>
            </div>

            <div class="summary-row">
                <span>Precio total (IVA incl.)</span>
                <span><?= formatCurrency($data['total']) ?></span>
            </div>
            <div class="summary-row" style="font-size:12px;">
                <span style="color:rgba(255,255,255,.4)">Base imponible</span>
                <span style="color:rgba(255,255,255,.4)"><?= formatCurrency($data['subtotal']) ?></span>
            </div>
            <div class="summary-row" style="font-size:12px;">
                <span style="color:rgba(255,255,255,.4)">IVA 19% (incluido)</span>
                <span style="color:rgba(255,255,255,.4)"><?= formatCurrency($data['iva']) ?></span>
            </div>
            <div class="summary-row total">
                <span>Total a pagar</span>
                <span><?= formatCurrency($data['total']) ?></span>
            </div>

            <div class="phone-group">
                <label><i class="fas fa-phone-alt"></i> Celular registrado en Nequi</label>
                <div class="phone-input-wrap">
                    <div class="phone-prefix">
                        <img src="https://flagcdn.com/w40/co.png" alt="CO">
                        +57
                    </div>
                    <input type="tel" class="phone-input" id="nequiPhone" 
                           placeholder="3XX XXX XXXX" maxlength="10" 
                           value="<?= htmlspecialchars($data['client']['phone'] ?? '') ?>"
                           autocomplete="tel">
                </div>
            </div>

            <div class="error-msg" id="errorMsg"></div>

            <button class="btn-nequi" id="btnPayNequi" onclick="initNequiPayment()">
                <i class="fas fa-mobile-alt"></i>
                Pagar <?= formatCurrency($data['total']) ?> con Nequi
            </button>

            <div class="secure-badge">
                <i class="fas fa-lock"></i>
                <span>Pago seguro procesado por ePayco. Transaccion cifrada con SSL de 256 bits.</span>
            </div>

            <a href="<?= APP_URL ?>/?route=checkout" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver al checkout con tarjeta
            </a>
        </div>
    </div>
</div>
</div>

<!-- Processing Overlay -->
<div class="processing-overlay" id="processingOverlay">
<div class="processing-box">
    <div class="nequi-pulse">N</div>
    <h2 id="processingTitle">Esperando aprobacion en Nequi</h2>
    <p id="processingDesc">Abre tu app Nequi y aprueba la solicitud de pago.<br>No cierres esta pagina.</p>

    <div class="nequi-steps">
        <div class="nequi-step done" id="step1">
            <div class="step-num"><i class="fas fa-check"></i></div>
            <div class="step-text">Solicitud de pago enviada</div>
        </div>
        <div class="nequi-step active" id="step2">
            <div class="step-num">2</div>
            <div class="step-text">Esperando aprobacion en tu app Nequi...</div>
        </div>
        <div class="nequi-step" id="step3">
            <div class="step-num">3</div>
            <div class="step-text">Confirmando el pago</div>
        </div>
    </div>

    <div class="timer-text" id="timerText">Tiempo restante: 5:00</div>
</div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
const TOTAL = <?= $data['total'] ?>;
const INVOICE_REF = '<?= $data['invoiceRef'] ?>';

let pollingInterval = null;
let countdownInterval = null;
let timeRemaining = 300; // 5 minutos

// Formatear input del telefono
document.getElementById('nequiPhone').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').substring(0, 10);
});

function showError(msg) {
    const el = document.getElementById('errorMsg');
    el.textContent = msg;
    el.classList.add('show');
    setTimeout(() => el.classList.remove('show'), 6000);
}

async function initNequiPayment() {
    const phone = document.getElementById('nequiPhone').value.trim();

    // Validar telefono
    if (!/^3\d{9}$/.test(phone)) {
        showError('Ingresa un numero de celular Nequi valido (10 digitos, empieza por 3)');
        return;
    }

    const btn = document.getElementById('btnPayNequi');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

    try {
        const response = await fetch(APP_URL + '/?route=nequi&action=create', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({phone: phone})
        });

        const result = await response.json();

        if (result.success) {
            // Mostrar overlay de procesamiento
            document.getElementById('processingOverlay').classList.add('active');
            startPolling(result.data.ref_payco);
            startCountdown();
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-mobile-alt"></i> Pagar ' + formatCOP(TOTAL) + ' con Nequi';

            if (result.fallback) {
                showError(result.message + ' Puedes intentar con tarjeta desde el checkout estandar.');
            } else {
                showError(result.message);
            }
        }
    } catch (err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-mobile-alt"></i> Pagar ' + formatCOP(TOTAL) + ' con Nequi';
        showError('Error de conexion. Verifica tu internet e intenta de nuevo.');
    }
}

function startPolling(refPayco) {
    // Consultar cada 5 segundos
    pollingInterval = setInterval(async () => {
        try {
            const response = await fetch(APP_URL + '/?route=nequi&action=status&ref_payco=' + refPayco);
            const result = await response.json();

            if (result.success) {
                if (result.status === 'approved') {
                    clearInterval(pollingInterval);
                    clearInterval(countdownInterval);
                    showApproved(result.data);
                } else if (result.status === 'rejected' || result.status === 'failed') {
                    clearInterval(pollingInterval);
                    clearInterval(countdownInterval);
                    showRejected(result.data);
                }
                // Si es 'pending', seguir esperando
            }
        } catch (err) {
            // Ignorar errores de red, seguir intentando
        }
    }, 5000);
}

function startCountdown() {
    timeRemaining = 300;
    updateTimerDisplay();

    countdownInterval = setInterval(() => {
        timeRemaining--;
        updateTimerDisplay();

        if (timeRemaining <= 0) {
            clearInterval(pollingInterval);
            clearInterval(countdownInterval);
            showTimeout();
        }
    }, 1000);
}

function updateTimerDisplay() {
    const mins = Math.floor(timeRemaining / 60);
    const secs = timeRemaining % 60;
    document.getElementById('timerText').textContent = 
        'Tiempo restante: ' + mins + ':' + secs.toString().padStart(2, '0');
}

function showApproved(data) {
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');

    step2.classList.remove('active');
    step2.classList.add('done');
    step2.querySelector('.step-num').innerHTML = '<i class="fas fa-check"></i>';
    step2.querySelector('.step-text').textContent = 'Pago aprobado en Nequi';

    step3.classList.add('done');
    step3.querySelector('.step-num').innerHTML = '<i class="fas fa-check"></i>';
    step3.querySelector('.step-text').textContent = 'Pago confirmado exitosamente';

    document.getElementById('processingTitle').textContent = '\u00a1Pago Exitoso!';
    document.getElementById('processingDesc').innerHTML = 
        'Tu pago de <strong>' + formatCOP(data.amount || TOTAL) + '</strong> ha sido procesado correctamente.<br>' +
        'Referencia: <strong>' + (data.ref_payco || '') + '</strong>';
    document.getElementById('timerText').innerHTML = 
        '<a href="' + APP_URL + '/?route=customer-dashboard" style="color:#da3791;text-decoration:none;font-weight:600;">' +
        '<i class="fas fa-chart-pie"></i> Ir a Mi Dashboard</a>';

    // Redirigir despues de 4 segundos
    setTimeout(() => {
        window.location.href = APP_URL + '/?route=nequi&action=response&ref_payco=' + (data.ref_payco || '');
    }, 4000);
}

function showRejected(data) {
    const step2 = document.getElementById('step2');
    step2.classList.remove('active');
    step2.querySelector('.step-num').innerHTML = '<i class="fas fa-times"></i>';
    step2.querySelector('.step-text').textContent = 'Pago rechazado';
    step2.style.borderColor = 'rgba(229,62,62,.3)';
    step2.style.background = 'rgba(229,62,62,.1)';
    step2.querySelector('.step-num').style.background = 'linear-gradient(135deg,#e53e3e,#fc8181)';

    document.getElementById('processingTitle').textContent = 'Pago No Completado';
    document.getElementById('processingDesc').textContent = 'El pago fue rechazado o cancelado. Puedes intentarlo nuevamente.';
    document.getElementById('timerText').innerHTML = 
        '<a href="' + APP_URL + '/?route=nequi" style="color:#da3791;text-decoration:none;font-weight:600;">' +
        '<i class="fas fa-redo"></i> Intentar de nuevo</a>';

    document.querySelector('.nequi-pulse').style.background = 'linear-gradient(135deg,#e53e3e,#fc8181)';
    document.querySelector('.nequi-pulse').innerHTML = '<i class="fas fa-times"></i>';
}

function showTimeout() {
    document.getElementById('processingTitle').textContent = 'Tiempo Agotado';
    document.getElementById('processingDesc').textContent = 
        'No se recibio confirmacion de Nequi. Si aprobaste el pago, se procesara automaticamente.';
    document.getElementById('timerText').innerHTML = 
        '<a href="' + APP_URL + '/?route=customer-dashboard" style="color:#da3791;text-decoration:none;font-weight:600;margin-right:16px">' +
        '<i class="fas fa-chart-pie"></i> Ir al Dashboard</a>' +
        '<a href="' + APP_URL + '/?route=nequi" style="color:rgba(255,255,255,.5);text-decoration:none;font-weight:600">' +
        '<i class="fas fa-redo"></i> Reintentar</a>';
}

function formatCOP(amount) {
    return '$ ' + parseInt(amount).toLocaleString('es-CO');
}
</script>
</body>
</html>
