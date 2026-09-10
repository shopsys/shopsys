<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\ProductReview;

use Override;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\ProductReview\Image\ProductReviewImageData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductReviewImageFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rejectionReason', TextareaType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Reason for photo rejection',
                    'rows' => 5,
                ],
                'row_attr' => [
                    'data-product-review-photo-target' => 'reasonRow',
                ],
                'help' => t('Required when the photo is rejected.'),
            ])
            // the photo has no standalone rejection flag - a rejected photo is a photo with a rejection reason
            // and the switch is only a view of that state, hence the getter/setter mapping;
            // the field must stay added after rejectionReason so its setter can discard the reason of a shown photo
            ->add('isShown', CheckboxType::class, [
                'label' => 'Approved',
                'required' => false,
                'getter' => static fn (ProductReviewImageData $productReviewImageData): bool => $productReviewImageData->rejectionReason === null,
                'setter' => static function (ProductReviewImageData $productReviewImageData, bool $isShown): void {
                    if ($isShown) {
                        $productReviewImageData->rejectionReason = null;
                    }
                },
                'label_attr' => [
                    'class' => 'checkbox-switch',
                    'data-product-review-photo-target' => 'label',
                ],
                'attr' => [
                    'class' => 'product-review-image-approval-switch',
                    'data-product-review-photo-target' => 'checkbox',
                    'data-action' => 'change->product-review-photo#render',
                ],
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, $this->validateRejectionReason(...));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductReviewImageData::class,
        ]);
    }

    private function validateRejectionReason(FormEvent $event): void
    {
        $form = $event->getForm();

        if ($form->get('isShown')->getData() === true) {
            return;
        }

        /** @var \Shopsys\FrameworkBundle\Model\ProductReview\Image\ProductReviewImageData $productReviewImageData */
        $productReviewImageData = $event->getData();

        if (TransformStringHelper::emptyToNull($productReviewImageData->rejectionReason) === null) {
            $form->get('rejectionReason')->addError(new FormError(t('Please enter reason for photo rejection')));
        }
    }
}
