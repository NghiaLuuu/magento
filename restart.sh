#!/bin/bash
cd /home/nghialuu/magento-docker

echo "🔵 Tắt nginx host (nếu đang chạy)..."
sudo systemctl stop nginx 2>/dev/null

echo "🔵 Khởi động Docker containers..."
docker compose up -d

echo "🔵 Đợi containers khởi động..."
sleep 3

echo "🔵 Kiểm tra containers..."
docker ps --filter "name=magento" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

echo ""
echo "🔵 Khởi động cron service..."
docker exec magento_php service cron start

echo ""
echo "✅ Magento đã sẵn sàng!"
echo "   Frontend: http://localhost/"
echo "   Admin: http://localhost/admin (admin/admin123)"
echo ""
echo "⚠️  Lưu ý: Production mode không cần deploy static content sau restart"
