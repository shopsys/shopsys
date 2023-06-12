<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Order\PromoCode\Grid\PromoCodeMassGeneratedBatchGridFactory;
use League\Csv\Writer;
use Shopsys\FrameworkBundle\Controller\Admin\PromoCodeController as BasePromoCodeController;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeGridFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
 * @property \App\Model\Order\PromoCode\PromoCodeDataFactory $promoCodeDataFactory
 * @property \App\Model\Order\PromoCode\Grid\PromoCodeGridFactory $promoCodeGridFactory
 */
class PromoCodeController extends BasePromoCodeController
{
    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \App\Model\Order\PromoCode\PromoCodeDataFactory $promoCodeDataFactory
     * @param \App\Model\Order\PromoCode\Grid\PromoCodeGridFactory $promoCodeGridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \App\Model\Order\PromoCode\Grid\PromoCodeMassGeneratedBatchGridFactory $promoCodeMassGeneratedBatchGridFactory
     */
    public function __construct(
        PromoCodeFacade $promoCodeFacade,
        AdministratorGridFacade $administratorGridFacade,
        PromoCodeDataFactoryInterface $promoCodeDataFactory,
        PromoCodeGridFactory $promoCodeGridFactory,
        BreadcrumbOverrider $breadcrumbOverrider,
        private PromoCodeMassGeneratedBatchGridFactory $promoCodeMassGeneratedBatchGridFactory,
    ) {
        parent::__construct($promoCodeFacade, $administratorGridFacade, $promoCodeDataFactory, $promoCodeGridFactory, $breadcrumbOverrider);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getUser();

        $grid = $this->promoCodeGridFactory->create();
        $grid->enablePaging();

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $this->render('Admin/Content/PromoCode/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/promo-code/new-mass-generate")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newMassGenerateAction(Request $request): Response
    {
        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->massGenerate = true;

        $form = $this->createForm(PromoCodeFormType::class, $promoCodeData, [
            'promo_code' => null,
            'mass_generate' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $promoCodeData->massGenerateBatchId = $this->promoCodeFacade->getMassLastGeneratedBatchId() + 1;
            $this->promoCodeFacade->massCreate($promoCodeData);

            $this->addSuccessFlashTwig(
                t('Bylo vytvořeno <strong>{{ quantity }}</strong> slevových kupónů'),
                [
                    'quantity' => $promoCodeData->quantity,
                ],
            );

            /** @var \Symfony\Component\Form\SubmitButton $saveButton */
            $saveButton = $form->get('saveAndDownloadCsv');

            if ($saveButton->isClicked()) {
                return $this->redirectToRoute('admin_promocode_listmassgeneratebatch', ['downloadBatchId' => $promoCodeData->massGenerateBatchId]);
            }

            return $this->redirectToRoute('admin_promocode_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('Admin/Content/PromoCode/newMassGenerate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/promo-code/list-mass-generate-batch")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function listMassGenerateBatchAction(Request $request): Response
    {
        $administrator = $this->getUser();
        /** @var \App\Model\Administrator\Administrator $administrator */
        $grid = $this->promoCodeMassGeneratedBatchGridFactory->create();
        $grid->enablePaging();

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $this->render('Admin/Content/PromoCode/listMassGeneratedBatch.html.twig', [
            'gridView' => $grid->createView(),
            'downloadBatchId' => $request->query->get('downloadBatchId'),
        ]);
    }

    /**
     * @Route("/promo-code/download-mass-generate-batch/{batchId}")
     * @param int $batchId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function downloadMassGenerateBatchAction(int $batchId): Response
    {
        $tempFileName = tempnam(sys_get_temp_dir(), 'promoCodesCsv');
        file_put_contents($tempFileName, $this->generateCsvFromPromoCodeFromBatchId((int)$batchId));

        $fileName = 'promoCodesBatch-' . $batchId;

        return $this->file($tempFileName, $fileName);
    }

    /**
     * @Route("/promo-code/new")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function newAction(Request $request)
    {
        $fillFromPromoCodeId = $request->query->get('fillFromPromoCodeId');

        if ($fillFromPromoCodeId === null) {
            $promoCodeData = $this->promoCodeDataFactory->create();
        } else {
            $promoCode = $this->promoCodeFacade->getById((int)$fillFromPromoCodeId);
            $promoCodeData = $this->promoCodeDataFactory->createFromPromoCode($promoCode);
            $promoCodeData->code = null;
        }

        $form = $this->createForm(PromoCodeFormType::class, $promoCodeData, [
            'promo_code' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $promoCode = $this->promoCodeFacade->create($form->getData());

            $this->addSuccessFlashTwig(
                t('Promo code <strong><a href="{{ url }}">{{ code }}</a></strong> created'),
                [
                    'code' => $promoCode->getCode(),
                    'url' => $this->generateUrl('admin_promocode_edit', ['id' => $promoCode->getId()]),
                ],
            );
            return $this->redirectToRoute('admin_promocode_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/PromoCode/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param int $batchId
     * @return string
     */
    private function generateCsvFromPromoCodeFromBatchId(int $batchId): string
    {
        $promoCodes = $this->promoCodeFacade->findByMassBatchId($batchId);

        $csv = Writer::createFromString('');
        $csv->setDelimiter(';');

        foreach ($promoCodes as $promoCode) {
            $csv->insertOne([$promoCode->getCode()]);
        }

        return $csv->getContent();
    }
}
