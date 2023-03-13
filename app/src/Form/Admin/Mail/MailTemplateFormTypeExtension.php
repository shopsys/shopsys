<?php

declare(strict_types=1);

namespace App\Form\Admin\Mail;

use App\Form\Admin\GrapesJsMailType;
use App\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Form\Admin\Mail\MailTemplateFormType;
use Shopsys\FrameworkBundle\Form\Constraints\Contains;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\Transformers\EmptyWysiwygTransformer;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\NotBlank;

class MailTemplateFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade
     */
    private OrderStatusFacade $orderStatusFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade
     */
    public function __construct(
        OrderStatusFacade $orderStatusFacade
    ) {
        $this->orderStatusFacade = $orderStatusFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \App\Model\Mail\MailTemplate|null $mailTemplate */
        $mailTemplate = $options['entity'];
        $isOrderStatusTemplate = $mailTemplate === null || $mailTemplate->getName() === MailTemplate::ORDER_STATUS_NAME;

        $builder->remove('body');

        $builder->add(
            $builder
                ->create('body', GrapesJsMailType::class, [
                    'label' => t('Content'),
                    'required' => true,
                    'constraints' => $this->getBodyConstraints($options),
                    'body_variables' => $options['body_variables'],
                ])
                ->addModelTransformer(new EmptyWysiwygTransformer())
        );

        if ($mailTemplate === null) {
            $builder->add('domainId', DomainType::class, [
                'required' => true,
                'label' => t('Domain'),
                'constraints' => [
                    new NotBlank(),
                ],
                'position' => ['after' => 'subject'],
            ]);
        }

        if ($isOrderStatusTemplate === true) {
            $builder
                ->add('orderStatus', ChoiceType::class, [
                    'required' => true,
                    'label' => t('Stav objednávky'),
                    'multiple' => false,
                    'expanded' => false,
                    'choices' => $this->orderStatusFacade->getAll(),
                    'choice_label' => 'name',
                    'choice_value' => 'id',
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'position' => ['after' => 'bccEmail'],
                ]);
        }
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
