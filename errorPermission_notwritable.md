Lỗi **`Fatal error: Uncaught Zend_Cache_Exception: cache_dir ... is not writable`** (và các lỗi `unlink ... Permission denied` đi kèm) chính là lỗi về **Quyền hạn (Permissions)** mà chúng ta đã xử lý ở phần cuối cùng.

Để hồ sơ của bạn đầy đủ nhất, bạn hãy bổ sung mục này vào file ghi chú. Đây là lỗi "kinh điển" nhất, chắc chắn bạn sẽ gặp lại nó nhiều lần.

Dưới đây là nội dung chi tiết để bạn copy vào file `fix_log.md`:

---

## 5. Lỗi "Zend_Cache_Exception" (Cache không ghi được)

### Dấu hiệu nhận biết:

* Màn hình web hoặc Terminal báo đỏ lòm:
> `Fatal error: Uncaught Zend_Cache_Exception: cache_dir "/var/www/html/magento/var/cache/" is not writable`


* Hoặc khi chạy lệnh compile bị báo:
> `The ".../generated/code/..." file can't be deleted. Warning! unlink(...): Permission denied`



### Nguyên nhân:

Do trước đó bạn lỡ chạy lệnh bằng quyền **ROOT** (hoặc `sudo`), khiến các file Cache/Code được tạo ra thuộc sở hữu của ông chủ `root`.
Sau đó, Magento (chạy bằng nhân viên `www-data`) cố gắng xóa hoặc ghi đè lên các file này thì bị hệ điều hành chặn lại (Nhân viên không được xóa file của Sếp).

### Cách sửa (Quy trình 3 bước chuẩn):

**Bước 1: Dùng quyền Sếp (Root) để xóa sạch file cũ**
*(Phải xóa bằng lệnh `sudo` trên Ubuntu mới được)*

```bash
cd ~/magento-docker/magento
sudo rm -rf var/cache var/page_cache var/view_preprocessed generated/code generated/metadata

```

**Bước 2: Mở rộng quyền ghi (Chmod 777)**
*(Để đảm bảo ai cũng ghi được vào thư mục này)*

```bash
sudo chmod -R 777 var generated pub/static

```

**Bước 3: Chạy lại lệnh Magento bằng đúng user**
*(Từ giờ trở đi chỉ chạy lệnh bằng `www-data`)*

```bash
docker exec -it -u www-data magento_php bin/magento setup:di:compile

```

---

### Tóm tắt ngắn gọn cho bạn dễ nhớ:

> Cứ thấy chữ **"Permission denied"** hoặc **"not writable"** => Là do file bị chiếm quyền bởi Root.
> **Giải pháp:** Xóa sạch file cũ bằng `sudo` -> Cấp quyền `777` -> Chạy lại.