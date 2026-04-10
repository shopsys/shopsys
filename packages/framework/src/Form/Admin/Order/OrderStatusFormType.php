<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Order;

use Override;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OrderStatusFormType extends AbstractType
{
    public function __construct(
        private readonly OrderStatusFacade $orderStatusFacade,
        private readonly WithdrawalRequestFacade $withdrawalRequestFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Order\Order $order */
        $order = $options['order'];
        $withdrawalRequest = $this->withdrawalRequestFacade->findByOrder($order);

        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'required' => true,
                'choices' => $this->orderStatusFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'choice_attr' => function (OrderStatus $orderStatus) {
                    return [
                        'data-js-order-status-type' => $orderStatus->getType(),
                    ];
                },
                'multiple' => false,
                'expanded' => false,
                'attr' => [
                    'data-js-order-status-select' => null,
                ],
            ]);

        $builder->add('withdrawalRequestData', OrderWithdrawalFormType::class, [
            'label' => false,
            'row_attr' => [
                'data-withdrawal-request-exists' => $withdrawalRequest !== null ? 'true' : 'false',
                'style' => $withdrawalRequest === null ? 'display: none;' : '',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('order')
            ->setAllowedTypes('order', Order::class)
            ->setDefaults([
                'inherit_data' => true,
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    /** @var \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData */
                    $orderData = $form->getData();

                    /** @var \Shopsys\FrameworkBundle\Model\Order\Order $order */
                    $order = $form->getConfig()->getOption('order');
                    $withdrawalRequest = $this->withdrawalRequestFacade->findByOrder($order);

                    if (
                        $withdrawalRequest !== null ||
                        $orderData->status?->getType() === OrderStatusTypeEnum::TYPE_WITHDRAWN
                    ) {
                        $validationGroups[] = OrderWithdrawalFormType::VALIDATION_GROUP_WITHDRAWAL_REQUIRED;
                    }

                    return $validationGroups;
                },
            ]);
    }
}
