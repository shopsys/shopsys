<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Mail\Exception\MailTemplateAlreadyExistsException;
use App\Model\Mail\MailTemplate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\FrameworkBundle\Controller\Admin\MailController as baseMailController;
use Shopsys\FrameworkBundle\Form\Admin\Mail\MailTemplateFormType;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateConfiguration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property \App\Model\Mail\MailTemplateFacade $mailTemplateFacade
 * @property \App\Model\Mail\Grid\MailTemplateGridFactory $mailTemplateGridFactory
 * @property \App\Model\Mail\MailTemplateDataFactory $mailTemplateDataFactory
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade, \App\Model\Mail\MailTemplateFacade $mailTemplateFacade, \Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade $mailSettingFacade, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \App\Model\Mail\Grid\MailTemplateGridFactory $mailTemplateGridFactory, \Shopsys\FrameworkBundle\Model\Mail\MailTemplateConfiguration $mailTemplateConfiguration, \App\Model\Mail\MailTemplateDataFactory $mailTemplateDataFactory)
 */
class MailController extends baseMailController
{
    /**
     * @Route("/mail/create", requirements={"id" = "\d+"})
     */
    public function createTemplateAction(Request $request): Response
    {
        $mailTemplateData = $this->mailTemplateDataFactory->create();
        $mailTemplateData->domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $mailTemplateVariables = $this->mailTemplateConfiguration->getMailTemplateVariablesBySlug(MailTemplate::ORDER_STOCK_STATUS_NAME);

        $form = $this->createForm(
            MailTemplateFormType::class,
            $mailTemplateData,
            [
                'allow_disable_sending' => ($mailTemplateVariables->getType() === MailTemplateConfiguration::TYPE_ORDER_STATUS),
                'entity' => null,
                'required_subject_variables' => $mailTemplateVariables->getRequiredSubjectVariables(),
                'required_body_variables' => $mailTemplateVariables->getRequiredBodyVariables(),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $mailTemplate = $this->mailTemplateFacade->createOrderStockStatusTemplate($mailTemplateData);

                $this->addSuccessFlashTwig(
                    t('Emailová šablona <strong><a href="{{ url }}">{{ name }}</a></strong> byla vytvořena.'),
                    [
                        'name' => $mailTemplateVariables->getReadableName(),
                        'url' => $this->generateUrl('admin_mail_edit', ['id' => $mailTemplate->getId()]),
                    ]
                );

                return $this->redirectToRoute('admin_mail_template');
            } catch (MailTemplateAlreadyExistsException $exception) {
                $this->addErrorFlashTwig(
                    t('Pro tuto kombinaci již <a href="{{ url }}">existuje emailová šablona</a>.'),
                    [
                        'name' => $mailTemplateVariables->getReadableName(),
                        'url' => $this->generateUrl('admin_mail_edit', ['id' => $exception->getMailTemplate()->getId()]),
                    ]
                );
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Vytvoření email template - %name%', ['%name%' => $mailTemplateVariables->getReadableName()]));

        return $this->render('Admin/Content/Mail/new.html.twig', [
            'form' => $form->createView(),
            'mailTemplateName' => $mailTemplateVariables->getReadableName(),
            'bodyVariables' => $mailTemplateVariables->getBodyVariables(),
            'subjectVariables' => $mailTemplateVariables->getSubjectVariables(),
            'requiredBodyVariables' => $mailTemplateVariables->getRequiredBodyVariables(),
            'requiredSubjectVariables' => $mailTemplateVariables->getRequiredSubjectVariables(),
            'labeledVariables' => $mailTemplateVariables->getLabeledVariables(),
        ]);
    }
}
