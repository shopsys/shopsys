<?php

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PromoCodeFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade
     */
    private $promoCodeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null
     */
    private $promoCode;

    public function __construct(PromoCodeFacade $promoCodeFacade)
    {
        $this->promoCodeFacade = $promoCodeFacade;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->promoCode = $options['promo_code'];

        $builder
            ->add('code', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter code',
                    ]),
                    new Constraints\Callback([$this, 'validateUniquePromoCode']),
                ],
            ])
            ->add('percent', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter discount percentage',
                    ]),
                    new Constraints\Range([
                        'min' => 0,
                        'max' => 100,
                    ]),
                ],
                'invalid_message' => 'Please enter whole number.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('promo_code')
            ->setAllowedTypes('promo_code', [PromoCode::class, 'null'])
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    public function validateUniquePromoCode(string $promoCodeValue, ExecutionContextInterface $context): void
    {
        if ($this->promoCode === null || $promoCodeValue !== $this->promoCode->getCode()) {
            $promoCode = $this->promoCodeFacade->findPromoCodeByCode($promoCodeValue);

            if ($promoCode !== null) {
                $context->addViolation('Promo code with this code already exists');
            }
        }
    }
}
