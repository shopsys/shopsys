<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Form\Admin\Type;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Grid of rich text cells, a list of rows with a list of cells in each
 */
final class TableCellsType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => CollectionType::class,
            'entry_options' => [
                'entry_type' => WysiwygType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
            ],
            'allow_add' => true,
            'allow_delete' => true,
        ]);
    }

    #[Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
