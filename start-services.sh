#!/bin/bash

echo "🚀 Starting Trade Track Services"
echo "=================================="
echo ""

# Check if Redis is running
echo "📊 Checking Redis..."
if redis-cli ping > /dev/null 2>&1; then
    echo "✅ Redis is running"
else
    echo "❌ Redis is not running. Starting..."
    redis-server --daemonize yes
    sleep 2
    if redis-cli ping > /dev/null 2>&1; then
        echo "✅ Redis started successfully"
    else
        echo "❌ Failed to start Redis. Please start it manually: sudo systemctl start redis-server"
        exit 1
    fi
fi

echo ""
echo "📧 Checking Mailpit..."
if curl -s http://localhost:8025 > /dev/null 2>&1; then
    echo "✅ Mailpit is running"
else
    echo "❌ Mailpit is not running. Starting..."
    
    # Check if mailpit binary exists
    if command -v mailpit > /dev/null 2>&1; then
        nohup mailpit > /dev/null 2>&1 &
        sleep 2
        if curl -s http://localhost:8025 > /dev/null 2>&1; then
            echo "✅ Mailpit started successfully"
        else
            echo "❌ Failed to start Mailpit"
        fi
    else
        echo "❌ Mailpit not installed. Installing..."
        sudo wget -O /usr/local/bin/mailpit https://github.com/axllent/mailpit/releases/download/v1.20.5/mailpit-linux-amd64
        sudo chmod +x /usr/local/bin/mailpit
        nohup mailpit > /dev/null 2>&1 &
        sleep 2
        echo "✅ Mailpit installed and started"
    fi
fi

echo ""
echo "⚡ Starting Horizon..."
php artisan horizon:terminate > /dev/null 2>&1
nohup php artisan horizon > storage/logs/horizon.log 2>&1 &
sleep 2
echo "✅ Horizon started"

echo ""
echo "=================================="
echo "✅ All services started!"
echo "=================================="
echo ""
echo "📱 Access Points:"
echo "  🌐 Application:  http://localhost:8000 (run: php artisan serve)"
echo "  📊 Horizon:      http://localhost:8000/horizon"
echo "  📧 Mailpit:      http://localhost:8025"
echo ""
echo "📝 Next Steps:"
echo "  1. Start Laravel: php artisan serve"
echo "  2. Import CSV:    php artisan orders:import orders_sample.csv"
echo "  3. Check Horizon: http://localhost:8000/horizon"
echo "  4. Check Emails:  http://localhost:8025"
echo ""
