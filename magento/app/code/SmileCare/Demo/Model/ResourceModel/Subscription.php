<?php
namespace SmileCare\Demo\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Subscription extends AbstractDb
{
    protected function _construct()
    {
        // Tham số 1: Tên bảng trong Database
        // Tham số 2: Tên cột khóa chính (Primary Key)
        $this->_init('smilecare_demo_subscription', 'subscription_id');
    }
}
