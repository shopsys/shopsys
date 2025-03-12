<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatePickerType extends AbstractType
{
    protected const string FORMAT_PHP = 'dd.MM.yyyy';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     */
    public function __construct(protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider)
    {
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaults = [
            'widget' => 'single_text',
            'format' => static::FORMAT_PHP,
            'html5' => false,
            'view_timezone' => $this->displayTimeZoneProvider->getDisplayTimeZoneForAdmin()->getName(),
        ];

        $resolver->setDefaults($defaults);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): ?string
    {
        return DateType::class;
    }
}
