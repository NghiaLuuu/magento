<?php
    namespace SmileCare\Demo\Controller\Hello;

    use Magento\Framework\App\Action\HttpGetActionInterface;
    use Magento\Framework\View\Result\PageFactory;

    class World implements HttpGetActionInterface
    {
        protected $pageFactory;

        public function __construct(PageFactory $pageFactory)
        {
            $this->pageFactory = $pageFactory;
        }

        public function execute()
        {
            // Trả về giao diện từ Layout XML
            return $this->pageFactory->create();
        }
    }
