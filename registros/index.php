<?php
// ==========================================
// CAPTURA DE IP DEL DISPOSITIVO
// ==========================================
$ip_publica = '';
if (isset($_SERVER['HTTP_CLIENT_IP'])) {
    $ip_publica = $_SERVER['HTTP_CLIENT_IP'];
} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_publica = $_SERVER['HTTP_X_FORWARDED_FOR'];
} elseif (isset($_SERVER['REMOTE_ADDR'])) {
    $ip_publica = $_SERVER['REMOTE_ADDR'];
}
$ip_publica = explode(',', $ip_publica)[0]; 

// ==========================================
// VARIABLES GLOBALES DE CONFIGURACIÓN (SEGURAS)
// ==========================================
$NOMBRE_SISTEMA      = "Laboratorios Dpto. Transversal";
$MENSAJE_BIENVENIDA  = "Selecciona tu acción y escanea tu carnet UArtes.";
$HORA_REFRESCO       = "06:00"; 

// 💡 SEGURIDAD MÁXIMA PARA GITHUB: Cero contraseñas o URLs quemadas en el código
// El servidor (Alwaysdata) DEBE proveer estas variables. Si no existen, quedan vacías.
$ADMIN_SECRET  = getenv('ADMIN_SECRET') ?: (isset($_ENV['ADMIN_SECRET']) ? $_ENV['ADMIN_SECRET'] : ""); 
$API_URL       = getenv('API_URL') ?: (isset($_ENV['API_URL']) ? $_ENV['API_URL'] : "/api/kiosk"); // Por defecto usa ruta relativa si fallan las variables

$esPantallaOficial   = (isset($_GET['kiosk']) && $ADMIN_SECRET !== "" && $_GET['kiosk'] === $ADMIN_SECRET);
$modoTecnicoID       = isset($_GET['tecnico']) ? trim($_GET['tecnico']) : "";

$LABORATORIOS = ["Mediaco (MZ14, 1er Piso)", "RecintoLab (MZ14, 4to Piso)"]; // , "Palacios 206 (Gobernación, 2do Piso)"];
$ACTIVIDADES  = ["Prácticas", "Desarrollo de proyecto de clase", "Reserva", "Tutorías", "Clase guiada", "Experimentación", "Otro"];
$AREAS        = ["EAS", "Producción Musical", "Artes Musicales", "EAE", "Teatro", "Danza", "Artes Visuales", "EAI", "Literatura", "Cine", "Transversal", "Vicerrectorado Académico", "Lenguas Extranjeras", "Biblioteca", "TICS", "Comunicación", "Telégrafo", "Otro"];
$ROLES        = ["Estudiante", "Pasante", "Alumni", "Docente", "Administrativo"];

