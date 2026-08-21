<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Tests\App\Test\ApplicationTestCase;

final class PromoCodeControllerTest extends ApplicationTestCase
{
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

        $client = $this->configureCurrentClient('admin', 'admin123');
        $client->request('GET', '/admin/promo-code/download-mass-generate-batch/' . $batchId);
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringStartsWith('text/csv', (string)$response->headers->get('Content-Type'));
        $this->assertSame(
            'attachment; filename=promoCodesBatch-' . $batchId . '.csv',
            $response->headers->get('Content-Disposition'),
        );

        $expectedCsvContent = '';

        foreach ($this->promoCodeFacade->findByMassBatchId($batchId) as $promoCode) {
            $expectedCsvContent .= $promoCode->getCode() . "\n";
        }

        $this->assertSame($expectedCsvContent, $response->getContent());
    }
}
