<?php
namespace SmileCare\Demo\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Display extends Template
{
    // Biến chứa công cụ lấy sản phẩm
    protected $_productCollectionFactory;

    // Hàm khởi tạo
    public function __construct(
        Template\Context $context,
        CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        // Cất công cụ vào kho của Class ($this)
        $this->_productCollectionFactory = $productCollectionFactory;

        // Gọi hàm khởi tạo của cha (Template)
        parent::__construct($context, $data);
    }

    // Hàm logic lấy sản phẩm
    public function getProductCollection()
    {
        // 1. Tạo bộ sưu tập sản phẩm
        $collection = $this->_productCollectionFactory->create();

        // 2. Lấy tất cả thuộc tính (Tên, giá, hình...)
        $collection->addAttributeToSelect('*');

        $collection->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);

        // 3. Giới hạn chỉ lấy 3 sản phẩm
        $collection->setPageSize(3);

        // ---------------------------------------------
        return $collection;
    }

    // Hàm logic trả về câu chào
    public function getWelcomeMessage() {
        return "Danh sách sản phẩm mới nhất (Lấy từ Database):";
    }
}
