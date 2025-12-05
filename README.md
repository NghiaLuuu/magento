# Magento 2 Docker Environment

Môi trường Docker hoàn chỉnh cho Magento 2 Community Edition với PHP 8.2, MySQL 8.0, Nginx và Elasticsearch 8.10.

## 🚀 Quick Start

### Yêu cầu
- Docker Desktop hoặc Docker Engine + Docker Compose
- 4GB RAM trở lên
- 10GB disk space

### Cài đặt

1. **Clone repository**
```bash
git clone <repository-url>
cd magento-docker
```

2. **Tạo file .env**
```bash
cp .env.example .env
nano .env  # Điền PUBLIC_KEY và PRIVATE_KEY từ https://marketplace.magento.com/
```

3. **Khởi động containers**
```bash
docker compose up -d
```

4. **Xem hướng dẫn chi tiết**
Mở file `SETUP_GUIDE.md` để xem hướng dẫn cài đặt Magento từng bước.

## 📁 Cấu trúc

```
magento-docker/
├── .env.example          # Template file cấu hình (copy thành .env)
├── docker-compose.yml    # Cấu hình Docker services
├── Dockerfile-php        # PHP 8.2-FPM image
├── nginx/
│   └── default.conf     # Nginx configuration
├── magento/             # Source code Magento (tạo sau khi cài)
├── magento_sampledata/  # Source code với sample data (optional)
└── SETUP_GUIDE.md       # Hướng dẫn chi tiết
```

## 🔧 Services

- **Nginx**: Port 80 (configurable via PORT_WEB)
- **PHP 8.2-FPM**: Extensions đầy đủ cho Magento 2
- **MySQL 8.0**: Port 3307 (configurable via PORT_MYSQL)
- **Elasticsearch 8.10**: Port 9201 (configurable via PORT_ELASTICSEARCH)

## 📖 Tài liệu

Xem `SETUP_GUIDE.md` để biết:
- Hướng dẫn cài đặt chi tiết từ A-Z
- Xử lý lỗi thường gặp
- Cài đặt sample data
- Chạy nhiều project cùng lúc
- Các lệnh hữu ích

## 🔑 Truy cập

Sau khi cài đặt xong:
- **Frontend**: http://localhost/
- **Admin**: http://localhost/admin (hoặc URI hiển thị sau khi cài)
- **Username**: admin (hoặc theo .env)
- **Password**: admin123 (hoặc theo .env)

## ⚡ Khởi động sau khi restart máy

```bash
cd magento-docker
docker compose up -d
```

Chỉ cần vậy! Không cần cài lại Magento.

## 🛑 Dừng containers

```bash
docker compose down
```

## 🆘 Hỗ trợ

Xem file `SETUP_GUIDE.md` phần "Xử Lý Lỗi Thường Gặp" để biết cách fix các lỗi phổ biến.

## 📝 License

Magento 2 Community Edition - OSL-3.0
