# Inventory & Sales Reporting System

A comprehensive ERP backend module built with Laravel 11 for managing food distribution inventory and sales data at scale. The system handles 15+ million records efficiently with optimized database queries, bulk import/export capabilities, and real-time reporting.

## Features

-   📊 **Dashboard** - Real-time sales metrics and low stock alerts
-   📈 **Advanced Reporting** - Top products, monthly sales, stock alerts, sales trends
-   📁 **Bulk Operations** - CSV import/export with queue processing
-   🔐 **API Access** - Token-based authentication for external integrations
-   ⚡ **Performance Optimized** - Handles 15+ million records efficiently
-   🐳 **Dockerized** - Complete Docker setup for easy deployment

## Tech Stack

-   **Backend**: Laravel 11, PHP 8.2
-   **Database**: MySQL 8.0
-   **Cache**: Redis
-   **Queue**: Laravel Jobs with database driver
-   **Frontend**: Blade templates with Tailwind CSS
-   **API**: Laravel Sanctum for authentication
-   **Container**: Docker & Docker Compose

## Prerequisites

-   Docker Desktop 4.0+
-   Docker Compose 2.0+
-   8GB RAM minimum
-   20GB free disk space

## Quick Installation

### 1. Clone the repository

```bash
git clone [repository-url]
cd inventory-sales-erp
```

### 2. Environment Setup

```bash
cp .env.example .env
```

### 3. Start Docker Containers

```bash
docker compose up -d
```

This will start:

-   PHP 8.2 application server
-   Nginx web server (port 8000)
-   MySQL 8.0 database (port 3306)
-   Redis cache server (port 6379)

### 4. Install Dependencies

```bash
# Install PHP dependencies
docker compose exec app composer install

# Install Node dependencies
docker compose exec app npm install

# Build frontend assets
docker compose exec app npm run dev
```

### 5. Application Setup

```bash
# Generate application key
docker compose exec app php artisan key:generate

# Run database migrations
docker compose exec app php artisan migrate

# Create storage link
docker compose exec app php artisan storage:link
```

### 6. Seed Database (Optional)

⚠️ **Warning**: Full seeding creates 15 million sales records and takes 25-30 minutes.

```bash
# Quick seed (for development - ~1000 records)
docker compose exec app php artisan db:seed --class=QuickSeeder

# Full seed (15 million records - takes 25-30 minutes)
docker compose exec app php artisan db:seed
```

### 7. Start Queue Worker (for import/export)

```bash
docker compose exec app php artisan queue:work
```

## API Usage

### Authentication

```bash
# Login to get token
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

### Making API Requests

```bash
# Use token for API requests
curl -X GET http://localhost:8000/api/v1/reports/top-products \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Available API Endpoints

#### Authentication

-   `POST /api/v1/register` - Register new user
-   `POST /api/v1/login` - Login user
-   `POST /api/v1/logout` - Logout user
-   `GET /api/v1/user` - Get authenticated user details

#### Reports

-   `GET /api/v1/reports/top-products` - Get top selling products
-   `GET /api/v1/reports/sales-summary` - Get sales summary
