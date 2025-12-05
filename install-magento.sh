#!/bin/bash

# Script cài đặt Magento 2 sau khi đã chạy composer create-project

echo "=== Bước 1: Cài đặt Magento 2 ==="
docker exec -it magento_php php bin/magento setup:install \
  --base-url=http://localhost/ \
  --db-host=db \
  --db-name=magento \
  --db-user=magento \
  --db-password=magento123 \
  --admin-firstname=Admin \
  --admin-lastname=User \
  --admin-email=admin@example.com \
  --admin-user=admin \
  --admin-password=admin123 \
  --language=en_US \
  --currency=USD \
  --timezone=America/Chicago \
  --use-rewrites=1 \
  --search-engine=elasticsearch8 \
  --elasticsearch-host=elasticsearch \
  --elasticsearch-port=9200 \
  --elasticsearch-index-prefix=magento2 \
  --elasticsearch-timeout=15

echo ""
echo "=== Bước 2: Disable 2FA (Two Factor Authentication) cho môi trường dev ==="
docker exec -it magento_php php bin/magento module:disable Magento_AdminAdobeImsTwoFactorAuth Magento_TwoFactorAuth

echo ""
echo "=== Bước 3: Set permissions ==="
docker exec -it magento_php chmod -R 777 var pub/static pub/media generated

echo ""
echo "=== Bước 4: Deploy static content ==="
docker exec -it magento_php php bin/magento setup:static-content:deploy -f

echo ""
echo "=== Bước 5: Reindex và clear cache ==="
docker exec -it magento_php php bin/magento indexer:reindex
docker exec -it magento_php php bin/magento cache:flush

echo ""
echo "=== Bước 6: Set Developer Mode ==="
docker exec -it magento_php php bin/magento deploy:mode:set developer

echo ""
echo "========================================="
echo "✅ Cài đặt hoàn tất!"
echo "========================================="
echo "Frontend: http://localhost/"
echo "Admin: http://localhost/admin"
echo "Username: admin"
echo "Password: admin123"
echo "========================================="
