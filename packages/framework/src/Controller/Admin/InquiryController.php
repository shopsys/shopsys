<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Inquiry\InquiryFacade;
use Shopsys\FrameworkBundle\Model\Inquiry\InquiryGridFactory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_INQUIRY)]
class InquiryController extends AdminBaseController
{
    public function __construct(
        protected readonly InquiryGridFactory $inquiryGridFactory,
        protected readonly InquiryFacade $inquiryFacade,
        protected readonly Localization $localization,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        protected readonly Domain $domain,
    ) {
    }

    #[Route(path: '/inquiry/list/')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $domainFilterNamespace = 'inquiries';

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $queryBuilder = $this->inquiryFacade->getInquiryListQueryBuilderByQuickSearchData(
            $quickSearchForm->getData(),
            $this->localization->getCurrentLocaleForTranslatableEntities(),
        );

        $selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId($domainFilterNamespace);

        if ($selectedDomainId !== null) {
            $queryBuilder
                ->andWhere('i.domainId = :selectedDomainId')
                ->setParameter('selectedDomainId', $selectedDomainId);
        } else {
            $queryBuilder
                ->andWhere('i.domainId IN (:domainIds)')
                ->setParameter('domainIds', $this->domain->getAdminEnabledDomainIds());
        }

        return $this->render('@ShopsysAdministration/content/inquiry/list.html.twig', [
            'gridView' => $this->inquiryGridFactory->createView($queryBuilder, $this->getCurrentAdministrator()),
            'domainFilterNamespace' => $domainFilterNamespace,
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    #[Route(path: '/inquiry/detail/{id}', requirements: ['id' => '\d+'])]
    #[CanView]
    public function detailAction(int $id): Response
    {
        $inquiry = $this->inquiryFacade->getById($id);

        return $this->render('@ShopsysAdministration/content/inquiry/detail.html.twig', [
            'inquiry' => $inquiry,
        ]);
    }
}
