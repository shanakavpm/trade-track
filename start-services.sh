#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}🚀 Starting Trade Track Services${NC}"
echo -e "${YELLOW}==================================${NC}"
echo ""

# Function to check if a service is running
check_service() {
    if [ "$1" = "redis" ]; then
        redis-cli ping > /dev/null 2>&1
    elif [ "$1" = "mailpit" ]; then
        curl -s http://localhost:8025 > /dev/null 2>&1
    elif [ "$1" = "horizon" ]; then
        pgrep -f "php artisan horizon" > /dev/null
    fi
    return $?
}

# Check and start Redis
echo -e "${YELLOW}📊 Checking Redis...${NC}"
if check_service "redis"; then
    echo -e "${GREEN}✅ Redis is running${NC}"
else
    echo "Starting Redis..."
    redis-server --daemonize yes
    sleep 2
    if check_service "redis"; then
        echo -e "${GREEN}✅ Redis started successfully${NC}"
    else
        echo -e "${RED}❌ Failed to start Redis. Please install it with:${NC}"
        echo "sudo apt update && sudo apt install -y redis-server"
        echo "sudo systemctl enable --now redis-server"
        exit 1
    fi
fi

# Check and start Mailpit
echo -e "\n${YELLOW}📧 Checking Mailpit...${NC}"
if check_service "mailpit"; then
    echo -e "${GREEN}✅ Mailpit is running${NC}"
else
    echo "Starting Mailpit..."
    
    # Check if mailpit binary exists
    if ! command -v mailpit > /dev/null 2>&1; then
        echo "Installing Mailpit..."
        wget -O /tmp/mailpit https://github.com/axllent/mailpit/releases/latest/download/mailpit-linux-amd64
        chmod +x /tmp/mailpit
        sudo mv /tmp/mailpit /usr/local/bin/
    fi
    
    # Start Mailpit
    nohup mailpit > storage/logs/mailpit.log 2>&1 &
    sleep 2
    
    if check_service "mailpit"; then
        echo -e "${GREEN}✅ Mailpit started successfully${NC}"
    else
        echo -e "${YELLOW}⚠️  Failed to start Mailpit. You can check logs at: storage/logs/mailpit.log${NC}"
    fi
fi

# Start Laravel Horizon
echo -e "\n${YELLOW}⚡ Starting Horizon...${NC}"

# Stop any existing Horizon instances
pkill -f "php artisan horizon" || true

# Clear the queue cache
php artisan queue:clear
php artisan queue:flush
php artisan queue:restart

# Start Horizon
nohup php artisan horizon > storage/logs/horizon.log 2>&1 &
sleep 3  # Give it time to start

if check_service "horizon"; then
    echo -e "${GREEN}✅ Horizon started successfully${NC}"
    echo -e "   Logs: tail -f storage/logs/horizon.log"
else
    echo -e "${RED}❌ Failed to start Horizon. Check logs:${NC} tail -f storage/logs/horizon.log"
    exit 1
fi

# Check if Laravel server is running
if ! pgrep -f "php artisan serve" > /dev/null; then
    echo -e "\n${YELLOW}🌐 Starting Laravel development server...${NC}"
    nohup php artisan serve --host=0.0.0.0 --port=8000 > storage/logs/server.log 2>&1 &
    sleep 2
    if pgrep -f "php artisan serve" > /dev/null; then
        echo -e "${GREEN}✅ Laravel server started on http://localhost:8000${NC}"
    else
        echo -e "${YELLOW}⚠️  Failed to start Laravel server. You can start it manually with:${NC}"
        echo "php artisan serve"
    fi
else
    echo -e "\n${GREEN}✅ Laravel server is already running at http://localhost:8000${NC}"
fi

# Show status
echo -e "\n${YELLOW}==================================${NC}"
echo -e "${GREEN}✅ All services started!${NC}"
echo -e "${YELLOW}==================================${NC}"

echo -e "\n${YELLOW}📱 Access Points:${NC}"
echo -e "  🌐 Application:  http://localhost:8000"
echo -e "  📊 Horizon:      http://localhost:8000/horizon"
echo -e "  📧 Mailpit:      http://localhost:8025"

echo -e "\n${YELLOW}📝 Next Steps:${NC}"
echo "  1. Import CSV:    php artisan orders:import file.csv"
echo "  2. Check Horizon: http://localhost:8000/horizon"
echo "  3. Check Emails:  http://localhost:8025"

echo -e "\n${YELLOW}📋 Useful Commands:${NC}"
echo "  View Horizon logs:  tail -f storage/logs/horizon.log"
echo "  View server logs:   tail -f storage/logs/laravel.log"
echo "  View Mailpit logs:  tail -f storage/logs/mailpit.log"
echo "  Stop all services:  pkill -f 'php artisan serve|horizon|mailpit|redis-server'"

echo -e "\n${YELLOW}🚀 Ready to import orders!${NC}"
echo -e "Run: ${GREEN}php artisan orders:import file.csv${NC}"
