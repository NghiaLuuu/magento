#!/bin/bash

echo "=== Cài đặt Magento 2 với Sample Data ==="

# Tạo database cho sampledata
docker exec -it magento_mysql mysql -uroot -proot123 -e "CREATE DATABASE IF NOT EXISTS magento_sampledata;"

# Composer create project
docker exec -it magento_php_sampledata composer create-project --repository=https://repo.magento.com/ magento/project-community-edition .

# Setup install
docker exec -it magento_php_sampledata php bin/magento setup:install \
  --base-url=http://localhost:8080/ \
  --db-host=db \
  --db-name=magento_sampledata \
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
  --elasticsearch-port=9200

# Deploy sample data
echo "=== Cài đặt Sample Data ==="
docker exec -it magento_php_sampledata php bin/magento sampledata:deploy
docker exec -it magento_php_sampledata php bin/magento setup:upgrade

# Disable 2FA
docker exec -it magento_php_sampledata php bin/magento module:disable Magento_AdminAdobeImsTwoFactorAuth Magento_TwoFactorAuth

# Set permissions
docker exec -it magento_php_sampledata bash -c "chown -R www-data:www-data /var/www/html/magento && find /var/www/html/magento -type d -exec chmod 755 {} \; && find /var/www/html/magento -type f -exec chmod 644 {} \; && chmod -R 777 /var/www/html/magento/var /var/www/html/magento/pub/static /var/www/html/magento/pub/media /var/www/html/magento/generated"
docker exec -it magento_php_sampledata chmod u+x bin/magento

# Compile
docker exec -it magento_php_sampledata php bin/magento setup:di:compile

# Deploy static content
docker exec -it magento_php_sampledata php bin/magento setup:static-content:deploy -f en_US

# Set developer mode
docker exec -it magento_php_sampledata php bin/magento deploy:mode:set developer

# Disable static signing
docker exec -it magento_php_sampledata php bin/magento config:set dev/static/sign 0

# Reindex và flush cache
docker exec -it magento_php_sampledata php bin/magento indexer:reindex
docker exec -it magento_php_sampledata php bin/magento cache:flush

echo ""
echo "========================================="
echo "✅ Cài đặt hoàn tất!"
echo "========================================="
echo "Frontend: http://localhost:8080/"
echo "Admin: http://localhost:8080/admin"
echo "Username: admin"
echo "Password: admin123"
echo "========================================="
