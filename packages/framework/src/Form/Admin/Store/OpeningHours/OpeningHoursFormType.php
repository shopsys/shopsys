<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Store\OpeningHours;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
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
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProvider $displayTimeZoneProvider
     */
    public function __construct(
        protected readonly OpeningHourTimeToStringTransformer $openingHourTimeToStringTransformer,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
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
            'view_timezone' => $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId(Domain::FIRST_DOMAIN_ID)->getName(),
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
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OpeningHoursData::class,
        ]);
    }
}
