<?php
/* ==========================================================================
   1. VARIABLES GLOBALES DE CONFIGURACIÓN (COLORES HEX)
   ========================================================================== */
// Paleta Principal
$colorPrimario    = getenv('COLOR_PRIMARIO')    ?: '#2B747F'; // Azul UArtes
$colorSecundario  = getenv('COLOR_SECUNDARIO')  ?: '#95CFD2'; // Azul variante
$colorAcento      = getenv('COLOR_ACENTO')      ?: '#90caf9'; // Para detalles en modo oscuro

// Paleta Modo Claro
$bgClaro          = getenv('BG_CLARO')          ?: '#f4f4f9';
$bgTarjetaClaro   = getenv('BG_TARJETA_CLARO')  ?: '#ffffff';
$textoClaro       = getenv('TEXTO_CLARO')       ?: '#333333';

// Paleta Modo Oscuro
$bgOscuro         = getenv('BG_OSCURO')         ?: '#121212';
$bgTarjetaOscuro  = getenv('BG_TARJETA_OSCURO') ?: '#1e1e1e';
$textoOscuro      = getenv('TEXTO_OSCURO')      ?: '#e0e0e0';

/* ==========================================================================
   2. RUTAS DE LOGOTIPOS (Claro y Oscuro) - RUTAS RELATIVAS
   ========================================================================== */
$logoUArtesClaro  = getenv('LOGO_UARTES_CLARO')  ?: 'logo-ua-vertical-grises-fondo-negro.png';
$logoUArtesOscuro = getenv('LOGO_UARTES_OSCURO') ?: 'logo-ua-vertical-fondo-oscuro.png';

$logoDptoClaro    = getenv('LOGO_DPTO_CLARO')    ?: 'Logo-OFICIAL-RECINTO-LAB-claro.png';
$logoDptoOscuro   = getenv('LOGO_DPTO_OSCURO')   ?: 'Logo-OFICIAL-RECINTO-LAB-oscuro.png';

/* ==========================================================================
   3. METADATA Y COMPARTICIÓN (Open Graph)
   ========================================================================== */
$tituloPagina     = getenv('TITULO_PAGINA')     ?: 'Dpto. Transversal - UArtes';
$descripcionPagina= getenv('DESC_PAGINA')       ?: 'Plataforma centralizada de servicios, reservas de espacios y préstamos de equipos del Departamento Transversal de la Universidad de las Artes.';

