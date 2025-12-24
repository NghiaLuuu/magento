<?php
namespace SmileCare\Demo\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloCommand extends Command
{
    protected function configure()
    {
        $this->setName('smilecare:hello') // Tên lệnh bạn sẽ gõ
        ->setDescription('Lenh chao hoi tu SmileCare');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Chuc mung! Ban da chay thanh cong lenh BE dau tien.");
        return 0; // 0 nghia la thanh cong
    }
}
