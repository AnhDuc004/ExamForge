# ExamForge Backend

Backend API for ExamForge - Multi-tenant Online Examination Platform.

## Tech Stack

* Laravel 12
* PHP 8.3
* PostgreSQL 17
* Docker & Docker Compose
* PgAdmin

---

## Prerequisites

Before running the project, make sure you have installed:

* Git
* Docker Desktop
* Docker Compose

Verify installation:

```bash
docker --version
docker compose version
git --version
```

---

## Clone Repository

```bash
git clone https://github.com/AnhDuc004/BE-ExamForge.git
cd BE-ExamForge
```

---

## Environment Setup

Copy the environment file:

```bash
cp .env.example .env
```

If you are using Windows PowerShell:

```powershell
copy .env.example .env
```

---

## Configure Database

Update the following values in `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=examforge
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

---

## Start Docker Containers

Build and start all services:

```bash
docker compose up -d --build
```

Check running containers:

```bash
docker ps
```

Expected services:

* examforge-app
* examforge-postgres
* examforge-pgadmin

---

## Install Dependencies

Enter Laravel container:

```bash
docker exec app bash
```

Install packages:

```bash
composer install
```

Generate application key:

```bash
php artisan key:generate
```

Run database migrations:

```bash
php artisan migrate
```

(Optional)

```bash
php artisan db:seed
```

Exit container:

```bash
exit
```

---

## Access Application

Laravel API:

```text
http://localhost:8000
```

PgAdmin:

```text
http://localhost:5050
```

Login credentials:

```text
Email: admin@examforge.com
Password: admin123
```

PostgreSQL Connection:

```text
Host: postgres
Port: 5432
Database: examforge
Username: postgres
Password: postgres
```

---

## Useful Commands

Start containers:

```bash
docker compose up -d
```

Stop containers:

```bash
docker compose down
```

View logs:

```bash
docker compose logs -f
```

Access application container:

```bash
docker exec -it examforge-app bash
```

Run migrations:

```bash
php artisan migrate
```

Clear cache:

```bash
php artisan optimize:clear
```

---

## Project Structure

```text
app/
bootstrap/
config/
database/
routes/
storage/
tests/
Dockerfile
docker-compose.yml
```

---

## Development Workflow

1. Pull latest code

```bash
git pull origin main
```

2. Create feature branch

```bash
git checkout -b feature/feature-name
```

3. Commit changes

```bash
git add .
git commit -m "feat: description"
```

4. Push branch

```bash
git push origin feature/feature-name
```

5. Create Pull Request

---

## Team

ExamForge Development Team
