Restart may chi can:
~/magento-docker/restart.sh



Hoac
# 🔄 CÁC BƯỚC KHỞI ĐỘNG LẠI MAGENTO SAU KHI RESTART MÁY

## ✅ Bước 1: Tắt nginx host (nếu đang chạy trên port 80)
```bash
sudo systemctl stop nginx
```

## ✅ Bước 2: Khởi động Docker containers
```bash
cd /home/nghialuu/magento-docker
docker compose up -d
```

## ✅ Bước 3: Kiểm tra tất cả containers đã chạy
```bash
docker ps
```
Phải thấy 4 containers: `magento_nginx`, `magento_php`, `magento_mysql`, `magento_elasticsearch`

## ⚠️ Nếu thiếu magento_nginx (bị conflict port 80):
```bash
sudo systemctl stop nginx
docker compose up -d --force-recreate nginx
```

## ✅ Bước 4: Deploy static content và flush cache (BẮT BUỘC)
```bash
docker exec -it magento_php bash -c "
cd /var/www/html/magento
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
chmod -R 777 var pub/static pub/media generated
"
```

## ✅ Bước 5: Truy cập Magento
- Frontend: http://localhost/
- Admin: http://localhost/admin
- Login: admin / admin123

---

## 📝 Lưu ý:
- **Production mode**: Không cần deploy static content mỗi lần restart
- **Developer mode**: BẮT BUỘC chạy Bước 4 sau mỗi lần restart
- Nếu nginx host tự bật sau restart máy: `sudo systemctl disable nginx`

---

## 🚀 Script tự động (khuyên dùng):
Chạy script đã tạo sẵn:
```bash
~/magento-docker/restart.sh
```
