<?php

declare(strict_types=1);

namespace App\Form\Admin\Mail;

use App\Form\Admin\GrapesJsMailType;
use App\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Form\Admin\Mail\MailTemplateFormType;
use Shopsys\FrameworkBundle\Form\Constraints\Contains;
use Shopsys\FrameworkBundle\Form\Transformers\EmptyWysiwygTransformer;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class MailTemplateFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('body');

        $builder->add(
            $builder
                ->create('body', GrapesJsMailType::class, [
                    'label' => t('Content'),
                    'required' => true,
                    'constraints' => $this->getBodyConstraints($options),
                    'body_variables' => $options['body_variables'],
                ])
                ->addModelTransformer(new EmptyWysiwygTransformer()),
        );
    }

    /**
     * @param array $options
     * @return \Symfony\Component\Validator\Constraint[]
     */
    private function getBodyConstraints(array $options): array
    {
        $bodyConstraints = [];

        $bodyConstraints[] = new Constraints\NotBlank([
            'message' => 'Please enter email content',
            'groups' => [MailTemplateFormType::VALIDATION_GROUP_SEND_MAIL],
        ]);

        foreach ($options['required_body_variables'] as $variableName) {
            $bodyConstraints[] = new Contains([
                'needle' => $variableName,
                'message' => 'Variable {{ needle }} is required',
                'groups' => [MailTemplateFormType::VALIDATION_GROUP_SEND_MAIL],
            ]);
        }

        return $bodyConstraints;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['body_variables'])
            ->setAllowedTypes('entity', [MailTemplate::class, 'null'])
            ->setAllowedTypes('body_variables', 'array')
            ->setDefault('body_variables', []);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield MailTemplateFormType::class;
    }
}
