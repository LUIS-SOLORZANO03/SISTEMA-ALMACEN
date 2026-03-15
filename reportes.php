<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "data_online");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';

// Nueva consulta que calcula el stock real (entradas - salidas)
$sql = "
SELECT 
    p.id, 
    p.nombre, 
    c.nombre AS categoria, 
    p.unidad_medida,
    (COALESCE(e.total_entradas, 0) - COALESCE(s.total_salidas, 0)) AS stock_total
FROM productos p
LEFT JOIN categorias c ON p.id_categoria = c.id
LEFT JOIN (
    SELECT id_producto, SUM(cantidad) AS total_entradas
    FROM entradas_productos
    GROUP BY id_producto
) e ON e.id_producto = p.id
LEFT JOIN (
    SELECT id_producto, SUM(cantidad) AS total_salidas
    FROM salidas_productos
    GROUP BY id_producto
) s ON s.id_producto = p.id
WHERE p.estado = 'activo'
";

if (!empty($categoria)) {
    $sql .= " AND c.nombre = '" . $conexion->real_escape_string($categoria) . "'";
}

$sql .= " ORDER BY p.id ASC";

$resultado = $conexion->query($sql);
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Reporte de Productos</title>

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(-45deg, #141e30, #243b55, #2c5364, #0f9b0f);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #2c3e50;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%
            }

            50% {
                background-position: 100% 50%
            }

            100% {
                background-position: 0% 50%
            }
        }

        header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
            text-align: center;
            animation: fadeIn 1s ease;
        }

        header img {
            height: 100px;
            margin-bottom: 10px;
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.6);
        }

        header h1 {
            font-size: 40px;
            color: #fff;
            margin: 5px 0;
            text-shadow: 0 0 10px rgba(0, 229, 255, 0.9);
        }

        header h2 {
            font-size: 20px;
            color: #f0f0f0;
            margin: 0;
            font-weight: 400;
        }

        form {
            text-align: center;
            margin-bottom: 20px;
        }

        select,
        button {
            padding: 10px 18px;
            font-size: 15px;
            margin: 6px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        select {
            border: 1px solid #ccc;
        }

        .buscar {
            background: linear-gradient(90deg, #27ae60, #2ecc71);
            color: white;
        }

        .limpiar {
            background: linear-gradient(90deg, #c0392b, #e74c3c);
            color: white;
        }

        .exportar {
            background: linear-gradient(90deg, #2980b9, #3498db);
            color: white;
        }

        .imprimir {
            background: linear-gradient(90deg, #8e44ad, #9b59b6);
            color: white;
        }

        button:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }

        .tabla-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
            padding: 25px;
            max-width: 1000px;
            width: 100%;
            backdrop-filter: blur(10px);
            animation: fadeInUp 1.2s ease;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
        }

        th {
            background: rgba(46, 204, 113, 0.9);
            color: white;
            text-transform: uppercase;
            text-align: center;
            padding: 16px;
            font-size: 15px;
        }

        td,
        th {
            padding: 14px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: scale(1.01);
        }

        td:nth-child(3) {
            font-weight: bold;
        }

        td.low-stock {
            background: rgba(231, 76, 60, 0.8);
            color: #fff;
            border-radius: 6px;
        }

        td.medium-stock {
            background: rgba(241, 196, 15, 0.8);
            color: #fff;
            border-radius: 6px;
        }

        td.high-stock {
            background: rgba(39, 174, 96, 0.8);
            color: #fff;
            border-radius: 6px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <!-- Librerías jsPDF + AutoTable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
</head>

<body>

    <header>
        <img src="logo.png" alt="Logo">
        <h1>PRODUCTOS EN STOCK</h1>
        <h2>Reporte actualizado de stock</h2>
    </header>

    <form method="GET" action="">
        <select name="categoria" onchange="this.form.submit()">
            <option value="">-- Todas las Categorías --</option>
            <?php
            $categorias = $conexion->query("SELECT nombre FROM categorias ORDER BY nombre ASC");
            while ($cat = $categorias->fetch_assoc()) {
                $selected = ($cat['nombre'] === $categoria) ? 'selected' : '';
                echo "<option value=\"" . htmlspecialchars($cat['nombre']) . "\" $selected>" . htmlspecialchars($cat['nombre']) . "</option>";
            }
            ?>
        </select>

        <a href="panel_reporte.php"><button type="button" class="limpiar">Volver</button></a>
        <button type="button" class="exportar" onclick="exportarExcel()">📊 Excel</button>
        <button type="button" class="imprimir" onclick="exportarPDF()">📄 PDF</button>
    </form>

    <div class="tabla-container">
        <table id="tabla-productos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Stock Total</th>
                    <th>Unidad de Medida</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $resultado->data_seek(0);
                while ($row = $resultado->fetch_assoc()):
                    $stock = (int)$row['stock_total'];
                    $class = $stock <= 5 ? 'low-stock' : ($stock <= 15 ? 'medium-stock' : 'high-stock');
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td class="<?= $class ?>"><?= $stock ?></td>
                        <td><?= htmlspecialchars($row['unidad_medida']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>

</html>



<script>
    function exportarExcel() {
        const XLSX = window.XLSX;
        const tabla = document.getElementById("tabla-productos");
        const fechaHora_actual = new Date().toLocaleString();

        const wb = XLSX.utils.book_new();
        const ws_data = [];

        // Añadir títulos (separados para dar espacio)
        ws_data.push([]);
        ws_data.push([]);
        ws_data.push(["MATERIALES EN STOCK"]);
        ws_data.push(["MATERIALES DATA ONLINE PERU SAC"]);
        ws_data.push([`Fecha y hora de exportación: ${fechaHora_actual}`]);
        ws_data.push([]);

        // Encabezados con nuevo orden
        const headersOriginales = [];
        tabla.querySelectorAll("thead tr th").forEach(th => {
            headersOriginales.push(th.innerText.trim());
        });
        const headers = [headersOriginales[0], headersOriginales[1], headersOriginales[3], headersOriginales[2]];
        ws_data.push(headers);

        // Añadir filas con nuevo orden (stock antes que unidad)
        tabla.querySelectorAll("tbody tr").forEach(tr => {
            const cells = tr.querySelectorAll("td");
            const id = cells[0].innerText.trim();
            const nombre = cells[1].innerText.trim();
            const unidad = cells[2].innerText.trim();
            const stockRaw = parseFloat(cells[3].innerText.trim());
            const stockFormateado = stockRaw.toFixed(2);

            ws_data.push([id, nombre, stockFormateado, unidad]);
        });

        // Crear la hoja de cálculo
        const ws = XLSX.utils.aoa_to_sheet(ws_data);

        // Estilos para títulos grandes
        const titleStyle = {
            font: {
                name: "Arial",
                sz: 42,
                bold: true,
                color: {
                    rgb: "0F4C81"
                }
            },
            alignment: {
                horizontal: "center"
            }
        };
        const subtitleStyle = {
            font: {
                name: "Arial",
                sz: 36,
                bold: true,
                color: {
                    rgb: "1F618D"
                }
            },
            alignment: {
                horizontal: "center"
            }
        };
        const fechaStyle = {
            font: {
                name: "Arial",
                sz: 14,
                italic: true,
                color: {
                    rgb: "566573"
                }
            },
            alignment: {
                horizontal: "center"
            }
        };

        // Aplicar estilos a celdas de título (fila 3 y 4, index 2 y 3)
        ws['A3'].s = titleStyle;
        ws['A4'].s = subtitleStyle;
        ws['A5'].s = fechaStyle;

        // Fusionar celdas para títulos (de A a D)
        ws['!merges'] = [{
                s: {
                    r: 2,
                    c: 0
                },
                e: {
                    r: 2,
                    c: 3
                }
            }, // MATERIALES EN STOCK
            {
                s: {
                    r: 3,
                    c: 0
                },
                e: {
                    r: 3,
                    c: 3
                }
            }, // MATERIALES DATA ONLINE PERU SAC
            {
                s: {
                    r: 4,
                    c: 0
                },
                e: {
                    r: 4,
                    c: 3
                }
            }, // Fecha y hora
        ];

        // Estilo encabezado de tabla
        const headerStyle = {
            font: {
                bold: true,
                color: {
                    rgb: "FFFFFF"
                }
            },
            fill: {
                fgColor: {
                    rgb: "27AE60"
                }
            },
            alignment: {
                horizontal: "center",
                vertical: "center"
            },
            border: {
                top: {
                    style: "thin",
                    color: {
                        rgb: "000000"
                    }
                },
                bottom: {
                    style: "thin",
                    color: {
                        rgb: "000000"
                    }
                },
                left: {
                    style: "thin",
                    color: {
                        rgb: "000000"
                    }
                },
                right: {
                    style: "thin",
                    color: {
                        rgb: "000000"
                    }
                }
            }
        };

        // Estilo para celdas de datos con bordes
        const cellStyle = {
            border: {
                top: {
                    style: "thin",
                    color: {
                        rgb: "000000"
                    }
                },
                bottom: {
                    style: "thin",
                    color: {
                        rgb: "000000"
                    }
                },
                left: {
                    style: "thin",
                    color: {
                        rgb: "000000"
                    }
                },
                right: {
                    style: "thin",
                    color: {
                        rgb: "000000"
                    }
                }
            }
        };

        // Aplicar estilo a encabezados de tabla (fila 7, index 6)
        for (let col = 0; col < headers.length; col++) {
            const cellAddress = XLSX.utils.encode_cell({
                r: 6,
                c: col
            });
            if (!ws[cellAddress]) continue;
            ws[cellAddress].s = headerStyle;
        }

        // Aplicar bordes a todas las celdas con datos (desde fila 8, index 7 hasta el final)
        const totalRows = ws_data.length;
        for (let row = 7; row < totalRows; row++) {
            for (let col = 0; col < headers.length; col++) {
                const cellAddress = XLSX.utils.encode_cell({
                    r: row,
                    c: col
                });
                if (!ws[cellAddress]) continue;
                ws[cellAddress].s = cellStyle;
            }
        }

        // Ajustar anchos de columnas
        ws['!cols'] = [{
                wch: 6
            }, // ID
            {
                wch: 40
            }, // Producto
            {
                wch: 15
            }, // Stock Total
            {
                wch: 20
            } // Unidad de Medida
        ];

        // Añadir hoja al libro y guardar archivo
        XLSX.utils.book_append_sheet(wb, ws, "Reporte");
        XLSX.writeFile(wb, `Reporte_Materiales_${fechaHora_actual.replace(/[/:]/g, "-")}.xlsx`);
    }




    async function exportarPDF() {
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF();
        const fechaHora_actual = new Date().toLocaleString();

        const img = new Image();
        img.src = 'logo.png';

        img.onload = function() {
            const size = 20; // Tamaño del logo reducido para optimizar espacio
            const pageWidth = doc.internal.pageSize.getWidth();
            const x = (pageWidth - size) / 2;
            const y = 8;

            // Logo
            doc.addImage(img, 'PNG', x, y, size, size);

            // Títulos
            doc.setFontSize(16);
            doc.setTextColor(40, 62, 80);
            doc.text('MATERIALES EN STOCK', pageWidth / 2, y + size + 6, {
                align: 'center'
            });

            doc.setFontSize(12);
            doc.setTextColor(127, 140, 141);
            doc.text('MATERIALES DATA ONLINE PERU SAC', pageWidth / 2, y + size + 14, {
                align: 'center'
            });

            doc.setFontSize(9);
            doc.setTextColor(100);
            doc.text(`Fecha y hora de exportación: ${fechaHora_actual}`, pageWidth / 2, y + size + 20, {
                align: 'center'
            });

            // Tabla
            const headers = ["ID", "Producto", "Stock Total", "Unidad de Medida"];
            const body = [];

            document.querySelectorAll("#tabla-productos tbody tr").forEach(tr => {
                const cells = tr.querySelectorAll("td");
                const id = cells[0].innerText.trim();
                const nombre = cells[1].innerText.trim();
                const stock = cells[2].innerText.trim(); // ← sin toFixed
                const unidad = cells[3].innerText.trim();
                body.push([id, nombre, stock, unidad]);
            });


            doc.autoTable({
                startY: y + size + 26, // Inmediatamente después del texto
                head: [headers],
                body: body,
                theme: 'grid',
                styles: {
                    fontSize: 9,
                    cellPadding: 2,
                    valign: 'middle'
                },
                headStyles: {
                    fillColor: [46, 204, 113],
                    textColor: 255,
                    fontStyle: 'bold'
                },
                margin: {
                    top: 10
                },
                didDrawPage: function(data) {
                    if (data.pageNumber > 1) {
                        doc.setFontSize(9);
                        doc.setTextColor(150);
                        doc.text(`Página ${data.pageNumber}`, data.settings.margin.left, 10);
                    }
                }
            });

            doc.save(`productos_stock_${fechaHora_actual.replace(/[/:]/g, "-")}.pdf`);
        };
    }
</script>


<!-- Librería XLSX para exportar Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

</body>

</html>