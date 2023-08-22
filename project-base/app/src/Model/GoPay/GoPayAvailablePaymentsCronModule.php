<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use App\Model\GoPay\Exception\GoPayNotConfiguredException;
use App\Model\GoPay\Exception\GoPayPaymentDownloadException;
use App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class GoPayAvailablePaymentsCronModule implements SimpleCronModuleInterface
{
    private Logger $logger;

    /**
     * @param array $goPayConfig
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade $paymentMethodFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly array $goPayConfig,
        private readonly GoPayPaymentMethodFacade $paymentMethodFacade,
        private readonly EntityManagerInterface $em,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        try {
            $this->em->beginTransaction();
            $this->downloadAndUpdatePaymentMethodsForAllDomains();
            $this->em->commit();
        } catch (GoPayNotConfiguredException $exception) {
            $this->logger->alert('GoPay configuration is not set.');
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            $this->em->rollback();

            throw $exception;
        }
    }

    private function downloadAndUpdatePaymentMethodsForAllDomains(): void
    {
        $allDomains = $this->domain->getAll();

        foreach ($allDomains as $domain) {
            if (array_key_exists($domain->getLocale(), $this->goPayConfig) === false) {
                continue;
            }

            $this->logger->info(sprintf('downloading for %s locale', $domain->getLocale()));

            try {
                $this->paymentMethodFacade->downloadAndUpdatePaymentMethods($domain);
            } catch (GoPayPaymentDownloadException $ex) {
                $this->logger->error($ex->getMessage());
            }
        }
    }
}
