# PRD: Migración AGC Assessors — Laravel + Filament (Clean Architecture)

**Versión:** 2.2  
**Fecha:** 2025-05-29  
**Autor:** Architect  
**Estado:** Draft  
**Proyecto:** `/home/yusney/app/agc/`  

---

## 1. Resumen Ejecutivo

Este documento define los requisitos técnicos y funcionales para la migración de la web corporativa de **AGC Assessors** (actualmente en un CMS headless/estático) a una arquitectura moderna basada en **Laravel 13 + Filament v5**, aplicando principios de **Clean Architecture**.

Este PRD integra las especificaciones funcionales del proyecto anterior (OpenSpec) descartando las implementaciones atadas a Strapi y Astro, y adaptándolas a la nueva arquitectura PHP.

**Objetivos principales:**
- Separar la lógica de negocio de los detalles de infraestructura (base de datos, Filament, web).
- Garantizar un backend administrativo robusto y extensible con Filament.
- Implementar multiidioma (catalán/español/inglés) de forma nativa y limpia.
- Soportar homepage con zonas dinámicas editables desde el panel.
- Optimizar rendimiento para VPS (evitar N+1, CSS ligero, eager loading, WebP).
- Facilitar el SEO dinámico y estructurado (JSON-LD, Open Graph, hreflang).
- Garantizar accesibilidad (teclado, ARIA, mobile 375px).
- Facilitar futuras integraciones (API, comandos de consola) sin duplicar código.

---

## 2. Contexto y Alcance

### 2.1 Web Actual (agcassessors.com)
Sitio corporativo de una asesoría española con más de 30 años de experiencia. Contenido estático con secciones informativas, servicios, oficinas, noticias y páginas legales.

**Idiomas identificados:** Catalán (principal), Español, Inglés (según especificación funcional heredada).

### 2.2 Alcance del PRD (MVP)
- Implementación de todas las secciones públicas con multiidioma (`ca`, `es`, `en`).
- Panel de administración Filament para gestionar:
  - **Posts (Noticias/Articles):** con categorías, autor, SEO.
  - **Categories:** para clasificar noticias.
  - **Oficinas:** 6 oficinas con datos de contacto y mapas.
  - **Servicios:** 6 departamentos con especializaciones y formularios específicos.
  - **Homepage Dinámica:** zonas editables (hero, stats, features, testimonials, CTA, carrusel).
  - **Páginas Estáticas:** About, Contact, Legales (privacidad, cookies, legal, uso).
  - **SEO:** metadatos personalizados por entidad.
  - **Menú de Navegación:** gestionable desde el panel.
- Clean Architecture: Domain / Infrastructure / Http / Filament.
- Assets: Tailwind CSS + Vite separado del panel Filament.
- SEO dinámico con DTOs/Value Objects.
- Multiidioma vía middleware de locale + `spatie/laravel-translatable`.
- Formularios de contacto, suscripción, lead capture, y formularios por departamento.
- Sitemap XML + hreflang alternates.
- Media Library con optimización WebP y transforms.

### 2.3 Fuera de Alcance (futuras fases)
- Migración de datos desde el CMS actual (se asume contenido fresco o migración manual).
- Integración real con Biloop API (solo enlaces).
- Sistema de caché distribuido (Redis) — se usará caché de archivo por defecto.
- Testing E2E con Playwright (se deja preparado pero no se implementa).
- Motor de búsqueda interno (diferido).

---

## 3. Arquitectura y Estructura del Proyecto

### 3.1 Filosofía: Clean Architecture Adaptada

La lógica de negocio vive en `Domain/` y no conoce nada de Eloquent, Filament ni HTTP. Las capas externas (`Infrastructure/`, `Http/`, `Filament/`) dependen de `Domain/`, nunca al revés.

```
app/
├── Domain/                         <- Lógica de negocio pura (ignora la Web y Filament)
│   ├── Shared/
│   │   ├── ValueObjects/
│   │   │   ├── Locale.php
│   │   │   ├── SEOData.php
│   │   │   ├── Image.php
│   │   │   └── Address.php
│   │   └── Exceptions/
│   │       └── DomainException.php
│   ├── Posts/
│   │   ├── Models/
│   │   │   └── Post.php
│   │   ├── Actions/
│   │   │   ├── CreatePostAction.php
│   │   │   ├── UpdatePostAction.php
│   │   │   └── DeletePostAction.php
│   │   ├── Repositories/
│   │   │   └── PostRepositoryInterface.php
│   │   └── ValueObjects/
│   │       └── PostStatus.php
│   ├── Categories/
│   │   ├── Models/
│   │   │   └── Category.php
│   │   └── Repositories/
│   │       └── CategoryRepositoryInterface.php
│   ├── Offices/
│   │   ├── Models/
│   │   │   └── Office.php
│   │   ├── Actions/
│   │   │   └── CreateOfficeAction.php
│   │   └── Repositories/
│   │       └── OfficeRepositoryInterface.php
│   ├── Services/
│   │   ├── Models/
│   │   │   └── Service.php
│   │   ├── Actions/
│   │   │   └── CreateServiceAction.php
│   │   └── Repositories/
│   │       └── ServiceRepositoryInterface.php
│   ├── Pages/
│   │   ├── Models/
│   │   │   ├── Page.php
│   │   │   └── ContentSection.php
│   │   └── Repositories/
│   │       └── PageRepositoryInterface.php
│   └── Contacts/
│       ├── Actions/
│       │   └── SubmitContactFormAction.php
│       └── ValueObjects/
│           └── ContactPayload.php
│
├── Infrastructure/                 <- Implementaciones técnicas
│   ├── Persistence/
│   │   ├── Eloquent/
│   │   │   ├── Models/
│   │   │   │   ├── EloquentPost.php
│   │   │   │   ├── EloquentCategory.php
│   │   │   │   ├── EloquentOffice.php
│   │   │   │   ├── EloquentService.php
│   │   │   │   ├── EloquentPage.php
│   │   │   │   └── EloquentContentSection.php
│   │   │   └── Mappers/
│   │   │       ├── PostMapper.php
│   │   │       ├── OfficeMapper.php
│   │   │       ├── ServiceMapper.php
│   │   │       └── PageMapper.php
│   │   └── Repositories/
│   │       ├── EloquentPostRepository.php
│   │       ├── EloquentCategoryRepository.php
│   │       ├── EloquentOfficeRepository.php
│   │       ├── EloquentServiceRepository.php
│   │       └── EloquentPageRepository.php
│   └── Filament/
│       └── Components/
│           └── TranslatableForm.php
│
├── Http/                           <- Frontend Público
│   ├── Web/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php
│   │   │   ├── PostController.php
│   │   │   ├── OfficeController.php
│   │   │   ├── ServiceController.php
│   │   │   ├── PageController.php
│   │   │   └── ContactController.php
│   │   ├── Requests/
│   │   │   ├── ContactFormRequest.php
│   │   │   ├── SubscriptionFormRequest.php
│   │   │   └── ServiceFormRequest.php
│   │   └── ViewModels/
│   │       ├── HomeViewModel.php
│   │       ├── PostViewModel.php
│   │       └── ServiceViewModel.php
│   └── Middleware/
│       └── SetLocale.php
│
├── Filament/                       <- Backend Administrativo (aislado)
│   └── Resources/
│       ├── PostResource.php
│       ├── CategoryResource.php
│       ├── OfficeResource.php
│       ├── ServiceResource.php
│       ├── PageResource.php
│       └── NavigationResource.php
│
└── Providers/
    └── AppServiceProvider.php      <- Binding interfaces -> implementaciones
```

