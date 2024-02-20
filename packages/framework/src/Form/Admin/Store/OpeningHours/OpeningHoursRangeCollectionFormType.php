<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Store\OpeningHours;

use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpeningHoursRangeCollectionFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('openingHoursRanges', CollectionType::class, [
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => OpeningHoursRangeFormType::class,
            'error_bubbling' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OpeningHoursData::class,
        ]);
    }
}
