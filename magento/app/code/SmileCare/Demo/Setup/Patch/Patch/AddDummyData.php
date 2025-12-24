<?php
namespace SmileCare\Demo\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddDummyData implements DataPatchInterface
{
    private $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        // Bắt đầu cài đặt dữ liệu
        $this->moduleDataSetup->startSetup();

        $tableName = $this->moduleDataSetup->getTable('smilecare_demo_subscription');

        // Dữ liệu mẫu cần chèn
        $data = [
            [
                'firstname' => 'Nguyen',
                'lastname' => 'Van A',
                'email' => 'a.nguyen@example.com',
                'status' => 'pending',
                'message' => 'Tôi muốn đăng ký tư vấn nha khoa.'
            ],
            [
                'firstname' => 'Le',
                'lastname' => 'Thi B',
                'email' => 'b.le@test.com',
                'status' => 'approved',
                'message' => 'Gói smilecare này giá bao nhiêu?'
            ],
            [
                'firstname' => 'Tran',
                'lastname' => 'Van C',
                'email' => 'c.tran@demo.com',
                'status' => 'closed',
                'message' => 'Spam test.'
            ]
        ];

        // Chèn vào database
        $this->moduleDataSetup->getConnection()->insertMultiple($tableName, $data);

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