### 3.2 Principios Aplicados

| Principio | Implementación |
|-----------|----------------|
| **SRP (Single Responsibility)** | Cada `Action` realiza UNA operación de negocio. Los controladores y recursos Filament delegan en Actions. |
| **Dependency Inversion** | `Domain` define interfaces (`PostRepositoryInterface`). `Infrastructure` las implementa (`EloquentPostRepository`). |
| **Separation of Concerns** | Web (`Http/`) y Admin (`Filament/`) son capas distintas que usan el mismo `Domain/`. |
| **Explicit over Implicit** | Tipado estricto `declare(strict_types=1);` en TODO archivo PHP. |

---

## 4. Stack Tecnológico

| Capa | Tecnología | Versión | Justificación |
|------|-----------|---------|---------------|
| Framework | Laravel | ^13.0 | Última versión estable, ecosistema maduro |
| Admin Panel | Filament | ^5.0 | Última versión estable, rápido, extensible, ya usa Tailwind |
| PHP | PHP | ^8.4 | Última versión estable con soporte activo, tipado estricto, readonly classes, enums |
| DB | PostgreSQL | ^16.0 | Potente, ACID, JSON nativo, full-text search, mejor soporte para JSON que MariaDB |
| Frontend CSS | Tailwind CSS | ^4.0 | Utility-first, bundle ligero |
| Build Tool | Vite | ^6.0 | Nativo en Laravel 13, HMR |
| Multiidioma DB | spatie/laravel-translatable | ^6.9 | JSON limpio por campo, traducciones en DB |
| Multiidioma App | laravel-localization | ^2.0 | Rutas con prefijo de idioma (`/ca/`, `/es/`, `/en/`) |
| Media | spatie/laravel-medialibrary | ^11.0 | Gestión de imágenes, WebP, transforms, responsive |
| Filament SEO | seo-tools (o custom) | - | Meta tags dinámicos por recurso |
| Testing | PHPUnit / Pest | ^3.0 | Nativo en Laravel |
| Code Quality | PHPStan (nivel 8) | ^2.0 | Análisis estático estricto |

---

## 4.2 Sistema de Diseño Visual (Stitch MCP)

El diseño frontend está basado en el proyecto Stitch `4268090125153922863` que contiene un Design System completo y 6 pantallas de referencia visual.

### Pantallas de Referencia

| Pantalla | Título | Dimensiones | Propósito |
|----------|--------|-------------|-----------|
| `2a70f853` | Home - AGC Assessors | 2560×4774 | Diseño Home original |
| `04d7dbd3` | Home - Rediseño Moderno v2 | 2560×6950 | **Home redesign moderno** |
| `01d8b27c` | Noticias - Rediseño Editorial v2 | 2560×2608 | **News grid editorial** |
| `170e7fa5` | Noticias - AGC Assessors | 2560×2492 | News original |
| `dd75e407` | Artículo - Experiencia Premium v2 | 2560×6842 | **Artículo premium** |
| `43898782` | Artículo - AGC Assessors | 2560×5658 | Artículo original |

> **Nota**: Las pantallas marcadas en **negrita** son las versiones de rediseño que deben implementarse.

### Tokens de Diseño

#### Paleta de Colores

| Token | Valor | Uso |
|-------|-------|-----|
| `--primary` | `#00346f` | Azul corporativo primario |
| `--primary-container` | `#004a99` | Variante primario (más claro) |
| `--surface` | `#f9f9ff` | Fondo principal |
| `--surface-dim` | `#d9d9e1` | Fondo atenuado |
| `--surface-container` | `#ededf5` | Contenedores de sección |
| `--on-surface` | `#191c21` | Texto principal |
| `--on-surface-variant` | `#424751` | Texto secundario |
| `--secondary` | `#5c5f61` | Color secundario |
| `--tertiary` | `#5f2200` | Color terciario (naranja/café) |
| `--tertiary-container` | `#833301` | Contenedor terciario |
| `--accent-cyan` | `#00B4D8` | Acento cian |
| `--text-main` | `#1E293B` | Texto principal alternativo |
| `--text-muted` | `#64748B` | Texto desactivado |
| `--border-light` | `#E2E8F0` | Bordes ligeros |
| `--error` | `#ba1a1a` | Errores |

#### Tipografía

