<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as SymfonyDateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DateTimeType extends AbstractType
{
    protected const string FORMAT_PHP = 'dd.MM.yyyy HH:mm:ss';

    public function __construct(protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider)
    {
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'format' => self::FORMAT_PHP,
            'view_timezone' => $this->displayTimeZoneProvider->getDisplayTimeZoneForAdmin()->getName(),
            'help' => t('Enter the date and time in the format dd.mm.yyyy hh:mm (e.g. 31.12.2023 10:12:00)'),
            'html5' => false,
            'input' => 'datetime_immutable',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return SymfonyDateTimeType::class;
    }
}
