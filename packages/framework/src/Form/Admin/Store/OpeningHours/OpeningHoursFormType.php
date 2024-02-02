<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Store\OpeningHours;

use Shopsys\FrameworkBundle\Form\Transformers\OpeningHourTimeToStringTransformer;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpeningHoursFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Form\Transformers\OpeningHourTimeToStringTransformer $openingHourTimeToStringTransformer
     */
    public function __construct(
        protected readonly OpeningHourTimeToStringTransformer $openingHourTimeToStringTransformer,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $timeOptions = [
            'widget' => 'single_text',
            'attr' => [
                'class' => 'full-width',
            ],
            'label' => false,
        ];

        $builder->add('openingTime', TimeType::class, $timeOptions);
        $builder->add('closingTime', TimeType::class, $timeOptions);

        foreach ($builder->all() as $child) {
            $child->addModelTransformer($this->openingHourTimeToStringTransformer);
        }
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
