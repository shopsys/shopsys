<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use League\Csv\Writer;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeGridFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeMassGeneratedBatchGridFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_PROMO_CODE)]
class PromoCodeController extends AdminBaseController
{
    public function __construct(
        protected readonly PromoCodeFacade $promoCodeFacade,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly PromoCodeDataFactory $promoCodeDataFactory,
        protected readonly PromoCodeGridFactory $promoCodeGridFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly PromoCodeMassGeneratedBatchGridFactory $promoCodeMassGeneratedBatchGridFactory,
    ) {
    }

    #[Route(path: '/promo-code/list')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $grid = $this->promoCodeGridFactory->create(AdminRoleConstant::ROLE_PROMO_CODE, search: $quickSearchForm->getData()->text);
        $grid->enablePaging();

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $this->render('@ShopsysAdministration/content/promoCode/list.html.twig', [
            'gridView' => $grid->createView(),
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    #[Route(path: '/promo-code/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
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

    #[Route(path: '/promo-code/new')]
    #[CanCreate]
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

        return $this->render('@ShopsysAdministration/content/promoCode/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/promo-code/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
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

        return $this->render('@ShopsysAdministration/content/promoCode/edit.html.twig', [
            'form' => $form->createView(),
            'promoCode' => $promoCode,
        ]);
    }

    #[Route(path: '/promo-code/new-mass-generate')]
    #[CanCreate]
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
            $saveButton = $form->get('actionBar')->get('saveAndDownloadCsv');

            if ($saveButton->isClicked()) {
                return $this->redirectToRoute('admin_promocode_listmassgeneratebatch', ['downloadBatchId' => $promoCodeData->massGenerateBatchId]);
            }

            return $this->redirectToRoute('admin_promocode_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/promoCode/newMassGenerate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/promo-code/list-mass-generate-batch')]
    #[CanView]
    public function listMassGenerateBatchAction(Request $request): Response
    {
        $grid = $this->promoCodeMassGeneratedBatchGridFactory->create();
        $grid->enablePaging();

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $this->render('@ShopsysAdministration/content/promoCode/listMassGeneratedBatch.html.twig', [
            'gridView' => $grid->createView(),
            'downloadBatchId' => $request->query->get('downloadBatchId'),
        ]);
    }

    #[Route(path: '/promo-code/download-mass-generate-batch/{batchId}')]
    #[CanView]
    public function downloadMassGenerateBatchAction(int $batchId): Response
    {
        $tempFileName = tempnam(sys_get_temp_dir(), 'promoCodesCsv');
        file_put_contents($tempFileName, $this->generateCsvFromPromoCodeFromBatchId($batchId));

        $fileName = 'promoCodesBatch-' . $batchId;

        return $this->file($tempFileName, $fileName);
    }

    protected function generateCsvFromPromoCodeFromBatchId(int $batchId): string
    {
        $promoCodes = $this->promoCodeFacade->findByMassBatchId($batchId);

        $csv = Writer::fromString();
        $csv->setDelimiter(';');

        foreach ($promoCodes as $promoCode) {
            $csv->insertOne([$promoCode->getCode()]);
        }

        return $csv->toString();
    }
}
