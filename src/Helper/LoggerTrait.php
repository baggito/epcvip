<?php

namespace App\Helper;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

trait LoggerTrait
{
    /** @var LoggerInterface|null */
    protected $logger;

    /** @var EntityManagerInterface*/
    protected $em;

    /**
     * @required
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $em
     */
    public function setLogger(LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->em = $em;
    }

    public function logInfo(string $message, array $context = [])
    {
        if ($this->logger) {
            if (getenv('LOG') == 'file') {
                $this->logger->info($message, $context);
            } elseif (getenv('LOG') == 'db') {

                $logEntry = new Log();

                $logEntry->setInfo($message);
                $logEntry->setData($context);

                $this->em->persist($logEntry);
                $this->em->flush();
            }
        }
    }
}