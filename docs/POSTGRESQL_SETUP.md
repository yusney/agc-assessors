# PostgreSQL Configuration for AGC Assessors

## Overview

This project uses an **external PostgreSQL 16 server**. The database container is intentionally excluded from the docker-compose to allow connection to your existing PostgreSQL Docker instance.

## Prerequisites

- PostgreSQL 16+ running (either as Docker container or native installation)
- Network connectivity between the PHP container and PostgreSQL server
- Database and user created with proper permissions

## Setup Instructions

### 1. Configure Environment Variables

Edit your `.env` file:

```env
DB_CONNECTION=pgsql
DB_HOST=localhost          # Or your PostgreSQL container name/IP
DB_PORT=5432
DB_DATABASE=agc
DB_USERNAME=agc
DB_PASSWORD=secret
DB_SSLMODE=prefer
```

### 2. Create Database and User

Connect to your PostgreSQL server and run:

```sql
-- Create database
CREATE DATABASE agc;

-- Create user
CREATE USER agc WITH PASSWORD 'secret';

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE agc TO agc;

-- Connect to database and grant schema privileges
\c agc
GRANT ALL ON SCHEMA public TO agc;
```

### 3. Network Configuration (Docker-to-Docker)

If your PostgreSQL is in another Docker container:

**Option A: Same Docker Network**
```bash
# Connect your postgres container to the agc-network
docker network connect agc-network <your-postgres-container>
```

Then set `DB_HOST=<your-postgres-container>` in `.env`

**Option B: Host Network (Linux)**
Set `DB_HOST=host.docker.internal` in `.env` (works on Docker Desktop for Mac/Windows)

**Option C: Docker IP**
Find the container IP:
```bash
docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' <your-postgres-container>
```

Then set `DB_HOST=<ip-address>` in `.env`

### 4. Test Connection

```bash
make shell
php artisan db:monitor
```

Or from the host:
```bash
psql -h localhost -U agc -d agc
```

## Docker Compose Override (Optional)

If you want to include PostgreSQL in this docker-compose for local development, create `docker-compose.override.yml`:

```yaml
services:
  database:
    image: postgres:16-alpine
    container_name: agc-postgres
    ports:
      - "5432:5432"
    environment:
      POSTGRES_DB: agc
      POSTGRES_USER: agc
      POSTGRES_PASSWORD: secret
    volumes:
      - postgres-data:/var/lib/postgresql/data
    networks:
      - agc-network

volumes:
  postgres-data:
```

## Troubleshooting

### Connection Refused
- Verify PostgreSQL is running: `docker ps | grep postgres`
- Check network connectivity: `docker compose exec php ping <DB_HOST>`
- Verify PostgreSQL accepts connections: check `pg_hba.conf`

### Authentication Failed
- Verify credentials in `.env` match PostgreSQL user
- Check PostgreSQL logs: `docker logs <your-postgres-container>`

### Database Does Not Exist
- Create the database manually (see step 2)
- Or run: `createdb -h <host> -U agc agc`

## Useful Commands

```bash
# Export database
pg_dump -h localhost -U agc agc > backup.sql

# Import database
psql -h localhost -U agc agc < backup.sql

# Connect to database
psql -h localhost -U agc agc
```
