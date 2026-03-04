<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Form;

use Override;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Form\Constraints\MoneyRange;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryDownloader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class HeurekaProductFormType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly HeurekaCategoryDownloader $heurekaCategoryDownloader,
        private readonly Domain $domain,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $defaultOptionsByLocale = [
            'cs' => [
                'currency' => 'CZK',
                'constraints' => [
                    new MoneyRange(
                        min: Money::zero(),
                        max: Money::create(1000),
                    ),
                ],
            ],
            'sk' => [
                'currency' => 'EUR',
                'constraints' => [
                    new MoneyRange(
                        min: Money::zero(),
                        max: Money::create(50),
                    ),
                ],
            ],
        ];

        $supportedLocales = $this->heurekaCategoryDownloader->getSupportedLocales();
        $optionsByDomainId = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();

            if (in_array($locale, $supportedLocales, true)) {
                $optionsByDomainId[$domainConfig->getId()] = $defaultOptionsByLocale[$locale] ?? [];
            } else {
                $optionsByDomainId[$domainConfig->getId()] = [
                    'disabled' => true,
                    'help' => $this->translator->trans('Heureka is not available for this domain locale'),
                ];
            }
        }

        $builder->add('cpc', MultidomainType::class, [
            'label' => $this->translator->trans('Maximum price per click'),
            'entry_type' => MoneyType::class,
            'required' => false,
            'layout' => 'block',
            'display_mode' => 'columns',
            'options_by_domain_id' => $optionsByDomainId,
        ]);
    }
}
