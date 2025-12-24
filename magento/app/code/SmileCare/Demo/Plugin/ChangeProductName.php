<?php
namespace SmileCare\Demo\Plugin;

class ChangeProductName
{
    /**
     * 1. BEFORE PLUGIN
     * Chạy TRƯỚC khi hàm gốc getName() bắt đầu.
     * Thường dùng để thay đổi tham số đầu vào (nếu hàm gốc có tham số).
     * Hàm getName() của Magento không có tham số, nên ở đây ta chỉ dùng để LOG dữ liệu.
     */
    public function beforeGetName(\Magento\Catalog\Model\Product $subject)
    {
        // Bạn có thể thực hiện logic chuẩn bị ở đây
        // Ví dụ: ghi log hoặc kiểm tra trạng thái sản phẩm
        return null;
    }

    /**
     * 2. AROUND PLUGIN
     * "Bao vây" lấy hàm gốc. Bạn có quyền cho hàm gốc chạy hoặc KHÔNG.
     * Cực kỳ mạnh mẽ nhưng cần cẩn thận vì có thể làm chậm hệ thống.
     */
    public function aroundGetName(
        \Magento\Catalog\Model\Product $subject,
        callable $proceed // Đây là biến đại diện cho hàm gốc
    ) {
        // --- Code chạy TRƯỚC khi hàm gốc chạy ---

        // Gọi hàm gốc để lấy kết quả (Bắt buộc phải gọi $proceed() nếu muốn hàm gốc chạy)
        $name = $proceed();

        // --- Code chạy SAU khi hàm gốc chạy ---
        if ($name) {
            $name = " [Around - Start] " . $name . " [Around - End]";
        }

        return $name;
    }

    /**
     * 3. AFTER PLUGIN
     * Chạy SAU cùng, nhận kết quả từ hàm gốc (hoặc từ Around) để xào nấu lại.
     * Đây là loại hay được dùng nhất để sửa hiển thị.
     */
    public function afterGetName(\Magento\Catalog\Model\Product $subject, $result)
    {
        if ($result) {
            return "SmileCare - " . $result;
        }
        return $result;
    }
}
