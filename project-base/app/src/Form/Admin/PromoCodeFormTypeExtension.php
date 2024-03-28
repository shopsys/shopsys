<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFormType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class PromoCodeFormTypeExtension extends AbstractTypeExtension
{
    private ?PromoCode $promoCode;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        private readonly BrandFacade $brandFacade,
        private readonly PricingGroupFacade $pricingGroupFacade,
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->promoCode = $options['promo_code'];

        if ($options['mass_generate'] !== true) {
            return;
        }

        $builder->add($this->addMassGenerationGroup($builder));
        $builder->add('saveAndDownloadCsv', SubmitType::class, [
            'label' => t('Create and download CSV'),
        ]);
        $builder->remove('code');
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['promo_code', 'mass_generate'])
            ->setAllowedTypes('promo_code', [PromoCode::class, 'null'])
            ->setAllowedTypes('mass_generate', 'bool')
            ->setDefaults([
                'mass_generate' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield PromoCodeFormType::class;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function addMassGenerationGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderMassPromoCodeGroup = $builder->create('massPromoCodeGroup', GroupType::class, [
            'label' => t('Bulk promo code generation'),
            'position' => 'first',
        ]);

        $builderMassPromoCodeGroup
            ->add('prefix', TextType::class, [
                'label' => t('Prefix (e.g. "SPRING_")'),
                'required' => false,
            ])
            ->add('quantity', IntegerType::class, [
                'label' => t('Number of generated promo codes'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter the quantity.',
                    ]),
                    new Positive([
                        'message' => 'Please enter the positive value.',
                    ]),
                ],
                'invalid_message' => 'Please enter the whole number.',
            ]);

        return $builderMassPromoCodeGroup;
    }
}
