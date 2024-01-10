<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as SymfonyDateTimeType;
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
        $resolver->setDefaults([
            'widget' => 'single_text',
            'format' => static::FORMAT_PHP,
            'view_timezone' => $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId(Domain::FIRST_DOMAIN_ID)->getName(),
            'html5' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return SymfonyDateTimeType::class;
    }
}
