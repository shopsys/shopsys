<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatePickerType extends AbstractType
{
    protected const FORMAT_PHP = 'dd.MM.yyyy';
    public const FORMAT_JS = 'dd.mm.yy';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface|null $displayTimeZoneProvider
     */
    public function __construct(protected readonly ?DisplayTimeZoneProviderInterface $displayTimeZoneProvider = null)
    {
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $defaults = [
            'widget' => 'single_text',
            'format' => static::FORMAT_PHP,
            'html5' => false,
        ];

        if ($this->displayTimeZoneProvider !== null) {
            $defaults['view_timezone'] = $this->displayTimeZoneProvider->getDisplayTimeZone()->getName();
        }

        $resolver->setDefaults($defaults);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return DateType::class;
    }
}