| Token | Familia | Tamaño | Peso | Altura | Espaciado |
|-------|---------|--------|------|--------|-----------|
| `display-lg` | Outfit | 48px | 600 | 1.2 | -0.02em |
| `headline-lg` | Outfit | 32px | 600 | 1.3 | — |
| `headline-lg-mobile` | Outfit | 28px | 600 | 1.3 | — |
| `headline-md` | Outfit | 24px | 600 | 1.4 | — |
| `body-lg` | Inter | 18px | 400 | 1.6 | — |
| `body-md` | Inter | 16px | 400 | 1.6 | — |
| `label-md` | Inter | 14px | 500 | 1.2 | 0.01em |
| `caption` | Inter | 12px | 400 | 1.4 | — |

#### Espaciado

| Token | Valor | Uso |
|-------|-------|-----|
| `container-max` | 1280px | Ancho máximo del contenedor |
| `reading-width` | 720px | Ancho máximo de lectura (artículos) |
| `gutter` | 24px | Gutter entre columnas |
| `margin-mobile` | 16px | Margen en móvil |
| `stack-sm` | 8px | Espaciado pequeño |
| `stack-md` | 16px | Espaciado medio |
| `stack-lg` | 32px | Espaciado grande |

#### Bordes Redondeados

| Token | Valor | Uso |
|-------|-------|-----|
| `sm` | 0.25rem (4px) | Elementos pequeños |
| `DEFAULT` | 0.5rem (8px) | **Default (ROUND_EIGHT)** |
| `md` | 0.75rem (12px) | Tarjetas medianas |
| `lg` | 1rem (16px) | Secciones grandes |
| `xl` | 1.5rem (24px) | Hero/modales |
| `full` | 9999px | Píldoras, avatares |

### Requisitos Visuales del Frontend

**RF-V1:** El frontend DEBE usar Outfit para títulos/headlines e Inter para cuerpo de texto.

**RF-V2:** La paleta de colores DEBE respetar los tokens definidos arriba, usando CSS variables o Tailwind theme extend.

**RF-V3:** El ancho máximo de lectura para artículos DEBE ser 720px (`reading-width`).

**RF-V4:** El contenedor principal DEBE ser 1280px (`container-max`) con gutters de 24px.

**RF-V5:** Las tarjetas y contenedores DEBEN usar bordes redondeados de 8px (ROUND_EIGHT).

### Requisitos Responsive (Mobile-First)

**RF-R1:** El diseño DEBE seguir el enfoque **mobile-first**: estilos base para móvil, breakpoints `md:` (768px) y `lg:` (1024px) para tablet y desktop.

**RF-R2:** Los **breakpoints** DEBEN ser:
- Mobile: 375px - 767px
- Tablet: 768px - 1023px  
- Desktop: 1024px+
- Wide: 1280px+ (container-max)

**RF-R3:** El layout DEBE ser legible en **375px sin overflow ni contenido comprimido**.

**RF-R4:** El menú de navegación DEBE convertirse en **menú hamburguesa** en móvil (< 768px) con animación suave de apertura/cierre.

**RF-R5:** Los **touch targets** (botones, enlaces, inputs) DEBEN tener un área mínima de **44×44px** en móvil.

**RF-R6:** Las tarjetas de servicios y noticias DEBEN apilarse en una sola columna en móvil, 2 columnas en tablet, 3 columnas en desktop.

**RF-R7:** El hero de la home DEBE adaptar su altura: compacto en móvil (60vh), mediano en tablet (70vh), grande en desktop (80vh).

**RF-R8:** Las tipografías DEBEN escalar responsive:
- `display-lg`: 36px (móvil) → 42px (tablet) → 48px (desktop)
- `headline-lg`: 24px (móvil) → 28px (tablet) → 32px (desktop)
- `body-lg`: 16px (móvil) → 17px (tablet) → 18px (desktop)

**RF-R9:** El footer DEBE apilar sus secciones verticalmente en móvil y distribuirse en columnas en desktop.

**RF-R10:** Las imágenes DEBEN usar `srcset` y `sizes` para servir variantes optimizadas según el viewport.

**RF-R11:** El selector de idioma DEBE ser accesible en móvil (no oculto dentro del menú hamburguesa, sino visible o en un submenu dedicado).

**RF-R12:** Las tablas (si las hay, ej: comparativa de servicios) DEBEN hacer scroll horizontal en móvil con indicador visual.

**RF-R13:** El formulario de contacto DEBE tener inputs de ancho completo en móvil y apilarse verticalmente.

### Buenas Prácticas de Diseño Frontend (frontend-design skill)

**RF-DF1:** El diseño DEBE tener una **dirección estética clara e intencional** — no genérica. La estética es "Minimalismo Profesional con Toques Corporativos": limpio, autoritario, con acentos de color cian que transmiten modernidad sin perder la seriedad del sector legal/fiscal.

**RF-DF2:** La **tipografía DEBE ser distintiva**. Outfit (display) e Inter (body) ya lo son, pero DEBEN usarse con intención:
- Outfit solo para títulos, hero text, y números grandes (stats).
- Inter para todo el cuerpo, navegación, y formularios.
- NUNCA usar Arial, Roboto, o system fonts como fallback principal.

**RF-DF3:** La **paleta DEBE ser dominante y cohesiva**:
- Azul corporativo `#00346f` como color dominante (60% de la interfaz).
- Blanco/gris `#f9f9ff` como fondo (30%).
- Cian `#00B4D8` como acento en CTAs, hover states, y elementos interactivos (10%).
- NO distribuir colores de forma equitativa ni usar gradientes genéricos.

**RF-DF4:** Las **animaciones DEBEN ser CSS-only** donde sea posible, priorizando:
- Micro-interacciones en hover de botones (scale 1.02, shadow elevation).
- Transiciones suaves en el menú hamburguesa.
- Staggered reveals en scroll para secciones de la home (hero, stats, servicios).
- NUNCA animar propiedades que disparen layout (width, height, top, left) — usar transform y opacity.
- Respetar `prefers-reduced-motion`.

**RF-DF5:** La **composición espacial DEBE romper la monotonía**:
- Asimetría controlada: el hero puede tener texto a la izquierda y un elemento visual abstracto a la derecha.
- Overlap sutil: tarjetas de servicios que se superponen ligeramente a la sección anterior.
- Generous negative space en secciones de confianza (stats, testimonials).
- Grid-breaking elements: una estadística grande que sobresale del grid en desktop.

