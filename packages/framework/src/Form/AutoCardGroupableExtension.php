<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AutoCardGroupableExtension extends AbstractTypeExtension
{
    /**
     * Invisible/utility types that don't break auto-card sequences
     * These fields are skipped during grouping and rendered by form_rest()
     *
     * @var array<class-string>
     */
    private array $invisibleTypes = [
        ActionBarType::class,
        HiddenType::class,
    ];

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($form->getParent() !== null) {
            return;
        }

        $this->markChildrenWithCardMetadata($view, $form);
    }

    private function markChildrenWithCardMetadata(FormView $view, FormInterface $form): void
    {
        $hasAutoCardableFields = false;

        foreach ($view->children as $name => $childView) {
            if (!isset($form[$name])) {
                $childView->vars['is_invisible_field'] = true;

                continue;
            }

            $child = $form[$name];
            $childFormType = $child->getConfig()->getType()->getInnerType();

            if ($this->isInvisibleType($childFormType::class)) {
                $childView->vars['is_invisible_field'] = true;

                continue;
            }

            $isRenderedInOwnCard = $child->getConfig()->getOption('renders_in_own_card', false);
            $childView->vars['renders_in_own_card'] = $isRenderedInOwnCard;

            if (!$isRenderedInOwnCard) {
                $hasAutoCardableFields = true;
            }
        }

        $view->vars['has_auto_cardable_fields'] = $hasAutoCardableFields;
    }

    /**
     * @param class-string $typeClass
     */
    private function isInvisibleType(string $typeClass): bool
    {
        return in_array($typeClass, $this->invisibleTypes, true);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'renders_in_own_card' => false,
        ]);

        $resolver->setAllowedTypes('renders_in_own_card', 'bool');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield FormType::class;
    }
}
