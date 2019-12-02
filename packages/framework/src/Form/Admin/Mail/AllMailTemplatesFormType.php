<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Mail;

use Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMail;
use Shopsys\FrameworkBundle\Model\Mail\AllMailTemplatesData;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateData;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMail;
use Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataExportMail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AllMailTemplatesFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMail
     */
    private $resetPasswordMail;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMail
     */
    private $personalDataAccessMail;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataExportMail
     */
    private $personalDataExportMail;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade
     */
    private $mailTemplateFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMail $resetPasswordMail
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMail $personalDataAccessMail
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataExportMail $personalDataExportMail
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     */
    public function __construct(
        ResetPasswordMail $resetPasswordMail,
        PersonalDataAccessMail $personalDataAccessMail,
        PersonalDataExportMail $personalDataExportMail,
        MailTemplateFacade $mailTemplateFacade
    ) {
        $this->resetPasswordMail = $resetPasswordMail;
        $this->personalDataAccessMail = $personalDataAccessMail;
        $this->personalDataExportMail = $personalDataExportMail;
        $this->mailTemplateFacade = $mailTemplateFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Mail\AllMailTemplatesData $data */
        $data = $options['data'];
        $domainId = $data->domainId;

        $builder
            ->add('registrationTemplate', MailTemplateFormType::class, [
                'entity' => $this->getMailTemplate($data->registrationTemplate, $domainId),
            ])
            ->add('personalDataAccessTemplate', MailTemplateFormType::class, [
                'required_body_variables' => $this->personalDataAccessMail->getRequiredBodyVariables(),
                'entity' => $this->getMailTemplate($data->personalDataAccessTemplate, $domainId),
            ])
            ->add('personalDataExportTemplate', MailTemplateFormType::class, [
                'required_body_variables' => $this->personalDataExportMail->getRequiredBodyVariables(),
                'entity' => $this->getMailTemplate($data->personalDataExportTemplate, $domainId),
            ])
            ->add('resetPasswordTemplate', MailTemplateFormType::class, [
                'required_subject_variables' => $this->resetPasswordMail->getRequiredSubjectVariables(),
                'required_body_variables' => $this->resetPasswordMail->getRequiredBodyVariables(),
                'entity' => $this->getMailTemplate($data->resetPasswordTemplate, $domainId),
            ]);

        $orderStatusTemplatesBuilder = $builder->create('orderStatusTemplates', FormType::class);

        foreach ($data->orderStatusTemplates as $orderStatusId => $orderStatusTemplate) {
            $orderStatusTemplatesBuilder->add($orderStatusId, MailTemplateFormType::class, [
                'entity' => $this->getMailTemplate($orderStatusTemplate, $domainId),
            ]);
        }

        $builder
            ->add($orderStatusTemplatesBuilder)
            ->add('domainId', HiddenType::class)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
            'data_class' => AllMailTemplatesData::class,
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateData $mailTemplateData
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate
     */
    private function getMailTemplate(MailTemplateData $mailTemplateData, int $domainId): MailTemplate
    {
        return $this->mailTemplateFacade->get($mailTemplateData->name, $domainId);
    }
}
