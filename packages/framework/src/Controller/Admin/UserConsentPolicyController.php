<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\UserConsentPolicy\UserConsentPolicySettingFormType;
use Shopsys\FrameworkBundle\Model\UserConsentPolicy\UserConsentPolicyFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserConsentPolicyController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\UserConsentPolicy\UserConsentPolicyFacade $userConsentPolicyFacade
     */
    public function __construct(
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly UserConsentPolicyFacade $userConsentPolicyFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/user-consent-policy/setting/')]
    public function settingAction(Request $request): Response
    {
        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $userConsentPolicyArticle = $this->userConsentPolicyFacade->findUserConsentPolicyArticleByDomainId($selectedDomainId);

        $form = $this->createForm(
            UserConsentPolicySettingFormType::class,
            [UserConsentPolicySettingFormType::USER_CONSENT_POLICY_ARTICLE_FIELD_NAME => $userConsentPolicyArticle],
            ['domain_id' => $selectedDomainId],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userConsentPolicyArticle = $form->getData()[UserConsentPolicySettingFormType::USER_CONSENT_POLICY_ARTICLE_FIELD_NAME];

            $this->userConsentPolicyFacade->setUserConsentPolicyArticleOnDomain(
                $userConsentPolicyArticle,
                $selectedDomainId,
            );

            $this->addSuccessFlashTwig(t('User consent policy settings modified.'));

            return $this->redirectToRoute('admin_userconsentpolicy_setting');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/UserConsentPolicy/setting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
