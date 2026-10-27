<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Store\OpeningHours;

use Override;
use Shopsys\FrameworkBundle\Form\InteractiveCollectionType;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OpeningHoursRangeCollectionFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('openingHoursRanges', InteractiveCollectionType::class, [
            'add_label' => t('Add opening hours'),
            'entry_type' => OpeningHoursRangeFormType::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OpeningHoursData::class,
        ]);
    }
}