**RF-DF6:** Los **detalles visuales DEBEN crear atmósfera**:
- Gradientes sutiles (mesh) en el hero o CTA sections, nunca como fondo principal.
- Sombras dramáticas pero controladas en tarjetas elevadas (`shadow-lg` en hover).
- Decorative borders en secciones de separación (líneas finas con gradiente).
- NO usar fondos sólidos aburridos en todas las secciones — alternar `surface` y `surface-container`.

**RF-DF7:** Las **tarjetas de servicios y noticias DEBEN tener un "momento memorable"**:
- Hover state que eleva la tarjeta (`translateY(-4px)` + sombra más pronunciada).
- Imagen con zoom sutil en hover (`scale(1.05)` dentro de un overflow-hidden).
- Tag de categoría con fondo semitransparente (`bg-primary/10`) en lugar de sólido.

**RF-DF8:** El **CTA (Call to Action) DEBE ser imposible de ignorar**:
- Botón primario: fondo `#004a99`, texto blanco, bordes redondeados `xl` (12px).
- Hover: transición a `#00346f` con sombra y scale 1.02.
- Espaciado generoso alrededor (padding `py-4 px-8`).
- Siempre acompañado de microcopy que reduzca fricción (ej: "Contactar gratis").

**RF-DF9:** El **footer DEBE ser una sección de "cierre" digna**, no un afterthought:
- Fondo `#191c21` (inverse-surface) con texto claro para contraste inverso.
- Logo + tagline + links organizados en columnas claras.
- Selector de idioma visible y prominente.
- Copyright + legal links alineados y espaciados.

**RF-DF10:** Los **formularios DEBEN sentirse "refinados"**:
- Inputs con bordes sutiles (`border-light`), no bordes gruesos por defecto.
- Focus state con ring azul (`ring-2 ring-primary/50`) en lugar de outline del navegador.
- Labels flotantes o bien posicionados, no placeholder-as-label.
- Botón de submit ancho completo en móvil, ancho auto en desktop.

---

## 4.1 Infraestructura Docker

Todo el entorno de desarrollo y producción se orquesta mediante Docker Compose. Esto garantiza que cada desarrollador ejecute exactamente las mismas versiones de PHP, PostgreSQL, Node, etc., eliminando el clásico "funciona en mi máquina".

### 4.1.1 Servicios Docker

| Servicio | Imagen Base | Puerto | Rol |
|----------|-------------|--------|-----|
| **php** | `php:8.4-fpm-alpine` (custom) | 9000 | PHP-FPM con extensiones completas, Composer, Xdebug |
| **nginx** | `nginx:alpine` | `${APP_PORT:-8080}` | Reverse proxy y servidor de archivos estáticos |
| **database** | `postgres:16-alpine` (EXTERNAL) | `${DB_PORT:-5432}` | Base de datos PostgreSQL (servidor externo) |
| **node** | `node:24-alpine` | `${VITE_PORT:-5173}` | Vite HMR para desarrollo frontend |
| **redis** | `redis:7-alpine` | `${REDIS_PORT:-6379}` | Caché, sesiones y colas |
| **mailpit** | `axllent/mailpit:latest` | 8025 / 1025 | Captura de emails en desarrollo (UI + SMTP) |
| **queue** | `php:8.4-fpm-alpine` | - | Worker de colas de Laravel (perfil `queue`) |
| **scheduler** | `php:8.4-fpm-alpine` | - | Ejecución de tareas programadas de Laravel (perfil `scheduler`) |

### 4.1.2 Extensiones PHP Instaladas

El Dockerfile de PHP instala **todas las extensiones requeridas por Laravel 13** más las necesarias para Filament 5, Spatie Media Library y desarrollo:

- **Core Laravel:** `gd`, `bcmath`, `ctype`, `curl`, `dom`, `exif`, `fileinfo`, `filter`, `hash`, `intl`, `mbstring`, `opcache`, `pdo`, `pdo_pgsql`, `pgsql`, `session`, `simplexml`, `sockets`, `tokenizer`, `xml`, `xmlwriter`, `xsl`, `zip`
- **Filament / Media:** `imagick` (para Spatie Media Library), `redis`
- **Desarrollo:** `xdebug` (puerto 9003, IDE key `AGC`, auto-detect)

### 4.1.3 Flujo de Inicialización

```
./docker/setup.sh  (o  make install)
├── Verifica Docker y Docker Compose
├── Crea .env desde .env.example
├── Crea directorios necesarios (storage, cache, etc.)
├── Construye imagen PHP custom
├── Inicia: database, redis, mailpit (espera healthcheck)
├── Inicia: php, nginx, node
├── composer install
├── npm install
├── php artisan key:generate
├── php artisan migrate --force
├── php artisan db:seed --force
├── php artisan storage:link
└── php artisan optimize
```

### 4.1.4 Comandos de Desarrollo (Makefile)

Se provee un `Makefile` completo para abstraer los comandos Docker:

- **Ciclo de vida:** `make build`, `make up`, `make down`, `make restart`, `make fresh`
- **Shell:** `make shell` (PHP), `make shell-node`, `make shell-db`
- **Composer:** `make composer-install`, `make composer-update`, `make composer ARGS="..."`
- **Artisan:** `make artisan ARGS="..."`, `make migrate`, `make migrate-fresh`, `make seed`
- **Filament:** `make filament-optimize`, `make filament-user`
- **Node:** `make npm-install`, `make dev`, `make build-assets`
- **Calidad:** `make test`, `make phpstan`, `make pint`
- **Mantenimiento:** `make clear`, `make optimize`, `make logs`, `make db-export`

### 4.1.5 Configuración de Xdebug

El contenedor PHP incluye Xdebug 3 pre-configurado para desarrollo remoto:

- **Puerto:** 9003
- **IDE Key:** `AGC`
- **Modo:** `debug,develop`
- **Client Host:** `host.docker.internal` (auto-detect con `discover_client_host=1`)

