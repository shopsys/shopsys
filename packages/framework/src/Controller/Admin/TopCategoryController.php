<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Category\TopCategory\TopCategoriesFormType;
use Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_TOP_CATEGORY)]
class TopCategoryController extends AdminBaseController
{
    public function __construct(
        protected readonly TopCategoryFacade $topCategoryFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly Localization $localization,
    ) {
    }

    #[Route(path: '/category/top-category/list/')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function listAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $formData = [
            'categories' => $this->topCategoryFacade->getAllCategoriesByDomainId($domainId),
        ];

        $form = $this->createForm(TopCategoriesFormType::class, $formData, [
            'domain_id' => $domainId,
            'locale' => $this->localization->getCurrentLocaleForTranslatableEntities(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categories = $form->getData()['categories'];

            $this->topCategoryFacade->saveTopCategoriesForDomain($domainId, $categories);

            $this->addSuccessFlash(t('Product settings on the main page successfully changed'));
        }

        return $this->render('@ShopsysAdministration/content/topCategory/list.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
