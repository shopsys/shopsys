<?php

declare(strict_types=1);

namespace App\Form\Admin\Mail;

use App\Model\Mail\MailTemplate;
use App\Model\Order\Order;
use App\Model\Payment\PaymentFacade;
use App\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Form\Admin\Mail\MailTemplateFormType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MailTemplateFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \App\Model\Transport\TransportFacade
     */
    private TransportFacade $transportFacade;

    /**
     * @var \App\Model\Payment\PaymentFacade
     */
    private PaymentFacade $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade
     */
    private OrderStatusFacade $orderStatusFacade;

    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade
     */
    public function __construct(
        TransportFacade $transportFacade,
        PaymentFacade $paymentFacade,
        OrderStatusFacade $orderStatusFacade
    ) {
        $this->transportFacade = $transportFacade;
        $this->paymentFacade = $paymentFacade;
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
                ])
                ->add('transport', ChoiceType::class, [
                    'required' => true,
                    'label' => t('Doprava'),
                    'multiple' => false,
                    'expanded' => false,
                    'choices' => $this->transportFacade->getAll(),
                    'choice_label' => 'name',
                    'choice_value' => 'id',
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'position' => ['after' => 'orderStatus'],
                ])
                ->add('payment', ChoiceType::class, [
                    'required' => true,
                    'label' => t('Platba'),
                    'multiple' => false,
                    'expanded' => false,
                    'choices' => $this->paymentFacade->getAll(),
                    'choice_label' => 'name',
                    'choice_value' => 'id',
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'position' => ['after' => 'transport'],
                ]);
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setAllowedTypes('entity', [MailTemplate::class, 'null']);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield MailTemplateFormType::class;
    }
}