Soporta VSCode (extensión PHP Debug) y PhpStorm sin configuración adicional del lado del contenedor.

### 4.1.6 Variables de Entorno Docker

| Variable | Descripción | Default |
|----------|-------------|---------|
| `APP_PORT` | Puerto público de la aplicación | 8080 |
| `DB_PORT` | Puerto público de PostgreSQL | 5432 |
| `VITE_PORT` | Puerto público de Vite HMR | 5173 |
| `MAILPIT_UI_PORT` | Puerto de la UI de Mailpit | 8025 |
| `MAILPIT_SMTP_PORT` | Puerto SMTP de Mailpit | 1025 |
| `REDIS_PORT` | Puerto público de Redis | 6379 |
| `XDEBUG_MODE` | Modo Xdebug (`debug`, `off`, `develop`) | `debug` |

---

## 5. Requisitos Funcionales por Módulo

### 5.1 Módulo: Multiidioma (Locale)

**RF-L1:** Las URLs del frontend DEBEN incluir prefijo de idioma: `/ca/`, `/es/`, `/en/`.

**RF-L2:** El idioma por defecto DEBE ser el catalán (`ca`).

**RF-L3:** El idioma DEBE persistirse en sesión. Si un usuario accede a `/es/` y luego a `/`, debe redirigir a `/es/`.

**RF-L4:** El cambio de idioma DEBE realizarse vía un selector visible en el frontend (footer o header) y DEBE actualizar el sub-path de la URL (`/ca/noticies` -> `/es/noticias`).

**RF-L5:** El contenido traducible de la base de datos DEBE almacenarse como JSON por campo usando `spatie/laravel-translatable`.

**RF-L6:** El middleware `SetLocale` DEBE:
1. Leer el segmento de idioma de la URL.
2. Validar que sea un locale soportado (`ca`, `es`, `en`).
3. Ejecutar `app()->setLocale($locale)`.
4. Almacenar en sesión.
5. Si no hay locale en URL ni sesión, usar `ca`.

**RF-L7:** Las etiquetas `<html lang="...">` DEBEN coincidir con el locale activo.

**RF-L8:** OpenGraph DEBE incluir tags de locale (`og:locale`, `og:locale:alternate`).

**RF-L9:** Si una traducción falta para un locale, DEBE hacer fallback al español (`es`). Si no existe en español, al catalán (`ca`).

---

### 5.2 Módulo: Posts (Noticias / Articles)

**RF-P1:** Los posts DEBEN tener los siguientes campos:
- `title` (traducible)
- `slug` (traducible, único por idioma)
- `content` / rich text (traducible)
- `excerpt` (traducible, auto-generado si falta)
- `author` (relación a User)
- `publication_date` (compartido entre locales)
- `cover_image` (compartido, vía Media Library)
- `status` (borrador / publicado)
- SEO fields: `seo_title`, `seo_description`, `share_image` (traducibles)
- Relación opcional a una o más `Category`.

**RF-P2:** Los campos traducibles: title, slug, content, excerpt, seo_title, seo_description.

**RF-P3:** El frontend DEBE listar posts paginados (12 por página) ordenados por fecha de publicación descendente, mostrando solo publicados.

**RF-P4:** El frontend DEBE mostrar el detalle de un post con navegación a anterior/siguiente.

**RF-P5:** El slug DEBE ser único por idioma.

**RF-P6:** En el listado, si se muestra el autor, DEBE usar eager loading (`Post::with('author')`).

**RF-P7:** La página de detalle DEBE incluir:
- Indicador de progreso de lectura (reading progress).
- Botones de compartir (share).
- Formulario de contacto al final del artículo.

**RF-P8:** El feed de noticias DEBE permitir filtrado por categoría.

**RF-P9:** La home DEBE mostrar hasta 3 artículos recientes destacados.

---

### 5.3 Módulo: Categories

**RF-CAT1:** Cada categoría DEBE tener: `name` (traducible), `slug` (traducible).

**RF-CAT2:** Las categorías DEBEN poder asignarse a múltiples posts.

**RF-CAT3:** El slug de categoría DEBE ser único por idioma.

---

### 5.4 Módulo: Oficinas

**RF-O1:** Cada oficina DEBE tener:
- `name` (traducible)
- `address` (traducible)
- `city` (traducible)
- `postal_code`
- `phone`
- `email`
- `image` (vía Media Library)
- `map_url` / coordenadas GPS

**RF-O2:** Deben existir exactamente 6 oficinas.

**RF-O3:** Las oficinas DEBEN listarse en la página de inicio (destacadas) y en una página dedicada.

**RF-O4:** El frontend DEBE mostrar un enlace a Google Maps por cada oficina.

**RF-O5:** Los datos traducibles: name, address, city.

**RF-O6:** Cada oficina DEBE mostrar sus datos de contacto completos y un mapa embebido.

---

### 5.5 Módulo: Servicios

**RF-S1:** Deben existir exactamente 6 servicios, cada uno con un slug único por idioma.

**RF-S2:** Cada servicio DEBE tener:
- `title` (traducible)
- `subtitle` / `description` (traducible)
- `hero_text` (traducible)
- `cover_image` (vía Media Library)
- `specializations` (lista traducible)
- `department_key` (clave de departamento para formularios específicos)

**RF-S3:** Cada servicio DEBE tener una página individual accesible por slug localizado.

**RF-S4:** La página de servicio DEBE mostrar detalles, especializaciones y un formulario de contacto específico del departamento.

**RF-S5:** El formulario por departamento DEBE tener opciones pre-cargadas filtradas por `department_key`.

**RF-S6:** El schema JSON-LD de la página de servicio DEBE usar `Service` schema con: address, telephone, areaServed.

---

### 5.6 Módulo: Homepage Dinámica (Dynamic Zones)

**RF-H1:** El contenido de la homepage DEBE ser gestionable desde Filament, con una entrada por locale.

**RF-H2:** Cada entrada de homepage DEBE tener: `title`, `slug`, `locale`, y una colección ordenada de `contentSections`.

