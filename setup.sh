#!/bin/bash

# Inventory & Sales ERP Setup Script
# This script automates the complete setup process

echo "🚀 Starting Inventory & Sales ERP Setup..."
echo "============================================"

# Check if docker-compose is available
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo "❌ Docker Compose is not available. Please install Docker Compose first."
    exit 1
fi

# Step 1: Environment Setup
echo "📝 Step 1/8: Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ Environment file created"
else
    echo "✅ Environment file already exists"
fi

# Step 2: Start Docker Containers
echo "🐳 Step 2/8: Starting Docker containers..."
docker compose up -d
if [ $? -eq 0 ]; then
    echo "✅ Docker containers started successfully"
else
    echo "❌ Failed to start Docker containers"
    exit 1
fi

# Wait for containers to be ready
echo "⏳ Waiting for containers to be ready..."
sleep 10

# Step 3: Install PHP Dependencies
echo "📦 Step 3/8: Installing PHP dependencies..."
docker compose exec app composer install --no-interaction
if [ $? -eq 0 ]; then
    echo "✅ PHP dependencies installed"
else
    echo "❌ Failed to install PHP dependencies"
    exit 1
fi

# Step 4: Install Node Dependencies
echo "📦 Step 4/8: Installing Node dependencies..."
docker compose exec app npm install
if [ $? -eq 0 ]; then
    echo "✅ Node dependencies installed"
else
    echo "❌ Failed to install Node dependencies"
    exit 1
fi

# Step 5: Application Setup
echo "🔧 Step 5/8: Setting up Laravel application..."

# Generate application key
docker compose exec app php artisan key:generate --force
echo "✅ Application key generated"

# Publish Sanctum
docker compose exec app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --force
echo "✅ Sanctum published"

# Run migrations
docker compose exec app php artisan migrate --force
echo "✅ Database migrations completed"

# Create storage link
docker compose exec app php artisan storage:link
echo "✅ Storage link created"

# Step 6: Build Frontend Assets
echo "🎨 Step 6/8: Building frontend assets..."
docker compose exec app npm run build
if [ $? -eq 0 ]; then
    echo "✅ Frontend assets built successfully"
else
    echo "❌ Failed to build frontend assets"
    exit 1
fi

# Step 7: Seed Database (Optional)
echo "🌱 Step 7/8: Seeding database..."
read -p "Do you want to run the QuickSeeder? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    docker compose exec app php artisan db:seed --class=QuickSeeder --force
    echo "✅ Database seeded with test data"
else
    echo "⏭️  Database seeding skipped"
fi

# Step 8: Start Queue Worker (Optional)
echo "🔄 Step 8/8: Queue worker setup..."
read -p "Do you want to start the queue worker in the background? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    docker compose exec -d app php artisan queue:work
    echo "✅ Queue worker started in background"
else
    echo "⏭️  Queue worker setup skipped"
    echo "💡 You can start it later with: docker compose exec app php artisan queue:work"
fi

echo ""
echo "🎉 Setup completed successfully!"
echo "============================================"
echo "📊 Dashboard: http://localhost:8000"
echo "🔑 Test User: test@example.com / password123"
echo "🚀 API Base URL: http://localhost:8000/api/v1"
echo ""
echo "📝 Useful commands:"
echo "  • Stop containers: docker compose down"
echo "  • View logs: docker compose logs -f"
echo "  • Access container: docker compose exec app bash"
echo ""