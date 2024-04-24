<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use League\Csv\Writer;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeGridFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeMassGeneratedBatchGridFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PromoCodeController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactoryInterface $promoCodeDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeGridFactory $promoCodeGridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeMassGeneratedBatchGridFactory $promoCodeMassGeneratedBatchGridFactory
     */
    public function __construct(
        protected readonly PromoCodeFacade $promoCodeFacade,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly PromoCodeDataFactoryInterface $promoCodeDataFactory,
        protected readonly PromoCodeGridFactory $promoCodeGridFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly PromoCodeMassGeneratedBatchGridFactory $promoCodeMassGeneratedBatchGridFactory,
    ) {
    }

    /**
     * @Route("/promo-code/list")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request): Response
    {
        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $grid = $this->promoCodeGridFactory->create(search: $quickSearchForm->getData()->text);
        $grid->enablePaging();

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $this->render('@ShopsysFramework/Admin/Content/PromoCode/list.html.twig', [
            'gridView' => $grid->createView(),
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    /**
     * @Route("/promo-code/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(int $id): RedirectResponse
    {
        try {
            $code = $this->promoCodeFacade->getById($id)->getCode();

            $this->promoCodeFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Promo code <strong>{{ code }}</strong> deleted.'),
                [
                    'code' => $code,
                ],
            );
        } catch (PromoCodeNotFoundException $ex) {
            $this->addErrorFlash(t('Selected promo code doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_promocode_list');
    }

    /**
     * @Route("/promo-code/new")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
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
     * @Route("/promo-code/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $promoCode = $this->promoCodeFacade->getById($id);
        $promoCodeData = $this->promoCodeDataFactory->createFromPromoCode($promoCode);

        $form = $this->createForm(PromoCodeFormType::class, $promoCodeData, [
            'promo_code' => $promoCode,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->promoCodeFacade->edit($id, $promoCodeData);

            $this->addSuccessFlashTwig(
                t('Promo code <strong><a href="{{ url }}">{{ code }}</a></strong> was modified'),
                [
                    'code' => $promoCode->getCode(),
                    'url' => $this->generateUrl('admin_promocode_edit', ['id' => $promoCode->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_promocode_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing promo code - %code%', ['%code%' => $promoCode->getCode()]),
        );

        return $this->render('@ShopsysFramework/Admin/Content/PromoCode/edit.html.twig', [
            'form' => $form->createView(),
            'promoCode' => $promoCode,
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
                t(
                    '{1}<strong>%count%</strong> promo code has been created|[2,Inf]<strong>%count%</strong> promo codes have been created',
                    ['%count%' => $promoCodeData->quantity],
                ),
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

        return $this->render('@ShopsysFramework/Admin/Content/PromoCode/newMassGenerate.html.twig', [
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
        $grid = $this->promoCodeMassGeneratedBatchGridFactory->create();
        $grid->enablePaging();

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $this->render('@ShopsysFramework/Admin/Content/PromoCode/listMassGeneratedBatch.html.twig', [
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
     * @param int $batchId
     * @return string
     */
    protected function generateCsvFromPromoCodeFromBatchId(int $batchId): string
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
