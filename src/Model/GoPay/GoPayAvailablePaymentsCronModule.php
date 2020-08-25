<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use App\Model\GoPay\Exception\GoPayPaymentDownloadException;
use App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class GoPayAvailablePaymentsCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade
     */
    private $paymentMethodFacade;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var array
     */
    private $goPayConfig;

    /**
     * @param array $goPayConfig
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade $paymentMethodFacade
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(
        array $goPayConfig,
        GoPayPaymentMethodFacade $paymentMethodFacade,
        EntityManagerInterface $entityManager,
        Domain $domain
    ) {
        $this->paymentMethodFacade = $paymentMethodFacade;
        $this->em = $entityManager;
        $this->domain = $domain;
        $this->goPayConfig = $goPayConfig;
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function run(): void
    {
        try {
            $this->em->beginTransaction();
            $this->downloadAndUpdatePaymentMethodsForAllDomains();
            $this->em->commit();
        } catch (\App\Model\GoPay\Exception\GoPayNotConfiguredException $exception) {
            $this->logger->addAlert('GoPay configuration is not set.');
        } catch (\Exception $exception) {
            $this->logger->addError($exception->getMessage(), ['exception' => $exception]);
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

            $this->logger->addInfo(sprintf('downloading for %s locale', $domain->getLocale()));
            try {
                $this->paymentMethodFacade->downloadAndUpdatePaymentMethods($domain);
            } catch (GoPayPaymentDownloadException $ex) {
                $this->logger->addError($ex);
            }
        }
    }
}
