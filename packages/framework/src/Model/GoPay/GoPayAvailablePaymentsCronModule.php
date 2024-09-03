<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayNotConfiguredException;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayNotEnabledOnDomainException;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayPaymentDownloadException;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class GoPayAvailablePaymentsCronModule implements SimpleCronModuleInterface
{
    protected Logger $logger;

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade $paymentMethodFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly GoPayPaymentMethodFacade $paymentMethodFacade,
        protected readonly EntityManagerInterface $em,
        protected readonly Domain $domain,
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
            try {
                $this->logger->info(sprintf(
                    'Downloading for the domain %d (%s)',
                    $domain->getId(),
                    $domain->getName(),
                ));
                $this->paymentMethodFacade->downloadAndUpdatePaymentMethods($domain);
            } catch (GoPayNotEnabledOnDomainException $ex) {
                $this->logger->info(sprintf(
                    'GoPay is not enabled on the domain %d (%s)',
                    $domain->getId(),
                    $domain->getName(),
                ));
            } catch (GoPayNotConfiguredException $exception) {
                $this->logger->alert(sprintf(
                    'GoPay configuration is not set properly for the domain %d (%s)',
                    $domain->getId(),
                    $domain->getName(),
                ));
            } catch (GoPayPaymentDownloadException $ex) {
                $this->logger->error($ex->getMessage(), [
                    'exception' => $ex,
                ]);
            }
        }
    }
}
