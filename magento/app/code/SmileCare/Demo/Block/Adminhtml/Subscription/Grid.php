<?php
namespace SmileCare\Demo\Block\Adminhtml\Subscription;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_subscriptionCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        // Inject Collection (Dữ liệu từ DB) - File này bạn đã có ở Chương 5 chưa?
        \SmileCare\Demo\Model\ResourceModel\Subscription\CollectionFactory $subscriptionCollectionFactory,
        array $data = []
    ) {
        $this->_subscriptionCollectionFactory = $subscriptionCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('subscriptionGrid');
        $this->setDefaultSort('subscription_id'); // Sắp xếp theo ID
        $this->setDefaultDir('DESC'); // Mới nhất lên đầu
        $this->setSaveParametersInSession(true);
    }

    // Nạp dữ liệu vào bảng
    protected function _prepareCollection()
    {
        $collection = $this->_subscriptionCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    // Định nghĩa các cột hiển thị
    protected function _prepareColumns()
    {
        // Cột ID
        $this->addColumn(
            'subscription_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'subscription_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        // Cột Tên (Firstname)
        $this->addColumn(
            'firstname',
            [
                'header' => __('Họ'),
                'index' => 'firstname',
            ]
        );

        // Cột Email
        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
            ]
        );

        // Cột Trạng thái
        $this->addColumn(
            'status',
            [
                'header' => __('Trạng thái'),
                'index' => 'status',
                'type' => 'options',
                'options' => [
                    'pending' => 'Chờ duyệt',
                    'approved' => 'Đã duyệt',
                    'closed' => 'Đã đóng'
                ]
            ]
        );

        // Cột Ngày tạo
        $this->addColumn(
            'created_at',
            [
                'header' => __('Ngày tạo'),
                'index' => 'created_at',
                'type' => 'datetime',
            ]
        );

        return parent::_prepareColumns();
    }
}
