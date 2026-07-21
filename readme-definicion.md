# Definición y Alcance: Cotizador de Mudanzas

## 📦 Descripción de la Aplicación

**Mudanzas Hermanos Monroy – Cotizador de Mudanzas** es un componente completo de cotización diseñado para ser insertado (embeddable) en cualquier sitio web o aplicación externa.

La aplicación funciona bajo una arquitectura de *micro-servicio*, proveyendo una interfaz de usuario moderna, interactiva y responsiva (construida con Alpine.js y Tailwind CSS) que guía al cliente paso a paso. Durante este proceso, el usuario puede seleccionar sus artículos, calcular volúmenes, y obtener una estimación de distancia automatizada (mediante la API de Gemini), resultando en un precio sugerido detallado. El frontend consume de forma independiente una API REST, por lo que su integración no requiere conocimientos profundos del backend.

## 🎯 Alcance del Proyecto

El alcance actual de la aplicación incluye las siguientes características y componentes principales:

1. **Widget de Cotización (Frontend):**
   - Interfaz de usuario con estética premium, glass-morphism y animaciones.
   - Asistente (Wizard) paso a paso para la selección de inventario de mudanza.
   - Cálculo automático de volumen total.
   - Estimación de distancia entre el origen y destino utilizando inteligencia artificial (Gemini API).
   - Presentación de la cotización final sugerida al cliente.

2. **API REST y Lógica de Negocio (Backend):**
   - Desarrollado en Laravel 11 y PHP 8.4.
   - Endpoints API (`/api/items`, `/api/quotes`) para alimentar el frontend de forma desacoplada.
   - Gestión de parámetros de negocio configurables mediante archivos de configuración.

3. **Panel Administrativo (Backoffice):**
   - Panel de administración construido con Filament v3.
   - Gestión y mantenimiento de catálogos (CRUD): Agentes, Vehículos, Ítems, Usuarios y Cotizaciones.
   - Visualización de las solicitudes de cotización recibidas y asignación de agentes.

## 🚀 Características a Futuro

Las siguientes características están planificadas para implementaciones posteriores y **no** forman parte del alcance inicial:

- **Modelo de Costeo ABC (Activity-Based Costing):** 
  Implementación de un sistema avanzado para calcular presupuestos rentables y precisos asumiendo que las mudanzas consumen actividades. Desglosará el costo en 5 actividades principales:
  1. **Comercial y Planificación:** Tarifa administrativa fija.
  2. **Embalaje y Preparación:** Basado en cantidad de ítems, tiempo de desarme y materiales.
  3. **Carga y Estiba:** Contemplando recargos por escaleras, falta de ascensor y distancia de caminata en origen.
  4. **Transporte:** Consumo de combustible, depreciación por km y salarios de traslado.
  5. **Descarga y Desembalaje:** Recargos por dificultad de acceso en el destino.
  Este modelo impactará el asistente para recolectar información logística detallada (pisos, distancia al vehículo, etc.) y guardará un desglose financiero completo.

- **Modo Multilenguaje:** Soporte i18n para inglés y español.
- **Pagos Integrados:** Integración con Stripe o PayPal para pagos de adelanto desde el widget.
- **Webhooks:** Notificaciones a sistemas externos sobre nuevas cotizaciones.
- **Micro-frontend:** Conversión para uso directo en SPA (React, Vue, Angular).
- **Inteligencia Artificial:** Sugerencia de ítems mediante machine-learning basados en datos históricos.
- **Multi-tenant y Carga Masiva:** Soporte para múltiples empresas de mudanzas y carga de catálogos en CSV/JSON.
