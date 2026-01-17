<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\LegalConditions\PrivacyPolicySettingFormType;
use Shopsys\FrameworkBundle\Form\Admin\LegalConditions\TermsAndConditionsSettingFormType;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalSetting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalConditionsController extends AdminBaseController
{
    public function __construct(
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly LegalConditionsFacade $legalConditionsFacade,
        protected readonly WithdrawalSetting $withdrawalSetting,
    ) {
    }

    #[Route(path: '/legal-conditions/setting/')]
    #[CanEdit(AdminRoleConstant::ROLE_LEGAL_CONDITIONS, methods: [HttpMethod::POST])]
    #[CanView(AdminRoleConstant::ROLE_LEGAL_CONDITIONS, methods: [HttpMethod::GET])]
    public function termsAndConditionsAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $settingData = [
            'termsAndConditionsArticle' => $this->legalConditionsFacade->findTermsAndConditions($domainId),
            'withdrawalDeadlineDays' => $this->withdrawalSetting->getDeadlineDays($domainId),
            'withdrawalInstructions' => $this->withdrawalSetting->getInstructions($domainId),
        ];

        $form = $this->createForm(TermsAndConditionsSettingFormType::class, $settingData, [
            'domain_id' => $domainId,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $this->legalConditionsFacade->setTermsAndConditions($domainId, $formData['termsAndConditionsArticle']);

            $this->withdrawalSetting->setDeadlineDays($formData['withdrawalDeadlineDays'], $domainId);
            $this->withdrawalSetting->setInstructions($formData['withdrawalInstructions'], $domainId);

            $this->addSuccessFlashTwig(t('Legal conditions settings modified.'));

            return $this->redirectToRoute('admin_legalconditions_termsandconditions');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/legalConditions/termsAndConditions.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/legal-conditions/privacy-policy/')]
    #[CanEdit(AdminRoleConstant::ROLE_PRIVACY_POLICY, methods: [HttpMethod::POST])]
    #[CanView(AdminRoleConstant::ROLE_PRIVACY_POLICY, methods: [HttpMethod::GET])]
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

        return $this->render('@ShopsysAdministration/content/legalConditions/privacyPolicy.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