**RF-H3:** Los tipos de sección soportados DEBEN ser:
1. **Hero:** title, subtitle, CTA text, CTA link, background image.
2. **Image Carousel:** title, images (múltiples), autoplay (boolean), interval (segundos).
3. **Features Grid:** title, subtitle, items repetibles (icon, title, description, link).
4. **Stats Bar:** items repetibles (value, label).
5. **Testimonial:** title, quotes repetibles (quote text, author name, author role, author avatar).
6. **Call to Action:** title, subtitle, CTA text, CTA link, variant (estilo visual).

**RF-H4:** La zona dinámica DEBE poder estar vacía y devolver array vacío sin error.

**RF-H5:** Si la fuente de datos no está disponible, la home DEBE renderizar un fallback estático sin fallar.

**RF-H6:** La home DEBE mostrar:
- Hero above the fold (headline, subheadline, CTA).
- Hasta 3 artículos recientes destacados.
- Secciones dinámicas configuradas desde el panel.
- Lead capture / formulario de contacto.

---

### 5.7 Módulo: Páginas Estáticas

**RF-PG1:** Las páginas DEBEN ser gestionables desde Filament con: título, slug, contenido HTML, tipo de página (servicio, legal, informativa, about), SEO personalizado.

**RF-PG2:** La página **About / Nosaltres** DEBE incluir:
- Hero title, subtitle.
- Mission statement.
- Team description.
- Team image.
- Key statistics (ej: 30 años experiencia, 50 profesionales, 6 oficinas).
- CTA link configurado.

**RF-PG3:** La página de **Norma UNE 420.001** DEBE tener secciones ancladas (nacimiento, requerimientos, reconocimiento, beneficios).

**RF-PG4:** Las páginas legales DEBEN ser accesibles desde el footer de todas las páginas:
- Política de Privacidad (`/ca/politica-privacitat`, `/es/politica-privacidad`, `/en/privacy-policy`)
- Normas de Uso
- Política de Cookies
- Información Legal

---

### 5.8 Módulo: SEO

**RF-SEO1:** Cada entidad traducible (Post, Page, Office, Service) DEBE tener metadatos SEO:
- `meta_title` (traducible)
- `meta_description` (traducible)
- `og:image` (share image, vía Media Library)
- `og:title` (traducible)
- `og:description` (traducible)
- `canonical_url` (automático o configurable)

**RF-SEO2:** Los metadatos SEO DEBEN ser traducibles.

**RF-SEO3:** DEBE existir un `SEOData` DTO/Value Object que:
- Reciba una entidad de dominio.
- Genere la descripción truncada a 155 caracteres sin HTML.
- Formatee fechas en ISO 8601.
- Construya el JSON-LD apropiado (`NewsArticle`, `LocalBusiness`, `Service`, `WebPage`).

**RF-SEO4:** El layout principal DEBE recibir `SEOData` y renderar las meta tags correspondientes.

**RF-SEO5:** DEBE existir un **sitemap XML dinámico** con:
- Home page.
- News feed.
- Todas las páginas publicadas.
- Todos los posts publicados.
- Todos los servicios y oficinas.

**RF-SEO6:** Cada página DEBE incluir **hreflang alternate links** para `ca`, `es`, `en`.

**RF-SEO7:** El `<html lang>` DEBE coincidir con el locale activo.

**RF-SEO8:** OpenGraph DEBE incluir tags de locale (`og:locale`, `og:locale:alternate`).

---

### 5.9 Módulo: Formularios e Interacciones

**RF-F1 (Contacto General):**
- Campos: `name`, `surname`, `email`, `message`, `privacy_consent` (checkbox).
- Validación: todos obligatorios, email válido, consentimiento obligatorio.
- El checkbox de privacidad DEBE enlazar a la página de política de privacidad localizada.
- Enviar notificación a `info@agc.cat`.
- Mostrar mensaje de éxito/error.

**RF-F2 (Suscripción):**
- Campo: `email`.
- Validación: formato email.
- Almacenar suscriptor o disparar email de bienvenida.

**RF-F3 (Formulario de Artículo):**
- Aparece al final de cada artículo.
- Permite contactar con los autores.

**RF-F4 (Lead Capture):**
- Formulario en la home para captar leads.
- Enviar datos al sistema de procesamiento de formularios.
- Mostrar mensaje de éxito.

**RF-F5 (Formulario de Servicio):**
- Dropdown pre-llenado con opciones específicas del departamento (`department_key`).
- Opciones filtradas por departamento.
- Validación condicional según departamento.

**RF-F6:** Todos los formularios DEBEN usar FormRequest de Laravel con validación explícita.

---

### 5.10 Módulo: Navegación

**RF-N1:** La navegación global DEBE incluir:
- Nosaltres / About
- Serveis / Services (dropdown con 6 items)
- Oficines / Offices
- Notícies / News
- Contacte / Contact

**RF-N2:** El dropdown de servicios DEBE ser accesible por teclado:
- Abrir/cerrar con Enter / Space.
- Cerrar con Escape.
- Navegación con flechas.

**RF-N3:** DEBE existir menú hamburguesa para mobile.

**RF-N4:** DEBE existir selector de idioma visible.

**RF-N5:** Los enlaces de navegación DEBEN respetar el locale actual.

---

### 5.11 Módulo: Media (Imágenes)

**RF-M1:** Las imágenes DEBEN gestionarse centralizadamente mediante **Spatie Media Library**.

**RF-M2:** Formatos requeridos:
- WebP para entrega web.
- Compresión automática.
- Soporte de resizing/transforms.

**RF-M3:** Tipos de imágenes a gestionar:
- Cover image de artículos.
- Team image.
- Office images.
- Hero background images.
- Carousel images.
- Testimonial avatars.
- Share image para SEO.

**RF-M4:** Cada imagen DEBE generar variantes responsive (srcset) cuando aplique.

---

## 6. Requisitos No Funcionales

