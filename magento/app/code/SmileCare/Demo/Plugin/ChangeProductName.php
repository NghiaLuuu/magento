<?php
namespace SmileCare\Demo\Plugin;

class ChangeProductName
{
    /**
     * Hàm after + Tên hàm gốc (GetName)
     * * @param \Magento\Catalog\Model\Product $subject (Đối tượng sản phẩm đang gọi hàm)
     * @param string $result (Kết quả tên gốc mà hàm getName() vừa trả về)
     * @return string
     */
    public function afterGetName(\Magento\Catalog\Model\Product $subject, $result)
    {
        // Kiểm tra nếu tên sản phẩm không trống thì thêm tiền tố vào
        if ($result) {
            return "SmileCare - " . $result;
        }
        return $result;
    }
}
