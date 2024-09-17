<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\MailController as baseMailController;
use Shopsys\FrameworkBundle\Form\Admin\Mail\MailTemplateFormType;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateConfiguration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\Mail\MailTemplateFacade $mailTemplateFacade
 * @property \App\Model\Mail\Grid\MailTemplateGridFactory $mailTemplateGridFactory
 * @property \App\Model\Mail\MailTemplateDataFactory $mailTemplateDataFactory
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade, \App\Model\Mail\MailTemplateFacade $mailTemplateFacade, \App\Model\Mail\Setting\MailSettingFacade $mailSettingFacade, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \App\Model\Mail\Grid\MailTemplateGridFactory $mailTemplateGridFactory, \App\Model\Mail\MailTemplateConfiguration $mailTemplateConfiguration, \App\Model\Mail\MailTemplateDataFactory $mailTemplateDataFactory)
 * @property \App\Model\Mail\Setting\MailSettingFacade $mailSettingFacade
 * @method \App\Model\Administrator\Administrator getCurrentAdministrator()
 * @property \App\Model\Mail\MailTemplateConfiguration $mailTemplateConfiguration
 */
class MailController extends baseMailController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/mail/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $mailTemplate = $this->mailTemplateFacade->getById($id);
        $mailTemplateData = $this->mailTemplateDataFactory->createFromMailTemplate($mailTemplate);

        $mailTemplateVariables = $this->mailTemplateConfiguration->getMailTemplateVariablesBySlug(
            $mailTemplate->getName(),
        );

        $form = $this->createForm(
            MailTemplateFormType::class,
            $mailTemplateData,
            [
                'allow_disable_sending' => in_array(
                    $mailTemplateVariables->getType(),
                    MailTemplateConfiguration::TYPES_WITH_SEND_MAIL_SETTING,
                    true,
                ),
                'entity' => $mailTemplate,
                'required_subject_variables' => $mailTemplateVariables->getRequiredSubjectVariables(),
                'required_body_variables' => $mailTemplateVariables->getRequiredBodyVariables(),
                'body_variables' => $this->transformBodyVariables(
                    $mailTemplateVariables->getLabeledVariables(),
                    $mailTemplateVariables->getRequiredBodyVariables(),
                ),
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->mailTemplateFacade->edit($id, $mailTemplateData);

            $this->addSuccessFlashTwig(
                t('Email template <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $mailTemplateVariables->getReadableName(),
                    'url' => $this->generateUrl('admin_mail_edit', ['id' => $mailTemplate->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_mail_template');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing email template - %name%', ['%name%' => $mailTemplateVariables->getReadableName()]),
        );

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
     * @param string[] $variables
     * @param string[] $requiredVariables
     * @return array<int, array<string, bool|int|string>>
     */
    private function transformBodyVariables(array $variables, array $requiredVariables): array
    {
        $transformedVariables = [];

        foreach ($variables as $placeholder => $label) {
            $transformedVariables[] = [
                'label' => $label,
                'placeholder' => $placeholder,
                'isRequired' => in_array($placeholder, $requiredVariables, true),
            ];
        }

        return $transformedVariables;
    }
}
