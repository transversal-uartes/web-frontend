<?php
$apiUrlKiosk = getenv('API_URL_KIOSK') ?: '/api/kiosk';
$apiUrlActas = getenv('API_URL_ACTAS') ?: '/api/actas';

$logoClaro        = getenv('LOGO_CLARO')  ?: '../Logo-OFICIAL-RECINTO-LAB-oscuro.png';
$logoOscuro       = getenv('LOGO_OSCURO') ?: '../Logo-OFICIAL-RECINTO-LAB-claro.png';
$imgUrlBase       = '../catalogo/img/'; 

$imageFiles = glob($imgUrlBase . '*.*') ?: [];
$imageMap = [];
foreach ($imageFiles as $file) {
    $info = pathinfo($file);
    $filename = $info['filename'];
    $basename = $info['basename'];
    $ids = explode('.', $filename);
    foreach ($ids as $id) {
        if (!empty($id)) $imageMap[strtoupper($id)] = $basename;
    }
}
header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Actas - Dpto. Transversal UArtes</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        :root {
            --bg-main: #f4f4f9; --text-main: #333; --bg-card: #ffffff;
            --border-color: #ccc; --input-bg: #ffffff; --input-text: #333;
            --primary: #0056b3; --primary-hover: #004494; --success: #28a745;
            --danger: #dc3545; --shadow: rgba(0,0,0,0.1); --table-hover: #f8f9fa;
        }
        
        [data-theme="dark"] {
            --bg-main: #121212; --text-main: #e0e0e0; --bg-card: #1e1e1e;
            --border-color: #444; --input-bg: #2a2a2a; --input-text: #e0e0e0;
            --primary: #0d47a1; --primary-hover: #1565c0; --success: #2e7d32;
            --danger: #c62828; --shadow: rgba(0,0,0,0.5); --table-hover: #2c2c2c;
        }

        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        body { background-color: var(--bg-main); color: var(--text-main); display: flex; flex-direction: column; transition: 0.3s; padding: 15px 20px; }
        
        .container { flex-grow: 1; display: flex; flex-direction: column; min-height: 0; max-width: 1400px; margin: 0 auto; width: 100%; }
        .card { background: var(--bg-card); padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px var(--shadow); border: 1px solid var(--border-color); }
        
        input, select, textarea { padding: 10px; border: 1px solid var(--border-color); border-radius: 5px; background: var(--input-bg); color: var(--input-text); font-size: 13.5px; width: 100%; transition: border-color 0.2s; }
        input:focus, select:focus, textarea:focus { border-color: var(--primary); outline: none; }
        input:disabled, select:disabled, textarea:disabled { opacity: 0.6; cursor: not-allowed; }
        
        button { cursor: pointer; border: none; font-weight: bold; transition: 0.2s; color: white; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 5px; }
        button:disabled { opacity: 0.5; cursor: not-allowed; filter: grayscale(1); }
        .btn-primary { background-color: var(--primary); padding: 10px 18px; }
        .btn-primary:hover:not(:disabled) { background-color: var(--primary-hover); }
        .btn-success { background-color: var(--success); padding: 10px 18px; }
        .btn-danger { background-color: var(--danger); padding: 10px 18px; }
        
        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 12px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        
        .tabs { flex-shrink: 0; display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; border-bottom: 2px solid var(--border-color); padding-bottom: 8px; }
        .tab-btn { background: transparent; border: 2px solid transparent; color: var(--text-main); padding: 10px 20px; opacity: 0.7; font-size: 15px; border-radius: 8px 8px 0 0; }
        .tab-btn.active { opacity: 1; border-bottom: 2px solid var(--primary); color: var(--primary); font-weight: bold; }
        
        .tab-content { display: none; flex-grow: 1; min-height: 0; }
        #tabGenerar.active { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
        @media (min-width: 1024px) { #tabGenerar.active { grid-template-columns: repeat(12, minmax(0, 1fr)); } }
        #tabPlantillas.active, #tabFormatos.active { display: flex; flex-direction: column; }
        
        .scrollable-col { overflow-y: auto; padding-right: 8px; min-height: 0; }
        .flex-col-container { display: flex; flex-direction: column; min-height: 0; }
        
        .tox-tinymce { flex-grow: 1 !important; height: auto !important; border-radius: 6px !important; }
        .tox-tinymce-aux { z-index: 9999999 !important; }

        #previewActaContainer { background: white; color: black; padding: 40px; border-radius: 4px; min-height: 100%; font-family: 'Times New Roman', Times, serif; box-shadow: inset 0 0 10px rgba(0,0,0,0.1); border: 1px solid #ccc; font-size: 11pt; }

        .toast-container { position: fixed; bottom: 20px; right: 20px; display: flex; flex-direction: column; gap: 10px; z-index: 999999; }
        .toast { padding: 15px 25px; border-radius: 8px; font-size: 15px; font-weight: bold; color: white; animation: slideIn 0.3s forwards; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        .toast.success { background-color: #2e7d32; }
        .toast.error { background-color: #c62828; }
        .toast.info { background-color: #0d47a1; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        .placeholder-btn { background: var(--border-color); color: var(--text-main); transition: filter 0.2s; border: 1px solid rgba(0,0,0,0.2); }
        .placeholder-btn:hover { filter: brightness(0.85); }
        .theme-switch { position: fixed; bottom: 20px; left: 20px; background: var(--border-color); color: var(--text-main); width: 45px; height: 45px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; z-index: 1000; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        
        .draggable-window { position: fixed; z-index: 99999; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.6); display: flex; flex-direction: column; resize: both; overflow: hidden; min-width: 350px; min-height: 250px; transition: width 0.3s, height 0.3s, top 0.3s, left 0.3s, border-radius 0.3s; }
        .draggable-window.dragging { transition: none !important; }
        .draggable-window.maximized { top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; border-radius: 0; resize: none; }
        .window-header { background: var(--primary); color: white; padding: 12px 15px; cursor: grab; display: flex; justify-content: space-between; align-items: center; user-select: none; }
        .window-header:active { cursor: grabbing; }
        .window-controls button { background: transparent; border: none; color: white; font-size: 16px; cursor: pointer; padding: 2px 8px; opacity: 0.8; transition: opacity 0.2s, transform 0.2s; }
        .window-controls button:hover { opacity: 1; transform: scale(1.1); }
        .window-body { padding: 15px; flex-grow: 1; overflow-y: auto; background: var(--bg-main); display: flex; flex-direction: column; }
        .window-footer { padding: 12px 15px; border-top: 1px solid var(--border-color); background: var(--bg-card); text-align: right; }

        .equipos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; overflow-y: auto; padding-right: 5px; padding-top: 5px; }
        .equipo-item { background: var(--bg-card); border: 1px solid var(--border-color); padding: 12px 8px; border-radius: 8px; display: flex; flex-direction: column; align-items: center; text-align: center; cursor: pointer; position: relative; transition: all 0.2s; }
        .equipo-item:hover { background: var(--table-hover); border-color: var(--primary); transform: translateY(-2px); box-shadow: 0 4px 12px var(--shadow); }
        .equipo-item input[type="checkbox"] { position: absolute; top: 10px; left: 10px; width: 16px; height: 16px; cursor: pointer; z-index: 10; margin: 0; }
        .equipo-item .input-cant-modal { position: absolute; top: 8px; right: 8px; width: 45px; height: 22px; padding: 2px 4px; font-size: 11px; border-radius: 4px; border: 1px solid var(--border-color); z-index: 10; background: var(--input-bg); color: var(--input-text); text-align: center; }
        .item-thumb-modal { width: 80px; height: 80px; object-fit: contain; border-radius: 4px; flex-shrink: 0; margin-top: 8px; margin-bottom: 8px; }
    </style>
</head>
<body data-theme="light">

    <div id="toastContainer" class="toast-container"></div>
    <button class="theme-switch" onclick="toggleTheme()" title="Cambiar Tema"><i class="fa-solid fa-moon"></i></button>

    <div id="loginView" class="container flex justify-center items-center h-full" style="display: flex;">
        <div class="card w-full max-w-md text-center">
            <h2 class="text-2xl font-bold mb-2">Generador de Actas</h2>
            <p class="text-sm opacity-75 mb-6">Validación requerida. Escanea tu carnet o ingresa tu C.I.</p>
            
            <div id="qrReaderContainer" class="mb-5 hidden"></div>
            
            <button id="btnStartScan" class="btn-primary w-full py-4 mb-5 text-lg" onclick="iniciarScannerQR()">
                <i class="fa-solid fa-qrcode"></i> Escanear Carnet UArtes
            </button>

            <div class="flex items-center gap-3 mb-5 opacity-50">
                <hr class="flex-grow border-gray-500"><span class="text-xs font-bold">O INGRESO MANUAL</span><hr class="flex-grow border-gray-500">
            </div>

            <div class="form-group text-left">
                <input type="text" id="loginCedula" placeholder="Ej. 0912345678" onkeypress="if(event.key === 'Enter') verificarTecnico()" class="text-center text-lg tracking-widest">
            </div>
            <button class="bg-gray-700 hover:bg-gray-600 w-full py-4 text-white font-bold rounded transition text-lg" onclick="verificarTecnico()">
                <i class="fa-solid fa-keyboard"></i> Validar Identidad
            </button>
        </div>
    </div>

    <!-- MAIN APP CONTAINER -->
    <div id="mainApp" class="container hidden">
        
        <div class="flex justify-between items-center mb-4 bg-blue-900 text-white p-3 rounded-lg shadow flex-shrink-0">
            <div>
                <h1 class="text-xl font-bold"><i class="fa-solid fa-file-signature"></i> Sistema Central de Actas</h1>
                <p class="text-xs text-blue-200 mt-0.5">Técnico Operador: <span id="tecnicoNombreBadge" class="font-bold text-white tracking-wide"></span></p>
            </div>
            <button onclick="cerrarSesion()" class="btn-danger px-4 py-2 text-sm shadow hover:scale-105 transition"><i class="fa-solid fa-power-off"></i> Cerrar Sesión</button>
        </div>

        <div class="tabs">
            <button class="tab-btn active" onclick="cambiarTab('generar')"><i class="fa-solid fa-wand-magic-sparkles"></i> Asistente de Generación</button>
            <button class="tab-btn" onclick="cambiarTab('plantillas')"><i class="fa-solid fa-pen-ruler"></i> Edición de Plantillas</button>
            <button class="tab-btn" onclick="cambiarTab('formatos')"><i class="fa-solid fa-table-cells"></i> Variables de Formatos</button>
        </div>

        <!-- TAB 1: ASISTENTE DE GENERACIÓN -->
        <div id="tabGenerar" class="tab-content active">
            <div class="col-span-1 lg:col-span-4 flex-col-container scrollable-col gap-4">
                
                <div class="card !mb-0 border-l-4 border-blue-600 !p-4 flex-shrink-0">
                    <h3 class="font-bold mb-3 border-b border-gray-400 pb-2 text-xs uppercase tracking-wide opacity-80">1. Fuente de Datos</h3>
                    
                    <div class="form-group">
                        <label class="text-xs font-bold">Tipo de Plantilla:</label>
                        <select id="selectTipoActa" onchange="renderizarCamposDinamicos()" class="text-sm"></select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="text-xs font-bold">Importar Solicitud del Sistema:</label>
                        <select id="selectSolicitud" onchange="poblarDesdeSolicitud()" class="text-sm">
                            <option value="">-- Llenado manual o seleccione una solicitud --</option>
                        </select>
                    </div>
                </div>

                <div class="card !mb-0 flex-shrink-0 !p-4">
                    <h3 class="font-bold mb-3 border-b border-gray-400 pb-2 text-xs uppercase tracking-wide opacity-80">2. Variables del Documento</h3>
                    <div id="dynamicFieldsContainer" class="flex flex-col gap-2 text-sm">
                        <span class="text-gray-500 italic text-xs">Cargando campos dinámicos...</span>
                    </div>
                </div>
                
                <div class="card !mb-0 border-l-4 border-green-500 flex-shrink-0 !p-4 mt-auto">
                    <h3 class="font-bold mb-3 border-b border-gray-400 pb-2 text-xs uppercase tracking-wide opacity-80">3. Registrar y Actualizar</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="text-xs font-bold">Estado Préstamo:</label>
                            <select id="selectEstadoPrestamo" class="text-sm p-2" onchange="actualizarPrevia();"></select>
                        </div>
                        <div class="form-group">
                            <label class="text-xs font-bold">Estado Inventario:</label>
                            <select id="selectEstadoInv" class="text-sm p-2"></select>
                        </div>
                    </div>
                    <button class="btn-success w-full mt-2" onclick="guardarActaEnSistema(false)">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Acta en Sistema
                    </button>
                </div>
            </div>

            <div class="col-span-1 lg:col-span-8 card flex-col-container !mb-0 relative shadow-lg min-h-0 !p-0 overflow-hidden">
                <div class="flex justify-between items-center p-4 border-b border-gray-300 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <h3 class="font-bold text-lg m-0"><i class="fa-solid fa-eye text-blue-500"></i> Documento a Generar</h3>
                    <button class="btn-primary text-sm py-2 px-4 shadow" onclick="copiarActaPortapapeles()">
                        <i class="fa-brands fa-windows"></i> Copiar Formato Word
                    </button>
                </div>
                
                <div class="flex-grow overflow-y-auto p-4 bg-gray-200 dark:bg-gray-900" style="background-color: var(--bg-main);">
                    <div id="previewActaContainer" class="mx-auto" style="max-width: 850px;">
                        <p class="text-center text-gray-500 mt-32 text-lg">Cargando previsualización...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: EDITOR DE PLANTILLAS -->
        <div id="tabPlantillas" class="tab-content card shadow-lg !p-4">
            <div class="flex flex-wrap justify-between items-center mb-4 border-b border-gray-400 pb-3 gap-4 flex-shrink-0">
                <h2 class="text-lg font-bold"><i class="fa-solid fa-file-code text-blue-500"></i> Gestor Global de Plantillas</h2>
                
                <div class="flex items-center gap-3 p-2 rounded border shadow-inner" style="background-color: var(--bg-main); border-color: var(--border-color);">
                    <label class="text-sm font-bold ml-2">Nueva Plantilla:</label>
                    <input type="text" id="nuevoTipoInput" placeholder="Ej. Acta de Baja" class="w-48 text-sm py-1.5">
                    <button class="btn-primary px-3 py-1.5 text-sm" onclick="crearNuevaPlantilla()"><i class="fa-solid fa-plus"></i> Crear</button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 flex-shrink-0">
                <div class="form-group mb-0">
                    <label class="font-bold text-sm">Seleccionar Plantilla a Editar:</label>
                    <select id="selectPlantillaEdit" onchange="cargarContenidoEditorPlantilla()" class="font-bold text-blue-600 dark:text-blue-400 text-sm"></select>
                </div>
                <div class="form-group mb-0">
                    <label class="font-bold text-xs uppercase opacity-70">Fuente de Variables (ID;Hoja!Rango):</label>
                    <div class="flex gap-2">
                        <input type="text" id="fuentePlantilla" placeholder="Ej: 1A2b3c...;Préstamos!A1:Z1" class="text-sm">
                        <button class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm font-bold" onclick="cargarPlaceholders('plantilla')">Cargar / Actualizar</button>
                    </div>
                </div>
            </div>
            
            <div class="form-group mb-3 p-3 rounded border shadow-inner flex-shrink-0" style="background-color: var(--bg-main); border-color: var(--border-color);">
                <label class="text-xs font-bold mb-2 block uppercase tracking-wider opacity-70">Placeholders de Inyección (Clic para insertar en el cursor):</label>
                <div id="placeholdersPlantilla" class="flex gap-2 flex-wrap min-h-[30px] max-h-[110px] overflow-y-auto">
                    <span class="text-xs opacity-50 italic mt-1">Haga clic en 'Cargar' para obtener las variables desde Google Sheets.</span>
                </div>
            </div>

            <div class="flex-grow flex-col-container min-h-0 border rounded">
                <textarea id="tinymceEditor"></textarea>
            </div>

            <div class="flex justify-end gap-3 mt-4 flex-shrink-0">
                <button id="btnGuardarPlantilla" class="btn-primary px-8 py-2.5 text-md shadow-lg hover:scale-105 transition" onclick="guardarPlantilla()">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Guardar Cambios en Google Sheets
                </button>
            </div>
        </div>

        <!-- TAB 3: EDITOR DE VARIABLES DE FORMATOS -->
        <div id="tabFormatos" class="tab-content card shadow-lg !p-4">
            <div class="flex flex-wrap justify-between items-center mb-4 border-b border-gray-400 pb-3 gap-4 flex-shrink-0">
                <h2 class="text-lg font-bold"><i class="fa-solid fa-table-cells text-blue-500"></i> Formato de Variables (Tablas Dinámicas)</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 flex-shrink-0">
                <div class="form-group mb-0">
                    <label class="font-bold text-sm">1. Seleccionar Plantilla:</label>
                    <select id="selectPlantillaFormato" onchange="cargarVariablesDePlantilla()" class="font-bold text-gray-700 dark:text-gray-300 text-sm"></select>
                </div>
                <div class="form-group mb-0">
                    <label class="font-bold text-sm">2. Variable a Formatear:</label>
                    <select id="selectVariableFormato" onchange="cargarContenidoEditorFormato()" class="font-bold text-blue-600 dark:text-blue-400 text-sm font-mono"></select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 flex-shrink-0">
                <div class="form-group mb-0 relative">
                    <label class="font-bold text-xs uppercase opacity-70">Regex (Extracción):</label>
                    <input type="text" id="inputRegexFormato" placeholder="Ej: (?<CODIGO>[A-Z0-9]+):([0-9]+)" class="text-sm font-mono" oninput="validarRegexFormato()">
                    <span id="regexValidationMsg" class="text-[10px] font-bold mt-1 block hidden"></span>
                </div>
                <div class="form-group mb-0 relative">
                    <label class="font-bold text-xs uppercase opacity-70">Ruta de Imágenes (Opcional):</label>
                    <input type="text" id="inputRutaImagenFormato" placeholder="Ej: ../catalogo/img/{{CODIGO}}" class="text-sm font-mono">
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-4 mb-4 flex-shrink-0">
                <div class="form-group mb-0">
                    <label class="font-bold text-xs uppercase opacity-70">Fuente de Cabezales (ID;Hoja!Rango):</label>
                    <div class="flex gap-2">
                        <input type="text" id="fuenteFormato" placeholder="Ej: 1A2b3c...;Inventario!A1:Z1" class="text-sm">
                        <button class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm font-bold" onclick="cargarPlaceholders('formato')">Cargar</button>
                    </div>
                </div>
            </div>
            
            <div class="form-group mb-3 p-3 rounded border shadow-inner flex-shrink-0" style="background-color: var(--bg-main); border-color: var(--border-color);">
                <label class="text-xs font-bold mb-2 block uppercase tracking-wider opacity-70">Placeholders para las Filas (Clic para insertar en el cursor):</label>
                <div id="placeholdersFormato" class="flex gap-2 flex-wrap min-h-[30px] max-h-[80px] overflow-y-auto">
                    <span class="text-xs opacity-50 italic mt-1">Haga clic en 'Cargar' para obtener las variables de las columnas. Si usa "Grupos Nombrados" en el Regex (ej. <code>(?&lt;CODIGO&gt;...)</code>), ese nombre servirá como variable (<code>{{CODIGO}}</code>). Recuerde usar siempre un &lt;tbody&gt;.</span>
                </div>
                <div id="placeholdersRegex" class="flex gap-2 flex-wrap min-h-[30px] overflow-y-auto mt-2 border-t border-gray-300 dark:border-gray-600 pt-2 hidden">
                </div>
            </div>

            <div class="flex-grow flex-col-container min-h-0 border rounded">
                <textarea id="tinymceFormatoTabla"></textarea>
            </div>

            <div class="flex justify-end gap-3 mt-4 flex-shrink-0">
                <button id="btnGuardarFormato" class="btn-primary px-8 py-2.5 text-md shadow-lg hover:scale-105 transition" onclick="guardarFormatoTablaNube(event)">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Guardar Formato en la Nube
                </button>
            </div>
        </div>

    </div>

    <div id="modalEquiposWindow" class="draggable-window hidden" style="left: 10vw; top: 10vh; width: 80vw; height: 75vh;">
        <div class="window-header" id="modalEquiposHeader">
            <span class="font-bold"><i class="fa-solid fa-boxes-stacked"></i> Catálogo de Equipos <span id="targetEquipmentName" class="text-xs font-mono ml-2 opacity-75"></span></span>
            <div class="window-controls">
                <button onclick="toggleMaximizarVentana()" title="Maximizar/Restaurar"><i id="iconMaximize" class="fa-regular fa-window-maximize"></i></button>
                <button onclick="cerrarSelectorEquipos()" title="Cerrar"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
        <div class="window-body">
            <input type="text" id="filtroEquipos" placeholder="🔍 Buscar por detalle, marca o modelo..." onkeyup="filtrarListaEquipos()" class="mb-4" style="padding: 10px 15px; font-size: 15px;">
            <div id="listaEquiposContainer" class="equipos-grid"></div>
        </div>
        <div class="window-footer">
            <button class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded mr-2 transition" onclick="cerrarSelectorEquipos()">Cancelar</button>
            <button class="btn-primary px-6 py-2" onclick="confirmarSeleccionEquipos()"><i class="fa-solid fa-check"></i> Aplicar Selección</button>
        </div>
    </div>

    <script>
        const API_URL_KIOSK = "<?php echo addslashes($apiUrlKiosk); ?>";
        const API_URL_ACTAS = "<?php echo addslashes($apiUrlActas); ?>";

        const imageMap = <?php echo json_encode($imageMap); ?>;
        const logoClaro = "<?php echo addslashes($logoClaro); ?>";
        const logoOscuro = "<?php echo addslashes($logoOscuro); ?>";
        const imgUrlBase = "<?php echo addslashes($imgUrlBase); ?>";

        const ESTADOS_INVENTARIO = ['Disponible', 'Solicitado', 'Prestado', 'En Uso', 'No Disponible'];
        const ESTADOS_PRESTAMO = ['Solicitado', 'Generado', 'Firmado'];

        let appState = {
            tecnico: null,
            plantillas: {},    
            tiposActa: [],     
            prestamos: [],     
            inventario: {},
            formatos: {}       
        };

        let html5QrCode;
        let activeEquipmentTarget = ""; 

        function showToast(msg, type='info') {
            const container = document.getElementById('toastContainer');
            const t = document.createElement('div');
            t.className = `toast ${type}`;
            t.innerHTML = `<i class="fa-solid fa-circle-info mr-2"></i> ${msg}`;
            container.appendChild(t);
            setTimeout(() => t.remove(), 4000);
        }

        function toggleTheme() {
            const body = document.body;
            const current = body.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            body.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);

            const placeholderSrc = next === 'dark' ? logoClaro : logoOscuro;
            document.querySelectorAll('.fallback-thumb').forEach(img => { img.src = placeholderSrc; });
        }
        if(localStorage.getItem('theme')) document.body.setAttribute('data-theme', localStorage.getItem('theme'));

        function iniciarScannerQR() {
            const qrContainer = document.getElementById('qrReaderContainer');
            qrContainer.classList.remove('hidden');
            if (!html5QrCode) html5QrCode = new Html5Qrcode("qrReaderContainer");
            const config = { fps: 15, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };
            html5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
                html5QrCode.stop();
                qrContainer.classList.add('hidden');
                document.getElementById('loginCedula').value = decodedText;
                verificarTecnico();
            }).catch(err => showToast("Cámara denegada o no disponible.", "error"));
        }

        function verificarTecnico() {
            const cedula = document.getElementById('loginCedula').value.trim();
            if (!cedula) return showToast("Debes ingresar tu cédula.", "error");
            
            const btn = document.querySelector('#loginView .bg-gray-700');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Validando...';
            btn.disabled = true;

            fetch(API_URL_KIOSK, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: 'validar_tecnico', tecnicoId: cedula })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok' && data.tecnicoValido) {
                    appState.tecnico = { id: cedula, nombre: data.nombre, rol: data.rol };
                    localStorage.setItem('uartes_tecnico_actas', JSON.stringify(appState.tecnico));
                    iniciarSistema();
                } else showToast(data.msg || "Técnico no autorizado.", "error");
            })
            .catch(err => showToast("Error servidor: " + err.message, "error"))
            .finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }

        function iniciarSistema() {
            if(html5QrCode && html5QrCode.isScanning) { html5QrCode.stop(); document.getElementById('qrReaderContainer').classList.add('hidden'); }
            document.getElementById('loginView').style.display = 'none';
            document.getElementById('mainApp').classList.remove('hidden');
            document.getElementById('tecnicoNombreBadge').innerText = appState.tecnico.nombre;
            
            const selEstPrest = document.getElementById('selectEstadoPrestamo');
            selEstPrest.innerHTML = '';
            ESTADOS_PRESTAMO.forEach(e => selEstPrest.innerHTML += `<option value="${e}" ${e==='Generado'?'selected':''}>${e}</option>`);
            
            const selEstInv = document.getElementById('selectEstadoInv');
            selEstInv.innerHTML = '';
            ESTADOS_INVENTARIO.forEach(e => selEstInv.innerHTML += `<option value="${e}" ${e==='Prestado'?'selected':''}>${e}</option>`);

            cargarDatosDesdeBackend();
        }

        function cerrarSesion() { localStorage.removeItem('uartes_tecnico_actas'); location.reload(); }

        function cargarDatosDesdeBackend() {
            showToast("Sincronizando con la nube...", "info");
            fetch(API_URL_ACTAS, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: 'obtener_datos' })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'ok') {
                    appState.plantillas = data.plantillas || {};
                    appState.tiposActa = data.tiposActa || [];
                    appState.prestamos = data.prestamos || [];
                    appState.inventario = data.inventario || {};
                    appState.formatos = data.formatos || {};
                    
                    if (Object.keys(appState.formatos).length === 0) {
                        appState.formatos['prestamo_EQUIPOS'] = {
                            plantilla: "prestamo", variable: "EQUIPOS", regex: "", fuente: "",
                            html: `<table style="width: 100%; border-collapse: collapse; margin: 15px 0;">\n    <thead>\n        <tr style="background-color: #f2f2f2;">\n            <th style="border: 1px solid #000; padding: 8px;">N°</th>\n            <th style="border: 1px solid #000; padding: 8px;">Cant.</th>\n            <th style="border: 1px solid #000; padding: 8px;">Descripción del Bien</th>\n            <th style="border: 1px solid #000; padding: 8px;">Código</th>\n        </tr>\n    </thead>\n    <tbody>\n        <tr>\n            <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{NUMERO}}</td>\n            <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{CANTIDAD}}</td>\n            <td style="border: 1px solid #000; padding: 6px;">{{DESCRIPCION}}</td>\n            <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{CODIGO}}</td>\n        </tr>\n    </tbody>\n</table>`
                        };
                    }
                    
                    sincronizarSelectsUI();
                    inicializarTinyMCE('tinymceEditor', cargarContenidoEditorPlantilla);
                    inicializarTinyMCE('tinymceFormatoTabla', cargarContenidoEditorFormato); 
                    showToast("✅ Sincronización completada.", "success");
                } else throw new Error(data.msg);
            })
            .catch(err => showToast("Fallo al obtener datos: " + err.message, "error"));
        }

        function sincronizarSelectsUI() {
            const selTipo = document.getElementById('selectTipoActa');
            const selEdit = document.getElementById('selectPlantillaEdit');
            const selPlantFormato = document.getElementById('selectPlantillaFormato');
            
            selTipo.innerHTML = ''; selEdit.innerHTML = ''; selPlantFormato.innerHTML = '<option value="">-- Seleccione una plantilla --</option>';
            appState.tiposActa.forEach(t => {
                const opt = `<option value="${t.nombre}">${t.nombre}</option>`;
                selTipo.innerHTML += opt; selEdit.innerHTML += opt; selPlantFormato.innerHTML += opt;
            });

            const selSol = document.getElementById('selectSolicitud');
            selSol.innerHTML = '<option value="">-- Llenado manual o seleccione una solicitud --</option>';
            appState.prestamos.forEach(p => {
                const isStudent = p.rolSolicitante === 'Estudiante';
                const nombreMostrar = isStudent ? `${p.nombreResponsable} (Resp.) / ${p.nombreSolicitante}` : p.nombreSolicitante;
                const desc = `[${p.id}] ${nombreMostrar} - ${p.motivo.substring(0,25)}...`;
                selSol.innerHTML += `<option value="${p.id}">${desc}</option>`;
            });

            if(appState.tiposActa.length > 0) {
                selTipo.value = appState.tiposActa[0].nombre;
                renderizarCamposDinamicos();
            }
        }

        function cambiarTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            let currentBtn;
            if (tabName === 'generar') {
                document.getElementById('tabGenerar').classList.add('active');
                currentBtn = document.querySelector('.tabs button:nth-child(1)');
                renderizarCamposDinamicos(); 
            } else if (tabName === 'plantillas') {
                document.getElementById('tabPlantillas').classList.add('active');
                currentBtn = document.querySelector('.tabs button:nth-child(2)');
                cargarContenidoEditorPlantilla();
            } else if (tabName === 'formatos') {
                document.getElementById('tabFormatos').classList.add('active');
                currentBtn = document.querySelector('.tabs button:nth-child(3)');
                cargarContenidoEditorFormato();
            }
            if(currentBtn) currentBtn.classList.add('active');
        }

        function inicializarTinyMCE(selectorId, onInitCallback) {
            tinymce.init({
                selector: '#' + selectorId,
                height: '100%',
                menubar: 'file edit view insert format tools table',
                plugins: 'table lists link code wordcount preview fullscreen searchreplace advlist autolink',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright justify | bullist numlist | table | code preview fullscreen',
                table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | tablecellprops tablemergecells tablesplitcells | tablebackgroundcolor tablebordercolor tablecellbackgroundcolor tablecellbordercolor',
                contextmenu: 'link table',
                table_advtab: true, table_cell_advtab: true, table_row_advtab: true, table_appearance_options: true,
                table_style_by_css: true,
                content_style: `table[border="0"], table[border="0"] td, table[border="0"] th, table[style*="border-width: 0"], table[style*="border-width: 0"] td { border: 1px dashed #aaaaaa !important; }`,
                skin: 'oxide', content_css: 'default', promotion: false,
                setup: function (editor) {
                    editor.on('init', function () { if (onInitCallback) onInitCallback(); });
                }
            });
        }

        function parseDateToInput(dateStr) {
            if (!dateStr) return "";
            let [datePart, timePart] = dateStr.split(' ');
            if (!datePart) return "";
            let [d, m, y] = datePart.split('/');
            if (y && m && d) {
                if (timePart) { let [th, tm] = timePart.split(':'); return `${y}-${m}-${d}T${th}:${tm}`; }
                return `${y}-${m}-${d}T00:00`;
            }
            try { let dObj = new Date(dateStr); if (!isNaN(dObj)) return dObj.toISOString().substring(0, 16); } catch(e) {}
            return "";
        }

        function renderizarCamposDinamicos() {
            const nombreActa = document.getElementById('selectTipoActa').value;
            const plantillaObj = appState.plantillas[nombreActa];
            const container = document.getElementById('dynamicFieldsContainer');
            container.innerHTML = '';

            if(!plantillaObj) return;

            const regex = /\{\{([A-Z0-9_]+)\}\}/g;
            let matches;
            const variables = new Set();
            while ((matches = regex.exec(plantillaObj.html)) !== null) { variables.add(matches[1]); }

            let standardFieldsHTML = '';
            let formatFieldsHTML = '';

            const dAhora = new Date();
            const fechaActualStr = dAhora.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });

            variables.forEach(v => {
                const formatKey = `${nombreActa}_${v}`;
                
                if (appState.formatos[formatKey]) {
                    const formatData = appState.formatos[formatKey];
                    formatFieldsHTML += `
                    <div class="form-group mt-3 p-3 bg-opacity-10 bg-blue-500 rounded border border-blue-200 shadow-inner">
                        <div class="flex justify-between items-center mb-2">
                            <label class="font-bold text-blue-700 dark:text-blue-300 m-0 text-xs uppercase tracking-wide"><i class="fa-solid fa-table"></i> ${v} (Formato Dinámico):</label>
                            ${v.includes('EQUIPO') ? `<button class="btn-success text-xs py-1.5 px-3 rounded shadow" onclick="abrirSelectorEquipos('${v}')"><i class="fa-solid fa-list-check"></i> Catálogo</button>` : ''}
                        </div>
                        <textarea id="var_${v}_RAW" rows="2" placeholder="Regex: ${formatData.regex || 'Ej. ID:Cant'}" oninput="actualizarPrevia()" class="mb-1 text-xs font-mono"></textarea>
                    </div>`;
                } else {
                    let type = "text";
                    let inputValue = "";
                    let readonlyAttr = "";

                    // Lógica para pre-llenar y bloquear visualmente las variables globales
                    if (v === 'FECHA_ACTUAL') {
                        inputValue = fechaActualStr; readonlyAttr = "disabled";
                    } else if (v === 'TECNICO_ID') {
                        inputValue = appState.tecnico ? appState.tecnico.id : "Desconocido"; readonlyAttr = "disabled";
                    } else if (v === 'TECNICO_NOMBRE') {
                        inputValue = appState.tecnico ? appState.tecnico.nombre : "Desconocido"; readonlyAttr = "disabled";
                    } else if (v === 'TECNICO_ROL') {
                        inputValue = (appState.tecnico && appState.tecnico.rol) ? appState.tecnico.rol : "Técnico"; readonlyAttr = "disabled";
                    } else if (v.includes('FECHA') || v.includes('HORA')) {
                        type = "datetime-local";
                    }
                    
                    const valAttrHtml = inputValue ? `value="${inputValue}"` : "";

                    if (v === 'MOTIVO' || v.includes('RAZON')) {
                        standardFieldsHTML += `<div class="form-group col-span-2"><label class="font-bold text-xs capitalize">${v.replace(/_/g, ' ').toLowerCase()}</label><textarea id="var_${v}" rows="2" oninput="actualizarPrevia()" ${readonlyAttr}>${inputValue}</textarea></div>`;
                    } else if (v === 'ESTADO') {
                        standardFieldsHTML += `<input type="hidden" id="var_${v}" value="Generado">`;
                    } else {
                        standardFieldsHTML += `<div class="form-group"><label class="font-bold text-xs capitalize">${v.replace(/_/g, ' ').toLowerCase()}</label><input type="${type}" id="var_${v}" oninput="actualizarPrevia()" ${valAttrHtml} ${readonlyAttr}></div>`;
                    }
                }
            });

            if (standardFieldsHTML) container.innerHTML += `<div class="grid grid-cols-2 gap-3">${standardFieldsHTML}</div>`;
            if (formatFieldsHTML) container.innerHTML += formatFieldsHTML;

            if (document.getElementById('selectSolicitud').value) poblarDesdeSolicitud();
            else actualizarPrevia();
        }

        function poblarDesdeSolicitud() {
            const idSol = document.getElementById('selectSolicitud').value;
            if(!idSol) { actualizarPrevia(); return; }
            const sol = appState.prestamos.find(s => s.id === idSol);
            if(!sol) return;

            const setVal = (id, val) => { const el = document.getElementById('var_' + id); if(el) el.value = val; };

            setVal('ID_PRESTAMO', sol.id);
            setVal('NOMBRE_SOLICITANTE', sol.nombreSolicitante);
            setVal('ROL_SOLICITANTE', sol.rolSolicitante);
            
            const rol = sol.rolSolicitante;
            if (rol !== 'Estudiante') {
                setVal('NOMBRE_RESPONSABLE', sol.nombreSolicitante); setVal('ROL_RESPONSABLE', rol); setVal('ID_RESPONSABLE', sol.idResponsable || "");
            } else {
                setVal('NOMBRE_RESPONSABLE', sol.nombreResponsable); setVal('ROL_RESPONSABLE', sol.rolResponsable); setVal('ID_RESPONSABLE', sol.idResponsable || "");
            }

            setVal('MOTIVO', sol.motivo);
            setVal('FECHA_Y_HORA_DE_PRESTAMO', parseDateToInput(sol.fechaInicio));
            setVal('FECHA_Y_HORA_DE_ENTREGA', parseDateToInput(sol.fechaFin));
            
            let eqMapeados = [];
            let eqRaw = (sol.equiposIds || "").split(',').map(s => s.trim()).filter(s => s !== "");
            eqRaw.forEach(e => { if(e.includes(':')) eqMapeados.push(e); else eqMapeados.push(`${e}:1`); });
            
            const nombreActa = document.getElementById('selectTipoActa').value;
            Object.keys(appState.formatos).forEach(k => {
                if(k.startsWith(nombreActa + '_')) {
                    setVal(`${appState.formatos[k].variable}_RAW`, eqMapeados.join(', '));
                }
            });
            actualizarPrevia();
        }

        function parseEquiposString(rawStr, regexStr) {
            const items = [];
            if (!rawStr || rawStr.trim() === "") return items;

            if (regexStr && regexStr.trim() !== "") {
                try {
                    const regex = new RegExp(regexStr, 'g');
                    let match;
                    let lastIndex = -1; 
                    
                    while ((match = regex.exec(rawStr)) !== null) {
                        if (match.index === lastIndex) { regex.lastIndex++; continue; }
                        lastIndex = match.index;

                        let customData = match.groups ? { ...match.groups } : {};
                        
                        // Inyectar posicionales
                        for (let i = 1; i < match.length; i++) {
                            if (match[i] !== undefined) customData[i.toString()] = match[i];
                        }
                        
                        let namedKeys = Object.keys(match.groups || {});
                        let idFallback = namedKeys.length > 0 ? match.groups[namedKeys[0]] : match[1];
                        let id = (customData.CODIGO || customData.ID || idFallback || "").trim().toUpperCase();
                        
                        let cantFallback = match[2] !== undefined ? match[2] : 1;
                        let cant = parseInt(customData.CANTIDAD || customData.CANT || customData.UNIDADES || cantFallback) || 1;

                        if (id) {
                            let cleanCustomData = {};
                            for (let k in customData) {
                                cleanCustomData[k.toUpperCase()] = customData[k];
                            }
                            items.push({ id, cant, customData: cleanCustomData });
                        }
                    }
                    if (items.length > 0) return items; 
                } catch (e) {
                    console.warn("Regex de formato inválido:", e);
                }
            }

            // Fallback sin Regex
            const partes = rawStr.split(',').map(s => s.trim()).filter(s => s !== "");
            partes.forEach(p => {
                const sec = p.split(':');
                const id = sec[0].trim().toUpperCase();
                const cant = sec.length > 1 ? parseInt(sec[1]) || 1 : 1;
                items.push({ id, cant, customData: { CODIGO: id, CANTIDAD: cant } });
            });
            return items;
        }

        function generarTablaEquiposHtml(rawIdsStr, formatVariable) {
            const nombreActa = document.getElementById('selectTipoActa').value;
            const formatKey = `${nombreActa}_${formatVariable}`;
            const formatData = appState.formatos[formatKey];
            
            if (!formatData || !formatData.html) return "<p>Error: Formato no encontrado.</p>";
            if (!rawIdsStr || rawIdsStr.trim() === "") return "<p style='color:red;'><i>No hay datos vinculados.</i></p>";
            
            // Extracción con DOM nativo (Reemplazo de Regex)
            const parser = new DOMParser();
            const doc = parser.parseFromString(formatData.html, 'text/html');
            const tbody = doc.querySelector('tbody');
            
            if (!tbody) return "<p style='color:red;'>Error: La variable de formato debe contener una tabla HTML válida con un &lt;tbody&gt;.</p>";

            const trTemplate = tbody.querySelector('tr');
            if (!trTemplate) return "<p style='color:red;'>Error: La variable de formato debe contener al menos un &lt;tr&gt; en el &lt;tbody&gt;.</p>";
            
            const filaStr = trTemplate.outerHTML;
            const regexAUsar = formatData.regex || "";
            const seleccionados = parseEquiposString(rawIdsStr, regexAUsar);
            let filasHTML = "";
            
            seleccionados.forEach((item, index) => {
                const invData = appState.inventario[item.id];
                const nombreItem = invData ? invData.desc : "Ítem no catalogado";
                const estadoItem = document.getElementById('selectEstadoInv').value || "Prestado";

                let filaHtml = filaStr;
                
                // Mapeo inteligente de imagen
                let urlImgFinal = (document.body.getAttribute('data-theme') === 'dark') ? logoClaro : logoOscuro;
                let realBasename = imageMap[item.id.toUpperCase()];
                if (realBasename && formatData.rutaImagen) {
                    urlImgFinal = formatData.rutaImagen.replace(/\{\{CODIGO\}\}/g, realBasename).replace(/\{\{ID\}\}/g, realBasename);
                }
                filaHtml = filaHtml.replace(/\{\{RUTA_IMAGEN\}\}/g, urlImgFinal);
                
                if (item.customData) {
                    for (const [key, value] of Object.entries(item.customData)) {
                        if (value !== undefined) {
                            const placeholderRegex = new RegExp(`\\{\\{${key.toUpperCase()}\\}\\}`, 'g');
                            filaHtml = filaHtml.replace(placeholderRegex, value);
                        }
                    }
                }

                filaHtml = filaHtml
                    .replace(/\{\{NUMERO\}\}/g, index + 1)
                    .replace(/\{\{CANTIDAD\}\}/g, item.cant)
                    .replace(/\{\{DESCRIPCION\}\}/g, nombreItem)
                    .replace(/\{\{DETALLE\}\}/g, nombreItem)
                    .replace(/\{\{CODIGO\}\}/g, item.id)
                    .replace(/\{\{ESTADO\}\}/g, estadoItem);
                
                filasHTML += filaHtml + "\n";
            });
            
            tbody.innerHTML = filasHTML;
            return doc.body.innerHTML;
        }

        function actualizarPrevia() {
            const nombreActa = document.getElementById('selectTipoActa').value;
            const container = document.getElementById('previewActaContainer');
            if(!nombreActa) { container.innerHTML = '<p class="text-center text-gray-500 mt-32 text-lg">Seleccione un tipo de acta.</p>'; return; }

            const plantillaObj = appState.plantillas[nombreActa];
            let html = plantillaObj ? plantillaObj.html : "<p>Plantilla en blanco.</p>";

            // --- INYECTAR PLACEHOLDERS GLOBALES ---
            const dAhora = new Date();
            const fechaActual = dAhora.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
            
            const tecId = appState.tecnico ? appState.tecnico.id : "Desconocido";
            const tecNombre = appState.tecnico ? appState.tecnico.nombre : "Desconocido";
            const tecRol = (appState.tecnico && appState.tecnico.rol) ? appState.tecnico.rol : "Técnico";
            
            html = html
                .replace(/\{\{FECHA_ACTUAL\}\}/g, fechaActual)
                .replace(/\{\{TECNICO_ID\}\}/g, tecId)
                .replace(/\{\{TECNICO_NOMBRE\}\}/g, tecNombre)
                .replace(/\{\{TECNICO_ROL\}\}/g, tecRol);

            const regex = /\{\{([A-Z0-9_]+)\}\}/g;
            let matches; const variables = new Set();
            while ((matches = regex.exec(html)) !== null) variables.add(matches[1]);

            if(document.getElementById('var_ESTADO')) document.getElementById('var_ESTADO').value = document.getElementById('selectEstadoPrestamo').value;

            variables.forEach(v => {
                const pattern = new RegExp(`\\{\\{${v}\\}\\}`, 'g');
                const formatKey = `${nombreActa}_${v}`;
                
                if (appState.formatos[formatKey]) {
                    const rawVal = document.getElementById(`var_${v}_RAW`)?.value || "";
                    html = html.replace(pattern, generarTablaEquiposHtml(rawVal, v));
                } else {
                    const el = document.getElementById(`var_${v}`);
                    let val = el ? el.value : "";
                    if (el && el.type === 'datetime-local' && val) val = new Date(val).toLocaleString('es-ES', { dateStyle: 'medium', timeStyle: 'short' });
                    else if (el && el.tagName === 'TEXTAREA') val = val.replace(/\n/g, '<br>');
                    html = html.replace(pattern, val || "________");
                }
            });

            container.innerHTML = html;
        }

        function renderizarPlaceholders(headers, tipo, contenedor) {
            contenedor.innerHTML = '';
            if(headers.length === 0) { contenedor.innerHTML = '<span class="text-xs opacity-50 italic mt-1 text-red-500">No se hallaron cabezales. Verifique el rango.</span>'; return; }
            
            headers.forEach(h => {
                if(!h || h.trim() === "") return;
                const norm = h.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-zA-Z0-9\s]/g, "").trim().replace(/\s+/g, '_').toUpperCase();
                const ph = `{{${norm}}}`;
                contenedor.innerHTML += `<button class="placeholder-btn text-xs px-2 py-1 rounded font-mono shadow-sm m-1 cursor-pointer" onclick="insertarPlaceholder('${ph}', '${tipo}')">${ph}</button>`;
            });

            if (tipo === 'plantilla') {
                const globalSeparator = document.createElement('div');
                globalSeparator.className = "w-full border-b border-gray-300 dark:border-gray-600 my-1";
                contenedor.appendChild(globalSeparator);
                
                ['FECHA_ACTUAL', 'TECNICO_ID', 'TECNICO_NOMBRE', 'TECNICO_ROL'].forEach(ph => {
                    contenedor.innerHTML += `<button class="placeholder-btn text-xs px-2 py-1 rounded font-mono shadow-sm m-1 cursor-pointer bg-purple-600 text-white border-none dark:bg-purple-700" onclick="insertarPlaceholder('{{${ph}}}', '${tipo}')" title="Variable Global">{{${ph}}}</button>`;
                });

                const plantillaActual = document.getElementById('selectPlantillaEdit').value;
                const formatKeys = Object.keys(appState.formatos).filter(k => appState.formatos[k].plantilla === plantillaActual);
                
                if (formatKeys.length > 0) {
                    const separator = document.createElement('div');
                    separator.className = "w-full border-b border-gray-300 dark:border-gray-600 my-1";
                    contenedor.appendChild(separator);
                    
                    formatKeys.forEach(k => {
                        const ph = `{{${appState.formatos[k].variable}}}`;
                        contenedor.innerHTML += `<button class="placeholder-btn text-xs px-2 py-1 rounded font-mono shadow-sm m-1 cursor-pointer" style="background-color: var(--primary); color: white; border: none;" onclick="insertarPlaceholder('${ph}', '${tipo}')" title="Formato Asociado a esta Plantilla">${ph}</button>`;
                    });
                }
            }
        }

        function cargarPlaceholders(tipo) {
            let inputFuente = tipo === 'plantilla' ? document.getElementById('fuentePlantilla').value : document.getElementById('fuenteFormato').value;
            let contenedor = tipo === 'plantilla' ? document.getElementById('placeholdersPlantilla') : document.getElementById('placeholdersFormato');
            
            if (!inputFuente.includes(';')) return showToast("Formato inválido. Use ID;Hoja!Rango", "error");
            contenedor.innerHTML = '<span class="text-xs opacity-50 italic mt-1"><i class="fa-solid fa-spinner fa-spin"></i> Conectando con Google Sheets...</span>';
            
            fetch(API_URL_ACTAS, {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ accion: 'obtener_encabezados', fuenteStr: inputFuente })
            }).then(r=>r.json()).then(data => {
                if (data.status === 'ok') {
                    localStorage.setItem('uartes_ph_cache_' + inputFuente, JSON.stringify(data.headers));
                    renderizarPlaceholders(data.headers, tipo, contenedor);
                    if (tipo === 'formato') validarRegexFormato();
                    showToast("✅ Placeholders actualizados desde la nube.", "success");
                } else {
                    contenedor.innerHTML = '<span class="text-xs opacity-50 italic mt-1 text-red-500">Error de conexión.</span>';
                    showToast(data.msg, "error");
                }
            }).catch(err => showToast("Fallo de red: " + err.message, "error"));
        }

        function insertarPlaceholder(ph, tipo) {
            let editorId = tipo === 'plantilla' ? 'tinymceEditor' : 'tinymceFormatoTabla';
            if (tinymce.get(editorId)) tinymce.get(editorId).insertContent(ph);
        }

        function validarRegexFormato() {
            const regexStr = document.getElementById('inputRegexFormato').value;
            const msgEl = document.getElementById('regexValidationMsg');
            const btnGuardar = document.getElementById('btnGuardarFormato');
            const contenedorRegex = document.getElementById('placeholdersRegex');

            if (!regexStr.trim()) {
                msgEl.classList.add('hidden');
                contenedorRegex.classList.add('hidden');
                if(btnGuardar) btnGuardar.disabled = false;
                return;
            }

            const fuente = document.getElementById('fuenteFormato').value;
            const cached = localStorage.getItem('uartes_ph_cache_' + fuente);
            let headers = [];
            if (cached) {
                headers = JSON.parse(cached).map(h => h.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-zA-Z0-9\s]/g, "").trim().replace(/\s+/g, '_').toUpperCase());
            }

            let isEscaped = false;
            let captureGroups = [];
            let groupIndex = 1;

            for (let i = 0; i < regexStr.length; i++) {
                let char = regexStr[i];
                if (char === '\\' && !isEscaped) {
                    isEscaped = true; continue;
                }
                if (char === '(' && !isEscaped) {
                    let next2 = regexStr.substring(i + 1, i + 3);
                    let next3 = regexStr.substring(i + 1, i + 4);
                    
                    if (next2 === '?:' || next2 === '?=' || next2 === '?!' || next3 === '?<=' || next3 === '?<!') {
                        // Grupo no-capturador
                    } else if (next2 === '?<') {
                        let nameEnd = regexStr.indexOf('>', i + 3);
                        if (nameEnd !== -1) {
                            let name = regexStr.substring(i + 3, nameEnd).toUpperCase();
                            captureGroups.push({ name: name, index: groupIndex });
                        }
                        groupIndex++;
                    } else {
                        captureGroups.push({ name: groupIndex.toString(), index: groupIndex });
                        groupIndex++;
                    }
                }
                isEscaped = false;
            }

            const gruposNombrados = captureGroups.filter(g => isNaN(g.name)).map(g => g.name);
            const interseccion = gruposNombrados.filter(g => headers.includes(g));

            if (captureGroups.length > 0) {
                contenedorRegex.innerHTML = '<span class="text-xs opacity-50 italic mt-1 w-full block">Variables dinámicas detectadas en tu Regex (Clic para insertar):</span>';
                captureGroups.forEach(g => {
                    const isRef = headers.includes(g.name);
                    const isNumbered = !isNaN(g.name);
                    
                    let colorClass = 'bg-blue-600 text-white dark:bg-blue-700';
                    let title = isNumbered ? `Grupo Posicional #${g.name}` : 'Variable Extraída (Custom)';
                    
                    if (isRef) {
                        colorClass = 'bg-green-600 text-white dark:bg-green-700';
                        title = 'Variable Referencial (Enlazada al Inventario)';
                    }

                    contenedorRegex.innerHTML += `<button class="placeholder-btn text-xs px-2 py-1 rounded font-mono shadow-sm m-1 cursor-pointer ${colorClass}" style="border: none;" onclick="insertarPlaceholder('{{${g.name}}}', 'formato')" title="${title}">{{${g.name}}}</button>`;
                });
                contenedorRegex.classList.remove('hidden');
            } else {
                contenedorRegex.innerHTML = '';
                contenedorRegex.classList.add('hidden');
            }

            if (interseccion.length > 1) {
                msgEl.innerHTML = `❌ Error: Múltiples grupos referenciales detectados (${interseccion.join(', ')}). Solo se permite 1.`;
                msgEl.className = 'text-[10px] font-bold mt-1 block text-red-600 dark:text-red-400';
                if(btnGuardar) btnGuardar.disabled = true; 
            } else if (interseccion.length === 1) {
                msgEl.innerHTML = `✅ Referencia enlazada correctamente: ${interseccion[0]}`;
                msgEl.className = 'text-[10px] font-bold mt-1 block text-green-600 dark:text-green-400';
                if(btnGuardar) btnGuardar.disabled = false;
            } else if (headers.length > 0 && captureGroups.length > 0) {
                msgEl.innerHTML = `⚠️ Advertencia: Ningún grupo nombrado coincide con los campos referenciales de la base de datos.`;
                msgEl.className = 'text-[10px] font-bold mt-1 block text-yellow-600 dark:text-yellow-400';
                if(btnGuardar) btnGuardar.disabled = false; 
            } else {
                msgEl.classList.add('hidden');
                if(btnGuardar) btnGuardar.disabled = false;
            }
        }

        function cargarContenidoEditorPlantilla() {
            const nombre = document.getElementById('selectPlantillaEdit').value;
            const plantillaObj = appState.plantillas[nombre];
            if (tinymce.get('tinymceEditor')) {
                tinymce.get('tinymceEditor').setContent(plantillaObj ? plantillaObj.html : "");
            }
            
            document.getElementById('fuentePlantilla').value = plantillaObj ? plantillaObj.fuente : "";
            const cachedHeaders = localStorage.getItem('uartes_ph_cache_' + (plantillaObj ? plantillaObj.fuente : ""));
            
            if (cachedHeaders && plantillaObj && plantillaObj.fuente) {
                renderizarPlaceholders(JSON.parse(cachedHeaders), 'plantilla', document.getElementById('placeholdersPlantilla'));
            } else {
                document.getElementById('placeholdersPlantilla').innerHTML = '<span class="text-xs opacity-50 italic mt-1">Haga clic en Cargar para obtener las variables.</span>';
            }
        }

        function crearNuevaPlantilla() {
            const nombre = document.getElementById('nuevoTipoInput').value.trim();
            if(!nombre) return showToast("Ingrese el nombre de la plantilla.", "error");
            if(appState.plantillas[nombre]) return showToast("Este tipo de acta ya existe.", "error");
            
            appState.plantillas[nombre] = { html: `<h2 style="text-align: center;">ACTA DE ${nombre.toUpperCase()}</h2>`, fuente: "" };
            appState.tiposActa.push({ nombre });
            sincronizarSelectsUI(); document.getElementById('selectPlantillaEdit').value = nombre;
            document.getElementById('nuevoTipoInput').value = ''; cargarContenidoEditorPlantilla();
            showToast("Plantilla creada. Presione Guardar Cambios para subir a la nube.", "success");
        }

        function guardarPlantilla() {
            const nombre = document.getElementById('selectPlantillaEdit').value;
            if(!nombre) return;
            const btn = document.getElementById('btnGuardarPlantilla');
            const original = btn.innerHTML; btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Subiendo...'; btn.disabled = true;

            const html = tinymce.get('tinymceEditor').getContent();
            const fuente = document.getElementById('fuentePlantilla').value;
            
            fetch(API_URL_ACTAS, { method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: 'guardar_plantilla', nombre, contenido: html, fuente })
            }).then(r=>r.json()).then(data => {
                if(data.status === 'ok') {
                    appState.plantillas[nombre] = { html, fuente };
                    showToast("✅ Plantilla guardada exitosamente.", "success");
                    if (document.getElementById('selectTipoActa').value === nombre) renderizarCamposDinamicos();
                } else throw new Error(data.msg);
            }).catch(e => showToast("Error al guardar: " + e.message, "error")).finally(() => { btn.innerHTML = original; btn.disabled = false; });
        }

        function cargarVariablesDePlantilla() {
            const plantillaName = document.getElementById('selectPlantillaFormato').value;
            const selVar = document.getElementById('selectVariableFormato');
            selVar.innerHTML = '<option value="">-- Seleccione Variable --</option>';
            
            if (!plantillaName || !appState.plantillas[plantillaName]) {
                cargarContenidoEditorFormato();
                return;
            }
            
            const fuente = appState.plantillas[plantillaName].fuente;
            
            if (!fuente) {
                selVar.innerHTML += `<option value="" disabled>Plantilla sin fuente definida en G. Sheets.</option>`;
                cargarContenidoEditorFormato();
                return;
            }

            const cachedHeaders = localStorage.getItem('uartes_ph_cache_' + fuente);
            
            const renderizarOpciones = (headers) => {
                selVar.innerHTML = '<option value="">-- Seleccione Variable --</option>';
                if (headers && headers.length > 0) {
                    headers.forEach(h => {
                        if(!h || h.trim() === "") return;
                        const norm = h.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-zA-Z0-9\s]/g, "").trim().replace(/\s+/g, '_').toUpperCase();
                        selVar.innerHTML += `<option value="${norm}">${norm}</option>`;
                    });
                } else {
                    selVar.innerHTML += `<option value="" disabled>No se encontraron columnas.</option>`;
                }
                cargarContenidoEditorFormato();
            };

            if (cachedHeaders) {
                renderizarOpciones(JSON.parse(cachedHeaders));
            } else {
                selVar.innerHTML = '<option value="">-- Cargando desde Google Sheets... --</option>';
                fetch(API_URL_ACTAS, {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'obtener_encabezados', fuenteStr: fuente })
                }).then(r=>r.json()).then(data => {
                    if (data.status === 'ok') {
                        localStorage.setItem('uartes_ph_cache_' + fuente, JSON.stringify(data.headers));
                        renderizarOpciones(data.headers);
                    } else {
                        selVar.innerHTML = '<option value="" disabled>Error de conexión</option>';
                        cargarContenidoEditorFormato();
                    }
                }).catch(err => {
                    selVar.innerHTML = '<option value="" disabled>Error de red</option>';
                    cargarContenidoEditorFormato();
                });
            }
        }

        function cargarContenidoEditorFormato() {
            const plantillaName = document.getElementById('selectPlantillaFormato').value;
            const variableName = document.getElementById('selectVariableFormato').value;
            
            const key = `${plantillaName}_${variableName}`;
            const data = appState.formatos[key];
            
            if (tinymce.get('tinymceFormatoTabla')) {
                tinymce.get('tinymceFormatoTabla').setContent(data ? data.html : (variableName ? "<table><tbody><tr><td>{{EJEMPLO}}</td></tr></tbody></table>" : ""));
            }
            
            document.getElementById('inputRegexFormato').value = data ? data.regex : "";
            document.getElementById('fuenteFormato').value = data ? data.fuente : "";
            document.getElementById('inputRutaImagenFormato').value = data && data.rutaImagen ? data.rutaImagen : "";
            
            const cachedHeaders = localStorage.getItem('uartes_ph_cache_' + (data ? data.fuente : ""));
            if (cachedHeaders && data && data.fuente) renderizarPlaceholders(JSON.parse(cachedHeaders), 'formato', document.getElementById('placeholdersFormato'));
            else document.getElementById('placeholdersFormato').innerHTML = '<span class="text-xs opacity-50 italic mt-1">Haga clic en Cargar para obtener los placeholders.</span>';
            
            validarRegexFormato();
        }

        function guardarFormatoTablaNube(event) {
            const plantilla = document.getElementById('selectPlantillaFormato').value;
            const variable = document.getElementById('selectVariableFormato').value;
            const regexFormato = document.getElementById('inputRegexFormato').value;
            const formato = tinymce.get('tinymceFormatoTabla').getContent();
            const fuente = document.getElementById('fuenteFormato').value;
            const rutaImagen = document.getElementById('inputRutaImagenFormato').value;
            
            if(!plantilla) return showToast("Seleccione una plantilla primero.", "error");
            if(!variable) return showToast("Seleccione una variable primero.", "error");
            if (!/(<tbody[^>]*>)([\s\S]*?)(<\/tbody>)/i.test(formato)) return showToast("⚠️ Error: El formato debe contener al menos una tabla HTML válida con cuerpo (<tbody>).", "error");

            const btn = event.currentTarget; const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Subiendo...'; btn.disabled = true;

            fetch(API_URL_ACTAS, { method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: 'guardar_formato', variable, regex: regexFormato, plantilla, formato, fuente, rutaImagen })
            }).then(r=>r.json()).then(data => {
                if (data.status === 'ok') {
                    appState.formatos[`${plantilla}_${variable}`] = { plantilla, variable, regex: regexFormato, html: formato, fuente, rutaImagen };
                    showToast("✅ Formato guardado en Google Sheets.", "success");
                    renderizarCamposDinamicos();
                } else throw new Error(data.msg);
            }).catch(err => showToast("Error: " + err.message, "error")).finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }

        function guardarActaEnSistema(overwrite = false) {
            const prestamoInput = document.getElementById('var_ID_PRESTAMO');
            if (!prestamoInput || !prestamoInput.value.trim()) return showToast("⚠ La plantilla actual no incluye un campo {{ID_PRESTAMO}} válido para el sistema.", "error");
            
            const prestamoId = prestamoInput.value.trim();
            const actaId = prestamoId;
            
            let allEqIds = [];
            const nombreActa = document.getElementById('selectTipoActa').value;
            const regexKeys = Object.keys(appState.formatos).filter(k => k.startsWith(nombreActa + '_'));
            
            regexKeys.forEach(k => { 
                const formatData = appState.formatos[k];
                const varName = formatData.variable;
                const el = document.getElementById(`var_${varName}_RAW`); 
                if (el && varName.includes('EQUIPO')) {
                    const parsed = parseEquiposString(el.value, formatData.regex);
                    parsed.forEach(item => allEqIds.push(item.id));
                }
            });
            const eqIds = allEqIds;
            
            const estInv = document.getElementById('selectEstadoInv').value;
            const estPrest = document.getElementById('selectEstadoPrestamo').value;

            fetch(API_URL_ACTAS, { method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ accion: 'guardar_acta', prestamoId, actaId, equiposIds: eqIds, estadoInventario: estInv, estadoPrestamo: estPrest, overwrite })
            }).then(r=>r.json()).then(data => {
                if (data.status === 'conflict') { if(confirm(data.msg)) guardarActaEnSistema(true); }
                else if (data.status === 'ok') showToast("✅ Acta vinculada exitosamente y estados de inventario actualizados.", "success");
                else showToast("Error del servidor: " + data.msg, "error");
            }).catch(err => showToast("Fallo de red al guardar el acta: " + err.message, "error"));
        }

        function copiarActaPortapapeles() {
            const previewContainer = document.getElementById('previewActaContainer');
            if (previewContainer.innerText.includes("Seleccione un tipo de acta")) return showToast("No hay datos para copiar.", "error");
            
            if (navigator.clipboard && window.ClipboardItem) {
                const htmlWrapper = `<div style="color: #000000; background-color: #ffffff; font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.15;">${previewContainer.innerHTML}</div>`;
                const htmlBlob = new Blob([htmlWrapper], { type: 'text/html' });
                const textBlob = new Blob([previewContainer.innerText], { type: 'text/plain' });
                const item = new ClipboardItem({ 'text/html': htmlBlob, 'text/plain': textBlob });
                navigator.clipboard.write([item]).then(() => showToast("✅ Acta copiada. Pega en Word con Ctrl+V.", "success")).catch(() => fallbackCopy(previewContainer));
            } else fallbackCopy(previewContainer);
        }
        function fallbackCopy(element) {
            const range = document.createRange(); range.selectNodeContents(element); const sel = window.getSelection(); sel.removeAllRanges(); sel.addRange(range);
            try { document.execCommand('copy'); showToast("✅ Acta copiada.", "success"); } catch (err) { showToast("❌ Bloqueado por el navegador.", "error"); }
            sel.removeAllRanges();
        }

        const winEquipos = document.getElementById('modalEquiposWindow');
        const headerEquipos = document.getElementById('modalEquiposHeader');
        let isDragging = false, dragOffsetX = 0, dragOffsetY = 0;

        headerEquipos.addEventListener('mousedown', (e) => {
            if(winEquipos.classList.contains('maximized')) return;
            isDragging = true; winEquipos.classList.add('dragging');
            const rect = winEquipos.getBoundingClientRect(); dragOffsetX = e.clientX - rect.left; dragOffsetY = e.clientY - rect.top;
        });
        document.addEventListener('mousemove', (e) => { if (isDragging) { winEquipos.style.left = (e.clientX - dragOffsetX) + 'px'; winEquipos.style.top = (e.clientY - dragOffsetY) + 'px'; } });
        document.addEventListener('mouseup', () => { if(isDragging) { isDragging = false; winEquipos.classList.remove('dragging'); } });

        function toggleMaximizarVentana() {
            const icon = document.getElementById('iconMaximize'); winEquipos.classList.toggle('maximized');
            if (winEquipos.classList.contains('maximized')) icon.classList.replace('fa-window-maximize', 'fa-window-restore'); 
            else icon.classList.replace('fa-window-restore', 'fa-window-maximize');
        }

        function abrirSelectorEquipos(targetVariable) {
            activeEquipmentTarget = targetVariable;
            document.getElementById('targetEquipmentName').innerText = `[${targetVariable}]`;
            
            const container = document.getElementById('listaEquiposContainer');
            document.getElementById('filtroEquipos').value = ""; 
            
            const rawTextarea = document.getElementById('var_' + targetVariable + '_RAW');
            const formatKey = `${document.getElementById('selectTipoActa').value}_${targetVariable}`;
            const formatData = appState.formatos[formatKey];
            const regexStr = formatData ? formatData.regex : "";
            
            const dictSelecciones = {};
            if(rawTextarea) parseEquiposString(rawTextarea.value, regexStr).forEach(s => dictSelecciones[s.id] = s.cant);
            
            container.innerHTML = '';
            if (Object.keys(appState.inventario).length === 0) { container.innerHTML = `<p class="col-span-full text-center text-gray-500 my-10">No hay equipos en inventario.</p>`; return; }

            const grupos = {};
            for (const [id, data] of Object.entries(appState.inventario)) {
                if (!grupos[data.desc]) grupos[data.desc] = { ids: [], desc: data.desc, totalStock: 0 };
                grupos[data.desc].ids.push(id); grupos[data.desc].totalStock += data.stock;
            }

            for (const [desc, grupo] of Object.entries(grupos)) {
                let cantSeleccionada = 0;
                grupo.ids.forEach(id => { if (dictSelecciones[id]) cantSeleccionada += dictSelecciones[id]; });
                const max = grupo.totalStock;
                let imgIdEncontrado = grupo.ids.find(id => imageMap[id.toUpperCase()]);
                let imgSrc = imgIdEncontrado ? imgUrlBase + imageMap[imgIdEncontrado.toUpperCase()] : (document.body.getAttribute('data-theme') === 'dark' ? logoClaro : logoOscuro);
                const chkId = "chk-" + grupo.ids[0].replace(/\W/g, ''); 
                
                let htmlCantidad = max > 1 ? `<input type="number" class="input-cant-modal" min="1" max="${max}" value="${cantSeleccionada > 0 ? cantSeleccionada : 1}" onclick="event.stopPropagation()" onchange="document.getElementById('${chkId}').checked = true">` : "";

                container.innerHTML += `
                    <label class="equipo-item" title="${desc}" onclick="if(event.target.tagName !== 'INPUT') document.getElementById('${chkId}').click()">
                        <input type="checkbox" id="${chkId}" class="chk-equipo-modal" ${cantSeleccionada > 0 ? 'checked' : ''} data-ids="${grupo.ids.join(',')}" onclick="event.stopPropagation()">
                        ${htmlCantidad}
                        <img src="${imgSrc}" class="item-thumb-modal ${!imgIdEncontrado ? 'fallback-thumb' : ''}" onerror="this.src='${document.body.getAttribute('data-theme') === 'dark' ? logoClaro : logoOscuro}'; this.classList.add('fallback-thumb');">
                        <div class="leading-tight w-full">
                            <span class="text-xs font-bold block mb-1 opacity-90 truncate whitespace-normal" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">${desc}</span>
                            <span class="text-[10px] text-gray-500 font-mono block">${max > 1 ? `Stock: ${max}` : grupo.ids[0]}</span>
                        </div>
                    </label>
                `;
            }
            winEquipos.classList.remove('hidden');
        }

        function cerrarSelectorEquipos() { winEquipos.classList.add('hidden'); }
        
        function filtrarListaEquipos() {
            const query = document.getElementById('filtroEquipos').value.toLowerCase();
            document.querySelectorAll('.equipo-item').forEach(item => item.style.display = item.innerText.toLowerCase().includes(query) ? 'flex' : 'none');
        }

        function confirmarSeleccionEquipos() {
            const targetTextarea = document.getElementById('var_' + activeEquipmentTarget + '_RAW');
            if(!targetTextarea) return;
            
            const formatKey = `${document.getElementById('selectTipoActa').value}_${activeEquipmentTarget}`;
            const formatData = appState.formatos[formatKey];
            const regexStr = formatData ? formatData.regex : "";
            
            const dictPrevio = {};
            parseEquiposString(targetTextarea.value, regexStr).forEach(s => dictPrevio[s.id] = s.cant);
            let seleccionesFinales = [];
            
            document.querySelectorAll('.chk-equipo-modal:checked').forEach(chk => {
                const allIds = chk.getAttribute('data-ids').split(',');
                const inputCant = chk.closest('.equipo-item').querySelector('.input-cant-modal');
                let cantidadRequerida = inputCant ? (parseInt(inputCant.value) || 1) : 1;
                
                for (let id of allIds) {
                    if (cantidadRequerida <= 0) break;
                    if (dictPrevio[id]) { let tomar = Math.min(appState.inventario[id].stock, cantidadRequerida); seleccionesFinales.push(`${id}:${tomar}`); cantidadRequerida -= tomar; }
                }
                for (let id of allIds) {
                    if (cantidadRequerida <= 0) break;
                    if (!dictPrevio[id] && appState.inventario[id].stock > 0) { let tomar = Math.min(appState.inventario[id].stock, cantidadRequerida); seleccionesFinales.push(`${id}:${tomar}`); cantidadRequerida -= tomar; }
                }
            });
            
            targetTextarea.value = seleccionesFinales.join(', ');
            actualizarPrevia(); cerrarSelectorEquipos(); showToast("✅ Equipos actualizados.", "success");
        }

        window.onload = function() {
            const savedTech = localStorage.getItem('uartes_tecnico_actas');
            if (savedTech) { appState.tecnico = JSON.parse(savedTech); iniciarSistema(); }
        }
    </script>
</body>
</html>