# ✅ HƯỚNG DẪN CÀI MAGENTO 2.4.x (KHÔNG CÓ SAMPLE DATA)

> Magento sạch, không có sản phẩm mẫu - tự thêm sản phẩm trong Admin
> Chạy song song với project Magento có data hiện tại

---

## 📋 YÊU CẦU TRƯỚC KHI BẮT ĐẦU:
- Docker và Docker Compose đã cài đặt
- Tắt nginx host: `sudo systemctl stop nginx`
- Đã có Composer keys (PUBLIC_KEY, PRIVATE_KEY) từ https://marketplace.magento.com/

---

# 🔥 PHẦN 1: TẠO PROJECT MỚI CHẠY SONG SONG

## 🔵 BƯỚC 0.1: Copy folder project

```bash
cp -r /home/nghialuu/magento-docker /home/nghialuu/magento-nodata
cd /home/nghialuu/magento-nodata
```

## 🔵 BƯỚC 0.2: Xóa folder code cũ

```bash
rm -rf magento/*
```

## 🔵 BƯỚC 0.3: Sửa file `.env` - ĐỔI CÁC GIÁ TRỊ SAU:

```bash
# Project Configuration
PROJECT_NAME=nodata
COMPOSE_PROJECT_NAME=nodata-docker

# Ports (PHẢI KHÁC project cũ - magento đang dùng 80, 3307, 9201)
PORT_WEB=8080
PORT_MYSQL=3308
PORT_ELASTICSEARCH=9202
```

> **Lưu ý:** Các biến khác như `MYSQL_DATABASE`, `MYSQL_USER`, `CONTAINER_*` sẽ TỰ ĐỘNG theo `PROJECT_NAME`

## 🔵 BƯỚC 0.4: Tạo folder cho project mới

```bash
mkdir -p nodata
```

---

# 🔥 PHẦN 2: CÀI ĐẶT MAGENTO

## 🔵 BƯỚC 1: Dựng container

```bash
docker compose up -d
```

---

## 🔵 BƯỚC 2: Fix MySQL function

```bash
docker exec -it nodata_mysql mysql -uroot -proot123 \
  -e "SET GLOBAL log_bin_trust_function_creators = 1;"
```

---

## 🔵 BƯỚC 3: Tạo auth.json cho Composer

```bash
docker exec -it nodata_php bash -c 'mkdir -p /root/.composer && cat > /root/.composer/auth.json << "EOF"
{
  "http-basic": {
    "repo.magento.com": {
      "username": "'$PUBLIC_KEY'",
      "password": "'$PRIVATE_KEY'"
    }
  }
}
EOF'
```

---

## 🔵 BƯỚC 4: Create Magento project

```bash
docker exec -it nodata_php bash -c "
cd /var/www/html/nodata && composer create-project --repository=https://repo.magento.com/ magento/project-community-edition .
"
```

---

## 🔵 BƯỚC 5: Install Magento

```bash
docker exec -it nodata_php php bin/magento setup:install \
  --base-url=http://localhost:8080 \
  --db-host=db \
  --db-name=nodata \
  --db-user=nodata \
  --db-password=nodata123 \
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
```

---

## 🔵 BƯỚC 6: Disable 2FA + Set Developer Mode

```bash
docker exec -it nodata_php php bin/magento module:disable Magento_TwoFactorAuth Magento_AdminAdobeImsTwoFactorAuth
docker exec -it nodata_php php bin/magento deploy:mode:set developer
docker exec -it nodata_php php bin/magento config:set dev/static/sign 0
```

---

## 🔵 BƯỚC 7: Build static content

```bash
docker exec -it nodata_php php bin/magento setup:static-content:deploy -f
```

---

## 🔵 BƯỚC 8: Reindex + Flush cache

```bash
docker exec -it nodata_php php bin/magento indexer:reindex
docker exec -it nodata_php php bin/magento cache:flush
```

---

## 🔵 BƯỚC 9: Fix permissions

```bash
docker exec -it nodata_php bash -c "chmod -R 777 var pub/static pub/media generated"
```

---

## ✅ HOÀN TẤT!

### Truy cập PROJECT NODATA:
- **Frontend:** http://localhost:8080/
- **Admin:** http://localhost:8080/admin
- **Login:** admin / admin123

### Truy cập PROJECT MAGENTO (có data):
- **Frontend:** http://localhost/
- **Admin:** http://localhost/admin
- **Login:** admin / admin123

---

## 📊 BẢNG SO SÁNH 2 PROJECT:

| Project | Folder | URL | MySQL Port | Containers |
|---------|--------|-----|------------|------------|
| Magento (có data) | `/home/nghialuu/magento-docker/magento/` | http://localhost/ | 3307 | magento_php, magento_nginx, magento_mysql |
| Nodata (không data) | `/home/nghialuu/magento-nodata/nodata/` | http://localhost:8080/ | 3308 | nodata_php, nodata_nginx, nodata_mysql |

---

## 📝 LƯU Ý:

1. **Sau khi restart máy**, chạy cho từng project:
   ```bash
   # Project Magento
   cd /home/nghialuu/magento-docker && ./restart.sh
   
   # Project Nodata
   cd /home/nghialuu/magento-nodata && ./restart.sh
   ```

2. **Nếu muốn chuyển sang Production mode** (ổn định hơn):
   ```bash
   docker exec -it nodata_php php bin/magento deploy:mode:set production
   ```

3. **Thêm sản phẩm:** Vào Admin → Catalog → Products → Add Product

---

## 🔧 KHẮC PHỤC LỖI THƯỜNG GẶP:

### Lỗi 404 Admin:
```bash
docker exec -it nodata_php php bin/magento cache:flush
sudo systemctl stop nginx && docker compose restart nginx
```

### Lỗi Interceptor / Generated code:
```bash
docker exec -it nodata_php bash -c "
cd /var/www/html/nodata
rm -rf generated/* var/cache/* var/page_cache/*
php bin/magento setup:di:compile
php bin/magento cache:flush
chmod -R 777 var pub/static pub/media generated
"
```

### Lỗi 404 CSS/JS:
```bash
docker exec -it nodata_php php bin/magento setup:static-content:deploy -f
docker exec -it nodata_php php bin/magento cache:flush
```