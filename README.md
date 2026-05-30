# AGC Assessors

Web corporativa multi-idioma (català / español / english) para AGC Assessors, firma d'assessoria fiscal, laboral i comptable amb més de 30 anys d'experiència.

- **Frontend públic**: pàgines informatives, serveis, actualitat, equip, oficines i formulari de contacte
- **Panell d'administració**: gestió completa de continguts, seccions de la home, menú de navegació, mitjans i configuració del footer

---

## Requisits

- [Docker](https://docs.docker.com/get-docker/) + [Docker Compose](https://docs.docker.com/compose/)
- [Git](https://git-scm.com/)
- (Opcional) PostgreSQL local o accés a una instància amb la xarxa `db_network`

---

## Instal·lació ràpida

### 1. Clonar el repositori

```bash
git clone <url-del-repo> agc
cd agc
```

### 2. Configurar variables d'entorn

```bash
cp .env.example .env
```

Edita `.env` i ajusta com a mínim:

| Variable | Valor recomanat |
|---|---|
| `APP_KEY` | Deixa-ho buit, es genera al pas 4 |
| `APP_URL` | `http://localhost:8080` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `postgres_local` (o el teu host de PostgreSQL) |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `agc` |
| `DB_USERNAME` | `postgres` |
| `DB_PASSWORD` | *(la teva contrasenya)* |
| `REDIS_HOST` | `redis` |

### 3. Aixecar els contenidors

```bash
docker compose up -d
```

Això inicia:
- **PHP 8.4 + Nginx** → `http://localhost:8080`
- **Redis** → cache, sessions i cues
- **Node 24** → Vite en mode dev amb Hot Module Replacement

### 4. Instal·lar dependències i preparar l'aplicació

```bash
# Dependències PHP
docker compose exec php composer install

# Clau d'aplicació
docker compose exec php php artisan key:generate

# Migracions i dades inicials
docker compose exec php php artisan migrate --seed

# Permisos d'emmagatzematge
docker run --rm -v "$(pwd):/work" -u root alpine sh -c "chown -R 33:33 /work/storage /work/bootstrap/cache"
```

### 5. Compilar assets (frontend)

```bash
# Opció A: via contenidor Node (recomanat)
docker compose run --rm node sh -c "npm install -g pnpm && pnpm install && pnpm build"

# Opció B: si tens pnpm instal·lat localment
pnpm install && pnpm build
```

---

## Accés a l'aplicació

| Servei | URL | Credencials |
|---|---|---|
| **Web pública** | http://localhost:8080 | — |
| **Panell admin (Filament)** | http://localhost:8080/admin | `admin@agcassessors.com` / `Admin*123` |
| **Mailpit (correu de dev)** | http://localhost:8025 | — |

---

## Comandes útils

```bash
# Entrar al contenidor PHP
docker compose exec php sh

# Artisan
docker compose exec php php artisan <comanda>

# Tests
docker compose exec php php artisan test

# Rebuild amb cache neta
docker compose exec php php artisan optimize:clear

# Frontend en mode desenvolupament
docker compose up node
```

---

## Estructura de l'arquitectura

El projecte segueix **Clean Architecture** amb namespaces separats:

```
src/
├── Domain/          # Entitats, Value Objects, interfaces de repositori (zero dependències de framework)
├── Application/     # Casos d'ús, depenen només de Domain
├── Infrastructure/  # Models Eloquent, repositoris, implementacions de serveis
└── Filament/        # Recursos del panell d'administració

app/                 # Controladors, middlewares, providers Laravel
resources/
├── views/           # Blades (frontend públic + admin)
├── css/             # Tailwind CSS 4 amb @theme
└── lang/            # Traduccions ca / es / en
```

---

## Stack tecnològic

- **Backend**: PHP 8.4, Laravel 13, PostgreSQL, Redis
- **Admin**: Filament 5 (panel de configuració visual)
- **Frontend**: Tailwind CSS 4, Alpine.js, Vite, pnpm
- **Infraestructura**: Docker Compose, Nginx, PHP-FPM
- **Qualitat**: `declare(strict_types=1)`, classes `final`, tests Pest/PHPUnit

---

## Notes

- **Multi-idioma**: El català és l'idioma per defecte sense prefix d'URL (`/`). Espanyol (`/es/`) i anglès (`/en/`) porten prefix.
- **Base de dades**: PostgreSQL s'espera a la xarxa Docker `db_network`. Si no la tens creada, aixeca un contenidor PostgreSQL a part amb aquesta xarxa.
- **Storage**: després de clonar sempre fixa permisos amb la comanda de `chown` del pas 4.
