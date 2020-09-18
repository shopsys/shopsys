<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge;

use App\Component\ScontoBridge\Transfer\Exception\ScontoBridgeTransferException;
use App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserRepository;
use App\Model\Customer\User\CustomerUserScontoBridgeStatusEnum;
use App\Model\Transfer\TransferIdentificationInterface;
use App\Model\Transfer\TransferLoggerInterface;
use Exception;
use Generator;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;

class ScontoBridgeExportCustomerFacade implements TransferIdentificationInterface
{
    /**
     * @var ScontoBridgeImportTransferDependency
     */
    private ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency;

    /**
     * @var TransferLoggerInterface
     */
    private TransferLoggerInterface $logger;

    /**
     * @var CustomerUserRepository
     */
    private CustomerUserRepository $repository;

    /**
     * @var CustomerTransferScontoBridgeExporter
     */
    private CustomerTransferScontoBridgeExporter $customerTransferScontoBridgeExporter;

    /**
     * @param ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency
     * @param CustomerUserRepository $repository
     * @param CustomerTransferScontoBridgeExporter $customerTransferScontoBridgeExporter
     */
    public function __construct(
        ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency,
        CustomerUserRepository $repository,
        CustomerTransferScontoBridgeExporter $customerTransferScontoBridgeExporter
    ) {
        $this->scontoBridgeImportTransferDependency = $scontoBridgeImportTransferDependency;
        $this->logger = $scontoBridgeImportTransferDependency->getTransferLoggerFactory()->getTransferLoggerByIdentifier($this);
        $this->repository = $repository;
        $this->customerTransferScontoBridgeExporter = $customerTransferScontoBridgeExporter;
    }

    public function exportCustomers(): void
    {
        foreach ($this->getData() as $customerUser) {
            $this->logger->addInfo(sprintf('Exporting customer user id \'%d\'', $customerUser->getCustomer()->getId()));
            $this->markCustomerUserProcessing($customerUser);
            $error = true;

            try {
                $this->customerTransferScontoBridgeExporter->exportCustomerUser($customerUser);
                $this->markCustomerUserDone($customerUser);
                $error = false;
            } catch (CustomerTransferScontoBridgeMapperException $e) {
                $this->logger->addError('Cannot map sconto customer user data to bridge format', [
                    'exception' => $e->getMessage()
                ]);
            } catch (ScontoBridgeTransferException $e) {
                $this->logger->addError('Order transfer API error occured', [
                    'response' => $e->getResponseContent(),
                    'httpStatus' => $e->getHttpCode(),
                ]);
            } catch (GuzzleException $e) {
                $response = $request = null;
                if ($e instanceof BadResponseException) {
                    $response = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null;
                    $request = (string)$e->getRequest()->getBody();
                }
                $this->logger->addCritical('Error occured during http transfer', [
                    'exception' => $e->getMessage(),
                    'response' => $response,
                    'request' => $request
                ]);
            } catch (Exception $e) {
                $this->logger->addCritical('Fatal error', [
                    'exception' => $e->getMessage()
                ]);
            }

            if ($error === true) {
                $this->markCustomerUserError($customerUser);
            }
        }
    }

    public function getTransferName(): string
    {
        return t("Export zákazníků do můstku");
    }

    public function getTransferIdentifier(): string
    {
        return 'CustomerUserExportTransfer';
    }

    public function getServiceIdentifier(): string
    {
        return 'ScontoBridge';
    }

    /**
     * @return Generator
     */
    private function getData(): Generator
    {
        $customers = $this->repository->findByScontoBridgeStatuses(
            CustomerUserScontoBridgeStatusEnum::create(CustomerUserScontoBridgeStatusEnum::STATUS_NEW),
            CustomerUserScontoBridgeStatusEnum::create(CustomerUserScontoBridgeStatusEnum::STATUS_SCHEDULED)
        );

        foreach ($customers as $customerUser) {
            yield $customerUser;
        }
    }

    /**
     * @param CustomerUser $customerUser
     */
    private function markCustomerUserProcessing(CustomerUser $customerUser): void
    {
        $this->setCustomerUserScontoBridgeStatus(
            $customerUser,
            CustomerUserScontoBridgeStatusEnum::create(CustomerUserScontoBridgeStatusEnum::STATUS_PROCESSING)
        );
    }

    /**
     * @param CustomerUser $customerUser
     */
    private function markCustomerUserDone(CustomerUser $customerUser): void
    {
        $this->setCustomerUserScontoBridgeStatus(
            $customerUser,
            CustomerUserScontoBridgeStatusEnum::create(CustomerUserScontoBridgeStatusEnum::STATUS_DONE)
        );
    }

    /**
     * @param CustomerUser $customerUser
     */
    private function markCustomerUserError(CustomerUser $customerUser): void
    {
        $this->setCustomerUserScontoBridgeStatus(
            $customerUser,
            CustomerUserScontoBridgeStatusEnum::create(CustomerUserScontoBridgeStatusEnum::STATUS_ERROR)
        );
    }

    /**
     * @param CustomerUser $customerUser
     * @param CustomerUserScontoBridgeStatusEnum $status
     */
    private function setCustomerUserScontoBridgeStatus(CustomerUser $customerUser, CustomerUserScontoBridgeStatusEnum $status): void
    {
        $customerUser->setScontoBridgeStatus($status);

        $em = $this->scontoBridgeImportTransferDependency->getEm();
        $em->persist($customerUser);
        $em->flush();
    }
}
