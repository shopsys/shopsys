<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use Override;
use Shopsys\FrameworkBundle\Form\Transformers\NumericToMoneyTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveWhitespacesTransformer;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MoneyTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly Localization $localization,
        private readonly CurrencyRepositoryInterface $intlCurrencyRepository,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new NumericToMoneyTransformer($options['scale']));
        $builder->addViewTransformer(new RemoveWhitespacesTransformer());
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['money_pattern'] = $this->getPattern();

        if (!$options['currency']) {
            return;
        }

        $view->vars['symbolAfterInput'] = $this->intlCurrencyRepository->get(
            $options['currency'],
            $this->localization->getRequestLocale(),
        );
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('currency', false);
    }

    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield MoneyType::class;
    }

    /**
     * Returns the pattern for this locale. Always places currency symbol after widget.
     * The pattern contains the placeholder "{{ widget }}" where the HTML tag should be inserted
     *
     * @see \Symfony\Component\Form\Extension\Core\Type\MoneyType::getPattern()
     */
    private function getPattern(): string
    {
        return '{{ widget }}';
    }
}