// Cálculo dinámico de la URL absoluta base apuntando a la carpeta de este archivo
$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$urlBase = rtrim($protocolo . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']), '/\\');

// Construcción de la URL absoluta requerida estrictamente por redes sociales
$ogImagenRelativa = getenv('OG_IMAGEN')         ?: $logoDptoOscuro; 
$ogImagenAbsoluta = $urlBase . '/' . ltrim($ogImagenRelativa, '/');

/* ==========================================================================
   4. BASE DE DATOS MODULAR (Servicios y Redes)
   ========================================================================== */
$modulos = [
    [
        "id" => "reservas",
        "titulo" => "Reserva de Espacios",
        "descripcion" => "Visualiza la disponibilidad y gestiona la reserva de los laboratorios RecintoLab y Mediaco.",
        "icono" => "🗓️", 
        "enlace" => "/reservas"
    ],
    [
        "id" => "prestamos",
        "titulo" => "Préstamo de Equipos",
        "descripcion" => "Catálogo de inventario y sistema de solicitud de hardware, periféricos y herramientas.",
        "icono" => "💻",
        "enlace" => "/catalogo"
    ]
];

$redesSociales = [
    [
        "nombre" => "Instagram",
        "icono" => "📷", 
        "url" => "https://instagram.com/transversal.uartes"
    ],
    [
        "nombre" => "Correo",
        "icono" => "✉️",
        "url" => "mailto:direccion.transver@uartes.edu.ec"
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloPagina); ?></title>
    
    <!-- METADATA PARA COMPARTIR (Open Graph / WhatsApp / Telegram) -->
    <meta property="og:title" content="<?php echo htmlspecialchars($tituloPagina); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($descripcionPagina); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($ogImagenAbsoluta); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($urlBase); ?>/">
    <meta property="og:type" content="website">

    <!-- METADATA PARA TWITTER CARDS -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($tituloPagina); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($descripcionPagina); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImagenAbsoluta); ?>">

    <!-- Script bloqueante para evitar parpadeos visuales al cargar la página -->
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    <style>
        /* ==========================================================================
           VARIABLES GLOBALES (Inyectadas desde PHP)
           ========================================================================== */
        :root {
            /* Colores Base */
            --color-primario: <?php echo $colorPrimario; ?>;
            --color-secundario: <?php echo $colorSecundario; ?>;
            --color-acento: <?php echo $colorAcento; ?>;
            
            /* TEMA CLARO (Por defecto) */
            --bg-body: <?php echo $bgClaro; ?>;
            --text-main: <?php echo $textoClaro; ?>;
            --bg-card: <?php echo $bgTarjetaClaro; ?>;
            --border-card: rgba(0, 0, 0, 0.1);
            
            --bg-header: var(--color-primario);
            --text-header: #ffffff;
            --bg-footer: #222222;
            
            --shadow-card: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.15);
            --transicion: all 0.3s ease;
        }

        /* TEMA OSCURO */
        [data-theme="dark"] {
            --bg-body: <?php echo $bgOscuro; ?>;
            --text-main: <?php echo $textoOscuro; ?>;
            --bg-card: <?php echo $bgTarjetaOscuro; ?>;
            --border-card: #333333;
            
            --bg-header: #1a1a1a;
            --text-header: #e0e0e0;
            --bg-footer: #0a0a0a;
            
            --shadow-card: 0 4px 6px rgba(0, 0, 0, 0.5);
            --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.8);
            
            /* En modo oscuro, usamos el color de acento para los títulos */
            --color-primario: var(--color-acento); 
        }

        /* ==========================================================================
           ESTILOS BASE
           ========================================================================== */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: background-color var(--transicion), color var(--transicion);
        }

        /* ==========================================================================
           ENCABEZADO (Header)
           ========================================================================== */
        header {
            background-color: var(--bg-header);
            color: var(--text-header);
            padding: 20px;
            box-shadow: var(--shadow-card);
            transition: background-color var(--transicion);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .logos-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-img {
            height: 60px;
            width: auto;
            object-fit: contain;
            transition: opacity var(--transicion);
        }

        .header-titles {
            flex: 1;
            text-align: center;
            min-width: 250px;
        }

        .header-titles h1 { font-size: 2.2rem; margin-bottom: 5px; }
        .header-titles p { font-size: 1.1rem; opacity: 0.9; }

        /* Botón de Tema */
        #themeToggleBtn {
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--text-header);
            font-size: 1.5rem;
            cursor: pointer;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transicion);
        }
        #themeToggleBtn:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        /* ==========================================================================
           CONTENEDOR PRINCIPAL
           ========================================================================== */
        main {
            flex: 1;
            padding: 50px 20px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .modulos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
        }

        .tarjeta-modulo {
            background-color: var(--bg-card);
            border: 2px solid var(--border-card);
            border-radius: 12px;
            padding: 40px 30px;
            text-decoration: none;
            color: var(--text-main);
            box-shadow: var(--shadow-card);
            transition: var(--transicion);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .tarjeta-modulo:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: var(--color-secundario);
        }

        .tarjeta-icono { font-size: 4rem; margin-bottom: 20px; }
        .tarjeta-titulo { font-size: 1.6rem; margin-bottom: 15px; color: var(--color-primario); transition: color var(--transicion); }
        .tarjeta-desc { font-size: 1.05rem; line-height: 1.6; opacity: 0.8; }

        /* ==========================================================================
           PIE DE PÁGINA
           ========================================================================== */
        footer {
            background-color: var(--bg-footer);
            color: #ffffff;
            text-align: center;
            padding: 40px 20px;
            transition: background-color var(--transicion);
        }

        .redes-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .enlace-red {
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            transition: var(--transicion);
        }
        .enlace-red:hover { background-color: var(--color-secundario); transform: scale(1.05); }

        @media (max-width: 768px) {
            .header-container { flex-direction: column; text-align: center; }
            .header-titles h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <header>
        <div class="header-container">
            <!-- Contenedor de Logos (IDs para manipulación por JS) -->
            <div class="logos-container">
                <img id="imgLogoUArtes" src="" alt="UArtes" class="logo-img">
                <img id="imgLogoDpto" src="" alt="Dpto. Transversal" class="logo-img">
            </div>

            <div class="header-titles">
                <h1>Departamento Transversal</h1>
                <p>Universidad de las Artes</p>
            </div>

            <button id="themeToggleBtn" title="Cambiar tema oscuro/claro">🌙</button>
        </div>
    </header>

    <!-- CONTENEDOR PRINCIPAL MODULAR -->
    <main>
        <div class="modulos-grid">
            <?php foreach ($modulos as $modulo): ?>
                <a href="<?php echo htmlspecialchars($modulo['enlace']); ?>" class="tarjeta-modulo">
                    <div class="tarjeta-icono"><?php echo $modulo['icono']; ?></div>
                    <h2 class="tarjeta-titulo"><?php echo htmlspecialchars($modulo['titulo']); ?></h2>
                    <p class="tarjeta-desc"><?php echo htmlspecialchars($modulo['descripcion']); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- PIE DE PÁGINA MODULAR -->
    <footer>
        <h3>Conecta con nosotros</h3>
        <div class="redes-container">
            <?php foreach ($redesSociales as $red): ?>
                <a href="<?php echo htmlspecialchars($red['url']); ?>" target="_blank" rel="noopener noreferrer" class="enlace-red">
                    <span><?php echo $red['icono']; ?></span>
                    <span><?php echo htmlspecialchars($red['nombre']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <p style="margin-top: 25px; font-size: 0.9rem; opacity: 0.5;">&copy; <?php echo date("Y"); ?> UArtes - Dpto. Transversal</p>
    </footer>

    <!-- LÓGICA DE INTERACTIVIDAD -->
    <script>
        // Exportando variables de PHP a JavaScript de forma segura, sumando la URL base calculada
        const urlBase = "<?php echo addslashes($urlBase); ?>";
        
        const LOGOS = {
            uartes: {
                light: urlBase + "/" + "<?php echo addslashes(ltrim($logoUArtesClaro, '/')); ?>",
                dark: urlBase + "/" + "<?php echo addslashes(ltrim($logoUArtesOscuro, '/')); ?>"
            },
            dpto: {
                light: urlBase + "/" + "<?php echo addslashes(ltrim($logoDptoClaro, '/')); ?>",
                dark: urlBase + "/" + "<?php echo addslashes(ltrim($logoDptoOscuro, '/')); ?>"
            }
        };

        document.addEventListener("DOMContentLoaded", () => {
            const btnTheme = document.getElementById('themeToggleBtn');
            const htmlElement = document.documentElement;
            
            const imgUArtes = document.getElementById('imgLogoUArtes');
            const imgDpto = document.getElementById('imgLogoDpto');

            // Función maestra para aplicar el tema en Interfaz y Logos
            const applyTheme = (theme) => {
                // Configurar CSS y Botón
                htmlElement.setAttribute('data-theme', theme);
                btnTheme.innerText = theme === 'dark' ? '☀️' : '🌙';
                
                // Actualizar URLs de logotipos
                imgUArtes.src = theme === 'dark' ? LOGOS.uartes.dark : LOGOS.uartes.light;
                imgDpto.src = theme === 'dark' ? LOGOS.dpto.dark : LOGOS.dpto.light;
            };

            // Aplicar el estado inicial (Leído del localStorage)
            const initialTheme = htmlElement.getAttribute('data-theme');
            applyTheme(initialTheme);

            // Escuchador de evento de Click para alternar el tema
            btnTheme.addEventListener('click', () => {
                const currentTheme = htmlElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                applyTheme(newTheme);
                localStorage.setItem('theme', newTheme);
            });
        });
    </script>
</body>
</html>