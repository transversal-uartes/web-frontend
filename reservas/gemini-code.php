<?php
// ==========================================
// CONFIGURACIÓN GLOBAL (Entorno AlwaysData)
// ==========================================
$PRONOMBRE_TECNICO = getenv('PRONOMBRE_TECNICO') ?: "Estimado";
$NOMBRE_TECNICO = getenv('NOMBRE_TECNICO') ?: "José A. Medina";
$CORREO_TECNICO = getenv('CORREO_TECNICO') ?: "jose.medina@uartes.edu.ec";
$CORREOS_CC = getenv('CORREOS_CC') ?: "julian.martincez@uartes.edu.ec, yamel.fernandez@uartes.edu.ec";

$AUTOR_SISTEMA = getenv('AUTOR_SISTEMA') ?: "JoeX92 & Gemini Pro";
$GITHUB_URL = getenv('GITHUB_URL') ?: "https://github.com/JoeX92";

// LOGOS (Modo Claro y Oscuro)
$LOGO_LIGHT = getenv('LOGO_LIGHT') ?: "Logo-OFICIAL-RECINTO-LAB-claro.png";
$LOGO_DARK = getenv('LOGO_DARK') ?: "Logo-OFICIAL-RECINTO-LAB-oscuro.png";

// Calcular URL absoluta para la imagen miniatura de WhatsApp/Redes
$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$urlBase = $protocolo . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

// ==========================================
// METADATA PARA COMPARTIR ENLACES
// ==========================================
$META_TITULO = "Reserva de Espacios del Dpto. Transversal - UArtes";
$META_DESC = "Sistema interactivo para la reserva de laboratorios y espacios administrados por el Departamento Transversal de la Universidad de las Artes.";
$META_IMAGEN = $urlBase . $LOGO_LIGHT; // Puedes cambiar esto por una URL de un banner panorámico (ideal 1200x630)

// API Key y Calendarios
$GOOGLE_API_KEY = "AIzaSyDRAcHO33qXh61-Zq--_Y7M15Q8e1HqVH0"; 
$LAB1_NOMBRE = "Mediaco Lab";
$LAB1_CAL_ID = "cb1802fa53c588e1421a005b18aa667c773440f116fbd27b771c77e393f40c09@group.calendar.google.com";
$LAB1_COLOR = "#0056b3";

$LAB2_NOMBRE = "Recinto Lab";
$LAB2_CAL_ID = "5779d81afe39150f21ce001a978054db3bc0864c00f14ef25a405f7361cc9683@group.calendar.google.com";
$LAB2_COLOR = "#8e44ad";

