<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoPageFormType;
use Shopsys\FrameworkBundle\Model\Seo\Page\Exception\DefaultSeoPageCannotBeDeletedException;
use Shopsys\FrameworkBundle\Model\Seo\Page\Exception\SeoPageNotFoundException;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageGridFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeoPageController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageGridFactory $seoPageGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDataFactory $seoPageDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade $seoPageFacade
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly SeoPageGridFactory $seoPageGridFactory,
        protected readonly SeoPageDataFactory $seoPageDataFactory,
        protected readonly SeoPageFacade $seoPageFacade,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/seo/page/list')]
    public function listAction(): Response
    {
        $grid = $this->seoPageGridFactory->create($this->domain->getId());

        return $this->render('@ShopsysFramework/Admin/Content/Seo/Page/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/seo/page/new')]
    public function newAction(Request $request): Response
    {
        $seoPageData = $this->seoPageDataFactory->create();

        $form = $this->createForm(SeoPageFormType::class, $seoPageData, [
            'seoPage' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

        return $this->render('@ShopsysFramework/Admin/Content/Seo/Page/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param int $id
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/seo/page/edit/{id}', requirements: ['id' => '\d+'])]
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

        return $this->render('@ShopsysFramework/Admin/Content/Seo/Page/edit.html.twig', [
            'form' => $form->createView(),
            'seoPage' => $seoPage,
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/seo/page/delete/{id}', requirements: ['id' => '\d+'])]
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

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/seo/page/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    public function deleteConfirmAction(int $id): Response
    {
        $message = t('Do you really want to remove this SEO page?');

        return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_seopage_delete', $id);
    }
}
