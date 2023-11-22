<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoRobotsSettingFormType;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoSettingFormType;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeoController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly SeoSettingFacade $seoSettingFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @Route("/seo/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $seoSettingData = [
            'title' => $this->seoSettingFacade->getTitleMainPage($domainId),
            'metaDescription' => $this->seoSettingFacade->getDescriptionMainPage($domainId),
            'titleAddOn' => $this->seoSettingFacade->getTitleAddOn($domainId),
        ];

        $form = $this->createForm(SeoSettingFormType::class, $seoSettingData, ['domain_id' => $domainId]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $seoSettingData = $form->getData();

            $this->seoSettingFacade->setTitleMainPage($seoSettingData['title'], $domainId);
            $this->seoSettingFacade->setDescriptionMainPage($seoSettingData['metaDescription'], $domainId);
            $this->seoSettingFacade->setTitleAddOn($seoSettingData['titleAddOn'], $domainId);

            $this->addSuccessFlash(t('SEO attributes settings modified'));

            return $this->redirectToRoute('admin_seo_index');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Seo/seoSetting.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/seo/robots/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function robotsAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $seoRobotsSettingData = ['content' => $this->seoSettingFacade->getRobotsTxtContent($domainId)];
        $form = $this->createForm(SeoRobotsSettingFormType::class, $seoRobotsSettingData)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $seoRobotsSettingData = $form->getData();

            $this->seoSettingFacade->setRobotsTxtContent($seoRobotsSettingData['content'], $domainId);

            $this->addSuccessFlash(t('Robots.txt settings modified'));

            return $this->redirectToRoute('admin_seo_robots');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Seo/robotsSetting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
