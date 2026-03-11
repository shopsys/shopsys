<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\PhonePrefix\PhonePrefixSettingsFormType;
use Shopsys\FrameworkBundle\Model\PhonePrefix\Settings\PhonePrefixSettingsFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_PHONE_PREFIX)]
class PhonePrefixController extends AdminBaseController
{
    public function __construct(
        protected readonly PhonePrefixSettingsFacade $phonePrefixSettingsFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    #[Route(path: '/phone-prefix/settings/', name: 'admin_phoneprefix_settings')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function settingsAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $phonePrefixFormData = $this->phonePrefixSettingsFacade->getByDomainId($domainId);

        $form = $this->createForm(
            PhonePrefixSettingsFormType::class,
            $phonePrefixFormData,
            [
                'domain_id' => $domainId,
            ],
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Shopsys\FrameworkBundle\Model\PhonePrefix\Settings\PhonePrefixSettingsData $phonePrefixFormData */
            $phonePrefixFormData = $form->getData();

            $this->phonePrefixSettingsFacade->edit($phonePrefixFormData, $domainId);

            $this->addSuccessFlash(t('Phone prefix settings modified.'));

            return $this->redirectToRoute('admin_phoneprefix_settings');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/phonePrefix/settings.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
