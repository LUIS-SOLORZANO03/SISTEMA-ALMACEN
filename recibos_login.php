<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧾 Módulo de Recibos | Data Online Perú</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

        body {
            background: #f5f6fa;
            font-family: 'Poppins', sans-serif;
            color: #222;
            padding: 30px;
        }

        .recibo-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #f26522;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.07);
            padding: 20px;
        }

        .recibo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f26522;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .recibo-header img {
            width: 120px;
        }

        .recibo-header .info-empresa {
            flex-grow: 1;
            text-align: center;
        }

        .recibo-header h3 {
            color: #f26522;
            font-weight: 700;
            font-size: 22px;
            margin: 0;
        }

        .recibo-body {
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr;
            gap: 10px;
            font-size: 13px;
        }

        .campo {
            display: flex;
            flex-direction: column;
            margin-bottom: 5px;
        }

        .campo label {
            font-weight: 600;
            font-size: 12px;
            color: #333;
        }

        .campo input,
        .campo select {
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 5px 8px;
            font-size: 12px;
            background: #fafafa;
        }

        /* 🎫 Ticketera térmica (58mm o 80mm) */
        .ticketera {
            width: 80mm;
            font-size: 11px;
            line-height: 1.3;
            margin: 0 auto;
            padding: 5px;
            border: none;
            background: #fff;
        }

        .ticketera .recibo-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        .ticketera .recibo-header img {
            width: 80px !important;
            margin-bottom: 5px;
        }

        .ticketera .info-empresa h3 {
            font-size: 14px;
            margin: 2px 0;
        }

        .ticketera .recibo-body {
            grid-template-columns: 1fr !important;
            font-size: 11px;
        }

        .ticketera .campo {
            margin-bottom: 3px;
        }

        .ticketera .total-box {
            border: 1px dashed #000;
            margin-top: 5px;
            padding: 4px;
        }

        .ticketera .total-box h4 {
            font-size: 13px;
            margin: 0;
        }

        .ticketera .info-final {
            border: none;
            font-size: 10px;
            margin-top: 5px;
        }

        .ticketera .agradecimiento {
            border-top: 1px dashed #000;
            margin-top: 4px;
            font-size: 10px;
        }

        @media print {
            body {
                background: #fff !important;
                margin: 0;
                padding: 0;
            }

            .btn-acciones,
            .swal2-container {
                display: none !important;
            }

            /* 📏 Ticketera: ajusta el rollo */
            .ticketera {
                width: 80mm !important;
            }

            @page {
                size: 80mm auto;
                margin: 2mm;
            }
        }

        .total-box {
            border: 2px solid #f26522;
            border-radius: 10px;
            padding: 10px;
            margin-top: 10px;
            text-align: center;
        }

        .total-box h4 {
            font-size: 15px;
            font-weight: 700;
            margin: 0;
        }

        /* 🔸 Ajuste visual del recuadro naranja */
        .info-final {
            border: 2px solid #f26522;
            border-radius: 10px;
            padding: 10px 12px;
            /* antes 15px */
            margin-top: 8px;
            /* antes 15px */
            background: linear-gradient(to bottom, #fff, #fff8f3);
            font-size: 12.5px;
            /* un pelín más pequeño */
        }

        .info-final .contenido {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 8px;
        }

        .formas h5 {
            font-size: 13px;
            margin-bottom: 4px;
        }

        .formas {
            flex: 1;
            min-width: 250px;
            text-align: left;
        }



        .formas p {
            margin: 3px 0;
        }

        .contacto {
            flex: 1;
            min-width: 250px;
            text-align: center;
            border-left: 1.5px dashed #f26522;
            padding-left: 15px;
        }

        .contacto img {
            width: 90px;
            border: 1.5px solid #f26522;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .contacto .whatsapp {
            font-size: 13px;
            color: #25d366;
            font-weight: 600;
        }

        .agradecimiento {
            border-top: 1.5px solid #f26522;
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            font-size: 12px;
            color: #555;
        }

        .btn-acciones {
            text-align: center;
            margin-top: 12px;
        }

        .btn-acciones button {
            margin: 5px;
            border-radius: 25px;
            padding: 8px 22px;
            font-weight: 600;
            background: #f26522;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-acciones button:hover {
            background: #d9540f;
        }

        /* 🖨️ IMPRESIÓN: RECIBO MEDIA HOJA, LIGERAMENTE MÁS ABAJO */
        @media print {
            body {
                background: #fff !important;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: flex-start;
                height: 297mm;
                width: 210mm;
            }

            .btn-acciones,
            .swal2-container {
                display: none !important;
            }

            .recibo-container {
                width: 190mm;
                height: 148.5mm;
                border: 1px solid #000 !important;
                box-shadow: none;
                border-radius: 0;
                margin: 0 auto;
                position: absolute;
                top: -5mm;
                /* 🔽 bajado un poco (antes -10mm) */
                left: 0;
                right: 0;
                padding: 12px 18px;
                transform: none;
                page-break-after: always;
            }

            @page {
                size: A4 portrait;
                margin: 5mm 10mm;
            }
        }
    </style>
</head>

<body>
    <div class="recibo-container" id="recibo">
        <div class="recibo-header">
            <img src="logo.png" alt="Logo">
            <div class="info-empresa">
                <h3>Data Online Perú</h3>
                <p>Av. Los Laureles 126 – Barranca</p>
                <p>📞 944558294 / 944558594</p>
            </div>
            <img src="QR.png" alt="QR Pago">
        </div>

        <div class="recibo-body">
            <div>
                <div class="campo">
                    <label>Fecha Emisión</label>
                    <input type="date" id="fecha_emision">
                </div>
                <div class="campo">
                    <label>Fecha Vencimiento</label>
                    <input type="date" id="fecha_vencimiento">
                </div>
                <div class="campo">
                    <label>Recibo N°</label>
                    <input type="text" id="num_recibo" readonly>
                </div>
            </div>

            <div>
                <div class="campo">
                    <label>Nombre</label>
                    <input type="text" id="nombre">
                </div>
                <div class="campo">
                    <label>DNI / RUC</label>
                    <input type="text" id="dni">
                </div>
                <div class="campo">
                    <label>Dirección</label>
                    <input type="text" id="direccion">
                </div>
            </div>

            <div>
                <div class="campo">
                    <label>Celular</label>
                    <input type="text" id="cel">
                </div>
                <div class="campo">
                    <label>Plan</label>
                    <select id="plan" onchange="actualizarOpcionesPlan()">
                        <option value="">Seleccionar</option>
                        <option value="tv">TV</option>
                        <option value="internet">Internet</option>
                        <option value="duo">Dúo (TV + Internet)</option>
                    </select>
                </div>
                <div class="campo" id="campoMontoPlan" style="display:none;">
                    <label>Monto del Plan</label>
                    <select id="monto_plan" onchange="actualizarTotal()"></select>
                </div>
            </div>
        </div>

        <div class="total-box">
            <h4>TOTAL A PAGAR: S/. <span id="total_monto">0.00</span></h4>
        </div>

        <!-- 🔸 RECUADRO FINAL ORDENADO -->
        <div class="info-final">
            <div class="contenido">
                <div class="formas">
                    <h5>Formas de Pago</h5>
                    <p>💳 <b>BCP:</b> 235-2226808-0-45</p>
                    <p>🏦 <b>CCI BCP:</b> 00223500222680804508</p>
                    <p>🏦 <b>INTERBANK:</b> 523-3006035172</p>
                </div>
                <div class="contacto">
                    <p>📎 Envía tu comprobante al WhatsApp:</p>
                    <p class="whatsapp"><i class="fab fa-whatsapp"></i> 944558294 / 944558594</p>
                    <p><b>⚠️ Corte 5 días después de vencimiento.</b></p>
                    <p>🌐 <b>www.dataonlineperu.com</b></p>
                </div>
            </div>
            <div class="agradecimiento">
                <p>Gracias por confiar en <b>Data Online Perú</b></p>
            </div>
        </div>

        <div class="btn-acciones">
            <button class="btn btn-success" onclick="generarRecibo()"><i class="fas fa-check"></i> Generar Recibo</button>
            <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
            <button class="btn btn-danger" onclick="limpiarCampos()"><i class="fas fa-broom"></i> Limpiar</button>
        </div>
    </div>

    <script>
        let contadorRecibo = 1;
        document.addEventListener('DOMContentLoaded', () => {
            const hoy = new Date();
            const fechaHoy = hoy.toISOString().split('T')[0];
            document.getElementById('fecha_emision').value = fechaHoy;
            document.getElementById('fecha_vencimiento').value = fechaHoy;
            actualizarNumeroRecibo();
            Swal.fire({
                icon: 'info',
                title: 'Bienvenido',
                text: 'Completa los datos para generar un recibo.',
                timer: 2000,
                showConfirmButton: false
            });
        });

        function actualizarNumeroRecibo() {
            const num = contadorRecibo.toString().padStart(3, '0');
            document.getElementById('num_recibo').value = `00-${num}`;
        }

        function actualizarOpcionesPlan() {
            const plan = document.getElementById('plan').value;
            const selectMonto = document.getElementById('monto_plan');
            const campoMonto = document.getElementById('campoMontoPlan');
            selectMonto.innerHTML = "";
            campoMonto.style.display = "block";
            if (plan === "tv") selectMonto.innerHTML = `<option value="50">S/.50</option>`;
            else if (plan === "internet")[50, 60, 70, 80, 90, 100, 110].forEach(v => selectMonto.innerHTML += `<option value="${v}">S/.${v}</option>`);
            else if (plan === "duo")[75, 85, 95, 105, 115, 125].forEach(v => selectMonto.innerHTML += `<option value="${v}">S/.${v}</option>`);
            else campoMonto.style.display = "none";
            actualizarTotal();
        }

        function actualizarTotal() {
            const montoPlan = parseFloat(document.getElementById('monto_plan').value) || 0;
            document.getElementById('total_monto').innerText = montoPlan.toFixed(2);
        }

        function generarRecibo() {
            const nombre = document.getElementById('nombre').value.trim();
            if (!nombre) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Completa los datos',
                    text: 'Falta nombre o monto.'
                });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Recibo Generado',
                text: 'Descargando PDF...',
                showConfirmButton: false,
                timer: 1500
            });

            const elemento = document.getElementById('recibo');
            const opciones = {
                margin: 0.3,
                filename: `Recibo_${document.getElementById('num_recibo').value}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };

            setTimeout(() => {
                html2pdf().set(opciones).from(elemento).save();
            }, 800);

            contadorRecibo++;
            actualizarNumeroRecibo();
        }

        function limpiarCampos() {
            Swal.fire({
                title: '¿Limpiar campos?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, limpiar'
            }).then(r => {
                if (r.isConfirmed) {
                    document.querySelectorAll('input, select').forEach(i => i.value = '');
                    document.getElementById('total_monto').innerText = '0.00';
                }
            });
        }

        function imprimirTicketera() {
            const recibo = document.getElementById('recibo');

            // Guardar clases originales
            const clasesOriginales = recibo.className;

            // Activar estilo de ticketera
            recibo.className = "recibo-container ticketera";

            // Imprimir
            window.print();

            // Restaurar estilo original (por si vuelves al formato A4)
            setTimeout(() => {
                recibo.className = clasesOriginales;
            }, 500);
        }
    </script>

</body>

</html>