<?php

declare(strict_types=1);

namespace App\Form\Admin\Store\OpeningHours;

use App\Form\Admin\Transformer\OpeningHourTimeToStringTransformer;
use App\Model\Store\OpeningHours\OpeningHoursData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpeningHoursFormType extends AbstractType
{
    /**
     * @param \App\Form\Admin\Transformer\OpeningHourTimeToStringTransformer $openingHourTimeToStringTransformer
     */
    public function __construct(
        protected readonly OpeningHourTimeToStringTransformer $openingHourTimeToStringTransformer,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $timeOptions = [
            'widget' => 'single_text',
            'attr' => [
                'class' => 'full-width',
            ],
        ];

        $builder->add('firstOpeningTime', TimeType::class, $timeOptions);
        $builder->add('firstClosingTime', TimeType::class, $timeOptions);
        $builder->add('secondOpeningTime', TimeType::class, $timeOptions);
        $builder->add('secondClosingTime', TimeType::class, $timeOptions);

        foreach ($builder->all() as $child) {
            $child->addModelTransformer($this->openingHourTimeToStringTransformer);
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OpeningHoursData::class,
        ]);
    }
}
