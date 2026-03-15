<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$rol = $_SESSION['rol'] ?? 'usuario';

// Contadores (seguro y reutilizable)
function contarTabla($conexion, $tabla)
{
    // prevenir inyeccion: permitir solo nombres esperados
    $permitidos = ['rehusados', 'modens_opti', 'modens_huawei', 'modens_catv'];
    if (!in_array($tabla, $permitidos)) return 0;
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM {$tabla}");
    if (!$stmt) return 0;
    $stmt->execute();
    $res = $stmt->get_result();
    return $res ? ($res->fetch_assoc()['total'] ?? 0) : 0;
}

$total_rehusados = contarTabla($conexion, 'rehusados');
$total_opti = contarTabla($conexion, 'modens_opti');
$total_huawei = contarTabla($conexion, 'modens_huawei');
$total_catv = contarTabla($conexion, 'modens_catv');

$total_general = $total_rehusados + $total_opti + $total_huawei + $total_catv;

// Para mostrar tarjetas (nombre => [icon, tabla, url])
$categorias = [
    'REHUSADOS' => ['icon' => '🔄', 'tabla' => 'rehusados', 'url' => 'modens_rehusados.php'],
    'OPTI'      => ['icon' => '📡', 'tabla' => 'modens_opti', 'url' => 'modens_opti.php'],
    'HUAWEI'    => ['icon' => '🌐', 'tabla' => 'modens_huawei', 'url' => 'modens_huawei.php'],
    'CA-TV'     => ['icon' => '📺', 'tabla' => 'modens_catv', 'url' => 'modens_catv.php'],
];

