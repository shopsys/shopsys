<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Form\Admin\ContactForm\ContactFormSettingsFormType;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsDataFactory;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole('ROLE_CONTACT_FORM')]
class ContactFormSettingsController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsDataFactory $contactFormSettingsDataFactory
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade $contactFormSettingsFacade
     */
    public function __construct(
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly ContactFormSettingsDataFactory $contactFormSettingsDataFactory,
        protected readonly ContactFormSettingsFacade $contactFormSettingsFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/contact-form/')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function indexAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $contactFormSettingsData = $this->contactFormSettingsDataFactory->createFromSettingsByDomainId($domainId);

        $form = $this->createForm(ContactFormSettingsFormType::class, $contactFormSettingsData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->contactFormSettingsFacade->editSettingsForDomain($contactFormSettingsData, $domainId);

            $this->addSuccessFlash(t('Contact form settings modified'));

            return $this->redirectToRoute('admin_contactformsettings_index');
        }

        return $this->render('@ShopsysFramework/Admin/Content/ContactFormSettings/contactFormSettings.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
