<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as SymfonyDateTimeType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeType extends AbstractType
{
    protected const FORMAT_PHP = 'dd.MM.yyyy HH:mm:ss';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     */
    public function __construct(protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider)
    {
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaultAttr = [
            'icon' => true,
            'iconTitle' => t('Enter the date and time in the format dd.mm.yyyy hh:mm (e.g. 31.12.2023 10:12:00)'),
        ];
        $resolver->setDefaults([
            'widget' => 'single_text',
            'format' => static::FORMAT_PHP,
            'view_timezone' => $this->displayTimeZoneProvider->getDisplayTimeZoneForAdmin()->getName(),
            'attr' => $defaultAttr,
            'html5' => false,
        ]);

        $resolver->setNormalizer('attr', function (Options $options, $userAttr) use ($defaultAttr) {
            return array_merge($defaultAttr, $userAttr);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return SymfonyDateTimeType::class;
    }
}
