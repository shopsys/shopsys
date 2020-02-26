<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\ContactForm\ContactFormSettingsFormType;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ContactFormSettingsController extends AdminBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    protected $adminDomainTabsFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsDataFactoryInterface
     */
    protected $contactFormSettingsDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade
     */
    protected $contactFormSettingsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsDataFactoryInterface $contactFormSettingsDataFactory
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade $contactFormSettingsFacade
     */
    public function __construct(
        AdminDomainTabsFacade $adminDomainTabsFacade,
        ContactFormSettingsDataFactoryInterface $contactFormSettingsDataFactory,
        ContactFormSettingsFacade $contactFormSettingsFacade
    ) {
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
        $this->contactFormSettingsDataFactory = $contactFormSettingsDataFactory;
        $this->contactFormSettingsFacade = $contactFormSettingsFacade;
    }

    /**
     * @Route("/contact-form/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function indexAction(Request $request)
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