// función auxiliar para etiqueta de color según cantidad
function badgeClassByCount($count)
{
    if ($count <= 5) return 'badge-low';       // rojo
    if ($count <= 20) return 'badge-medium';   // amarillo
    return 'badge-high';                      // verde
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard de Módems - Premium</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- tsParticles -->
    <script src="https://cdn.jsdelivr.net/npm/tsparticles@3.6.0/tsparticles.bundle.min.js"></script>

    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.08);
            --card-border: rgba(255, 255, 255, 0.12);
            --text-soft: rgba(255, 255, 255, 0.85);
            --muted: rgba(255, 255, 255, 0.7);
            --accent1: #6a11cb;
            --accent2: #2575fc;
            --accent-grad: linear-gradient(90deg, var(--accent1), var(--accent2));
        }

        .btn-volver {
            display: inline-flex;
            align-items:center;
            gap: 10px;
            padding: 14px 28px;
            font-size: 1.2rem;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-volver i {
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }

        .btn-volver:hover {
            transform: translateY(-4px) scale(1.05);
            background: linear-gradient(135deg, #2575fc, #6a11cb);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.35);
        }

        .btn-volver:hover i {
            transform: translateX(-6px);
        }

        /* ✨ Efecto glow animado */
        .btn-volver::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 10%, transparent 70%);
            transform: rotate(25deg);
            animation: shine 6s infinite linear;
        }

        @keyframes shine {
            0% {
                transform: rotate(25deg) translateX(-100%);
            }

            100% {
                transform: rotate(25deg) translateX(100%);
            }
        }

        * {
            box-sizing: border-box
        }

        html,
        body {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f1724;
        }

        /* Fondo animado y partículas */
        #bg-gradient {
            position: fixed;
            inset: 0;
            z-index: -3;
            background: linear-gradient(120deg, #0b1020 0%, #0f1724 40%, #081225 100%);
        }

        #bg-glow {
            position: fixed;
            inset: 0;
            z-index: -2;
            background: radial-gradient(600px 400px at 10% 10%, rgba(106, 17, 203, 0.12), transparent 8%),
                radial-gradient(500px 350px at 90% 80%, rgba(37, 117, 252, 0.10), transparent 12%);
            pointer-events: none;
            mix-blend-mode: screen;
        }

        /* Particles container */
        #tsparticles {
            position: fixed;
            inset: 0;
            z-index: -1;
        }

        /* Container */
        .container-fluid.custom {
            padding: 40px 24px;
        }

        /* Header */
        .header-wrap {
            display: flex;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .title {
            color: var(--text-soft);
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            text-shadow: 0 6px 18px rgba(58, 12, 163, 0.12);
        }

        .controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        /* Stats cards area */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 26px;
        }

        .stat-card {
            border-radius: 14px;
            padding: 18px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 8px 30px rgba(2, 6, 23, 0.6);
            color: var(--text-soft);
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .stat-card .icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            background: linear-gradient(135deg, #1e2a78, #4b33a8);
            box-shadow: 0 6px 18px rgba(32, 66, 150, 0.28);
        }

        .stat-card h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
        }

        .stat-card p {
            margin: 0;
            color: var(--muted);
            font-size: 0.9rem;
        }

        /* Grid of category cards */
        .grid-cats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
            margin-top: 10px;
        }

        .card-option {
            position: relative;
            border-radius: 14px;
            padding: 18px;
            min-height: 150px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: var(--text-soft);
            transition: transform .35s cubic-bezier(.2, .9, .2, 1), box-shadow .35s;
            cursor: pointer;
            display: flex;
            flex-direction: column;
        }

        .card-option:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 30px 50px rgba(3, 7, 18, 0.6);
        }

        .card-option .badge-stock {
            position: absolute;
            top: 14px;
            right: 14px;
            padding: 8px 12px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.85rem;
            color: #06131a;
            box-shadow: 0 8px 20px rgba(8, 12, 30, 0.4);
        }

        .badge-high {
            background: linear-gradient(90deg, #b6f79a, #36d1dc);
            color: #022;
        }

        .badge-medium {
            background: linear-gradient(90deg, #ffd86b, #ff7a7a);
            color: #111;
        }

        .badge-low {
            background: linear-gradient(90deg, #ff7b7b, #ff4b4b);
            color: #111;
        }

        /* Card content */
        .card-option h4 {
            margin: 0 0 8px 0;
            font-size: 1.1rem;
            color: #fff;
        }

        .card-option p {
            margin: 0;
            color: var(--muted);
            font-size: 0.9rem;
            flex-grow: 1;
        }

        /* Buttons */
        .btn-glow {
            background: var(--accent1);
            background: linear-gradient(90deg, var(--accent1), var(--accent2));
            border: none;
            color: #fff;
            padding: 9px 14px;
            border-radius: 10px;
            font-weight: 700;
            transition: transform .18s, box-shadow .18s;
        }

        .btn-glow:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 40px rgba(33, 51, 136, 0.35);
        }

        /* Search */
        .search-wrap {
            min-width: 250px;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .search-wrap input {
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            padding: 10px 12px;
            background: transparent;
            color: var(--text-soft);
            outline: none;
            width: 260px;
        }

        /* Footer small */
        .small-muted {
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 18px;
            text-align: center;
        }

        /* Responsive tweaks */
        @media (max-width:576px) {
            .search-wrap input {
                width: 140px;
                font-size: 0.9rem;
            }

            .stat-card {
                flex-direction: row;
                gap: 10px;
            }
        }
    </style>
</head>

<body>

    <!-- Background layers -->
    <div id="bg-gradient"></div>
    <div id="bg-glow"></div>
    <div id="tsparticles"></div>

    <div class="container-fluid custom">

        <!-- Header -->
        <div class="header-wrap">
            <div>
                <div class="title">📶 Panel de Módems</div>
                <div class="small-muted">Usuario: <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong> · Rol: <strong><?= htmlspecialchars($rol) ?></strong></div>
            </div>

            <div class="controls">
                <!-- Search -->
                <div class="search-wrap" title="Buscar tarjetas por nombre">
                    <i class="bi bi-search" style="color:var(--muted)"></i>
                    <input id="searchInput" placeholder="Buscar categorías..." />
                </div>

                <!-- Dark/Light toggle -->
                <button id="themeToggle" class="btn btn-sm btn-outline-light" title="Cambiar tema">
                    <i id="themeIcon" class="bi bi-moon-fill"></i>
                </button>

                <!-- Help -->
                <button id="helpBtn" class="btn btn-sm btn-outline-light" title="Atajos y ayuda">
                    <i class="bi bi-question-circle"></i>
                </button>
            </div>
        </div>

        <!-- Stats cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon"><i class="bi bi-bar-chart-line"></i></div>
                <div>
                    <h3><?= number_format($total_general) ?></h3>
                    <p>Total de equipos registrados</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="icon"><i class="bi bi-clock-history"></i></div>
                <div>
                    <h3><?= number_format($total_rehusados) ?></h3>
                    <p>Rehusados</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="icon"><i class="bi bi-lightning-charge-fill fs-3 text-warning"></i></div>
                <div>
                    <h3><?= number_format($total_opti) ?></h3>
                    <p>OPTI</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="icon"><i class="bi bi-globe2"></i></div>
                <div>
                    <h3><?= number_format($total_huawei) ?></h3>
                    <p>Huawei</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="bi bi-broadcast"></i></div>
                <div>
                    <h3><?= number_format($total_catv) ?></h3>
                    <p>CA-TV</p>
                </div>
            </div>
        </div>

        <!-- Chart + grid -->
        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="stat-card" style="flex-direction:column; gap:12px;">
                    <h3 style="color:#fff; margin-bottom:0">Distribución por categoría</h3>
                    <canvas id="donutChart" style="max-height:260px"></canvas>
                    <div style="width:100%; display:flex; gap:8px; justify-content:space-between; margin-top:6px;">
                        <small class="small-muted">Total <?= number_format($total_general) ?></small>
                        <small class="small-muted">Actualizado: <?= date('d/m/Y H:i') ?></small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <h4 style="color:var(--text-soft); margin:0">Categorías</h4>
                    <div>
                        <button id="refreshStats" class="btn btn-sm btn-glow"><i class="bi bi-arrow-clockwise"></i> Actualizar</button>
                    </div>
                </div>

                <div class="grid-cats" id="cardsContainer">
                    <?php foreach ($categorias as $nombre => $meta):
                        $count = contarTabla($conexion, $meta['tabla']);
                        $badgeClass = badgeClassByCount($count);
                        // disabled por rol
                        $disabled = ($rol !== 'almacenero' && $rol !== 'admin');
                    ?>
                        <div class="card-option" data-name="<?= strtolower($nombre) ?>" data-url="<?= $meta['url'] ?>" data-allowed="<?= $disabled ? '0' : '1' ?>">
                            <span class="badge-stock <?= $badgeClass ?>"><?= $count ?> disponibles</span>
                            <h4><?= $meta['icon'] ?> <?= $nombre ?></h4>
                            <p>Gestiona y consulta los equipos de <strong><?= strtolower($nombre) ?></strong>. Accede para ver listados, editar y gestionar stock.</p>
                            <div style="display:flex; gap:10px; margin-top:12px;">
                                <button class="btn btn-glow btn-sm btn-enter">Ingresar</button>
                                <button class="btn btn-sm btn-outline-light btn-info" onclick="event.stopPropagation(); mostrarInfo('<?= addslashes($nombre) ?>','<?= $meta['url'] ?>', <?= $count ?>)"> Info</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <a href="panel_almacen.php" class="btn-volver">
            <i class="bi bi-arrow-left-circle-fill"></i> Volver al Panel
        </a>

        <div class="small-muted" style="margin-top:18px">© <?= date('Y') ?> - Sistema de inventario</div>

    </div>

    <!-- Scripts libs -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ---------- tsParticles (fondo suave) ----------
        tsParticles.load("tsparticles", {
            fullScreen: {
                enable: true,
                zIndex: -1
            },
            particles: {
                number: {
                    value: 35,
                    density: {
                        enable: true,
                        area: 800
                    }
                },
                color: {
                    value: ["#6a11cb", "#2575fc", "#00c6ff"]
                },
                shape: {
                    type: "circle"
                },
                opacity: {
                    value: 0.06
                },
                size: {
                    value: {
                        min: 2,
                        max: 8
                    }
                },
                move: {
                    enable: true,
                    speed: 0.6,
                    outModes: "bounce"
                }
            },
            retina_detect: true
        });

        // ---------- Chart.js donut ----------
        const donutCtx = document.getElementById('donutChart').getContext('2d');
        const donut = new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['REHUSADOS', 'OPTI', 'HUAWEI', 'CA-TV'],
                datasets: [{
                    data: [<?= $total_rehusados ?>, <?= $total_opti ?>, <?= $total_huawei ?>, <?= $total_catv ?>],
                    backgroundColor: ['#7b61ff', '#4290ff', '#5be7c1', '#ffd26a'],
                    hoverOffset: 8,
                    borderWidth: 0
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#e6eef8'
                        }
                    },
                    tooltip: {
                        bodyColor: '#000',
                        titleColor: '#000'
                    }
                },
                maintainAspectRatio: false,
                cutout: '60%',
            }
        });

        // ---------- Interacciones tarjetas ----------
        const cards = Array.from(document.querySelectorAll('.card-option'));
        const cardsContainer = document.getElementById('cardsContainer');
        cards.forEach((card, idx) => {
            // click sobre tarjeta -> intenta redirigir
            card.addEventListener('click', e => {
                const allowed = card.getAttribute('data-allowed') === '1';
                const url = card.getAttribute('data-url');
                if (!allowed) {
                    // Toast con SweetAlert2 (permiso)
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Acceso denegado',
                        text: 'Necesitas rol de almacenero o admin para ingresar a esta sección.',
                        showConfirmButton: false,
                        timer: 3500
                    });
                    return;
                }
                // Loader + redirección
                Swal.fire({
                    title: 'Cargando...',
                    text: 'Redirigiendo a la sección',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                        // simular pequeño delay
                        setTimeout(() => {
                            window.location.href = url;
                        }, 700);
                    }
                });
            });

            // boton ingresar interno
            const btnEnter = card.querySelector('.btn-enter');
            if (btnEnter) {
                btnEnter.addEventListener('click', e => {
                    e.stopPropagation();
                    card.click();
                });
            }
        });

        // ---------- Search en vivo ----------
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', e => {
            const q = e.target.value.trim().toLowerCase();
            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                if (!q || name.includes(q)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // ---------- Atajos de teclado ----------
        document.addEventListener('keydown', e => {
            if (document.activeElement && (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA')) return;
            if (e.key === '?') {
                e.preventDefault();
                mostrarAyuda();
                return;
            }
            if (['1', '2', '3', '4'].includes(e.key)) {
                const index = parseInt(e.key) - 1;
                if (cards[index]) cards[index].click();
            }
        });

        // ---------- Ayuda modal con SweetAlert2 ----------
        function mostrarAyuda() {
            Swal.fire({
                title: 'Atajos y Ayuda',
                html: `<ul style="text-align:left">
                <li><strong>1..4</strong> - Abrir categoría rápida</li>
                <li><strong>Buscar</strong> - Filtrar tarjetas</li>
                <li><strong>Tema</strong> - Cambiar claro/oscuro</li>
               </ul>`,
                icon: 'info'
            });
        }

        // ---------- Mostrar info tarjeta ----------
        function mostrarInfo(nombre, url, count) {
            Swal.fire({
                title: `${nombre}`,
                html: `<p>Registros: <strong>${count}</strong></p><p>Ir a: <code>${url}</code></p>`,
                icon: 'info'
            });
        }

        // ---------- Actualizar (simula refrescar) ----------
        document.getElementById('refreshStats').addEventListener('click', () => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Estadísticas actualizadas',
                showConfirmButton: false,
                timer: 1800
            });
            // Si quieres, aquí podrías usar fetch() para traer datos via AJAX.
        });

        // ---------- Theme toggle (dark/light) ----------
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');

        function setTheme(mode) {
            if (mode === 'light') {
                document.documentElement.style.setProperty('--glass-bg', 'rgba(255,255,255,0.9)');
                document.documentElement.style.setProperty('--card-border', 'rgba(15,23,42,0.06)');
                document.documentElement.style.setProperty('--text-soft', '#0f1724');
                themeIcon.className = 'bi bi-sun-fill';
                // ajustar background claro
                document.getElementById('bg-gradient').style.background = 'linear-gradient(120deg,#f6f9ff,#e9f0ff 60%, #f0faff)';
                document.getElementById('bg-glow').style.opacity = '0.9';
            } else {
                document.documentElement.style.setProperty('--glass-bg', 'rgba(255,255,255,0.08)');
                document.documentElement.style.setProperty('--card-border', 'rgba(255,255,255,0.06)');
                document.documentElement.style.setProperty('--text-soft', 'rgba(255,255,255,0.92)');
                themeIcon.className = 'bi bi-moon-fill';
                document.getElementById('bg-gradient').style.background = 'linear-gradient(120deg,#0b1020,#0f1724 40%, #081225 100%)';
                document.getElementById('bg-glow').style.opacity = '1';
            }
            localStorage.setItem('theme_mode', mode);
        }

        themeToggle.addEventListener('click', () => {
            const cur = localStorage.getItem('theme_mode') || 'dark';
            const next = cur === 'dark' ? 'light' : 'dark';
            setTheme(next);
        });

        // aplicar al cargar
        document.addEventListener('DOMContentLoaded', () => {
            const saved = localStorage.getItem('theme_mode') || 'dark';
            setTheme(saved);
        });

        // ---------- Toaster inicial si rol limitado ----------
        (function() {
            const role = "<?= addslashes($rol) ?>";
            if (role !== 'admin' && role !== 'almacenero') {
                // mostramos un pequeño aviso no intrusivo
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'Acceso restringido',
                    text: 'Tu rol no permite editar inventario. Contacta a administración.',
                    showConfirmButton: false,
                    timer: 4500
                });
            }
        })();
    </script>

</body>

</html>