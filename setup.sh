#!/bin/bash

# Trade Track - Simple Setup Script
# Run this once to set up everything

set -e

echo "🚀 Trade Track Setup"
echo "===================="
echo ""

# Check PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP not found. Install PHP 8.4+ first."
    exit 1
fi

# Check Composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer not found. Install Composer first."
    exit 1
fi

echo "✅ Requirements OK"
echo ""

# Setup .env
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    echo "✅ .env created"
else
    echo "✅ .env exists"
fi

# Install dependencies
echo ""
echo "📦 Installing dependencies..."
composer install --optimize-autoloader --no-interaction

# Generate key
echo ""
echo "🔑 Generating app key..."
php artisan key:generate --force

# Setup database
echo ""
echo "📊 Setting up database..."
php artisan migrate:fresh --seed --force

# Cache
echo ""
echo "⚡ Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage
echo ""
echo "📁 Setting up storage..."
php artisan storage:link

# Permissions
echo ""
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo ""
echo "✅ Setup Complete!"
echo ""
echo "===================="
echo "🎯 Quick Start:"
echo "===================="
echo ""
echo "1. Start services:"
echo "   ./start-services.sh"
echo ""
echo "2. Import sample orders:"
echo "   php artisan orders:import file.csv"
echo ""
echo "3. View results:"
echo "   - App:     http://localhost:8000"
echo "   - Horizon: http://localhost:8000/horizon"
echo "   - Emails:  http://localhost:8025"
echo ""
echo "4. Generate KPI snapshot:"
echo "   php artisan kpi:snapshot"
echo ""
