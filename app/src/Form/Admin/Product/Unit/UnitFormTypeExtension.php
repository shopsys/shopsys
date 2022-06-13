<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Unit;

use Shopsys\FrameworkBundle\Form\Admin\Product\Unit\UnitFormType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class UnitFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var \App\Model\Product\Unit\UnitData $unitData */
        $unitData = $options['data'];

        $builder->add('akeneoCode', DisplayOnlyType::class, [
            'data' => $unitData->akeneoCode,
            'compound' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield UnitFormType::class;
    }
}
