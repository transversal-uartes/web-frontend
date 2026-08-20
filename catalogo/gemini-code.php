<?php
// Variables de entorno de AlwaysData
$nombreTecnico    = getenv('NOMBRE_TECNICO') ?: 'Técnico de Laboratorio';
$correoTecnico    = getenv('CORREO_TECNICO') ?: 'laboratorios@uartes.edu.ec';
$correosCC        = getenv('CORREOS_CC')     ?: ''; 
$csvUrl           = getenv('CSV_URL')        ?: 'URL_POR_DEFECTO'; 
$pronombreTecnico = getenv('PRONOMBRE_TECNICO') ?: 'Estimado/a'; 

// VARIABLES DE CRÉDITOS
$autorNombre      = getenv('AUTOR_NOMBRE') ?: 'Autor';
$githubUrl        = getenv('GITHUB_URL')   ?: 'https://github.com/';

// VARIABLES PHP GLOBALES PARA LOS LOGOS
$logoClaro        = getenv('LOGO_CLARO')  ?: 'Logo-OFICIAL-RECINTO-LAB-claro.png';
$logoOscuro       = getenv('LOGO_OSCURO') ?: 'Logo-OFICIAL-RECINTO-LAB-oscuro.png';

// Calcular URL absoluta para la imagen miniatura de WhatsApp/Redes
$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$urlBase = $protocolo . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$urlLogoOG = $urlBase . "/" . $logoOscuro; 

