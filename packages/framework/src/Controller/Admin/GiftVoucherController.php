<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\GiftVoucher\GiftVoucherFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherDataFactory;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherFacade;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherPdfGenerator;
use Shopsys\FrameworkBundle\Model\GiftVoucher\Grid\GiftVoucherGridFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_GIFT_VOUCHER)]
class GiftVoucherController extends AdminBaseController
{
    public function __construct(
        protected readonly GiftVoucherFacade $giftVoucherFacade,
        protected readonly GiftVoucherDataFactory $giftVoucherDataFactory,
        protected readonly GiftVoucherGridFactory $giftVoucherGridFactory,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly GiftVoucherPdfGenerator $giftVoucherPdfGenerator,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly EntityLogFacade $entityLogFacade,
        protected readonly ClockInterface $clock,
    ) {
    }

    #[Route(path: '/gift-voucher/list')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $grid = $this->giftVoucherGridFactory->create(AdminRoleConstant::ROLE_GIFT_VOUCHER, $quickSearchForm->getData()->text);
        $grid->enablePaging();

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $this->render('@ShopsysAdministration/content/giftVoucher/list.html.twig', [
            'gridView' => $grid->createView(),
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    #[Route(path: '/gift-voucher/new')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $giftVoucherData = $this->giftVoucherDataFactory->createForDomainId($this->adminDomainTabsFacade->getSelectedDomainId());

        $form = $this->createForm(GiftVoucherFormType::class, $giftVoucherData, [
            'gift_voucher' => null,
            'currency_code' => $giftVoucherData->currencyCode,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $giftVoucher = $this->giftVoucherFacade->create($form->getData());

            $this->addSuccessFlashTwig(
                t('Gift voucher <strong><a href="{{ url }}">{{ code }}</a></strong> created'),
                [
                    'code' => $giftVoucher->getCode(),
                    'url' => $this->generateUrl('admin_giftvoucher_edit', ['id' => $giftVoucher->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_giftvoucher_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/giftVoucher/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/gift-voucher/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $giftVoucher = $this->giftVoucherFacade->getById($id);
        $giftVoucherData = $this->giftVoucherDataFactory->createFromGiftVoucher($giftVoucher);

        $form = $this->createForm(GiftVoucherFormType::class, $giftVoucherData, [
            'gift_voucher' => $giftVoucher,
            'currency_code' => $giftVoucher->getCurrencyCode(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->giftVoucherFacade->edit($id, $form->getData());

            $this->addSuccessFlashTwig(
                t('Gift voucher <strong><a href="{{ url }}">{{ code }}</a></strong> modified'),
                [
                    'code' => $giftVoucher->getCode(),
                    'url' => $this->generateUrl('admin_giftvoucher_edit', ['id' => $giftVoucher->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_giftvoucher_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing gift voucher - %code%', ['%code%' => $giftVoucher->getCode()]),
        );

        return $this->render('@ShopsysAdministration/content/giftVoucher/edit.html.twig', [
            'form' => $form->createView(),
            'giftVoucher' => $giftVoucher,
            'isExpired' => $giftVoucher->isUnredeemed() && $giftVoucher->isExpiredAt($this->clock->now()),
            'entityLogEntityName' => $this->entityLogFacade->getEntityNameByEntity(GiftVoucher::class),
        ]);
    }

    #[Route(path: '/gift-voucher/download-pdf/{id}', requirements: ['id' => '\d+'])]
    #[CanView]
    public function downloadPdfAction(int $id): DownloadFileResponse
    {
        $giftVoucher = $this->giftVoucherFacade->getById($id);
        $pdfContent = $this->giftVoucherPdfGenerator->generatePdfContent($giftVoucher);

        return new DownloadFileResponse(
            sprintf('gift-voucher-%s.pdf', $giftVoucher->getCode()),
            $pdfContent,
            'application/pdf',
        );
    }
}
