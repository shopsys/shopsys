<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Mail;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Component\ContentEditor\Validation\ContentDocumentContainsVariable;
use Shopsys\FrameworkBundle\Component\ContentEditor\Validation\ContentDocumentNotEmpty;
use Shopsys\FrameworkBundle\Form\Constraints\Contains;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\FileUploadType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class MailTemplateFormType extends AbstractType
{
    public const string VALIDATION_GROUP_SEND_MAIL = 'sendMail';

    private const string IMAGE_VARIABLE_BLOCK_TYPE = 'mail-image-variable';

    // The framework must not depend on the administration bundle, so the type is referenced by name only.
    private const string CONTENT_EDITOR_TYPE = 'Shopsys\AdministrationBundle\Form\Admin\Type\ContentEditorType';

    public function __construct(
        private readonly ContentBlockRegistry $contentBlockRegistry,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate */
        $mailTemplate = $options['entity'];

        $builder
            ->add('subject', TextType::class, [
                'label' => 'Subject',
                'required' => true,
                'constraints' => $this->getSubjectConstraints($options),
                'label_attr' => [
                    'class' => 'js-conditional-field',
                ],
            ])
            ->add('bccEmail', EmailType::class, [
                'label' => 'Hidden copy',
                'required' => false,
                'constraints' => [
                    new Email(),
                    new Constraints\Length(
                        max: 255,
                        maxMessage: 'Email cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('content', self::CONTENT_EDITOR_TYPE, [
                'label' => 'Content',
                'required' => false,
                'profile' => ContentProfileEnum::MAIL,
                'variables' => $options['body_variables'],
                'domain_id' => $mailTemplate->getDomainId(),
                'allowed_types' => $this->getAllowedBlockTypes($options['body_variables']),
                'constraints' => $this->getContentConstraints($options),
                'label_attr' => [
                    'class' => 'js-conditional-field',
                ],
            ])
            ->add('attachments', FileUploadType::class, [
                'label' => 'Upload attachment',
                'required' => false,
                'file_constraints' => [
                    new Constraints\File(
                        maxSize: '2M',
                        maxSizeMessage: 'Uploaded file is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an file is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'entity' => $mailTemplate,
                'file_entity_class' => MailTemplate::class,
            ]);

        if ($options['allow_disable_sending']) {
            $builder->add('sendMail', CheckboxType::class, [
                'label' => 'Send email about change to this status',
                'attr' => ['class' => 'js-send-mail-checkbox'],
                'required' => false,
            ]);
        }

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_mail_template',
            'entity' => $options['entity'],
            'apply' => true,
        ]);
    }

    /**
     * @return \Symfony\Component\Validator\Constraint[]
     */
    private function getSubjectConstraints(array $options): array
    {
        $subjectConstraints = [];

        $subjectConstraints[] = new Constraints\NotBlank(
            message: 'Please enter subject',
            groups: [static::VALIDATION_GROUP_SEND_MAIL],
        );
        $subjectConstraints[] = new Constraints\Length(
            max: 255,
            maxMessage: 'Email subject cannot be longer than {{ limit }} characters',
        );

        foreach ($options['required_subject_variables'] as $variableName) {
            $subjectConstraints[] = new Contains(
                needle: $variableName,
                message: 'Variable {{ needle }} is required',
                groups: [static::VALIDATION_GROUP_SEND_MAIL],
            );
        }

        return $subjectConstraints;
    }

    /**
     * @return \Symfony\Component\Validator\Constraint[]
     */
    private function getContentConstraints(array $options): array
    {
        $contentConstraints = [];

        $contentConstraints[] = new ContentDocumentNotEmpty(
            message: 'Please enter email content',
            groups: [static::VALIDATION_GROUP_SEND_MAIL],
        );

        foreach ($options['required_body_variables'] as $variableName) {
            $contentConstraints[] = new ContentDocumentContainsVariable(
                variable: $variableName,
                groups: [static::VALIDATION_GROUP_SEND_MAIL],
            );
        }

        return $contentConstraints;
    }

    /**
     * The block with an image from a variable is useful only in templates having such a variable (e.g. the product image of the watchdog)
     *
     * @param array<int, array{label: string, placeholder: string, isRequired: bool, isImage: bool}> $bodyVariables
     * @return string[]|null
     */
    private function getAllowedBlockTypes(array $bodyVariables): ?array
    {
        if (in_array(true, array_column($bodyVariables, 'isImage'), true)) {
            return null;
        }

        return array_values(array_diff(
            array_keys($this->contentBlockRegistry->getForProfile(ContentProfileEnum::MAIL)),
            [self::IMAGE_VARIABLE_BLOCK_TYPE],
        ));
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['required_subject_variables', 'required_body_variables', 'entity', 'allow_disable_sending', 'body_variables'])
            ->setAllowedTypes('required_subject_variables', 'array')
            ->setAllowedTypes('required_body_variables', 'array')
            ->setAllowedTypes('entity', MailTemplate::class)
            ->setAllowedTypes('allow_disable_sending', 'boolean')
            ->setAllowedTypes('body_variables', 'array')
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
                'body_variables' => [],
            ]);
    }
}
