<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayNotConfiguredException;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayPaymentDownloadException;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class GoPayAvailablePaymentsCronModule implements SimpleCronModuleInterface
{
    protected LoggerInterface $logger;

    /**
     * @param array $goPayConfig
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade $paymentMethodFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly array $goPayConfig,
        protected readonly GoPayPaymentMethodFacade $paymentMethodFacade,
        protected readonly EntityManagerInterface $em,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
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

    protected function downloadAndUpdatePaymentMethodsForAllDomains(): void
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
                $this->logger->error($ex->getMessage(), [
                    'exception' => $ex,
                ]);
            }
        }
    }
}
