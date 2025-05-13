<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\LegalConditions\PrivacyPolicySettingFormType;
use Shopsys\FrameworkBundle\Form\Admin\LegalConditions\TermsAndConditionsSettingFormType;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrameworkBundle\Model\Security\AccessControl\AccessControlRule;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalConditionsController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     */
    public function __construct(
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly LegalConditionsFacade $legalConditionsFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/legal-conditions/setting/')]
    #[AccessControlRule([Roles::ROLE_LEGAL_CONDITIONS_FULL], ['POST'])]
    #[AccessControlRule([Roles::ROLE_LEGAL_CONDITIONS_VIEW], ['GET'])]
    public function termsAndConditionsAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $settingData = [
            'termsAndConditionsArticle' => $this->legalConditionsFacade->findTermsAndConditions($domainId),
        ];

        $form = $this->createForm(TermsAndConditionsSettingFormType::class, $settingData, [
            'domain_id' => $domainId,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $this->legalConditionsFacade->setTermsAndConditions($domainId, $formData['termsAndConditionsArticle']);

            $this->addSuccessFlashTwig(t('Legal conditions settings modified.'));

            return $this->redirectToRoute('admin_legalconditions_termsandconditions');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/LegalConditions/termsAndConditions.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/legal-conditions/privacy-policy/')]
    #[AccessControlRule([Roles::ROLE_PRIVACY_POLICY_FULL], ['POST'])]
    #[AccessControlRule([Roles::ROLE_PRIVACY_POLICY_VIEW], ['GET'])]
    public function privacyPolicyAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $settingData = [
            'privacyPolicyArticle' => $this->legalConditionsFacade->findPrivacyPolicy($domainId),
        ];

        $form = $this->createForm(PrivacyPolicySettingFormType::class, $settingData, [
            'domain_id' => $domainId,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $this->legalConditionsFacade->setPrivacyPolicy($domainId, $formData['privacyPolicyArticle']);

            $this->addSuccessFlashTwig(t('Legal conditions settings modified.'));

            return $this->redirectToRoute('admin_legalconditions_privacypolicy');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/LegalConditions/privacyPolicy.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
