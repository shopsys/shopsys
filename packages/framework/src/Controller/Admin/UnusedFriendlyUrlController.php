<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlInlineEdit;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_FRIENDLY_URL)]
class UnusedFriendlyUrlController extends AdminBaseController
{
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly FriendlyUrlInlineEdit $friendlyUrlInlineEdit,
    ) {
    }

    #[Route(path: '/unused-friendly-url/list/', name: 'admin_unused_friendly_url_list')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $this->friendlyUrlInlineEdit->setGridQuickSearchFormData($quickSearchForm->getData());
        $unusedFriendlyUrlInlineEditGrid = $this->friendlyUrlInlineEdit->getGrid();

        return $this->render('@ShopsysAdministration/content/unusedFriendlyUrl/list.html.twig', [
            'quickSearchForm' => $quickSearchForm->createView(),
            'gridView' => $unusedFriendlyUrlInlineEditGrid->createView(),
        ]);
    }

    #[Route(path: '/unused-friendly-url/delete/{domainId}/{slug}', requirements: [
        'domainId' => '\d+',
        'slug' => '.+',
    ], name: 'admin_unused_friendly_url_delete')]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(Request $request, int $domainId, string $slug): Response
    {
        $this->friendlyUrlFacade->removeFriendlyUrl($domainId, $slug);

        $this->addSuccessFlash(t('Friendly URL "%slug%" was removed.', ['%slug%' => $slug]));

        return $this->redirectToRoute('admin_unused_friendly_url_list');
    }
}
