<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizador de Mudanzas · Mudanzas Hermanos Monroy</title>
    <meta name="description" content="Cotiza tu mudanza en minutos con nuestra tecnología de inteligencia artificial. Mudanzas Hermanos Monroy - Servicio de mudanzas profesional.">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
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
                            DEFAULT: '#ED3426',
                            neon: '#ff5544',
                            dark: '#0f172a',
                            darker: '#020617',
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'spin-slow': 'spin 3s linear infinite',
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

        .glass-panel {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.6);
        }

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
            background: rgba(255, 255, 255, 0.06);
        }
        .input-cyber::placeholder { color: rgba(255,255,255,0.3); }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: rgba(237, 52, 38, 0.5); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(237, 52, 38, 0.8); }

        .step-enter { animation: slideIn 0.45s forwards cubic-bezier(0.16, 1, 0.3, 1); opacity: 0; }
        @keyframes slideIn {
            from { transform: translateX(18px) scale(0.98); opacity: 0; }
            to   { transform: translateX(0) scale(1); opacity: 1; }
        }

        .item-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.25s ease;
        }
        .item-card:hover { border-color: rgba(255,85,68,0.4); transform: translateY(-2px); }
        .item-card.selected {
            border-color: rgba(255,85,68,0.7);
            background: rgba(237,52,38,0.12);
        }

        .stat-box {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .price-box {
            background: linear-gradient(135deg, rgba(237,52,38,0.2) 0%, rgba(56,189,248,0.1) 100%);
            border: 1px solid rgba(237,52,38,0.4);
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4" x-data="cotizador()">

    <!-- Ambient Elements -->
    <div class="fixed top-16 left-16 w-72 h-72 bg-brand-neon/15 rounded-full mix-blend-screen filter blur-[120px] animate-pulse-slow pointer-events-none"></div>
    <div class="fixed bottom-16 right-16 w-96 h-96 bg-blue-500/8 rounded-full mix-blend-screen filter blur-[120px] animate-pulse-slow pointer-events-none" style="animation-delay: 1.5s;"></div>
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-purple-900/5 rounded-full filter blur-[150px] pointer-events-none"></div>

    <!-- Main Container -->
    <div class="w-full max-w-2xl glass-panel rounded-3xl relative z-10 overflow-hidden flex flex-col" style="max-height: 92vh;">

        <!-- Header -->
        <header class="p-5 border-b border-white/10 flex justify-between items-center flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-brand to-orange-400 flex items-center justify-center shadow-[0_0_20px_rgba(237,52,38,0.5)]">
                    <span class="material-symbols-rounded text-white text-xl">local_shipping</span>
                </div>
                <div>
                    <p class="text-xs font-mono text-gray-400 tracking-widest uppercase">Sistema Mudanzas Hermanos Monroy</p>
                    <p class="text-base font-semibold text-white" x-text="step < 10 ? 'Cotizador Inteligente' : 'Análisis Completado'"></p>
                </div>
            </div>
            <!-- Dashboard & Progress -->
            <div class="flex items-center gap-4">
                <a href="/admin" target="_blank" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-semibold text-gray-300 hover:text-white transition-all shadow-sm">
                    <span class="material-symbols-rounded text-sm">dashboard</span>
                    Dashboard
                </a>
                
                <div class="flex items-center gap-2" x-show="step < 9 && step > 0">
                    <span class="text-xs font-mono text-brand-neon" x-text="'0' + step"></span>
                    <div class="w-20 h-1.5 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-brand to-orange-400 transition-all duration-700 rounded-full" :style="'width: ' + ((step/8)*100) + '%'"></div>
                    </div>
                    <span class="text-xs font-mono text-gray-600">08</span>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 p-6 sm:p-8" :class="activeInput ? 'overflow-visible' : 'overflow-y-auto'">

            <!-- STEP 1: Name -->
            <div x-show="step === 1" class="step-enter flex flex-col justify-center min-h-full space-y-6">
                <div class="space-y-2">
                    <span class="material-symbols-rounded text-5xl text-brand-neon animate-float block">waving_hand</span>
                    <h1 class="text-3xl sm:text-4xl font-bold leading-tight">Iniciando protocolo.<br><span class="text-gray-400 font-light">¿Cuál es tu nombre?</span></h1>
                    <p class="text-gray-400 font-light text-sm">Para personalizar tu experiencia de cotización.</p>
                </div>
                <input type="text" id="input-name" x-model="data.name" @keydown.enter="next()" placeholder="Ej. Ana Pérez" class="w-full text-2xl p-4 rounded-xl input-cyber font-light" autofocus>
            </div>

            <!-- STEP 2: Date -->
            <div x-show="step === 2" class="step-enter flex flex-col justify-center min-h-full space-y-6" style="display:none">
                <div class="space-y-2">
                    <h2 class="text-3xl font-bold">Hola <span class="text-brand-neon" x-text="data.name"></span>,<br><span class="font-light text-gray-300">¿cuándo planeas mudarte?</span></h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button id="btn-asap" @click="data.date = 'asap'; next()" class="p-6 rounded-xl input-cyber flex flex-col items-center justify-center gap-2 hover:-translate-y-1 hover:border-brand-neon/50 transition-all group">
                        <span class="material-symbols-rounded text-3xl group-hover:text-brand-neon transition-colors">rocket_launch</span>
                        <span class="font-semibold">Lo antes posible</span>
                    </button>
                    <div class="relative">
                        <input type="date" id="input-date" x-model="data.date" @change="data.date && next()" class="w-full h-full min-h-[110px] p-6 text-center rounded-xl input-cyber font-semibold text-base cursor-pointer">
                        <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center gap-2" x-show="!data.date">
                            <span class="material-symbols-rounded text-3xl text-gray-500">calendar_month</span>
                            <span class="font-semibold text-gray-500 text-sm">Elegir fecha</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Origin -->
            <div x-show="step === 3" class="step-enter flex flex-col justify-center min-h-full space-y-6" style="display:none">
                <div class="space-y-2">
                    <span class="inline-block px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-mono text-gray-300 mb-1">PUNTO A — ORIGEN</span>
                    <h2 class="text-3xl font-bold">¿Desde dónde<br><span class="font-light text-gray-300">te mudas?</span></h2>
                </div>
                <div class="relative">
                    <span class="material-symbols-rounded absolute left-4 top-4 text-gray-500">my_location</span>
                    <input type="text" id="input-origin" x-model="data.origin" 
                           @input.debounce.300ms="fetchSuggestions('origin')"
                           @focus="activeInput = 'origin'; if (data.origin.trim().length >= 3) fetchSuggestions('origin')"
                           @keydown.enter="next()" 
                           placeholder="Ingresa dirección de origen completa..." 
                           class="w-full text-lg p-4 pl-12 pr-12 rounded-xl input-cyber"
                           autocomplete="off">
                    
                    <!-- Loading spinner -->
                    <div x-show="loadingSuggestions && activeInput === 'origin'" 
                         class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center">
                        <div class="w-5 h-5 border-2 border-brand-neon border-t-transparent rounded-full animate-spin"></div>
                    </div>

                    <!-- Suggestions Dropdown -->
                    <div x-show="activeInput === 'origin' && suggestions.length > 0" 
                         @click.away="activeInput = null"
                         class="absolute z-50 left-0 right-0 mt-2 bg-slate-950/95 backdrop-blur-md border border-white/10 rounded-xl overflow-hidden shadow-2xl max-h-60 overflow-y-auto">
                        <template x-for="item in suggestions">
                            <button @click="data.origin = item; suggestions = []; activeInput = null;" 
                                    type="button"
                                    class="w-full text-left px-4 py-3 hover:bg-white/10 hover:text-brand-neon text-sm text-gray-200 transition-colors flex items-center gap-3 border-b border-white/5 last:border-0">
                                <span class="material-symbols-rounded text-gray-400 text-base">location_on</span>
                                <span x-text="item"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- STEP 4: Destination -->
            <div x-show="step === 4" class="step-enter flex flex-col justify-center min-h-full space-y-6" style="display:none">
                <div class="space-y-2">
                    <span class="inline-block px-3 py-1 rounded-full bg-brand-neon/10 border border-brand-neon/30 text-xs font-mono text-brand-neon mb-1">PUNTO B — DESTINO</span>
                    <h2 class="text-3xl font-bold">¿Hacia dónde<br><span class="font-light text-gray-300">te vas?</span></h2>
                </div>
                <div class="relative">
                    <span class="material-symbols-rounded absolute left-4 top-4 text-brand-neon">location_on</span>
                    <input type="text" id="input-dest" x-model="data.destination" 
                           @input.debounce.300ms="fetchSuggestions('destination')"
                           @focus="activeInput = 'destination'; if (data.destination.trim().length >= 3) fetchSuggestions('destination')"
                           @keydown.enter="next()" 
                           placeholder="Ingresa dirección de destino completa..." 
                           class="w-full text-lg p-4 pl-12 pr-12 rounded-xl input-cyber"
                           autocomplete="off">

                    <!-- Loading spinner -->
                    <div x-show="loadingSuggestions && activeInput === 'destination'" 
                         class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center">
                        <div class="w-5 h-5 border-2 border-brand-neon border-t-transparent rounded-full animate-spin"></div>
                    </div>

                    <!-- Suggestions Dropdown -->
                    <div x-show="activeInput === 'destination' && suggestions.length > 0" 
                         @click.away="activeInput = null"
                         class="absolute z-50 left-0 right-0 mt-2 bg-slate-950/95 backdrop-blur-md border border-white/10 rounded-xl overflow-hidden shadow-2xl max-h-60 overflow-y-auto">
                        <template x-for="item in suggestions">
                            <button @click="data.destination = item; suggestions = []; activeInput = null;" 
                                    type="button"
                                    class="w-full text-left px-4 py-3 hover:bg-white/10 hover:text-brand-neon text-sm text-gray-200 transition-colors flex items-center gap-3 border-b border-white/5 last:border-0">
                                <span class="material-symbols-rounded text-gray-400 text-base">location_on</span>
                                <span x-text="item"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- STEP 5: Origen Access / Logistics -->
            <div x-show="step === 5" class="step-enter flex flex-col justify-center min-h-full space-y-6" style="display:none">
                <div class="space-y-2">
                    <span class="inline-block px-3 py-1 rounded-full bg-brand-neon/10 border border-brand-neon/30 text-xs font-mono text-brand-neon mb-1">ORIGEN - PROTOCOLO LOGÍSTICO</span>
                    <h2 class="text-3xl font-bold">Acceso en Origen</h2>
                    <p class="text-gray-400 font-light text-sm">Detalles estructurales de tu domicilio actual.</p>
                </div>
                
                <div class="space-y-4">
                    <!-- Elevador -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-300 mb-2">¿Cuenta con ascensor?</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="elevatorStart" value="yes" x-model="data.elevatorStart" class="peer sr-only">
                                <div class="p-4 rounded-xl input-cyber border-2 peer-checked:border-brand-neon peer-checked:bg-brand-neon/10 transition-all flex items-center justify-center gap-3">
                                    <span class="material-symbols-rounded text-2xl">elevator</span>
                                    <span class="font-semibold text-sm">Sí, disponible</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="elevatorStart" value="no" x-model="data.elevatorStart" class="peer sr-only">
                                <div class="p-4 rounded-xl input-cyber border-2 peer-checked:border-brand-neon peer-checked:bg-brand-neon/10 transition-all flex items-center justify-center gap-3">
                                    <span class="material-symbols-rounded text-2xl">stairs</span>
                                    <span class="font-semibold text-sm">No / Escaleras</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Pisos -->
                    <div>
                        <label for="select-pisos-origen" class="block text-sm font-semibold text-gray-300 mb-2">Número de piso / plantas</label>
                        <select id="select-pisos-origen" x-model.number="data.pisos_origen" class="w-full p-4 rounded-xl input-cyber text-base font-semibold">
                            <option value="1">Planta Baja / 1er Piso</option>
                            <option value="2">2do Piso</option>
                            <option value="3">3er Piso</option>
                            <option value="4">4to Piso</option>
                            <option value="5">5to Piso o más</option>
                        </select>
                    </div>

                    <!-- Distancia Caminata -->
                    <div>
                        <label for="select-caminata-origen" class="block text-sm font-semibold text-gray-300 mb-2">Distancia de caminata al camión</label>
                        <select id="select-caminata-origen" x-model.number="data.distancia_caminata_origen_m" class="w-full p-4 rounded-xl input-cyber text-base font-semibold">
                            <option value="10">Corta (Menos de 10 metros)</option>
                            <option value="25">Mediana (Entre 10 y 30 metros)</option>
                            <option value="45">Larga (Más de 30 metros)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- STEP 6: Destino Access / Logistics -->
            <div x-show="step === 6" class="step-enter flex flex-col justify-center min-h-full space-y-6" style="display:none">
                <div class="space-y-2">
                    <span class="inline-block px-3 py-1 rounded-full bg-brand-neon/10 border border-brand-neon/30 text-xs font-mono text-brand-neon mb-1">DESTINO - PROTOCOLO LOGÍSTICO</span>
                    <h2 class="text-3xl font-bold">Acceso en Destino</h2>
                    <p class="text-gray-400 font-light text-sm">Detalles estructurales de tu nuevo domicilio.</p>
                </div>
                
                <div class="space-y-4">
                    <!-- Elevador -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-300 mb-2">¿Cuenta con ascensor?</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="ascensor_destino" value="yes" x-model="data.ascensor_destino" class="peer sr-only">
                                <div class="p-4 rounded-xl input-cyber border-2 peer-checked:border-brand-neon peer-checked:bg-brand-neon/10 transition-all flex items-center justify-center gap-3">
                                    <span class="material-symbols-rounded text-2xl">elevator</span>
                                    <span class="font-semibold text-sm">Sí, disponible</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="ascensor_destino" value="no" x-model="data.ascensor_destino" class="peer sr-only">
                                <div class="p-4 rounded-xl input-cyber border-2 peer-checked:border-brand-neon peer-checked:bg-brand-neon/10 transition-all flex items-center justify-center gap-3">
                                    <span class="material-symbols-rounded text-2xl">stairs</span>
                                    <span class="font-semibold text-sm">No / Escaleras</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Pisos -->
                    <div>
                        <label for="select-pisos-destino" class="block text-sm font-semibold text-gray-300 mb-2">Número de piso / plantas</label>
                        <select id="select-pisos-destino" x-model.number="data.pisos_destino" class="w-full p-4 rounded-xl input-cyber text-base font-semibold">
                            <option value="1">Planta Baja / 1er Piso</option>
                            <option value="2">2do Piso</option>
                            <option value="3">3er Piso</option>
                            <option value="4">4to Piso</option>
                            <option value="5">5to Piso o más</option>
                        </select>
                    </div>

                    <!-- Distancia Caminata -->
                    <div>
                        <label for="select-caminata-destino" class="block text-sm font-semibold text-gray-300 mb-2">Distancia de caminata al camión</label>
                        <select id="select-caminata-destino" x-model.number="data.distancia_caminata_destino_m" class="w-full p-4 rounded-xl input-cyber text-base font-semibold">
                            <option value="10">Corta (Menos de 10 metros)</option>
                            <option value="25">Mediana (Entre 10 y 30 metros)</option>
                            <option value="45">Larga (Más de 30 metros)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- STEP 7: Inventory -->
            <div x-show="step === 7" class="step-enter flex flex-col space-y-4" style="display:none">
                <div>
                    <h2 class="text-2xl font-bold">Selecciona tus artículos</h2>
                    <p class="text-sm text-gray-400">Indica la cantidad de cada ítem que deseas trasladar.</p>
                </div>

                <!-- Loading state -->
                <div x-show="loadingItems" class="flex items-center justify-center py-10 gap-3">
                    <div class="w-6 h-6 border-2 border-brand-neon border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-sm text-gray-400">Cargando catálogo...</span>
                </div>

                <!-- Group and Category Selectors -->
                <div x-show="!loadingItems" class="space-y-3 pb-1">
                    <!-- Groups Pill Bar -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1.5 scrollbar-none" style="-webkit-overflow-scrolling: touch;">
                        <template x-for="group in getGroups()" :key="group">
                            <button @click="selectGroup(group)" 
                                    type="button"
                                    :class="selectedGroup === group ? 'bg-brand text-white border-brand shadow-[0_0_12px_rgba(237,52,38,0.3)]' : 'bg-white/5 text-gray-300 border-white/10 hover:bg-white/10'"
                                    class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-all whitespace-nowrap flex-shrink-0">
                                <span x-text="group"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Categories Pill Bar -->
                    <div x-show="getCategories().length > 2" class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none" style="-webkit-overflow-scrolling: touch;">
                        <span class="text-[10px] uppercase tracking-wider text-gray-500 font-bold pr-1 flex-shrink-0">Filtro:</span>
                        <template x-for="category in getCategories()" :key="category">
                            <button @click="selectedCategory = category" 
                                    type="button"
                                    :class="selectedCategory === category ? 'bg-brand-neon/25 text-brand-neon border-brand-neon/40 shadow-[0_0_8px_rgba(237,52,38,0.15)]' : 'bg-white/5 text-gray-400 border-white/5 hover:bg-white/10'"
                                    class="px-2.5 py-1 rounded-full text-[10px] font-semibold border transition-all whitespace-nowrap flex-shrink-0">
                                <span x-text="category"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Items Grid -->
                <div x-show="!loadingItems" class="grid grid-cols-2 sm:grid-cols-3 gap-3 overflow-y-auto pb-2 pr-1" style="max-height: 380px;">
                    <template x-for="item in getFilteredItems()" :key="item.id">
                        <div class="item-card rounded-2xl p-3 flex flex-col items-center text-center gap-2 relative"
                             :class="{ 'selected': item.count > 0 }">
                            <!-- Glow when selected -->
                            <div class="absolute inset-0 bg-gradient-to-b from-brand-neon/10 to-transparent rounded-2xl opacity-0 transition-opacity" :class="{ 'opacity-100': item.count > 0 }"></div>

                            <span class="text-3xl relative z-10" x-text="item.icon || '📦'"></span>
                            <span class="text-sm font-semibold relative z-10 leading-tight" x-text="item.nombre"></span>

                            <!-- Optional details for TV / Fridge -->
                            <template x-if="item.permite_detalles_opcionales && item.count > 0">
                                <div class="w-full space-y-1 relative z-10">
                                    <input type="number" x-model.number="item.volumen_m3" min="0" step="0.1"
                                        placeholder="Volumen m³"
                                        class="w-full text-xs p-1.5 rounded-lg input-cyber text-center">
                                    <input type="number" x-model.number="item.peso_kg" min="0" step="1"
                                        placeholder="Peso kg"
                                        class="w-full text-xs p-1.5 rounded-lg input-cyber text-center">
                                </div>
                            </template>

                            <!-- Counter -->
                            <div class="flex items-center justify-between w-full relative z-10 bg-black/30 rounded-xl p-1 mt-auto">
                                <button @click="if(item.count > 0) item.count--"
                                    class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors text-sm font-bold">−</button>
                                <span class="font-mono font-bold text-sm" x-text="item.count"></span>
                                <button @click="item.count++"
                                    class="w-8 h-8 rounded-lg bg-brand/80 hover:bg-brand flex items-center justify-center transition-colors text-sm font-bold">+</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="pt-2 border-t border-white/10 flex justify-between items-center text-sm">
                    <span class="text-gray-400">Total artículos seleccionados:</span>
                    <span class="font-mono font-bold text-brand-neon text-lg" x-text="getTotalItems()"></span>
                </div>
            </div>

            <!-- STEP 8: Contact Info -->
            <div x-show="step === 8" class="step-enter flex flex-col justify-center min-h-full space-y-6" style="display:none">
                <div class="space-y-2">
                    <h2 class="text-3xl font-bold">Datos de contacto</h2>
                    <p class="text-gray-400 font-light text-sm">¿A dónde enviamos los resultados de tu cotización?</p>
                </div>
                <div class="space-y-4">
                    <div class="relative">
                        <span class="material-symbols-rounded absolute left-4 top-4 text-gray-500">alternate_email</span>
                        <input type="email" id="input-email" x-model="data.email" placeholder="Correo electrónico *" class="w-full text-lg p-4 pl-12 rounded-xl input-cyber">
                    </div>
                    <div class="relative">
                        <span class="material-symbols-rounded absolute left-4 top-4 text-gray-500">call</span>
                        <input type="tel" id="input-phone" x-model="data.phone" placeholder="Número de teléfono (Opcional)" class="w-full text-lg p-4 pl-12 rounded-xl input-cyber">
                    </div>
                    
                    <!-- Error message if any -->
                    <div x-show="errorMessage" class="bg-red-900/30 border border-red-500/50 rounded-xl p-4 text-sm text-red-300" x-text="errorMessage"></div>
                </div>
            </div>

            <!-- STEP 9: Processing / Loading -->
            <div x-show="step === 9" class="step-enter flex flex-col items-center justify-center min-h-full space-y-8 text-center py-12" style="display:none">
                <div class="relative w-36 h-36 flex items-center justify-center">
                    <div class="absolute inset-0 border-4 border-brand-neon/20 border-t-brand-neon rounded-full animate-spin"></div>
                    <div class="absolute inset-3 border-4 border-blue-500/20 border-b-blue-400 rounded-full animate-[spin_1.8s_linear_infinite_reverse]"></div>
                    <div class="absolute inset-6 border-2 border-orange-400/20 border-r-orange-400 rounded-full animate-spin-slow"></div>
                    <span class="material-symbols-rounded text-4xl text-white animate-pulse">memory</span>
                </div>
                <div>
                    <h2 class="text-2xl font-bold mb-2">Calculando tu mudanza...</h2>
                    <p class="text-brand-neon font-mono text-sm animate-pulse">Estimando rutas · Calculando volumen · Generando cotización</p>
                </div>
            </div>

            <!-- STEP 10: Results -->
            <div x-show="step === 10" class="step-enter flex flex-col space-y-5 py-2" style="display:none">
                <!-- Success header -->
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-green-500/20 border border-green-500/50 flex items-center justify-center shadow-[0_0_25px_rgba(34,197,94,0.25)] flex-shrink-0">
                        <span class="material-symbols-rounded text-3xl text-green-400">check_circle</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">¡Cotización Lista!</h2>
                        <p class="text-sm text-gray-400">Folio: <span class="font-mono text-white" x-text="results.transaction_id"></span></p>
                    </div>
                </div>

                <!-- Price Box -->
                <div class="price-box rounded-2xl p-5 text-center">
                    <p class="text-sm text-gray-400 mb-1">Precio Estimado Sugerido</p>
                    <p class="text-4xl font-bold text-white" x-text="formatCurrency(results.precio_sugerido)"></p>
                    <p class="text-xs text-gray-500 mt-1">Precio orientativo · Incluye 50% de margen de ganancia</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="stat-box rounded-xl p-4 space-y-1">
                        <div class="flex items-center gap-2 text-gray-400 text-xs">
                            <span class="material-symbols-rounded text-base text-brand-neon">straighten</span>
                            Volumen Total
                        </div>
                        <p class="text-xl font-bold font-mono" x-text="(results.volumen_total_m3 || 0) + ' m³'"></p>
                    </div>
                    <div class="stat-box rounded-xl p-4 space-y-1">
                        <div class="flex items-center gap-2 text-gray-400 text-xs">
                            <span class="material-symbols-rounded text-base text-brand-neon">local_shipping</span>
                            Vehículo Sugerido
                        </div>
                        <p class="text-sm font-bold" x-text="results.vehiculo_sugerido || 'N/A'"></p>
                    </div>
                    <div class="stat-box rounded-xl p-4 space-y-1">
                        <div class="flex items-center gap-2 text-gray-400 text-xs">
                            <span class="material-symbols-rounded text-base text-brand-neon">route</span>
                            Distancia Estimada
                        </div>
                        <p class="text-xl font-bold font-mono" x-text="(results.distancia_km || 0) + ' km'"></p>
                    </div>
                    <div class="stat-box rounded-xl p-4 space-y-1">
                        <div class="flex items-center gap-2 text-gray-400 text-xs">
                            <span class="material-symbols-rounded text-base text-brand-neon">group</span>
                            Personal Sugerido
                        </div>
                        <p class="text-xl font-bold font-mono" x-text="(results.personas_sugeridas || 2) + ' personas'"></p>
                    </div>
                    <div class="stat-box rounded-xl p-4 space-y-1">
                        <div class="flex items-center gap-2 text-gray-400 text-xs">
                            <span class="material-symbols-rounded text-base text-brand-neon">schedule</span>
                            Tiempo de Traslado
                        </div>
                        <p class="text-xl font-bold font-mono" x-text="formatHours(results.tiempo_traslado_horas)"></p>
                    </div>
                    <div class="stat-box rounded-xl p-4 space-y-1">
                        <div class="flex items-center gap-2 text-gray-400 text-xs">
                            <span class="material-symbols-rounded text-base text-brand-neon">inventory</span>
                            Tiempo de Empaque
                        </div>
                        <p class="text-xl font-bold font-mono" x-text="formatMinutes(results.tiempo_empaque_total_min)"></p>
                    </div>

                <!-- Contact confirmation -->
                <div class="bg-white/5 rounded-xl p-4 border border-white/10 text-sm">
                    <p class="text-gray-400">Un agente de <span class="text-brand-neon font-semibold">Mudanzas Hermanos Monroy</span> se pondrá en contacto a través de <span class="font-semibold text-white" x-text="data.email"></span> o al <span class="font-semibold text-white" x-text="data.phone || 'número proporcionado'"></span>.</p>
                </div>
                <div class="flex space-x-4 mt-4 justify-center">
                    <button id="btn-pdf-client" @click="window.open(`/quotes/${results.quote_id || results.quoteId}/pdf/client`, '_blank')" class="px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-neon transition-colors">Descargar PDF (Cliente)</button>
                    <button id="btn-excel-admin" @click="window.open(`/quotes/${results.quote_id || results.quoteId}/excel/admin`, '_blank')" class="px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-neon transition-colors">Descargar Excel (Admin)</button>
                </div>

                <!-- Error message if any -->
                <div x-show="errorMessage" class="bg-red-900/30 border border-red-500/50 rounded-xl p-4 text-sm text-red-300" x-text="errorMessage"></div>

                <button id="btn-new-quote" @click="resetForm()" class="w-full px-6 py-3 border border-white/15 rounded-xl hover:bg-white/10 transition-colors text-sm font-semibold">
                    Iniciar nueva cotización →
                </button>
            </div>

        </main>

        <!-- Footer Navigation -->
        <footer class="p-5 border-t border-white/10 flex justify-between items-center flex-shrink-0" x-show="step < 9 && step > 0">
            <button id="btn-back"
                @click="prev()"
                :class="{'opacity-0 pointer-events-none': step === 1}"
                class="px-5 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 transition-all font-semibold flex items-center gap-2 text-sm">
                <span class="material-symbols-rounded text-sm">arrow_back</span> Atrás
            </button>

            <button id="btn-next"
                @click="next()"
                :disabled="!canProceed()"
                class="px-8 py-3 rounded-xl bg-brand hover:bg-brand-neon transition-all shadow-[0_0_20px_rgba(237,52,38,0.35)] font-semibold flex items-center gap-2 text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                <span x-text="step === 8 ? 'Calcular Cotización' : 'Siguiente'"></span>
                <span class="material-symbols-rounded text-sm" x-show="step < 8">arrow_forward</span>
                <span class="material-symbols-rounded text-sm" x-show="step === 8">auto_awesome</span>
            </button>
        </footer>

    </div><!-- end main container -->

    <script>
    function cotizador() {
        return {
            step: 1,
            data: {
                name: '',
                date: '',
                origin: '',
                destination: '',
                elevatorStart: null,
                email: '',
                phone: '',
                pisos_origen: 1,
                distancia_caminata_origen_m: 10,
                pisos_destino: 1,
                ascensor_destino: 'yes',
                distancia_caminata_destino_m: 10,
            },
            inventory: [],
            loadingItems: false,
            results: {},
            errorMessage: '',
            suggestions: [],
            activeInput: null,
            loadingSuggestions: false,
            selectedGroup: 'Todos',
            selectedCategory: 'Todas',

            async init() {
                // Items are loaded lazily when we reach step 6
            },

            async loadItems() {
                if (this.inventory.length > 0) return; // already loaded
                this.loadingItems = true;
                try {
                    const response = await fetch('/api/items');
                    const items = await response.json();
                    this.inventory = items.map(item => ({
                        ...item,
                        count: 0,
                        volumen_m3: null,
                        peso_kg: null,
                    }));
                } catch (e) {
                    console.error('Error loading items:', e);
                } finally {
                    this.loadingItems = false;
                }
            },

            async fetchSuggestions(field) {
                const query = this.data[field];
                if (!query || query.trim().length < 3) {
                    this.suggestions = [];
                    return;
                }
                this.loadingSuggestions = true;
                this.activeInput = field;
                try {
                    const response = await fetch(`/api/autocomplete?query=${encodeURIComponent(query)}`);
                    const results = await response.json();
                    if (this.activeInput === field) {
                        this.suggestions = results;
                    }
                } catch (e) {
                    console.error('Error fetching suggestions:', e);
                } finally {
                    this.loadingSuggestions = false;
                }
            },

            getGroups() {
                if (this.inventory.length === 0) return ['Todos'];
                return ['Todos', ...new Set(this.inventory.map(item => item.grupo_categoria).filter(Boolean))];
            },

            getCategories() {
                if (this.inventory.length === 0) return ['Todas'];
                let items = this.inventory;
                if (this.selectedGroup !== 'Todos') {
                    items = items.filter(item => item.grupo_categoria === this.selectedGroup);
                }
                return ['Todas', ...new Set(items.map(item => item.categoria).filter(Boolean))];
            },

            selectGroup(group) {
                this.selectedGroup = group;
                this.selectedCategory = 'Todas';
            },

            getFilteredItems() {
                return this.inventory.filter(item => {
                    const matchesGroup = this.selectedGroup === 'Todos' || item.grupo_categoria === this.selectedGroup;
                    const matchesCategory = this.selectedCategory === 'Todas' || item.categoria === this.selectedCategory;
                    return matchesGroup && matchesCategory;
                });
            },

            getTotalItems() {
                return this.inventory.reduce((acc, i) => acc + i.count, 0);
            },

            canProceed() {
                if (this.step === 1) return this.data.name.trim().length > 0;
                if (this.step === 3) return this.data.origin.trim().length > 0;
                if (this.step === 4) return this.data.destination.trim().length > 0;
                if (this.step === 8) return this.data.email.trim().length > 0 && this.data.email.includes('@');
                return true;
            },

            async next() {
                if (!this.canProceed()) return;

                if (this.step === 5 && !this.data.elevatorStart) {
                    this.data.elevatorStart = 'no'; // default
                }

                if (this.step === 7) {
                    // Ensure at least 1 item is selected
                    if (this.getTotalItems() === 0) {
                        alert('Por favor selecciona al menos un artículo para continuar.');
                        return;
                    }
                }

                if (this.step === 8) {
                    // Check default for ascensor_destino before submitting
                    if (!this.data.ascensor_destino) {
                        this.data.ascensor_destino = 'yes';
                    }
                    await this.submitQuote();
                    return;
                }

                this.step++;

                if (this.step === 7) {
                    await this.loadItems();
                }
            },

            prev() {
                if (this.step > 1) this.step--;
            },

            async submitQuote() {
                this.step = 9;
                this.errorMessage = '';

                const itemsPayload = this.inventory
                    .filter(i => i.count > 0)
                    .map(i => ({
                        item_id: i.id,
                        count: i.count,
                        volumen_m3: i.volumen_m3 || null,
                        peso_kg: i.peso_kg || null,
                    }));

                try {
                    const response = await fetch('/api/quotes', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({
                            name: this.data.name,
                            email: this.data.email,
                            phone: this.data.phone,
                            origin: this.data.origin,
                            destination: this.data.destination,
                            elevatorStart: this.data.elevatorStart,
                            pisos_origen: this.data.pisos_origen,
                            distancia_caminata_origen_m: this.data.distancia_caminata_origen_m,
                            pisos_destino: this.data.pisos_destino,
                            ascensor_destino: this.data.ascensor_destino,
                            distancia_caminata_destino_m: this.data.distancia_caminata_destino_m,
                            items: itemsPayload,
                        }),
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Include quote_id for PDF generation URLs
                        this.results = {
                            quote_id: result.quote_id,
                            transaction_id: result.transaction_id,
                            ...result.results,
                        };
                        // Small delay for UX drama
                        await new Promise(r => setTimeout(r, 1500));
                        this.step = 10;
                    } else {
                        this.errorMessage = result.message || 'Error al procesar la cotización.';
                        // Stay on Step 8 so they can see the error and fix it
                        this.step = 8;
                    }
                } catch (e) {
                    console.error('Error submitting quote:', e);
                    this.errorMessage = 'Error de conexión. Por favor intenta de nuevo.';
                    this.step = 8;
                }
            },

            resetForm() {
                this.step = 1;
                this.data = { 
                    name: '', 
                    date: '', 
                    origin: '', 
                    destination: '', 
                    elevatorStart: null, 
                    email: '', 
                    phone: '',
                    pisos_origen: 1,
                    distancia_caminata_origen_m: 10,
                    pisos_destino: 1,
                    ascensor_destino: 'yes',
                    distancia_caminata_destino_m: 10
                };
                this.inventory = [];
                this.results = {};
                this.errorMessage = '';
                this.suggestions = [];
                this.activeInput = null;
                this.loadingSuggestions = false;
                this.selectedGroup = 'Todos';
                this.selectedCategory = 'Todas';
            },

            formatCurrency(amount) {
                if (!amount) return '$0.00';
                return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(amount);
            },

            formatHours(hours) {
                if (!hours) return '—';
                const h = Math.floor(hours);
                const m = Math.round((hours - h) * 60);
                if (h === 0) return m + ' min';
                if (m === 0) return h + ' h';
                return h + 'h ' + m + 'min';
            },

            formatMinutes(minutes) {
                if (!minutes) return '—';
                const h = Math.floor(minutes / 60);
                const m = minutes % 60;
                if (h === 0) return m + ' min';
                if (m === 0) return h + ' h';
                return h + 'h ' + m + 'min';
            },
        }
    }
    </script>
</body>
</html>
