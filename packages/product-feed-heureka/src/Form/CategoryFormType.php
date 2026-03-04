<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryDownloader;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CategoryFormType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly HeurekaCategoryFacade $heurekaCategoryFacade,
        private readonly Domain $domain,
        private readonly HeurekaCategoryDownloader $heurekaCategoryDownloader,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($this->heurekaCategoryDownloader->getSupportedLocales() as $locale) {
            if (!$this->domain->anyDomainHasLocale($locale)) {
                continue;
            }

            $heurekaCategories = $this->heurekaCategoryFacade->getAllIndexedByHeurekaId($locale);
            $builder->add(CategoryCrudExtension::createFormFieldKeyByLocale($locale), ChoiceType::class, [
                'label' => $this->getLabelForLocale($locale),
                'translation_domain' => false,
                'choices' => $heurekaCategories,
                'required' => false,
                'choice_label' => 'getName',
            ]);
        }
    }

    private function getLabelForLocale(string $locale): string
    {
        return match ($locale) {
            'cs' => $this->translator->trans('Heureka.cz category'),
            'sk' => $this->translator->trans('Heureka.sk category'),
            default => $this->translator->trans('Heureka category'),
        };
    }
}
