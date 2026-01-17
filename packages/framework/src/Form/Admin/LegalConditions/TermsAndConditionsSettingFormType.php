<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\LegalConditions;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalSettingFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class TermsAndConditionsSettingFormType extends AbstractType
{
    public function __construct(private readonly ArticleFacade $articleFacade)
    {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $articles = $this->articleFacade->getAllByDomainId($options['domain_id']);

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        $builderSettingsGroup
            ->add('termsAndConditionsArticle', ChoiceType::class, [
                'required' => false,
                'choices' => $articles,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => '-- Choose article --',
                'label' => 'Terms and conditions',
                'help' => t('Choose article, which will serve as terms and conditions with which the customer has to agree when creating order.'),
            ]);

        $builderWithdrawalGroup = $builder->create('withdrawal', GroupType::class, [
            'label' => t('Withdrawal from contract'),
        ]);

        $builderWithdrawalGroup
            ->add('withdrawalDeadlineDays', IntegerType::class, [
                'required' => true,
                'label' => 'Withdrawal deadline (days)',
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please enter withdrawal deadline.',
                    ),
                    new Constraints\Positive(
                        message: 'Withdrawal deadline must be a positive number.',
                    ),
                ],
            ])
            ->add('withdrawalInstructions', CKEditorType::class, [
                'required' => true,
                'label' => 'Withdrawal instructions',
                'help' => t('The instructions are presented to the customer after they submit the order withdrawal request.'),
                'available_variables' => [
                    WithdrawalSettingFacade::VARIABLE_ORDER_NUMBER => t('Order number'),
                    WithdrawalSettingFacade::VARIABLE_ORDER_DETAIL_URL => t('Order detail URL address'),
                ],
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please enter withdrawal instructions.',
                    ),
                ],
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add($builderWithdrawalGroup)
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('domain_id')
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
