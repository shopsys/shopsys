<?php

declare(strict_types=1);

namespace App\Form\Admin\Mail;

use App\Model\Mail\MailTemplate;
use App\Model\Order\Order;
use App\Model\Payment\PaymentFacade;
use App\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Form\Admin\Mail\MailTemplateFormType;
use Shopsys\FrameworkBundle\Form\DomainType;
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
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     */
    public function __construct(TransportFacade $transportFacade, PaymentFacade $paymentFacade)
    {
        $this->transportFacade = $transportFacade;
        $this->paymentFacade = $paymentFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \App\Model\Mail\MailTemplate|null $mailTemplate */
        $mailTemplate = $options['entity'];
        $isOrderStockStatusTemplate = $mailTemplate === null || $mailTemplate->getName() === MailTemplate::ORDER_STOCK_STATUS_NAME;

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

        if ($isOrderStockStatusTemplate === true) {
            $builder
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
                    'position' => ['before' => 'bccEmail'],
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
                ])
                ->add('orderStockStatus', ChoiceType::class, [
                    'required' => true,
                    'label' => t('Skladovost'),
                    'multiple' => false,
                    'expanded' => false,
                    'choices' => array_flip(Order::getAllStockStatusesTranslations()),
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'position' => ['after' => 'payment'],
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