// MAPEO DE IMÁGENES: Lee la carpeta img/ para detectar extensiones reales
$imageFiles = glob('img/*.*') ?: [];
$imageMap = [];
foreach ($imageFiles as $file) {
    $info = pathinfo($file);
    $imageMap[$info['filename']] = $info['basename'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Préstamos - UArtes</title>
    
    <meta property="og:title" content="Catálogo de Equipos - UArtes">
    <meta property="og:description" content="Catálogo de inventario y sistema de solicitud de préstamos de equipos para RecintoLab y Mediaco.">
    <meta property="og:image" content="<?php echo $urlLogoOG; ?>">
    <meta property="og:url" content="<?php echo $urlBase; ?>">
    <meta property="og:type" content="website">

    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    <style>
        * { box-sizing: border-box; }
        
        /* SISTEMA DE VARIABLES DE TEMA */
        :root {
            --bg-main: #121212;
            --text-main: #e0e0e0;
            --text-title: #ffffff;
            --bg-card: #1e1e1e;
            --border-color: #444;
            --border-light: #333;
            --table-header-bg: #0d47a1;
            --table-header-text: #ffffff;
            --table-hover: #2c2c2c;
            --input-bg: #2a2a2a;
            --input-text: #e0e0e0;
            --input-border: #555;
            --input-readonly-bg: #1a1a1a;
            --input-readonly-text: #777;
            --shadow-color: rgba(0,0,0,0.5);
            --modal-overlay: rgba(0,0,0,0.8);
            --info-bg: #222;
            
            --badge-disp-bg: #1b4332;
            --badge-disp-text: #81c784;
            --badge-nodisp-bg: #4a191e;
            --badge-nodisp-text: #e57373;
            --badge-warn-bg: #4d3800;
            --badge-warn-text: #ffca28;
            
            --btn-copy-bg: #555;
            --btn-copy-hover: #777;
        }

        [data-theme="light"] {
            --bg-main: #f4f4f9;
            --text-main: #333;
            --text-title: #2c3e50;
            --bg-card: #ffffff;
            --border-color: #ccc;
            --border-light: #eee;
            --table-header-bg: #0056b3;
            --table-header-text: #ffffff;
            --table-hover: #f8f9fa;
            --input-bg: #ffffff;
            --input-text: #333;
            --input-border: #ccc;
            --input-readonly-bg: #e9ecef;
            --input-readonly-text: #6c757d;
            --shadow-color: rgba(0,0,0,0.1);
            --modal-overlay: rgba(0,0,0,0.6);
            --info-bg: #f8f9fa;
            
            --badge-disp-bg: #d4edda;
            --badge-disp-text: #155724;
            --badge-nodisp-bg: #f8d7da;
            --badge-nodisp-text: #721c24;
            --badge-warn-bg: #fff3cd;
            --badge-warn-text: #856404;

            --btn-copy-bg: #6c757d;
            --btn-copy-hover: #495057;
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bg-main); padding: 20px; margin: 0; color: var(--text-main); height: 100vh; display: flex; flex-direction: column; transition: background-color 0.3s, color 0.3s; }
        
        .sticky-top-section { position: -webkit-sticky; position: sticky; top: 0; background-color: var(--bg-main); z-index: 100; padding-bottom: 15px; width: 100%; max-width: 1100px; margin: 0 auto; transition: background-color 0.3s; }

        .header-title-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; padding-top: 10px; }
        #logoRecinto { height: 45px; width: auto; object-fit: contain; flex-shrink: 0; }
        .header-title-row h1 { text-align: center; color: var(--text-title); margin: 0; font-size: 28px; flex-grow: 1; }
        #themeToggle { background: transparent; border: none; font-size: 24px; cursor: pointer; flex-shrink: 0; padding: 5px; border-radius: 50%; transition: transform 0.2s; }
        #themeToggle:hover { transform: scale(1.1); }
        
        .top-controls { display: flex; justify-content: space-between; align-items: center; gap: 15px; }
        .btn-reload { background: #6c757d; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; white-space: nowrap; }
        .btn-reload:hover { background: #5a6268; }
        .btn-reload:disabled { opacity: 0.6; cursor: not-allowed; }
        #searchInput { padding: 10px; width: 100%; max-width: 350px; border-radius: 5px; border: 1px solid var(--input-border); background: var(--input-bg); color: var(--input-text); box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); }

        .table-container { width: 100%; max-width: 1100px; margin: 0 auto 80px auto; background: var(--bg-card); border-radius: 8px; box-shadow: 0 4px 6px var(--shadow-color); overflow: auto; max-height: calc(100vh - 180px); transition: background-color 0.3s; }
        
        /* ESTILOS ENCAPSULADOS SOLO PARA LA TABLA PRINCIPAL */
        #inventoryTable { width: 100%; border-collapse: collapse; min-width: 700px; }
        #inventoryTable th, #inventoryTable td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-light); vertical-align: middle; }
        #inventoryTable th { background-color: var(--table-header-bg); color: var(--table-header-text); cursor: pointer; user-select: none; position: -webkit-sticky; position: sticky; top: 0; z-index: 90; box-shadow: 0 2px 2px -1px rgba(0,0,0,0.2); }
        #inventoryTable tr:hover { background-color: var(--table-hover); }
        
        .celda-seleccion { display: flex; align-items: center; gap: 12px; }
        .celda-seleccion input[type="checkbox"] { transform: scale(1.2); cursor: pointer; margin: 0; }
        
        .item-thumb {
            width: 40px; height: 40px; object-fit: cover; border-radius: 4px;
            cursor: pointer; background-color: var(--input-bg);
            border: 1px solid var(--border-color); transition: transform 0.2s;
        }
        .item-thumb:hover { transform: scale(1.1); box-shadow: 0 2px 5px var(--shadow-color); }

        .badge { padding: 5px 10px; border-radius: 15px; font-weight: bold; font-size: 11px; white-space: nowrap; }
        .disponible { background-color: var(--badge-disp-bg); color: var(--badge-disp-text); }
        .no-disponible { background-color: var(--badge-nodisp-bg); color: var(--badge-nodisp-text); }
        .advertencia { background-color: var(--badge-warn-bg); color: var(--badge-warn-text); }
        
        .input-cantidad { width: 45px; padding: 5px; border: 1px solid var(--input-border); border-radius: 4px; text-align: center; background: var(--input-bg); color: var(--input-text); }
        .input-cantidad[readonly] { background-color: var(--input-readonly-bg); color: var(--input-readonly-text); cursor: not-allowed; border-color: var(--input-border); }

        .btn-solicitar-float { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background-color: #0056b3; color: white; border: none; padding: 15px 30px; border-radius: 50px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 15px rgba(0,0,0,0.3); cursor: pointer; z-index: 100; transition: transform 0.2s, background-color 0.2s; }
        .btn-solicitar-float:hover { transform: translateX(-50%) scale(1.05); background-color: #004494; }

        .footer-credits { position: fixed; bottom: 10px; right: 15px; font-size: 10px; color: var(--input-readonly-text); z-index: 50; text-align: right; pointer-events: none; }
        .footer-credits a { color: var(--badge-disp-text); text-decoration: none; pointer-events: auto; }

        /* ========================================================================
           DISEÑO DE VENTANA DE PREVIA: SPLIT DASHBOARD LAYOUT (ESTILO RESERVA ESPACIOS)
           ======================================================================== */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: var(--modal-overlay); justify-content: center; align-items: center; }
        
        .modal-content { 
            background: var(--bg-card); 
            padding: 25px; 
            border-radius: 12px; 
            width: 95%; 
            max-width: 1200px; 
            height: 85vh; 
            max-height: 95vh; 
            position: relative; 
            display: flex; 
            flex-direction: column; 
            box-shadow: 0 10px 25px var(--shadow-color); 
        }
        
        /* Contenedor dividido en 2 columnas */
        .modal-split-layout {
            display: flex;
            flex: 1;
            gap: 20px;
            min-height: 0; /* Permite scroll independiente interno */
            margin-bottom: 15px;
        }
        
        /* Columna Izquierda: Formulario y Alerta */
        .modal-left-pane {
            width: 45%;
            display: flex;
            flex-direction: column;
            gap: 12px;
            overflow-y: auto;
            padding-right: 8px;
        }
        
        /* Columna Derecha: Vista Previa Enriquecida */
        .modal-right-pane {
            width: 55%;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .info-header { background: var(--info-bg); border-radius: 8px; padding: 12px; border-left: 5px solid var(--table-header-bg); position: relative; flex-shrink: 0; margin-bottom: 10px; }
        .info-header p { margin: 6px 0; font-size: 13.5px; word-break: break-all; }
        
        .copy-btn { background: var(--btn-copy-bg); color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; cursor: pointer; border: none; margin-left: 10px; }
        .copy-btn:hover { background: var(--btn-copy-hover); }

        /* Panel de inputs organizados verticalmente para la columna izquierda */
        .modal-inputs-vertical { display: flex; flex-direction: column; gap: 12px; }
        .modal-inputs-vertical input, .modal-inputs-vertical select, .modal-inputs-vertical textarea { padding: 10px; border: 1px solid var(--input-border); border-radius: 5px; width: 100%; background: var(--input-bg); color: var(--input-text); font-family: inherit; font-size: 14px; transition: border-color 0.2s, box-shadow 0.2s; }
        textarea#inputRazon { height: 65px; resize: none; }
        
        .date-inputs-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        /* CUADRO DE ADVERTENCIA DEL ACTA FIRMADA */
        .warning-notice-card {
            background-color: rgba(255, 202, 40, 0.12);
            border: 1px solid var(--badge-warn-text);
            border-left: 5px solid var(--badge-warn-text);
            padding: 12px;
            border-radius: 6px;
            font-size: 12.5px;
            line-height: 1.45;
            color: var(--badge-warn-text);
        }

        /* GRUPO DE RESPONSABLE */
        .responsable-group {
            display: none;
            padding: 12px;
            border: 1px dashed var(--border-color);
            border-radius: 8px;
            background: var(--input-readonly-bg);
        }
        .responsable-title { margin: 0 0 10px 0; font-size: 12.5px; font-weight: bold; color: var(--badge-warn-text); }
        .resp-grid { display: flex; flex-direction: column; gap: 8px; }

        .error-input { border-color: #e57373 !important; box-shadow: 0 0 5px rgba(229, 115, 115, 0.5) !important; }

        .date-group { display: flex; flex-direction: column; gap: 4px; }
        .date-group label { font-size: 12px; font-weight: bold; color: var(--input-readonly-text); }

        .preview-box-container { position: relative; display: flex; flex-direction: column; flex-grow: 1; min-height: 100px; }
        .preview-html-box { width: 100%; flex-grow: 1; padding: 15px; border: 1px solid var(--input-border); border-radius: 8px; background: #ffffff; color: #222222; font-family: 'Segoe UI', sans-serif; font-size: 14px; line-height: 1.5; overflow-y: auto; }

        .top-right-btn { position: absolute; top: 10px; right: 10px; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0; padding-top: 5px; }

        /* REGLAS RESPONSIVAS MÓVILES (UX COMPACTA) */
        @media (max-width: 768px) {
            body { padding: 10px; padding-bottom: 0; font-size: 12px; }
            .sticky-top-section { padding-bottom: 10px; }
            
            .header-title-row { gap: 10px; margin-bottom: 10px; }
            #logoRecinto { height: 32px; }
            .header-title-row h1 { font-size: 16px; }
            #themeToggle { font-size: 18px; }

            .top-controls { flex-direction: column; align-items: stretch; gap: 8px; }
            #searchInput { max-width: 100%; padding: 8px; font-size: 13px; }
            .btn-reload { width: 100%; text-align: center; padding: 8px; font-size: 13px; }
            
            .table-container { max-height: calc(100vh - 210px); margin-bottom: 60px; border-radius: 6px; -webkit-overflow-scrolling: touch; }
            #inventoryTable th, #inventoryTable td { padding: 6px 8px; font-size: 11.5px; }
            
            .celda-seleccion { gap: 8px; }
            .item-thumb { width: 32px; height: 32px; }
            .badge { padding: 3px 6px; font-size: 10px; }
            .input-cantidad { width: 38px; padding: 3px; font-size: 11px; }

            .btn-solicitar-float { bottom: 0; left: 0; transform: none; width: 100%; border-radius: 0; padding: 16px; font-size: 14px; }
            .btn-solicitar-float:hover { transform: none; }

            .footer-credits { display: none; }
            
            /* En móviles cancelamos el split horizontal y apilamos */
            .modal-content { width: 95%; height: 95vh; padding: 12px; border-radius: 8px; }
            .modal-split-layout { flex-direction: column; overflow-y: auto; gap: 15px; }
            .modal-left-pane, .modal-right-pane { width: 100%; }
            .modal-left-pane { overflow-y: visible; }
            
            .modal-inputs-vertical input, .modal-inputs-vertical select, .modal-inputs-vertical textarea { padding: 7px; font-size: 12.5px; }
            textarea#inputRazon { height: 50px; }
            
            .copy-btn { padding: 3px 5px; font-size: 9.5px; margin-top: 3px; display: inline-block; }
            .preview-html-box { padding: 8px; font-size: 11.5px; max-height: 250px; }
            
            .modal-actions { margin-top: 8px; gap: 6px; }
            .modal-actions button { padding: 8px 12px; font-size: 13px; width: 100%; }
        }
    </style>
</head>
<body>

    <div class="sticky-top-section">
        <div class="header-title-row">
            <img id="logoRecinto" src="" alt="RecintoLab">
            <h1>Catálogo de Equipos - UArtes</h1>
            <button id="themeToggle" onclick="toggleTheme()"></button>
        </div>

        <div class="top-controls">
            <button class="btn-reload" onclick="cargarInventario(true)">🔄 Actualizar Catálogo</button>
            <input type="text" id="searchInput" onkeyup="filtrarTabla()" placeholder="🔍 Filtrar equipos...">
        </div>
    </div>

    <div class="table-container">
        <table id="inventoryTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="uncheckAllBox" disabled onchange="deseleccionarTodo(this)"></th>
                    <th>Cant.</th>
                    <th onclick="ordenarTabla(2)">Detalle ⇕</th>
                    <th onclick="ordenarTabla(3)">Marca/Modelo ⇕</th>
                    <th onclick="ordenarTabla(4)">Estado ⇕</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="5" style="text-align:center;">Cargando inventario...</td></tr>
            </tbody>
        </table>
    </div>

    <button class="btn-solicitar-float" onclick="abrirPrevia()">Solicitar Préstamo</button>

    <div class="footer-credits">
        Desarrollado por <strong><?php echo htmlspecialchars($autorNombre); ?></strong> | 
        <a href="<?php echo htmlspecialchars($githubUrl); ?>" target="_blank">GitHub</a>
    </div>

    <!-- MODAL DE PREVIA REDISEÑADO EN SPLIT DASHBOARD LAYOUT -->
    <div id="previewModal" class="modal">
        <div class="modal-content">
            <!-- Header superior común -->
            <div class="info-header">
                <p><strong>Para:</strong> <?php echo htmlspecialchars($nombreTecnico); ?> 
                   <button class="copy-btn" onclick="copiarTexto('<?php echo $correoTecnico; ?>')">Copiar Correo</button></p>
                <?php if($correosCC): ?>
                <p><strong>CC:</strong> <?php echo htmlspecialchars($correosCC); ?>
                   <button class="copy-btn" onclick="copiarTexto('<?php echo $correosCC; ?>')">Copiar CC</button></p>
                <?php endif; ?>
                <p><strong>Asunto:</strong> Solicitud de préstamos de equipos
                   <button class="copy-btn" onclick="copiarTexto('Solicitud de préstamos de equipos')">Copiar Asunto</button></p>
            </div>
            
            <!-- Zona de Trabajo Dividida -->
            <div class="modal-split-layout">
                
                <!-- COLUMNA IZQUIERDA: Formulario y Advertencias -->
                <div class="modal-left-pane">
                    <div class="modal-inputs-vertical">
                        <input type="text" id="inputNombre" placeholder="Tu Nombre Completo" oninput="actualizarCuerpo()" required>
                        
                        <select id="inputRol" onchange="verificarRol(); actualizarCuerpo()" required>
                            <option value="" disabled selected>Tu rol...</option>
                            <option value="Docente">Docente</option>
                            <option value="Administrativo">Administrativo</option>
                            <option value="Estudiante">Estudiante</option>
                        </select>
                        
                        <input type="text" id="inputCedula" placeholder="Tu Cédula" oninput="actualizarCuerpo()" required>
                        
                        <!-- Bloque Responsable Dinámico -->
                        <div id="responsableContainer" class="responsable-group">
                            <p class="responsable-title">Datos del Responsable (Requerido):</p>
                            <div class="resp-grid">
                                <input type="text" id="inputRespNombre" placeholder="Nombre del Docente/Admin" oninput="actualizarCuerpo()">
                                <select id="inputRespRol" onchange="actualizarCuerpo()">
                                    <option value="" disabled selected>Rol del responsable...</option>
                                    <option value="Docente">Docente</option>
                                    <option value="Administrativo">Administrativo</option>
                                </select>
                            </div>
                        </div>

                        <textarea id="inputRazon" placeholder="Motivo del préstamo (proyecto, materia, etc.)" oninput="actualizarCuerpo()" required></textarea>

                        <div class="date-inputs-row">
                            <div class="date-group">
                                <label>Retiro:</label>
                                <input type="datetime-local" id="inputFechaInicio" oninput="actualizarCuerpo()" required>
                            </div>
                            <div class="date-group">
                                <label>Entrega:</label>
                                <input type="datetime-local" id="inputFechaFin" oninput="actualizarCuerpo()" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- NUEVO CUADRO DE ADVERTENCIA ADAPTADO AL TEMA -->
                    <div class="warning-notice-card">
                        <strong>⚠️ Importante:</strong> El préstamo será habilitado únicamente en el momento que la acta de préstamo que se genere posteriormente sea firmada por el responsable.
                    </div>
                </div>
                
                <!-- COLUMNA DERECHA: Vista previa en tiempo real -->
                <div class="modal-right-pane">
                    <div class="preview-box-container">
                        <button class="copy-btn top-right-btn" onclick="copiarCuerpoFormato()">Copiar Cuerpo</button>
                        <div id="emailBodyHTML" class="preview-html-box"></div>
                    </div>
                </div>
                
            </div>

            <!-- Botones de Acción Inferiores -->
            <div class="modal-actions">
                <button style="background:var(--btn-copy-bg); color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;" onclick="cerrarModal()">Cancelar</button>
                <button style="background:#28a745; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; font-weight:bold;" onclick="enviarFinal()">Enviar vía Email</button>
            </div>
        </div>
    </div>

    <!-- LIGHTBOX -->
    <div id="imgLightbox" class="modal" onclick="cerrarLightbox(event)">
        <span class="close-lightbox" onclick="cerrarLightbox(event)">&times;</span>
        <img class="lightbox-img" id="lightboxImg">
    </div>

    <script>
        // VARIABLES GLOBALES INYECTADAS DESDE PHP
        const imageMap = <?php echo json_encode($imageMap); ?>;
        const logoClaro = "<?php echo addslashes($logoClaro); ?>";
        const logoOscuro = "<?php echo addslashes($logoOscuro); ?>";
        const csvUrl = "<?php echo addslashes($csvUrl); ?>";
        const tecnicoNom = "<?php echo addslashes($nombreTecnico); ?>";
        const tecnicoMail = "<?php echo addslashes($correoTecnico); ?>";
        const copiaMail = "<?php echo addslashes($correosCC); ?>";
        const tecnicoPronombre = "<?php echo addslashes($pronombreTecnico); ?>"; 

        // ================= DATOS PERSONALES Y ROLES =================
        function cargarDatosGuardados() {
            if (localStorage.getItem('uartes_nombre')) document.getElementById('inputNombre').value = localStorage.getItem('uartes_nombre');
            if (localStorage.getItem('uartes_rol')) document.getElementById('inputRol').value = localStorage.getItem('uartes_rol');
            if (localStorage.getItem('uartes_cedula')) document.getElementById('inputCedula').value = localStorage.getItem('uartes_cedula');
            if (localStorage.getItem('uartes_razon')) document.getElementById('inputRazon').value = localStorage.getItem('uartes_razon');
            if (localStorage.getItem('uartes_resp_nombre')) document.getElementById('inputRespNombre').value = localStorage.getItem('uartes_resp_nombre');
            if (localStorage.getItem('uartes_resp_rol')) document.getElementById('inputRespRol').value = localStorage.getItem('uartes_resp_rol');
            verificarRol();
        }

        function verificarRol() {
            const rol = document.getElementById('inputRol').value;
            const respContainer = document.getElementById('responsableContainer');
            if (rol === 'Estudiante') {
                respContainer.style.display = 'block';
            } else {
                respContainer.style.display = 'none';
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            cargarDatosGuardados();
            cargarInventario(false); 
        });

        // ================= TEMA UI =================
        function aplicarTemaUI(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            
            const logo = document.getElementById('logoRecinto');
            const btnTheme = document.getElementById('themeToggle');
            const placeholderSrc = theme === 'dark' ? logoClaro : logoOscuro;
            
            logo.src = placeholderSrc;
            btnTheme.innerHTML = theme === 'dark' ? '☀️' : '🌙';

            document.querySelectorAll('.fallback-thumb').forEach(img => {
                img.src = placeholderSrc;
            });
        }
        function toggleTheme() { aplicarTemaUI(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'); }
        aplicarTemaUI(document.documentElement.getAttribute('data-theme'));

        // ================= LIGHTBOX =================
        function abrirLightbox(src) {
            document.getElementById('lightboxImg').src = src;
            document.getElementById('imgLightbox').style.display = 'flex';
        }
        function cerrarLightbox(e) {
            if(e.target.id === 'imgLightbox' || e.target.classList.contains('close-lightbox')) {
                document.getElementById('imgLightbox').style.display = 'none';
            }
        }

        // ================= CSV PARSER =================
        function parseCSVRow(row) {
            let result = [], insideQuotes = false, currentVal = '';
            for (let i = 0; i < row.length; i++) {
                let char = row[i];
                if (char === '"') {
                    if (insideQuotes && i + 1 < row.length && row[i+1] === '"') { currentVal += '"'; i++; }
                    else { insideQuotes = !insideQuotes; }
                } else if (char === ',' && !insideQuotes) { result.push(currentVal.trim()); currentVal = ''; }
                else { currentVal += char; }
            }
            result.push(currentVal.trim());
            return result;
        }

        // ================= CARGAR E INYECTAR INVENTARIO =================
        function cargarInventario(isRefresh = false) {
            const tbody = document.getElementById('tableBody');
            const btnReload = document.querySelector('.btn-reload');
            
            let seleccionPrevia = {};
            if (isRefresh) {
                // Feedback visual en el botón
                if (btnReload) {
                    btnReload.innerHTML = '⏳ Actualizando...';
                    btnReload.disabled = true;
                }
                
                // Guardar la selección actual
                document.querySelectorAll('.chk-equipo:checked').forEach(chk => {
                    const codigo = chk.getAttribute('data-codigo');
                    const detalle = chk.getAttribute('data-detalle');
                    const tr = chk.closest('tr');
                    const inputCant = tr.querySelector('.input-cantidad');
                    const cant = inputCant ? inputCant.value : "1";
                    
                    const llaveUnica = codigo !== "" ? codigo : detalle;
                    seleccionPrevia[llaveUnica] = cant;
                });
                
                // Feedback visual en la tabla
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Descargando datos actualizados...</td></tr>';
            }

            // Agregar un timestamp para forzar al navegador a omitir la caché (Cache-Busting)
            const fetchUrl = csvUrl + (csvUrl.includes('?') ? '&' : '?') + 't=' + new Date().getTime();

            fetch(fetchUrl, { cache: 'no-store' }).then(res => res.text()).then(text => {
                tbody.innerHTML = '';
                const filasRaw = text.split(/\r?\n/);
                const filasValidas = [];

                for (let fila of filasRaw) {
                    if (fila.trim().length === 0) continue;
                    const col = parseCSVRow(fila);
                    if (col.join('').trim().length === 0) continue;
                    filasValidas.push(col);
                }

                filasValidas.slice(1).forEach((col) => {
                    const codigo = col[0] ? col[0].replace(/^"|"$/g, '').trim() : '';
                    const detalle = col[1] ? col[1].replace(/^"|"$/g, '').trim() : '';
                    const modelo = col[2] ? col[2].replace(/^"|"$/g, '').trim() : '';
                    const marca = col[3] ? col[3].replace(/^"|"$/g, '').trim() : '';
                    const stock = parseInt(col[4]) || 0;
                    const inSitu = col[6] ? col[6].replace(/^"|"$/g, '').trim().toLowerCase() : '';
                    const disp = col[7] ? col[7].replace(/^"|"$/g, '').trim() : '';
                    
                    if (codigo.toLowerCase() === 'código' || codigo.toLowerCase() === 'codigo') return;
                    if (inSitu === 'sí' || inSitu === 'si') return;

                    const dispNorm = disp.toLowerCase();
                    let badgeClass = 'advertencia';
                    let esSelectable = true;

                    if (dispNorm === 'disponible') {
                        badgeClass = 'disponible';
                    } else if (dispNorm === 'no disponible') {
                        badgeClass = 'no-disponible';
                        esSelectable = false;
                    }
                    
                    const detalleCompleto = `${detalle} (${marca} ${modelo})`;
                    const itemKey = codigo !== "" ? codigo : detalleCompleto;
                    
                    const estabaSeleccionado = isRefresh && seleccionPrevia.hasOwnProperty(itemKey) && esSelectable;
                    const cantidadARestaurar = estabaSeleccionado ? seleccionPrevia[itemKey] : "1";

                    let cantidadHtml = '-';
                    if (esSelectable) {
                        let maxStock = stock > 0 ? stock : 1; 
                        if (maxStock === 1) {
                            cantidadHtml = `<input type="number" class="input-cantidad" value="1" readonly title="Solo 1 unidad permitida">`;
                        } else {
                            cantidadHtml = `<input type="number" class="input-cantidad" value="${cantidadARestaurar}" min="1" max="${maxStock}">`;
                        }
                    }

                    let nombreBuscado = codigo !== "" ? codigo : detalle;
                    let imgSrc = "";
                    let isFallback = false;

                    if (imageMap[nombreBuscado]) {
                        imgSrc = 'img/' + imageMap[nombreBuscado];
                    } else {
                        imgSrc = document.documentElement.getAttribute('data-theme') === 'dark' ? logoClaro : logoOscuro;
                        isFallback = true;
                    }

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="celda-seleccion">
                            <input type="checkbox" class="chk-equipo" ${!esSelectable ? 'disabled' : ''} ${estabaSeleccionado ? 'checked' : ''} data-codigo="${codigo}" data-detalle="${detalleCompleto}" onchange="actualizarEstadoMasterCheckbox()">
                            <img src="${imgSrc}" class="item-thumb ${isFallback ? 'fallback-thumb' : ''}" onclick="abrirLightbox(this.src)" title="Ver imagen">
                        </td>
                        <td>${cantidadHtml}</td>
                        <td>${detalle}</td>
                        <td>${marca} - ${modelo}</td>
                        <td><span class="badge ${badgeClass}">${disp}</span></td>
                    `;
                    tbody.appendChild(tr);
                });

                actualizarEstadoMasterCheckbox();
            }).catch(err => {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Error cargando el archivo CSV.</td></tr>';
            }).finally(() => {
                if (btnReload) {
                    btnReload.innerHTML = '🔄 Actualizar Catálogo';
                    btnReload.disabled = false;
                }
            });
        }

        function actualizarEstadoMasterCheckbox() {
            const master = document.getElementById('uncheckAllBox');
            const sel = document.querySelectorAll('.chk-equipo:checked').length;
            master.disabled = sel === 0; master.checked = sel > 0;
        }
        
        function deseleccionarTodo(m) { 
            if (!m.checked) { 
                document.querySelectorAll('.chk-equipo:checked').forEach(c => c.checked = false); 
                m.disabled = true; 
            } 
        }
        
        function abrirPrevia() {
            const chks = document.querySelectorAll('.chk-equipo:checked');
            if(chks.length === 0) return alert("Selecciona al menos un equipo primero.");
            document.getElementById('previewModal').style.display = 'flex';
            actualizarCuerpo();
        }
        
        function limpiarError(elemento) {
            if(elemento && elemento.value.trim() !== "") {
                elemento.classList.remove('error-input');
            }
        }

        function actualizarCuerpo() {
            const nomInput = document.getElementById('inputNombre');
            const ciInput = document.getElementById('inputCedula');
            const rolSelect = document.getElementById('inputRol');
            const rznInput = document.getElementById('inputRazon');
            const f1Input = document.getElementById('inputFechaInicio');
            const f2Input = document.getElementById('inputFechaFin');
            
            const respNomInput = document.getElementById('inputRespNombre');
            const respRolSelect = document.getElementById('inputRespRol');

            limpiarError(nomInput);
            limpiarError(ciInput);
            limpiarError(rolSelect);
            limpiarError(rznInput);
            limpiarError(f1Input);
            limpiarError(f2Input);
            limpiarError(respNomInput);
            limpiarError(respRolSelect);
            
            localStorage.setItem('uartes_nombre', nomInput.value); 
            localStorage.setItem('uartes_cedula', ciInput.value); 
            if(rolSelect.value) localStorage.setItem('uartes_rol', rolSelect.value); 
            localStorage.setItem('uartes_razon', rznInput.value);
            localStorage.setItem('uartes_resp_nombre', respNomInput.value);
            if(respRolSelect.value) localStorage.setItem('uartes_resp_rol', respRolSelect.value);

            const nom = nomInput.value || "[Tu Nombre]";
            const ci = ciInput.value || "[Tu Cédula]";
            const rol = rolSelect.value || "[Tu Rol]"; 
            const rzn = rznInput.value;
            
            const f1S = f1Input.value ? new Date(f1Input.value).toLocaleString('es-ES', { dateStyle: 'medium', timeStyle: 'short' }) : "[Fecha]";
            const f2S = f2Input.value ? new Date(f2Input.value).toLocaleString('es-ES', { dateStyle: 'medium', timeStyle: 'short' }) : "[Fecha]";
            
            let itemsHTML = '';
            let itemsPlano = '';

            document.querySelectorAll('.chk-equipo:checked').forEach(c => { 
                const tr = c.closest('tr');
                const cant = tr.querySelector('.input-cantidad').value;
                const codigo = c.getAttribute('data-codigo') || '-';
                const detalle = c.getAttribute('data-detalle');

                itemsPlano += `  - ${cant}x ${codigo !== '-' ? `[${codigo}] ` : ''}${detalle}
`;

                itemsHTML += `
                    <tr>
                        <td style="border: 1px solid #dddddd; padding: 8px; text-align: center;">${codigo}</td>
                        <td style="border: 1px solid #dddddd; padding: 8px; text-align: left;">${detalle}</td>
                        <td style="border: 1px solid #dddddd; padding: 8px; text-align: center;">${cant}</td>
                    </tr>
                `;
            });
            
            let bloqueRespPlano = "";
            let bloqueRespHTML = "";
            if (rol === "Estudiante") {
                const rNom = respNomInput.value || "[Nombre del Responsable]";
                const rRol = respRolSelect.value || "[Rol del Responsable]";
                bloqueRespPlano = `\nResponsable: ${rNom} (${rRol})`;
                bloqueRespHTML = `<br><strong>Responsable:</strong> ${rNom} (${rRol})`;
            }

            window.plainTextMsg = `${tecnicoPronombre} ${tecnicoNom},

Por medio de la presente, solicito formalmente el préstamo de los siguientes equipos:

Motivo del préstamo:
  - ${rzn || "[Especificar motivo]"}

Equipos:
${itemsPlano}
Horario Requerido:
  - Desde: ${f1S}
  - Hasta: ${f2S}

Me comprometo a seguir las normativas de uso correspondientes.

Atentamente,
${nom}
Rol: ${rol}
Cédula: ${ci}${bloqueRespPlano}`;

            const htmlMsg = `
                <div style="font-family: Arial, sans-serif; font-size: 14px; color: #222222;">
                    <p>${tecnicoPronombre} ${tecnicoNom},</p>
                    <p>Por medio de la presente, solicito formalmente el préstamo de los siguientes equipos:</p>
                    
                    <p><strong>Motivo del préstamo:</strong><br>${rzn.replace(/\n/g, '<br>') || "[Especificar motivo]"}</p>
                    
                    <p><strong>Equipos:</strong></p>
                    <table style="border-collapse: collapse; width: 100%; max-width: 700px; margin: 15px 0; font-size: 14px; color: #222222;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid #dddddd; padding: 8px; text-align: center; width: 25%;">Código</th>
                                <th style="border: 1px solid #dddddd; padding: 8px; text-align: left; width: 65%;">Detalle</th>
                                <th style="border: 1px solid #dddddd; padding: 8px; text-align: center; width: 10%;">Cant.</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHTML}
                            <tr>
                                <td style="border: 1px solid #dddddd; padding: 8px; text-align: right;"><strong>Fecha de préstamo:</strong></td>
                                <td colspan="2" style="border: 1px solid #dddddd; padding: 8px; text-align: left;">${f1S}</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #dddddd; padding: 8px; text-align: right;"><strong>Fecha de entrega:</strong></td>
                                <td colspan="2" style="border: 1px solid #dddddd; padding: 8px; text-align: left;">${f2S}</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <p>Me comprometo a seguir las normativas de uso correspondientes.</p>
                    
                    <p>Atentamente,<br>
                    <strong>${nom}</strong><br>
                    Rol: ${rol}<br>
                    Cédula: ${ci}${bloqueRespHTML}</p>
                </div>
            `;
            document.getElementById('emailBodyHTML').innerHTML = htmlMsg;
        }
        
        function copiarTexto(t) { navigator.clipboard.writeText(t); alert("Copiado."); }
        
        function copiarCuerpoFormato() {
            const htmlBox = document.getElementById('emailBodyHTML');
            if (navigator.clipboard && window.ClipboardItem) {
                const htmlBlob = new Blob([htmlBox.innerHTML], { type: 'text/html' });
                const textBlob = new Blob([window.plainTextMsg], { type: 'text/plain' });
                const item = new ClipboardItem({ 'text/html': htmlBlob, 'text/plain': textBlob });
                navigator.clipboard.write([item]).then(() => {
                    alert("Cuerpo del correo (con tabla) copiado al portapapeles. Listo para pegar en Outlook o Gmail.");
                }).catch(err => {
                    fallbackCopy(htmlBox);
                });
            } else {
                fallbackCopy(htmlBox);
            }
        }

        function fallbackCopy(element) {
            const range = document.createRange();
            range.selectNodeContents(element);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
            try {
                document.execCommand('copy');
                alert("Cuerpo del correo (con tabla) copiado al portapapeles.");
            } catch (err) {
                alert("Error al copiar. Usa el botón Enviar.");
            }
            sel.removeAllRanges();
        }

        function cerrarModal() { document.getElementById('previewModal').style.display = 'none'; }
        
        function enviarFinal() {
            const rol = document.getElementById('inputRol').value;
            const els = [
                document.getElementById('inputNombre'),
                document.getElementById('inputRol'),
                document.getElementById('inputCedula'),
                document.getElementById('inputRazon'),
                document.getElementById('inputFechaInicio'),
                document.getElementById('inputFechaFin')
            ];

            if (rol === 'Estudiante') {
                els.push(document.getElementById('inputRespNombre'));
                els.push(document.getElementById('inputRespRol'));
            }

            let isValid = true;
            els.forEach(el => {
                if (!el.value.trim()) {
                    el.classList.add('error-input');
                    isValid = false;
                } else {
                    el.classList.remove('error-input');
                }
            });

            if (!isValid) return; 

            const advertencia = confirm("ℹ️ Se abrirá tu aplicación de correo predeterminada para completar el envío.\n\nNOTA: Si usas webmail te sugerimos cancelar y presionar 'Copiar Cuerpo' en su lugar.\n\n¿Deseas continuar?");
            
            if (advertencia) {
                let link = `mailto:${tecnicoMail}?subject=${encodeURIComponent("Solicitud de préstamos de equipos")}&body=${encodeURIComponent(window.plainTextMsg)}`;
                if(copiaMail) link += `&cc=${copiaMail}`;
                window.location.href = link;
            }
        }
        
        function filtrarTabla() {
            const q = document.getElementById('searchInput').value.toUpperCase();
            document.querySelectorAll('#inventoryTable tbody tr').forEach(r => r.style.display = r.innerText.toUpperCase().includes(q) ? '' : 'none');
        }
        
        function ordenarTabla(n) {
            const table = document.getElementById("inventoryTable");
            let rows, switching = true, i, x, y, shouldSwitch, dir = "asc", count = 0;
            while (switching) {
                switching = false; rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false; x = rows[i].getElementsByTagName("TD")[n]; y = rows[i + 1].getElementsByTagName("TD")[n];
                    if ((dir == "asc" && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) || (dir == "desc" && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())) { shouldSwitch = true; break; }
                }
                if (shouldSwitch) { rows[i].parentNode.insertBefore(rows[i + 1], rows[i]); switching = true; count++; }
                else if (count == 0 && dir == "asc") { dir = "desc"; switching = true; }
            }
        }
    </script>
</body>
</html>
