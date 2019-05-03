<?php

namespace App\Command;

use App\Controller\ProductController;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetPendingProducts extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'products:pending';

    /** @var EntityManagerInterface */
    private $em;

    /** @var Swift_Mailer */
    private $mailer;

    /** @var ProductController */
    private $productController;

    public function __construct(EntityManagerInterface $em, Swift_Mailer $mailer, ProductController $productController)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->productController = $productController;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Fetch a list of pending products')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to fetch all products with status "pending"...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = (new Swift_Message('Hello Email'))
            ->setFrom(getenv('MAILER_SENDER'))
            ->setTo(getenv('MAILER_RECIPIENT'))
            ->setBody(
                $this->productController->renderPendingProductsForEmail($this->em),
                'text/html'
            );

        $this->mailer->send($message);

        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            '===================================',
            'Products list is sent to your email',
            '===================================',
            '',
        ]);
    }
}