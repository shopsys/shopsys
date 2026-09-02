<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\ProductReview;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewData;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewStatusEnum;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class ProductReviewFormType extends AbstractType
{
    protected const string VALIDATION_GROUP_STATUS_REJECTED = 'statusRejected';
    protected const string VALIDATION_GROUP_CONTENT_EDITED = 'contentEdited';

    public function __construct(
        protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
        protected readonly ProductReviewStatusEnum $productReviewStatusEnum,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $productReview = $options['productReview'];

        $builder->add($this->createReviewContentGroup($builder));
        $builder->add($this->createModerationGroup($builder, $productReview));

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_crud_product_review_list',
            'entity' => $productReview,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('productReview')
            ->setAllowedTypes('productReview', ProductReview::class)
            ->setDefaults([
                'data_class' => ProductReviewData::class,
                'attr' => [
                    'novalidate' => 'novalidate',
                    'data-controller' => 'product-review-form',
                    'data-product-review-form-rejected-status-value' => ProductReviewStatusEnum::STATUS_REJECTED,
                ],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    /** @var \Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewData $productReviewData */
                    $productReviewData = $form->getData();

                    if ($productReviewData->status === ProductReviewStatusEnum::STATUS_REJECTED) {
                        $validationGroups[] = self::VALIDATION_GROUP_STATUS_REJECTED;
                    }

                    /** @var \Shopsys\FrameworkBundle\Model\ProductReview\ProductReview $productReview */
                    $productReview = $form->getConfig()->getOption('productReview');

                    if ($productReview->isContentEdited($productReviewData)) {
                        $validationGroups[] = self::VALIDATION_GROUP_CONTENT_EDITED;
                    }

                    return $validationGroups;
                },
            ]);
    }

    private function createReviewContentGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderReviewContentGroup = $builder->create('reviewContentGroup', GroupType::class, [
            'label' => 'Review content',
        ]);

        $builderReviewContentGroup
            ->add('firstName', TextType::class, [
                'label' => 'First name',
                'required' => true,
                'attr' => $this->getContentFieldAttributes(),
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter first name'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'First name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last name',
                'required' => true,
                'attr' => $this->getContentFieldAttributes(),
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter last name'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Last name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'required' => true,
                'attr' => $this->getContentFieldAttributes(),
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter email'),
                    new Email(message: 'Please enter valid email'),
                    new Constraints\Length(
                        max: 255,
                        maxMessage: 'Email cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('isAnonymous', CheckboxType::class, [
                'label' => 'Published anonymously',
                'required' => false,
                'attr' => $this->getContentFieldAttributes(),
            ])
            ->add('text', TextareaType::class, [
                'label' => 'Text review',
                'required' => false,
                'attr' => $this->getContentFieldAttributes(),
            ])
            ->add('contentChangeReason', TextareaType::class, [
                'label' => 'Reason for content change',
                'required' => true,
                'row_attr' => [
                    'data-product-review-form-target' => 'contentChangeReason',
                ],
                'help' => t('Required when the review content is changed. The reason is stored in the review history only.'),
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please enter reason for content change',
                        groups: [self::VALIDATION_GROUP_CONTENT_EDITED],
                    ),
                ],
            ]);

        return $builderReviewContentGroup;
    }

    /**
     * @return array<string, string>
     */
    private function getContentFieldAttributes(): array
    {
        return [
            'data-product-review-form-target' => 'contentField',
            'data-action' => 'product-review-form#updateContentChangeReasonVisibility',
        ];
    }

    private function createModerationGroup(
        FormBuilderInterface $builder,
        ProductReview $productReview,
    ): FormBuilderInterface {
        $builderModerationGroup = $builder->create('moderationGroup', GroupType::class, [
            'label' => 'Moderation',
        ]);

        $builderModerationGroup
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'required' => true,
                'choices' => $this->productReviewStatusEnum->getAllIndexedByTranslations(),
                'attr' => [
                    'data-product-review-form-target' => 'status',
                    'data-action' => 'product-review-form#updateRejectionReasonVisibility',
                ],
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please choose review status'),
                ],
            ])
            ->add('rejectionReason', TextareaType::class, [
                'label' => 'Reason for rejection',
                'required' => true,
                'row_attr' => [
                    'data-product-review-form-target' => 'rejectionReason',
                ],
                'help' => t('Required when the review is rejected.'),
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please enter reason for rejection',
                        groups: [self::VALIDATION_GROUP_STATUS_REJECTED],
                    ),
                ],
            ])
            ->add('responseText', TextareaType::class, [
                'label' => 'E-shop response',
                'required' => false,
                'help' => $this->getResponsePublishingHelp($productReview),
            ]);

        return $builderModerationGroup;
    }

    private function getResponsePublishingHelp(ProductReview $productReview): ?string
    {
        $responseCreatedAt = $productReview->getResponseCreatedAt();

        if ($responseCreatedAt === null) {
            return null;
        }

        return t('Response published on %date%', [
            '%date%' => $this->dateTimeFormatterExtension->formatDateTime($responseCreatedAt),
        ]);
    }
}
