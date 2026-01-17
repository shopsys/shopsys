<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\UserConsentPolicy\UserConsentPolicySettingFormType;
use Shopsys\FrameworkBundle\Model\UserConsentPolicy\UserConsentPolicyFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_USER_CONSENT_POLICY)]
class UserConsentPolicyController extends AdminBaseController
{
    public function __construct(
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly UserConsentPolicyFacade $userConsentPolicyFacade,
    ) {
    }

    #[Route(path: '/user-consent-policy/setting/')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
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

        return $this->render('@ShopsysAdministration/content/userConsentPolicy/setting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
