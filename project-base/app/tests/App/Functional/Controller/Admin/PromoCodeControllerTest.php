<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Tests\App\Test\ApplicationTestCase;

final class PromoCodeControllerTest extends ApplicationTestCase
{
    private const string ADMIN_USERNAME = 'admin';

    private const string ADMIN_PASSWORD = 'admin123';

    /**
     * @inject
     */
    private PromoCodeFacade $promoCodeFacade;

    /**
     * @inject
     */
    private PromoCodeDataFactory $promoCodeDataFactory;

    public function testDownloadedMassGeneratedBatchIsCsvFileWithUnchangedContent(): void
    {
        $batchId = $this->promoCodeFacade->getMassLastGeneratedBatchId() + 1;
        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->domainId = Domain::FIRST_DOMAIN_ID;
        $promoCodeData->massGenerate = true;
        $promoCodeData->prefix = 'BATCH-';
        $promoCodeData->quantity = 3;
        $promoCodeData->massGenerateBatchId = $batchId;
        $this->promoCodeFacade->massCreate($promoCodeData);

        $client = $this->configureCurrentClient(self::ADMIN_USERNAME, self::ADMIN_PASSWORD);
        $client->request('GET', '/admin/promo-code/download-mass-generate-batch/' . $batchId);
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringStartsWith('text/csv', (string)$response->headers->get('Content-Type'));
        $this->assertSame(
            'attachment; filename=promoCodesBatch-' . $batchId . '.csv',
            $response->headers->get('Content-Disposition'),
        );

        $expectedCodes = [];

        foreach ($this->promoCodeFacade->findByMassBatchId($batchId) as $promoCode) {
            $expectedCodes[] = $promoCode->getCode();
        }
        sort($expectedCodes);

        $downloadedCodes = explode("\n", rtrim((string)$response->getContent(), "\n"));
        sort($downloadedCodes);

        $this->assertSame($expectedCodes, $downloadedCodes);
    }

    public function testDownloadingNonExistentBatchReturnsNotFound(): void
    {
        $nonExistentBatchId = $this->promoCodeFacade->getMassLastGeneratedBatchId() + 1;

        $client = $this->configureCurrentClient(self::ADMIN_USERNAME, self::ADMIN_PASSWORD);
        $client->request('GET', '/admin/promo-code/download-mass-generate-batch/' . $nonExistentBatchId);

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
