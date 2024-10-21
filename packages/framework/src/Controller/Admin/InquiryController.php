<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Inquiry\InquiryFacade;
use Shopsys\FrameworkBundle\Model\Inquiry\InquiryGridFactory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InquiryController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryGridFactory $inquiryGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryFacade $inquiryFacade
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        protected readonly InquiryGridFactory $inquiryGridFactory,
        protected readonly InquiryFacade $inquiryFacade,
        protected readonly Localization $localization,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/inquiry/list/')]
    public function listAction(Request $request): Response
    {
        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $queryBuilder = $this->inquiryFacade->getInquiryListQueryBuilderByQuickSearchData(
            $quickSearchForm->getData(),
            $this->localization->getAdminLocale(),
        );

        return $this->render('@ShopsysFramework/Admin/Content/Inquiry/list.html.twig', [
            'gridView' => $this->inquiryGridFactory->createView($queryBuilder, $this->getCurrentAdministrator()),
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/inquiry/detail/{id}', requirements: ['id' => '\d+'])]
    public function detailAction(int $id): Response
    {
        $inquiry = $this->inquiryFacade->getById($id);

        return $this->render('@ShopsysFramework/Admin/Content/Inquiry/detail.html.twig', [
            'inquiry' => $inquiry,
        ]);
    }
}
