#!/bin/bash

# Trade Track - Deployment Setup Script
# This script sets up the Laravel application for deployment

set -e

echo "🚀 Trade Track - Deployment Setup"
echo "===================================="
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.4+ first."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;" | cut -d. -f1,2)
if (( $(echo "$PHP_VERSION < 8.4" | bc -l) )); then
    echo "⚠️  Warning: PHP version $PHP_VERSION detected. PHP 8.4+ is recommended."
fi

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    exit 1
fi

echo "✅ PHP $PHP_VERSION and Composer are installed"
echo ""

# Copy .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    echo "✅ .env file created"
    echo "⚠️  Please configure your .env file with database and Redis credentials"
    echo ""
else
    echo "✅ .env file already exists"
fi

echo ""
echo "📦 Installing Composer dependencies..."
composer install --optimize-autoloader

echo ""
echo "🔑 Generating application key..."
php artisan key:generate

echo ""
echo "📊 Running database migrations..."
php artisan migrate:fresh --force

echo ""
echo "🌱 Seeding database..."
php artisan db:seed --force

echo ""
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "📁 Setting up storage..."
php artisan storage:link

echo ""
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo ""
echo "✅ Setup complete!"
echo ""
echo "===================================="
echo "🚀 Next Steps:"
echo "===================================="
echo "1. Configure your .env file:"
echo "   - Database credentials"
echo "   - Redis connection"
echo "   - Mail settings"
echo ""
echo "2. Start the application:"
echo "   php artisan serve"
echo ""
echo "3. Start Horizon (in another terminal):"
echo "   php artisan horizon"
echo ""
echo "4. Run tests:"
echo "   php artisan test"
echo ""
echo "===================================="
echo "📱 Application URLs (after starting):"
echo "===================================="
echo "🌐 Application:  http://localhost:8000"
echo "📊 Horizon:      http://localhost:8000/horizon"
echo ""
