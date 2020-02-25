<?php

namespace Shopsys\FrameworkBundle\Form;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use Shopsys\FrameworkBundle\Form\Transformers\NumericToMoneyTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveWhitespacesTransformer;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoneyTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    /**
     * @var \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface
     */
    private $intlCurrencyRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface $intlCurrencyRepository
     */
    public function __construct(Localization $localization, CurrencyRepositoryInterface $intlCurrencyRepository)
    {
        $this->localization = $localization;
        $this->intlCurrencyRepository = $intlCurrencyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new NumericToMoneyTransformer($options['scale']));
        $builder->addViewTransformer(new RemoveWhitespacesTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['money_pattern'] = $this->getPattern();
        if ($options['currency']) {
            $view->vars['symbolAfterInput'] = $this->intlCurrencyRepository->get($options['currency'], $this->localization->getLocale());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('currency', false);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield MoneyType::class;
    }

    /**
     * Returns the pattern for this locale. Always places currency symbol after widget.
     * The pattern contains the placeholder "{{ widget }}" where the HTML tag should be inserted
     * @return string
     * @see \Symfony\Component\Form\Extension\Core\Type\MoneyType::getPattern()
     */
    private function getPattern()
    {
        return '{{ widget }}';
    }
}
