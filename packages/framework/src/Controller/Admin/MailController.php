<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\Mail\MailSettingFormType;
use Shopsys\FrameworkBundle\Form\Admin\Mail\MailTemplateFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Mail\Grid\MailTemplateGridFactory;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateConfiguration;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactory;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AdminBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    protected $adminDomainTabsFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade
     */
    protected $mailTemplateFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade
     */
    protected $mailSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider
     */
    protected $breadcrumbOverrider;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\Grid\MailTemplateGridFactory
     */
    protected $mailTemplateGridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateConfiguration
     */
    protected $mailTemplateConfiguration;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactory
     */
    protected $mailTemplateDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade $mailSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Mail\Grid\MailTemplateGridFactory $mailTemplateGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateConfiguration $mailTemplateConfiguration
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactory $mailTemplateDataFactory
     */
    public function __construct(
        AdminDomainTabsFacade $adminDomainTabsFacade,
        MailTemplateFacade $mailTemplateFacade,
        MailSettingFacade $mailSettingFacade,
        BreadcrumbOverrider $breadcrumbOverrider,
        MailTemplateGridFactory $mailTemplateGridFactory,
        MailTemplateConfiguration $mailTemplateConfiguration,
        MailTemplateDataFactory $mailTemplateDataFactory
    ) {
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
        $this->mailTemplateFacade = $mailTemplateFacade;
        $this->mailSettingFacade = $mailSettingFacade;
        $this->breadcrumbOverrider = $breadcrumbOverrider;
        $this->mailTemplateGridFactory = $mailTemplateGridFactory;
        $this->mailTemplateConfiguration = $mailTemplateConfiguration;
        $this->mailTemplateDataFactory = $mailTemplateDataFactory;
    }

    /**
     * @Route("/mail/template/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function templateAction(): Response
    {
        $grid = $this->mailTemplateGridFactory->create();

        return $this->render('@ShopsysFramework/Admin/Content/Mail/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/mail/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $mailTemplate = $this->mailTemplateFacade->getById($id);
        $mailTemplateData = $this->mailTemplateDataFactory->createFromMailTemplate($mailTemplate);

        $mailTemplateVariables = $this->mailTemplateConfiguration->getMailTemplateVariablesBySlug($mailTemplate->getName());

        $form = $this->createForm(
            MailTemplateFormType::class,
            $mailTemplateData,
            [
                'allow_disable_sending' => ($mailTemplateVariables->getType() === MailTemplateConfiguration::TYPE_ORDER_STATUS),
                'entity' => $mailTemplate,
                'required_subject_variables' => $mailTemplateVariables->getRequiredSubjectVariables(),
                'required_body_variables' => $mailTemplateVariables->getRequiredBodyVariables(),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->mailTemplateFacade->edit($id, $mailTemplateData);

            $this->addSuccessFlashTwig(
                t('Email template <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $mailTemplateVariables->getReadableName(),
                    'url' => $this->generateUrl('admin_mail_edit', ['id' => $mailTemplate->getId()]),
                ]
            );

            return $this->redirectToRoute('admin_mail_template');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing email template - %name%', ['%name%' => $mailTemplateVariables->getReadableName()]));

        return $this->render('@ShopsysFramework/Admin/Content/Mail/edit.html.twig', [
            'form' => $form->createView(),
            'mailTemplateName' => $mailTemplateVariables->getReadableName(),
            'bodyVariables' => $mailTemplateVariables->getBodyVariables(),
            'subjectVariables' => $mailTemplateVariables->getSubjectVariables(),
            'requiredBodyVariables' => $mailTemplateVariables->getRequiredBodyVariables(),
            'requiredSubjectVariables' => $mailTemplateVariables->getRequiredSubjectVariables(),
            'labeledVariables' => $mailTemplateVariables->getLabeledVariables(),
            'entity' => $mailTemplate,
        ]);
    }

    /**
     * @Route("/mail/setting/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function settingAction(Request $request)
    {
        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $mailSettingData = [
            'email' => $this->mailSettingFacade->getMainAdminMail($selectedDomainId),
            'name' => $this->mailSettingFacade->getMainAdminMailName($selectedDomainId),
        ];

        $form = $this->createForm(MailSettingFormType::class, $mailSettingData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mailSettingData = $form->getData();

            $this->mailSettingFacade->setMainAdminMail($mailSettingData['email'], $selectedDomainId);
            $this->mailSettingFacade->setMainAdminMailName($mailSettingData['name'], $selectedDomainId);

            $this->addSuccessFlash(t('Email settings modified.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Mail/setting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
