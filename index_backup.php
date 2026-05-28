<?php
require_once 'config.php';
require_once 'config_backup.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizador Futurista - Monroy</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js para la lógica reactiva ligera -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Space+Grotesk:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        mono: ['Space Grotesk', 'monospace'],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#ED3426', // Mudanzas Hermanos Monroy Orange
                            neon: '#ff5544',
                            dark: '#0f172a', // slate-900
                            darker: '#020617', // slate-950
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #020617;
            background-image: 
                radial-gradient(at 0% 0%, rgba(237, 52, 38, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(56, 189, 248, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
            color: #f8fafc;
        }

        /* Glassmorphism utility */
        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
        }

        /* Futuristic Inputs */
        .input-cyber {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }
        .input-cyber:focus {
            outline: none;
            border-color: #ff5544;
            box-shadow: 0 0 15px rgba(237, 52, 38, 0.3);
            background: rgba(255, 255, 255, 0.08);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: rgba(237, 52, 38, 0.5); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(237, 52, 38, 0.8); }

        /* Step transitions */
        .step-enter { animation: slideIn 0.5s forwards cubic-bezier(0.16, 1, 0.3, 1); opacity: 0; }
        @keyframes slideIn {
            from { transform: translateX(20px) scale(0.98); opacity: 0; }
            to { transform: translateX(0) scale(1); opacity: 1; }
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4 overflow-hidden" x-data="movingBot()">

    <!-- Decorative ambient elements -->
    <div class="fixed top-20 left-20 w-64 h-64 bg-brand-neon/20 rounded-full mix-blend-screen filter blur-[100px] animate-pulse-slow pointer-events-none"></div>
    <div class="fixed bottom-20 right-20 w-80 h-80 bg-blue-500/10 rounded-full mix-blend-screen filter blur-[100px] animate-pulse-slow pointer-events-none" style="animation-delay: 1s;"></div>

    <!-- Main Wizard Container -->
    <div class="w-full max-w-2xl glass-panel rounded-3xl relative z-10 overflow-hidden flex flex-col h-[650px] max-h-[90vh]">
        
        <!-- Header / Progress Area -->
        <header class="p-6 border-b border-white/10 flex justify-between items-center relative">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-brand to-orange-400 flex items-center justify-center shadow-[0_0_15px_rgba(237,52,38,0.5)]">
                    <span class="material-symbols-rounded text-white text-xl">smart_toy</span>
                </div>
                <div>
                    <h1 class="text-sm font-mono text-gray-400 tracking-wider">SISTEMA MUDANZAS MONROY</h1>
                    <p class="text-lg font-semibold bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400" x-text="step < 9 ? 'Asistente de IA' : 'Análisis Completado'"></p>
                </div>
            </div>
            
            <!-- Progress Tracker -->
            <div class="flex items-center gap-2" x-show="step < 9">
                <span class="text-xs font-mono text-brand-neon" x-text="'0' + step"></span>
                <div class="w-24 h-1 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-neon transition-all duration-500" :style="'width: ' + ((step/8)*100) + '%'"></div>
                </div>
                <span class="text-xs font-mono text-gray-500">08</span>
            </div>
        </header>

        <main class="flex flex-col flex-1 p-6 sm:p-10 overflow-y-auto relative">
            
            <!-- STEP 1: Name -->
            <div x-show="step === 1" class="step-enter flex flex-col justify-center h-full space-y-6">
                <div class="space-y-2">
                    <span class="material-symbols-rounded text-4xl text-brand-neon animate-float">waving_hand</span>
                    <h2 class="text-3xl sm:text-4xl font-bold">Iniciando protocolo.<br>¿Cuál es tu nombre?</h2>
                    <p class="text-gray-400 font-light">Para personalizar tu experiencia de cotización.</p>
                </div>
                <div class="relative">
                    <input type="text" x-model="data.name" @keydown.enter="next()" placeholder="Ej. Ana Pérez" class="w-full text-2xl p-4 rounded-xl input-cyber font-light" autofocus>
                </div>
            </div>

            <!-- STEP 2: Date -->
            <div x-show="step === 2" class="step-enter flex flex-col justify-center h-full space-y-6" style="display: none;">
                <div class="space-y-2">
                    <h2 class="text-3xl font-bold">Hola <span class="text-brand-neon" x-text="data.name"></span>,<br>¿Cuándo planeas mudarte?</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button @click="data.date = 'asap'; next()" class="p-6 rounded-xl input-cyber flex flex-col items-center justify-center gap-2 hover:-translate-y-1 transition-transform group">
                        <span class="material-symbols-rounded text-3xl group-hover:text-brand-neon transition-colors">rocket_launch</span>
                        <span class="font-semibold">Lo antes posible</span>
                    </button>
                    <div class="relative group">
                        <input type="date" x-model="data.date" class="w-full h-full min-h-[100px] p-6 text-center rounded-xl input-cyber font-semibold text-lg cursor-pointer">
                        <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center gap-2" x-show="!data.date">
                            <span class="material-symbols-rounded text-3xl text-gray-400 group-hover:text-brand-neon transition-colors">calendar_month</span>
                            <span class="font-semibold text-gray-400">Elegir fecha</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Origin -->
            <div x-show="step === 3" class="step-enter flex flex-col justify-center h-full space-y-6" style="display: none;">
                <div class="space-y-2">
                    <div class="inline-block px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-mono text-gray-300 mb-2">PUNTO A</div>
                    <h2 class="text-3xl font-bold">¿Desde dónde te mudas?</h2>
                </div>
                <div class="relative">
                    <span class="material-symbols-rounded absolute left-4 top-4 text-gray-500">my_location</span>
                    <input type="text" x-model="data.origin" placeholder="Ingresa dirección de origen..." class="w-full text-lg p-4 pl-12 rounded-xl input-cyber">
                </div>
            </div>

            <!-- STEP 4: Destination -->
            <div x-show="step === 4" class="step-enter flex flex-col justify-center h-full space-y-6" style="display: none;">
                <div class="space-y-2">
                    <div class="inline-block px-3 py-1 rounded-full bg-brand-neon/10 border border-brand-neon/30 text-xs font-mono text-brand-neon mb-2">PUNTO B</div>
                    <h2 class="text-3xl font-bold">¿Hacia dónde vas?</h2>
                </div>
                <div class="relative">
                    <span class="material-symbols-rounded absolute left-4 top-4 text-brand-neon">location_on</span>
                    <input type="text" x-model="data.destination" placeholder="Ingresa dirección de destino..." class="w-full text-lg p-4 pl-12 rounded-xl input-cyber">
                </div>
            </div>

            <!-- STEP 5: Logistics (Elevator) -->
            <div x-show="step === 5" class="step-enter flex flex-col justify-center h-full space-y-6" style="display: none;">
                <div class="space-y-2">
                    <h2 class="text-3xl font-bold">Análisis estructural</h2>
                    <p class="text-gray-400 font-light">¿Cómo es el acceso en tu domicilio de <span class="text-brand-neon">Origen</span>?</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="elevator" value="yes" x-model="data.elevatorStart" class="peer sr-only">
                        <div class="p-6 rounded-xl input-cyber border-2 peer-checked:border-brand-neon peer-checked:bg-brand-neon/10 transition-all flex items-center gap-4">
                            <span class="material-symbols-rounded text-3xl">elevator</span>
                            <div>
                                <h3 class="font-bold">Hay ascensor</h3>
                                <p class="text-xs text-gray-400">Y mis cosas caben</p>
                            </div>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="elevator" value="no" x-model="data.elevatorStart" class="peer sr-only">
                        <div class="p-6 rounded-xl input-cyber border-2 peer-checked:border-brand-neon peer-checked:bg-brand-neon/10 transition-all flex items-center gap-4">
                            <span class="material-symbols-rounded text-3xl">stairs</span>
                            <div>
                                <h3 class="font-bold">Solo escaleras</h3>
                                <p class="text-xs text-gray-400">O cosas muy grandes</p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- STEP 6: Inventory -->
            <div x-show="step === 6" class="step-enter flex flex-col space-y-4" style="display: none;">
                <div class="space-y-1">
                    <h2 class="text-2xl font-bold">Cálculo de volumen</h2>
                    <p class="text-sm text-gray-400">Selecciona los principales ítems a trasladar o descríbelos a la IA.</p>
                </div>
                
                <div class="bg-white/5 border border-brand-neon/30 p-3 rounded-xl">
                    <label class="text-xs text-brand-neon mb-2 block font-mono">✨ IA: INVENTARIO MÁGICO</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="aiInventoryText" @keydown.enter="analyzeInventory()" placeholder="Ej: Llevo 1 cama king, 2 cajas y el refri..." class="flex-1 p-3 rounded-lg input-cyber text-sm font-light">
                        <button @click="analyzeInventory()" :disabled="isAnalyzing" class="px-4 py-2 bg-brand/20 text-brand-neon border border-brand/50 rounded-lg hover:bg-brand hover:text-white transition-colors flex items-center gap-2 disabled:opacity-50">
                            <span class="material-symbols-rounded text-sm" :class="{'animate-spin': isAnalyzing}">smart_toy</span>
                            <span x-show="!isAnalyzing" class="hidden sm:inline">Analizar</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 overflow-y-auto pb-4 pr-2 h-[280px] auto-rows-max">
                    <template x-for="(item, index) in inventory" :key="index">
                        <div class="bg-white/5 border border-white/10 rounded-xl p-3 flex flex-col items-center justify-between text-center h-[140px] relative overflow-hidden group">
                            <!-- Glow effect on selection -->
                            <div class="absolute inset-0 bg-brand-neon/20 opacity-0 transition-opacity" :class="{'opacity-100': item.count > 0}"></div>
                            
                            <span class="text-3xl relative z-10" x-text="item.icon"></span>
                            <span class="text-sm font-semibold relative z-10" x-text="item.name"></span>
                            
                            <div class="flex items-center justify-between w-full mt-2 relative z-10 bg-black/30 rounded-lg p-1">
                                <button @click="if(item.count > 0) item.count--" class="w-9 h-9 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                                    <span class="material-symbols-rounded text-base">remove</span>
                                </button>
                                <span class="font-mono font-bold text-base" x-text="item.count"></span>
                                <button @click="item.count++" class="w-9 h-9 rounded-lg bg-brand/80 hover:bg-brand flex items-center justify-center transition-colors">
                                    <span class="material-symbols-rounded text-base">add</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div class="pt-2 border-t border-white/10 flex justify-between items-center text-sm">
                    <span class="text-gray-400">Total ítems:</span>
                    <span class="font-mono font-bold text-brand-neon text-lg" x-text="getTotalItems()"></span>
                </div>
            </div>

            <!-- STEP 7: Contact -->
            <div x-show="step === 7" class="step-enter flex flex-col justify-center h-full space-y-6" style="display: none;">
                <div class="space-y-2">
                    <h2 class="text-3xl font-bold">Destino de la cotización</h2>
                    <p class="text-gray-400 font-light">¿A dónde enviamos los resultados de la IA?</p>
                </div>
                <div class="space-y-4">
                    <div class="relative">
                        <span class="material-symbols-rounded absolute left-4 top-4 text-gray-500">alternate_email</span>
                        <input type="email" x-model="data.email" placeholder="Correo electrónico" class="w-full text-lg p-4 pl-12 rounded-xl input-cyber">
                    </div>
                    <div class="relative">
                        <span class="material-symbols-rounded absolute left-4 top-4 text-gray-500">call</span>
                        <input type="tel" x-model="data.phone" placeholder="Número de teléfono (Opcional)" class="w-full text-lg p-4 pl-12 rounded-xl input-cyber">
                    </div>
                </div>
            </div>

            <!-- STEP 8: Processing Loading Screen -->
            <div x-show="step === 8" class="step-enter flex flex-col items-center justify-center h-full space-y-8 text-center" style="display: none;">
                <div class="relative w-32 h-32 flex items-center justify-center">
                    <!-- Spinning glowing rings -->
                    <div class="absolute inset-0 border-4 border-brand-neon/20 border-t-brand-neon rounded-full animate-spin"></div>
                    <div class="absolute inset-2 border-4 border-blue-500/20 border-b-blue-500 rounded-full animate-[spin_2s_linear_infinite_reverse]"></div>
                    <span class="material-symbols-rounded text-4xl text-white animate-pulse">memory</span>
                </div>
                <div>
                    <h2 class="text-2xl font-bold mb-2">Procesando variables...</h2>
                    <p class="text-brand-neon font-mono text-sm animate-pulse">Calculando rutas óptimas y volumen cuántico</p>
                </div>
            </div>

            <!-- STEP 9: Success Result -->
            <div x-show="step === 9" class="step-enter flex flex-col items-center justify-center space-y-6 text-center" style="display: none;">
                <div class="w-24 h-24 rounded-full bg-green-500/20 border border-green-500 flex items-center justify-center shadow-[0_0_30px_rgba(34,197,94,0.3)]">
                    <span class="material-symbols-rounded text-5xl text-green-400">check_circle</span>
                </div>
                <div>
                    <h2 class="text-3xl font-bold mb-2">¡Cotización Lista!</h2>
                    <p class="text-gray-300 font-light">Hemos enviado el enlace mágico a <br><span class="font-bold text-white" x-text="data.email"></span></p>
                </div>

                <div class="p-4 bg-brand-darker/80 rounded-xl border border-brand-neon/40 w-full max-w-md mt-2 text-sm text-left font-sans text-gray-300 relative shadow-[0_0_15px_rgba(237,52,38,0.15)]">
                    <span class="material-symbols-rounded absolute -top-3 -left-3 text-brand-neon bg-[#020617] rounded-full p-1 border border-brand-neon/40">auto_awesome</span>
                    <p x-html="aiSummary || 'Procesando coordenadas finales...'"></p>
                </div>

                <div class="p-4 bg-white/5 rounded-xl border border-white/10 w-full max-w-sm mt-4 text-sm text-left font-mono text-gray-400">
                    <p>ID Transacción: <span class="text-white">MDG-88X-FT</span></p>
                    <p>Volumen Estimado: <span class="text-brand-neon" x-text="(getTotalItems() * 1.5) + ' m³'"></span></p>
                    <p>Ruta: <span class="text-white">Optimizada</span></p>
                </div>
                <button onclick="location.reload()" class="mt-6 px-6 py-2 border border-white/20 rounded-full hover:bg-white/10 transition-colors text-sm">
                    Iniciar nueva consulta
                </button>
            </div>

        </main>

        <!-- Footer / Navigation Controls -->
        <footer class="p-6 border-t border-white/10 flex justify-between items-center" x-show="step < 8">
            <button @click="prev()" :class="{'opacity-0 pointer-events-none': step === 1}" class="px-5 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 transition-all font-semibold flex items-center gap-2">
                <span class="material-symbols-rounded text-sm">arrow_back</span> Atrás
            </button>
            
            <button @click="next()" class="px-8 py-3 rounded-xl bg-brand hover:bg-brand-neon transition-all shadow-[0_0_20px_rgba(237,52,38,0.4)] font-semibold flex items-center gap-2">
                <span x-text="step === 7 ? 'Analizar Datos' : 'Siguiente'"></span>
                <span class="material-symbols-rounded text-sm" x-show="step < 7">arrow_forward</span>
                <span class="material-symbols-rounded text-sm" x-show="step === 7">auto_awesome</span>
            </button>
        </footer>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('movingBot', () => ({
                step: 1,
                data: {
                    name: '',
                    date: '',
                    origin: '',
                    destination: '',
                    elevatorStart: null,
                    email: '',
                    phone: ''
                },
                // AI Features State
                aiInventoryText: '',
                isAnalyzing: false,
                aiSummary: '',

                // Simplified mock inventory for prototyping
                inventory: [
                    { id: 'bed_king', name: 'Cama King', icon: '🛏️', count: 0 },
                    { id: 'bed_single', name: 'Cama 1 Plaza', icon: '🛌', count: 0 },
                    { id: 'sofa_big', name: 'Sofá 3 Cuerpos', icon: '🛋️', count: 0 },
                    { id: 'tv', name: 'Televisor', icon: '📺', count: 0 },
                    { id: 'fridge', name: 'Refrigerador', icon: '🧊', count: 0 },
                    { id: 'washing_machine', name: 'Lavadora', icon: '🧺', count: 0 },
                    { id: 'table', name: 'Mesa Comedor', icon: '🪑', count: 0 },
                    { id: 'boxes', name: 'Cajas (x5)', icon: '📦', count: 0 }
                ],
                
                getTotalItems() {
                    return this.inventory.reduce((acc, item) => acc + item.count, 0);
                },

                async analyzeInventory() {
                    if (!this.aiInventoryText.trim() || this.isAnalyzing) return;
                    this.isAnalyzing = true;
                    
                    try {
                        const prompt = `Analiza el siguiente texto y extrae las cantidades de los muebles mencionados. Mapealos estrictamente a los siguientes IDs: bed_king (Cama King), bed_single (Cama 1 Plaza), sofa_big (Sofá), tv (Televisor), fridge (Refrigerador), washing_machine (Lavadora), table (Mesa), boxes (Cajas). Texto del usuario: "${this.aiInventoryText}"`;

                        const payload = {
                            contents: [{ parts: [{ text: prompt }] }],
                            generationConfig: {
                                responseMimeType: "application/json",
                                responseSchema: {
                                    type: "ARRAY",
                                    items: {
                                        type: "OBJECT",
                                        properties: {
                                            id: { type: "STRING" },
                                            count: { type: "INTEGER" }
                                        },
                                        propertyOrdering: ["id", "count"]
                                    }
                                }
                            }
                        };
                        
                        // PHP dynamically outputs API key and model here
                        const apiKey = "<?php echo GEMINI_API_KEY; ?>";
                        const apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/<?php echo GEMINI_MODEL; ?>:generateContent?key=" + apiKey;
                        
                        const response = await fetch(apiUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                        
                        const result = await response.json();
                        if (result.candidates && result.candidates[0].content) {
                            const extractedItems = JSON.parse(result.candidates[0].content.parts[0].text);
                            
                            // Update inventory state reactively
                            extractedItems.forEach(extractedItem => {
                                const invItem = this.inventory.find(i => i.id === extractedItem.id);
                                if (invItem && extractedItem.count > 0) {
                                    invItem.count += extractedItem.count;
                                }
                            });
                            this.aiInventoryText = ''; // Clear input on success
                        }
                    } catch (error) {
                        console.error("Error AI Inventory:", error);
                    } finally {
                        this.isAnalyzing = false;
                    }
                },

                async generateSummary() {
                    this.aiSummary = 'Generando el reporte cuántico de tu mudanza...';
                    try {
                        const itemsList = this.inventory.filter(i => i.count > 0).map(i => `${i.count}x ${i.name}`).join(', ');
                        const origin = this.data.origin || 'tu origen';
                        const dest = this.data.destination || 'tu nuevo destino';
                        
                        const prompt = `Actúa como el IA central de Mudanzas Monroy. Escribe un párrafo hiper-personalizado, amistoso y sutilmente futurista (máximo 3 oraciones cortas). Resume la mudanza de ${this.data.name} desde "${origin}" hacia "${dest}". Menciona que transportaremos estos ítems: "${itemsList || 'unas cuantas cajas'}". Usa formato HTML <b> para destacar el nombre de las ciudades/direcciones y no uses markdown.`;

                        const payload = {
                            contents: [{ parts: [{ text: prompt }] }],
                            systemInstruction: { parts: [{ text: "Eres un asistente de IA experto en mudanzas, amigable y eficiente." }]}
                        };
                        
                        // PHP dynamically outputs API key and model here
                        const apiKey = "<?php echo GEMINI_API_KEY; ?>";
                        const apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/<?php echo GEMINI_MODEL; ?>:generateContent?key=" + apiKey;
                        
                        const response = await fetch(apiUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                        
                        const result = await response.json();
                        if (result.candidates && result.candidates[0].content) {
                            this.aiSummary = result.candidates[0].content.parts[0].text;
                        }
                    } catch (error) {
                        console.error("Error AI Summary:", error);
                        this.aiSummary = `¡Todo listo, ${this.data.name}! Hemos procesado los datos de tu traslado exitosamente.`;
                    }
                },

                next() {
                    // Basic validation simulation
                    if(this.step === 1 && !this.data.name.trim()) return;
                    if(this.step === 7 && !this.data.email.trim()) return;

                    if (this.step === 7) {
                        this.processSimulation();
                    } else if (this.step < 9) {
                        this.step++;
                    }
                },

                prev() {
                    if (this.step > 1) this.step--;
                },

                processSimulation() {
                    this.step = 8; // Loading screen
                    
                    // Trigger AI summary generation in the background
                    this.generateSummary();

                    // Simulate API call and calculation time
                    setTimeout(() => {
                        this.step = 9; // Success screen
                    }, 4000); // Increased slightly to give AI more time to return
                }
            }))
        })
    </script>
</body>
</html>
