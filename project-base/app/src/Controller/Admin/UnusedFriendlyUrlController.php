<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Component\Router\FriendlyUrl\FriendlyUrlInlineEdit;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UnusedFriendlyUrlController extends AdminBaseController
{
    /**
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlInlineEdit $friendlyUrlInlineEdit
     */
    public function __construct(
        private FriendlyUrlFacade $friendlyUrlFacade,
        private FriendlyUrlInlineEdit $friendlyUrlInlineEdit,
    ) {
    }

    /**
     * @Route("/unused-friendly-url/list/", name="admin_unused_friendly_url_list")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request): Response
    {
        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $this->friendlyUrlInlineEdit->setGridQuickSearchFormData($quickSearchForm->getData());
        $unusedFriendlyUrlInlineEditGrid = $this->friendlyUrlInlineEdit->getGrid();

        return $this->render('Admin/Content/UnusedFriendlyUrl/list.html.twig', [
            'quickSearchForm' => $quickSearchForm->createView(),
            'gridView' => $unusedFriendlyUrlInlineEditGrid->createView(),
        ]);
    }

    /**
     * @Route("/unused-friendly-url/delete/{domainId}/{slug}", requirements={"domainId" = "\d+", "slug" = ".+"}, name="admin_unused_friendly_url_delete")
     * @CsrfProtection
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $domainId
     * @param string $slug
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, int $domainId, string $slug): Response
    {
        $this->friendlyUrlFacade->removeFriendlyUrl($domainId, $slug);

        $this->addSuccessFlash(sprintf('Friendlu URL "%s" was removed.', $slug));
        return $this->redirectToRoute('admin_unused_friendly_url_list');
    }
}
