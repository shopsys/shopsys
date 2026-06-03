<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryDownloader;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CategoryFormType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ZboziCategoryFacade $zboziCategoryFacade,
        private readonly Domain $domain,
        private readonly ZboziCategoryDownloader $zboziCategoryDownloader,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($this->zboziCategoryDownloader->getSupportedLocales() as $locale) {
            if (!$this->domain->anyDomainHasLocale($locale)) {
                continue;
            }

            $zboziCategories = $this->zboziCategoryFacade->getAllIndexedByZboziId($locale);
            $builder->add(CategoryCrudExtension::createFormFieldKeyByLocale($locale), ChoiceType::class, [
                'label' => $this->getLabelForLocale($locale),
                'translation_domain' => false,
                'choices' => $zboziCategories,
                'required' => false,
                'choice_label' => 'getFullName',
            ]);
        }
    }

    private function getLabelForLocale(string $locale): string
    {
        return match ($locale) {
            'cs' => $this->translator->trans('Zbozi.cz category'),
            default => $this->translator->trans('Zbozi category'),
        };
    }
}
