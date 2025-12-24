<?php
namespace SmileCare\Demo\Controller\Adminhtml\Index;

// Nhớ đổi namespace
class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        // Đổi tiêu đề
        $resultPage->getConfig()->getTitle()->prepend(__('SmileCare Management'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        // Đổi ACL Resource
        return $this->_authorization->isAllowed('SmileCare_Demo::index');
    }
}