// ==========================================
// MATRIZ DINÁMICA DE LABORATORIOS
// ==========================================
// Puedes añadir o quitar laboratorios de esta lista sin tocar el resto del código.
$LABORATORIOS = [
    [
        "id" => "mediacolab",
        "nombre" => "Mediaco Lab",
        "calendar_id" => "cb1802fa53c588e1421a005b18aa667c773440f116fbd27b771c77e393f40c09@group.calendar.google.com",
        "color" => "#0056b3" // Azul
    ],
    [
        "id" => "recintolab",
        "nombre" => "Recinto Lab",
        "calendar_id" => "5779d81afe39150f21ce001a978054db3bc0864c00f14ef25a405f7361cc9683@group.calendar.google.com",
        "color" => "#8e44ad" // Púrpura
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $META_TITULO; ?></title>

    <!-- Primary Meta Tags -->
    <meta name="title" content="<?php echo $META_TITULO; ?>">
    <meta name="description" content="<?php echo $META_DESC; ?>">
    <meta name="theme-color" content="#c9302c">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $META_TITULO; ?>">
    <meta property="og:description" content="<?php echo $META_DESC; ?>">
    <meta property="og:image" content="<?php echo $META_IMAGEN; ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?php echo $META_TITULO; ?>">
    <meta property="twitter:description" content="<?php echo $META_DESC; ?>">
    <meta property="twitter:image" content="<?php echo $META_IMAGEN; ?>">
    
    <!-- Librerías de FullCalendar v6 -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    
    <style>
        /* ================= VARIABLES DE TEMA ================= */
        :root {
            --primary: #c9302c;
            --bg-color: #f4f6f9;
            --text-color: #333;
            --border-color: #ddd;
            --card-bg: #ffffff;
            --input-bg: #ffffff;
            --tab-bg: #e9ecef;
            --tab-hover: #dde0e3;
            --btn-text: #555;
            --info-bg: #e2f3f5;
            --info-text: #0c5460;
            --info-border: #17a2b8;
        }

        body.dark-mode {
            --bg-color: #121212;
            --text-color: #f1f1f1;
            --card-bg: #1e1e1e;
            --border-color: #444;
            --input-bg: #2a2a2a;
            --tab-bg: #2d2d2d;
            --tab-hover: #3d3d3d;
            --btn-text: #ddd;
            --info-bg: #113339;
            --info-text: #b8e6ec;
            --info-border: #17a2b8;
            
            /* FullCalendar Variables Nativas */
            --fc-page-bg-color: #1e1e1e;
            --fc-neutral-bg-color: #2d2d2d;
            --fc-neutral-text-color: #f1f1f1;
            --fc-border-color: #444;
            --fc-today-bg-color: rgba(255, 255, 255, 0.05);
        }

        /* LAYOUT ESTRUCTURAL FIJO (No scroll de página) */
        body { 
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            background-color: var(--bg-color); 
            margin: 0; 
            padding: 20px; 
            color: var(--text-color); 
            transition: background-color 0.3s, color 0.3s;
            height: 100dvh; /* Usa 100dvh en móviles modernos, fallback a vh */
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Corta el scroll a nivel de página entera */
        }
        
        /* ================= HEADER Y LOGO ================= */
        .header { display: flex; justify-content: space-between; align-items: center; background: var(--card-bg); padding: 15px 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; transition: background-color 0.3s; flex-wrap: wrap; gap: 15px; flex-shrink: 0;}
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header-left h1 { margin: 0; font-size: 1.5rem; color: var(--primary); }
        .header-right { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; justify-content: center;}
        
        .logo { height: 45px; width: auto; object-fit: contain; }
        body.dark-mode .logo-light { display: none !important; }
        body.dark-mode .logo-dark { display: block !important; }
        body:not(.dark-mode) .logo-light { display: block !important; }
        body:not(.dark-mode) .logo-dark { display: none !important; }
        
        #btn-theme, #btn-help, #btn-refresh { background: transparent; border: 1px solid var(--border-color); color: var(--text-color); font-size: 1.2rem; padding: 8px 12px; border-radius: 5px; cursor: pointer; transition: 0.2s; }
        #btn-theme:hover, #btn-help:hover, #btn-refresh:hover { background: var(--tab-hover); }

        /* ================= CUADRO DE INFORMACIÓN ================= */
        .info-box {
            background-color: var(--info-bg);
            border-left: 4px solid var(--info-border);
            color: var(--info-text);
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            position: relative;
            font-size: 0.95rem;
            line-height: 1.5;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }
        .info-box p { margin: 5px 0 0 0; }
        .close-info {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.5rem;
            cursor: pointer;
            font-weight: bold;
            opacity: 0.7;
        }
        .close-info:hover { opacity: 1; }

        /* ================= TABS ================= */
        .tab-menu { display: flex; gap: 5px; background: var(--tab-bg); padding: 5px; border-radius: 8px; flex-wrap: wrap; justify-content: center;}
        .tab-btn { padding: 8px 15px; border: none; background: transparent; border-radius: 5px; font-size: 0.95rem; font-weight: bold; color: var(--btn-text); cursor: pointer; transition: all 0.2s; }
        .tab-btn:hover { background: var(--tab-hover); }
        .tab-btn.active { background: var(--card-bg); color: var(--primary); box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        /* ================= CONTENEDOR DEL CALENDARIO ================= */
        #calendar-container { 
            background: var(--card-bg); 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
            transition: background-color 0.3s;
            flex: 1; /* Permite que tome todo el espacio restante */
            min-height: 0; /* Evita que el contenedor crezca más de la cuenta en flex */
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Forzamos a que FullCalendar maneje el scroll interno */
        }

        #calendar {
            flex: 1; /* El calendario toma todo el espacio de la tarjeta blanca */
            min-height: 0;
        }

        /* ================= CALENDARIO CUSTOMS ================= */
        /* Cursor en Celdas */
        .fc-timegrid-slot, .fc-daygrid-day-frame, .fc-event { cursor: pointer !important; }
        
        /* Indicador de Hora Actual (Línea Roja) */
        .fc-now-indicator-line { border-color: #ea4335 !important; border-width: 2px !important; z-index: 10 !important; }
        .fc-now-indicator-arrow { border-color: #ea4335 !important; border-width: 6px !important; margin-top: -5px !important; }

        /* LÓGICA DE FILTRADO CSS GENERADO DINÁMICAMENTE */
        <?php foreach ($LABORATORIOS as $lab): ?>
            #calendar-container[data-active-tab="<?php echo $lab['id']; ?>"] .lab-event:not(.<?php echo $lab['id']; ?>) { display: none !important; }
        <?php endforeach; ?>

        /* ================= TOAST NOTIFICATIONS ================= */
        .toast-container {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none; /* Que no bloqueen clics */
        }

        .toast {
            background-color: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            text-align: center;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* ================= ELEMENTOS FLOTANTES ================= */
        #btn-flotante-reservar { position: fixed; bottom: 30px; right: 30px; background-color: #28a745; color: white; border: none; padding: 15px 25px; font-size: 1.1rem; font-weight: bold; border-radius: 50px; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4); cursor: pointer; display: none; z-index: 1000; transition: transform 0.2s; }
        #btn-flotante-reservar:hover { transform: scale(1.05); }
        
        #btn-flotante-limpiar { position: fixed; bottom: 30px; left: 30px; background-color: #dc3545; color: white; border: none; padding: 15px 25px; font-size: 1.1rem; font-weight: bold; border-radius: 50px; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4); cursor: pointer; display: none; z-index: 1000; transition: transform 0.2s; }
        #btn-flotante-limpiar:hover { transform: scale(1.05); }

        /* ================= MODAL AVANZADO ================= */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: none; justify-content: center; align-items: center; z-index: 2000; }
        .modal-content { background: var(--card-bg); color: var(--text-color); padding: 30px; border-radius: 10px; width: 95%; max-width: 1100px; height: 85vh; display: flex; flex-direction: column; box-shadow: 0 5px 20px rgba(0,0,0,0.3); position: relative; }
        .close-btn { position: absolute; top: 15px; right: 20px; font-size: 1.8rem; cursor: pointer; color: #888; z-index: 10; }
        .close-btn:hover { color: var(--primary); }
        
        .modal-grid { display: flex; gap: 30px; height: 100%; overflow: hidden; margin-top: 10px; }
        .form-section { flex: 1; overflow-y: auto; padding-right: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.95rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; background: var(--input-bg); color: var(--text-color); border: 1px solid var(--border-color); border-radius: 5px; box-sizing: border-box; outline: none; transition: 0.3s; }
        .form-row { display: flex; gap: 15px; }
        .form-row > .form-group { flex: 1; }
        .error-input { border: 2px solid #dc3545 !important; box-shadow: 0 0 5px rgba(220, 53, 69, 0.3); }

        .preview-section { flex: 1.2; display: flex; flex-direction: column; background: var(--bg-color); border-radius: 8px; border: 1px solid var(--border-color); overflow: hidden; }
        .preview-header-box { padding: 15px; border-bottom: 1px solid var(--border-color); background: var(--card-bg); }
        .preview-header-box p { margin: 5px 0; font-size: 0.95rem; display: flex; justify-content: space-between; align-items: center; }
        .copy-btn-small { background: var(--tab-bg); color: var(--text-color); border: 1px solid var(--border-color); padding: 3px 8px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; }
        .copy-btn-small:hover { background: var(--tab-hover); }
        .preview-body { flex: 1; padding: 20px; overflow-y: auto; background: var(--card-bg); font-size: 0.95rem; color: var(--text-color); }
        
        .action-buttons { display: flex; gap: 10px; padding: 15px; background: var(--card-bg); border-top: 1px solid var(--border-color); }
        .action-buttons button { flex: 1; padding: 12px; font-size: 1.05rem; font-weight: bold; border: none; border-radius: 5px; cursor: pointer; }
        .btn-copy-body { background: #6c757d; color: white; }
        .btn-send-app { background: var(--primary); color: white; }
        
        .mail-table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 0.9rem; }
        .mail-table th, .mail-table td { border: 1px solid #999; padding: 8px; text-align: left; }
        .mail-table th { font-weight: bold; background-color: var(--tab-bg); color: var(--text-color); }
        
        .creditos { position: fixed; bottom: 10px; right: 15px; font-size: 0.75rem; color: #888; z-index: 100; }
        .creditos a { color: #888; text-decoration: none; font-weight: bold; }

        @media (max-width: 900px) {
            .header-right { width: 100%; justify-content: space-between; flex-wrap: nowrap; gap: 8px; overflow: hidden; }
            
            /* ================= AJUSTES GLOBALES PARA MÓVILES ================= */
            body { font-size: 14px; padding: 10px; } /* Reduce fuente general y márgenes */
            .header { padding: 10px 15px; }
            .header-left h1 { font-size: 1.15rem; }
            .logo { height: 35px; }
            
            /* ================= MENÚ DE PESTAÑAS SCROLLABLE ================= */
            .tab-menu { 
                flex: 1; 
                flex-wrap: nowrap; 
                overflow-x: auto; 
                justify-content: flex-start; 
                -webkit-overflow-scrolling: touch; /* Desplazamiento suave en iOS */
                scrollbar-width: none; /* Oculta scrollbar en Firefox */
                -ms-overflow-style: none; /* Oculta scrollbar en IE/Edge */
            }
            .tab-menu::-webkit-scrollbar { display: none; } /* Oculta scrollbar en Chrome/Safari */
            
            .tab-btn { 
                padding: 6px 10px; 
                font-size: 0.85rem; 
                flex-shrink: 0; /* Evita que el texto se achique o se deforme */
                white-space: nowrap; /* Evita que el texto salte a otra línea */
            }
            
            .info-box { font-size: 0.85rem; padding: 10px; margin-bottom: 10px; }

            .modal-grid { flex-direction: column; overflow-y: auto; }
            .form-section { overflow-y: visible; padding-right: 0; }
            .preview-section { overflow-y: visible; height: auto; min-height: 400px; }
            .form-row { flex-direction: column; gap: 0; }
            #btn-flotante-limpiar { width: 40%; bottom: 0; left: 0; border-radius: 0; padding: 20px; font-size: 1rem; }
            #btn-flotante-reservar { width: 60%; bottom: 0; right: 0; border-radius: 0; padding: 20px; font-size: 1rem; }
            .creditos { display: none; }
            
            /* ================= AJUSTES DE CALENDARIO PARA MÓVILES ================= */
            #calendar { font-size: 0.75rem; } /* Reduce el tamaño base del calendario */
            .fc .fc-toolbar-title { font-size: 1.1rem !important; } /* Título del mes más pequeño */
            .fc .fc-button { padding: 0.3rem 0.5rem !important; font-size: 0.8rem !important; } /* Botones de navegación compactos */
            .fc-timegrid-slot-label-cushion { font-size: 0.7rem !important; } /* Horas laterales más pequeñas */
            .fc-col-header-cell-cushion { font-size: 0.75rem !important; padding: 4px 2px !important; } /* Días de la semana compactos */
            .fc-event-title, .fc-event-time { font-size: 0.7rem !important; line-height: 1.1 !important; } /* Texto dentro de las reservas */
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-left">
            <img src="<?php echo $LOGO_LIGHT; ?>" class="logo logo-light" alt="Logo UArtes">
            <img src="<?php echo $LOGO_DARK; ?>" class="logo logo-dark" alt="Logo UArtes" style="display: none;">
            <h1>Reserva de Espacios Transversales</h1>
        </div>
        
        <div class="header-right">
            <div class="tab-menu">
                <button class="tab-btn active" data-tab-id="todos" onclick="cambiarLab('todos', this, 'Todos')">Todos</button>
                <!-- Generación Dinámica de Pestañas PHP -->
                <?php foreach ($LABORATORIOS as $lab): ?>
                    <button class="tab-btn" data-tab-id="<?php echo $lab['id']; ?>" onclick="cambiarLab('<?php echo $lab['id']; ?>', this, '<?php echo htmlspecialchars($lab['nombre']); ?>')">
                        <?php echo htmlspecialchars($lab['nombre']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <button id="btn-refresh" onclick="refrescarCalendario()" title="Actualizar datos de Google Calendar">🔄</button>
            <button id="btn-help" onclick="mostrarInfoBox()" title="Mostrar Instrucciones">❓</button>
            <button id="btn-theme" onclick="toggleTheme()" title="Cambiar Tema">🌙</button>
        </div>
    </div>

    <!-- Cuadro de Información Instructiva -->
    <div id="info-box" class="info-box">
        <span class="close-info" onclick="cerrarInfoBox()" title="Cerrar este aviso">&times;</span>
        <strong>ℹ️ ¿Cómo realizar una reserva?</strong>
        <p>
            1. Selecciona el <strong>Laboratorio específico</strong> en la parte superior.<br>
            2. <strong>Selección libre:</strong> Haz clic, arrastra o mantén presionado (en celulares) para agregar franjas de horario. Todo lo que elijas se irá sumando a tu reserva.<br>
            3. Puedes <strong>modificar tu selección arrastrándola o estirando sus bordes</strong>, o hacer clic sobre ella para borrarla.<br>
            4. Para <strong>borrar toda tu selección</strong> de golpe, presiona el botón rojo inferior izquierdo.
        </p>
    </div>

    <!-- Contenedor con data-active-tab para controlar la visibilidad CSS -->
    <div id="calendar-container" data-active-tab="todos">
        <div id="calendar"></div>
    </div>

    <button id="btn-flotante-limpiar" onclick="limpiarSeleccionesTemp()">
        🗑️ Borrar
    </button>
    <button id="btn-flotante-reservar" onclick="abrirModalReserva()">
        📝 Reservar (<span id="contador-espacios">0</span>)
    </button>

    <!-- Contenedor para Toast Notifications -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Modal de Reserva -->
    <div class="modal-overlay" id="modal-reserva">
        <div class="modal-content">
            <span class="close-btn" onclick="cerrarModal('modal-reserva')">&times;</span>
            <h2 style="margin-top: 0;">Solicitud de Reserva</h2>
            
            <div class="modal-grid">
                <div class="form-section">
                    <div class="form-group"><label>Nombre Completo</label><input type="text" id="nombre" oninput="quitarError(this); actualizarCuerpo();"></div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Rol</label>
                            <select id="rol" onchange="quitarError(this); actualizarCuerpo();">
                                <option value="">Seleccione...</option><option value="Estudiante">Estudiante</option><option value="Docente">Docente</option><option value="Administrativo">Administrativo</option>
                            </select>
                        </div>
                        <div class="form-group"><label>Cédula</label><input type="text" id="cedula" oninput="quitarError(this); actualizarCuerpo();"></div>
                    </div>
                    <div class="form-group">
                        <label>Motivo de la Reserva (Proyecto/Materia)</label>
                        <textarea id="motivo" rows="4" oninput="quitarError(this); actualizarCuerpo();"></textarea>
                    </div>
                </div>

                <div class="preview-section">
                    <div class="preview-header-box">
                        <p><strong>Para:</strong> <?php echo $CORREO_TECNICO; ?> <button class="copy-btn-small" onclick="copiarTextoSimple('<?php echo $CORREO_TECNICO; ?>', this)">Copiar</button></p>
                        <p><strong>CC:</strong> <?php echo $CORREOS_CC; ?> <button class="copy-btn-small" onclick="copiarTextoSimple('<?php echo $CORREOS_CC; ?>', this)">Copiar</button></p>
                        <p><strong>Asunto:</strong> <span id="prev-asunto">Solicitud de Reserva de Espacios</span> <button class="copy-btn-small" onclick="copiarTextoSimple(document.getElementById('prev-asunto').innerText, this)">Copiar</button></p>
                    </div>
                    <div id="prev-cuerpo" class="preview-body"></div>
                    <div class="action-buttons">
                        <button class="btn-copy-body" onclick="copiarCuerpoHTML(this)">Copiar</button>
                        <button class="btn-send-app" onclick="enviarViaApp()">Enviar vía App</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Info Eventos -->
    <div class="modal-overlay" id="modal-info" style="z-index: 3000;">
        <div class="modal-content" style="max-width: 400px; height: auto;">
            <span class="close-btn" onclick="cerrarModal('modal-info')">&times;</span>
            <h2 id="info-titulo" style="margin-top:0;">Detalle de Reserva</h2>
            <p><strong>Espacio:</strong> <span id="info-lab" style="color: var(--primary); font-weight:bold;"></span></p>
            <p><strong>Inicio:</strong> <span id="info-inicio"></span></p>
            <p><strong>Fin:</strong> <span id="info-fin"></span></p>
        </div>
    </div>

    <div class="creditos">
        Creado por <strong><?php echo $AUTOR_SISTEMA; ?></strong> | <a href="<?php echo $GITHUB_URL; ?>" target="_blank">Ver en GitHub</a>
    </div>

    <script>
        // ================= CONFIGURACIÓN DE TEMA =================
        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
            let isDark = document.body.classList.contains('dark-mode');
            document.getElementById('btn-theme').innerText = isDark ? '☀️' : '🌙';
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }

        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
            document.getElementById('btn-theme').innerText = '☀️';
        }

        // ================= LOGICA INFO BOX =================
        function mostrarInfoBox() {
            let box = document.getElementById('info-box');
            box.style.display = 'block';
            // Pequeño retardo para que la transición CSS de opacidad funcione
            setTimeout(() => { box.style.opacity = '1'; }, 10);
            localStorage.setItem('ocultarInfoBoxReservas', 'false');
        }

        function cerrarInfoBox() {
            let box = document.getElementById('info-box');
            box.style.opacity = '0';
            setTimeout(() => {
                box.style.display = 'none';
            }, 300);
            localStorage.setItem('ocultarInfoBoxReservas', 'true');
        }

        if (localStorage.getItem('ocultarInfoBoxReservas') === 'true') {
            document.getElementById('info-box').style.display = 'none';
            document.getElementById('info-box').style.opacity = '0';
        }

        // ================= TOAST NOTIFICATIONS =================
        function showToast(mensaje) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerText = mensaje;
            
            container.appendChild(toast);
            
            // Forzar reflow para que corra la transición de CSS
            void toast.offsetWidth; 
            toast.classList.add('show');

            // Eliminar después de 3.5 segundos
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 300); // Tiempo de la transición CSS
            }, 3500);
        }

        // ================= TRANSFERENCIA DE DATOS PHP -> JS =================
        const labsConfig = <?php echo json_encode($LABORATORIOS); ?>;
        
        let selectedSlots = [];
        let tempEventCounter = 0;
        let calendar;
        let labGlobalSeleccionado = 'Todos';

        document.addEventListener('DOMContentLoaded', function() {
            cargarDatosGuardados();

            // ================= LECTURA DE URL HASH (AL INICIAR) =================
            let hash = window.location.hash.substring(1);
            let vistaInicial = 'timeGridWeek';
            let fechaInicial = null; // null usa la fecha de hoy por defecto
            let labInicialId = 'todos';

            if (hash) {
                if (hash.includes('=')) {
                    // Formato nuevo: #lab=X&view=Y&date=Z
                    let params = new URLSearchParams(hash);
                    if (params.has('view')) vistaInicial = params.get('view');
                    if (params.has('date')) fechaInicial = params.get('date');
                    if (params.has('lab')) labInicialId = params.get('lab');
                } else {
                    // Compatibilidad con los enlaces viejos (solo #lab_mediaco)
                    labInicialId = hash;
                }
            }

            // Activar la pestaña correcta en la interfaz
            setTimeout(() => {
                let tabObjetivo = document.querySelector(`.tab-btn[data-tab-id="${labInicialId}"]`);
                if (tabObjetivo) tabObjetivo.click();
            }, 50);

            // ================= CONEXIÓN DIRECTA A GOOGLE CALENDAR API =================
            const fuentesDeCalendario = labsConfig.map(lab => ({
                id: lab.id,
                events: function(fetchInfo, successCallback, failureCallback) {
                    // Preparamos la URL de la API nativa de Google
                    let timeMin = fetchInfo.start.toISOString();
                    let timeMax = fetchInfo.end.toISOString();
                    let url = `https://www.googleapis.com/calendar/v3/calendars/${encodeURIComponent(lab.calendar_id)}/events?key=<?php echo $GOOGLE_API_KEY; ?>&timeMin=${timeMin}&timeMax=${timeMax}&singleEvents=true&maxResults=2500`;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                console.error('Error Google API:', data.error);
                                showToast("⚠️ Error de conexión con Google. Revisa tu API_KEY o el ID del Calendario.");
                                failureCallback(data.error);
                                return;
                            }
                            
                            let eventosParseados = data.items.map(item => {
                                let isAllDay = item.start.date ? true : false;
                                let colorPersonalizado = null;
                                
                                // Mapeo oficial de los 11 colores de Google Calendar
                                if (item.colorId) {
                                    const googleColors = {
                                        '1': '#7986cb', // Lavanda
                                        '2': '#33b679', // Salvia
                                        '3': '#8e24aa', // Uva
                                        '4': '#e67c73', // Flamenco
                                        '5': '#f6bf26', // Plátano
                                        '6': '#f4511e', // Mandarina
                                        '7': '#039be5', // Pavo real
                                        '8': '#616161', // Grafito
                                        '9': '#3f51b5', // Arándano
                                        '10': '#0b8043', // Albahaca
                                        '11': '#d50000'  // Tomate
                                    };
                                    colorPersonalizado = googleColors[item.colorId];
                                }

                                return {
                                    id: item.id,
                                    title: item.summary || 'Ocupado',
                                    start: item.start.dateTime || item.start.date,
                                    end: item.end.dateTime || item.end.date,
                                    allDay: isAllDay,
                                    // Si el evento tiene color personalizado lo usa, si no, usa el predeterminado del Lab
                                    backgroundColor: colorPersonalizado || lab.color,
                                    borderColor: colorPersonalizado || lab.color,
                                    extendedProps: {
                                        lab: lab.nombre,
                                        isDraft: false,
                                        description: item.description
                                    }
                                };
                            });
                            successCallback(eventosParseados);
                        })
                        .catch(err => {
                            console.error('Fetch Error:', err);
                            showToast("⚠️ Error de red al intentar conectar con Google Calendar.");
                            failureCallback(err);
                        });
                }
            }));

            var calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                height: '100%', 
                firstDay: 1, 
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek' 
                },
                initialView: vistaInicial, // Carga la vista de la URL o la predeterminada
                initialDate: fechaInicial || new Date(), // Carga la fecha de la URL o Hoy
                slotMinTime: '06:00:00',
                slotMaxTime: '22:00:00',
                slotLabelFormat: { hour: 'numeric', minute: '2-digit', omitZeroMinute: false, meridiem: 'short' },
                allDaySlot: true, 
                locale: 'es',
                selectable: true,
                selectMirror: false,
                unselectAuto: true,
                nowIndicator: true, 

                longPressDelay: 300, 
                selectLongPressDelay: 300,
                
                eventSources: fuentesDeCalendario,

                // Actualizar la URL cada vez que el usuario cambie de mes, semana o día
                datesSet: function(info) {
                    actualizarURLHash();
                },

                // Asignar clases dinámicas basadas en la fuente para el CSS de Pestañas
                eventClassNames: function(arg) {
                    let classes = ['lab-event'];
                    if (arg.event.extendedProps.isDraft) {
                        classes.push('draft-event');
                        // Asignar el ID del lab al borrador para que también se oculte en otras pestañas
                        let labId = labsConfig.find(l => l.nombre === arg.event.extendedProps.lab)?.id;
                        if(labId) classes.push(labId);
                    } else if (arg.event.source) {
                        classes.push('is-real-event');
                        classes.push(arg.event.source.id);
                    }
                    return classes;
                },

                select: function(info) {
                    if (labGlobalSeleccionado === 'Todos') {
                        showToast("Por favor, selecciona un laboratorio específico en las pestañas superiores para reservar.");
                        calendar.unselect();
                        return;
                    }

                    // Restricción de tiempo real
                    let now = new Date();
                    if (info.allDay) {
                        let today = new Date();
                        today.setHours(0,0,0,0);
                        if (info.start < today) {
                            showToast("No puedes reservar espacios en fechas pasadas.");
                            calendar.unselect();
                            return;
                        }
                    } else {
                        if (info.start < now) {
                            showToast("No puedes reservar espacios en horarios que ya pasaron.");
                            calendar.unselect();
                            return;
                        }
                    }

                    // Identificar si es un arrastre de múltiples días en vista de semana
                    let startDateObj = new Date(info.start.getFullYear(), info.start.getMonth(), info.start.getDate());
                    let adjustedEnd = new Date(info.end.getTime() - 1);
                    let endDateObj = new Date(adjustedEnd.getFullYear(), adjustedEnd.getMonth(), adjustedEnd.getDate());
                    let diffDays = Math.round((endDateObj - startDateObj) / (1000 * 60 * 60 * 24));

                    if (!info.allDay && diffDays > 0) {
                        // DIVIDIR EN MÚLTIPLES DÍAS (Comportamiento de arrastre horizontal en la cuadrícula)
                        let sMins = info.start.getHours() * 60 + info.start.getMinutes();
                        let eMins;
                        if (info.end.getHours() === 0 && info.end.getMinutes() === 0 && info.end.getTime() > info.start.getTime()) {
                            eMins = 24 * 60;
                        } else {
                            eMins = info.end.getHours() * 60 + info.end.getMinutes();
                        }

                        let startMins = Math.min(sMins, eMins);
                        let endMins = Math.max(sMins, eMins);

                        for (let i = 0; i <= diffDays; i++) {
                            let currDay = new Date(startDateObj);
                            currDay.setDate(currDay.getDate() + i);

                            let s = new Date(currDay);
                            s.setHours(Math.floor(startMins / 60), startMins % 60, 0, 0);

                            let e = new Date(currDay);
                            if (endMins === 1440) {
                                e.setDate(e.getDate() + 1);
                                e.setHours(0, 0, 0, 0);
                            } else {
                                e.setHours(Math.floor(endMins / 60), endMins % 60, 0, 0);
                            }

                            // Evitar que un bloque caiga en el pasado si el arrastre incluye días anteriores
                            if (s < now) continue; 

                            tempEventCounter++;
                            let nuevoDraft = {
                                id: 'draft_' + tempEventCounter,
                                title: 'Tu Selección (' + labGlobalSeleccionado + ')',
                                start: s, 
                                end: e,
                                allDay: false,
                                backgroundColor: '#28a745', 
                                borderColor: '#218838',
                                editable: true, // <-- PERMITE ARRASTRAR Y REDIMENSIONAR EL BLOQUE
                                extendedProps: { isDraft: true, lab: labGlobalSeleccionado, allDay: false }
                            };

                            calendar.addEvent(nuevoDraft);
                            selectedSlots.push(nuevoDraft);
                        }
                    } else {
                        // SELECCIÓN NORMAL (Un solo día o Todo el día)
                        tempEventCounter++;
                        
                        let nuevoDraft = {
                            id: 'draft_' + tempEventCounter,
                            title: 'Tu Selección (' + labGlobalSeleccionado + ')',
                            start: info.startStr,
                            end: info.endStr,
                            allDay: info.allDay,
                            backgroundColor: '#28a745', 
                            borderColor: '#218838',
                            editable: true, // <-- PERMITE ARRASTRAR Y REDIMENSIONAR EL BLOQUE
                            extendedProps: { isDraft: true, lab: labGlobalSeleccionado, allDay: info.allDay }
                        };

                        calendar.addEvent(nuevoDraft);
                        selectedSlots.push(nuevoDraft);
                    }

                    calendar.unselect(); 
                    actualizarBotonFlotante();
                },

                // Lógica de Modificación (Al arrastrar el bloque a otra hora o día)
                eventDrop: function(info) {
                    if (!info.event.extendedProps.isDraft) {
                        info.revert(); // Evita mover eventos reales de Google
                        return;
                    }
                    actualizarDraftModificado(info.event);
                },

                // Lógica de Redimensión (Al estirar el bloque para hacerlo más largo/corto)
                eventResize: function(info) {
                    if (!info.event.extendedProps.isDraft) {
                        info.revert(); // Evita alterar eventos reales de Google
                        return;
                    }
                    actualizarDraftModificado(info.event);
                },

                eventClick: function(info) {
                    info.jsEvent.preventDefault(); 
                    if (info.event.extendedProps.isDraft) {
                        // Ahora borramos usando estrictamente el ID en caso de que se haya modificado o arrastrado
                        let aEliminar = selectedSlots.filter(s => s.id === info.event.id);

                        aEliminar.forEach(slot => {
                            let ev = calendar.getEventById(slot.id);
                            if(ev) ev.remove();
                            // Quitamos el elemento eliminado de la matriz global
                            selectedSlots = selectedSlots.filter(item => item.id !== slot.id);
                        });
                        actualizarBotonFlotante();
                    } else {
                        // Resolución dinámica del nombre del laboratorio
                        let nombreDelLab = "Desconocido";
                        if (info.event.source) {
                            let labEncontrado = labsConfig.find(l => l.id === info.event.source.id);
                            if (labEncontrado) nombreDelLab = labEncontrado.nombre;
                        }
                        
                        document.getElementById('info-titulo').innerText = info.event.title || 'Espacio Ocupado';
                        document.getElementById('info-lab').innerText = nombreDelLab;
                        document.getElementById('info-inicio').innerText = info.event.start.toLocaleString('es-ES');
                        document.getElementById('info-fin').innerText = info.event.end ? info.event.end.toLocaleString('es-ES') : '';
                        document.getElementById('modal-info').style.display = 'flex';
                    }
                }
            });

            calendar.render();
        });

        // ================= LOGICA DE PESTAÑAS Y HASH =================
        function actualizarURLHash() {
            if (!calendar) return;
            let vistaActual = calendar.view.type;
            
            // Obtener fecha actual en vista (YYYY-MM-DD local)
            let d = calendar.getDate();
            let fechaActual = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
            
            let tabActivo = document.querySelector('.tab-btn.active');
            let labActual = tabActivo ? tabActivo.getAttribute('data-tab-id') : 'todos';

            let newHash = `lab=${labActual}&view=${vistaActual}&date=${fechaActual}`;
            
            if (window.history.replaceState) {
                window.history.replaceState(null, null, '#' + newHash);
            }
        }

        function cambiarLab(tabId, btnElement, nombreLab) {
            let botones = document.querySelectorAll('.tab-btn');
            botones.forEach(b => b.classList.remove('active'));
            btnElement.classList.add('active');
            
            labGlobalSeleccionado = nombreLab;
            
            // Este atributo data es lo único que cambia. El CSS ocultará los laboratorios que no coincidan.
            document.getElementById('calendar-container').setAttribute('data-active-tab', tabId);

            // Actualiza la URL incluyendo la nueva pestaña
            actualizarURLHash();
        }

        function refrescarCalendario() {
            if (calendar) {
                showToast("Sincronizando con Google Calendar...");
                calendar.refetchEvents(); // Fuerza la recarga desde Google
            }
        }

        // ================= FUNCIONES DE APOYO Y DATOS =================
        function actualizarDraftModificado(event) {
            // Actualiza la memoria interna si el usuario arrastra o estira el bloque
            let slotIndex = selectedSlots.findIndex(s => s.id === event.id);
            if (slotIndex !== -1) {
                selectedSlots[slotIndex].start = event.start;
                selectedSlots[slotIndex].end = event.end;
                selectedSlots[slotIndex].allDay = event.allDay;
                if (selectedSlots[slotIndex].extendedProps) {
                    selectedSlots[slotIndex].extendedProps.allDay = event.allDay;
                }
            }
        }

        function limpiarSeleccionesTemp() {
            selectedSlots.forEach(slot => { let ev = calendar.getEventById(slot.id); if(ev) ev.remove(); });
            selectedSlots = [];
            actualizarBotonFlotante();
        }

        function actualizarBotonFlotante() {
            let btnRes = document.getElementById('btn-flotante-reservar');
            let btnLim = document.getElementById('btn-flotante-limpiar');
            let contador = document.getElementById('contador-espacios');
            if (selectedSlots.length > 0) { 
                contador.innerText = selectedSlots.length; 
                btnRes.style.display = 'block'; 
                btnLim.style.display = 'block'; 
            } else { 
                btnRes.style.display = 'none'; 
                btnLim.style.display = 'none'; 
            }
        }

        function abrirModalReserva() {
            document.getElementById('modal-reserva').style.display = 'flex';
            actualizarCuerpo(); 
        }

        function cerrarModal(idModal) { document.getElementById(idModal).style.display = 'none'; }
        function quitarError(elemento) { elemento.classList.remove('error-input'); }

        function cargarDatosGuardados() {
            if (localStorage.getItem('reserva_nombre')) document.getElementById('nombre').value = localStorage.getItem('reserva_nombre');
            if (localStorage.getItem('reserva_rol')) document.getElementById('rol').value = localStorage.getItem('reserva_rol');
            if (localStorage.getItem('reserva_cedula')) document.getElementById('cedula').value = localStorage.getItem('reserva_cedula');
            
            // Nos aseguramos explícitamente de que el motivo comience vacío
            let txtMotivo = document.getElementById('motivo');
            if (txtMotivo) txtMotivo.value = ''; 
        }

        function guardarDatos() {
            localStorage.setItem('reserva_nombre', document.getElementById('nombre').value);
            localStorage.setItem('reserva_rol', document.getElementById('rol').value);
            localStorage.setItem('reserva_cedula', document.getElementById('cedula').value);
            // El motivo no se guarda intencionalmente
        }

        function formatFechaFormal(dateStr) {
            let parts = dateStr.split('-');
            let d = new Date(parts[0], parts[1]-1, parts[2]);
            return d.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        function esConsecutivo(fechasStr) {
            if (fechasStr.length <= 1) return true;
            for (let i = 1; i < fechasStr.length; i++) {
                let p1 = fechasStr[i-1].split('-'); let d1 = new Date(p1[0], p1[1]-1, p1[2]);
                let p2 = fechasStr[i].split('-');   let d2 = new Date(p2[0], p2[1]-1, p2[2]);
                let diffDays = Math.round(Math.abs(d2 - d1) / (1000 * 60 * 60 * 24));
                if (diffDays !== 1) return false;
            }
            return true;
        }

        function agruparSlots() {
            let map = {};
            selectedSlots.forEach(s => {
                let lab = s.extendedProps.lab;
                let isAllDay = s.extendedProps.allDay || s.allDay || false; 
                let startDate = new Date(s.start);
                let endDate = s.end ? new Date(s.end) : new Date(startDate.getTime() + 60*60*1000);

                let dates = [];
                let curr = new Date(startDate.getTime()); curr.setHours(0,0,0,0);
                let endD = new Date(endDate.getTime()); 
                
                if (isAllDay) {
                    endD.setHours(0,0,0,0);
                    while (curr < endD) {
                        dates.push(`${curr.getFullYear()}-${String(curr.getMonth()+1).padStart(2,'0')}-${String(curr.getDate()).padStart(2,'0')}`);
                        curr.setDate(curr.getDate() + 1);
                    }
                    if(dates.length === 0) dates.push(`${curr.getFullYear()}-${String(curr.getMonth()+1).padStart(2,'0')}-${String(curr.getDate()).padStart(2,'0')}`);
                } else {
                    dates.push(`${startDate.getFullYear()}-${String(startDate.getMonth()+1).padStart(2,'0')}-${String(startDate.getDate()).padStart(2,'0')}`);
                }
                
                let key, horaInicio, horaFin;
                if (isAllDay) {
                    horaInicio = "Todo el día"; horaFin = ""; key = `${lab}_allday`;
                } else {
                    horaInicio = startDate.toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'});
                    horaFin = endDate.toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'});
                    key = `${lab}_${horaInicio}_${horaFin}`;
                }
                
                if (!map[key]) map[key] = { lab: lab, isAllDay: isAllDay, horaInicio, horaFin, fechas: new Set() };
                dates.forEach(d => map[key].fechas.add(d));
            });

            let grupos = [];
            for (let k in map) {
                let g = map[k];
                g.fechas = Array.from(g.fechas).sort();
                grupos.push(g);
            }
            return grupos;
        }

        // ================= FUNCIONES PARA ENLACES DE GOOGLE CALENDAR =================
        function formatearFechaGCal(fechaStr, isAllDay) {
            let d = new Date(fechaStr);
            let year = d.getFullYear();
            let month = String(d.getMonth() + 1).padStart(2, '0');
            let day = String(d.getDate()).padStart(2, '0');
            if (isAllDay) {
                return `${year}${month}${day}`; // Formato Todo el Día: YYYYMMDD
            }
            let hours = String(d.getHours()).padStart(2, '0');
            let minutes = String(d.getMinutes()).padStart(2, '0');
            let seconds = String(d.getSeconds()).padStart(2, '0');
            return `${year}${month}${day}T${hours}${minutes}${seconds}`; // Formato Hora: YYYYMMDDTHHMMSS
        }

        function generarLinkGCal(slot, nombre, rol, cedula, motivo) {
            let isAllDay = slot.extendedProps.allDay || slot.allDay || false;
            let startGcal = formatearFechaGCal(slot.start, isAllDay);
            let endGcal = slot.end ? formatearFechaGCal(slot.end, isAllDay) : formatearFechaGCal(new Date(new Date(slot.start).getTime() + 3600000), isAllDay);
            
            let titulo = `Reserva ${slot.extendedProps.lab} - ${nombre}`;
            let detalles = `Solicitante: ${nombre}\nCédula: ${cedula}\nRol: ${rol}\n\nMotivo / Proyecto:\n${motivo}`;
            
            return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(titulo)}&dates=${startGcal}/${endGcal}&details=${encodeURIComponent(detalles)}&location=${encodeURIComponent(slot.extendedProps.lab)}`;
        }

        // ================= GENERADOR DE CORREO =================
        function actualizarCuerpo() {
            guardarDatos();
            let nombre = document.getElementById('nombre').value || '[Tu Nombre]';
            let rol = document.getElementById('rol').value || '[Tu Rol]';
            let cedula = document.getElementById('cedula').value || '[Tu Cédula]';
            let motivo = document.getElementById('motivo').value || '[Motivo de la solicitud]';
            let pronombre = "<?php echo $PRONOMBRE_TECNICO; ?>";
            let nombreTecnico = "<?php echo $NOMBRE_TECNICO; ?>";

            let htmlCuerpo = `<p>${pronombre} ${nombreTecnico},</p>
            <p>Por medio de la presente, solicito la reserva de los siguientes espacios en los laboratorios.</p>
            <p><strong>Motivo/Proyecto:</strong><br>${motivo.replace(/\n/g, '<br>')}</p>
            
            <table class="mail-table">
                <thead>
                    <tr>
                        <th>Laboratorio</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha final</th>
                        <th>Hora inicio</th>
                        <th>Hora final</th>
                    </tr>
                </thead>
                <tbody>`;

            let grupos = agruparSlots();
            grupos.forEach(g => {
                let len = g.fechas.length;
                let isCons = esConsecutivo(g.fechas);
                
                htmlCuerpo += `<tr><td>${g.lab}</td>`;
                
                // Lógica de celdas para las Fechas
                if (len === 1) {
                    htmlCuerpo += `<td colspan="2" style="text-align:center;">${formatFechaFormal(g.fechas[0])}</td>`;
                } else if (isCons) {
                    htmlCuerpo += `<td>${formatFechaFormal(g.fechas[0])}</td><td>${formatFechaFormal(g.fechas[len-1])}</td>`;
                } else {
                    htmlCuerpo += `<td colspan="2" style="text-align:center;">${g.fechas.map(f => formatFechaFormal(f)).join('<br>')}</td>`;
                }
                
                // Lógica de celdas para las Horas
                if (g.isAllDay) {
                    htmlCuerpo += `<td colspan="2" style="text-align:center;">Todo el día</td>`;
                } else {
                    htmlCuerpo += `<td>${g.horaInicio}</td><td>${g.horaFin}</td>`;
                }
                htmlCuerpo += `</tr>`;
            });

            htmlCuerpo += `</tbody></table>
            <p><strong>DATOS DEL SOLICITANTE:</strong><br>
            Nombre: ${nombre}<br>Rol: ${rol}<br>Cédula: ${cedula}</p>
            
            <br><p><strong>📅 Enlaces rápidos (Uso exclusivo del técnico):</strong></p><ul>`;
            
            // Inyectamos un enlace directo a Google Calendar por cada bloque de reserva
            selectedSlots.forEach(s => {
                let link = generarLinkGCal(s, nombre, rol, cedula, motivo);
                let isAllDay = s.extendedProps.allDay || s.allDay || false;
                let fechaLabel = new Date(s.start).toLocaleDateString('es-ES', {weekday: 'short', day: '2-digit', month: 'short'});
                let horaLabel = isAllDay ? "Todo el día" : `${new Date(s.start).toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})} a ${new Date(s.end).toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})}`;
                
                htmlCuerpo += `<li><a href="${link}" target="_blank">Añadir <strong>${s.extendedProps.lab}</strong> el ${fechaLabel} (${horaLabel})</a></li>`;
            });

            htmlCuerpo += `</ul><p>Quedo a la espera de la confirmación.<br>Saludos cordiales.</p>`;

            document.getElementById('prev-cuerpo').innerHTML = htmlCuerpo;
        }

        function generarCuerpoTextoPlano() {
            let nombre = document.getElementById('nombre').value;
            let rol = document.getElementById('rol').value;
            let cedula = document.getElementById('cedula').value;
            let motivo = document.getElementById('motivo').value;
            let pronombre = "<?php echo $PRONOMBRE_TECNICO; ?>";
            let nombreTecnico = "<?php echo $NOMBRE_TECNICO; ?>";

            // Usamos \n de forma estándar para texto plano
            let texto = `${pronombre} ${nombreTecnico},\n\n`;
            texto += `Por medio de la presente, solicito la reserva de los siguientes espacios:\n\n`;
            texto += `Motivo/Proyecto:\n${motivo}\n\n`;
            
            let grupos = agruparSlots();
            grupos.forEach(g => {
                texto += `[${g.lab}]\n`;
                let len = g.fechas.length;
                let isCons = esConsecutivo(g.fechas);
                
                if (!g.isAllDay) {
                    if (len === 1) texto += `- ${formatFechaFormal(g.fechas[0])} de ${g.horaInicio} a ${g.horaFin}\n`;
                    else if (isCons) texto += `- Del ${formatFechaFormal(g.fechas[0])} al ${formatFechaFormal(g.fechas[len-1])} de ${g.horaInicio} a ${g.horaFin}\n`;
                    else {
                        texto += `- De ${g.horaInicio} a ${g.horaFin} los días:\n`;
                        g.fechas.forEach(f => { texto += `  - ${formatFechaFormal(f)}\n`; });
                    }
                } else {
                    if (len === 1) texto += `- ${formatFechaFormal(g.fechas[0])} todo el día.\n`;
                    else if (isCons) texto += `- Del ${formatFechaFormal(g.fechas[0])} al ${formatFechaFormal(g.fechas[len-1])} todo el día.\n`;
                    else {
                        texto += `- Los días (Todo el día):\n`;
                        g.fechas.forEach(f => { texto += `  - ${formatFechaFormal(f)}\n`; });
                    }
                }
                texto += `\n`;
            });

            texto += `DATOS DEL SOLICITANTE:\n`;
            texto += `Nombre: ${nombre}\nRol: ${rol}\nCédula: ${cedula}\n\n`;
            
            texto += `📅 ENLACES RÁPIDOS PARA AGREGAR A CALENDAR (Uso del técnico):\n`;
            selectedSlots.forEach(s => {
                let link = generarLinkGCal(s, nombre, rol, cedula, motivo);
                let isAllDay = s.extendedProps.allDay || s.allDay || false;
                let fechaLabel = new Date(s.start).toLocaleDateString('es-ES', {weekday: 'short', day: '2-digit', month: 'short'});
                let horaLabel = isAllDay ? "Todo el día" : `${new Date(s.start).toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})} a ${new Date(s.end).toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})}`;
                
                texto += `- Añadir ${s.extendedProps.lab} | ${fechaLabel} (${horaLabel}):\n  ${link}\n\n`;
            });

            return texto;
        }

        // ================= ACCIONES FINALES =================
        function copiarTextoSimple(texto, botonElement) {
            navigator.clipboard.writeText(texto).then(() => {
                let originalText = botonElement.innerText;
                botonElement.innerText = "¡Copiado!";
                setTimeout(() => { botonElement.innerText = originalText; }, 2000);
            });
        }

        function copiarCuerpoHTML(botonElement) {
            let contenido = document.getElementById('prev-cuerpo').innerHTML;
            const blobHtml = new Blob([contenido], { type: "text/html" });
            const blobText = new Blob([document.getElementById('prev-cuerpo').innerText], { type: "text/plain" });
            const data = [new ClipboardItem({ "text/html": blobHtml, "text/plain": blobText })];
            
            navigator.clipboard.write(data).then(() => {
                let originalText = botonElement.innerText;
                botonElement.innerText = "¡Tabla Copiada!";
                setTimeout(() => { botonElement.innerText = originalText; }, 2000);
            });
        }

        function enviarViaApp() {
            let idsAValidar = ['nombre', 'cedula', 'rol', 'motivo'];
            let todoValido = true;

            idsAValidar.forEach(id => {
                let el = document.getElementById(id);
                if (!el.value.trim()) { el.classList.add('error-input'); todoValido = false; }
            });

            if (!todoValido) return;

            if(confirm("Se abrirá tu aplicación de correo predeterminada. Asegúrate de enviarlo desde tu correo institucional.")){
                let asunto = document.getElementById('prev-asunto').innerText;
                let cuerpoTexto = generarCuerpoTextoPlano();
                let cc = "<?php echo $CORREOS_CC; ?>";
                let destino = "<?php echo $CORREO_TECNICO; ?>";
                
                // Usamos encodeURIComponent universal para garantizar que los enlaces GCal no rompan el correo
                window.location.href = `mailto:${destino}?cc=${cc}&subject=${encodeURIComponent(asunto)}&body=${encodeURIComponent(cuerpoTexto)}`;
                cerrarModal('modal-reserva');
            }
        }
    </script>
</body>
</html>