🔵 1. Dựng container
docker compose up -d

🔵 2. Set MySQL function
docker exec -it magento_mysql mysql -uroot -proot123 \
  -e "SET GLOBAL log_bin_trust_function_creators = 1;"

🔵 3. Tạo auth.json (1 lệnh)
docker exec -it magento_php bash -c 'mkdir -p /root/.composer && cat > /root/.composer/auth.json << "EOF"
{
  "http-basic": {
    "repo.magento.com": {
      "username": "'$PUBLIC_KEY'",
      "password": "'$PRIVATE_KEY'"
    }
  }
}
EOF'

🔵 4. Create project
docker exec -it magento_php bash -c "
cd /var/www/html/magento && composer create-project --repository=https://repo.magento.com/ magento/project-community-edition .
"

🔵 5. Install Magento
docker exec -it magento_php php bin/magento setup:install \
  --base-url=http://localhost \
  --db-host=db \
  --db-name=magento \
  --db-user=magento \
  --db-password=magento123 \
  --backend-frontname=admin \
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

🔵 6. Install Sample Data (CHUẨN – KHÔNG LỖI)
docker exec -it magento_php bash -c "
cd /var/www/html/magento &&
php bin/magento sampledata:deploy &&
php bin/magento setup:upgrade
"
docker exec -it magento_php php bin/magento setup:upgrade #neu khong sampledata

🔵 7. Disable 2FA + set dev mode
docker exec -it magento_php php bin/magento module:disable Magento_TwoFactorAuth Magento_AdminAdobeImsTwoFactorAuth
docker exec -it magento_php php bin/magento deploy:mode:set developer
docker exec -it magento_php php bin/magento config:set dev/static/sign 0

❗ ❗ BẮT BUỘC THÊM 2 BƯỚC SAU (ĐỂ KHÔNG LỖI GIAO DIỆN)
🔵 8.1. Build static content

Nếu không chạy lệnh này sẽ bị 404 CSS/JS/images:

docker exec -it magento_php php bin/magento setup:static-content:deploy -f


Developer mode vẫn cần deploy lần đầu.

🔵 8.2. Reindex + flush cache
docker exec -it magento_php php bin/magento indexer:reindex
docker exec -it magento_php php bin/magento cache:flush

🔵 9. Fix Permissions

(đúng rồi)

docker exec -it magento_php bash -c "chmod -R 777 var pub/static pub/media generated"





1. tai lai sample 
docker exec -it magento_php bash -c '
cd /var/www/html/magento
composer config -g process-timeout 2000

echo "===== Bắt đầu tải Sample Data (tự retry) ====="

while true; do
    composer require magento/sample-data --ignore-platform-reqs -vvv && break
    echo "===== Lỗi mạng, sẽ thử lại sau 5 giây... ====="
    sleep 5
done

echo "===== Sample Data tải xong! ====="
'

2. xem tien trinh
docker exec -it magento_php composer install -vvv
