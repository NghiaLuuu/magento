<?php
namespace SmileCare\Demo\Model\ResourceModel\Subscription;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'subscription_id';
    protected $_eventPrefix = 'smilecare_demo_subscription_collection';
    protected $_eventObject = 'subscription_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        // Tham số 1: Đường dẫn file Model (File 1)
        // Tham số 2: Đường dẫn file ResourceModel (File 2)
        $this->_init(
            'SmileCare\Demo\Model\Subscription',
            'SmileCare\Demo\Model\ResourceModel\Subscription'
        );
    }
}