**RNF-1 (Rendimiento):**
- El CSS público DEBE pesar < 20KB minificado y gzip.
- Las consultas a la base de datos en listados DEBEN usar eager loading obligatoriamente.
- No DEBEN producirse consultas N+1 en ningún listado público.
- Las peticiones independientes de la home DEBEN ejecutarse en paralelo donde sea posible.
- Las imágenes DEBEN servirse en WebP con compresión.

**RNF-2 (Seguridad):**
- Todas las entradas de usuario DEBEN sanitizarse.
- CSRF en todos los formularios.
- Filament DEBE estar protegido con autenticación.
- Rate limiting en endpoints de formulario.

**RNF-3 (SEO Técnico):**
- Sitemap XML generado automáticamente.
- Canonical URLs por idioma.
- `hreflang` alternates en head para `ca`, `es`, `en`.
- `<html lang>` dinámico.

**RNF-4 (Accesibilidad):**
- Dropdown de servicios accesible por teclado (Enter, Space, Escape, flechas).
- Indicador de progreso de lectura DEBE tener estado ARIA válido o estar oculto como decorativo.
- Layout mobile DEBE ser legible a 375px sin overflow ni contenido comprimido.
- Contraste de color WCAG AA (ratio mínimo 4.5:1 para texto normal, 3:1 para texto grande).
- Labels asociados a todos los inputs de formulario.
- **Skip to content** link para navegación por teclado.
- Focus visible en todos los elementos interactivos (outline claro, no solo color).
- Menú hamburguesa DEBE tener `aria-expanded`, `aria-controls`, y cerrarse con Escape.
- Las imágenes informativas DEBEN tener `alt` descriptivo; las decorativas DEBEN tener `alt=""`.
- Formularios DEBEN mostrar errores asociados a cada campo con `aria-describedby`.
- Animaciones DEBEN respetar `prefers-reduced-motion`.

**RNF-5 (Mantenibilidad):**
- `declare(strict_types=1);` en TODO archivo PHP.
- PHPStan nivel 8 sin errores.
- Mínimo 80% de cobertura en Actions y Repositories.

---

## 7. API de Dominio (Contratos)

### 7.1 Post Repository Interface

```php
<?php
declare(strict_types=1);

namespace App\Domain\Posts\Repositories;

use App\Domain\Posts\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

interface PostRepositoryInterface
{
    public function findById(int $id): ?Post;
    
    public function findBySlug(string $slug, string $locale): ?Post;
    
    public function getPublishedPaginated(int $perPage = 12): LengthAwarePaginator;
    
    public function getRecentPublished(int $limit = 3): array;
    
    public function getPublishedByCategorySlug(string $slug, string $locale, int $perPage = 12): LengthAwarePaginator;
    
    public function save(Post $post): Post;
    
    public function delete(int $id): void;
}
```

### 7.2 CreatePostAction

```php
<?php
declare(strict_types=1);

namespace App\Domain\Posts\Actions;

use App\Domain\Posts\Models\Post;
use App\Domain\Posts\Repositories\PostRepositoryInterface;
use App\Domain\Posts\ValueObjects\PostStatus;

final readonly class CreatePostAction
{
    public function __construct(
        private PostRepositoryInterface $repository,
    ) {}

    public function execute(
        array $translations,
        ?string $featuredImage = null,
        ?\DateTimeImmutable $publishedAt = null,
        int $authorId = 1,
        array $categoryIds = [],
    ): Post {
        $post = new Post(
            id: null,
            translations: $translations,
            slug: $translations['ca']['slug'] ?? $translations['es']['slug'] ?? $translations['en']['slug'],
            status: PostStatus::DRAFT,
            featuredImage: $featuredImage,
            publishedAt: $publishedAt,
            authorId: $authorId,
            categoryIds: $categoryIds,
        );

        return $this->repository->save($post);
    }
}
```

### 7.3 SEOData Value Object

```php
<?php
declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

final readonly class SEOData
{
    public function __construct(
        public string $title,
        public string $description,
        public ?string $canonicalUrl = null,
        public ?string $ogImage = null,
        public ?string $ogTitle = null,
        public ?string $ogDescription = null,
        public ?string $locale = null,
        public ?array $alternateLocales = null,
        public ?array $jsonLd = null,
    ) {}

    public static function fromPost(Post $post, string $locale): self
    {
        $title = $post->getTranslation('seo_title', $locale) ?? $post->getTranslation('title', $locale);
        $description = $post->getTranslation('seo_description', $locale) 
            ?? self::truncate(strip_tags($post->getTranslation('excerpt', $locale) ?? ''), 155);
        
        return new self(
            title: $title,
            description: $description,
            ogTitle: $title,
            ogDescription: $description,
            ogImage: $post->featuredImage,
            locale: $locale,
            alternateLocales: ['ca', 'es', 'en'],
            jsonLd: [
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => $title,
                'description' => $description,
                'datePublished' => $post->publishedAt?->format(\DateTime::ATOM),
                'author' => [
                    '@type' => 'Organization',
                    'name' => 'AGC Assessors',
                ],
            ],
        );
    }

    private static function truncate(string $text, int $length): string
    {
        return mb_strlen($text) > $length 
            ? mb_substr($text, 0, $length) . '...' 
            : $text;
    }
}
```

---

## 8. Plan de Assets (Tailwind + Vite)

### 8.1 Separación de CSS

**Problema:** Filament compila su propio Tailwind. Si mezclamos configuraciones, el CSS público puede incluir clases del panel y viceversa.

**Solución:**
- `tailwind.config.js` en raíz → SOLO para el frontend público.
- Filament usa su propia compilación interna (no tocar).
- Tema personalizado de Filament registrado en `AdminPanelProvider` si se requiere branding corporativa.

### 8.2 Estructura de Assets

```
resources/
├── css/
│   └── app.css           <- @import 'tailwindcss'; + custom CSS mínimo
├── js/
│   └── app.js            <- Alpine.js para interactividad (mobile menu, dropdown, reading progress)
├── views/
│   ├── components/         <- Blade components reutilizables
│   ├── layouts/
│   │   └── app.blade.php  <- Layout principal con SEOData
│   └── pages/             <- Vistas públicas
└── images/
```

