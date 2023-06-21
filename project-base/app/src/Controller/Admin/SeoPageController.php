<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\SeoPageFormType;
use App\Model\SeoPage\Exception\DefaultSeoPageCannotBeDeletedException;
use App\Model\SeoPage\Exception\SeoPageNotFoundException;
use App\Model\SeoPage\SeoPageDataFactory;
use App\Model\SeoPage\SeoPageFacade;
use App\Model\SeoPage\SeoPageGridFactory;
use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeoPageController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\SeoPage\SeoPageGridFactory $seoPageGridFactory
     * @param \App\Model\SeoPage\SeoPageDataFactory $seoPageDataFactory
     * @param \App\Model\SeoPage\SeoPageFacade $seoPageFacade
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly SeoPageGridFactory $seoPageGridFactory,
        private readonly SeoPageDataFactory $seoPageDataFactory,
        private readonly SeoPageFacade $seoPageFacade,
        private readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
    ) {
    }

    /**
     * @Route("/seo/page/list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $grid = $this->seoPageGridFactory->create($this->domain->getId());

        return $this->render('Admin/Content/SeoPage/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/seo/page/new")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

        return $this->render('Admin/Content/SeoPage/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/seo/page/edit/{id}", requirements={"id" = "\d+"})
     * @param int $id
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

        return $this->render('Admin/Content/SeoPage/edit.html.twig', [
            'form' => $form->createView(),
            'seoPage' => $seoPage,
        ]);
    }

    /**
     * @Route("/seo/page/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
     * @Route("/seo/page/delete-confirm/{id}", requirements={"id" = "\d+"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteConfirmAction(int $id): Response
    {
        $message = t('Do you really want to remove this SEO page?');

        return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_seopage_delete', $id);
    }
}
