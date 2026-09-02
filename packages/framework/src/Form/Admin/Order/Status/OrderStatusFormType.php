<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Order\Status;

use Override;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewEnabledChecker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class OrderStatusFormType extends AbstractType
{
    public function __construct(
        private readonly ProductReviewEnabledChecker $productReviewEnabledChecker,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', LocalizedType::class, [
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(message: 'Please enter order status name in all languages'),
                        new Constraints\Length(
                            max: 255,
                            maxMessage: 'Status name cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
            ]);

        if ($this->productReviewEnabledChecker->isEnabledOnAnyDomain()) {
            $builder->add('productReviewsAllowed', CheckboxType::class, [
                'label' => 'Allow product reviews',
                'required' => false,
            ]);
        }
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderStatusData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
