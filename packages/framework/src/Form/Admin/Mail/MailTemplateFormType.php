<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Mail;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FrameworkBundle\Form\Constraints\Contains;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\FileUploadType;
use Shopsys\FrameworkBundle\Form\Transformers\EmptyWysiwygTransformer;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class MailTemplateFormType extends AbstractType
{
    public const VALIDATION_GROUP_SEND_MAIL = 'sendMail';

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => t('Subject'),
                'required' => true,
                'constraints' => $this->getSubjectConstraints($options),
            ])
            ->add('bccEmail', EmailType::class, [
                'label' => t('Hidden copy'),
                'required' => false,
                'constraints' => [
                    new Email(),
                    new Constraints\Length(
                        ['max' => 255, 'maxMessage' => 'Email cannot be longer than {{ limit }} characters'],
                    ),
                ],
            ])
            ->add(
                $builder
                    ->create('body', CKEditorType::class, [
                        'label' => t('Content'),
                        'required' => true,
                        'config_name' => 'email',
                        'constraints' => $this->getBodyConstraints($options),
                    ])
                    ->addModelTransformer(new EmptyWysiwygTransformer()),
            )
            ->add('attachments', FileUploadType::class, [
                'label' => t('Upload attachment'),
                'required' => false,
                'file_constraints' => [
                    new Constraints\File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded file is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an file is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'entity' => $options['entity'],
                'file_entity_class' => MailTemplate::class,
            ]);

        if ($options['allow_disable_sending']) {
            $builder->add('sendMail', CheckboxType::class, [
                'label' => t('Send email about change to this status'),
                'attr' => ['class' => 'js-send-mail-checkbox'],
                'required' => false,
            ]);
        }

        $builder->add('save', SubmitType::class);
    }

    /**
     * @param array $options
     * @return \Symfony\Component\Validator\Constraint[]
     */
    private function getSubjectConstraints(array $options)
    {
        $subjectConstraints = [];

        $subjectConstraints[] = new Constraints\NotBlank([
            'message' => 'Please enter subject',
            'groups' => [static::VALIDATION_GROUP_SEND_MAIL],
        ]);
        $subjectConstraints[] = new Constraints\Length([
            'max' => 255,
            'maxMessage' => 'Email subject cannot be longer than {{ limit }} characters',
        ]);

        foreach ($options['required_subject_variables'] as $variableName) {
            $subjectConstraints[] = new Contains([
                'needle' => $variableName,
                'message' => 'Variable {{ needle }} is required',
                'groups' => [static::VALIDATION_GROUP_SEND_MAIL],
            ]);
        }

        return $subjectConstraints;
    }

    /**
     * @param array $options
     * @return \Symfony\Component\Validator\Constraint[]
     */
    private function getBodyConstraints(array $options)
    {
        $bodyConstraints = [];

        $bodyConstraints[] = new Constraints\NotBlank([
            'message' => 'Please enter email content',
            'groups' => [static::VALIDATION_GROUP_SEND_MAIL],
        ]);

        foreach ($options['required_body_variables'] as $variableName) {
            $bodyConstraints[] = new Contains([
                'needle' => $variableName,
                'message' => 'Variable {{ needle }} is required',
                'groups' => [static::VALIDATION_GROUP_SEND_MAIL],
            ]);
        }

        return $bodyConstraints;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['required_subject_variables', 'required_body_variables', 'entity', 'allow_disable_sending'])
            ->setAllowedTypes('required_subject_variables', 'array')
            ->setAllowedTypes('required_body_variables', 'array')
            ->setAllowedTypes('entity', MailTemplate::class)
            ->setAllowedTypes('allow_disable_sending', 'boolean')
            ->setDefaults([
                'required_subject_variables' => [],
                'required_body_variables' => [],
                'allow_disable_sending' => false,
                'data_class' => MailTemplateData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    /** @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateData $mailTemplateData */
                    $mailTemplateData = $form->getData();

                    if ($mailTemplateData->sendMail) {
                        $validationGroups[] = static::VALIDATION_GROUP_SEND_MAIL;
                    }

                    return $validationGroups;
                },
            ]);
    }
}