### 8.3 Configuración Vite

```js
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

---

## 9. Estrategia de Implementación (Fases)

### Fase 1: Bootstrap y Dominio (1-2 días)
- Instalar Laravel 13, Filament v5, spatie/laravel-translatable, spatie/laravel-medialibrary, laravel-localization.
- Configurar PHPStan nivel 8, Pest.
- Crear estructura de carpetas `Domain/`, `Infrastructure/`.
- Definir entidades de dominio, value objects y repositorios (Post, Category, Office, Service, Page, ContentSection).

### Fase 2: Infraestructura y Persistencia (2-3 días)
- Crear modelos Eloquent con traducciones y media collections.
- Implementar repositorios Eloquent + mappers.
- Crear migrations y seeders iniciales (6 servicios, 6 oficinas, página about, páginas legales).
- Binding de interfaces en AppServiceProvider.
- Implementar sitemap dinámico.

### Fase 3: Panel Administrativo Filament (3-4 días)
- Resources: PostResource, CategoryResource, OfficeResource, ServiceResource, PageResource.
- Campos traducibles con componente reusable TranslatableForm.
- Gestión de Media Library en recursos.
- Gestión de SEO por recurso.
- Homepage dinámica: gestor de ContentSections ordenables.
- Autenticación y autorización básica.

### Fase 4: Frontend Público (4-5 días)
- Middleware SetLocale con soporte para `ca`, `es`, `en`.
- Rutas con prefijo de idioma.
- Controladores + ViewModels.
- Layout principal con SEOData, hreflang, JSON-LD.
- Blade components y layouts.
- Home: hero dinámico, stats, features, testimonials, CTA, 3 posts recientes, lead form.
- News feed: grid, filtros, paginación.
- Article detail: reading progress, share, contact form.
- About: mission, team, stats.
- Offices: listado + mapas.
- Services: detalle + formulario por departamento.
- Contact: formulario general + info.
- Legales: páginas estáticas.

### Fase 5: Formularios y Notificaciones (1-2 días)
- Formulario de contacto general + envío de email.
- Formulario de suscripción.
- Formulario de lead capture.
- Formularios por departamento (service forms).
- Rate limiting y validaciones.

### Fase 6: Optimización, Accesibilidad y QA (2-3 días)
- Revisar eager loading en todas las queries.
- Auditar CSS final (debe ser < 20KB).
- Revisar accesibilidad: dropdown teclado, mobile 375px, ARIA.
- Testear multiidioma completo (`ca`, `es`, `en`).
- Revisión PHPStan y tests (mínimo 80% en Domain).
- WebP y responsive images.

---

## 10. Criterios de Aceptación

- [ ] La web pública replica todas las secciones de agcassessors.com con diseño corporativo.
- [ ] Todas las rutas públicas incluyen prefijo de idioma (`/ca/`, `/es/`, `/en/`).
- [ ] El selector de idioma actualiza la URL y el contenido sin errores.
- [ ] El panel Filament permite CRUD completo de posts, categorías, oficinas, servicios, páginas y homepage dinámica.
- [ ] Los campos traducibles se editan sin errores en `ca`, `es`, `en`.
- [ ] El SEO dinámico genera meta tags, Open Graph, JSON-LD y hreflang correctos en cada página.
- [ ] No hay consultas N+1 en los listados (verificado con Laravel Debugbar o logs).
- [ ] El CSS público minificado pesa menos de 20KB.
- [ ] Las imágenes se sirven en WebP con variantes responsive.
- [ ] PHPStan nivel 8 pasa sin errores.
- [ ] Los tests de dominio (Actions + Repositories) pasan con >80% cobertura.
- [ ] El dropdown de servicios es navegable por teclado (Enter, Space, Escape, flechas).
- [ ] El layout mobile es legible a 375px sin overflow.
- [ ] El sitemap XML incluye todas las páginas, posts, servicios y oficinas.
- [ ] Si la homepage no puede cargar contenido dinámico, renderiza fallback estático.

---

## 11. Notas y Decisiones de Arquitectura

**ND-A1: ¿Por qué no usar Eloquent directamente en Domain?**  
Para que el dominio sea 100% testable sin base de datos y pueda reutilizarse en futuros comandos de artisan o APIs.

**ND-A2: ¿Por qué Action pattern en lugar de Services?**  
Cada acción tiene una única responsabilidad y es fácil de testear unitariamente. Un PostService con 10 métodos viola SRP.

**ND-A3: ¿Por qué mappers en lugar de accesorios en Eloquent?**  
Los mappers explícitan la conversión Eloquent -> Domain. Son fáciles de testear y no dependen de magic methods de Eloquent.

**ND-A4: ¿Por qué separar Filament de Http?**  
Filament es una capa de presentación administrativa. Si mañana se reemplaza por otro panel o se expone una API, `Domain/` no se toca.

**ND-A5: ¿Por qué 3 idiomas (ca, es, en)?**  
La especificación funcional heredada del openspec requiere soporte para ES, EN y CA. El catalán es el idioma principal y fallback.

**ND-A6: ¿Por qué Media Library de Spatie en lugar de campos string?**  
Permite gestión centralizada, generación de variantes (thumbnails, WebP), responsive images, y desacopla la lógica de almacenamiento del dominio.

**ND-A7: ¿Por qué homepage dinámica con ContentSections?**  
El openspec especifica zonas dinámicas editables (hero, stats, features, testimonials, CTA, carrusel). Esto permite al equipo de marketing modificar la home sin código.

---

## 12. Referencias

- [Laravel 13 Docs](https://laravel.com/docs/13.x)
- [Filament v3 Docs](https://filamentphp.com/docs/3.x)
- [Spatie Laravel Translatable](https://github.com/spatie/laravel-translatable)
- [Spatie Laravel Media Library](https://github.com/spatie/laravel-medialibrary)
- [Clean Architecture (Uncle Bob)](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- Web actual: https://agcassessors.com/
- OpenSpec funcional heredado: `/home/yusney/app/agcassessors/openspec/`

---

*Fin del PRD v2.0*
