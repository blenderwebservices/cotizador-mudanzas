# Mudanzas Hermanos Monroy – Cotizador de Mudanzas (Embeddable Component)

## 📦 Qué es esta aplicación

Este repositorio contiene **un componente completo de cotización de mudanzas** pensado para ser insertado en cualquier sitio web o aplicación externa.  Provee una UI moderna y responsiva (Alpine.js + Tailwind CSS) que guía al usuario paso‑a‑paso para seleccionar sus artículos, calcular volúmenes, estimar distancias mediante la API Gemini y devolver un precio sugerido con desglose financiero.

> **Nota:** La aplicación está diseñada como un *micro‑servicio*: el frontend solo consume la API `/api/*` y no requiere conocimientos de Laravel para su integración.

## 🎯 Objetivo

- **Facilitar la integración** de un cotizador de mudanzas en cualquier proyecto web, sin necesidad de desarrollar la lógica de cálculo desde cero.
- Ofrecer una **experiencia futurista** con animaciones, glass‑morphism y una estética premium.
- Proveer un **panel administrativo** (Filament) para gestionar agentes, vehículos, ítems y solicitudes de cotización.

## 🏗️ Arquitectura

| Capa | Tecnologías | Responsabilidad |
|------|--------------|-----------------|
| **Backend** | Laravel 11, SQLite, Filament v3, PHP 8.4 | API REST (`/api/items`, `/api/quotes`), persistencia de datos, cálculo de cotizaciones vía `QuoteCalculator` (Gemini API) |
| **Frontend** | Alpine.js, Tailwind CSS (CDN), Blade (`cotizador.blade.php`) | UI interactiva, carga dinámica de ítems, envío de formulario a la API, presentación de resultados |
| **Panel Admin** | Filament (Resources: Agent, Vehicle, Item, Quote, User) | CRUD de catálogos, asignación de agentes, visualización de cotizaciones |
| **Configuración** | `config/mudanzas.php` y `config.php` (compatibilidad) | Parámetros de negocio (tarifas, salarios, precios de combustible, etc.) |
| **Deploy** | Vite (`npm run build`) para assets estáticos, Laravel Artisan para migraciones y cache | Preparación de entorno de producción |

## 🚀 Uso rápido

```bash
# Clonar y entrar al proyecto
git clone <repo-url> cotizador-mudanzas
cd cotizador-mudanzas

# Instalar dependencias PHP y JS
composer install
npm install

# Configurar .env (APP_NAME, APP_URL) y ejecutar migraciones
php artisan migrate --seed

# Compilar assets
npm run build

# Servir la aplicación
php artisan serve
```

Accede a:
- Frontend: `http://cotizador-mudanzas.test/`
- Panel admin: `http://cotizador-mudanzas.test/admin` (credenciales: **admin@mudanzashnosmonroy.com** / `password`)

## 📊 Modelo de Costeo ABC (Activity-Based Costing)

La aplicación implementa un sistema avanzado de **Costeo Basado en Actividades (ABC)** para calcular presupuestos rentables y precisos. En lugar de aplicar márgenes genéricos, el costeo ABC asume que *las mudanzas consumen actividades, y las actividades consumen recursos*.

### 1. Las 5 Actividades del Modelo
El cálculo del costo operativo se desglosa en 5 actividades principales parametrizables en `config/mudanzas.php`:

1. **Actividad A: Comercial y Planificación**
   - **Inductor (Driver):** Número de cotización.
   - **Cálculo:** Tarifa administrativa fija (ej. `$150.00 MXN`).
2. **Actividad B: Embalaje y Preparación**
   - **Inductor (Driver):** Cantidad de ítems + horas de embalaje y desarme.
   - **Cálculo:** Costo acumulado de materiales de cada ítem + (Tiempo de embalaje y desarme total en horas × Salario por hora de operario × Cantidad de personal). Añade 15 minutos extras automáticos por cada mueble que tenga la bandera `requiere_desarmarse = true`.
3. **Actividad C: Carga y Estiba**
   - **Inductor (Driver):** Volumen total ($m^3$) + Dificultad de acceso en origen (pisos y caminata).
   - **Cálculo:** (Volumen × Tarifa base de carga) + Recargos por escaleras (si no hay elevador y es piso $> 1$) + Recargo por caminata (si la distancia al camión supera los 10 metros).
4. **Actividad D: Transporte (Conducción)**
   - **Inductor (Driver):** Kilómetros recorridos (calculados con precisión mediante Gemini API).
   - **Cálculo:** Combustible consumido + Costo de depreciación/seguro por Km (específico para el vehículo sugerido) + Salarios del personal durante las horas de traslado.
5. **Actividad E: Descarga y Desembalaje**
   - **Inductor (Driver):** Volumen total ($m^3$) + Dificultad de acceso en destino (pisos y caminata).
   - **Cálculo:** (Volumen × Tarifa base de descarga) + Recargos por escaleras en destino + Recargos por caminata en destino.

---

### 2. Impacto en el Asistente (Wizard)
El asistente pasó de ser un formulario logístico lineal y simple a un colector de complejidad estructural:
- **Paso 5 (Origen):** Solicita el número de pisos de origen, disponibilidad de ascensor y distancia estimada hasta el camión.
- **Paso 6 (Destino):** Añade un nuevo paso idéntico para recopilar los mismos drivers de acceso (pisos, ascensor y distancia) en el domicilio de destino.
- El backend procesa estos drivers en `QuoteCalculator` y almacena tanto los datos logísticos como el costo detallado de cada una de las 5 actividades en la base de datos.

---

### 3. Cálculo del Presupuesto Final
Una vez calculado el costo operativo total sumando las 5 actividades:
1. Se le añade el margen de ganancia configurable (`ganancia_porcentaje`, por defecto 50% extra).
2. Se compara el resultado con la `tarifa_minima` configurada.
3. Si el total es inferior, se aplica la tarifa mínima.
4. El desglose detallado se almacena en la tabla de base de datos para auditoría, se muestra en el panel Filament y se adjunta de forma desglosada en el PDF administrativo para el cálculo de comisiones.

---

## 📈 Roadmap

### Corto plazo (0‑3 meses)
- Publicar la versión **v1.0** en Packagist como paquete Laravel.
- Añadir pruebas unitarias y de integración para el `QuoteCalculator`.
- Mejorar la documentación de la API (OpenAPI/Swagger).

### Mediano plazo (3‑9 meses)\n- Implementar **modo multilenguaje** (i18n) para soportar inglés y español.
- Añadir **Webhooks** para notificar a sistemas externos cuando se genera una cotización.
- Integrar **Stripe** o **PayPal** para permitir pagos de adelanto directamente desde el widget.
- Optimizar la llamada a Gemini con caché de distancias frecuente.

### Largo plazo (9‑18 meses)
- Convertir el componente en **micro‑frontend** (importable vía npm o CDN) para uso en SPA (React/Vue/Angular).
- Añadir **machine‑learning** para sugerir ítems basados en datos históricos.
- Soporte para **carga masiva** de catálogos de ítems mediante CSV/JSON.
- Escalado a **multi‑tenant** permitiendo a distintas empresas de mudanzas usar la misma instancia.

## 🤝 Contribuciones

Las contribuciones son bienvenidas.  Por favor abre un *pull request* siguiendo las guías de estilo de Laravel y escribe pruebas para cualquier nueva funcionalidad.

---

*Este proyecto es una adaptación de un ejemplo original llamado **Mudango**; todos los rastros de esa marca han sido sustituidos por **Mudanzas Hermanos Monroy**.*