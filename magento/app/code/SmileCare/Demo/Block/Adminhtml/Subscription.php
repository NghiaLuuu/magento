<?php
namespace SmileCare\Demo\Block\Adminhtml;

class Subscription extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        // 1. Định nghĩa Controller xử lý Grid
        $this->_controller = 'adminhtml_subscription';

        // 2. Định nghĩa tên Module
        $this->_blockGroup = 'SmileCare_Demo';

        // 3. Tiêu đề hiển thị trên đầu bảng
        $this->_headerText = __('Danh sách Đăng ký SmileCare');

        // 4. Nhãn của nút Thêm mới
        $this->_addButtonLabel = __('Tạo Đăng ký Mới');

        parent::_construct();
    }
}
