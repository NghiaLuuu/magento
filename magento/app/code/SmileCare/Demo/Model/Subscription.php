<?php
namespace SmileCare\Demo\Model;

use Magento\Framework\Model\AbstractModel;

class Subscription extends AbstractModel
{
    protected function _construct()
    {
        // Khai báo ResourceModel tương ứng
        $this->_init('SmileCare\Demo\Model\ResourceModel\Subscription');
    }
}
