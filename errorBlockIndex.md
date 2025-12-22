1. Lỗi "Sát thủ": Elasticsearch 8 chặn sắp xếp theo ID
Đây là nguyên nhân chính khiến Frontend báo "We can't find products..." dù cấu hình Admin đúng hết.

Dấu hiệu nhận biết (Log): Xem file var/log/exception.log thấy dòng:

Elasticsearch\Exception\ClientResponseException(code: 400): ... Fielddata access on the _id field is disallowed ... indices.id_field_data.enabled

Lệnh kiểm tra log:

Bash

docker exec -it magento_php tail -f var/log/exception.log
Lệnh sửa (FIX) - Chạy 1 lần duy nhất: (Lưu ý: Port 9201 là port Docker map ra ngoài máy bạn)

Bash

curl -X PUT "localhost:9201/_cluster/settings" -H 'Content-Type: application/json' -d'
{
  "persistent": {
    "indices.id_field_data.enabled": true
  }
}
'
2. Lỗi Quyền truy cập (Permission Denied)
Đây là lỗi phổ biến khi dùng Docker trên Linux, khiến web bị "Màn hình xanh chết chóc".

Dấu hiệu nhận biết: Trang web hiện: "There has been an error processing your request..." hoặc log báo:

Warning: file_put_contents(.../var/cache/...): Failed to open stream: Permission denied

Lệnh sửa (FIX): Đứng tại thư mục chứa code Magento trên máy thật (Ubuntu), chạy:

Bash

# Cấp quyền ghi tối đa cho các thư mục sinh file tạm
sudo chmod -R 777 var generated pub/static
3. Quy trình làm mới dữ liệu (Refresh) chuẩn cho Docker
Mỗi khi bạn sửa code, cài module mới, hoặc thấy dữ liệu hiển thị sai, hãy chạy bộ lệnh này.

Nguyên tắc vàng: Luôn thêm -u www-data để chạy dưới quyền web user, tránh bị lỗi Permission.

Bash

# 1. Xóa Cache (Quan trọng nhất)
docker exec -it -u www-data magento_php bin/magento cache:flush

# 2. Reindex (Cập nhật dữ liệu sản phẩm/danh mục)
docker exec -it -u www-data magento_php bin/magento indexer:reindex

# 3. Biên dịch lại Code (Dùng khi cài module mới hoặc sửa code PHP sâu)
docker exec -it -u www-data magento_php bin/magento setup:di:compile

# 4. Deploy giao diện (Dùng khi sửa CSS/JS/HTML mà không nhận)
docker exec -it -u www-data magento_php bin/magento setup:static-content:deploy -f
4. Các lệnh kiểm tra nhanh (Cheat Sheet)
Kiểm tra xem Elasticsearch có chứa sản phẩm ID 44 không: (Thay magento2_fix... bằng tên index thực tế của bạn)

Bash

curl "localhost:9201/magento2_fix_product_1_v2/_search?q=_id:44&pretty"
Nếu "value": 1 -> OK.

Nếu "value": 0 -> Chưa vào index.

Kiểm tra trạng thái Index của Magento:

Bash

docker exec -it -u www-data magento_php bin/magento indexer:status
Tất cả phải là Ready (Màu xanh).

Xem danh sách các Index trong Elasticsearch:

Bash

curl "localhost:9201/_cat/indices?v"
Chúc dự án SmileCare Dental của bạn phát triển thuận lợi! Nếu gặp lỗi gì tiếp theo, cứ nhắn mình nhé.