function generarOpcionesTiempo() {
    $html = "";
    for ($h = 0; $h <= 8; $h++) {
        for ($m = 0; $m < 60; $m += 15) {
            if ($h == 0 && $m == 0) continue;
            if ($h == 8 && $m > 0) break;
            $val = sprintf("%02d:%02d", $h, $m);
            $textParts = [];
            if ($h == 1) $textParts[] = "1 hora"; elseif ($h > 1) $textParts[] = "$h horas";
            if ($m > 0) $textParts[] = "$m minutos";
            $text = implode(" y ", $textParts);
            $selected = ($val == "02:00") ? "selected" : ""; 
            $html .= "<option value=\"$val\" $selected>$text</option>\n";
        }
    }
    return $html;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kiosco - <?php echo $NOMBRE_SISTEMA; ?></title>
    
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    
    <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
    <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
    
    <script src="https://unpkg.com/turndown/dist/turndown.js"></script>
    <script src="https://unpkg.com/turndown-plugin-gfm/dist/turndown-plugin-gfm.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; background-color: #f4f4f9; padding: 20px; color: #111;}
        .kiosco-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: relative; border: 2px solid #ddd;}
        h1 { color: #000; margin-bottom: 5px; font-weight: 900; letter-spacing: -0.5px;}
        
        #subtitulo { background: #ffc107; padding: 12px; border-radius: 8px; color: #000; font-size: 18px; font-weight: 900; text-transform: uppercase; border: 2px solid #d39e00; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px;}
        label { font-weight: 900; margin-top: 20px; display: block; color: #000; text-transform: uppercase; font-size: 15px; background: #e9ecef; padding: 10px; border-radius: 4px; border-left: 6px solid #007bff; margin-bottom: 8px; text-align: left;}
        
        select, input[type="text"], input[type="password"], textarea { width: 100%; padding: 14px; font-size: 18px; border: 2px solid #888; border-radius: 6px; box-sizing: border-box; font-weight: bold; color: #000; background-color: #fff;}
        select:focus, input:focus, textarea:focus { border-color: #007bff; outline: none; box-shadow: 0 0 8px rgba(0,123,255,0.4); }
        
        p { font-weight: bold; color: #222; }

        .btn { padding: 15px 30px; font-size: 18px; font-weight: 900; margin: 10px 0; cursor: pointer; border: none; border-radius: 8px; color: white; transition: transform 0.1s, box-shadow 0.2s; width: 100%; text-transform: uppercase;}
        .btn:active { transform: scale(0.97); }
        .btn-entrada { background-color: #28a745; box-shadow: 0 5px 15px rgba(40,167,69,0.3); }
        .btn-salida { background-color: #dc3545; box-shadow: 0 5px 15px rgba(220,53,69,0.3); }
        .btn-prestamo { background-color: #007bff; box-shadow: 0 5px 15px rgba(0,123,255,0.3); }
        
        .btn-editar { background-color: #868e96; box-shadow: 0 4px 10px rgba(134, 142, 150, 0.3); margin-top: 20px; font-size: 15px; padding: 12px 20px; }
        .btn-editar:hover { background-color: #6c757d; }
        
        .btn-siguiente { background-color: #007bff; box-shadow: 0 5px 15px rgba(0,123,255,0.3); margin-top: 25px; }
        .btn-atras { background-color: #6c757d; margin-top: 15px; }
        .btn-warning { background-color: #ffc107; color: #000; margin-top: 15px; }
        .btn-info { background-color: #17a2b8; margin-top: 15px; }
        .btn-foto { background-color: #17a2b8; margin-top: 15px; }
        
        .formulario { display: none; text-align: left; background: #f9f9f9; padding: 25px; border-radius: 8px; margin-top: 20px; border: 1px solid #ddd;}
        .input-oculto { display: none; margin-top: 10px; border-color: #007bff !important; }
        select:disabled { background-color: #e9ecef; cursor: not-allowed; color: #6c757d; border-color: #ccc;}
        
        #qr-container { margin-top: 25px; display: none; background: #fff; padding: 15px; border-radius: 8px; border: 2px solid #ddd; }
        #reader { width: 100%; max-width: 400px; margin: 10px auto; border-radius: 8px; overflow: hidden; background: #000; position: relative; cursor: pointer; border: 4px solid #333;}
        #reader video { object-fit: cover; width: 100%; }
        
        .focus-ring { position: absolute; border: 3px solid #00ffcc; border-radius: 50%; width: 60px; height: 60px; pointer-events: none; box-shadow: 0 0 15px rgba(0,255,204,0.6); animation: focusAnim 0.5s ease-out forwards; z-index: 10; }
        @keyframes focusAnim { 0% { transform: scale(1.5); opacity: 0; } 50% { transform: scale(1); opacity: 1; } 100% { transform: scale(1); opacity: 0; } }

        .manual-entry { margin-top: 30px; display: none; border-top: 3px dashed #ccc; padding-top: 20px;}
        #mensaje { margin-top: 20px; font-weight: 900; font-size: 20px; min-height: 30px; padding: 10px; border-radius: 6px;}
        .msg-success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb;} 
        .msg-error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb;} 
        .msg-info { color: #004085; background-color: #cce5ff; border: 1px solid #b8daff;} 
        .msg-warning { color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba;}

        .banner-pin { background-color: #222; color: #00ffcc; padding: 15px; border-radius: 8px; font-size: 28px; font-weight: 900; letter-spacing: 3px; margin-bottom: 25px; text-transform: uppercase; border: 2px solid #00ffcc;}
        .caja-opcion { background: white; padding: 20px; border-radius: 8px; margin-top: 20px; border: 2px solid #ccc; }
        .bloque-qr { margin-top: 35px; padding-top: 25px; border-top: 3px dashed #ccc; }
        .bloque-qr img { width: 100%; max-width: 200px; border-radius: 12px; box-shadow: 0 6px 15px rgba(0,0,0,0.2); margin-top: 15px; border: 3px solid #fff;}
        .dato-confirmacion { font-size: 18px; color: #000; margin: 8px 0; font-weight: bold;}
        
        .btn-flotante { position: fixed; bottom: 20px; right: 20px; background-color: #222; color: white; border: 2px solid #555; border-radius: 50%; width: 60px; height: 60px; font-size: 26px; cursor: pointer; box-shadow: 0 6px 15px rgba(0,0,0,0.4); z-index: 1000; display: flex; align-items: center; justify-content: center; opacity: 0.6; transition: opacity 0.3s, transform 0.2s; }
        .btn-flotante:hover { opacity: 1; transform: scale(1.1); }
        
        .markdown-view { background: white; border: 2px solid #ccc; padding: 15px; border-radius: 6px; margin-top: 5px; font-size: 15px; max-height: 400px; overflow-y: auto;}
        .markdown-view img { max-width: 100%; border-radius: 4px; } .markdown-view ul { margin: 5px 0; padding-left: 20px; }
        
        .editor-tabs { display: flex; position: relative; z-index: 10; margin-top: 15px; }
        .editor-tab { padding: 10px 20px; background: #e9ecef; border: 2px solid #888; border-bottom: 2px solid #888; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 900; color: #555; margin-right: 5px; font-size: 14px; text-transform: uppercase; transition: all 0.2s; }
        .editor-tab.active { background: #fff; color: #007bff; border-bottom: 2px solid #fff; margin-bottom: -2px; padding-bottom: 12px; }
        .editor-tab:hover:not(.active) { background: #ddd; }
        
        #wrap-preview { background: #fff; border: 2px solid #888; border-radius: 0 8px 8px 8px; padding: 15px; min-height: 235px; text-align: left; overflow-y: auto; font-size: 15px; margin-top: 0; box-sizing: border-box; }
        #wrap-preview img { max-width: 100%; border-radius: 4px; }
        #wrap-preview ul { margin: 5px 0; padding-left: 20px; }
        
        .CodeMirror { 
            height: 220px !important;       
            min-height: 150px !important;   
            max-height: none !important;    
            resize: vertical !important;    
            font-family: inherit; 
            border-radius: 0 0 8px 8px !important; 
            border: 2px solid #888 !important; 
            border-top: none !important; 
        }
        .editor-toolbar { border-radius: 0 8px 0 0 !important; border: 2px solid #888 !important; background: #e9ecef !important; opacity: 1 !important; }
        
        .modo-tecnico-badge { display: none; background: #dc3545; color: #fff; padding: 8px 20px; border-radius: 30px; font-size: 16px; font-weight: 900; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(220,53,69,0.4); text-transform: uppercase; border: 2px solid #a71d2a;}
        
        .time-picker-row { display: flex; align-items: center; gap: 15px; margin-top: 5px; }
        .time-picker-row select { flex: 1; font-size: 20px; text-align: center; border-color: #007bff; background: #e8f4ff; color: #0056b3; cursor: pointer;}
    </style>
</head>
<body>

<?php if(!$esPantallaOficial): ?>
    <button id="btnFullscreen" class="btn-flotante" onclick="toggleFullScreen()" title="Pantalla Completa">⛶</button>
<?php endif; ?>

<div class="kiosco-container">
    
    <?php if($esPantallaOficial): ?>
        <div id="badgeTecnico" class="modo-tecnico-badge"><i class="fa-solid fa-user-shield"></i> TÉCNICO AUTORIZADO: <span id="nombreTecnicoBadge"></span></div>
        <div class="banner-pin">🔑 PIN DEL DÍA: <strong id="pin-display">...</strong></div>
    <?php endif; ?>

    <h1>📍 <?php echo $NOMBRE_SISTEMA; ?></h1>
    <p id="subtitulo"><?php echo $MENSAJE_BIENVENIDA; ?></p>

    <div id="botones-accion">
        <button class="btn btn-entrada" onclick="mostrarFormularioEntrada()">Registrar Entrada</button>
        <button class="btn btn-salida" onclick="iniciarEscaneo('salida')">Registrar Salida</button>
        
        <button class="btn btn-editar" onclick="iniciarEscaneo('editar_perfil')">✏️ Editar mi información personal</button>

        <?php if($esPantallaOficial): ?>
            <div style="margin: 30px 0;"></div>
            <button class="btn btn-prestamo" onclick="abrirMenuPrestamos()">📋 Gestión de Préstamos</button>
            <?php if($modoTecnicoID === ""): ?>
            <div class="bloque-qr" id="bloqueQrInicio">
                <p style="color: #000; font-size: 18px; font-weight: 900; margin: 0; text-transform: uppercase;">📲 Usa tu celular escaneando este QR</p>
                <img src="QR.jpeg" alt="Código QR de Registro">
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- 1. ENTRADA NORMAL LABORATORIO -->
    <div id="detallesEntrada" class="formulario">
        <?php if(!$esPantallaOficial): ?>
            <label style="border-left-color: #dc3545; background-color: #f8d7da; color: #721c24;">PIN del Laboratorio (Ver pantalla):</label>
            <input type="text" id="inputPin" placeholder="Ej. A1B2" style="text-transform: uppercase;">
        <?php endif; ?>
        
        <label>¿En qué laboratorio estarás?</label>
        <select id="selectLab" onchange="verificarOtro('selectLab', 'inputLabOtro')">
            <option value="" disabled selected>-- SELECCIONA EL LABORATORIO --</option>
            <?php foreach($LABORATORIOS as $lab): ?><option value="<?php echo $lab; ?>"><?php echo $lab; ?></option><?php endforeach; ?>
        </select>
        <input type="text" id="inputLabOtro" class="input-oculto" placeholder="Escribe el nombre del espacio...">
        
        <label>¿Qué actividad realizarás?</label>
        <select id="selectActividad" onchange="verificarOtro('selectActividad', 'inputActividadOtro')">
            <option value="" disabled selected>-- SELECCIONA TU ACTIVIDAD --</option>
            <?php foreach($ACTIVIDADES as $act): ?><option value="<?php echo $act; ?>"><?php echo $act; ?></option><?php endforeach; ?>
        </select>
        <input type="text" id="inputActividadOtro" class="input-oculto" placeholder="Escribe tu actividad...">
        
        <label style="border-left-color: #28a745; background-color: #d4edda; color: #155724;">¿Cuánto tiempo prevés quedarte?</label>
        <div class="time-picker-row">
            <i class="fa-solid fa-hourglass-half" style="font-size: 28px; color: #28a745;"></i>
            <select id="inputTiempoEstimado" style="border-color: #28a745; background: #e8f5e9; color: #155724;">
                <?php echo generarOpcionesTiempo(); ?>
            </select>
        </div>
        <p style="font-size: 13px; color: #444; margin-top: 10px; font-weight: bold;">* El sistema cerrará tu registro automáticamente al cumplirse este tiempo si olvidas hacerlo.</p>

        <button class="btn btn-siguiente" onclick="confirmarDetalles()">Siguiente 📸</button>
        <button class="btn btn-atras" onclick="cancelar()">[ Atrás ] Volver al inicio</button>
    </div>

    <!-- 2. MENÚ PRÉSTAMOS E INVENTARIO -->
    <div id="menuPrestamos" class="formulario">
        <label>Selecciona ID de Acta de Préstamo (AP ID):</label>
        <select id="selectAp" onchange="cambioSeleccionAp()">
            <option value="" disabled selected>CARGANDO DATOS...</option>
        </select>
        
        <div id="contenedorNuevoAp" style="display:none; background: #e9ecef; padding: 20px; border-radius: 8px; margin-top: 15px; border: 2px solid #ccc;">
            <label style="margin-top:0;">ID del Acta de Préstamo:</label>
            <input type="text" id="nuevoApId" placeholder="Ej. AP-2026-001">
            <label style="margin-bottom: 0;">Descripción del Equipo / Documentos:</label>
            
            <div class="editor-tabs">
                <div id="tabCodigo" class="editor-tab active" onclick="switchEditorTab('codigo')"><i class="fa-solid fa-code"></i> Editor Markdown</div>
                <div id="tabPreview" class="editor-tab" onclick="switchEditorTab('preview')"><i class="fa-solid fa-eye"></i> Vista Previa</div>
            </div>
            
            <div id="wrap-codigo">
                <textarea id="editor-container"></textarea>
            </div>
            
            <div id="wrap-preview" style="display:none;"></div>
            
            <button class="btn btn-entrada" id="btnGuardarAp" style="width:100%; margin-top:20px;" onclick="guardarNuevoAp()">Guardar Nuevo AP</button>
        </div>

        <div id="contenedorInfoAp" style="display:none;">
            <label>Información del Equipo:</label>
            <div id="apInfoView" class="markdown-view"></div>
            
            <button class="btn btn-info" id="btnEditarAp" style="width:100%; margin-top:10px; display:none;" onclick="iniciarEdicionAp()">✏️ Editar Detalles del AP</button>
            <button class="btn btn-entrada" id="btnRegistrarRecepcion" style="width:100%; margin-top:20px; display:none;" onclick="iniciarFlujoPrestamo('recepcion')">1️⃣ Registrar Recepción</button>
            <button class="btn btn-warning" id="btnRegistrarDevolucion" style="width:100%; margin-top:20px; display:none;" onclick="iniciarFlujoPrestamo('devolucion')">2️⃣ Registrar Devolución</button>
            <p id="msgApCompletado" style="display:none; color: #155724; background: #d4edda; padding: 10px; border: 2px solid #c3e6cb; border-radius: 6px; font-weight: 900; text-align: center; margin-top: 20px;">✅ ESTA ACTA DE PRÉSTAMO YA ESTÁ COMPLETADA.</p>
        </div>
        <button class="btn btn-atras" onclick="cancelar()">[ Atrás ] Volver al inicio</button>
    </div>

    <!-- ÁREA DEL ESCÁNER -->
    <div id="qr-container">
        <div id="camera-controls"><select id="cameraSelect" onchange="cambiarCamara()"></select></div>
        <div id="reader"></div>
        <button class="btn btn-foto" onclick="forzarCapturaFrame()"><i class="fa-solid fa-camera"></i> Forzar captura de QR<br>(Autofocus fallido)</button>
    </div>
    <div id="hidden-reader" style="display:none;"></div>

    <div class="manual-entry" id="manualEntry">
        <p id="txtManualEntry" style="color: #dc3545; font-size: 18px;">¿OLVIDASTE TU CARNET O NO LEE EL QR?<br>INGRESA TU C.I. MANUALMENTE:</p>
        <input type="text" id="codigoManual" placeholder="Ej. 0912345678" onkeypress="if(event.key === 'Enter') procesarEscaneo(document.getElementById('codigoManual').value)">
        <button class="btn btn-siguiente" style="padding: 12px 20px;" onclick="procesarEscaneo(document.getElementById('codigoManual').value)">Enviar C.I.</button>
        <br><br><button class="btn btn-atras" onclick="cancelar()">[ Atrás ] Cancelar proceso</button>
    </div>

    <!-- PANTALLAS EXTRAS -->
    <div id="confirmarUsuario" class="formulario">
        <h3 style="color: #007bff; text-align: center; margin-top:0; font-weight: 900; font-size: 24px;">¡HOLA DE NUEVO, <br><span id="confNombre" style="color:#000;"></span>!</h3>
        <p style="text-align: center; font-size: 16px;">Por favor, verifica que tus datos sigan siendo correctos:</p>
        <div class="caja-opcion" style="text-align: center; background: #e8f4ff; border-color: #007bff;">
            <p class="dato-confirmacion"><b>ID/CARNET:</b> <span id="confIdVisual" style="font-family: monospace; font-size: 20px; background: #fff; padding: 4px 10px; border-radius: 6px; border: 2px solid #ccc;"></span></p>
            <p class="dato-confirmacion" style="margin-top: 15px;"><b>ÁREA/CARRERA:</b> <span id="confArea"></span></p>
            <p class="dato-confirmacion"><b>ROL:</b> <span id="confRol"></span></p>
        </div>
        <button id="btnConfirmarUsuario" class="btn btn-entrada" style="width: 100%; margin-top: 25px;" onclick="confirmarYEntrar()">SÍ, TODO ESTÁ BIEN. CONTINUAR 🚀</button>
        <button class="btn btn-warning" onclick="abrirActualizacion()">ACTUALIZAR MIS DATOS ✏️</button>
        <button class="btn btn-atras" onclick="cancelar()">[ Atrás ] Cancelar proceso</button>
    </div>

    <div id="registroNuevoUsuario" class="formulario">
        <h3 id="tituloRegistro" style="color: #dc3545; text-align: center; margin-top:0; font-weight: 900; font-size: 24px;">¡HOLA! ES TU PRIMERA VEZ AQUÍ.</h3>
        <label>Nombre Completo:</label>
        <input type="text" id="inputNombre">
        <label>Área o Carrera:</label>
        <select id="selectArea" onchange="verificarOtro('selectArea', 'inputAreaOtro')">
            <option value="" disabled selected>-- SELECCIONA TU ÁREA --</option>
            <?php foreach($AREAS as $area): ?><option value="<?php echo $area; ?>"><?php echo $area; ?></option><?php endforeach; ?>
        </select>
        <input type="text" id="inputAreaOtro" class="input-oculto" placeholder="Escribe tu área o carrera...">
        <label>Rol:</label>
        <select id="selectRol">
            <option value="" disabled selected>-- SELECCIONA TU ROL --</option>
            <?php foreach($ROLES as $rol): ?><option value="<?php echo $rol; ?>"><?php echo $rol; ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn-entrada" id="btnGuardarUsuario" style="width: 100%; margin-top: 25px;" onclick="guardarUsuarioYEntrar()">GUARDAR INFORMACIÓN</button>
        <button class="btn btn-atras" onclick="cancelar()">[ Atrás ] Cancelar</button>
    </div>

    <div id="mensaje"></div>
    
    <button id="btnReintentar" class="btn btn-warning" style="display:none; padding: 15px;">🔄 ERROR DE RED. REINTENTAR</button>
    <button id="btnMensajeVolver" class="btn btn-atras" style="display:none; padding: 15px;" onclick="cancelar()">[ Atrás ] VOLVER AL INICIO</button>
</div>

<script>
    const URL_NODE = "<?php echo $API_URL; ?>"; // 💡 Se carga seguro desde PHP
    const ES_PANTALLA_OFICIAL = <?php echo $esPantallaOficial ? 'true' : 'false'; ?>;
    const HORA_REFRESCO = "<?php echo $HORA_REFRESCO; ?>";
    const MODO_TECNICO_ID = "<?php echo $modoTecnicoID; ?>";
    
    const IP_PUBLICA = "<?php echo $ip_publica; ?>";
    let IP_LOCAL = "Buscando...";
    let IP_COMBINADA = IP_PUBLICA; 

    let pasteFormatEnabled = true;

    function obtenerIpLocal() {
        window.RTCPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;
        if (!window.RTCPeerConnection) return;
        let pc = new RTCPeerConnection({ iceServers: [] }); let noop = function() {};
        pc.createDataChannel(''); pc.createOffer(pc.setLocalDescription.bind(pc), noop);
        pc.onicecandidate = function(ice) {
            if (ice && ice.candidate && ice.candidate.candidate) {
                let miIp = /([0-9]{1,3}(\.[0-9]{1,3}){3})/.exec(ice.candidate.candidate);
                if (miIp && miIp.length > 1) { IP_LOCAL = miIp[1]; IP_COMBINADA = IP_LOCAL + ", " + IP_PUBLICA; pc.onicecandidate = noop; }
            }
        };
    }
    obtenerIpLocal();

    let accionActual = ''; let datosEntrada = {}; let usuarioIdActual = ''; let tecnicoIdActual = '';
    let datosUsuarioCache = null; let esActualizacion = false; 
    let wakeLock = null; let resetTimer = null; 
    let html5QrCode = null; let hiddenQrScanner = null;
    let listaPrestamosActivos = []; let apIdSeleccionado = ''; let tipoPrestamoEnProceso = ''; 
    let tecnicoVerificado = false;
    
    let autoConfirmTimer = null; 
    let autoConfirmInterval = null; 
    let timeLeftConf = 5;

    function switchEditorTab(tab) {
        if(tab === 'codigo') {
            document.getElementById('tabCodigo').classList.add('active');
            document.getElementById('tabPreview').classList.remove('active');
            document.getElementById('wrap-codigo').style.display = 'block';
            document.getElementById('wrap-preview').style.display = 'none';
        } else {
            document.getElementById('tabPreview').classList.add('active');
            document.getElementById('tabCodigo').classList.remove('active');
            document.getElementById('wrap-codigo').style.display = 'none';
            document.getElementById('wrap-preview').style.display = 'block';
            document.getElementById('wrap-preview').innerHTML = marked.parse(easymde.value());
        }
    }

    // 💡 MOTOR DE RED: Simplificado, seguro y envía JSON puro
    function intentarFetch(payload, resolve, reject) {
        document.getElementById('btnReintentar').style.display = 'none';
        document.getElementById('btnMensajeVolver').style.display = 'none';
        
        let timerDemora = setTimeout(() => {
            document.getElementById('btnReintentar').style.display = 'inline-block';
            document.getElementById('btnReintentar').onclick = () => {
                clearTimeout(timerDemora);
                mostrarMensaje("🔄 Forzando reconexión...", "msg-info");
                intentarFetch(payload, resolve, reject);
            };
        }, 5000);

        const controllerNode = new AbortController();
        const timeoutNode = setTimeout(() => controllerNode.abort(), 8000); 

        // 💡 Envia JSON puro al único backend Node.js
        fetch(URL_NODE, { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload), 
            signal: controllerNode.signal 
        })
        .then(res => res.json())
        .then(data => {
            clearTimeout(timeoutNode);
            clearTimeout(timerDemora);
            document.getElementById('btnReintentar').style.display = 'none';
            resolve(data);
        })
        .catch(err => {
            clearTimeout(timeoutNode);
            clearTimeout(timerDemora);
            console.error("Error del servidor/red:", err);
            mostrarMensaje("❌ Falla de Red o Servidor inactivo.", "msg-error");
            document.getElementById('btnMensajeVolver').style.display = 'inline-block';
            document.getElementById('btnReintentar').style.display = 'inline-block';
            document.getElementById('btnReintentar').onclick = () => {
                mostrarMensaje("🔄 Reintentando...", "msg-info");
                intentarFetch(payload, resolve, reject);
            };
        });
    }

    function fetchResiliente(payload) {
        return new Promise((resolve, reject) => { intentarFetch(payload, resolve, reject); });
    }

    function getGuayaquilDateParts() {
        let formatter = new Intl.DateTimeFormat('en-US', {
            timeZone: 'America/Guayaquil', year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
        });
        let parts = formatter.formatToParts(new Date()); let res = {}; parts.forEach(p => { res[p.type] = p.value; }); return res; 
    }

    function generarPinDelDia() {
        let g = getGuayaquilDateParts(); let str = g.year + "-" + g.month + "-" + g.day + "UArtes2024";
        let hash = 0; for (let i = 0; i < str.length; i++) { hash = ((hash << 5) - hash) + str.charCodeAt(i); hash = hash & hash; }
        return ("0000" + Math.abs(hash).toString(36).toUpperCase()).slice(-4);
    }
    
    // Solo generar PIN si la variable de entorno nos confirmó que es un kiosco oficial
    if(ES_PANTALLA_OFICIAL) { document.getElementById('pin-display').innerText = generarPinDelDia(); }

    const turndownService = new TurndownService({
        headingStyle: 'atx',
        codeBlockStyle: 'fenced',
        bulletListMarker: '-'
    });
    const gfm = turndownPluginGfm.gfm;
    turndownService.use(gfm);

    let easymde = new EasyMDE({
        element: document.getElementById('editor-container'),
        spellChecker: false,
        status: false,
        toolbar: [
            "bold", "italic", "|", 
            "heading-2", "|", 
            "unordered-list", "ordered-list", "|", 
            "image", "link", "|",
            {
                name: "insertIframe",
                action: function customFunction(editor) {
                    let url = prompt("Pega aquí la URL pública del documento o PDF:");
                    if (url) {
                        url = url.trim();
                        if (!/^https?:\/\//i.test(url)) { url = 'https://' + url; }
                        if (url.includes('drive.google.com') && url.includes('/view')) { 
                            url = url.replace(/\/view.*$/, '/preview'); 
                        }
                        let cm = editor.codemirror;
                        let output = '\n\n<iframe src="' + url + '" width="100%" height="350px" style="border:1px solid #ccc; border-radius:6px;"></iframe>\n';
                        cm.replaceSelection(output);
                    }
                },
                className: "fa-solid fa-file-pdf",
                title: "Incrustar PDF / Iframe",
            },
            "|",
            {
                name: "convertHtml",
                action: function convertHtmlToMd(editor) {
                    let cm = editor.codemirror;
                    let selection = cm.getSelection();

                    if (!selection) {
                        mostrarMensaje("⚠️ Selecciona el código o texto que deseas convertir.", "msg-warning");
                        setTimeout(() => mostrarMensaje("", ""), 3000);
                        return;
                    }

                    let cleanHtml = selection.replace(/<!--[\s\S]*?-->/g, '');
                    cleanHtml = cleanHtml.replace(/<(style|script|meta|xml)[^>]*>[\s\S]*?<\/\1>/gi, '');
                    cleanHtml = cleanHtml.replace(/<\/?(o:p|w:sdt|v:)[^>]*>/gi, '');

                    let markdownFromHtml = turndownService.turndown(cleanHtml);
                    markdownFromHtml = markdownFromHtml.replace(/\n{3,}/g, '\n\n');

                    cm.replaceSelection(markdownFromHtml);
                    mostrarMensaje("✅ Conversión completada.", "msg-success");
                    setTimeout(() => mostrarMensaje("", ""), 2500);
                },
                className: "fa-solid fa-file-code",
                title: "Convertir Selección HTML a Markdown",
            }
        ]
    });

    function programarReinicioAutomatico() {
        if (!HORA_REFRESCO) return; let g = getGuayaquilDateParts(); let [targetH, targetM] = HORA_REFRESCO.split(':').map(Number);
        let millisCurrent = ((parseInt(g.hour) * 60 + parseInt(g.minute)) * 60 + parseInt(g.second)) * 1000; let millisTarget = ((targetH * 60 + targetM) * 60) * 1000;
        let waitTime = millisTarget - millisCurrent; if (waitTime <= 0) { waitTime += 24 * 60 * 60 * 1000; }
        setTimeout(() => { window.location.reload(); }, waitTime);
    }
    programarReinicioAutomatico();

    async function requestWakeLock() { if ('wakeLock' in navigator) { try { wakeLock = await navigator.wakeLock.request('screen'); } catch (err) {} } }
    document.addEventListener('visibilitychange', () => { if (wakeLock !== null && document.visibilityState === 'visible') requestWakeLock(); });
    function toggleFullScreen() {
        requestWakeLock(); let doc = window.document; let docEl = doc.documentElement;
        let req = docEl.requestFullscreen || docEl.webkitRequestFullscreen; let cncl = doc.exitFullscreen || doc.webkitExitFullscreen;
        if(!doc.fullscreenElement && !doc.webkitFullscreenElement) { req.call(docEl); } else { cncl.call(doc); }
    }
    function manejarCambioFullscreen() {
        let btn = document.getElementById('btnFullscreen'); if(!btn) return;
        let isFS = window.document.fullscreenElement || window.document.webkitFullscreenElement;
        btn.style.display = (isFS && ES_PANTALLA_OFICIAL) ? 'none' : 'flex';
    }
    document.addEventListener('fullscreenchange', manejarCambioFullscreen); document.addEventListener('webkitfullscreenchange', manejarCambioFullscreen);

    function inicializarKiosco() {
        if (!ES_PANTALLA_OFICIAL || MODO_TECNICO_ID === "") return;
        fetchResiliente({ accion: 'init_kiosco', tecnicoId: MODO_TECNICO_ID })
        .then(data => {
            if(data.status === "ok" && data.tecnicoValido) {
                tecnicoVerificado = true;
                document.getElementById('badgeTecnico').style.display = 'inline-block';
                document.getElementById('nombreTecnicoBadge').innerText = data.tecnicoNombre;
                let blqQr = document.getElementById('bloqueQrInicio'); if(blqQr) blqQr.style.display = 'none';
            }
        });
    }
    inicializarKiosco();

    function abrirMenuPrestamos() {
        document.getElementById('botones-accion').style.display = 'none'; document.getElementById('menuPrestamos').style.display = 'block';
        document.getElementById('subtitulo').innerText = "GESTIÓN DE PRÉSTAMOS (INVENTARIO)"; document.getElementById('subtitulo').style.backgroundColor = "#007bff"; document.getElementById('subtitulo').style.color = "#fff"; document.getElementById('subtitulo').style.borderColor = "#0056b3";
        mostrarMensaje("⏳ Descargando base de datos...", "msg-info");
        let select = document.getElementById('selectAp'); select.innerHTML = '<option value="" disabled selected>CARGANDO DATOS...</option>'; select.disabled = true;
        document.getElementById('contenedorInfoAp').style.display = 'none'; document.getElementById('contenedorNuevoAp').style.display = 'none';

        fetchResiliente({ accion: 'obtener_prestamos' }).then(data => {
            mostrarMensaje("", ""); select.disabled = false;
            if(data.status === "ok") { listaPrestamosActivos = data.prestamos; actualizarDesplegablePrestamos(); } 
            else { mostrarMensaje("❌ Error al cargar préstamos", "msg-error"); }
        });
    }

    function actualizarDesplegablePrestamos(seleccionarId = "") {
        let select = document.getElementById('selectAp'); select.innerHTML = '<option value="" disabled selected>-- ELIGE UN AP ID --</option>';
        for(let i = listaPrestamosActivos.length - 1; i >= 0; i--) {
            let ap = listaPrestamosActivos[i]; let estado = (ap.idRecepcion !== "" && ap.idDevolucion !== "") ? " [COMPLETADO]" : ""; select.innerHTML += `<option value="${ap.apId}">${ap.apId}${estado}</option>`;
        }
        select.innerHTML += '<option value="NUEVO_AP" style="font-weight:900; color:#007bff;">➕ AGREGAR NUEVO AP ID...</option>';
        if(seleccionarId !== "") { select.value = seleccionarId; cambioSeleccionAp(); }
    }

    function prepararNuevoAp() {
        document.getElementById('contenedorInfoAp').style.display = 'none'; document.getElementById('contenedorNuevoAp').style.display = 'block';
        document.getElementById('nuevoApId').value = ""; document.getElementById('nuevoApId').disabled = false; easymde.value("");
        switchEditorTab('codigo'); 
        let btnGuardar = document.getElementById('btnGuardarAp'); btnGuardar.innerText = "Guardar Nuevo AP"; btnGuardar.onclick = guardarNuevoAp;
    }

    function iniciarEdicionAp() {
        document.getElementById('contenedorInfoAp').style.display = 'none'; document.getElementById('contenedorNuevoAp').style.display = 'block';
        document.getElementById('nuevoApId').value = apIdSeleccionado; document.getElementById('nuevoApId').disabled = true; 
        let apData = listaPrestamosActivos.find(p => p.apId === apIdSeleccionado); easymde.value(apData.apInfo);
        switchEditorTab('codigo'); 
        let btnGuardar = document.getElementById('btnGuardarAp'); btnGuardar.innerText = "Guardar Cambios del AP"; btnGuardar.onclick = guardarEdicionAp;
    }

    function cambioSeleccionAp() {
        let selectVal = document.getElementById('selectAp').value;
        if(selectVal === "NUEVO_AP") {
            document.getElementById('contenedorInfoAp').style.display = 'none'; document.getElementById('contenedorNuevoAp').style.display = 'none'; document.getElementById('menuPrestamos').style.display = 'none';
            iniciarValidacionTecnico('autorizar_nuevo_ap'); return;
        }
        document.getElementById('contenedorNuevoAp').style.display = 'none';
        let apData = listaPrestamosActivos.find(p => p.apId === selectVal);
        if(apData) {
            apIdSeleccionado = apData.apId; document.getElementById('contenedorInfoAp').style.display = 'block'; document.getElementById('apInfoView').innerHTML = marked.parse(apData.apInfo);
            document.getElementById('btnRegistrarRecepcion').style.display = 'none'; document.getElementById('btnRegistrarDevolucion').style.display = 'none'; document.getElementById('msgApCompletado').style.display = 'none';
            if (tecnicoVerificado) { document.getElementById('btnEditarAp').style.display = 'block'; }
            if(apData.idRecepcion === "") document.getElementById('btnRegistrarRecepcion').style.display = 'block';
            else if (apData.idDevolucion === "") document.getElementById('btnRegistrarDevolucion').style.display = 'block';
            else document.getElementById('msgApCompletado').style.display = 'block';
        }
    }

    function guardarNuevoAp() {
        let nuevoId = document.getElementById('nuevoApId').value.trim(); let nuevaInfo = easymde.value().trim();
        if(nuevoId === "" || nuevaInfo === "") { alert("Completa el ID y la información."); return; }
        if(listaPrestamosActivos.some(p => p.apId === nuevoId)) { alert("Este AP ID ya existe."); return; }
        mostrarMensaje("⏳ Guardando nuevo AP...", "msg-info");
        fetchResiliente({ accion: 'crear_ap', apId: nuevoId, apInfo: nuevaInfo }).then(data => {
            if(data.status === "ok") {
                mostrarMensaje("✅ AP Guardado. Actualizando lista...", "msg-success");
                listaPrestamosActivos.push({ apId: nuevoId, apInfo: nuevaInfo, idRecepcion: "", idDevolucion: "" }); actualizarDesplegablePrestamos(nuevoId);
            } else { mostrarMensaje("❌ Error: " + data.msg, "msg-error"); }
        });
    }

    function guardarEdicionAp() {
        let infoEditada = easymde.value().trim(); if(infoEditada === "") { alert("La información no puede estar vacía."); return; }
        mostrarMensaje("⏳ Guardando cambios...", "msg-info");
        fetchResiliente({ accion: 'editar_ap', apId: apIdSeleccionado, apInfo: infoEditada }).then(data => {
            if(data.status === "ok") {
                mostrarMensaje("✅ Cambios guardados exitosamente.", "msg-success");
                let apData = listaPrestamosActivos.find(p => p.apId === apIdSeleccionado); apData.apInfo = infoEditada;
                document.getElementById('contenedorNuevoAp').style.display = 'none'; cambioSeleccionAp(); 
            } else { mostrarMensaje("❌ Error: " + data.msg, "msg-error"); }
        });
    }

    function iniciarFlujoPrestamo(tipo) { tipoPrestamoEnProceso = tipo; document.getElementById('menuPrestamos').style.display = 'none'; iniciarEscaneo('prestamo_usuario'); }
    function iniciarValidacionTecnico(accionDestino) { if (tecnicoVerificado) { accionActual = accionDestino; procesarEscaneoTecnicoAutenticado(MODO_TECNICO_ID); } else { iniciarEscaneo(accionDestino); } }

    function procesarEscaneoTecnicoAutenticado(scanResult) {
        tecnicoIdActual = scanResult; document.getElementById('codigoManual').value = ""; mostrarMensaje("⏳ Procesando autorización...", "msg-info");
        fetchResiliente({ accion: 'validar_tecnico', tecnicoId: tecnicoIdActual }).then(data => {
            if(data.status === "ok") {
                detenerEscaneo(); mostrarMensaje(`✅ Técnico autorizado: ${data.nombre}.`, "msg-success");
                
                if (accionActual === 'prestamo_tecnico') {
                    ejecutarPeticion({ 
                        accion: 'registrar_prestamo', 
                        apId: apIdSeleccionado, 
                        usuarioId: usuarioIdActual, 
                        tipoRegistro: tipoPrestamoEnProceso,
                        tecnicoId: tecnicoIdActual 
                    });
                } else if (accionActual === 'autorizar_nuevo_ap') {
                    setTimeout(() => {
                        mostrarMensaje("", ""); document.getElementById('menuPrestamos').style.display = 'block'; prepararNuevoAp();
                        document.getElementById('subtitulo').innerText = "CREAR NUEVO ACTA DE PRÉSTAMO"; document.getElementById('subtitulo').style.backgroundColor = "#ffc107"; document.getElementById('subtitulo').style.color = "#000"; document.getElementById('selectAp').value = "NUEVO_AP";
                    }, 1000);
                }
            } else { 
                mostrarMensaje(`❌ Error: ${data.msg}. Intenta de nuevo.`, "msg-error"); 
                if(tecnicoVerificado) resetTimer = setTimeout(() => { cancelar(); }, 4000);
            }
        });
    }

    document.getElementById('reader').addEventListener('click', async function(e) {
        let video = document.querySelector('#reader video'); if(!video || !video.srcObject) return;
        let rect = this.getBoundingClientRect(); let x = e.clientX - rect.left; let y = e.clientY - rect.top;
        let focusRing = document.createElement('div'); focusRing.className = 'focus-ring'; focusRing.style.left = (x - 30) + 'px'; focusRing.style.top = (y - 30) + 'px'; this.appendChild(focusRing); setTimeout(() => focusRing.remove(), 500);
        try {
            let track = video.srcObject.getVideoTracks()[0]; let caps = track.getCapabilities();
            if(caps.focusMode) { await track.applyConstraints({ advanced: [{ focusMode: "single-shot" }] }); setTimeout(() => { track.applyConstraints({ advanced: [{ focusMode: "continuous" }] }); }, 1500); }
        } catch(err) {}
    });

    function verificarOtro(sId, iId) { document.getElementById(iId).style.display = (document.getElementById(sId).value === 'Otro') ? 'block' : 'none'; }
    function obtenerValor(sId, iId) { let v = document.getElementById(sId).value; return (v === 'Otro') ? (document.getElementById(iId).value.trim() || 'Otro') : v; }

    function mostrarFormularioEntrada() { 
        document.getElementById('botones-accion').style.display = 'none'; document.getElementById('detallesEntrada').style.display = 'block'; 
        document.getElementById('subtitulo').innerText = "PASO 1: DETALLES DE TU VISITA"; document.getElementById('subtitulo').style.backgroundColor = "#ffc107"; document.getElementById('subtitulo').style.color = "#000"; document.getElementById('subtitulo').style.borderColor = "#d39e00";
    }

    function confirmarDetalles() {
        if (!ES_PANTALLA_OFICIAL) {
            let pinIngresado = document.getElementById('inputPin').value.trim().toUpperCase();
            if (pinIngresado === "") { alert("Por favor, ingresa el PIN."); return; }
            if (pinIngresado !== generarPinDelDia()) { mostrarMensaje("❌ PIN incorrecto.", "msg-error"); return; }
        }
        datosEntrada.tiempoEstimado = document.getElementById('inputTiempoEstimado').value;
        if (!datosEntrada.tiempoEstimado) { alert("Por favor, indica cuánto tiempo te quedarás."); return; }
        datosEntrada.laboratorio = obtenerValor('selectLab', 'inputLabOtro'); datosEntrada.actividad = obtenerValor('selectActividad', 'inputActividadOtro');
        if (!datosEntrada.laboratorio || !datosEntrada.actividad) { alert("Selecciona laboratorio y actividad."); return; }
        
        document.getElementById('detallesEntrada').style.display = 'none'; mostrarMensaje("", ""); iniciarEscaneo('entrada'); 
    }

    function iniciarEscaneo(accion) {
        accionActual = accion; document.getElementById('botones-accion').style.display = 'none'; document.getElementById('manualEntry').style.display = 'block'; mostrarMensaje("Iniciando cámara...", "msg-info");
        let cm = document.getElementById('codigoManual');
        if(accion === 'prestamo_tecnico' || accion === 'autorizar_nuevo_ap') {
            document.getElementById('subtitulo').innerText = "TÉCNICO 🔑: APUNTA EL QR DE TU CARNET PARA AUTORIZAR."; document.getElementById('subtitulo').style.backgroundColor = "#dc3545"; document.getElementById('subtitulo').style.color = "#fff"; document.getElementById('subtitulo').style.borderColor = "#a71d2a";
            cm.type = 'password'; cm.placeholder = "ID DEL TÉCNICO";
        } else if (accion === 'editar_perfil') {
            document.getElementById('subtitulo').innerText = "USUARIO 👤: APUNTA TU CÓDIGO QR PARA BUSCAR TU PERFIL."; document.getElementById('subtitulo').style.backgroundColor = "#868e96"; document.getElementById('subtitulo').style.color = "#fff"; document.getElementById('subtitulo').style.borderColor = "#495057";
            cm.type = 'text'; cm.placeholder = "EJ. 0912345678";
        } else {
            document.getElementById('subtitulo').innerText = (accion === 'prestamo_usuario') ? "USUARIO 👤: APUNTA EL CÓDIGO QR DE TU CARNET A LA CÁMARA." : "PASO 2: APUNTA EL QR DE TU CARNET A LA CÁMARA."; document.getElementById('subtitulo').style.backgroundColor = "#28a745"; document.getElementById('subtitulo').style.color = "#fff"; document.getElementById('subtitulo').style.borderColor = "#1e7e34";
            cm.type = 'text'; cm.placeholder = "EJ. 0912345678";
        }

        if (!html5QrCode) { html5QrCode = new Html5Qrcode("reader"); }
        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                document.getElementById('qr-container').style.display = 'block'; let select = document.getElementById('cameraSelect'); select.innerHTML = ''; let backCameraId = devices[0].id;
                devices.forEach(device => {
                    let label = device.label.toLowerCase(); if (label.includes('back') || label.includes('trasera') || label.includes('rear') || label.includes('environment')) { backCameraId = device.id; }
                    let option = document.createElement('option'); option.value = device.id; option.text = device.label || `Cámara ${select.length + 1}`; select.appendChild(option);
                });
                select.value = backCameraId; arrancarCamara(backCameraId);
            } else { document.getElementById('qr-container').style.display = 'none'; mostrarMensaje("No se detectó cámara.", "msg-warning"); }
        }).catch(err => { document.getElementById('qr-container').style.display = 'none'; mostrarMensaje("Cámara bloqueada o inexistente.", "msg-warning"); });
    }

    function arrancarCamara(cameraId) { if (html5QrCode.isScanning) { html5QrCode.stop().then(() => { iniciarStream(cameraId); }).catch(err => console.log(err)); } else { iniciarStream(cameraId); } }
    function cambiarCamara() { let select = document.getElementById('cameraSelect'); if (select.value) { arrancarCamara(select.value); } }
    function iniciarStream(cameraId) {
        mostrarMensaje("CÁMARA ACTIVADA (TOCA EL VIDEO PARA ENFOCAR)", "msg-info");
        html5QrCode.start( cameraId, { fps: 15, qrbox: { width: 350, height: 350 } }, (decodedText) => { procesarEscaneo(decodedText); }, (errorMessage) => { }
        ).catch(err => { mostrarMensaje("Error al abrir esta cámara. Intenta con otra.", "msg-error"); });
    }

    function forzarCapturaFrame() {
        let video = document.querySelector('#reader video'); if(!video) return; mostrarMensaje("⏳ Analizando fotograma actual...", "msg-info");
        let canvas = document.createElement('canvas'); canvas.width = video.videoWidth; canvas.height = video.videoHeight;
        let ctx = canvas.getContext('2d'); ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        canvas.toBlob(function(blob) {
            let file = new File([blob], "captura.jpg", { type: "image/jpeg" });
            if(!hiddenQrScanner) hiddenQrScanner = new Html5Qrcode("hidden-reader");
            hiddenQrScanner.scanFile(file, true).then(decodedText => { mostrarMensaje("✅ QR detectado.", "msg-success"); procesarEscaneo(decodedText);
            }).catch(err => { mostrarMensaje("❌ El QR sigue sin leerse. Limpia el lente y enfoca mejor.", "msg-error"); });
        }, 'image/jpeg');
    }

    function procesarEscaneo(decodedText) {
        if (!decodedText || decodedText.trim() === "") return; let scanResult = String(decodedText).trim(); 
        if (accionActual !== 'prestamo_tecnico' && accionActual !== 'autorizar_nuevo_ap') { detenerEscaneo(); document.getElementById('codigoManual').value = ""; }

        if (accionActual === 'salida') {
            usuarioIdActual = scanResult; ejecutarPeticion({ accion: 'salida', usuarioId: usuarioIdActual });
        } else if (accionActual === 'prestamo_tecnico' || accionActual === 'autorizar_nuevo_ap') {
            procesarEscaneoTecnicoAutenticado(scanResult);
        } else {
            usuarioIdActual = scanResult; mostrarMensaje("⏳ Buscando perfil...", "msg-info");
            
            fetchResiliente({ accion: 'verificar', usuarioId: usuarioIdActual }).then(respuesta => {
                mostrarMensaje("", ""); 
                if (respuesta.status === "error") { mostrarMensaje("❌ Error del servidor: " + respuesta.msg, "msg-error"); resetTimer = setTimeout(() => { cancelar(); }, 6000); return; }
                datosUsuarioCache = respuesta;
                if (respuesta.existe) { 
                    if (accionActual === 'editar_perfil') {
                        abrirActualizacion();
                    } else {
                        mostrarPantallaConfirmacion(); 
                    }
                } 
                else {
                    if (accionActual === 'editar_perfil') {
                        mostrarMensaje("❌ No se encontró tu perfil. Debes registrarte primero al marcar Entrada.", "msg-error");
                        resetTimer = setTimeout(() => { cancelar(); }, 5000);
                    } else {
                        esActualizacion = false; document.getElementById('tituloRegistro').innerText = "¡HOLA! ES TU PRIMERA VEZ AQUÍ."; document.getElementById('btnGuardarUsuario').innerText = (accionActual === 'prestamo_usuario') ? "Guardar Información y Continuar" : "Guardar y Registrar Entrada"; document.getElementById('registroNuevoUsuario').style.display = 'block';
                    }
                }
            });
        }
    }

    function limpiarTimersConfirmacion() {
        if (autoConfirmTimer) clearTimeout(autoConfirmTimer);
        if (autoConfirmInterval) clearInterval(autoConfirmInterval);
        let btnConf = document.getElementById('btnConfirmarUsuario');
        if (btnConf) btnConf.innerText = "SÍ, TODO ESTÁ BIEN. CONTINUAR 🚀";
    }

    function mostrarPantallaConfirmacion() {
        document.getElementById('confNombre').innerText = datosUsuarioCache.nombre; 
        document.getElementById('confIdVisual').innerText = usuarioIdActual; 
        document.getElementById('confArea').innerText = datosUsuarioCache.area; 
        document.getElementById('confRol').innerText = datosUsuarioCache.rol; 
        document.getElementById('confirmarUsuario').style.display = 'block'; 
        document.getElementById('subtitulo').innerText = "CONFIRMA TUS DATOS PARA CONTINUAR."; 
        document.getElementById('subtitulo').style.backgroundColor = "#ffc107"; 
        document.getElementById('subtitulo').style.color = "#000"; 
        document.getElementById('subtitulo').style.borderColor = "#d39e00";

        limpiarTimersConfirmacion();
        timeLeftConf = 5;
        let btnConf = document.getElementById('btnConfirmarUsuario');
        if(btnConf) btnConf.innerText = `SÍ, TODO ESTÁ BIEN. CONTINUAR 🚀 (${timeLeftConf}s)`;
        
        autoConfirmInterval = setInterval(() => {
            timeLeftConf--;
            if (timeLeftConf > 0 && btnConf) {
                btnConf.innerText = `SÍ, TODO ESTÁ BIEN. CONTINUAR 🚀 (${timeLeftConf}s)`;
            }
        }, 1000);

        autoConfirmTimer = setTimeout(() => {
            limpiarTimersConfirmacion();
            confirmarYEntrar();
        }, 5000);
    }

    function confirmarYEntrar() {
        limpiarTimersConfirmacion(); 
        document.getElementById('confirmarUsuario').style.display = 'none';
        if(accionActual === 'prestamo_usuario') { iniciarValidacionTecnico('prestamo_tecnico'); } 
        else {
            ejecutarPeticion({ 
                accion: 'entrada', usuarioId: usuarioIdActual, nombre: datosUsuarioCache.nombre, area: datosUsuarioCache.area, rol: datosUsuarioCache.rol, laboratorio: datosEntrada.laboratorio, actividad: datosEntrada.actividad, tiempoEstimado: datosEntrada.tiempoEstimado, esNuevo: false, actualizarUsuario: false 
            });
        }
    }

    function abrirActualizacion() {
        limpiarTimersConfirmacion(); 
        esActualizacion = true; document.getElementById('confirmarUsuario').style.display = 'none'; 
        
        let btnGuardar = document.getElementById('btnGuardarUsuario');
        if (accionActual === 'editar_perfil') {
            document.getElementById('tituloRegistro').innerText = "EDITAR MI INFORMACIÓN"; 
            btnGuardar.innerText = "GUARDAR CAMBIOS";
        } else {
            document.getElementById('tituloRegistro').innerText = "ACTUALIZAR MIS DATOS"; 
            btnGuardar.innerText = (accionActual === 'prestamo_usuario') ? "Actualizar y Continuar" : "Actualizar y Registrar Entrada"; 
        }
        
        document.getElementById('registroNuevoUsuario').style.display = 'block';
        document.getElementById('inputNombre').value = datosUsuarioCache.nombre; document.getElementById('selectRol').value = datosUsuarioCache.rol; let selArea = document.getElementById('selectArea'); let areaOptions = Array.from(selArea.options).map(o => o.value);
        if (areaOptions.includes(datosUsuarioCache.area)) { selArea.value = datosUsuarioCache.area; document.getElementById('inputAreaOtro').style.display = 'none'; } 
        else { selArea.value = 'Otro'; document.getElementById('inputAreaOtro').value = datosUsuarioCache.area; document.getElementById('inputAreaOtro').style.display = 'block'; }
    }

    function guardarUsuarioYEntrar() {
        let nombre = document.getElementById('inputNombre').value.trim(); let area = obtenerValor('selectArea', 'inputAreaOtro'); let rol = document.getElementById('selectRol').value;
        if (nombre === "" || !document.getElementById('selectArea').value || !rol) { alert("Completa tus datos."); return; }
        document.getElementById('registroNuevoUsuario').style.display = 'none';
        
        if (accionActual === 'prestamo_usuario' || accionActual === 'editar_perfil') {
            mostrarMensaje("⏳ Guardando perfil...", "msg-info");
            fetchResiliente({ accion: 'registrar_solo_usuario', usuarioId: usuarioIdActual, nombre: nombre, area: area, rol: rol, esNuevo: !esActualizacion, actualizarUsuario: esActualizacion })
            .then(data => { 
                if(data.status === "ok") { 
                    if (accionActual === 'prestamo_usuario') {
                        mostrarMensaje("", ""); iniciarValidacionTecnico('prestamo_tecnico'); 
                    } else {
                        mostrarMensaje("✅ Información personal actualizada exitosamente.", "msg-success"); 
                        resetTimer = setTimeout(() => { cancelar(); }, 3000);
                    }
                } else { 
                    mostrarMensaje("❌ Error: " + data.msg, "msg-error"); 
                } 
            });
        } else {
            ejecutarPeticion({ accion: 'entrada', usuarioId: usuarioIdActual, nombre: nombre, area: area, rol: rol, laboratorio: datosEntrada.laboratorio, actividad: datosEntrada.actividad, tiempoEstimado: datosEntrada.tiempoEstimado, esNuevo: !esActualizacion, actualizarUsuario: esActualizacion });
        }
    }

    function ejecutarPeticion(payload) {
        payload.clientIp = IP_COMBINADA; mostrarMensaje("⏳ Guardando...", "msg-info"); document.getElementById('btnMensajeVolver').style.display = 'none'; if(resetTimer) clearTimeout(resetTimer);
        fetchResiliente(payload).then(data => {
            if(data.status === "ok") {
                mostrarMensaje(`✅ ¡ÉXITO! REGISTRO COMPLETADO.`, "msg-success"); resetTimer = setTimeout(() => { cancelar(); }, 4000);
            } else { 
                mostrarMensaje(`❌ ERROR: ${data.msg}`, "msg-error"); document.getElementById('btnMensajeVolver').style.display = 'inline-block'; resetTimer = setTimeout(() => { cancelar(); }, 6000);
            }
        });
    }

    function detenerEscaneo() { 
        if (html5QrCode && html5QrCode.isScanning) { html5QrCode.stop().then(() => { html5QrCode.clear(); }).catch(err => console.log(err)); } 
        document.getElementById('qr-container').style.display = 'none'; document.getElementById('manualEntry').style.display = 'none'; 
    }

    function cancelar() {
        if(resetTimer) clearTimeout(resetTimer); detenerEscaneo(); limpiarTimersConfirmacion(); 
        document.getElementById('detallesEntrada').style.display = 'none'; document.getElementById('menuPrestamos').style.display = 'none'; document.getElementById('confirmarUsuario').style.display = 'none'; document.getElementById('registroNuevoUsuario').style.display = 'none'; document.getElementById('btnMensajeVolver').style.display = 'none'; document.getElementById('btnReintentar').style.display = 'none'; document.getElementById('botones-accion').style.display = 'block'; 
        let sub = document.getElementById('subtitulo'); sub.innerText = "<?php echo $MENSAJE_BIENVENIDA; ?>"; sub.style.backgroundColor = "#ffc107"; sub.style.color = "#000"; sub.style.borderColor = "#d39e00";
        document.querySelectorAll('select').forEach(s => { if(s.id === 'inputTiempoEstimado') { s.value = '02:00'; } else { s.selectedIndex = 0; } });
        document.querySelectorAll('input[type="text"], input[type="password"], textarea').forEach(i => { if(i.id !== 'inputPin') { i.value = ''; } });
        document.querySelectorAll('.input-oculto').forEach(i => i.style.display = 'none'); easymde.value(""); switchEditorTab('codigo');
        datosEntrada = {}; usuarioIdActual = ''; tecnicoIdActual = ''; esActualizacion = false; apIdSeleccionado = ''; tipoPrestamoEnProceso = ''; mostrarMensaje("", "");
    }

    function mostrarMensaje(texto, clase) { document.getElementById('mensaje').innerText = texto; document.getElementById('mensaje').className = clase; }
</script>
</body>
</html>