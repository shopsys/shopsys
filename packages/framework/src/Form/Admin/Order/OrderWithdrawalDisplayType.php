<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Order;

use Override;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OrderWithdrawalDisplayType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension
     */
    public function __construct(
        protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest $withdrawalRequest */
        $withdrawalRequest = $options['withdrawal_request'];

        $builder
            ->add('firstName', DisplayOnlyType::class, [
                'label' => 'First name',
                'data' => $withdrawalRequest->getFirstName(),
            ])
            ->add('lastName', DisplayOnlyType::class, [
                'label' => 'Last name',
                'data' => $withdrawalRequest->getLastName(),
            ])
            ->add('telephone', DisplayOnlyType::class, [
                'label' => 'Phone',
                'data' => $withdrawalRequest->getTelephone(),
            ])
            ->add('email', DisplayOnlyType::class, [
                'label' => 'Email',
                'data' => $withdrawalRequest->getEmail(),
            ])
            ->add('note', DisplayOnlyType::class, [
                'label' => 'Note',
                'data' => $withdrawalRequest->getNote(),
            ])
            ->add('requestedAt', DisplayOnlyType::class, [
                'label' => 'Requested at',
                'data' => $this->dateTimeFormatterExtension->formatDateTime($withdrawalRequest->getRequestedAt()),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('withdrawal_request')
            ->setAllowedTypes('withdrawal_request', WithdrawalRequest::class)
            ->setDefaults([
                'label' => false,
                'mapped' => false,
            ]);
    }
}
