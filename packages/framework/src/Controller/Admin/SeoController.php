<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Seo\HreflangSettingFormType;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoRobotsSettingFormType;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoSettingFormType;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SeoController extends AdminBaseController
{
    public function __construct(
        protected readonly SeoSettingFacade $seoSettingFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    #[Route(path: '/seo/')]
    #[CanView(AdminRoleConstant::ROLE_SEO, methods: [HttpMethod::GET])]
    #[CanEdit(AdminRoleConstant::ROLE_SEO, methods: [HttpMethod::POST])]
    public function indexAction(Request $request): Response
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

        return $this->render('@ShopsysAdministration/content/seo/seoSetting.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/seo/robots/')]
    #[CanView(AdminRoleConstant::ROLE_ROBOTS, methods: [HttpMethod::GET])]
    #[CanEdit(AdminRoleConstant::ROLE_ROBOTS, methods: [HttpMethod::POST])]
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

        return $this->render('@ShopsysAdministration/content/seo/robotsSetting.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/seo/hreflang/')]
    #[CanView(AdminRoleConstant::ROLE_HREFLANG, methods: [HttpMethod::GET])]
    #[CanEdit(AdminRoleConstant::ROLE_HREFLANG, methods: [HttpMethod::POST])]
    public function hreflangAction(Request $request): Response
    {
        $hreflangData = [
            HreflangSettingFormType::FIELD_HREFLANG_COLLECTION => $this->seoSettingFacade->getAllAlternativeDomains(),
        ];

        $form = $this->createForm(HreflangSettingFormType::class, $hreflangData)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hreflangData = $form->getData();

            $this->seoSettingFacade->setAllAlternativeDomains(
                $hreflangData[HreflangSettingFormType::FIELD_HREFLANG_COLLECTION],
            );

            $this->addSuccessFlash(t('Alternate language settings modified'));

            return $this->redirectToRoute('admin_seo_hreflang');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/seo/hreflangSetting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
