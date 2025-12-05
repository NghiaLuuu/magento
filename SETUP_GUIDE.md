# Hướng Dẫn Cài Đặt Magento 2 với Docker

## Mục Lục
1. [Cấu Trúc Project](#cấu-trúc-project)
2. [File Cấu Hình](#file-cấu-hình)
3. [Các Bước Cài Đặt](#các-bước-cài-đặt)
4. [Xử Lý Lỗi Thường Gặp](#xử-lý-lỗi-thường-gặp)
5. [Cài Đặt Sample Data](#cài-đặt-sample-data)
6. [Chạy Nhiều Project Cùng Lúc](#chạy-nhiều-project-cùng-lúc)

---

## Cấu Trúc Project

```
magento-docker/
├── .env                          # File cấu hình biến môi trường (QUAN TRỌNG)
├── docker-compose.yml            # Cấu hình Docker services
├── Dockerfile-php                # Build PHP-FPM image
├── nginx/
│   └── default.conf             # Cấu hình Nginx
├── magento/                     # Source code Magento chính
├── magento_sampledata/          # Source code Magento với sample data (optional)
└── install-magento.sh           # Script tự động cài đặt
```

---

## File Cấu Hình

### 1. File `.env` - Cấu Hình Biến Môi Trường

File này chứa tất cả các biến có thể thay đổi khi tạo project mới:

```env
# Magento Composer Credentials (lấy từ https://marketplace.magento.com/)
PUBLIC_KEY=your_public_key_here
PRIVATE_KEY=your_private_key_here

# Project Configuration
PROJECT_NAME=magento                    # Thay đổi cho mỗi project
COMPOSE_PROJECT_NAME=magento-docker     # Prefix cho Docker resources

# Container Names
CONTAINER_PHP=${PROJECT_NAME}_php
CONTAINER_NGINX=${PROJECT_NAME}_nginx
CONTAINER_MYSQL=${PROJECT_NAME}_mysql
CONTAINER_ELASTICSEARCH=${PROJECT_NAME}_elasticsearch

# Ports (THAY ĐỔI NẾU CHẠY NHIỀU PROJECT)
PORT_WEB=80                            # Port web, đổi thành 81, 82... cho project khác
PORT_MYSQL=3307                        # Port MySQL, đổi thành 3308, 3309...
PORT_ELASTICSEARCH=9201                # Port Elasticsearch, đổi thành 9202, 9203...

# Database Configuration
MYSQL_ROOT_PASSWORD=root123
MYSQL_DATABASE=magento
MYSQL_USER=magento
MYSQL_PASSWORD=magento123

# Elasticsearch Configuration
ES_MEMORY=512m
CLUSTER_NAME=magento-cluster
NODE_NAME=magento-node

# Magento Admin Configuration
ADMIN_FIRSTNAME=Admin
ADMIN_LASTNAME=User
ADMIN_EMAIL=admin@example.com
ADMIN_USER=admin
ADMIN_PASSWORD=admin123
BASE_URL=http://localhost/             # Thay đổi theo PORT_WEB

# PHP Configuration
PHP_MEMORY_LIMIT=2G
```

### 2. File `docker-compose.yml`

Đã cấu hình sử dụng biến từ `.env`. Các service:
- **php**: PHP-FPM container chính
- **nginx**: Web server chính
- **db**: MySQL 8.0
- **elasticsearch**: Elasticsearch 8.10.0
- **php_sampledata** & **nginx_sampledata**: Cho sample data (optional)

### 3. File `Dockerfile-php`

Build PHP 8.2-FPM với các extension cần thiết cho Magento 2.

### 4. File `nginx/default.conf`

Cấu hình Nginx cho Magento 2. File này dùng chung cho tất cả project.

---

## Các Bước Cài Đặt

### Bước 1: Chuẩn Bị

```bash
# Clone hoặc tạo thư mục project
mkdir magento-docker && cd magento-docker

# Tạo file .env và cấu hình
cp .env.example .env
nano .env  # Điền PUBLIC_KEY và PRIVATE_KEY

# Tạo thư mục cho source code
mkdir magento
```

### Bước 2: Khởi Động Docker Containers

```bash
# Build và start tất cả containers
docker compose up -d

# Kiểm tra containers đang chạy
docker ps
```

**Lưu ý:** Nếu có lỗi port conflict, sửa PORT_WEB, PORT_MYSQL, PORT_ELASTICSEARCH trong `.env`

### Bước 3: Tạo Magento Authentication File

```bash
# Tạo file auth.json để tránh phải nhập credentials nhiều lần
docker exec -it magento_php mkdir -p /root/.composer

docker exec -it magento_php bash -c 'cat > /root/.composer/auth.json << EOF
{
    "http-basic": {
        "repo.magento.com": {
            "username": "YOUR_PUBLIC_KEY",
            "password": "YOUR_PRIVATE_KEY"
        }
    }
}
EOF'
```

### Bước 4: Cài Đặt Magento via Composer

```bash
# Chạy composer create-project (mất 5-10 phút)
docker exec -it magento_php composer create-project \
  --repository=https://repo.magento.com/ \
  magento/project-community-edition .
```

### Bước 5: Cấu Hình MySQL

```bash
# Set log_bin_trust_function_creators (quan trọng để tránh lỗi trigger)
docker exec -it magento_mysql mysql -uroot -proot123 \
  -e "SET GLOBAL log_bin_trust_function_creators = 1;"
```

### Bước 6: Chạy Magento Setup Install

```bash
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
  --elasticsearch-port=9200
```

**Lưu ý Admin URI:** Sau khi cài xong, lưu lại Admin URI (ví dụ: `/admin_abc123`)

### Bước 7: Tắt Two-Factor Authentication (2FA)

```bash
# Tắt 2FA cho môi trường development
docker exec -it magento_php php bin/magento module:disable \
  Magento_AdminAdobeImsTwoFactorAuth Magento_TwoFactorAuth
```

### Bước 8: Set Permissions

```bash
# Set owner và permissions cho các thư mục
docker exec -it magento_php bash -c "\
  chown -R www-data:www-data /var/www/html/magento && \
  find /var/www/html/magento -type d -exec chmod 755 {} \; && \
  find /var/www/html/magento -type f -exec chmod 644 {} \; && \
  chmod -R 777 /var/www/html/magento/var \
              /var/www/html/magento/pub/static \
              /var/www/html/magento/pub/media \
              /var/www/html/magento/generated && \
  chmod u+x bin/magento"
```

### Bước 9: Compile Code

```bash
# Compile dependency injection code (mất 2-3 phút)
docker exec -it magento_php php bin/magento setup:di:compile
```

### Bước 10: Deploy Static Content

```bash
# Deploy static files (CSS, JS, images)
docker exec -it magento_php php bin/magento setup:static-content:deploy -f en_US
```

### Bước 11: Set Developer Mode

```bash
# Chuyển sang developer mode
docker exec -it magento_php php bin/magento deploy:mode:set developer
```

### Bước 12: Tắt Static File Signing

```bash
# Tắt version trong URL static files
docker exec -it magento_php php bin/magento config:set dev/static/sign 0
```

### Bước 13: Set Permissions Lại Cho Static Files

```bash
# Đảm bảo static files có quyền ghi
docker exec -it magento_php chmod -R 777 \
  /var/www/html/magento/pub/static \
  /var/www/html/magento/var
```

### Bước 14: Reindex và Flush Cache

```bash
# Reindex tất cả indexers
docker exec -it magento_php php bin/magento indexer:reindex

# Flush cache
docker exec -it magento_php php bin/magento cache:flush
```

### Bước 15: Truy Cập Website

- **Frontend:** http://localhost/
- **Admin:** http://localhost/admin_abc123 (thay `admin_abc123` bằng URI thực tế)
- **Username:** admin
- **Password:** admin123

---

## Xử Lý Lỗi Thường Gặp

### 1. Lỗi: Port Already in Use

**Lỗi:**
```
Error: failed to bind host port 0.0.0.0:80/tcp: address already in use
```

**Giải pháp:**
```bash
# Kiểm tra process đang dùng port
sudo lsof -i :80
sudo lsof -i :3306
sudo lsof -i :9200

# Sửa port trong .env
PORT_WEB=81
PORT_MYSQL=3308
PORT_ELASTICSEARCH=9202

# Restart containers
docker compose down
docker compose up -d
```

### 2. Lỗi: MySQL Trigger Permission

**Lỗi:**
```
SQLSTATE[HY000]: General error: 1419 You do not have the SUPER privilege
```

**Giải pháp:**
```bash
docker exec -it magento_mysql mysql -uroot -proot123 \
  -e "SET GLOBAL log_bin_trust_function_creators = 1;"
```

### 3. Lỗi: Elasticsearch Not Running

**Lỗi:**
```
Could not validate a connection to Elasticsearch
```

**Giải pháp:**
```bash
# Kiểm tra Elasticsearch
docker ps | grep elastic

# Nếu không chạy, xem logs
docker logs magento_elasticsearch

# Restart Elasticsearch
docker compose restart elasticsearch

# Đợi 10-15 giây
sleep 15
docker ps | grep elastic
```

### 4. Lỗi: Missing CSS/Images (404 on Static Files)

**Lỗi:**
```
404 Not Found: /static/version123/frontend/Magento/luma/en_US/css/styles-m.css
```

**Giải pháp:**
```bash
# 1. Set permissions
docker exec -it magento_php bash -c "\
  chown -R www-data:www-data /var/www/html/magento && \
  chmod -R 777 /var/www/html/magento/var \
              /var/www/html/magento/pub/static \
              /var/www/html/magento/pub/media \
              /var/www/html/magento/generated"

# 2. Compile code
docker exec -it magento_php php bin/magento setup:di:compile

# 3. Deploy static content
docker exec -it magento_php rm -rf /var/www/html/magento/pub/static/frontend \
                                    /var/www/html/magento/pub/static/adminhtml \
                                    /var/www/html/magento/var/view_preprocessed \
                                    /var/www/html/magento/var/cache

docker exec -it magento_php php bin/magento setup:static-content:deploy -f en_US

# 4. Tắt static signing
docker exec -it magento_php php bin/magento config:set dev/static/sign 0

# 5. Set permissions
docker exec -it magento_php chmod -R 777 \
  /var/www/html/magento/pub/static \
  /var/www/html/magento/var

# 6. Flush cache
docker exec -it magento_php php bin/magento cache:flush
```

### 5. Lỗi: Class Interceptor Does Not Exist

**Lỗi:**
```
ReflectionException: Class "Magento\Framework\App\Http\Interceptor" does not exist
```

**Giải pháp:**
```bash
# Compile code
docker exec -it magento_php php bin/magento setup:di:compile

# Flush cache
docker exec -it magento_php php bin/magento cache:flush
```

### 6. Lỗi: Cache Directory Not Writable

**Lỗi:**
```
cache_dir "/var/www/html/magento/var/cache/" is not writable
```

**Giải pháp:**
```bash
docker exec -it magento_php bash -c "\
  chown -R www-data:www-data /var/www/html/magento && \
  chmod -R 777 /var/www/html/magento/var"
```

---

## Cài Đặt Sample Data

Sample data bao gồm sản phẩm mẫu, categories, CMS pages giúp demo website.

### Bước 1: Tăng Composer Timeout

```bash
# Tăng timeout vì file sample data rất lớn (80MB+)
docker exec -it magento_php composer config --global process-timeout 2000
```

### Bước 2: Deploy Sample Data

**⚠️ LƯU Ý:** File `magento-sample-data-media` rất lớn (80MB) và thường bị timeout khi download.

**Cách 1: Bỏ qua Media (Khuyến nghị - nhanh hơn)**

```bash
# Thêm sample data modules (không có media/images)
docker exec -it magento_php bash -c "cd /var/www/html/magento && composer require --no-update \
  magento/module-bundle-sample-data \
  magento/module-theme-sample-data \
  magento/module-wishlist-sample-data \
  magento/module-sales-sample-data \
  magento/module-tax-sample-data \
  magento/module-catalog-sample-data \
  magento/module-widget-sample-data \
  magento/module-cms-sample-data \
  magento/module-customer-sample-data \
  magento/module-downloadable-sample-data \
  magento/module-review-sample-data \
  magento/module-catalog-rule-sample-data \
  magento/module-configurable-sample-data \
  magento/module-product-links-sample-data \
  magento/module-sales-rule-sample-data \
  magento/module-msrp-sample-data \
  magento/module-grouped-product-sample-data \
  magento/module-swatches-sample-data \
  magento/module-offline-shipping-sample-data"

# Update composer
docker exec -it magento_php composer update
```

**Cách 2: Deploy đầy đủ (bao gồm media - file 80MB)**

```bash
# Set timeout = 0 (không giới hạn)
docker exec -it magento_php composer config --global process-timeout 0

# Cài sample data bằng composer require với --ignore-platform-reqs
docker exec -it magento_php bash -c "cd /var/www/html/magento && \
  COMPOSER_PROCESS_TIMEOUT=0 composer require \
  magento/module-bundle-sample-data \
  magento/module-catalog-sample-data \
  magento/module-sales-sample-data \
  magento/module-customer-sample-data \
  magento/module-cms-sample-data \
  magento/module-widget-sample-data \
  magento/module-theme-sample-data \
  magento/module-downloadable-sample-data \
  magento/module-wishlist-sample-data \
  magento/module-review-sample-data \
  magento/module-tax-sample-data \
  magento/module-catalog-rule-sample-data \
  magento/module-configurable-sample-data \
  magento/module-product-links-sample-data \
  magento/module-sales-rule-sample-data \
  magento/module-msrp-sample-data \
  magento/module-grouped-product-sample-data \
  magento/module-swatches-sample-data \
  magento/module-offline-shipping-sample-data \
  magento/sample-data-media \
  --ignore-platform-reqs"

# Download file media sẽ mất 10-30 phút tùy tốc độ mạng
# Kiểm tra tiến trình: docker exec -it magento_php ls -lh vendor/magento/sample-data-media/
```

### Bước 3: Upgrade Database

```bash
# Chạy upgrade để cài sample data vào database
docker exec -it magento_php php bin/magento setup:upgrade
```

### Bước 4: Compile và Deploy

```bash
# Compile code
docker exec -it magento_php php bin/magento setup:di:compile

# Deploy static content
docker exec -it magento_php php bin/magento setup:static-content:deploy -f en_US

# Reindex
docker exec -it magento_php php bin/magento indexer:reindex

# Flush cache
docker exec -it magento_php php bin/magento cache:flush
```

**Xử lý lỗi timeout khi download sample data:**

```
curl error 28 while downloading https://repo.magento.com/archives/magento/sample-data-media/
magento-sample-data-media-100.4.0.0.zip: Operation timed out after 300001 milliseconds
```

**Giải pháp:**

**⭐ KHUYẾN NGHỊ - Chạy lại nhiều lần với resume:**

File `magento-sample-data-media-100.4.0.0.zip` có 80MB. Mỗi lần timeout (300 giây) download được ~40-50MB.

```bash
# Bước 1: Set timeout = 0 (không giới hạn)
docker exec -it magento_php composer config --global process-timeout 0
docker exec -it magento_php composer config process-timeout 0

# Bước 2: Chạy lệnh này 2-3 lần (mỗi lần timeout sẽ resume)
docker exec -it magento_php bash -c "cd /var/www/html/magento && composer install --ignore-platform-reqs"

# Lần 1: Download 0 → 47MB (timeout)
# Lần 2: Resume 47MB → 80MB (hoàn thành!)
```

**Cách tự động retry:**
```bash
# Loop tự động cho đến khi download xong
docker exec -it magento_php bash -c "
cd /var/www/html/magento
while ! composer install --ignore-platform-reqs 2>&1 | grep -q 'Generating autoload'; do
    echo '===== Timeout, retrying in 5s... ====='
    sleep 5
done
echo '===== Download completed! ====='
"
```

**Các cách khác (ít hiệu quả hơn):**

1. **Bỏ qua media files** (sản phẩm có nhưng không có ảnh):
   ```bash
   docker exec -it magento_php composer remove magento/sample-data-media --no-update
   docker exec -it magento_php composer update
   ```

2. **Download thủ công** (phức tạp):
   - Download file từ https://repo.magento.com/ về máy local
   - Copy vào container và giải nén vào `pub/media/`

---

## Chạy Nhiều Project Cùng Lúc

### Ví dụ: Tạo Project Thứ 2

#### 1. Copy thư mục project
```bash
cp -r magento-docker magento-project2
cd magento-project2
```

#### 2. Sửa file `.env`
```env
# Project Configuration
PROJECT_NAME=magento2
COMPOSE_PROJECT_NAME=magento2-docker

# Ports - PHẢI KHÁC PROJECT 1
PORT_WEB=81              # Project 1 dùng 80
PORT_MYSQL=3308          # Project 1 dùng 3307
PORT_ELASTICSEARCH=9202  # Project 1 dùng 9201

# Database - Đặt tên khác
MYSQL_DATABASE=magento2

# Admin - Thay đổi nếu muốn
ADMIN_USER=admin2
ADMIN_PASSWORD=admin123

# Base URL - Theo port mới
BASE_URL=http://localhost:81/
```

#### 3. Tạo thư mục source
```bash
mkdir magento
```

#### 4. Start containers
```bash
docker compose up -d
```

#### 5. Cài đặt Magento
Làm theo các bước từ 3-14 ở trên, nhớ thay đổi:
- Container names: `magento2_php` thay vì `magento_php`
- Database name: `magento2`
- Base URL: `http://localhost:81/`

### Kiểm Tra Nhiều Project

```bash
# Xem tất cả containers
docker ps

# Bạn sẽ thấy:
# - magento_php, magento_nginx (project 1)
# - magento2_php, magento2_nginx (project 2)
```

**Truy cập:**
- Project 1: http://localhost/
- Project 2: http://localhost:81/

---

## Khởi Động Lại Sau Khi Restart Máy Tính

Sau khi cài đặt Magento xong 1 lần, mỗi lần restart máy tính chỉ cần:

```bash
# Di chuyển vào thư mục project
cd /home/nghialuu/magento-docker

# Khởi động tất cả containers
docker compose up -d

# Đợi 10-20 giây để containers khởi động
sleep 15

# Kiểm tra containers đang chạy
docker ps
```

**Truy cập website:**
- Frontend chính: http://localhost/
- Frontend sample data: http://localhost:8080/
- Admin: http://localhost/admin (hoặc URI được cung cấp khi cài đặt)

**KHÔNG cần làm lại:**
- ❌ Composer install
- ❌ Setup install  
- ❌ Compile code
- ❌ Deploy static content
- ❌ Set permissions

**Lý do:** 
- Source code lưu trong `magento/` và `magento_sampledata/` trên host
- Database lưu trong Docker volume `db_data`
- Tất cả config đã có sẵn

---

## Các Lệnh Hữu Ích

### Docker Commands

```bash
# Xem tất cả containers
docker ps -a

# Xem logs container
docker logs -f magento_php
docker logs magento_mysql --tail 50

# Vào terminal container
docker exec -it magento_php bash

# Stop tất cả containers
docker compose down

# Start lại
docker compose up -d

# Rebuild containers
docker compose build --no-cache
docker compose up -d
```

### Magento Commands

```bash
# Cache management
docker exec -it magento_php php bin/magento cache:clean
docker exec -it magento_php php bin/magento cache:flush
docker exec -it magento_php php bin/magento cache:status

# Reindex
docker exec -it magento_php php bin/magento indexer:reindex
docker exec -it magento_php php bin/magento indexer:status

# Deploy mode
docker exec -it magento_php php bin/magento deploy:mode:show
docker exec -it magento_php php bin/magento deploy:mode:set developer
docker exec -it magento_php php bin/magento deploy:mode:set production

# Module management
docker exec -it magento_php php bin/magento module:status
docker exec -it magento_php php bin/magento module:enable Vendor_Module
docker exec -it magento_php php bin/magento module:disable Vendor_Module

# Admin URI
docker exec -it magento_php php bin/magento info:adminuri

# Create admin user
docker exec -it magento_php php bin/magento admin:user:create \
  --admin-user=newadmin \
  --admin-password=Admin123 \
  --admin-email=admin@example.com \
  --admin-firstname=Admin \
  --admin-lastname=User

# Upgrade
docker exec -it magento_php php bin/magento setup:upgrade

# Compile
docker exec -it magento_php php bin/magento setup:di:compile

# Static content deploy
docker exec -it magento_php php bin/magento setup:static-content:deploy -f en_US
```

### MySQL Commands

```bash
# Vào MySQL CLI
docker exec -it magento_mysql mysql -uroot -proot123

# Tạo database mới
docker exec -it magento_mysql mysql -uroot -proot123 \
  -e "CREATE DATABASE magento2; GRANT ALL ON magento2.* TO 'magento'@'%';"

# Backup database
docker exec magento_mysql mysqldump -uroot -proot123 magento > backup.sql

# Restore database
docker exec -i magento_mysql mysql -uroot -proot123 magento < backup.sql
```

---

## Troubleshooting

### Container không start

```bash
# Xem lỗi
docker compose logs

# Xem logs của container cụ thể
docker logs magento_php
docker logs magento_mysql
docker logs magento_elasticsearch

# Remove và tạo lại
docker compose down -v
docker compose up -d
```

### Magento chạy chậm

```bash
# Tăng PHP memory limit trong .env
PHP_MEMORY_LIMIT=4G

# Rebuild containers
docker compose build --no-cache php
docker compose up -d

# Enable production mode
docker exec -it magento_php php bin/magento deploy:mode:set production
```

### Xóa tất cả và cài lại

```bash
# Stop và xóa containers
docker compose down -v

# Xóa source code
rm -rf magento/*

# Xóa docker volumes (optional, xóa cả database)
docker volume prune

# Start lại từ đầu
docker compose up -d
```

---

## Tóm Tắt Script Tự Động

Tạo file `install.sh`:

```bash
#!/bin/bash

# Script tự động cài đặt Magento 2

set -e  # Exit on error

echo "=== Bước 1: Khởi động containers ==="
docker compose up -d
sleep 10

echo "=== Bước 2: Tạo auth.json ==="
docker exec -it ${CONTAINER_PHP:-magento_php} mkdir -p /root/.composer
docker exec -it ${CONTAINER_PHP:-magento_php} bash -c "cat > /root/.composer/auth.json << EOF
{
    \"http-basic\": {
        \"repo.magento.com\": {
            \"username\": \"${PUBLIC_KEY}\",
            \"password\": \"${PRIVATE_KEY}\"
        }
    }
}
EOF"

echo "=== Bước 3: Composer create-project ==="
docker exec -it ${CONTAINER_PHP:-magento_php} composer create-project \
  --repository=https://repo.magento.com/ \
  magento/project-community-edition .

echo "=== Bước 4: Cấu hình MySQL ==="
docker exec -it ${CONTAINER_MYSQL:-magento_mysql} mysql -uroot -p${MYSQL_ROOT_PASSWORD} \
  -e "SET GLOBAL log_bin_trust_function_creators = 1;"

echo "=== Bước 5: Setup install ==="
docker exec -it ${CONTAINER_PHP:-magento_php} php bin/magento setup:install \
  --base-url=${BASE_URL} \
  --db-host=db \
  --db-name=${MYSQL_DATABASE} \
  --db-user=${MYSQL_USER} \
  --db-password=${MYSQL_PASSWORD} \
  --admin-firstname=${ADMIN_FIRSTNAME} \
  --admin-lastname=${ADMIN_LASTNAME} \
  --admin-email=${ADMIN_EMAIL} \
  --admin-user=${ADMIN_USER} \
  --admin-password=${ADMIN_PASSWORD} \
  --language=en_US \
  --currency=USD \
  --timezone=America/Chicago \
  --use-rewrites=1 \
  --search-engine=elasticsearch8 \
  --elasticsearch-host=elasticsearch \
  --elasticsearch-port=9200

echo "=== Bước 6: Tắt 2FA ==="
docker exec -it ${CONTAINER_PHP:-magento_php} php bin/magento module:disable \
  Magento_AdminAdobeImsTwoFactorAuth Magento_TwoFactorAuth

echo "=== Bước 7: Set permissions ==="
docker exec -it ${CONTAINER_PHP:-magento_php} bash -c "\
  chown -R www-data:www-data /var/www/html/magento && \
  find /var/www/html/magento -type d -exec chmod 755 {} \; && \
  find /var/www/html/magento -type f -exec chmod 644 {} \; && \
  chmod -R 777 /var/www/html/magento/var \
              /var/www/html/magento/pub/static \
              /var/www/html/magento/pub/media \
              /var/www/html/magento/generated && \
  chmod u+x bin/magento"

echo "=== Bước 8: Compile ==="
docker exec -it ${CONTAINER_PHP:-magento_php} php bin/magento setup:di:compile

echo "=== Bước 9: Deploy static content ==="
docker exec -it ${CONTAINER_PHP:-magento_php} php bin/magento setup:static-content:deploy -f en_US

echo "=== Bước 10: Developer mode ==="
docker exec -it ${CONTAINER_PHP:-magento_php} php bin/magento deploy:mode:set developer

echo "=== Bước 11: Disable static signing ==="
docker exec -it ${CONTAINER_PHP:-magento_php} php bin/magento config:set dev/static/sign 0

echo "=== Bước 12: Set permissions ==="
docker exec -it ${CONTAINER_PHP:-magento_php} chmod -R 777 \
  /var/www/html/magento/pub/static \
  /var/www/html/magento/var

echo "=== Bước 13: Reindex và flush cache ==="
docker exec -it ${CONTAINER_PHP:-magento_php} php bin/magento indexer:reindex
docker exec -it ${CONTAINER_PHP:-magento_php} php bin/magento cache:flush

echo ""
echo "========================================="
echo "✅ Cài đặt hoàn tất!"
echo "========================================="
echo "Frontend: ${BASE_URL}"
echo "Admin URI: Chạy lệnh để xem:"
echo "  docker exec -it ${CONTAINER_PHP:-magento_php} php bin/magento info:adminuri"
echo "Username: ${ADMIN_USER}"
echo "Password: ${ADMIN_PASSWORD}"
echo "========================================="
```

**Chạy script:**
```bash
chmod +x install.sh
./install.sh
```

---

**Lưu ý cuối cùng:**
- Luôn kiểm tra `.env` trước khi start project mới
- Backup database thường xuyên
- Sử dụng developer mode khi development
- Chuyển sang production mode khi deploy lên server thật

**Tài liệu tham khảo:**
- [Magento DevDocs](https://devdocs.magento.com/)
- [Docker Documentation](https://docs.docker.com/)
- [Composer](https://getcomposer.org/)
