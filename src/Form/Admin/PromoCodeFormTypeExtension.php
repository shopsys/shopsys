<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Order\PromoCode\PromoCode;
use App\Model\Order\PromoCode\PromoCodeData;
use App\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFormType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PromoCodeFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFacade
     */
    private $promoCodeFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCode|null
     */
    private $promoCode;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     */
    public function __construct(PromoCodeFacade $promoCodeFacade)
    {
        $this->promoCodeFacade = $promoCodeFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->promoCode = $options['promo_code'];

        if ($this->promoCode === null) {
            $builder->add('domainId', DomainType::class, [
                'required' => true,
                'label' => t('Domain'),
            ]);
        }

        $codeOptions = $builder->get('code')->getOptions();
        $codeOptions['constraints'] = [
            new Constraints\NotBlank(['message' => 'Please enter code']),
        ];
        $codeOptions['position'] = 'first';

        $builder->add('code', TextType::class, $codeOptions);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setAllowedTypes('promo_code', [PromoCode::class, 'null'])
            ->setDefaults([
                'constraints' => [
                    new Constraints\Callback([$this, 'validateUniquePromoCodeByDomain']),
                ],
            ]);
    }

    public function getExtendedType()
    {
        return PromoCodeFormType::class;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateUniquePromoCodeByDomain(PromoCodeData $promoCodeData, ExecutionContextInterface $context)
    {
        if ($this->promoCode === null || $promoCodeData->code !== $this->promoCode->getCode()) {
            $promoCode = $this->promoCodeFacade->findPromoCodeByCodeAndDomain($promoCodeData->code, $promoCodeData->domainId);
            if ($promoCode !== null) {
                $context->buildViolation('Promo code with this code already exists')->atPath('code')->addViolation();
            }
        }
    }
}
