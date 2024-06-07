<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\LegalConditionsController as BaseLegalConditionsController;
use Shopsys\FrameworkBundle\Form\Admin\LegalConditions\LegalConditionsSettingFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade, \App\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade)
 * @method \App\Model\Administrator\Administrator getCurrentAdministrator()
 */
class LegalConditionsController extends BaseLegalConditionsController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Route(path: '/legal-conditions/setting/')]
    public function termsAndConditionsAction(Request $request)
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $settingData = [
            'termsAndConditionsArticle' => $this->legalConditionsFacade->findTermsAndConditions($domainId),
        ];

        $form = $this->createForm(LegalConditionsSettingFormType::class, $settingData, [
            'domain_id' => $domainId,
        ]);
        $legalConditionSettingForm = $form->get('settings');
        $legalConditionSettingForm->remove('privacyPolicyArticle');

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

        return $this->render('Admin/Content/LegalConditions/termsAndConditions.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Route(path: '/legal-conditions/privacy-policy/')]
    public function privacyPolicy(Request $request)
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $settingData = [
            'privacyPolicyArticle' => $this->legalConditionsFacade->findPrivacyPolicy($domainId),
        ];

        $form = $this->createForm(LegalConditionsSettingFormType::class, $settingData, [
            'domain_id' => $domainId,
        ]);
        $legalConditionSettingForm = $form->get('settings');
        $legalConditionSettingForm->remove('termsAndConditionsArticle');

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

        return $this->render('Admin/Content/LegalConditions/privacyPolicy.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
