# Guía SEO para AGC Assessors

> **Manual práctico para editores y marketing** · Cómo rellenar correctamente los campos SEO (títulos, descripciones, imágenes) en cada página del sitio web.

---

## Tabla de contenidos

1. [Introducción](#1-introducción)
2. [Estructura básica de una página SEO](#2-estructura-básica-de-una-página-seo)
3. [Title (Título SEO)](#3-title-título-seo)
4. [Meta Description](#4-meta-description)
5. [URLs amigables](#5-urls-amigables)
6. [Imágenes — Todo lo que necesitas saber](#6-imágenes--todo-lo-que-necesitas-saber)
7. [Encabezados (H1, H2, H3)](#7-encabezados-h1-h2-h3)
8. [Contenido y texto](#8-contenido-y-texto)
9. [SEO Local (esencial para AGC)](#9-seo-local-esencial-para-agc)
10. [Checklist por página](#10-checklist-por-página)
11. [Errores comunes que debes evitar](#11-errores-comunes-que-debes-evitar)
12. [Herramientas recomendadas](#12-herramientas-recomendadas)
13. [Glosario rápido](#13-glosario-rápido)

---

## 1. Introducción

### ¿Qué es el SEO?

El SEO (Search Engine Optimization) es el conjunto de técnicas que hacen que tu web aparezca en los primeros resultados de Google cuando alguien busca "asesoría fiscal en Caldes de Montbui" o "gestoría laboral Mollet del Vallès". Cuanto mejor sea tu SEO, más clientes potenciales te encontrarán sin pagar publicidad.

### ¿Por qué es importante para AGC Assessors?

- El **75% de los usuarios** no pasa de la primera página de Google.
- El **40% de las búsquedas** tienen intención local (gente buscando un servicio cerca).
- Un buen SEO te trae clientes cualificados **sin pagar por clic**.

### ¿A quién va dirigida esta guía?

A las personas del equipo de marketing/editor que publican contenido en el panel de administración (`http://localhost:8080/admin`). No necesitas conocimientos técnicos: solo seguir las reglas de esta guía y rellenar los campos correctamente.

> **Importante**: AGC tiene tres idiomas (catalán, español, inglés). **Cada campo SEO debe rellenarse en los tres idiomas**. No dejes ningún idioma vacío. Google penaliza los huecos y considera contenido duplicado la misma descripción traducida automáticamente.

---

## 2. Estructura básica de una página SEO

Cuando Google muestra tu página en los resultados, el usuario ve tres elementos básicos:

```
┌─────────────────────────────────────────────────────────────────┐
│ Título de la página - hasta 60 caracteres          │ Marca    │  ← TITLE
│ https://www.agc.cat/es/serveis/assessoria-fiscal                │  ← URL
│ Descripción de la página, máximo 160 caracteres. Aquí va el     │  ← META
│ gancho para que el usuario haga clic.                           │  DESCRIPTION
└─────────────────────────────────────────────────────────────────┘
```

### Los 3 elementos principales

| Elemento | ¿Qué es? | ¿Dónde lo relleno en AGC? | Longitud |
|---|---|---|---|
| **Title** | El titular que se ve en Google y en la pestaña del navegador | `Título SEO (ca/es/en)` en cada Resource | 50–60 caracteres |
| **Meta Description** | La descripción que aparece bajo el título en Google | `Descripción SEO (ca/es/en)` en cada Resource | 150–160 caracteres |
| **URL** | La dirección de la página | Se genera automáticamente al crear la página | Limpia, con guiones |

### Recursos de Filament donde encontrarás estos campos

AGC tiene los siguientes recursos editables desde el panel de administración:

| Recurso | URL admin | ¿Tiene campos SEO? |
|---|---|---|
| **Pages** (Páginas estáticas) | `/admin/pages` | Sí: `seo_title`, `seo_description` (en ca, es, en) |
| **Services** (Servicios) | `/admin/services` | Sí: `seo_title`, `seo_description` (en ca, es, en) |
| **News** (Noticias / Blog) | `/admin/news` | Sí: `seo_title`, `seo_description` (en ca, es, en) |
| **Home Sections** (Secciones de inicio) | `/admin/home-sections` | Sí: `image_alt` para imágenes |
| **Offices** (Oficinas) | `/admin/offices` | Por confirmar con desarrollo |
| **Team Members** (Equipo) | `/admin/team-members` | Por confirmar con desarrollo |
| **Menu Items** (Menú de navegación) | `/admin/menu-items` | No tiene SEO propio |

> **Regla de oro**: si una página existe en tu web, debe tener título SEO y descripción SEO en los **tres idiomas**. No publicar nunca una página con los campos SEO vacíos.

---

## 3. Title (Título SEO)

El title es el **factor SEO on-page más importante**. Es lo que Google usa para entender de qué trata tu página y lo primero que ve el usuario en los resultados de búsqueda.

### Fórmula recomendada

```
Palabra clave principal + Diferenciador | Marca
```

**Ejemplo desglosado**:
`Asesoría fiscal para empresas | AGC Assessors`
- `Asesoría fiscal para empresas` → palabra clave principal
- `AGC Assessors` → marca (va al final, separada por `|`)

### Reglas fundamentales

1. **Longitud**: 50–60 caracteres. Google corta a partir de ~580px de ancho; en móvil caben menos caracteres.
2. **La palabra clave principal va al principio** (no después de la marca).
3. **Cada página debe tener un title único**. No copies el mismo título en dos páginas.
4. **Incluye la marca al final**, separada por `|` o `–`.
5. **No repetir** palabras clave (Google lo detecta como keyword stuffing).
6. **No abuses de mayúsculas** ni signos de exclamación. `¡¡OFERTA!!` es spam.
7. **El title debe ser distinto del H1** en la mayoría de los casos (puede parecerse, no ser idéntico).

### Ejemplos concretos para AGC

| Página | Title recomendado (es) |
|---|---|
| **Home** | `Asesoría fiscal, laboral y contable en Caldes de Montbui \| AGC Assessors` |
| **Servicios (índice)** | `Servicios de asesoría fiscal, laboral y contable \| AGC Assessors` |
| **Asesoría Fiscal** | `Asesoría fiscal para empresas y autónomos \| AGC Assessors` |
| **Asesoría Laboral** | `Asesoría laboral y gestión de nóminas \| AGC Assessors` |
| **Asesoría Contable** | `Asesoría contable y contabilidad para empresas \| AGC Assessors` |
| **Actualidad / Blog** | `Novedades fiscales y laborales \| Blog AGC Assessors` |
| **Artículo de blog (ejemplo)** | `Novedades fiscales 2026: qué cambia para empresas \| AGC` |
| **Contacto** | `Contacta con nosotros · AGC Assessors Caldes de Montbui` |
| **Oficinas (índice)** | `Oficinas en Caldes, Sant Celoni y Mollet \| AGC Assessors` |
| **Trabaja con nosotros** | `Trabaja con nosotros · AGC Assessors` |
| **Equipo** | `Conoce a nuestro equipo de profesionales \| AGC Assessors` |

### ✅ Ejemplos correctos vs ❌ ejemplos incorrectos

> ✅ `Asesoría fiscal para empresas en Caldes de Montbui | AGC`
> - Tiene keyword al principio, ubicación (local SEO), marca al final, 64 caracteres.

> ❌ `AGC Assessors - AGC Assessors - Asesoría fiscal en AGC`
> - Keyword stuffing, la marca se repite, no aporta valor.

> ❌ `Inicio`
> - Demasiado corto, no incluye keyword, no diferencia de otras páginas.

> ❌ `Asesoría fiscal para empresas en Caldes de Montbui, Sant Celoni, Mollet del Vallès, Granollers, Sabadell, Barcelona, todo tipo de asesoría fiscal, laboral, contable, mercantil, internacional, patrimonial | AGC Assessors - Los mejores asesores`
> - Demasiado largo (Google lo cortará), keyword stuffing, ilegible.

### Cómo medir la longitud

Cuenta manualmente: cada carácter (incluido el espacio y el `|`). Como regla práctica, si tu title cabe en una línea de WhatsApp, vas bien.

---

## 4. Meta Description

La meta description **no es un factor directo de ranking**, pero es el "anuncio gratuito" de tu página en Google. Una buena descripción aumenta el **CTR** (click-through rate), es decir, cuántas personas hacen clic en tu resultado frente a la competencia.

### Fórmula recomendada

```
Resumen natural + Beneficio/diferenciador + CTA + Ubicación
```

**Ejemplo desglosado**:
`Gestionamos IRPF, IVA e Impuesto sobre Sociedades con atención personalizada. Más de 25 años de experiencia en Caldes de Montbui. Pide consulta gratis.`
- `Gestionamos IRPF, IVA e Impuesto sobre Sociedades` → servicios (keywords)
- `con atención personalizada` → diferenciador
- `Más de 25 años de experiencia en Caldes de Montbui` → confianza + ubicación
- `Pide consulta gratis` → CTA (Call to Action)

### Reglas fundamentales

1. **Longitud**: 150–160 caracteres. Más largo y Google lo corta.
2. **Única por página** (nunca duplicar).
3. **Incluye la palabra clave principal** (Google la resalta en negrita cuando coincide con la búsqueda).
4. **Incluye un CTA** (llamada a la acción): "Contacta", "Pide cita", "Solicita consulta", "Descubre", "Lee más".
5. **Incluye ubicación** en páginas principales (AGC es negocio local).
6. **No la repitas palabra por palabra** del contenido de la página.
7. **Escribe para humanos**, no para Google. Que invite a hacer clic.

### Ejemplos concretos para AGC

| Página | Description recomendada (es) |
|---|---|
| **Home** | `Asesoría fiscal, laboral y contable con más de 25 años de experiencia en Caldes de Montbui. Atención personalizada para autónomos, pymes y grandes empresas. Pide consulta.` |
| **Servicios (índice)** | `Servicios de asesoría fiscal, laboral, contable, mercantil, patrimonial e internacional. Equipo actualizado y atención personalizada en Caldes, Sant Celoni y Mollet.` |
| **Asesoría Fiscal** | `Gestionamos IRPF, IVA trimestral, Impuesto sobre Sociedades y planificación fiscal estratégica. Equipo actualizado y atención personalizada en Caldes de Montbui. Pide consulta.` |
| **Asesoría Laboral** | `Elaboración de nóminas, contratos, gestión de bajas y asesoramiento en ERTEs y EREs. Más de 25 años gestionando relaciones laborales en el Vallès Oriental.` |
| **Asesoría Contable** | `Llevamos tu contabilidad al día: cuentas anuales, conciliación bancaria y reporting mensual. Visión clara de la situación financiera de tu empresa. Solicita presupuesto.` |
| **Actualidad / Blog** | `Novedades fiscales, laborales y contables para empresas y autónomos. Artículos prácticos escritos por el equipo de AGC Assessors.` |
| **Artículo de blog** | `Repasamos las principales modificaciones tributarias que entran en vigor en 2026 y cómo afectarán a tu empresa. Actualizado el 28 de mayo.` |
| **Contacto** | `Contacta con AGC Assessors. Te respondemos en menos de 24 horas. Tres oficinas en Caldes de Montbui, Sant Celoni y Mollet del Vallès. ¡Llámanos!` |
| **Oficinas (índice)** | `Tres oficinas en el Vallès Oriental: Caldes de Montbui, Sant Celoni y Mollet del Vallès. Encuentra la más cercana y ven a vernos.` |

### ✅ Ejemplos correctos vs ❌ ejemplos incorrectos

> ✅ `Asesoría fiscal para empresas y autónomos en Caldes de Montbui. IRPF, IVA y Sociedades con atención personalizada. Pide consulta gratis.`
> - 145 caracteres, keyword al principio, ubicación, CTA, beneficio claro.

> ❌ `Bienvenido a nuestra página web. Somos una empresa de asesoría. Ofrecemos servicios. Contacta con nosotros.`
> - Genérico, sin ubicación, sin diferenciador, sin CTA, sin keywords.

> ❌ `Asesoría fiscal, asesoría laboral, asesoría contable, asesoría mercantil, asesoría patrimonial, asesoría internacional, asesoría fiscal, asesoría laboral`
> - Keyword stuffing, ilegible, Google lo detectará.

> ❌ `AGC Assessors es una empresa fundada en el año 1995 dedicada a ofrecer servicios profesionales de asesoría y consultoría en el ámbito fiscal laboral y contable a empresas y particulares...`
> - Más de 160 caracteres, Google lo cortará, empieza con el nombre de marca.

---

## 5. URLs amigables

La URL de cada página es otro factor SEO. Una URL limpia, corta y con la palabra clave ayuda a Google a entender la página y mejora la confianza del usuario.

### Reglas fundamentales

1. **Minúsculas siempre** (`/serveis`, no `/Serveis`).
2. **Separar palabras con guiones** (`asesoria-fiscal`, no `asesoria_fiscal` ni `asesoriafiscal`).
3. **Sin acentos en la URL** (`asesoria-fiscal` en lugar de `asesoria-fiscàl`).
4. **Incluir la palabra clave** cuando sea natural.
5. **Sin parámetros** (`?id=12`, `?utm_source=...`).
6. **Sin fechas** en URLs de páginas permanentes (sí en noticias).
7. **Estructura jerárquica**: `/serveis/assessoria-fiscal` mejor que `/assessoria-fiscal`.

### Ejemplos para AGC

| Tipo | ✅ URL correcta | ❌ URL incorrecta |
|---|---|---|
| Home | `/` o `/es/` | `/index.php?id=1` |
| Servicio | `/serveis/assessoria-fiscal` | `/servicio.php?id=3` |
| Página "Quiénes somos" | `/sobre-nosotros` o `/ca/empresa` | `/quienes_somos.html` |
| Noticia | `/actualitat/novetats-fiscals-2026` | `/actualitat/noticia.php?id=89` |
| Oficina | `/oficinas/caldes-de-montbui` | `/oficinas/oficina-1` |
| Contacto | `/contacto` | `/contact.php` |
| Equipo | `/equipo` | `/team` (mejor en español) |

> **Nota técnica**: en AGC la estructura es multi-idioma con prefijos (`/ca/`, `/es/`, `/en/`). La home en catalán no lleva prefijo (`/serveis`), pero `/es/serveis` es la versión en español. Google lo entiende automáticamente mediante `hreflang`.

---

## 6. Imágenes — Todo lo que necesitas saber

Las imágenes son críticas para el SEO. Una imagen bien optimizada puede aparecer en **Google Imágenes** y traer tráfico adicional. Una imagen mal optimizada (demasiado pesada, sin alt) penaliza la velocidad de carga y el SEO.

### 6.1 Formatos de imagen recomendados

| Formato | Cuándo usarlo | Ventajas | Desventajas |
|---|---|---|---|
| **WebP** | **El recomendado por defecto** (fotos, ilustraciones, capturas) | 30% más ligero que JPG/PNG con misma calidad. Soportado por todos los navegadores modernos. | No se puede usar en programas antiguos |
| **JPG / JPEG** | Fotos sin transparencia | Universal, buen tamaño | Sin transparencia, calidad con compresión |
| **PNG** | Logos, iconos, imágenes con fondo transparente | Calidad perfecta, transparencia | Pesado, no usar para fotos |
| **SVG** | Logos, iconos vectoriales, ilustraciones simples | Escalable sin perder calidad, peso mínimo | No sirve para fotos |
| ❌ **GIF** | Evitar. Solo para animaciones muy puntuales | - | Pesado, animación innecesaria |
| ❌ **BMP, TIFF** | Nunca. | - | Obsoletos, enormes |

> **Regla práctica**: si dudas, sube la imagen como **WebP**. Es el formato moderno y el que Google recomienda.

### 6.2 Peso máximo por tipo de imagen

El peso de las imágenes afecta directamente a la **velocidad de carga** y al **Core Web Vitals** (LCP, CLS), que son factores de ranking desde 2021.

| Tipo de imagen | Peso máximo recomendado | Por qué |
|---|---|---|
| **Hero / Carrusel principal** | 200–300 KB | Es lo primero que ve el usuario, debe cargar rápido |
| **Imágenes de sección** | 100–150 KB | Equilibrio entre calidad y rendimiento |
| **Cards de servicios / blog** | 50–80 KB | Aparecen muchas en una página, suman peso |
| **Miniaturas / thumbnails** | 20–40 KB | Pequeñas pero críticas en listas |
| **Logos e iconos** | SVG (preferido) o PNG < 20 KB | Se repiten en cada página |
| **Favicon** | 5–10 KB | Aparece en pestaña y bookmarks |
| **Open Graph (redes sociales)** | < 200 KB | Para cuando se comparte la web |

> **Si una imagen pesa más de 300 KB, redúcela antes de subirla**. Usa herramientas como Squoosh (gratis) o TinyPNG.

### 6.3 Dimensiones recomendadas (ancho × alto)

Las dimensiones se miden en píxeles (px). Una imagen más grande de lo necesario **no se ve mejor**, solo pesa más.

| Ubicación | Dimensiones recomendadas | Proporción | Notas |
|---|---|---|---|
| **Hero / Banner principal** | 1920×1080 px o 1920×800 px | 16:9 o 21:9 | Primera imagen visible de la home |
| **Carrusel home** | 1920×800 px | 21:9 | Se ve completa en pantallas grandes |
| **Cards de servicios** | 800×600 px o 800×450 px | 4:3 o 16:9 | Una por cada servicio |
| **Cards de blog / noticias** | 1200×675 px | 16:9 | Estándar para OpenGraph también |
| **Imágenes de sección** | 1200×800 px | 3:2 | Flexible |
| **Foto de oficina** | 1200×800 px | 3:2 | Una por cada sede |
| **Foto de miembro del equipo** | 600×600 px o 800×1000 px | 1:1 (cuadrada) o 4:5 (retrato) | Para grids de equipo |
| **Logo principal** | SVG vectorial (preferido) o PNG 500×500 px | - | Aparece en header, footer, favicon |
| **Favicon** | 512×512 px (master), 32×32 (tab), 192×192 (Android) | 1:1 | Convertir a .ico para navegadores antiguos |
| **Imagen Open Graph** | 1200×630 px | 1.91:1 | Cuando se comparte en Facebook, LinkedIn, WhatsApp |
| **Imagen Twitter Card** | 1200×675 px o 1200×600 px | 2:1 o 2.4:1 | Para tweets con preview |

> **Importante**: AGC usa el formato `srcset` para servir imágenes responsivas. Esto significa que puedes subir la imagen grande (1920px) y el sistema genera versiones a 400px, 800px, 1200px, 1920px automáticamente. **Pero la imagen original debe ser la más grande** y en buena calidad.

### 6.4 Nombre del archivo

El nombre del archivo es un factor SEO sutil pero real. Google lo usa para entender qué muestra la imagen.

### ✅ Ejemplos correctos vs ❌ ejemplos incorrectos

> ✅ `asesoria-fiscal-caldes-de-montbui.webp`
> - Descriptivo, con keywords, en minúsculas, con guiones.

> ❌ `IMG_20240512_143022.jpg`
> - Nombre automático de cámara, no dice nada.

> ❌ `Captura de pantalla 2024-05-12 a las 14.32.45.png`
> - Demasiado largo, incluye fecha y hora.

> ❌ `foto-final-FINAL-2-ESTA-SI.webp`
> - Confuso, redundante, no descriptivo.

> ❌ `image1.jpg`
> - Genérico, sin valor SEO.

### Reglas para nombrar archivos

1. **Descriptivo y con keyword** si aplica.
2. **Minúsculas** siempre.
3. **Guiones** para separar palabras (nunca espacios, ni guiones bajos).
4. **Sin acentos** ni caracteres especiales (ñ → n, à → a).
5. **Sin números de versión** (`foto-final-2`, `foto-ok-ok`).
6. **Extensión al final** en minúsculas (`.webp`, no `.WEBP`).

### 6.5 Alt text (texto alternativo)

El alt text es el **campo más importante** del SEO de imágenes. Sirve para tres cosas:

1. **Accesibilidad**: los lectores de pantalla lo leen en voz alta para personas ciegas o con problemas de visión.
2. **SEO**: Google no puede "ver" las imágenes, lee el alt text para entender qué muestran.
3. **Contexto**: si la imagen no carga, el alt text aparece en su lugar, dando información.

### Reglas fundamentales

1. **Obligatorio en todas las imágenes** que aporten información.
2. **Imágenes decorativas** (líneas, fondos, separadores) → `alt=""` (vacío, indica a Google que las ignore).
3. **Longitud**: 5–15 palabras, máximo 125 caracteres.
4. **Describe lo que se ve**, no lo que es ("foto de un edificio" → sí; "edificio" → no).
5. **No empieces con "Imagen de..."** o "Foto de..." (redundante).
6. **Incluye la keyword** cuando sea natural y relevante.
7. **No hagas keyword stuffing** (`alt="asesoría asesoría fiscal asesoría"` → spam).
8. **Una keyword por imagen**, máximo dos.

### Ejemplos para AGC

| Imagen | ✅ Alt correcto | ❌ Alt incorrecto |
|---|---|---|
| Foto hero home (equipo trabajando) | `Equipo de AGC Assessors trabajando en la oficina de Caldes de Montbui` | `foto1` / `imagen` / `equipo` |
| Foto oficina Caldes | `Oficina de AGC Assessors en Caldes de Montbui` | `oficina` / `IMG_001` |
| Foto oficina Sant Celoni | `Fachada de la oficina de AGC Assessors en Sant Celoni` | `sant` / `foto oficina 2` |
| Foto equipo (retrato) | `María García, asesora fiscal senior de AGC Assessors` | `mujer` / `foto maria` |
| Icono asesoría fiscal | `Icono de calculadora representando asesoría fiscal` | `icono` / `assessoria fiscal` |
| Imagen de blog (reforma laboral) | `Reforma laboral 2026: análisis de los principales cambios` | `grafico` / `blog` / `reforma reforma` |
| Imagen decorativa (línea divisoria) | `alt=""` (vacío, no aporta) | (sin alt) o `línea` |
| Logo AGC | `Logo de AGC Assessors` o `alt=""` si es decorativa | `logo` / `AGC` |

> **Consejo**: antes de rellenar el alt, pregúntate: *"Si no pudiera ver la imagen, ¿qué necesitaría saber para entender el contenido de esta página?"*. Esa es la respuesta correcta.

### Cómo rellenar el alt en AGC

En el panel de administración, los campos de alt text están en:

- **HomeSectionResource** (Secciones de inicio): `Texto alternativo (alt)` o `settings.image_alt`.
- **NewsResource** (Noticias): el campo aparece al subir la imagen.
- **ServiceResource** (Servicios): al subir la imagen del servicio.
- **OfficeResource** (Oficinas): al subir la foto de la sede.
- **TeamMemberResource** (Equipo): al subir la foto del miembro.

> **Si no encuentras el campo al subir una imagen, pide al equipo de desarrollo que lo añada**. Es crítico para SEO y accesibilidad.

---

## 7. Encabezados (H1, H2, H3)

Los encabezados organizan el contenido de la página y ayudan a Google a entender la jerarquía y los temas tratados.

### Reglas fundamentales

1. **Un solo H1 por página**. Es el título principal. No usar H1 para imágenes, logos o elementos decorativos.
2. **H1 con la palabra clave principal** de la página.
3. **Jerarquía lógica**: H1 → H2 → H3. No saltar niveles (no usar H1 y luego H3 sin H2).
4. **Encabezados descriptivos**: que se entienda el tema solo leyéndolos.
5. **No usar encabezados solo para agrandar texto** (eso se hace con CSS).

### Ejemplo de estructura correcta para AGC

```html
<h1>Asesoría fiscal para empresas y autónomos</h1>  <!-- keyword principal -->
  <h2>¿Qué incluye nuestro servicio de asesoría fiscal?</h2>
    <h3>Declaración del IRPF</h3>
    <h3>IVA trimestral y anual</h3>
    <h3>Impuesto sobre Sociedades</h3>
  <h2>¿Por qué elegir AGC Assessors?</h2>
    <h3>Más de 25 años de experiencia</h3>
    <h3>Atención personalizada</h3>
```

### ❌ Errores comunes

- ❌ Varios H1 en la misma página.
- ❌ H1 que no contiene la palabra clave (`<h1>Bienvenidos</h1>` en una página de asesoría fiscal).
- ❌ Saltar de H1 a H3 sin H2 intermedio.
- ❌ Usar H1 para el logo de la empresa en cada página (debería ser `<div>` o `<a>`).
- ❌ Encabezados que no describen el contenido (`<h2>Más información</h2>`).

> **En AGC, el H1 de cada página se genera automáticamente desde el campo `title` del recurso**. Por eso es importante que el `title` contenga la palabra clave: se convierte en el H1.

---

## 8. Contenido y texto

Google premia el contenido útil, profundo y bien estructurado. Una página con poco texto compite peor que una con contenido completo.

### Reglas fundamentales

1. **Mínimo 300 palabras** por página (incluso las páginas de servicios).
2. **Artículos de blog**: 800–1500 palabras mínimo.
3. **Keyword en los primeros 100 palabras** del contenido.
4. **Variantes y sinónimos** de la keyword (no repetir la misma exacta 10 veces).
5. **Párrafos cortos** (3–4 líneas máximo). Mejor para lectura en pantalla.
6. **Negritas para destacar** puntos importantes, no para SEO.
7. **Listas y bullets** para contenido escaneable.
8. **Enlaces internos** a 2–3 páginas relacionadas.

### Ejemplo de keyword en los primeros 100 palabras

> ✅ *"La **asesoría fiscal para empresas en Caldes de Montbui** es un servicio esencial para autónomos, pymes y grandes empresas que necesitan gestionar correctamente sus obligaciones tributarias. En AGC Assessors ofrecemos un servicio integral que cubre desde la declaración del IRPF hasta la planificación fiscal estratégica."*

En los primeros 100 caracteres aparece la keyword principal. El resto del párrafo la refuerza con variantes.

### Errores comunes de contenido

- ❌ Páginas con menos de 200 palabras (thin content).
- ❌ Keyword stuffing (repetir "asesoría fiscal" 15 veces en un párrafo).
- ❌ Texto genérico copiado de otras webs.
- ❌ Contenido generado por IA sin revisión humana (Google lo detecta).
- ❌ Sin enlaces internos a otras páginas del sitio.
- ❌ Párrafos de 15 líneas sin estructura.

### Enlaces internos (linking interno)

Enlazar desde una página a otras relacionadas de AGC mejora el SEO y la experiencia del usuario.

**Ejemplo**: en la página de "Asesoría Fiscal", enlazar a:
- "Equipo" (mostrar quién hace el trabajo)
- "Contacto" (para pedir consulta)
- Un artículo de blog relevante ("Novedades fiscales 2026")

> Usa anchor text descriptivo: `consultar nuestro servicio de asesoría laboral` mejor que `haz clic aquí`.

---

## 9. SEO Local (esencial para AGC)

AGC es un **negocio local**. La mayoría de sus clientes potenciales buscan "asesoría fiscal cerca de mí" o "gestoría en Caldes de Montbui". El SEO local es lo que hace que aparezcáis en esas búsquedas y en Google Maps.

### 9.1 NAP (Name, Address, Phone)

**NAP = Nombre, Dirección, Teléfono.** Debe ser **idéntico** en todos los sitios donde aparezca AGC:

- Web de AGC
- Google Business Profile (Google Maps)
- Páginas de directorios (Páginas Amarillas, Yelp, etc.)
- Redes sociales (LinkedIn, Instagram, Facebook)
- Notas de prensa, artículos

### NAP oficial de AGC

> ⚠️ **Pide al equipo de desarrollo o marketing la lista oficial de NAP** y distribúyela literalmente. Cualquier variación (un acento, una abreviatura, una "C/" vs "Carrer") confunde a Google y debilita el SEO local.

**Ejemplo de inconsistencia peligrosa**:
- ❌ Web: "Av. Pi i Margall, 114, Caldes de Montbui"
- ❌ Google Maps: "Avinguda Pi i Margall 114, 08140 Caldes de Montbui"
- ❌ LinkedIn: "Avda. Pi y Margall, 114 - Caldes"

Las tres son la misma dirección, pero Google las trata como tres empresas distintas.

### 9.2 Google Business Profile (Google Maps)

Cada oficina de AGC debe tener su **ficha de Google Business Profile** verificada y optimizada:

- **Nombre**: AGC Assessors - Caldes de Montbui
- **Categoría principal**: Asesor fiscal
- **Categorías secundarias**: Gestoría, Asesor laboral, Contable
- **Dirección exacta** (idéntica a la web)
- **Teléfono** (con prefijo +34)
- **Horario** actualizado
- **Fotos** de la oficina, equipo, fachada
- **Reseñas**: pedir a clientes satisfechos que dejen reseña

### 9.3 Páginas individuales por oficina

**Recomendado**: cada oficina debe tener su **propia página en la web** con:

- URL dedicada: `/oficinas/caldes-de-montbui`
- Title SEO local: `Asesoría fiscal en Caldes de Montbui | AGC Assessors`
- Description con la ciudad: `Despacho de asesoría fiscal, laboral y contable en Caldes de Montbui. Más de 25 años de experiencia. Pide cita: +34 93 862 61 00.`
- Dirección, teléfono, email
- Mapa de Google embebido
- Horario de atención
- Foto de la oficina
- Texto único (no copy-paste de la página de Sant Celoni)

> **Es long-tail SEO local puro**: poca competencia, alta conversión, "asesoría fiscal en [pueblo]" es lo que busca la gente.

### 9.4 Schema markup (datos estructurados)

Son "etiquetas invisibles" en el código que Google usa para entender mejor tu negocio. Para AGC se recomienda implementar:

| Schema | Para qué sirve | Prioridad |
|---|---|---|
| **LocalBusiness** | Identifica la empresa, dirección, horarios, teléfono | Alta |
| **Organization** | Logo, redes sociales, datos de contacto | Alta |
| **BreadcrumbList** | Migas de pan visibles en Google | Media |
| **Article** | Para cada post del blog | Media |
| **FAQPage** | Para secciones de preguntas frecuentes | Baja |
| **Review** | Mostrar estrellas de reseñas en Google | Media |

> **No tienes que tocar esto como editor**. Es tarea del equipo de desarrollo. Pero es bueno que sepas que existe y que se lo pidas.

---

## 10. Checklist por página

Usa este checklist para cada nueva página o artículo que publiques:

### Antes de publicar

- [ ] **Title SEO** (en ca, es y en) — entre 50 y 60 caracteres, con keyword al principio, marca al final
- [ ] **Meta description** (en ca, es y en) — entre 150 y 160 caracteres, con CTA y ubicación si aplica
- [ ] **URL limpia** — en minúsculas, con guiones, con keyword, sin parámetros
- [ ] **H1 único** — coincide con la intención de la página, contiene keyword
- [ ] **Estructura de encabezados** — H1 > H2 > H3 sin saltar niveles
- [ ] **Contenido** — mínimo 300 palabras (800+ si es blog), keyword en los primeros 100 caracteres
- [ ] **Imágenes** — todas en WebP, peso correcto, dimensiones correctas
- [ ] **Alt text** — en todas las imágenes informativas, descriptivo y natural
- [ ] **Nombre de archivo** — descriptivo, con keyword, en minúsculas, con guiones
- [ ] **Enlaces internos** — al menos 2 enlaces a otras páginas relevantes de AGC
- [ ] **Canonical URL** — definido (lo hace el sistema automáticamente si está bien configurado)
- [ ] **Hreflang** — verificado para ca, es, en (lo hace el sistema, pero revisarlo)
- [ ] **Sin contenido duplicado** — comprobar que no existe otra página con el mismo title/description
- [ ] **Texto revisado** — sin errores ortográficos, sin keyword stuffing, sin contenido AI sin revisar

### Después de publicar

- [ ] **Comprobar en Google Search Console** que se ha indexado
- [ ] **Probar la URL** en Google: `site:agc.cat/es/[url-de-tu-pagina]`
- [ ] **Medir la velocidad** en PageSpeed Insights (mínimo 80/100 en mobile)
- [ ] **Probar en móvil** que se ve bien y se lee
- [ ] **Compartir en redes** con la imagen Open Graph correcta (1200×630)

---

## 11. Errores comunes que debes evitar

### Top 10 errores SEO más frecuentes

1. **Dejar los campos SEO vacíos**. Es el error más común y más grave. Google no sabe de qué trata la página.
2. **Title y description duplicados** en varias páginas. Google las considera contenido duplicado.
3. **Keyword stuffing**: repetir la misma palabra 20 veces en un párrafo. Google penaliza.
4. **Imágenes sin alt text** o con alt genérico (`alt="foto"`).
5. **Imágenes de 5 MB** que ralentizan toda la página.
6. **Nombres de archivo** automáticos de cámara (`IMG_20240512.jpg`).
7. **No traducir** los campos SEO a los tres idiomas (ca, es, en).
8. **Description que no es una description**: pegar el primer párrafo de la página.
9. **Title demasiado largo** (Google lo corta a los 60 caracteres y se ve mal).
10. **Olvidar el SEO local** en un negocio que solo opera en tres pueblos.

### Errores menos obvios pero igual de importantes

- ❌ Páginas con poco contenido (menos de 200 palabras).
- ❌ Múltiples H1 en la misma página.
- ❌ Imágenes decorativas con alt text (deberían tener `alt=""`).
- ❌ Redirigir páginas a URLs distintas de la canonical.
- ❌ Olvidar el sitemap.xml al añadir páginas nuevas.
- ❌ No enlazar internamente entre páginas.
- ❌ No tener `https` (la web debe ser siempre HTTPS).
- ❌ Cambiar la URL de una página sin redirigir la antigua (pierde todo el SEO acumulado).

---

## 12. Herramientas recomendadas

Todas son **gratuitas** (o tienen versión gratuita útil).

### Esenciales (úsalas a menudo)

| Herramienta | Para qué | URL |
|---|---|---|
| **Google Search Console** | Ver cómo Google ve tu web, errores, indexación, posicionamiento | https://search.google.com/search-console |
| **Google PageSpeed Insights** | Medir velocidad y Core Web Vitals | https://pagespeed.web.dev |
| **Google Rich Results Test** | Validar schema markup y rich snippets | https://search.google.com/test/rich-results |
| **Google Trends** | Ver el volumen de búsqueda de keywords | https://trends.google.com |
| **Bing Webmaster Tools** | SEO en Bing (5% del mercado, no lo ignores) | https://www.bing.com/webmasters |

### Para investigación de keywords

| Herramienta | Para qué | URL |
|---|---|---|
| **Ubersuggest** | Volumen de búsqueda, dificultad, ideas de keywords (3 búsquedas/día gratis) | https://neilpatel.com/ubersuggest |
| **AnswerThePublic** | Preguntas que hace la gente sobre una keyword | https://answerthepublic.com |
| **Google Keyword Planner** | Volumen oficial de Google (necesitas cuenta de Google Ads) | https://ads.google.com/keyword-planner |
| **Keyword Surfer** | Extensión de Chrome que muestra volumen en cada búsqueda de Google | https://surferseo.com |

### Para optimizar imágenes

| Herramienta | Para qué | URL |
|---|---|---|
| **Squoosh** | Convertir y comprimir imágenes (de Google, gratis, sin registro) | https://squoosh.app |
| **TinyPNG** | Comprimir PNG y JPG (gratis hasta cierto tamaño) | https://tinypng.com |
| **Canva** | Crear imágenes para redes y blog (versión gratis suficiente) | https://canva.com |
| **Photopea** | Editor de imágenes online gratuito tipo Photoshop | https://www.photopea.com |
| **RealFaviconGenerator** | Generar todos los tamaños de favicon desde una imagen | https://realfavicongenerator.net |

### Para validar y testear

| Herramienta | Para qué | URL |
|---|---|---|
| **Google Mobile-Friendly Test** | Verificar que la web se ve bien en móvil | https://search.google.com/test/mobile-friendly |
| **W3C Markup Validator** | Validar que el HTML no tiene errores | https://validator.w3.org |
| **GTmetrix** | Análisis completo de velocidad | https://gtmetrix.com |
| **WebPageTest** | Test de velocidad desde múltiples ubicaciones | https://www.webpagetest.org |

### Para SEO local

| Herramienta | Para qué | URL |
|---|---|---|
| **Google Business Profile** | Gestionar la ficha de Google Maps | https://business.google.com |
| **Moz Local** | Comprobar la consistencia del NAP en la web | https://moz.com/local |
| **BrightLocal** | Auditoría de SEO local | https://www.brightlocal.com |

---

## 13. Glosario rápido

| Término | Significado |
|---|---|
| **SEO** | Search Engine Optimization. Optimización para motores de búsqueda. |
| **SERP** | Search Engine Results Page. La página de resultados de Google. |
| **CTR** | Click-Through Rate. Porcentaje de personas que hacen clic en tu resultado. |
| **Title** | El título de la página que aparece en Google y en la pestaña del navegador. |
| **Meta description** | La descripción que aparece bajo el título en Google. |
| **H1, H2, H3** | Encabezados de la página (del más importante al menos). |
| **Alt text** | Texto alternativo que describe una imagen. |
| **Keyword** | Palabra clave por la que los usuarios buscan en Google. |
| **Keyword stuffing** | Uso excesivo y artificial de keywords (penalizado por Google). |
| **Backlink** | Enlace desde otra web hacia la tuya. Cuantos más (de calidad), mejor SEO. |
| **NAP** | Name, Address, Phone. Datos básicos del negocio local. |
| **Canonical URL** | La URL "oficial" de una página, para evitar contenido duplicado. |
| **Hreflang** | Etiqueta que indica a Google qué versión de idioma mostrar a cada usuario. |
| **Schema / Schema markup** | Código que ayuda a Google a entender el contenido de la página. |
| **Sitemap.xml** | Archivo que lista todas las páginas de tu web para Google. |
| **Robots.txt** | Archivo que indica a Google qué páginas puede rastrear y cuáles no. |
| **Core Web Vitals** | Métricas de Google sobre velocidad y experiencia de usuario. |
| **LCP** | Largest Contentful Paint. Cuánto tarda en cargarse el contenido principal. |
| **CLS** | Cumulative Layout Shift. Cuánto "salta" el contenido al cargar. |
| **INP** | Interaction to Next Paint. Rapidez de respuesta a interacciones. |
| **Open Graph** | Protocolo para definir cómo se ve la página al compartirla en redes. |
| **Long-tail keyword** | Keyword larga y específica, con menos competencia. |
| **Indexación** | Proceso por el que Google añade una página a su base de datos. |
| **Posicionamiento** | Posición que ocupa tu página en los resultados de Google. |
| **Contenido duplicado** | Cuando dos o más páginas tienen el mismo contenido. Penalizado. |
| **Anchor text** | El texto visible de un enlace. |
| **URL amigable** | URL corta, legible, con palabras clave. |
| **WebP** | Formato de imagen moderno, más ligero que JPG/PNG. |
| **Open Graph** | Imagen/texto que aparece al compartir en Facebook, LinkedIn, etc. |

---

## Apéndice: Ejemplo completo de página bien optimizada

### Página: "Asesoría Fiscal" en español

| Campo | Valor |
|---|---|
| **URL** | `/es/servicios/asesoria-fiscal` |
| **Title SEO (es)** | `Asesoría fiscal para empresas y autónomos \| AGC Assessors` |
| **Description SEO (es)** | `Gestionamos IRPF, IVA trimestral e Impuesto sobre Sociedades con atención personalizada. Más de 25 años de experiencia en Caldes de Montbui. Pide consulta.` |
| **H1** | `Asesoría fiscal para empresas y autónomos` |
| **H2** | `¿Qué incluye nuestro servicio?` |
| **H3** | `Declaración del IRPF e Impuesto sobre Sociedades` |
| **H3** | `IVA trimestral y anual` |
| **H3** | `Planificación fiscal estratégica` |
| **H2** | `¿Por qué elegir AGC Assessors?` |
| **Imagen hero** | `asesoria-fiscal-equipo-trabajando.webp` (1920×800, 250 KB) |
| **Alt text hero** | `Equipo de AGC Assessors realizando asesoría fiscal en Caldes de Montbui` |
| **Imagen card 1** | `declaracion-irpf-iva-sociedades.webp` (800×600, 100 KB) |
| **Alt text card 1** | `Especialistas en declaración del IRPF, IVA e Impuesto sobre Sociedades` |
| **Imagen card 2** | `planificacion-fiscal-estrategica.webp` (800×600, 100 KB) |
| **Alt text card 2** | `Planificación fiscal estratégica para empresas y autónomos` |
| **Enlaces internos** | `/es/equipo` (presentar al equipo) · `/es/contacto` (pedir cita) · `/es/blog/novetats-fiscals-2026` (artículo relacionado) |
| **CTA final** | Botón "Solicitar consulta" enlazando a `/es/contacto` |
| **Canonical** | `https://www.agc.cat/es/servicios/asesoria-fiscal` |
| **Hreflang** | `ca`, `es`, `en`, `x-default` (lo gestiona el sistema) |

### Misma página en catalán

| Campo | Valor |
|---|---|
| **URL** | `/serveis/assessoria-fiscal` |
| **Title SEO (ca)** | `Assessoria fiscal per a empreses i autònoms \| AGC Assessors` |
| **Description SEO (ca)** | `Gestionem IRPF, IVA trimestral i Impost sobre Societats amb atenció personalitzada. Més de 25 anys d'experiència a Caldes de Montbui. Demana consulta.` |
| **Title SEO (en)** | `Tax advisory for businesses and freelancers \| AGC Assessors` |
| **Description SEO (en)** | `We manage income tax, VAT and corporate tax with personalized attention. Over 25 years of experience in Caldes de Montbui. Book a consultation.` |

> **Nota**: cada idioma debe tener title y description únicos, no una traducción automática. Aprovecha para adaptar el tono y los matices a cada mercado.

---

## Recordatorio final

> El SEO no es un trabajo de una vez, es un proceso continuo. Cada página nueva, cada cambio, cada imagen subida es una oportunidad para mejorar o empeorar tu posicionamiento. Sigue esta guía, revisa el checklist antes de publicar, y monitoriza los resultados en Google Search Console.

**Última actualización**: junio 2026.
**Versión**: 1.0
**Mantenido por**: equipo de marketing AGC Assessors + equipo de desarrollo.
