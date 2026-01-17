<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoPageFormType;
use Shopsys\FrameworkBundle\Model\Seo\Page\Exception\DefaultSeoPageCannotBeDeletedException;
use Shopsys\FrameworkBundle\Model\Seo\Page\Exception\SeoPageNotFoundException;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageGridFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_SEO_PAGES)]
class SeoPageController extends AdminBaseController
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly SeoPageGridFactory $seoPageGridFactory,
        protected readonly SeoPageDataFactory $seoPageDataFactory,
        protected readonly SeoPageFacade $seoPageFacade,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
    ) {
    }

    #[Route(path: '/seo/page/list')]
    #[CanView]
    public function listAction(): Response
    {
        $grid = $this->seoPageGridFactory->create(Domain::FIRST_DOMAIN_ID);

        return $this->render('@ShopsysAdministration/content/seo/page/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    #[Route(path: '/seo/page/new')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $seoPageData = $this->seoPageDataFactory->create();

        $form = $this->createForm(SeoPageFormType::class, $seoPageData, [
            'seoPage' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->domain->hasAdminAllDomainsEnabled()) {
                $this->addErrorFlash(t('Creating a record requires all domains to be enabled as domain-specific fields cannot be empty. If you want to proceed, select all domains in the Domain filter in the header first.'));

                return $this->redirectToRoute('admin_seopage_new');
            }

            $seoPage = $this->seoPageFacade->create($seoPageData);

            $this
                ->addSuccessFlashTwig(
                    t('SEO Page <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                    [
                        'name' => $seoPage->getPageName(),
                        'url' => $this->generateUrl('admin_seopage_edit', ['id' => $seoPage->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_seopage_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/seo/page/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/seo/page/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(int $id, Request $request): Response
    {
        $seoPage = $this->seoPageFacade->getById($id);
        $seoPageData = $this->seoPageDataFactory->createFromSeoPage($seoPage);

        $form = $this->createForm(SeoPageFormType::class, $seoPageData, [
            'seoPage' => $seoPage,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->seoPageFacade->edit($id, $seoPageData);

            $this
                ->addSuccessFlashTwig(
                    t('SEO Page <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                    [
                        'name' => $seoPage->getPageName(),
                        'url' => $this->generateUrl('admin_seopage_edit', ['id' => $seoPage->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_seopage_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/seo/page/edit.html.twig', [
            'form' => $form->createView(),
            'seoPage' => $seoPage,
        ]);
    }

    #[Route(path: '/seo/page/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $seoPage = $this->seoPageFacade->getById($id);
            $this->seoPageFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('SEO Page <strong>{{ name }}</strong> removed'),
                [
                    'name' => $seoPage->getPageName(),
                ],
            );
        } catch (SeoPageNotFoundException) {
            $this->addErrorFlash(t('Selected SEO page does not exist'));
        } catch (DefaultSeoPageCannotBeDeletedException) {
            $this->addErrorFlash(t('Selected SEO page cannot be deleted'));
        }

        return $this->redirectToRoute('admin_seopage_list');
    }

    #[Route(path: '/seo/page/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteConfirmAction(int $id): Response
    {
        $message = t('Do you really want to remove this SEO page?');

        return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_seopage_delete', $id);
    }
}
