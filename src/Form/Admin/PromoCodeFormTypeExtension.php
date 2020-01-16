<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFormType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class PromoCodeFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['promo_code'] === null) {
            $builder->add('domainId', DomainType::class, [
                'required' => true,
                'label' => t('Domain'),
            ]);
        }
    }

    public function getExtendedType()
    {
        return PromoCodeFormType::class;
    }
}
